<?php
/**
 * Template Name: Inbound Now
 * @package    Inbound Email
 *
 */

$key = basename(dirname(__FILE__));

/* Configures Template Information */
$inbound_email_data[$key]['info'] = array(
    'data_type' => 'email-template',
    'label' => __('Inbound Now', 'inbound-mailer'),
    'category' => 'responsive',
    'demo' => '',
    'description' => __('An email template in the style of Inbound Now emails.', 'inbound-mailer'),
    'acf' => true
);

/*
* Define ACF Fields to be used in this template
* Pay special attention to the 'location' key as this is where we tell ACF to load when this template is selected
*/
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array (
        'key' => 'group_56b8eedbdc29b',
        'title' => 'Inbound Now Email Template',
        'fields' => array (
            array (
                'key' => 'field_56b8f76766d7c',
                'label' => 'Logo',
                'name' => 'logo',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'full',
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
                'key' => 'field_56b8fa8acadf1',
                'label' => 'Logo Positioning',
                'name' => 'logo_positioning',
                'type' => 'select',
                'instructions' => 'Select where to align the logo',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'left' => 'left',
                    'center' => 'center',
                    'right' => 'right',
                ),
                'default_value' => array (
                    'center' => 'center',
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_56b8fb975c61f',
                'label' => 'Logo URL',
                'name' => 'logo_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'Optionally link to logo somewhere',
            ),
            array (
                'key' => 'field_56be4dd7a4c32',
                'label' => 'Email Font',
                'name' => 'email_font',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'Helvetica' => 'Helvetica',
                    'Arial' => 'Arial',
                    'Arial Black' => 'Arial Black',
                    'Comic Sans' => 'Comic Sans',
                    'Courier New' => 'Courier New',
                    'Georgia' => 'Georgia',
                    'Impact' => 'Impact',
                    'Charcoal' => 'Charcoal',
                    'Lucida Console' => 'Lucida Console',
                    'Lucida Sans Unicode' => 'Lucida Sans Unicode',
                    'Lucida Grande' => 'Lucida Grande',
                    'Palatino Linotype' => 'Palatino Linotype',
                    'Book Antiqua' => 'Book Antiqua',
                    'Palatino' => 'Palatino',
                    'Tahoma' => 'Tahoma',
                    'Geneva' => 'Geneva',
                    'Times' => 'Times',
                    'Times New Roman' => 'Times New Roman',
                    'Trebuchet MS' => 'Trebuchet MS',
                    'Verdana' => 'Verdana',
                    'Monaco' => 'Monaco',
                    'serif' => 'serif',
                    'sans-serif' => 'sans-serif',
                    'cursive' => 'cursive',
                    'fantasy' => 'fantasy',
                    'monospace' => 'monospace',
                ),
                'default_value' => array (
                    'serif' => 'serif',
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_56b8f79266d7d',
                'label' => 'Headline Text',
                'name' => 'headline',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'It\'s Here.',
                'placeholder' => 'Headline Text Here',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b910ca48882',
                'label' => 'Headline Size',
                'name' => 'headline_size',
                'type' => 'text',
                'instructions' => 'Be sure to include the px.',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '36px',
                'placeholder' => '36px',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b910ff48883',
                'label' => 'Sub Headline Size',
                'name' => 'sub_headline_size',
                'type' => 'text',
                'instructions' => 'Be sure to include the px.',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '20px',
                'placeholder' => '20px',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b8f7be66d7e',
                'label' => 'Sub Headline Text',
                'name' => 'sub_headline',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'An all new product...',
                'placeholder' => 'Subheadline text here',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b8f7e466d7f',
                'label' => 'Featured Image',
                'name' => 'featured_image',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'medium',
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
                'key' => 'field_56b912ac93890',
                'label' => 'Image Width',
                'name' => 'image_width',
                'type' => 'text',
                'instructions' => 'Control the width of	your featured image. Be sure to include px or %.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '500px',
                'placeholder' => '500px',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b912df93891',
                'label' => 'Image Height',
                'name' => 'image_height',
                'type' => 'text',
                'instructions' => 'Control the heightof	your featured image. Be sure to include px or %.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'auto',
                'placeholder' => 'auto',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b8f87166d80',
                'label' => 'Message Content',
                'name' => 'message_content',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'Dear [lead-field id="wpleads_first_name" default="Subscriber"],

Thank you for being a valued customer. May your inbound experience be a good one.

Cheers!
@inboundnow',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array (
                'key' => 'field_56b9107848881',
                'label' => 'Align Message Content',
                'name' => 'align_message_content',
                'type' => 'select',
                'instructions' => 'Select how to position your message content. ',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'left' => 'left',
                    'center' => 'center',
                    'right' => 'right',
                ),
                'default_value' => array (
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'disabled' => 0,
                'readonly' => 0,
            ),
            array (
                'key' => 'field_56b8f89866d81',
                'label' => 'Footer Address',
                'name' => 'footer_address',
                'type' => 'text',
                'instructions' => 'In order to be complaint with CAN-SPAM Act please enter a valid address.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'Inbound LLC. - 388 Dolores Street San Francisco San Francisco, CA',
                'placeholder' => 'Enter address here for CAN-SPAM compliance',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
                'readonly' => 0,
                'disabled' => 0,
            ),
            array (
                'key' => 'field_56b9024fd3ddc',
                'label' => 'Background Contrast Color',
                'name' => 'contrast_background_color',
                'type' => 'color_picker',
                'instructions' => 'You can set this to white sometimes... we do.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#e8e8e8',
            ),
            array (
                'key' => 'field_56b919501db79',
                'label' => 'Content Background Color',
                'name' => 'content_background_color',
                'type' => 'color_picker',
                'instructions' => 'This is the background color behind the content',
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
                'key' => 'field_56b91b15ffe34',
                'label' => 'Content Text Color',
                'name' => 'content_color',
                'type' => 'color_picker',
                'instructions' => 'This is the background color behind the content',
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
                'key' => 'field_56b9028ed3ddd',
                'label' => 'Show email content border?',
                'name' => 'show_email_content_border',
                'type' => 'checkbox',
                'instructions' => 'This will hide/reveal a border around your email message.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'yes' => 'yes',
                ),
                'default_value' => array (
                ),
                'layout' => 'vertical',
                'toggle' => 0,
            ),
            array (
                'key' => 'field_56b91774c2c42',
                'label' => 'Hide \'Show this email in browser\' link',
                'name' => 'hide_show_email_in_browser',
                'type' => 'checkbox',
                'instructions' => 'Hide/Reveal the link to the online version of the email.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array (
                    'hide' => 'hide',
                ),
                'default_value' => array (
                ),
                'layout' => 'vertical',
                'toggle' => 0,
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'template_id',
                    'operator' => '==',
                    'value' => $key,
                ),
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