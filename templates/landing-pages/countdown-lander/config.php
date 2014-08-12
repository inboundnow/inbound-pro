<?php
/**
* WordPress Landing Page Config File
* Template Name:  Countdown Lander Template
*
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 

//EDIT - START - defines template information - helps categorizae template and provides additional popup information
$lp_data[$key]['category'] = "miscellaneous"; 
$lp_data[$key]['version'] = "2.0.0"; 
$lp_data[$key]['description'] = "Countdown Lander Template"; 
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/countdown-lander-preview/"); 
$lp_data[$key]['features'][] = lp_list_feature("The countdown lander template is for counting down to events or limited time offers."); 


//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; 
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); 


//************************************************
// Add User Options to Your Landing Page Template
//************************************************
// Date Picker Example
// Add a colorpicker option to your theme's options panel. 
$lp_data[$key]['options'][] = 
	lp_add_option($key,"datepicker","date-picker","2012-12-27","Countdown Date","What date are we counting down to?", $options=null);

// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","headline-color","ffffff","Headline Text Color","Use this setting to change the Heading Text Color", $options=null);

// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","other-text-color","ffffff","Other Text Color","Use this setting to change the template's text color", $options=null);	

// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","5baa1e","Submit Button Color","Use this setting to change the template's submit button color.", $options=null);		
// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","content-background","000000","Content Background Color","Use this setting to change the content area's background color", $options=null);	

// Add a radio button option to your theme's options panel.	
$options = array('on' => 'on','off'=>'off');
$lp_data[$key]['options'][] = 
	lp_add_option($key,"radio","background-on","on","Show Transparent Background behind content?","Toggle this on to render the transparent background behind your content for better visability", $options);

// Textfield Example
// Add a text input field to the landing page options panel	
$lp_data[$key]['options'][] = 
	lp_add_option($key,"text","countdown-message","Countdown Until... Message","Countdown Until... Message","Insert the event you are counting down to", $options=null);
	
// Media Uploaded Example
// Add a media uploader field to your landing page options	
$lp_data[$key]['options'][] = 
	lp_add_option($key,"media","bg-image","","Background Image","Enter an URL or upload an image for the background.", $options=null);

// Radio Button Example
// Add a radio button option to your theme's options panel.	
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['options'][] = 
	lp_add_option($key,"radio","display-social","1","Display Social Media Share Buttons","Toggle social sharing on and off", $options);	