<?php

/* uncomment this line for testing *
set_site_transient( 'update_plugins', null );
/**/

/**
 * Inbound plugin API
 *
 */
class Inbound_Updater {

    static $api_key;
    static $domain;
    static $api_url;
    static $file;
    static $name;
    static $slug;
    static $response;
    static $info;
    static $current_version;

    /**
     * Class constructor.
     *
     * @uses plugin_basename()
     *
     * @param string  $_api_url     The URL pointing to the custom API endpoint.
     * @param string  $_plugin_file Path to the plugin file.
     */
    public function __construct( $_api_url, $_plugin_file , $_current_version) {

        self::$api_key = Inbound_API_Wrapper::get_api_key();
        self::$domain = site_url();
        self::$api_url  = $_api_url;
        self::$file  = $_plugin_file ;
        self::$name = plugin_basename( $_plugin_file );
        self::$slug     = basename( $_plugin_file, '.php' );
        self::$current_version  = $_current_version ;

        // Set up hooks.
        self::load_hooks();
    }

    /**
     * Set up WordPress filters to hook into WP's update process.
     *
     * @uses add_filter()
     * @uses add_action()
     *
     * @return void
     */
    public static function load_hooks() {
        add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check_update' ) );
        add_filter( 'plugins_api', array( __CLASS__, 'plugins_api_filter' ), 10, 3 );
        add_action( 'after_plugin_row_' . self::$name, array( __CLASS__, 'show_update_notification' ), 10, 2 );
        add_action( 'wp_ajax_update-plugin', array( __CLASS__ , 'run_update' ) , 1 );
    }

    /**
     * Check for Updates at the defined API endpoint and modify the update array.
     *
     * This function dives into the update API just when WordPress creates its update array,
     * then adds a custom API call and injects the custom plugin data retrieved from the API.
     * It is reassembled from parts of the native WordPress plugin update code.
     * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
     *
     * @uses api_request()
     *
     * @param array   $_transient_data Update array build by WordPress.
     * @return array Modified update array with custom plugin data.
     */
    public static function check_update( $_transient_data ) {

        global $pagenow;

        if( ! is_object( $_transient_data ) ) {
            $_transient_data = new stdClass;
        }

        if( 'plugins.php' == $pagenow && is_multisite() ) {
            return $_transient_data;
        }

        if ( empty( $_transient_data->response ) || empty( $_transient_data->response[ self::$name ] ) ) {

            self::api_request();

            if ( false !== self::$info && is_object( self::$info ) && isset( self::$info->new_version ) ) {

                if( version_compare( self::$current_version, self::$info->new_version, '<' ) ) {

                    $_transient_data->response[ self::$name ] = self::$info;

                }

                $_transient_data->last_checked = time();
                $_transient_data->checked[ self::$name ] = self::$current_version;

            }

        }
        //print_r($_transient_data);exit;
        return $_transient_data;
    }

    /**
     * API Call
     */
     public static function api_request() {
         self::$response  = wp_remote_get( self::$api_url );

         if ( is_wp_error(self::$response) ) {
            return;
         }

         self::$info  = json_decode( self::$response['body'] );
         self::$info->slug = self::$slug;
         self::$info->plugin = self::$name;
         self::$info->last_updated = '';
         self::$info->sections =  (array) self::$info->sections;
         //print_r(self::$info);exit;
         //print_r(self::$info);exit;
     }


    /**
     * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
     *
     * @param string  $file
     * @param array   $plugin
     */
    public static function show_update_notification( $file , $plugin ) {

        if( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        if( ! is_multisite() ) {
            return;
        }

        if ( self::$name != $file ) {
            return;
        }

        // Remove our filter on the site transient
        remove_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__ , 'check_update' ), 10 );

        $update_cache = get_site_transient( 'update_plugins' );

