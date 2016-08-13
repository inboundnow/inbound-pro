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

    public function __construct() {
        self::add_hooks();
    }

    public static function add_hooks() {
        if (is_admin()) {
            /* For mail service activation */
            add_action('inbound-settings/after-field-value-update', array(__CLASS__, 'sparkpost_activation_routines'));

            /* For processing webhooks */
            add_action('wp_ajax_nopriv_sparkpost_webhook', array(__CLASS__, 'process_webhook'));

            add_action( 'inbound-mailer/unschedule-email' , array( __CLASS__, 'unschedule_email' ) , 10 , 1 );
        }

        /* process send event */
        add_action( 'sparkpost/send/response' , array( __CLASS__ , 'process_send_event') , 10 , 2 );

        /* process rejection errors */
        add_action( 'sparkpost/send/response' , array( __CLASS__ , 'process_rejections') , 10 , 2 );
    }

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

        /* save updated statistics object into database */
        Inbound_SparkPost_Stats::update_statistics_object();

        return self::$stats;

    }

    /**
     *  Get SparkPost Stats including varition totals
     */
    public static function get_sparkpost_webhook_stats() {
        global $post, $Inbound_Mailer_Variations;

        /* first get totals */
        self::get_sparkpost_inbound_events( $post->ID );

        /* now total variations */
        $variations = $Inbound_Mailer_Variations->get_variations($post->ID, $vid = null);

        foreach ( $variations as $vid => $variation ) {
            self::get_sparkpost_inbound_events( $post->ID , $vid );
        }

        return self::$stats;
    }

    /**
     * Get all all custom event data by a certain indicator
     */
    public static function get_sparkpost_inbound_events( $email_id , $variation_id = null ) {
        global $wpdb, $post, $inbound_settings;

        /* check if email id is set else use global post object */
        if ($email_id) {
            $post = get_post($email_id);
        }

        /* we do not collect stats for statuses not in this array */
        if (!in_array($post->post_status, array('sent', 'sending', 'automated'))) {
            return array();
        }

        /* get email setup data */
        $settings = Inbound_Email_Meta::get_settings($post->ID);
        $table_name = $wpdb->prefix . "inbound_events";

        if (is_numeric($variation_id)) {
            $variation_query = 'AND variation_id="' . $variation_id . '"';
        } else {
            $variation_query = '';
        }

        /* get deliveries */
        $wordpress_date_time =  date_i18n('Y-m-d G:i:s');
        $today = new DateTime($wordpress_date_time);
        $schedule_date = new DateTime($settings['send_datetime']);
        $interval = $today->diff($schedule_date);


        if ( $interval->format('%R') == '-' ) {
            $query = 'SELECT DISTINCT(lead_id) FROM ' . $table_name . ' WHERE `email_id` = "' . $email_id . '"  ' . $variation_query . ' AND `event_name` =  "sparkpost_delivery"';
            $results = $wpdb->get_results($query);
            $sent = $wpdb->num_rows;
        } else {
            $sent = 0;
        }

        /* get opens */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` =  "sparkpost_open"';
        $results = $wpdb->get_results( $query );
        $opens = $wpdb->num_rows;

        /* get clicks */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` =  "sparkpost_click"';
        $results = $wpdb->get_results( $query );
        $clicks = $wpdb->num_rows;

        /* get bounce */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` =  "sparkpost_bounce"';
        $results = $wpdb->get_results( $query );
        $bounces = $wpdb->num_rows;

        /* get rejects */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` =  "sparkpost_rejected" OR `event_name` = "sparkpost_relay_rejection"';
        $results = $wpdb->get_results( $query );
        $rejects = $wpdb->num_rows;

        /* get spam complaints */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` LIKE  "sparkpost_spam_complaint"';
        $results = $wpdb->get_results( $query );
        $complaints = $wpdb->num_rows;

        /* get unsubscribes */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` LIKE  "inbound_unsubscribe"';
        $results = $wpdb->get_results( $query );
        $unsubs = $wpdb->num_rows;

        /* get mutes */
        $query = 'SELECT DISTINCT(lead_id) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '.$variation_query.' AND `event_name` LIKE  "inbound_mute"';
        $results = $wpdb->get_results( $query );
        $mutes = $wpdb->num_rows;


        $totals = array(
            'sent' => $sent,
            'opens' =>$opens,
            'clicks' => $clicks,
            'bounces' => $bounces,
            'rejects' => $rejects,
            'complaints' => $complaints,
            'unsubs' => $unsubs,
            'mutes' => $mutes,
            'unique_opens' => $opens,
            'unique_clicks' => $clicks,
            'unopened' => $sent - $opens
        );

        if ( is_numeric($variation_id) ) {
            self::$stats[ 'totals' ][ 'variations' ][ $variation_id ] = $totals;
            self::$stats[ 'totals' ][ 'variations' ][ $variation_id ][ 'label' ] =	Inbound_Mailer_Variations::vid_to_letter( $email_id , $variation_id );
        } else {
            self::$stats[ 'totals' ] = $totals;
        }

        return self::$stats;
    }


    /**
     *	Get stats object from db
     */
    public static function get_statistics_object() {
        global $post;

        //delete_post_meta( $post->ID , 'inbound_statistics'  );
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
        global $inbound_settings;
        global $post;

        self::$vid = $Inbound_Mailer_Variations->get_current_variation_id();

        $campaign_id =  $post->ID	. '_'. self::$vid;
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['inbound-mailer']['sparkpost-key'] );
        self::$results = $sparkpost->get_transmissions( $campaign_id );

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
        //error_log(print_r(self::$stats,true));
        update_post_meta( $post->ID , 'inbound_statistics' , self::$stats );

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
        self::$stats['date_from'] = (isset(self::$stats['date_from'])) ? self::$stats['date_from'] : $today->format('c');
        self::$stats['date_to'] = (isset(self::$stats['date_to'])) ? self::$stats['date_to'] : $today->format('c');

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
            self::$stats['date_to'] = $next_date;

        } else if ($date_from_interval->days > 90  ) {

            $datetime = new DateTime( self::$stats['date_to']);
            $datetime->modify('+90 days');
            $next_date = self::get_sparkpost_timestamp( $datetime->format('c') );

            /* set start date at last processed datetime */
            self::$stats['date_from'] = self::get_sparkpost_timestamp( $today->format('c') );
            self::$stats['date_to'] = $next_date;
        } else {
            $today->modify('+90 days');
            self::$stats['date_from'] = ( !empty(self::$settings['send_datetime']) ) ? self::get_sparkpost_timestamp( self::$settings['send_datetime'] ) :  self::get_sparkpost_timestamp( $post->post_date ) ;
            self::$stats['date_to'] = self::get_sparkpost_timestamp(  $today->format('c')  );
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
            'rejects' => 0,
            'complaints' => 0,
            'unsubs' => 0,
            'mutes' => 0,
            'unique_opens' => 0,
            'unique_clicks' => 0,
            'opens' => 0,
            'unopened' => 0
        );

        /* skip processing if no data */
        if (!isset(self::$stats['sparkpost'])) {
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
                    'rejects' => 0,
                    'complaints' => 0,
                    'unsubs' => 0,
                    'mutes' => 0,
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
        self::$stats['totals']['mutes'] = self::prepare_mutes();


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

    /**
     *	Returns an array of zeros for email statistics
     */
    public static function prepare_mutes(  ) {

        global $post;


        return Inbound_Events::get_mutes_count_by_email_id( $post->ID  );


    }

    /**
     *
     */
    public static function create_sparkpost_webhooks($field) {
        global $inbound_settings;

        /* load SparkPost connector */
        $sparkpost = new Inbound_SparkPost(  $field['value'] );

        $inbound_settings['inbound-mailer']['sparkpost-key'] = $field['value'];

        /* check if webhook is already created */
        if (isset($inbound_settings['inbound-mailer']['sparkpost']['webhook']['id']) ) {
            $webhook = $sparkpost->get_webhook($inbound_settings['inbound-mailer']['sparkpost']['webhook']['id']);

            if ( isset($webhook['results']['name']) && $webhook['results']['name'] == 'Inbound Now Webhook' ) {
                return;
            }
        }

        /* create webhook location and save it */
        $url = add_query_arg(
            array(
                'action' => 'sparkpost_webhook' ,
                'fast_ajax' => true ,
                'load_plugins' => '["_inbound-now/inbound-pro.php"]'
            ),
            admin_url('admin-ajax.php')
        );

        self::$results = $sparkpost->create_webhook( array(
            'name' => 'Inbound Now Webhook',
            'events' => array(
                'bounce',
                'open',
                'click',
                'relay_rejection',
                'generation_rejection',
                'spam_complaint'
            ),
            'target' => $url,
            'auth_type' => 'basic',
            'auth_credentials' => array(
                'username' => preg_replace("/[^A-Za-z0-9 ]/", '', AUTH_KEY),
                'password' => preg_replace("/[^A-Za-z0-9 ]/", '', AUTH_SALT)
            )
        ) );

        if (isset(self::$results['results'])) {
            $inbound_settings['inbound-mailer']['sparkpost']['webhook'] = self::$results['results'];
        } else {
            $inbound_settings['inbound-mailer']['sparkpost']['webhook'] = self::$results;
        }

        Inbound_Options_API::update_option('inbound-pro', 'settings', $inbound_settings);

    }

    /**
     * @param $field
     */
    public static function sparkpost_activation_routines( $field ) {

        if (!isset($field['sparkpost-key'])) {
            return;
        }

        Inbound_SparkPost_Stats::create_sparkpost_webhooks($field);

    }

    public static function process_webhook() {

        $data = stripslashes(file_get_contents("php://input"));

        $events = json_decode($data,true);

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return;
        }

        $auth_user = preg_replace( "/[^A-Za-z0-9 ]/", '', AUTH_KEY );
        $auth_pass = preg_replace( "/[^A-Za-z0-9 ]/", '', AUTH_SALT );


        if ( $auth_user != $_SERVER['PHP_AUTH_USER'] ) {
            return;
        }

        if ( $auth_pass != $_SERVER['PHP_AUTH_PW'] ) {
            return;
        }

        foreach ($events as $i => $event) {


            if (isset($event['msys']['message_event'])) {
                $event = $event['msys']['message_event'];
            }

            if (isset($event['msys']['track_event'])) {
                $event = $event['msys']['track_event'];
            }


            if ( $event['campaign_id'] == 'test') {
                return;
            }

            if (!isset($event['rcpt_meta']['email_id'])) {
                return;
            }

            if ($event['type'] == 'delivery') {
                continue;
            }

            $args = array(
                'event_name' => 'sparkpost_' . $event['type'],
                'email_id' => $event['rcpt_meta']['email_id'],
                'variation_id' =>  $event['rcpt_meta']['variation_id'],
                'form_id' => '',
                'lead_id' => $event['rcpt_meta']['lead_id'],
                'session_id' => '',
                'event_details' => json_encode($event)
            );

            /* lets not spam our events table with repeat opens and clicks */
            if (!Inbound_Events::event_exists($args)) {
                Inbound_Events::store_event($args);
            }


            /* handle rejections */
            if ($event['type'] == 'spam_compaint') {

                /* create/get maintenance lists */
                $parent = Inbound_Leads::create_lead_list( array(
                    'name' => __( 'Maintenance' , 'inbound-pro' )
                ));

                /* createget spam lists */
                $term = Inbound_Leads::create_lead_list( array(
                    'name' => __( 'Spam Complaints' , 'inbound-pro' ),
                    'parent' =>$parent['id']
                ));

                /* add to spam complaint list */
                Inbound_Leads::add_lead_to_list( $event['rcpt_meta']['lead_id'], $term['id'] );


            }

        }

    }

    /**
     * Check SparkPost Response for Errors and Handle them
     */
    public static function process_send_event( $transmission_args , $response ) {

        /* skip if contains errors */
        if (isset($response['errors'])) {
            return;
        }

        if (isset($transmission_args['recipients'][0]['tags']) && in_array( 'test' , $transmission_args['recipients'][0]['tags']) ) {
            return;
        }

        /* recipients */
        $args = array(
            'event_name' => 'sparkpost_delivery',
            'email_id' => $transmission_args['metadata']['email_id'],
            'variation_id' =>  $transmission_args['metadata']['variation_id'],
            'form_id' => '',
            'lead_id' => $transmission_args['metadata']['lead_id'],
            'session_id' => '',
            'event_details' => json_encode($transmission_args)
        );

        /* lets not spam our events table with repeat opens and clicks */
        if (!Inbound_Events::event_exists($args)) {
            Inbound_Events::store_event($args);
        }

    }

    /**
     * Check SparkPost Response for Errors and Handle them
     */
    public static function process_rejections( $transmission_args , $response ) {
        if (!isset($response['errors']) || !isset($transmission_args['metadata']['lead_id'])) {
            return;
        }

        foreach ($response['errors'] as $error) {

            switch( $error['code'] ) {
                case '1902':

                    /* create/get maintenance lists */
                    $parent = Inbound_Leads::create_lead_list( array(
                        'name' => __( 'Maintenance' , 'inbound-pro' )
                    ));

                    /* create/get rejected lists */
                    $term = Inbound_Leads::create_lead_list( array(
                        'name' => __( 'Rejected' , 'inbound-pro' ),
                        'parent' =>$parent['id']
                    ));

                    /* add to rejected list */
                    Inbound_Leads::add_lead_to_list( $transmission_args['metadata']['lead_id'], $term['id'] );

                    $args = array(
                        'event_name' => 'sparkpost_rejected',
                        'email_id' => $transmission_args['metadata']['email_id'],
                        'variation_id' =>  $transmission_args['metadata']['variation_id'],
                        'form_id' => '',
                        'lead_id' => $transmission_args['metadata']['lead_id'],
                        'session_id' => '',
                        'event_details' => json_encode($error)
                    );

                    /* lets not spam our events table with repeat opens and clicks */
                    if (!Inbound_Events::event_exists($args)) {
                        Inbound_Events::store_event($args);
                    }
                    break;
            }
        }

    }

    public static function unschedule_email( $email_id ) {
        global $Inbound_Mailer_Variations;
        global $inbound_settings;
        global $post;

        $variations = $Inbound_Mailer_Variations->get_variations($post->ID, $vid = null);
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['inbound-mailer']['sparkpost-key'] );

        foreach ($variations as $vid => $variation) {
            $campaign_id =  $email_id	. '_'. $vid;
            $results = $sparkpost->get_transmissions( $campaign_id );
            print_r($results);exit;
        }



    }

    /**
     * Display API status inside settings area
     * @param $field
     */
    public static function display_api_status( $field ) {
        global $inbound_settings;

        /* do nothing if no key present */
        if (!isset($inbound_settings['inbound-mailer']['sparkpost-key'])) {
            return;
        }

        /* set the sparkpost apikey and load sparkpost connector */
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['inbound-mailer']['sparkpost-key'] );

        /* discover sending domains */
        $domains = $sparkpost->get_domains();

        /* check if webhooks are created */
        $webhook_status = __('not created' , 'inbound-pro');
        if (isset($inbound_settings['inbound-mailer']['sparkpost']['webhook']['id']) ) {
            $webhook = $sparkpost->get_webhook($inbound_settings['inbound-mailer']['sparkpost']['webhook']['id']);

            if ( isset($webhook['results']['name']) && $webhook['results']['name'] == 'Inbound Now Webhook' ) {
                $webhook_status = '<span style="color:green;!important;">'.__('created' , 'inbound-pro') . '</span>';
            }
        }

        ?>
        <table class="sparkpost-status-table">
            <tr>
                <td class="inbound-label-field">
                    <?php _e('API Key:','inbound-pro'); ?>
                </td>
                <td class="">

                    <?php

                    if (isset($domains['results']) && is_array($domains['results']) ){
                        echo '<span style="color:green !important;">'.__('active' , 'inbound-pro') . '</span>';
                    } else {
                        if (isset($domains['errors'])) {
                            switch($domains['errors'][0]['message']) {
                                case 'Unauthorized.':
                                    echo '<pre>'.__('invalid' , 'inbound-pro') . '</pre>';

                                    break;
                            }
                        }
                    }

                    ?>

                </td>

            </tr>
        </table>
        <table>
            <tr>
                <td class="inbound-label-field">
                    <?php _e('Sending Domains:','inbound-pro'); ?>
                </td>

                <td class="sparkpost-status-domains status-value">
                    <table>
                        <?php

                        if (isset($domains['results']) && is_array($domains['results']) ){
                            foreach($domains['results'] as $i => $domains ) {

                                ?>

                                <tr>
                                    <td class="inbound-label-field" style='' colspan="2">
                                        <span style="color:green !important;"><?php echo $domains['domain']; ?></span>
                                    </td>

                                </tr>
                                <tr>
                                    <td class="inbound-label-field" style=''>
                                        <?php _e('ownership','inbound-pro'); ?>:
                                    </td>

                                    <td class="status-value">
                                        <?php
                                        if ($domains['status']['ownership_verified']) {
                                            echo '<span style="color:green !important;">'.__('confirmed','inbound-pro').'</span>';
                                        } else {
                                            echo '<span style="color:red !important;">'.__('not confirmed','inbound-pro').'</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="inbound-label-field" style=''>
                                        <?php _e('spf','inbound-pro'); ?>:
                                    </td>

                                    <td class="status-value">
                                        <?php
                                        if ($domains['status']['spf_status'] == 'valid' ) {
                                            echo '<span style="color:green !important;">'.__('valid','inbound-pro').'</span>';
                                        } else {
                                            echo '<span style="color:red !important;">'.__('invalid','inbound-pro').'</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="inbound-label-field" style=''>
                                        <?php _e('dkim','inbound-pro'); ?>:
                                    </td>

                                    <td class="status-value">
                                        <?php
                                        if ($domains['status']['dkim_status'] == 'valid' ) {
                                            echo '<span style="color:green !important;">'.__('valid','inbound-pro').'</span>';
                                        } else {
                                            echo '<span style="color:red !important;">'.__('invalid','inbound-pro').'</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="inbound-label-field" style=''>
                                        <?php _e('compliance status','inbound-pro'); ?>
                                    </td>

                                    <td class="status-value">
                                        <?php
                                        if ($domains['status']['compliance_status'] == 'valid' ) {
                                            echo '<span style="color:green !important;">'.__('valid','inbound-pro').'</span>';
                                        } else {
                                            echo '<span style="color:red !important;">'.__('invalid','inbound-pro').'</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <?php
                            }

                        } else {
                            echo __( 'No sending domains set. Please see https://app.sparkpost.com/account/sending-domains' , 'inbound-pro' );
                        }

                        ?>
                    </table>
                </td>

            </tr>
        </table>

        <table>
            <tr>
                <td class="inbound-label-field">
                    <?php _e('Webhooks:','inbound-pro'); ?>
                </td>

                <td class="sparkpost-status-webhooks status-value">
                    <?php echo  $webhook_status; ?>
                </td>

            </tr>
        </table>
        <?php
    }
}

new Inbound_SparkPost_Stats();