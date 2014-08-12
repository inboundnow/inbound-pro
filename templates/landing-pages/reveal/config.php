<?php
/**
* Template Name: reveal
* @package	WordPress Landing Pages
* @author	Inbound Template Generator!
* WordPress Landing Page Config File
*/
do_action('lp_global_config');
$key = lp_get_parent_directory(dirname(__FILE__));


/* Configures Template Information */
$lp_data[$key]['info'] = array(
	'data_type' => 'template',
	'version' => '2.0.0',
	'label' => 'reveal',
	'category' => 'custom',
	'demo' => '',
	'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
	array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
		),
	array(
           'label' => __( 'Call to Action Content' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
	array(
		'label' => 'Content below headline',
		'description' => '',
		'id'	=> 'top-content',
		'type'	=> 'wysiwyg',
		'default'	=> '',
		'context'	=> 'normal'
		),
	array(
		'label' => "Text on Button",
		'description' => "Text on Button",
		'id' => "text-on-button",
		'type' => "text",
		'default' => "Get This Now",
		'selector' => "#split-me",
	),
	array(
		'label' => 'Top Color/Color Scheme',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'scheme',
		'type'	=> 'colorpicker',
		'default'	=> '3447d8',
		'context'	=> 'normal'
		),

	array(
		'label' => 'Submit Button Background Color',
		'description' => '',
		'id'	=> 'submit-bg-color',
		'type'	=> 'colorpicker',
		'default'	=> '69c773',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Submit Button Text Color',
		'description' => '',
		'id'	=> 'submit-text-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Bottom Content',
		'description' => '',
		'id'	=> 'bottom-content',
		'type'	=> 'wysiwyg',
		'default'	=> '',
		'context'	=> 'normal'
		),
	array(
	'label' => 'Background Settings',
		'description' => 'Set the template\'s background',
		'id'	=> 'background-style',
		'type'	=> 'dropdown',
		'default'	=> 'color',
		'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Image',
		'description' => 'Enter an URL or upload an image for the banner.',
		'id'	=> 'background-image',
		'type'	=> 'media',
		'default'	=> '',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'background-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),

);
