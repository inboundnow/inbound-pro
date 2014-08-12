<?php
/**
* Template Name: fade
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
    'label' => 'fade',
    'category' => 'custom',
    'demo' => '',
    'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
 array(
   'label' => "Default Content",
   'description' => "This is the default content from template.",
   'id' => "default-content",
   'type' => "default-content",
   'default' => '
 <h1 data-eq-selector="body p:eq(0)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(0)">Sub-headline goes in here</h1>
 <p data-eq-selector="body p:eq(0)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(0)">Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Aenean lacinia bibendum nulla sed consectetur. Sed posuere consectetur est at lobortis. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
 [list icon="check-circle-o" font_size="24" icon_color="#16b33d" text_color="#000000" columns="3" bottom_margin="10"]
 <ul>
  <li>List item</li>
  <li>List item</li>
  <li>List item</li>
  <li>List item</li>
 <li>List item</li>
  <li>List item</li>
  <li>List item</li>
  <li>List item</li>
 <li>List item</li>
  <li>List item</li>
  <li>List item</li>
  <li>List item</li>
 </ul>
 [/list]
 <h2>Sub-sub headline goes in here</h2>
 <p data-eq-selector="body p:eq(1)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(1)">Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
 <p data-eq-selector="body p:eq(2)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(2)">Sed posuere consectetur est at lobortis. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Sed posuere consectetur est at lobortis. Maecenas sed diam eget risus varius blandit sit amet non magna. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
 <p style="text-align: center;" data-eq-selector="body p:eq(1)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(1)">[button font_size="40" color="#c8232b" text_color="#ffffff" icon="check-circle" url="http://www.inboundnow.com" target="_self"]Download This Now[/button]</p>
 <p data-eq-selector="body p:eq(3)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(3)">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec id elit non mi porta gravida at eget metus. Nullam quis risus eget urna mollis ornare vel eu leo. Etiam porta sem malesuada magna mollis euismod.</p>
 <p data-eq-selector="body p:eq(4)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(4)">Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Aenean lacinia bibendum nulla sed consectetur. Sed posuere consectetur est at lobortis. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
 <p data-eq-selector="body p:eq(5)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(5)">Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
 <p data-eq-selector="body p:eq(5)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(5)">[inbound_forms id="default_1" name="First, Last, Email Form"]</p>
 <p data-eq-selector="body p:eq(6)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(6)">Sed posuere consectetur est at lobortis. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Sed posuere consectetur est at lobortis. Maecenas sed diam eget risus varius blandit sit amet non magna. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
 <p data-eq-selector="body p:eq(7)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(7)">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec id elit non mi porta gravida at eget metus. Nullam quis risus eget urna mollis ornare vel eu leo. Etiam porta sem malesuada magna mollis euismod.</p>
 <p style="text-align: center;" data-eq-selector="body p:eq(1)" data-count-size="8" data-css-selector="body p" data-js-selector="body p:eq(1)">[button font_size="40" color="#c8232b" text_color="#ffffff" icon="check-circle" url="http://www.inboundnow.com" target="_self"]Download This Now[/button]</p>
',
 ),
array(
    'label' => 'Headline Color',
    'description' => 'Use this setting to change the templates background color',
    'id'  => 'headline-color',
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
          'default'  => '/wp-content/uploads/landing-pages/templates/fade/images/bg2.jpg',
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
        'default'  => '#lp_2_form_content {display:none !important;}'
        ),
);
