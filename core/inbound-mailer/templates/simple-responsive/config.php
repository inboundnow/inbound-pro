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
	'label' => __( 'Simple Responsive' , 'inbound-mailer') ,
	'category' => 'responsive',
	'demo' => '',
	'description' => __( 'Responsive email template.' , 'inbound-mailer' ),
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
			'label' => 'Logo URL',
			'name' => 'logo_url',
			'prefix' => '',
			'type' => 'image',
			'instructions' => 'Enter or upload your logo here',
			'required' => false,
			'conditional_logic' => 0,
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'library' => 'uploadedTo',
			'default_value' => INBOUND_EMAIL_URLPATH.'templates/'.$key.'/images/logo-wide-3.png'
		),
		array (
			'key' => 'field_544ebfe4a4135',
			'label' => 'Main Email Content',
			'name' => 'main_email_content',
			'prefix' => '',
			'type' => 'wysiwyg',
			'instructions' => 'The content of your email should go here. ',
			'required' => 0,
			'conditional_logic' => 0,
			'default_value' => 'Dear [lead-field id="wpleads_first_name" default="Subscriber"],

Thank you for taking the time to read this email.

Warm regards from Inbound Now',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
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
	