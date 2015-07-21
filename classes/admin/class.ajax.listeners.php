<?php

/**
*	This class loads miscellaneous WordPress AJAX listeners
*/
class Inbound_Pro_Admin_Ajax_Listeners {

	/**
	*	Initializes class
	*/
	public function __construct() {
		self::load_hooks();
	}

	/**
	*	Loads hooks and filters
	*/
	public static function load_hooks() {

		/* Adds listener to save meta filter position */
		add_action( 'wp_ajax_inbound_update_meta_filter', array( __CLASS__ , 'update_meta_filter' ) );

		/* Adds listener validate inbound now api key*/
		add_action( 'wp_ajax_inbound_validate_api_key', array( __CLASS__ , 'validate_api_key' ) );

	}

	/**
	*	Saves meta pair values give cta ID, meta key, and meta value
	*/
	public static function update_meta_filter() {
		global $wpdb;

		if ( !isset($_POST) ) {
			return;
		}
		$memory = Inbound_Options_API::get_option( 'inbound-pro' , 'memory' , array() );

		$memory['meta_filter'] = $_POST['meta_filter'];

		Inbound_Options_API::update_option( 'inbound-pro' , 'memory' , $memory );

		header('HTTP/1.1 200 OK');
		exit;
	}

	/**
     * Validate API Key
     */
     public static function  validate_api_key() {
         /* get customer data */
         $customer = Inbound_Options_API::get_option( 'inbound-pro' , 'customer' , array() );

         /* get api key */
         $settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

         /* if there is no change in the api get then return the data on record */
         if ( $_REQUEST['api_key'] == $settings[ 'api-key' ][ 'api-key' ] && get_transient( 'inbound_api_key_cache') ) {
            $data['customer'] = $customer;
            echo  json_encode($data);
            exit;
         }

         /* update api key if changed */
         $settings[ 'api-key' ][ 'api-key' ] = trim($_REQUEST['api_key']);
         Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );

         /* look up api key to see what permissions it has */
         $response = wp_remote_post( Inbound_API_Wrapper::get_api_url() . 'key/check' ,  array(
            'body' => array(
                'api-key' => trim($_REQUEST['api_key']),
                'site' => $_REQUEST['site']
            )
         ));

         if ( is_wp_error( $response ) ) {
             return;
         }

         /* decode json response */
         $decoded = json_decode( $response['body'] , true );

         if (isset( $decoded['customer'] )) {
            $customer['is_active'] = true;
            $customer['is_pro'] = $decoded['customer']['is_pro'];
            Inbound_Options_API::update_option( 'inbound-pro' , 'customer' , $customer );
            update_option('inbound_activate_pro_components' , true );
            set_transient( 'inbound_api_key_cache' , 60 * 60 * 24 ); /* cache the good results for one day */
         } else {
            $customer['is_active'] = false;
            $customer['is_pro'] = false;
            Inbound_Options_API::update_option( 'inbound-pro' , 'customer' , $customer );
         }

         echo wp_remote_retrieve_body( $response );
         exit;
     }


}

/* Loads Inbound_Pro_Admin_Ajax_Listeners pre init */
$Inbound_Pro_Admin_Ajax_Listeners = new Inbound_Pro_Admin_Ajax_Listeners();