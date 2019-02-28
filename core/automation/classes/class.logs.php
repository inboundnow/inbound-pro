<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class for managing automation rule event logs.
 * @package     Automation
 * @subpackage  Rules
 *
*/
class Inbound_Logging_Automation {
	
	static $log_limit;

	public function __construct() {
		self::$log_limit = apply_filters('inbound_automation_log_limit' , 100 );
	}

	/*
	* Create new log entry
	*/
	public function add( $title = '', $message = '', $rule_id, $job_id ,  $type) {
		
		$log_data = array(
			'log_title' 	=> $title,
			'log_content'	=> base64_encode($message),
			'rule_id'		=> $rule_id,
			'job_id'		=> $job_id,
			'log_type'		=> $type,
			'log_datetime'	=> date( __('Y-m-d H:i:s' , 'ma' ) , current_time( 'timestamp', 0 ) )
		);

		return self::insert_log( $log_data );
	}

	/*
	* Stores a log entry
	*/
	function insert_log( $log_data = array() ) {
	
		/* Get ALL Log From Rule ID */
		$logs_array = Inbound_Logging_Automation::get_logs( $log_data['rule_id'] );

		/* Get Type Logs from Rule ID */
		$log_type_array = Inbound_Logging_Automation::get_logs( $log_data['rule_id']  , $log_data['log_type'] );

		/* Push log to front of array */
		$logs_array[] = $log_data;
		$log_type_array[] = $log_data;

		/* Trim main logs array to X entries */
		if ( count($logs_array) > self::$log_limit ) {
			$trim = count($logs_array) - self::$log_limit;
			$logs_array = array_slice($logs_array, $trim);
		}

		if ( count($log_type_array) > self::$log_limit ) {
			$trim = count($log_type_array) - self::$log_limit;
			$log_type_array = array_slice($log_type_array, $trim);
		}

		/* Update logs meta */
		update_post_meta( $log_data['rule_id'] , '_automation_logs' ,  json_encode($logs_array) );
		update_post_meta( $log_data['rule_id'] , '_automation_logs_' . $log_data['log_type'] ,  json_encode($log_type_array) );

	}

	/*
	* Retrieves log meta and returns it as an array
	* @param rule_id INI id of rule in found in wp_posts table
	*
	* @returns ARRAY of logs related to post_id
	*/
	public function get_logs( $rule_id = 0 , $log_type='all' ) {
		
		/* Get Log From Rule ID */
		switch($log_type) {
			case 'all':
				$log_type_suffix = "";
				break;
			default:
				$log_type_suffix = "_".$log_type;
				break;
		}
		error_log($log_type_suffix);
		$logs_encoded = get_post_meta( $rule_id , '_automation_logs'.$log_type_suffix , true );

		if ( !$logs_encoded ) {
			$logs_array = array();
		} else {			
			$logs_array = json_decode( $logs_encoded , true );
		}
		
		/* check for corrupted unserialization */
		if ( !is_array($logs_array) ) {
			$logs_array = array();
		}
		
		return $logs_array;		
	}

	/*
	* Retrieves number of log entries connected to particular object ID
	*/
	public function get_log_count( $object_id = 0, $type = null, $meta_query = null, $date_query = null ) {
		
		
	}

}

/* Initiate the logging system */
$GLOBALS['inbound_automation_logs'] = new Inbound_Logging_Automation();

/*
 * Record a log entry
 * This is just a simple wrapper function for the log class add() function
*/
function inbound_record_log( $title = '', $message = '', $rule_id = 0, $job_id = '',  $type = null ) {
	global $inbound_automation_logs;
	$inbound_automation_logs->add( $title, $message, $rule_id, $job_id, $type );
}