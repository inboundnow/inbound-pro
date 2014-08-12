<?php
/**
* WordPress Landing WP Calls To Action Template Config File
* Template Name:  Blank Template
* @package  WordPress Calls To Action
* @author 	InboundNow
*/

do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));
$this_path = WP_CTA_PATH.'templates/'.$key.'/';
$url_path = WP_CTA_URLPATH.'templates/'.$key.'/';

$wp_cta_data[$key]['info'] =
array(
	'data_type' => "template", // datatype
	'version' => "1.0", // Version Number
	'label' => "Blank Template", // Nice Name
	'category' => 'Blank, Box', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
	'description'  => __( 'This template is completely blank, you can use any shortcodes or HTML you want in here' , 'cta' ), // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);



$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "<div class='cta-description-box'><span class='calc button-secondary'>Calculate height/width</span></div><p>Insert your call to action graphic into the content area below. Don't forget to hyperlink it to your final destination</p>", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => __( '<p><b>Insert your call to action graphic into the content area below</b>. Don\'t forget to hyperlink it to your final destination</p>' , 'cta' ) , // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),		
    array(
        'label' => 'Message Text',
        'description' => "Message Text",
        'id'  => 'content-text',
        'type'  => 'wysiwyg',
        'default'  => __( 'Insert your call to action content here.' , 'cta' ),
        'context'  => 'normal'
        )
    );
	


/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');