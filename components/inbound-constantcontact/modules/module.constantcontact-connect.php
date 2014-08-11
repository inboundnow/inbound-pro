<?php

function inboundnow_constantcontact_connect( $nature = null )
{
	//require_once INBOUNDNOW_CONSTANTCONTACT_PATH.'includes/constantcontact_api/class.exception.php';

	$constantcontact_api_key = get_option('inboundnow_constantcontact_api_key' , '' );
	$constantcontact_access_key =  get_option('inboundnow_constantcontact_access_key' , '' );
	
	if ( !$constantcontact_api_key )
		return null;
		
	return new ConstantContact($constantcontact_api_key);
		
}

function inboundnow_constantcontact_get_lists()
{

	$constantcontact_lists = get_transient('inboundnow_constantcontact_lists');

	if ($constantcontact_lists)
		return $constantcontact_lists;		

	$constantcontact_api_key = get_option('inboundnow_constantcontact_api_key' , '' );
	$constantcontact_access_key =  get_option('inboundnow_constantcontact_access_key' , '' );
	
	$args = array( 
		'redirection' => 0,
		'sslverify'   => false,
		'headers' => array( 'Authorization'=>"Bearer $constantcontact_access_key"),
	); 
	
	$response = wp_remote_get( 'https://api.constantcontact.com/v2/lists?api_key='.$constantcontact_api_key  , $args );
	$lists_data = json_decode($response['body'], true);	

	foreach($lists_data as $id => $list) {
		$lists[$list['id']] = $list['name'];
	}
	
	if (!isset($lists))
		$lists['0'] = "No lists discovered.";
	
	set_transient( 'inboundnow_constantcontact_lists', $lists, 60*5 );
	
	return $lists;
}

function inboundnow_constantcontact_add_subscriber( $lead_data , $target_list )
{	

	$constantcontact_api_key = get_option('inboundnow_constantcontact_api_key' , '' );
	$constantcontact_access_key =  get_option('inboundnow_constantcontact_access_key' , '' );
	$constantcontact_double_optin =  get_option('inboundnow_constantcontact_double_optin' , 'ACTION_BY_VISITOR' );
	
	
	
	if (!$lead_data['wpleads_last_name'] && $lead_data['wpleads_first_name'])
	{
		$parts = explode(' ' , $lead_data['wpleads_first_name']);
		if ($parts>1)
		{
			$lead_data['wpleads_first_name'] = $parts[0];
			$lead_data['wpleads_last_name'] = $parts[1];
		}
	}
	
	$contact = array (
		'status' =>'ACTIVE',
		'lists' => array( array( "id"=>$target_list ) ),					
		'email_addresses' => array( array( 'status' => 'ACTIVE' ,  'email_address' => $lead_data['wpleads_email_address'] ) ),
		'first_name' => $lead_data['wpleads_first_name'],
		'last_name' => $lead_data['wpleads_last_name'],
	);
	

	$contact = apply_filters('inboundnow_constantcontact_lead_data',$contact);
	
	$contact_encoded = json_encode($contact);
	
	/* check if subscriber is in constant contact db */
	$args = array( 
		'redirection' => 0,
		'sslverify'   => false,
		'headers' => array( 'content-type'=>'application/json' ,'Authorization' => "Bearer $constantcontact_access_key" ),
	); 
	
	$response = wp_remote_get( 'https://api.constantcontact.com/v2/contacts?email='.$lead_data['wpleads_email_address'].'&limit=1&api_key='.$constantcontact_api_key , $args );
	$cc_contact_data = json_decode($response['body'], true);	
	
	/* if contact does not exist create it else update it to new list  */
	if (!isset($cc_contact_data['results'][0]['id']))
	{	
		/* add contact to list */	
		$args = array( 
			'redirection' => 0,
			'sslverify'   => false,
			'headers' => array( 'content-type'=>'application/json' ,'Authorization' => "Bearer $constantcontact_access_key" ),
			'body' => $contact_encoded
		); 
		
		$response = wp_remote_post( 'https://api.constantcontact.com/v2/contacts?action_by='.$constantcontact_double_optin.'&api_key='.$constantcontact_api_key  , $args );		
		$added_contact = json_decode($response['body'], true);
	}
	else
	{
		$chlead = curl_init();
		curl_setopt($chlead, CURLOPT_URL, 'https://api.constantcontact.com/v2/contacts/'.$cc_contact_data['results'][0]['id'].'?action_by='.$constantcontact_double_optin.'&api_key='.$constantcontact_api_key );
		curl_setopt($chlead, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$constantcontact_access_key ));
		curl_setopt($chlead, CURLOPT_VERBOSE, 1);
		curl_setopt($chlead, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chlead, CURLOPT_CUSTOMREQUEST, "PUT"); 
		curl_setopt($chlead, CURLOPT_POSTFIELDS,$contact_encoded);
		curl_setopt($chlead, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($chlead);
		$chleadapierr = curl_errno($chlead);
		$chleaderrmsg = curl_error($chlead);
		curl_close($chlead);
		
		$updated_contact = json_decode($response['body'], true);

	}

}