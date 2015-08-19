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

//sets the default date for the countdown
$next_month_timestamp = strtotime("+1 month");
$next_month = date('Y-m-d H:i', $next_month_timestamp);

$lp_data[$key]['info'] = array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.0.5", // Version Number
	'label' => __( 'Countdown Lander' , 'landing-pages' ), // Nice Name
	'category' => 'Countdown, v1, 1 column layout', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/countdown-lander/', // Demo Link
	'description'  => __( 'Coundown Lander provides a simple sharp looking countdown page.' , 'landing-pages' ), // template description
	'acf' => true
);

/* disables editor */
$lp_data[$key]['settings'] = array(
	array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
	),
	array(
		'label' => 'Instructions', /* Turns off main content */
		'description' => __( 'If changing to this template from another template, save the landing page and after the refresh the page will display the template settings.' , 'landing-pages' ),
		'id'	=> 'instructions',
		'type'	=> 'description-block',
		'default'	=> 'test'
	)
);

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_55d38b033048e',
	'title' => 'Countdown Lander',
	'fields' => array (
		array (
			'key' => 'field_55d38b42835ac',
			'label' => 'Conversion Area',
			'name' => 'countdown-lander-conversion-area-content',
			'type' => 'wysiwyg',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 'Content',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55d38bff835ad',
			'label' => 'Main Content',
			'name' => 'main-content',
			'type' => 'wysiwyg',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

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

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_55d38c97835ae',
			'label' => 'Countdown Date',
			'name' => 'countdown-lander-date-picker',
			'type' => 'date_picker',
			'instructions' => 'What date are we counting down to?',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'display_format' => 'd/m/Y',
			'return_format' => 'd/m/Y',
			'first_day' => 1,
		),
		array (
			'key' => 'field_55d39e3a47e90',
			'label' => 'Headline Text Color',
			'name' => 'countdown-lander-headline-color',
			'type' => 'color_picker',
			'instructions' => 'Use this setting to change headline color',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#FFFFFF',
		),
		array (
			'key' => 'field_55d39e7347e91',
			'label' => 'Other Text Color',
			'name' => 'countdown-lander-other-text-color',
			'type' => 'color_picker',
			'instructions' => 'Use this setting to change the template\'s text color',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#FFFFFF',
		),
		array (
			'key' => 'field_55d39fde47e92',
			'label' => 'Submit Button Color',
			'name' => 'countdown-lander-submit-button-color',
			'type' => 'color_picker',
			'instructions' => 'Use this setting to change the template\'s submit button color.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#5baa1e',
		),
		array (
			'key' => 'field_55d3a01347e93',
			'label' => 'Content Background Color',
			'name' => 'countdown-lander-content-background',
			'type' => 'color_picker',
			'instructions' => 'Use this setting to change the content area\'s background color',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#000000',
		),
		array (
			'key' => 'field_55d3a04d47e94',
			'label' => 'Show Transparent Background behind content?',
			'name' => 'countdown-lander-background-on',
			'type' => 'radio',
			'instructions' => 'Toggle this on to render the transparent background behind your content for better visability',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'on' => 'On',
				'off' => 'Off',
			),
			'other_choice' => 0,
			'save_other_choice' => 0,
			'default_value' => '',
			'layout' => 'vertical',
		),
		array (
			'key' => 'field_55d3a09e47e95',
			'label' => 'Countdown Until... Message',
			'name' => 'countdown-lander-countdown-message',
			'type' => 'text',
			'instructions' => 'Insert the event you are counting down to.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 'Countdown Until... Message',
			'placeholder' => 'Countdown Until... Message',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_55d3a0e947e96',
			'label' => 'Background Image',
			'name' => 'countdown-lander-bg-image',
			'type' => 'image',
			'instructions' => 'Enter an URL or upload an image for the background.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
		array (
			'key' => 'field_55d3a14b47e97',
			'label' => 'Display Social Media Share Buttons',
			'name' => 'countdown-lander-display-social',
			'type' => 'radio',
			'instructions' => 'Toggle social sharing on and off',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				1 => 'On',
				0 => 'Off',
			),
			'other_choice' => 0,
			'save_other_choice' => 0,
			'default_value' => '1',
			'layout' => 'vertical',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'landing-page',
			),
			array (
				'param' => 'template_id',
				'operator' => '==',
				'value' => $key,
			)
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;


/**
*  Enqueue JS & CSS
*/
function lp_countdown_lander_enqueue_scripts() {
	global $post;
	if ( isset($post) && $post->post_type != 'landing-page' ) {
		return;
	}

	/* Get file locations */
	$key = basename(dirname(__FILE__));
	$url_path = LANDINGPAGES_URLPATH.'templates/'.$key.'/';


}
//add_action('admin_enqueue_scripts' , 'lp_countdown_lander_enqueue_scripts');