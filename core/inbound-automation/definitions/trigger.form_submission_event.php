<?php
/*
Trigger Name: Form Submission Event
Trigger Description: This trigger fires whenever a tracked Form is submitted.
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/

class Inbound_Automation_Trigger_Form_Submission {

    static $trigger;

	/**
	*  Initialize Class
	*/
	function __construct() {
	    self::$trigger = 'inbound_store_lead_post';
		add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
		add_action( 'activate/inbound-automation' , array( __CLASS__ , 'create_dummy_event' ) );
	}

	/**
	*  Define Trigger
	*/
	public static function define_trigger( $triggers ) {

		/* Set & Extend Trigger Argument Filters */
		$arguments = apply_filters('trigger/inbound_store_lead_post/args' , array(
			'lead_data' => array(
				'id' => 'lead_data',
				'label' => __( 'Lead Data' , 'inbound-pro' )
			)
		) );

		/* Set & Extend Action DB Lookup Filters */
		$db_lookup_filters = apply_filters( 'trigger/inbound_store_lead_post/db_filters' , array (
			array(
                'id' => 'lead_data',
                'label' => __( 'Validate Lead Data' , 'inbound-pro' ),
                'class_name' => 'Inbound_Automation_Query_Lead'
			)
		));

		/* Set & Extend Available Actions */
		$actions = apply_filters('trigger/inbound_store_lead_post/actions' , array(
			'send_email' ,
			'wait' ,
			'relay_data' ,
			'add_remove_lead_list'
		) );

		$triggers[self::$trigger] = array (
			'label' => __( 'On Inbound Form Submission' , 'inbound-pro' ),
			'description' => __( 'This trigger fires whenever a lead is submitted through an Inbound Form.' , 'inbound-pro' ),
			'action_hook' => self::$trigger,
			'scheduling' => false,
			'arguments' => $arguments,
			'db_lookup_filters' => $db_lookup_filters,
			'actions' => $actions
		);

		return $triggers;
	}

    /**
     * Simulate trigger - perform on plugin activation
     */
    public static function create_dummy_event() {

        parse_str( 'wpleads_first_name=Example%20User&wpleads_email_address=test%40inboundnow.com&country-dropdown=US&wpleads_notes=Please%20contact%20me!%20&inbound_form_n=Auto%20Responder%20Form&inbound_form_lists=108&inbound_form_id=2741&inbound_current_page_url=http%3A%2F%2Finboundsoon.dev%2Fcall-to-action-testing%2F&inbound_notify=YXR3ZWxsLnB1Ymxpc2hpbmdAZ21haWwuY29t' , $raw_params );
        parse_str( 'wpleads_first_name=Example%20User&wpleads_email_address=test%40inboundnow.com&wpleads_notes=Please%20contact%20me!%20&inbound_form_lists=108' , $mapped_params );
        $pageviews = json_encode( stripslashes('{\"95897\":[\"2015/06/01 20:01:49\"]}' )  , true );
        $lead = array (
            'user_ID' => 2,
            'wordpress_date_time' => '2015-06-01 18:03:31 UTC',
            'email' => 'test@inboundnow.com',
            'name' => 'Example User',
            'first_name' => 'Example',
            'last_name' => 'User',
            'page_id' => 95897,
            'page_views' => $pageviews,
            'raw_params' => $raw_params ,
            'mapped_params' => $mapped_params ,
            'url_params' => '{}',
            'variation' => 0,
            'source' => 'http://www.inboundnow.com/',
            'ip_address' => '127.0.0.1',
            'lead_lists' => Array (
                '0' => 108
            ),
            'post_type' => 'post',
            'id' => 97623
        );

        $inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );
        $inbound_arguments = ( $inbound_arguments  ) ?  $inbound_arguments : array();
        $inbound_arguments[self::$trigger]['lead_data'] = $lead;
        Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
    }

}

/* Load Trigger */
$Inbound_Automation_Trigger_Form_Submission = new Inbound_Automation_Trigger_Form_Submission;
