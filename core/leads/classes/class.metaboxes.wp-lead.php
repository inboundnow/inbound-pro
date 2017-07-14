<?php

/**
 * Class manages lead profile edit area
 *
 * @package Leads
 * @subpackage Management
 */


class Inbound_Metaboxes_Leads {

    static $tabs;
    static $active_tab;
    static $mapped_fields;
    static $full_contact;
    static $page_views;
    static $conversions;
    static $form_submissions;
    static $custom_event_data;
    static $comments;
    static $searches;
    static $custom_events;
    static $lead_metadata;

    /**
     *  Initialize Class
     */
    public function __construct() {
        self::load_hooks();
    }


    /**
     *  Load hooks and filters
     */
    public static function load_hooks() {

        /* setup class variables */
        add_action('admin_head', array(__CLASS__, 'setup_vars'));

        /* Hide metaboxes */
        add_filter('default_hidden_meta_boxes', array(__CLASS__, 'hide_metaboxes'), 10, 2);

        /* Add Metaboxes */
        add_action('add_meta_boxes', array(__CLASS__, 'define_metaboxes'));

        /* Add Quick Stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_page_views'));
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_form_submissions'));
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_lead_searches'));
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_lead_comments'));
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_last_activity'), 100 );

        /* Add header metabox   */
        add_action('edit_form_after_title', array(__CLASS__, 'add_header'));

        /* Add Save Actions */
        add_action('save_post', array(__CLASS__, 'save_data'));

        /* Enqueue JS */
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
        add_action('admin_print_footer_scripts', array(__CLASS__, 'print_admin_scripts'));

    }

    /**
     * Setup static variables
     */
    public static function setup_vars() {

        $screen = get_current_screen();

        if (!isset($screen) || $screen->id != 'wp-lead' || !isset($_GET['post'])) {
            return;
        }

        /* load lead meta data and make it available */
        self::$lead_metadata = get_post_meta($_GET['post']);
        foreach (self::$lead_metadata as $key => $array) {
            self::$lead_metadata[$key] = $array[0];
        }

        /* create first name and last name if not present */
        self::$lead_metadata['wpleads_first_name'] = (isset(self::$lead_metadata['wpleads_first_name'])) ? self::$lead_metadata['wpleads_first_name'] : '';
        self::$lead_metadata['wpleads_last_name'] = (isset(self::$lead_metadata['wpleads_last_name'])) ? self::$lead_metadata['wpleads_last_name']: '';

    }

    /**
     *  Hide unused metaboxes
     */
    public static function hide_metaboxes($hidden, $screen) {
        global $post;

        if (isset($post) && $post->post_type != 'wp-lead') {
            return $hidden;
        }

        $hidden = array('postexcerpt', 'slugdiv', 'postcustom', 'trackbacksdiv', 'lead-timelinestatusdiv', 'lead-timelinesdiv', 'authordiv', 'revisionsdiv', 'wpseo_meta', 'wp-advertisement-dropper-post', 'postdivrich');

        return $hidden;
    }

    /**
     *
     */
    public static function define_metaboxes() {
        global $post, $wp_meta_boxes;

        if ($post->post_type != 'wp-lead') {
            return;
        }

        self::$form_submissions = Inbound_Events::get_form_submissions($post->ID);
        self::$custom_events = Inbound_Events::get_custom_event_data_by('lead_id', array('lead_id' => $post->ID));

        /* Show quick stats */
        add_meta_box('wplead-quick-stats-metabox', __("Lead Stats", 'inbound-pro'), array(__CLASS__, 'display_quick_stats'), 'wp-lead', 'side', 'high');

        /* Main metabox */
        add_meta_box('wplead_metabox_main', // $id
            __('Lead Overview', 'inbound-pro'), array(__CLASS__, 'display_main'), // $callback
            'wp-lead', // $page
            'normal', // $context
            'high' // $priority
        );


        add_meta_box('wplead_metabox_referal', // $id
            __('Source of Lead', 'inbound-pro'), array(__CLASS__, 'display_sources'), 'wp-lead', // $page
            'normal', // $context
            'high' // $priority
        );

        /*change the lead list metabox callback to a new function*/
        $wp_meta_boxes['wp-lead']['side']['core']['wplead_list_categorydiv']['callback'] = array(__CLASS__, 'display_lead_list_metabox');

    }

    /**
     * Renders a custom metabox for leads
     * The base function is post_categories_meta_box
     */
    public static function display_lead_list_metabox($post, $box){

        $defaults = array( 'taxonomy' => 'category' );
        if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
            $args = array();
        } else {
            $args = $box['args'];
        }
        $r = wp_parse_args( $args, $defaults );
        $tax_name = esc_attr( $r['taxonomy'] );
        $taxonomy = get_taxonomy( $r['taxonomy'] );

        /*get the lists waiting for double optin from the lead's meta*/
        $double_optin_lists = get_post_meta($post->ID, 'double_optin_lists', true);
        /*get the lists that the lead is in*/
        $applied_terms = wp_get_post_terms($post->ID, 'wplead_list_category');

        /**Update the lead's array of lists that still need confirmation if a list has been manually confirmed**/
        /*if there are double optin lists*/
        if(!empty($double_optin_lists) && is_array($double_optin_lists)){
            foreach($applied_terms as $applied_term){
                if(in_array($applied_term->term_id, $double_optin_lists)){
                    $index = array_search($applied_term->term_id, $double_optin_lists);
                    unset($double_optin_lists[$index]);
                }
            }
            /**if there are still lists awaiting double optin confirmation after the "waiting" meta listing has been updated**/
            if(!empty($double_optin_lists)){
                /*update the "waiting" meta listing with the remaining lists*/
                update_post_meta($post->ID, 'double_optin_lists', array_values($double_optin_lists));
            }else{
                /**if there are no lists awaiting double optin confirmation**/
                /*get the double optin waiting list id*/
                if(!defined('INBOUND_PRO_CURRENT_VERSION')){
                    $double_optin_list_id = get_option('list-double-optin-list-id', '');
                }else{
                    $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                    $double_optin_list_id = $settings['leads']['list-double-optin-list-id'];
                }
                /*remove the meta listing for double optin*/
                delete_post_meta($post->ID, 'double_optin_lists');
                /*remove this lead from the double optin list*/
                wp_remove_object_terms($post->ID, $double_optin_list_id, 'wplead_list_category');
                /*update the lead status*/
                update_post_meta( $post->ID , 'wp_lead_status' , 'read');
                ?>
                <script>
                    /**update the status selector for the initial page reload**/
                    jQuery(document).ready(function(){
                        jQuery('#wp_lead_status').val('read');
                    });
                </script>
                <?php
            }
        }
        ?>
        <div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">
            <ul id="<?php echo $tax_name; ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo $tax_name; ?>-all"><?php echo $taxonomy->labels->all_items; ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo $tax_name; ?>-pop"><?php _e( 'Most Used' ); ?></a></li>
                <?php if(!empty($double_optin_lists) && is_array($double_optin_lists)){ ?>
                    <li class="hide-if-no-js"><a href="#<?php echo $tax_name; ?>-need-double-optin"><?php _e( 'Unconfirmed' ); ?></a></li>
                <?php } ?>
            </ul>

            <div id="<?php echo $tax_name; ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo $tax_name; ?>checklist-pop" class="categorychecklist form-no-clear" >
                    <?php $popular_ids = wp_popular_terms_checklist( $tax_name ); ?>
                </ul>
            </div>

