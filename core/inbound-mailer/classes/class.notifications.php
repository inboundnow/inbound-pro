<?php

/**
*  This class will throw all the correct admin notifications
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
		
		/* Load template selector in background */
		add_action('admin_notices', array( __CLASS__ , 'prompt_mandrill_key' ) );
		
		/* Load template selector in background */
		add_action('admin_notices', array( __CLASS__ , 'prompt_email_send_error' ) );
	}
	
	/**
	*  Checks to see if Mandril Key is inputed. If it's not then it throws the notice
	*/
	public static function prompt_mandrill_key() {
		global $post;
		

		if (!isset($post)||$post->post_type!='inbound-email'){
			return false; 
		}
		
		/* Check if key exists */
		$settings = Inbound_Mailer_Settings::get_settings();
		
		if ( !isset($settings['api_key']) || !$settings['api_key'] ) {
			$settings_url = Inbound_Mailer_Settings::get_settings_url();
			?>
			<div class="updated">
				<p><?php _e( sprintf( 'Email requires a Mandrill API Key with a positive balance. Head to your %s to input your Mandrill API key.' , '<a href="'.$settings_url.'">'.__( 'settings page' , 'inbound-email' ).'</a>') , 'inbound-email'); ?></p>
			</div>
			<?php
		}
	}
	
	/**
	*  Let user know Mandril is not processing their sends
	*/
	public static function prompt_email_send_error() {
		$mandrill_error = Inbound_Options_API::get_option( 'inbound-email' , 'errors-detected' , false );
		if( $mandrill_error ) {
			?>
			<div class="error">
				<p><?php _e( sprintf( 'Mandrill is rejecting email send attempts and returning the message below:  <pre>%s</pre>' , $mandrill_error) , 'inbound-email'); ?></p>
			</div>
			<?php
		}
	}
	
}

new Inbound_Mailer_Notifications;