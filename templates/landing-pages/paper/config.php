<?php
/**
* WordPress Landing Page Config File
* Template Name:  Paper Template
*
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 

//adds template data to global array for use with landing page plugin - edit theme category and description only. 

//EDIT - START - defines template information - helps categorizae template and provides additional popup information
$lp_data[$key]['category'] = "miscellaneous"; 
$lp_data[$key]['version'] = "2.0.0"; 
$lp_data[$key]['description'] = "Paper Template"; 
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/paper-template-preview/"); 
$lp_data[$key]['features'][] = lp_list_feature("Paper is a simple sheet of paper for you to lay out your offer"); 


//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; 
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); 


//************************************************
// Add User Options to Your Landing Page Template
//************************************************
/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
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

[list icon="ok-sign" font_size="16" icon_color="#00a319" text_color="" bottom_margin="10"]
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
			'label' => __( 'Call to Action Content' , 'landing-pages' ),
			'description' => __( 'Place your call to action here.' , 'landing-page' ),
			'id' => "conversion-area-content",
			'type' => "wysiwyg",
			'default' => ''
		)
);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","headline-color","000000","Headline Text Color","Use this setting to change the template's headline text color", $options=null);	

// Add a text input field
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","sub-headline","Sub Headline Goes Here","Sub Headline Text","Sub headline text goes here", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","sub-headline-color","a3a3a3","Sub Headline Text Color","Use this setting to change the template's headline text color", $options=null);	

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","5baa1e","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);

/* Template Background Settings */
// Select Background Type Setting
$options = array('default' => 'Default', 'fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","background-style","default","Background Settings","Decide how you want the bodies background to be", $options);	
// Full Screen Image Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","background-image","","Background Image","Enter an URL or upload an image for the banner.", $options=null);
// Solid Backgound Color Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","background-color","dfdfdf","Background Color","Use this setting to change the templates background color", $options=null);
/* Template End Background Settings */

// Add a Radio button
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"radio","display-social","0","Display Social Media Share Buttons","Toggle social sharing on and off", $options);	

// Add a text input field to the landing page options panel	
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","text-box-id","Footer Text. Company ©","Text Field Label","Text field Description", $options=null);	