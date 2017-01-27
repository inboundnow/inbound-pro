<?php

/* Public methods in this class will be run at least once during plugin activation script. */
/* Updater methods fired are stored in transient to prevent repeat processing */

if ( !class_exists('Inbound_Upgrade_Routines') ) {

    class Inbound_Upgrade_Routines {
        static $routines;
        static $past_version;
        static $current_version;

        /**
         * Run Generic Upgrade Routines
         */
        public static function load() {
            self::define_routines();
            self::load_routines();
        }

        /**
         *
         */
        public static function define_routines() {

            /* alter page view table */
            self::$routines['page-views-table-1'] = array(
                'id' => 'page-views-table-1',
                'scope' => 'shared',
                'introduced' => '1.0.1',
                'callback' => array( __CLASS__ , 'alter_page_views_table_1')
            );

            /* alter page view table */
            self::$routines['events-table-1'] = array(
                'id' => 'events-table-1',
                'scope' => 'shared',
                'introduced' => '1.0.1',
                'callback' => array( __CLASS__ , 'alter_events_table_1')
            );
        }

        /**
         *
         */
        public static function load_routines() {

            self::$routines = apply_filters( 'inbound-pro/upgrade-routines' , self::$routines);

            foreach (self::$routines as $routine) {
                /* set versions int static vars */
                self::set_versions($routine);

                /* compare versions and see last installed version is beneath the introduced version  */
                if ( (
                        !self::$past_version
                            ||
                        !version_compare( (int) self::$past_version , (int) $routine['introduced'] , '<')
                    )
                    &&
                    !isset($_GET['force_upgrade_routines'])
                )  {
                    continue;
                }

                /* run the routine */
                call_user_func(array($routine['callback'][0] , $routine['callback'][1]) );
            }

            /* set shared version transient */
            set_transient('inbound_shared_version' , INBOUNDNOW_SHARED_DBRV);
        }


        /**
         * @param $routine
         */
        public static function set_versions( $routine ) {
            switch($routine['scope']) {
                case 'shared':
                    self::$past_version = get_transient('inbound_shared_version');
                    self::$current_version = INBOUNDNOW_SHARED_DBRV;
                    break;
                case 'leads':
                    self::$past_version = get_transient('leads_shared_version');
                    self::$current_version = WPL_CURRENT_VERSION;
                    break;
                case 'landing-pages':
                    self::$past_version = get_transient('lp_current_version');
                    self::$current_version = LANDINGPAGES_CURRENT_VERSION;
                    break;
                case 'cta':
                    self::$past_version = get_transient('cta_current_version');
                    self::$current_version = WP_CTA_CURRENT_VERSION;
                    break;
            }
        }

        /**
         * Alter pageview table from INT to VARCHARR
         * @param $routines
         */
        public static function alter_page_views_table_1() {
            global $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $table_name = $wpdb->prefix . "inbound_page_views";

            /* add ip field if does not exist */
            $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'ip'"  );
            if(empty($row)){
                $wpdb->get_results( "ALTER TABLE {$table_name} ADD `ip` VARCHAR(45) NOT NULL" );
            }

            /* alter ip field to fix bad field types */
            $wpdb->get_results( "ALTER TABLE {$table_name} MODIFY COLUMN `ip` VARCHAR(45)" );

        }

        /**
         * @migration-type: alter inbound_events table
         * @mirgration: adds columns list_id funnel, and source to events table
         */
        public static function alter_events_table_1() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "inbound_events";

            /* add columns funnel and source to legacy table */
            $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'source'"  );
            if(empty($row)){
                // do your stuff
                $wpdb->get_results( "ALTER TABLE {$table_name} ADD `funnel` text NOT NULL" );
                $wpdb->get_results( "ALTER TABLE {$table_name} ADD `source` text NOT NULL" );
            }

            /* add columns list_id inbound events table */
            $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'list_id'"  );
            if(empty($row)){
                $wpdb->get_results( "ALTER TABLE {$table_name} ADD `list_id` varchar(255) NOT NULL" );
            }
        }
    }

    /* hook upgrade routines into activation script */
    add_action('inbound_shared_activate' , array( 'Inbound_Upgrade_Routines' , 'load') );


    if (isset($_REQUEST['force_upgrade_routines']) && $_REQUEST['force_upgrade_routines'] ) {
        Inbound_Upgrade_Routines::load();
    }
}