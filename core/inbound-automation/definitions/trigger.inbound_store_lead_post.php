<?php
/*
Trigger Name: Form Submission Event
Trigger Description: This trigger fires whenever a tracked Form is submitted.
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/

class Inbound_Automation_Trigger_inbound_store_lead_post {

    static $trigger;

	/**
	*  Initialize Class
	*/
	function __construct() {
	    self::$trigger = 'inbound_store_lead_post';
		add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
		add_action( 'activate/inbound-automation' , array( __CLASS__ , 'create_dummy_event' ) );

		/* make sure event is fired when leads are being created manually */
		add_action( 'wp_insert_post', array( __CLASS__ , 'simulate_new_lead' ) , 10, 3 );
	}

	/**
	 *  Fire inbound_store_lead_post when a new lead is manually created
	 */
	public static function simulate_new_lead( $post_id ) {
		global $post_id, $post;

		if ( wp_is_post_revision( $post_id )
			|| (defined('DOING_AJAX') && DOING_AJAX )
			|| ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			) {
			return;
		}

		remove_action( 'wp_insert_post', array( __CLASS__ , 'simulate_new_lead' ) , 10, 3 );

		if ( !isset($post) || $post->post_type != 'wp-lead' ) {
			return;
		}

		$lead = get_post_custom( $post_id );


		foreach ( $lead as $key => $value ) {
			if (isset($value[0])) {
				$lead[$key] = $value[0];
			}
		}

		$lead['id'] = $post_id;
		$lead_lists = Inbound_Leads::get_lead_lists_by_lead_id($post_id);
		$lead['lead_lists'] = json_encode(array_keys( $lead_lists));
		$lead['form_id'] = 'wp-core';
		do_action( 'inbound_store_lead_post' , $lead );
	}


	/**
	*  Define Trigger
	*/
	public static function define_trigger( $triggers ) {

		/* Set & Extend Trigger Argument Filters */
		$arguments = apply_filters('trigger/inbound_store_lead_post/args' , array(
			'lead_data' => array(
				'id' => 'lead_data',
				'label' => __( 'Lead Data' , 'inbound-pro' ),
				'callback' => array(
					get_class() , 'expand_argument_data'
				)
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
			'add_remove_lead_list',
			'add_remove_lead_tag',
			'kill_lead_tasks',
		) );

		$triggers[self::$trigger] = array (
			'label' => __( 'On add/update lead' , 'inbound-pro' ),
			'description' => __( 'This trigger fires whenever a lead added into the Leads database.' , 'inbound-pro' ),
			'action_hook' => self::$trigger,
			'arguments' => $arguments,
			'db_lookup_filters' => $db_lookup_filters,
			'actions' => $actions
		);

		return $triggers;
	}

	public static function expand_argument_data( $args ) {
		$new_args = array();

		foreach ($args as $arg_key => $arg_value) {

			if (is_array($arg_value) || is_numeric($arg_key)) {
				$arg_value = json_encode($arg_value);
			}

			/* account for raw params */
			if ($arg_key == 'raw_params') {
				if (self::is_json($arg_value)) {
					$arg_value_array = json_decode($arg_value,true);
				} else {
					parse_str($arg_value , $arg_value_array);
				}

				$arg_value_array = (is_array($arg_value_array)) ? $arg_value_array : array();
				foreach ($arg_value_array as $raw_key => $raw_value) {
					if (is_array($raw_value)) {
						$raw_value = json_encode($raw_value);
					}
					$new_args[$raw_key]  = $raw_value;
				}
			}
			/* account for mapped params */
			else if ($arg_key == 'mapped_params') {
				parse_str($arg_value , $arg_value_array);
				$arg_value_array = (is_array($arg_value_array)) ? $arg_value_array : array();
				foreach ($arg_value_array as $mapped_key => $mapped_value) {
					if (is_array($mapped_value)) {
						$mapped_value = json_encode($mapped_value);
					}
					$new_args[$mapped_key]  = $mapped_value;
				}
			}

			$new_args[$arg_key] = $arg_value;
		}

		return $new_args;
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
			'form_id' => 10,
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
		$inbound_arguments = ( is_array($inbound_arguments)  ) ?  $inbound_arguments : array();
		$inbound_arguments[self::$trigger]['lead_data'] = $lead;
        Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
    }

	/**
	 * Check if string is a json string
	 * @param $string
	 * @return bool
	 */
	public static function  is_json($string) {

		$array = array('{','[');

		foreach ($array as $v) {
			if (substr($string, 0, 1) === $v) {
				return true;
			}
		}

		return false;
	}

}

/* Load Trigger */
new Inbound_Automation_Trigger_inbound_store_lead_post;
