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
		add_filter( 'inbound_menu_debug' , array (__CLASS__ , 'load_debug_links') , 10 , 2);
	}

	/**
	*  Adds sub-menus to 'Inbound Email Component'
	*/
	public static function add_menu_items() {

		add_menu_page(
			__( 'Inbound Now' , INBOUNDNOW_TEXT_DOMAIN ) ,  /* page title */
			__( 'Inbound Now' , INBOUNDNOW_TEXT_DOMAIN ) , /* menu title */
			'edit_posts', /* capability */
			'inbound-pro', /* menu slug */
			array( 'Inbound_Pro_Settings' , 'display' ), /* page function */
			INBOUND_PRO_URLPATH . 'core/shared/assets/images/global/inbound-icon.png', /* icon url */
			30  /* position */
		);

		/* Manage Settings */
		add_submenu_page('inbound-pro', __( 'Settings' , INBOUNDNOW_TEXT_DOMAIN ) , __( 'Settings' , INBOUNDNOW_TEXT_DOMAIN ) , 'manage_options', 'inbound-pro', array( 'Inbound_Pro_Settings' , 'display' ) );

		/* Manage Templates
		add_submenu_page('inbound-pro', __( 'Test' , INBOUNDNOW_TEXT_DOMAIN ) , __( 'Test' , INBOUNDNOW_TEXT_DOMAIN ) , 'manage_options', 'inbound-marketing', array( 'Inbound_Pro_Downloads' , 'test_ui' ) );
		*/

		/* Manage Templates */
		add_submenu_page('inbound-pro', __( 'Templates' , INBOUNDNOW_TEXT_DOMAIN ) , __( 'Templates' , INBOUNDNOW_TEXT_DOMAIN ) , 'edit_posts', 'inbound-manage-templates', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

		/* Manage Extensions */
		add_submenu_page('inbound-pro', __( 'Extensions' , INBOUNDNOW_TEXT_DOMAIN ) , __( 'Extensions' , INBOUNDNOW_TEXT_DOMAIN ) , 'edit_posts', 'inbound-manage-extensions', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

	}

	/**
	 *  Loads debug menu item section
	 */
	public static function load_debug_links( $secondary_menu_items , $debug_key ) {
		$actual_link = "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$reset_templates_link = add_query_arg( array('inbound_reset_downloads_data' => true ) , admin_url('admin.php?page=inbound-manage-templates'));
		$reset_extensions_link = add_query_arg( array('inbound_reset_downloads_data' => true ) , admin_url('admin.php?page=inbound-manage-extensions'));

		$secondary_menu_items['inbound-pro-reset-templates'] = array(
			'parent' => $debug_key,
			'title'  => __( 'Templates::Refresh Available Templates', 'inbound-pro' ),
			'href'   => $reset_templates_link,
			'meta'   => array( 'title' =>  __( 'Refresh availavle template data from Inbound Now.', 'inbound-pro' ) )
		);

		$secondary_menu_items['inbound-pro-reset-extensiond'] = array(
			'parent' => $debug_key,
			'title'  => __( 'Extensions::Refresh Available Extensions', 'inbound-pro' ),
			'href'   => $reset_extensions_link,
			'meta'   => array( 'title' =>  __( 'Refresh availavle template data from Inbound Now.', 'inbound-pro' ) )
		);

		return $secondary_menu_items;
	}

}

/**
*  Loads Class Pre-Init
*/
$Inbound_Menus_Admin = new Inbound_Menus_Admin();

