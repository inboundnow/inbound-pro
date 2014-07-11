<?php

/* FUNCTION TO SEND SUBSCRIBER TO MAILCHIMP */

function inboundnow_mailchimp_add_subscriber($target_list , $subscriber)
{
	$api_key = get_option( 'inboundnow_mailchimp_api_key' , 0 );				
	
	
	if (!$api_key)
		return;
		
	$MailChimp = new MailChimp($api_key);

	/* get ready for groupings */
	if (isset($subscriber['groupings']))
	{
	}
	
	$args = array(
		'id'                => $target_list,
		'email'             => array('email'=>$subscriber['wpleads_email_address']),
		'merge_vars'        => array('FNAME'=>$subscriber['wpleads_first_name'], 'LNAME'=>$subscriber['wpleads_last_name']),
		'double_optin'      => false,
		'update_existing'   => true,
		'replace_interests' => false,
		'send_welcome'      => false,
	);
	
	$args = apply_filters('inboundnow_mailchimp_args' , $args);
	
	$debug = 0;
	if ($debug==1)
	{
		var_dump($args);
	}
	
	$result = $MailChimp->call('lists/subscribe', $args );	
}



/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */

//add_action('inbound_store_lead_post','inboundnow_mailchimp_landing_page_integratation');
function inboundnow_mailchimp_landing_page_integratation($data)
{		
	if (get_post_meta($data['lp_id'],'inboundnow-mailchimp-mailchimp_integration',true))
	{
		$target_list = get_post_meta($data['lp_id'],'inboundnow-mailchimp-mailchimp_list',true);
		
		inboundnow_mailchimp_add_subscriber( $target_list , $data );		
	}				
}



/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
add_action('inboundnow_form_submit_actions','inboundnow_mailchimp_inboundnow_form_integratation' , 10 , 2 );
function inboundnow_mailchimp_inboundnow_form_integratation($form_post_data , $form_meta_data )
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
	
	if ($form_settings['inbound_shortcode_mailchimp_enable']=='on')
	{		
		$target_list = $form_settings['inbound_shortcode_mailchimp_list_id'];
		inboundnow_mailchimp_add_subscriber($target_list , $subscriber);	
	}
}