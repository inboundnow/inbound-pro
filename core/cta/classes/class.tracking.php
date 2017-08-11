<?php

/**
 * Class that monitors cta link click and form submission conversions related to CTAs
 *
 * @package CTA
 * @subpackage Tracking
 */


class CTA_Conversion_Tracking {

	/**
	*  Initializes Class
	*/
	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {

		/* track masked cta links */
		add_action( 'inbound_track_link', array(__CLASS__, 'track_cta_link'));

		/* Track form submissions related to call to actions a conversions */
		add_filter('inboundnow_store_lead_pre_filter_data', array(__CLASS__, 'set_form_submission_conversion'), 20, 1 );

		/* Records impression for cta */
		add_action( 'wp_cta_record_impression', array(__CLASS__, 'record_impression_event'), 10, 1);

		/* Records impression for cta */
		add_action( 'wp_cta_record_impression', array(__CLASS__, 'update_impression_object'), 10, 1);

		/* Records conversion for cta */
		add_action( 'wp_cta_record_conversion', array(__CLASS__, 'record_conversion'), 10, 2);

	}

	/**
	*  Listens for tracked masked link processing
	*/
	public static function track_cta_link( $args ) {

		$do_not_track = apply_filters('inbound_analytics_stop_track', false );

		/* do not track if tracking disabled, if not cta, if cta is 0, or link is directly accessed without referrer */
		if ( $do_not_track || !isset($args['cta_id']) || !$args['cta_id'] || !wp_get_referer() ) {
			return;
		}


		$event = array(
			'event_name' => 'inbound_cta_click',
			'cta_id' => (isset($args['cta_id'])) ? $args['cta_id'] : 0,
			'page_id' => (isset($args['page_id'])) ? $args['page_id'] : 0,
			'variation_id' => (isset($args['vid'])) ? $args['vid'] : 0
		);

		/* for events table tracking and other hooks */
		Inbound_events::store_event($event);

		/* increase split testing conversion */
		self::record_conversion( $args['cta_id'] , $args['vid'] );

	}

	/**
	*  Listens for tracked form submissions embedded in calls to actions & incrememnt conversions
	*/
	public static function set_form_submission_conversion( $data ) {

		parse_str($data['raw_params'], $raw_post_values );

		if (!isset($raw_post_values['wp_cta_id']) || !$raw_post_values['wp_cta_id'] ) {
			return $data;
		}

		$do_not_track = apply_filters('inbound_analytics_stop_track', false );

		if ( $do_not_track ) {
			return $data;
		}

		$cta_id = $raw_post_values['wp_cta_id'];
		$vid = $raw_post_values['wp_cta_vid'];

		$lp_conversions = get_post_meta( $cta_id, 'wp-cta-ab-variation-conversions-'.$vid, true );
		$lp_conversions++;
		update_post_meta($cta_id, 'wp-cta-ab-variation-conversions-'.$vid, $lp_conversions );

		return $data;
	}


	/**
	 * Stores lead as conversion
	 * @param $args['cta_id']
	 * @param  $args['id']
	 * @param $args['vid']
	 */
	public static function store_as_conversion( $args ) {

		if (!isset($args['id']) || !$args['id'] ) {
			return;
		}

		$time = current_time( 'timestamp', 0 );
		$wordpress_date_time = date("Y-m-d G:i:s T", $time);


		$conversion_data = get_post_meta( $args['id'] , 'wpleads_conversion_data', TRUE );


		if (!$conversion_data) {
			$conversion_data = array();
		} else {
			$conversion_data = json_decode($conversion_data,true);
		}

		$conversion_data[]['id'] = $args['cta_id'];
		$conversion_data[]['variation'] = $args['vid'];
		$conversion_data[]['datetime'] = $wordpress_date_time;
		$conversion_data = json_encode($conversion_data);


		update_post_meta( $args['id'], 'wpleads_conversion_data', $conversion_data );
		update_post_meta( $args['id'], 'wpl-lead-conversion-count', count($conversion_data));

	}


	/**
	 * Adds page view event to represent the CTA impression
	 *
	 * @param INT $cta_id id of call to action
	 * @param INT $vid id of variation belonging to call to action
	 *
	 */
	public static function record_impression_event( $event ) {

		Inbound_Events::store_page_view(array(
			'page_id' => (isset($event['page_id'])) ? $event['page_id'] : 0 ,
			'cta_id' => (isset($event['cta_id'])) ? $event['cta_id'] : 0 ,
			'variation_id' => (isset($event['variation_id'])) ? $event['variation_id'] : 0 ,
		));
	}
	/**
	 * Increments impression count for given cta and variation id
	 *
	 * @param INT $cta_id id of call to action
	 * @param INT $vid id of variation belonging to call to action
	 *
	 */
	public static function update_impression_object( $event ) {

		$impressions = get_post_meta( $event['cta_id'] ,'wp-cta-ab-variation-impressions-'.$event['variation_id'], true);

		if (!is_numeric($impressions)) {
			$impressions = 1;
		} else {
			$impressions++;
		}

		update_post_meta( $event['cta_id'], 'wp-cta-ab-variation-impressions-'.$event['variation_id'], $impressions);
	}


	/**
	 * Increments conversion count for given cta id and variation id
	 *
	 * @param INT $cta_id id of call to action
	 * @param INT $vid id of variation belonging to call to action
	 *
	 */
	public static function record_conversion(	$cta_id, $vid ) {

		$conversions = get_post_meta( $cta_id, 'wp-cta-ab-variation-conversions-' . $vid, true);

		if (!is_numeric($conversions)) {
			$conversions = 1;
		} else {
			$conversions++;
		}

		update_post_meta( $cta_id, 'wp-cta-ab-variation-conversions-'.$vid, $conversions);
	}
}

new CTA_Conversion_Tracking();