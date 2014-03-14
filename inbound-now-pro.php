<?php
/*
Plugin Name: Inbound Now Professional
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
		 * Main Inbound_Now_Pro Instance
		 *
		 * Insures that only one instance of Easy_Digital_Downloads exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
	*/
	public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Inbound_Now_Pro ) ) {
				self::$instance = new Inbound_Now_Pro;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				// self::$instance->roles   = new EDD_Roles();
				// self::$instance->fees    = new EDD_Fees();
				// self::$instance->api     = new EDD_API();
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
	private function includes() {
		global $inboundnow_options;

		/* load core files */
		$default_pro_files = array('inboundnow-lead-revisit-notifications', 'inboundnow-zapier');
		/* Add filter here for core files */
		/* load toggled addon files */
		$toggled_addon_files = get_transient( 'inbound-now-active-addons' );
		$toggled_addon_files = array(); // tests
		$inbound_load_files = array_unique(array_merge($toggled_addon_files, $default_pro_files));
		if (isset($inbound_load_files) && is_array($inbound_load_files)) {
			foreach ($inbound_load_files as $key => $value) {
				include_once('components/'.$value.'/'.$value.'.php'); // include each toggled on
			}
		}

		/*
		include_once('components/inboundnow-lead-revisit-notifications/inboundnow-lead-revisit-notifications.php');
		include_once('components/inboundnow-zapier/inboundnow-zapier.php');
		include_once('components/inboundnow-aweber/inboundnow-aweber.php');
		include_once('components/inboundnow-mailchimp/inboundnow-mailchimp.php');
		include_once('components/inboundnow-hubspot/inboundnow-hubspot.php');
		//include_once('components/inboundnow-home-page-lander/inboundnow-home-page-lander.php');
		include_once('classes/pro-welcome.class.php');

		/**/



		//require_once INBOUND_NOW_PATH . 'includes/admin/settings/register-settings.php';
		//$edd_options = edd_get_settings();

		/* Global Includes */
		//require_once INBOUND_NOW_PATH . 'includes/actions.php';

		if ( is_admin() ) {
			/* Admin Includes */
			require_once INBOUND_NOW_PATH . '/classes/admin/define_settings.php';

		} else {
			/* Frontend Includes */
			//require_once INBOUND_NOW_PATH . 'includes/process-download.php';

		}

		//require_once INBOUND_NOW_PATH . 'includes/install.php';
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

// Get Inbound Now Running
Inbound_Now();