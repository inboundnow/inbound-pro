<?php
/*
Action Name: Relay Data
Action Description: Relay trigger data to extenal URL using several different methods.
Action Author: Inbound Now
Contributors: Hudson Atwell
*/

if ( !class_exists( 'Inbound_Automation_Action_Add_Lead_To_List' ) ) {

	class Inbound_Automation_Action_Add_Lead_To_List {

		function __construct() {
			add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_action' ) , 1 , 1);
		}

		/* Build Action Definitions */
		public static function define_action( $actions ) {
			global $Inbound_Leads;
			
			if (!isset($Inbound_Leads)) {
				return;
			}
			
			/* Build Action */
			$actions['add_lead_to_list'] = array (
				'class_name' => get_class(),
				'id' => 'add_lead_to_list',
				'label' => __( 'Add Lead to List', 'marketing-automation') ,
				'description' => __('Adds the lead to a lead list.' , 'marketing-automation' ),
				'settings' => array (
					array (
						'id' => 'add_lead_to_list',
						'label' => __( 'Add Lead to These Lists' , 'marketing-automation' ) ,
						'type' => 'checkbox',
						'options' =>  $Inbound_Leads->get_lead_lists_as_array()
						) ,
					array (
						'id' => 'remove_lead_to_list',
						'label' => __( 'Remove leads from these lists' , 'marketing-automation' ),
						'type' => 'checkbox',
						'options' =>  $Inbound_Leads->get_lead_lists_as_array()
						)
				)
			);

			return $actions;
		}

		/*
		* Sends the Data 
		*/
		public static function run_action( $action , $arguments ) {
			global $Inbound_Leads;
			$added = array();
			$removed = array();
			
			$final_args = array();
			
			/* Break multiple arrays into single array */
			foreach ($arguments as $key => $argument) {
				if (is_array($argument) ) {
					foreach ($argument as $k => $value ) {
						$final_args[ $k ] = $value;
					}
				} else {
					$final_args[ $key ] = $argument;
				}
			}
			
			/* Listen for List Adds */
			if ( isset($action['add_lead_to_list']) && is_array($action['add_lead_to_list']) ) {
				foreach ( $action['add_lead_to_list'] as $list_id ) {
					$Inbound_Leads->add_lead_to_list( $final_args['lead_id'] , intval($list_id) );
					$added[] = $list_id;
				}
			}
			
			/* Listen for List Removes */
			if ( isset($action['remove_lead_from_list']) && is_array($action['remove_lead_from_list']) ) {
				foreach ( $action['remove_lead_from_list'] as $list_id ) {
					$Inbound_Leads->remove_lead_from_list( $final_args['lead_id'] , intval($list_id) );
					$removed[] = $list_id;
				}
			}
			
			$action_encoded = json_encode($action) ;
			inbound_record_log(  __( 'Action Event - Adding/removing lead to list(s)' , 'marketing-automation' ) , '<h2>Added</h2> Lead '.$final_args['lead_id'].' added to these lists:<pre>'. print_r( $added , true) .'</pre> <h2>Removed</h2> Lead '.$final_args['lead_id'].' removed from these lists <pre>'. print_r($removed , true) .'</pre> <h2>Settings</h2><pre>'. $action_encoded .'</pre> <h2>Arguments</h2><pre>' . json_encode($arguments) . '</pre>', $action['rule_id'] , 'action_event' );
			
		}

	}

	/* Load Action */
	add_action( 'init' , function() {
		$Inbound_Automation_Action_Add_Lead_To_List = new Inbound_Automation_Action_Add_Lead_To_List();
	} , 9 );

}
