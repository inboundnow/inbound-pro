<?php

/**
 * Class for loading landing page templates when landing page is called on the frontend.
 * @package LandingPages
 * @subpackage Templates
 */

class Landing_Pages_Template_Switcher {

    /**
     * Initiate class
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     * Load hook and filters
     */
    public static function add_hooks() {
        /* Alter the title for landing page to it's administrative title */
        add_filter('wp_title', array( __CLASS__ , 'display_landing_page_title' ), 9, 2);
        add_filter('the_title', array( __CLASS__ , 'display_landing_page_title' ) , 10, 2);
        add_filter('get_the_title', array( __CLASS__ , 'display_landing_page_title' ), 10, 2);

        /* prepare and display landing page content  */
        add_filter('the_content', array( __CLASS__ , 'display_landing_page_content' ) , 10, 2);
        add_filter('get_the_content', array( __CLASS__ , 'display_landing_page_content' ) , 10, 2);

        /* prepare conversion area if default template */
        add_filter('the_content', array( __CLASS__ , 'display_conversion_area' ) , 20);
        add_filter('get_the_content', array( __CLASS__ , 'display_conversion_area' ), 20);

        /* Switch to correct landing page template */
        add_filter('template_include', array( __CLASS__ , 'switch_template' ), 13);

        /* Load custom CSS and load custom JS */
        add_action('wp_head', array( __CLASS__ , 'load_custom_js_css' ) );

        /* add conversion area shortcode */
        add_shortcode('lp_conversion_area', array( __CLASS__ , 'process_conversion_area_shortcode') );
        add_shortcode('landing-page-conversion', array( __CLASS__ , 'process_conversion_shortcode') );

        /* listen for postback URL conversions */
        add_action( 'init' , array( __CLASS__ , 'process_postback_conversion' ));

        /* Add Custom Class to Landing Page Nav Menu to hide/remove */
        add_filter('wp_nav_menu_args', array( __CLASS__ , 'hide_nav_menu' ) );

        /* strips active theme styling from non default landing pages */
        add_action('wp_print_styles', array( __CLASS__ , 'strip_styles' ), 100);
    }

    /**
     * Return custom Landing Page headline
     */
    public static function display_landing_page_title( $content, $id = null ) {
        global $post;

        if (!isset($post)) {
            return $content;
        }

        if ( ($post->post_type != 'landing-page' || is_admin()) || $id != $post->ID ) {
            return $content;
        }

        return lp_main_headline($post, null, true);
    }

    /**
     * Displays landing page content
     * @param $content
     * @return string
     */
    public static function display_landing_page_content($content) {
        global $post;

        if (!isset($post) || $post->post_type != 'landing-page') {
            return $content;
        }

        $content = Landing_Pages_Variations::get_post_content( $post->ID );
        $content = do_shortcode( $content );
        if (!defined('LANDING_PAGES_WPAUTOP') || LANDING_PAGES_WPAUTOP === TRUE) {
           $content = wpautop($content);
        }
        return $content;
    }

    /**
     * display conversion area
     */
    public static function display_conversion_area($content) {

        if ('landing-page' != get_post_type() || is_admin()) {
            return $content;
        }

        global $post;

        remove_action('the_content', array( __CLASS__ , 'display_conversion_area' ) , 20 );

        $template = Landing_Pages_Variations::get_current_template( $post->ID );

        $my_theme = wp_get_theme($template);

        if ( !$my_theme->exists() &&  $template != 'default') {
            return $content;
        }

        $wrapper_class = "";

        $position = Landing_Pages_Variations::get_conversion_area_placement( $post->ID );
        $conversion_area = lp_conversion_area(null, null, true, true);
        $conversion_area = "<div id='lp_container' class='$wrapper_class'>" . $conversion_area . "</div>";

        if ($position == 'top') {
            $content = $conversion_area . $content;
        } else if ($position == 'bottom') {
            $content = $content . $conversion_area;
        } else if ($position == 'widget') {
            $content = $content;
        } else {
            $conversion_area = str_replace("id='lp_container'", "id='lp_container' class='lp_form_$position' style='float:$position'", $conversion_area);
            $content = $conversion_area . $content;

        }

        return $content;
    }

