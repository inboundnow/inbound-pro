<?php
/**
 * API Key Generation
 *
 * @package     Leads
 * @subpackage  Inbound API
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }


if (!class_exists('Inbound_API_Keys_Generation')) {

	/**
	 * Inbound_API_Keys_Generation Class
	 *
	 * Adds listeners and processors for api generation,regeneration, and revocation
	 *
	 */
	class Inbound_API_Keys_Generation {


		/**
		 * Initiate Class
		 */
		public function __construct() {

			/* Listen for key generation commands and execute them */
			$this->generate_keys();

		}

		/**
		 *  Listens for key generation commands issued from the 'API Keys' tab in Leads's Global Settings
		 */
		public function generate_keys() {
			if (!isset($_REQUEST['inbound_action'])) {
				return;
			}

			/* Get User ID */
			if( isset( $_REQUEST['user_id'] ) ) {

				$userdata   = get_user_by( 'id', intval($_REQUEST['user_id']) );
				if (isset($userdata->ID)) {
					$user_id    = $userdata->ID;
				} else {
					wp_redirect( add_query_arg( 'inbound-message', 'user-id-does-not-exits', 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ) ); exit();
				}
			}


			switch( $_REQUEST['inbound_action'] ) {
				case 'generate-api-keys':
					if( $this->generate_api_key( $user_id ) ) {
						delete_transient( 'inbound-total-api-keys' );
						wp_redirect( add_query_arg( 'inbound-message', 'api-key-generated', 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ) ); exit();
					} else {
						wp_redirect( add_query_arg( 'inbound-message', 'api-key-exists', 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ) ); exit();
					}
					break;
				case 'regenerate-api-keys':
					$this->generate_api_key( $user_id, true );
					delete_transient( 'inbound-total-api-keys' );
					wp_redirect( add_query_arg( 'inbound-message', 'api-key-regenerated', 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ) ); exit();
					break;
				case 'revoke-api-keys':
					$this->revoke_api_key( $user_id );
					delete_transient( 'inbound-total-api-keys' );
					wp_redirect( add_query_arg( 'inbound-message', 'api-key-revoked', 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ) ); exit();
					break;
				default;
					break;
			}
		}

		/**
		 * Generate new API keys for a user
		 *
		 * @access public
		 * @param INT $user_id id of user
		 * @param BOOL $regenerate toggle regenerate command
		 * @return bool
		 */
		public function generate_api_key( $user_id, $regenerate = false ) {
			$user = get_userdata( $user_id );

			if ( empty( $user->inbound_user_public_key ) ) {
				update_user_meta( $user_id, 'inbound_user_public_key', $this->generate_public_key( $user->user_email ) );
				update_user_meta( $user_id, 'inbound_user_secret_key', $this->generate_private_key( $user->ID ) );
			} elseif( $regenerate == true ) {
				$this->revoke_api_key( $user->ID );
				update_user_meta( $user_id, 'inbound_user_public_key', $this->generate_public_key( $user->user_email ) );
				update_user_meta( $user_id, 'inbound_user_secret_key', $this->generate_private_key( $user->ID ) );
			} else {
				return false;
			}

			return true;
		}

		/**
		 * Revoke a users API keys
		 *
		 * @access public
		 * @param INT $user_id
		 * @return bool
		 */
		public function revoke_api_key( $user_id ) {
			$user = get_userdata( $user_id );

			if ( ! empty( $user->inbound_user_public_key ) ) {
				delete_transient( md5( 'inbound_api_user_' . $user->inbound_user_public_key ) );
				delete_user_meta( $user_id, 'inbound_user_public_key' );
				delete_user_meta( $user_id, 'inbound_user_secret_key' );
			} else {
				return false;
			}

			return true;
		}

		/**
		 * Generate the public key for a user
		 *
		 * @access private
		 * @param string $user_email
		 * @return string
		 */
		private function generate_public_key( $user_email = '' ) {
			$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
			$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );
			return $public;
		}

		/**
		 * Generate the secret key for a user
		 *
		 * @access private
		 * @param int $user_id
		 * @return string
		 */
		private function generate_private_key( $user_id = 0 ) {
			$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
			$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );
			return $secret;
		}

	}

	add_action('admin_init', 'load_Inbound_API_Keys_Generation');

	function load_Inbound_API_Keys_Generation() {
		$Inbound_API_Keys_Generation = new Inbound_API_Keys_Generation();
	}
}