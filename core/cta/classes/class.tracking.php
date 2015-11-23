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
		add_action('inboundnow_store_lead_pre_filter_data', array(__CLASS__, 'set_form_submission_conversion'), 20, 1 );
	}

	/**
	*  Listens for tracked masked link processing
	*/
	public static function track_link( $args ) {

		$do_not_track = apply_filters('inbound_analytics_stop_track', false );

		if ( $do_not_track ) {
			return;
		}

		/* might want to study and sunset this */
		self::store_click_data( $args['cta_id'], $args['vid'] );

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
			return;
		}

		$cta_id = $raw_post_values['wp_cta_id'];
		$vid = $raw_post_values['wp_cta_vid'];

		$lp_conversions = get_post_meta( $cta_id, 'wp-cta-ab-variation-conversions-'.$vid, true );
		$lp_conversions++;
		update_post_meta($cta_id, 'wp-cta-ab-variation-conversions-'.$vid, $lp_conversions );

		return $data;
	}

	/**
	 * Store the click data to the correct CTA variation
	 *
	 * @param  INT $cta_id      cta id
	 * @param  INT $lead_id       lead id
	 * @param  INT $variation_id which variation was clicked
	 */
	public static function store_click_data($cta_id, $variation_id) {
		// If leads_triggered meta exists do this
		$event_trigger_log = get_post_meta( $cta_id, 'leads_triggered' ,true );
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);
		$conversion_count = get_post_meta($cta_id,'wp-cta-ab-variation-conversions-'.$variation_id ,true);
		$conversion_count++;
		update_post_meta($cta_id, 'wp-cta-ab-variation-conversions-'.$variation_id, $conversion_count);
		update_post_meta($cta_id, 'wp_cta_last_triggered', $wordpress_date_time ); // update last fired date
	}

	/**
	*  	Store click event to lead profile
	*
	*  @param INT $cta_id
	*/
	public static function store_as_cta_click($args) {
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);


		$args = array(
			'cta_id' => $args['cta_id'],
			'page_id' => $args['page_id'],
			'variation_id' => $args['vid'],
			'lead_id' => $args['id'],
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