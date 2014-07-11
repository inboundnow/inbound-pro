<?php

/* Provide backwards compatibility for older data array model */
//add_filter('lp_extension_data','inboundnow_hubspot_add_metaboxes');
//add_filter('wp_cta_extension_data','inboundnow_hubspot_add_metaboxes');

function inboundnow_hubspot_add_metaboxes( $metabox_data )
{
	$lists = inboundnow_hubspot_get_hubspot_lists();
	
	$metabox_data['inboundnow-hubspot']['info']['data_type'] = 'metabox';
	$metabox_data['inboundnow-hubspot']['info']['position'] = 'side';
	$metabox_data['inboundnow-hubspot']['info']['priority'] = 'default';
	$metabox_data['inboundnow-hubspot']['info']['label'] = 'HubSpot Integration';
		
	$metabox_data['inboundnow-hubspot']['settings'] = array(
		//ADD METABOX - SELECTED TEMPLATE	
		array(
			'id'  => 'hubspot_integration',
			'label' => 'Enable:',
			'description' => "Enable this setting to send email related conversion data to hubspot list. Email must be present in conversion form for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
			'default'  => '0',
			'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
		),
		array(
			'id'  => 'hubspot_list',
			'label' => 'Target list:',
			'description' => "Select the hubspot list that converted data will be sent to. Must have setup a hubspot api key & portal id for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
			'default'  => '',
			'options' => $lists
		)
	);
	
	return $metabox_data;
}

	
	