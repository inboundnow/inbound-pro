<?php
/**
* Template Name:  RSVP Envelope Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // global config action hook

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.1", // Version Number
	'label' => "RSVP Template", // Nice Name
	'category' => 'v1, 2 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/rsvp-envelope-lander-preview/', // Demo Link
	'description'  => 'This template is great for sending out invitations to events.' // template description
);

// Define Meta Options for template
// These values are returned in the template's index.php file with lp_get_value($post, $key, 'field-id') function
$lp_data[$key]['settings'] =
array(
    array(
        'label' => "Template body color", // Label of field
        'description' => "Use this setting to change the template's body background color", // field description
        'id' => 'body-color', // metakey.
        'type'  => 'colorpicker', // text metafield type
        'default'  => 'CCCCCC', // default content
        'context'  => 'normal' // Context in screen for organizing options
        ),
    array(
        'label' => 'Headline Color',
        'description' => "Use this setting to change the template's headline text color",
        'id'  => 'headline-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => "Text Color",
        'description' => "Text Color",
        'id'  => 'text-color',
        'type'  => 'colorpicker',
        'default'  => '7C7873',
        'context'  => 'normal'
        ),
    array(
        'label' => "Form Text Color",
        'description' => "Form Text Color",
        'id'  => 'form-text-color',
        'type'  => 'colorpicker',
        'default'  => '7C7873',
        'context'  => 'normal'
        ),
    array(
        'label' => "Display Social Media Share Buttons",
        'description' => "Display Social Media Share Buttons",
        'id'  => 'display-social',
        'type'  => 'radio',
        'default'  => '1',
        'options' => array('1' => 'on','0'=>'off'),
        'context'  => 'normal'
        ),
    array(
        'label' => "Sidebar Layout",
        'description' => "Align sidebar to the left or the right",
        'id'  => 'sidebar',
        'type'  => 'dropdown',
        'default'  => 'right',
        'options' => array('right'=>'Envelope on right', 'left'=>'Envelope on left'),
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Image',
        'description' => "Background Image",
        'id'  => 'media-example',
        'type'  => 'media',
        'default'  => '',
        'context'  => 'normal'
        )
    );