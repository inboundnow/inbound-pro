<?php

/* Load Shared Files */
if (!class_exists('Inbound_Load_Shared')) {

	class Inbound_Load_Shared {

		/**
		 * Initialize shared component loading only once.
		 *
		 */
		public static function init() {
			/* Bail if shared files already loaded */
			if (defined('INBOUDNOW_SHARED')) {
				return;
			}

			self::load_constants();
			self::load_files();
			self::load_legacy_elements();
			self::load_activation_rules();

		}

		/**
		 *  Define constants used by shared files here
		 *
		 */
		public static function load_constants() {
			define( 'INBOUDNOW_SHARED' , 'loaded' );
			define( 'INBOUDNOW_SHARED_PATH' , self::get_shared_path() );
			define( 'INBOUDNOW_SHARED_URLPATH' , self::get_shared_urlpath() );
			define( 'INBOUDNOW_SHARED_FILE' , self::get_shared_file() );
		}

		/**
		 *  Include shared php files here
		 *
		 */
		public static function load_files() {

			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.post-type.wp-lead.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.post-type.email-template.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.form.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.menus.adminbar.php');	/* Moved to PRO */
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.feedback.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.debug.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.compatibility.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.templating-engine.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.shortcodes.email-template.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.shortcodes.cookie-values.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.lead-fields.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.inbound-forms.akismet.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.welcome.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.branching.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.options-api.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.lead-storage.php');
			include_once( INBOUDNOW_SHARED_PATH . 'shortcodes/inbound-shortcodes.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.licensing.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.master-license.php');
			include_once( INBOUDNOW_SHARED_PATH . 'legacy/functions.php');
			include_once( INBOUDNOW_SHARED_PATH . 'assets/assets.loader.class.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.notifications.php');
			include_once( INBOUDNOW_SHARED_PATH . 'classes/class.ajax.php');


			self::load_legacy_elements();
		}

		/**
		 *  Legacy constants go here
		 *
		 *
		 */
		public static function load_legacy_elements() {

			if ( !defined( 'LANDINGPAGES_TEXT_DOMAIN' ) ) {
				define('LANDINGPAGES_TEXT_DOMAIN', 'landing-pages' );
			}

			if (!defined('INBOUNDNOW_LABEL')) {
				define('INBOUNDNOW_LABEL', 'inboundnow-legacy' );
			}

		}

		/**
		 *  Returns the correct absolute path to the Inbound Now shared directory
		 *
		 *  @return Path to shared folder
		 *
		 */
		public static function get_shared_path() {
			if ( defined('WP_CTA_PATH') ) {
				return WP_CTA_PATH . 'shared/';
			} else if (	defined('LANDINGPAGES_PATH') ) {
				return LANDINGPAGES_PATH . '/shared/';
			} else if (	defined('WPL_PATH') ) {
				return WPL_PATH . '/shared/';
			}
		}

		/**
		 *  Returns the correct URL path to the Inbound Now Shared directory
		 *
		 *  @return URL path to shared directory
		 *
		 */
		public static function get_shared_urlpath() {
			if ( defined('WP_CTA_URLPATH') ) {
				return WP_CTA_URLPATH . 'shared/';
			} else if (	defined('LANDINGPAGES_URLPATH') ) {
				return LANDINGPAGES_URLPATH . '/shared/';
			} else if (	defined('WPL_URLPATH') ) {
				return WPL_URLPATH . '/shared/';
			}
		}

		/**
		 *  Returns the correct __FILE__ string
		 *
		 *  @return plugin path/filename.php
		 *
		 */
		public static function get_shared_file() {
			if ( defined('WP_CTA_FILE') ) {
				return WP_CTA_FILE;
			} else if (	defined('LANDINGPAGES_URLPATH') ) {
				return LANDINGPAGES_FILE;
			} else if (	defined('WPL_FILE') ) {
				return WPL_FILE;
			}
		}

		/**
		*  Hooks shared activation rules into admin_init
		*/
		public static function load_activation_rules() {
			add_action('admin_init' , array( __CLASS__ , 'run_activation_rules') );
		}

		/**
		*  Run activation rules hosted in shared directory
		*/
		public static function run_activation_rules() {
			if ( is_admin() && get_option( 'Inbound_Activate' ) ) {

				/* Delete activation trigger */
				delete_option( 'Inbound_Activate' );

				/* Run activation action hook for shared components */
				do_action( 'inbound_shared_activate' );
			}
		}
	}
}