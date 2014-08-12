<?php
/**
* WordPress: WP Calls To Action Template Config File
* @package  WordPress Calls to Action
* Template Name:  Feedburner - Subscribe to Download
* @author 	InboundNow
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
	'label' => "Feedburner Subscribe to Download", // Nice Name
	'category' => 'social', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
	'description'  => 'Get more feedburner RSS subscribers with this CTA', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);

// Define Meta Options for template
$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "This Call to action is used for gating downloadable content behind a feedburner RSS subscription Form. Basically you can get more blog subscribers return for a peice of downloadable content.<p><strong>Recommened Dimensions:</strong> 250px by 300px</p>", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
    array(
        'label' => 'Text Above the CTA (optional)',
        'description' => "This is the text above the call to action describing what they get if they share. You can use HTML or shortcodes in this box",
        'id'  => 'header-text',
        'type'  => 'textarea',
        'default'  => 'Download our Latest Ebook. To download subscribe to our Blog',
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
        'label' => 'Feedburner Username',
        'description' => "This is your Feedburner Username only. Example: http://feeds.feedburner.com/<span style='color:red;'>inboundnowblog</span>",
        'id'  => 'share-url',
        'type'  => 'text',
        'default'  => 'inboundnowblog',
        'context'  => 'normal'
        ),
     array(
        'label' => 'Link to Download',
        'description' => "This will be the download for people to get once they like the above URL",
        'id'  => 'download-url',
        'type'  => 'text',
        'default'  => 'http://www.link-to-download.com',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Background Color',
        'description' => "Changes background color",
        'id'  => 'content-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => 'turn-off-editor',
        'description' => "Turn off editor",
        'id'  => 'turn-off-editor',
        'type'  => 'custom-css',
        'default'  => '#postdivrich, .calc.button-secondary {display:none !important;}'
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
	   'description' => "<strong>Please Note:</strong> there is no linkedin share callback and people can simply close the share window to download your content. Most folks will actually share the URL", // what field does
	   'id' => 'description-two', // metakey. $key Prefix is appended from parent in array loop
	   'type'  => 'description-block', // metafield type
	   'default'  => '', // default content
	   'context'  => 'normal' // Context in screen (advanced layouts in future)
	   ),
    );


/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');