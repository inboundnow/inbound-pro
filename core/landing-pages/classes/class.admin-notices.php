<?php

/**
 * Class for defining and displaying admin notices to users. Only displays notices in areas related to Landing Pages plugin.
 * @package LandingPages
 * @subpackage Notices
 */

class Landing_Pages_Admin_Notices {

    public function __construct() {
        self::add_hooks();
    }


    public static function add_hooks() {
        add_action('admin_notices', array( __CLASS__, 'dont_install_landing_page_templates_here'));
        add_action('admin_notices', array( __CLASS__, 'get_more_templates_notice' ) );
        add_action('admin_notices', array( __CLASS__, 'permalink_structure_notice' ) );
    }

    /**
     * Persistant message to not install landing page themes at templates.
     */
    public static function dont_install_landing_page_templates_here() {

        $screen = get_current_screen();

        if( $screen->id === 'themes' ||
            $screen->id === 'theme-install' ||
            $screen->id === 'update' && isset($_GET['action']) && $_GET['action'] === "upload-theme"
        ) {
            /* only show administrators */
            if( !current_user_can('activate_plugins') ) {
                return;
            }

            $message_id = 'landing-page-installation';

            if (is_plugin_active('landing-pages/landing-pages.php')) {
                $lp = true;
                $message_id = 'landing-page-installation';
            }

            if (is_plugin_active('cta/calls-to-action.php')) {
                $cta = true;
                $message_id = 'cta-installation';
            }

            /* check if user viewed message already */
            if (self::check_if_viewed($message_id)) {
                return;
            }

            $doc = 'http://docs.inboundnow.com/guide/installing-new-templates/';
            $link = admin_url( 'edit.php?post_type=landing-page&page=lp_templates_upload' );


            ?>
            <div class="error" style="margin-bottom:10px;"  id="inbound_notice_<?php echo $message_id; ?>">
                <h3 style='font-weight:normal; margin-bottom:0px;padding-bottom:0px;'>
                    <strong>
                        <?php _e( 'Attention Landing Page Users:' , 'inbound-pro' ); ?>
                    </strong>
                </h3>
                <p style='font-weight:normal; margin-top:0px;margin-bottom:0px;'><?php _e( sprintf( 'If you are trying to install a <strong>landing page template</strong> from Inbound Now, %s Please Follow these instructions%s' , '<a href=\'http://docs.inboundnow.com/guide/installing-new-templates/\' target=\'_blank\'>' , '</a>' ) , 'inbound-pro' ); ?>
                    <br>
                    <?php echo "Landing page templates need to be installed <a href='".$link."'>here</a> in the <strong><a href='".$link."'>Landing pages</a> > <a href='".$link."'>Manage templates area</a></strong>"; ?>
                </p>
                <a class="button button-large inbound_dismiss" href="#" id="<?php echo $message_id; ?>"  data-notification-id="<?php echo $message_id; ?>" ><?php _e('Dismiss','inbound-pro'); ?></a>
                <br><br>
            </div>
            <?php

            /* echo javascript used to listen for notice closing */
            self::javascript_dismiss_notice();
        }
    }


    /**
     * Call to action to download more templates
     */
    public static function get_more_templates_notice() {
        global $pagenow;
        $page_string = isset($_GET["page"]) ? $_GET["page"] : "null";
        if ((($pagenow == 'edit.php') && ($page_string == "lp_manage_templates")) || (($pagenow == "post-new.php") && (isset($_GET['post_type']) && $_GET['post_type'] == "landing-page"))) {

            /* only show administrators */
            if( !current_user_can('activate_plugins') ) {
                return;
            }

            ?>
            <div id="more-templates-button" style="display:none;">
                <a target="_blank" href="https://www.inboundnow.com/marketplace/?show=landing-pages" class="button new-lp-button button-primary button-large"><?php _e( 'Download Additional Landing Page Templates' , 'inbound-pro' ); ?></a>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var moretemp = jQuery("#more-templates-button");
                    jQuery("#bulk_actions").prepend(moretemp);
                    jQuery(".lp-selection-heading h1").append(moretemp);
                    jQuery(".lp-selection-heading #more-templates").css("float","right");
                    jQuery(moretemp).show();
                });
            </script>
            <?php
        }
    }


    /**
     * Notice to tell people that a permalink structure besides default must be selected to enable split testing
     */
    public static function permalink_structure_notice(){
        global $pagenow;

        if ( !get_option('permalink_structure') ) {

            /* only show administrators */
            if( !current_user_can('activate_plugins') ) {
                return;
            }
            ?>
            <div class="error">
                <p>
                    <?php _e( 'We\'ve noticed that your permalink settings are set to the default setting. Landing Page varation roation is not possible on this setting. To enable roation please go into Settings->Permalinks and update them to a different format.' , 'inbound-pro' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Notice to tell people that variation A needs to be save first
     */
    public static function acf5_required(){
        global $post;

        $screen = get_current_screen();

        if ( !isset($post) || $screen->id == 'landing-pages' ||$screen->id == 'edit-landing-page' ||  $post->post_status !='publish' ) {
            return;
        }

        $extension_data = Landing_Pages_Load_Extensions::get_extended_data();;
        $current_template = Landing_Pages_Variations::get_current_template($post->ID);

        if ( defined('ACF_PRO') || !isset($extension_data[$current_template]['info']['data_type']) || $extension_data[$current_template]['info']['data_type'] != 'acf5' ) {
            return;
        }

        ?>

        <div class="error">
            <p>
                <?php echo sprintf(__('This landing page template requires %sInbound Pro Plugin + active subscription%s or the  %sInbound Premium Template Support Extension%s to operate. Please download the best available option and activate it as a plugin to continue working with this template.', 'landing-pages'), '<a href="https://www.inboundnow.com/pricing/">', '</a>', '<a href="https://www.inboundnow.com/account/">', '</a>'); ?>
            </p>
        </div>
        <?php

    }


    /**
     * check if user has viewed and dismissed cta
     * @param $notificaiton_id
     */
    public static function check_if_viewed( $notificaiton_id ) {
        global $current_user;

        $user_id = $current_user->ID;

        return get_user_meta($user_id, 'inbound_notification_' . $notificaiton_id ) ;
    }


    public static function javascript_dismiss_notice() {
        global $current_user;

        $user_id = $current_user->ID;
        ?>
        <script type="text/javascript">
            jQuery( document ).ready(function() {

                jQuery('body').on('click' , '.inbound_dismiss' , function() {

                    var notification_id = jQuery( this ).data('notification-id');

                    jQuery('#inbound_notice_' + notification_id).hide();

                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        context: this,
                        data: {
                            action: 'inbound_dismiss_ajax',
                            notification_id: notification_id,
                            user_id: '<?php echo $user_id; ?>'
                        },

                        success: function (data) {
                        },

                        error: function (MLHttpRequest, textStatus, errorThrown) {
                            alert("Ajax not enabled");
                        }
                    });
                })

            });
        </script>
        <?php
    }

}

new Landing_Pages_Admin_Notices;