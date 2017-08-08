<?php

/**
 *  Class for adding wp-cron hooks to power automation and mailer components
 *
 * @package InboundPro
 * @subpackage  Cron
 */
if (!class_exists('Inbound_Cron')) {

    class Inbound_Cron {

        /**
         *  Initialize class
         */
        function __construct() {

            /* Load Hooks */
            self::load_hooks();

        }

        /*
        * Load Hooks & Filters
        */
        public static function load_hooks() {

            /* Adds 'Every Two Minutes' to System Cron */
            add_filter( 'cron_schedules', array( __CLASS__ , 'add_ping_interval' ) );

            /* create inbound_heartbeat event if does not exist */
            add_action( 'admin_init' , array( __CLASS__ , 'restore_heartbeats' ) );

            /* Adds 'Every Two Minutes' to System Cron */
            add_filter( 'cron_schedules', array( __CLASS__ , 'add_ping_interval' ) );

        }

        /**
         *  Pacemaker for the heartbeat
         */
        public static function restore_heartbeats() {
            if ( !wp_get_schedule('inbound_mailer_heartbeat') ) {
                wp_schedule_event( time(), '2min', 'inbound_mailer_heartbeat' );
            }

            if ( !wp_get_schedule('inbound_automation_heartbeat') ) {
                wp_schedule_event( time(), '2min', 'inbound_automation_heartbeat' );
            }
        }


        /**
         *  	Adds '2min' to cronjob interval options
         */
        public static function add_ping_interval( $schedules ) {
            $schedules['2min'] = array(
                'interval' => 60 * 2,
                'display' => __( 'Every Two Minutes' , 'inbound-pro' )
            );

            return $schedules;
        }


    }

    /**
     *  Load heartbeat on init
     */
    add_action('init' , function() {
        new Inbound_Cron();
    } , 1 );

}