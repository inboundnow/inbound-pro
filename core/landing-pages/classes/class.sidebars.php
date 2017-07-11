<?php

/**
 * Class sets up landing page sidebar for holding conversion areas when 'default' template is selected.
 * @package LandingPages
 * @subpackage Sidebars
 */

class Landing_Pages_Sidebars {

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
        add_action('init', array( __CLASS__ , 'register_sidebars' ) , 100 );
        add_action('wp_head', array( __CLASS__ , 'replace_sidebars' ) );
    }

    /**
     * Register sidebars
     */
    public static function register_sidebars() {
        if (function_exists('register_sidebar')) {
            register_sidebar( array(
                'id' => 'lp_sidebar',
                'name' => __('Landing Pages Sidebar', 'landing-pages'),
                'description' => __('Landing Pages Sidebar Area: For default and native theme templates only.', 'landing-pages'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
                'priority' => 10
            ));
        }
    }

    /**
     * Replaces default template's side bar with landing page sidebar
     */
    public static function replace_sidebars() {

        global $_wp_sidebars_widgets, $post, $wp_registered_sidebars, $wp_registered_widgets;

        if ( !isset($post) || $post->post_type != 'landing-page') {
            return;
        }

        $whitelist = array('sidebar-1','sidebar-left','primary','default','blog','sidebar-right','blog-sidebar','left-sidebar','right-sidebar');

        /* get correct registered widget */
        $registered_widget_id = 'id_lp_conversion_area_widget-1';
        foreach ($wp_registered_widgets as $key => $array ) {
            if (strstr($key , 'id_lp_conversion_area_widget')) {
                $registered_widget_id = $key;
                break;
            }
        }

        if (!is_active_sidebar('lp_sidebar')) {
            $active_widgets = get_option('sidebars_widgets');

            if (!isset($active_widgets['lp_sidebar']) || !$active_widgets['lp_sidebar'] ) {
                $active_widgets['lp_sidebar'] = array($registered_widget_id);
                $_wp_sidebars_widgets['lp_sidebar'] = $active_widgets['lp_sidebar'];
                update_option('sidebars_widgets', $active_widgets);
            }
        }


        $count = 0;
        $found = 0;
        foreach ($_wp_sidebars_widgets as $key => $val) {

            foreach ($whitelist as $item) {

                if (strpos($key, $item) !== FALSE || $key == 'sidebar') {
                    $_wp_sidebars_widgets['wp_inactive_widgets'] = array();
                    $_wp_sidebars_widgets[$key] = $_wp_sidebars_widgets['lp_sidebar'];
                    $found = 1;
                }
            }
            if (!$found && $count===0) {
                $_wp_sidebars_widgets[$key] = $_wp_sidebars_widgets['lp_sidebar'];
            }
            $count++;
        }

        /* error_log(print_r($wp_registered_widgets,true)); */
        /* error_log(print_r($_wp_sidebars_widgets,true)); */


    }
}

new Landing_Pages_Sidebars;