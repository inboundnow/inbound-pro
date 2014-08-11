<?php
/*
Plugin Name: Inbound Extension - BuddyPress Integration
Plugin URI: http://www.inboundnow.com/
Description: Adds Tracking Class to BuddyPress registration.
Version: 1.0.1
Author: Hudson Atwell
Author URI: http://www.inboundnow.com/

*/

if(!defined('INBOUNDNOW_BUDDYPRESS_CURRENT_VERSION')) { define('INBOUNDNOW_BUDDYPRESS_CURRENT_VERSION', '1.0.1' ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_LABEL')) { define('INBOUNDNOW_BUDDYPRESS_LABEL' , 'WooCommerce Integration' ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_SLUG')) { define('INBOUNDNOW_BUDDYPRESS_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_FILE')) { define('INBOUNDNOW_BUDDYPRESS_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_BUDDYPRESS_REMOTE_ITEM_NAME' , 'woocommerce-integration' ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_URLPATH')) { define('INBOUNDNOW_BUDDYPRESS_URLPATH', plugins_url( ' ', __FILE__ ) ); }
if(!defined('INBOUNDNOW_BUDDYPRESS_PATH')) { define('INBOUNDNOW_BUDDYPRESS_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

if (!class_exists('Inbound_BuddyPress')) {

class Inbound_BuddyPress {
	
	static $map;

	public function __construct() {

		/* Setup Automatic Updating & Licensing */
		add_action('admin_init', array( __CLASS__ , 'license_setup') );
		
		/* Adds user to leads on activation */
		add_action( 'bp_core_activated_user', array( __CLASS__ , 'add_lead') , 10 , 3 );
		
		/* Updates lead profile on extended profile update */
		add_action( 'xprofile_updated_profile', array( __CLASS__ , 'add_lead') , 10 , 3 );
		
		/* Remove all tracking classes from cart page */
		add_action('wp_footer' , array( __CLASS__ , 'remove_cart_tracking_class') );

	}

	/* 
	* Setups Software Update API 
	*/
	public static function license_setup() {
		
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUNDNOW_BUDDYPRESS_FILE , INBOUNDNOW_BUDDYPRESS_LABEL , INBOUNDNOW_BUDDYPRESS_SLUG , INBOUNDNOW_BUDDYPRESS_CURRENT_VERSION  , INBOUNDNOW_BUDDYPRESS_REMOTE_ITEM_NAME ) ;
		}
	}
	
	/* 
	* Add Lead on Payment Complete 
	*/
	public static function add_lead( $user_id, $key, $user  ) {

		/* Get WordPress User Meta */
		$user_meta = get_user_meta( $user_id ); 
		
		/* Get User Email */
		$user = get_userdata( $user_id );
		$user_data = $user->data;
		
		/* Get Extended Fields */
		$extended_fields = self::get_extended_fields( $user_id );
		
		/* Map user email */
		$lead['wpleads_email_address'] = $user_data->user_email;
		
		/* Map user IP Address */
		$lead['ip_address'] = $_SERVER['REMOTE_ADDR'];
		
		/* Map User Meta Fields */
		foreach ($user_meta as $key => $value ) {
			switch ( $key ) {
				
				case 'first_name' :
				
					$lead['wpleads_first_name'] = $value[0];
					BREAK;
					
				case 'last_name' :
				
					$lead['wpleads_last_name'] = $value[0];
					BREAK;
					
				case 'billing_first_name' :
				
					$lead['wpleads_billing_first_name'] = $value[0];
					BREAK;
					
				case 'billing_last_name' :
				
					$lead['wpleads_billing_last_name'] = $value[0];
					BREAK;
					
				case 'billing_company' :
				
					$lead['wpleads_billing_company_name'] = $value[0];
					BREAK;
					
				case 'billing_address_1' :
				
					$lead['wpleads_billing_address_line_1'] = $value[0];
					BREAK;
				
				case 'billing_address_2' :
				
					$lead['wpleads_billing_address_line_2'] = $value[0];
					BREAK;
				
				case 'billing_city' :
				
					$lead['wpleads_billing_city'] = $value[0];
					BREAK;
				
				case 'billing_postcode' :
				
					$lead['wpleads_billing_zip'] = $value[0];
					BREAK;
				
				case 'billing_state' :
				
					$lead['wpleads_billing_region_name'] = $value[0];
					BREAK;
				
				case 'billing_country' :
				
					$lead['wpleads_billing_country_code'] = $value[0];
					BREAK;				
					
				case 'shipping_first_name' :
				
					$lead['wpleads_shipping_first_name'] = $value[0];
					BREAK;
					
				case 'shipping_last_name' :
				
					$lead['wpleads_shipping_last_name'] = $value[0];
					BREAK;
					
				case 'shipping_company' :
				
					$lead['wpleads_shipping_company_name'] = $value[0];
					BREAK;
					
				case 'shipping_address_1' :
				
					$lead['wpleads_shipping_address_line_1'] = $value[0];
					BREAK;
				
				case 'shipping_address_2' :
				
					$lead['wpleads_shipping_address_line_2'] = $value[0];
					BREAK;
				
				case 'shipping_city' :
				
					$lead['wpleads_shipping_city'] = $value[0];
					BREAK;
				
				case 'shipping_postcode' :
				
					$lead['wpleads_shipping_zip'] = $value[0];
					BREAK;
				
				case 'shipping_state' :
				
					$lead['wpleads_shipping_region_name'] = $value[0];
					BREAK;
				
				case 'shipping_country' :
				
					$lead['wpleads_shipping_country_code'] = $value[0];
					BREAK;
				
			}
		}

		
		/* Map Extended Fields - This will require developer intervention for propper mapping */
		$lead = apply_filters( 'inbound_buddypress_map_extended_fields' , $lead );
	
		/* Populate Tracking Cookie When Activate Hook is Fired (not when update hook is fired - Create if not Available */
		if ( current_filter() == 'bp_core_activated_user' ) {
			if (isset($_COOKIE['wp_lead_uid'])) {
				$lead['wp_lead_uid'] = $_COOKIE['wp_lead_uid'];	
			} else	{
				$lead['wp_lead_uid'] = md5($lead['wpleads_email_address']);
				$lead('wp_lead_uid' , $lead['wp_lead_uid'] , time() + (20 * 365 * 24 * 60 * 60),'/');
			}
		}

		/* Add/Update Lead Record */
		inbound_store_lead( $lead );

	}
	
	/**
	*  Gets extended fields give user id 
	*/
	public static function get_extended_fields( $user_id ) {
		
		$fields = array();
		
		/* Get User Extended Data */
		$r = bp_parse_args( $args['args'], array(
			'profile_group_id' => 0,
			'user_id'          =>  $user_id
		), 'bp_xprofile_user_admin_profile_loop_args' );

		$i = 0;
		
		if ( bp_has_profile( $r ) ) {
			
			while ( bp_profile_groups() ) {
				
				bp_the_profile_group(); 
				
				while ( bp_profile_fields() ) {
					
					bp_the_profile_field();
					$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );

					$fields[ $i ]['name'] = bp_get_the_profile_field_name();
					$fields[ $i ]['id'] = bp_get_the_profile_field_input_name();
					$fields[ $i ]['value'] = bp_get_the_profile_field_edit_value();
	
					$i++;
				}
			}		
		}
		return $fields;
		
	}
	
	/**
	*  Removes tracking class 'wpl-track-me' from cart details page.
	*/
	public static function remove_cart_tracking_class() {
		 ?>
		<script type='text/javascript'>
		  jQuery( document ).ready(function() {

		  })
		</script>
		<?php
	}
	
	
	/**
	 * Helper log function for debugging
	 *
	 * @since 1.2.2
	 */
	static function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

}


$Inbound_BuddyPress = new Inbound_BuddyPress();


}