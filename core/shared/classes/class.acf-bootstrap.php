<?php

class Inbound_Shared_ACF_BootStrap {

    public function __construct() {
        self::load_acf();
        self::load_acf_extended_fields();
    }

    public static function load_acf() {

        /* load ACF if not already loaded */
        if( !class_exists('acf') || defined('ACF_PRELOADED') ) {

            define( 'ACF_LITE', true );
            define( 'ACF_FREE', true );

            include_once( INBOUNDNOW_SHARED_PATH . 'assets/plugins/advanced-custom-fields/acf.php');

            /* customize ACF path */
            add_filter('acf/settings/path', array( __CLASS__, 'define_acf_settings_path' ) );

            /* customize ACF URL path */
            add_filter('acf/settings/dir', array( __CLASS__, 'define_acf_settings_url' ) );

            /* Hide ACF field group menu item */
            add_filter('acf/settings/show_admin', '__return_false');


        } else {
            /* find out if ACF free or ACF Pro is installed & activated*/
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if ( !function_exists('acf_add_local_field_group') ) {
                define( 'ACF_FREE', true );
            }  else {
                define( 'ACF_PRO', true );
                add_filter('lp_init' , array(__CLASS__,'acf_register_global') , 20 , 1 ); /* registeres a global of registered field values for support between ACF5 & ACF6 */
            }
        }
    }

    /**
     * Load extended/custom field support
     */
    public static function load_acf_extended_fields() {
        if (!function_exists('register_fields_font_awesome')) {
            include_once( INBOUNDNOW_SHARED_PATH . 'assets/plugins/advanced-custom-fields-font-awesome/acf-font-awesome.php');
            include_once( INBOUNDNOW_SHARED_PATH . 'assets/plugins/acf-field-date-time-picker/acf-date-time-picker.php');
        }
    }

    /**
     * If ACF Pro is active then register a global for active fields - this provides legacy support to Landing Pages
     */
    public static function acf_register_global( $field_group ) {
        $GLOBALS['acf_register_field_group'][] = array(
            'fields' => acf_local()->fields
        );
    }
    /**
     * define custom ACF path
     * @param $path
     * @return string
     */
    public static function define_acf_settings_path( $path ) {

        $path = INBOUNDNOW_SHARED_PATH . 'assets/plugins/advanced-custom-fields/';

        return $path;

    }

    /**
     * define custom settings URL
     * @param $url
     * @return string
     */
    public static function define_acf_settings_url( $url ) {

        $url = INBOUNDNOW_SHARED_PATH . 'assets/plugins/advanced-custom-fields/';

        return $url;
    }

}


new Inbound_Shared_ACF_BootStrap;