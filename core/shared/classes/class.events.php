<?php

/**
 * Class Inbound_Events
 * Stores action events into MySQL database table
 * Stores: form submissions events, cta link clicks, email link clicks, custom events
 *
 */
class Inbound_Events {

    /**
     * Inbound_Events constructor.
     */
    public function __construct(){
        self::add_hooks();
    }

    /**
     * Define WordPress hooks and filters
     */
    public static function add_hooks() {
        /* create events table if does not exist */
        add_action('inbound_shared_activate' , array( __CLASS__ , 'create_events_table' ));

        /* listen for cta clicks and record event to events table */
        add_action('inbound_tracked_cta_click' , array( __CLASS__ , 'store_cta_click'), 10 , 1);

        /* listen for Inbound Form submissions and record event to events table */
        add_action('inbound_store_lead_post' , array( __CLASS__ , 'store_form_submission'), 10 , 1);

        /* listen for Inbound Form submissions and record event to events table */
        add_action('inbound_email_click_event' , array( __CLASS__ , 'store_email_click'), 10 , 1);

    }

    /**
     * Creates inbound_events
     */
    public static function create_events_table(){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `event_name` varchar(255) NOT NULL,
			  `page_id` mediumint(20) NOT NULL,
			  `variation_id` mediumint(9) NOT NULL,
			  `form_id` mediumint(20) NOT NULL,
			  `cta_id` mediumint(20) NOT NULL,
			  `email_id` mediumint(20) NOT NULL,
			  `lead_id` mediumint(20) NOT NULL,
			  `lead_uid` varchar(255) NOT NULL,
			  `session_id` varchar(255) NOT NULL,
			  `event_details` text NOT NULL,
			  `datetime` datetime NOT NULL,

			  UNIQUE KEY id (id)
			) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Stores a form submission event into events table
     * @param $lead
     */
    public static function store_form_submission( $lead ){

        parse_str($lead['raw_params'] , $raw_params );
        $details = array_merge($raw_params,$lead);
        $args = array(
            'event_name' => 'inbound_form_submission',
            'page_id' => $lead['page_id'],
            'variation_id' =>  $lead['variation'],
            'form_id' => $raw_params['inbound_form_id'],
            'lead_id' => $lead['id'],
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : null ),
            'session_id' => null,
            'event_details' => json_encode($details),
            'datetime' => $lead['wordpress_date_time']
        );

        self::store_event($args);
    }

    /**
     * Stores cta click event into events table
     * @param $args
     */
    public static function store_cta_click( $args ) {
        $args['event_name'] = 'inbound_cta_click';
        self::store_event($args);
    }

    /**
     * Stores inbound email click event into events table
     * @param $args
     */
    public static function store_email_click( $args ){
        global $wp_query;

        $current_page_id = $wp_query->get_queried_object_id();
        error_log($current_page_id);
        $args = array(
            'event_name' => 'inbound_email_click',
            'page_id' => $current_page_id,
            'email_id' => $args['id'],
            'variation_id' => $args['urlparams']['inbvid'],
            'lead_id' => $args['urlparams']['lead_id'],
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : null ),
            'event_details' => json_encode($args['urlparams']),
            'datetime' => $args['datetime']
        );

        self::store_event($args);
    }

    public static function store_event( $args ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $timezone_format = 'Y-m-d G:i:s T';
        $wordpress_date_time =  date_i18n($timezone_format);

        /* event name required */
        if (!isset($args['event_name'])) {
            return;
        }

        $defaults = array(
            'page_id' => null,
            'variation_id' => null,
            'form_id' => null,
            'cta_id' => null,
            'email_id' => null,
            'lead_id' => ( isset($_COOKIE['wp_lead_id']) ? $_COOKIE['wp_lead_id'] : null ),
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : null ),
            'session_id' => null,
            'event_details' => null,
            'datetime' => $wordpress_date_time
        );

        $args = array_merge( $defaults , $args );
        error_log(print_r($args,true));
        $wpdb->insert(
            $table_name,
            $args
        );
    }
}

new Inbound_Events();