<?php

/**
*  Loads admin sub-menus and performs misc menu related functions
*/
class Inbound_Menus_Admin {

	/**
	*  Initializes class
	*/
	public function __construct() {
		self::load_hooks();
	}
	
	/**
	*  Loads hooks and filters
	*/
	public static function load_hooks() {
		add_action('admin_menu', array( __CLASS__ , 'add_menu_items' ) , 1 );
	}
	
	/**
	*  Adds sub-menus to 'Inbound Email Component'
	*/
	public static function add_menu_items() {
		
		add_menu_page( 
			__( 'Inbound Pro' , 'inbound-pro' ) ,  /* page title */
			__( 'Inbound Pro' , 'inbound-pro' ) , /* menu title */
			'manage_options', /* capability */
			'inbound-pro', /* menu slug */
			array( 'Inbound_Pro_Settings' , 'display' ), /* page function */
			INBOUND_PRO_URLPATH . '/assets/images/shortcodes-blue.png', /* icon url */
			30  /* position */
		);
	
		/* Manage Settings */
		add_submenu_page('inbound-pro', __( 'Settings' , 'inbound-pro' ) , __( 'Settings' , 'inbound-pro' ) , 'manage_options', 'inbound-pro', array( 'Inbound_Pro_Settings' , 'display' ) );
		
		/* Manage Templates */
		add_submenu_page('inbound-pro', __( 'Templates' , 'inbound-pro' ) , __( 'Templates' , 'inbound-pro' ) , 'manage_options', 'inbound-manage-templates', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

		/* Manage Extensions */
		add_submenu_page('inbound-pro', __( 'Extensions' , 'inbound-pro' ) , __( 'Extensions' , 'inbound-pro' ) , 'manage_options', 'inbound-manage-extensions', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

	}
	
}

/** 
*  Loads Class Pre-Init 
*/
$Inbound_Menus_Admin = new Inbound_Menus_Admin();

