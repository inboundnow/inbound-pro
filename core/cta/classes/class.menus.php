<?php

class CTA_Menus {

	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {
		add_action('admin_menu', array( __CLASS__ , 'add_sub_menus' ) );
	}

	public static function add_sub_menus() {
		if ( !current_user_can('manage_options')) {
			return;
		}

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Forms' , 'cta' ), __( 'Manage Forms' , 'cta' ) , 'manage_options', 'inbound-forms-redirect',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Templates' , 'cta' ) , __( 'Manage Templates' , 'cta' ) , 'manage_options', 'wp_cta_manage_templates','wp_cta_manage_templates',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Settings' , 'cta' ) , __( 'Global Settings' , 'cta') , 'manage_options', 'wp_cta_global_settings','wp_cta_display_global_settings');

		/* Add settings page for frontend editor */
		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Editor' , 'cta' ), __( 'Editor' , 'cta' ) , 'manage_options', 'wp-cta-frontend-editor', 'wp_cta_frontend_editor_screen');
	}

}

$CTA_Menus = new CTA_Menus();