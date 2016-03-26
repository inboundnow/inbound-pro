<?php

/**
*  This class loads installed extensions
*/

class Inbound_Extension_Loads {

	/**
	*  Initiate class
	*/
	public function __construct() {
		self::load_hooks();
		self::load_extensions();
	}

	/**
	*  Load hooks and filters
	*/
	public static function load_hooks() {

		/* add hook to plugins_url for extension constant correction */
		add_filter( 'plugins_url' , array( __CLASS__ , 'alter_constants' ) , 1 , 3 );
	}


	/**
	*  Load extensions set as installed in the configuration dataset
	*/
	public static function load_extensions() {

	    if ( Inbound_Pro_Plugin::get_customer_status() < 1) {
	        return;
        }


		$configuration = Inbound_Options_API::get_option( 'inbound-pro' , 'configuration' , array() );
		foreach( $configuration as $key => $extension){

			if ( $extension['status'] != 'installed' || $extension['download_type'] != 'extension' || !isset($extension['filename']) ) {
				continue;
			}

			if ( file_exists( $extension['upload_path'] . '/' . $extension['filename'] . '.php' ) ) {
				include_once( $extension['upload_path'] . '/' . $extension['filename'] . '.php' );
			}
		}
	}


	/**
	*  check to see if plugins_url() is being called from an uploads folder
	*/
	public static function alter_constants( $url, $path, $plugin ) {
		/* TODO remove extra layer of folders */
		if ( !strstr($plugin , 'uploads/inbound-pro/extensions/' ) ) {
			return $url;
		}

		$parts = explode( 'uploads/inbound-pro/' , $plugin );
		$extension = explode( '/' , $parts[1]) ;

		return INBOUND_PRO_UPLOADS_URLPATH . 'extensions/' . $extension[1] . '/';
	}

}

new Inbound_Extension_Loads();