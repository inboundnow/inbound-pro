<?php

/**
 *	Calculates & serves Mandrill Stats
 */

class Inbound_SparkPost_Stats {

    static $settings; /* email settings */
    static $results; /* results returned from sparkpost */
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
    public static function get_email_timeseries_stats( $email_id = null ) {
        global $Inbound_Mailer_Variations, $post, $inbound_settings;

        /* check if email id is set else use global post object */
        if ( $email_id ) {
            $post = get_post($email_id);
        }

        /* we do not collect stats for statuses not in this array */
        if ( !in_array( $post->post_status , array( 'sent' , 'sending', 'automated' )) ) {
            return array();
        }

        /* get historical statistic blob from db */
        Inbound_SparkPost_Stats::get_statistics_object();

        /* get settings from db */
        self::$settings = Inbound_Email_Meta::get_settings( $post->ID );

        /* prepare processing criteria */
        self::prepare_date_ranges();

        /* prepare campaign ids for api lookups */
        foreach ( self::$settings['variations'] as $vid => $variation ) {

            self::$vid = $vid;
            self::$email_id = $post->ID;

            $campaign_id =  $post->ID	. '_'. self::$vid;
            $campaign_ids[] = $campaign_id;


        }

        $sparkpost = new Inbound_SparkPost(  $inbound_settings['inbound-mailer']['sparkpost-key'] );
        self::$results = $sparkpost->get_campaign_metrics($campaign_ids, self::$stats['date_from'] , self::$stats['date_to'] );

        /* loop through hour-based totals and create totals for variations */
        self::process_variation_totals();

        /* return empty stats if empty */
        if (!self::$stats) {
            self::$stats['variations'][0] =	Inbound_SparkPost_Stats::prepare_empty_stats();
            Inbound_SparkPost_Stats::prepare_totals();
            return self::$stats ;
        }

        /* prepare totals from variations */
        Inbound_SparkPost_Stats::prepare_totals();
        error_log(print_r(self::$stats,true));

        /* save updated statistics object into database */
        Inbound_SparkPost_Stats::update_statistics_object();

        return self::$stats;

    }

    /**
     *	Get stats object from db
     */
    public static function get_statistics_object() {
        global $post;

        $stats = get_post_meta( $post->ID , 'inbound_statistics' , true );

        self::$stats = ($stats) ? $stats : array( 'sparkpost' => array() );
    }

    /**
     *	Converts gmt timestamps to correct timezones
     *	@param DATETIME $timestamp timestamp in gmt before calculating timezone
     */
    public static function get_sparkpost_timestamp( $timestamp ) {

        /* make sure we have a timezone set */
        $tz = Inbound_Mailer_Scheduling::get_current_timezone();
        self::$settings['timezone'] = (!empty(self::$settings['timezone'])) ? self::$settings['timezone'] :  $tz['abbr'] . '-UTC' . $tz['offset'];

        $sparkpost_timestamp = date( 'c' , strtotime( $timestamp ) );
        $date_parts = explode('+' , $sparkpost_timestamp );

        // $timezone_parts = explode('UTC' , self::$settings['timezone'] );

        $sparkpost_timestamp = $date_parts[0];
        $date_parts = explode(':' , $sparkpost_timestamp );
        array_pop($date_parts);

        $sparkpost_timestamp = implode(':', $date_parts);

        return $sparkpost_timestamp;
    }

    /**
     * Pull data from Mandrill based on email id and variation id
     *
     */
    public static function get_send_stream() {
        global $Inbound_Mailer_Variations;
        global $post;

        self::$vid = $Inbound_Mailer_Variations->get_current_variation_id();
        self::$email_id = $post->ID;

        self::$stats['date_from'] =  self::get_sparkpost_timestamp( $post->post_date );
        self::$stats['date_to'] =  self::get_sparkpost_timestamp( date( "c" ) );

        $query = 'u_email_id:' .	$post->ID	. ' ( tags:batch OR tags:automated)';

        self::query_sparkpost_search( $query );

        return self::$results;
    }


    /**
     *  Timezone check
     */
    public static function timezone_check( $timezone ) {
        if ($timezone) {
            return $timezone;
        }

        switch( self::$settings['timezone'] ) {
            case 'BIT-UTC-12' :
                return '';
                break;
        }
    }

