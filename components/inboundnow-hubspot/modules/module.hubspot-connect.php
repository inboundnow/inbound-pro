<?php

function inboundnow_hubspot_connect( $nature = null ) {
	require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.exception.php';

	$hubspot_api_key = get_option('inboundnow_hubspot_api_key' , '' );
	$hubspot_portal_id =  get_option('inboundnow_hubspot_portal_id' , '' );

	if ( !$hubspot_api_key || !$hubspot_portal_id )
		return null;

	switch($nature) {

		case "lists":

			require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.lists.php';

			return new HubSpot_Lists($hubspot_api_key ,  $hubspot_portal_id );

			break;
		case "contacts":
			require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.contacts.php';

			return new HubSpot_Contacts($hubspot_api_key , $hubspot_portal_id);

			break;
	}

	return null;
}

function inboundnow_hubspot_get_hubspot_lists() {

	$hubspot_lists = get_transient('inboundnow_hubspot_lists');

	if ($hubspot_lists)
		return $hubspot_lists;

	$lists = inboundnow_hubspot_connect('lists');

	if (!$lists)
		return null;

	/* Get Static Lists */
	$static_lists = $lists->get_static_lists(null);

	foreach($static_lists->lists as $key => $hubspot_list) {
		//var_dump($hubspot_list);
		$options[$hubspot_list->internalListId] = $hubspot_list->name;
	}

	if (!isset($options))
		$options['0'] = "No lists discovered.";

	set_transient( 'inboundnow_hubspot_lists', $options, 60*5 );

	return $options;
}

function inboundnow_hubspot_add_subscriber( $lead_data , $target_list ) {
	$contacts = inboundnow_hubspot_connect('contacts');
	$lists = inboundnow_hubspot_connect('lists');

	/*check if contact exists */
	$contact = $contacts->get_contact_by_email($lead_data['email']);
	if (isset($contact->vid)) {
		$contact_id = $contact->vid;
	} else {
		/* create contact if does not exist*/
		$lead_data = array('email'=> $lead_data['wpleads_email_address'],
						'firstname'=> $lead_data['wpleads_first_name'],
						'lastname'=> $lead_data['wpleads_last_name']
						);

		$lead_data = apply_filters('inboundnow_hubspot_lead_data',$lead_data);

		$createdContact = $contacts->create_contact($lead_data);

		$contact_id = $createdContact->{'vid'};
	}

	/* add contact to list */
	$contacts_to_add = array($contact_id);
	$added_contacts = $lists->add_contacts_to_list( $contacts_to_add , $target_list  );

}