<?php

class Inbound_Mailer_Tracking {
	
	/**
	*  Initializes Class
	*/
	public function __construct() {
		
		self::load_hooks();
	
	}
	
	public static function load_hooks() {
		
		/* Stores custom click event */
		add_action( 'init' , array( __CLASS__ ,  'add_click_event_listener' ) , 11); // Click Tracking init
		
	}
	
	/**
	*  Record a click event in lead profile
	*/
	public static function add_click_event_listener() {
		
		if ( is_admin() ) {
			return;
		}
		
		if ( !isset($_GET['lead_id']) || !isset($_GET['email_id']) ) {
			return;
		}
		
		
		/* setup args */
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);
		$args['id'] = $_GET['email_id'];
		$args['datetime'] = $wordpress_date_time;
		$args['type'] = 'clicked-link';
		$args['urlparams'] = $_GET;
		
		/* record click event */
		Inbound_Mailer_Tracking::add_click_event( $args );
		
		
	}

	
	/**
	*  Add email click event to lead profile
	*/
	public static function add_click_event( $args ) {
		
		$events = Inbound_Mailer_Tracking::get_click_events( $_GET['lead_id'] );
	
		$events[] = $args;
		
		Inbound_Mailer_Tracking::update_events_meta(  $_GET['lead_id'] , $events );
	}
	
	
	/**
	*  Get array of email click events
	*  @param INT $lead_id
	*  @return ARRAY $events
	*/
	public static function get_click_events( $lead_id ) {
		
		$events = get_post_meta( $lead_id ,'wpleads_email_events', true);

		if (!$events) {
			$events = array();
		}
		
		return $events;
	}
	
	/**
	*  Updates email events meta pair
	*  @param INT $lead_id
	*  @param ARRAY $events new events array
	*/
	public static function update_events_meta( $lead_id , $events ) {
		update_post_meta( $lead_id , 'wpleads_email_events' , $events);
	}
}

$Inbound_Mailer_Tracking = new Inbound_Mailer_Tracking();