    /**
     *	Update db stats object
     */
    public static function update_statistics_object() {
        global $post;

        //delete_post_meta( $post->ID , 'inbound_statistics'  );
        update_post_meta( $post->ID , 'inbound_statistics' , self::$stats );

    }


    /**
     *	Get Mandrill Search Stats
     */
    public static function query_sparkpost_search( $query ) {
        global $post;
        $start = microtime(true);

        /* load sparkpost time	*/
        $settings = Inbound_Mailer_Settings::get_settings();
        $sparkpost = new Inbound_Mandrill(  $settings['sparkpost_key'] );

        $tags = array();
        $senders = array();
        $api_keys = array();

        self::$results = $sparkpost->search($query, self::$stats['date_from'] , self::$stats['date_to'] , $tags, $senders , $api_keys , 1000 );

        /* echo microtime(true) - $start; */
    }

    /**
     *	build variation totals
     */
    public static function process_variation_totals() {
        $batch_key_parts = explode('T' , self::$stats['date_to'] );
        $batch_key = $batch_key_parts[0];

        /* skip processing if no data */
        if (!isset(self::$results['results'])) {
            self::$stats['sparkpost'][$batch_key][ 'variations' ] = array();
            return;
        }

        /* loop through sparkpost object & compile hour totals to build variation totals */
        foreach ( self::$results['results'] as $i => $totals ) {
            $campaign_parts = explode('_' , $totals['campaign_id'] );
            $vid = $campaign_parts[1];

            self::$stats['sparkpost'][$batch_key][ 'variations' ][ $vid ] = array(
                'sent' => $totals['count_sent'],
                'opens' => $totals['count_rendered'],
                'clicks' => $totals['count_unique_clicked'],
                'bounces' => $totals['count_bounce'],
                'hard_bounces' => $totals['count_hard_bounce'],
                'soft_bounces' => $totals['count_soft_bounce'],
                'rejects' => $totals['count_rejected'],
                'complaints' => $totals['count_spam_complaint'],
                'unique_opens' => $totals['count_unique_rendered'],
                'unique_clicks' => $totals['count_unique_clicked'],
                'unopened' => $totals['count_sent']
            );

         }


    }

    /**
     *	Prepares dates to process
     */
    public static function prepare_date_ranges() {
        global $post;

        /* check if we have reached a 90 day block */
        $today = new DateTime( date('c') );

        /* account for first load by setting empty variables to today */
        self::$stats['date_from'] = (isset(self::$stats['date_from'])) ? self::$stats['date_from'] : date('c');
        self::$stats['date_to'] = (isset(self::$stats['date_to'])) ? self::$stats['date_to'] : date('c');

        /* create date objects */
        $date_from = new DateTime(self::$stats['date_from']);
        $date_to = new DateTime(self::$stats['date_to']);

        /* calculated differences */
        $date_from_interval = $date_from->diff($today);
        $date_to_interval = $date_to->diff($today);

        /* ceck if we have a full 90 day block */
        if ($date_from_interval->days > 90 && $date_to_interval->days > 0 ) {

            $datetime = new DateTime( self::$stats['date_from']	);
            $datetime->modify('+90 days');
            $next_date = self::get_sparkpost_timestamp( $datetime->format('c') );

            /* set start date at last processed datetime */
            self::$stats['date_from'] = $today;
            self::$stats['date_to'] = $next_date;

        } else if ($date_from_interval->days > 90  ) {

            $datetime = new DateTime( self::$stats['date_to']);
            $datetime->modify('+90 days');
            $next_date = self::get_sparkpost_timestamp( $datetime->format('c') );

            /* set start date at last processed datetime */
            self::$stats['date_from'] = $today;
            self::$stats['date_to'] = $next_date;
        } else {
            $today->modify('+90 days');
            self::$stats['date_from'] = ( !empty(self::$settings['send_datetime']) ) ? self::get_sparkpost_timestamp( self::$settings['send_datetime'] ) :  self::get_sparkpost_timestamp( $post->post_date ) ;
            self::$stats['date_to'] = self::get_sparkpost_timestamp( self::get_sparkpost_timestamp( $today->format('c') ) );
        }

    }

