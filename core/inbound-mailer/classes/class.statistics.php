<?php

/**
*	Calculates & serves Mandrill Stats
*/

class Inbound_Email_Stats {

	static $settings; /* email settings */
	static $results; /* results returned from mandrill */
	static $email_id; /* email id being processed */
	static $vid; /* variation id being processed */
	static $stats; /* stats array */
	static $timemarker; /* marks time of last api call */

	/**
	*	Gets email statistics
	*	@param INT $email_id ID of email
	*	@param BOOLEAN $return false for json return true for array return
	*	@return JSON
	*/
	public static function get_email_stats ( ) {
		global $Inbound_Mailer_Variations, $post;


		if ( !in_array( $post->post_status , array( 'sent' , 'sending', 'automated' )) ) {
			return '{}';
		}

		/* get historical statistic blob from db */
		Inbound_Email_Stats::get_statistics_object();

		/* get settings from db */
		self::$settings = Inbound_Email_Meta::get_settings( $post->ID );

		/* prepare processing criteria */
		self::prepare_date_ranges();

		/* get mandrill stats from api */
		foreach ( self::$settings['variations'] as $vid => $variation ) {

			self::$vid = $vid;
			self::$email_id = $post->ID;

			$query = 'u_email_id:' .	$post->ID	. ' u_variation_id:'. self::$vid .' subaccount:' . InboundNow_Connection::get_licence_key();
			self::query_mandrill( $query );

			/* sort data into local stats object by hour */
			self::process_mandrill_stats();
		}

		/* loop through hour-based totals and create totals for variations */
		self::process_variation_totals();

		/* return empty stats if empty */
		if (!self::$stats) {
			self::$stats['variations'][0] =	Inbound_Email_Stats::prepare_empty_stats();
			Inbound_Email_Stats::prepare_totals();
			return self::$stats ;
		}

		/* prepare totals from variations */
		Inbound_Email_Stats::prepare_totals();


		/* save updated statistics object into database */
		Inbound_Email_Stats::update_statistics_object();

		return self::$stats;

	}

	/**
	*	Get stats object from db
	*/
	public static function get_statistics_object() {
		global $post;

		$stats = get_post_meta( $post->ID , 'inbound_statistics' , true );

		self::$stats = ($stats) ? $stats : array();

	}

	/**
	*	Converts gmt timestamps to correct timezones
	*	@param DATETIME $timestamp timestamp in gmt before calculating timezone
	*/
	public static function get_mandrill_timestamp( $timestamp ) {
		/* get timezone */
		$tz = explode( '-UTC' , self::$settings['timezone'] );

		$timezone = timezone_name_from_abbr($tz[0] , 60 * 60 * intval( $tz[1] ) );
		date_default_timezone_set( $timezone );

		$mandrill_timestamp = gmdate( "Y-m-d\\TG:i:s\\Z" ,	strtotime($timestamp) );

		return $mandrill_timestamp;
	}

	/**
	*	Update db stats object
	*/
	public static function update_statistics_object() {
		global $post;

		update_post_meta( $post->ID , 'inbound_statistics' , self::$stats );

	}
	/**
	*	Get Mandrill Time Series Stats
	*	@param STRING $query
	*/
	public static function query_mandrill( $query ) {
		global $post;

		/* load mandrill time	*/
		$mandrill = new Mandrill();

		$tags = array();
		$senders = array();

		self::$results = $mandrill->messages->searchTimeSeries($query, self::$stats['date_from'] , self::$stats['date_to'] , $tags, $senders);

	}

	/**
	*	process mandrill statistics
	*/
	public static function process_mandrill_stats() {
		
		/* skip processing if no data */
		if (!self::$results) {
			self::$stats[ 'mandrill' ] = array();
			return;
		}
		
		/* stores data by hour */
		foreach ( self::$results as $key => $totals ) {

			/* update processed totals */
			foreach ($totals as $k => $value ) {
				self::$stats[ 'mandrill' ][ self::$vid ][ $totals['time'] ][ $k ] = $value;
			}

		}
	}

	/**
	*	build variation totals
	*/
	public static function process_variation_totals() {

		/* skip processing if no data */
		if (!self::$stats['mandrill']) {
			self::$stats[ 'variations' ] = array();
			return;
		}
		
		/* loop through mandrill object & compile hour totals to build variation totals */
		foreach ( self::$stats['mandrill'] as $vid => $hours ) {
			
			/* set to zero */
			self::$stats[ 'variations' ][ $vid ] = array(
				'sent' => 0,
				'opens' => 0,
				'clicks' => 0,
				'hard_bounces' => 0,
				'soft_bounces' => 0,
				'rejects' => 0,
				'complaints' => 0,
				'unsubs' => 0,
				'unique_opens' => 0,
				'unique_clicks' => 0,
				'unopened' => 0
			);
			
			/* loop through each hour's totals for variation */
			foreach ( $hours as $hour => $totals ) {
				unset($totals['time']);
				
				/* update processed totals */
				foreach ($totals as $key => $value ) {
					self::$stats[ 'variations' ][ $vid ][ $key ] = self::$stats[ 'variations' ][ $vid ][ $key ] + $value;
				}
				
			}
			
			/* process unopened */
			self::$stats[ 'variations' ][ $vid ][ 'unopened' ] = self::$stats[ 'variations' ][ $vid ][ 'sent'] - self::$stats[ 'variations' ][ $vid ][ 'opens'];

			/* add label */
			self::$stats[ 'variations' ][ $vid ][ 'label' ] =	Inbound_Mailer_Variations::vid_to_letter( self::$email_id , $vid );
			
			/* add subject line */
			self::$stats[ 'variations' ][ $vid ][ 'subject' ] = self::$settings['variations'][ $vid ][ 'subject' ];

		}


	}

