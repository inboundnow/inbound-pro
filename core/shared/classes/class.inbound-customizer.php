<?php
/**
 * One Customizer to rule them all
 */

class Inbound_Customizer {

    /**
    *   Initiates class CTA_Customizer
    */
    public function __construct() {

        self::load_hooks();

        /* If preview mode in effect then kill admin bar */
        if (isset($_GET['cache_bust'])) {
            show_admin_bar( false );
        }
    }

    /**
    *   Loads hooks and filters
    */
    public static function load_hooks() {


        /* Load popup iframe preview not in customizer
            TODO: Move elsewhere
        */
        if (isset($_GET['inbound_popup_preview']))   {
            /* Enqueue preview window css */
            wp_enqueue_style('inbound_iframe_preview_css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/iframe-preview.css');
            /* Loads Preview Iframe in wp_head */
            add_action('wp_head', array(__CLASS__, 'iframe_preview_window_header' ) );
        }

        /* Load customizer launch */
        if (isset($_GET['inbound-customizer']) && $_GET['inbound-customizer'] === 'true') {
            add_filter('wp_head', array(__CLASS__, 'launch_customizer' ) );
        }

        /* Load only when customizer customizer mode is on */
        if (isset($_GET['inbound-frontend-edit']) && $_GET['inbound-frontend-edit'] === 'true') {
            add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_editor_scripts' ));

        }

        if (isset($_GET['inbound_editor_preview'])) {
            show_admin_bar( false );
            wp_enqueue_style('inbound-preview-iframe-styles', INBOUNDNOW_SHARED_URLPATH . 'assets/css/iframe-preview.css');

        }

    }
    /* loads in admin when customizer on */
    public static function enqueue_editor_scripts() {

        $screen = get_current_screen();
        wp_enqueue_style('inbound-customizer-editor-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/new-customizer-admin.css');
        if ( ( isset($screen) && $screen->post_type != 'wp-call-to-action' ) ){
            return;
        }
        /* TODO combine and rewrite */
        wp_enqueue_script('inbound-customizer-editor-js', INBOUNDNOW_SHARED_URLPATH . 'js/customizer.save.js');
        wp_enqueue_script('inbound-customizer-editor-admin', INBOUNDNOW_SHARED_URLPATH . 'js/admin/new-customizer-admin.js');

    }
    /* TODO @Hudson Standardize meta data across A/B testing post types */
    public static function get_post_variations($id, $post_type) {
            $variations = json_decode( get_post_meta( $id ,'inbound-variations', true), true );
            $variations = ( is_array( $variations ) ) ? $variations : array( 0 => array( 'status' => 'active' ) );

            /* unset unneeded   variation data if $vid is specified */
            if ($vid !== null ) {
                foreach ($variations as $id => $variation) {
                    if ($id != $vid ) {
                        unset($variations[ $id ]);
                    }
                }
            }

            return $variations;
    }
    /* Adds code to Preview Iframe head tag */
    public static function iframe_preview_window_header() {
        /* load custom variation toggles */

    }

    add_filter('admin_body_class', 'add_body_classes');
    public static function add_body_classes($classes) {
            $classes[] = 'inbound-customizer';
            return $classes;
    }

    public static function add_hidden_inputs() {
        global $post, $CTA_Variations;

        if ( !$post || $post->post_type != 'wp-call-to-action' ) {
            return;
        }

        /*  Add hidden param for visual editor */
        if(isset($_REQUEST['inbound-editor']) && $_REQUEST['inbound-editor'] == 'true') {
            echo '<input type="hidden" name="frontend" id="frontend-on" value="true" />';
        }

        /* Get current variation id */
        $vid = CTA_Variations::get_current_variation_id();

        /* Add variation status */
        $variations_status = $CTA_Variations->get_variation_status( $post->ID, $vid );
        echo '<input type="hidden" name="wp-cta-variation-status['.$vid.']" value = "'.$variations_status .'">';

        /* Add variation id */
        echo '<input type="hidden" name="wp-cta-variation-id" id="open_variation" value = "'.$vid .'">';

        /* Add call to action permalink */
    }

