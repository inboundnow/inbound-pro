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
    public static function  count_pro_extensions( $settings ) {
        /* check for premium plugins */
        $extensions =  apply_filters( 'inbound_settings/extend' , array()) ;
        if (isset($extensions['inbound-pro-settings'])) {
            return count($extensions['inbound-pro-settings']);
        } else {
            return 0;
        }
    }


    /**
     * Counts extensions by totaling settings groups added to the Inbound Pro Extensions settings area.
     */
    public static function  count_non_core_templates( $settings ) {

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

}

