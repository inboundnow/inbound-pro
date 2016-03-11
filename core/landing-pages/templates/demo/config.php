<?php
/**
 * WordPress Landing Page Config File
 * Template Name:    Demo Template
 * @package    WordPress Landing Pages
 * @author    Inbound Now
 *
 * This is a demo template for developers and designers to use as a reference for building landing page templates
 * for Wordpress Landing Pages Plugin http://wordpress.org/plugins/landing-pages/
 *
 * As of September 20015 we've begun using Advanced Custom Fields to design our Landing Page Template's dynamic fields:
 * http://www.advancedcustomfields.com/
 *
 */

/* get the name of the template folder */
$key = basename(dirname(__FILE__));

/* discover the absolute path of where this template is located. Core templates are loacted in /wp-content/plugins/landing-pages/templates/ while custom templates belong in /wp-content/uploads/landing-pages/tempaltes/ */
$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/'; // This defines the path to your template folder. /wp-content/uploads/landing-pages/templates by default


/* This is where we setup our template meta data */
$lp_data[$key]['info'] = array(
	'data_type' => "acf4", 												/* tell landing pages that this data represents a landing page template powered by ACF */
	'version' => "2.0.0", 												/* lets give our template a version number */
	'label' => __( 'Demo','landing-pages'), 							/* Let's give our template a nice name */
	'category' => 'Demo', 												/* you can categorize your landing pages by adding comma separated keywords */
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', 	/* a link to a third party demo page if applicable */
	'description'	=> __( 'The Demo theme is here to help developers and designs implement their own designs into the landing page plugin. Study this template to learn about Landing Page Plugin\'s templating system and to assist in building new templates.' , 'inbound-pro' ) /* template description here! */
);

/* now setup ACF field definitions */
if( function_exists('register_field_group') ):

	register_field_group(array (
		'key' => 'group_55de1ff86df89',
		'title' => __('Demo Landing Page','landing-pages'),
		'fields' => array (
			array (
				'key' => 'field_55de1fedc4d99',
				'label' => __('WYSIWYG','landing-pages'),
				'name' => 'wysiwyg',
				'type' => 'wysiwyg',
				'instructions' => __('This is the default content from template.','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

<strong>In this guide you will learn:</strong>

[list icon="ok-sign" font_size="16" icon_color="#00a319" text_color="" bottom_margin="10"]
<ul>
	<li>This list was created with the list icon shortcode.</li>
	<li>Click on the power icon in your editor to customize your own</li>
	<li>Explain why users will want to fill out the form</li>
	<li>Keep it short and sweet.</li>
	<li>This list should be easily scannable</li>
</ul>
[/list]

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>',
				'tabs' => 'all',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
			array (
				'key' => 'field_55de20a76221c',
				'label' => __('Text Field','landing-pages'),
				'name' => 'text',
				'type' => 'text',
				'instructions' => __('Example of a text field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'Example text field',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55de20f550346',
				'label' => __('Text Area','landing-pages'),
				'name' => 'textarea',
				'type' => 'textarea',
				'instructions' => __('Example text area field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'Lorem ipsum dolor sit amet, est omnis feugiat eu, eum scripta temporibus at. Per ne verterem detraxit comprehensam, lucilius aliquando reprehendunt pri in, duo no dictas tacimates. Et vis maiorum imperdiet. Odio signiferumque est ei, discere dissentiunt cu mei. His no solum accusamus eloquentiam.',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => 'wpautop',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55de213471d4a',
				'label' => __('Number','landing-pages'),
				'name' => 'number',
				'type' => 'number',
				'instructions' => __('Example number field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '3.14159265359',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55de2143c251b',
				'label' => __('Email','landing-pages'),
				'name' => 'email',
				'type' => 'email',
				'instructions' => __('Example email field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'example@inboundnow.com',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_55de21d216412',
				'label' => __('Url','landing-pages'),
				'name' => 'url',
				'type' => 'url',
				'instructions' => __('Example URL field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'http://www.inboundnow.com',
				'placeholder' => '',
			),
			array (
				'key' => 'field_55de227087051',
				'label' => __('Image','landing-pages'),
				'name' => 'image',
				'type' => 'image',
				'instructions' => __('Example image field','landing-pages'),
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
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array (
				'key' => 'field_55de228f2edc1',
				'label' => __('File','landing-pages'),
				'name' => 'file',
				'type' => 'file',
				'instructions' => __('File field type example','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'library' => 'all',
				'min_size' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array (
				'key' => 'field_55de22aa0fd1e',
				'label' => __('Gallery','landing-pages'),
				'name' => 'gallery',
				'type' => 'gallery',
				'instructions' => __('Gallery field example','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'min' => '',
				'max' => '',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array (
				'key' => 'field_55de22d6f3d2e',
				'label' => __('Select','landing-pages'),
				'name' => 'select',
				'type' => 'select',
				'instructions' => __('Example of a dropdown select field type. ','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'0' => 'no',
					'1' => 'yes',
				),
				'default_value' => 1,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55de2307f3d2f',
				'label' => __('Checkbox','landing-pages'),
				'name' => 'checkbox',
				'type' => 'checkbox',
				'instructions' => __('Example of a checkbox select field type. ','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'0' => 'no',
					'1' => 'yes',
				),
				'default_value' =>  1,
				'layout' => 'vertical',
				'toggle' => 0,
			),
			array (
				'key' => 'field_55de232f0a4d9',
				'label' => __('Radio','landing-pages'),
				'name' => 'radio',
				'type' => 'radio',
				'instructions' => __('Example of a radio field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'0' => 'no',
					'1' => 'yes',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'yes',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_55de24540a4da',
				'label' => __('True/False','landing-pages'),
				'name' => 'truefalse',
				'type' => 'true_false',
				'instructions' => __('Example true false field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => __('Do you agree with the statement?','landing-pages'),
				'default_value' => 0,
			),
			array (
				'key' => 'field_55de24ab0a4db',
				'label' => __('Google Map','landing-pages'),
				'name' => 'googlemap',
				'type' => 'google_map',
				'instructions' => __('Example google map field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'center_lat' => '37.7833',
				'center_lng' => '122.4167',
				'zoom' => 14,
				'height' => 400,
			),
			array (
				'key' => 'field_55de2520b8967',
				'label' => __('Datepicker','landing-pages'),
				'name' => 'datepicker',
				'type' => 'date_picker',
				'instructions' => __('Example datepicker field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'm/d/Y',
				'return_format' => 'm/d/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_55de254bb8968',
				'label' => __('Colorpicker','landing-pages'),
				'name' => 'colorpicker',
				'type' => 'color_picker',
				'instructions' => __('Example colorpicker field','landing-pages'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#016EA8',
			)
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'landing-page',
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
		'active' => 1,
		'description' => '',
		'options' => array(),
	));

endif;