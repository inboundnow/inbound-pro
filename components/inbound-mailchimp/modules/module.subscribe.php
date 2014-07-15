<?php

/**
*   FUNCTION TO SEND SUBSCRIBER TO MAILCHIMP 
*/

function inboundnow_mailchimp_add_subscriber($target_list , $subscriber)
{
	$api_key = get_option( 'inboundnow_mailchimp_api_key' , 0 );				
	
	if (!$api_key) {
		return;
	}
	
	$MailChimp = new MailChimp($api_key);

	/* get double optin setting */
	$mailchimp_double_optin =  get_option('inboundnow_mailchimp_double_optin' , 'true' );
	
	/* get ready for groupings */
	if (isset($subscriber['groupings']))
	{
	}
	
	$args = array(
		'id'                => $target_list,
		'email'             => array('email'=>$subscriber['wpleads_email_address']),
		'merge_vars'        => array('FNAME'=>$subscriber['wpleads_first_name'], 'LNAME'=>$subscriber['wpleads_last_name']),
		'double_optin'      => $mailchimp_double_optin,
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

	$subscriber['wpleads_email_address'] = (isset($form_post_data['wpleads_email_address'])) ? $form_post_data['wpleads_email_address'] : '';

	$subscriber['wpleads_first_name'] = (isset($form_post_data['wpleads_first_name'])) ? $form_post_data['wpleads_first_name'] : '';
	
	$subscriber['wpleads_last_name'] = (isset($form_post_data['wpleads_last_name'])) ? $form_post_data['wpleads_last_name'] : "";

	if (!$subscriber['wpleads_last_name']) {
		$first_name_array = explode(' ' , $subscriber['wpleads_first_name'] );
		if ( count( $first_name_array ) > 1 ) {
			$subscriber['wpleads_first_name'] = $first_name_array[0];
			$subscriber['wpleads_last_name'] = $first_name_array[1];
		}
	}
	
	$form_settings = $form_meta_data['inbound_form_values'][0];
	parse_str($form_settings, $form_settings);
	
	if ($form_settings['inbound_shortcode_mailchimp_enable']=='on')
	{		
		$target_list = $form_settings['inbound_shortcode_mailchimp_list_id'];
		inboundnow_mailchimp_add_subscriber($target_list , $subscriber);	
	}
}