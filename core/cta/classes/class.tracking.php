<?php

class CTA_Conversion_Tracking {

	/**
	*  Initializes Class
	*/
	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {

		/* track masked cta links */
		add_action( 'inbound_track_link', array(__CLASS__, 'track_link'));

		/* Track form submissions related to call to actions a conversions */
		add_filter('inboundnow_store_lead_pre_filter_data', array(__CLASS__, 'set_form_submission_conversion'), 20, 1 );
	}

	/**
	*  Listens for tracked masked link processing
	*/
	public static function track_link( $args ) {

		$do_not_track = apply_filters('inbound_analytics_stop_track', false );

		if ( $do_not_track || !isset($args['cta_id']) || !$args['cta_id'] ) {
			return;
		}

		/* store click event in inbound_events table and legacy lead metadata */
		self::store_as_cta_click($args);

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
	*  	Store click event to lead profile
	*
	*  @param INT $cta_id
	*/
	public static function store_as_cta_click($args) {
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);

		/* do not record directly accessed links as conversion */
		if (!wp_get_referer()) {
			return;
		}

		$args = array(
			'cta_id' => (isset($args['cta_id'])) ? $args['cta_id'] : '',
			'page_id' => (isset($args['page_id'])) ? $args['page_id'] : '',
			'variation_id' => (isset($args['vid'])) ? $args['vid'] : '',
			'lead_id' => (isset($args['id'])) ? $args['id'] : '',
			'datetime' => $wordpress_date_time
		);

		/* for events table tracking and other hooks */
		do_action('inbound_tracked_cta_click' , $args);

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
}

$CTA_Conversion_Tracking = new CTA_Conversion_Tracking();