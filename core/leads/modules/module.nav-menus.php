<?php

/**********************************************************/
/******************CREATE SETTINGS SUBMENU*****************/
add_action('admin_menu', 'wpleads_add_menu');
function wpleads_add_menu() {
	if (current_user_can('manage_options')) {
		add_submenu_page('edit.php?post_type=wp-lead', __( 'Lead Management' , 'leads' ) , __( 'Lead Management' , 'leads' ), 'manage_options', 'lead_management','lead_management_admin_screen');

		//add_submenu_page('edit.php?post_type=wp-lead', 'Lead Rules', 'Lead Rules', 'manage_options', 'lead-rules-redirect',100);

		add_submenu_page('edit.php?post_type=wp-lead',  __( 'Forms' , 'leads' ),  __( 'Manage Forms' , 'leads' ) , 'manage_options', 'inbound-forms-redirect',100);

		add_submenu_page('edit.php?post_type=wp-lead',  __( 'Settings' , 'leads' ),  __( 'Global Settings' , 'leads' ), 'manage_options', 'wpleads_global_settings','wpleads_display_global_settings');
	}

	/* remove main lead menu from nav*/
    global $submenu;
    unset($submenu['post-new.php?post_type=wp-lead'][15]);
	remove_submenu_page('edit.php?post_type=wp-lead', 'post-new.php?post_type=wp-lead');


}
