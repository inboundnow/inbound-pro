<?php


/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
add_filter('lp_define_global_settings','inboundnow_getresponse_add_global_settings');
add_filter('wpleads_define_global_settings','inboundnow_getresponse_add_global_settings');
add_filter('wp_cta_define_global_settings','inboundnow_getresponse_add_global_settings');
function inboundnow_getresponse_add_global_settings($global_settings)
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
			'id'  => 'inboundnow_header_getresponse',			
			'type'  => 'header', 
			'default'  => __('<h4>GetResponse API Key</h4>', 'inbound-getresponse'),
			'options' => null
		);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_getresponse_api_key',
				'option_name'  => 'inboundnow_getresponse_api_key',
				'label' => 'GetResponse API Key',
				'description' => "Get your GetResponse API Key at <a href='https://app.getresponse.com/my_api_key.html' target='_blank'>https://app.getresponse.com/my_api_key.html</a>.",
				'type'  => 'text', 
				'default'  => ''
			);
	
	return $global_settings;
}
