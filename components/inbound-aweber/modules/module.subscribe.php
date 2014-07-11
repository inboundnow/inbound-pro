<?php

/* FUNCTION TO SEND SUBSCRIBER TO MAILCHIMP */

function inboundnow_aweber_add_subscriber($target_list , $subscriber)
{
	/* prepare api access codes */
	$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
	$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
	$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
	$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );
	
	/* initialize aweber api wrapper */ 
	$aweber = new AWeberAPI($consumer_key , $consumer_secret);
	
	/* get account id */
	$account = $aweber->getAccount($accessKey, $accessSecret);
	$account_data = $account->data;
	$account_id = $account_data['id'];
	
	/* load subscriber object */
	$list = $account->loadFromUrl("/accounts/{$account_id}/lists/{$target_list}");
	$subscribers = $list->subscribers;
		
	/* prepare subscriber details for transfer */
	$params = array(
		'email' => $subscriber['wpleads_email_address'],
		'name' => $subscriber['wpleads_first_name']." ".$subscriber['wpleads_last_name']
	);	
	
	$params = apply_filters('inboundnow_aweber_subscriber_params',$params);
	
	/* add new subscriber to the api */
	$new_subscriber = $subscribers->create($params);

	//var_dump($new_subscriber);
}


/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */

//add_action('inbound_store_lead_post','inboundnow_aweber_landing_page_integratation');
function inboundnow_aweber_landing_page_integratation($data)
{			

	if (get_post_meta($data['lp_id'],'inboundnow-aweber-aweber_integration',false))
	{
		/* get target list */
		$target_list = get_post_meta($data['lp_id'],'inboundnow-aweber-aweber_list',true);

		inboundnow_aweber_add_subscriber( $target_list , $data );	
	}				
}



/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
add_action('inboundnow_form_submit_actions','inboundnow_aweber_inboundnow_form_integratation' , 10 , 2 );
function inboundnow_aweber_inboundnow_form_integratation($form_post_data , $form_meta_data )
{			
	
	if (isset($form_post_data['email'])) 
		$subscriber['wpleads_email_address'] = $form_post_data['email'];
	
	if (isset($form_post_data['wpleads_email_address'])) 
		$subscriber['wpleads_email_address'] = $form_post_data['wpleads_email_address'];
	
	if (isset($form_post_data['first-name'])) 
		$subscriber['wpleads_first_name'] = $form_post_data['first-name'];
	
	if (isset($form_post_data['wpleads_first_name'])) 
		$subscriber['wpleads_first_name'] = $form_post_data['wpleads_first_name'];
		
	if (isset($form_post_data['last-name'])) 
		$subscriber['wpleads_last_name'] = $form_post_data['last-name'];
	
	if (isset($form_post_data['wpleads_last_name'])) 
		$subscriber['wpleads_last_name'] = $form_post_data['wpleads_last_name'];
	
	$form_settings = $form_meta_data['inbound_form_values'][0];
	parse_str($form_settings, $form_settings);
	
	if ($form_settings['inbound_shortcode_aweber_enable']=='on')
	{		
		$target_list = $form_settings['inbound_shortcode_aweber_list_id'];
		inboundnow_aweber_add_subscriber($target_list , $subscriber);	
	}
}