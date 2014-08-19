<?php

class Leads_CSV_Admin_Menus {

	public function __construct() {
		self::load_hooks();
	}
	
	public static function load_hooks() {
	
		/* Add sub menu to leads */
		add_action('admin_menu', array( __CLASS__ , 'add_sub_menus') , 99 );
		
		/* Load js components */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
	}

	/**
	*  Adds menu item under 'Leads'
	*/
	public static function add_sub_menus()
	{
		if (current_user_can('manage_options')) {

			add_submenu_page('edit.php?post_type=wp-lead', __( 'Import' , 'inbound-pro' ), __( 'Import' , 'cta' ) , 'manage_options', 'leads-import', array( __CLASS__ , 'import_page_step_1' )) ;

		}
	}

	/**
	*  Enqueue JS & CSS scripts relative to CSV Importing administration screen
	*/
	public static function enqueue_scripts() {
	
		$screen = get_current_screen();

		if ( isset($screen->base) && $screen->base != 'wp-lead_page_leads-import') {
			return;
		}
		
		/* load bootstrap */
		wp_register_script( 'bootstrap-js' , INBOUND_NOW_URL .'includes/BootStrap/bootstrap.min.js');
		wp_enqueue_script( 'bootstrap-js' );
	
		wp_register_style( 'bootstrap-css' , INBOUND_NOW_URL . 'includes/BootStrap/bootstrap.css');
		wp_enqueue_style( 'bootstrap-css' );

	}
	
	public static function import_page_step_1() {
		echo 'hello';
	}
}

$GLOBALS['Leads_CSV_Admin_Menus'] = new Leads_CSV_Admin_Menus;