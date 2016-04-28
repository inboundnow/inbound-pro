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

		/**
		 * @param $webhook_args
		 * @return mixed
		 */
		public static function get_domains( ){

			$request_url = 'https://api.sparkpost.com/api/v1/sending-domains';

			$domain_args = array(
				'match' => null,
				'limits' => null
			);

			$args = array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Accecpt'  => 'application/json',
					'Authorization' =>  self::$apikey

				),
				'body' => null,
				'cookies' => array()
			);

			$response    = wp_remote_get( add_query_arg($domain_args , $request_url), $args );

			return json_decode($response['body'] , true);

		}

		/**
		 * @param $webhook_args
		 * @return mixed
		 */
		public static function get_webhook( $id ){

			$request_url = 'https://api.sparkpost.com/api/v1/webhooks/' . trim($id);

			$webhook_args = array(
				//'timezone' => ''
			);

			$args = array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Accecpt'  => 'application/json',
					'Authorization' =>  self::$apikey

				),
				'body' => null,
				'cookies' => array()
			);

			$response = wp_remote_get( add_query_arg($webhook_args , $request_url), $args );

			return json_decode($response['body'] , true);

		}

		/**
		 * @param $webhook_args
		 * @return mixed
		 */
		public static function create_webhook( $webhook_args ){

			$request_url = 'https://api.sparkpost.com/api/v1/webhooks';
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
				'body' => json_encode($webhook_args),
				'cookies' => array()
			);


			$response = wp_remote_post( $request_url, $args );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				error_log($request_url);
				error_log(print_r($args,true));
				error_log(print_r($response,true));
			}

			return json_decode($response['body'] , true);

		}

		/**
		 * @param $transmission_args
		 * @return mixed
		 */
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


			$response = wp_remote_post( $request_url, $args );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				error_log($request_url);
				error_log(print_r($args,true));
				error_log(print_r($response,true));
			}

			return json_decode($response['body'] , true);

		}


		public static function get_campaign_metrics( $campaign_ids , $from , $to ) {

			if (is_array($campaign_ids)) {
				$campaign_ids = implode(',',$campaign_ids);
			}

			$request_url = 'https://api.sparkpost.com/api/v1/metrics/deliverability/campaign/';

			$metric_args = array(
				'from' => $from,
				'to' => $to, //2014-07-11T08:00
				'campaigns' => $campaign_ids,
				'metrics' => 'count_sent,count_accepted,count_bounce,count_hard_bounce,count_soft_bounce,count_rejected,count_rendered,count_unique_rendered,count_unique_clicked,count_clicked,count_rejected,count_spam_complaint'
				//https://github.com/SparkPost/sparkpost-api-documentation/blob/master/services/metrics_api.md
			);

			$args = array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Accecpt'  => 'application/json',
					'Authorization' =>  self::$apikey

				),
				'body' => null,
				'cookies' => array()
			);

			$response    = wp_remote_get( add_query_arg($metric_args , $request_url), $args );
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				error_log(print_r($response,true));
				print_r(print_r($response));
			}

			return json_decode($response['body'] , true);
		}

		public function log($msg) {
			if(self::debug) error_log($msg);
		}
	}

}