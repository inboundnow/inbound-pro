<?php
/*
Plugin Name: Inbound Now PRO
Plugin URI: http://www.inboundnow.com/
Description: Professional Inbound Marketing Suite for WordPress
Author: InboundWP LLC
Version: 1.8.5.1
Author URI: http://www.inboundnow.com/
Text Domain: inbound-pro
Domain Path: /lang/
*/

if ( !class_exists('Inbound_Pro_Plugin')	) {
	/**
	 * Class Inbound_Pro_Plugin
	 * @package     InboundPro
	 */
	final class Inbound_Pro_Plugin {

		/**
		 * Admin notices, collected and displayed on proper action
		 *
		 * @var array
		 */
		public static $notices = array();
		static $settings = array();

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
			self::notice( __( 'Inbound Professional Components require PHP version 5.3+, plugin is currently NOT ACTIVE.', 'inbound-email' ) );
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
				$class_name	= empty( $notice['is_error'] ) ? 'updated' : 'error';
				$html_message = sprintf( '<div class="%s">%s</div>', esc_attr( $class_name ), wpautop( $notice['message'] ) );
				echo wp_kses_post( $html_message );
			}
		}

		/**
		* Main Inbound_Pro_Plugin Instance
		*/
		public function __construct() {
			self::define_constants();
			self::load_pro_classes();
			self::load_shared_components();
			self::load_core_components();
			self::load_text_domain_init();
		}

		/*
		* Setup plugin constants
		*
		*/
		private static function define_constants() {

			define('INBOUND_PRO_CURRENT_VERSION', '1.8.5.1' );
			define('INBOUND_PRO_STABLE_VERSION', '1.8.4.6' );
			define('INBOUND_PRO_TRANSLATIONS_VERSION', '1.30.16' );
			define('INBOUND_PRO_URLPATH', plugin_dir_url( __FILE__ ));
			define('INBOUND_PRO_PATH', plugin_dir_path( __FILE__ ) );
			define('INBOUND_PRO_SLUG', plugin_basename( dirname( __FILE__ ) ) );
			define('INBOUND_PRO_FILE', __FILE__ );

			$uploads = wp_upload_dir();
			define('INBOUND_PRO_UPLOADS_PATH', $uploads['basedir'].'/inbound-pro/' );
			define('INBOUND_PRO_UPLOADS_URLPATH', $uploads['baseurl'].'/inbound-pro/' );
			define('INBOUND_PRO_STORE_URL', 'http://www.inboundnow.com/market/' );

			if (strstr( 'inboundnow.dev' , site_url() )) {
				define('INBOUND_COMPONENT_PATH', WP_PLUGIN_DIR);
			} else {
				define('INBOUND_COMPONENT_PATH', 'core');
			}

		}

		/**
		 *  Load inbound pro classes
		 */
		private static function load_pro_classes() {
			global $inbound_settings;

			/* Frontend & Admin */
			include_once( INBOUND_PRO_PATH . 'classes/class.options-api.php');

			/* load settings global */
			$inbound_settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
			$inbound_settings = (is_array($inbound_settings)) ? $inbound_settings : array();

			/* secret debug tool for administrators */
			if (isset($_GET['_inbound_settings']) && is_admin() && current_user_can('administrator') ) {
				print_r($inbound_settings);exit;
			}

			/* determine customer access level */
			self::get_customer_status();

			/* reporting template class need to be loaded before extensions */
			if (is_admin()) {
				include_once(INBOUND_PRO_PATH . 'classes/admin/class.reporting.templates.php');
			}

			/* load templates and extensions */
			include_once( INBOUND_PRO_PATH . 'classes/class.extension-loader.php');

			/* load misc front & backend files */
			include_once( INBOUND_PRO_PATH . 'classes/admin/class.lead-field-mapping.php');

			/* load tracking report features */
			include_once( INBOUND_PRO_PATH . 'classes/class.tracking.php');

			/* load cronjob system for mailer and automation */
			if ( !isset($inbound_settings['inbound-core-loading']['toggle-email-automation']) || $inbound_settings['inbound-core-loading']['toggle-email-automation'] =='on' ) {
				include_once( INBOUND_PRO_PATH . 'classes/class.cron.php');
			}

				/* load subscriber only assets/features */
			if ( INBOUND_ACCESS_LEVEL> 0 && !isset($_GET['acf_off']) && INBOUND_ACCESS_LEVEL != 9 ) {

				/* if lite mode enabled then set the constant */
				if ( !isset($inbound_settings['inbound-acf']['toggle-acf-lite']) || $inbound_settings['inbound-acf']['toggle-acf-lite'] == 'on') {
					define('ACF_LITE', true);
				}

				include_once( INBOUND_PRO_PATH . 'assets/plugins/advanced-custom-fields-pro/acf.php');

			}


			/* Admin Only */
			if (is_admin()) {
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.updater.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.activate.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.notifications.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.menus.adminmenu.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.lead-status-mapping.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.settings.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.download-management.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.inbound-api-wrapper.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.ajax.listeners.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.oauth-engine.php');
				include_once( INBOUND_PRO_PATH . 'classes/admin/class.translations.php');
				if ( INBOUND_ACCESS_LEVEL> 0 && INBOUND_ACCESS_LEVEL != 9 ) {
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.impressions.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.visitor-pageviews.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.visitors.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.event.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.events.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.visitor-events.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.email-stats.php');
					include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.lead-searches-and-comments.php');
				}

				include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.upgrade.php');
				include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.quick-view.php');
				include_once(INBOUND_PRO_PATH . 'classes/admin/report-templates/report.cta.quick-view.php');
				include_once(INBOUND_PRO_PATH . 'classes/admin/class.inbound-analytics.php');


				//include_once( INBOUND_PRO_PATH . 'classes/admin/class.reporting.funnels.php');

			}


		}

		/**
		*  Load shared components
		*/
		private static function load_shared_components() {

			include_once( INBOUND_PRO_PATH . 'core/shared/classes/class.load-shared.php' );
			add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 1 );

		}

		/**
		 *  Conditionally load core components
		 */
		private static function load_core_components() {
			global $inbound_settings;

			/* load calls to action  */
			if ( !isset($inbound_settings['inbound-core-loading']['toggle-calls-to-action']) || $inbound_settings['inbound-core-loading']['toggle-calls-to-action'] =='on' ) {

				include_once( INBOUND_COMPONENT_PATH . '/cta/calls-to-action.php');
			}

			/* load leads */
			if ( !isset($inbound_settings['inbound-core-loading']['toggle-leads']) || $inbound_settings['inbound-core-loading']['toggle-leads'] =='on' ) {
				include_once( INBOUND_COMPONENT_PATH . '/leads/leads.php');
			}

			/* load landing pages */
			if ( !isset($inbound_settings['inbound-core-loading']['toggle-landing-pages']) || $inbound_settings['inbound-core-loading']['toggle-landing-pages'] =='on' ) {
				include_once( INBOUND_COMPONENT_PATH . '/landing-pages/landing-pages.php');
			}

			if ( INBOUND_ACCESS_LEVEL == 0 || INBOUND_ACCESS_LEVEL == 9 ) {
				return;
			}


			/* load inbound mailer & inbound automation */
			if ( !isset($inbound_settings['inbound-core-loading']['toggle-email-automation']) || $inbound_settings['inbound-core-loading']['toggle-email-automation'] =='on' ) {
				include_once( INBOUND_COMPONENT_PATH . '/mailer/mailer.php');
				include_once( INBOUND_COMPONENT_PATH . '/automation/automation.php');
			}

		}

        /**
         * Get customer status
         */
        public static function get_customer_status() {
			if (defined('INBOUND_ACCESS_LEVEL')) {
				return INBOUND_ACCESS_LEVEL;
			}

            $customer = Inbound_Options_API::get_option( 'inbound-pro' , 'customer' , array() );
            $status = ( isset($customer['is_pro']) ) ? $customer['is_pro'] : 0;
			define('INBOUND_ACCESS_LEVEL' , $status);
            return $status;
        }

		/**
		*	Loads the correct .mo file for this plugin
		*
		*/
		private static function load_text_domain_init() {
			$local = get_locale();
			global $inbound_settings;

			if (!isset($inbound_settings['translations']['toggle-translations']) || $inbound_settings['translations']['toggle-translations'] != 'on' ) {
				return;
			}

			load_textdomain( 'inbound-pro' , INBOUND_PRO_UPLOADS_PATH . 'assets/lang/'.$local.'.mo'  );
		}

	}

	/* Initiate Plugin */
	if ( Inbound_Pro_Plugin::is_valid_php_version() ) {

		if (!isset($_GET['inbound_off'] )  ) {
			$Inbound_Pro_Plugin = new Inbound_Pro_Plugin;
		}

	} else {
		// Show Fail
		Inbound_Pro_Plugin::fail_php_version();
	}

}
