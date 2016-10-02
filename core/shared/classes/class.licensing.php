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


if ( ! defined( 'ABSPATH' ) ) { exit; } /* Exit if accessed directly */


if ( ! defined( 'INBOUNDNOW_STORE_URL' ) ) {
	define('INBOUNDNOW_STORE_URL', 'http://www.inboundnow.com/');
}

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

		function __construct( $plugin_file, $plugin_label, $plugin_slug, $plugin_version, $remote_download_slug )
		{

			$this->plugin_basename = plugin_basename( $plugin_file );
			$this->plugin_slug = $plugin_slug;
			$this->plugin_label = $plugin_label;
			$this->plugin_version = $plugin_version;
			$this->remote_download_slug = $remote_download_slug;
			$this->master_license_key = (defined('INBOUND_ACCESS_LEVEL')) ? Inbound_API_Wrapper::get_api_key() : get_option('inboundnow_master_license_key', '');
			$this->remote_api_url = INBOUNDNOW_STORE_URL;

			$this->hooks();
		}


		private function hooks() {

			/* add automatic updates to plugin */
			/*update_option('_site_transient_update_plugins',''); //uncomment to force upload update check */
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3);

			/* render license key settings in license keys tab */
			if (defined('INBOUND_ACCESS_LEVEL') ) {
				return;
			}

		}

		public function check_license_status($field){

			$date = date("Y-m-d");
			$cache_date = get_transient($field['id']."-expire");
			$license_status = get_option('inboundnow_license_status_'.$this->plugin_slug);


			if (isset($cache_date)&&$license_status=='active') {
				return "valid";
			}

			$api_params = array(
				'edd_action' => 'inbound_check_license',
				'license' => $field['value'],
				'item_name' => urlencode( $this->remote_download_slug ) ,
				'cache_bust'=> substr(md5(rand()),0,7)
			);

			/* Call the custom API. */
			$response = wp_remote_get( add_query_arg( $api_params, $this->remote_api_url ), array( 'timeout' => 30, 'sslverify' => false ) );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( $license_data->license == 'active' ) {
				$newDate = date('Y-m-d', strtotime($license_data->expires));
				set_transient($field['id']."-expire", true, YEAR_IN_SECONDS / 2 );
				return 'active';
				/* this license is still valid */
			} else {
				return 'inactive';
			}
		}

		public function pre_set_site_transient_update_plugins_filter( $_transient_data )
		{

			if( empty( $_transient_data ) ) {
				return $_transient_data;
			}

			$to_send = array( 'slug' => $this->plugin_slug );

			$api_response = $this->api_request( );


			if( false !== $api_response && is_object( $api_response ) ) {
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
		public function api_request() {

			$api_params = array(
				'edd_action' 	=> 'inbound_get_version',
				'license' 		=> $this->master_license_key,
				'name' 			=> $this->remote_download_slug,
				'slug' 			=> $this->plugin_slug
			);

			/*print_r($api_params); */
			/*	echo "<hr>"; */

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
}	/* end class_exists check */

/* Legacy Class Name */
if ( !class_exists('INBOUNDNOW_EXTEND') ) {
	if (
		!defined('INBOUND_ACCESS_LEVEL')
		||
		( defined('INBOUND_ACCESS_LEVEL') && INBOUND_ACCESS_LEVEL < 1 )
	) {
		class INBOUNDNOW_EXTEND extends Inbound_License {
		}

		;
	}

}