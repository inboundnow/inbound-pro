<?php
/*
Plugin Name: Landing Pages
Plugin URI: http://www.inboundnow.com/landing-pages/
Description: Landing page template framework with variant testing and lead capturing through cooperation with Inbound Now's Leads plugin. This is the stand alone version served through WordPress.org. 
Version: 2.6.6
Author: Inbound Now
Author URI: http://www.inboundnow.com/

*/

if (!class_exists('Inbound_Landing_Pages_Plugin')) {

	/**
	 * Class Inbound_Landing_Pages_Plugin loads Landing Pages plugin
	 * @package     Leads
	 */
	final class Inbound_Landing_Pages_Plugin {

		/**
		* Main Inbound_Landing_Pages_Plugin Instance
		*/
		public function __construct() {

			/* Start a PHP Session if in wp-admin */
			if(session_id() == '' && !headers_sent() && is_admin() ) {
				session_start();
			}

			/* Run Loaders */
			self::load_constants();
			self::load_text_domain_init();
			self::load_files();
			self::load_shared_files();

		}

		/**
		* Setup plugin constants
		*
		*/
		private static function load_constants() {

			define('LANDINGPAGES_CURRENT_VERSION', '2.6.6' );
			define('LANDINGPAGES_URLPATH', plugins_url( '/' , __FILE__ ) );
			define('LANDINGPAGES_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('LANDINGPAGES_PLUGIN_SLUG', 'landing-pages' );
			define('LANDINGPAGES_FILE', __FILE__ );
			define('LANDINGPAGES_STORE_URL', 'http://www.inboundnow.com/market' );
			$uploads = wp_upload_dir();
			define('LANDINGPAGES_UPLOADS_PATH', $uploads['basedir'].'/landing-pages/templates/' );
			define('LANDINGPAGES_UPLOADS_URLPATH', $uploads['baseurl'].'/landing-pages/templates/' );
			define('LANDINGPAGES_THEME_TEMPLATES_PATH' , get_template_directory(). '/landing-pages/' );
			define('LANDINGPAGES_THEME_TEMPLATES_URLPATH' , get_template_directory_uri(). '/landing-pages/' );
		}

		/**
		* Include required plugin files
		*/
		private static function load_files() {
			/* load core files */
			switch (is_admin()) :
				case true :
					/* loads admin files */
					include_once( LANDINGPAGES_PATH . 'classes/class.settings.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.activation.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.activation.upgrade-routines.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.variations.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.metaboxes.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.acf-integration.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.postmeta.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.template-management.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.wp-list-table.templates.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.admin-menus.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.split-testing-stats.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.admin-notices.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.row-actions.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.welcome.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.install.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.landing-pages.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.load-templates.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.post-type.landing-page.php');
					include_once( LANDINGPAGES_PATH . 'modules/module.utils.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.sidebars.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.widgets.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.store.php');

					//include_once( LANDINGPAGES_PATH . 'classes/class.branching.php');


				BREAK;

				case false :
					/* load front-end files */
					include_once( LANDINGPAGES_PATH . 'classes/class.settings.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.variations.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.acf-integration.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.postmeta.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.split-testing-stats.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.post-type.landing-page.php');
					include_once( LANDINGPAGES_PATH . 'modules/module.utils.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.sidebars.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.widgets.php');
					include_once( LANDINGPAGES_PATH . 'classes/class.landing-pages.php');

					BREAK;
			endswitch;
		}

		/**
         * Load Shared Files at priority 2
         */
		private static function load_shared_files() {
			if (!defined('INBOUND_PRO_PATH')) { 
				require_once( LANDINGPAGES_PATH . 'shared/classes/class.load-shared.php');
				add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 2 );
			}
		}

		/**
		*  Hooks the text domain loader to the init
		*/
		private static function load_text_domain_init() {
			add_action( 'init' , array( __CLASS__ , 'load_text_domain' ) , 1 );
		}

		/**
		*  Loads the correct .mo file for this plugin
		*/
		public static function load_text_domain() {

			if (!class_exists('Inbound_Pro_Plugin')) {
				load_plugin_textdomain('inbound-pro', false, LANDINGPAGES_PLUGIN_SLUG . '/assets/lang/');
			}
		}

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
			self::notice( __( 'Landing Pages requires PHP version 5.3+ to run. Your version '.PHP_VERSION.' is not high enough.<br><u>Please contact your hosting provider</u> to upgrade your PHP Version.<br>The plugin is NOT Running. You can disable this warning message by <a href="'.$plugin_url.'">deactivating the plugin</a>', 'inbound-pro' ) );
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
