<?php
/*
Plugin Name: EasyDigitalDownloads - Inbound Now Integration
Plugin URI: http://www.inboundnow.com/
Description: Enhances the integration experience between Inbound Now Tools and Easy Digital Downloads
Version: 1.0.5
Author: Hudson Atwell
Author URI: http://www.inboundnow.com/

*/

if(!defined('INBOUNDNOW_EDD_CURRENT_VERSION')) { define('INBOUNDNOW_EDD_CURRENT_VERSION', '1.0.5' ); }
if(!defined('INBOUNDNOW_EDD_LABEL')) { define('INBOUNDNOW_EDD_LABEL' , 'Easy Digital Downloads Integration' ); }
if(!defined('INBOUNDNOW_EDD_SLUG')) { define('INBOUNDNOW_EDD_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_EDD_FILE')) { define('INBOUNDNOW_EDD_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_EDD_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_EDD_REMOTE_ITEM_NAME' , 'easydigitaldownloads-integration' ); }
if(!defined('INBOUNDNOW_EDD_URLPATH')) { define('INBOUNDNOW_EDD_URLPATH', plugins_url( ' ', __FILE__ ) ); }
if(!defined('INBOUNDNOW_EDD_PATH')) { define('INBOUNDNOW_EDD_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

if (!class_exists('Inbound_EDD')) {


class Inbound_EDD {
	
	static $map;

	public function __construct() {		
		/* Load Hooks & Filters */
		self::load_hooks();		
	}
	
	/**
	* Load Hooks & Filters 
	*/
	public static function load_hooks() {
	
		/* Setup Automatic Updating & Licensing */
		add_action('admin_init', array( __CLASS__ , 'license_setup') );
		
		/* WP-Admin Only */
		if ( is_admin() ) {
		
			/* Replace Conversions Count With Sale Count if EDD Download */
			add_filter('inbound_conversions' , array( __CLASS__ , 'replace_conversion_count' ) );	
			
			/* Add 'paid' column */
			add_filter( 'manage_wp-lead_posts_columns', array( __CLASS__ , 'add_column' ) , 20 , 1 );
			
			/* Calculate data for 'paid' column */
			add_action( 'manage_posts_custom_column', array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );
			
			/* Make 'paid' column sortable */
			add_filter( 'manage_edit-wp-lead_sortable_columns', array( __CLASS__ , 'make_sortable' ) , 10  );
			
			/* Add handler for sorting 'paid' column */
			add_action( 'pre_get_posts', array( __CLASS__ , 'sort_column' ) );  
		}
		
		/* Update/Add Lead with Cart Data */
		add_action( 'edd_complete_purchase', array( __CLASS__ , 'add_lead' ) , 10 , 1 );
		add_action( 'edd_complete_download_purchase', array( __CLASS__ , 'process_conversions') , 10 , 3 );
	}

	/*
	* Applies to: EDD Checkout
	* Depends on: Leads Plugin
	* Adds lead data to leads db on payment complete
	* @param INT 
	*/
	public static function add_lead( $payment_id ) {
		
		$user_data = edd_get_payment_meta_user_info( $payment_id );
		$cart_data = edd_get_payment_meta_cart_details($payment_id);
		
		/* Setup Field Mapping */
		(isset($user_data['email'])) ? self::$map['wpleads_email_address'] = $user_data['email'] : self::$map['wpleads_email_address'] = "";
		(isset($user_data['first_name'])) ? self::$map['wpleads_first_name'] = $user_data['first_name'] : self::$map['wpleads_first_name'] = "";
		(isset($user_data['last_name'])) ? self::$map['wpleads_last_name'] = $user_data['last_name'] : self::$map['wpleads_first_name'] = "";
		(isset($user_data['country'])) ? self::$map['wpleads_country'] = $user_data['country'] : self::$map['wpleads_country'] = "";
		(isset($user_data['address'])) ? self::$map['wpleads_address_line_1'] = $user_data['address'] : self::$map['wpleads_address_line_1'] = "";
		(isset($user_data['address_2'])) ? self::$map['wpleads_address_line_2'] = $user_data['address_2'] : self::$map['wpleads_address_line_2'] = "";
		(isset($user_data['city'])) ? self::$map['wpleads_city'] = $user_data['city'] : self::$map['wpleads_city'] = "";
		(isset($user_data['state'])) ? self::$map['wpleads_region_name'] = $user_data['state'] : self::$map['wpleads_region_name'] = "";
		(isset($user_data['postcode'])) ? self::$map['wpleads_zip'] = $user_data['postcode'] : self::$map['wpleads_zip'] = "";
		(isset($user_data['shipping_address'])) ? self::$map['wpleads_shipping_address_line_1'] = $user_data['shipping_address'] : self::$map['wpleads_shipping_address_line_1'] = "";
		(isset($user_data['shipping_address_2'])) ? self::$map['wpleads_shipping_address_line_2'] = $user_data['shipping_address_2'] : self::$map['wpleads_shipping_address_line_2'] = "";
		(isset($user_data['shipping_city'])) ? self::$map['wpleads_shipping_city'] = $user_data['shipping_city'] : self::$map['wpleads_shipping_city'] = "";
		(isset($user_data['shipping_state'])) ? self::$map['wpleads_shipping_region_name'] = $user_data['shipping_state'] : self::$map['wpleads_shipping_region_name'] = "";
		(isset($user_data['shipping_postcode'])) ? self::$map['wpleads_shipping_zip'] = $user_data['shipping_postcode'] : self::$map['wpleads_shipping_zip'] = "";
		
		/* Abort if no email detected */
		if(!isset(self::$map['wpleads_email_address']) || !strstr( self::$map['wpleads_email_address'] , '@' ) ) {
			return;
		}
		
		
		/* Populate Tracking Cookie - Create if not Available */
		if (isset($_COOKIE['wp_lead_uid'])) {
			self::$map['wp_lead_uid'] = $_COOKIE['wp_lead_uid'];	
		} else	{
			self::$map['wp_lead_uid'] = md5(self::$map['wpleads_email_address']);
			setcookie('wp_lead_uid' , self::$map['wp_lead_uid'] , time() + (20 * 365 * 24 * 60 * 60),'/');
		}
		
		
		/* Store Lead */
		$lead_id = inbound_store_lead( self::$map );
		
		/* Accociate Product Pages with Conversions */
		if ( function_exists( 'inbound_add_conversion_to_lead' ) ) {
			foreach ($cart_data as $item) {		
				$lead_data['page_id'] = $item['id'];
				$lead_data['variation'] = 0;
				inbound_add_conversion_to_lead( $lead_id , $lead_data );
			}
		}
	}
	
	/*
	* Applies to: EDD Checkout
	* Depends on: Leads Plugin
	* Adds downloads in cart as conversion items to lead
	* @param INT 
	* @param INT 
	* @param STRING 
	*/
	public static function process_conversions( $download_id, $payment_id, $download_type ) {
		
		/* First Get Custo */
	}
	
	
	/**
	* Replaces Inbound Statistics Conversion Number with Sales on WP-Admin Download Edit.php
	* @param INT 
	*/
	public static function replace_conversion_count( $conversion_count ) {
		global $post;
		
		if (!isset($post) || $post->post_type != 'download' ) {
			return $conversion_count;
		}
		
		$sales = get_post_meta( $post->ID , '_edd_download_sales' , true );
		
		return $sales;		
		
	}
	
	/**
	* Adds total spent calculation to leads listing table
	* @param array
	*/
	public static function add_column( $cols ) {
		print_r($cols);
		$cols['edd-leads-lead-investment'] = "Paid";
		return $cols;
	}
	
	/**
	* Calculates total paid for lead and returns data
	* @param string
	* @param int
	*/
	public static function prepare_column_data( $column, $post_id ) 
	{
		if( $column == "edd-leads-lead-investment") {
			$total_spent = get_post_meta( $post_id , 'edd_total_spent', true);
			if (!$total_spent)
			{
				echo edd_format_amount( 0.00 );
			}
			else
			{
				echo edd_format_amount( $total_spent );
			}
		}		
	}
	
	/**
	* Makes 'paid' column sortable
	* @param array
	*/
	public static function make_sortable($columns) {

		$columns['edd-leads-lead-investment'] = 'edd-leads-lead-investment';    
		 
		return $columns;
	}
	
	/**
	* Sort by 'paid'
	* @param array
	*/
	public static function sort_column( $query ) {  
	
        if( ! is_admin() )  {
            return;  
		}
		
        $orderby = $query->get( 'orderby');  
      
        if( 'edd-leads-lead-investment' == $orderby ) {  
            $query->set('meta_key','edd_total_spent');  
            $query->set('orderby','meta_value_num');  
        }  
    }  

	
	/**
	* Setups Software Update API 
	*/
	public static function license_setup() {
		
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUNDNOW_EDD_FILE , INBOUNDNOW_EDD_LABEL , INBOUNDNOW_EDD_SLUG , INBOUNDNOW_EDD_CURRENT_VERSION  , INBOUNDNOW_EDD_REMOTE_ITEM_NAME ) ;
		}
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


$Inbound_EDD = new Inbound_EDD();

}