    /**
     * Detects if landing page & issues the correct template
     */
    public static function switch_template( $template ) {
        global $wp_query, $post, $query_string;

        if (!isset($post) || $post->post_type != "landing-page" || !is_singular("landing-page")) {
            return $template;
        }

        /* nextgen gallery support */
        if (!defined('NGG_DISABLE_FILTER_THE_CONTENT')) {
            define( 'NGG_DISABLE_FILTER_THE_CONTENT' , true );
        }

        $selected_template = Landing_Pages_Variations::get_current_template( $post->ID );

        if (!isset($selected_template) || $selected_template === 'default' ) {
            return $template;
        }

        /* check if inactive theme */
        $my_theme = wp_get_theme( $selected_template );
        if ($my_theme->exists()) {
            return $template;
        }

        /* check if core template first */
        if (file_exists(LANDINGPAGES_PATH . 'templates/' . $selected_template . '/index.php')) {
            return LANDINGPAGES_PATH . 'templates/' . $selected_template . '/index.php';
        }
        /* next check if it is an uploaded template */
        else if (file_exists(LANDINGPAGES_UPLOADS_PATH . $selected_template . '/index.php')) {
            return LANDINGPAGES_UPLOADS_PATH . $selected_template . '/index.php';
        }
        /* next check if it is included with a WordPress theme */
        else if (file_exists(LANDINGPAGES_THEME_TEMPLATES_PATH . $selected_template . '/index.php')) {
            return LANDINGPAGES_THEME_TEMPLATES_PATH . $selected_template . '/index.php';
        }

        return $template;
    }

