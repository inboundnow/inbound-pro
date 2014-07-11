<?php


/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
add_filter('lp_define_global_settings','inboundnow_hubspot_add_global_settings');
add_filter('wpleads_define_global_settings','inboundnow_hubspot_add_global_settings');
add_filter('wp_cta_define_global_settings','inboundnow_hubspot_add_global_settings');
function inboundnow_hubspot_add_global_settings($global_settings)
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
	
	$global_settings[$tab_slug]['settings'][] = 
		array(
			'id'  => 'inboundnow_header_hubspot',			
			'type'  => 'header', 
			'default'  => __('<h4>HubSpot API Key</h4>', INBOUND_LABEL),
			'options' => null
		);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_hubspot_portal_id',
				'option_name'  => 'inboundnow_hubspot_portal_id',
				'label' => __('HubSpot Portal ID', INBOUND_LABEL),
				'description' => __('Get your HubSpot API Key at https://app.hubspot.com/keys/get.', INBOUND_LABEL),
				'type'  => 'text', 
				'default'  => ''
			);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_hubspot_api_key',
				'option_name'  => 'inboundnow_hubspot_api_key',
				'label' => 'HubSpot API Key',
				'description' => "Get your HubSpot API Key at https://app.hubspot.com/keys/get.",
				'type'  => 'text', 
				'default'  => ''
			);
	
	return $global_settings;
}
