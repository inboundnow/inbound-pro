<?php

/**
 * Class for prompting users with important information related to their Inbound Pro instance
 * @package     InboundPro
 * @subpackage  Notifications
 */

class Inbound_Pro_Notifications {

    /**
     *  Initialize Class
     */
    function __construct() {
        self::load_hooks();
    }


    /**
     *  Load hooks and filters
     */
    public static function load_hooks() {

        /* Load template selector in background */
        add_action('admin_notices', array( __CLASS__ , 'prompt_key_notifications' ) );

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


    /**
     *  Checks to see if Mandril Key is inputed. If it's not then it throws the notice
     */
    public static function prompt_key_notifications() {
        global $post;
        global $inbound_settings;
        global $pagenow;
        global $current_user;

        if (
            isset($inbound_settings['api-key']['api-key'])
                &&
            trim($inbound_settings['api-key']['api-key'])
                ||
            (
                isset($_REQUEST['page'])
                &&
                $_REQUEST['page'] == 'inbound-pro'
            )
        ){
            return false;
        }

        /* only show administrators */
        if( !current_user_can('activate_plugins') ) {
            return;
        }

        $message_id = 'api-key-empty';
        $settings_url = admin_url('admin.php?page=inbound-pro');

        /* check if user viewed message already */
        if (Inbound_Notifications::check_if_viewed($message_id)) {
            return;
        }

        echo '<div class="updated" id="inbound_notice_'.$message_id.'">
				<h2>' . __('Inbound Pro has been installed!', 'inbound-pro') . '</h2>
				 <p style="width:80%;">' . __( 'Inbound Pro requires an API Key to enable automatic updates and subscriber ready components.' , 'inbound-pro') . '</p>
				 <a class="button button-primary button-large" href="'. $settings_url .'" >' . __('Setup API Key', 'inbound-pro') . '</a>
				 <a class="button button-large inbound_dismiss" href="#" id="'.$message_id.'"  data-notification-id="'.$message_id.'" >' . __('Do This Later', 'inbound-pro') . '</a>
				 <br><br>
			  </div>';

        /* echo javascript used to listen for notice closing */
        Inbound_Notifications::javascript_dismiss_notice();

    }

}

new Inbound_Pro_Notifications;
