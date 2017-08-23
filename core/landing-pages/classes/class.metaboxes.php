<?php

/**
 * Class for rendering and storing data related to the landing-page CPT edit screen
 * @package LandingPages
 * @subpackage Management
 */

class Landing_Pages_Metaboxes {

    static $current_vid;
    static $current_template;
    static $variations;
    static $is_new;
    static $is_clone;
    static $content_area;

    /**
     * initiate class
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     * load hooks and filters
     */
    public static function add_hooks() {
        add_action( 'admin_init' , array( __CLASS__ , 'run_actions' ) );
        add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
        add_action( 'add_meta_boxes' , array( __CLASS__ , 'register_metaboxes' ) );
        add_action( 'edit_form_after_title', array( __CLASS__ , 'display_variations_nav_metabox' ) );
        add_action( 'edit_form_after_title', array( __CLASS__ , 'display_main_headline' ) );
        add_filter( 'enter_title_here', array( __CLASS__, 'filter_default_title_text' ) , 10, 2 );
        add_filter( 'wp_default_editor', array( __CLASS__  , 'filter_default_wysiwyg_view' ) );

        /* get selected template metabox html */
        add_action( 'wp_ajax_lp_get_template_meta' , array( __CLASS__ , 'ajax_get_template_metabox_html' ));

        /* hidden select template container */
        add_action('admin_notices', array( __CLASS__ , 'display_select_template_container' ) );

        /* save landing page */
        add_action('save_post', array( __CLASS__ , 'save_landing_page' ) );

        /* set wpseo priority to low */
        add_filter('wpseo_metabox_prio', array( __CLASS__ , 'set_wpseo_priority' ));

    }

    /**
     * Register metaboxes
     */
    public static function register_metaboxes() {

        global $post;

        if ( !isset($post) || $post->post_type!='landing-page') {
            return;
        }


        if($post->post_status !== 'draft') {
            add_meta_box(
                'lp-thumbnail-sidebar-preview',
                __( 'Template Preview', 'landing-pages'),
                array( __CLASS__ , 'display_template_preview_metabox' ),
                'landing-page' ,
                'side',
                'high'
            );
        }


        /* Add conversion area for default template */
        add_meta_box(
            'lp_2_form_content',
            __('Insert Form / Conversion Content', 'landing-pages'),
            array( __CLASS__ , 'display_conversion_area_metabox' ),
            'landing-page',
            'normal',
            'high'
        );

        /* Select Template Metbox */
        add_meta_box(
            'lp_metabox_select_template', /* $id */
            __( 'Template Selection', 'landing-pages'),
            array( __CLASS__ , 'display_select_template' ),
            'landing-page',
            'normal',
            'high'
        );

        /* Load Template Settings */
        $extension_data = Landing_Pages_Load_Extensions::get_extended_data();;
        $current_template = Landing_Pages_Variations::get_current_template($post->ID);
        foreach ($extension_data as $key => $data) {

            if ( $key != $current_template || ( isset($data['info']['data_type']) && strstr( $data['info']['data_type'] , 'acf') ) ) {
                continue;
            }

            $template_name = ucwords(str_replace('-', ' ', $key));
            $id = strtolower(str_replace(' ', '-', $key));

            add_meta_box(
                "lp_{$id}_custom_meta_box", /* $id */
                "<small>$template_name</small>",
                array( __CLASS__ , 'display_extended_metabox' ),
                'landing-page', /* post-type */
                'normal', /* $context */
                'default',/* $priority */
                array('key' => $key)
            );

        }


        /* add custom css */
        add_meta_box(
            'lp_3_custom_css',
            __( 'Custom CSS' , 'landing-pages') ,
            array( __CLASS__ , 'display_custom_css_metabox' ),
            'landing-page',
            'normal',
            'low'
        );

        /* add custom js */
        add_meta_box(
            'lp_3_custom_js',
            __('Custom JS' , 'landing-pages') ,
            array( __CLASS__ , 'display_custom_js_metabox' ),
            'landing-page',
            'normal',
            'low'
        );

        /* add custom variation notes */
        add_meta_box(
            'lp_4_variation_notes',
            __('Variation Notes' , 'landing-pages') ,
            array( __CLASS__ , 'display_variation_notes' ),
            'landing-page',
            'normal',
            'low'
        );

        /* Add AB Testing Stats Box */
        add_meta_box(
            'lp_ab_display_stats_metabox',
            __( 'A/B Testing', 'landing-pages'),
            array( __CLASS__ , 'display_quick_stats_metabox' ) ,
            'landing-page' ,
            'side',
            'high'
        );

        /* discover extended metaboxes and render them */
        foreach ($extension_data as $key => $data) {

            if ( !isset( $data['info']['data_type']) ||  $data['info']['data_type'] != 'metabox') {
                continue;
            }

            $id = "metabox-" . $key;

            (isset($data['info']['label'])) ? $name = $data['info']['label'] : $name = ucwords(str_replace(array('-', 'ext '), ' ', $key) . " Extension Options");
            (isset($data['info']['position'])) ? $position = $data['info']['position'] : $position = "normal";
            (isset($data['info']['priority'])) ? $priority = $data['info']['priority'] : $priority = "default";

            add_meta_box(
                "lp_{$id}_custom_meta_box",
                $name,
                array( __CLASS__ , 'display_extended_metabox' ),
                'landing-page',
                $position,
                $priority,
                array('key' => $key)
            );
        }

        /* Display short description */
        add_meta_box(
            'postexcerpt',
            __('Short Description', 'landing-pages'),
            'post_excerpt_meta_box',
            'landing-page',
            'normal',
            'core'
        );

        /* Display conversion tracking helper */
        add_meta_box(
            'lp_conversion_tracking',
            __('Additional Resources', 'landing-pages'),
            array( __CLASS__ , 'display_additional_resources' ),
            'landing-page',
            'normal',
            'low'
        );
    }

