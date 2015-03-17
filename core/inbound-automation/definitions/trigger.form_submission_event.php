<?php
/**
Trigger Name: Form Submission Event
Trigger Description: This trigger fires whenever a tracked Form is submitted.
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/



class Inbound_Automation_Trigger_Form_Submission {
	
	/**
	*  Initialize Class
	*/
	function __construct() {
		add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
	}
	
	/**
	*  Define Trigger
	*/
	public static function define_trigger( $triggers ) {
		
		/* Set & Extend Trigger Argument Filters */
		$arguments = apply_filters('inbound_automation_trigger_arguments-save-lead' , array( 
				array( 
					'id' => 'lead_data',
					'label' => __( 'Lead Data' , 'inbound-pro' )
				)
		) );
		
		/* Set & Extend Action DB Lookup Filters */	
		$db_lookup_filters = apply_filters( 'inbound_automation_db_lookup_filters-form-submission' , array (
			array( 
					'id' => 'lead_data',
					'label' => __( 'Lead Lookup' , 'inbound-pro' ),
					'class_name' => 'Inbound_Automation_Query_Lead'				
				)
		));
		
		/* Set & Extend Available Actions */
		$actions = apply_filters('inbound_automation_trigger_actions-form-submission' , array( 
			'send_email' , 'wait' , 'relay_data' , 'add_lead_to_list'
		) );
		
		$triggers['inbound_store_lead_post'] = array (
			'label' => __( 'On Inbound Form Submission' , 'inbound-pro' ),
			'description' => __( 'This trigger fires whenever a lead is submitted through an Inbound Form.' , 'inbound-pro' ),
			'action_hook' => 'inbound_store_lead_post',
			'scheduling' => false,
			'arguments' => $arguments,
			'db_lookup_filters' => $db_lookup_filters,
			'actions' => $actions
		);
		
		return $triggers;
	}
}

/* Load Trigger */
$Inbound_Automation_Trigger_Form_Submission = new Inbound_Automation_Trigger_Form_Submission;
