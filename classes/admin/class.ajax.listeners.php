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

        /* save api key */
         $settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
         $settings[ 'api-key' ][ 'api-key' ] = trim($_REQUEST['api']);
         Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );

        $response = wp_remote_post( Inbound_API_Wrapper::get_api_url() . 'key/check' ,  array(
            'body' => array(
                'api' => trim($_REQUEST['api']),
                'site' => $_REQUEST['site']
            )
        ));

         if ( is_wp_error( $response ) ) {
             return;
         }

         $decoded = json_decode( $response['body'] , true );

         if (isset( $decoded['apikey'] )) {
            $customer = Inbound_Options_API::get_option( 'inbound-pro' , 'customer' , array() );
            $customer['active'] = true;
            Inbound_Options_API::update_option( 'inbound-pro' , 'customer' , $customer );
         } else {
            $customer = Inbound_Options_API::get_option( 'inbound-pro' , 'customer' , array() );
            $customer['active'] = false;
            Inbound_Options_API::update_option( 'inbound-pro' , 'customer' , $customer );
         }

         echo wp_remote_retrieve_body( $response );
         exit;
     }


}

/* Loads Inbound_Pro_Admin_Ajax_Listeners pre init */
$Inbound_Pro_Admin_Ajax_Listeners = new Inbound_Pro_Admin_Ajax_Listeners();