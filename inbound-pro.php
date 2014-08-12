<?php
/*
Plugin Name: Inbound Pro
Plugin URI: http://www.inboundnow.com/leads/
Description: Pro Version of Inbound Now Plugins
Author: Inbound Now
Version: 1.0.0
Author URI: http://www.inboundnow.com/
Text Domain: inbound-now
Domain Path: /languages/
*/


/* Main Inbound Now Pro Class */
if ( ! class_exists( 'Inbound_Now_Pro' ) ) {
final class Inbound_Now_Pro {

	private static $instance;
	//public $api;
    //public $session;
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
		add_action( 'plugins_loaded', array( __CLASS__, 'i18n' ) );
		self::notice( __( 'Stream requires PHP version 5.3+, plugin is currently NOT ACTIVE.', 'stream' ) );
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
		 * Main Inbound_Now_Pro Instance
		 *
		 * Insures that only one instance of Easy_Digital_Downloads exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
	*/
	public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Inbound_Now_Pro ) ) {
				self::$instance = new Inbound_Now_Pro;
				self::$instance->setup_constants();
				self::$instance->load_core();
				self::$instance->includes();
				self::$instance->load_textdomain();

			}
			return self::$instance;
		}
	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd' ), '1.6' );
	}

	/* Disable unserializing of the class */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd' ), '1.6' );
	}

	/* Setup plugin constants */
	private function setup_constants() {
		if(!defined('INBOUND_NOW_ACTIVATE')) { define('INBOUND_NOW_ACTIVATE', __FILE__ ); }
		if(!defined('INBOUND_NOW_CURRENT_VERSION')) { define('INBOUND_NOW_CURRENT_VERSION', '1.0.0' ); }
		if(!defined('INBOUND_NOW_URL')) {  define('INBOUND_NOW_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) ); }
		if(!defined('INBOUND_NOW_PATH')) {  define('INBOUND_NOW_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) ); }
		if(!defined('INBOUND_NOW_CORE')) {  define('INBOUND_NOW_CORE', plugin_basename( __FILE__ ) );}
		if(!defined('INBOUND_NOW_SLUG')) {  define('INBOUND_NOW_SLUG', plugin_basename( __FILE__ ) );}
		if(!defined('INBOUND_NOW_FILE')) {  define('INBOUND_NOW_FILE',  __FILE__ );}
		if(!defined('INBOUND_NOW_STORE_URL')) {  define('INBOUND_NOW_STORE_URL', 'http://www.inboundnow.com' );}
		$uploads = wp_upload_dir();
		if(!defined('INBOUND_NOW_UPLOADS_PATH')) {  define('INBOUND_NOW_UPLOADS_PATH', $uploads['basedir'].'/inbound-pro/' );}
		if(!defined('INBOUND_NOW_UPLOADS_URLPATH')) {  define('INBOUND_NOW_UPLOADS_URLPATH', $uploads['baseurl'].'/inbound-pro/' );}
	}
	/* Include required files */
	private function load_core() {
		// core plugins load here
		//if(!defined('ACF_LITE') ) {  define( 'ACF_LITE' , true ); }
		include_once( INBOUND_NOW_PATH .'/includes/advanced-custom-fields-pro/acf.php');
		include_once( INBOUND_NOW_PATH .'/includes/acf-field-manage-inbound-addons/acf-MANAGE_INBOUND_ADDONS.php');
		/* load ACF fields */
		include_once( INBOUND_NOW_PATH .'/classes/admin/admin-settings.php');


		if( function_exists('acf_add_options_page') ) {

			acf_add_options_page(array(
					'page_title' 	=> 'Inbound Pro Settings',
					'menu_title'	=> 'Inbound Pro',
					'menu_slug' 	=> 'inbound-pro-setting',
					'capability'	=> 'edit_posts',
					'redirect'		=> false
				));

		}
		include_once( INBOUND_NOW_PATH .'/core/cta/wordpress-cta.php');
	}
	/* Include required files */
	private function includes() {
		global $inboundnow_options;

		/* load core files */
		//$default_pro_files = array('inboundnow-lead-revisit-notifications', 'inboundnow-zapier');
		$default_pro_files = array();
		/* Add filter here for core files */
		/* load toggled addon files */
		$toggled_addon_files = get_transient( 'inbound-now-active-addons');
		if(is_array($toggled_addon_files) && is_array($default_pro_files)) {
		$inbound_load_files = array_unique(array_merge($toggled_addon_files, $default_pro_files));

			foreach ($inbound_load_files as $key => $value) {
				if(!strpos($value, 'inbound-')) {
					if(file_exists( INBOUND_NOW_PATH . '/components/'.$value.'/'.$value.'.php')) {
						// include each toggled on
						include_once( INBOUND_NOW_PATH .'/components/'.$value.'/'.$value.'.php');
					}
				}
			}
		}

		// include_once('classes/pro-welcome.class.php');

		//require_once INBOUND_NOW_PATH . 'includes/admin/settings/register-settings.php';
		//$edd_options = edd_get_settings();

		/* Global Includes */
		//require_once INBOUND_NOW_PATH . 'includes/actions.php';

		if ( is_admin() ) {
			/* Admin Includes */
			require_once INBOUND_NOW_PATH . '/classes/admin/admin.initClass.php';

		} else {
			/* Frontend Includes */

		}


	}

	/* Loads the plugin language files */
	public function load_textdomain() {
			// Set filter for plugin's languages directory
			$inbound_now_lang_dir = dirname( plugin_basename( INBOUND_NOW_FILE ) ) . '/languages/';
			$inbound_now_lang_dir = apply_filters( 'inbound_now_languages_directory', $inbound_now_lang_dir );

			// Traditional WordPress plugin locale filter
			$locale        = apply_filters( 'plugin_locale',  get_locale(), 'inbound-now' );
			$mofile        = sprintf( '%1$s-%2$s.mo', 'inbound-now', $locale );

			// Setup paths to current locale file
			$mofile_local  = $inbound_now_lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/inbound-now/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/inbound-now folder
				load_textdomain( 'inbound-now', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/inbound-now-pro/languages/ folder
				load_textdomain( 'inbound-now', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'inbound-now', false, $inbound_now_lang_dir );
			}
		}
	}

}

function Inbound_Now() {
	return Inbound_Now_Pro::instance();
}

if ( Inbound_Now_Pro::is_valid_php_version() ) {
	// Get Inbound Now Running
	Inbound_Now();
} else {
	Inbound_Now_Pro::fail_php_version();
}