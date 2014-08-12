<?php
/**
* WordPress Landing Page Config File
* Template Name:  Scrolling Lander Template
*
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 


// Add in global templata data
//EDIT - START - defines template information - helps categorize template and provides additional popup information
// Add Landing Page to a specific category. 
$lp_data[$key]['category'] = "Miscellaneous"; 
// Add version control to your template.
$lp_data[$key]['version'] = "2.0.0"; 
// Add description visible to the user
$lp_data[$key]['description'] = "This is Template that scrolls in a cool way"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/scrolling-lander/"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("Scrolling Lander Template"); 
//EDIT - END

//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; //do not edit this
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); //do not edit this

//*************************************************************************************************
/* Add User Options to Your Landing Page Template Below */
// For more on adding meta-boxes to templates head to:
// http://plugins.inboundnow.com/docs/dev/creating-templates/template-config/
//*************************************************************************************************

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

/* Main Background Image Option */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","main-bg-image","/wp-content/uploads/landing-pages/templates/scrolling-lander/hero.jpg","Main Background Image","Main Background Image of site", $options=null);

/* Logo Text Option */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","logo-img-id","/wp-content/uploads/landing-pages/templates/scrolling-lander/inboundlogo.png","Logo Image","Upload Logo image for site", $options=null);

/* Main Title Text Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","main-title-text-color","f7f7f7","Top Title Text Color","Use this setting to change the template's Main Content Area title text color", $options=null);	

/* Top content area Text Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","top-content-area-text-color","ffffff","Top Content Area Text Color","Use this setting to change the template's Top content area text color", $options=null);	
	
// Add Top content Area
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","top-content-area","<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
","Top content area","This is the top content area", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","57d100","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);	



/* Top Title Text  */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","top-title-text","Bottom Title Text","Bottom Title Text","Use this setting to change the template's Bottom Heading Text", $options=null);	


/* Top Title Text Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","title-text-color","000000","Bottom Title Text Color","Use this setting to change the template's Top title text color", $options=null);	


// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","bottom-content-bgcolor","ffffff","Bottom Content Background Color","Use this setting to change the template's Bottom Background color", $options=null);

// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","bottom-content-text-color","000000","Bottom Content Text Color","Use this setting to change the template's Bottom Content Text Color", $options=null);	

// Add a Radio button
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"radio","display-bottom-form","1","Display Conversion Area at Bottom?","Toggle display on and off", $options);	

// Add a Radio button
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"radio","display-social","1","Display Social Media Share Buttons","Toggle social sharing on and off", $options);	

