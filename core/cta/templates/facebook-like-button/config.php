<?php
/**
* WordPress: WP Calls To Action Template Config File
* @package  WordPress Calls to Action
* @author   InboundNow
*/
do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));
$this_path = WP_CTA_PATH.'templates/'.$key.'/';
$url_path = WP_CTA_URLPATH.'templates/'.$key.'/';


$wp_cta_data[$key]['info'] =
array(
	'data_type' => 'template',
    'version' => "1.0", // Version Number
    'label' => "Facebook Like Button", // Nice Name
    'category' => 'social', // Template Category
    'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
    'description'  => 'Get more facebook likes', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //path to template folder
);



// Define Meta Options for template
$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "This Call to action is used for getting more likes on facebook. The conversions on this call to action are clicks on the like button<p><strong>Recommened Dimensions:</strong> 300px by 170px</p>", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
    array(
        'label' => 'Select an Image',
        'description' => "Choose your Image Style",
        'id'  => 'image-style',
        'type'  => 'image-select',
		'image_type' => 'png',
        'default'  => '1',
        'options' => array( '1'=>'Image 1','2'=>'Image 2', '3'=>'Imge 3', '4'=>'Image 4', '5'=>'Image 5', '6'=>'Image 6', '7'=>'Image 7', '8'=>'Image 8', '9'=>'Image 9', '10'=>'Image 10', '11'=>'Image 11','12'=>'Image 12', '13'=>'Image 13', '14'=>'Image 14', '15'=>'Image 16', '17'=>'Image 17', '18'=>'Image 18', '19'=>'Image 19', '20'=>'Image 20', '21'=>'Image 21', '22'=>'Image 22'),
        'context'  => 'normal'
        ),
    array(
        'label' => 'Text Above the CTA (optional)',
        'description' => "This is the text above the call to action describing what they get if they share. You can use HTML or shortcodes in this box",
        'id'  => 'header-text',
        'type'  => 'textarea',
        'default'  => '',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Top Text Color',
        'description' => "Changes Text Color",
        'id'  => 'text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
    array(
        'label' => 'URL to Like on Facebook',
        'description' => "Header Text",
        'id'  => 'share-url',
        'type'  => 'text',
        'default'  => 'http://www.inboundnow.com/',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Color',
        'description' => "Changes background color",
        'id'  => 'background-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
       'label' => 'Border Radius (Set rounded corners)',
       'description' => "Set to 0 for no rounded corners, set to 5+ to round the CTA edges",
       'id'  => 'border-radius',
       'type'  => 'number',
       'default'  => '0',
       'context'  => 'normal'
       ),
       array(
        'label' => 'Instructions', // Name of field
        'description' => "<strong>Advanced Options:</strong> Sometimes facebook requires unique app IDs on sites to run a like to download tool. If the like button doesn't work. Enter your app id below", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
        array(
        'label' => 'Facebook App ID',
        'description' => "Optional",
        'id'  => 'fb-app-id',
        'type'  => 'text',
        'default'  => '209205675779605',
        'context'  => 'normal'
        )
    );
	

/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');