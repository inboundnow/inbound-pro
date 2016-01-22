<?php
/**
 * WordPress Landing Page Config File
 * Template Name:  Countdown Lander Template
 *
 * @package  WordPress Landing Pages
 * @author 	Inbound Now
 */

/* gets template directory name to use as identifier - do not edit - include in all template files */
$key = basename(dirname(__FILE__));

/* sets the default date for the countdown */
$next_month_timestamp = strtotime("+1 month");
$next_month = date('Y-m-d H:i', $next_month_timestamp);

$lp_data[$key]['info'] = array(
	'data_type' => 'acf4',
	'version' => "1.0.5",
	'label' => __( 'Countdown Lander' , 'landing-pages' ),
	'category' => 'Countdown,1 column',
	'demo' => 'http://demo.inboundnow.com/go/countdown-lander/',
	'description'  => __( 'Coundown Lander provides a simple sharp looking countdown page.' , 'landing-pages' )
);

/* register ACF fields */
if( function_exists('register_field_group') ):
	register_field_group(array (
		'key' => 'group_55d38b033048e',
		'title' => 'Countdown Lander',
		'fields' => array (
			array (
				'key' => 'field_55de0ba2f9c9a',
				'label' => __( 'Main Headline','landing-pages' ),
				'name' => 'lp-main-headline', /* legacy support */
				'type' => 'text',
				'instructions' => __( 'This will be the main headline.','landing-pages' ),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55d38b42835ac',
				'label' => __( 'Conversion Area','landing-pages' ),
				'name' => 'countdown-lander-conversion-area-content',
				'type' => 'wysiwyg',
				'instructions' => __( '','landing-pages' ),
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
				'label' => __( 'Main Content','landing-pages' ),
				'name' => 'countdown-lander-main-content',
				'type' => 'wysiwyg',
				'instructions' => __( '','landing-pages' ),
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
				'label' => __( 'Countdown Date','landing-pages' ),
				'name' => 'countdown-lander-date-picker',
				'type' => 'date_time_picker',
				'instructions' => __( 'What date are we counting down to?','landing-pages' ),
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'm/d/Y',
				'return_format' => 'm/d/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_55d39e3a47e90',
				'label' => __( 'Headline Text Color','landing-pages' ),
				'name' => 'countdown-lander-headline-color',
				'type' => 'color_picker',
				'instructions' => __( 'Use this setting to change headline color','landing-pages' ),
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
				'label' => __( 'Other Text Color','landing-pages' ),
				'name' => 'countdown-lander-other-text-color',
				'type' => 'color_picker',
				'instructions' => __( 'Use this setting to change the template\'s text color','landing-pages' ),
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
				'label' => __( 'Submit Button Color','landing-pages' ),
				'name' => 'countdown-lander-submit-button-color',
				'type' => 'color_picker',
				'instructions' => __( 'Use this setting to change the template\'s submit button color.','landing-pages' ),
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
				'label' => __( 'Content Background Color','landing-pages' ),
				'name' => 'countdown-lander-content-background',
				'type' => 'color_picker',
				'instructions' => __( 'Use this setting to change the content area\'s background color','landing-pages' ),
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
				'label' => __( 'Show Transparent Background behind content?','landing-pages' ),
				'name' => 'countdown-lander-background-on',
				'type' => 'radio',
				'instructions' => __( 'Toggle this on to render the transparent background behind your content for better visability','landing-pages' ),
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
				'label' => __( 'Countdown Until... Message','landing-pages' ),
				'name' => 'countdown-lander-countdown-message',
				'type' => 'text',
				'instructions' => __( 'Insert the event you are counting down to.','landing-pages' ),
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
				'label' => __( 'Background Image','landing-pages' ),
				'name' => 'countdown-lander-bg-image',
				'type' => 'image',
				'instructions' => __( 'Enter an URL or upload an image for the background.','landing-pages' ),
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
				'label' => __( 'Display Social Media Share Buttons','landing-pages' ),
				'name' => 'countdown-lander-display-social',
				'type' => 'radio',
				'instructions' => __( 'Toggle social sharing on and off','landing-pages' ),
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
		'options' => array(),
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