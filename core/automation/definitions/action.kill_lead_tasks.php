<?php
/**
 * Send Email
 * @name Send Email
 * @description Send an email.
 * @author Inbound Now
 * @contributors Hudson Atwell
 * @package     Automation
 * @subpackage  Actions
*/

if ( !class_exists( 'Inbound_Automation_Action_Kill_Tasks' ) ) {

	class Inbound_Automation_Action_Kill_Tasks {

		function __construct() {

			add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_action' ) , 1 , 1);
		}

		/* Build Action Definitions */
		public static function define_action( $actions ) {

			/* Get All Rules as an array  */
			$rules_array = Inbound_Automation_Post_Type::get_rules_as_array();

			/* Build Action */
			$actions['kill_lead_tasks'] = array (
					'class_name' => get_class(),
					'id' => 'kill_lead_tasks',
					'label' => __('Terminate Email Series for Lead' , 'inbound-pro'),
					'description' => __('If this action fires we will look for all email series tasks targeting a lead email and cancel them.', 'inbound-pro'),
					'settings' => array (
							array (
									'id' => 'cancel_criteria',
									'label' => __( 'Cancel Options' , 'inbound-pro' ),
									'type' => 'dropdown',
									'description' => __( 'Select whether to terminate automation jobs related to this lead for all current running jobs or pertaining to a specific rule id' , 'inbound-pro' ),
									'options' => array(
											'all_tasks' => __( 'All jobs targeting this lead.' , 'inbound-pro' ),
											'specific_rule' => __( 'Jobs targeting this lead belonging to a specific rule' , 'inbound-pro' )
									)
							),
							array (
									'id' => 'target_rule_id',
									'label' => __( 'Rule:' , 'inbound-pro' ),
									'type' => 'dropdown',
									'hidden' => true,
									'description' => __( 'Select the rule we want to terminate jobs related to this lead.' , 'inbound-pro' ),
									'reveal' => array(
											'selector' => 'cancel_criteria',
											'value' => 'specific_rule'
									),
									'options' => $rules_array
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
			global $wpdb;

			$table_name = $wpdb->prefix . "inbound_automation_queue";
			$trigger_data = apply_filters( 'action/kill_lead_taks/trigger_data' , $trigger_data );
			$response = array();

			switch ($action['cancel_criteria']) {

				case 'all_tasks':
					$wpdb->query("DELETE FROM $table_name WHERE `trigger_data` LIKE '%{$trigger_data['lead_data']['email']}%' AND `tasks` LIKE '%send_email%'");
					BREAK;
				case 'specific_rule':
					$wpdb->query("DELETE FROM $table_name WHERE `trigger_data` LIKE '%{$trigger_data['lead_data']['email']}%' AND `tasks` LIKE '%send_email%' AND `rule_id` = '{$action['target_rule_id']}'");
					error_log("DELETE FROM $table_name WHERE `trigger_data` LIKE '%{$trigger_data['lead_data']['email']}%' AND `tasks` LIKE '%send_email%' AND `rule_id` = '{$action['target_rule_id']}'");
					BREAK;



			}

			inbound_record_log(
					__( 'Terminate Email Series' , 'inbound-pro') ,
					'<h2>'.__('Total Deleted', 'inbound-pro') .'</h2><pre>'.$wpdb->insert_id.'</pre>' .
					'<h2>'.__('Action Settings' , 'inbound-pro') .'</h2><pre>'. print_r($action,true).'</pre><h2>'.__('Action Settings' , 'inbound-pro') .'</h2><pre>'.print_r($trigger_data,true) .'</pre>',
					$action['rule_id'] ,
					$action['job_id'] ,
					'action_event'
			);
		}

	}

	/* Load Action */
	new Inbound_Automation_Action_Kill_Tasks();

}
