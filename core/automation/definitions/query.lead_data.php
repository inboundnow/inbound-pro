<?php
/**
 * Lead Data Query
 * @name Lead Queries
 * @description Definitions and Lookup Maps for Lead Data
 * @uthor Inbound Now
 * @contributors Hudson Atwell
 * @package Automation
 * @subpackage Queries
 *
*/



if ( !class_exists( 'Inbound_Automation_Query_Lead' ) ) {

	class Inbound_Automation_Query_Lead {

		/**
		*  	Build Query Loopup Dataset. Keys in th queries array should also have a corresponding method inside of this class prefixed with the query_ namespace.
		*/
		public static function get_key_map( ) {

			$queries['page_view_count'] = __( 'Page view count' , 'inbound-pro' );
			$queries['form_submissions_count'] = __( 'Form submission count' , 'inbound-pro' );
			$queries['email_open_count'] = __( 'Email open count' , 'inbound-pro' );
			$queries['email_click_count'] = __( 'Email click count' , 'inbound-pro' );
			$queries['viewed_page_id'] = __( 'Lead has viewed this content (Page/Post ID)' , 'inbound-pro' );
			$queries['opened_email_id'] = __( 'Lead has opening this email (Email ID)' , 'inbound-pro' );
			$queries['clicked_cta_id'] = __( 'Lead has clicked this CTA (CTA ID)' , 'inbound-pro' );

			return $queries;
		}


		/**
		 * Look up how many email links the user has clicked
		 * @param ARRAY $trigger_data dataset of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/

		public static function query_page_view_count( $filter , $arguments ) {
			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			if ( !isset($lead_id) && !$lead_id ) {
				return null;
			}

			$results = Inbound_Events::get_page_views_by('lead_id' , array('lead_id'=>$lead_id) );

			return count($results);
		}

		/**
		 * Look up how many forms the user has submissed
		 * @param arguments ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_email_click_count(  $filter , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			if ( !isset($lead_id) && !$lead_id ) {
				return null;
			}

			$results = Inbound_Events::get_all_email_clicks($lead_id);

			return count($results);
		}

		/**
		 * Look up how many emails the lead has opened
		 * @param lead_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_email_open_count(  $filter  , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			if ( !isset($lead_id) || !$lead_id ) {
				return null;
			}

			$results = Inbound_Events::get_all_email_opens($lead_id);

			return count($results);
		}

		/**
		 * Look up how many emails the lead has opened
		 * @param lead_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_form_submissions_count(  $filter , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			if ( !isset($lead_id) || !$lead_id ) {
				return null;
			}

			$results = Inbound_Events::get_form_submissions_by('lead_id' , array('lead_id'=>$lead_id));

			return count($results);
		}

		/**
		 * Lookup if lead has viewed a specific page or not
		 * @param lead_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_viewed_page_id(  $filter , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			/* discover email id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['page_id'])) {
					$email_id = $args['page_id'];
					break;
				}
			}

			/* bail if data not set or set to 0 */
			if ( (!isset($lead_id) || !$lead_id )
				||
				(!isset($page_id) || !$page_id )
			) {
				return null;
			}

			$result = Inbound_Events::check_page_view(array(
				'lead_id'=>$lead_id,
				'page_id' => $filter['action_filter_value']
			));

			return $result;
		}

		/**
		 * Lookup if lead has opened a specific email
		 * @param tracked_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_opened_email_id(  $filter , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			/* bail if data not set or set to 0 */
			if ( !isset($lead_id) || !$lead_id ) {
				return null;
			}

			$result = Inbound_Events::check_email_open(array(
				'lead_id'=>$lead_id,
				'email_id' => $filter['action_filter_value']
			));

			return $result;
		}


		/**
		 * Lookup if lead has opened a specific email
		 * @param tracked_data ARRAY of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		 * @return INT
		*/
		public static function query_clicked_cta_id(  $filter , $arguments ) {

			/* discover lead id */
			foreach ($arguments as $key=> $args) {
				if (isset($args['lead_id'])) {
					$lead_id = $args['lead_id'];
					break;
				}
			}

			/* bail if data not set or set to 0 */
			if ( !isset($lead_id) || !$lead_id ) {
				return null;
			}

			$result = Inbound_Events::check_cta_click(array(
				'lead_id'=>$lead_id,
				'cta_id' => $filter['action_filter_value']
			));

			return $result;
		}

	}
}