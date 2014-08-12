<?php
/**
* Template Name: captivate
* @package	WordPress Landing Pages
* @author	Inbound Template Generator!
* WordPress Landing Page Config File
*/
do_action('lp_global_config');
$key = lp_get_parent_directory(dirname(__FILE__));


/* Configures Template Information */
$lp_data[$key]['info'] = array(
	'data_type' => 'template',
	'version' => '2.0.0',
	'label' => 'Captivate',
	'category' => 'video, responsive',
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
	array( 'label' => 'Show Top Content Area',
		'description' => 'shows or hides the top content area',
		'id'	=> 'show_top',
		'type'	=> 'dropdown',
		'default'	=> 'on',
		'options' => array('on'=>'On', 'off'=>'Off'),
		'context'	=> 'normal'
		),
	array(
		'label' => 'Top Content Area',
		'description' => 'Top Area for Video or Additional Copy',
		'id'	=> 'top-area',
		'type'	=> 'wysiwyg',
		'default'	=> '<iframe src="http://www.youtube.com/embed/BzcD6PgvLP4?list=UUCqiE-EcfDjaKGXSxtegcyg" height="360" width="640" allowfullscreen="" frameborder="0"></iframe>',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Content Background Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'content-background-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Content Text Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'content-text-color',
		'type'	=> 'colorpicker',
		'default'	=> '404040',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Submit Button Background Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'submit-bg',
		'type'	=> 'colorpicker',
		'default'	=> '1A95BC',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Submit Button text Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'submit-text',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
	'label' => 'Background Settings',
		'description' => 'Set the template\'s background',
		'id'	=> 'background-style',
		'type'	=> 'dropdown',
		'default'	=> 'color',
		'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Image',
		'description' => 'Enter an URL or upload an image for the banner.',
		'id'	=> 'background-image',
		'type'	=> 'media',
		'default'	=> 'http://lorempixel.com/1400/800/',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Background Color',
		'description' => 'Use this setting to change the templates background color',
		'id'	=> 'background-color',
		'type'	=> 'colorpicker',
		'default'	=> '1A95BC',
		'context'	=> 'normal'
		),
);
