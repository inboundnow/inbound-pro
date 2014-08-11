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

if ( ! class_exists( 'Inbound_Now_Pro' ) ) {

	final class Inbound_Now_Pro {

		private static $instance;

		/**
		* Main Inbound_Now_Pro Instance
		*
		*/
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Inbound_Now_Pro ) ) {
				self::$instance = new Inbound_Now_Pro;
				self::$instance->setup_constants();
				self::$instance->include_core_classes();
				self::$instance->include_toggled_components();
				self::$instance->load_textdomain();
			
			}
			return self::$instance;
		}
		
		/**
		* Throw error on object clone
		*
		*/
		public function __clone() {
			_doing_it_wrong( __FUNCTION__ , __( 'Cheatin&#8217; huh?', 'inbound-pro' ) );
		}

		/**
		*  	Disable unserializing of the class
		*/
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__ , __( 'Cheatin&#8217; huh?', 'inbound-pro' ) );
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
		
		/**
		*  Include core classes
		*/
		private static function include_core_classes() {
			
			/* Admin Includes */
			if ( is_admin() ) {
				
				require_once INBOUND_NOW_PATH . '/classes/admin/define_settings.php';
				require_once INBOUND_NOW_PATH . '/classes/acf-integration.php';

			} 
			
			/* Frontend Includes */
			else {
				
			}

		}

		/**
		*  Include component files that are activated
		*/
		private function include_toggled_components() {
			global $inboundnow_options;

			/* load core files */	
			$default_pro_files = array();
			
			/* Add filter here for core files */
			
			/* load toggled addon files */
			$toggled_addon_files = get_transient( 'inbound-now-active-addons');
			if(is_array($toggled_addon_files) && is_array($default_pro_files)) {
				
				$inbound_load_files = array_unique(array_merge($toggled_addon_files, $default_pro_files));

				foreach ($inbound_load_files as $key => $value) {

					if(file_exists( INBOUND_NOW_PATH . '/components/'.$value.'/'.$value.'.php')) {
						include_once( INBOUND_NOW_PATH .'/components/'.$value.'/'.$value.'.php'); // include each toggled on
					}
				}
			}
		}

		/**
		*  	Loads the plugin language files 
		*/
		public function load_textdomain() {
			
		}

	}

	function Inbound_Now() {
		return Inbound_Now_Pro::instance();
	}

	// Get Inbound Now Running
	Inbound_Now();
}