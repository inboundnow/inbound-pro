<?php

class Landing_Pages_Customizer {

    /**
     * initiate class
     */
    public function __construct() {
        self::add_hooks();

        /* Kill admin bar on visual editor preview window */
        if (isset($_GET['cache_bust']) || isset($_GET['iframe_window']) ) {
            show_admin_bar(false);
        }
    }

    /**
     * load hooks and filters
     */
    public static function add_hooks() {
        add_action('wp_before_admin_bar_render', array( __CLASS__ , 'add_costomizer_menu_item_to_admin_bar') );

        /* preview area */
        if (isset($_GET['live-preview-area'])) {
            show_admin_bar( false );
            add_action('admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts_preview' )  );
        }

        /* load landing page edit area */
        if (isset($_GET['frontend']) && $_GET['frontend'] ) {
            add_action('admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts_editor' )  );
        }

        /* Load customizer main split container hooks */
        if (isset($_GET['template-customize']) && $_GET['template-customize'] == 'on') {
            add_action('wp_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts_controller' )  );
            add_filter('wp_head', array( __CLASS__ , 'load_customizer_controller' ) );
        }

        add_filter('redirect_post_location' , array( __CLASS__ , 'redirect_after_save' ) );
    }

    /**
     * Add to edit screen admin view
     */
    public static function add_costomizer_menu_item_to_admin_bar() {

        global $post;
        global $wp_admin_bar;

        if (is_admin() || !isset($post) || $post->post_type != 'landing-page') {
            return;
        }

        $permalink = Landing_Pages_Variations::get_variation_permalink( $post->ID );

        if ( isset($_GET['template-customize']) && $_GET['template-customize'] == 'on') {
            $menu_title = __( 'Turn Off Editor' , 'landing-pages' );
        } else {
            $menu_title = __( 'Launch Visual Editor' , 'landing-pages' );
            $permalink = add_query_arg( array( 'template-customize' => 'on' ) , $permalink );
        }


        $wp_admin_bar->add_menu(array('id' => 'launch-lp-front-end-customizer', 'title' => __($menu_title, 'landing-pages'), 'href' => $permalink));
        $wp_admin_bar->add_menu(array('id' => 'lp-list-pages', 'title' => __("View Landing Page List", 'landing-pages'), 'href' => '/wp-admin/edit.php?post_type=landing-page'));
    }


    /**
     * Enqueue scripts and css for preview side of customizer
     */
    public static function enqueue_scripts_preview() {
        wp_enqueue_script('lp-customizer-load', LANDINGPAGES_URLPATH . 'assets/js/customizer.load.js', array('jquery'));
        echo '<style type="text/css">
                html, html.no-js, html[dir="ltr"] {
                    margin-top: 0px !important;
                }
            </style>';
    }

    /**
     * Enqueue scripts and css for editor side of customizer
     */
    public static function enqueue_scripts_editor() {

        wp_enqueue_style('lp-customizer-admin', LANDINGPAGES_URLPATH . 'assets/css/admin/customizer-edit.css');
        wp_enqueue_script('lp-customizer-admin', LANDINGPAGES_URLPATH . 'assets/js/admin/new-customizer-admin.js');

    }

    /**
     * Enqueue scripts and css for iframe preview side of customizer
     */
    public static function enqueue_scripts_controller() {
        global $post;

        $permalink = get_permalink($post->ID);
        $randomstring = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

        wp_enqueue_script('lp_ab_testing_customizer_js', LANDINGPAGES_URLPATH . 'assets/js/customizer.ab-testing.js', array('jquery'));
        wp_localize_script('lp_ab_testing_customizer_js', 'ab_customizer', array('lp_id' => $post->ID, 'permalink' => $permalink, 'randomstring' => $randomstring));
        wp_enqueue_style('lp_ab_testing_customizer_css', LANDINGPAGES_URLPATH . 'assets/css/customizer-ab-testing.css');

        echo "<style type='text/css'>#variation-list{background:#eaeaea !important; top: 26px !important; height: 35px !important;padding-top: 10px !important;}#wpadminbar {height: 32px !important;}</style>"; /* enqueue styles not firing */

    }

    public static function load_customizer_controller() {

        global $post;

        $permalink = get_permalink($post->ID);

        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
        $variation_id = Landing_Pages_Variations::get_current_variation_id( $post->ID );

        $preview_link = add_query_arg( array( 'lp-variation-id' => $variation_id , 'live-preview-area' => $randomString ) , $permalink );
        $customizer_link = add_query_arg( array( 'lp-variation-id' => $variation_id , 'post' => $post->ID , 'action' => 'edit' , 'frontend' => 'true' ) , admin_url('post.php') );

        do_action('lp_launch_customizer_pre', $post);
        ?>

        <style type="text/css">
            #wpadminbar {
                z-index: 99999999999 !important;
            }

            #lp-live-preview #wpadminbar {
                margin-top: 0px;
            }

            .lp-load-overlay {
                position: absolute;
                z-index: 9999999999 !important;
                z-index: 999999;
                background-color: #000;
                opacity: 0;
                background: -moz-radial-gradient(center, ellipse cover, rgba(0, 0, 0, 0.4) 0, rgba(0, 0, 0, 0.9) 100%);
                background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%, rgba(0, 0, 0, 0.4)), color-stop(100%, rgba(0, 0, 0, 0.9)));
                background: -webkit-radial-gradient(center, ellipse cover, rgba(0, 0, 0, 0.4) 0, rgba(0, 0, 0, 0.9) 100%);
                background: -o-radial-gradient(center, ellipse cover, rgba(0, 0, 0, 0.4) 0, rgba(0, 0, 0, 0.9) 100%);
                background: -ms-radial-gradient(center, ellipse cover, rgba(0, 0, 0, 0.4) 0, rgba(0, 0, 0, 0.9) 100%);
                background: radial-gradient(center, ellipse cover, rgba(0, 0, 0, 0.4) 0, rgba(0, 0, 0, 0.9) 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#66000000', endColorstr='#e6000000', GradientType=1);
                -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
                filter: alpha(opacity=50);

            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                jQuery("#wp-admin-bar-edit a").text("Main Edit Screen");

                setTimeout(function () {
                    jQuery(document).find("#lp-live-preview").contents().find("#wpadminbar").hide()
                    jQuery(document).find("#lp-live-preview").contents().find("html").css("margin-bottom", "-28px");

                }, 2000);
            });

        </script>

        <?php

        echo '<div class="lp-load-overlay" style="top: 0;bottom: 0; left: 0;right: 0;position: fixed;opacity: .8; display:none;"></div><iframe id="lp_customizer_options" src="' . $customizer_link . '" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>';

        echo '<iframe id="lp-live-preview" src="' . $preview_link . '" style="width: 68%; height: 100%; position: fixed; right: 0px; top: 26px; z-index: 999999999; background-color: #eee;
	//background-image: linear-gradient(45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194)), linear-gradient(-45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194));
	//background-size:25px 25px; background-position: initial initial; background-repeat: initial initial;"></iframe>';
        wp_footer();
        exit;
    }

    /**
     * Redirect post location after save
     */
    public static function redirect_after_save($url) {

        if( !isset($_REQUEST['_wp_http_referer']) || !strstr( $_REQUEST['_wp_http_referer'] , 'frontend' ) ) {
            return $url;
        }

        return add_query_arg( array('frontend' => true )  , $url );

    }

}


new Landing_Pages_Customizer;