    /**
     * load custom CSS & JS
     */
    public static function load_custom_js_css() {
        global $post;

        if ( !isset($post) || 'landing-page' != $post->post_type) {
            return;
        }

        $custom_css_name = Landing_Pages_Variations::prepare_input_id('lp-custom-css');
        $custom_js_name =Landing_Pages_Variations::prepare_input_id('lp-custom-js');
        $custom_css = Landing_Pages_Variations::get_custom_css( $post->ID );
        $custom_js = Landing_Pages_Variations::get_custom_js( $post->ID );
        echo "<!-- This site landing page was built with the WordPress Landing Pages plugin - https://www.inboundnow.com/landing-pages/ -->";

        if (!stristr($custom_css, '<style')) {
            echo '<style type="text/css" id="lp_css_custom">' . $custom_css . '</style>';
        } else {
            echo $custom_css;
        }

        if (!stristr($custom_js, '<script') && ( stristr($custom_js, '$.') || stristr($custom_js, 'jQuery') ) ) {
            echo '<script type="text/javascript" id="lp_js_custom">jQuery(document).ready(function($) {
        ' . $custom_js . ' });</script>';
        } else if (!stristr($custom_js, '<script')) {
            echo '<script type="text/javascript" id="lp_js_custom">' . $custom_js . '</script>';
        } else {
            echo $custom_js;
        }
    }


    /**
     *
     * [lp_conversion_area] shortcode support
     *
     */
    public static function process_conversion_area_shortcode($atts, $content = null) {
        extract(shortcode_atts(array('id' => '', 'align' => '' /*'style' => ''*/
        ), $atts));


        $conversion_area = lp_conversion_area($post = null, $content = null, $return = true, $doshortcode = true, $rebuild_attributes = true);

        return $conversion_area;
    }

    /**
     *
     * [landing-page-conversion] shortcode support
     *
     */
    public static function process_conversion_shortcode($atts, $content = null) {
        extract(shortcode_atts(array(
            'id' => '',
            'vid' => '0'
        ), $atts));


        /* check do not track flag */
        $do_not_track = apply_filters('inbound_analytics_stop_track' , false );
        if ( $do_not_track || isset($_SESSION['landing_page_conversions']) && in_array( $id , $_SESSION['landing_page_conversions'] ) )  {
            return;
        }

        Landing_Pages_Variations::record_conversion($id , $vid);

        $_SESSION['landing_page_conversions'][] = $id;

    }

    /**
     * Use postback URL to record conversion for landing pages
     */
    public static function process_postback_conversion($atts, $content = null) {

        if ( !isset($_GET['postback']) ) {
            return;
        }

        if ( !isset($_GET['event']) || $_GET['event'] != 'lp_conversion' ) {
            return;
        }

        $id = $_GET['id'];
        $vid = $_GET['vid'];

        $salt = md5( $id . AUTH_KEY );

        if ( $_GET['salt'] != $salt ) {
            return;
        }

        Landing_Pages_Variations::record_conversion($id , $vid);

        _e('success','landing-pages');
        exit;

    }


    /**
     * Hides navigation menu on default landing page tempaltes
     * @param string $args
     * @return string
     */
    public static function hide_nav_menu($args = '') {
        global $post;

        if ( !isset($post) || $post->post_type != 'landing-page') {
            return $args;
        }


        $template_name = Landing_Pages_Variations::get_current_template( $post->ID );
        if ($template_name != 'default') {
            return $args;
        }

        $nav_status = get_post_meta($post->ID, 'default-lp_hide_nav', true);

        if ($nav_status != 'off' ) {
            return $args;
        }

        if (isset($args['container_class'])) {
            $current_class = " " . $args['container_class'];
        }

        $args['container_class'] = "custom_landing_page_nav{$current_class}";

        $args['echo'] = false;

        return $args;
    }

    /**
     * Utility method for loading popular 3rd party assets into Landing Page
     */
    public static function load_misc_plugin_support() {
        /* WP Featherlight */
        if (class_exists('WP_Featherlight_Scripts')) {
            $wpfl = new WP_Featherlight_Scripts(plugin_dir_url( 'wp-featherlight' ) , '');
            $wpfl->load_css();
        }
    }

    /**
     * Remove all base css from the current active wordpress theme in landing pages
     * currently removes all css from wp_head and re-enqueues the admin bar css.
     */
    public static function strip_styles() {

        if (is_admin() || 'landing-page' != get_post_type() || !is_singular('landing-page')) {
            return;
        }

        global $post;
        $template = Landing_Pages_Variations::get_current_template( $post->ID );

        $my_theme = wp_get_theme($template);

        if ($my_theme->exists() || $template == 'default') {
            return;
        }

        global $wp_styles;

        $registered_scripts = $wp_styles->registered;
        $inbound_white_list = array();
        foreach ($registered_scripts as $handle) {
            if (preg_match("/\/plugins\/leads\//", $handle->src)) {
                /*echo $handle->handle; */
                $inbound_white_list[] = $handle->handle;
            }
            if (preg_match("/\/plugins\/cta\//", $handle->src)) {
                /*echo $handle->handle; */
                $inbound_white_list[] = $handle->handle;
            }
            if (preg_match("/\/plugins\/landing-pages\//", $handle->src)) {
                /*echo $handle->handle; */
                $inbound_white_list[] = $handle->handle;
            }
        }

        $wp_styles->queue = $inbound_white_list;

        wp_enqueue_style('admin-bar');


    }
}


new Landing_Pages_Template_Switcher;


/**
 * Echos or returns main headline
 * @param OBJECT $post
 * @param STRING $headline depreciated
 * @param bool $return
 */
function lp_main_headline($post = null, $headline = null, $return = false) {
    if (!isset($post)) {
        global $post;
    }

    $main_headline = Landing_Pages_Variations::get_main_headline( $post->ID );

    if (!$return) {
        echo $main_headline;
    } else {
        return $main_headline;
    }
}


/**
 * Display conversion area for default template
 * @param OBJECT $post
 * @param STRING $content
 * @param bool $return
 * @param bool $doshortcode
 * @return null
 */
function lp_conversion_area($post = null, $content = null, $return = false, $doshortcode = true) {
    if (!isset($post)) {
        global $post;
    }

    $content = Landing_Pages_Variations::get_conversion_area( $post->ID );
    $wrapper_class = lp_discover_important_wrappers($content);

    if ($doshortcode) {
        $content = do_shortcode($content);
    }

    $content = apply_filters('lp_conversion_area_post', $content, $post);

    if (!$return) {
        $content = str_replace('<p><div id="inbound-form-wrapper"', '<div id="inbound-form-wrapper"', $content);
        $content = preg_replace('/<p[^>]*><\/p[^>]*>/', '', $content); /* remove empty p tags */
        $content = preg_replace('/<\/p>/', '', $content); /* remove last empty p tag */
        echo do_shortcode($content);
    } else {
        return $content;
    }

}


