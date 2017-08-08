<?php
/**
 * Class defines trigger that fires when a tracked link is clicked
 * @author: Inbound Now
 * @contributors: Hudson Atwell
 * @package Automation
 * @subpackage Triggers
 *
 */

class Inbound_Automation_Trigger_inbound_track_link {

    static $trigger;

    /**
     *  Initialize Class
     */
    function __construct() {
        self::$trigger = 'inbound_track_link';
        add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
        add_action( 'activate/automation' , array( __CLASS__ , 'create_dummy_event' ) );

    }

    /**
     *  Define Trigger
     */
    public static function define_trigger( $triggers ) {

        /* Set & Extend Trigger Argument Filters */
        $arguments = apply_filters('trigger/'.self::$trigger.'/args' , array(
            'link_data' => array(
                'id' => 'link_data',
                'label' => __( 'Clicked link data' , 'inbound-pro' ),
                'callback' => array(
                    get_class() , 'enrich_data'
                )
            )
        ) );

        /* Set & Extend Action DB Lookup Filters */
        $db_lookup_filters = apply_filters( 'trigger/'.self::$trigger.'/db_filters' , array (
            array(
                'id' => 'lead_data',
                'label' => __( 'Lead lookup' , 'inbound-pro' ),
                'class_name' => 'Inbound_Automation_Query_Lead'
            )
        ));

        /* Set & Extend Available Actions */
        $actions = apply_filters('trigger/'.self::$trigger.'/actions' , array(
            'send_email' ,
            'wait' ,
            'relay_data' ,
            'add_remove_lead_list',
            'add_remove_lead_tag',
            'kill_lead_tasks',
        ) );

        $triggers[self::$trigger] = array (
            'label' => __( 'On tracked link click' , 'inbound-pro' ),
            'description' => __( 'This trigger fires whenever a link tracked by Inbound Pro is clicked.' , 'inbound-pro' ),
            'action_hook' => self::$trigger,
            'arguments' => $arguments,
            'db_lookup_filters' => $db_lookup_filters,
            'actions' => $actions
        );

        return $triggers;
    }

    /**
     * Filter Lead data and make sure defaults we need are present and remove unneeded elements
     * @param $args
     * @return array
     */
    public static function enrich_data( $args ) {

        $args['cta_id'] = isset($args['cta_id']) ? $args['cta_id'] : 0;
        $args['page_id'] = isset($args['page_id']) ? $args['page_id'] : 0;
        $args['vid'] = isset($args['vid']) ? $args['vid'] : 0;
        $args['url'] = isset($args['url']) ? $args['url'] : '';
        $args['lead_id'] = isset($args['lead_id']) ? $args['lead_id'] : 0;
        $args['id'] = isset($args['id']) ? $args['id'] : 0;
        $args['lead_id'] = ($args['lead_id']) ? $args['lead_id'] : $args['id'] ;

        return $args;
    }

    /**
     * Simulate trigger - perform on plugin activation
     */
    public static function create_dummy_event() {

       $lead = array (
            'id' => 54321,
            'lead_id' => 54321,
            'email_id' => 321,
            'page_id' => 123,
            'cta_id' => 132,
            'vid' => 0,
            'url' => 'https://www.inboundnow.com/',
            'tracking_id' => __('Special Tracking ID' , 'inbound-pro' )
        );

        $inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );
        $inbound_arguments = ( is_array($inbound_arguments)  ) ?  $inbound_arguments : array();
        $inbound_arguments[self::$trigger]['lead_data'] = $lead;
        Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
    }
}

/* Load Trigger */
new Inbound_Automation_Trigger_inbound_track_link;
