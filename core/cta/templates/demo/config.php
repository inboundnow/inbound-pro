<?php
/**
* WordPress: WP Calls To Action Template Config File
* Template Name:  Demo Template
* @package  WordPress Calls to Action
* @author 	InboundNow
*/

do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files

$key = basename(dirname(__FILE__));
$this_path = WP_CTA_PATH.'templates/'.$key.'/';
$url_path = WP_CTA_URLPATH.'templates/'.$key.'/';

/// Information START - define template information
/**
 * $wp_cta_data[$key]['info']
 * type - multidemensional array
 *
 * This array houses the template metadata
 */

/* $wp_cta_data[$key]['settings'] Parameters

    'version' - (string) (optional)
    Version Number. default = "1.0"

    'label' - (string) (optional)
    Custom Nice Name for templates. default = template file folder name

    'description' - (string) (optional)
    Landing page description.

    'category' - (string) (optional)
    Category for template. default = "all"

    'demo' - (string) (optional)
    Link to demo url.
*/

$wp_cta_data[$key]['info'] =
array(
    'data_type' => 'template', // Template Data Type
	'version' => "1.0", // Version Number
	'label' => "Demo Template", // Nice Name
	'category' => 'Box', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
	'description'  => 'The Demo theme is here to help developers and designs implment thier own designs into the landing page plugin. Study this template to learn about Landing Page Plugin\'s templating system and to assist in building new templates. The template files inside of /cta/templates/demo will show you exactly how to build your own templates', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);

/**
 * $wp_cta_data[$key]['settings']
 * type - multidemensional array
 *
 * This array houses the metabox options for the template
 */

/* $wp_cta_data[$key]['settings'] Parameters

    'label' - (string) (required)
    Label for Meta Fields.

    'description' - (string) (optional)
    Description for meta Field

    'id' - (string) (required)
    unprefixed-meta-key. The $key (template file path name) is appended in the loop this array is used in.

    'type' - (string) (required)
    Meta box type. default = 'text'

    'default' - (string) (optional)
    Default Field Value.  default = ''

    'context' - (string) (optional)
    where this box will go, will be used for advanced placement/styling.  default = normal

 */

// Define Meta Options for template
$wp_cta_data[$key]['settings'] =
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "This is a demo template for developers and designers to learn how to create your own call to action templates. View the source inside /wp-content/plugins/cta/templates/demo for more info", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        ),
    /* Text field Example */
    array(
        'label' => 'Text Field Label', // Label of field
        'description' => "Text field Description", // field description
        'id' => 'text_box_id', // metakey. The $key Prefix is appended making the meta value demo-text-box-id
        'type'  => 'text', // text metafield type
        'default'  => 'Text in box', // default content
        'context'  => 'normal' // Context in screen for organizing options
        ),
    /* Textarea Example */
    array(
        'label' => 'Textarea Label',
        'description' => "Textarea description to the user",
        'id'  => 'textarea_id', // called in template's index.php file with lp_get_value($post, $key, 'textarea-id');
        'type'  => 'textarea',
        'default'  => 'Default text in textarea',
        'context'  => 'normal'
        ),
    /* Colorpicker Example */
    array(
        'label' => 'ColorPicker Label',
        'description' => "Colorpicker field description",
        'id'  => 'color_picker_id', // called in template's index.php file with lp_get_value($post, $key, 'color-picker-id');
        'type'  => 'colorpicker',
        'default'  => 'ce1010',
        'context'  => 'normal'
        ),
    /* Radio Button Example */
    array(
        'label' => 'Radio Label',
        'description' => "Radio field description",
        'id'  => 'radio_id_here', // called in template's index.php file with lp_get_value($post, $key, 'radio-id-here');
        'type'  => 'radio',
        'default'  => '1',
        'options' => array('1' => 'on','0'=>'off'),
        'context'  => 'normal'
        ),
    /* Checkbox Example */
    array(
        'label' => 'Checkbox Label',
        'description' => "Example Checkbox Description",
        'id'  => 'checkbox_id_here', // called in template's index.php file with lp_get_value($post, $key, 'checkbox-id-here');
        'type'  => 'checkbox',
        'default'  => 'on',
        'options' => array('option_on' => 'on'),
        'context'  => 'normal'
        ),
    /* Dropdown Example */
    array(
        'label' => 'Dropdown Label',
        'description' => "Dropdown option description",
        'id'  => 'dropdown_id_here', // called in template's index.php file with lp_get_value($post, $key, 'dropdown-id-here');
        'type'  => 'dropdown',
        'default'  => 'default',
        'options' => array('right'=>'Float right','left'=>'Float left', 'default'=>'Default option'),
        'context'  => 'normal'
        ),
    /* Date Picker Example */
     array(
        'label' => 'Date Picker Label',
        'description' => "Date Picker Description",
        'id'  => 'date_picker', // called in template's index.php file with lp_get_value($post, $key, 'date-picker');
        'type'  => 'datepicker',
        'default'  => '2013-12-27',
        'context'  => 'normal'
        ),
     /* WYSIWYG Example */
     array(
        'label' => 'Main Content Box 2',
        'description' => "wysiwyg description",
        'id'  => 'wysiwyg_id', // called in template's index.php file with lp_get_value($post, $key, 'wysiwyg-id');
        'type'  => 'wysiwyg',
        'default'  => 'Default WYSIWYG content',
        'context'  => 'normal'
        ),
     /* Media Uploaded Example */
     array(
        'label' => 'File/Image Upload Label',
        'description' => "File/Image Upload Description",
        'id'  => 'media_id', // called in template's index.php file with lp_get_value($post, $key, 'media-id');
        'type'  => 'media',
        'default'  => 'http://www.fillmurray.com/200/300',
        'context'  => 'normal'
        ),
     array(
         'label' => 'Boolean Test',
         'description' => "Set the Boolean",
         'id'  => 'boolean',
         'type'  => 'dropdown',
         'default'  => 'false',
         'options' => array('false'=>'False', 'true'=>'True'),
         'context'  => 'normal'
         ),
    );

/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = file_get_contents($this_path . 'index.php');


