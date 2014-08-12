<?php
/**
* Template Name: simple
* @package  WordPress Landing Pages
* @author   Inbound Template Generator!
* WordPress Landing Page Config File
*/
do_action('lp_global_config');
$key = lp_get_parent_directory(dirname(__FILE__));


/* Configures Template Information */
$lp_data[$key]['info'] = array(
    'data_type' => 'template',
    'version' => '2.0.0',
    'label' => 'simple',
    'category' => 'custom',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
 array(
   'label' => "Logo",
   'description' => "Logo",
   'id' => "logo",
   'type' => "media",
   'default' => "/wp-content/uploads/landing-pages/templates/simple/images/inbound-logo.png",
   'selector' => ".page-header .nprogress-logo.fade",
 ),
 array(
     'label' => 'Headline Text Color',
     'description' => '',
     'id'  => 'headline-text-color',
     'type'  => 'colorpicker',
     'default'  => '333333',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Sub Headline & Content Area Text Color',
     'description' => '',
     'id'  => 'subheadline-text-color',
     'type'  => 'colorpicker',
     'default'  => '888888',
     'context'  => 'normal'
     ),
 array(
   'label' => "Sub Headline & Content Area",
   'description' => "Sub Headline",
   'id' => "sub-headline",
   'type' => "wysiwyg",
   'default' => "Inbound Now's Landing Pages Can Help You Convert More Traffic",
   'selector' => ".page-header .fade.brief.big",
 ),
 array(
   'label' => "Form Area",
   'description' => "Content Area",
   'id' => "content-area",
   'type' => "wysiwyg",
   'default' => '[inbound_forms id="default_2" name="Standard Company Form"]',
   'selector' => ".contents.fade .controls",
 ),
 array(
     'label' => 'Submit Button Background Color',
     'description' => '',
     'id'  => 'submit-bg-color',
     'type'  => 'colorpicker',
     'default'  => '3498db',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Submit Button Text Color',
     'description' => '',
     'id'  => 'submit-text-color',
     'type'  => 'colorpicker',
     'default'  => 'ffffff',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Bottom Text Color',
     'description' => '',
     'id'  => 'bottom-text-color',
     'type'  => 'colorpicker',
     'default'  => '222222',
     'context'  => 'normal'
     ),
 array(
   'label' => "Below Form Area",
   'description' => "Conversion Area",
   'id' => "conversion-area",
   'type' => "wysiwyg",
   'default' => '[social_share style="bar" align="horizontal" heading_align="inline" facebook="1" twitter="1" google_plus="1" linkedin="1" pinterest="1" /]

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.',
   'selector' => ".contents.fade .actions",
 ),
array(
'label' => 'Background Settings',
          'description' => 'Set the template\'s background',
          'id'  => 'background-style',
          'type'  => 'dropdown',
          'default'  => 'color',
          'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
          'context'  => 'normal'
          ),
      array(
          'label' => 'Background Image',
          'description' => 'Enter an URL or upload an image for the banner.',
          'id'  => 'background-image',
          'type'  => 'media',
          'default'  => 'http://lorempixel.com/1400/800/',
          'context'  => 'normal'
          ),
      array(
          'label' => 'Background Color',
          'description' => 'Use this setting to change the templates background color',
          'id'  => 'background-color',
          'type'  => 'colorpicker',
          'default'  => 'ffffff',
          'context'  => 'normal'
          ),

      array(
        'label' => 'turn-off-editor',
        'description' => 'Turn off editor',
        'id'  => 'turn-off-editor',
        'type'  => 'custom-css',
        'default'  => '#postdivrich, #lp_2_form_content {display:none !important;}'
        ),
);
