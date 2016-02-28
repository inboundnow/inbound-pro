<?php



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

        $original_widgets = $_wp_sidebars_widgets;

        if (!is_active_sidebar('lp_sidebar')) {
            $active_widgets = get_option('sidebars_widgets');
            $active_widgets['lp_sidebar'] = array('0', 'id_lp_conversion_area_widget-1');
            update_option('sidebars_widgets', $active_widgets);
        }

        $stop = 0;
        foreach ($original_widgets as $key => $val) {

            if (stristr($key, 'header') || stristr($key, 'footer') || stristr($key, 'lp_sidebar') || stristr($key, 'wp_inactive_widgets') || stristr($key, 'wp_inactive_widgets') || stristr($key, 'array_version')) {

            } else if (strstr($key, 'secondary')) {
                unset($_wp_sidebars_widgets[$key]);
            } else if (isset($_wp_sidebars_widgets['lp_sidebar'])) {
                $_wp_sidebars_widgets[$key] = $_wp_sidebars_widgets['lp_sidebar'];
                $stop = 1;
            }
        }


    }
}

new Landing_Pages_Sidebars;