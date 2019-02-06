<?php

/**
 * Class Inbound_Mailer_Notifications adds admin notifications unique to the Mailer component
 *
 * @package Mailer
 * @subpackage  Notifications
 */

class Inbound_Mailer_Notifications {

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

		/* Add notification ignore listener */
		add_action( 'admin_init' , array( __CLASS__ , 'ignore_notifications' ) );

		/* Load template selector in background */
		add_action('admin_notices', array( __CLASS__ , 'prompt_key_notifications' ) );


		add_action('admin_notices', array( __CLASS__ , 'prompt_rebuild_database' ) );

	}

	/**
	 * listen for the command to disable the mandril send error notifications
	 */
	public static function ignore_notifications() {
		if (!isset($_REQUEST['mailer-disable-notification'])) {
			return;
		}

		global $current_user;
		$user_id = $current_user->ID;

		$ignore_check = get_transient('inbound_pro_ignore_email_errors' , array());

		$ignore_check[] = $user_id;

		set_transient( 'inbound_pro_ignore_email_errors' , array_unique($ignore_check) , 60 * 60 * 24 * 3 );
	}


	/**
	 *  Checks to see if email service Key is inputed. If it's not then it throws the notice
	 */
	public static function prompt_key_notifications() {
		global $post , $inbound_settings;


		if (!isset($post)||$post->post_type!='inbound-email'){
			return false;
		}

		/* Check if key exists */
		$settings_url = Inbound_Mailer_Settings::get_settings_url();
		$email_service = (isset($inbound_settings['mailer']['mail-service'])) ? $inbound_settings['mailer']['mail-service'] : 'wp_mail';

		switch($email_service) {
			case 'sparkpost':

				if ( isset($inbound_settings['mailer']['sparkpost-key']) && $inbound_settings['mailer']['sparkpost-key'] ) {
					return;
				}
				?>
				<div class="updated">
					<p><?php _e( sprintf( 'Email requires a SparkPost API Key. Head to your %s to input your SparkPost API key.' , '<a href="'.$settings_url.'">'.__( 'settings page' , 'inbound-pro' ).'</a>') , 'inbound-email'); ?></p>
				</div>
				<?php
				break;
			case 'sparkpost-eu':

				if ( isset($inbound_settings['mailer']['sparkpost-key']) && $inbound_settings['mailer']['sparkpost-key'] ) {
					return;
				}
				?>
				<div class="updated">
					<p><?php _e( sprintf( 'Email requires a SparkPost API Key. Head to your %s to input your SparkPost API key.' , '<a href="'.$settings_url.'">'.__( 'settings page' , 'inbound-pro' ).'</a>') , 'inbound-email'); ?></p>
				</div>
				<?php
				break;
			case 'wp_mail':

				return;
				break;
			default:
				?>
				<div class="updated">
					<p><?php _e( sprintf( 'An email service is required to send emails. Head to your %s to select a mail service.' , '<a href="'.$settings_url.'">'.__( 'settings page' , 'inbound-pro' ).'</a>') , 'inbound-email'); ?></p>
				</div>
				<?php
				break;
		}

	}

	/**
	 *  Checks to see if email service Key is inputed. If it's not then it throws the notice
	 */
	public static function prompt_rebuild_database() {
		global $post , $wpdb;


		if (!isset($post)||$post->post_type!='inbound-email'){
			return false;
		}

		/* Check if database table exists exists */
		$table_name = $wpdb->prefix.'inbound_email_queue';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			return;
		}
		?>
		<div class="error">
			<p><?php echo sprintf(__('WARNING: Required database table %s not found. %sClick here to rebuild table.%s' ,'inbound-pro') , $table_name, '<a href="' . admin_url('edit.php?post_type=inbound-email&force_upgrade_routines=true') . '">','</a>'); ?></p>
		</div>
		<?php



	}


	/**
	 *  Let user know Mandril is not processing their sends
	 */
	public static function prompt_email_send_error()  {
		global $current_user, $post;
		$user_id = $current_user->ID;


		$errors = Inbound_Options_API::get_option('inbound-email', 'errors-detected', false);

		/* if no error message then return */
		if (!$errors) {
			return;
		}

		$ignore_check = get_transient('inbound_pro_ignore_email_errors' , array());

		if ($ignore_check && in_array( $user_id , $ignore_check ) && (!isset($post) || $post->post_type != 'inbound-email')) {
			return;
		}

		echo '<div class="error">';

		if ((!isset($post) || $post->post_type != 'inbound-email')) {
			echo '<div style="float:right;margin-top:10px;"><a href="?mailer-disable-notification=true" title="'. __('Disable this notification. Note this error message will still appear in the email listing area until all scheduled emails are canceled or the error itself resolves.', 'inbound-pro') . '"><strong>x</strong></a> </div>';
		}

		echo '<p>' . __( sprintf( 'The selected email service is rejecting email send attempts and returning the message below:  <pre>%s</pre>' , $errors) , 'inbound-pro') .'</p>';
		echo '     </div>';

	}

}

new Inbound_Mailer_Notifications;