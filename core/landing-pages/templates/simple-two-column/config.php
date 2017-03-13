<?php
/**
* WordPress Landing Page Config File
* Template Name:  Simple Two Column Template
*
* @package  WordPress Landing Pages
* @author 	Inbound Now
*/


//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'acf4',
	'version' => "1.0.1",
	'label' => "Simple Two Column",
	'category' => '2 column',
	'demo' => 'http://demo.inboundnow.com/go/simple-two-column/',
	'description'  => 'Two column landing page template.'
);

/* ACF field definitions here */
if( function_exists('register_field_group') ):

    register_field_group(array (
        'key' => 'group_55df8e583b9c6',
        'title' => 'Simple Two Column',
        'fields' => array (
            array (
                'key' => 'field_55df8e622aa25',
                'label' => __( 'Main Headline','landing-pages'),
                'name' => 'lp-main-headline',
                'type' => 'text',
                'instructions' => __( 'This input holds the template\'s main headline','landing-pages'),
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
                'key' => 'field_55df8ecb2aa26',
                'label' => __( 'Main Content','landing-pages'),
                'name' => 'simple-two-column-main-content',
                'type' => 'wysiwyg',
                'instructions' => __( 'This input holds the template\'s main content.','landing-pages'),
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
                'key' => 'field_55df9ba8c7ea9',
                'label' => __('Conversion Area Content','landing-pges'),
                'name' => 'simple-two-column-conversion-area-content',
                'type' => 'wysiwyg',
                'instructions' => __('Input your call to action / Inbound form shortcodes here. ','landing-pages'),
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
                'key' => 'field_55df8eee2aa27',
                'label' => __( 'Display Social Media Share Buttons','landing-pages'),
                'name' => 'simple-two-column-display-social',
                'type' => 'select',
                'instructions' => __( 'Select to reveal or to hide social media share buttons.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    1 => __('on','landing-pages'),
                    0 => __('off','landing-pages'),
                ),
                'default_value' => 1,
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_55df8f032aa28',
                'label' => __( 'Sidebar Layout','landing-pages'),
                'name' => 'simple-two-column-sidebar',
                'type' => 'select',
                'instructions' => __( 'Align sidebar to the left or the right','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'right' => __('Sidebar on right','landing-pages'),
                    'left' => __('Sidebar on left','landing-pages'),
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
                'key' => 'field_55df8f162aa29',
                'label' => __( 'Submit Button Color','landing-pages'),
                'name' => 'simple-two-column-submit-button-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the background color of the submit button.','landing-pages'),
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
                'key' => 'field_55df8f2a2aa2a',
                'label' => __( 'Main Content Area Background Color','landing-pages'),
                'name' => 'simple-two-column-content-color',
                'type' => 'color_picker',
                'instructions' => __( 'Change the main background color here.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#1240AB',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_55df8f3f2aa2b',
                'label' => __( 'Main Content Area Text Color','landing-pages'),
                'name' => 'simple-two-column-content-text-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the text color of the main content area.','landing-pages'),
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
                'key' => 'field_55df8f5b2aa2c',
                'label' => __( 'Sidebar Background Color','landing-pages'),
                'name' => 'simple-two-column-sidebar-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the sidebar background color','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#2A4480',
            ),
            array (
                'key' => 'field_55df8f6d2aa2d',
                'label' => __( 'Sidebar Text Color','landing-pages'),
                'name' => 'simple-two-column-sidebar-text-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the sidebar text color','landing-pages'),
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
                'key' => 'field_55df8f842aa2e',
                'label' => __( 'Background Color','landing-pages'),
                'name' => 'simple-two-column-body-color',
                'type' => 'color_picker',
                'instructions' => __( 'Use this setting to change the template\'s background color','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#06266F',
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