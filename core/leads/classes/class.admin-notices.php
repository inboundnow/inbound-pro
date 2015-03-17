<?php

/**
 * Provides a place for miscellaneous admin notices to be defined
 *
 * @package     Leads
 * @subpackage  Admin Notices

*/

if (!class_exists('Leads_Admin_Notices')) {

	class Leads_Admin_Notices {
		
		/**
		 *  Initiate class
		 */
		function __construct() {
			add_action( 'admin_notices', array( __CLASS__ , 'define_notices') );
		}
		
		public static function define_notices() {
		
			if ( isset( $_GET['inbound-message'] ) && 'user-id-does-not-exits' == $_GET['inbound-message'] ) {
				self::throw_notice( __( 'User ID provided does not exist.', 'leads' ) , 'error' );
			}
			
			if ( isset( $_GET['inbound-message'] ) && 'api-key-generated' == $_GET['inbound-message'] ) {
				self::throw_notice( __( 'API keys successfully generated.', 'leads' ) , 'updated' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-exists' == $_GET['inbound-message'] ) {
				self::throw_notice(  __( 'The specified user already has API keys.', 'leads' ), 'error' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-regenerated' == $_GET['inbound-message']  ) {
				self::throw_notice(  __( 'API keys successfully regenerated.', 'leads' ), 'updated' );
			}

			if ( isset( $_GET['inbound-message'] ) && 'api-key-revoked' == $_GET['inbound-message'] ) {
				self::throw_notice(  __( 'API keys successfully revoked.', 'leads' ), 'updated' );
			}
		}
		
		public static function throw_notice( $message , $type = 'updated' ) {
			?>
			<div class="<?php echo $type; ?>">
				<p><?php echo $message ?></p>
			</div>
			<?php
		}
	
	}
	
	$Leads_Admin_Notices = new Leads_Admin_Notices();

}