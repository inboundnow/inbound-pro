<?php
/**
* WordPress Landing Page Config File
* Template Name:  Countdown Lander Template
*
* @package  WordPress Landing Pages
* @author 	David Wells, Hudson Atwell
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.0.5", // Version Number
	'label' => "Countdown Lander", // Nice Name
	'category' => 'Countdown, v1, 1 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/countdown-lander/', // Demo Link
	'description'  => 'Coundown Lander provides a simple sharp looking countdown page.' // template description
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
           'label' => __( 'Conversion Area' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
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
        'label' => 'Countdown Date', // Name of field
        'description' => "What date are we counting down to?", // what field does
        'id' => 'date-picker', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'datepicker', // metafield type
        'default'  => '2015-12-31 13:00', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
    array(
        'label' => 'Headline Text Color',
        'description' => "Use this setting to change headline color",
        'id'  => 'headline-color',
        'type'  => 'colorpicker',
        'default'  => 'FFFFFF',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Other Text Color',
        'description' => "Use this setting to change the template's text color",
        'id'  => 'other-text-color',
        'type'  => 'colorpicker',
        'default'  => 'FFFFFF',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Submit Button Color',
        'description' => "Use this setting to change the template's submit button color.",
        'id'  => 'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => '5baa1e',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Content Background Color',
        'description' => "Use this setting to change the content area's background color",
        'id'  => 'content-background',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
    array(
        'label' => 'Show Transparent Background behind content?',
        'description' => "Toggle this on to render the transparent background behind your content for better visability",
        'id'  => 'background-on',
        'type'  => 'radio',
        'default'  => 'on',
		'options' => array('on' => 'on','off'=>'off'),
        'context'  => 'normal'
        ),
     array(
        'label' => 'Countdown Until... Message',
        'description' => "Insert the event you are counting down to.",
        'id'  => 'countdown-message',
        'type'  => 'text',
        'default'  => 'Countdown Until... Message',
        'context'  => 'normal'
        ),
     array(
        'label' => 'Background Image',
        'description' => "Enter an URL or upload an image for the background.",
        'id'  => 'bg-image',
        'type'  => 'media',
        'default'  => '',
        'context'  => 'normal'
        ),
     array(
        'label' => 'Display Social Media Share Buttons',
        'description' => "Toggle social sharing on and off",
        'id'  => 'display-social',
        'type'  => 'radio',
        'default'  => '1',
		'options' => array('1' => 'on','0'=>'off'),
        'context'  => 'normal'
        )
    );