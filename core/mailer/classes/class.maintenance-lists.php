<?php

/**
 * Class Inbound_Maintenance_Lists provides methods for managing component maintenance lead lists
 * @package Mailer
 * @subpackage SparkPost
*/


class Inbound_Maintenance_Lists {

	/**
	 * Get maintenance lead lists from memory
	 */
	public static function get_lists() {
		global $inbound_memory;

		/* if global is already set then use it */
		if ($inbound_memory) {
			return $inbound_memory;
		}

		$inbound_memory = Inbound_Options_API::get_option('inbound-pro', 'memory', array());
		$update = false;

		/* setup default array using memory */
		$inbound_memory['maintenance_lists'] = (isset($inbound_memory['maintenance_lists'])) ? $inbound_memory['maintenance_lists'] : array();
		$inbound_memory['maintenance_lists']['parent'] = (isset($inbound_memory['maintenance_lists']['parent'])) ? $inbound_memory['maintenance_lists']['parent'] : '';
		$inbound_memory['maintenance_lists']['rejected'] = (isset($inbound_memory['maintenance_lists']['rejected'])) ? $inbound_memory['maintenance_lists']['rejected'] : '';
		$inbound_memory['maintenance_lists']['spam'] = (isset($inbound_memory['maintenance_lists']['spam'])) ? $inbound_memory['maintenance_lists']['spam'] : '';
		$inbound_memory['maintenance_lists']['bounces'] = (isset($inbound_memory['maintenance_lists']['bounces'])) ? $inbound_memory['maintenance_lists']['bounces'] : '';

		/* create/get maintenance list */
		if (!$inbound_memory['maintenance_lists']['parent']) {
			$inbound_memory['maintenance_lists']['parent'] = Inbound_Leads::create_lead_list( array(
				'name' => __( 'Maintenance' , 'inbound-pro' )
			));
			$update = true;
		}

		/* create/get rejected maintenance list */
		if (!$inbound_memory['maintenance_lists']['rejected']) {
			$inbound_memory['maintenance_lists']['rejected'] = Inbound_Leads::create_lead_list( array(
				'name' => __( 'Rejected' , 'inbound-pro' ),
				'parent' => $inbound_memory['maintenance_lists']['parent']
			));
			$update = true;
		}

		/* create/get 'spam complaints' maintenance list */
		if (!$inbound_memory['maintenance_lists']['spam']) {
			$inbound_memory['maintenance_lists']['spam'] = Inbound_Leads::create_lead_list( array(
				'name' => __( 'Spam Complaints' , 'inbound-pro' ),
				'parent' => $inbound_memory['maintenance_lists']['parent']
			));
			$update = true;
		}

		/* create/get 'spam complaints' maintenance list */
		if (!$inbound_memory['maintenance_lists']['bounces']) {
			$inbound_memory['maintenance_lists']['bounces'] = Inbound_Leads::create_lead_list( array(
				'name' => __( 'Bounces' , 'inbound-pro' ),
				'parent' => $inbound_memory['maintenance_lists']['parent']
			));
			$update = true;
		}

		/* update memory object if changed */
		if ($update) {
			Inbound_Options_API::update_option('inbound-pro', 'memory', $inbound_memory);
		}

		return $inbound_memory['maintenance_lists'];
	}

}