/**
 * Echo or return content area content for default template
 * @param OBJECT $post
 * @param STRING  $content depreciated
 * @param bool $return
 *
 */
function lp_content_area($post = null, $content = null, $return = false) {
    if (!isset($post)) {
        global $post;
    }

    if (!isset($post) && isset($_REQUEST['post'])) {
        $post = get_post(intval($_REQUEST['post']));
    } else if (!isset($post) && isset($_REQUEST['lp_id'])) {
        $post = get_post(intval($_REQUEST['lp_id']));
    }


    $content_area = Landing_Pages_Variations::get_post_content( $post->ID );

    if (!is_admin()) {
        $content_area = apply_filters('the_content', $content_area);
    }

    if (!$return) {
        echo $content_area;
    } else {
        return $content_area;
    }

}


/**
 * Get parent directory of calling template - used by templates
 * @param $path
 * @return mixed
 */
function lp_get_parent_directory($path) {
    return basename($path);
}



/**
 * Improve body class for landing page template
 * @return string
 */
function lp_body_class() {
    global $post;
    global $lp_data;

    $template = Landing_Pages_Variations::get_current_template( $post->ID );
    if ($template) {
        $lp_body_class = "template-" . $template;
        $postid = "page-id-" . get_the_ID();
        echo 'class="';
        echo $lp_body_class . " " . $postid . " wordpress-landing-page";
        echo '"';
    }
    return $lp_body_class;
}


/**
 * Shorthand function for getting a settings value from a landing page variation
 * @param $post
 * @param $key
 * @param $variation_id
 * @return string
 */
function lp_get_value($post, $key, $field_id) {

    if (!isset($post)) {
        return '';
    }

    $return = Landing_Pages_Variations::get_setting_value( $key . '-'. $field_id , $post->ID );

    return do_shortcode($return);

}

/**
 * Generate a dropdown of available landing pages - May be unused
 * @param $select_id
 * @param $post_type
 * @param int $selected
 * @param int $width
 * @param int $height
 * @param int $font_size
 * @param bool $multiple
 */
function lp_generate_drowndown($select_id, $post_type, $selected = 0, $width = 400, $height = 230, $font_size = 13, $multiple = true) {
    $post_type_object = get_post_type_object($post_type);
    $label = $post_type_object->label;

    if ($multiple == true) {
        $multiple = "multiple='multiple'";
    } else {
        $multiple = "";
    }

    $posts = get_posts(array('post_type' => $post_type, 'post_status' => 'publish', 'suppress_filters' => false, 'posts_per_page' => -1));
    echo '<select name="' . $select_id . '" id="' . $select_id . '" class="lp-multiple-select" style="width:' . $width . 'px;height:' . $height . 'px;font-size:' . $font_size . 'px;"  ' . $multiple . '>';
    foreach ($posts as $post) {
        echo '<option value="', $post->ID, '"', $selected == $post->ID ? ' selected="selected"' : '', '>', $post->post_title, '</option>';
    }
    echo '</select>';
}

/**
 * Remove custom fields metaboxes from Landing Pages post type
 */
function lp_in_admin_header() {
    global $post, $wp_meta_boxes;

    if ( !isset($post) || $post->post_type != 'landing-page') {
        return;
    }

    unset($wp_meta_boxes[get_current_screen()->id]['normal']['core']['postcustom']);
}
add_action('in_admin_header', 'lp_in_admin_header');

/**
 * detect gravity forms class names
 * @param $content
 * @return string
 */
function lp_discover_important_wrappers($content) {
    $wrapper_class = "";
    if (strstr($content, 'gform_wrapper')) {
        $wrapper_class = 'gform_wrapper';
    }
    return $wrapper_class;
}

