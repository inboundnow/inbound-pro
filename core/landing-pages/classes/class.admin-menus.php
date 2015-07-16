<?php

/**
 * Class Landing_Pages_Admin_Menus
 */

class Landing_Pages_Admin_Menus {

    /**
     *  Initiate class
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     *  Load hooks and filters
     */
    public static function add_hooks() {
        add_action('admin_menu', array(__CLASS__, 'add_sub_menus') );
    }

	/**
     *  Add sub menu items to Landing Pages
     */
	public static function add_sub_menus() {

        if (!current_user_can('manage_options')) {
            return;
        }

        add_submenu_page('edit.php?post_type=landing-page', __('Forms', 'landing-pages'), __('Manage Forms', 'landing-pages'), 'manage_options', 'inbound-forms-redirect', 100);
        add_submenu_page('edit.php?post_type=landing-page', __('Templates', 'landing-pages'), __('Manage Templates', 'landing-pages'), 'manage_options', 'lp_manage_templates', 'lp_manage_templates', 100);
        add_submenu_page('edit.php?post_type=landing-page', __('Settings', 'landing-pages'), __('Settings', 'landing-pages'), 'manage_options', 'lp_global_settings', 'lp_display_global_settings');

    }
}

new Landing_Pages_Admin_Menus;
