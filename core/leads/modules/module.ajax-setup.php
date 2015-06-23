<?php

/*  Generate Lead Rule Processing Batch */
add_action('wp_ajax_automation_run_automation_on_all_leads', 'wpleads_lead_automation_build_queue');
add_action('wp_ajax_nopriv_automation_run_automation_on_all_leads', 'wpleads_lead_automation_build_queue');

function wpleads_lead_automation_build_queue() {
	global $wpdb;

	$automation_id = $_POST['automation_id'];
	$automation_queue = get_option( 'automation_queue');
	$automation_queue = json_decode( $automation_queue , true);

	if ( !is_array($automation_queue) ) {
		$automation_queue = array();
	}

	if ( !in_array( $automation_id , $automation_queue ) ) {
		/* get all lead ids */
		$sql = "SELECT distinct(ID) FROM {$wpdb->prefix}posts WHERE post_status='publish'  AND post_type = 'wp-lead' ";
		$result = mysql_query($sql);

		$batch = 1;
		$row = 0;

		while ($lead = mysql_fetch_array($result))
		{
			if ($row>1000)
			{
				$batch++;
				$row=0;
			}

			$automation_queue[$automation_id][$batch][] = $lead['ID'];

			$row++;
		}
	}

	$automation_queue = json_encode( $automation_queue);
	update_option( 'automation_queue' , $automation_queue);

	var_dump($automation_queue);
	die();
}

/* Grab all lead data and return to localstorage*/
add_action('wp_ajax_inbound_get_all_lead_data', 'inbound_get_all_lead_data');
add_action('wp_ajax_nopriv_inbound_get_all_lead_data', 'inbound_get_all_lead_data');
function inbound_get_all_lead_data() {
	$wp_lead_id = $_POST['wp_lead_id'];
	if (isset($wp_lead_id) && is_numeric($wp_lead_id)) {
		global $wpdb;
		$data   =   array();
		$wpdb->query($wpdb->prepare("
		  SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = %d", $wp_lead_id
		));

		foreach($wpdb->last_result as $k => $v) {
			$data[$v->meta_key] =   $v->meta_value;
		};

		echo json_encode($data,JSON_FORCE_OBJECT);
		wp_die();
	}
}

/* delete from list - lead management */

