<?php

function inboundnow_getresponse_connect()
{
	require_once INBOUNDNOW_GETRESPONSE_PATH.'includes/getresponse/GetResponseAPI.class.php';

	$getresponse_api_key = get_option('inboundnow_getresponse_api_key' , '' );
	
	if ( !$getresponse_api_key )
		return null;
	
	return new GetResponse($getresponse_api_key);
		
}

function inboundnow_getresponse_get_lists()
{

	$getresponse_lists = get_transient('inboundnow_getresponse_lists');

	if ($getresponse_lists)
		return $getresponse_lists;		

	$gr = inboundnow_getresponse_connect();
	
	if (!is_object($gr))
		return;
		
	$campaigns 	 = $gr->getCampaigns();;

	if (!$campaigns)
		return;
	
	
	foreach($campaigns as $id=>$campaign) {
		$options[$id] = $campaign->name;
	}
	
	if (!isset($options))
		$options['0'] = "No lists discovered.";
	
	set_transient( 'inboundnow_getresponse_lists', $options, 60*5 );
	
	return $options;
}

function inboundnow_getresponse_add_subscriber( $lead_data , $target_list )
{	

	$gr = inboundnow_getresponse_connect();
	
	if (!is_object($gr)) {
		return;
	}

	$name = $lead_data['first_name'].' '.$lead_data['last_name'];		

	$response = $gr->addContact($target_list, $name, $lead_data['email']);

}