    public static function launch_customizer() {

        global $post;

        $page_id = $post->ID;
        $permalink = get_permalink($page_id);

        $random_string = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
        $variation_id = (isset($_GET['variation-id'])) ? $_GET['variation-id'] : '0';

        $params = '?wp-cta-variation-id='.$variation_id.'&cache_bust='.$random_string.'&inbound-preview='.$random_string;

        $preview_link = add_query_arg( array(  'cache_bust' => $random_string, 'inbound-preview' => 'true', 'wmode' => 'opaque'), get_permalink( $page_id ) );
        $preview_link = apply_filters( 'wp_cta_customizer_preview_link', $preview_link );

        $customizer_link = add_query_arg( array( 'wp-cta-variation-id' => $wp_cta_variation, 'action' => 'edit', 'inbound-editor' => 'true' ), admin_url() .'post.php?post='.$page_id );

        wp_enqueue_style('wp_cta_ab_testing_customizer_css', WP_CTA_URLPATH . 'assets/css/customizer-ab-testing.css');
        ?>

        <style type="text/css">
            #wpadminbar {
                z-index: 99999999999 !important;
            }
            #wp-cta-live-preview #wpadminbar {
                margin-top:0px;
            }
            .wp-cta-load-overlay {
                position: absolute;
                z-index: 9999999999 !important;
                z-index: 999999;
                background-color: #000;
                opacity: 0;
                background: -moz-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
                background: -webkit-gradient(radial,center center,0px,center center,100%,color-stop(0%,rgba(0,0,0,0.4)),color-stop(100%,rgba(0,0,0,0.9)));
                background: -webkit-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
                background: -o-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
                background: -ms-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
                background: radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#66000000',endColorstr='#e6000000',GradientType=1);
                -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
                filter: alpha(opacity=50);

            }

            body.customize-support, body {
                background-color: #eee !important;
            background-image: linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)), linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)) !important;
            background-size: 60px 60px !important;
            background-position: 0 0, 30px 30px !important;
            }


        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            jQuery("#wp-admin-bar-edit a").text("Main Edit Screen");

            setTimeout(function() {
                jQuery(document).find("#wp-cta-live-preview").contents().find("#wpadminbar").hide()
                jQuery(document).find("#wp-cta-live-preview").contents().find("html").css("margin-bottom", "-28px");

            }, 2000);
         });

        </script>

        <?php
        global $post;
        global $wp_query;

        $version = $_GET['wp-cta-variation-id'];

        $current_page_id = $wp_query->get_queried_object_id();

        $width = get_post_meta($current_page_id, 'wp_cta_width-'.$version, true);
        $height = get_post_meta($current_page_id, 'wp_cta_height-'.$version, true);
        /*$replace = get_post_meta( 2112, 'wp_cta_global_bt_lists', true); // move to ext */

        $correct_height = self::get_correct_dimensions($height, 'height');
        (!$correct_height) ? $correct_height = 'auto' : $correct_height = $correct_height;
        $correct_width = 'width:100%;';

        ?>
        <?php
        echo '<div class="wp-cta-load-overlay" style="top: 0;bottom: 0; left: 0;right: 0;position: fixed;opacity: .8; display:none;"></div>';
        echo '<table style="width:100%">';
        echo '  <tr>';
        echo '      <td style="width:35%">';
        echo '          <iframe id="wp_cta_customizer_options" src="'.$customizer_link.'" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>';
        echo '      </td>';

        echo '      <td>';
        echo '          <iframe id="wp-cta-live-preview" scrolling="no" src="'.$preview_link.'" style="max-width: 68%; '.$correct_width.' height:1000px; left: 32%; position: fixed;  top: 20%; z-index: 1; border: none; overflow:hidden;
            background-image: linear-gradient(45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194)), linear-gradient(-45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194));
         background-position: initial initial; background-repeat: initial initial;"></iframe>';
        echo '      </td>';
        echo '  </tr>';
        echo '</table>';
        wp_footer();
        exit;
    }

    /**
    *  Looks at user inputed width and height and prepares correct format
    */
    public static function get_correct_dimensions($input, $css_prop) {

        if (preg_match("/px/i", $input)){
           $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
        } else if (preg_match("/%/", $input)) {
           $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
        } else if (preg_match("/em/", $input)) {
           $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
        } else {
           $input = " ".$css_prop.": $input" . "px;";
        }

        return $input;
    }

}

$Inbound_Customizer = new Inbound_Customizer();