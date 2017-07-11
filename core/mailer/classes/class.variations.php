<?php

/**
 * Class interface for retrieving email variation elements and recording "online version" event statistics
 * @package Mailer
 * @subpackage Variations
 */

class Inbound_Mailer_Variations {

	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {

		/* Filter to add variation id to end of meta key */
		add_filter( 'inbound_email_prepare_input_id' , array( __CLASS__ , 'prepare_input_id' ) );

		/* Appends variation id to given url */
		add_filter( 'inbound_email_customizer_customizer_link', array( __CLASS__ , 'append_variation_id_to_url') );
		add_filter( 'post_type_link', array( __CLASS__ , 'append_variation_id_to_url') );

		/* Records impression for cta */
		add_action( 'inbound_email_record_impression' , array( __CLASS__ , 'record_impression' ) , 10, 2);

		/* Records conversion for cta */
		add_action( 'inbound_email_record_conversion' , array( __CLASS__ , 'record_conversion' ) , 10, 2);
	}


	/**
	* Deletes variation for	a call to action
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation to delete
	*
	*/
	public static function delete_variation( $inbound_email_id	,	$vid ) {

		/* Update variations meta object */
		$variations = self::get_variations( $inbound_email_id );
		unset($variations[$vid]);

		self::update_variations( $inbound_email_id , $variations );

		/* Get first array variation and set it as open variation */
		reset($variations);
		$vid = key($variations);
		$_SESSION[ $inbound_email_id . '-variation-id'] = $vid;
	}

	/**
	* Pauses variation for a call to action
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation to delete
	*
	*/
	public static function pause_variation( $inbound_email_id	,	$vid ) {

		/* Update variations meta object */
		$variations = self::get_variations( $inbound_email_id );
		$variations[ $vid ]['status'] = 'paused';

		self::update_variations( $inbound_email_id , $variations );
	}

	/**
	* Activations variation for a call to action
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation to play
	*
	*/
	public static function play_variation( $inbound_email_id	,	$vid ) {

		/* Update variations meta object */
		$variations = self::get_variations( $inbound_email_id );
		$variations[ $vid ]['status'] = 'active';

		self::update_variations( $inbound_email_id , $variations );
	}

	/**
	* Sets the variation status to a custom status
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation to delete
	* @param STRING $status custom status
	*
	*/
	public static function set_variation_status( $inbound_email_id , $vid , $status = 'play' ) {

		/* Update variations meta object */
		$variations = self::get_variations( $inbound_email_id );
		$variations[ $vid ]['status'] = $status ;

		self::update_variations( $inbound_email_id , $variations );
	}


	/**
	* Returns array of variation data given a call to action id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of specific variation
	*
	* @returns ARRAY of variation data
	*/
	public static function get_variations( $inbound_email_id , $vid = null ) {

		$settings = Inbound_Email_Meta::get_settings( $inbound_email_id );
		$variations = ( isset($settings['variations']) ) ? $settings['variations'] : array( 0 => array( 'status' => 'active' ) );

		return $variations;
	}


	/**
	* Returns the status of a variation given inbound_email_id and vid
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid variation id of call to action
	*
	* @returns STRING status
	*/
	public static function get_variation_status( $inbound_email_id , $vid = null ) {

		if ( $vid === null ) {
			$vid = Inbound_Mailer_Variations::get_current_variation_id();
		}

		$variations = Inbound_Mailer_Variations::get_variations( $inbound_email_id );
		$status = ( is_array( $variations ) && isset($variations[ $vid ][ 'status' ]) ) ? $variations[ $vid ][ 'status' ] : 'active';

		return $status;
	}


	/**
	*  Set Variant Marker - When automated emails are sent we still want to rotate variations if they exist. When an email is bein sent to one lead, batching needs a consistant way to rotate variations
	*  @param INT $inbound_email_id
	*  @return INT $next_variant_marker
	*/
	public static function get_next_variant_marker( $inbound_email_id ) {

		/* get email settins */
		$settings = Inbound_Email_Meta::get_settings( $inbound_email_id );

		/* get variations */
		$variations = ( isset($settings['variations']) ) ? $settings['variations'] : array( 0 => array( 'status' => 'active' ) );

		/* count variatons */
		$variation_count = count($variations);

		/* if only one variation return appropraite variant id */
		if ( $variation_count == 1 ) {
			return current(array_keys($variations));
		}

		/* get last known variation marker if it exists else create it with first key in array */
		$variation_marker = ( !empty($settings['variation_marker']) ) ? $settings['variation_marker'] : current(array_keys($variations));

		/* safety fallback */
		if (empty($variation_marker) && $variation_marker !== 0 ) {
			return 0;
		}

		/* set pointer to variation id in array */
		$i = 0;
		while (key($variations) !== $variation_marker) {

			next($variations);

			$i++;

			if ($i>99999) {
				echo 'break';
				break;
			}
		}

		/* Save future variation marker */
		next($variations);
		Inbound_Mailer_Variations::set_variation_marker( $inbound_email_id , key($variations) );

		/* return next pointer in line */
		return $variation_marker;

	}

