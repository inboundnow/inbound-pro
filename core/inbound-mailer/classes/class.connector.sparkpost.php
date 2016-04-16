<?php

if ( !class_exists('Inbound_SparkPost') ) {


	class Inbound_SparkPost {

		public $apikey;
		public $debug = false;

		/**
		*  Initialize Class
		*/
		public function __construct( $apikey ) {

			self::apikey = $apikey;
		}

		public static function send( $args ){

			$request_url = 'https://api.sparkpost.com/api/v1/transmissions';
			$response    = wp_remote_post( $request_url, $args );
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

		}
		public function log($msg) {
			if(self::debug) error_log($msg);
		}
	}

}