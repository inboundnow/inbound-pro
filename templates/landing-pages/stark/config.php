<?php
/**
 * Template Name: Stark
 * @package	WordPress Landing Pages
 * @author	David Wells
 */

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

do_action('lp_global_config');

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "2.2.1", // Version Number
	'label' => "stark", // Nice Name
	'category' => '2 Column Layout, responsive', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/stark/', // Demo Link
	'description'	=> 'Create a great looking quote styled landing page' // template description
);

// Define Meta Options for template
// These values are returned in the template's index.php file with lp_get_value($post, $key, 'field-id') function
$lp_data[$key]['settings'] =
array(
	array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
		),
	array(
			'label' => __( 'Main Content' , 'landing-pages' ) ,
			'description' => __( 'This is the default content from template.' , 'landing-pages' ),
			'id' => "main-content",
			'type' => "wysiwyg",
			'default' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

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

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>'
		),
	array(
			'label' => __( 'Call to Action Content' , 'landing-pages' ),
			'description' => __( 'Place your call to action here.' , 'landing-page' ),
			'id' => "conversion-area-content",
			'type' => "wysiwyg",
			'default' => ''
		),
	array(
		'label' => 'Main Color Scheme',
		'description' => "Use this setting to change the main Color",
		'id'	=> 'color-scheme',
		'type'	=> 'colorpicker',
		'default'	=> 'ef133b',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Text color', // Label of field
		'description' => "Use this setting to change the Text Color", // field description
		'id' => 'text-color', // metakey.
		'type'	=> 'colorpicker', // text metafield type
		'default'	=> '000000', // default content
		'context'	=> 'normal' // Context in screen for organizing options
		),
	/* Background Settings */
	array(
		'label' => 'Background Settings',
		'description' => "Set the template's background",
		'id'	=> 'background-style',
		'type'	=> 'dropdown',
		'default'	=> 'color',
		'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Image',
		'description' => "Enter an URL or upload an image for the banner.",
		'id'	=> 'background-image',
		'type'	=> 'media',
		'default'	=> '',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Color',
		'description' => "Use this setting to change the templates background color",
		'id'	=> 'background-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Logo Image',
		'description' => "Upload Your Logo",
		'id'	=> 'logo',
		'type'	=> 'media',
		'default'	=> '/wp-content/uploads/landing-pages/templates/stark/assets/images/logo.png',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Display Social Media Share Buttons', // Label of field
		'description' => "Display Social Media Share Buttons", // field description
		'id' => 'display-social', // metakey.
		'type'	=> 'radio', // text metafield type
		'default'	=> '1', // default content
		'options' => array('1' => 'on','0'=>'off'),
		'context'	=> 'normal' // Context in screen for organizing options
		),
	array(
		'label' => 'Form Layout',
		'description' => "Align form area on the left or the right",
		'id'	=> 'sidebar',
		'type'	=> 'dropdown',
		'default'	=> 'right',
		'options' => array('left'=>'Form on left', 'right'=>'Form on right'),
		'context'	=> 'normal'
		),
	array(
		'label' => 'Form Headline Text (optional)',
		'description' => "Create a headline above your form",
		'id'	=> 'form-headline',
		'type'	=> 'text',
		'default'	=> '',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Share Text',
		'description' => "This is the text that goes with the Share button",
		'id'	=> 'sharetext',
		'type'	=> 'text',
		'default'	=> 'Share this',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Custom Share URL',
		'description' => "This creates a custom share url for share buttons",
		'id'	=> 'shareurl',
		'type'	=> 'text',
		'default'	=> '',
		'context'	=> 'normal'
		)

	);