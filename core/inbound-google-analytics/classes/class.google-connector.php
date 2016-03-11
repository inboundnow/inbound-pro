<?php


if ( !class_exists('Inbound_Google_Connect') ) {

	class Inbound_Google_Connect {
		static $ga_settings;
		static $gaData;

		/**
		 *  Loads data from Inbound Cloud given parameterss
		 *
		 *  @param ARRAY $args
		 */
		public static function load_data( $args ) {
			global $post;

			self::$ga_settings = get_option('inbound_ga' , false );

			self::$gaData = new Inbound_GA_Gadata(self::$ga_settings);


			$default = array(
				'per_days' => 7 ,
				'skip' => 0,
				'query' => 'impressions',
				'page_path' => Inbound_Google_Connect::get_relative_permalink( $post->ID ),
				'check_cache' => false
			);

			$request = array_replace( $default , $args );

			if ( $request['check_cache'] == true ) {
				self::load_cached_data( $request );
			} else {
				switch( $request['query'] ) {
					case 'impressions' :
						return self::get_impressions( $request );
						break;
					case 'visitors' :
						return self::get_visitors( $request );
						break;
					case 'traffic_sources' :
						return self::get_traffic_sources( $request );
						break;
				}
			}

		}

		/**
		 * Get impressions given a URL
		 * @param $request
		 */
		public static function get_impressions( $request ) {


			$wordpress_date_time =  date_i18n('Y-m-d');
		
			if ($request['skip']) {
				$start_date = date( 'Y-m-d' , strtotime("-". $request['per_days'] * ( $request['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
				$end_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time) ));
			} else {
				$start_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time )));
				$end_date = $wordpress_date_time;
			}

			$url = add_query_arg( array(
				'ids' => 'ga:'.self::$ga_settings['linked_profile'],
				'dimensions' => 'ga:pagePath',
				'metrics' => 'ga:pageviews',
				'filters' => 'ga:pagePath==' . $request['page_path'],
				'start-date' => $start_date,
				'end-date' => $end_date,
				'max-results' => '50'
			) ,
				'https://www.googleapis.com/analytics/v3/data/ga'
			);

			$result = self::$gaData->callApi( $url );

			$impressions = 0;

			if (isset( $result->rows )) {
				foreach ( $result->rows as  $row ) {
					$impressions = $impressions + $row[1];
				}
			}
			
			return $impressions;
		}
		
		/**
		 * Get visitor count given a URL
		 * @param $request
		 */
		public static function get_visitors( $request ) {
			/*
			https://www.googleapis.com/analytics/v3/data/ga?ids=ga:76760592&dimensions=ga:pagePath&metrics=ga:visitors&filters=ga:pagePath==/wp-admin/admin.php&start-date=2015-08-27&end-date=2015-09-03&max-results=50
			*/

			$wordpress_date_time =  date_i18n('Y-m-d');
		
			if ($request['skip']) {
				$start_date = date( 'Y-m-d' , strtotime("-". $request['per_days'] * ( $request['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
				$end_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time) ));
			} else {
				$start_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time )));
				$end_date = $wordpress_date_time;
			}

			$url = add_query_arg( array(
					'ids' => 'ga:'.self::$ga_settings['linked_profile'],
					'dimensions' => 'ga:pagePath',
					'metrics' => 'ga:visitors',
					'filters' => 'ga:pagePath==' . $request['page_path'],
					'start-date' => $start_date,
					'end-date' => $end_date,
					'max-results' => '50'
				) ,
				'https://www.googleapis.com/analytics/v3/data/ga'
			);

			$result = self::$gaData->callApi( $url );

			$impressions = 0;

			if (isset( $result->rows )) {
				foreach ( $result->rows as  $row ) {
					$impressions = $impressions + $row[1];
				}
			}
			
			return $impressions;
		}

		/**
		 * Get visitor count given a URL
		 * @param $request
		 */
		public static function get_traffic_sources( $request ) {
			/*
			https://www.googleapis.com/analytics/v3/data/ga?ids=ga:76760592&dimensions=ga:pagePath&metrics=ga:visitors&filters=ga:pagePath==/wp-admin/admin.php&start-date=2015-08-27&end-date=2015-09-03&max-results=50
			*/

			$wordpress_date_time =  date_i18n('Y-m-d');
		
			if ($request['skip']) {
				$start_date = date( 'Y-m-d' , strtotime("-". $request['per_days'] * ( $request['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
				$end_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time) ));
			} else {
				$start_date = date( 'Y-m-d' , strtotime("-".$request['per_days']." days" , strtotime($wordpress_date_time )));
				$end_date = $wordpress_date_time;
			}

			$url = add_query_arg( array(
					'ids' => 'ga:'.self::$ga_settings['linked_profile'],
					'dimensions' => 'ga:sourceMedium',
					'metrics' => 'ga:pageviews',
					'filters' => 'ga:pagePath==' . $request['page_path'],
					'start-date' => $start_date,
					'end-date' => $end_date,
					'max-results' => '100'
				) ,
				'https://www.googleapis.com/analytics/v3/data/ga'
			);
			

			$result = self::$gaData->callApi( $url );

			/* get direct & search engine totals */
			$traffic_sources = array(
				'direct' => 0,
				'social' => 0,
				'3rdparty' => 0,
				'search' => 0
			);		
			
			if (isset( $result->rows )) {
				foreach ( $result->rows as  $row ) {
					if (strstr($row[0] , "(none)")){
						$type = "direct";
					} else if (strstr($row[0] , "organic")){
						$type = "search";
					} else if ( strstr($row[0] , "referral")) {
						$type = (self::check_social($row[0])) ? "social" : "3rdparty";
					}
					
					$traffic_sources[$type] = $row[1];
				
				}
			}
			

			
			return $traffic_sources;
		}
		
		

		public static function load_cached_data() {

		}

		public static function cache_data() {

		}

		public static function load_datetime_paramers() {

		}

		public static function get_relative_permalink( $post_id ) {
			$url_endpoint = get_permalink( $post_id );
			$url_endpoint = parse_url( $url_endpoint );
			$url_endpoint = $url_endpoint['path'];
			return $url_endpoint;
		}

		public static function check_social( $string ) {
			$social_sites = array('facebook','twitter','pinterest','tumblr','linkedin','Google','instagram');
			foreach ($social_sites as $site) {
				if ( stristr($string,$site) ){
					return true;
				}
			}
			
			return false;
		}
	}
}