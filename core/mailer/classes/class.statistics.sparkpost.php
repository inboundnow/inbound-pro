<?php

/**
 * Class Inbound_SparkPost_Stats provides webhook listeners and data storage and retrieval methods related to email stats
 *
 * @package Mailer
 * @subpackage  SparkPost
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

            add_action( 'mailer/unschedule-email' , array( __CLASS__, 'unschedule_email' ) , 10 , 1 );
        }

        /* For processing webhooks */
        add_action('wp_ajax_nopriv_sparkpost_webhook', array(__CLASS__, 'process_webhook'));

        /* process send event */
        add_action( 'sparkpost/send/response' , array( __CLASS__ , 'process_send_event') , 10 , 2 );

        /* process rejection errors */
        add_action( 'sparkpost/send/response' , array( __CLASS__ , 'process_rejections') , 10 , 2 );
    }


    /**
     *  Get SparkPost Stats including varition totals
     */
    public static function get_sparkpost_webhook_stats() {
        global $post, $Inbound_Mailer_Variations;

        /* prepare date range */
        self::prepare_date_range();

        /* get user saved job id for automated email */
        if ($post->post_status=='automated') {
            $job_id = get_user_option(
                'inbound_mailer_reporting_job_id_' .$post->ID,
                get_current_user_id()
            );
        } else {
            $job_id = null;
        }

        /* first get totals */
        self::get_sparkpost_inbound_events( $post->ID , $vid = null , $job_id  );

        /* now total variations */
        $variations = $Inbound_Mailer_Variations->get_variations($post->ID, $vid = null );

        foreach ( $variations as $vid => $variation ) {
            self::get_sparkpost_inbound_events( $post->ID , $vid , $job_id );
        }

        return self::$stats;
    }

    /**
     * Get all all custom event data by a certain indicator
     */
    public static function get_sparkpost_inbound_events( $email_id , $variation_id = null , $job_id = null ) {
        global $wpdb, $post, $inbound_settings;

        /* check if email id is set else use global post object */
        if ($email_id) {
            $post = get_post($email_id);
        }


        /* whitelist of email statuses we prepare statistics for */
        if (!in_array($post->post_status, array('sent', 'sending', 'automated'))) {
            return array();
        }

        /* get email setup data */
        $settings = Inbound_Email_Meta::get_settings($post->ID);
        $table_name = $wpdb->prefix . "inbound_events";
        $variation_query = '';
        $job_id_query = '';

        /* check if automated email has a job id saved */
        if ($post->post_status=='automated') {

            /* account for last_send */
            $job_id = ($job_id == 'last_send') ?Inbound_Mailer_Post_Type::get_last_job_id($email_id) : $job_id ;

            /* add job id to the query if not zero or false */
            if ($job_id) {
                $job_id_query = ' AND job_id="' . $job_id . '" ';
            }
        }

        /* Add variation id to the query if it's present */
        if (is_numeric($variation_id)) {
            $variation_query = ' AND variation_id="' . $variation_id . '" ';
        }

        /* get deliveries */
        $wordpress_date_time =  date_i18n('Y-m-d G:i:s');
        $today = new DateTime($wordpress_date_time);
        $schedule_date = new DateTime($settings['send_datetime']);
        $interval = $today->diff($schedule_date);

        /* complicated  - needs doc */
        if ( !$settings['send_datetime'] || $interval->format('%R') == '-' || $settings['email_type'] == 'automated' ) {
            $query = 'SELECT `variation_id`, `lead_id` FROM ' . $table_name . ' WHERE `email_id` = "' . $email_id . '"  ' . $variation_query . $job_id_query . ' AND `event_name` =  "sparkpost_delivery"';

            /* add date constraints if applicable */
            if (isset(self::$stats['start_date'])) {
                $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
            }
            $results = $wpdb->get_results($query, ARRAY_A);

            /*get the unique lead_id sends for each variation*/
            $sent_array = array();
            foreach( $results as $index => $rows ){
                if(!isset($sent_array[$rows['variation_id']][$rows['lead_id']])){
                    $sent_array[$rows['variation_id']][$rows['lead_id']] = 1;
                }
            }
            $sent = (count($sent_array, 1) - count($sent_array));
        } else {
            $sent = 0;
        }

        /* get opens */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "sparkpost_open"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $opens = self::process_selected_rows($results);

        /* get clicks */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "sparkpost_click"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $clicks = self::process_selected_rows($results);

        /* get bounce */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "sparkpost_bounce"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $bounces = self::process_selected_rows($results);

        /* get rejects */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' ';
        $query .= ' AND ( `event_name` =  "sparkpost_rejected" OR `event_name` = "sparkpost_relay_rejection" )';

        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $rejects = self::process_selected_rows($results);

        /* get spam complaints */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "sparkpost_spam_complaint"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $complaints = self::process_selected_rows($results);

        /* get unsubscribes */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "inbound_unsubscribe"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $unsubs = self::process_selected_rows($results);

        /* get mutes */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "inbound_mute"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $mutes = self::process_selected_rows($results);


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
     * Processes queried rows into action counts
     */
    public static function process_selected_rows($query){

        /*get the unique lead_id sends for each variation*/
        $action_count = array();
        foreach( $query as $index => $rows ){
            if(!isset($action_count[$rows['variation_id']][$rows['lead_id']])){
                $action_count[$rows['variation_id']][$rows['lead_id']] = 1;
            }
        }

        /* count recursivly first, then remove the counted variation ids*/
        $counted_actions = (count($action_count, 1) - count($action_count));

        return $counted_actions;
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
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['mailer']['sparkpost-key'] );
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
    public static function prepare_date_range() {
        $range = get_user_option(
            'inbound_mailer_screen_option_range',
            get_current_user_id()
        );


        $range = ($range) ? $range : 90;

        /* create date objects */
        $start_date = new DateTime();
        $start_date->modify('-'.$range.' days');
        self::$stats['start_date'] = $start_date->format('Y-m-d G:i:s T');

        $end_date = new DateTime();
        self::$stats['end_date'] = $end_date->format('Y-m-d G:i:s T');

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

        $inbound_settings['mailer']['sparkpost-key'] = $field['value'];

        /* check if webhook is already created */
        if (isset($inbound_settings['mailer']['sparkpost']['webhook']['id']) ) {
            $webhook = $sparkpost->get_webhook($inbound_settings['mailer']['sparkpost']['webhook']['id']);

            if ( isset($webhook['results']['name']) && $webhook['results']['name'] == 'Inbound Now Webhook' ) {
                return;
            }
        }

        /* create webhook location and save it */
        $url = add_query_arg(
            array(
                'action' => 'sparkpost_webhook' ,
                'fast_ajax' => true ,
                'load_plugins' => '["inbound-pro/inbound-pro.php"]'
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
            $inbound_settings['mailer']['sparkpost']['webhook'] = self::$results['results'];
        } else {
            $inbound_settings['mailer']['sparkpost']['webhook'] = self::$results;
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
            error_log('PHP_AUTH_USER not detected');
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

        if (!is_array($events)) {
            error_log('Events empty?');
            error_log($events);
            error_log($data);
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
                'job_id' => (isset($event['rcpt_meta']['job_id'])) ? $event['rcpt_meta']['job_id'] : 0,
                'rule_id' => (isset($event['rcpt_meta']['rule_id'])) ? $event['rcpt_meta']['rule_id'] : 0,
                'session_id' => '',
                'event_details' => json_encode($event)
            );

            /* lets not spam our events table with repeat opens and clicks */
            if (!Inbound_Events::event_exists($args)) {
                Inbound_Events::store_event($args);
            }


            /* handle rejections */
            if ($event['type'] == 'spam_compaint') {

                $maintenance_lists = Inbound_Maintenance_Lists::get_lists();

                /* add to spam complaint list */
                Inbound_Leads::add_lead_to_list( $event['rcpt_meta']['lead_id'], $maintenance_lists['spam']['id'] );
            }

            /* handle bounces */
            if ($event['type'] == 'bounce') {

                /* create/get maintenance lists */
                $maintenance_lists = Inbound_Maintenance_Lists::get_lists();

                /* add to spam complaint list */
                Inbound_Leads::add_lead_to_list( $event['rcpt_meta']['lead_id'], $maintenance_lists['bounce']['id'] );


            }

        }

    }

    /**
     * Check SparkPost Response for Errors and Handle them
     */
    public static function process_send_event( $transmission_args , $response ) {

        /* skip if contains errors */
        if (isset($response['errors'])) {
            error_log(print_r($errors,true));
            return;
        }

        if (isset($transmission_args['recipients'][0]['tags']) && in_array( 'test' , $transmission_args['recipients'][0]['tags']) ) {
            return;
        }

        /* clear funnel from session if it exists */
        $_SESSION['inbound_page_views'] = "";

        /* recipients */
        $args = array(
            'event_name' => 'sparkpost_delivery',
            'email_id' => $transmission_args['metadata']['email_id'],
            'variation_id' =>  $transmission_args['metadata']['variation_id'],
            'form_id' => '',
            'lead_id' => $transmission_args['metadata']['lead_id'],
            'rule_id' => $transmission_args['metadata']['rule_id'],
            'job_id' => $transmission_args['metadata']['job_id'],
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
            error_log(print_r($errors,true));
            return;
        }

        foreach ($response['errors'] as $error) {

            switch( $error['code'] ) {
                case '1902':

                    /* create/get maintenance lists */
                    $maintenance_lists = Inbound_Maintenance_Lists::get_lists();

                    /* add to rejected list */
                    Inbound_Leads::add_lead_to_list( $transmission_args['metadata']['lead_id'], $maintenance_lists['rejected']['id'] );

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
        global $inbound_settings;

        $variations = Inbound_Mailer_Variations::get_variations($email_id);
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['mailer']['sparkpost-key'] );

        foreach ($variations as $vid => $variation) {

            $campaign_id =  $email_id	. '_'. $vid;
            $results = $sparkpost->get_transmissions( $campaign_id );

            $delete_count = 0;
            foreach ($results['results'] as $key=>$transmission) {

                if ($transmission['state'] != 'submitted' ) {
                    continue;
                }

                $result = $sparkpost->delete_transmission( $transmission['id'] );

                $delete_count++;
            }
        }

    }

    /**
     * Display API status inside settings area
     * @param $field
     */
    public static function display_api_status( $field ) {
        global $inbound_settings;

        /* do nothing if no key present */
        if (!isset($inbound_settings['mailer']['sparkpost-key'])) {
            return;
        }

        /* set the sparkpost apikey and load sparkpost connector */
        $sparkpost = new Inbound_SparkPost(  $inbound_settings['mailer']['sparkpost-key'] );

        /* discover sending domains */
        $domains = $sparkpost->get_domains();
        $webhooks = $sparkpost->get_webhooks();


        /* check if webhooks are created */
        $webhook_status = __('not created' , 'inbound-pro');
        if (isset($inbound_settings['mailer']['sparkpost']['webhook']['id']) ) {
            $webhook = $sparkpost->get_webhook($inbound_settings['mailer']['sparkpost']['webhook']['id']);

            if ( isset($webhook['results']['name']) && $webhook['results']['name'] == 'Inbound Now Webhook' ) {
                $webhook_status = '<span style="color:green;!important;">'.__('created' , 'inbound-pro') . '</span>';
            }
        } else if (isset($webhooks['results']) && count($webhooks['results']) > 0 ) {
            /* If for any reason we lost data and the webhooks still exist lets find them and update the data */
            foreach ($webhooks['results'] as $key => $webhook) {

                if ( $webhook['name'] != 'Inbound Now Webhook') {
                    continue;
                }

                if (!strstr($webhook['target'] , site_url() )) {
                    continue;
                }

                $inbound_settings['mailer']['sparkpost']['webhook']['id'] = $webhook['id'];

                Inbound_Options_API::update_option('inbound-pro', 'settings', $inbound_settings);
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
                                case 'Forbidden.':
                                    echo '<pre>'.__('API Key does not have correct permissions checked. Try creating a new key and checking all the available permissions.' , 'inbound-pro') . '</pre>';

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
