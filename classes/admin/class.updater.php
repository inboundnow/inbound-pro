<?php

/* uncomment this line for testing *
set_site_transient( 'update_plugins', null );
/**/

/**
 * Class for managing updates to Inbound Pro plugin
 * Inbound plugin API
 * @package     InboundPro
 * @subpackage  Updates
 */
class Inbound_Updater {

    static $api_key;
    static $domain;
    static $api_url;
    static $path;
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
    public function __construct( $_api_url, $_plugin_path ,  $_plugin_file , $_current_version) {

        self::$api_key = Inbound_API_Wrapper::get_api_key();
        self::$domain = site_url();
        self::$api_url  = $_api_url;
        self::$path  = $_plugin_path ;
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
        add_filter( 'http_request_args', array( __CLASS__ , 'allow_download_url' ) , 10, 1 );
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

        return $_transient_data;
    }

    /**
     * API Call -
     */
    public static function api_request() {
        global $inbound_settings;

        self::$response  = wp_remote_get( self::$api_url , array ('timeout' => 10 ) );

        if ( is_wp_error(self::$response) || empty(self::$response['body']) ) {
            return;
        }


        self::$info  = json_decode( self::$response['body'] );

        if (!is_object(self::$info)) {
            self::$info = false;
            return false;
        }

        /* error */
        if (isset(self::$info->error) && self::$info->error) {
            self::$info = false;
            return;
        }

        self::$info->slug = self::$slug;
        self::$info->plugin = self::$name;
        self::$info->last_updated = '';
        self::$info->sections =  (array) self::$info->sections;
        //self::$info->package = add_query_arg( array( 'api' => self::$api_key , 'site' => self::$domain ) , self::$info->package );
    }


    /**
     * show update nofication row -- because WP won't tell you otherwise!
     *
     * @param string  $file
     * @param array   $plugin
     */
    public static function show_update_notification( $file , $plugin ) {

        if( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        if ( self::$name != $file || !is_multisite() ) {
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

            if( version_compare( self::$current_version, self::$info->new_version, '<' ) ) {

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
            return;
        }

        $plugins = get_site_transient( 'update_plugins' );

        if( ! current_user_can( 'update_plugins' ) ) {
            self::ajax_throw_fail();
        }

        if (!isset($plugins->response[ $_REQUEST['plugin']] )) {
            self::ajax_throw_fail();
        }

        self::$info = $plugins->response[ $_REQUEST['plugin'] ];

        self::$response =  wp_remote_get(
            self::$info->package ,
            array(
                'timeout'     => 500,
                'redirection'     => 5,
                'decompress'  => false
            )
        );

        if (!empty(self::$response['body'])) {

            /* delete old plugin */
            self::delete_plugin_folder( self::$path );

            /* install new plugin */
            self::install_new_plugin();

            /* throw success */
            self::ajax_throw_success();
        } else {
            self::ajax_throw_fail();
        }
    }

    /**
     * thow successful json message if plugin update succeeded
     */
    public static function ajax_throw_success()  {
        $message = array(
            'update'=>"plugin",
            'plugin'=> $_REQUEST['plugin'],
            'slug' => $_REQUEST['slug'],
            'oldVersion'=> 'Version ' . self::$current_version,
            'newVersion' => 'Version ' . self::$info->new_version
        );

        return  wp_send_json_success($message);
    }

    /**
     * thow fail json message if plugin update succeeded
     */
    public static function ajax_throw_fail()  {
        $message = array(
            'update'=>"plugin",
            'plugin'=> $_REQUEST['plugin'],
            'slug' => $_REQUEST['slug'],
            'oldVersion'=> 'Version ' . self::$current_version,
            'newVersion' => 'Version ' . self::$info->new_version
        );

        return  wp_send_json_error(json_encode($message));
    }

    /**
     *	deletes plugin folder
     */
    public static function delete_plugin_folder($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        self::delete_plugin_folder($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            @rmdir($dirPath);
        }

    }

    /**
     * Install new plugin
     */
    public static function install_new_plugin() {
        /* load pclzip */
        include_once( ABSPATH . '/wp-admin/includes/class-pclzip.php');

        /* create temp file */
        $temp_file = tempnam('/tmp', 'TEMPPLUGIN' );

        /* write zip file to temp file */
        $handle = fopen($temp_file, "w");
        fwrite($handle, self::$response['body']);
        fclose($handle);


        /* extract temp file to plugins direction */
        $archive = new PclZip($temp_file);

        if ( $_REQUEST['slug'] == 'inbound-pro' ) {
            if (strstr(self::$info->package , 'inbound-pro.zip' )) {
                $result = $archive->extract( PCLZIP_OPT_REMOVE_PATH, 'inbound-pro' , PCLZIP_OPT_PATH, self::$path , PCLZIP_OPT_REPLACE_NEWER );
            } else {
                $result = $archive->extract( PCLZIP_OPT_REMOVE_PATH, '_inbound-now' , PCLZIP_OPT_PATH, self::$path , PCLZIP_OPT_REPLACE_NEWER );
            }
        } else {
            $result = $archive->extract( PCLZIP_OPT_PATH, self::$path , PCLZIP_OPT_REPLACE_NEWER );
        }

        if ($result == 0) {
            die("Error : ".$archive->errorInfo(true));
        }

        /* delete templ file */
        unlink($temp_file);
    }

    /**
     * Permit non standard zip files to be used in automatic update
     * @param $allow
     * @param $host
     * @param $url
     * @return bool
     */
    public static function allow_download_url( $args ) {
        $args['reject_unsafe_urls'] = false;
        return $args;
    }
}


/**
 * Class Inbound_Pro_Automatic_Updates adds Inbound Pro to the updater API
 * Inbound plugin API
 * @package     InboundPro
 * @subpackage  Updates
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
        self::$api_url =  add_query_arg( array( 'api-key' => self::$api_key , 'site' => self::$domain ), Inbound_API_Wrapper::get_pro_info_endpoint() );
    }

    /**
     * setup uploaded with custom uploaded plugin located in /assets/plugins/plugin-update-checker/
     */
    public static function setup_uploader() {
        new Inbound_Updater( self::$api_url , INBOUND_PRO_PATH , INBOUND_PRO_FILE ,  INBOUND_PRO_CURRENT_VERSION );
    }



}

new Inbound_Pro_Automatic_Updates;
