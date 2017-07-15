<?php
/*
Plugin Name: Inbound Mailer
Plugin URI: http://www.inboundnow.com/
Description: Email marketing component developed for Inbound Now tools.
Version: 1.0.1
Author: Inbound Now
Author URI: http://www.inboundnow.com/
Text Domain: inbound-email
Domain Path: lang
*/

if ( !class_exists('Inbound_Mailer_Plugin')	) {

	/**
	 * Class Inbound_Mailer_Plugin loading Mailer component
	 * @package Mailer
	 */
	final class Inbound_Mailer_Plugin {

		/* START PHP VERSION CHECKS */
		/**
		 * Admin notices, collected and displayed on proper action
		 *
		 * @var array
		 */
		public static $notices = array();

		/**
		 * Main Inbound_Mailer_Plugin Instance
		 */
		public function __construct() {
			self::define_constants();
			self::includes();
			self::load_text_domain_init();
		}


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
			self::notice( __( 'Inbound Email Component requires PHP version 5.3+, plugin is currently NOT ACTIVE.', 'inbound-pro' ) );
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
		/* END PHP VERSION CHECKS */



		/*
		* Setup plugin constants
		*
		*/
		private static function define_constants() {

			define('INBOUND_EMAIL_CURRENT_VERSION', '2.2.1' );
			define('INBOUND_EMAIL_URLPATH', plugin_dir_url( __FILE__ ) );
			define('INBOUND_EMAIL_PATH', plugin_dir_path( __FILE__ ) );
			define('INBOUND_EMAIL_SLUG', 'mailer');
			define('INBOUND_EMAIL_FILE', __FILE__ );

			$uploads = wp_upload_dir();
			define('INBOUND_EMAIL_UPLOADS_PATH', $uploads['basedir'].'/inbound-email/templates/' );
			define('INBOUND_EMAIL_UPLOADS_URLPATH', $uploads['baseurl'].'/inbound-email/templates/' );
			define('INBOUND_EMAIL_THEME_TEMPLATES_PATH' , get_template_directory(). '/emails/' );
			define('INBOUND_EMAIL_THEME_TEMPLATES_URLPATH' , get_template_directory_uri(). '/emails/' );
			define('INBOUND_EMAIL_STORE_URL', 'http://www.inboundnow.com/market/' );

		}

		/* Include required plugin files */
		private static function includes() {

			switch (is_admin()) :
				case true :
					/* loads admin files */
					include_once('classes/class.activation.database-routines.php');
					include_once('classes/class.activation.php');
					include_once('classes/class.maintenance-lists.php');
					include_once('classes/class.postmeta.php');
					include_once('classes/class.post-type.inbound-email.php');
					include_once('classes/class.extension.wordpress-seo.php');
					include_once('classes/class.metaboxes.inbound-email.php');
					include_once('classes/class.token-engine.php');
					include_once('classes/class.inbound-forms.php');
					include_once('classes/class.menus.php');
					include_once('classes/class.ajax.listeners.php');
					include_once('classes/class.enqueues.php');
					include_once('classes/class.settings.php');
					include_once('classes/class.notifications.php');
					include_once('classes/class.clone-post.php');
					include_once('classes/class.acf-integration.php');
					include_once('classes/class.variations.php');
					include_once('classes/class.load.email-settings.php');
					include_once('classes/class.load.email-templates.php');
					include_once('classes/class.templates.list-table.php');
					include_once('classes/class.templates.manage.php');
					include_once('classes/class.templates.preview.php');
					include_once('modules/module.utils.php');
					include_once('classes/class.customizer.php');
					include_once('classes/class.tracking.php');
					include_once('classes/class.statistics.sparkpost.php');
					include_once('classes/class.scheduling.php');
					include_once('classes/class.mailer.php');
					include_once('classes/class.mailer.sparkpost.php');
					include_once('classes/class.connector.sparkpost.php');
					include_once('classes/class.unsubscribe.php');
					include_once('classes/class.lead-profile.php');

					BREAK;

				case false :
					/* load front-end files */
					include_once('classes/class.settings.php');
					include_once('classes/class.maintenance-lists.php');
					include_once('classes/class.postmeta.php');;
					include_once('classes/class.load.email-templates.php');
					include_once('classes/class.post-type.inbound-email.php');
					include_once('classes/class.inbound-forms.php');
					include_once('classes/class.extension.wordpress-seo.php');
					include_once('classes/class.enqueues.php');
					include_once('classes/class.tracking.php');
					include_once('classes/class.ajax.listeners.php');
					include_once('classes/class.variations.php');
					include_once('classes/class.templates.preview.php');
					include_once('classes/class.unsubscribe.php');
					include_once('classes/class.acf-integration.php');
					include_once('modules/module.utils.php');
					include_once('classes/class.customizer.php');
					include_once('classes/class.token-engine.php');
					include_once('classes/class.mailer.php');
					include_once('classes/class.mailer.sparkpost.php');
					include_once('classes/class.connector.sparkpost.php');
					include_once('classes/class.scheduling.php');
					include_once('classes/class.statistics.sparkpost.php');

					BREAK;
			endswitch;
		}

		/**
		 *	Loads the correct .mo file for this plugin
		 *
		 */
		private static function load_text_domain_init() {
			add_action( 'init' , array( __CLASS__ , 'load_text_domain' ) );
		}

		public static function load_text_domain() {
			load_plugin_textdomain( 'inbound-email' , false , INBOUND_EMAIL_SLUG . '/lang/' );
		}


	}

	/* Initiate Plugin */
	if ( Inbound_Mailer_Plugin::is_valid_php_version() ) {
		 new Inbound_Mailer_Plugin;
	} else {
		// Show Fail
		Inbound_Mailer_Plugin::fail_php_version();
	}

	/**
	 *  Checks if mailer plugin is active
	 */
	function mailer_check_active() {
		return 1;
	}
}
