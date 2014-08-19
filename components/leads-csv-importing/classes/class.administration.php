<?php

class Leads_CSV_Admin_Menus {

	public function __construct() {
		self::load_hooks();
	}
	
	public static function load_hooks() {
		add_action('admin_menu', array( __CLASS__ , 'add_sub_menus') );
	}

	public static function add_sub_menus()
	{
		if (current_user_can('manage_options')) {

			add_submenu_page('edit.php?post_type=wp-lead', __( 'Import' , 'inbound-pro' ), __( 'Import' , 'cta' ) , 'manage_options', 'leads-import', array( __CLASS__ , 'import_page_step_1' )) ;

		}
	}

	public static function import_page_step_1() {
		echo 'hello';
	}
}