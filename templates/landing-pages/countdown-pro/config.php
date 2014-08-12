<?php
/**
* WordPress Landing Page Config File
* Template Name:  CountDown Pro Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 

// Add in global templata data
//EDIT - START - defines template information - helps categorize template and provides additional popup information
// Add Landing Page to a specific category. 
$lp_data[$key]['category'] = "countdown"; 
// Add version control to your template.
$lp_data[$key]['version'] = "2.0.0"; 
// Add description visible to the user
$lp_data[$key]['description'] = "Create a countdown landing page that counts down to your event or launch!"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/countdown-pro/"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("Countdown Landing Page"); 
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

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","headline-color","000000","Headline Text Color","Use this setting to change the Heading Text Color", $options=null);

// Date Picker Example
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"datepicker","date-picker","2013-1-31 13:00","Countdown Date","What date are we counting down to?", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","text-color","000000","Content Area Text Color","Use this setting to change the Main Area Text Color", $options=null);		

// ADD IN META BOX OPTIONS TO YOUR TEMPLATE BELOW
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","content-background","ffffff","Content Background Color","Use this setting to change the content area's background color", $options=null);

/* Dropdown Example */
$options = array('1'=>'100% (Solid)', '0.9'=>'90%', '0.8'=>'80%', '0.7'=>'70%', '0.6'=>'60%', '0.5'=>'50%', '0.4'=>'40%', '0.3'=>'30%', '0.2'=>'20%', '0.1'=>'10%', '0'=>'0% (transparent)');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","opacity","1","Opacity of Content Background", "How see through is the content background? Default 50%", $options);		

	// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","form-submit-color","7c94b4","Form Submit Button Color","Use this setting to change the Form submit button color", $options=null);		

/* Style Options */
$options = array('default'=>'Default','full-circle'=>'Filled Circle', 'half-circle'=>'Half Filled Circle');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","style","default","Countdown Style","Dropdown option description", $options);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","countdown-text","333333","Countdown Text Color","Use this setting to change the countdown Text Color", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","circle-days","C7654A","Days Circle Color","Use this setting to change the circle color", $options=null);	

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","circle-hours","EC885A","Hours Circle Color","Use this setting to change the circle color", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","circle-minutes","FFAF64","Minutes Circle Color","Use this setting to change the circle color", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","circle-seconds","FFE591","Seconds Circle Color","Use this setting to change the circle color", $options=null);						
	
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
	lp_add_option($key,"colorpicker","background-color","ffffff","Background Color","Use this setting to change the templates background color", $options=null);
/* Template End Background Settings */		
