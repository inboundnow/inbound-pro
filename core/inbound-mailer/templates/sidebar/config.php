<?php
/**
* Template Name: Sidebar
* @package	Inbound Email
* 
*/

$key = basename(dirname(__FILE__));


/* Configures Template Information */
$inbound_email_data[$key]['info'] = array(
	'data_type' =>'email-template',
	'label' => __( 'Sidebar' , 'inbound-mailer') ,
	'category' => 'responsive',
	'demo' => '',
	'description' => __( 'Sidebar email template.' , 'inbound-mailer' ),
	'acf' => true
);

/*
* Define ACF Fields to be used in this template
* Pay special attention to the 'location' key as this is where we tell ACF to load when this template is selected
*/
if( function_exists('register_field_group') ):

register_field_group(array (
	'key' => 'group_55cc433477c28',
	'title' => 'Sidebar',
	'fields' => array (
		array (
			'key' => 'field_55cd81a2fc784',
			'label' => 'Header',
			'name' => 'header',
			'prefix' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
		),
		array (
			'key' => 'field_55cc4334a8973',
			'label' => 'Logo URL',
			'name' => 'logo_url',
			'prefix' => '',
			'type' => 'image',
			'instructions' => 'Enter or upload your logo here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'library' => 'all',
		),
		array (
			'key' => 'field_55cc4c906a6a7',
			'label' => 'Header Background color',
			'name' => 'header_bg_color',
			'prefix' => '',
			'type' => 'color_picker',
			'instructions' => 'Choose the background color of your header',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
		),
		array (
			'key' => 'field_55cc44eecaab2',
			'label' => 'Email Body',
			'name' => 'main_content',
			'prefix' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
		),
		array (
			'key' => 'field_55cc4334a8d5b',
			'label' => 'Content Before Callout',
			'name' => 'main_email_content',
			'prefix' => '',
			'type' => 'wysiwyg',
			'instructions' => 'The content of your email should go here.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 'Dear [lead-field id="wpleads_first_name" default="Subscriber"],
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et. Lorem ipsum dolor sit amet.</p>',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55cc4334a9143',
			'label' => 'Callout Text',
			'name' => 'callout_text',
			'prefix' => '',
			'type' => 'wysiwyg',
			'instructions' => 'Add your callout text here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.
<a href="#">Do it Now! »</a>',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55cc4334a952b',
			'label' => 'Callout Background Color',
			'name' => 'callout_background_color',
			'prefix' => '',
			'type' => 'color_picker',
			'instructions' => 'Choose the background color of the callout',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#ecf8ff',
		),
		array (
			'key' => 'field_55cc4334a9913',
			'label' => 'Content After Callout',
			'name' => 'content_after_callout',
			'prefix' => '',
			'type' => 'wysiwyg',
			'instructions' => 'The email content after the callout area should go here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et. Lorem ipsum dolor sit amet.</p>',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55cc4334a9cfb',
			'label' => 'Button Link',
			'name' => 'button_link',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Add the link to a landing page',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
		array (
			'key' => 'field_55cc4334aa0e4',
			'label' => 'Button Text',
			'name' => 'button_text',
			'prefix' => '',
			'type' => 'text',
			'instructions' => 'Add the text that will appear inside the button',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_55cc455ccaab4',
			'label' => 'Social Box',
			'name' => 'footer',
			'prefix' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
		),
		array (
			'key' => 'field_55cc4334ab084',
			'label' => 'Social Box Background Color',
			'name' => 'footer_bg_color',
			'prefix' => '',
			'type' => 'color_picker',
			'instructions' => 'Choose the background color of the social box',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
		),
		array (
			'key' => 'field_55cc4334aa4cc',
			'label' => 'Facebook Page',
			'name' => 'facebook_page',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Enter the URL of your Facebook page if you have one',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Enter the complete URL',
		),
		array (
			'key' => 'field_55cc4334aa8b4',
			'label' => 'Twitter Handle',
			'name' => 'twitter_handle',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Enter you Twitter handle here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Add the complete URL here',
		),
		array (
			'key' => 'field_55cc4334aac9c',
			'label' => 'Google Plus',
			'name' => 'google_plus',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Enter your Google Plus URL',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Add the complete URL',
		),
		array (
			'key' => 'field_55cc4334ab46c',
			'label' => 'Phone Number',
			'name' => 'phone_number',
			'prefix' => '',
			'type' => 'text',
			'instructions' => 'Enter your phone number here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_55cc4334ab854',
			'label' => 'Email',
			'name' => 'email',
			'prefix' => '',
			'type' => 'email',
			'instructions' => 'Enter your email here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array (
			'key' => 'field_55cc4524caab3',
			'label' => 'Sidebar',
			'name' => 'sidebar',
			'prefix' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
		),
		array (
			'key' => 'field_55cc4334ac40c',
			'label' => 'Sidebar Section',
			'name' => 'sidebar',
			'prefix' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'In the following section you will add your sidebar content. You can add a header and up to 9 sections. The header and each section can also optionally be a link that sends to a URL of your interest. If you want less than 9 sections simply leave the exceeeding sections empty',
		),
		array (
			'key' => 'field_55cc4cf88ee15',
			'label' => 'Sidebar Background Color',
			'name' => 'sidebar_bg_color',
			'prefix' => '',
			'type' => 'color_picker',
			'instructions' => 'Choose the background color of your sidebar',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
		),
		array (
			'key' => 'field_55cc4334ac7f4',
			'label' => 'Sidebar Header',
			'name' => 'sidebar_header',
			'prefix' => '',
			'type' => 'text',
			'instructions' => 'This is the header on top of your sidebar',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 'Header Thing »',
			'placeholder' => 'Add Here Your Sidebar Header',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_55cc4334acbdc',
			'label' => 'Sidebar Subheader',
			'name' => 'sidebar_subheader',
			'prefix' => '',
			'type' => 'text',
			'instructions' => 'Here you should add the subheader that will show up under the sidebar header. Leave the field empty if you don\'t want one',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Add Here the sidebar Subheader',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_55d005a26a3a9',
			'label' => 'Sidebar Sections',
			'name' => 'sidebar_sections',
			'prefix' => '',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add Row',
			'sub_fields' => array (
				array (
					'key' => 'field_55d005e429b2f',
					'label' => 'Section Link Text',
					'name' => 'section_link_text',
					'prefix' => '',
					'type' => 'text',
					'instructions' => 'Here you should add the text of your link',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_55d00632b9d71',
					'label' => 'Section Link URL',
					'name' => 'section_link_url',
					'prefix' => '',
					'type' => 'url',
					'instructions' => 'Here you should add the URL of the link',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 'Add the complete URL',
				),
			),
		),
		array (
			'key' => 'field_55cd6cf637faa',
			'label' => 'Footer',
			'name' => 'footer',
			'prefix' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
		),
		array (
			'key' => 'field_55cc4334abc3c',
			'label' => 'Terms Page URL',
			'name' => 'terms_page_url',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Enter the URL of your terms page here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
		array (
			'key' => 'field_55cc4334ac024',
			'label' => 'Privacy Page URL',
			'name' => 'privacy_page_url',
			'prefix' => '',
			'type' => 'url',
			'instructions' => 'Enter the URL of your privacy page here',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
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