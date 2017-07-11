<?php
/**
 * Class for adding Landing Pages menu items to left wp-admin menu
 * @package LandingPages
 * @subpackage Menus
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
        add_action('admin_init', array(__CLASS__, 'redirect_inbound_pro_settings') );
    }

	/**
     *  Add sub menu items to Landing Pages
     */
	public static function add_sub_menus() {

        /* add_submenu_page('edit.php?post_type=landing-page', __('Forms', 'landing-pages'), __('Forms', 'landing-pages'), 'edit_landing_pages', 'inbound-forms-redirect', 100); */
        if ( !class_exists('Inbound_Pro_Plugin') ) {
            add_submenu_page('edit.php?post_type=landing-page', __('Settings', 'landing-pages'), __('Settings', 'landing-pages'), 'edit_landing_pages', 'lp_global_settings', array('Landing_Pages_Settings' , 'display_settings'));
            add_submenu_page('edit.php?post_type=landing-page', __('Upgrade to Pro' , 'landing-pages'),__('Upgrade to Pro' , 'landing-pages'), 'edit_landing_pages', 'lp_store', array( 'Inbound_Now_Store' , 'store_display' ),100);
        } else {
            add_submenu_page('edit.php?post_type=landing-page', __('Settings', 'landing-pages'), __('Settings', 'landing-pages'), 'edit_landing_pages', 'inbound-pro-landing-pages', array( 'Landing_Pages_Settings' , 'redirect_settings' ));
        }

        add_submenu_page('edit.php?post_type=landing-page', __('Upload Templates', 'landing-pages'), __('Upload Templates', 'landing-pages'), 'edit_landing_pages', 'lp_manage_templates', 'lp_manage_templates', 100);

    }

    /**
     * redirects settings link to Inbound Pro settings page with Landing Pages settings pre-loaded
     */
    public static function redirect_inbound_pro_settings() {

        if ( !isset($_GET['page']) || $_GET['page'] != 'inbound-pro-landing-pages') {
            return;
        }

        header('Location: ' . admin_url('admin.php?page=inbound-pro&setting=Landing+Pages'));
        exit;

    }
}

new Landing_Pages_Admin_Menus;
