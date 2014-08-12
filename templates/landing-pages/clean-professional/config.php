<?php
/**
* WordPress Landing Page Config File
* Template Name:  Clean Professional Template
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
$lp_data[$key]['description'] = "Clean Professional Template"; 
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/clean-professional/"); 
$lp_data[$key]['features'][] = lp_list_feature("The clean professional template is awesome!"); 


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
			'default' => '<p>This is the first paragraph of your landing page. You want to grab the visitors attention and describe a commonly felt problem that they might be experiencing. Try and relate to your target audience and draw them in.</p>'
		), 
	
	array(
			'label' => __( 'Call to Action Content' , 'landing-pages' ),
			'description' => __( 'Place your call to action here.' , 'landing-page' ),
			'id' => "conversion-area-content",
			'type' => "wysiwyg",
			'default' => ''
		)
);

// Colorpicker Example
// Add a colorpicker option to your theme's options panel. This is called with....
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","top-color","ffffff","Top & Subheadline Area Background Color","Use this setting to change the template's body color", $options=null);

// Colorpicker Example
// Add a colorpicker option to your theme's options panel. This is called with....
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","top-text-color","000000","Text Color for Top & Subheadline Area","Use this setting to change the template's body color", $options=null);	

// Media Uploaded Example
// Add a media uploader field to your landing page options	
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","logo","/wp-content/uploads/landing-pages/templates/clean-professional/assets/images/inbound-logo-horizontal.png","Logo Image","Enter an URL or upload an image for the banner. Recommended width 260px", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","middle-text-color","ffffff","Middle Area Text Color","Use this setting to change the template's headline text color", $options=null);	

/* WYSIWYG Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","link-area","","Top Right Links","Top Link Area. Leave Blank for no links. Make sure links are in a list format", $options=null);		

// Media Uploaded Example
// Add a media uploader field to your landing page options	
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","bg-image","/wp-content/uploads/landing-pages/templates/clean-professional/assets/images/background.jpg","Background Image","Enter an URL or upload an image for the banner. 1600 x 456 pixels", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","5baa1e","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);

// Textfield Example
// Add a text input field to the landing page options panel	
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","sub-headline","Sub Headline Goes Here","Sub Headline Text","Sub headline text goes here", $options=null);
// text field
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","footer-text","Footer Tagline Here","Footer Tagline Here","Footer Tagline Here", $options=null);


// Colorpicker Example
// Add a colorpicker option to your theme's options panel. This is called with....
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","bottom-color","161209","Footer Background Color","Use this setting to change the template's Bottom Body Color", $options=null);	

