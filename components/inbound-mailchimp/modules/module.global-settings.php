<?php

add_filter('lp_define_global_settings','inboundnow_mailchimp_add_global_settings');
add_filter('wpleads_define_global_settings','inboundnow_mailchimp_add_global_settings');
add_filter('wp_cta_define_global_settings','inboundnow_mailchimp_add_global_settings');
function inboundnow_mailchimp_add_global_settings($global_settings)
{
	switch (current_filter())
	{
		case "lp_define_global_settings":		
			$tab_slug = 'lp-extensions';
			break;
		case "wpleads_define_global_settings":		
			$tab_slug = 'wpleads-extensions';
			break;
		case "wp_cta_define_global_settings":		
			$tab_slug = 'wp-cta-extensions';
			break;
	}
	
	$global_settings[$tab_slug]['settings'][] = array(
		'id'  => 'inboundnow_header_mailchimp',			
		'type'  => 'header', 
		'default'  => __('<h4>MailChimp API Key</h4>', 'inbound-pro'),
		'options' => null
	);

	$global_settings[$tab_slug]['settings'][] = array(
		'id'  => 'inboundnow_mailchimp_api_key',
		'option_name'  => 'inboundnow_mailchimp_api_key',
		'label' => __('MailChimp API Key', 'inbound-pro'),
		'description' => __("Enter Mailchimp API Key to power extension: http://kb.mailchimp.com/article/where-can-i-find-my-api-key.", 'inbound-pro'),
		'type'  => 'text', 
		'default'  => ''
	);
	
	$global_settings[$tab_slug]['settings'][] = array(
		'id'	=> 'inboundnow_mailchimp_double_optin',
		'option_name'	=> 'inboundnow_mailchimp_double_optin',
		'label' => __('Double Optin', 'inbound-mailchimp'),
		'description' => __('Enable Double Optin to have leads sent a confirmation email to confirm their subscription.', 'inbound-pro'),
		'type'	=> 'dropdown', 
		'default'	=> 'true',
		'options' => array( 'false' => 'off' , 'true' => 'on' )
	);

	return $global_settings;
}
