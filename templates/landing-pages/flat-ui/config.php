<?php
/**
* Template Name: flat-ui
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
    'label' => 'flat-ui',
    'category' => 'custom',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
  array(
      'label' => 'Header Image',
      'description' => "Enter an URL or upload an image for the banner. 1400px by 560px",
      'id'  => 'header-image',
      'type'  => 'media',
      'default'  => 'http://lorempixel.com/1400/560/',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Sub-headline',
      'description' => "",
      'id'  => 'subheadline',
      'type'  => 'textarea',
      'default'  => 'The subtitle for the main text blurb',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Main Content',
      'description' => "",
      'id'  => 'main-content',
      'type'  => 'wysiwyg',
      'default'  => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
      <a href="" class="button button-cta" data-eq-selector=".blank.blurb .button.button-cta:eq(0)" data-count-size="1" data-css-selector=".blank.blurb .button.button-cta" data-js-selector=".blank.blurb .button.button-cta">Call To Action! Buy our stuff!</a>
      ',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Middle Left Content',
      'description' => "",
      'id'  => 'middle-left-content',
      'type'  => 'wysiwyg',
      'default'  => '<img src="http://www.fillmurray.com/500/300/" alt="Image" class="" data-eq-selector=".item-featured img:eq(0)" data-count-size="1" data-css-selector=".item-featured img" data-js-selector=".item-featured img" data-old="">',
      'context'  => 'normal'
      ),
  array(
      'label' => 'Middle right Content',
      'description' => "",
      'id'  => 'middle-right-content',
      'type'  => 'wysiwyg',
      'default'  => '<div class="item-info" data-eq-selector=".item-featured .item-info:eq(0)" data-count-size="1" data-css-selector=".item-featured .item-info" data-js-selector=".item-featured .item-info">
        <h1 class="">Item title</h1>
        <p class="">What the hell is this? Get out of town, I didn\'t know you did anything creative. Ah, let me read some.</p>
        <span class="price">£49.99</span>
        <a href="" class="button button-buy">Get it now</a>
      </div>',
      'context'  => 'normal'
      ),

  array(
      'label' => 'Bottom Left Content',
      'description' => "",
      'id'  => 'bottom-left-content',
      'type'  => 'wysiwyg',
      'default'  => "
      <h1 class=''>Simple Title</h1>
      <h2 class=''>This is a simple subtitle</h2>
      <img src='http://lorempixel.com/500/250/' alt='Image' class=''>
      <p class=''>What the hell is this? Get out of town, I didn't know you did anything creative. Ah, let me read some. Yeah, but you're uh, you're so, you're so thin. Hey Biff, check out this guy's life preserver, dork thinks he's gonna drown. Doc, you gotta help me. you were the only one who knows how your time machine works.</p>
      <a href='#' class='button-read-more'>Read more »</a>
    ",
      'context'  => 'normal'
      ),
  array(
      'label' => 'Bottom right Content',
      'description' => "",
      'id'  => 'bottom-right-content',
      'type'  => 'wysiwyg',
      'default'  => "
           <h1 class=''>Simple Title</h1>
           <h2 class=''>This is a simple subtitle</h2>
           <img src='http://lorempixel.com/500/250/' alt='Image' class=''>
           <p class=''>What the hell is this? Get out of town, I didn't know you did anything creative. Ah, let me read some. Yeah, but you're uh, you're so, you're so thin. Hey Biff, check out this guy's life preserver, dork thinks he's gonna drown. Doc, you gotta help me. you were the only one who knows how your time machine works.</p>
           <a href='#' class='button-read-more'>Read more »</a>
         ",
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
          'default'  => 'on',
          'context'  => 'normal'
          ),
      array(
          'label' => 'Background Color',
          'description' => 'Use this setting to change the templates background color',
          'id'  => 'background-color',
          'type'  => 'colorpicker',
          'default'  => 'cccccc',
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
