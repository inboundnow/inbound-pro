<?php
/**
* WordPress: WP Calls To Action Template Config File
* Template Name:  Call Out Box
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
	'label' => "Ebook CTA", // Nice Name
	'category' => 'Popup, Wide', // Template Category
	'demo' => '', // Demo Link
	'description'  => 'This is a cta that works great as a popup but can also be placed anywhere on your site', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);

// Define Meta Options for template
$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "<p>This is a popup call to action used to promote something. Use the main hero image and the main content area to create the copy for your popup</p>", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '<p>This is a popup call to action used to promote something. Use the main hero image and the main content area to create your popup. You can use this call to action as a non popup as well. Recommended height 400px and width 660px</p>', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
    array(
        'label' => 'Main Image',
        'description' => "This is the main graphic with the popup",
        'id'  => 'hero', // called in template's index.php file with lp_get_value($post, $key, 'media-id');
        'type'  => 'media',
        'default'  => $this_path . '/img/download.png',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Header Text',
        'description' => "Header Text",
        'id'  => 'header-text',
        'type'  => 'text',
        'default'  => 'Download our Awesome Ebook it will Teach You XYZ',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Headline Text Color',
        'description' => "Use this setting to change headline color",
        'id'  => 'headline-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
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
       'label' => 'Main Content',
       'description' => "Use this setting to change the content text color",
       'id'  => 'main_content',
       'type'  => 'wysiwyg',
       'default'  => '',
       'context'  => 'normal'
       ),
    array(
       'label' => 'Form Content',
       'description' => "Insert form here",
       'id'  => 'form_content',
       'type'  => 'wysiwyg',
       'default'  => '',
       'context'  => 'normal'
       ),
     array(
        'label' => 'Content Text Color',
        'description' => "Use this setting to change the content text color",
        'id'  => 'content-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
     /*array(
        'label' => 'Submit Button Color',
        'description' => "Use this setting to change the template's submit button color.",
        'id'  => 'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => 'E14D4D'
        ),
     array(
        'label' => 'Submit Button Text Color',
        'description' => "Use this setting to change the template's submit button text color.",
        'id'  => 'submit-button-text-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff'
        ),
     array(
        'label' => 'Submit Button Text',
        'description' => "Text on the button.",
        'id'  => 'submit-button-text',
        'type'  => 'text',
        'default'  => 'Download Now'
        ),
      array(
        'label' => 'Redirect URL',
        'description' => "Where to redirect people",
        'id'  => 'redirect',
        'type'  => 'text',
        'default'  => 'http://www.link-to-final-destination.com'
        ),
      array(
        'label' => 'Notification Email',
        'description' => "This will send you a notice when a lead fills out the form",
        'id'  => 'email',
        'type'  => 'text',
        'default'  => 'youremail@email.com'
        ),
      array(
        'label' => 'turn-off-editor',
        'description' => "Turn off editor",
        'id'  => 'turn-off-editor',
        'type'  => 'custom-css',
        'default'  => '.calc.button-secondary {display:none !important;}'
        ),*/
    );
/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');