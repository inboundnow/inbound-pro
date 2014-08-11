<?php

add_filter('inboundnow_forms_settings', 'inboundnow_constantcontact_add_form_settings' , 10 , 1);
function inboundnow_constantcontact_add_form_settings($fields)
{
	$fields['forms']['options']['constantcontact_enable'] =   array(
		'name' => __('Enable ConstantContact Sync', CONSTANTCONTACT_TEXT_DOMAIN),
		'desc' => __('Enable/Disable ConstantContact Integration for this form.', CONSTANTCONTACT_TEXT_DOMAIN),
		'type' => 'checkbox',
		'std' => '',
		'class' => 'main-form-settings exclude-from-refresh' 
	);

	$constantcontact_lists = inboundnow_constantcontact_get_lists();
	
	$fields['forms']['options']['constantcontact_list_id'] =   array(
		'name' => __('ConstantContact List', CONSTANTCONTACT_TEXT_DOMAIN),
		'desc' => __('Send submissions to this ConstantContact list', CONSTANTCONTACT_TEXT_DOMAIN),
		'type' => 'select',
		'options' => $constantcontact_lists,
		'std' => '',
		'class' => 'main-form-settings exclude-from-refresh' 
	);
	return $fields;
}