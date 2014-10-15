<?php

/**
 * @wordpress-plugin
 * Plugin Name: Inbound Extension - GravityForms Integration
 * Plugin URI: http://www.inboundnow.com/market/gravityforms-integration/
 * Description: Integrates Gravity Forms with Leads by allowing you to map your form fields to leads fields. Also allows for conversions to be sorted into lead lists.
 * Version: 2.0.1
 * Author: InboundNow, Gravity+
 * Author URI: http://www.inboundnow.com
 * Text Domain: inbound-gravityforms
 * Domain Path: /languages
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( !class_exists('Inbound_GravityForms') ) {

	class Inbound_GravityForms {

		public function __construct() {

			self::load_constants();
			self::load_files();

		}

		/**
		 *  Load constants
		 */
		public static function load_constants() {
			define( 'INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION', '2.0.0' );
			define( 'INBOUNDNOW_GRAVITYFORMS_LABEL', 'Gravity Forms Integration' );
			define( 'INBOUNDNOW_GRAVITYFORMS_SLUG', plugin_basename( dirname( __FILE__ ) ) );
			define( 'INBOUNDNOW_GRAVITYFORMS_FILE', __FILE__ );
			define( 'INBOUNDNOW_GRAVITYFORMS_REMOTE_ITEM_NAME', 'gravityforms-integration' );
			define( 'INBOUNDNOW_GRAVITYFORMS_URLPATH', plugin_dir_url( __FILE__ ) );
			define( 'INBOUNDNOW_GRAVITYFORMS_PATH', plugin_dir_path( __FILE__ ) );
		}

		public static function load_files() {
			require_once( 'includes/class-gravityformsleads.php' );
		}
	}


	$GLOBALS['Inbound_GravityForms'] = new Inbound_GravityForms();

}