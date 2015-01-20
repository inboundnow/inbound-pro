<?php

add_filter('inboundnow_forms_settings', 'inboundnow_aweber_add_form_settings' , 10 , 1);
function inboundnow_aweber_add_form_settings($fields)
{
	$fields['forms']['options']['aweber_enable'] =   array(
                                                'name' => __('Enable Aweber Sync', INBOUND_LABEL),
                                                'desc' => __('Enable/Disable Aweber Integration for this form.', INBOUND_LABEL),
                                                'type' => 'checkbox',
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );

	$aweber_lists = inboundnow_aweber_get_aweber_lists();
	$fields['forms']['options']['aweber_list_id'] =   array(
                                                'name' => __('Aweber List', INBOUND_LABEL),
                                                'desc' => __('Send submissions to this Aweber list', INBOUND_LABEL),
                                                'type' => 'select',
                                                'options' => $aweber_lists,
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );
	return $fields;
}