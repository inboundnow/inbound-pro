<?php

/**
 * Class for expanding the core WordPress top admin navigation menu with Inbound Pro related links and shortcuts
 * @package     InboundPro
 * @subpackage  Menus
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

		/* only show administrators */
		if( !current_user_can('activate_plugins') ) {
			return;
		}

		if (!defined('INBOUND_PRO_MENU_LABEL')) {
			define('INBOUND_PRO_MENU_LABEL' , __( 'Inbound Pro' , 'inbound-pro' ) );
		}

		add_menu_page(
			INBOUND_PRO_MENU_LABEL ,  /* page title */
			INBOUND_PRO_MENU_LABEL , /* menu title */
			'edit_posts', /* capability */
			'inbound-pro', /* menu slug */
			array( 'Inbound_Pro_Settings' , 'display' ), /* page function */
			INBOUND_PRO_URLPATH . 'core/shared/assets/images/global/inbound-icon.png', /* icon url */
			30  /* position */
		);

		/* Manage Settings */
		add_submenu_page('inbound-pro', __( 'Settings' , 'inbound-pro' ) , __( 'Settings' , 'inbound-pro' ) , 'manage_options', 'inbound-pro', array( 'Inbound_Pro_Settings' , 'display' ) );

		/* Manage Templates
		add_submenu_page('inbound-pro', __( 'Test' , 'inbound-pro' ) , __( 'Test' , 'inbound-pro' ) , 'manage_options', 'inbound-marketing', array( 'Inbound_Pro_Downloads' , 'test_ui' ) );
		*/

		/* Manage Templates */
		add_submenu_page('inbound-pro', __( 'Templates' , 'inbound-pro' ) , __( 'Templates' , 'inbound-pro' ) , 'edit_posts', 'inbound-manage-templates', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

		/* Manage Extensions */
		add_submenu_page('inbound-pro', __( 'Extensions' , 'inbound-pro' ) , __( 'Extensions' , 'inbound-pro' ) , 'edit_posts', 'inbound-manage-extensions', array( 'Inbound_Pro_Downloads' , 'display_ui' ) );

		/* Reporting */
		//add_submenu_page('inbound-pro', __( 'Funnels' , 'inbound-pro' ) , __( 'Funnels' , 'inbound-pro' ) , 'edit_posts', 'inbound-reporting', array( 'Inbound_Funnel_Reporting' , 'load_ui' ) );

	}

	/**
	 *  Loads debug menu item section
	 */
	public static function load_debug_links( $secondary_menu_items , $debug_key ) {
		$actual_link = "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$reset_templates_link = add_query_arg( array('inbound_reset_downloads_data' => true ) , admin_url('admin.php?page=inbound-manage-templates'));
		$reset_extensions_link = add_query_arg( array('inbound_reset_downloads_data' => true ) , admin_url('admin.php?page=inbound-manage-extensions'));
		$disable_inbound_pro_link = add_query_arg( array('inbound_off' => true ) , $actual_link);

		$secondary_menu_items['inbound-pro-reset-templates'] = array(
			'parent' => $debug_key,
			'title'  => __( 'Templates::Refresh Available Templates', 'inbound-pro' ),
			'href'   => $reset_templates_link,
			'meta'   => array( 'title' =>  __( 'Refresh available template data from Inbound Now.', 'inbound-pro' ) )
		);

		$secondary_menu_items['inbound-pro-reset-extensiond'] = array(
			'parent' => $debug_key,
			'title'  => __( 'Extensions::Refresh Available Extensions', 'inbound-pro' ),
			'href'   => $reset_extensions_link,
			'meta'   => array( 'title' =>  __( 'Refresh available template data from Inbound Now.', 'inbound-pro' ) )
		);

		$secondary_menu_items['inbound-pro-disable'] = array(
			'parent' => $debug_key,
			'title'  => __( 'Turn Inbound Pro Off For This Page', 'inbound-pro' ),
			'href'   => $disable_inbound_pro_link,
			'meta'   => array( 'title' =>  __( 'Tell WordPress not to load Inbound Pro on this page.', 'inbound-pro' ) )
		);

		return $secondary_menu_items;
	}

}

/**
*  Loads Class Pre-Init
*/
$Inbound_Menus_Admin = new Inbound_Menus_Admin();