	/**
	*  Updates variation marker (used for single sends)
	*  @param INT $inbound_email_id
	*  @param INT $variation_marker
	*/
	public static function set_variation_marker( $inbound_email_id , $variation_marker ) {
		/* get email settins */
		$settings = Inbound_Email_Meta::get_settings( $inbound_email_id );
		$settings['variation_marker'] = $variation_marker;
		Inbound_Email_Meta::update_settings( $inbound_email_id , $settings );
	}

	/**
	* Returns the permalink of a variation given inbound_email_id and vid
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid variation id of call to action
	*
	* @returns STRING permalink
	*/
	public static function get_variation_permalink( $inbound_email_id , $vid = null ) {

		if ( $vid === null ) {
			$vid = Inbound_Mailer_Variations::get_current_variation_id();
		}

		$permalink = get_permalink($inbound_email_id);

		return add_query_arg( array('inbvid'=> $vid ) , $permalink ) ;
	}

	/**
	* Updates 'inbound-email-variations' meta key with json object
	*
	* @param INT $inbound_email_id id of call to action
	* @param variations ARRAY of variation data
	*
	*/
	public static function update_variations ( $inbound_email_id , $variations ) {

		$settings = Inbound_Email_Meta::get_settings( $inbound_email_id );
		$settings[ 'variations' ] = $variations;
		Inbound_Email_Meta::update_settings( $inbound_email_id , $settings );

	}


	/**
	* Returns array of variation specific meta data
	*
	* @param INT $inbound_email_id ID of call to action
	* @param INT $vid ID of variation belonging to call to action
	*
	* @return ARRAY $meta array of variation meta data
	*/
	public static function get_variation_meta ( $inbound_email_id , $vid ) {
		$meta = array();

		$inbound_email_meta = get_post_meta( $inbound_email_id );

		$suffix = '-'.$vid;
		$len = strlen($suffix);

		foreach ($inbound_email_meta as $key=>$value)
		{
			if (substr($key,-$len)==$suffix)
			{
				$meta[$key] = $value[0];
			}
		}

		return $meta;
	}

	/**
	* Gets the call to action variation notes
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
	*
	* @return STRING $notes variation notes.
	*/
	public static function get_variation_notes ( $inbound_email_id , $vid = null) {

		if ( $vid === null ) {
			$vid = Inbound_Mailer_Variations::get_current_variation_id();
		}

		$notes = get_post_meta( $inbound_email_id , 'mailer-variation-notes-' . $vid , true );

		return $notes;

	}

	/**
	* Gets the call to action variation custom css
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
	*
	* @return STRING $custom_css.
	*/
	public static function get_variation_custom_css ( $inbound_email_id , $vid = null) {

		if ( $vid === null ) {
			$vid = Inbound_Mailer_Variations::get_current_variation_id();
		}

		$custom_css = get_post_meta( $inbound_email_id , 'mailer-custom-css-' . $vid , true );

		return $custom_css;

	}

	/**
	* Gets the call to action variation custom js
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
	*
	* @return STRING $custom_js.
	*/
	public static function get_variation_custom_js ( $inbound_email_id , $vid = null) {

		if ( $vid === null ) {
			$vid = Inbound_Mailer_Variations::get_current_variation_id();
		}

		$custom_js = get_post_meta( $inbound_email_id , 'mailer-custom-js-' . $vid , true );

		return $custom_js;

	}

	/* Adds variation id onto base meta key
	*
	* @param id STRING of meta key to store data into for given setting
	* @param INT $vid id of variation belonging to call to action, will attempt to autodetect if left as null
	*
	* @returns STRING of meta key appended with variation id
	*/
	public static function prepare_input_id( $id , $vid = null ) {

		if ( $vid === null ) {
			$vid =	Inbound_Mailer_Variations::get_current_variation_id();
		}

		return $id . '-' . $vid;
	}

