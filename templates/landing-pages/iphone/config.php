<?php
/**
* Template Name: iphone
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
    'label' => 'iPhone Landing Page',
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
   'default' => "/wp-content/uploads/landing-pages/templates/iphone/images/inbound-logo.png",
   'selector' => ".span12.slogan.text-center .logo",
 ),
 array(
     'label' => 'Headline Text Color',
     'description' => '',
     'id'  => 'headline-color',
     'type'  => 'colorpicker',
     'default'  => '000000',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Sub Headline Text Color',
     'description' => '',
     'id'  => 'sub-headline-color',
     'type'  => 'colorpicker',
     'default'  => '999999',
     'context'  => 'normal'
     ),
 array(
   'label' => "Subheadline",
   'description' => "Subheadline",
   'id' => "subheadline",
   'type' => "text",
   'default' => "This is the sub headline",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
 'label' => 'Top Background Settings',
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
           'default'  => '/wp-content/uploads/landing-pages/templates/iphone/images/beach.jpg',
           'context'  => 'normal'
           ),
       array(
           'label' => 'Background Color',
           'description' => 'Use this setting to change the templates background color',
           'id'  => 'background-color',
           'type'  => 'colorpicker',
           'default'  => 'eeeeee',
           'context'  => 'normal'
           ),

 array(
   'label' => "Left Content",
   'description' => "Left Content",
   'id' => "left-content",
   'type' => "wysiwyg",
   'default' => "
			<h2 class=''>Heading</h2>
			<p class=''>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
		",
   'selector' => ".span4.info-container.left .hero-unit.info",
 ),
 array(
   'label' => "iPhone Image",
   'description' => "Iphone Image",
   'id' => "iphone-image",
   'type' => "media",
   'default' => "/wp-content/uploads/landing-pages/templates/iphone/images/iwhite.png",
   'selector' => ".span4.text-center img",
 ),
 array(
     'label' => 'Image Instructions', // Name of field
     'description' => "<strong>iPhone Image Instructions:</strong> You can download the iPhone image <a download href='/wp-content/uploads/landing-pages/templates/iphone/images/iwhite.png'>here</a> and customize it to your liking. Or use another image. Its up to you!", // what field does
     'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
     'type'  => 'description-block', // metafield type
     'default'  => '', // default content
     'context'  => 'normal' // Context in screen (advanced layouts in future)
     ),
 array(
   'label' => "Right Content",
   'description' => "Right Content",
   'id' => "right-content",
   'type' => "wysiwyg",
   'default' => "
  		<h2>Heading</h2>
  		<p class=''>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
		",
   'selector' => ".span4.info-container.right .hero-unit.info",
 ),
 array(
   'label' => "Google Play Link",
   'description' => "Subheadline",
   'id' => "googlelink",
   'type' => "text",
   'default' => "http://www.inboundnow.com",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
   'label' => "Applestore Link",
   'description' => "",
   'id' => "applelink",
   'type' => "text",
   'default' => "http://www.inboundnow.com",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
   'label' => "Twitter Link",
   'description' => "",
   'id' => "twitterlink",
   'type' => "text",
   'default' => "http://www.inboundnow.com",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
   'label' => "Google Plus Link",
   'description' => "",
   'id' => "googlepluelink",
   'type' => "text",
   'default' => "http://www.inboundnow.com",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
   'label' => "Facebook Link",
   'description' => "",
   'id' => "facebooklink",
   'type' => "text",
   'default' => "http://www.inboundnow.com",
   'selector' => ".span12.slogan.text-center small",
 ),
 array(
   'label' => 'turn-off-editor',
   'description' => "Turn off editor",
   'id'  => 'turn-off-editor',
   'type'  => 'custom-css',
   'default'  => '#postdivrich, #lp_2_form_content {display:none !important;}',
   ),
);
