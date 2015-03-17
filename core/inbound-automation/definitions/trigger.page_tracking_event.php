<?php
/*
Trigger Name: Lead Page View Event
Trigger Description: This trigger fires whenever a page_view is updated,
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Trigger_Update_Lead' ) ) {

	class Inbound_Automation_Trigger_Update_Lead {
		
		function __construct() {
			add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
		}
		
		/* Build Trigger Definitions */
		public static function define_trigger( $triggers ) {
			
			/* Set & Extend Trigger Argument Filters */
			$arguments = apply_filters('inbound_automation_trigger_arguments-track-lead' , array( 
					array( 
						'id' => 'lead_data',
						'label' => 'Lead Data'
					)
			) );
			
			/* Set & Extend Action DB Lookup Filters */	
			$db_lookup_filters = apply_filters( 'inbound_automation_db_lookup_filters-update-lead' , array (
				array( 
						'id' => 'lead_data',
						'label' => 'Lead Lookup',
						'class_name' => 'Inbound_Automation_Query_Lead'				
					)
			));
			
			/* Set & Extend Available Actions */
			$actions = apply_filters('inbound_automation_trigger_actions-update-lead' , array( 
				'send_email' , 'wait' , 'relay_data' , 'add_lead_to_list'
			) );
			
			$triggers['wplead_page_view'] = array (
				'label' => 'On Lead Tracking Event',
				'description' => 'This trigger fires whenever a tracked lead visits a new page.',
				'action_hook' => 'wplead_page_view',
				'scheduling' => false,
				'arguments' => $arguments,
				'db_lookup_filters' => $db_lookup_filters,
				'actions' => $actions
			);
			
			return $triggers;
		}
	}
	
	/* Load Trigger */
	$Inbound_Automation_Trigger_Update_Lead = new Inbound_Automation_Trigger_Update_Lead;

}