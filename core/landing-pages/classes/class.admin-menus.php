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

        add_submenu_page('edit.php?post_type=landing-page', __('Forms', 'landing-pages'), __('Forms', 'landing-pages'), 'edit_landing_pages', 'inbound-forms-redirect', 100);

        add_submenu_page('edit.php?post_type=landing-page', __('Settings', 'landing-pages'), __('Settings', 'landing-pages'), 'edit_landing_pages', 'lp_global_settings', array('Landing_Pages_Settings' , 'display_settings'));

        if ( !class_exists('Inbound_Pro_Plugin') ) {
            add_submenu_page('edit.php?post_type=landing-page', __('Upgrade to Pro' , 'landing-pages'),__('Upgrade to Pro' , 'landing-pages'), 'edit_landing_pages', 'lp_store', array( 'Inbound_Now_Store' , 'store_display' ),100);
        }

        add_submenu_page('edit.php?post_type=landing-page', __('Upload Templates', 'landing-pages'), __('Upload Templates', 'landing-pages'), 'edit_landing_pages', 'lp_manage_templates', 'lp_manage_templates', 100);

    }
}

new Landing_Pages_Admin_Menus;
