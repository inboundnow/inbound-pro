<?php
/**
 * - Editor
 * - Preview
 * - Parent ( Customizer )
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

        /* Load popup iframe preview not in customizer */
        /* TODO: Move elsewhere */
        if (isset($_GET['inbound_popup_preview']))  {
            /* Enqueue Scripts  */
            add_action( 'admin_enqueue_scripts', array(__CLASS__,'popup_preview_scripts'));
        }

        /* Load customizer Parent Window. 'inbound-editor' & 'inbound-preview' live inside */
        if (isset($_GET['inbound-customizer']) && $_GET['inbound-customizer']=='on') {
            add_filter('wp_head', array(__CLASS__, 'launch_customizer'));
        } else {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'customizer_off_parent_scripts'));
        }

        /* Load customizer editor */
        if (isset($_GET['inbound-editor']) && $_GET['inbound-editor'] === 'true') {
            add_action('admin_enqueue_scripts', array(__CLASS__, 'customizer_editor_scripts'));
            add_filter('admin_body_class', array(__CLASS__, 'add_editor_body_class'));
            /* Add hidden inputs */
            add_action( 'edit_form_after_title', array(__CLASS__, 'add_hidden_inputs'));
        }

        /* Load customizer preview */
        if (isset($_GET['inbound-preview'])) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'customizer_preview_scripts'));
            add_filter('acf/load_value', array(__CLASS__, 'filter_acf_load_field'), 12, 3 );
        }

        add_filter('redirect_post_location', array(__CLASS__,'redirect_after_save'));

    }

    public static function filter_acf_load_field( $value, $post_id, $field ) {
        // make filter magic happen here...

        if(isset($field) && isset($field['type'])) {

            if($field['type'] === "text" || $field['type'] === "wysiwyg") {
            //*
            //print_r($field); exit;
                $style = "margin: 0 !important;
                        padding: 0 !important;
                        border: 0 !important;
                        outline: 0 !important;
                        color: inherit !important;
                        font-weight: inherit !important;
                        font-style: inherit !important;
                        font-size: 100% !important;
                        font-family: inherit !important;
                        visibility:visible !important;
                        vertical-align: baseline !important;";
                $new_value = "<span style='$style' class='wrap-this' data-key='".$field['key']."'>";
                $new_value .= $value;
                //print_r($field);
                //echo "<pre>";
                //print_r($value);
                $new_value .= "</span>";

                return $new_value;
            }
            /**/

        }
        return $value;
    }


    /* Load Scripts for Iframe Popup Preview Window */
    public static function popup_preview_scripts() {
        wp_enqueue_style('inbound-iframe-popup-preview', INBOUNDNOW_SHARED_URLPATH . 'assets/css/iframe-preview.css');
    }
    /* Load Scripts for Preview Window */
    public static function customizer_preview_scripts() {
        show_admin_bar(false);
        wp_enqueue_style('inbound-customizer-preview-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/customizer-preview.css');
        wp_enqueue_script('inbound-customizer-preview-js', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-preview.js' , array() , false , true );

    }
    /* Load Scripts for Editor Window */
    public static function customizer_editor_scripts() {
        wp_enqueue_script('inbound-customizer-editor-js', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-editor.js' , array() , false , true );
        wp_enqueue_style('inbound-customizer-editor-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/customizer-editor.css');
        //wp_enqueue_style('cta-customizer-admin', WP_CTA_URLPATH . 'assets/css/new-customizer-admin.css');

    }
    /* Add customizer class to body for Editor Window */
    public static function add_editor_body_class($classes) {
            global $post;
            $post_type = get_post_type( $post->ID );
            $classes .= 'inbound-customizer';
            return $classes;
    }
    /* Keep Customizer Active on post save */
    public static function add_hidden_inputs() {

        if((isset($_REQUEST['inbound-editor']) && $_REQUEST['inbound-editor'] === 'true')
            || isset($_GET['inbound-editor']) && $_GET['inbound-editor'] === 'true' ) {
            echo '<input type="hidden" name="inbound-editor" id="inbound-editor-status" value="true" />';
        }
    }

    /* Part of popup iframe */
    public static function toggle_between_variations() {
        /* Way to toggle between Variations */
    }

    /**
     * Load scripts to modify the 'customize' link
     */
    public static function customizer_off_parent_scripts() {
        global $post;

        if (!isset($post) || !in_array( $post->post_type , array('inbound-email','landing-page','wp-call-to-action'))) {
            return;
        }

        wp_enqueue_script('inbound-customizer-parent-js', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-off-parent.js' , array() , false , true );
        wp_localize_script(
            'inbound-customizer-parent-js' ,
            'customizer_off',
            array(
                'launch_visual_editor' =>__('Launch Visual Editor' , 'inbound-pro') ,
                'url' => add_query_arg( array('inbound-customizer'=> 'on' ) , get_permalink($post->ID))
            )
        );
    }

    /* cta specific */
    public static function launch_customizer() {
        global $post;

        $post_id = $post->ID;
        $post_type = $post->post_type;
        $permalink = get_permalink($post_id);

        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

        if($post_type === "wp-call-to-action") {
            $syntax = 'wp-cta-variation-id';
            $vid = (isset($_GET['wp-cta-variation-id'])) ? intval($_GET['wp-cta-variation-id']) : '0';
        } elseif ($post_type === "landing-page") {
            $syntax = 'lp-variation-id';
            $vid = (isset($_GET['lp-variation-id'])) ? intval($_GET['lp-variation-id']) : '0';
            /* Fix email post type */
        } elseif ($post_type === "email") {
            $syntax = 'email-variation-id';
            $vid = (isset($_GET['wp-cta-variation-id'])) ? intval($_GET['wp-cta-variation-id']) : '0';
        } else {
            $syntax = 'na';
            $vid = '0';
        }

        $vid = intval($vid);

        $preview_link = add_query_arg(
                array(  'cache_bust' => $randomString,
                        $syntax => $vid,
                        'inbound-preview' => 'true',
                        'wmode' => 'opaque'),
                $permalink);


        $customizer_link = add_query_arg(
               array( $syntax => $vid,
                      'action' => 'edit',
                      'inbound-editor' => 'true' ),
                      admin_url() .'post.php?post='.$post_id );


        ?>

        <script type='text/javascript' src='<?php echo INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-parent.js';?>'></script>
        <link rel='stylesheet'  href='<?php echo INBOUNDNOW_SHARED_URLPATH . 'assets/css/customizer-parent.css';?>' type='text/css' media='all' />
        </head>
        <!-- http://stackoverflow.com/questions/7816372/make-iframes-resizable-dynamically -->
        <body class="<?php echo $post_type; ?>">
            <div id="inbound-customizer-overlay" class="wp-cta-load-overlay"
            style="display:none;">
                <div class="inbound-loading">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="220" height="220" fill="white">
                  <circle transform="translate(8 0)" cx="0" cy="16" r="0">
                    <animate attributeName="r" values="0; 4; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0"
                      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
                  </circle>
                  <circle transform="translate(16 0)" cx="0" cy="16" r="0">
                    <animate attributeName="r" values="0; 4; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0.3"
                      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
                  </circle>
                  <circle transform="translate(24 0)" cx="0" cy="16" r="0">
                    <animate attributeName="r" values="0; 4; 0; 0" dur="1.2s" repeatCount="indefinite" begin="0.6"
                      keytimes="0;0.2;0.7;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.6 0.4 0.8;0.2 0.6 0.4 0.8" calcMode="spline" />
                  </circle>
                </svg>
                </div>
            </div>

            <table style="width:100%">
                <tr>
                    <td style="width:35%">
                        <iframe id="wp_cta_customizer_options" class="inbound-customizer-editor"
                        src="<?php echo $customizer_link;?>" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>
                    </td>

                    <td style="width:55%">
                        <iframe id="wp-cta-live-preview" class="inbound-customizer-preview" scrolling="auto" src="<?php echo $preview_link; ?>"></iframe>
                    </td>
                </tr>
            </table>

        <?php wp_footer(); ?>
        </body>
        <?php exit;
    }

    public static function redirect_after_save($url) {

        $ref = sanitize_text_field($_REQUEST['_wp_http_referer']);
        if( !isset($ref) || !strstr($ref, 'inbound-editor') || strstr($ref, 'inbound-editor=false')) {
            return $url;
        }

        return add_query_arg(array('inbound-editor' => 'true'), $url );

    }

}

$Inbound_Customizer = new Inbound_Customizer();