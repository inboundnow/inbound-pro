<?php
/**
* WordPress: WP Calls To Action Template Config File
* Template Name:  Flat CTA
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
    'label' => "Clean CTA", // Nice Name
    'category' => 'Minimal', // Template Category
    'demo' => '', // Demo Link
    'description'  => 'Clean call to action', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);


/* Define Meta Options for template */
$wp_cta_data[$key]['settings'] =
array(
   array(
       'label' => 'Instructions', // Name of field
       'description' => "Fill in the below fields to configure the clean CTA. Make sure to insert the height and width of your call to action. Use the visual editor to see live changes and for faster editing", // what field does
       'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
       'type'  => 'description-block', // metafield type
       'default'  => '', // default content
       'context'  => 'normal' // Context in screen (advanced layouts in future)
       ),

   array(
         'label' => 'Header Background Image',
         'description' => "Header Image",
         'id'  => 'header-image',
         'type'  => 'media',
         'default'  => $url_path . 'bg.jpg',
         'context'  => 'normal'
         ),
   array(
         'label' => 'Custom Header Height',
         'description' => "enter height in px",
         'id'  => 'header-height',
         'type'  => 'text',
         'default'  => '',
         'context'  => 'normal'
         ),
   array(
         'label' => 'Header Text',
         'description' => "Header Text",
         'id'  => 'header-text',
         'type'  => 'text',
         'default'  => 'Snappy Headline',
         'context'  => 'normal'
         ),
   array(
       'label' => 'Header Text Color',
       'description' => "Use this setting to change headline color",
       'id'  => 'header-text-color',
       'type'  => 'colorpicker',
       'default'  => '000000',
       'context'  => 'normal'
       ),
      array(
         'label' => 'Sub Header Text',
         'description' => "Sub Header Text",
         'id'  => 'sub-header-text',
         'type'  => 'wysiwyg',
         'default'  => '<h3>Awesome Subheadline Text Goes here</h3>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Sub Header Text Color',
         'description' => "Use this setting to change headline color",
         'id'  => 'sub-header-text-color',
         'type'  => 'colorpicker',
         'default'  => '000000',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Background Color',
         'description' => "Changes background color",
         'id'  => 'content-color',
         'type'  => 'colorpicker',
         'default'  => 'ed1a4b',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Bottom Text Option',
         'description' => "Content below button",
         'id'  => 'bottom-text',
         'type'  => 'wysiwyg',
         'default'  => '',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Bottom Text Option (optional)',
         'description' => "Use this setting to change headline color",
         'id'  => 'bottom-text-color',
         'type'  => 'colorpicker',
         'default'  => '000000',
         'context'  => 'normal'
         ),
      array(
         'label' => 'Submit Button Color',
         'description' => "Use this setting to change the template's submit button color.",
         'id'  => 'submit-button-color',
         'type'  => 'colorpicker',
         'default'  => '69c773'
         ),
      array(
         'label' => 'Submit Button Text',
         'description' => "Text on the button.",
         'id'  => 'submit-button-text',
         'type'  => 'text',
         'default'  => 'Download Now'
         ),
      array(
         'label' => 'Submit Button Text Color',
         'description' => "Text on the button.",
         'id'  => 'submit-button-text-color',
         'type'  => 'colorpicker',
         'default'  => 'ffffff'
         ),
       array(
         'label' => 'Destination Link',
         'description' => "Where do you want to link people to?",
         'id'  => 'link_url',
         'type'  => 'text',
         'default'  => 'http://www.inboundnow.com'
         ),
       array(
         'label' => 'turn-off-editor',
         'description' => "Turn off editor",
         'id'  => 'turn-off-editor',
         'type'  => 'custom-css',
         'default'  => '#postdivrich {display:none !important;}'
         )
   );


/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');
