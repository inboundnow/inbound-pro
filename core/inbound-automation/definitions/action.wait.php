<?php
/*
Action Name: Lead Data
Action Description: Data array passed trough inbound_now_store_lead_post hook. Matches data map.
Action Author: Inbound Now
Contributors: Hudson Atwell, David Wells
*/

if ( !class_exists( 'Inbound_Automation_Action_Wait' ) ) {

	class Inbound_Automation_Action_Wait {

		function __construct() {

			add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_filter' ) , 1 , 1);
		}

		/* Build Action Definitions */
		public static function define_filter( $actions ) {

			/* Build Action */
			$actions['wait'] = array (
				'class_name' => get_class(),
				'id' => 'wait',
				'label' => 'Wait',
				'description' => 'Wait an amount of time before firing the next event.',
				'settings' => array (
								array (
									'id' => 'wait_time_hours',
									'label' => 'Wait Time Hours:',
									'type' => 'text',
									'default' => '72',
									),
								array (
									'id' => 'wait_time_minutes',
									'label' => 'Wait Time Minutes:',
									'type' => 'text',
									'default' => '0',
									)
								)

			);

			return $actions;
		}
		
		/*
		* Sets the wait time 
		*/
		public static function run_action( $action , $arguments ) {
			
			/* get current time formatted */
			$current_time = current_time('Y-m-d H:i:s');
			
			/* turn current time into string */
			$currentDate = strtotime($current_time);
			
			/* Add minutes */
			$futureDate = $currentDate + ( 60 * $action['wait_time_minutes'] );
			
			/* Add hour */
			$futureDate = $futureDate + ( 60 * 60 * $action['wait_time_hours'] );
			
			$action['run_date'] = date("Y-m-d H:i:s", $futureDate);
			
			inbound_record_log(  
				__( 'Wait' , 'inbound-pro') , 
				__( 'Scheduling Next Action to Run at:' , 'inbound-pro' ) . $action['run_date'] . 
				'<h2>'.__( 'Action Settings' , 'inbound-pro' ) .'</h2> ' . print_r( $action , true ) , $action['rule_id'] , $action['job_id'] , 'action_event' 
			);
			
			return $action;
			
		}

	}

	/* Load Action */
	$Inbound_Automation_Action_Wait = new Inbound_Automation_Action_Wait();

}