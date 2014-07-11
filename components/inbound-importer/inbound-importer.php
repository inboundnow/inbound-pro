<?php
/*
Plugin Name: Inbound Now - Import/Export Tools
Plugin URI: http://www.inboundnow.com/
Description: Imports Leads into the Leads database using csv. 
Version: 1.0.1
Author: Inbound Now
Author URI: http://www.inboundnow.com/

*/


if (!class_exists('Inbound_Importer')) {


class Inbound_Importer {
	
	/**
	* Initalize Inbound_Importer Class
	*/
	public function __construct() {				
		self::define_constants();
		self::load_hooks();		
	}
	
	/**
	* Define Constants
	*  
	*/
	private static function define_constants() {
		define('INBOUND_IMPORTER_CURRENT_VERSION', '1.0.5' ); 
		define('INBOUND_IMPORTER_LABEL' , 'Inbound Importer' ); 
		define('INBOUND_IMPORTER_SLUG' , plugin_basename( dirname(__FILE__) ) ); 
		define('INBOUND_IMPORTER_FILE' ,  __FILE__ ); 
		define('INBOUND_IMPORTER_REMOTE_ITEM_NAME' , 'easydigitaldownloads-integration' ); 
		define('INBOUND_IMPORTER_URLPATH', plugins_url( ' ', __FILE__ ) ); 
		define('INBOUND_IMPORTER_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); 
	}
	
	
	/**
	* Load files
	*/
	public static function load_fils() {
	
		include_once(
		
		
	}
	
	/**
	*  Adds license key and WordPress URL 
	*  
	*  @return ARRAY with license key and wordpress url
	*  
	*/
	public static function prime_event_object() {
		$json = array(
			'license_key' => self::$license_key,
			'wordpress_url' => self::$wordpress_url,
		);
		
		return $json;
	}

	/**
	*  Prepare JSON for push event - page view
	*  @dependency Leads
	*  @param ARRAY $lead_data array of data related to inbound form submission
	*  @return 
	*  
	*/
	public static function push_page_view_event( $lead_data ) {

		/* Add license key and wordpress url to event */
		$event_prime = self::prime_event_object();
		
		/* Build up event object */
		$event_data = array(
			'content_id' => $lead_data['page_id'],
			'lead_id' => $lead_data['lead_id'],
			'lead_uid' => $lead_data['wp_lead_uid'],
			'event' => array(
				'event_type' => 'page_view'
			)
		);

		/* Merge data arrays */
		$event = array_merge( $event_prime , $event_data );
		
		/* Send event to cloud */
		self::push_event( $event );
	}
	
	
	/**
	*  Prepare JSON for push event - form submission
	*  
	*  @param ARRAY $lead_data array of data related to inbound form submission
	*  @return 
	*  
	*/
	public static function push_form_submit_event( $lead_data ) {

		/* Add license key and wordpress url to event */
		$event_prime = self::prime_event_object();
		
		/* Build up event object */
		$event_data = array(
			'content_id' => $lead_data['page_id'],
			'lead_id' => $lead_data['lead_id'],
			'lead_uid' => $lead_data['wp_lead_uid'],
			'event' => array(
				'event_type' => 'form_submission'
			)
		);
		
		/* Discover additional data about the form */
		if ( isset($lead_data['Mapped_Data']) ) {
			$mapped_data = json_decode( stripslashes($lead_data['Mapped_Data']) , true );
			if (isset($mapped_data['form_name'])) {
				$event_data['event']['meta'] = array(
					//'group_id' => $mapped_data['form_id'],
					'form_name' => $mapped_data['form_name'],
					'form_id' => $mapped_data['form_id'],
				);
			}
		}
		
		/* Merge data arrays */
		$event = array_merge( $event_prime , $event_data );
		
		/* Send event to cloud */
		self::push_event( $event );
	}
	
	/**
	*  Push json event data to remote api
	*  
	*  @param ARRAY $event contains all data related to action event being pushed to remote api
	*  
	*/
	public static function push_event( $event ) {
		error_log('start');
		/* Send the data to the cloud! */
		$response = wp_remote_post( self::$api , array(
				'method' => 'POST',
				'timeout' => 20,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					),
				'cookies' => array(),
				'body' => json_encode($event)
			)
		);
		
		self::log($event);
		self::log($response);
	}

	
	/**
	* Setups Software Update API 
	*/
	public static function license_setup() {

		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUND_IMPORTER_FILE , INBOUND_IMPORTER_LABEL , INBOUND_IMPORTER_SLUG , INBOUND_IMPORTER_CURRENT_VERSION  , INBOUND_IMPORTER_REMOTE_ITEM_NAME ) ;
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


$GLOBALS['Inbound_Importer'] = new Inbound_Importer();

}