<?php
/*
Plugin Name: Landing Pages
Plugin URI: http://www.inboundnow.com/landing-pages/
Description: The first true all-in-one Landing Page solution for WordPress, including ongoing conversion metrics, a/b split testing, unlimited design options and so much more!
Version: 1.8.4
Author: Inbound Now
Author URI: http://www.inboundnow.com/
Text Domain: landing-pages
Domain Path: lang
*/

if (!class_exists('Inbound_Landing_Pages_Plugin')) {

	final class Inbound_Landing_Pages_Plugin {

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
			self::notice( __( 'Landing Pages requires PHP version 5.3+ to run. Your version '.PHP_VERSION.' is not high enough.<br><u>Please contact your hosting provider</u> to upgrade your PHP Version.<br>The plugin is NOT Running. You can disable this warning message by <a href="'.$plugin_url.'">deactivating the plugin</a>', 'landing-pages' ) );
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
		/* END PHP VERSION CHECKS */


		/**
		* Main Inbound_Landing_Pages_Plugin Instance
		*
		*/
		public function __construct() {

			/* Start a PHP Session if in wp-admin */
			if (is_admin()) {
				if(!isset($_SESSION)){@session_start();}
			}

			/* Run Loaders */
			self::load_constants();
			self::load_files();
			self::load_shared_files();
			self::load_text_domain_init();

		}

		/**
		* Setup plugin constants
		*
		*/
		private static function load_constants() {

			define('LANDINGPAGES_CURRENT_VERSION', '1.8.4' );
			define('LANDINGPAGES_URLPATH', plugins_url( '/' , __FILE__ ) );
			define('LANDINGPAGES_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('LANDINGPAGES_PLUGIN_SLUG', plugin_basename( dirname(__FILE__) ) );
			define('LANDINGPAGES_FILE', __FILE__ );
			define('LANDINGPAGES_STORE_URL', 'http://www.inboundnow.com/' );
			$uploads = wp_upload_dir();
			define('LANDINGPAGES_UPLOADS_PATH', $uploads['basedir'].'/landing-pages/templates/' );
			define('LANDINGPAGES_UPLOADS_URLPATH', $uploads['baseurl'].'/landing-pages/templates/' );

		}

		/**
		* Include required plugin files
		*
		*/
		private static function load_files() {

			/* load core files */
			switch (is_admin()) :
				case true :
					/* loads admin files */
					include_once('modules/module.language-support.php');
					include_once('modules/module.javascript-admin.php');
					include_once('classes/class.activation.php');
					include_once('classes/class.activation.upgrade-routines.php');
					include_once('modules/module.global-settings.php');
					include_once('modules/module.clone.php');
					include_once('modules/module.extension-updater.php');
					include_once('modules/module.extension-licensing.php');
					include_once('modules/module.admin-menus.php');
					include_once('modules/module.welcome.php');
					include_once('modules/module.install.php');
					include_once('modules/module.alert.php');
					include_once('modules/module.metaboxes.php');
					include_once('modules/module.landing-page.php');
					include_once('classes/class.load-extensions.php');
					include_once('modules/module.post-type.php');
					include_once('modules/module.track.php');
					include_once('modules/module.ajax-setup.php');
					include_once('modules/module.utils.php');
					include_once('modules/module.sidebar.php');
					include_once('modules/module.widgets.php');
					include_once('modules/module.cookies.php');
					include_once('modules/module.ab-testing.php');
					include_once('modules/module.click-tracking.php');
					include_once('modules/module.templates.php');
					include_once('modules/module.store.php');
					include_once('modules/module.customizer.php');					
					include_once('classes/class.inbound-statistics.php');
					//include_once('classes/class.branching.php');


				BREAK;

				case false :
					/* load front-end files */
					include_once('modules/module.javascript-frontend.php');
					include_once('modules/module.post-type.php');
					include_once('modules/module.track.php');
					include_once('modules/module.ajax-setup.php');
					include_once('modules/module.utils.php');
					include_once('modules/module.sidebar.php');
					include_once('modules/module.widgets.php');
					include_once('modules/module.cookies.php');
					include_once('modules/module.ab-testing.php');
					include_once('modules/module.click-tracking.php');
					include_once('modules/module.landing-page.php');
					include_once('classes/class.load-extensions.php');
					include_once('modules/module.customizer.php');

					BREAK;
			endswitch;
		}

		/** Load Shared Files at priority 2 */
		private static function load_shared_files() {
			if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
				require_once('shared/classes/class.load-shared.php');
				add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init' ) , 2 );
			}
		}

		/**
		*  Hooks the text domain loader to the init
		*/
		private static function load_text_domain_init() {
			add_action( 'init' , array( __CLASS__ , 'load_text_domain' ) );
		}

		/**
		*  Loads the correct .mo file for this plugin
		*/
		public static function load_text_domain() {
			load_plugin_textdomain( 'landing-pages' , false , LANDINGPAGES_PLUGIN_SLUG . '/lang/' );
		}

	}

	/* Initiate Plugin */
	if ( Inbound_Landing_Pages_Plugin::is_valid_php_version() ) {
		// Get Inbound Now Running
		$GLOBALS['Inbound_Landing_Pages_Plugin'] = new Inbound_Landing_Pages_Plugin;
	} else {
		// Show Fail
		Inbound_Landing_Pages_Plugin::fail_php_version();
	}

		
		

	/* lagacy - Conditional check LP active */
	function lp_check_active() {
		return 1;
	}

	/* Function to check This has been loaded for the tests */
	function landingpages_is_active() {
		return true;
	}

	/* Function to check plugin code is running in travis */
	function inbound_travis_check() {
		echo '*** Landing Pages Plugin is Running on Travis ***';
		return true;
	}



}


