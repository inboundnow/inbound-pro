<?php
/**
 * License handler for InboundNow Packaged Extensions
 *
 * This class should simplify the process of adding license information
 * to inboundnow multi-purposed extensions.
 *
 * @author  Hudson Atwell
 * @version 1.1
 */
 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! defined( 'INBOUNDNOW_STORE_URL' ) )
	define('INBOUNDNOW_STORE_URL','http://www.inboundnow.com/');
	
if ( ! class_exists( 'Inbound_License' ) ) 
{

	class Inbound_License {

		private $plugin_basename;
		private $plugin_slug;
		private $plugin_label;
		private $plugin_version;
		private $remote_download_slug;
		private $master_license_key;
		private $remote_api_url;
		
		function __construct( $plugin_file , $plugin_label , $plugin_slug , $plugin_version , $remote_download_slug ) 
		{
			$this->plugin_basename = plugin_basename( $plugin_file );
			$this->plugin_slug = $plugin_slug;
			$this->plugin_label = $plugin_label;
			$this->plugin_version = $plugin_version;			
			$this->remote_download_slug = $remote_download_slug;			
			$this->master_license_key = get_option('inboundnow_master_license_key' , '');
			$this->remote_api_url = INBOUNDNOW_STORE_URL;
			
			$this->hooks();
		}

		 
		private function hooks() {
			
			/* Add licenses key to global settings array */
			add_filter( 'lp_define_global_settings', array( $this, 'lp_settings' ), 2 );
			add_filter( 'wp_cta_define_global_settings', array( $this, 'wp_cta_settings' ), 2 );
			add_filter( 'wpleads_define_global_settings', array( $this, 'wpleads_settings' ), 2 );
			
			/* save license key data / activate license keys */
			if (is_admin())
				$this->save_license_field();
				
			/* render license key settings in license keys tab */	 
			add_action('lp_render_global_settings', array( $this, 'display_license_field' ) );
			add_action('wpleads_render_global_settings', array( $this, 'display_license_field' ) );
			add_action('wp_cta_render_global_settings', array( $this, 'display_license_field' ) );			
			
			/* add automatic updates to plugin */
			//update_option('_site_transient_update_plugins',''); //uncomment to force upload update check
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3);

		}

		public function lp_settings( $lp_global_settings ) {
			$lp_global_settings['lp-license-keys']['settings'][$this->plugin_slug] = array(
					'id' => $this->plugin_slug,
					'slug' => $this->plugin_slug,
					'remote_download_slug' => $this->remote_download_slug,
					'label' => sprintf( '%1$s' , $this->plugin_label ),
					'description' => 'Head to http://www.inboundnow.com/ to retrieve your license key for '.$this->plugin_label,
					'type' => 'inboundnow-license-key',
					'default'  => $this->master_license_key
				);
			
			return $lp_global_settings;
		}

		public function wp_cta_settings( $wp_cta_global_settings ) {
		
			$wp_cta_global_settings['wp-cta-license-keys']['settings'][$this->plugin_slug] = array(
				'id' => $this->plugin_slug,
				'slug' => $this->plugin_slug,
				'remote_download_slug' => $this->remote_download_slug,
				'label' => sprintf( '%1$s' , $this->plugin_label ),
				'description' => 'Head to http://www.inboundnow.com/ to retrieve your license key for '.$this->plugin_label,
				'type' => 'inboundnow-license-key',
				'default'  => ''
			);
			
			return $wp_cta_global_settings;
		}
		

		public function wpleads_settings( $wpleads_global_settings ) {
			$wpleads_global_settings['wpleads-license-keys']['label'] = 'License Keys';
			$wpleads_global_settings['wpleads-license-keys']['settings'][$this->plugin_slug] = array(					
					'id' => $this->plugin_slug,
					'slug' => $this->plugin_slug,
					'remote_download_slug' => $this->remote_download_slug,
					'label' => sprintf( '%1$s' , $this->plugin_label ),
					'description' => 'Head to http://www.inboundnow.com/ to retrieve your license key for '.$this->plugin_label,
					'type' => 'inboundnow-license-key',
					'default'  => $this->master_license_key
			);
			
			//print_r($lp_global_settings);exit;
			return $wpleads_global_settings;
		}



		function display_license_field($field)
		{
			if ( $field['type']=='inboundnow-license-key' &&  ($field['slug']==$this->plugin_slug) )
			{
			
				$field['id']  = "inboundnow-license-keys-".$field['slug'];			
				$field['value'] =  get_option('inboundnow_master_license_key' , '');
				
				echo '<input  type="hidden" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />';
				
				
				switch ($_GET['post_type']){
				
					case "landing-page":
						$prefix = "lp_";
						break;
					case "wp-lead":
						$prefix = "wpleads_";
						break;
					case "wp-call-to-action":
						$prefix = "wp_cta_";
						break;
					
				}
				//echo here;exit;
				$license_status = $this->check_license_status($field);

				echo '<input type="hidden" name="inboundnow_license_status-'.$field['slug'].'" id="'.$field['id'].'" value="'.$license_status.'" size="30" />';
				
				
				if ($license_status=='valid')
				{
					echo '<div class="'.$prefix.'license_status_valid">Enabled</div>';
				}
				else
				{
					echo '<div class="'.$prefix.'license_status_invalid">Disabled</div>';
				}						
				
				echo '<div class="'.$prefix.'tooltip tool_text" title="'.$field['description'].'"></div>'; 
			}
		}



		public function check_license_status($field)
		{

			$date = date("Y-m-d");
			$cache_date = get_option($field['id']."-expire");
			$license_status = get_option('inboundnow_license_status_'.$this->plugin_slug);

			/*
			echo "date: $date <br>";
			echo "cache date: $cache_date <br>";
			echo "license status: $license_status <br>";
			echo "license key: ".$field['value'];
			echo "<br>";
			*/
			
			if (isset($cache_date)&&($date<$cache_date)&&$license_status=='valid')
			{
				return "valid";
			}
			
			$api_params = array( 
				'edd_action' => 'check_license', 
				'license' => $field['value'], 
				'item_name' => urlencode( $this->remote_download_slug ) ,
				'cache_bust'=> substr(md5(rand()),0,7)
			);
					
			//print_r($api_params);
			//echo '<br>';
				
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->remote_api_url ), array( 'timeout' => 15, 'sslverify' => false ) );
			//print_r($response['body']);exit;

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			//print_r($license_data);exit;
			
			if( $license_data->license == 'valid' ) {
				$newDate = date('Y-m-d', strtotime($license_data->expires));
				update_option($field['id']."-expire", $newDate);
				return 'valid';
				// this license is still valid
			} else {
				return 'invalid';
			}
		}
		
		
		
		/* SAVE & ACTIVATE LICENSE & CHECK STATUS OF KEYS */
		 
		
		public function save_license_field()
		{
			
			if (!isset($_POST['inboundnow_master_license_key']))
				return;
			
			$field_id  = "inboundnow-license-keys-".$this->plugin_slug;		
			
			$license_status = get_option('inboundnow_license_status_'.$this->plugin_slug );
			
			$master_license_key  = $_POST['inboundnow_master_license_key'];
			
			/*
			echo "license status:".$license_status;
			echo "<br>";
			echo "new_key:".$master_license_key;
			echo "<br>";
			echo "old_key:".$this->master_license_key;
			echo "<br>";
			echo "plugin_slug:".$this->plugin_slug;
			echo "<hr>";
			*/
			
			if ($license_status=='valid' && $master_license_key == $this->master_license_key )
				return;
			
			if ( $master_license_key ) 
			{
				update_option($field_id ,$master_license_key);	
				
				// data to send in our API request
				$api_params = array( 
					'edd_action'=> 'activate_license', 
					'license' 	=> $master_license_key, 
					'item_name' =>  $this->remote_download_slug ,
					'cache_bust'=> substr(md5(rand()),0,7)
				);							
				//print_r($api_params);
				
				
				// Call the custom API.
				$response = wp_remote_get( add_query_arg( $api_params, $this->remote_api_url ), array( 'timeout' => 30, 'sslverify' => false ) );
				//echo $response['body'];
				//echo "<hr>";

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				
				// $license_data->license will be either "active" or "inactive"						
				$license_status = update_option('inboundnow_license_status_'.$this->plugin_slug, $license_data->license);
				
			} 
			elseif ( empty($master_license_key) )
			{				
				update_option($field_id , '' );
				update_option('inboundnow_license_status_'.$this->plugin_slug, 'inactive');							
			}
		}
		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update api just when Wordpress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native Wordpress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array $_transient_data Update array build by Wordpress.
		 * @return array Modified update array with custom plugin data.
		 */

		public function pre_set_site_transient_update_plugins_filter( $_transient_data ) 
		{
			
			if( empty( $_transient_data ) ) return $_transient_data;

			$to_send = array( 'slug' => $this->plugin_slug );

			$api_response = $this->api_request( );
			
			
			if( false !== $api_response && is_object( $api_response ) ) 
			{
				if( version_compare( $this->plugin_version, $api_response->new_version, '<' ) )
					$_transient_data->response[$this->plugin_basename] = $api_response;
			}
			
			return $_transient_data;
		} 


		/** Updates information on the "View version x.x details" page with custom data. */
		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		
			if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->plugin_slug ) ) return $_data;
			
			$api_response = $this->api_request();

			if ( false !== $api_response ) $_data = $api_response;
		
			return $_data;
		}

		/*** Calls the API and, if successfull, returns the object delivered by the API. */
		public function api_request(  ) {
			
			$api_params = array(
				'edd_action' 	=> 'get_version',
				'license' 		=> $this->master_license_key,
				'name' 			=> $this->remote_download_slug,
				'slug' 			=> $this->plugin_slug
			);
			
			//print_r($api_params);
			//	echo "<hr>";
			
			$request = wp_remote_post( $this->remote_api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			
			if ( !is_wp_error( $request ) ):
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if( $request )
					$request->sections = maybe_unserialize( $request->sections );
				return $request;
			else:
				return false;
			endif;
		}

	}
}	// end class_exists check

/* Legacy Class Name */
if ( !class_exists('INBOUNDNOW_EXTEND') ) {
	
	class INBOUNDNOW_EXTEND extends Inbound_License {};
	
}