/**
 * If no forms are found in conversion area add tracking class to links
 * @param null $content
 * @param null $wrapper_class
 * @return null|string
 */
function lp_rebuild_attributes($content = null, $wrapper_class = null) {
    if (strstr($content, '<form')) {
        return $content;
    }

    /* Standardize all links */
    $inputs = preg_match_all('/\<a(.*?)\>/s', $content, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $key => $value) {
            if ($key == 0) {
                $new_value = $value;
                $new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/', 'class="$2 inbound-track-link"', $new_value);


                $content = str_replace($value, $new_value, $content);
                break;
            }
        }
    }

    $check_wrap = preg_match_all('/lp_container_noform/s', $content, $check);
    if (empty($check[0])) {
        $content = "<div id='lp_container_noform'  class='$wrapper_class link-click-tracking'>{$content}</div>";
    }

    return $content;
}

/* LEGACY CODE FOR ADDING LANDING PAGE TEMPLATE METABOX SETTINGS TO TEMPLATE METABOX */
function lp_add_option($key, $type, $id, $default = null, $label = null, $description = null, $options = null) {
    switch ($type) {
        case "colorpicker":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'colorpicker', 'default' => $default);
            break;
        case "text":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'text', 'default' => $default);
            break;
        case "license-key":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'inbound-license-key', 'default' => $default, 'slug' => $id);
            break;
        case "textarea":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'textarea', 'default' => $default);
            break;
        case "wysiwyg":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'wysiwyg', 'default' => $default);
            break;
        case "media":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'media', 'default' => $default);
            break;
        case "checkbox":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'checkbox', 'default' => $default, 'options' => $options);
            break;
        case "radio":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'radio', 'default' => $default, 'options' => $options);
            break;
        case "dropdown":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'dropdown', 'default' => $default, 'options' => $options);
            break;
        case "datepicker":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'datepicker', 'default' => $default);
            break;
        case "html":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'html', 'default' => $default);
            break;
        case "custom-css":
            return array('label' => $label, 'description' => $description, 'id' => $id, 'type' => 'turn-off-editor', 'default' => $default /* inline css */
            );
            break;
        case "description-block":
            return array('label' => $label, 'description' => $description, 'id' => $key . '-' . $id, 'type' => 'description-block', 'default' => $default);
            break;
    }
}

/**
 * legacy function to discover current landing page id. Please use Landing_Pages_Variations::get_current_variation_id();
 * @return int
 */
function lp_ab_testing_get_current_variation_id() {
    if (isset($_GET['ab-action']) && is_admin()) {
        return $_SESSION['lp_ab_test_open_variation'];
    }

    if (!isset($_SESSION['lp_ab_test_open_variation']) && !isset($_REQUEST['lp-variation-id'])) {
        $current_variation_id = 0;
    }
    /*echo $_REQUEST['lp-variation-id']; */
    if (isset($_REQUEST['lp-variation-id'])) {
        $_SESSION['lp_ab_test_open_variation'] = intval($_REQUEST['lp-variation-id']);
        $current_variation_id = intval($_REQUEST['lp-variation-id']);
        /*echo "setting session $current_variation_id"; */
    }

    if (isset($_GET['message']) && $_GET['message'] == 1 && isset($_SESSION['lp_ab_test_open_variation'])) {
        $current_variation_id = $_SESSION['lp_ab_test_open_variation'];

        /*echo "here:".$_SESSION['lp_ab_test_open_variation']; */
    }

    if (isset($_GET['ab-action']) && $_GET['ab-action'] == 'delete-variation') {
        $current_variation_id = 0;
        $_SESSION['lp_ab_test_open_variation'] = 0;
    }

    if (!isset($current_variation_id)) $current_variation_id = 0;

    return $current_variation_id;
}


/* LEGACY CALLBACKS -- STILL USED BY SOME OLDER EXTENSIONS AND TEMPLATES */
function lp_list_feature() {
    return null;
}


function lp_global_config() {
    do_action('lp_global_config');
}

if (!function_exists('lp_init')) {
    function lp_init() {
        do_action('lp_init');
    }
}

function lp_head() {
    do_action('lp_head');
}

function lp_footer() {
    do_action('lp_footer');
}
