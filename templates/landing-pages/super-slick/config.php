<?php
/**
* Template Name:  Super Slick Template
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
	'label' => "Super Slick Template", // Nice Name
	'category' => 'v1, 2 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/super-slick-lander-preview/', // Demo Link
	'description'  => 'SuperSlick is great for showcasing a hero image or video on your landing page.' // template description
);


// Define Meta Options for template
// These values are returned in the template's index.php file with lp_get_value($post, $key, 'field-id') function
$lp_data[$key]['settings'] =
array(
    array(
        'label' => 'Headline Text Color', // Label of field
        'description' => "Use this setting to change the template's headline text color", // field description
        'id' => 'headline-color', // metakey.
        'type'  => 'colorpicker', // text metafield type
        'default'  => '000000', // default content
        'context'  => 'normal' // Context in screen for organizing options
        ),
    array(
        'label' => 'Sub Headline Text',
        'description' => "Sub headline text goes here",
        'id'  => 'sub-headline',
        'type'  => 'text',
        'default'  => 'Sub Headline Goes Here',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Sub Headline Text Color',
        'description' => "Sub Headline Text Color",
        'id'  => 'sub-headline-color',
        'type'  => 'colorpicker',
        'default'  => 'a3a3a3',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Top Main Background Color',
        'description' => "Use this setting to change the template's body color",
        'id'  => 'top-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Top Area Text Color',
        'description' => "Use this setting to change the template's Top Text Color",
        'id'  => 'top-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Top Area Layout',
        'description' => "Do you want the conversion/form area on the right or left?",
        'id'  => 'form-placement',
        'type'  => 'dropdown',
        'default'  => 'left',
        'options' => array('left'=>'Form on left', 'right'=>'Form on right'),
        'context'  => 'normal'
        ),
	  array(
        'label' => 'Submit Button Background Color',
        'description' => "Submit Button Background Color",
        'id'  => 'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => '5baa1e',
        'context'  => 'normal'
        ),
	 array(
        'label' => "Bottom Area Content",
        'description' => "This is the content in the bottom of the page",
        'id'  => 'wysiwyg-content',
        'type'  => 'wysiwyg',
        'default'  => 'This is the bottom area text',
        'context'  => 'normal'
        ),
  array(
        'label' => 'Bottom Text Color',
        'description' => "Bottom Text Color",
        'id'  => 'bottom-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
   array(
        'label' => 'Bottom Background Color',
        'description' => "Bottom Background Color",
        'id'  => 'bottom-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Display Social Media Share Buttons',
        'description' => "Display Social Media Share Buttons",
        'id'  => 'display-social',
        'type'  => 'radio',
        'default'  => '0',
        'options' => array('1' => 'on','0'=>'off'),
        'context'  => 'normal'
        )
    );