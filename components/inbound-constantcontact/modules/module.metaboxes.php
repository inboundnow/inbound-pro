<?php

/* Provide backwards compatibility for older data array model */
//add_filter('lp_extension_data','inboundnow_constantcontact_add_metaboxes');
//add_filter('wp_cta_extension_data','inboundnow_constantcontact_add_metaboxes');

function inboundnow_constantcontact_add_metaboxes( $metabox_data )
{
	$lists = inboundnow_constantcontact_get_lists();
	
	$metabox_data['inboundnow-constantcontact']['info']['data_type'] = 'metabox';
	$metabox_data['inboundnow-constantcontact']['info']['position'] = 'side';
	$metabox_data['inboundnow-constantcontact']['info']['priority'] = 'default';
	$metabox_data['inboundnow-constantcontact']['info']['label'] = 'ConstantContact Integration';
		
	$metabox_data['inboundnow-constantcontact']['settings'] = array(
		//ADD METABOX - SELECTED TEMPLATE	
		array(
			'id'  => 'constantcontact_integration',
			'label' => 'Enable:',
			'description' => "Enable this setting to send email related conversion data to constantcontact list. Email must be present in conversion form for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
			'default'  => '0',
			'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
		),
		array(
			'id'  => 'constantcontact_list',
			'label' => 'Target list:',
			'description' => "Select the constantcontact list that converted data will be sent to. Must have setup a constantcontact api key and enabled the setting above for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
			'default'  => '',
			'options' => $lists
		)
	);
	
	return $metabox_data;
}

	
	