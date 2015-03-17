<?php
/**
* Template Name:  Svtle Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // global config action hook

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

$lp_data[$key]['info'] = array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.1", // Version Number
	'label' => "Svbtle", // Nice Name
	'category' => 'v1, 2 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/sbvtle-lander-preview/', // Demo Link
	'description'  => 'Clean and minimalistic design for a straight forward conversion page.' // template description
);

// Define Meta Options for template
// These values are returned in the template's index.php file with lp_get_value($post, $key, 'field-id') function
$lp_data[$key]['settings'] =
array(
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

[list icon="check" font_size="16" icon_color="#00a319" text_color="" bottom_margin="10"]
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
           'label' => __( 'Conversion Area' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
    array(
        'label' => __( 'Display Social Media Share Buttons', 'landing-pages' ), // Label of field
        'description' => __( 'Display Social Media Share Buttons' , 'landing-pages' ), // field description
        'id' => 'display-social', // metakey.
        'type'  => 'radio', // text metafield type
        'default'  => '1', // default content
        'options' => array('1' => 'on','0'=>'off'),
        'context'  => 'normal' // Context in screen for organizing options
        ),
    array(
        'label' => __( 'Sidebar Layout' , 'landing-pages' ),
        'description' => __( 'Align sidebar to the left or the right' , 'landing-pages' ),
        'id'  => 'sidebar',
        'type'  => 'dropdown',
        'default'  => 'left',
        'options' => array('left'=> __( 'Sidebar on left' , 'landing-pages' ) , 'right' => __( 'Sidebar on right' , 'landing-pages' ) ),
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Submit Button Background Color' , 'landing-pages' ),
        'description' => __( 'Submit Button Background Color' , 'landing-pages' ) ,
        'id'  =>'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => '5baa1e',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Logo Image' , 'landing-pages') ,
        'description' => __('Upload Your Logo (300x110px)' , 'landing-pages') ,
        'id'  => 'logo',
        'type'  => 'media',
        'default'  => '/wp-content/plugins/landing-pages/templates/svtle/assets/images/inbound-logo.png',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Content Area Background Color' , 'landing-pages') ,
        'description' => __( 'Content Area Background Color' , 'landing-pages') ,
        'id'  => 'body-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Content Area Text Color' , 'landing-pages') ,
        'description' => __( 'Use this setting to change the template\'s text color' , 'landing-pages') ,
        'id'  => 'page-text-color',
        'type'  => 'colorpicker',
        'default'  => '4D4D4D',
        'context'  => 'normal'
        ),
	array(
        'label' => __( 'Sidebar color' , 'landing-pages') ,
        'description' => __( 'Use this setting to change the template\'s sidebar color' , 'landing-pages') ,
        'id'  => 'sidebar-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
	array(
        'label' => __( 'Sidebar Text Color' , 'landing-pages') ,
        'description' => __( 'Use this setting to change the template\'s sidebar text color' , 'landing-pages') ,
        'id'  => 'sidebar-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
  	array(
        'label' => __( 'Header Color' , 'landing-pages') ,
        'description' => __( 'Use this setting to change the template\'s header color' , 'landing-pages') ,
        'id'  => 'header-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
   array(
        'label' => __( 'Display form below content on mobile?' , 'landing-pages') ,
        'description' => __( 'Toggle this on to render the form below the content in the mobile view' , 'landing-pages') ,
        'id'  => 'mobile-form',
        'type'  => 'radio',
        'default'  => 'off',
        'options' => array('on' => 'on','off'=>'off'),
        'context'  => 'normal'
        )
    );