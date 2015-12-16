<?php
/**
* WordPress Landing Page Config File
* Template Name:	Tubelar Template
*
* @package	WordPress Landing Pages
* @author 	Inbound Now
*/

/* gets template directory name to use as identifier - do not edit - include in all template files */
$key = basename(dirname(__FILE__));

$lp_data[$key]['info'] = array(
	'data_type' => 'acf4',
	'version' => "2.0.1",
	'label' => __( 'Tublar' , 'landing-pages' ),
	'category' => 'Video',
	'demo' => 'http://demo.inboundnow.com/tubelar-lander-lander-preview/',
	'description'  => __( 'Tublar template is a simple video background template.' , 'landing-pages' )
);


/* define ACF fields here */
if( function_exists('register_field_group') ):

	register_field_group(array (
		'key' => 'group_55e4dcd1a2dfe',
		'title' => 'Tubelar',
		'fields' => array (
			array (
				'key' => 'field_55e4dcf5fe07b',
				'label' => __('Main Headline','landing-page'),
				'name' => 'lp-main-headline',
				'type' => 'text',
				'instructions' => __('Enter in the main content here. ','landing-page'),
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
				'key' => 'field_55e4dd0ebcfc9',
				'label' => __('Main Content','landing-page'),
				'name' => 'tubelar-main-content',
				'type' => 'wysiwyg',
				'instructions' => __('Main content here.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

<strong>In this guide you will learn:</strong>

[list icon="check" font_size="16" icon_color="#00a319" text_color="" bottom_margin="10"]
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
				'key' => 'field_55e4dd30e0fc3',
				'label' => __('Conversion Area','landing-page'),
				'name' => 'tubelar-conversion-area-content',
				'type' => 'wysiwyg',
				'instructions' => __('Place your call to action / inbound form here.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'tabs' => 'all',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
			array (
				'key' => 'field_55e4dd44e0fc4',
				'label' => __('YouTube Background Video URL','landing-page'),
				'name' => 'tubelar-yt-video',
				'type' => 'text',
				'instructions' => __('Paste in the URL of the YouTube Video here.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'http://www.youtube.com/watch?v=_OBlgSz8sSM',
				'placeholder' => '',
			),
			array (
				'key' => 'field_55e4dd5fe0fc5',
				'label' => __('Sidebar Layout','landing-page'),
				'name' => 'tubelar-sidebar',
				'type' => 'select',
				'instructions' => __('Align sidebar to the right or the left.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'lp_right' => 'Sidebar on Right',
					'lp_left' => 'Sidebar on Left',
				),
				'default_value' => 'lp_right',
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55e4dd6de0fc6',
				'label' => __('Text Color','landing-page'),
				'name' => 'tubelar-text-color',
				'type' => 'color_picker',
				'instructions' => __('Use this setting to change the content area\'s background color.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#FFFFFF',
			),
			array (
				'key' => 'field_55e4dd7ce0fc7',
				'label' => __('Content Background Color','landing-page'),
				'name' => 'tubelar-box-color',
				'type' => 'color_picker',
				'instructions' => __('Use this setting to change the template\'s submit button color.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#000000',
			),
			array (
				'key' => 'field_55e4dd8ee0fc8',
				'label' => __('Background Color Settings','landing-page'),
				'name' => 'tubelar-clear-bg-settings',
				'type' => 'select',
				'instructions' => __('Use this setting to change the content area\'s background nature.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'transparent' => 'Transparent Background',
					'solid' => 'Solid',
				),
				'default_value' => 'trasparent',
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55e4dda6e0fc9',
				'label' => __('Logo Image','landing-page'),
				'name' => 'tubelar-logo',
				'type' => 'image',
				'instructions' => __('Upload your logo (300px x 110px) ','landing-page'),
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
				'key' => 'field_55e4ddb2e0fca',
				'label' => __('Display Social Media Share Buttons','landing-page'),
				'name' => 'tubelar-display-social',
				'type' => 'radio',
				'instructions' => __('Toggle social sharing on and off','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					1 => 'on',
					0 => 'off',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 1,
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_55e4ddcee0fcb',
				'label' => __('Show Play Controls','landing-page'),
				'name' => 'tubelar-controls',
				'type' => 'radio',
				'instructions' => __('Toggle display of background video controls on or off.','landing-page'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					1 => 'on',
					0 => 'off',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 1,
				'layout' => 'vertical',
			),
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