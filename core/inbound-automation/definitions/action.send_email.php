<?php
/*
Action Name: Send Email
Action Description: Send an email.
Action Author: Inbound Now
Contributors: Hudson Atwell
*/

if ( !class_exists( 'Inbound_Automation_Action_Send_Email' ) ) {

	class Inbound_Automation_Action_Send_Email {

		function __construct() {
			
			add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_action' ) , 1 , 1);
		}

		/* Build Action Definitions */
		public static function define_action( $actions ) {

			
			/* Get Lead Lists */
			$lead_lists = Inbound_Leads::get_lead_lists_as_array();
			
			/* Get Available Email Templates */
			if ( class_exists('Inbound_Mailer_Post_Type') ) {
				$emails = Inbound_Mailer_Post_Type::get_automation_emails_as( 'ARRAY' );
			} else {
				$emails = array('email component not installed.');
			}
			
			/* Build Action */
			$actions['send_email'] = array (
				'class_name' => get_class(),
				'id' => 'send_email',
				'label' => 'Send Email',
				'description' => 'Send an email using available filter data.',
				'settings' => array (
					array (
						'id' => 'send_to',
						'label' => __( 'Send Email To' , 'inbound-pro' ),
						'type' => 'dropdown',
						'options' => array(
							'lead' => __( 'Lead' , 'inbound-pro' ),
							'custom' => __( 'Custom Email Address' , 'inbound-pro' ),
							'lead_list' => __( 'Lead List' , 'inbound-pro' ),
						)
					),
					array (
						'id' => 'custom_email',
						'label' => __( 'Enter Email Address' , 'inbound-pro' ),
						'type' => 'text',
						'hidden' => true,
						'reveal' => array(
							'selector' => 'send_to',
							'value' => 'custom'
						)
					),
					array (
						'id' => 'lead_lists',
						'label' => __( 'Select Lead List' , 'inbound-pro' ),
						'type' => 'select2',
						'hidden' => true,
						'reveal' => array(
							'selector' => 'send_to',
							'value' => 'lead_list'
						), 
						'options' => $lead_lists
					),
					array (
						'id' => 'email_id',
						'label' => __( 'Select Email' , 'inbound-pro' ),
						'type' => 'dropdown',
						'options' => $emails
					)
				)
			);

			return $actions;
		}

		
		/**
		* Runs the send email processing action
		* @param ARRAY $action saved action settings
		* @param ARRAY $trigger_data action filters
		* @param ARRAY $rule_id rule id
		*/
		public static function run_action( $action , $trigger_data ) {
			
			//error_log( print_r( $action , true ) );
			//error_log( print_r( $trigger_data , true ) ); 
			$Inbound_Templating_Engine = Inbound_Templating_Engine();
			
			
			switch ($action['send_to']) {
				
				case 'lead':					
					/* Load sender class */
					$Inbound_Mail_Daemon = new Inbound_Mail_Daemon();	
					
					/* get lead id */
					$params = array(	
						'email' => $trigger_data[0]['email'],
						'return' => 'ID',
						'results_per_page' => -1,
						'fields' => 'ids'
					);
					
					/* get variant marker */
					$vid = Inbound_Mailer_Variations::get_next_variant_marker( $action['email_id'] );				
				
					/* check for lead lists */
					$lead_lists = (isset($trigget_data[0]['lead_lists'])) ? $trigget_data[0]['lead_lists'] : array();
					
					/* send email */
					$response = $Inbound_Mail_Daemon->send_solo_email( array(
						'email_address' => $trigger_data[0]['email'],
						'lead_id' => $trigger_data[0]['id'],
						'email_id' => $action['email_id'],
						'tags' => array( 'automated' ),
						'vid' => $vid,
						'lead_lists' => $lead_lists
					)); 
					
					BREAK;
				case 'custom':
					/* Load sender class */
					$Inbound_Mail_Daemon = new Inbound_Mail_Daemon();	
					
					/* get variant marker */
					$vid = Inbound_Mailer_Variations::get_next_variant_marker( $action['email_id'] );
					
					/* send email */
					$response = $Inbound_Mail_Daemon->send_solo_email( array(
						'email_address' => $action['custom_email'],
						'email_id' => $action['email_id'],
						'vid' => $vid,
						'tags' => array('automated')
					));
					BREAK;
				case 'lead_list':
					$Inbound_Mailer_Scheduling = new Inbound_Mailer_Scheduling;
					$Inbound_Mailer_Scheduling->recipients = $action['lead_lists'];
					$response = $Inbound_Mailer_Scheduling->schedule_email( $action['email_id'] );
					$response = __( sprintf( '%s emails have been scheduled' , $response ) , 'inbound-pro' );
					BREAK;
				
			
			}

			inbound_record_log(  
				__( 'Send Email' , 'inbound-pro') , 
				'<h2>'.__('Mandrill Response', 'inbound-pro') .'</h2><pre>'.print_r($response,true).'</pre>' .
				'<h2>'.__('Action Settings' , 'inbound-pro') .'</h2><pre>'. print_r($action,true).print_r($filter,true) .'</pre>',
				$action['rule_id'] ,
				$action['job_id'] ,
				'action_event' 
			);
			
		}

	}

	/* Load Action */
	$Inbound_Automation_Action_Send_Email = new Inbound_Automation_Action_Send_Email();

}
