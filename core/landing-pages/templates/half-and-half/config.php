<?php
/**
* Template Name:  Half and Half Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'acf',
	'version' => "2.0.1",
	'label' => "Half and Half",
	'category' => '2 column',
	'demo' => 'http://demo.inboundnow.com/go/half-and-half-lander-preview/',
	'description'  => __('Half and Half is a template with two content areas on each side of the page. One side has your conversion area and the other your content on the page.','landing-pages')
);

/* define ACF field definitions */
if( function_exists('register_field_group') ):

    register_field_group(array (
        'key' => 'group_55df63aeca586',
        'title' => 'Half & Half',
        'fields' => array (
            array (
                'key' => 'field_55df63c006cb5',
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
                'key' => 'field_55df640806cb6',
                'label' => __('Main Content','landing-pages'),
                'name' => 'half-and-half-main-content',
                'type' => 'wysiwyg',
                'instructions' => __('Inset main template content here','landing-pages'),
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
                'key' => 'field_55df643406cb8',
                'label' => __('Conversion Area','landing-pages'),
                'name' => 'half-and-half-conversion-area-content',
                'type' => 'wysiwyg',
                'instructions' => __('Inset your call to action or inbound form shortcode here. ','landing-pages'),
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
                'key' => 'field_55df647006cb9',
                'label' => __('Display Social Media Buttons','landing-pages'),
                'name' => 'half-and-half-display-social',
                'type' => 'radio',
                'instructions' => __('Setting this to on will render the social share icons.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    1 => 'on',
                    0 => 'off',
                ),
                'other_choice' => 0,
                'save_other_choice' => 0,
                'default_value' => 1,
                'layout' => 'vertical',
            ),
            array (
                'key' => 'field_55df64f77d091',
                'label' => __('Page Layout','landing-pages'),
                'name' => 'half-and-half-sidebar',
                'type' => 'select',
                'instructions' => __('Determines which side the set the conversion area sidebar on.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'right' => 'Call to Action on right',
                    'left' => 'Call to Action on left',
                ),
                'default_value' => 'left',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_55df65617d092',
                'label' => __('Content Background Color','landing-pages'),
                'name' => 'half-and-half-content-color',
                'type' => 'color_picker',
                'instructions' => __('Set the content background color','landing-pages'),
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
                'key' => 'field_55df658a7d093',
                'label' => __('Content Text Color','landing-pages'),
                'name' => 'half-and-half-content-text-color',
                'type' => 'color_picker',
                'instructions' => __('Content text color','landing-pages'),
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
                'key' => 'field_55df66937d094',
                'label' => __('Conversion Area Background Color','landing-pages'),
                'name' => 'half-and-half-sidebar-color',
                'type' => 'color_picker',
                'instructions' => __('Sidebar / conversion area background color','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#EE6E4C',
            ),
            array (
                'key' => 'field_55df66c47d095',
                'label' => __('Submit Button Background Color','landing-pages'),
                'name' => 'half-and-half-submit-button-color',
                'type' => 'color_picker',
                'instructions' => __('The background color of the submit button.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#38A6F0',
            ),
            array (
                'key' => 'field_55df67147d096',
                'label' => __('Conversion Area Text Color','landing-pages'),
                'name' => 'half-and-half-sidebar-text-color',
                'type' => 'color_picker',
                'instructions' => __('The font color of the sidebar/conversion area.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
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