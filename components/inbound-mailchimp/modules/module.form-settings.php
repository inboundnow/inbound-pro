<?php

add_filter('inboundnow_forms_settings', 'inboundnow_mailchimp_add_form_settings' , 10 , 1);
function inboundnow_mailchimp_add_form_settings($fields)
{
	$fields['forms']['options']['mailchimp_enable'] =   array(
                                                'name' => __('Enable MailChimp Sync', INBOUND_LABEL),
                                                'desc' => __('Enable/Disable MailChimp Integration for this form.', INBOUND_LABEL),
                                                'type' => 'checkbox',
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );

	$mailchimp_lists = inboundnow_mailchimp_get_mailchimp_lists();
	$fields['forms']['options']['mailchimp_list_id'] =   array(
                                                'name' => __('MailChimp List', INBOUND_LABEL),
                                                'desc' => __('Send submissions to this MailChimp list', INBOUND_LABEL),
                                                'type' => 'select',
                                                'options' => $mailchimp_lists,
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );
	return $fields;
}