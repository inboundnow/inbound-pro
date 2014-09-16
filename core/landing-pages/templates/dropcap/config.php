<?php
/**
 * Template Name: Dropcap
 * @package  WordPress Landing Pages
 * @author   David Wells
 */

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

do_action('lp_global_config');

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "2.0.0", // Version Number
	'label' => "Dropcap", // Nice Name
	'category' => 'v1, 1 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/dropcap-lander-preview/', // Demo Link
	'description'  => 'Create a great looking quote styled landing page' // template description
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
           'label' => __( 'Conversion Area' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
    array(
        'label' => 'Text color', // Label of field
        'description' => "Use this setting to change the Text Color", // field description
        'id' => 'text-color', // metakey.
        'type'  => 'colorpicker', // text metafield type
        'default'  => 'ffffff', // default content
        'context'  => 'normal' // Context in screen for organizing options
        ),
    array(
        'label' => 'Content Background Color',
        'description' => "Use this setting to change the Content Area Background Color",
        'id'  => 'content-background',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Conversion Area Text Color',
        'description' => "Use this setting to change the Conversion Area text Color",
        'id'  => 'form-text-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    /* Background Settings */
    array(
        'label' => 'Background Settings',
        'description' => "Set the template's background",
        'id'  => 'background-style',
        'type'  => 'dropdown',
        'default'  => 'fullscreen',
        'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Image',
        'description' => "Enter an URL or upload an image for the banner.",
        'id'  => 'background-image',
        'type'  => 'media',
        'default'  => '/wp-content/plugins/landing-pages/templates/dropcap/assets/images/beach-1.jpg',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Color',
        'description' => "Use this setting to change the templates background color",
        'id'  => 'background-color',
        'type'  => 'colorpicker',
        'default'  => '186d6d',
        'context'  => 'normal'
        )
    );