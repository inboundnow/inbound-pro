<?php
/**
* WordPress Landing Page Config File
* Template Name:  Video Lander Template
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
$lp_data[$key]['category'] = "video"; 
$lp_data[$key]['version'] = "2.0.0"; 
// Add description visible to the user
$lp_data[$key]['description'] = "Video Lander"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://inboundsoon.wpengine.com/copy-of-youtube-lander/"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("This landing page is for promotional video type landing pages."); 
//EDIT - END

//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; //do not edit this
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); //do not edit this

//*************************************************************************************************
/* Add User Options to Your Landing Page Template Below */
// For more on adding meta-boxes to templates head to:
// http://plugins.inboundnow.com/docs/dev/creating-templates/template-config/
//*************************************************************************************************

// ADD IN META BOX OPTIONS TO YOUR TEMPLATE BELOW


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
           'label' => __( 'Video Content' , 'landing-pages' ) ,
           'description' => __( 'This is the default content from template.' , 'landing-pages' ),
           'id' => "main-content",
           'type' => "wysiwyg",
           'default' => '<iframe src="http://www.youtube.com/embed/BzcD6PgvLP4?list=UUCqiE-EcfDjaKGXSxtegcyg" height="360" width="640" allowfullscreen="" frameborder="0"></iframe>'
         ),
	array(
           'label' => __( 'Call to Action Content' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         )
);

// Select Background Type Setting
$options = array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","background-style","fullscreen","Background Settings","Decide how you want the bodies background to be", $options);	
// Full Screen Image Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","background-image","","Background Image","Enter an URL or upload an image for the banner.", $options=null);
// Solid Backgound Color Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","background-color","186d6d","Background Color","Use this setting to change the templates background color", $options=null);
/* Template End Background Settings */

// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","text-color","000000","Headline Text Color","Use this setting to change the Template heading Color", $options=null);

$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","form-text-color","000000","Conversion Area Text Color","Use this setting to change the Conversion Area text Color", $options=null);
/* Colorpicker Example
 */
// Add a colorpicker option to your template's options panel. 
// This is called in the template's index.php file with lp_get_value($post, $key, 'color-picker-id'); 
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","439E47","Submit Button Color","This changes the submit button color", $options=null);
