<?php
/*
Query Name: Lead Queries
Query Description: Definitions and Lookup Maps for Lead Data
Query Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Query_Lead' ) ) {

	class Inbound_Automation_Query_Lead {

		/**
		*  	Build Query Loopup Dataset
		*/
		public static function get_key_map( ) {

			$queries['page_views'] = __( 'Page Views' , 'ma' );
			$queries['conversions'] = __( 'Conversions' , 'ma' );
			$queries['conversion_rate'] = __( 'Conversions Rate (Use decimal format. eg: 5% = .05)' , 'ma' );

			return $queries;
		}


		/* Gets Page View Count for Lead
		* @param ARRAY $trigger_data dataset of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		*
		* @return page_views INT
		*/

		public static function query_page_views( $argument_id , $arguments ) {

			$lead_id = $arguments[$argument_id ]['id'];

			if ( !$lead_id ) {
				return null;
			}


			$page_views = get_post_meta( $lead_id ,'wpleads_page_view_count', true );

			if ( !is_numeric($page_views) ) {
				$page_views = 0;
			}

			return $page_views;
		}

		/* Gets Page Conversion Count for Lead
		* @param arguments ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		*
		* @return conversions INT
		*/
		public static function query_conversions(  $argument_id , $arguments ) {

			$lead_id = $arguments[ $argument_id ]['id'];

			if ( !$lead_id ) {
				return null;
			}

			$conversions = get_post_meta( $lead_id ,'wpleads_conversion_count', true );

			if ( !is_numeric($conversions) ) {
				$conversions = 0;
			}

			return $conversions;
		}

		/* Gets Page Conversion Rate for Lead
		* @param lead_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		*
		* @return page_views INT
		*/
		public static function query_conversion_rate(  $argument_id , $arguments ) {

			$lead_id = $arguments[ $argument_id ]['id'];

			if ( !$lead_id ) {
				return null;
			}

			$page_views = get_post_meta( $lead_id ,'wpleads_page_view_count', true );
			$conversions = get_post_meta( $lead_id ,'wpleads_conversion_count', true );

			if ( !is_numeric($page_views) || !is_numeric($conversions) ) {
				$conversion_rate = 0;
			} else {
				$conversion_rate = $conversions / $page_views ;
			}


			return $conversion_rate;
		}

	}
}