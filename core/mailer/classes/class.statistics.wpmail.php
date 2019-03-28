<?php

/**
 * Class Inbound_WPMail_Stats provides data storage and retrieval methods related to email stats for WPMail service
 *
 * @package Mailer
 * @subpackage  WPMail
 */


class Inbound_WPMail_Stats {

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
            add_action( 'mailer/unschedule-email' , array( __CLASS__, 'unschedule_email' ) , 10 , 1 );
        }

        /* For processing webhooks */
        add_action('init', array(__CLASS__, 'process_open_event'));

        /* process send event */
        add_action( 'mailer/wp_mail/send' , array( __CLASS__ , 'process_send_event') , 10 , 3 );


    }

    /**
     *  Get SparkPost Stats including variation totals
     */
    public static function get_wpmail_stats() {
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
        self::get_wpmail_inbound_events( $post->ID , $vid = null , $job_id  );

        /* now total variations */
        $variations = $Inbound_Mailer_Variations->get_variations($post->ID, $vid = null );

        foreach ( $variations as $vid => $variation ) {
            self::get_wpmail_inbound_events( $post->ID , $vid , $job_id );
        }

        return self::$stats;
    }

    /**
     * Get all all custom event data by a certain indicator
     */
    public static function get_wpmail_inbound_events( $email_id , $variation_id = null , $job_id = null ) {
        global $wpdb, $post, $inbound_settings;

        /* check if email id is set else use global post object */
        if ($email_id) {
            $post = get_post($email_id);
        }

        /* whitelist of email statuses we prepare statistics for */
        if (!in_array($post->post_status, array('sent', 'sending', 'automated', 'direct_email'))) {
            return array();
        }

        /* get email setup data */
        $settings = Inbound_Email_Meta::get_settings($post->ID);
        $settings['send_datetime'] = (isset($settings['send_datetime'])) ? $settings['send_datetime'] : '';
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

        /* get correct format - d/m/Y date formats will fatal */
        $wordpress_date_time_format = 'Y-m-d G:i:s';


        /* add date if does not exist */
        if(!$settings['send_datetime']) {
            $settings['send_datetime'] = date_i18n('Y-m-d G:i:00');
        }

        /* add time to timestamp if does not exist */
        if (substr_count($settings['send_datetime'],':') === 1 ) {
            $settings['send_datetime'] = $settings['send_datetime'] .':00';
        }

        /* add time if does not exist */
        if(!strstr($settings['send_datetime'],':')) {
            $settings['send_datetime'] = $settings['send_datetime'] . " 00:00:00";
        }

        $date = DateTime::createFromFormat(trim($wordpress_date_time_format) , trim($settings['send_datetime']));

        /* if the date object is bad then prevent fatal */
        if (!$date) {
            $date = new DateTime(date_i18n($wordpress_date_time_format));
        }

        $schedule_date = new DateTime($date->format('Y-m-d G:i:s'));
        $interval = $today->diff($schedule_date);

        /* complicated  - needs doc */
        if ( !$settings['send_datetime'] || $interval->format('%R') == '-' || $settings['email_type'] == 'automated' ) {
            $query = 'SELECT `variation_id`, `lead_id` FROM ' . $table_name . ' WHERE `email_id` = "' . $email_id . '"  ' . $variation_query . $job_id_query . ' AND `event_name` =  "wpmail_delivery"';

            /* add date constraints if applicable */
            if (isset(self::$stats['start_date'])) {
                $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
            }

            $results = $wpdb->get_results($query, ARRAY_A);

            $sent = count($results);
        } else {
            $sent = 0;
        }

        /* get opens */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "wpmail_open"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $opens = self::process_selected_rows($results);

        /* get clicks */
        $query = 'SELECT `variation_id`, `lead_id` FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"  '. $variation_query . $job_id_query.' AND `event_name` =  "wpmail_click"';
        /* add date constraints if applicable */
        if (isset(self::$stats['start_date'])) {
            $query .= ' AND datetime >= "'.self::$stats['start_date'].'" AND  datetime <= "'.self::$stats['end_date'].'" ';
        }
        $results = $wpdb->get_results( $query, ARRAY_A );
        $clicks = self::process_selected_rows($results);


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
            'unsubs' => $unsubs,
            'mutes' => $mutes,
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

        $stats = get_post_meta( $post->ID , 'inbound_statistics' , true );

        self::$stats = ($stats) ? $stats : array( 'sparkpost' => array() );
    }

    /**
     *	Update db stats object
     */
    public static function update_statistics_object() {
        global $post;

        update_post_meta( $post->ID , 'inbound_statistics' , self::$stats );

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
        $end_date->modify('+2 days');
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
            'rejects' => 0,
            'mutes' => 0,
            'unopened' => 0
        );

        /* skip processing if no data */
        if (!isset(self::$stats['wp_mail'])) {
            return;
        }

        foreach (self::$stats['wp_mail'] as $block_key => $data ) {

            /* prepare default counters for variations */
            foreach ($data['variations'] as $vid => $totals ) {
                self::$stats['totals']['variations'][$vid] = array(
                    'sent' => 0,
                    'opens' => 0,
                    'clicks' => 0,
                    'unsubs' => 0,
                    'mutes' => 0,
                    'unopened' => 0
                );
            }

            foreach ($data['variations'] as $vid => $totals ) {

                self::$stats['totals']['sent'] = self::$stats['totals']['sent'] + $totals['sent'];
                self::$stats['totals']['opens'] = self::$stats['totals']['opens'] + $totals['opens'];
                self::$stats['totals']['clicks'] = self::$stats['totals']['clicks'] + $totals['clicks'];

                self::$stats['totals']['variations'][$vid]['sent'] = self::$stats['totals']['variations'][$vid]['sent'] + $totals['sent'];
                self::$stats['totals']['variations'][$vid]['clicks'] = self::$stats['totals']['variations'][$vid]['clicks'] + $totals['clicks'];
                self::$stats['totals']['variations'][$vid]['opens'] = self::$stats['totals']['variations'][$vid]['opens'] + $totals['opens'];

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

    public static function process_open_event() {

        if (!isset($_GET['action']) || $_GET['action'] !=  'wpmail_open' ) {
            return;
        }

        $args = array(
            'event_name' => 'wpmail_open',
            'email_id' => $_GET['email_id'],
            'variation_id' =>  $_GET['variation_id'],
            'form_id' => '',
            'lead_id' => $_GET['lead_id'],
            'job_id' => (isset($_GET['job_id'])) ? $_GET['job_id'] : 0,
            'rule_id' => (isset($_GET['rule_id'])) ? $_GET['rule_id'] : 0,
            'session_id' => '',
            'event_details' => json_encode($_GET)
        );

        /* lets not spam our events table with repeat opens and clicks */
        if (!Inbound_Events::event_exists($args)) {
            Inbound_Events::store_event($args);
        }
        exit;
    }

    /**
     * Check SparkPost Response for Errors and Handle them
     */
    public static function process_send_event( $response , $email , $row ) {

        /* skip if contains errors */
        if ($response['message'] == 'fail') {
            error_log(print_r($response,true));
            return;
        }

        /* clear funnel from session if it exists */
        $_SESSION['inbound_page_views'] = "";

        /* recipients */
        $args = array(
            'event_name' => 'wpmail_delivery',
            'email_id' => isset($row['email_id']) ? $row['email_id'] : 0,
            'variation_id' =>  (isset($row['variation_id'])) ? $row['variation_id'] : 0,
            'form_id' => '',
            'lead_id' => isset($row['lead_id']) ? $row['lead_id'] : 0 ,
            'rule_id' => isset($row['rule_id']) ? $row['rule_id'] : 0,
            'job_id' => isset($row['job_id']) ? $row['job_id'] : 0 ,
            'session_id' => '',
            'event_details' => json_encode(array_merge($email,$row))
        );

        /* lets not spam our events table with repeat opens and clicks */
        if (!Inbound_Events::event_exists($args)) {
            Inbound_Events::store_event($args);
        }
    }


    public static function unschedule_email( $email_id ) {
        global $inbound_settings, $wpdb;

        /* Set target mysql table name */
        $table_name = $wpdb->prefix . "inbound_email_queue";

        $query = "DELETE FROM {$table_name} WHERE `email_id` = '{$email_id}' ";
        $wpdb->query($query);
    }

}

new Inbound_WPMail_Stats();
