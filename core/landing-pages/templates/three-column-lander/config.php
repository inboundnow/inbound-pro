<?php
/**
* Template Name:  3 Column Lander Template
* @package  WordPress Landing Pages
* @author 	Inbound Now
*/

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'acf4',
	'version' => "2.0.1",
	'label' => "3 Column Lander",
	'category' => '3 column, responsive',
	'demo' => 'http://demo.inboundnow.com/go/3-column-lander/',
	'description'  => ''
);

/* define ACF fields here */
if( function_exists('register_field_group') ):
    register_field_group(array (
        'key' => 'group_55e4bdb6b1985',
        'title' => 'Three Column Lander',
        'fields' => array (
            array (
                'key' => 'field_55e4bdc9e878f',
                'label' => __('Main Headline','landing-pages'),
                'name' => 'lp-main-headline',
                'type' => 'text',
                'instructions' => __('Insert main headline here.','landing-pages'),
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
                'key' => 'field_55e4bdd1e8790',
                'label' => __('Middle Content','landing-pages'),
                'name' => 'three-column-lander-main-content',
                'type' => 'wysiwyg',
                'instructions' => __('This is the default content for the center column. ','landing-pages'),
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
                'toolbar' => 'full'
            ),
            array (
                'key' => 'field_55e4beb2e8797',
                'label' => __('Middle Content Background Color','landing-pages'),
                'name' => 'three-column-lander-middle-content-bg-color',
                'type' => 'color_picker',
                'instructions' => __('Input the color of the middle content column.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#F3F1EF',
            ),
            array (
                'key' => 'field_55e4bec6e8798',
                'label' => __('Middle Content Text Color','landing-pages'),
                'name' => 'three-column-lander-middle-content-text-color',
                'type' => 'color_picker',
                'instructions' => __('Font color of the middle content box.','landing-pages'),
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
                'key' => 'field_55e4bdfae8791',
                'label' => __('Conversion Area','landing-pages'),
                'name' => 'three-column-lander-conversion-area',
                'type' => 'wysiwyg',
                'instructions' => __('Place your call to action or Inbound Form here. ','landing-pages'),
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
                'key' => 'field_55e4be1ae8792',
                'label' => __('Conversion Area Placement','landing-pages'),
                'name' => 'three-column-lander-conversions_area',
                'type' => 'select',
                'instructions' => __('Determine which side the call to action should display on.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'right' => 'Conversion Area on Right Column',
                    'left' => 'Conversion Area on Left Column',
                    'middle' => 'Conversion Area in Middle Column',
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
                'key' => 'field_55e4be2de8793',
                'label' => __('Submit Button Color','landing-pages'),
                'name' => 'three-column-lander-submit-button-color',
                'type' => 'color_picker',
                'instructions' => __('Select the background color of the submit button.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#33B96B',
            ),
            array (
                'key' => 'field_55e4be6ee8795',
                'label' => __('Left Content','landing-pages'),
                'name' => 'three-column-lander-left-content-area',
                'type' => 'wysiwyg',
                'instructions' => __('Input the content of the left column. ','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
                'tabs' => 'all',
                'toolbar' => 'full'
            ),
            array (
                'key' => 'field_55e4be45e8794',
                'label' => __('Left Content Background Color','landing-pages'),
                'name' => 'three-column-lander-left-content-bg-color',
                'type' => 'color_picker',
                'instructions' => __('Input the background color of the left content column.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#0B61A4',
            ),
            array (
                'key' => 'field_55e4be8ce8796',
                'label' => __('Left Content Text Color','landing-pages'),
                'name' => 'three-column-lander-left-content-text-color',
                'type' => 'color_picker',
                'instructions' => __('Input the font color of the left content column.','landing-pages'),
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
                'key' => 'field_55e4bf0ae879b',
                'label' => __('Right Content','landing-pages'),
                'name' => 'three-column-lander-right-content-area',
                'type' => 'wysiwyg',
                'instructions' => __('Right area content. ','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
                'tabs' => 'all',
                'toolbar' => 'full'
            ),
            array (
                'key' => 'field_55e4bedae8799',
                'label' => __('Right Content Background Color','landing-pages'),
                'name' => 'three-column-lander-right-content-bg-color',
                'type' => 'color_picker',
                'instructions' => __('Input the color of the right content column.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#0B61A4',
            ),
            array (
                'key' => 'field_55e4bef6e879a',
                'label' => __('Right Content Text Color','landing-pages'),
                'name' => 'three-column-lander-right-content-text-color',
                'type' => 'color_picker',
                'instructions' => __('Right content text color.','landing-pages'),
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
        'options' => array(),
    ));

endif;