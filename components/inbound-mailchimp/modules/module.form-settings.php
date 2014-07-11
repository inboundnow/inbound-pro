<?php

add_filter('inboundnow_forms_settings', 'inboundnow_mailchimp_add_form_settings' , 10 , 1);
function inboundnow_mailchimp_add_form_settings($fields)
{
	$fields['forms']['options']['mailchimp_enable'] =   array(
                                                'name' => __('Enable MailChimp Sync', 'inbound-pro'),
                                                'desc' => __('Enable/Disable MailChimp Integration for this form.', 'inbound-pro'),
                                                'type' => 'checkbox',
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );

	$mailchimp_lists = inboundnow_mailchimp_get_mailchimp_lists();
	$fields['forms']['options']['mailchimp_list_id'] =   array(
                                                'name' => __('MailChimp List', 'inbound-pro'),
                                                'desc' => __('Send submissions to this MailChimp list', 'inbound-pro'),
                                                'type' => 'select',
                                                'options' => $mailchimp_lists,
                                                'std' => '',
                                                'class' => 'main-form-settings exclude-from-refresh' );
	return $fields;
}