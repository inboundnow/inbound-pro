<?php
/**
* WordPress: WP Calls To Action Template Config File
* Template Name: Thumbnail Call To Action
* @package  WordPress Calls to Action
* @author 	InboundNow
*/

do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));
$this_path = WP_CTA_PATH.'templates/'.$key.'/';
$url_path = WP_CTA_URLPATH.'templates/'.$key.'/';

$wp_cta_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0", // Version Number
	'label' => "Thumbnail Call To Action", // Nice Name
	'category' => 'Box', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
	'description'  => 'This is a simple cta template with an icon/image', // template description
	'path' => $this_path //path to template folder
);


/* Define Meta Options for template */
$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Headline Text Color',
        'description' => "Use this setting to change headline color",
        'id'  => 'headline-text-color',
        'type'  => 'colorpicker',
        'default'  => '333333',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Header Text',
        'description' => "Header Text",
        'id'  => 'header-text',
        'type'  => 'text',
        'default'  => 'Check Out our Latest Whitepaper',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Color',
        'description' => "Changes background color",
        'id'  => 'content-background-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
       'label' => 'Button Text',
       'description' => "Text on the button.",
       'id'  => 'submit-button-text',
       'type'  => 'text',
       'default'  => 'LEARN MORE'
       ),
    array(
       'label' => 'Button Link',
       'description' => "Link on the button.",
       'id'  => 'submit-button-link',
       'type'  => 'text',
       'default'  => 'http://www.landing-page-url.com'
       ),
     array(
        'label' => 'Button Background Color',
        'description' => "Use this setting to change the template's submit button color.",
        'id'  => 'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff'
        ),
     array(
        'label' => 'Button Text Color',
        'description' => "Use this setting to change the template's submit button text color.",
        'id'  => 'submit-button-text-color',
        'type'  => 'colorpicker',
        'default'  => 'ff6b2c'
        ),
     array(
        'label' => 'Thumbnail Image',
        'description' => "File/Image Upload Description",
        'id'  => 'image-url', // called in template's index.php file with lp_get_value($post, $key, 'media-id');
        'type'  => 'media',
        'default'  => $url_path . 'assets/report.png',
        'context'  => 'normal'
        ),
    );


/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');
