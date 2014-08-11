<?php


/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
add_filter('lp_define_global_settings','inboundnow_constantcontact_add_global_settings');
add_filter('wpleads_define_global_settings','inboundnow_constantcontact_add_global_settings');
add_filter('wp_cta_define_global_settings','inboundnow_constantcontact_add_global_settings');
function inboundnow_constantcontact_add_global_settings($global_settings)
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
		'id'	=> 'inboundnow_header_constantcontact',			
		'type'	=> 'header', 
		'default'	=> __('<h4>ConstantContact API Key</h4>', 'inbound-constantcontact'),
		'options' => null
	);

	$global_settings[$tab_slug]['settings'][] = array(
		'id'	=> 'inboundnow_constantcontact_api_key',
		'option_name'	=> 'inboundnow_constantcontact_api_key',
		'label' => __('ConstantContact API Key', 'inbound-constantcontact'),
		'description' => __('The first thing we need is an API Key. Singup at <a href=\'https://constantcontact.mashery.com/\'>https://constantcontact.mashery.com/</a> and create your first application.', 'inbound-constantcontact'),
		'type'	=> 'text', 
		'default'	=> ''
	);
	
	
	$global_settings[$tab_slug]['settings'][] = array(
		'id'	=> 'inboundnow_constantcontact_access_key',
		'option_name'	=> 'inboundnow_constantcontact_access_key',
		'label' => __('Constant Contact Access Key', 'inbound-constantcontact'),
		'description' => __("After creating your API Key you can generate your Access Key here: <a href='https://constantcontact.mashery.com/apps/mykeys' target='_blank'>https://constantcontact.mashery.com/apps/mykeys</a>", 'inbound-constantcontact'),
		'type'	=> 'text'
	);

	
	$global_settings[$tab_slug]['settings'][] = array(
			'id'	=> 'inboundnow_constantcontact_double_optin',
			'option_name'	=> 'inboundnow_constantcontact_double_optin',
			'label' => __('Double Optin', 'inbound-constantcontact'),
			'description' => __('Enable Double Optin to have leads sent a confirmation email to confirm their subscription.', 'inbound-constantcontact'),
			'type'	=> 'dropdown', 
			'default'	=> 'ACTION_BY_VISITOR',
			'options' => array( 'ACTION_BY_OWNER' => 'off' , 'ACTION_BY_VISITOR' => 'on' )
	);

	return $global_settings;
}
