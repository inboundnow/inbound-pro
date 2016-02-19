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
            remove_menu_page( 'edit.php?post_type=landing-page' );
            return;
        }

        add_submenu_page('edit.php?post_type=landing-page', __('Forms', 'landing-pages'), __('Forms', 'landing-pages'), 'manage_options', 'inbound-forms-redirect', 100);

        /* Mebership holders can use Inbound Pro to manage templates */
        //if ( !class_exists('Inbound_Pro_Plugin') || Inbound_Pro_Plugin::get_customer_status() < 1 ) {
       //}

        add_submenu_page('edit.php?post_type=landing-page', __('Settings', 'landing-pages'), __('Settings', 'landing-pages'), 'manage_options', 'lp_global_settings', array('Landing_Pages_Settings' , 'display_settings'));
        add_submenu_page('edit.php?post_type=landing-page', __('Upload', 'landing-pages'), __('Upload', 'landing-pages'), 'manage_options', 'lp_manage_templates', 'lp_manage_templates', 100);

    }
}

new Landing_Pages_Admin_Menus;
