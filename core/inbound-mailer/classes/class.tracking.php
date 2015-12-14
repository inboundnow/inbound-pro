<?php

class Inbound_Mailer_Tracking {

    /**
     *  Initializes Class
     */
    public function __construct() {

        self::load_hooks();

    }

    public static function load_hooks() {

        /* Stores custom click event */
        add_action( 'template_redirect' , array( __CLASS__ ,  'add_click_event_listener' ) , 11); // Click Tracking init
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

        /* record click event */
        do_action( 'inbound_email_click_event' , $args );


    }




}

$Inbound_Mailer_Tracking = new Inbound_Mailer_Tracking();