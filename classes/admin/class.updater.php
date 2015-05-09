<?php

/**
 *
 *	Updater class for Inbound Pro
 *
 */


class Inbound_Pro_Updater {

    static $license_key;
    static $domain;
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
        self::$license_key = Inbound_API_Wrapper::get_api_key();
        self::$domain = site_url();
    }

    /**
     * setup uploaded with custom uploaded plugin located in /assets/plugins/plugin-update-checker/
     */
    public static function setup_uploader() {
        /**
        var_dump( wp_remote_get(add_query_arg( array( 'api' => self::$license_key , 'site' => self::$domain ), Inbound_API_Wrapper::get_pro_info_endpoint())) );
        exit;
        /**/

        $myUpdateChecker = PucFactory::buildUpdateChecker(
            add_query_arg( array( 'api' => self::$license_key , 'site' => self::$domain ), Inbound_API_Wrapper::get_pro_info_endpoint() ),
            INBOUND_PRO_FILE
        );
    }

    /**
     *  Get download zip file from inbound now
     */
    public static function get_download_zip( $download ) {

        $response = wp_remote_post( self::$downloads_fileserver_api , array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'download' => $download,
                'site' => self::$domain,
                'api' => self::$license_key
            )
        ));

        /* print error if wp_remote_post has error */
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            exit;
        }

        /* decode response from body */
        $json = $response['body'];
        $array = json_decode($json, true);

        /* if error show error message and die */
        if(isset($array['error'])) {
            echo $array['error']; exit;
        }

        return $array['url'];

    }
}

new Inbound_Pro_Updater;
