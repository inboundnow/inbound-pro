<?php
/**
 * Double Optin Confirmation
 * @name Doouble Optin Confirmation
 * @description: Fires when a lead confirms his lead list subscription via email
 * @uthor: Inbound Now
 * @contributors: Hudson Atwell
 * @package Automation
 * @subpackage Triggers
 *
*/

class Inbound_Automation_Trigger_inbound_double_optin_confirm {

    static $trigger;

	/**
	*  Initialize Class
	*/
	function __construct() {
	    self::$trigger = 'inbound_double_optin_confirm';
		add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
		add_action( 'activate/automation' , array( __CLASS__ , 'create_dummy_event' ) );

	}

	/**
	*  Define Trigger
	*/
	public static function define_trigger( $triggers ) {

		/* Set & Extend Trigger Argument Filters */
		$arguments = apply_filters('trigger/'.self::$trigger.'/args' , array(
			'lead_data' => array(
				'id' => 'lead_data',
				'label' => __( 'Lead Data' , 'inbound-pro' ),
				'callback' => array(
					get_class() , 'enrich_lead_data'
				)
			)
		) );

		/* Set & Extend Action DB Lookup Filters */
		$db_lookup_filters = apply_filters( 'trigger/'.self::$trigger.'/db_filters' , array (
			array(
                'id' => 'lead_data',
                'label' => __( 'Query Lead Data' , 'inbound-pro' ),
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
			'label' => __( 'On double-optin confirmation' , 'inbound-pro' ),
			'description' => __( 'This trigger fires whenever a lead confirms their email address using our double optin feature' , 'inbound-pro' ),
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
	public static function enrich_lead_data( $args ) {

		$new_args = array();

		$args['wp_lead_status'] = isset($args['wp_lead_status']) ? $args['wp_lead_status'] : '';
		$args['wpleads_raw_post_data'] = '';

		foreach ($args as $arg_key => $arg_value) {

			/* encode arrays */
			if (is_array($arg_value) || is_numeric($arg_key)) {
				$arg_value = json_encode($arg_value);
			}

			$new_args[$arg_key] = $arg_value;
		}

		//error_log(print_r($new_args,true));
		return $new_args;
	}

    /**
     * Simulate trigger - perform on plugin activation
     */
    public static function create_dummy_event() {

       $lead = array (
            'id' => 2,
            'wordpress_date_time' => '2015-06-01 18:03:31 UTC',
            'wpleads_email_address' => 'test@inboundnow.com',
            'wpleads_first_name' => 'Example',
            'wpleads_last_name' => 'Lead',
            'wpleads_inbound_form_lists' => '208,210',
            'wpleads_ip_address' => '127.0.0.1'
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
new Inbound_Automation_Trigger_inbound_double_optin_confirm;
