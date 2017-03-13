<?php
/**
* Template Name: Simple-Solid
* @package	WordPress Landing Pages
* @author	Inbound Template Generator!
* WordPress Landing Page Config File
*/

$key = basename(dirname(__FILE__));

$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/';

/* Configures Template Information */
$lp_data[$key]['info'] = array(
	'data_type' => 'acf4',
	'version' => '2.0.0',
	'label' => 'Simple Solid Lite',
	'category' => '1 Column',
	'demo' => 'http://demo.inboundnow.com/go/simple/',
	'description' => __( 'This is a free template provided by Inbound Now' , 'inbound-pro' )
);

/* Configures template ACF fields here */
if( function_exists('register_field_group') ):

	register_field_group(array (
		'key' => 'group_55df73a9053d1',
		'title' => 'Simple Solid Lite',
		'fields' => array (
			array (
				'key' => 'field_55df6384rh9',
				'label' => __('Main Headline','landing-pages'),
				'name' => 'lp-main-headline',
				'type' => 'text',
				'instructions' => __('Insert the main template headline here.','landing-pages'),
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
				'key' => 'field_55df74363ffr3ccd',
				'label' => 'Main Headline Color',
				'name' => 'main-headline-color',
				'type' => 'color_picker',
				'instructions' => 'Font color of headline',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#fff',
			),
			array (
				'key' => 'field_55df73bb3fb9a',
				'label' => 'Main Content',
				'name' => 'simple-solid-lite-main-content',
				'type' => 'wysiwyg',
				'instructions' => 'Insert your main content here.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

<strong>In this guide you will learn:</strong>

<ul>
	<li>This list was created with the list icon shortcode.</li>
	<li>Click on the power icon in your editor to customize your own</li>
	<li>Explain why users will want to fill out the form</li>
	<li>Keep it short and sweet.</li>
	<li>This list should be easily scannable</li>
</ul>

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>',
				'tabs' => 'all',
				'toolbar' => 'full'
			),
			array (
				'key' => 'field_55df73e03fb9b',
				'label' => 'Conversion Area',
				'name' => 'simple-solid-lite-conversion-area-content',
				'type' => 'wysiwyg',
				'instructions' => 'Insert a call to action or Inbound form here. ',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'tabs' => 'all',
				'toolbar' => 'full'
			),
			array (
				'key' => 'field_55df73f73fb9c',
				'label' => 'Top Bar',
				'name' => 'simple-solid-lite-header-display',
				'type' => 'select',
				'instructions' => 'Hide/reveal the top bar',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'off' => 'Hide',
					'on' => 'Show',
				),
				'default_value' => 'on',
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55df74123fb9d',
				'label' => 'Logo',
				'name' => 'simple-solid-lite-logo',
				'type' => 'image',
				'instructions' => 'Upload your logo here',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'default_value' => $path . "/images/inbound-logo.png",
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
				'key' => 'field_55df74203fb9e',
				'label' => 'Top Right Area',
				'name' => 'simple-solid-lite-social-media-options',
				'type' => 'wysiwyg',
				'instructions' => 'Insert your social media shortcode/snippet here. We provide one out of the box for you but you are welcome to change it.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => 'wpautop',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55df74363fb9f',
				'label' => 'Submit Button Color',
				'name' => 'simple-solid-lite-submit-color',
				'type' => 'color_picker',
				'instructions' => 'Background color of the submit bar',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#27ae60',
			),
			array (
				'key' => 'field_55df75043fba0',
				'label' => 'Footer Bar',
				'name' => 'simple-solid-lite-footer-display',
				'type' => 'select',
				'instructions' => 'Hide/reveal the footer bar',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'off' => 'Hide',
					'on' => 'Show',
				),
				'default_value' => 'on',
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55df75153fba1',
				'label' => 'Copyright Text',
				'name' => 'simple-solid-lite-copyright-text',
				'type' => 'text',
				'instructions' => 'Set the copyright text here',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '2013 Your Company | All Right Reserved',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55df75263fba2',
				'label' => 'Background Setting',
				'name' => 'simple-solid-lite-background-style',
				'type' => 'select',
				'instructions' => 'Set the template\'s background',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'fullscreen' => 'Fullscreen Image',
					'tile' => 'Tile Background Image',
					'color' => 'Solid Color',
					'repeat-x' => 'Repeat Image Horizontally',
					'repeat-y' => 'Repeat Image Vertically',
					'custom' => 'Custom CSS',
				),
				'default_value' => 'color',
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_55df753a3fba3',
				'label' => 'Background Image',
				'name' => 'simple-solid-lite-background-image',
				'type' => 'image',
				'instructions' => 'Enter an URL or upload an image for the banner.',
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
				'key' => 'field_55df754c3fba4',
				'label' => 'Background Color',
				'name' => 'simple-solid-lite-background-color',
				'type' => 'color_picker',
				'instructions' => 'Use this setting to change the templates background color',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#186d6d',
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