    /**
     * Run administrative actions on landing page
     */
    public static function run_actions() {

        if (!isset($_GET['post'])) {
            return;
        }

        $post = get_post( $_GET['post'] );

        if ( !isset($post) || $post->post_type != 'landing-page') {
            return;
        }


        self::$current_vid = Landing_Pages_Variations::get_current_variation_id( $post->ID );
        self::$variations =Landing_Pages_Variations::get_variations( $post->ID );

        /*check for delete command */
        if (isset($_GET['ab-action']) && $_GET['ab-action'] == 'delete-variation') {
            Landing_Pages_Variations::delete_variation( $post->ID , intval($_REQUEST['action-variation-id']) );
        }

        /*check for pause command */
        if (isset($_GET['ab-action']) && $_GET['ab-action'] == 'pause-variation') {
            Landing_Pages_Variations::pause_variation( $post->ID ,  intval($_REQUEST['action-variation-id']) );

        }

        /*check for pause command */
        if (isset($_GET['ab-action']) && $_GET['ab-action'] == 'play-variation') {
            Landing_Pages_Variations::play_variation( $post->ID ,  intval($_REQUEST['action-variation-id']) );
        }

        self::$is_new = (isset($_GET['new-variation'])) ? 1 : 0;
        self::$is_clone = (isset($_GET['clone'])) ? $_GET['clone'] : null;
        self::$content_area = Landing_Pages_Variations::get_post_content( $post->ID );

        (isset($_GET['new-variation']) && $_GET['new-variation'] == 1) ? $new_variation = 1 : $new_variation = 0;

        /*if new variation and cloning then programatically prepare the next variation id */
        if (self::$is_new ) {
            $_SESSION['lp_ab_test_open_variation'] = Landing_Pages_Variations::prepare_new_variation_id( $post->ID );
        }
    }


    /**
     * Enqueue scripts
     */
    public static function enqueue_scripts( $hook ) {

        global $post;
        $screen = get_current_screen();

        if ( !isset($screen) || $screen->id != 'landing-page') {
            return;
        }

        wp_enqueue_script(array('jquery', 'jqueryui', 'editor', 'thickbox', 'media-upload'));
        wp_enqueue_style('edit-landing-page', LANDINGPAGES_URLPATH . 'assets/css/admin/edit-landing-page.css', array() , null);
        wp_enqueue_script('lp-js-metaboxes', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.metaboxes.js', array() , null);
        wp_enqueue_script('jpicker', LANDINGPAGES_URLPATH . 'assets/libraries/jpicker/jpicker-1.1.6.min.js', array() , null);
        wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => LANDINGPAGES_URLPATH.'assets/libraries/jpicker/images/' ));
        wp_enqueue_style('jpicker-css', LANDINGPAGES_URLPATH . 'assets/libraries/jpicker/css/jPicker-1.1.6.min.css', array() , null);

        $template_data = Landing_Pages_Load_Extensions::get_extended_data();;
        $template_data_json = json_encode($template_data);
        $template = Landing_Pages_Variations::get_current_template( $post->ID );
        $params = array('selected_template'=>$template, 'templates'=>$template_data_json);
        wp_localize_script('lp-js-metaboxes', 'data', $params);

        /* if ACF load CSS to hide WordPress core elements */
        if ( isset($template_data[$template]['info']['data_type']) && strstr( $template_data[$template]['info']['data_type'] , 'acf')){
            wp_enqueue_style('lp-acf-template', LANDINGPAGES_URLPATH . 'assets/css/admin/acf-hide-wp-elements.css' , array() , null );
        }

