<?php
/**
 * Lead Page View Event
 * @name: Lead Page View Event
 * @description: This trigger fires whenever a page_view is updated,
 * @author: Inbound Now
 * @contributors: Hudson Atwell
 * @package Automation
 * @subpackage Triggers
*/


if ( !class_exists( 'Inbound_Automation_Trigger_inbound_pro_event_page_view' ) ) {

	class Inbound_Automation_Trigger_inbound_pro_event_page_view {

        static $trigger;

		function __construct() {
            self::$trigger = 'inbound-pro/events/page_view';
			add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
            add_action( 'activate/automation' , array( __CLASS__ , 'create_dummy_event' ) );
		}

		/* Build Trigger Definitions */
		public static function define_trigger( $triggers ) {

			/* Set & Extend Trigger Argument Filters */
			$arguments = apply_filters('trigger/'.self::$trigger.'/args' , array(
					'event_data' => array(
						'id' => 'event_data',
						'label' => __('Event Object' , 'inbound-pro')
					)
			) );

			/* Set & Extend Action DB Lookup Filters */
			$db_lookup_filters = apply_filters(  'trigger/'.self::$trigger.'/db_filters' , array (
				array(
                    'id' => 'lead_data',
                    'label' => __( 'Validate Lead Data', 'inbound-pro' ),
                    'class_name' => 'Inbound_Automation_Query_Lead'
                )
			));

			/* Set & Extend Available Actions */
			$actions = apply_filters('trigger/'.self::$trigger.'/actions' , array(
				'send_email' , 'wait' , 'relay_data' , 'add_remove_lead_list', 'add_remove_lead_tag'
			) );

			$triggers[ self::$trigger ] = array (
				'label' => __('Tracked page view event' , 'inbound-pro'),
				'description' => __('This trigger fires whenever a tracked lead visits a new page. For high traffic sights turn defer processing to off' , ' inbound-pro' ),
				'action_hook' => self::$trigger ,
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


			$defaults = array(
				'page_id' => 123,
				'variation_id' => 0,
				'cta_id' => 0,
				'lead_id' => 23,
				'lead_uid' => 654321,
				'session_id' => 9909921,
				'datetime' => '2019/8/28',
				'source' => 'direct traffic',
				'ip' => '192.198.192.1'
			);

            $inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );
            $inbound_arguments = ( $inbound_arguments  ) ?  $inbound_arguments : array();
            $inbound_arguments[self::$trigger]['event_data'] = $defaults;

            Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
        }
	}

	/* Load Trigger */
	new Inbound_Automation_Trigger_inbound_pro_event_page_view;

}