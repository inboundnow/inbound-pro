<?php

/**
 * Class provides a place for miscellaneous admin notices to be defined
 *
 * @package Leads
 * @subpackage  Notifications
*/

if (!class_exists('Leads_Admin_Notices')) {

	class Leads_Admin_Notices {

		function __construct() {
			add_action( 'admin_init', array( __CLASS__ , 'dismiss_notices') );
			add_action( 'admin_notices', array( __CLASS__ , 'define_notices') );
		}

		public static function dismiss_notices() {
			global $current_user;
			$user_id = $current_user->ID;

			/* handlers for stop notifications */
			if ( isset($_GET['leads_user_message_ignore']) && '0' == $_GET['leads_user_message_ignore'] ) {
				add_user_meta($user_id, 'leads_user_message_ignore', 'true', true);
			}
		}


		public static function define_notices() {
			global $post_type, $pagenow , $current_user;
			$user_id = $current_user->ID;

			if ( isset( $_GET['inbound-message'] ) && 'user-id-does-not-exits' == $_GET['inbound-message'] ) {
				self::throw_notice( __( 'User ID provided does not exist.', 'inbound-pro' ) , 'error' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-generated' == $_GET['inbound-message'] ) {
				self::throw_notice( __( 'API keys successfully generated.', 'inbound-pro' ) , 'updated' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-exists' == $_GET['inbound-message'] ) {
				self::throw_notice(  __( 'The specified user already has API keys.', 'inbound-pro' ), 'error' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-regenerated' == $_GET['inbound-message']  ) {
				self::throw_notice(  __( 'API keys successfully regenerated.', 'inbound-pro' ), 'updated' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-revoked' == $_GET['inbound-message'] ) {
				self::throw_notice(  __( 'API keys successfully revoked.', 'inbound-pro' ), 'updated' );
			}

			if ($pagenow == 'edit.php' && $post_type == 'wp-lead' && isset($_REQUEST['exported']) && (int)$_REQUEST['exported']) {
				$message = sprintf(_n('Lead exported.', '%s lead exported.', $_REQUEST['exported']), number_format_i18n($_REQUEST['exported']));
				self::throw_notice(   $message , 'updated' );
			}
			if ($pagenow == 'edit.php' && $post_type == 'wp-lead' && isset($_REQUEST['added']) && (int)$_REQUEST['added']) {
				$message = sprintf(_n('Lead Added.', '%s leads added to list.', $_REQUEST['added']), number_format_i18n($_REQUEST['added']));
				self::throw_notice(   $message , 'updated' );
			}

			/*
			if ( ! get_user_meta($user_id, 'leads_user_message_ignore') ) {
				echo '<div class="updated">';
				echo "<a style='float:right;color:red; margin-top:10px;' href='?leads_user_message_ignore=0'>Dismiss This</a>";
				echo "<h2>Attention Leads users</h2><p>The email templating system, <a href='http://www.screencast.com/t/Z80uAWrvD'>seen here</a>, has been depricated in preparation for our improved email tool (<a href='http://www.inboundnow.com/automation/'>coming soon</a>)<br><br> If you used the email templating features to customize email responses or customize core WordPress email templates you can restore your setup with this additional wordpress plugin:</p>
        		<p><a href='https://wordpress.org/plugins/leads-edit-core-email-templates/'>https://wordpress.org/plugins/leads-edit-core-email-templates/</a> - this will not be supported once the new email tool is out</p>";
				echo "<a style='margin-bottom:10px;' class='button button-primary button-large' href='?leads_user_message_ignore=0'>Got it. Dismiss this</a>";
				echo "</div>";
			}
			*/


		}

		public static function throw_notice( $message , $type = 'updated' ) {
			?>
			<div class="<?php echo $type; ?>">
				<p><?php echo $message ?></p>
			</div>
			<?php
		}

	}


	new Leads_Admin_Notices();

}