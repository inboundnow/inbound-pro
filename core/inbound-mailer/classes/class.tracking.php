<?php

class Inbound_Mailer_Tracking {

    /**
     *  Initializes Class
     */
    public function __construct() {

        self::load_hooks();

    }

    public static function load_hooks() {
        /* track masked cta links */
        add_action( 'inbound_track_link', array(__CLASS__, 'track_link'));
 }

    /**
     *  Listens for tracked masked link processing
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

    }

}

$Inbound_Mailer_Tracking = new Inbound_Mailer_Tracking();