	/**
	*	Prepares dates to process
	*/
	public static function prepare_date_ranges() {

		
		/* If we've already processed time & stats already exits then start from last processing point */
		if ( isset(self::$stats['date_to'] ) && self::$stats['totals'] ) {

			/* get today's datetimestamp */
			$today = self::get_mandrill_timestamp( gmdate( "Y-m-d\\TG:i:s\\Z" ) );

			/* set start date at last processed datetime */
			self::$stats['date_from'] = self::$stats['date_to'];

			/* get 90 days from last processing date */
			$datetime = new DateTime( self::$stats['date_from']	);
			$datetime->modify('+90 days');
			$next_date = self::get_mandrill_timestamp( $datetime->format('Y-m-d H:i:s') );

			/* if next processing date is in the past use it else use current day */
			if ( $next_date < $today ) {
				self::$stats['date_to'] = $next_date;
			} else {
				self::$stats['date_to'] = self::get_mandrill_timestamp( gmdate( "Y-m-d\\TG:i:s\\Z" ) );
			}
		} else {
			self::$stats['date_from'] = self::get_mandrill_timestamp( self::$settings['send_datetime'] );
			self::$stats['date_to'] = self::get_mandrill_timestamp( gmdate( "Y-m-d\\TG:i:s\\Z" ) );
		}

	}

	/**
	*	Totals variation stats to create an aggregated statistic total_stat
	*	@param ARRAY $stats array of variations with email statics
	*	@returns ARRAY $stats array of variations with email statics and aggregated statistics
	*/
	public static function prepare_totals(	) {

		/* skip processing if no data */
		if (!self::$stats['variations']) {
			self::$stats[ 'totals' ] = array();
			return;
		}
		
		
		self::$stats[ 'totals' ] = array(
			'sent' => 0,
				'opens' => 0,
				'clicks' => 0,
				'hard_bounces' => 0,
				'soft_bounces' => 0,
			'bounces' => 0,
				'rejects' => 0,
				'complaints' => 0,
				'unsubs' => 0,
				'unique_opens' => 0,
				'unique_clicks' => 0,
			'opens' => 0,
			'unopened' => 0
		);


		foreach (self::$stats['variations'] as $vid => $totals ) {


			self::$stats['totals']['sent'] = self::$stats['totals']['sent']	+ $totals['sent'];
			self::$stats['totals']['opens'] = self::$stats['totals']['opens']	+ $totals['opens'];
			self::$stats['totals']['clicks'] = self::$stats['totals']['clicks']	+ $totals['clicks'];
			self::$stats['totals']['hard_bounces'] = self::$stats['totals']['hard_bounces']	+ $totals['hard_bounces'];
			self::$stats['totals']['soft_bounces'] = self::$stats['totals']['soft_bounces']	+ $totals['soft_bounces'];
			self::$stats['totals']['rejects'] = self::$stats['totals']['rejects']	+ $totals['rejects'];
			self::$stats['totals']['complaints'] = self::$stats['totals']['complaints']	+ $totals['complaints'];
			self::$stats['totals']['unsubs'] = self::$stats['totals']['unsubs']	+ $totals['unsubs'];
			self::$stats['totals']['unique_opens'] = self::$stats['totals']['unique_opens']	+ $totals['unique_opens'];
			self::$stats['totals']['unique_clicks'] = self::$stats['totals']['unique_clicks']	+ $totals['unique_clicks'];

		}


		/* calculate unopened */
		self::$stats['totals']['unopened'] = self::$stats['totals']['sent']	- self::$stats['totals']['opens'];

		/* calcumate total bounces */
		self::$stats['totals']['bounces'] = self::$stats['totals']['soft_bounces']	+ self::$stats['totals']['hard_bounces'];


	}

	/**
	*	Returns an array of zeros for email statistics
	*/
	public static function prepare_empty_stats() {

		return array(
			'sends' => 0,
			'opens' => 0,
			'unopened' => 0,
			'clicks' => 0
		);

	}

	/**
	*	Prepare dummy stats - populates an email with dummy statistics
	*/
	public static function prepare_dummy_stats( $email_id ) {

		$settings = Inbound_Email_Meta::get_settings( $email_id );

		if (!isset( $settings ) ) {
			return;
		}

		/* V1 */
		$settings['statistics']['variations'][0] = array(
			'label' => Inbound_Mailer_Variations::vid_to_letter( $email_id , 0 ),
			'sends' => 400,
			'opens' => 300,
			'unopened' => 100,
			'clicks' => 19
		);

		/* V2 */
		$settings['statistics']['variations'][1] = array(
			'label' => Inbound_Mailer_Variations::vid_to_letter( $email_id , 1 ),
			'sends' => 400,
			'opens' => 350,
			'unopened' => 50,
			'clicks' => 28
		);

		/* Totals */
		$settings['statistics']['totals'] = array(
			'sends' => 800,
			'opens' => 650,
			'unopened' => 150,
			'clicks' => 47
		);

		$settings = Inbound_Email_Meta::update_settings( $email_id , $settings );
	}
}

