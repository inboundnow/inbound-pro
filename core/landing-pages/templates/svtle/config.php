<?php
/**
 * Template Name:  Svtle Template
 * @package  WordPress Landing Pages
 * @author 	Inbound Now
 */

/* gets template directory name to use as identifier - do not edit - include in all template files */
$key = basename(dirname(__FILE__));

$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/';

$lp_data[$key]['info'] = array(
    'data_type' => 'acf4',
    'version' => "1.0.1",
    'label' => "Svbtle",
    'category' => '2 column',
    'demo' => 'http://demo.inboundnow.com/go/sbvtle-lander-preview/',
    'description'  => __('Clean and minimalistic design for a straight forward conversion page.','landing-pages')
);

/* define ACF fields here */
if( function_exists('register_field_group') ):

    register_field_group(array (
        'key' => 'group_55e4ad14ab37b',
        'title' => 'Svtle',
        'fields' => array (
            array (
                'key' => 'field_55e4ae2e31895',
                'label' => __('Main Headline','landing-pages'),
                'name' => 'lp-main-headline',
                'type' => 'text',
                'instructions' => __('Insert the main headline here.','landing-pages'),
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
                'key' => 'field_55e4ae4a1810b',
                'label' => __('Main Content','landing-pages'),
                'name' => 'svtle-main-content',
                'type' => 'wysiwyg',
                'instructions' => __('This is the default content from template.','landing-pages'),
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
                'key' => 'field_55e4aef61810c',
                'label' => __('Conversion Area','landing-pages'),
                'name' => 'svtle-conversion-area-content',
                'type' => 'wysiwyg',
                'instructions' => __('Place your call to action or Inbound Form here.','landing-pages'),
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
            ),
            array (
                'key' => 'field_55e4af0c1810d',
                'label' => __('Display Social Media Share Buttons','landing-pages'),
                'name' => 'svtle-display-social',
                'type' => 'select',
                'instructions' => __('Display Social Media Share Buttons.','landing-pages'),
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
                'key' => 'field_55e4af861810e',
                'label' => __('Sidebar Layout','landing-pages'),
                'name' => 'svtle-sidebar',
                'type' => 'select',
                'instructions' => __('Align sidebar to the left or the right.','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'left' => 'Sidebar on left',
                    'right' => 'Sidebar on right',
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
                'key' => 'field_55e4af961810f',
                'label' => __('Submit Button Background Color','landing-pages'),
                'name' => 'svtle-submit-button-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s submit button background color','landing-pages'),
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
                'key' => 'field_55e4b08318110',
                'label' => __('Logo Image','landing-pages'),
                'name' => 'svtle-logo',
                'type' => 'image',
                'instructions' => __('Upload Your Logo (300x110px)','landing-pages'),
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
                'default_value' => $path .'assets/images/inbound-logo.png',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
            array (
                'key' => 'field_55e4b09018111',
                'label' => __('Content Area Background Color','landing-pages'),
                'name' => 'svtle-body-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s main content area background color','landing-pages'),
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
                'key' => 'field_55e4b17918112',
                'label' => __('Content Area Text Color','landing-pages'),
                'name' => 'svtle-page-text-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s content area text color','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#4D4D4D',
            ),
            array (
                'key' => 'field_55e4b19618113',
                'label' => __('Sidebar Color','landing-pages'),
                'name' => 'svtle-sidebar-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s sidebar color','landing-pages'),
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
                'key' => 'field_55e4b24111a74',
                'label' => __('Sidebar Text Color','landing-pages'),
                'name' => 'svtle-sidebar-text-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s sidebar color','landing-pages'),
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
                'key' => 'field_55e4b1ab18114',
                'label' => __('Header Color','landing-pages'),
                'name' => 'svtle-header-color',
                'type' => 'color_picker',
                'instructions' => __('Use this setting to change the template\'s header color','landing-pages'),
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
                'key' => 'field_55e4b1ba18115',
                'label' => __('Display Form Below Content On Mobile?','landing-pages'),
                'name' => 'svtle-mobile-form',
                'type' => 'radio',
                'instructions' => __('Toggle this on to render the form below the content in the mobile view','landing-pages'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'on' => 'on',
                    'off' => 'off',
                ),
                'other_choice' => 0,
                'save_other_choice' => 0,
                'default_value' => 'on',
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