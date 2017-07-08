<?php

/* Public methods in this class will be run at least once during plugin activation script. */
/* Updater methods fired are stored in transient to prevent repeat processing */

if ( !class_exists('Inbound_Upgrade_Routines') ) {

    class Inbound_Upgrade_Routines {
        static $routines;
        static $past_version;
        static $current_version;

        /**
         * Run upgrade routines defined in this class
         */
        public static function load() {
            self::define_routines();
            self::load_routines();
        }

        /**
         * Add fallback listener to make sure database upgrade routines are ran even if the activation protocol does not fire.
         * This function compares the shared files version in the database to the INBOUNDNOW_SHARED_DBRV constant. If they aren't the same then it runs the routines.
         *
         */
        public static function add_update_check() {
            self::set_versions( array('scope'=>'shared') );
            if ( self::$past_version != self::$current_version ) {
                self::load();
            }
        }

        /**
         * Defines upgrade routines to run.
         */
        public static function define_routines() {

            /* alter page view table */
            self::$routines['page-views-table-1'] = array(
                'id' => 'page-views-table-1',
                'scope' => 'shared',
                'introduced' => '1.0.2',
                'callback' => array( __CLASS__ , 'alter_page_views_table_1')
            );

            /* alter page view table */
            self::$routines['events-table-1'] = array(
                'id' => 'events-table-1',
                'scope' => 'shared',
                'introduced' => '1.0.2',
                'callback' => array( __CLASS__ , 'alter_events_table_1')
            );

            /* alter events table */
            self::$routines['events-table-2'] = array(
                'id' => 'events-table-2',
                'scope' => 'shared',
                'introduced' => '1.0.5',
                'callback' => array( __CLASS__ , 'alter_events_table_1_0_5')
            );

            /* alter events table */
            self::$routines['events-table-3'] = array(
                'id' => 'events-table-3',
                'scope' => 'shared',
                'introduced' => '1.0.8',
                'callback' => array( __CLASS__ , 'alter_events_table_1_0_8')
            );

            /* alter automation queue table */
            self::$routines['automation-queue-table-1'] = array(
                'id' => 'automation-queue-table-1',
                'scope' => 'shared',
                'introduced' => '1.0.3',
                'callback' => array( __CLASS__ , 'alter_automation_queue_table_1')
            );


            /* alter events table */
            self::$routines['events-pageviews-107'] = array(
                'id' => 'events-pageviews-107',
                'scope' => 'shared',
                'introduced' => '1.0.7',
                'callback' => array( __CLASS__ , 'alter_events_pageviews_107')
            );

            /* alter events table */
            self::$routines['inbound-settings-109'] = array(
                'id' => 'inbound-settings-109',
                'scope' => 'shared',
                'introduced' => '1.0.9',
                'callback' => array( __CLASS__ , 'alter_inbound_settings_109')
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
                if (
                    self::$past_version
                    &&
                    !version_compare( self::$past_version , $routine['introduced'] , '<')
                    &&
                    !isset($_GET['force_upgrade_routines'])
                )  {
                    continue;
                }

                /* run the routine */
                call_user_func(array($routine['callback'][0] , $routine['callback'][1]) );
            }

            /* set shared version transient */
            update_option('inbound_shared_version' , INBOUNDNOW_SHARED_DBRV , false);
        }


        /**
         * @param $routine
         */
        public static function set_versions( $routine ) {
            switch($routine['scope']) {
                case 'shared':
                    self::$past_version = get_option('inbound_shared_version');
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

            $col_check = $wpdb->get_row("SELECT * FROM " . $table_name);

            if(!isset($col_check->ip)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `ip` VARCHAR(45) NOT NULL");
            } else {
                $wpdb->get_results( "ALTER TABLE {$table_name} MODIFY COLUMN `ip` VARCHAR(45)" );
            }

        }

        /**
         * @migration-type: alter inbound_events table
         * @mirgration: adds columns list_id funnel, and source to events table
         */
        public static function alter_events_table_1() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "inbound_events";

            $col_check = $wpdb->get_row("SELECT * FROM " . $table_name);

            if(!isset($col_check->funnel)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `funnel` text NOT NULL");
            }

            if(!isset($col_check->source)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `source` text NOT NULL");
            }

            if(!isset($col_check->list_id)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `list_id` mediumint(20) NOT NULL");
            }

        }

        /**
         * @migration-type: alter inbound_events table
         * @mirgration: adds columns list_id funnel, and source to events table
         */
        public static function alter_events_table_1_0_5() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "inbound_events";

            $col_check = $wpdb->get_row("SELECT * FROM " . $table_name);

            if(!isset($col_check->rule_id)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `rule_id` mediumint(20) NOT NULL");
            }

            if(!isset($col_check->job_id)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `job_id` mediumint(20) NOT NULL");
            }
        }


        /**
         * @migration-type: alter inbound_events table
         * @mirgration: adds columns list_id funnel, and source to events table
         */
        public static function alter_events_table_1_0_8() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "inbound_events";

            $col_check = $wpdb->get_row("SELECT * FROM " . $table_name);

            if(!isset($col_check->comment_id)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `comment_id` mediumint(20) NOT NULL");
            }
        }

        /**
         * @migration-type: alter inbound_events,inbound_pageviews table
         * @mirgration: convert page_id to VARCHAR to accept complex ids related to taxonomy archives
         */
        public static function alter_events_pageviews_107() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            /* events table */
            $table_name = $wpdb->prefix . "inbound_events";
            $wpdb->get_results( "ALTER TABLE {$table_name} MODIFY COLUMN `page_id` VARCHAR(20)" );

            /* pageviews table */
            $table_name = $wpdb->prefix . "inbound_page_views";
            $wpdb->get_results( "ALTER TABLE {$table_name} MODIFY COLUMN `page_id` VARCHAR(20)" );
        }


        /**
         * @migration-type: alter inbound_automation_queue table
         * @mirgration: adds columns lead_id
         */
        public static function alter_automation_queue_table_1() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "inbound_automation_queue";

            $col_check = $wpdb->get_row("SELECT * FROM " . $table_name);

            if(!isset($col_check->lead_id)) {
                $wpdb->get_results("ALTER TABLE {$table_name} ADD `lead_id` mediumint(20)  NOT NULL");
            }
        }


        /**
         * @migration-type: inbound pro settings array
         * @mirgration: change the 'mailer' key to 'mailer' key in $inbound_settings
         */
        public static function alter_inbound_settings_109() {
            if (class_exists('Inbound_Options_API')) {
                $inbound_settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                $inbound_settings['mailer'] = (isset($inbound_settings['mailer'])) ? $inbound_settings['mailer'] : array();
                unset($inbound_settings['inbound-mailer']);
                Inbound_Options_API::update_option('inbound-pro', 'settings', $inbound_settings);
            }
        }
    }

    /* set fallback action in case routines do not run via activation */
    add_action('admin_init' , array( 'Inbound_Upgrade_Routines' , 'add_update_check') );


    /**
     * Listen for Database Repair Call
     */
    if (isset($_REQUEST['force_upgrade_routines']) && $_REQUEST['force_upgrade_routines'] ) {
        Inbound_Events::create_page_views_table();
        Inbound_Events::create_events_table();
        if (class_exists('Inbound_Automation_Activation')) {
            Inbound_Automation_Activation::create_automation_queue_table();
        }
        if (class_exists('Inbound_Mailer_Activation')) {
            Inbound_Mailer_Activation::create_email_queue_table();
        }
        Inbound_Upgrade_Routines::load();
    }
}