<?php

/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
add_action('inbound_store_lead_post','inboundnow_zapier_landing_page_integratation');
function inboundnow_zapier_landing_page_integratation($lead_data)
{		
	if (get_post_meta( $lead_data['lp_id'] ,'inboundnow-zapier-zapier_integration' , true ))
	{
		$webhook_urls = get_option( 'inboundnow_zapier_webhook_url' ); 		
		$webhook_urls =  preg_split("/[\r\n,]+/", $webhook_urls, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($webhook_urls as $webhook_url)
		{
			inboundnow_zapier_add_subscriber( $lead_data , $webhook_url );	
		}
	}				
}



/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
add_action('inboundnow_form_submit_actions','inboundnow_zapier_inboundnow_form_integratation' , 10 , 2 );
function inboundnow_zapier_inboundnow_form_integratation($form_post_data , $form_meta_data )
{			
		
	$form_settings = $form_meta_data['inbound_form_values'][0];
	parse_str($form_settings, $form_settings);
	
	if ($form_settings['inbound_shortcode_zapier_enable']=='on')
	{		
		$webhook_urls = get_option( 'inboundnow_zapier_webhook_url' ); 
		$webhook_urls =  preg_split("/[\r\n,]+/", $webhook_urls, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($webhook_urls as $webhook_url)
		{
			inboundnow_zapier_add_subscriber( $form_post_data , $webhook_url );	
		}
	}
}