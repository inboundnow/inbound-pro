<?php

/**
 * Class Inbound_Mailer_Menus defines and loads Mailer component left vertical wp-admin menu items
 * @package     Automation
 * @subpackage  Rules
 *
 */

if ( !class_exists('Inbound_Mailer_Menus') ) {


class Inbound_Mailer_Menus {

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
	*  Adds sub-menus to 'Inbound Email Component'
	*/
	public static function add_sub_menus() {
		if ( !current_user_can('manage_options')) {
			remove_menu_page( 'edit.php?post_type=inbound-email' );
			return;
		}

		add_submenu_page('edit.php?post_type=inbound-email', __( 'Upload Templates' , 'inbound-pro' ) , __( 'Upload' , 'inbound-pro' ) , 'manage_options', 'inbound_email_manage_templates', array( 'Inbound_Mailer_Template_Manager' , 'display_management_page' ) );

		/* Include Settings only if Inbound Pro not installed */
		if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
			add_submenu_page( 'edit.php?post_type=inbound-email' , __( 'Settings' , 'inbound-pro' ) , __( 'Settings' , 'inbound-email') , 'manage_options', 'inbound_email_global_settings', array( 'Inbound_Mailer_Settings' , 'display_global_settings' ) );
		} else {

		}

	}
	
}

/** 
*  Loads Class Pre-Init 
*/
$Inbound_Mailer_Menus = new Inbound_Mailer_Menus();

}