        if ( ! is_object( $update_cache ) || empty( $update_cache->response ) || empty( $update_cache->response[ self::$name ] ) ) {

            $cache_key    = md5( 'inbound_plugin_' .sanitize_key( self::$name ) . '_version_info' );
            self::$info = get_transient( $cache_key );

            if( false === self::$info ) {

                self::api_request();

                set_transient( $cache_key, self::$info, 3600 );
            }


            if( ! is_object( self::$info ) ) {
                return;
            }

            if( version_compare( self::current_version, self::$info->new_version, '<' ) ) {

                $update_cache->response[ self::$name ] = self::$info;

            }

            $update_cache->last_checked = time();
            $update_cache->checked[ self::$name ] = self::$current_version;

            set_site_transient( 'update_plugins', $update_cache );

        } else {

            self::$info = $update_cache->response[ self::$name ];

        }

        // Restore our filter
        add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__ , 'check_update' ) );

        if ( ! empty( $update_cache->response[ self::$name ] ) && version_compare( self::$current_version, self::$info->new_version, '<' ) ) {

            // build a plugin list row, with update notification
            $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
            echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';


            printf(
                __( 'There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.', 'inbound-pro' ),
                    esc_html( self::$info->name ),
                    esc_url( $changelog_link ),
                    esc_html( self::$info->new_version ),
                    esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . self::$name, 'upgrade-plugin_' . self::$name ) )
            );


            echo '</div></td></tr>';
        }
    }


    /**
     * Updates information on the "View version x.x details" page with custom data.
     *
     * @uses api_request()
     *
     * @param mixed   $_data
     * @param string  $_action
     * @param object  $_args
     * @return object $_data
     */
    public static function plugins_api_filter( $_data, $_action = '', $_args = null ) {


        if ( $_action != 'plugin_information' ) {
            return $_data;

        }

        if ( ! isset( $_args->slug ) || ( $_args->slug != self::$slug ) ) {

            return $_data;

        }

        self::api_request();

        if ( false !== self::$info ) {
            $_data = self::$info;
        }
        //print_r($_data);exit;

        return $_data;
    }

    /**
     * Show changelog
     */
    public static function run_update() {


        if( empty( $_REQUEST['action'] ) || 'update-plugin' != $_REQUEST['action'] ) {
            return;
        }

        if( self::$slug != $_REQUEST['slug']) {
            return 'donezies';
            exit;
        }

        $plugins = get_site_transient( 'update_plugins' );

        if( ! current_user_can( 'update_plugins' ) ) {
            wp_die( '{status:\'fail\'}' );
        }

        if (!isset($plugins->response[ $_REQUEST['plugin']] )) {
            wp_die( '{status:\'fail\'}' );
        }
        echo add_query_arg( array( 'api' => self::$api_key , 'site' => self::$domain ) , $plugins->response[ $_REQUEST['plugin'] ]->package );
        $response =  wp_remote_get( add_query_arg( array( 'api' => self::$api_key , 'site' => self::$domain ) , $plugins->response[ $_REQUEST['plugin'] ]->package ) );
        var_dump($response['body']);exit;




        exit;
    }
}


/**
 *
 *	Adds Inbound Pro to Updater
 *
 */


class Inbound_Pro_Automatic_Updates {

    static $api_key;
    static $domain;
    static $api_url;
    static $response;

    /**
     * initite class
     */
    public function __construct() {
        self::load_hooks();
    }

    /**
     * Loads hooks and filters
     */
    public static function load_hooks() {
        add_action( 'admin_init' , array( __CLASS__ , 'load_static_vars' ) , 1 );
        add_action( 'admin_init' , array( __CLASS__ , 'setup_uploader' ) );
    }

    /**
     * Load static vars
     */
    public static function load_static_vars() {
        self::$api_key = Inbound_API_Wrapper::get_api_key();
        self::$domain = site_url();
        self::$api_url =  add_query_arg( array( 'api' => self::$api_key , 'site' => self::$domain ), Inbound_API_Wrapper::get_pro_info_endpoint() );
    }

    /**
     * setup uploaded with custom uploaded plugin located in /assets/plugins/plugin-update-checker/
     */
    public static function setup_uploader() {
        new Inbound_Updater( self::$api_url , INBOUND_PRO_FILE ,  INBOUND_PRO_CURRENT_VERSION );
    }

}

new Inbound_Pro_Automatic_Updates;