        wp_enqueue_style('inbound-metaboxes', INBOUNDNOW_SHARED_URLPATH . 'assets/css/admin/inbound-metaboxes.css' , array() , null);
        wp_enqueue_script( 'lp-admin-clear-stats-ajax-request', LANDINGPAGES_URLPATH . 'assets/js/ajax.clearstats.js', array( 'jquery' ) ,  null );
        wp_localize_script( 'lp-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'lp_clear_nonce' => wp_create_nonce('lp-clear-nonce') ) );

        wp_enqueue_script('jquery-zoomer', LANDINGPAGES_URLPATH . 'assets/libraries/jquery.zoomer.js', array() , null);
        wp_enqueue_script('lp-post-edit-ui', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.post-edit.js', array() , null);
        wp_localize_script( 'lp-post-edit-ui', 'lp_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_landing_page_meta_nonce' => wp_create_nonce('wp-landing-page-meta-nonce'),  'lp_template_nonce' => wp_create_nonce('lp-nonce') ) );
        wp_enqueue_style('admin-post-edit-css', LANDINGPAGES_URLPATH . 'assets/css/admin-post-edit.css', array() , null);

        /* Load FontAwesome */
        wp_register_style('font-awesome', INBOUNDNOW_SHARED_URLPATH.'assets/fonts/fontawesome/css/font-awesome.min.css', array() , null);
        wp_enqueue_style('font-awesome');

        /* Load Sweet Alert */
        wp_enqueue_script('sweet-alert', INBOUNDNOW_SHARED_URLPATH.'assets/includes/SweetAlert/sweetalert.min.js', array() , null);
        $localized = array(
            'title' => __("Are you sure?","landing-pages"),
            'text' => __("Are you sure you want to select this template?","landing-pages"),
            'confirmButtonText' => __("Yes","landing-pages"),
            'confirmTextTitle' =>  __("Deleted!","landing-pages"),
            'confirmText' =>  __("Your imaginary file has been deleted.","landing-pages"),
            'waitTitle' =>  __("Please wait","landing-pages"),
            'waitText' =>  __("We are peparing your template now.","landing-pages"),
            'waitImage' => INBOUNDNOW_SHARED_URLPATH .'assets/includes/SweetAlert/loading_colorful.gif'
        );
        wp_localize_script('sweet-alert', 'sweetalert', $localized );
        wp_enqueue_style('sweet-alert', INBOUNDNOW_SHARED_URLPATH.'assets/includes/SweetAlert/sweetalert.css', array() , null);

        wp_enqueue_style('lp-ab-testing-admin', LANDINGPAGES_URLPATH . 'assets/css/admin-ab-testing.css', array() , null);
        wp_enqueue_script('lp-ab-testing-admin', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.post-edit-ab-testing.js', array('jquery') , null );
        wp_localize_script('lp-ab-testing-admin', 'variation', array('pid' => $post->ID , 'vid' => self::$current_vid, 'new_variation' => self::$is_new , 'variations' => self::$variations, 'content_area' => self::$content_area));

        /* enqueue supportive scripts */
        wp_enqueue_script( 'jquery-time-picker', LANDINGPAGES_URLPATH . 'assets/libraries/datetimepicker/jquery.datetimepicker.js', array('jquery') , null );
        wp_enqueue_style( 'jquery-time-picker', LANDINGPAGES_URLPATH . 'assets/libraries/datetimepicker/jquery.datetimepicker.css' , array() , null );
        wp_enqueue_script( 'jquery-date-picker', LANDINGPAGES_URLPATH . 'assets/libraries/datetimepicker/picker_functions.js', array('jquery') , null );

        /* only load these scripts and styles when creatng a new landing page  */
        if ( $hook == 'post-new.php'  ) {
            wp_enqueue_script('lp-js-create-new-lander', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.post-new.js', array('jquery'), null, true );
            wp_localize_script( 'lp-js-create-new-lander', 'lp_post_new_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_landing_page_meta_nonce' => wp_create_nonce('lp_nonce')  , 'LANDINGPAGES_URLPATH' => LANDINGPAGES_URLPATH ) );
            wp_enqueue_style('lp-css-post-new', LANDINGPAGES_URLPATH . 'assets/css/admin-post-new.css' , array() , null);
        }

        if ( $hook == 'post.php'  ) {
            /* change template sweet alert support */
            wp_enqueue_script('lp-change-template', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.post.js', array('jquery'), null, true );
        }
    }

    /**
     * force wysiwyg eeditor to open in html mode
     * @return string
     */
    public static function filter_default_wysiwyg_view( $default ) {
        global $post;
        if ( !isset($post) || $post->post_type != 'landing-page' ) {
            return $default;
        }

        return 'html';
    }


    /**
     * change the default title placeholder text for landing pages
     * @param $text
     * @param $post
     * @return mixed
     */
    public static function filter_default_title_text( $text , $post ) {
        if ($post->post_type == 'landing-page') {
            return __( 'Enter Landing Page Description' , 'landing-pages');
        } else {
            return $text;
        }
    }


    /**
     * Display main headline
     */
    public static function display_main_headline() {
        global $post;

        if (!isset($post) || $post->post_type !='landing-page' ) {
            return;
        }

        $variation_id = Landing_Pages_Variations::get_current_variation_id( );
        $main_headline = Landing_Pages_Variations::get_main_headline( $post->ID , $variation_id );

        ?>
        <div id="main-title-area">
            <input type="text" name="<?php echo Landing_Pages_Variations::prepare_input_id( 'lp-main-headline'); ?>" placeholder="<?php  _e('Enter Headline' , 'landing-pages'); ?>" id="lp-main-headline" value="<?php echo $main_headline; ?>" title="'. __('This headline will appear in the landing page template.' , 'landing-pages') .'">
        </div>
        <div id="switch-lp">0</div>

        <?php
        /* Frontend params */
        if(isset($_REQUEST['frontend']) && $_REQUEST['frontend'] == 'true') {
            echo('<input type="hidden" name="frontend" id="frontend-on" value="true" />');
        }

    }
    /**
     * dipslay select template metabox
     */
    public static function display_select_template() {
        global $post;

        $template =  Landing_Pages_Variations::get_current_template( $post->ID );

        $name = Landing_Pages_Variations::prepare_input_id( 'lp-selected-template' );

        /* Use nonce for verification */
        echo "<input type='hidden' name='lp_lp_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";
        ?>

        <div id="lp_template_change"><h2>
                <a class="button" id="lp-change-template-button"><?php _e( 'Choose Another Template' , 'landing-pages'); ?></a>
        </div>
        <input type='hidden' id='lp_select_template' name='<?php echo $name; ?>' value='<?php echo $template; ?>'>
        <div id="template-display-options">

        </div>

    <?php
    }

    /**
     * Display variation tabs
     */
    public static function display_variations_nav_metabox() {
        global $post;

        global $post;

        if ( !isset($post) || $post->post_type!='landing-page') {
            return;
        }

        $current_variation_id = Landing_Pages_Variations::get_current_variation_id($post->ID);

        echo "<input type='hidden' id='open_variation' value='{$current_variation_id}'>";
        echo "<input type='hidden' name='lp-variation-id' id='lp-variation-id' value='{$current_variation_id}'>";

        $variations = Landing_Pages_Variations::get_variations($post->ID);
        $new_variation_id =  Landing_Pages_Variations::prepare_new_variation_id($post->ID);

        if ($current_variation_id > 0 || self::$is_new ) {
            $first_class = 'inactive';
        } else {
            $first_class = 'active';
        }

        echo '<h2 class="nav-tab-wrapper a_b_tabs">';

        foreach ($variations as $i => $vid) {
            $letter = Landing_Pages_Variations::vid_to_letter( $post->ID , $i);
            $pre = ($i < 1) ? __('Version ', 'landing-pages') : '';

            if ($current_variation_id == $vid && !isset($_GET['new-variation'])) {
                $cur_class = 'active';
            } else {
                $cur_class = 'inactive';
            }
            $permalink = get_permalink($post->ID) . '?' . '&lp-variation-id=' . $vid ;
            echo '<a href="?post=' . $post->ID . '&lp-variation-id=' . $vid . '&action=edit" class="lp-nav-tab nav-tab nav-tab-special-' . $cur_class . '" data-permalink="'.$permalink.'" id="tabs-add-variation" target="_parent">' . $pre . $letter . '</a>';
        }

        if (!isset($_GET['new-variation'])) {
            echo '<a href="?post=' . $post->ID . '&lp-variation-id=' . $new_variation_id . '&action=edit&new-variation=1" class="lp-nav-tab nav-tab nav-tab-special-inactive nav-tab-add-new-variation" id="tabs-add-variation">' . __('Add New Variation', 'landing-pages') . '</a>';
        } else {
            $variation_count = $i + 1;
            $letter = Landing_Pages_Variations::vid_to_letter( $post->ID, $variation_count);
            echo '<a href="?post=' . $post->ID . '&lp-variation-id=' . $new_variation_id . '&action=edit" class="lp-nav-tab nav-tab nav-tab-special-active" id="tabs-add-variation">' . $letter . '</a>';
        }
        $edit_link = (isset($_GET['lp-variation-id'])) ? '?lp-variation-id=' . $_GET['lp-variation-id'] . '' : '?lp-variation-id=0';
        $post_link = get_permalink($post->ID);
        $post_link = preg_replace('/\?.*/', '', $post_link);
        echo "<a rel='" . $post_link . "' id='launch-visual-editer' class='button-primary new-save-lp-frontend' href='$post_link$edit_link&inbound-customizer=on'>" . __('Launch Visual Editor', 'landing-pages') . "</a>";
        echo '</h2>';


    }

    /**
     * Displays quick stats metabox
     */
    public static function display_quick_stats_metabox() {
        global $post;
        $variations = Landing_Pages_Variations::get_variations($post->ID);

        ?>
        <div>

            <style type="text/css">

            </style>
            <div class="inside" id="a-b-testing">

                <div id="stat-box">
                    <?php

                    if (isset($_GET['new_meta_key']) && is_numeric($_GET['new_meta_key']) ) {
                    ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function($) {
                                /* This fixes meta data saves for cloned pages */
                                function isNumber (o) {
                                    return ! isNaN (o-0) && o !== null && o !== "" && o !== false;
                                }
                                var new_meta_key = "<?php echo $_GET['new_meta_key'];?>";
                                jQuery('#template-display-options input[type=text], #template-display-options select, #template-display-options input[type=radio], #template-display-options textarea').each(function(){
                                    var this_id = jQuery(this).attr("id");
                                    var final_number = this_id.match(/[^-]+$/g);
                                    var new_id = this_id.replace(/[^-]+$/g, new_meta_key);
                                    var is_number = isNumber(final_number);

                                    if (is_number === false) {
                                        jQuery(this).attr("id", this_id + "-" + new_meta_key);
                                        jQuery(this).attr("name", this_id + "-" + new_meta_key);
                                    } else {
                                        jQuery(this).attr("id", new_id);
                                        jQuery(this).attr("name", new_id);
                                    }
                                });
                            });
                        </script>
                    <?php
                    }

                    $howmany = count($variations);

                    foreach ($variations as $key => $vid) {


                        $variation_status = Landing_Pages_Variations::get_variation_status($post->ID, $vid);
                        $variation_status_class = ($variation_status == 1) ? "variation-on" : 'variation-off';

                        $permalink = Landing_Pages_Variations::get_variation_permalink($post->ID, $vid);

                        $impressions = Landing_Pages_Variations::get_impressions($post->ID, $vid);
                        $conversions = Landing_Pages_Variations::get_conversions($post->ID, $vid);
                        $conversion_rate = Landing_Pages_Variations::get_conversion_rate($post->ID, $vid);
                        $title = Landing_Pages_Variations::get_main_headline($post->ID, $vid);

                        ?>

                        <div id="lp-variation-<?php echo Landing_Pages_Variations::vid_to_letter( $post->ID , $key); ?>"
                             class="variation-row <?php echo $variation_status_class; ?>">
                            <div class='varation-header'>
								<span class='variation-name'><?php _e('Variation', 'landing-pages'); ?> <span
                                        class='stat-letter'><?php echo Landing_Pages_Variations::vid_to_letter( $post->ID , $key); ?></span>
                                    <?php
                                    if ($variation_status != 1) {
                                        ?>
                                        <span class='is-paused'>(<?php _e('Paused', 'landing-pages') ?>)</span>
                                    <?php
                                    }
                                    ?>
								</span>

<span class="settings_icon"> </span>
<span class="settings_wrapper">
<span class="settings_wrapper_heading">Variation Settings</span>
    <ul class="settings_list_li">
        <li class="settings_edit">
            <span class='stat-menu-edit'>
                <a title="<?php _e('Edit this variation', 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&action-variation-id=<?php echo $vid; ?>'>
                    <?php _e('Edit', 'landing-pages'); ?>
                </a>
            </span>
        </li>
        <li class="settings_preview">
            <span class='stat-menu-preview'>
                <a title="<?php _e('Preview this variation', 'landing-pages'); ?>" class='thickbox' href='<?php echo $permalink; ?>&iframe_window=on&post_id=<?php echo $post->ID; ?>&TB_iframe=true&width=1503&height=467' target='_blank'>
                    <?php _e('Preview', 'landing-pages'); ?>
                </a>
            </span>
        </li>
        <li class="settings_clone">
            <span class='stat-menu-clone'>
                <a title="<?php _e('Clone this variation', 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&new-variation=1&clone=<?php echo $vid; ?>&new_meta_key=<?php echo $howmany; ?>'>
                    <?php _e('Clone', 'landing-pages'); ?>
                </a>
            </span>
        </li>
        <li class="settings_delete">
            <span class='stat-control-delete'>
                <a title="<?php _e('Delete this variation', 'landing-pages'); ?>" href='?post=<?php echo $post->ID; ?>&action=edit&action-variation-id=<?php echo $vid; ?>&ab-action=delete-variation'>
                    <?php _e('Delete', 'landing-pages'); ?>
                </a>
            </span>
        </li>
        <li class="settings_clearstat">
<!-- CLEAR STATS START -->
           <span class="lp-delete-var-stats" data-letter='<?php echo Landing_Pages_Variations::vid_to_letter( $post->ID , $key); ?>' data-vid='<?php echo $vid; ?>' rel='<?php echo $post->ID; ?>' title="<?php _e('Delete this variations stats', 'landing-pages'); ?>">
                <?php _e('Clear Stats', 'landing-pages'); ?>
            </span>
<!-- CLEAR STAT END --></li>
    </ul>
</span>


                                
<!-- PAUSE START -->                                
<span class='stat-control-pause'><a title="<?php _e('Pause this variation', 'landing-pages'); ?>"
                                                href='?post=<?php echo $post->ID; ?>&action=edit&action-variation-id=<?php echo $vid; ?>&ab-action=pause-variation'> </a></span>
<!-- PAUSE END -->                                

<!-- PLAY START -->
<span class='stat-seperator pause-sep'>|</span>
<span class='stat-control-play'><a
        title="<?php _e('Turn this variation on', 'landing-pages'); ?>"
href='?post=<?php echo $post->ID; ?>&action=edit&action-variation-id=<?php echo $vid; ?>&ab-action=play-variation'> </a></span>
<!-- PLAY END -->
                         
                                
                                
                            </div>
                            <div class="stat-row">
                                <div class='stat-stats' colspan='2'>
                                    <div class='stat-container-impressions number-box'>
                                       <span class="stat-id"><?php _e('Views', 'landing-pages'); ?> </span>
                                        <span class='stat-span-impressions'><?php echo $impressions; ?></span>
                                    </div>
                                    <div class='stat-container-conversions number-box'>
<span class="stat-id"><?php _e('Conversions', 'landing-pages'); ?></span>                                        <span class='stat-span-conversions'><?php echo $conversions; ?></span>
                                        </span>
                                    </div>
                                    <div class='stat-container-conversion_rate number-box'>
                        <span class="stat-id rate"><?php _e('Conversion Rate', 'landing-pages'); ?></span>
                        <span class='stat-span-conversion_rate'><?php echo $conversion_rate; ?></span>
                                    </div>
                                     
                                </div>
                            </div>
                            <div class="stat-row">

                                <div class='stat-menu-container'>

                                    <?php do_action('lp_ab_testing_stats_menu_post'); ?>

                                </div>
                            </div>
                        </div>
                    <?php

                    }
                    ?>
                </div>

            </div>
        </div>
    <?php
    }

    /**
     * Display conversion area metabox
     */
    public static function display_conversion_area_metabox(){

        global $post;

        $meta_box_id = 'lp_2_form_content';
        $editor_id = 'landing-page-myeditor';

        /* Add CSS & jQuery to make this work like the original WYSIWYG */
        echo "
			<style type='text/css'>
					#$meta_box_id #edButtonHTML, #$meta_box_id #edButtonPreview {background-color: #F1F1F1; border-color: #DFDFDF #DFDFDF #CCC; color: #999;}
					#$editor_id{width:100%;}
					#$meta_box_id #editorcontainer{background:#fff !important;}
					#$meta_box_id #editor_id_fullscreen{display:none;}
			</style>

			<script type='text/javascript'>
            jQuery(function($){
                jQuery('#lp_2_form_content #editor-toolbar > a').click(function(){
                        jQuery('#$meta_box_id #editor-toolbar > a').removeClass('active');
                        jQuery(this).addClass('active');
                });

                if(jQuery('#lp_2_form_content #edButtonPreview').hasClass('active')){
                        jQuery('#$meta_box_id #ed_toolbar').hide();
                }

                jQuery('#lp_2_form_content #edButtonPreview').click(function(){
                        jQuery('#$meta_box_id #ed_toolbar').hide();
                });

                jQuery('#lp_2_form_content #edButtonHTML').click(function(){
                        jQuery('#$meta_box_id #ed_toolbar').show();
                });

                /*Tell the uploader to insert content into the correct WYSIWYG editor */
                jQuery('#media-buttons a').bind('click', function(){
                    var customEditor = jQuery(this).parents('#$meta_box_id');
                    if(customEditor.length > 0){
                        edCanvas = document.getElementById('$editor_id');
                    }
                    else{
                        edCanvas = document.getElementById('content');
                    }
                });
			});
			</script>
	    ";

        /*Create The Editor */
        $conversion_area = Landing_Pages_Variations::get_conversion_area( $post->ID );
        wp_editor($conversion_area, $editor_id);

        /*Clear The Room! */
        echo "<div style='clear:both; display:block;'></div>";
        echo "<div style='width:100%;text-align:right;margin-top:11px;'><div class='lp_tooltip'  title=\"". __('To help track conversions Landing Pages Plugin will automatically add a tracking class to forms. If you would like to track a link add this class to it' , 'landing-pages') ." class='inbound-track-link'\" ></div></div>";

    }

    /**
     * Display custom CSS metabox
     */
    public static function display_custom_css_metabox() {
        global $post;

        echo sprintf(
            __('%sCustom CSS may be required to customize this landing page.%s%s %sFormat%s: #element-id { display:none !important; }%s' , 'landing-pages') ,
            '<em>' , '</em>' , '<strong>' , '<u>' , '</u>' ,'</strong>'
        );

        $custom_css_name = Landing_Pages_Variations::prepare_input_id( 'lp-custom-css' );
        $custom_css = Landing_Pages_Variations::get_custom_css( $post->ID );
        echo '<textarea name="'.$custom_css_name.'" id="lp-custom-css" rows="5" cols="30" style="width:100%;">'. $custom_css .'</textarea>';
    }

    /**
     * Display custom JS metabox
     */
    public static function display_custom_js_metabox() {
        global $post;

        $custom_js_name = Landing_Pages_Variations::prepare_input_id( 'lp-custom-js' );
        $custom_js = Landing_Pages_Variations::get_custom_js( $post->ID );

        echo '<textarea name="'.$custom_js_name.'" id="lp_custom_js" rows="5" cols="30" style="width:100%;">'.$custom_js.'</textarea>';
    }

    /**
     * Display variation notes metabox
     */
    public static function display_variation_notes() {
        global $post;

        $variation_id = Landing_Pages_Variations::get_current_variation_id( );
        $variation_notes = Landing_Pages_Variations::get_variation_notes( $post->ID , $variation_id );
        $variation_notes_id = Landing_Pages_Variations::prepare_input_id( 'lp-variation-notes');

        echo '<textarea name="'.$variation_notes_id.'" id="lp_variation_notes" rows="5" cols="30" style="width:100%;">'.$variation_notes.'</textarea>';
    }

    /**
     * Display select template container
     */
    public static function display_select_template_container() {
        global $post;


        if (!isset($post) || $post->post_type != 'landing-page') {
            return false;
        }

        $screen = get_current_screen();

        $toggle = ($screen->parent_file != 'edit.php?post_type=landing-page' || $screen->action != 'add') ? "display:none" : "";

        $extension_data = Landing_Pages_Load_Extensions::get_extended_data();;
        $extension_data_cats = Landing_Pages_Load_Extensions::get_template_categories();

        unset($extension_data['lp']);

        ksort($extension_data_cats);
        $uploads = wp_upload_dir();
        $uploads_path = $uploads['basedir'];
        $extended_path = $uploads_path . '/landing-pages/templates/';

        self::$current_template = Landing_Pages_Variations::get_current_template($post->ID);

        echo "<div class='lp-template-selector-container' style='{$toggle}'>";
        echo "<div class='lp-selection-heading'>";
        echo "<h1>" . __('Select Your Landing Page Template!', 'landing-pages') . "</h1>";
        echo '<a class="button-secondary" style="display:none;" id="lp-cancel-selection">' . __('Cancel Template Change', 'landing-pages') . '</a>';
        echo "</div>";
        echo '<ul id="template-filter" >';
        echo '<li class="button-primary button"><a href="#" data-filter=".template-item-boxes">' . __('All', 'landing-pages') . '</a></li>';
        echo '<li class="button-primary button"><a href="#" data-filter=".theme">' . __('Theme', 'landing-pages') . '</a></li>';
        $categories = array('Theme');
        foreach ($extension_data_cats as $cat) {

            $slug = str_replace(' ', '-', $cat['value']);
            $slug = strtolower($slug);
            $cat['value'] = ucwords($cat['value']);
            if (!in_array($cat['value'], $categories)) {
                echo '<li class="button"><a href="#" data-filter=".' . $slug . '">' . $cat['value'] . '</a></li>';
                $categories[] = $cat['value'];
            }

        }
        echo "</ul>";
        echo '<div id="templates-container" >';

        foreach ($extension_data as $this_extension => $data) {

            if (substr($this_extension, 0, 4) == 'ext-') {
                continue;
            }

            if (isset($data['info']['data_type']) && $data['info']['data_type'] == 'metabox') {
                continue;
            }


            $cats = explode(',', $data['info']['category']);
            foreach ($cats as $key => $cat) {
                $cat = (is_array($cat)) ? implode(',',$cat) : $cat;
                $cat = ($cat) ? trim($cat) : '';
                $cat = str_replace(' ', '-', $cat);
                $cats[$key] = trim(strtolower($cat));
            }

            $cat_slug = implode(' ', $cats);

            $thumb = false;
            /* Get Thumbnail */
            if (file_exists(LANDINGPAGES_PATH . 'templates/' . $this_extension . "/thumbnail.png")) {
                if ($this_extension == 'default') {
                    $thumbnail = get_template_directory() . "/screenshot.png";
                    if (file_exists($thumbnail)) {
                        $thumbnail = get_bloginfo('template_directory') . "/screenshot.png";
                        $thumb = true;
                    }
                } else {
                    $thumbnail = LANDINGPAGES_URLPATH . 'templates/' . $this_extension . "/thumbnail.png";
                    $thumb = true;
                }

            }

            if (file_exists(LANDINGPAGES_UPLOADS_PATH . $this_extension . "/thumbnail.png")) {
                $thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $this_extension . "/thumbnail.png";
                $thumb = true;
            }

            if (file_exists(LANDINGPAGES_UPLOADS_PATH . $this_extension . "/thumbnail.jpg")) {
                $thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $this_extension . "/thumbnail.jpg";
                $thumb = true;
            }

            if (!$thumb) {
                $thumbnail = LANDINGPAGES_URLPATH . 'templates/default/thumbnail.png';
            }

            $demo_link = (isset($data['info']['demo'])) ? $data['info']['demo'] : '';
            ?>
            <div id='template-item' class="<?php echo $cat_slug; ?> template-item-boxes">
                <div id="template-box">
                    <div class="lp_tooltip_templates" title="<?php echo $data['info']['description']; ?>"></div>
                    <a class='lp_select_template' href='#' label='<?php echo $data['info']['label']; ?>'
                       id='<?php echo $this_extension; ?>'>
                        <img src="<?php echo $thumbnail; ?>" class='template-thumbnail'
                             alt="<?php echo $data['info']['label']; ?>" id='lp_<?php echo $this_extension; ?>'>
                    </a>

                    <p>

                    <div id="template-title"><?php echo $data['info']['label']; ?></div>
                    <a href='#' label='<?php echo $data['info']['label']; ?>' id='<?php echo $this_extension; ?>'
                       class='lp_select_template'><?php _e('Select', 'landing-pages'); ?></a> |
                    <a class='<?php echo $cat_slug;?>' target="_blank" href='<?php echo $demo_link;?>'
                       id='lp_preview_this_template'><?php _e('Preview', 'landing-pages'); ?></a>
                    </p>
                </div>
            </div>
        <?php
        }
        echo '</div>';
        echo "<div class='clear'></div>";
        echo "</div>";
        echo "<div style='display:none;' class='currently_selected'>" . __('This is Currently Selected', 'landing-pages') . "</a></div>";
    }

    /**
     * Display template preview metabox
     */
    public static function display_template_preview_metabox() {
        global $post;

        $template = Landing_Pages_Variations::get_current_template( $post->ID );
        $permalink = Landing_Pages_Variations::get_variation_permalink( $post->ID );

        $datetime = the_modified_date('YmjH',null,null,false);
        $permalink = add_query_arg( array( 'dt' => $datetime , 'dont_save' => true ) , $permalink );
        ?>

        <style type="text/css">
            <?php
             /* hide featured image slot if not default template */
             if ($template != 'default' ) {
                echo '#postimagediv {display:none;}';
             }

            ?>
            #lp-thumbnail-sidebar-preview {
                background: transparent !important;
            }
            #lp-thumbnail-sidebar-preview .handlediv, #lp-thumbnail-sidebar-preview .hndle {
                display: none !important;
            }
            #lp-thumbnail-sidebar-preview .inside {
                padding: 0px !important;

