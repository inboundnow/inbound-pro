<?php
/**
* Template Name: Simple-Solid
* @package	WordPress Landing Pages
* @author	Inbound Template Generator!
* WordPress Landing Page Config File
*/
do_action('lp_global_config');
$key = lp_get_parent_directory(dirname(__FILE__));

$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/';

/* Configures Template Information */
$lp_data[$key]['info'] = array(
	'data_type' => 'template',
	'version' => '1.0',
	'label' => 'Simple Solid Lite',
	'category' => '1 Column',
	'demo' => 'http://demo.inboundnow.com/go/simple/',
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
			'description' => __( 'Place your call to action here.' , 'landing-pages' ),
			'id' => "conversion-area-content",
			'type' => "wysiwyg",
			'default' => ''
	),		
	array(
		'label' => __( 'Top Bar' , 'landing-pages' ),
		'description' => __( 'Hide/Reveal the top bar.' , 'landing-pages' ),
		'id'	=> 'header-display',
		'type'	=> 'radio',
		'default'	=> 'on',
		'context'	=> 'normal',
		'options'	=> array(
			'off' => __( 'Hide' , 'landing-pages' ),
			'on' => __( 'Show' , 'landing-pages' )			
		)
	),
	array(
		'label' => __( 'Logo' , 'landing-pages' ),
		'description' => __( 'Logo' , 'landing-pages' ),
		'id' => "logo",
		'type' => "media",
		'default' => $path . "/images/inbound-logo.png",
		'selector' => ".logo a",
	),
	array(
		'label' => __( 'Top Right Area' , 'landing-pages' ),
		'description' => "",
		'id' => "social-media-options",
		'type' => "textarea",
		'default' => '[social_share style="bar" align="horizontal" heading_align="inline" heading="" facebook="1" twitter="1" google_plus="1" linkedin="1" pinterest="0" /]',
		'selector' => ".inner .network",
	 ),
	 array(
		'label' => __( 'Submit Button Color' , 'landing-pages' ),
		'description' => '',
		'id'	=> 'submit-color',
		'type'	=> 'colorpicker',
		'default'	=> '27ae60',
		'context'	=> 'normal'
	),		
	array(
		'label' => __( 'Footer Bar' , 'landing-pages' ),
		'description' => __( 'Hide/Reveal the footer bar.' , 'landing-pages' ),
		'id'	=> 'footer-display',
		'type'	=> 'radio',
		'default'	=> 'on',
		'context'	=> 'normal',
		'options'	=> array(
			'off' => __( 'Hide' , 'landing-pages' ),
			'on' => __( 'Show' , 'landing-pages' )			
		)
	),
	array(
		'label' => __( 'Copyright Text' , 'landing-pages' ),
		'description' => __( 'Copyright Text' , 'landing-pages' ),
		'id' => "copyright-text",
		'type' => "text",
		'default' => __( '© 2013 Your Company | All Right Reserved' , 'landing-pages' ),
		'selector' => ".cf.container .foot-left",
	),
	array(
		'label' => __( 'Background Settings' , 'landing-pages' ),
		'description' => __( 'Set the template\'s background' , 'landing-pages' ),
		'id'	=> 'background-style',
		'type'	=> 'dropdown',
		'default'	=> 'color',
		'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
		'context'	=> 'normal'
	),
	array(
		'label' => __( 'Background Image', 'landing-pages' ),
		'description' => __( 'Enter an URL or upload an image for the banner.' , 'landing-pages' ),
		'id'	=> 'background-image',
		'type'	=> 'media',
		'default'	=> '',
		'context'	=> 'normal'
	),
	array(
		'label' => __( 'Background Color', 'landing-pages' ),
		'description' => __( 'Use this setting to change the templates background color' , 'landing-pages' ),
		'id'	=> 'background-color',
		'type'	=> 'colorpicker',
		'default'	=> '186d6d',
		'context'	=> 'normal'
		)
	);