    /**
     *	Totals variation stats to create an aggregated statistic total_stat
     *	@param ARRAY $stats array of variations with email statics
     *	@returns ARRAY $stats array of variations with email statics and aggregated statistics
     */
    public static function prepare_totals(	) {

        self::$stats[ 'totals' ] = array(
            'sent' => 0,
            'opens' => 0,
            'clicks' => 0,
            'bounces' => 0,
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

        /* skip processing if no data */
        if (!self::$stats['sparkpost']) {
            return;
        }

        foreach (self::$stats['sparkpost'] as $block_key => $data ) {

            /* prepare default counters for variations */
            foreach ($data['variations'] as $vid => $totals ) {
                self::$stats['totals']['variations'][$vid] = array(
                    'sent' => 0,
                    'opens' => 0,
                    'clicks' => 0,
                    'bounces' => 0,
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
            }

            foreach ($data['variations'] as $vid => $totals ) {

                self::$stats['totals']['sent'] = self::$stats['totals']['sent'] + $totals['sent'];
                self::$stats['totals']['opens'] = self::$stats['totals']['opens'] + $totals['opens'];
                self::$stats['totals']['clicks'] = self::$stats['totals']['clicks'] + $totals['clicks'];
                self::$stats['totals']['bounces'] = self::$stats['totals']['bounces'] + $totals['bounces'];
                self::$stats['totals']['hard_bounces'] = self::$stats['totals']['hard_bounces'] + $totals['hard_bounces'];
                self::$stats['totals']['soft_bounces'] = self::$stats['totals']['soft_bounces'] + $totals['soft_bounces'];
                self::$stats['totals']['rejects'] = self::$stats['totals']['rejects'] + $totals['rejects'];
                self::$stats['totals']['complaints'] = self::$stats['totals']['complaints'] + $totals['complaints'];
                self::$stats['totals']['unique_opens'] = self::$stats['totals']['unique_opens'] + $totals['unique_opens'];
                self::$stats['totals']['unique_clicks'] = self::$stats['totals']['unique_clicks'] + $totals['unique_clicks'];
                //print_r(self::$stats);exit;

                self::$stats['totals']['variations'][$vid]['sent'] = self::$stats['totals']['variations'][$vid]['sent'] + $totals['sent'];
                self::$stats['totals']['variations'][$vid]['clicks'] = self::$stats['totals']['variations'][$vid]['clicks'] + $totals['clicks'];
                self::$stats['totals']['variations'][$vid]['bounces'] = self::$stats['totals']['variations'][$vid]['bounces'] + $totals['bounces'];
                self::$stats['totals']['variations'][$vid]['hard_bounces'] = self::$stats['totals']['variations'][$vid]['hard_bounces'] + $totals['hard_bounces'];
                self::$stats['totals']['variations'][$vid]['soft_bounces'] = self::$stats['totals']['variations'][$vid]['soft_bounces'] + $totals['soft_bounces'];
                self::$stats['totals']['variations'][$vid]['rejects'] = self::$stats['totals']['variations'][$vid]['rejects'] + $totals['rejects'];
                self::$stats['totals']['variations'][$vid]['complaints'] = self::$stats['totals']['variations'][$vid]['complaints'] + $totals['complaints'];
                self::$stats['totals']['variations'][$vid]['opens'] = self::$stats['totals']['variations'][$vid]['opens'] + $totals['opens'];
                self::$stats['totals']['variations'][$vid]['unique_opens'] = self::$stats['totals']['variations'][$vid]['unique_opens'] + $totals['unique_opens'];
                self::$stats['totals']['variations'][$vid]['unique_clicks'] = self::$stats['totals']['variations'][$vid]['unique_clicks'] + $totals['unique_clicks'];

                /* add label */
                self::$stats['totals']['variations'][ $vid ][ 'label' ] =	Inbound_Mailer_Variations::vid_to_letter( self::$email_id , $vid );

                /* add subject line */
                self::$stats['totals']['variations'][ $vid ][ 'subject' ] = self::$settings['variations'][ $vid ][ 'subject' ];

                /* add unopened */
                self::$stats['totals']['variations'][ $vid ]['unopened'] = self::$stats['totals']['variations'][$vid]['sent']	- self::$stats['totals']['variations'][$vid]['opens'];
            }

        }


        /* calculate unopened */
        self::$stats['totals']['unopened'] = self::$stats['totals']['sent']	- self::$stats['totals']['opens'];
        self::$stats['totals']['unsubs'] = self::prepare_unsubscribes();


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
     *	Returns an array of zeros for email statistics
     */
    public static function prepare_unsubscribes(  ) {

        global $post;


        return Inbound_Events::get_unsubscribes_count_by_email_id( $post->ID  );


    }

}