	/*
	* Gets the current variation id
	*
	* @returns INT of variation id
	*/
	public static function get_current_variation_id() {
		global $post;

		if (isset($_REQUEST['inbvid'])){
			return intval($_REQUEST['inbvid']);
		}

		$post_id = (isset($post->ID)) ? $post->ID : intval($_REQUEST['post']);

		if (isset($_SESSION[ $post_id . '-variation-id'])) {
			return $_SESSION[ $post_id . '-variation-id'];
		}

		return 0;
	}

	/*
	* Gets the next available variation id
	*
	* @returns INT of variation id
	*/
	public static function get_next_available_variation_id( $inbound_email_id ) {

		$variations = Inbound_Mailer_Variations::get_variations( $inbound_email_id );
		$array_variations = $variations;

		end($array_variations);

		$last_variation_id = key($array_variations);

		return $last_variation_id + 1;
	}

	/*
	* Gets string id of template given email id
	*
	* @param INT $inbound_email_id of call to action
	* @param INT $vid of variation id
	*
	* @returns STRING id of selected template
	*/
	public static function get_current_template( $inbound_email_id , $vid = null ) {

		if ( $vid === null ) {
			$vid =	Inbound_Mailer_Variations::get_current_variation_id();
		}

		$settings = Inbound_Email_Meta::get_settings( $inbound_email_id );
		$variations = ( isset($settings['variations']) ) ? $settings['variations'] : null;

		$template = ( isset( $variations[ $vid ][ 'selected_template' ] ) ) ? $variations[ $vid ][ 'selected_template' ] : 'simple-responsive';

		/* If new variation use historic template id */
		if ( isset($_GET['new-variation'] ) ) {
			$vid = key($variations);
			$template = ( isset( $variations[ $vid ][ 'selected_template' ] ) ) ? $variations[ $vid ][ 'selected_template' ] : 'simple-responsive';
		}

		return $template;

	}

	/**
	* Get Screenshot URL for email preview. If local environment show template thumbnail.
	*
	* @param INT $inbound_email_id id if of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	* @return STRING url of preview
	*/
	public static function get_screenshot_url( $inbound_email_id , $vid = null) {

		if ( $vid === null ) {
			$vid =	Inbound_Mailer_Variations::get_current_variation_id();
		}

		$template = Inbound_Mailer_Variations::get_current_template( $inbound_email_id , $vid);

		if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {

			if (file_exists(INBOUND_EMAIL_PATH . 'templates/' . $template . '/thumbnail.png')) {
				$screenshot = INBOUND_EMAIL_URLPATH . 'templates/' . $template . '/thumbnail.png';
			} else if (file_exists(INBOUND_EMAIL_UPLOADS_PATH . 'templates/' . $template . '/thumbnail.png')) {
				$screenshot = INBOUND_EMAIL_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png';
			} else if (file_exists(INBOUND_EMAIL_THEME_TEMPLATES_PATH . 'templates/' . $template . '/thumbnail.png')) {
				$screenshot = INBOUND_EMAIL_THEME_TEMPLATES_URLPATH . 'templates/' . $template . '/thumbnail.png';
			}

		} else {
			$screenshot = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';
		}

		return $screenshot;
	}

	/**
	* Appends current variation id onto a URL
	*
	* @param link STRING URL that param will be appended onto
	*
	*
	* @return STRING modified URL.
	*/
	public static function append_variation_id_to_url( $link ) {
		global $post;

		if ( !isset($post) || $post->post_type != 'inbound-email' ) {
			return $link;
		}

		$current_variation_id =	Inbound_Mailer_Variations::get_current_variation_id();


		$link = add_query_arg( array('inbvid' => $current_variation_id ) , $link );

		return $link;
	}

