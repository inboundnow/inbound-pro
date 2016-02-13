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

        /* Stores custom click event */
        add_action( 'template_redirect' , array( __CLASS__ ,  'add_click_event_listener' ) , 11); // Click Tracking init
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

        /* record click event */
        do_action( 'inbound_email_click_event' , $args );

    }

    /**
     *  Record a click event in lead profile
     */
    public static function add_click_event_listener() {

        if ( is_admin() ) {
            return;
        }

        if ( !isset($_GET['lead_id']) || !isset($_GET['email_id']) ) {
            return;
        }

        global $post;

        /* setup args */
        $args['page_id'] = (isset($post) && $post->ID ) ? $post->ID : 0;
        $args['email_id'] = $_GET['email_id'];
        $args['lead_id'] = $_GET['lead_id'];
        $args['variation_id'] = (isset($_GET['inbvid'])) ? $_GET['inbvid'] : 0;
        $args['event_details'] = json_encode($_GET);
        error_log(print_r($args,true));
        /* record click event */
        do_action( 'inbound_email_click_event' , $args );


    }




}

$Inbound_Mailer_Tracking = new Inbound_Mailer_Tracking();