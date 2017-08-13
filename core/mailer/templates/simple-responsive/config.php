<?php
/**
* Template Name: Simple Responsive
* @package	Inbound Email
*
*/

$key = basename(dirname(__FILE__));


/* Configures Template Information */
$inbound_email_data[$key]['info'] = array(
	'data_type' =>'email-template',
	'label' => __( 'Simple Responsive' , 'inbound-pro') ,
	'category' => 'responsive',
	'demo' => '',
	'description' => __( 'Responsive email template.' , 'inbound-pro' ),
	'acf' => true
);

/*
* Define ACF Fields to be used in this template
* Pay special attention to the 'location' key as this is where we tell ACF to load when this template is selected
*/
if( function_exists('register_field_group') ):

register_field_group(array (
	'key' => 'group_544ebe1337324',
	'title' => 'Simple Responsive',
	'fields' => array (
		array (
			'key' => 'field_544ebf0aa4133',
			'label' => __('Logo URL','inbound-pro'),
			'name' => 'logo_url',
			'prefix' => '',
			'type' => 'image',
			'instructions' => __('Enter or upload your logo here','inbound-pro'),
			'required' => false,
			'conditional_logic' => 0,
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => INBOUND_EMAIL_URLPATH.'templates/'.$key.'/images/logo-wide-3.png'
		),
		array (
			'key' => 'field_544ebfe4a4135',
			'label' => __('Main Email Content','inbound-pro'),
			'name' => 'main_email_content',
			'prefix' => '',
			'type' => 'wysiwyg',
			'instructions' => __('The content of your email should go here. ','inbound-pro'),
			'required' => 0,
			'conditional_logic' => 0,
			'default_value' => 'Dear [lead-field id="wpleads_first_name" default="Subscriber"],

Thank you for taking the time to read this email.

Warm regards from Inbound Now',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55cd9b0catswillshowup',
			'label' => __('Unsubscribe Link Anchor','inbound-pro'),
			'name' => 'unsubscribe_link_text',
			'type' => 'text',
			'instructions' => __('Enter the text of the unsubscribe link.','inbound-pro'),
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => __('Unsubscribe','inbound-pro'),
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_56b91774c2c42sr99',
			'label' => __('Hide \'Show this email in browser\' link', 'inbound-pro'),
			'name' => 'hide_show_email_in_browser',
			'type' => 'checkbox',
			'instructions' => __('Hide/Reveal the link to the online version of the email.','inbound-pro'),
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'hide' => __('hide','inbound-pro'),
			),
			'default_value' => array(),
			'layout' => 'vertical',
			'toggle' => 0,
		),
	),
	'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'inbound-email',
				),
				array (
					'param' => 'template_id',
					'operator' => '==',
					'value' => $key,
				)
			),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
));

endif;
