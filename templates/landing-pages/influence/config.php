<?php
/**
* Template Name: influence
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
    'label' => 'influence',
    'category' => 'custom',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
array(
    'label' => 'Headline Color',
    'description' => '',
    'id'  => 'headline-text-color',
    'type'  => 'colorpicker',
    'default'  => 'ffffff',
    'context'  => 'normal'
    ),
 array(
   'label' => "Sub Headline",
   'description' => "Sub headline",
   'id' => "sub-headline",
   'type' => "textarea",
   'default' => "You can insert your catchy sub-headline here",
   'selector' => "body .address-bar",
 ),
 array(
     'label' => 'Sub Headline Text Color',
     'description' => 'Use this setting to change the templates background color',
     'id'  => 'sub-headline-text-color',
     'type'  => 'colorpicker',
     'default'  => 'ffffff',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Content Background Color',
     'description' => 'Use this setting to change the templates background color',
     'id'  => 'content-bg-color',
     'type'  => 'colorpicker',
     'default'  => 'ffffff',
     'context'  => 'normal'
     ),
 array(
     'label' => 'Content Text Color',
     'description' => '',
     'id'  => 'content-text-color',
     'type'  => 'colorpicker',
     'default'  => '333333',
     'context'  => 'normal'
     ),
 array(
   'label' => "Top Nav Menu",
   'description' => "Top Nav Menu",
   'id' => "top-nav-menu",
   'type' => "wysiwyg",
   'default' => '
          <ul class="nav navbar-nav" style="overflow: visible;" >
            <li><a href="">Home</a></li>
            <li><a href="">About</a></li>
            <li><a href="">Blog</a></li>
            <li ><a href="">Contact</a></li>
          </ul>
        ',
   'selector' => ".container .collapse.navbar-collapse.navbar-ex1-collapse",
 ),
 array(
   'label' => "Content Area 1",
   'description' => "Content Area 1",
   'id' => "content-area-1",
   'type' => "wysiwyg",
   'default' => "
            <div id='carousel-example-generic' class='carousel slide'>
              <div class='carousel-inner'>
               <img src='/wp-content/uploads/landing-pages/templates/influence/images/hero.jpeg'>
              </div>
            </div>
            <h1 class=''><small>Welcome to</small><br><span class='brand-name'>Influence Landing Page</span></h1>
          ",
   'selector' => ".box .col-lg-12:eq(0)",
 ),
 array(
   'label' => "Content Area 2",
   'description' => "Content Area 2",
   'id' => "content-area-2",
   'type' => "wysiwyg",
   'default' => '<h2 style="text-align: center;">This is the Headline in Section Two</h2>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.
<p style="text-align: center;">[social_share style="bar" align="horizontal" heading_align="inline" facebook="1" twitter="1" google_plus="1" linkedin="1" pinterest="1" /]</p>',
   'selector' => ".box .col-lg-12:eq(1)",
 ),
 array(
   'label' => "Content Area 3",
   'description' => "Content Area 3",
   'id' => "content-area-3",
   'type' => "wysiwyg",
   'default' => "
            <h2>Beautiful boxes <strong>to showcase your content</strong></h2>

            <p class=''>Use as many boxes as you like, and put anything you want in them! They are great for just about anything, the sky's the limit!</p>
            <p class=''>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc placerat diam quis nisl vestibulum dignissim. In hac habitasse platea dictumst. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
          ",
   'selector' => ".box .col-lg-12:eq(2)",
 ),
 array(
   'label' => "Footer",
   'description' => "Footer",
   'id' => "footer",
   'type' => "textarea",
   'default' => "Copyright © Company 2013",
   'selector' => ".col-lg-12.text-center p",
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
          'default'  => '/wp-content/uploads/landing-pages/templates/influence/images/brick.jpg',
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

      array(
        'label' => 'turn-off-editor',
        'description' => 'Turn off editor',
        'id'  => 'turn-off-editor',
        'type'  => 'custom-css',
        'default'  => '#postdivrich, #lp_2_form_content {display:none !important;}'
        ),
);
