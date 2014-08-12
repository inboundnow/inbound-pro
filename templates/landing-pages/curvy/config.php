<?php
/**
* Template Name: curvy
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
    'label' => 'curvy',
    'category' => 'custom',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
   array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
		),
     array(
           'label' => __( 'Main Content' , 'landing-pages' ) ,
           'description' => __( 'This is the default content from template.' , 'landing-pages' ),
           'id' => "main-content",
           'type' => "wysiwyg",
           'default' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

<strong>In this guide you will learn:</strong>

[list icon="ok-sign" font_size="16" icon_color="#00a319" text_color="" bottom_margin="10"]
<ul>
	<li>This list was created with the list icon shortcode.</li>
	<li>Click on the power icon in your editor to customize your own</li>
	<li>Explain why users will want to fill out the form</li>
	<li>Keep it short and sweet.</li>
	<li>This list should be easily scannable</li>
</ul>
[/list]

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>'
         ), 
	
	array(
           'label' => __( 'Call to Action Content' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
  array(
      'label' => 'Headline Area Text Color',
      'description' => 'Use this setting to change the templates background color',
      'id'  => 'headline-text-color',
      'type'  => 'colorpicker',
      'default'  => 'FFFFFF',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Headline Area Background Color',
      'description' => 'Use this setting to change the templates background color',
      'id'  => 'headline-background-color',
      'type'  => 'colorpicker',
      'default'  => '2C84AF',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Content/Form Area Text Color',
      'description' => 'Use this setting to change the templates background color',
      'id'  => 'content-text-color',
      'type'  => 'colorpicker',
      'default'  => '6B7C93',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Content/Form Area Background Color',
      'description' => 'Use this setting to change the templates background color',
      'id'  => 'content-background-color',
      'type'  => 'colorpicker',
      'default'  => 'EAEEF2',
      'context'  => 'normal'
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
   'label' => "Top Logo or Video",
   'description' => "logo",
   'id' => "logo",
   'type' => "wysiwyg",
   'default' => "<img src='/wp-content/uploads/landing-pages/templates/curvy/images/inbound-logo-white.png'>",
   'selector' => "#header a",
 ),
 array(
   'label' => "Bottom Text",
   'description' => "Bottom Text",
   'id' => "bottom-text",
   'type' => "textarea",
   'default' => "Copyright (c) Your Site 2015.",
   'selector' => "#footer",
 ),
 array(
     'label' => 'Bottom Text Color',
     'description' => '',
     'id'  => 'bottom-color',
     'type'  => 'colorpicker',
     'default'  => 'ffffff',
     'context'  => 'normal'
     ),
array(
'label' => 'Background Settings',
          'description' => 'Set the template\'s background',
          'id'  => 'background-style',
          'type'  => 'dropdown',
          'default'  => 'fullscreen',
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
          'default'  => '186d6d',
          'context'  => 'normal'
          ),

);