                border: none !important;
                margin-top: -33px !important;
                margin-bottom: -10px;
                overflow:hidden;
            }
            #lp-thumbnail-sidebar-preview  .zoomer-wrapper {
                vertical-align: top;
                margin-top:33px !important;
            }
            #lp-thumbnail-sidebar-preview iframe#zoomer {
                margin-top: -30px;
            }
        </style>
        <?php

        if (isset($_GET['new-variation'])) {
            return;
        }
        if( isset($_GET['inbound-editor']) && $_GET['inbound-editor'] !== true ) {
            return;
        }
        // default
        echo "<iframe src='$permalink' id='zoomer'></iframe>";

    }


    /**
     * generate metabox html from extended dataset
     */
    public static function display_extended_metabox( $post , $args) {

        $extension_data = Landing_Pages_Load_Extensions::get_extended_data();;

        $key = $args['args']['key'];

        if (!isset( $extension_data[$key]['settings'] ) ) {
            return;
        }

        self::render_fields($key ,  $extension_data[$key]['settings'] , $post);
    }


    /**
     * Display additional documentaiton metabox
     */
    public static function display_additional_resources() {
        global $post;

        $variation_id = Landing_Pages_Variations::get_current_variation_id();
        $salt = md5( $post->ID . AUTH_KEY );
       ?>
       <div>
            <table style='width:100%'>
                <tr>
                    <td style='width:22%'>
                        <?php _e( 'Conversion Shortcode' , 'inbound-pro' ); ?>
                    </td>
                    <td>
                        <input type='text' style='width:95%;display:inline;' readonly='readonly' value="[landing-page-conversion id='<?php echo $post->ID; ?>' vid='<?php echo $variation_id; ?>']">
                        <div class="lp_tooltip" title="<?php _e( 'Instead of depending on Inbound Forms or tracked clicks for conversion tracking, enter this shortcode into your final destination page to manually increment this variation\'s conversion count' , 'landing-page' ); ?>" ><i class="fa fa-question-circle"></i></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php _e( 'Conversion Callback URL' , 'inbound-pro' ); ?>
                    </td>
                    <td>
                        <input type='text' style='width:95%;display:inline;' readonly='readonly' value="<?php echo add_query_arg( array( 'postback'=>'true' , 'event' => 'lp_conversion' , 'id' => $post->ID , 'vid' => $variation_id , 'salt' => $salt ) , site_url())  ?>">
                        <div class="lp_tooltip" title="<?php _e( 'If you would like to use a thrid party event to record a conversion you can use this cusomized callback URL.' , 'landing-page' ); ?>" ><i class="fa fa-question-circle"></i></div>
                    </td>
                </tr>
            </table>
       </div>
       <?php
    }


    /**
     * Renders metabox html
     * @param STRING $key data key
     * @param ARRAY $custom_fields field data
     */
    public static function render_fields($key, $custom_fields, $post) {

        /* Use nonce for verification */
        echo "<input type='hidden' name='lp_{$key}_custom_fields_nonce' value='" . wp_create_nonce('lp-nonce') . "' />";

        /*  Begin the field table and loop */
        echo '<div class="form-table" id="inbound-meta">';

        foreach ($custom_fields as $field) {

            $field_id = Landing_Pages_Variations::prepare_input_id( $key . "-" . $field['id'] );
            $field_name = $field['id'];
            $label_class = $field['id'] . "-label";
            $type_class = " inbound-" . $field['type'];
            $type_class_row = " inbound-" . $field['type'] . "-row";
            $type_class_option = " inbound-" . $field['type'] . "-option";
            $option_class = (isset($field['class'])) ? $field['class'] : '';

            $ink = get_option('lp-license-keys-' . $key);
            $status = get_option('lp_license_status-' . $key);
            $status_test = (isset($status) && $status != "") ? $status : 'inactive';

            $meta = Landing_Pages_Variations::get_setting_value( $key . "-" . $field['id'] , $post->ID , null, $field['default'] );

            /* Remove prefixes on global => true template options */
            if (isset($field['global']) && $field['global'] === true) {
                $field_id = $field_name;
                $meta = get_post_meta($post->ID, $field_name, true);
            }

            /* begin a table row with */
            echo '<div class="' . $field['id'] . $type_class_row . ' div-' . $option_class . ' wp-call-to-action-option-row inbound-meta-box-row">';

            if ($field['type'] != "description-block" && $field['type'] != "custom-css") {
                echo '<div id="inbound-' . $field_id . '" data-actual="' . $field_id . '" class="inbound-meta-box-label wp-call-to-action-table-header ' . $label_class . $type_class . '"><label for="' . $field_id . '">' . $field['label'] . '</label></div>';
            }

            echo '<div class="wp-call-to-action-option-td inbound-meta-box-option ' . $type_class_option . '" data-field-type="' . $field['type'] . '">';
            switch ($field['type']) {
                case 'description-block':
                    echo '<div id="' . $field_id . '" class="description-block">' . $field['description'] . '</div>';
                    break;
                case 'custom-css':
                    echo '<style type="text/css">' . $field['default'] . '</style>';
                    break;
                /* text */
                case 'colorpicker':
                    if (!$meta) {
                        $meta = $field['default'];
                    }
                    $var_id = (isset($_GET['new_meta_key'])) ? "-" . $_GET['new_meta_key'] : '';
                    echo '<input type="text" class="jpicker" style="background-color:#' . $meta . '" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '" size="5" /><span class="button-primary new-save-lp" data-field-type="text" id="' . $field_id . $var_id . '" style="margin-left:10px; display:none;">Update</span>
                                <div class="lp_tooltip tool_color" title="' . $field['description'] . '"></div>';
                    break;
                case 'datepicker':
                    echo '<div class="jquery-date-picker inbound-datepicker" id="date-picking" data-field-type="text">
                        <span class="datepair" data-language="javascript">
                                    Date: <input type="text" id="date-picker-' . $key . '" class="date start" /></span>
                                    Time: <input id="time-picker-' . $key . '" type="text" class="time time-picker" />
                                    <input type="hidden" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '" class="new-date" value="" >
                                    <p class="description">' . $field['description'] . '</p>
                            </div>';
                    break;
                case 'text':
                    echo '<input type="text" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '" size="30" />
                                <div class="lp_tooltip" title="' . $field['description'] . '"></div>';
                    break;
                case 'number':

                    echo '<input type="number" class="' . $option_class . '" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '" size="20" ' . (isset($field['min']) ? 'min="'.$field['min'].'"' : '' ) . '  ' . (isset($field['max']) ? 'max="'.$field['max'].'"' : '' ) . '  ' . (isset($field['step']) ? 'step="'.$field['step'].'"' : '' ) . '/>
                                <div class="lp_tooltip" title="' . $field['description'] . '"></div>';

                    break;
                /* textarea */
                case 'textarea':
                    echo '<textarea name="' . $field_id . '" id="' . $field_id . '" cols="106" rows="6" style="width: 75%;">' . $meta . '</textarea>
                                <div class="lp_tooltip tool_textarea" title="' . $field['description'] . '"></div>';
                    break;
                /* wysiwyg */
                case 'wysiwyg':
                    echo "<div class='iframe-options iframe-options-" . $field_id . "' id='" . $field['id'] . "'>";
                    wp_editor($meta, $field_id, $settings = array('editor_class' => $field_name));
                    echo '<p class="description">' . $field['description'] . '</p></div>';
                    break;
                /* media */
                case 'media':
                    /*echo 1; exit; */
                    echo '<label for="upload_image" data-field-type="text">';
                    echo '<input name="' . $field_id . '"  id="' . $field_id . '" type="text" size="36" name="upload_image" value="' . $meta . '" />';
                    echo '<input data-field-id="' . $field_id . '"  class="upload_image_button" id="uploader_' . $field_id . '" type="button" value="'.__('Upload Image' , 'inbound-pro' ) .'" />';
                    echo '<p class="description">' . $field['description'] . '</p>';
                    break;
                /* checkbox */
                case 'checkbox':
                    $i = 1;
                    echo "<table class='lp_check_box_table'>";
                    if (!isset($meta)) {
                        $meta = array();
                    } elseif (!is_array($meta)) {
                        $meta = array($meta);
                    }
                    foreach ($field['options'] as $value => $label) {
                        if ($i == 5 || $i == 1) {
                            echo "<tr>";
                            $i = 1;
                        }
                        echo '<td data-field-type="checkbox"><input type="checkbox" name="' . $field_id . '[]" id="' . $field_id . '" value="' . $value . '" ', in_array($value, $meta) ? ' checked="checked"' : '', '/>';
                        echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label></td>';
                        if ($i == 4) {
                            echo "</tr>";
                        }
                        $i++;
                    }
                    echo "</table>";
                    echo '<div class="lp_tooltip tool_checkbox" title="' . $field['description'] . '"></div>';
                    break;
                /* radio */
                case 'radio':
                    foreach ($field['options'] as $value => $label) {
                        /*echo $meta.":".$field_id; */
                        /*echo "<br>"; */
                        echo '<input type="radio" name="' . $field_id . '" id="' . $field_id . '" value="' . $value . '" ', $meta == $value ? ' checked="checked"' : '', '/>';
                        echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label> &nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    echo '<div class="lp_tooltip" title="' . $field['description'] . '"></div>';
                    break;
                /* select */
                case 'dropdown':
                    echo '<select name="' . $field_id . '" id="' . $field_id . '" class="' . $field['id'] . '">';
                    foreach ($field['options'] as $value => $label) {
                        echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="' . $value . '">' . $label . '</option>';
                    }
                    echo '</select><div class="lp_tooltip" title="' . $field['description'] . '"></div>';
                    break;


            }
            echo '</div></div>';
        } /* end foreach */
        echo '</div>'; /* end table */
        /*exit; */
    }


    /**
     * Ajax listener to get template settings html
     */
    public static function ajax_get_template_metabox_html() {
        global $wpdb;

        $current_template = sanitize_text_field($_POST['selected_template']);

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        $args['args']['key'] = $current_template;

        self::display_extended_metabox($post, $args);
        die();
    }


    /**
     * Save Landing Page
     */
    public static function save_landing_page( $landing_page_id ) {
        global $post;


        if ( !isset($post) || $post->post_type !='landing-page' || wp_is_post_revision( $landing_page_id ) ) {
            return;
        }


        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) {
            return;
        }

        $variations = Landing_Pages_Variations::get_variations( $landing_page_id );
        $variation_id = (isset($_REQUEST['lp-variation-id'])) ? intval($_REQUEST['lp-variation-id']) : '0';
        $_SESSION['lp_ab_test_open_variation'] = $variation_id;
        if (!in_array( $variation_id , $variations) ) {
            $variations[] = $variation_id;
        }
        Landing_Pages_Variations::update_variations( $landing_page_id , $variations );

        /* save all post data */
        $ignore_list = array( 'acf' , 'post_status', 'post_type', 'tax_input', 'post_author', 'user_ID', 'post_ID', 'landing-page-myeditor',  'catslist', 'post_title', 'samplepermalinknonce', 'autosavenonce', 'action', 'autosave', 'mm', 'jj', 'aa', 'hh', 'mn', 'ss', '_wp_http_referer', 'lp-variation-id', '_wpnonce', 'originalaction', 'original_post_status', 'referredby', '_wp_original_http_referer', 'meta-box-order-nonce', 'closedpostboxesnonce', 'hidden_post_status', 'hidden_post_password', 'hidden_post_visibility', 'visibility', 'post_password', 'hidden_mm', 'cur_mm', 'hidden_jj', 'cur_jj', 'hidden_aa', 'cur_aa', 'hidden_hh', 'cur_hh', 'hidden_mn', 'cur_mn', 'original_publish', 'save', 'newlanding_page_category', 'newlanding_page_category_parent', '_ajax_nonce-add-landing_page_category', 'lp_lp_custom_fields_nonce', 'post_mime_type', 'ID', 'comment_status', 'ping_status');
        foreach ($_REQUEST as $key => $value) {

            if (in_array( $key , $ignore_list) ) {
                continue;
            }

            if ( $variation_id > 0 && !strstr( $key, "-{$variation_id}")) {
                $key = $key . '-' . $variation_id;
            }

            update_post_meta( $landing_page_id  , $key , $value );
        }

        /* save conversion area */
        if(isset($_REQUEST['landing-page-myeditor'])) {
            $conversion_area = wpautop($_REQUEST['landing-page-myeditor']);
            $conversion_area_key = Landing_Pages_Variations::prepare_input_id( 'lp-conversion-area' , $variation_id );
            update_post_meta( $landing_page_id , $conversion_area_key , $conversion_area);
        }

    }

    /**
     * Sets WPSEO metabox priority to low
     * @return string
     */
    public static function set_wpseo_priority() {
        return 'low';
    }

}


new Landing_Pages_Metaboxes;
