<?php

/**
*  Inbound Heartbeat adds a 3 minute cron hook for processing Inbound Now actions
*/
if (!class_exists('Inbound_Heartbeat')) {

	class Inbound_Heartbeat {

		static $event_hook_name;
		static $definitions;
		static $queue;

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

			//set_time_limit ( 0 );
			//ignore_user_abort ( true );

			/* create inbound_heartbeat event if does not exist */
			add_action( 'admin_init' , array( __CLASS__ , 'restore_heartbeat' ) );

			/* Adds 'Every Two Minutes' to System Cron */
			add_filter( 'cron_schedules', array( __CLASS__ , 'add_ping_interval' ) );

		}

		/**
		*  Pacemaker for the heartbeat
		*/
		public static function restore_heartbeat() {
			if ( !wp_get_schedule('inbound_heartbeat') ) {
				wp_schedule_event( time(), '2min', 'inbound_heartbeat' );
			}
		}

		/*
		* Adds Cron Hook to System on Activation  - Create inbound_automation_queue in wp_options
		*/
		public static function activation() {
			/* Adds 'Every Two Minutes' to System Cron */
			add_filter( 'cron_schedules', array( __CLASS__ , 'add_ping_interval' ) );

			wp_schedule_event( time(), '2min', 'inbound_heartbeat' );
			add_option( 'inbound_automation_queue' , null , null , 'no' );
		}

		/*
		* Adds Cron Hook to System on Activation
		*/
		public static function deactivation() {
			wp_clear_scheduled_hook( 'inbound_heartbeat' );
		}


		/**
		*  	Adds '3min' to cronjob interval options
		*/
		public static function add_ping_interval( $schedules ) {
			$schedules['2min'] = array(
				'interval' => 60 * 2,
				'display' => __( 'Every Two Minutes' , 'inbound-email' )
			);

			return $schedules;
		}


	}

	/**
	*  Load heartbeat on init
	*/
	add_action('init' , function() {
		$GLOBALS['Inbound_Heartbeat'] = new Inbound_Heartbeat();
	} , 1 );

	/* Register Activation Hooks */
	register_activation_hook( INBOUND_EMAIL_FILE , array( 'Inbound_Heartbeat' , 'activation' ) );
	register_deactivation_hook( INBOUND_EMAIL_FILE , array( 'Inbound_Heartbeat' , 'deactivation' ) );

}