	/**
	* Discovers which alphabetic letter should be associated with a given cta's variation id.
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	* @return STRING alphebit letter.
	*/
	public static function vid_to_letter( $inbound_email_id , $vid ) {
		$variations = Inbound_Mailer_Variations::get_variations( $inbound_email_id );

		$i = 0;
		foreach ($variations as $key => $variation ) {
			if ( $vid == $key ) {
				break;
			}
			$i++;
		}

		$alphabet = array(
			__( 'A' , 'inbound-pro' ),
			__( 'B' , 'inbound-pro' ),
			__( 'C' , 'inbound-pro' ),
			__( 'D' , 'inbound-pro' ),
			__( 'E' , 'inbound-pro' ),
			__( 'F' , 'inbound-pro' ),
			__( 'G' , 'inbound-pro' ),
			__( 'H' , 'inbound-pro' ),
			__( 'I' , 'inbound-pro' ),
			__( 'J' , 'inbound-pro' ),
			__( 'K' , 'inbound-pro' ),
			__( 'L' , 'inbound-pro' ),
			__( 'M' , 'inbound-pro' ),
			__( 'N' , 'inbound-pro' ),
			__( 'O' , 'inbound-pro' ),
			__( 'P' , 'inbound-pro' ),
			__( 'Q' , 'inbound-pro' ),
			__( 'R' , 'inbound-pro' ),
			__( 'S' , 'inbound-pro' ),
			__( 'T' , 'inbound-pro' ),
			__( 'U' , 'inbound-pro' ),
			__( 'V' , 'inbound-pro' ),
			__( 'W' , 'inbound-pro' ),
			__( 'X' , 'inbound-pro' ),
			__( 'Y' , 'inbound-pro' ),
			__( 'Z' , 'inbound-pro' )
		);

		if (isset($alphabet[$i])){
			return $alphabet[$i];
		}
	}

	/**
	* Returns impression for given cta and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	* @return INT impression count
	*/
	public static function get_impressions( $inbound_email_id , $vid ) {

		$impressions = get_post_meta( $inbound_email_id ,'mailer-ab-variation-impressions-'.$vid , true);

		if (!is_numeric($impressions)) {
			$impressions = 0;
		}

		return $impressions;
	}

	/**
	* Increments impression count for given cta and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	*/
	public static function record_impression( $inbound_email_id , $vid ) {

		$impressions = get_post_meta( $inbound_email_id ,'mailer-ab-variation-impressions-'.$vid, true);

		if (!is_numeric($impressions)) {
			$impressions = 1;
		} else {
			$impressions++;
		}

		update_post_meta( $inbound_email_id , 'mailer-ab-variation-impressions-'.$vid , $impressions);
	}

	/**
	* Manually sets conversion count for given cta id and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	*/
	public static function set_impression_count( $inbound_email_id , $vid , $count) {

		update_post_meta( $inbound_email_id , 'mailer-ab-variation-impressions-'.$vid , $count);
	}

	/**
	* Returns impression for given cta and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	* @return INT impression count
	*/
	public static function get_conversions( $inbound_email_id , $vid ) {

		$conversions = get_post_meta( $inbound_email_id ,'mailer-ab-variation-conversions-'.$vid, true);

		if (!is_numeric($conversions)) {
			$conversions = 0;
		}

		return $conversions;
	}

	/**
	* Returns conversion rate for given cta and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	* @return INT conversion rate
	*/
	public static function get_conversion_rate( $inbound_email_id , $vid ) {

		$impressions = Inbound_Mailer_Variations::get_impressions( $inbound_email_id , $vid );
		$conversions = Inbound_Mailer_Variations::get_conversions( $inbound_email_id , $vid );

		if ($impressions>0) {
			$conversion_rate = $conversions / $impressions;
			$conversion_rate_number = $conversion_rate * 100;
			$conversion_rate_number = round($conversion_rate_number, 2);
			$conversion_rate = $conversion_rate_number;
		} else {
			$conversion_rate = 0;
		}

		return $conversion_rate;
	}

	/**
	* Increments conversion count for given cta id and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	*/
	public static function record_conversion(	$inbound_email_id , $vid ) {

		$conversions = get_post_meta( $inbound_email_id , 'mailer-ab-variation-conversions-' . $vid , true);

		if (!is_numeric($conversions)) {
			$conversions = 1;
		} else {
			$conversions++;
		}

		update_post_meta( $inbound_email_id , 'mailer-ab-variation-conversions-'.$vid , $conversions);
	}

	/**
	* Manually sets conversion count for given cta id and variation id
	*
	* @param INT $inbound_email_id id of call to action
	* @param INT $vid id of variation belonging to call to action
	*
	*/
	public static function set_conversion_count(	$inbound_email_id , $vid , $count) {

		update_post_meta( $inbound_email_id , 'mailer-ab-variation-conversions-'.$vid , $count);
	}

}

$GLOBALS['Inbound_Mailer_Variations'] = new Inbound_Mailer_Variations();