            <div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
                <?php
                $name = ( $tax_name == 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
                echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
                ?>
                <ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
                    <?php wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'popular_cats' => $popular_ids ) ); ?>
                </ul>
            </div>

            <?php if(!empty($double_optin_lists) && is_array($double_optin_lists)){ ?>
                <div id="<?php echo $tax_name; ?>-need-double-optin" class="tabs-panel" style="display: none;">
                    <ul id="<?php echo $tax_name; ?>checklist-need-double-optin" class="categorychecklist form-no-clear" >
                        <?php foreach($double_optin_lists as $list_id){ ?>
                            <li id="wplead_list_category-<?php echo $list_id; ?>">

                                <input value="<?php echo $list_id; ?>" type="checkbox" name="tax_input[wplead_list_category][]" id="in-wplead_list_category-<?php echo $list_id; ?>" >
                                <label class="selectit" for="in-wplead_list_category-<?php echo $list_id; ?>" ><?php echo get_term($list_id, 'wplead_list_category')->name; ?></label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if(!empty($double_optin_lists) && is_array($double_optin_lists)){ ?>
                <div id="<?php echo $tax_name; ?>-need-double-optin" class="tabs-panel" style="display: none;">

                </div>
            <?php } ?>

            <?php if ( current_user_can( $taxonomy->cap->edit_terms ) ) : ?>
                <div id="<?php echo $tax_name; ?>-adder" class="wp-hidden-children">
                    <a id="<?php echo $tax_name; ?>-add-toggle" href="#<?php echo $tax_name; ?>-add" class="hide-if-no-js taxonomy-add-new">
                        <?php
                        /* translators: %s: add new taxonomy label */
                        printf( __( '+ %s' ), $taxonomy->labels->add_new_item );
                        ?>
                    </a>
                    <p id="<?php echo $tax_name; ?>-add" class="category-add wp-hidden-child">
                        <label class="screen-reader-text" for="new<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
                        <input type="text" name="new<?php echo $tax_name; ?>" id="new<?php echo $tax_name; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true"/>
                        <label class="screen-reader-text" for="new<?php echo $tax_name; ?>_parent">
                            <?php echo $taxonomy->labels->parent_item_colon; ?>
                        </label>
                        <?php
                        /*commented out to avoid possible conflicts*/
                        /*
                        $parent_dropdown_args = array(
                            'taxonomy'         => $tax_name,
                            'hide_empty'       => 0,
                            'name'             => 'new' . $tax_name . '_parent',
                            'orderby'          => 'name',
                            'hierarchical'     => 1,
                            'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
                        );
                        */
                        /**
                         * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
                         *
                         * @since 4.4.0
                         *
                         * @param array $parent_dropdown_args {
                         *     Optional. Array of arguments to generate parent dropdown.
                         *
                         *     @type string   $taxonomy         Name of the taxonomy to retrieve.
                         *     @type bool     $hide_if_empty    True to skip generating markup if no
                         *                                      categories are found. Default 0.
                         *     @type string   $name             Value for the 'name' attribute
                         *                                      of the select element.
                         *                                      Default "new{$tax_name}_parent".
                         *     @type string   $orderby          Which column to use for ordering
                         *                                      terms. Default 'name'.
                         *     @type bool|int $hierarchical     Whether to traverse the taxonomy
                         *                                      hierarchy. Default 1.
                         *     @type string   $show_option_none Text to display for the "none" option.
                         *                                      Default "&mdash; {$parent} &mdash;",
                         *                                      where `$parent` is 'parent_item'
                         *                                      taxonomy label.
                         * }
                         */
                        /*$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', $parent_dropdown_args );

                        wp_dropdown_categories( $parent_dropdown_args );*/
                        ?>
                        <input type="button" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />
                        <?php wp_nonce_field( 'add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false ); ?>
                        <span id="<?php echo $tax_name; ?>-ajax-response"></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }


    /**
     *    Adds header menu items
     */
    public static function add_header() {
        global $post;

        $statuses = Inbound_Leads::get_lead_statuses();

        if (empty ($post) || 'wp-lead' !== get_post_type($GLOBALS['post'])) {
            return;
        }


        echo "<div id='lead-top-area'>";
        echo "<div id='lead-header'><h1>" . self::$lead_metadata['wpleads_first_name'] . ' ' . self::$lead_metadata['wpleads_last_name'] . "</h1></div>";

        ?>
        <!-- REWRITE FOR FILTERS -->
        <div id='lead-status'>
            <label for="wp_lead_status"><?php _e('Lead Status:', 'inbound-pro'); ?></label>
            <?php

            echo '<select name="wp_lead_status" id="wp_lead_status" class="lead_status_dropdown">';
            foreach ($statuses as $status) {
                $selected = $status['key'] == (self::$lead_metadata['wp_lead_status']) ? ' selected ' : '';
                echo '<option value="' . $status['key'] . '" data-color="' . $status['color'] . '" ' . $selected . '> ' . $status['label'] . '</option>';
            }
            echo "</select>";
            ?>
        </div>
        <span id="current-lead-status" style="display:none;"><?php echo self::$lead_metadata['wp_lead_status']; ?></span>
        </div>
        <?php
    }


    /**
     *    Save meta data
     */
    public static function save_data($post_id) {
        global $post;

        if (!isset($post) || $post->post_type != 'wp-lead') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }


        /* lead status */
        if (isset($_POST['wp_lead_status'])) {
            update_post_meta($post_id, 'wp_lead_status', sanitize_text_field($_POST['wp_lead_status']));
        }

        /* Loop through mappable fields and save data */
        $Leads_Field_Map = new Leads_Field_Map();
        $wpleads_user_fields = $Leads_Field_Map->get_lead_fields();

        foreach ($wpleads_user_fields as $key => $field) {

            $old = get_post_meta($post_id, $field['key'], true);

            if (isset($_POST[$field['key']])) {

                $new = $_POST[$field['key']];

                if (is_array($new)) {
                    $new = implode(',', $new);
                    update_post_meta($post_id, $field['key'], array($new));

                } else if (isset($new) && $new != $old) {

                    update_post_meta($post_id, $field['key'], $new);

                    if ($field['key'] == 'wpleads_email_address') {
                        $args = array('ID' => $post_id, 'post_title' => $new);
                        wp_update_post($args);
                    }

                } else if ('' == $new && $old) {

                    delete_post_meta($post_id, $field['key'], $old);

                }
            }
        }
    }

    /* Enqueue Admin Scripts */
    public static function enqueue_admin_scripts($hook) {
        global $post;

        $post_type = isset($post) ? get_post_type($post) : null;

        if ($post_type != 'wp-lead') {
            return;
        }

        $screen = get_current_screen();

        if ($screen->id == 'wp-lead') {

            wp_enqueue_script('wpleads-edit', WPL_URLPATH . 'assets/js/wpl.admin.edit.js', array('jquery'));
            wp_enqueue_script('tinysort', WPL_URLPATH . 'assets/js/jquery.tinysort.js', array('jquery'));
            wp_enqueue_script('tag-cloud', WPL_URLPATH . 'assets/js/jquery.tagcloud.js', array('jquery'));
            wp_localize_script('wpleads-edit', 'wp_lead_map', array('ajaxurl' => admin_url('admin-ajax.php'), 'wp_lead_map_nonce' => wp_create_nonce('wp-lead-map-nonce')));

            if (isset($_GET['small_lead_preview'])) {
                wp_enqueue_style('wpleads-popup-css', WPL_URLPATH . 'assets/css/wpl.popup.css');
                wp_enqueue_script('wpleads-popup-js', WPL_URLPATH . 'assets/js/wpl.popup.js', array('jquery'));
            }

            wp_enqueue_style('wpleads-admin-edit-css', WPL_URLPATH . 'assets/css/wpl.edit-lead.css');

            //Tool tip js
            wp_enqueue_script('jquery-qtip', WPL_URLPATH . 'assets/js/jquery-qtip/jquery.qtip.min.js');
            wp_enqueue_script('wpl-load-qtip', WPL_URLPATH . 'assets/js/jquery-qtip/load.qtip.js');
            wp_enqueue_style('qtip-css', WPL_URLPATH . 'assets/css/jquery.qtip.min.css'); //Tool tip css
            wp_enqueue_style('wpleads-admin-css', WPL_URLPATH . 'assets/css/wpl.admin.css');


        }

        if ($hook == 'post-new.php') {
            wp_enqueue_script('wpleads-create-new-lead', WPL_URLPATH . 'assets/js/wpl.add-new.js');
        }

        if ($hook == 'post.php') {
            if (isset($_GET['small_lead_preview'])) {
                wp_enqueue_style('wpleads-popup-css', WPL_URLPATH . 'assets/css/wpl.popup.css');
            }
            wp_enqueue_style('wpleads-admin-edit-css', WPL_URLPATH . 'assets/css/wpl.edit-lead.css');
        }

    }

    /* Print Admin Scripts */
    public static function print_admin_scripts() {
        global $post;

        $screen = get_current_screen();

        if ($screen->base != 'post' && $screen->post_type != 'wp-lead') {
            return;
        }

    }

    /**
     * Gets time difference between two date time strings
     */
    public static function get_time_diff($date1, $date2) {
        $time_diff = array();

        $diff = abs(strtotime($date2) - strtotime($date1));
        //echo $diff; //exit;
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

        $time_diff['years'] = $years;
        $time_diff['y-text'] = ($years > 1) ? __('Years', 'inbound-pro') : __('Year', 'inbound-pro');
        $time_diff['months'] = $months;
        $time_diff['m-text'] = ($months > 1) ? __('Months', 'inbound-pro') : __('Month', 'inbound-pro');
        $time_diff['days'] = $days;
        $time_diff['d-text'] = ($days > 1) ? __('Days', 'inbound-pro') : __('Day', 'inbound-pro');
        $time_diff['hours'] = $hours;
        $time_diff['h-text'] = ($hours > 1) ? __('Hours', 'inbound-pro') : __('Hour', 'inbound-pro');
        $time_diff['minutes'] = $minutes;
        $time_diff['mm-text'] = ($minutes > 1) ? __('Minutes', 'inbound-pro') : __('Minute', 'inbound-pro');
        $time_diff['seconds'] = $seconds;
        $time_diff['sec-text'] = ($seconds > 1) ? __('Seconds', 'inbound-pro') : __('Second', 'inbound-pro');

        return $time_diff;
    }

    /**
     *    Get details from full contact
     */
    public static function get_full_contact_details() {
        global $post;

        self::$full_contact = false;
        $email = self::$mapped_fields['wpleads_email_address']['value'];
        $api_key = Leads_Settings::get_setting('wpl-main-extra-lead-data', "");

        if (!$api_key) {
            return;
        }

        $social_data = get_post_meta($post->ID, 'social_data', true);
        $person_obj = $social_data;

        /* check for social data */
        if (!$social_data) {

            $args = array('sslverify' => false);

            $api_call = "https://api.fullcontact.com/v2/person.json?email=" . urlencode($email) . "&apiKey=$api_key";

            $response = wp_remote_get($api_call, $args);

            /* error. bail. */
            if (is_wp_error($response)) {
                return;
            }

            $status_code = $response['response']['code']; /* Check for API limit */

            if ($status_code === 200) {
                // if api still good. parse return values
                $person_obj = json_decode($response['body'], true);
                $image = (isset($person_obj['photos'][0]['url'])) ? $person_obj['photos'][0]['url'] : "";
                update_post_meta($post->ID, 'lead_main_image', $image);
                update_post_meta($post->ID, 'social_data', $person_obj);

            } elseif ($status_code === 404) {
                $person_obj = array(); // return empty on failure
            } else {
                $person_obj = array(); // return empty on failure
            }

        }


        self::$full_contact = $person_obj;
    }


    /**
     *    Setups main metabox tab navigation
     */
    public static function setup_tabs() {

        $tabs['wpleads_lead_tab_main'] =  array(
            'id' => 'wpleads_lead_tab_main',
            'label' => __('Profile', 'inbound-pro')
        );

        $tabs['wpleads_lead_tab_activity'] =  array(
            'id' => 'wpleads_lead_tab_activity',
            'label' => __('Activity', 'inbound-pro')
        );

        $tabs['wpleads_lead_tab_conversions'] =  array(
            'id' => 'wpleads_lead_tab_conversions',
            'label' => __('Conversion Paths', 'inbound-pro')
        );

        /*
         $tabs['wpleads_lead_tab_raw_form_data'] =  array(
            'id' => 'wpleads_lead_tab_raw_form_data',
            'label' => __('WordPress Meta Fields', 'inbound-pro')
        );*/

        self::$tabs = apply_filters('wpl_lead_tabs', $tabs);

        /* get open tab */
        self::$active_tab = 'wpleads_lead_tab_main';
        if (isset($_REQUEST['open-tab'])) {
            self::$active_tab = sanitize_text_field($_REQUEST['open-tab']);
        }

        /* Set hidden input for active tab */
        echo "<input type='hidden' name='open-tab' id='id-open-tab' value='" . self::$active_tab . "'>";

        /* Print JS controls */
        self::navigation_js($tabs);
    }


    /**
     *    Generates JS for Tab Switching
     */
    public static function navigation_js($tabs) {

        if (isset($_GET['tab'])) {
            $default_id = $_GET['tab'];
        } else {
            $default_id = 'main';
        }

        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function () {
                jQuery('.wpl-nav-tab').live('click', function () {

                    var this_id = this.id.replace('tabs-', '');
                    jQuery('.lead-profile-section').css('display', 'none');
                    jQuery('#' + this_id).css('display', 'block');
                    jQuery('.wpl-nav-tab').removeClass('nav-tab-special-active');
                    jQuery('.wpl-nav-tab').addClass('nav-tab-special-inactive');
                    jQuery('#tabs-' + this_id).addClass('nav-tab-special-active');
                    jQuery('#id-open-tab').val(this_id);

                });

                <?php
                if ( $default_id == 'main' ) {
                ?>
                jQuery('.lead-profile-section').hide();
                jQuery('#wpleads_lead_tab_main').show();
                <?php
                }
                ?>
            });
        </script>
        <?php
    }

    /**
     *    Gets mapped field data for this lead provide and sets it into static variable
     */
    public static function get_mapped_fields() {
        global $post;

        $fields = array();

        $mapped_fields = Leads_Field_Map::get_lead_fields();
        $mapped_fields = Leads_Field_Map::prioritize_lead_fields($mapped_fields);

        foreach ($mapped_fields as $key => $field) {

            if (isset($field['enable']) && $field['enable'] == 'off') {
                continue;
            }

            $fields[$field['key']] = $field;

            /* Get related meta value if exists */
            $fields[$field['key']]['value'] = get_post_meta($post->ID, $mapped_fields[$key]['key'], true);

            /* Get default mapped value if meta value does not exists */
            if (!$fields[$field['key']]['value'] && isset($mapped_fields[$key]['default'])) {
                $fields[$field['key']]['value'] = $mapped_fields[$key]['default'];
            } elseif (!isset($fields[$field['key']]['value'])) {
                $fields[$field['key']]['value'] = "";
            }
        }

        self::$mapped_fields = $fields;
    }

    /**
     * Display Nav Tabs
     */
    public static function display_tabs() {
        ?>
        <h2 id="lead-tabs" class="nav-tab-wrapper">
            <?php
            foreach (self::$tabs as $key => $array) {
                ?>
                <a id='tabs-<?php echo $array['id']; ?>'
                   class="wpl-nav-tab nav-tab nav-tab-special<?php echo self::$active_tab == $array['id'] ? '-active' : '-inactive'; ?>"><?php echo $array['label']; ?></a>
                <?php
            }
            ?>
        </h2>

        <?php

    }

    /**
     *    Display Quick Stats Metabox
     */
    public static function display_quick_stats() {
        global $post;

        ?>
        <div>
            <div class="inside" style='margin-left:-8px;text-align:center;'>
                <div id="quick-stats-box">

                    <?php do_action('wpleads_before_quickstats', $post); ?>
                    <div class="leads_stat_box">
                        <div class="leads_stat_box_heading">
                            <div class="label_1"><?php _e('Action Breakdown' , 'inbound-pro'); ?>   </div>
                            <div class="label_2"><?php _e('Count' , 'inbound-pro'); ?></div>
                            <div class="clearfix"></div>
                        </div>
                        <?php do_action('wpleads_display_quick_stat', $post); ?> <!-- Display's the data-->
                    </div>
                    <div id="time-since-last-visit"></div>
                    <div id="lead-score"></div>
                    <!-- Custom Before Quick stats and After Hook here for custom fields shown -->
                </div>
                <?php do_action('wpleads_after_quickstats'); // Custom Action for additional data after quick stats ?>
            </div>
        </div>
        <?php
    }

    /**
     * Adds Page Views to Quick Stat Box
     */
    public static function display_quick_stat_page_views($post) {
        self::$page_views = Inbound_Events::get_page_views($post->ID);

        ?>
        <div class="quick-stat-label">
            <div class="label_1"><?php _e('Page Views', 'inbound-pro') ?>:</div>
            <div class="label_2">

                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&class=Inbound_Visitor_Impressions_Report&range=10000&lead_id='.$post->ID.'&title='.__('Page Views','inbound-pro') .'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox' title="<?php echo  sprintf(__('past %s days','inbound-pro') , 10000 ); ?>">
                        <?php  echo count(self::$page_views); ?>
                    </a>
                    <?php
                } else {
                    echo count(self::$page_views);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }


    /**
     * Adds Inbound Form Submissions to Quick Stat Box
     */
    public static function display_quick_stat_form_submissions($post) {

        ?>

        <div class="quick-stat-label">
            <div class="label_1"><?php echo Inbound_Events::get_event_label('inbound_form_submission'); ?>:</div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_form_submission&range=10000&title='. urlencode(Inbound_Events::get_event_label('inbound_form_submission')).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo count(self::$form_submissions); ?>
                    </a>
                    <?php
                } else {
                    echo count(self::$form_submissions);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php

    }

    /**
     * Adds Lead Searches to Quick Stat Box
     */
    public static function display_quick_stat_lead_searches($post) {
            $search_count = Inbound_Events::get_total_activity($post->ID, 'search_made');
        ?>

        <div class="quick-stat-label">
            <div class="label_1"><?php echo Inbound_Events::get_event_label('search_made'); ?>:</div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Search_And_Comment_Report&event_name=search_made&range=10000&title='. urlencode(Inbound_Events::get_event_label('search_made')).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo $search_count; ?>
                    </a>
                    <?php
                } else {
                    echo $search_count;
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }

    /**
     * Adds Lead Comments to Quick Stat Box
     */
    public static function display_quick_stat_lead_comments($post) {
        $comment_count = Inbound_Events::get_total_activity($post->ID, 'comment_made');
        ?>

        <div class="quick-stat-label">
            <div class="label_1"><?php echo Inbound_Events::get_event_label('comment_made'); ?>:</div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Search_And_Comment_Report&event_name=comment_made&range=10000&title='. urlencode(Inbound_Events::get_event_label('comment_made')).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo $comment_count; ?>
                    </a>
                    <?php
                } else {
                    echo $comment_count;
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }

    /**
     * Adds Latest Activity to Quick Stat Box
     */
    public static function display_quick_stat_last_activity($post) {

        /* skip stat if none available */
        if (!self::$custom_events) {
            return;
        }

        /* get time of last activity */
        $datetime = Inbound_Events::get_last_activity($post->ID, 'any');

        /* skip if no recorded activity */
        if (!$datetime) {
            return;
        }

        $time = current_time('timestamp', 0); // Current wordpress time from settings
        $wordpress_date_time = date("Y-m-d G:i:s T", $time);
        $today = new DateTime($wordpress_date_time);
        $today = $today->format('Y-m-d G:i:s T');
        $date_obj = self::get_time_diff($datetime, $today);
        $wordpress_timezone = get_option('gmt_offset');
        $years = $date_obj['years'];
        $months = $date_obj['months'];
        $days = $date_obj['days'];
        $hours = $date_obj['hours'];
        $minutes = $date_obj['minutes'];
        $year_text = $date_obj['y-text'];
        $month_text = $date_obj['m-text'];
        $day_text = $date_obj['d-text'];
        $hours_text = $date_obj['h-text'];
        $minute_text = $date_obj['mm-text'];

        ?>
        <div id="last_touch_point"><?php _e('Time Since Last Activity', 'inbound-pro'); ?>

            <span id="touch-point">

                <?php

                echo "<span class='touchpoint-year'><span class='touchpoint-value'>" . $years . "</span> " . $year_text . " </span><span class='touchpoint-month'><span class='touchpoint-value'>" . $months . "</span> " . $month_text . " </span><span class='touchpoint-day'><span class='touchpoint-value'>" . $days . "</span> " . $day_text . " </span><span class='touchpoint-hour'><span class='touchpoint-value'>" . $hours . "</span> " . $hours_text . " </span><span class='touchpoint-minute'><span class='touchpoint-value'>" . $minutes . "</span> " . $minute_text . "</span>";
                ?>
            </span>
        </div>
        <?php
    }

    public static function display_lead_profile() {
        self::display_profile_image();
        echo '<div id="leads-right-col">';

        do_action('wpleads_before_main_fields');

        self::render_settings();

        echo "<table id='full-contact-table'>";
        self::display_full_contact_details(self::$full_contact, 'work'); // Display extra data work history
        self::display_full_contact_details(self::$full_contact, 'social'); // Display extra social

        /* Display more Full Contact data */
        self::display_full_contact_details(self::$full_contact, 'website');
        self::display_full_contact_details(self::$full_contact, 'demographics');
        self::display_full_contact_details(self::$full_contact, 'topics');
        echo "</table>";
        /* Display tag cloud */
        self::display_tag_cloud();

        /* Hook for displaying content after mapped fields */
        echo "<div id='wpl-after-main-fields'>";
        do_action('wpleads_after_main_fields'); // Custom Action for additional info above Lead list
        echo "</div>";

        echo '</div>';

    }

    /**
     *    Displayed mapped field data
     */
    public static function display_profile_image() {
        global $post;

        $extra_image = get_post_meta($post->ID, 'lead_main_image', true);
        $size = 150;
        $size_small = 36;
        $url = site_url();
        $default = WPL_URLPATH . '/assets/images/gravatar_default_150.jpg';

        $gravatar = "//www.gravatar.com/avatar/" . md5(strtolower(trim(self::$mapped_fields['wpleads_email_address']['value']))) . "?d=" . urlencode($default) . "&s=" . $size;
        $gravatar2 = "//www.gravatar.com/avatar/" . md5(strtolower(trim(self::$mapped_fields['wpleads_email_address']['value']))) . "?d=" . urlencode($default) . "&s=" . $size_small;

        if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
            $gravatar = $default;
            $gravatar2 = WPL_URLPATH . '/assets/images/gravatar_default_32-2x.png';
        }
        // If social picture exists use it
        if (preg_match("/gravatar_default_/", $gravatar) && $extra_image != "") {
            $gravatar = $extra_image;
            $gravatar2 = $extra_image;
        }
        ?>
        <div id="left-sidebar">
            <div id="lead_image">
                <div id="lead_image_container">

                    <?php
                    if (preg_match("/gravatar_default_/", $gravatar) && $extra_image != "") {
                        $gravatar = $extra_image;
                    }
                    echo '<img src="' . $gravatar . '" id="lead-main-image" title="' . self::$mapped_fields['wpleads_first_name']['value'] . ' ' . self::$mapped_fields['wpleads_last_name']['value'] . '"></a>';
                    ?>
                </div>
                <?php
                /* Display WP USer edit link */
                $wp_user_id = get_post_meta($post->ID, 'wpleads_wordpress_user_id', true);
                if (isset($wp_user_id) && ($wp_user_id != 1)) {
                    $edit_user_link = get_edit_user_link($wp_user_id);
                    //echo '<a  target="_blank" href="'.$edit_user_link.'">'. __( 'Edit User Profile' , 'inbound-pro' ) .'</a>';
                }
                ?>
            </div>

            <?php
            $user = get_user_by( 'email', self::$lead_metadata['wpleads_email_address'] );
            if (isset($user->ID)) {
                ?>
                <div id='show-edit-user'>
                    <a  class='button button-secondary' href="<?php echo get_edit_user_link($user->ID); ?>"><?php _e('Edit User Profile', 'inbound-pro'); ?></a>
                </div>

                <?php
            }
            ?>

            <div id='toggle-lead-fields'>
                <a class='button button-primary' id='show-hidden-fields'>
                    <?php _e('Show Empty Fields', 'inbound-pro'); ?>
                </a>
            </div>
        </div>
        <style type="text/css">.icon32-posts-wp-lead {
                background-image: url("<?php echo $gravatar2;?>") !important;
            }</style>
        <?php
    }

    /**
     *    Loads Activity UI
     */
    public static function display_lead_activity() {

        /* do not render legacy activity tab if Inbound Analytics is on and user is subscriber */
        if (class_exists('Inbound_Analytics')) {
            if (INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9 ) {
                return;
            }
        }

        echo '<div id="activity-data-display">';
        self::activity_navigation();
        self::activity_form_submissions();
        self::activity_comments();
        self::activity_searches();
        self::activity_pageviews();

        do_action('wpleads_after_activity_log');
        echo '</div>';
    }

    /**
     *    Displays conversion funnel data
     */
    public static function display_lead_conversion_paths() {
        global $post, $wpdb;

        /* do not render legacy activity tab if Inbound Analytics is on and user is subscriber */
        if (class_exists('Inbound_Analytics')) {
            if (INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9 ) {
                return;
            }
        }

        self::get_conversions( $post->ID );

        if (!self::$conversions) {
            return;
        }

        foreach (self::$conversions as $key => $value) {
            /* get funnel */
            $value['funnel'] = json_decode($value['funnel'],true);
            if (!$value['funnel']) {
                continue;
            }

            $date = date_create($value['datetime']);


            /* now add action event */
            switch($value['event_name']) {
                case 'inbound_cta_click':
                    $event_label = __('Call to Action Click','inbound-pro');
                    $event_id = ($value['cta_id']) ? $value['cta_id'] : __('undefined', 'inbound-pro' );
                    $event_source_title = ($value['form_id']) ? get_the_title($value['form_id']) : __('undefined', 'inbound-pro' );
                    break;
                default:
                    $event_label = __('Form Submission','inbound-pro');
                    $event_id = ($value['form_id']) ? $value['form_id'] : __('undefined', 'inbound-pro' );
                    $event_source_title = ($value['form_id']) ? get_the_title($value['form_id']) : __('undefined', 'inbound-pro' );
                    break;
            }

            /* start funnel */
            ?>
            <div id="conversion-tracking" class="wpleads-conversion-tracking-table">
                <div class="conversion-tracking-header">
                    <div class="path-left">
                        <h2><?php echo $event_label; ?> - <span class="shown_date"><?php echo date_format($date, 'F jS, Y \a\t g:i:s a');?></span></h2>
                    </div>
                    <div class="path-right">
                        <span class="toggle-conversion-list">-</span>
                    </div>
                </div>

                <div class="session-item-holder">

                    <?php
                    /* show source */
                    ?>
                    <div class="lp-page-view-item ">
                        <div class="path-left">
                         <span class="marker">
                             <i class="fa fa-map-marker" aria-hidden="true"></i>
                         </span>
                            <?php echo __( 'Source' , 'inbound-pro');  ?>
                        </div>
                        <div class="path-right">
                            <?php echo $value['source'];  ?>
                        </div>
                    </div>
                    <?php

                    $count = 1;
                    foreach($value['funnel'] as $page_id) {

                        if (!$page_id) {
                            continue;
                        }



                        if (strpos($page_id, 'cat_') !== false) {
                            $cat_id = str_replace("cat_", "", $page_id);
                            $page_title = get_cat_name($cat_id) . " Category Page";
                            $tag_names = '';
                            $page_permalink = get_category_link($cat_id);

                        } elseif (strpos($page_id, 'tag_') !== false) {
                            $tag_id = str_replace("tag_", "", $page_id);
                            $tag = get_tag($tag_id);
                            $page_title = $tag->name . " - Tag Page";
                            $tag_names = '';
                            $page_permalink = get_tag_link($tag_id);

                        } else {
                            $page_title = get_the_title($page_id);
                            $page_title = ($page_id != 0) ? $page_title : 'N/A';
                            $page_permalink = get_permalink($page_id);
                        }

                        $page_title_short = strlen($page_title) > 65 ? substr($page_title, 0, 65) . "..." : $page_title;
                        ?>
                        <div class="lp-page-view-item ">
                            <div class="path-left">
                                <span class="marker">
                                    <?php echo $count; ?>
                                </span>
                                <a href='<?php echo $page_permalink; ?>' title='<?php echo $page_title; ?>' target='_blank'><?php echo $page_title_short; ?></a>
                            </div>
                            <div class="path-right">
                                <span class="time-on-page">
                                </span>
                            </div>
                        </div>
                        <?php
                        $count++;
                    }

                    ?>
                    <div class="lp-page-view-item ">
                        <div class="path-left">
                         <span class="marker">
                                <i class="fa fa-crosshairs" aria-hidden="true"></i>
                         </span>
                            <?php echo $event_label; ?> (<?php echo $value['event_name'];?>)
                        </div>
                        <div class="path-right">
                            <?php echo $event_source_title; ?>
                        </div>
                    </div>
                    <?php
                    ?>
                </div>
            </div>
            <?php
        }

    }

    /**
     *    Displays main lead content containers
     */
    public static function display_sources() {
        global $post;


        /* Get Referrals */
        $sources = Inbound_Events::get_lead_sources($post->ID);

        if (count($sources)>0) {
            foreach ($sources as $datetime => $source) {
                $date = date_create($datetime);

                /* skip internal sources */
                if (strstr($source,site_url()) || !$source) {
                    //continue;
                }
                ?>
                <div class="wpl-raw-data-tr">
                    <span class="wpl-raw-data-td-value">
                        <?php
                            $src = ($source === "Direct Traffic" || !$source ) ? __("Direct Traffic",'inbound-pro') : $source;
                            echo $src . ' on ' . date_format($date, 'F jS, Y \a\t g:ia (l)');
                        ?>
                    </span>
                </div>
                <?php
            }
        } else {
            echo "<h2>" .__('No Referral Data Detected.' , 'inbound-pro' ) . "</h2>";
        }
    }

    /**
     * Display raw data logs
     */
    public static function display_raw_logs() {
        global $post;
        ?>
        <div id="raw-data-display">

        </div>
        <?php
    }


    /**
     *    Gets data from full contact social object
     */
    public static function display_full_contact_details($values, $type) {

        $person_obj = $values;

        //print_r($person_obj);
        $confidence_level = (isset($person_obj['likelihood'])) ? $person_obj['likelihood'] : "";

        $photos = (isset($person_obj['photos'])) ? $person_obj['photos'] : "No Photos";
        $fullname = (isset($person_obj['contactInfo']['fullName'])) ? $person_obj['contactInfo']['fullName'] : "";
        $websites = (isset($person_obj['contactInfo']['websites'])) ? $person_obj['contactInfo']['websites'] : "N/A";
        $chats = (isset($person_obj['contactInfo']['chats'])) ? $person_obj['contactInfo']['chats'] : "No";
        $social_profiles = (isset($person_obj['socialProfiles'])) ? $person_obj['socialProfiles'] : "No Profiles Found";
        $organizations = (isset($person_obj['organizations'])) ? $person_obj['organizations'] : "No Organizations Found";
        $demographics = (isset($person_obj['demographics'])) ? $person_obj['demographics'] : "N/A";
        $interested_in = (isset($person_obj['digitalFootprint']['topics'])) ? $person_obj['digitalFootprint']['topics'] : "N/A";
        $image = (isset($person_obj['photos'][0]['url'])) ? $person_obj['photos'][0]['url'] : "/wp-content/plugins/leads/assets/images/gravatar_default_150.jpg";

        $klout_score = (isset($person_obj['digitalFootprint']['scores'][0]['value'])) ? $person_obj['digitalFootprint']['scores'][0]['value'] : "N/A";

        echo '<tr class="" style="display: table-row;">';

        /* Get All Photos associated with the person */
        if ($type === 'photo' && isset($photos) && is_array($photos)) {
            foreach ($photos as $photo) {
                echo $photo['url'] . " from " . $photo['typeName'] . "<br>";
            }
        } /* Get All Websites associated with the person */
        else if ($type === 'website' && isset($websites) && is_array($websites)) {
            ?>
            <td class="wpleads-th">
                <img title="<?php _e('Full Contact' , 'inbound-pro' ); ?>" src="<?php  echo WPL_URLPATH . 'assets/images/fullcontact.png'; ?>" width="16px">
                <label for=""><?php  _e('Websites:', 'inbound-pro'); ?></label></td>
            <td class="wpleads-td" id="">
                <?php
                foreach ($websites as $site) {
                    echo "<a href='" . $site['url'] . "' target='_blank'>" . $site['url'] . "</a><br>";
                }
                ?>
            </td>
            <?php
        } /* Get All Social Media Account associated with the person */
        else if ($type === 'social' && isset($social_profiles) && is_array($social_profiles)) {
            ?>
            <td class="wpleads-th">
                <img title="<?php _e('Full Contact' , 'inbound-pro' ); ?>" src="<?php  echo WPL_URLPATH . 'assets/images/fullcontact.png'; ?>" width="16px">
                <label for=""><?php  _e('Social Profiles:', 'inbound-pro'); ?></label>
            </td>
            <td class="wpleads-td" id="">
                <?php

                foreach ($social_profiles as $profiles) {
                    $network = (isset($profiles['typeName'])) ? $profiles['typeName'] : "";
                    $username = (isset($profiles['username'])) ? $profiles['username'] : "";
                    ($network == 'Twitter') ? $echo_val = "@" . $username : $echo_val = "";
                    echo "<a href='" . $profiles['url'] . "' target='_blank'>" . $profiles['typeName'] . "</a> " . $echo_val . "<br>";
                }
                ?>
            </td>
            <?php
        } /* Get All Work Organizations associated with the person */
        else if ($type === 'work' && isset($organizations) && is_array($organizations) && $organizations ) {
            ?>
            <td class="wpleads-th">
                <img title="<?php _e('Full Contact' , 'inbound-pro' ); ?>" src="<?php  echo WPL_URLPATH . 'assets/images/fullcontact.png'; ?>" width="16px">
                <label for=""><?php
                    _e('Work:', 'inbound-pro');
                    ?></label>
            </td>
            <td class="wpleads-td" id="">
                <?php

                foreach ($organizations as $org) {
                    $title = (isset($org['title'])) ? $org['title'] : "";
                    $org_name = (isset($org['name'])) ? $org['name'] : "";
                    (isset($org['name'])) ? $at_org = "<span class='primary-work-org'>" . $org['name'] . "</span>" : $at_org = ""; // get primary org
                    (isset($org['isPrimary']) && $org['isPrimary'] === true) ? $print = "<span id='primary-title'>" . $title . "</span> at " . $at_org : $print = "";
                    (isset($org['isPrimary']) && $org['isPrimary'] === true) ? $hideclass = "work-primary" : $hideclass = "work-secondary";
                    echo $print;
                    echo "<span class='lead-work-label " . $hideclass . "'>" . $title . " at " . $org_name . "</span><br>";
                }
                echo "<span id='show-work-history'>" . __('View past work', 'inbound-pro') . "</span></div>";
                ?>
            </td>
            <?php
        } /* Get All demo graphic info associated with the person */
        else if ($type === 'demographics' && isset($demographics) && is_array($demographics)) {
            ?>
            <td class="wpleads-th">
                <img id="full-contact-icon" title="<?php _e('Full Contact' , 'inbound-pro' ); ?>" src="<?php  echo WPL_URLPATH . '/assets/images/fullcontact.png'; ?>" width="16px">
                <label for=""><?php  _e('Demographics', 'inbound-pro'); ?></label>
            </td>
            <td class="wpleads-td" id="">
                <?php

                $location = (isset($demographics['locationGeneral'])) ? $demographics['locationGeneral'] : "";
                $age = (isset($demographics['age'])) ? $demographics['age'] : "";
                $ageRange = (isset($demographics['ageRange'])) ? $demographics['ageRange'] : "";
                $gender = (isset($demographics['gender'])) ? $demographics['gender'] : "";
                echo $gender . " in " . $location;
                ?>
            </td>
            <?php
        } /*  Get All Topics associated with the person */
        elseif ($type === 'topics' && isset($interested_in) && is_array($interested_in)) {
            ?>
            <td class="wpleads-th"><label for=""><?php  _e('Interests:', 'inbound-pro'); ?></label></td>
            <td class="wpleads-td" id="">
                <?php
                foreach ($interested_in as $topic) {
                    echo "<span class='lead-topic-tag'>" . $topic['value'] . "</span>";
                }
                ?>
            </td>
            <?php

        }

        echo '</tr>';
    }


    /**
     *    Displays tag cloud
     */
    public static function display_tag_cloud() {
        $tags = self::get_lead_tag_cloud(); // get content tags
        if (!$tags) {
            return;
        }
        ?>
        <table>
            <tr class="" style="display: table-row;">
                <td class="wpleads-th"><label for=""><?php  _e('Consumed Tags:', 'inbound-pro'); ?></label></td>
                <td class="wpleads-td" id="">
                    <?php

                    foreach ($tags as $key => $value) {
                        echo "<a href='#' rel='$value'>$key</a> ,";
                    }

                    ?>

            </tr>
        </table>
        <?php
    }


    /**
     *    Displays main lead content containers
     */
    public static function display_main() {
        global $post, $wpdb;

        self::setup_tabs();

        self::get_mapped_fields();

        self::get_full_contact_details();


        ?>
        <div class="lead-profile">
            <?php
            self::display_tabs();
            ?>
            <div class="lead-profile-section" id='wpleads_lead_tab_main'>

                <div id="wpleads_lead_tab_main_inner">
                    <?php

                    self::display_lead_profile();

                    ?>
                </div>
            </div>
            <div class="lead-profile-section" id='wpleads_lead_tab_activity'>
                <?php

                self::display_lead_activity();

                ?>
            </div>
            <div class="lead-profile-section" id='wpleads_lead_tab_conversions'>
                <?php

                self::display_lead_conversion_paths();

                ?>
            </div>
            <div class="lead-profile-section" id="wpleads_lead_tab_raw_form_data">
                <?php
                //self::display_raw_logs();

                ?>
            </div>
        </div>


        <?php
        do_action('wpl_print_lead_tab_sections');

    }

    /**
     *    Gets number of form submission events
     */
    public static function get_form_submissions_count() {
        global $post;

        $form_submissions = count(self::$form_submissions);

        return $form_submissions;
    }

    /**
     *    Gets number of onsite search events and loads the $searches variable
     */
    public static function get_search_count() {
        global $post;

        self::$searches = self::retrieve_lead_event_data($post->ID, 'search_made');

        $search_count = Inbound_Events::get_total_activity($post->ID, 'search_made');

        return $search_count;
    }

    /**
     *    Gets the number of comment events and sets up the comment data array
     */
    public static function get_comment_count() {
        global $post;

        self::$comments = self::retrieve_lead_event_data($post->ID, 'comment_made');

        $comment_count = Inbound_Events::get_total_activity($post->ID, 'comment_made');

        return $comment_count;
    }

    /**
     *    Gets number of tracked link clicks
     */
    public static function get_custom_events_count() {
        global $post;

        if (isset(self::$custom_events) && is_array(self::$custom_events)) {
            return count(self::$custom_events);
        } else {
            return 0;
        }
    }

    /**
     * Retrieves lead search or comment data on page load
     */
    public static function retrieve_lead_event_data( $lead_id = null, $event_name = '', $limit = 10000, $offset = 0 ){

        if( isset( $lead_id ) && !empty( $lead_id ) ){

            $args = array(
                'event_name' => sanitize_text_field( $event_name ),
                'lead_id' => intval( $lead_id ),
                'order_by' => 'datetime',
                'limit' => intval( $limit ),
                'offset' => intval( $offset )
            );

        }else{
            return;
        }

        foreach( $args as $value ){
            if( !isset( $value ) && empty( $value ) ){
                return;
            }
        }

        $event_data = Inbound_Events::get_events( $args );

        return $event_data;
    }

    /**
     *    Setup Activity Navigation
     */
    public static function activity_navigation() {
        global $post;

        $nav_items = array(
            array(
                'id' => 'lead-form-submissions',
                'label' => __('Form Submissions', 'inbound-pro'),
                'count' => self::get_form_submissions_count()
            ),
            array(
                'id' => 'lead-page-views',
                'label' => __('Page Views', 'inbound-pro'),
                'count' => count(self::$page_views)
            ),
            array(
                'id' => 'lead-comments',
                'label' => __('Comments', 'inbound-pro'),
                'count' => self::get_comment_count()
            ),
            array(
                'id' => 'lead-searches',
                'label' => __('Searches', 'inbound-pro'),
                'count' => self::get_search_count()
            )
        );

        $nav_items = apply_filters('wpl_lead_activity_tabs', $nav_items); ?>

        <div class="nav-container">
            <nav>
                <ul id="lead-activity-toggles">
                    <li class="active"><a href="#all"
                                          class="lead-activity-show-all"><?php _e('All', 'inbound-pro'); ?></a></li>
                    <?php
                    // Print toggles
                    foreach ($nav_items as $key => $array) {
                        $count = (isset($array['count'])) ? $array['count'] : '0';
                        ?>
                        <li><a href='#<?php echo $array['id']; ?>'
                               class="lead-activity-toggle"><?php echo $array['label']; ?><span
                                    class="badge"><?php echo $count; ?></span></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </nav>
        </div>
        <ul class="event-order-list" data-change-sort='#all-lead-history'>
            Sort by:
            <li id="newest-event" class='lead-sort-active'><?php _e('Most Recent', 'inbound-pro'); ?></li>
            |
            <li id="oldest-event"><?php _e('Oldest', 'inbound-pro'); ?></li>
            <!-- <li id="highest">Highest Rated</li>
                    <li id="lowest">Lowest Rated</li> -->
        </ul>
        <div id="all-lead-history">
            <ol></ol>
        </div>
        <?php
    }

    /**
     *   Display form submission activity
     */
    public static function activity_form_submissions() {
        global $post;

        echo '<div id="lead-form-submissions" class="lead-activity">';
        echo '  <h2>' . __('Form Submissions', 'inbound-pro') . '</h2>';


        if (!isset(self::$form_submissions) || !is_array(self::$form_submissions)) {
            echo "  <span id='wpl-message-none'>" . __('No submissions found!', 'inbound-pro') . "</span>";
            echo '</div>';
            return;
        }


        $i = count(self::$form_submissions);
        foreach (self::$form_submissions as $key => $event) {

            if (!isset($event['id']) || !isset($event['datetime'])) {
                continue;
            }

            $form_id = ($event['form_id']) ? $event['form_id'] : __('undefined', 'inbound-pro' );
            $form_name = ($event['form_id']) ? get_the_title($event['form_id']) : __('undefined', 'inbound-pro' );
            $converted_page_id = $event['page_id'];
            $converted_page_permalink = get_permalink($converted_page_id);
            $converted_page_title = get_the_title($converted_page_id);
            $date_raw = new DateTime($event['datetime']);
            $datetime = $date_raw->format('F jS, Y \a\t g:ia (l)');


            // Display Data
            ?>
            <div class="lead-timeline recent-conversion-item form-conversion" data-date="<?php echo $event['datetime']; ?>">
                <a class="lead-timeline-img" href="#non">
                    <!--<i class="lead-timeline-img page-views"></i>-->
                </a>

                <div class="lead-timeline-body">
                    <div class="lead-event-text">
                        <p>
                            <span class="lead-item-num"><?php echo $i; ?></span>
                            <span class="conversion-date"><b><?php echo $datetime; ?></b></span>
                            <br>
                                <span class="lead-helper-text" style="padding-left:6px;">
                                    <?php
                                    _e(' Converted on page', 'inbound-pro' );
                                    ?>
                                </span>
                            <a href="<?php echo $converted_page_permalink; ?>" id="lead-session-<?php echo $i; ?>" rel="<?php echo $i; ?>" target="_blank"><?php echo $converted_page_title; ?></a>
                            <?php
                            _e('using the form ', 'inbound-pro' );
                            echo '<a href="' . admin_url('post.php?post=' . $event['form_id'] . '&action=edit') . '" target="_blank" title="' . ($event['form_id'] ? __('This is the form the user submitted their data through', 'inbound-pro' ) : __('Submission was processed through a 3rd party form tool or event data is incomplete.', 'inbound-pro')) . '">' . $form_name . '</a>';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
            $i--;

        }

        echo '</div>';
    }

    /**
     *    Displays activity comments
     */
    public static function activity_comments() {

        echo '<div id="lead-comments" class="lead-activity">';
        echo '<h2>Lead Comments</h2>';

        if (!self::$comments) {
            echo "<span id='wpl-message-none'>No comments found!</span>";
            echo '</div>';
            return;
        }

        $comment_count = count(self::$comments);

        $c_i = $comment_count;
        foreach (self::$comments as $comment) {

            $event_details = json_decode($comment['event_details'], true);

            /* if the json parsing failed, continue */
            if(empty($event_details)){
                continue;
            }

            /* if the comment author's name is given */
            if(!empty($event_details['comment_author'])){
                $author_details = sprintf(__( 'Commented as: %s', 'inbound-pro' ), $event_details['comment_author']);

                /* if the comment author gave a website, make the author name element a link to that site */
                if(!empty($event_details['comment_author_url'])){
                    $author_details = '- <a target="_blank" href="' . $event_details['comment_author_url'] . '">' . $author_details . '</a>';
                }else{
                    $author_details = '- <span class="comment-author-name">' . $author_details . '</span>';
                }
            }else{
                /* if the author name wasn't given, don't output anything */
                $author_details = '';
            }

            $comment_date_raw = new DateTime($comment['datetime']);
            $date_of_comment = $comment_date_raw->format('F jS, Y \a\t g:ia (l)');
            $comment_clean_date = $comment_date_raw->format('Y-m-d H:i:s');

            $commented_page_permalink = get_permalink($comment['page_id']);
            $commented_page_title = get_the_title($comment['page_id']);
            $comment_id = "#comment-" . $comment['comment_id'];

            // Display Data
            echo '<div class="lead-timeline recent-conversion-item lead-comment-conversion" data-date="' . $comment_clean_date . '">
                    <a class="lead-timeline-img" href="#non">
                        <img src="/wp-content/plugins/leads/assets/images/comment.png" alt="" width="50" height="50" />
                    </a>

                    <div class="lead-timeline-body">
                        <div class="lead-event-text lead-comment-div">
                            <p><span class="lead-item-num">' . $c_i . '.</span><span class="lead-helper-text">Comment on </span><a title="' . __('View and respond to the comment', 'inbound-pro') . '" href="' . $commented_page_permalink . $comment_id . '" id="lead-session-' . $c_i . '" rel="' . $c_i . '" target="_blank">' . $commented_page_title . '</a><span class="conversion-date">' . $date_of_comment . '</span> <!--<a rel="' . $c_i . '" href="#view-session-"' . $c_i . '">(view visit path)</a>--></p>
                                                            <p class="lead-comment">"' . apply_filters( 'the_content', $event_details['comment_content'] ) . '"' . $author_details . '</p>
                        </div>
                    </div>
                </div>';
            $c_i--;
        }


        echo '</div>';
    }

    /**
     *    Displays activity searches
     */
    public static function activity_searches() {
        echo '<div id="lead-searches" class="lead-activity">';
        echo '  <h2>' . __('Lead Searches', 'inbound-pro') . '</h2>';

        if (empty(self::$searches)) {
            echo "<span id='wpl-message-none'>" . __('No searches found!', 'inbound-pro') . "</span>";
            echo '</div>';
            return;
        }

        $index = 0;
        $search_array = array();
        foreach (self::$searches as $key => $value) {
            $search_query = json_decode($value['event_details'], true);

            /* if the json parsing failed, continue */
            if(empty($search_query)){
                continue;
            }

            $field_details = explode( '|value|', $search_query['search_data'] );

            /* if the current text isn't from the search field, continue*/
            if($field_details[0] !== 'search_text'){
                continue;
            }

            if(!empty($search_query['page_id']) && $search_query['page_id'] !== '0' && $search_query['page_id'] !== 's'){

                /* if the search page id was saved as a string instead of an id */
                if(is_string($search_query['page_id']) && ((int)$search_query['page_id']) === 0){

                    if($search_query['page_id'] === 'blog_home'){
                        $search_page = '<a href="' . get_home_url() . '">' . __('On the homepage', 'inbound-pro') . '</a>';

                    }else{
                        $post = get_page_by_title($search_query['page_id'], 'OBJECT');

                        if($post !== null){
                            $search_page = '<a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>';
                        }else{
                            $search_page = '<span>' . sprintf(__('On: %s', 'inbound-pro'), $search_query['page_id']) . '</span>';
                        }
                    }
                }else{
                    $post = get_post($search_query['page_id']);

                    if(!empty($post)){
                        $search_page = '<a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>';
                    }else{
                        $search_page = '<span>' . __('On a deleted post', 'inbound-pro') . '</span>';
                    }
                }
            }else{
                $search_page = '<span>' . __('On the search page', 'inbound-pro') . '</span>';

            }

            $search_date_raw = new DateTime($value['datetime']);
            $date_of_search = $search_date_raw->format('F jS, Y \a\t g:ia (l)');
            $search_clean_date = $search_date_raw->format('Y-m-d H:i:s');

            if(empty($field_details[1])){
                $field_details[1] = __('No search saved. The search was made, but it wasn\'t logged for some reason.', 'inbound-pro' );
            }

            $search_array[$index]['search_query'] = $field_details[1];
            $search_array[$index]['search_clean_date'] = $search_clean_date;
            $search_array[$index]['search_date'] = $date_of_search;
            $search_array[$index]['search_page'] = $search_page;

            $index++;
        }

        $search_count = count($search_array);
        $search_errors = count(self::$searches) - $search_count;
        $listed_date = array();
        $bump_up_time = 10000000;
        foreach($search_array as $key => $values){

            // if for some reason there's multiple searches with the same timestamp,
            // add a tiny bit more time to the next coming ones so the sorting doesn't get messed up
            if( isset( $listed_date[$values['search_clean_date']] ) ){
                $values['search_clean_date'] .= $bump_up_time;
                $bump_up_time++;
            }else{
                $listed_date[$values['search_clean_date']] = true;
            }

            // Display Data
            echo '<div class="lead-timeline recent-conversion-item lead-search-conversion" data-date="' . $values['search_clean_date'] . '">
                    <a class="lead-timeline-img" href="#non"></a>
                    <div class="lead-timeline-body">
                        <div class="lead-event-text lead-search-div">
                            <p>
                                <span class="lead-item-num">' . $search_count . '.</span>
                                <span class="lead-helper-text">' . __('Lead searched for: ', 'inbound-pro') . '
                                    <strong>"' . $values['search_query'] . '"</strong> on
                                </span>
                                <span class="conversion-date">' . $values['search_date'] . '.</span><br />
                                ' . $values['search_page'] .'
                            </p>
                        </div>
                    </div>
                </div>';
            $search_count--;
        }

        /* if there were any searches that were empty or not formatted correctly, add a message so the user knows what happened to them */
        if($search_errors > 0){
            echo '<div id="search-error-count">
                    <i class="fa fa-info-circle search-error-icon" aria-hidden="true"></i>
                    <p class="search-error-message">' . sprintf( __( 'There were %d searches that were empty or had an error.', 'inbound-pro'), $search_errors ) . '</p>
                  </div>';
        }

        echo '</div>';
    }

    /**
     *    Displays page view activity
     */
    public static function activity_pageviews() {
        global $post;

        echo '<div id="lead-page-views" class="lead-activity">';
        echo '  <h2>' . __('Page Views', 'inbound-pro') . '</h2>';


        if (!self::$page_views) {
            echo "<span id='wpl-message-none'>" . __('No Page View History Found', 'inbound-pro') . "</span>";
            echo '</div>';
            return;
        }

        foreach (self::$page_views as $key => $row) {


            $this_post_type = '';
            if (strpos($row['page_id'], 'cat_') !== false) {
                $cat_id = str_replace("cat_", "", $row['page_id']);
                $page_name = get_cat_name($cat_id) . ' '. __('Category Page' , 'inbound-pro');
                $page_permalink = get_category_link($cat_id);
            } elseif (strpos($row['page_id'], 'tag_') !== false) {
                $tag_id = str_replace("tag_", "", $row['page_id']);
                $tag = get_tag($tag_id);
                $page_name = $tag->name . " - " . __('Tag Page' , 'inbound-pro');
                $page_permalink = get_tag_link($tag_id);
            } else {
                $page_title = get_the_title($row['page_id']);
                $page_name = ($row['page_id'] != 0) ? $page_title : 'N/A';
                $page_permalink = get_permalink($row['page_id']);
                $this_post_type = get_post_type($row['page_id']);
            }

            $date_print = date_create($row['datetime']);

            // Display Data
            echo '<div class="lead-timeline recent-conversion-item page-view-item ' . $this_post_type . '" title="' . $page_permalink . '"  data-date="' . date_format($date_print, 'F jS, Y \a\t g:ia (l)') . '">
                    <a class="lead-timeline-img page-views" href="#non">

                    </a>

                    <div class="lead-timeline-body">
                        <div class="lead-event-text">
                            <p>
                            <span class="lead-item-num"></span>
                            <span class="lead-helper-text">
                                <b>' . __('Viewed page', 'inbound-pro' ) . ' :</b>
                                <span class="conversion-date"><b>' . date_format($date_print, 'F jS, Y \a\t g:ia (l)') . '</b></span>
                                <br>
                            </span>
                            <a href="' . $page_permalink . '" id="lead-session" rel="" target="_blank">' . $page_title . '</a>
                            </p>
                        </div>
                    </div>
                </div>';

        }

        echo '</div>';
    }





    /**
     *    Field sorting array filter
     */
    public static function piority_sort_filter($a, $b) {
        return $a['priority'] > $b['priority'] ? 1 : -1;
    }

    /**
     *    Loops through settings definitions and displays settings
     * @param ARRAY $fields
     */
    public static function render_settings() {
        global $inbound_settings;
        //uasort( self::$mapped_fields , array( __CLASS__ , 'piority_sort_filter' ) );

        ?>

        <table id='wpleads_main_container'>


        <?php
        $api_key = Leads_Settings::get_setting('wpl-main-extra-lead-data', "");

        if ($api_key === "" || empty($api_key)) {
            if (class_exists('Inbound_Pro_Plugin')) {
                $full_contact_setup_link = admin_url('edit.php?post_type=wp-lead&page=inbound-pro-leads');
            } else {
                $full_contact_setup_link = admin_url('edit.php?post_type=wp-lead&page=wpleads_global_settings');
            }
            echo "<div class='lead-notice'>". sprintf(__('Please %s enter your Full Contact API key %s for additional lead data.' , 'inbound-pro' ) , "<a href='" . $full_contact_setup_link  . "'>" , '</a>')." <a href='http://www.inboundnow.com/collecting-advanced-lead-intelligence-wordpress-free/' target='_blank'>". __('Read more' , 'inbound-pro') ."</a></div>";
        }

        foreach (self::$mapped_fields as $field) {

            $id = strtolower($field['key']);
            echo '<tr class="' . $id . '">
                <th class="wpleads-th" ><label for="' . $id . '">' . $field['label'] . ':</label></th>
                <td class="wpleads-td" id="wpleads-td-' . $id . '">';
            switch (true) {
                case strstr($field['type'], 'textarea'):
                    $parts = explode('-', $field['type']);

                    if (is_array($field['value'])) {
                        $field['value'] = implode( "\r\n" , $field['value'] );
                    }

                    (isset($parts[1])) ? $rows = $parts[1] : $rows = '10';

                    $is_json = self::is_json($field['value']);

                    if ($is_json ) {
                         echo "<textarea name='" . $id . "' id='" . $id . "' rows='" . $rows . "' style='' readonly>" . $field['value'] . "</textarea>";
                    } else {
                      echo '<textarea name="' . $id . '" id="' . $id . '" rows=' . $rows . '" style="" >' . $field['value'] . '</textarea>';
                   }

                    break;
                case strstr($field['type'], 'text'):
                    $parts = explode('-', $field['type']);
                    (isset($parts[1])) ? $size = $parts[1] : $size = 35;

                    $field['value'] = (!is_array($field['value'])) ? $field['value'] : implode(' ' , $field['value']);

                    $is_json = self::is_json($field['value']);

                    if ($is_json ) {
                        echo "<input type='text' name='" . $id . "' id='" . $id . "' value='" . $field['value'] . "' size='" . $size . "' readonly/>";
                    } else {
                        echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $field['value'] . '" size="' . $size . '" />';
                    }
                    break;
                case strstr($field['type'], 'links'):

                    if (!$field['value']) {
                        continue;
                    }

                    $parts = explode('-', $field['type']);
                    (isset($parts[1])) ? $channel = $parts[1] : $channel = 'related';
                    $links = explode(';', $field['value']);

                    $links = array_filter($links);

                    echo "<div style='position:relative;'><span class='add-new-link'>" . __('Add New Link') . " <span title='" . __('add link') . "' align='ABSMIDDLE' class='wpleads-add-link' 'id='{$id}-add-link'>+</span></div>";
                    echo "<div class='wpleads-links-container' id='{$id}-container'>";

                    $remove_icon = WPL_URLPATH . '/assets/images/remove.png';

                    if (count($links) > 0) {
                        foreach ($links as $key => $link) {
                            $icon = self::get_social_link_icon($link);
                            $icon = apply_filters('wpleads_links_icon', $icon);
                            echo '<span id="' . $id . '-' . $key . '"><img src="' . $remove_icon . '" class="wpleads_remove_link" id = "' . $key . '" title="Remove Link">';
                            echo '<a href="' . $link . '" target="_blank"><img src="' . $icon . '" align="ABSMIDDLE" class="wpleads_link_icon"><input type="hidden" name="' . $id . '[' . $key . ']" value="' . $link . '" size="70"    class="wpleads_link"    />' . $link . '</a> ';
                            echo "</span><br>";

                        }
                    } else {
                        echo '<input type="text" name="' . $id . '[]" value="" size="70" />';
                    }
                    echo '</div>';
                    break;
                // wysiwyg
                case strstr($field['type'], 'wysiwyg'):
                    wp_editor($field['value'], $id, $settings = array());
                    echo '<p class="description">' . $field['desc'] . '</p>';
                    break;
                /* media */
                case strstr($field['type'], 'media'):
                    //echo 1; exit;
                    echo '<label for="upload_image">';
                    echo '<input name="' . $id . '" id="' . $id . '" type="text" size="36" name="upload_image" value="' . $field['value'] . '" />';
                    echo '<input class="upload_image_button" id="uploader_' . $id . '" type="button" value="Upload Image" />';
                    echo '<p class="description">' . $field['desc'] . '</p>';
                    break;
                /* checkbox */
                case strstr($field['type'], 'checkbox'):

                    if (!isset($field['value']) || !$field['value'] ) {
                        $field['value'] = array();
                    } else if (!is_array($field['value'])) {
                        $field['value'] = explode( ',' , $field['value'] );
                    }

                    /* get available options from memory */
                    $field['options'] = (isset($inbound_settings['leads-custom-fields']['fields'][$id]['options']) ) ? $inbound_settings['leads-custom-fields']['fields'][$id]['options'] : array();
                    $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = $field['options'];

                    /* store current option if not available */
                    foreach ($field['value'] as $key=>$value) {
                        if (in_array( $value , $field['options'])) {
                            continue;
                        }

                        $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = array_merge($field['value'] , $field['options']);

                        $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = array_unique($inbound_settings['leads-custom-fields']['fields'][$id]['options']);

                        Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $inbound_settings );
                    }

                    $field['options'] = array_merge($inbound_settings['leads-custom-fields']['fields'][$id]['options'] , $field['options']);
                    $field['options'] = array_unique($field['options']);

                    foreach( $field['options'] as $key => $value) {
                        echo '<input type="checkbox" name="' . $id . '[]" id="' . $id . '" value="' . $value . '" '. ( in_array($value, $field['value']) ? ' checked="checked"' : '' ) . '/>';
                        echo ' ' . $value;
                        echo '<br>';
                    }

                    if (isset($field['desc'])) {
                        echo '<div class="wpl_tooltip tool_checkbox" title="' . $field['desc'] . '"></div>';
                    }

                    break;
                /* radio */
                case strstr($field['type'], 'radio'):

                   /* get available options from memory */
                    $field['options'] = (isset($inbound_settings['leads-custom-fields']['fields'][$id]['options']) ) ? $inbound_settings['leads-custom-fields']['fields'][$id]['options'] : array();
                    $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = $field['options'];

                    /* store current option if not available */
                    if (!in_array($field['value'], $field['options'])) {

                        $inbound_settings['leads-custom-fields']['fields'][$id]['options'][] = $field['value'];


                        $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = array_filter($inbound_settings['leads-custom-fields']['fields'][$id]['options']);

                        $inbound_settings['leads-custom-fields']['fields'][$id]['options'] = array_unique($inbound_settings['leads-custom-fields']['fields'][$id]['options']);
                        Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $inbound_settings );
                    }

                    $field['options'] = array_merge($inbound_settings['leads-custom-fields']['fields'][$id]['options'] , $field['options']);
                    $field['options'] = array_unique($field['options']);

                    foreach ($field['options'] as $key => $value) {
                        echo '<input type="radio" name="' . $id . '" id="' . $id . '" value="' . $value . '" ', $field['value'] == $value ? ' checked="checked"' : '', '/>';
                        echo '<label for="' . $value . '">&nbsp;&nbsp;' . $value . '</label> &nbsp;&nbsp;&nbsp;&nbsp;';
                    }

                    if (isset($field['desc'])) {
                        echo '<div class="wpl_tooltip" title="' . $field['desc'] . '"></div>';
                    }
                    break;
                // select
                case $field['type'] == 'dropdown':

                   echo '<select name="' . $id . '" id="' . $id . '" >';
                   foreach ($field['options'] as $value => $label) {
                        echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="' . $value . '">' . $label . '</option>';
                   }
                   echo '</select>';

                   if (isset($field['desc'])) {
                    echo '<div class="wpl_tooltip" title="' . $field['desc'] . '"></div>';
                   }
                   break;
                case $field['type'] == 'dropdown-country':
                    echo '<input type="hidden" id="hidden-country-value" value="' . $field['value'] . '">';
                    echo '<select name="' . $id . '" id="' . $id . '" class="wpleads-country-dropdown">';
                    ?>
                        <option value=""><?php _e('Country...', 'inbound-pro'); ?></option>
                        <option value="AF"><?php _e('Afghanistan', 'inbound-pro'); ?></option>
                        <option value="AL"><?php _e('Albania', 'inbound-pro'); ?></option>
                        <option value="DZ"><?php _e('Algeria', 'inbound-pro'); ?></option>
                        <option value="AS"><?php _e('American Samoa', 'inbound-pro'); ?></option>
                        <option value="AD"><?php _e('Andorra', 'inbound-pro'); ?></option>
                        <option value="AG"><?php _e('Angola', 'inbound-pro'); ?></option>
                        <option value="AI"><?php _e('Anguilla', 'inbound-pro'); ?></option>
                        <option value="AG"><?php _e('Antigua &amp; Barbuda', 'inbound-pro'); ?></option>
                        <option value="AR"><?php _e('Argentina', 'inbound-pro'); ?></option>
                        <option value="AA"><?php _e('Armenia', 'inbound-pro'); ?></option>
                        <option value="AW"><?php _e('Aruba', 'inbound-pro'); ?></option>
                        <option value="AU"><?php _e('Australia', 'inbound-pro'); ?></option>
                        <option value="AT"><?php _e('Austria', 'inbound-pro'); ?></option>
                        <option value="AZ"><?php _e('Azerbaijan', 'inbound-pro'); ?></option>
                        <option value="BS"><?php _e('Bahamas', 'inbound-pro'); ?></option>
                        <option value="BH"><?php _e('Bahrain', 'inbound-pro'); ?></option>
                        <option value="BD"><?php _e('Bangladesh', 'inbound-pro'); ?></option>
                        <option value="BB"><?php _e('Barbados', 'inbound-pro'); ?></option>
                        <option value="BY"><?php _e('Belarus', 'inbound-pro'); ?></option>
                        <option value="BE"><?php _e('Belgium', 'inbound-pro'); ?></option>
                        <option value="BZ"><?php _e('Belize', 'inbound-pro'); ?></option>
                        <option value="BJ"><?php _e('Benin', 'inbound-pro'); ?></option>
                        <option value="BM"><?php _e('Bermuda', 'inbound-pro'); ?></option>
                        <option value="BT"><?php _e('Bhutan', 'inbound-pro'); ?></option>
                        <option value="BO"><?php _e('Bolivia', 'inbound-pro'); ?></option>
                        <option value="BL"><?php _e('Bonaire', 'inbound-pro'); ?></option>
                        <option value="BA"><?php _e('Bosnia &amp; Herzegovina', 'inbound-pro'); ?></option>
                        <option value="BW"><?php _e('Botswana', 'inbound-pro'); ?></option>
                        <option value="BR"><?php _e('Brazil', 'inbound-pro'); ?></option>
                        <option value="BC"><?php _e('British Indian Ocean Ter', 'inbound-pro'); ?></option>
                        <option value="BN"><?php _e('Brunei', 'inbound-pro'); ?></option>
                        <option value="BG"><?php _e('Bulgaria', 'inbound-pro'); ?></option>
                        <option value="BF"><?php _e('Burkina Faso', 'inbound-pro'); ?></option>
                        <option value="BI"><?php _e('Burundi', 'inbound-pro'); ?></option>
                        <option value="KH"><?php _e('Cambodia', 'inbound-pro'); ?></option>
                        <option value="CM"><?php _e('Cameroon', 'inbound-pro'); ?></option>
                        <option value="CA"><?php _e('Canada', 'inbound-pro'); ?></option>
                        <option value="IC"><?php _e('Canary Islands', 'inbound-pro'); ?></option>
                        <option value="CV"><?php _e('Cape Verde', 'inbound-pro'); ?></option>
                        <option value="KY"><?php _e('Cayman Islands', 'inbound-pro'); ?></option>
                        <option value="CF"><?php _e('Central African Republic', 'inbound-pro'); ?></option>
                        <option value="TD"><?php _e('Chad', 'inbound-pro'); ?></option>
                        <option value="CD"><?php _e('Channel Islands', 'inbound-pro'); ?></option>
                        <option value="CL"><?php _e('Chile', 'inbound-pro'); ?></option>
                        <option value="CN"><?php _e('China', 'inbound-pro'); ?></option>
                        <option value="CI"><?php _e('Christmas Island', 'inbound-pro'); ?></option>
                        <option value="CS"><?php _e('Cocos Island', 'inbound-pro'); ?></option>
                        <option value="CO"><?php _e('Colombia', 'inbound-pro'); ?></option>
                        <option value="CC"><?php _e('Comoros', 'inbound-pro'); ?></option>
                        <option value="CG"><?php _e('Congo', 'inbound-pro'); ?></option>
                        <option value="CK"><?php _e('Cook Islands', 'inbound-pro'); ?></option>
                        <option value="CR"><?php _e('Costa Rica', 'inbound-pro'); ?></option>
                        <option value="CT"><?php _e('Cote D\'Ivoire', 'inbound-pro'); ?></option>
                        <option value="HR"><?php _e('Croatia', 'inbound-pro'); ?></option>
                        <option value="CU"><?php _e('Cuba', 'inbound-pro'); ?></option>
                        <option value="CB"><?php _e('Curacao', 'inbound-pro'); ?></option>
                        <option value="CY"><?php _e('Cyprus', 'inbound-pro'); ?></option>
                        <option value="CZ"><?php _e('Czech Republic', 'inbound-pro'); ?></option>
                        <option value="DK"><?php _e('Denmark', 'inbound-pro'); ?></option>
                        <option value="DJ"><?php _e('Djibouti', 'inbound-pro'); ?></option>
                        <option value="DM"><?php _e('Dominica', 'inbound-pro'); ?></option>
                        <option value="DO"><?php _e('Dominican Republic', 'inbound-pro'); ?></option>
                        <option value="TM"><?php _e('East Timor', 'inbound-pro'); ?></option>
                        <option value="EC"><?php _e('Ecuador', 'inbound-pro'); ?></option>
                        <option value="EG"><?php _e('Egypt', 'inbound-pro'); ?></option>
                        <option value="SV"><?php _e('El Salvador', 'inbound-pro'); ?></option>
                        <option value="GQ"><?php _e('Equatorial Guinea', 'inbound-pro'); ?></option>
                        <option value="ER"><?php _e('Eritrea', 'inbound-pro'); ?></option>
                        <option value="EE"><?php _e('Estonia', 'inbound-pro'); ?></option>
                        <option value="ET"><?php _e('Ethiopia', 'inbound-pro'); ?></option>
                        <option value="FA"><?php _e('Falkland Islands', 'inbound-pro'); ?></option>
                        <option value="FO"><?php _e('Faroe Islands', 'inbound-pro'); ?></option>
                        <option value="FJ"><?php _e('Fiji', 'inbound-pro'); ?></option>
                        <option value="FI"><?php _e('Finland', 'inbound-pro'); ?></option>
                        <option value="FR"><?php _e('France', 'inbound-pro'); ?></option>
                        <option value="GF"><?php _e('French Guiana', 'inbound-pro'); ?></option>
                        <option value="PF"><?php _e('French Polynesia', 'inbound-pro'); ?></option>
                        <option value="FS"><?php _e('French Southern Ter', 'inbound-pro'); ?></option>
                        <option value="GA"><?php _e('Gabon', 'inbound-pro'); ?></option>
                        <option value="GM"><?php _e('Gambia', 'inbound-pro'); ?></option>
                        <option value="GE"><?php _e('Georgia', 'inbound-pro'); ?></option>
                        <option value="DE"><?php _e('Germany', 'inbound-pro'); ?></option>
                        <option value="GH"><?php _e('Ghana', 'inbound-pro'); ?></option>
                        <option value="GI"><?php _e('Gibraltar', 'inbound-pro'); ?></option>
                        <option value="GB"><?php _e('Great Britain', 'inbound-pro'); ?></option>
                        <option value="GR"><?php _e('Greece', 'inbound-pro'); ?></option>
                        <option value="GL"><?php _e('Greenland', 'inbound-pro'); ?></option>
                        <option value="GD"><?php _e('Grenada', 'inbound-pro'); ?></option>
                        <option value="GP"><?php _e('Guadeloupe', 'inbound-pro'); ?></option>
                        <option value="GU"><?php _e('Guam', 'inbound-pro'); ?></option>
                        <option value="GT"><?php _e('Guatemala', 'inbound-pro'); ?></option>
                        <option value="GN"><?php _e('Guinea', 'inbound-pro'); ?></option>
                        <option value="GY"><?php _e('Guyana', 'inbound-pro'); ?></option>
                        <option value="HT"><?php _e('Haiti', 'inbound-pro'); ?></option>
                        <option value="HW"><?php _e('Hawaii', 'inbound-pro'); ?></option>
                        <option value="HN"><?php _e('Honduras', 'inbound-pro'); ?></option>
                        <option value="HK"><?php _e('Hong Kong', 'inbound-pro'); ?></option>
                        <option value="HU"><?php _e('Hungary', 'inbound-pro'); ?></option>
                        <option value="IS"><?php _e('Iceland', 'inbound-pro'); ?></option>
                        <option value="IN"><?php _e('India', 'inbound-pro'); ?></option>
                        <option value="ID"><?php _e('Indonesia', 'inbound-pro'); ?></option>
                        <option value="IA"><?php _e('Iran', 'inbound-pro'); ?></option>
                        <option value="IQ"><?php _e('Iraq', 'inbound-pro'); ?></option>
                        <option value="IR"><?php _e('Ireland', 'inbound-pro'); ?></option>
                        <option value="IM"><?php _e('Isle of Man', 'inbound-pro'); ?></option>
                        <option value="IL"><?php _e('Israel', 'inbound-pro'); ?></option>
                        <option value="IT"><?php _e('Italy', 'inbound-pro'); ?></option>
                        <option value="JM"><?php _e('Jamaica', 'inbound-pro'); ?></option>
                        <option value="JP"><?php _e('Japan', 'inbound-pro'); ?></option>
                        <option value="JO"><?php _e('Jordan', 'inbound-pro'); ?></option>
                        <option value="KZ"><?php _e('Kazakhstan', 'inbound-pro'); ?></option>
                        <option value="KE"><?php _e('Kenya', 'inbound-pro'); ?></option>
                        <option value="KI"><?php _e('Kiribati', 'inbound-pro'); ?></option>
                        <option value="NK"><?php _e('Korea North', 'inbound-pro'); ?></option>
                        <option value="KS"><?php _e('Korea South', 'inbound-pro'); ?></option>
                        <option value="KW"><?php _e('Kuwait', 'inbound-pro'); ?></option>
                        <option value="KG"><?php _e('Kyrgyzstan', 'inbound-pro'); ?></option>
                        <option value="LA"><?php _e('Laos', 'inbound-pro'); ?></option>
                        <option value="LV"><?php _e('Latvia', 'inbound-pro'); ?></option>
                        <option value="LB"><?php _e('Lebanon', 'inbound-pro'); ?></option>
                        <option value="LS"><?php _e('Lesotho', 'inbound-pro'); ?></option>
                        <option value="LR"><?php _e('Liberia', 'inbound-pro'); ?></option>
                        <option value="LY"><?php _e('Libya', 'inbound-pro'); ?></option>
                        <option value="LI"><?php _e('Liechtenstein', 'inbound-pro'); ?></option>
                        <option value="LT"><?php _e('Lithuania', 'inbound-pro'); ?></option>
                        <option value="LU"><?php _e('Luxembourg', 'inbound-pro'); ?></option>
                        <option value="MO"><?php _e('Macau', 'inbound-pro'); ?></option>
                        <option value="MK"><?php _e('Macedonia', 'inbound-pro'); ?></option>
                        <option value="MG"><?php _e('Madagascar', 'inbound-pro'); ?></option>
                        <option value="MY"><?php _e('Malaysia', 'inbound-pro'); ?></option>
                        <option value="MW"><?php _e('Malawi', 'inbound-pro'); ?></option>
                        <option value="MV"><?php _e('Maldives', 'inbound-pro'); ?></option>
                        <option value="ML"><?php _e('Mali', 'inbound-pro'); ?></option>
                        <option value="MT"><?php _e('Malta', 'inbound-pro'); ?></option>
                        <option value="MH"><?php _e('Marshall Islands', 'inbound-pro'); ?></option>
                        <option value="MQ"><?php _e('Martinique', 'inbound-pro'); ?></option>
                        <option value="MR"><?php _e('Mauritania', 'inbound-pro'); ?></option>
                        <option value="MU"><?php _e('Mauritius', 'inbound-pro'); ?></option>
                        <option value="ME"><?php _e('Mayotte', 'inbound-pro'); ?></option>
                        <option value="MX"><?php _e('Mexico', 'inbound-pro'); ?></option>
                        <option value="MI"><?php _e('Midway Islands', 'inbound-pro'); ?></option>
                        <option value="MD"><?php _e('Moldova', 'inbound-pro'); ?></option>
                        <option value="MC"><?php _e('Monaco', 'inbound-pro'); ?></option>
                        <option value="MN"><?php _e('Mongolia', 'inbound-pro'); ?></option>
                        <option value="MS"><?php _e('Montserrat', 'inbound-pro'); ?></option>
                        <option value="MA"><?php _e('Morocco', 'inbound-pro'); ?></option>
                        <option value="MZ"><?php _e('Mozambique', 'inbound-pro'); ?></option>
                        <option value="MM"><?php _e('Myanmar', 'inbound-pro'); ?></option>
                        <option value="NA"><?php _e('Nambia', 'inbound-pro'); ?></option>
                        <option value="NU"><?php _e('Nauru', 'inbound-pro'); ?></option>
                        <option value="NP"><?php _e('Nepal', 'inbound-pro'); ?></option>
                        <option value="AN"><?php _e('Netherland Antilles', 'inbound-pro'); ?></option>
                        <option value="NL"><?php _e('Netherlands (Holland, Europe)', 'inbound-pro'); ?></option>
                        <option value="NV"><?php _e('Nevis', 'inbound-pro'); ?></option>
                        <option value="NC"><?php _e('New Caledonia', 'inbound-pro'); ?></option>
                        <option value="NZ"><?php _e('New Zealand', 'inbound-pro'); ?></option>
                        <option value="NI"><?php _e('Nicaragua', 'inbound-pro'); ?></option>
                        <option value="NE"><?php _e('Niger', 'inbound-pro'); ?></option>
                        <option value="NG"><?php _e('Nigeria', 'inbound-pro'); ?></option>
                        <option value="NW"><?php _e('Niue', 'inbound-pro'); ?></option>
                        <option value="NF"><?php _e('Norfolk Island', 'inbound-pro'); ?></option>
                        <option value="NO"><?php _e('Norway', 'inbound-pro'); ?></option>
                        <option value="OM"><?php _e('Oman', 'inbound-pro'); ?></option>
                        <option value="PK"><?php _e('Pakistan', 'inbound-pro'); ?></option>
                        <option value="PW"><?php _e('Palau Island', 'inbound-pro'); ?></option>
                        <option value="PS"><?php _e('Palestine', 'inbound-pro'); ?></option>
                        <option value="PA"><?php _e('Panama', 'inbound-pro'); ?></option>
                        <option value="PG"><?php _e('Papua New Guinea', 'inbound-pro'); ?></option>
                        <option value="PY"><?php _e('Paraguay', 'inbound-pro'); ?></option>
                        <option value="PE"><?php _e('Peru', 'inbound-pro'); ?></option>
                        <option value="PH"><?php _e('Philippines', 'inbound-pro'); ?></option>
                        <option value="PO"><?php _e('Pitcairn Island', 'inbound-pro'); ?></option>
                        <option value="PL"><?php _e('Poland', 'inbound-pro'); ?></option>
                        <option value="PT"><?php _e('Portugal', 'inbound-pro'); ?></option>
                        <option value="PR"><?php _e('Puerto Rico', 'inbound-pro'); ?></option>
                        <option value="QA"><?php _e('Qatar', 'inbound-pro'); ?></option>
                        <option value="ME"><?php _e('Republic of Montenegro', 'inbound-pro'); ?></option>
                        <option value="RS"><?php _e('Republic of Serbia', 'inbound-pro'); ?></option>
                        <option value="RE"><?php _e('Reunion', 'inbound-pro'); ?></option>
                        <option value="RO"><?php _e('Romania', 'inbound-pro'); ?></option>
                        <option value="RU"><?php _e('Russia', 'inbound-pro'); ?></option>
                        <option value="RW"><?php _e('Rwanda', 'inbound-pro'); ?></option>
                        <option value="NT"><?php _e('St Barthelemy', 'inbound-pro'); ?></option>
                        <option value="EU"><?php _e('St Eustatius', 'inbound-pro'); ?></option>
                        <option value="HE"><?php _e('St Helena', 'inbound-pro'); ?></option>
                        <option value="KN"><?php _e('St Kitts-Nevis', 'inbound-pro'); ?></option>
                        <option value="LC"><?php _e('St Lucia', 'inbound-pro'); ?></option>
                        <option value="MB"><?php _e('St Maarten', 'inbound-pro'); ?></option>
                        <option value="PM"><?php _e('St Pierre &amp; Miquelon', 'inbound-pro'); ?></option>
                        <option value="VC"><?php _e('St Vincent &amp; Grenadines', 'inbound-pro'); ?></option>
                        <option value="SP"><?php _e('Saipan', 'inbound-pro'); ?></option>
                        <option value="SO"><?php _e('Samoa', 'inbound-pro'); ?></option>
                        <option value="AS"><?php _e('Samoa American', 'inbound-pro'); ?></option>
                        <option value="SM"><?php _e('San Marino', 'inbound-pro'); ?></option>
                        <option value="ST"><?php _e('Sao Tome &amp; Principe', 'inbound-pro'); ?></option>
                        <option value="SA"><?php _e('Saudi Arabia', 'inbound-pro'); ?></option>
                        <option value="SN"><?php _e('Senegal', 'inbound-pro'); ?></option>
                        <option value="SC"><?php _e('Seychelles', 'inbound-pro'); ?></option>
                        <option value="SL"><?php _e('Sierra Leone', 'inbound-pro'); ?></option>
                        <option value="SG"><?php _e('Singapore', 'inbound-pro'); ?></option>
                        <option value="SK"><?php _e('Slovakia', 'inbound-pro'); ?></option>
                        <option value="SI"><?php _e('Slovenia', 'inbound-pro'); ?></option>
                        <option value="SB"><?php _e('Solomon Islands', 'inbound-pro'); ?></option>
                        <option value="OI"><?php _e('Somalia', 'inbound-pro'); ?></option>
                        <option value="ZA"><?php _e('South Africa', 'inbound-pro'); ?></option>
                        <option value="ES"><?php _e('Spain', 'inbound-pro'); ?></option>
                        <option value="LK"><?php _e('Sri Lanka', 'inbound-pro'); ?></option>
                        <option value="SD"><?php _e('Sudan', 'inbound-pro'); ?></option>
                        <option value="SR"><?php _e('Suriname', 'inbound-pro'); ?></option>
                        <option value="SZ"><?php _e('Swaziland', 'inbound-pro'); ?></option>
                        <option value="SE"><?php _e('Sweden', 'inbound-pro'); ?></option>
                        <option value="CH"><?php _e('Switzerland', 'inbound-pro'); ?></option>
                        <option value="SY"><?php _e('Syria', 'inbound-pro'); ?></option>
                        <option value="TA"><?php _e('Tahiti', 'inbound-pro'); ?></option>
                        <option value="TW"><?php _e('Taiwan', 'inbound-pro'); ?></option>
                        <option value="TJ"><?php _e('Tajikistan', 'inbound-pro'); ?></option>
                        <option value="TZ"><?php _e('Tanzania', 'inbound-pro'); ?></option>
                        <option value="TH"><?php _e('Thailand', 'inbound-pro'); ?></option>
                        <option value="TG"><?php _e('Togo', 'inbound-pro'); ?></option>
                        <option value="TK"><?php _e('Tokelau', 'inbound-pro'); ?></option>
                        <option value="TO"><?php _e('Tonga', 'inbound-pro'); ?></option>
                        <option value="TT"><?php _e('Trinidad &amp; Tobago', 'inbound-pro'); ?></option>
                        <option value="TN"><?php _e('Tunisia', 'inbound-pro'); ?></option>
                        <option value="TR"><?php _e('Turkey', 'inbound-pro'); ?></option>
                        <option value="TU"><?php _e('Turkmenistan', 'inbound-pro'); ?></option>
                        <option value="TC"><?php _e('Turks &amp; Caicos Is', 'inbound-pro'); ?></option>
                        <option value="TV"><?php _e('Tuvalu', 'inbound-pro'); ?></option>
                        <option value="UG"><?php _e('Uganda', 'inbound-pro'); ?></option>
                        <option value="UA"><?php _e('Ukraine', 'inbound-pro'); ?></option>
                        <option value="AE"><?php _e('United Arab Emirates', 'inbound-pro'); ?></option>
                        <option value="GB"><?php _e('United Kingdom', 'inbound-pro'); ?></option>
                        <option value="US"><?php _e('United States of America', 'inbound-pro'); ?></option>
                        <option value="UY"><?php _e('Uruguay', 'inbound-pro'); ?></option>
                        <option value="UZ"><?php _e('Uzbekistan', 'inbound-pro'); ?></option>
                        <option value="VU"><?php _e('Vanuatu', 'inbound-pro'); ?></option>
                        <option value="VS"><?php _e('Vatican City State', 'inbound-pro'); ?></option>
                        <option value="VE"><?php _e('Venezuela', 'inbound-pro'); ?></option>
                        <option value="VN"><?php _e('Vietnam', 'inbound-pro'); ?></option>
                        <option value="VB"><?php _e('Virgin Islands (Brit)', 'inbound-pro'); ?></option>
                        <option value="VA"><?php _e('Virgin Islands (USA)', 'inbound-pro'); ?></option>
                        <option value="WK"><?php _e('Wake Island', 'inbound-pro'); ?></option>
                        <option value="WF"><?php _e('Wallis &amp; Futana Is', 'inbound-pro'); ?></option>
                        <option value="YE"><?php _e('Yemen', 'inbound-pro'); ?></option>
                        <option value="ZR"><?php _e('Zaire', 'inbound-pro'); ?></option>
                        <option value="ZM"><?php _e('Zambia', 'inbound-pro'); ?></option>
                        <option value="ZW"><?php _e('Zimbabwe', 'inbound-pro'); ?></option>
                        </select>
                        <?php
                    break;
            } //end switch
            echo '</td></tr>';
        }

        echo '</table>';
    }

    /**
     * Get conversion events from event table
     */
    public static function get_conversions( $lead_id ){
        global $wpdb;

        $event_whitelist = array('inbound_form_submission','inbound_cta_click','ninja_form_submission' , 'gravity_form_submission');

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND ( ';

        foreach($event_whitelist as $key => $event_name) {
            $query .= 'event_name = "'.$event_name.'" ';

            if (isset($event_whitelist[$key+1])) {
                $query .= 'OR ';
            }
        }

        $query .= ') ORDER BY `datetime` DESC';

        self::$conversions = $wpdb->get_results( $query , ARRAY_A );

        return self::$conversions;
    }

    /**
     *    fetches social icon given link url
     */
    public static function get_social_link_icon($link) {
        switch (true) {
            case strstr($link, 'facebook.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/facebook.png';
                break;
            case strstr($link, 'linkedin.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/linkedin.png';
                break;
            case strstr($link, 'twitter.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/twitter.png';
                break;
            case strstr($link, 'pinterest.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/pinterest.png';
                break;
            case strstr($link, 'plus.google.'):
                $icon = WPL_URLPATH . '/assets/images/icons/google.png';
                break;
            case strstr($link, 'youtube.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/youtube.png';
                break;
            case strstr($link, 'reddit.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/reddit.png';
                break;
            case strstr($link, 'badoo.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/badoo.png';
                break;
            case strstr($link, 'meetup.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/meetup.png';
                break;
            case strstr($link, 'livejournal.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/livejournal.png';
                break;
            case strstr($link, 'myspace.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/myspace.png';
                break;
            case strstr($link, 'deviantart.com'):
                $icon = WPL_URLPATH . '/assets/images/icons/deviantart.png';
                break;
            default:
                $icon = WPL_URLPATH . '/assets/images/icons/link.png';
                break;
        }

        return $icon;
    }


    /**
     *    Fetches tag cloud
     */
    public static function get_lead_tag_cloud() {

        global $post;


        if (self::$page_views && is_array(self::$page_views)) {
            // Collect all viewed page IDs
            foreach (self::$page_views as $key => $val) {
                $id = $key;
                $ids[] = $key;
            }

            // Get Tags from all pages viewed

            foreach ($ids as $key => $val) {
                //echo $val;
                $array = wp_get_post_tags($val, array('fields' => 'names'));
                if (!empty($array)) $tag_names[] = wp_get_post_tags($val, array('fields' => 'names'));


            }
            // Merge and count
            $final_tags = array();
            if (!empty($tag_names)) {
                foreach ($tag_names as $array) {

                    foreach ($array as $key => $value) {

                        $final_tags[] = $value;
                    }
                }
            }

            $return_tags = array_count_values($final_tags);
        } else {
            $return_tags = array(); // empty
        }

        return $return_tags; // return tag array
    }

    /**
     *    Array filter for sorting by datetime DESC
     */
    public static function datetime_sort_reverse($a, $b) {
        return strtotime($a['date']) > strtotime($b['date']) ? 1 : -1;
    }

    /**
     *    Array filter for sorting by datetime ASC
     */
    public static function datetime_sort($a, $b) {
        return strtotime($a['date']) < strtotime($b['date']) ? 1 : -1;
    }

    /**
     * Check if string is a json string
     * @param $string
     * @return bool
     */
    public static function  is_json($string) {

        $array = array('{','[');

        foreach ($array as $v) {
            if (substr($string, 0, 1) === $v) {
                return true;
            }
        }

        return false;
    }

}


new Inbound_Metaboxes_Leads;
