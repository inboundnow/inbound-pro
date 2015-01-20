<?php
/*
Plugin Name: Leads - Import Leads (CSV)
Plugin URI: http://www.inboundnow.com/
Description: Imports lead profiles from CSV file. 
Version: 1.0.2
Author: Inbound Now
Author URI: http://www.inboundnow.com/

*/


if (!class_exists('Inbound_CSV_Importer')) {


class Inbound_CSV_Importer {
	

	/**
	* Initialize Inbound_CSV_Importer Class
	*/
	public function __construct() {		
		
		self::define_constants();
		self::load_files();
		self::load_hooks();		
	}
	
	/**
	* Define Constants
	*  
	*/
	private static function define_constants() {
		define('INBOUND_CSV_IMPORTING_CURRENT_VERSION', '1.0.1' ); 
		define('INBOUND_CSV_IMPORTING_LABEL' , 'Leads - CSV Importing' ); 
		define('INBOUND_CSV_IMPORTING_SLUG' , plugin_basename( dirname(__FILE__) ) ); 
		define('INBOUND_CSV_IMPORTING_FILE' ,  __FILE__ ); 
		define('INBOUND_CSV_IMPORTING_REMOTE_ITEM_NAME' , 'import-leads-csv' ); 
		define('INBOUND_CSV_IMPORTING_URLPATH', plugins_url( '', __FILE__ ) .'/' ) ; 
		define('INBOUND_CSV_IMPORTING_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); 
		$uploads = wp_upload_dir();
		define('INBOUND_CSV_IMPORTING_UPLOADS_PATH', $uploads['basedir'].'/leads/json/' );
		define('INBOUND_CSV_IMPORTING_UPLOADS_URLPATH', $uploads['baseurl'].'/leads/json/' );
	}
	
	/**
	*  Loads php files
	*/
	public static function load_files() {	
		
		if (is_admin()) {
			
			/* Load Admin Menu */
			include_once INBOUND_CSV_IMPORTING_PATH . 'classes/class.administration.php';
			
		}
	
	}
	
	
	/**
	* Load Hooks & Filters 
	*/
	public static function load_hooks() {
	
		/* Setup Automatic Updating & Licensing */
		add_action('admin_init', array( __CLASS__ , 'license_setup') );		
	}
	
	/**
	* Setups Software Update API 
	*/
	public static function license_setup() {

		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUND_CSV_IMPORTING_FILE , INBOUND_CSV_IMPORTING_LABEL , INBOUND_CSV_IMPORTING_SLUG , INBOUND_CSV_IMPORTING_CURRENT_VERSION  , INBOUND_CSV_IMPORTING_REMOTE_ITEM_NAME ) ;
		}
	}

	/**
	 * Helper log function for debugging
	 *
	 * @since 1.2.2
	 */
	static function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

}


$GLOBALS['Inbound_CSV_Importer'] = new Inbound_CSV_Importer();

}