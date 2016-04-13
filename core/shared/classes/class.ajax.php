<?php

/**
 *	This class loads miscellaneous WordPress AJAX listeners
 */
if (!class_exists('Inbound_Ajax')) {

	class Inbound_Ajax {

		/**
		 *	Initializes classs
		 */
		public function __construct() {
			self::load_hooks();
		}

		/**
		 *	Loads hooks and filters
		 */
		public static function load_hooks() {

			/* Ajax that runs on pageload */
			add_action( 'wp_ajax_nopriv_inbound_ajax', array(__CLASS__, 'run_ajax_actions') );
			add_action( 'wp_ajax_inbound_ajax', array(__CLASS__, 'run_ajax_actions') );


			/* Increases the page view statistics of lead on page load */
			add_action('wp_ajax_inbound_track_lead', array(__CLASS__, 'track_lead' ) );
			add_action('wp_ajax_nopriv_inbound_track_lead', array(__CLASS__, 'track_lead' ) );

		}

		/**
		 * Executes hook that runs all ajax actions
		 */
		public static function run_ajax_actions() {

		}

		/**
		 *
		 */
		public static function track_lead() {

			global $wpdb;

			(isset(	$_POST['wp_lead_id'] )) ? $lead_data['lead_id'] = $_POST['wp_lead_id'] : $lead_data['lead_id'] = '';
			(isset(	$_POST['nature'] )) ? $lead_data['nature'] = $_POST['nature'] : $lead_data['nature'] = 'non-conversion'; /* what is nature? */
			(isset(	$_POST['json'] )) ? $lead_data['json'] = addslashes($_POST['json']) : $lead_data['json'] = 0;
			(isset(	$_POST['wp_lead_uid'] )) ? $lead_data['wp_lead_uid'] = $_POST['wp_lead_uid'] : $lead_data['wp_lead_uid'] = 0;
			(isset(	$_POST['page_id'] )) ? $lead_data['page_id'] = $_POST['page_id'] : $lead_data['page_id'] = 0;
			(isset(	$_POST['current_url'] )) ? $lead_data['current_url'] = $_POST['current_url'] : $lead_data['current_url'] = 'notfound';


			$page_views = json_decode(stripslashes($_POST['page_views']));

			/* update funnel cookie */
			if (isset($_COOKIE['inbound_page_views'])) {
				$stored_views = json_decode(stripslashes($_COOKIE['inbound_page_views']), true);
			} else {
				$stored_views = array();
			}

			foreach ($page_views as $page_id => $visits ) {
				if (!in_array($page_id, $stored_views)) {
					$stored_views[] = $page_id;
				} else {

					/* check if user doubled back to the first page to convert */
					$funnel_count = count($stored_views);
					$last_key = $funnel_count - 1;
					if ( $funnel_count > 1  && $stored_views[0] == $page_id && $stored_views[$last_key] != $page_id ){
						$stored_views[] = $page_id;
					}

				}
			}

			setcookie( 'inbound_page_views' , 	json_encode($stored_views) , time()+3600 , "/" );

			/* update lead data */
			if(isset($_POST['wp_lead_id']) && function_exists('wp_leads_update_page_view_obj') ) {
				wp_leads_update_page_view_obj($lead_data);
			}

			/* update content data */

			do_action( 'lp_record_impression', $lead_data['page_id'], $_POST['post_type'],  $_POST['variation_id'] );

			/* set lead list cookies */
			if ( function_exists('wp_leads_set_current_lists') && isset( $_POST['wp_lead_id']) && !empty( $_POST['wp_lead_id']) ) {
				wp_leads_set_current_lists( $_POST['wp_lead_id'] );
			}

			die();
		}

	}

	/* Loads Inbound_Ajax pre init */
	$Inbound_Ajax = new Inbound_Ajax();
}