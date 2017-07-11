<?php

/**
 * Class for adding admin notices to Call to Action sections only
 * @package CTA
 * @subpackage Notices
 */


class CTA_Admin_Notices {

    public function __construct() {
        self::add_hooks();
    }


    public static function add_hooks() {
        /* add_action('admin_notices', array( __CLASS__, 'dont_install_call_to_action_templates_here')); */
        add_action('admin_notices', array( __CLASS__, 'get_more_templates_notice' ) );
    }

    /**
     * Persistant message to not install call to action themes at templates.
     */
    public static function dont_install_call_to_action_templates_here() {

        /* only show administrators */
        if( !current_user_can('activate_plugins') ) {
            return;
        }

        $screen = get_current_screen();

        if( $screen->id === 'themes' ||
            $screen->id === 'theme-install' ||
            $screen->id === 'update' && isset($_GET['action']) && $_GET['action'] === "upload-theme"
        ) {
            $message_id = 'cta-installation';

            /* check if user viewed message already */
            if (self::check_if_viewed($message_id)) {
                return;
            }

            $doc = 'http://docs.inboundnow.com/guide/installing-new-templates/';
            $link = admin_url( 'edit.php?post_type=wp-call-to-action&page=wp_cta_templates_upload' );

            ?>
            <div class="error" style="margin-bottom:10px;"  id="inbound_notice_<?php echo $message_id; ?>">
                <h3 style='font-weight:normal; margin-bottom:0px;padding-bottom:0px;'>
                    <strong>
                        <?php _e( 'Warning to Call to Action users:' , 'inbound-pro' ); ?>
                    </strong>
                </h3>
                <p style='font-weight:normal; margin-top:0px;margin-bottom:0px;'>
                    <?php echo "Call to action templates need to be installed in <strong><a href='".$link."'>Call to Action</a> > <a href='".$link."'>Manage templates area</a></strong>"; ?>
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

        if (!isset($_GET["page"])) {
            return;
        }

        /* only show administrators */
        if( !current_user_can('activate_plugins') ) {
            return;
        }

        if ( $_GET["page"] == "wp_cta_manage_templates" )  {
           ?>
            <div id="more-templates-button" style="display:none;display:inline;">
                <a target="_blank" href="https://www.inboundnow.com/marketplace/?show=cta" class="button new-lp-button button-primary button-large"><?php _e( 'Open Marketplace' , 'inbound-pro' ); ?></a>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var moretemp = jQuery("#more-templates-button");
                    jQuery(".wrap h2").append(moretemp);
                    jQuery(moretemp).show();
                });
            </script>
            <?php
        }
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


    /**
     * check if user has viewed and dismissed cta
     * @param $notificaiton_id
     */
    public static function check_if_viewed( $notificaiton_id ) {
        global $current_user;

        $user_id = $current_user->ID;

        return get_user_meta($user_id, 'inbound_notification_' . $notificaiton_id ) ;
    }
}


new CTA_Admin_Notices;

