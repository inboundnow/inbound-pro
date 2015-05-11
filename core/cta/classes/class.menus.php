<?php

if ( !class_exists('CTA_Menus') ) {

/**
*  Loads admin sub-menus and performs misc menu related functions
*/
class CTA_Menus {

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
		add_action('admin_menu', array( __CLASS__ , 'add_sub_menus' ) );
	}

	/**
	*  Adds sub-menus to 'Calls to Action'
	*/
	public static function add_sub_menus() {
		if ( !current_user_can('manage_options')) {
			return;
		}

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Forms' , 'cta' ), __( 'Manage Forms' , 'cta' ) , 'manage_options', 'inbound-forms-redirect',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Templates' , 'cta' ) , __( 'Manage Templates' , 'cta' ) , 'manage_options', 'wp_cta_manage_templates', array( 'CTA_Template_Manager' , 'display_management_page' ) );

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Settings' , 'cta' ) , __( 'Global Settings' , 'cta') , 'manage_options', 'wp_cta_global_settings', array( 'CTA_Global_Settings' , 'display_global_settings' ) );

	}

}

/**
*  Loads Class Pre-Init
*/
$CTA_Menus = new CTA_Menus();

}