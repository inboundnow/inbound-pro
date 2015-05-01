<?php

/**
 *
 *	Updater class for Inbound Pro
 *
 */


class Inbound_Pro_Updater {

    static $license_key;
    static $pro_download_api_uri = 'http://www.inboundnow.com/';
    static $response;

    /**
     * initite class
     */
    public function __construct() {
        self::add_hooks();
        self::load_static_vars();
    }

    /**
     * Loads hooks and filters
     */
    public static function load_hooks() {
        add_action( 'admin_init' , array( __CLASS__ , 'setup_uploader' ) );
    }

    /**
     * Load static vars
     */
    public static function load_static_vars() {
        self::$license_key = self::Inbound_API_Wrapper::get_license_key();
    }

    /**
     * setup uploaded with custom uploaded plugin located in /assets/plugins/plugin-update-checker/
     */
    public static function setup_uploader() {
        $myUpdateChecker = PucFactory::buildUpdateChecker(
            add_query_arg( array( 'key' => self::$license_key ), self::$pro_download_api_uri );
            INBOUND_PRO_FILE
        );
    }

    /**
     *  Get download zip file from inbound now
     */
    public static function get_download_zip( $download ) {

        /* get license key */
        $settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
        $license_key = $settings_values['license-key']['license-key'];

        /* get domain */
        $domain = "$_SERVER[HTTP_HOST]";

        $response = wp_remote_post( self::$downloads_fileserver_api , array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'download' => $download,
                'site' => $domain,
                'api' => $license_key
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
