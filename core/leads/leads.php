<?php
/*
Plugin Name: Leads
Plugin URI: http://www.inboundnow.com/leads/
Description: Track website visitor activity, manage incoming leads, and send collected emails to your email service provider.
Author: Inbound Now
Version: 1.6.4
Author URI: http://www.inboundnow.com/
Text Domain: leads
Domain Path: lang
*/

if ( ! class_exists( 'Inbound_Leads_Plugin' ) ) {

	final class Inbound_Leads_Plugin {

		/* START PHP VERSION CHECKS */
		/**
		 * Admin notices, collected and displayed on proper action
		 *
		 * @var array
		 */
		public static $notices = array();

		/**
		 * Whether the current PHP version meets the minimum requirements
		 *
		 * @return bool
		 */
		public static function is_valid_php_version() {
			return version_compare( PHP_VERSION, '5.3', '>=' );
		}

		/**
		 * Invoked when the PHP version check fails. Load up the translations and
		 * add the error message to the admin notices
		 */
		static function fail_php_version() {
			//add_action( 'plugins_loaded', array( __CLASS__, 'load_text_domain_init' ) );
			$plugin_url = admin_url( 'plugins.php' );
			self::notice( __( 'Leads requires PHP version 5.3+ to run. Your version '.PHP_VERSION.' is not high enough.<br><u>Please contact your hosting provider</u> to upgrade your PHP Version.<br>The plugin is NOT Running. You can disable this warning message by <a href="'.$plugin_url.'">deactivating the plugin</a>', 'leads' ) );
		}

		/**
		 * Handle notice messages according to the appropriate context (WP-CLI or the WP Admin)
		 *
		 * @param string $message
		 * @param bool $is_error
		 * @return void
		 */
		public static function notice( $message, $is_error = true ) {
			if ( defined( 'WP_CLI' ) ) {
				$message = strip_tags( $message );
				if ( $is_error ) {
					WP_CLI::warning( $message );
				} else {
					WP_CLI::success( $message );
				}
			} else {
				// Trigger admin notices
				add_action( 'all_admin_notices', array( __CLASS__, 'admin_notices' ) );

				self::$notices[] = compact( 'message', 'is_error' );
			}
		}

		/**
		 * Show an error or other message in the WP Admin
		 *
		 * @action all_admin_notices
		 * @return void
		 */
		public static function admin_notices() {
			foreach ( self::$notices as $notice ) {
				$class_name   = empty( $notice['is_error'] ) ? 'updated' : 'error';
				$html_message = sprintf( '<div class="%s">%s</div>', esc_attr( $class_name ), wpautop( $notice['message'] ) );
				echo wp_kses_post( $html_message );
			}
		}

		/**
		 * Main Inbound_Leads_Plugin Instance
		 *
		*/
		public function __construct() {
			self::define_constants();
			self::includes();
			self::load_shared_files();
			self::load_text_domain_init();
		}

		/**
		*  	Setup plugin constants
		*/
		private static function define_constants() {
			define('WPL_CURRENT_VERSION', '1.6.4' );
			define('WPL_URLPATH',  plugins_url( '/', __FILE__ ) );
			define('WPL_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
			define('WPL_CORE', plugin_basename( __FILE__ ) );
			define('WPL_SLUG', plugin_basename( dirname(__FILE__) ) );
			define('WPL_FILE',  __FILE__ );
			define('WPL_STORE_URL', 'http://www.inboundnow.com' );
			$uploads = wp_upload_dir();
			define('WPL_UPLOADS_PATH', $uploads['basedir'].'/leads/' );
			define('WPL_UPLOADS_URLPATH', $uploads['baseurl'].'/leads/' );
		}

		/**
		*  Include required files
		*/
		private static function includes() {

			if ( is_admin() ) {

				/* Admin Includes */
				require_once('classes/class.activation.php');
				require_once('classes/class.activation.upgrade-routines.php');
				require_once('modules/module.ajax-setup.php');
				require_once('modules/module.nav-menus.php');
				require_once('classes/class.metaboxes.wp-lead.php');
				require_once('modules/module.post-type.wp-lead.php');
				require_once('modules/module.post-type.landing-pages.php');
				require_once('modules/module.lead-management.php');
				require_once('modules/module.form-integrations.php');
				require_once('modules/module.global-settings.php');
				require_once('classes/class.dashboard.php');
				require_once('modules/module.tracking.php');
				require_once('modules/module.enqueue-admin.php');
				require_once('modules/module.form-integrations.php');
				require_once('classes/class.metaboxes.email-template.php');
				require_once('classes/class.wordpress-core.email.php');
				require_once('classes/class.inbound-api.php');
				require_once('classes/class.inbound-api.api-key-generation.php');
				require_once('classes/class.inbound-api.api-keys-table.php');
				require_once('classes/class.admin-notices.php');
				require_once('classes/class.branching.php');
				require_once('classes/class.login.php');

			} else {
				/* Frontend Includes */
				/* load global */
				require_once('modules/module.ajax-setup.php');
				require_once('modules/module.post-type.wp-lead.php');
				require_once('modules/module.form-integrations.php');
				require_once('classes/class.metaboxes.email-template.php');
				require_once('classes/class.wordpress-core.email.php');
				require_once('classes/class.inbound-api.php');
				require_once('classes/class.login.php');

				/* load frontend */
				require_once('modules/module.enqueue-frontend.php');
				require_once('modules/module.tracking.php');


			}

			//require_once INBOUND_NOW_PATH . 'includes/install.php';
		}

		/**
		*  Load Shared Files
		*/
		private static function load_shared_files() {
			require_once('shared/classes/class.load-shared.php');
			add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 3 );
		}


		/**
		* Hook method to load correct text domain
		*
		*/
		private static function load_text_domain_init() {
			add_action( 'init' , array( __CLASS__ , 'load_text_domain' ) );
		}

		/**
		*   Loads the text domain
		*/
		public static function load_text_domain() {
			load_plugin_textdomain( 'leads' , false , WPL_SLUG . '/lang/' );
		}
	}

	/* Initiate Plugin */
	if ( Inbound_Leads_Plugin::is_valid_php_version() ) {
		// Get Inbound Now Running
		$GLOBALS['Inbound_Leads_Plugin'] = new Inbound_Leads_Plugin;
	} else {
		// Show Failure message
		Inbound_Leads_Plugin::fail_php_version();
	}

	/* method to see if leads is active */
	function wpleads_check_active() {
		return true;
	}

}
