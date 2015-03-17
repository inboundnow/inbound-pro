<?php
/**
* WordPress Landing Page Config File
* Template Name:	Tubelar Template
*
* @package	WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

//adds template data to global array for use with landing page plugin - edit theme category and description only.

//EDIT - START - defines template information - helps categorizae template and provides additional popup information
$lp_data[$key]['category'] = "Video";
$lp_data[$key]['description'] = "Tubelar Template";
$lp_data[$key]['version'] = "1.0.1";
$lp_data[$key]['thumbnail'] = LANDINGPAGES_URLPATH.'templates/'.$key.'/thumbnail.png';

//DO NOT EDIT - adds template to template selection dropdown
$lp_data[$key]['value'] = $key;
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key));


//************************************************
// Add User Options to Your Landing Page Template
//************************************************

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.0.5", // Version Number
	'label' => "Countdown Lander", // Nice Name
	'category' => 'Countdown, v1, 1 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/countdown-lander/', // Demo Link
	'description'	=> 'Coundown Lander provides a simple sharp looking countdown page.' // template description
);

// Define Meta Options for template
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
		'label' => __( 'YouTube Background Video URL' , 'landing-pages') , // Name of field
		'description' => "Paste in the URL of the YouTube Video here", // what field does
		'id' => 'yt-video', // metakey. $key Prefix is appended from parent in array loop
		'type'	=> 'text', // metafield type
		'default'	=> 'http://www.youtube.com/watch?v=_OBlgSz8sSM', // default content
		'context'	=> 'normal' // Context in screen (advanced layouts in future)
		),
	array(
		'label' => 'Sidebar Layout',
		'description' => __( 'Align sidebar to the right or the left.' , 'landing-pages' ),
		'id'	=> 'sidebar',
		'type'	=> 'dropdown',
		'default'	=> 'lp_right',
		'context'	=> 'normal',
		'options' => array('lp_right'=>'Sidebar on right','lp_left'=>'Sidebar on left'),
		),
	array(
		'label' => __( 'Text Color' , 'landing-pages' ),
		'description' => __( 'Use this setting to change the content area\'s background color' , 'landing-pages' ),
		'id'	=> 'text-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Content Background Color',
		'description' => "Use this setting to change the template's submit button color.",
		'id'	=> 'box-color',
		'type'	=> 'colorpicker',
		'default'	=> '000000',
		'context'	=> 'normal'
		),
	array(
		'label' => __('Background Color Settings' , 'landing-pages' ),
		'description' => __ ('Use this setting to change the content area\'s background color' , 'landing-pages' ),
		'id'	=> 'clear-bg-settings',
		'type'	=> 'dropdown',
		'default'	=> 'clear-bg-settings',
		'context'	=> 'normal',
		'options' => array( 'transparent' => __( 'Transparent Background' , 'landing-pages') , 'solid' => __( 'Solid' , 'landing-pages' ) ),
		),
	array(
		'label' => __( 'Logo Image' , 'landing-pages' ),
		'description' => __( 'Upload your logo (300px x 110px) ' , 'landing-pages' ),
		'id'	=> 'logo',
		'type'	=> 'media',
		'default'	=> '/wp-content/plugins/landing-pages/templates/tubelar/assets/img/inbound-now-logo.png',
		'context'	=> 'normal'
		),
	array(
		'label' => __( 'Display Social Media Share Buttons' , 'landing-pages' ),
		'description' => __( 'Toggle social sharing on and off' , 'landing-pages' ) ,
		'id'	=> 'display-social',
		'type'	=> 'radio',
		'default'	=> '1',
		'context'	=> 'normal',
		'options' => array('1' => 'on','0'=>'off')
		),
	array(
		'label' => __( 'Show Play Controls' , 'landing-pages' ),
		'description' => __( 'Toggle display of background video controls on or off.' , 'landing-pages' ) ,
		'id'	=> 'controls',
		'type'	=> 'radio',
		'default'	=> '1',
		'context'	=> 'normal',
		'options' => array('1' => 'on','0'=>'off')
		)
	);
