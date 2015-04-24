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


}

/* Loads Inbound_Pro_Admin_Ajax_Listeners pre init */
$Inbound_Pro_Admin_Ajax_Listeners = new Inbound_Pro_Admin_Ajax_Listeners();