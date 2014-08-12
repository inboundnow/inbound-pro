<?php


if ( !class_exists('Inbound_Analytics_Connect') ) {

	class Inbound_Analytics_Connect {
		
		static $range;
		

		/**
		*  Loads data from Inbound Cloud given parameterss
		*  
		*  @param ARRAY $args
		*/
		public static function load_data( $args ) {
			
			$default = array(
				'per_days' => 7 ,
				'skip' => 0,
				'query' => 'count_impressions',
				'check_cache' => false,
				//'cache_range' => 'last_7_days'
			);
			
			$request = array_replace( $default , $args );
			
			if ( $request['check_cache'] == true ) {
				Inbound_Analytics_Connect::load_cached_data( $request );
			}
			
		}
		
		public static function load_cached_data() {
		
		}
		
		public static function cache_data() {
			//delete_option('
		}
		
		public static function load_datetime_paramers() {
		
		}
		
		
		
	}
}