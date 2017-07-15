<?php

/**
 * Tracks email clicks under the `inbound_email_click` event name in the `inbound_events` table. We also record email clicks under the {service}_click event name.
 *
 * @package Mailer
 * @subpackage ClickTracking
 */


class Inbound_Mailer_Tracking {

    /**
     *  Initializes Class
     */
    public function __construct() {

        self::load_hooks();

    }

    public static function load_hooks() {
        add_action( 'inbound_track_link', array(__CLASS__, 'track_link'));
 }

    /**
     *  Listens for tracked link processing - Right now this preparing an event but not storing one. Needs investigation.
     *
     */
    public static function track_link( $args ) {


        $do_not_track = apply_filters('inbound_analytics_stop_track', false );

        if ( $do_not_track || !isset($args['email_id']) || !$args['email_id'] ) {
            return;
        }

        /* setup args */
        $args['page_id'] = (isset($post) && $post->ID ) ? $post->ID : 0;
        $args['email_id'] = $args['email_id'];
        $args['lead_id'] = $args['lead_id'];
        $args['variation_id'] = (isset($args['vid'])) ? $args['vid'] : 0;
        $args['event_details'] = json_encode($_GET);

        do_action('inbound_email_click_event' , $args );
    }

}

$Inbound_Mailer_Tracking = new Inbound_Mailer_Tracking();