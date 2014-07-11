<?php

add_filter('inboundnow_forms_settings', 'inboundnow_hubspot_add_form_settings' , 10 , 1);
function inboundnow_hubspot_add_form_settings($fields)
{
	$fields['forms']['options']['hubspot_enable'] =   array(
                                                'name' => __('Enable Hubspot Sync', INBOUND_LABEL),
                                                'desc' => __('Enable/Disable Hubspot Integration for this form.', INBOUND_LABEL),
                                                'type' => 'checkbox',
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );

	$hubspot_lists = inboundnow_hubspot_get_hubspot_lists();
	$fields['forms']['options']['hubspot_list_id'] =   array(
                                                'name' => __('Hubspot List', INBOUND_LABEL),
                                                'desc' => __('Send submissions to this Hubspot list', INBOUND_LABEL),
                                                'type' => 'select',
                                                'options' => $hubspot_lists,
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );
	return $fields;
}