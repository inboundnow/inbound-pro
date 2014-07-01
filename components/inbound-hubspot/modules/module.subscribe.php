<?php

/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
add_action('inbound_store_lead_post','inboundnow_hubspot_landing_page_integratation');
function inboundnow_hubspot_landing_page_integratation($lead_data)
{			

	if (get_post_meta($data['lp_id'],'inboundnow-hubspot-hubspot_integration',false))
	{
		/* get target list */
		$target_list = get_post_meta($data['lp_id'],'inboundnow-hubspot-hubspot_list',true);

		inboundnow_hubspot_add_subscriber( $data , $target_list );	
	}				
}



/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
add_action('inboundnow_form_submit_actions','inboundnow_hubspot_inboundnow_form_integratation' , 10 , 2 );
function inboundnow_hubspot_inboundnow_form_integratation($form_post_data , $form_meta_data )
{		
	if (isset($form_post_data['email'])) 
		$subscriber['email'] = $form_post_data['email'];
	
	if (isset($form_post_data['wpleads_email_address'])) 
		$subscriber['email'] = $form_post_data['wpleads_email_address'];
	
	if (isset($form_post_data['first-name'])) 
		$subscriber['first_name'] = $form_post_data['first-name'];
	
	if (isset($form_post_data['wpleads_first_name'])) 
		$subscriber['first_name'] = $form_post_data['wpleads_first_name'];
		
	if (isset($form_post_data['last-name'])) 
		$subscriber['last_name'] = $form_post_data['last-name'];
	
	if (isset($form_post_data['wpleads_last_name'])) 
		$subscriber['last_name'] = $form_post_data['wpleads_last_name'];
	
	$form_settings = $form_meta_data['inbound_form_values'][0];
	parse_str($form_settings, $form_settings);
	
	if ($form_settings['inbound_shortcode_hubspot_enable']=='on')
	{		
		$target_list = $form_settings['inbound_shortcode_hubspot_list_id'];
		inboundnow_hubspot_add_subscriber($subscriber , $target_list);	
	}
}