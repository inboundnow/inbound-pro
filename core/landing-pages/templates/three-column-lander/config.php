<?php
/**
* Template Name:  3 Column Lander Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // global config action hook

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__));

$lp_data[$key]['info'] =
array(
	'data_type' => 'template', // Template Data Type
	'version' => "1.0.1", // Version Number
	'label' => "3 Column Lander", // Nice Name
	'category' => '3 column layout, Responsive, V2', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/3-column-lander/', // Demo Link
	'description'  => '' // template description
);

// Define Meta Options for template
// These values are returned in the template's index.php file with lp_get_value($post, $key, 'field-id') function
$lp_data[$key]['settings'] =
array(
   array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
		),
     array(
           'label' => __( 'Main Content' , 'landing-pages' ) ,
           'description' => __( 'This is the default content from template.' , 'landing-pages' ),
           'id' => "main-content",
           'type' => "wysiwyg",
           'default' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>

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

<p>This is the final sentence or paragraph reassuring the visitor of the benefits of filling out the form and how their data will be safe.</p>'
         ),
	array(
           'label' => __( 'Conversion Area' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
    array(
        'label' => __( 'Conversion Area Placement' , 'landing-pages' ) ,
        'description' => __( 'Where do you want to place the conversion area?' , 'landing-pages' ) ,
        'id'  => 'conversion_area',
        'type'  => 'dropdown',
        'default'  => 'middle',
        'options' => array('right'=>'Conversion Area on right', 'middle'=>'Conversion Area in middle', 'left'=>'Conversion Area on left' ),
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Submit Button Color' , 'landing-pages' ) ,
        'description' => __( 'Submit Button Color' , 'landing-pages' ) ,
        'id'  => 'submit-button-color',
        'type'  => 'colorpicker',
        'default'  => '33B96B',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Left Content Background Color' , 'landing-pages' ) ,
        'description' => __( 'Content Background Color' , 'landing-pages' ) ,
        'id'  => 'left-content-bg-color',
        'type'  => 'colorpicker',
        'default'  => '0B61A4',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Left Content Text Color' , 'landing-pages' ) ,
        'description' => __( 'Content Text Color' , 'landing-pages' ) ,
        'id'  => 'left-content-text-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
     array(
        'label' => __( 'Left Content' , 'landing-pages' ) ,
        'description' => __( 'Left Content Area' , 'landing-pages' ) ,
        'id'  => 'left-content-area', // called in template's index.php file with lp_get_value($post, $key, 'wysiwyg-id');
        'type'  => 'wysiwyg',
        'default'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Middle Content Background Color' , 'landing-pages' ) ,
        'description' => __( 'Content Background Color. The content of this area is controlled by the main editor above' , 'landing-pages' ) ,
        'id'  => 'middle-content-bg-color',
        'type'  => 'colorpicker',
        'default'  => 'F3F1EF',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Middle Content Text Color' , 'landing-pages' ) ,
        'description' => __( 'Content Text Color. The content of this area is controlled by the main editor above' , 'landing-pages' ) ,
        'id'  => 'middle-content-text-color',
        'type'  => 'colorpicker',
        'default'  => '000000',
        'context'  => 'normal'
        ),
     array(
        'label' => __( 'Right Content Background Color', 'landing-pages' ) ,
        'description' => __( 'Content Background Color' , 'landing-pages' ) ,
        'id'  => 'right-content-bg-color',
        'type'  => 'colorpicker',
        'default'  => '0B61A4',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Right Content Text Color' , 'landing-pages' ) ,
        'description' => __( 'Content Text Color' , 'landing-pages' ) ,
        'id'  => 'right-content-text-color',
        'type'  => 'colorpicker',
        'default'  => 'ffffff',
        'context'  => 'normal'
        ),
    array(
        'label' => __( 'Right Content' , 'landing-pages' ) ,
        'description' => __( 'Right Content Area' , 'landing-pages' ) ,
        'id'  => 'right-content-area', // called in template's index.php file with lp_get_value($post, $key, 'wysiwyg-id');
        'type'  => 'wysiwyg',
        'default'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.',
        'context'  => 'normal'
        )
);