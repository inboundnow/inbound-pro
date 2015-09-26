<?php
/**
 * Template Name: Dropcap
 * @package  WordPress Landing Pages
 * @author   David Wells
 */

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'acf4',
	'version' => "2.0.1",
	'label' => "Dropcap",
	'category' => '1 column layout',
	'demo' => 'http://demo.inboundnow.com/go/dropcap-lander-preview/',
	'description'  => __( 'Create a great looking quote styled landing page' , 'landing-pages' )
);

/* Load ACF definitions for Dropcap */
if( function_exists('register_field_group') ):

    register_field_group(array (
        'key' => 'group_55dcf14c4c7e3',
        'title' => 'Dropcap',
        'fields' => array (
            array (
                'key' => 'field_55de10a366359',
                'label' => __('Main Headline','landing-pages'),
                'name' => 'lp-main-headline',
                'type' => 'text',
                'instructions' => __('Enter in the headline here','landing-pages'),
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
                'key' => 'field_55dcf15e758b0',
                'label' => __( 'Main Content', 'landing-pages' ),
                'name' => 'dropcap-main-content',
                'type' => 'wysiwyg',
                'instructions' => __( 'This is the default content from template.', 'landing-pages' ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

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

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array (
                'key' => 'field_55dcf19d758b1',
                'label' => __( 'Conversion Area', 'landing-pages' ),
                'name' => 'dropcap-conversion-area-content',
                'type' => 'wysiwyg',
                'instructions' => __( 'Place your call to action here.', 'landing-pages' ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array (
                'key' => 'field_55dcf1d4f7702',
                'label' => __( 'Text Color', 'landing-pages' ),
                'name' => 'dropcap-text-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the Text Color', 'landing-pages' ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
            ),
            array (
                'key' => 'field_55dcf213a7269',
                'label' => __( 'Content Background Color', 'landing-pages' ),
                'name' => 'dropcap-content-background',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the Content Area Background Color', 'landing-pages' ),
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
                'key' => 'field_55dcf24aa726a',
                'label' => __( 'Conversion Area Text Color', 'landing-pages' ),
                'name' => 'dropcap-form-text-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the Conversion Area text Color', 'landing-pages' ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
            ),
            array (
                'key' => 'field_55dcf276a726b',
                'label' => __( 'Background Settings', 'landing-pages' ),
                'name' => 'dropcap-background-style',
                'type' => 'select',
                'instructions' => __( 'Set the template\'s background', 'landing-pages' ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'fullscreen' => __( 'Fullscreen Image','landing-pages'),
                    'tile' => __( 'Tile Background Image','landing-pages'),
                    'color' => __( 'Solid Color','landing-pages'),
                    'repeat-x' => __( 'Repeat Image Horizontally','landing-pages'),
                    'repeat-y' => __( 'Repeat Image Vertically','landing-pages'),
                    'custom' => __( 'Custom CSS','landing-pages'),
                ),
                'default_value' => 'fullscreen',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_55dcf31b70c76',
                'label' => __( 'Background Image', 'landing-pages' ),
                'name' => 'dropcap-background-image',
                'type' => 'image',
                'instructions' => __( 'Upload or select a background image from the media library.', 'landing-pages' ),
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
                'default_value' => LANDINGPAGES_URLPATH.'templates/dropcap/assets/images/beach-1.jpg'
            ),
            array (
                'key' => 'field_55dcf36870c77',
                'label' => __( 'Background Color', 'landing-pages' ),
                'name' => 'dropcap-background-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the templates background color', 'landing-pages' ),
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
    ));

endif;