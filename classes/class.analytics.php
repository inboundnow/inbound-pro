<?php

/**
*  This class loads installed extensions
*/
	
class Inbound_Analytics {
	
	/**
	*  Initiate class
	*/
	public function __construct() {
		self::load_hooks();		
	}
	
	/**
	*  Load hooks and filters
	*/
	public static function load_hooks() {
		
		/* modify localizated data to disable tracking */
		add_filter( 'inbound_analytics_localized_data' , array( __CLASS__ , 'filter_lead_tracking_rules' ) );
		
		/* prevent landing page conversions */
		add_filter( 'inbound_analytics_stop_track' , array( __CLASS__ , 'filter_conversions' ) );
		
	}
	
	/**
	*  Hooks into Inbound Analytics and enables/disabled lead tracking based on IP or is admin.
	*/
	public static function filter_lead_tracking_rules( $inbound_localized_data ) {
		
		$disable = false;
		
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		
		$ignore_admin = ( isset( $settings['inbound-analytics-rules']['exclude-admin'] ) && $settings['inbound-analytics-rules']['exclude-admin'] == 'on' ) ? true : false;
		
		/* determine if user is admin and admin filtering is on */
		if ( current_user_can( 'manage_options' ) && $ignore_admin ) {
			$disable = true;
		} 
		
		/* determine if visitor's IP address is in blacklist */
		$ip_addresses = ( isset( $settings['inbound-analytics-rules']['ip-addresses']) ) ? $settings['inbound-analytics-rules']['ip-addresses'] : array();
		if ( in_array( $inbound_localized_data['ip_address'] , $ip_addresses ) ) {
			$disable = true;
		}
		
		if (!$disable) {
			return $inbound_localized_data;
			return $inbound_localized_data;
		}
		
		$inbound_localized_data['page_tracking'] = 'off';
		$inbound_localized_data['search_tracking'] = 'off';
		$inbound_localized_data['comment_tracking'] = 'off';
		
		return $inbound_localized_data;
	}
	
	/**
	*  Prevents conversion tracking if visitor is on no track list
	*  @param BOOL $do_not_track sets to true to disable conversion tracking
	*  @return BOOL
	*/
	public static function filter_conversions( $do_not_track ) {
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		
		$ignore_admin = ( isset( $settings['inbound-analytics-rules']['exclude-admin'] ) && $settings['inbound-analytics-rules']['exclude-admin'] == 'on' ) ? true : false;
		
		/* determine if user is admin and admin filtering is on */
		if ( current_user_can( 'manage_options' ) && $ignore_admin ) {
			$do_not_track = true;
		} 
		
		/* determine if visitor's IP address is in blacklist */
		$ip_addresses = ( isset( $settings['inbound-analytics-rules']['ip-addresses']) ) ? $settings['inbound-analytics-rules']['ip-addresses'] : array();
		if ( in_array( LeadStorage::lookup_ip_address() , $ip_addresses ) ) {
			$do_not_track = true;
		}

		return $do_not_track;
	}
	
	
}

new Inbound_Analytics();