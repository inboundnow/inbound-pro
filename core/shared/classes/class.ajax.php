<?php

/**
 *	This class loads miscellaneous WordPress AJAX listeners
 */
if (!class_exists('Inbound_Ajax')) {

	class Inbound_Ajax {

		/**
		 *    Initializes classs
		 */
		public function __construct() {
			self::load_hooks();
		}

		/**
		 *    Loads hooks and filters
		 */
		public static function load_hooks() {

			/* Ajax that runs on pageload */
			add_action('wp_ajax_nopriv_inbound_ajax', array(__CLASS__, 'run_ajax_actions'));
			add_action('wp_ajax_inbound_ajax', array(__CLASS__, 'run_ajax_actions'));


			/* Increases the page view statistics of lead on page load */
			add_action('wp_ajax_inbound_track_lead', array(__CLASS__, 'track_lead'));
			add_action('wp_ajax_nopriv_inbound_track_lead', array(__CLASS__, 'track_lead'));

		}

		/**
		 * Executes hook that runs all ajax actions
		 */
		public static function run_ajax_actions() {

		}

		/**
		 * Listen for page view event
		 */
		public static function track_lead() {

			global $wpdb;

			/* check for known bots and ignore */
			if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
				return;
			}

			$lead_data['lead_id'] = (isset($_POST['wp_lead_id'])) ? $_POST['wp_lead_id'] : '';
			$lead_data['nature'] = (isset($_POST['nature'])) ? $_POST['nature'] : 'non-conversion'; /* what is nature? */
			$lead_data['json'] = (isset($_POST['json'])) ? addslashes($_POST['json']) : 0;
			$lead_data['wp_lead_uid'] = (isset($_POST['wp_lead_uid'])) ? $_POST['wp_lead_uid'] : 0;
			$lead_data['page_id'] = (isset($_POST['page_id'])) ? $_POST['page_id'] : 0;
			$lead_data['current_url'] = (isset($_POST['current_url'])) ? $_POST['current_url'] : 'notfound';
			$lead_data['variation_id'] = (isset($_POST['variation_id'])) ? $_POST['variation_id'] : '0';

			$timezone_format = 'Y-m-d G:i:s T';
			$lead_data['datetime'] =  date_i18n($timezone_format);

			$page_views = stripslashes($_POST['page_views']);

			$page_views = ($page_views) ? $page_views : '';
			$lead_data['event_details']['funnel'] = json_decode($page_views,true);
			$lead_data['funnel'] = $page_views;

			/* update funnel cookie */
			if (isset($_COOKIE['inbound_page_views']) && !$page_views) {
				$_SESSION['inbound_page_views'] = stripslashes($_COOKIE['inbound_page_views']);
			} else {
				$_SESSION['inbound_page_views'] = $page_views;
			}

			/* update lead data and set lead lists into cookies */
			if ($lead_data['lead_id']) {
				self::update_page_view_obj($lead_data);
				self::set_current_lists($lead_data['lead_id']);
			}

			/* create page_view event */
			if ($lead_data['page_id']) {
				Inbound_Events::store_page_view($lead_data);
			}

			/* record CTA impressions */
			$cta_impressions = ( isset($_POST['cta_impressions']) ) ? json_decode(stripslashes($_POST['cta_impressions']),true) : array();

			foreach ( $cta_impressions as $cta_id => $vid ) {
				do_action('wp_cta_record_impression', (int) $cta_id, (int) $vid );
			}

			/* update content data */
			do_action('lp_record_impression', $lead_data['page_id'], $_POST['post_type'], $_POST['variation_id']);

			die();
		}

		public static function update_page_view_obj($lead_data) {

			if (!$lead_data['page_id']) {
				return;
			}

			$current_page_view_count = get_post_meta($lead_data['lead_id'], 'wpleads_page_view_count', true);
			$increment_page_views = $current_page_view_count + 1;

			update_post_meta($lead_data['lead_id'], 'wpleads_page_view_count', $increment_page_views); // update count

			$time = current_time('timestamp', 0); // Current wordpress time from settings
			$wordpress_date_time = date("Y-m-d G:i:s T", $time);
			$page_view_data = get_post_meta($lead_data['lead_id'], 'page_views', TRUE);


			// If page_view meta exists do this
			if ($page_view_data) {
				$current_count = 0; // default
				$timeout = 30;  // 30 Timeout analytics tracking for same page timestamps
				$page_view_data = json_decode($page_view_data, true);
				// increment view count on page
				if (isset($page_view_data[$lead_data['page_id']])) {
					$current_count = count($page_view_data[$lead_data['page_id']]);
					$last_view = $page_view_data[$lead_data['page_id']][$current_count];
					$timeout = abs(strtotime($last_view) - strtotime($wordpress_date_time));
				}
				// If page hasn't been viewed in past 30 seconds. Log it
				if ($timeout >= 30) {
					$page_view_data[$lead_data['page_id']][$current_count + 1] = $wordpress_date_time;
					$page_view_data = json_encode($page_view_data);
					update_post_meta($lead_data['lead_id'], 'page_views', $page_view_data);
				}
			} else {
				// Create page_view meta if it doesn't exist
				$page_view_data = array();
				$page_view_data[$lead_data['page_id']][0] = $wordpress_date_time;
				$page_view_data = json_encode($page_view_data);
				update_post_meta($lead_data['lead_id'], 'page_views', $page_view_data);
			}
			/* Run hook that tells WordPress lead data has been updated */
			do_action('wplead_page_view', $lead_data);
		}

		public static function set_current_lists($lead_id) {
			$terms = get_the_terms( $lead_id , 'wplead_list_category' );

			if ( $terms && ! is_wp_error( $terms ) ) {
				$lead_list = array();
				$count = 0;

				foreach ( $terms as $term ) {
					$lead_list[] = $term->term_id;
					$count++;
				}

				$list_array = json_encode(array('ids' => $lead_list));;
				setcookie('wp_lead_list', $list_array, time() + (20 * 365 * 24 * 60 * 60), '/');
			}
		}

	}

	/* Loads Inbound_Ajax pre init */
	$Inbound_Ajax = new Inbound_Ajax();
}