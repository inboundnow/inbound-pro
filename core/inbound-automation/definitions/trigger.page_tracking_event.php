<?php
/*
Trigger Name: Lead Page View Event
Trigger Description: This trigger fires whenever a page_view is updated,
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Trigger_Update_Lead' ) ) {

	class Inbound_Automation_Trigger_Update_Lead {

        static $trigger;

		function __construct() {
            self::$trigger = 'wplead_page_view';
			add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
            add_action( 'activate/inbound-automation' , array( __CLASS__ , 'create_dummy_event' ) );
		}

		/* Build Trigger Definitions */
		public static function define_trigger( $triggers ) {

			/* Set & Extend Trigger Argument Filters */
			$arguments = apply_filters('inbound_automation_trigger_arguments-track-lead' , array(
					'lead_data' => array(
						'id' => 'lead_data',
						'label' => 'Lead Data'
					)
			) );

			/* Set & Extend Action DB Lookup Filters */
			$db_lookup_filters = apply_filters( 'inbound_automation_db_lookup_filters-update-lead' , array (
				array(
                    'id' => 'lead_data',
                    'label' => __( 'Validate Lead Data', 'inbound-pro' ),
                    'class_name' => 'Inbound_Automation_Query_Lead'
                )
			));

			/* Set & Extend Available Actions */
			$actions = apply_filters('inbound_automation_trigger_actions-update-lead' , array(
				'send_email' , 'wait' , 'relay_data' , 'add_remove_lead_list', 'add_remove_lead_tag'
			) );

			$triggers[ self::$trigger ] = array (
				'label' => 'On Lead Tracking Event',
				'description' => 'This trigger fires whenever a tracked lead visits a new page.',
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


            $lead = array (
                'lead_id' => 97351,
                'nature' => 'non-conversion',
                'json' => 0,
                'wp_lead_uid' => 'mxb8EHq5H71LtVmOTyXINufHKwA3EaUxTpH',
                'page_id' => 97188,
                'current_url' => 'http://inboundsoon.dev/go/flat-ui/'
            );

            $inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );
            $inbound_arguments = ( $inbound_arguments  ) ?  $inbound_arguments : array();
            $inbound_arguments[self::$trigger]['lead_data'] = $lead;

            Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
        }
	}

	/* Load Trigger */
	new Inbound_Automation_Trigger_Update_Lead;

}