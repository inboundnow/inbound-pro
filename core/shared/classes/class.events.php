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
			  `page_id` mediumint(9) NOT NULL,
			  `variation_id` mediumint(9) NOT NULL,
			  `form_id` mediumint(9) NOT NULL,
			  `cta_id` mediumint(9) NOT NULL,
			  `email_id` mediumint(9) NOT NULL,
			  `lead_id` mediumint(9) NOT NULL,
			  `session_id` mediumint(9) NOT NULL,
			  `event_details` text NOT NULL,
			  `datetime` datetime NOT NULL,

			  UNIQUE KEY id (id)
			) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public static function store_form_submission( $args ){
        $args['event_name'] = 'inbound_form_submission';
        self::store_event($args);
    }

    public static function store_cta_click( $args ) {
        $args['event_name'] = 'inbound_cta_click';
        self::store_event($args);
    }

    public static function store_email_click( $args ){
        $args['event_name'] = 'inbound_email_click';
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
            'lead_id' => null,
            'session_id' => null,
            'event_details' => null,
            'datetime' => $wordpress_date_time
        );

        $args = array_merge( $defaults , $args );

        $wpdb->insert(
            $table_name,
            $args
        );
    }
}

new Inbound_Events();