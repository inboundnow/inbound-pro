<?php

add_filter('inboundnow_forms_settings', 'inboundnow_getresponse_add_form_settings' , 10 , 1);
function inboundnow_getresponse_add_form_settings($fields)
{
	$fields['forms']['options']['getresponse_enable'] =   array(
                                                'name' => __('Enable GetResponse Sync', 'inbound-getresponse'),
                                                'desc' => __('Enable/Disable GetResponse Integration for this form.', 'inbound-getresponse'),
                                                'type' => 'checkbox',
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );

	$getresponse_lists = inboundnow_getresponse_get_lists();
	$fields['forms']['options']['getresponse_list_id'] =   array(
                                                'name' => __('GetResponse List', 'inbound-getresponse'),
                                                'desc' => __('Send submissions to this GetResponse list', 'inbound-getresponse'),
                                                'type' => 'select',
                                                'options' => $getresponse_lists,
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );
	return $fields;
}