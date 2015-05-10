<?php

class InboundUseTracking {

    public function __construct() {
       self::load_hooks();
    }


    public static function load_hooks() {

    }

    /**
     * Counts extensions by totaling settings groups added to the Inbound Pro Extensions settings area.
     */
    public static function  count_pro_extensions( ) {
        /* check for premium plugins */
        $extensions =  apply_filters( 'inbound_settings/extend' , array()) ;
        if (isset($extensions['inbound-pro-settings'])) {
            return count($extensions['inbound-pro-settings']);
        } else {
            return 0;
        }
    }


    /**
     * Counts templates by reading directories in each plugin's updload folder
     */
    public static function  count_non_core_templates( ) {

        /* count templates in landing pages uploads folder */
        if( is_defined('LANDINGPAGES_UPLOADS_PATH') ) {
            $templates['landing-pages'] = self::count_templates( LANDINGPAGES_UPLOADS_PATH );
        }

        /* count templates in calls to action uploads folder */
        if( is_defined('WP_CTA_UPLOADS_PATH') ) {
            $templates['cta'] = self::count_templates( LANDINGPAGES_UPLOADS_PATH );
        }

        /* count templates in mailer uploads folder */
        if( is_defined('INBOUND_EMAIL_PATH') ) {
            $templates['mailer'] = self::count_templates( INBOUND_EMAIL_PATH );
        }

        return $templates;

    }

    /**
     * Counts the number of first level child folders of a parent folder
     * @param $directory
     * @return array|string
     */
    public static function count_templates( $directory ) {
        /* count themes in landing pages uploads folder */
        if ( !$handle = opendir( $directory ) ) {
            return $count['error'] = "directory doesnt exist";
        }


        $templates = array();
        while ( false !== ( $name = readdir($handle) ) ) {

            if ($name == "." && $name == "..") {
                continue;
            }

            if (is_dir($name)) {
                echo "Folder => " . $name . "<br>";
                $templates['templates'][] = $name;
            }

        }

        $templates['count'] = count($templates);

        return $templates;
    }

    /**
     * Checks if using inbound pro and if user's license is active
     */
     public static function get_pro_user_data() {
        $pro['installed'] = false;
        $pro['active_license'] = false;

        if (is_defined('INBOUND_PRO_PATH')) {
            $pro['installed'] = true;

            if (self::get_customer_status()) {
                $pro['active_license'] = true;
            }
        }

        return $pro;
     }

}

