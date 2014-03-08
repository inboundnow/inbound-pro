<?php
/*
Plugin Name: Leads Revisit Noticiations
Plugin URI: http://www.inboundnow.com/leads/
Description: Get notified on lead revisits
Author: Inbound Now
Version: 1.3.4
Author URI: http://www.inboundnow.com/
Text Domain: landing-pages
Domain Path: shared/languages/leads/
*/

/**
 * Lead_Revisit_Notifications Class
 */
if (!class_exists('Lead_Revisit_Notifications')) {
class Lead_Revisit_Notifications {
	static $run_addon;

	static function init() {
		self::$run_addon = true;
		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_head', array(__CLASS__, 'print_script'));
		add_action('admin_head',  array(__CLASS__, 'component_test'));
		add_action('wp_ajax_lead_revisit_notifications', array(__CLASS__, 'lead_revisit_notifications' ));
		add_action('wp_ajax_nopriv_lead_revisit_notifications', array(__CLASS__, 'lead_revisit_notifications' ));
	}
	static function handle_shortcode($atts) {
		self::$run_addon = true;
	}
	static function component_test() {
		echo "CORE"; // test
	}

	static function register_script() {
		echo "bye";
		wp_register_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
	}

	static function print_script() {
		if ( ! self::$run_addon ) {
			return;
		} ?>

		<script type="text/javascript">
		// make script and localize for special email routing
		jQuery(document).ready(function($) {
		   var lead_email = $.cookie('wp_lead_email') || false;
		   var run_check = $.cookie('lead_session_expire');
			   if (lead_email && run_check === null) {
				   	jQuery.ajax({
				   	    type: "POST",
				   	    url: ajaxurl,
				   	    data: {
				   	    	lead_email: lead_email,
				   	        action: "lead_revisit_notifications"
				   	    },
				   	    success: function (e) {
				   	       console.log('Lead Revisit Notification Sent');
				   	    }
				   	});
			   }
		 });
		</script>

	<?php wp_print_scripts('my-script');

	}
	static function addon_options(){

	}
	static function route_lead($lead_email){
		// Route lead email to correct person
	}
	/* Ajax call */
	public function lead_revisit_notifications() {
		global $wp, $post;
		$email = (isset($_POST['lead_email'])) ? $_POST['lead_email'] : false;

		// Only proceed if lead exists
		if ($email) {

			$to = 'david@inboundnow.com';
			$subject = 'Hello from my blog!';
			$message = 'Check it out -- my blog is emailing you!';

			$mail = wp_mail($to, $subject, $message);

			if($mail) {
				echo 'Your message has been sent!';
			} else {
				echo 'There was a problem sending your message. Please try again.';
			}

		}
	}
}

Lead_Revisit_Notifications::init();

}