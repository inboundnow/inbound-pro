<?php

if ( !class_exists('Inbound_SparkPost') ) {


	class Inbound_SparkPost {

		static $apikey;
		static $debug = false;

		/**
		*  Initialize Class
		*/
		public function __construct( $apikey ) {

			self::$apikey = $apikey;
		}

		public static function send( $transmission_args ){

			$request_url = 'https://api.sparkpost.com/api/v1/transmissions';
			$args = array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-type'  => 'application/json',
					'Authorization' =>  self::$apikey,
					'User-Agent'    => 'sparkpost-inbound',
				),
				'body' => json_encode($transmission_args),
				'cookies' => array()
			);


			$response    = wp_remote_post( $request_url, $args );
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				error_log(print_r($response,true));
			}

			return $response;

		}
		public function log($msg) {
			if(self::debug) error_log($msg);
		}
	}

}