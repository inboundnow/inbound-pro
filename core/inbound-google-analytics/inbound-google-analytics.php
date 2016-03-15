<?php
/*
Plugin Name: Inbound Extension - Google Analytics
Plugin URI: http://www.inboundnow.com/
Description: Incredible integrations with Google Analyitics
Version: 1.0.1
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*
*/

if ( !class_exists( 'Inbound_Google_Analytics' )) {

	class Inbound_Google_Analytics {

		/**
		*	initiates class
		*/
		public function __construct() {

			global $wpdb;

			/* Define constants */
			self::define_constants();

			/* Define hooks and filters */
			self::load_hooks();

			/* load files */
			self::load_files();
		}

		/**
		*	Loads hooks and filters selectively
		*/
		public static function load_hooks() {
			/* Setup Automatic Updating & Licensing */
			add_action('admin_init', array( __CLASS__ , 'license_setup') );

			/* Disable Legacy Inbound Statistics */
		}


		/**
		*	Defines constants
		*/
		public static function define_constants() {
			define('INBOUND_GA_CURRENT_VERSION', '0.0.1' );
			define('INBOUND_GA_LABEL' , 'Google Analytics Integration' );
			define('INBOUND_GA_SLUG' , plugin_basename( dirname(__FILE__) ) );
			define('INBOUND_GA_FILE' ,	__FILE__ );
			define('INBOUND_GA_REMOTE_ITEM_NAME' , 'google-analytics-integration' );
			define('INBOUND_GA_URLPATH', plugins_url( '/', __FILE__ ) );
			define('INBOUND_GA_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
		}

		/**
		* Setups Software Update API
		*/
		public static function license_setup() {

			/*PREPARE THIS EXTENSION FOR LICESNING*/
			if ( class_exists( 'Inbound_License' ) ) {
				$license = new Inbound_License( INBOUND_GA_FILE , INBOUND_GA_LABEL , INBOUND_GA_SLUG , INBOUND_GA_CURRENT_VERSION	, INBOUND_GA_REMOTE_ITEM_NAME ) ;
			}
		}

		/**
		*  Loads PHP files
		*/
		public static function load_files() {

			if ( is_admin() ) {
				/* settings page files */
				include_once INBOUND_GA_PATH . 'classes/class.admin.php';
				include_once INBOUND_GA_PATH . 'assets/libraries/oauth/apisettings.class.php';
				include_once INBOUND_GA_PATH . 'assets/libraries/oauth/gadata.class.php';
				include_once INBOUND_GA_PATH . 'assets/libraries/oauth/googleoauth2.class.php';

				/* load reporting files */
				include_once INBOUND_GA_PATH . 'classes/class.google-connector.php';	/* Load Inbound Analytics Connector Class */
				include_once INBOUND_GA_PATH . 'classes/class.build-ui-containers.php'; /* Load administration files */
				//include_once INBOUND_GA_PATH . 'classes/class.post-types.php';

				/* Load template files */
				include_once INBOUND_GA_PATH . 'templates/content.quick-view.php';
				include_once INBOUND_GA_PATH . 'templates/content.impressions-expanded.php';

				/* Load Template Loader */
				include_once INBOUND_GA_PATH . 'classes/class.template-loader.php';
			} else {
				
				/* Load Tracking Script */
				include_once INBOUND_GA_PATH . 'classes/class.frontend-tracking.php';
			}
			
		}

	}

	/**
	*	Load Inbound_Google_Analytics class in init
	*/
	function Load_Inbound_Google_Analytics() {
		$Inbound_Google_Analytics = new Inbound_Google_Analytics();
	}
	add_action( 'init' , 'Load_Inbound_Google_Analytics' , 1 );
}