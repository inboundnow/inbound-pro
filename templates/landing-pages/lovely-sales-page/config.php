<?php
/**
* Template Name: lovely-sales-page
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
    'label' => 'Lovely Sales Page',
    'category' => 'video, sales page',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
  array(
      'label' => 'Top Headline Color',
      'description' => '',
      'id'  => 'headline-color',
      'type'  => 'colorpicker',
      'default'  => 'ffffff',
      'context'  => 'normal'
      ),
 array(
   'label' => "Subheadline",
   'description' => "Subheadline",
   'id' => "subheadline",
   'type' => "textarea",
   'default' => "Sub-headline Goes right Here",
   'selector' => "body h3",
 ),
 array(
     'label' => 'Subheadline Color',
     'description' => '',
     'id'  => 'subheadline-color',
     'type'  => 'colorpicker',
     'default'  => 'BBD8F3',
     'context'  => 'normal'
     ),

 array(
   'label' => "Top Content",
   'description' => "Main Content",
   'id' => "top-content",
   'type' => "wysiwyg",
   'default' => '<iframe src="http://www.youtube.com/embed/BzcD6PgvLP4?list=UUCqiE-EcfDjaKGXSxtegcyg" height="360" width="640" allowfullscreen="" frameborder="0"></iframe>',
   'selector' => "#content",
 ),

 array(
   'label' => "Main Content",
   'description' => "Main Content",
   'id' => "main-content",
   'type' => "wysiwyg",
   'default' => "
   <h1>Amazing Awesome Headline Here. WOWZER</h1>
   [columns gutter='20']
   [two_third]
   <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
   <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
   [/two_third]

   [one_third]

   [inbound_forms id='default_2' name='Standard Company Form']


   [/one_third]
   [/columns]
   <h1>Amazing Awesome Headline 2 Here</h1>
   <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
   <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
   <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
  ",
   'selector' => "#content",
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
          'default'  => '3498db',
          'context'  => 'normal'
          ),
      array(
        'label' => 'turn-off-editor',
        'description' => "Turn off editor",
        'id'  => 'turn-off-editor',
        'type'  => 'custom-css',
        'default'  => '#postdivrich, #lp_2_form_content {display:none !important;}'
        ),
);
