<?php

/**
 * LOAD SUB MENU SECTIONS 
 */
 
add_action('admin_menu', 'wp_cta_add_menu');
function wp_cta_add_menu()
{
	if (current_user_can('manage_options'))
	{

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Forms' , 'cta' ), __( 'Manage Forms' , 'cta' ) , 'manage_options', 'inbound-forms-redirect',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Templates' , 'cta' ) , __( 'Manage Templates' , 'cta' ) , 'manage_options', 'wp_cta_manage_templates','wp_cta_manage_templates',100);

		// comming soon add_submenu_page('edit.php?post_type=wp-call-to-action', 'Get Addons', 'Add-on Extensions', 'manage_options', 'wp_cta_store','wp_cta_store_display',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Settings' , 'cta' ) , __( 'Global Settings' , 'cta') , 'manage_options', 'wp_cta_global_settings','wp_cta_display_global_settings');

		// Add settings page for frontend editor
		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Editor' , 'cta' ), __( 'Editor' , 'cta' ) , 'manage_options', 'wp-cta-frontend-editor', 'wp_cta_frontend_editor_screen');

	}
}