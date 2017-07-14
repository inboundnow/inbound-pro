<?php

/**
 * Class provides a data interface for storing and retrieving data from the Inbound Pro settings system or the GPL plugin settings system. They two are different at the moment.
 *
 * @package Leads
 * @subpackage Settings
 */


class Leads_Settings {

    /**
     * Initiate class
     */
    public function __construct() {
        self::load_hooks();
    }

    /**
     * load hooks and filters
     */
    public static function load_hooks() {
        add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_admin_scripts' ) , 10 , 1 );

        /*  Add settings to inbound pro  */
        add_filter('inbound_settings/extend', array( __CLASS__  , 'define_pro_settings' ) );

        /* redirect to Inbound pro settings */
        add_action('admin_init', array(__CLASS__, 'redirect_inbound_pro_settings') );

    }

    public static function enqueue_admin_scripts( $hook ) {
        global $post;

        $post_type = isset($post) ? get_post_type( $post ) : null;

        $screen = get_current_screen();

        // Global Settings Screen
        if ( $screen->id != 'wp-lead_page_wpleads_global_settings') {
            return;
        }

        wp_enqueue_script('wpleads-list-page', WPL_URLPATH.'assets/js/wpl.global-settings.js', array('jquery'));
        wp_enqueue_style('wpl_manage_lead_css', WPL_URLPATH. 'assets/css/wpl.admin-global-settings.css');

        /* load ToolTipster */
        wp_enqueue_style('tooltipster', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/css/tooltipster.css');
        wp_enqueue_style('tooltipster-theme', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/css/themes/tooltipster-noir.css');
        wp_enqueue_script('tooltipster', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/js/jquery.tooltipster.min.js');
    }


    /**
     *  Adds pro admin settings
     */
    public static function define_pro_settings( $settings ) {
        global $inbound_settings;
        $pages = self::leads_get_pages_array();

        $settings['inbound-pro-setup'][] = array(
            'group_name' => WPL_SLUG ,
            'keywords' => __('leads' , 'inbound-pro'),
            'fields' => array (
                array(
                    'id'  => 'header-leads',
                    'type'  => 'header',
                    'default'  => __('Leads Core Settings', 'inbound-pro' ),
                    'options' => null
                ),
                array(
                    'id'  => 'search-tracking',
                    'label' => __('Search Query Tracking' , 'inbound-pro' ),
                    'description' => __("WordPress Leads records searches made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'comment-tracking',
                    'label' => __('Comment Tracking' , 'inbound-pro' ),
                    'description' => __("WordPress Leads records comments made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'enable-dashboard',
                    'label' => __('Show Lead/List Data in Dashboard' , 'inbound-pro' ),
                    'description' => __("Turn this on to show graphical and list data about lead collection in WP Dashboard." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'disable-widgets',
                    'label' => __('Disable Default WordPress Dashboard Widgets' , 'inbound-pro' ),
                    'description' => __("This turns off some default widgets on the wordpress dashboard." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'extra-lead-data',
                    'label' => __('Full Contact API Key' , 'inbound-pro' ),
                    'description' => sprintf( __("Enter your Full contact API key. If you don't have one sign up with full contact here (https://www.fullcontact.com/) and then grab a free API key here: %s Full Contact Portal %s" , 'inbound-pro' ) , "<a href='https://portal.fullcontact.com/' target='_blank'>" , "</a>"),
                    'type'  => 'text',
                    'default'  => '',
                    'options' => null
                ),
                array(
                    'id'  => 'inbound_admin_notification_inboundnow_link',
                    'option_name'  => 'inbound_admin_notification_inboundnow_link',
                    'label' => __('Credit Inbound Now in admin notification emails.' , 'inbound-pro' ),
                    'description' => __("Admin notification emails are sent after a visitor fills out an inbound form." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'inbound_forms_enable_akismet',
                    'option_name'  => 'inbound_forms_enable_akismet',
                    'label' => __('Run form submissions through Akismet if akismet is enabled.' , 'inbound-pro' ),
                    'description' => __("Enabling this option will tell Leads to run form submissions through akismet to prevent spam submissions." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '0',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'list-double-optin-options',
                    'type'  => 'sub-header',
                    'default'  => __('Double Opt In Options', 'inbound-pro' ),
                    'options' => null
                ),
                array(
                    'id'  => 'confirm-link-shortcode',
                    'label'  => __('List double opt in confirmation link:', 'inbound-pro' ),
                    'description'  => __( 'Use this shortcode inside an automated email and it will return the list confirmation link. This will only work when creating custom confirmation email templates using our Inbound Mailer component.' , 'inbound-pro' ),
                    'type'  => 'text',
                    'readonly'  => true,
                    'default'  => '[inbound-list-double-optin-link]',
                ),
                array(
                    'id'  => 'list-double-optin-page-id',
                    'label'  => __('Confirmation Page Location', 'inbound-pro' ),
                    'description'  => __( 'Where to send readers to confirm being added to a list. We auto create a confirmation page on activation, but you can use our shortcode on any page [inbound-list-confirm-double-optin]. ' , 'inbound-pro' ),
                    'type'  => 'dropdown',
                    'default'  => '',
                    'options' => $pages,
                )
            )

        );


        return $settings;
    }

    /**
     * Prepare settings object for settings
     * @return mixed
     */
    public static function define_stand_alone_settings() {


        /* Setup License Keys Tab */
        if ( !defined('INBOUND_PRO_PATH') )  {
            $tab_slug = 'wpl-main';

            $wpleads_global_settings[$tab_slug]['label'] = __( 'Settings' , 'inbound-pro' );

            $wpleads_global_settings[$tab_slug]['settings'] = array(
                array(
                    'id'  => 'search-tracking',
                    'label' => __('Search Query Tracking' , 'inbound-pro' ),
                    'description' => __("WordPress Leads records searches made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'comment-tracking',
                    'label' => __('Comment Tracking' , 'inbound-pro' ),
                    'description' => __("WordPress Leads records comments made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'enable-dashboard',
                    'label' => __('Show Lead/List Data in Dashboard' , 'inbound-pro' ),
                    'description' => __("Turn this on to show graphical and list data about lead collection in WP Dashboard." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'disable-widgets',
                    'label' => __('Disable Default WordPress Dashboard Widgets' , 'inbound-pro' ),
                    'description' => __("This turns off some default widgets on the wordpress dashboard." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'extra-lead-data',
                    'label' => __('Full Contact API Key' , 'inbound-pro' ),
                    'description' => sprintf( __("Enter your Full contact API key. If you don't have one. Grab a free one here: %s" , 'inbound-pro' ) , "<a href='https://www.fullcontact.com/developer/pricing/' target='_blank'>" , "</a>"),
                    'type'  => 'text',
                    'default'  => '',
                    'options' => null
                ),
                array(
                    'id'  => 'inbound_admin_notification_inboundnow_link',
                    'option_name'  => 'inbound_admin_notification_inboundnow_link',
                    'label' => __('Credit Inbound Now in admin notification emails.' , 'inbound-pro' ),
                    'description' => __("Admin notification emails are sent after a visitor fills out an inbound form." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '1',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'inbound_forms_enable_akismet',
                    'option_name'  => 'inbound_forms_enable_akismet',
                    'label' => __('Run form submissions through Akismet if akismet is enabled.' , 'inbound-pro' ),
                    'description' => __("Enabling this option will tell Leads to run form submissions through akismet to prevent spam submissions." , 'inbound-pro' ),
                    'type'  => 'radio',
                    'default'  => '0',
                    'options' => array('1'=>'On','0'=>'Off')
                ),
                array(
                    'id'  => 'confirm-link-shortcode',
                    'label'  => __('List double opt in confirmation link:', 'inbound-pro' ),
                    'description'  => __( 'This shortcode is used to add a special link to emails requesting list double opt in confirmation. The link contains coded information required for the lead to confirm being added to a list.' , 'inbound-pro' ),
                    'type'  => 'text',
                    'readonly'  => true,
                    'default'  => '[inbound-list-double-optin-link link_text=&quot;' . __('Please confirm being put on our mailing list', 'inbound-pro') . '&quot;]',
                ),
                array(
                    'id'  => 'list-double-optin-page-id',
                    'label'  => __('Confirmation Page Location', 'inbound-pro' ),
                    'description'  => __( 'Where to send readers to confirm being added to a list. We auto create a confirmation page on activation, but you can use our shortcode on any page [inbound-list-confirm-double-optin]. ' , 'inbound-pro' ),
                    'type'  => 'dropdown',
                    'default'  => '',
                    'options' => self::leads_get_pages_array(),
                )
            );


            $tab_slug = 'wpleads-license-keys';
            $wpleads_global_settings[$tab_slug]['label'] = __('License Keys' , 'inbound-pro' );

            /* Setup Extensions Tab */
            $tab_slug = 'wpleads-extensions';
            $wpleads_global_settings[$tab_slug]['label'] = __('Extensions' , 'inbound-pro' );

            /* Setup API Keys Tab */
            $tab_slug = 'wpleads-apikeys';
        } else {
            $tab_slug = 'wpl-main';
        }



        $wpleads_global_settings[$tab_slug]['label'] = __('API Keys' , 'inbound-pro' );
        if (current_user_can('activate_plugins')) {
            $wpleads_global_settings[$tab_slug]['settings'] = array(
                array(
                    'id'  => 'api-keys-table',
                    'label' => __('API Keys Table' , 'inbound-pro' ),
                    'type'  => 'api-keys-table'
                )
            );
        } else {
            $wpleads_global_settings[$tab_slug]['settings'] = array(
                array(
                    'id'  => 'api-keys-table',
                    'label' => __('API Keys Table' , 'inbound-pro' ),
                    'type'  => 'html',
                    'default' => __('Not permitted', 'inbound-pro')
                )
            );
        }

        $wpleads_global_settings = apply_filters('wpleads_define_global_settings', $wpleads_global_settings);


        return $wpleads_global_settings;
    }
    
    /**
    *  Gets array of pages with ID => Label format
    */
    public static function leads_get_pages_array() {
        $pages = get_pages();

        $pages_array = array() ;

        foreach ($pages as $page) {
            $pages_array[ $page->ID ] = $page->post_title;
        }

        return $pages_array;
    }

    /**
     * Displays global settings
     */
    public static function display_stand_alone_settings() {
        global $wpdb;
        $wpleads_global_settings = self::define_stand_alone_settings();

        /* if running pro do not load license keys tab */
        if (defined('INBOUND_PRO_PATH') ) {
            unset($wpleads_global_settings['wpleads-license-keys']);
        }

        $active_tab = 'wpl-main';
        if (isset($_REQUEST['open-tab'])) {
            $active_tab = sanitize_text_field($_REQUEST['open-tab']);
        }


        self::save_settings();

        echo '<h2 class="nav-tab-wrapper">';

        foreach ($wpleads_global_settings as $key => $data) {
            ?>
            <a  id='tabs-<?php echo $key; ?>' class="wpl-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php _e( $data['label'] , 'inbound-pro' ); ?></a>
            <?php
        }
        echo "</h2><div class='lp-settings-tab-sidebar'>";

        echo "<div class='lp-sidebar-settings'><h2 style='font-size:16px;'>Like the Plugin? Leave us a review</h2><center><a class='review-button' href='http://wordpress.org/support/view/plugin-reviews/leads?rate=5#postform' target='_blank'>Leave a Quick Review</a></center><small>Reviews help constantly improve the plugin & keep us motivated! <strong>Thank you for your support!</strong></small></div><div class='lp-sidebar-settings'><h2>Help keep the plugin up to date, awesome & free!</h2><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
		<input type='hidden' name='cmd' value='_s-xclick'>
		<input type='hidden' name='hosted_button_id' value='GKQ2BR3RKB3YQ'>
		<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
		<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'></form>
		<small>Spare some change? Buy us a coffee/beer.<strong> We appreciate your continued support.</strong></small></div><div class='lp-sidebar-settings'><h2 style='font-size:18px;'>Follow Updates on Facebook</h2><iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'></iframe></div></div>";
        echo "<form action='edit.php?post_type=wp-lead&page=wpleads_global_settings' method='POST'>";
        echo "<input type='hidden' name='nature' value='wpl-global-settings-save'>";
        echo "<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";

        foreach ($wpleads_global_settings as $key => $array) {
            if (!array_key_exists('settings',$array)){
                continue;
            }

            $these_settings = $wpleads_global_settings[$key]['settings'];
            self::render_settings($key, $these_settings , $active_tab);
        }
        echo '<div style="float:left;padding-left:9px;padding-top:20px;">
			<input type="submit" value="Save Settings" tabindex="5" id="wpl-button-create-new-group-open" class="button-primary" >
		</div>';
        echo "</form>";

        self::render_inline_js();
    }

    /**
     * Get setting value from DB. Handles stand alone landing pages plugin differently from Inbound Pro included landing pages plugin
     * @param $field_id
     * @param $default
     * @return mixed
     */
    public static function get_setting( $field_id , $default ) {
        global $inbound_settings;
        $value = $default;

        if (defined('INBOUND_PRO_CURRENT_VERSION')) {
            $field_id = str_replace('wpl-main-' , '', $field_id );
            $value = (isset($inbound_settings['leads'][$field_id])) ? $inbound_settings['leads'][$field_id] : $default;
        } else {
            $value = get_option( $field_id, $default );
        }

        return $value;
    }

    /**
     * Saves global settings
     */
    public static function save_settings() {

        if (!isset($_POST['nature'])) {
            return;
        }

        $wpleads_global_settings = self::define_stand_alone_settings();

        foreach ($wpleads_global_settings as $key=>$array) {

            if (!isset($wpleads_global_settings[$key]['settings']) || !$wpleads_global_settings[$key]['settings'] ) {
                continue;
            }

            /* loop through fields and save the data */
            foreach ($wpleads_global_settings[$key]['settings'] as $field) {

                $field['id'] = $key.'-'.$field['id'];

                if (array_key_exists('option_name',$field) && $field['option_name'] ) {
                    $field['id'] = $field['option_name'];
                }

                if ($field['type']=='inboundnow-license-key' && isset($_POST['inboundnow_master_license_key'] ) && $_POST['inboundnow_master_license_key'] ) {
                    /* error_log(print_r($field, true)); */

                    $api_params = array(
                        'edd_action' => 'activate_license',
                        'license' =>   sanitize_text_field($_POST['inboundnow_master_license_key']),
                        'item_name' => sanitize_text_field($field['remote_download_slug'])
                    );
                    /* error_log(print_r($api_params, true)); */

                    /* Call the edd API */
                    $response = wp_remote_get(add_query_arg($api_params, WPL_STORE_URL), array('timeout' => 30, 'sslverify' => false));
                    /* error_log(print_r($response, true)); */

                    /* make sure the response came back okay */
                    if (is_wp_error($response)) {
                        break;
                    }

                    /* decode the license data */
                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    /* error_log(print_r($license_data, true)); */

                    /* $license_data->license will be either "active" or "inactive" */
                    update_option('wpleads_license_status-' . $field['slug'], $license_data->license);
                } else {
                    if (isset($_POST[$field['id']])) {
                        update_option($field['id'], $_POST[$field['id']]);
                    }
                }


                do_action('wpleads_save_global_settings',$field);

            } // end foreach

        }

    }

    /**
     * Load inline JS that powers settings area
     */
    public static function render_inline_js() {
        global $wpleads_global_settings;

        ?>
        <script type='text/javascript'>
            <?php
             if ( defined('INBOUND_PRO_PATH') ) {
                echo 'var hide_sidebar = true;';
             }else {
                 echo 'var hide_sidebar = false;';
             }
            ?>
            /* Hide sidebar when API Keys Tab is opened */
            jQuery(document).ready( function($) {

                jQuery('body').on( 'click' , '.wpl-nav-tab' , function() {

                    if ( this.id == 'tabs-wpleads-apikeys' || hide_sidebar ) {
                        jQuery('.lp-settings-tab-sidebar').hide();
                        jQuery('#wpl-button-create-new-group-open').hide();
                    } else {
                        jQuery('.lp-settings-tab-sidebar').show();
                        jQuery('#wpl-button-create-new-group-open').show();
                    }
                });

                <?php
                if ( isset($_GET['tab']) && $_GET['tab'] == 'tabs-wpleads-apikeys' ) {
                    echo "jQuery('.lp-settings-tab-sidebar').hide();";
                    echo "jQuery('#wpl-button-create-new-group-open').hide();";
                }
                ?>

            });

        </script>

        <?php

    }

    /**
     * Render settings field
     * @param $key
     * @param $custom_fields
     * @param $active_tab
     */
    public static function render_settings($key,$custom_fields,$active_tab) {

        /* Check if active tab */
        if ($key==$active_tab) {
            $display = 'block';
        } else {
            $display = 'none';
        }

        /* add extra styling for the api tab */
        if ( $key == 'wpleads-apikeys' ) {
            $styling = 'padding:0px;';
        } else {
            $styling = '';
        }

        /* Use nonce for verification */
        echo "<input type='hidden' name='wpl_{$key}_custom_fields_nonce' value='".wp_create_nonce('wpl-nonce')."' />";

        /* Begin the field table and loop */
        echo '<table class="wpl-tab-display" id="'.$key.'" style="display:'.$display.'; ' . $styling .'">';

        foreach ($custom_fields as $field) {
            /* get value of this field if it exists for this post */
            (isset($field['default'])) ? $default = $field['default'] : $default = null;

            $field['id'] = $key.'-'.$field['id'];

            if (array_key_exists('option_name',$field) && $field['option_name'] ){
                $field['id'] = $field['option_name'];
            }

            $field['value'] = get_option($field['id'], $default);

            /* Handle the API Keys List Table separately */
            if ( isset($field['type']) && $field['type'] == 'api-keys-table') {
                echo '</form><tr><td>';
                $api_keys_table = new Inbound_API_Keys_Table();
                $api_keys_table->display_controls();
                $api_keys_table->prepare_items();
                $api_keys_table->display();
                echo '</td></tr>';
                continue;
            }

            echo '<tr><th class="wpl-gs-th" valign="top" style="font-weight:300;">';
            if ($field['type']=='header'){
                echo $field['default'];
            } else {
                echo '<div class="inbound-setting-label tooltip" title="' . $field['description'] . '">'.$field['label'].'</div>';
            }
            echo '</th><td>';
            switch($field['type']) {
                // text
                case 'colorpicker':
                    if (!$field['value'])
                    {
                        $field['value'] = $field['default'];
                    }
                    echo '<input type="text" class="jpicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="5" />';
                    break;
                case 'datepicker':
                    echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="8" />';
                    break;
                case 'text':
                    echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" '.(isset($field['readonly']) && $field['readonly']  ? 'readonly' :'' ).'/>';
                    break;
                // textarea
                case 'textarea':
                    echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6">'.$field['value'].'</textarea>';
                    break;
                // wysiwyg
                case 'wysiwyg':
                    wp_editor( $field['value'], $field['id'], $settings = array() );
                    break;
                // media
                case 'media':
                    //echo 1; exit;
                    echo '<label for="upload_image">';
                    echo '<input name="'.$field['id'].'"  id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
                    echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
                    break;
                // checkbox
                case 'checkbox':
                    $i = 1;
                    echo "<table>";
                    if (!isset($field['value'])){$field['value']=array();}
                    elseif (!is_array($field['value'])){
                        $field['value'] = array($field['value']);
                    }
                    foreach ($field['options'] as $value=>$label) {
                        if ($i==5||$i==1) {
                            echo "<tr>";
                            $i=1;
                        }
                        echo '<td><input type="checkbox" name="'.$field['id'].'[]" id="'.$field['id'].'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','/>';
                        echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
                        if ($i==4) {
                            echo "</tr>";
                        }
                        $i++;
                    }
                    echo "</table>";
                    break;
                // radio
                case 'radio':
                    foreach ($field['options'] as $value=>$label) {
                        //echo $meta.":".$field['id'];
                        //echo "<br>";
                        echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
                        echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    break;
                // select
                case 'dropdown':
                    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                    foreach ($field['options'] as $value=>$label) {
                        echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
                    }
                    echo '</select>';
                    break;
                case 'html':
                    echo $field['value'];
                    break;


            } //end switch

            do_action('wpleads_render_global_settings',$field);
            echo '</td></tr>';
        } // end foreach
        echo '</table>'; // end table
    }

    /**
     * redirects settings link to Inbound Pro settings page with Landing Pages settings pre-loaded
     */
    public static function redirect_inbound_pro_settings() {

        if ( !isset($_GET['page']) || $_GET['page'] != 'inbound-pro-leads') {
            return;
        }

        header('Location: ' . admin_url('admin.php?page=inbound-pro&setting=leads'));
        exit;

    }
}

new Leads_Settings;
