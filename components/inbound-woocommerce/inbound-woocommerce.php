<?php
/*
Plugin Name: Inbound Extension - WooCommerce Integration
Plugin URI: http://www.inboundnow.com/
Description: Adds Tracking Class to Checkout Form
Version: 1.0.1
Author: Hudson Atwell
Author URI: http://www.inboundnow.com/

*/

if(!defined('INBOUNDNOW_WOOCOMMERCE_CURRENT_VERSION')) { define('INBOUNDNOW_WOOCOMMERCE_CURRENT_VERSION', '1.0.1' ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_LABEL')) { define('INBOUNDNOW_WOOCOMMERCE_LABEL' , 'WooCommerce Integration' ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_SLUG')) { define('INBOUNDNOW_WOOCOMMERCE_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_FILE')) { define('INBOUNDNOW_WOOCOMMERCE_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_WOOCOMMERCE_REMOTE_ITEM_NAME' , 'woocommerce-integration' ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_URLPATH')) { define('INBOUNDNOW_WOOCOMMERCE_URLPATH', plugins_url( ' ', __FILE__ ) ); }
if(!defined('INBOUNDNOW_WOOCOMMERCE_PATH')) { define('INBOUNDNOW_WOOCOMMERCE_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

if (!class_exists('WC_leads')) {

class WC_Leads {
	
	static $map;

	public function __construct() {
		/* Setup Automatic Updating & Licensing */
		add_action('admin_init', array( __CLASS__ , 'license_setup') );
		
		/* Add Lead on Update Order Meta */
		//add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'add_lead' ), 1000, 1 );

		/* Add Lead on Payment Complete */
		add_action( 'woocommerce_payment_complete', array( __CLASS__ , 'add_lead' ), 10, 3 );
		
		/* Remove all tracking classes from cart page */
		add_action('wp_footer' , array( __CLASS__ , 'remove_cart_tracking_class') );

	}

	/* 
	* Setups Software Update API 
	*/
	public static function license_setup() {
		
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUNDNOW_WOOCOMMERCE_FILE , INBOUNDNOW_WOOCOMMERCE_LABEL , INBOUNDNOW_WOOCOMMERCE_SLUG , INBOUNDNOW_WOOCOMMERCE_CURRENT_VERSION  , INBOUNDNOW_WOOCOMMERCE_REMOTE_ITEM_NAME ) ;
		}
	}
	
	/* 
	* Add Lead on Payment Complete 
	*/
	public static function add_lead( $id, $status = 'new', $new_status = 'pending' ) {
		global $woocommerce;
		
		$order = new WC_Order( $id );
		$cart_items = $woocommerce->cart->cart_contents;
		
		//var_dump(  $woocommerce->cart );
		//exit;
		
		/* Setup Field Mapping */
		(isset($order->billing_email)) ? self::$map['wpleads_email_address'] = $order->billing_email : self::$map['wpleads_email_address'] = "";
		(isset($order->billing_first_name)) ? self::$map['wpleads_first_name'] = $order->billing_first_name : self::$map['wpleads_first_name'] = "";
		(isset($order->billing_last_name)) ? self::$map['wpleads_last_name'] = $order->billing_last_name : self::$map['wpleads_first_name'] = "";
		(isset($woocommerce->customer->country)) ? self::$map['wpleads_country'] = $woocommerce->customer->country : self::$map['wpleads_country'] = "";
		(isset($woocommerce->customer->address)) ? self::$map['wpleads_address_line_1'] = $woocommerce->customer->address : self::$map['wpleads_address_line_1'] = "";
		(isset($woocommerce->customer->address_2)) ? self::$map['wpleads_address_line_2'] = $woocommerce->customer->address_2 : self::$map['wpleads_address_line_2'] = "";
		(isset($woocommerce->customer->city)) ? self::$map['wpleads_city'] = $woocommerce->customer->city : self::$map['wpleads_city'] = "";
		(isset($woocommerce->customer->state)) ? self::$map['wpleads_region_name'] = $woocommerce->customer->state : self::$map['wpleads_region_name'] = "";
		(isset($woocommerce->customer->postcode)) ? self::$map['wpleads_zip'] = $woocommerce->customer->postcode : self::$map['wpleads_zip'] = "";
		(isset($woocommerce->customer->shipping_address)) ? self::$map['wpleads_shipping_address_line_1'] = $woocommerce->customer->shipping_address : self::$map['wpleads_shipping_address_line_1'] = "";
		(isset($woocommerce->customer->shipping_address_2)) ? self::$map['wpleads_shipping_address_line_2'] = $woocommerce->customer->shipping_address_2 : self::$map['wpleads_shipping_address_line_2'] = "";
		(isset($woocommerce->customer->shipping_city)) ? self::$map['wpleads_shipping_city'] = $woocommerce->customer->shipping_city : self::$map['wpleads_shipping_city'] = "";
		(isset($woocommerce->customer->shipping_state)) ? self::$map['wpleads_shipping_region_name'] = $woocommerce->customer->shipping_state : self::$map['wpleads_shipping_region_name'] = "";
		(isset($woocommerce->customer->shipping_postcode)) ? self::$map['wpleads_shipping_zip'] = $woocommerce->customer->shipping_postcode : self::$map['wpleads_shipping_zip'] = "";
		
		
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
			foreach ($cart_items as $id => $item) {		
				$lead_data['page_id'] = $item['product_id'];
				$lead_data['variation'] = 0;
				inbound_add_conversion_to_lead( $lead_id , $lead_data );
			}
		}
		
	}

	/**
	*  Removes tracking class 'wpl-track-me' from cart details page.
	*/
	public static function remove_cart_tracking_class() {
		 ?>
		<script type='text/javascript'>
		  jQuery( document ).ready(function() {
			jQuery('input[name="proceed"]').closest("form").removeClass('wpl-track-me');
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


$WC_Leads = new WC_leads();


}