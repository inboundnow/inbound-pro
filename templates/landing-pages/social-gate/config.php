<?php
/**
* WordPress Landing Page Config File
* Template Name:  Social Gate Template
* @package  WordPress Landing Pages
* @author 	David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 

// Add in global templata data
//EDIT - START - defines template information - helps categorize template and provides additional popup information
// Add Landing Page to a specific category. 
$lp_data[$key]['category'] = "miscellaneous"; 
// Add version control to your template.
$lp_data[$key]['version'] = "2.0.0"; 
// Add description visible to the user
$lp_data[$key]['description'] = "Create a social gate to entice visitors to share the landing page link (or any link you want) through their social media networks"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/social-gate-landing-page-demo/"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("Social gate"); 
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

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","headline-color","000000","Headline Text Color","Use this setting to change the Heading Text Color", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","text-color","000000","Main Text Color","Use this setting to change the Main Area Text Color", $options=null);	

// ADD IN META BOX OPTIONS TO YOUR TEMPLATE BELOW
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","content-background","ffffff","Content Background Color","Use this setting to change the content area's background color", $options=null);

/* Dropdown Example */
$options = array('0'=>'100% (Solid)', '10'=>'90%', '20'=>'80%', '30'=>'70%', '40'=>'60%', '50'=>'50%', '60'=>'40%', '70'=>'30%', '80'=>'20%', '90'=>'10%', '100'=>'0% (transparent)');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","opacity","50","Opacity of Content Background", "How see through is the content background? Default 50%", $options);		

/* Textfield Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","unlock-heading","THE CONTENT IS LOCKED!","Unlock Heading","Unlock area heading", $options=null);

/* Textarea Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"textarea","unlock-text","This content is locked! To access the link people share on one of your social networks","Unlock Text","Unlock area text, explaining what to do", $options=null);

/* Textfield Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","facebook-link","http://www.inboundnow.com","Facebook Like URL","This is the url you would like people to like", $options=null);

/* Textfield Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","twitter-link","http://www.inboundnow.com","Twitter URL for Tweet","This is the url you want people to Tweet", $options=null);

/* Textarea Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"textarea","twitter-text","This is the Default Message in the tweet. Make sure it's under 120 characters long","Message in Tweet","This is the message in the tweet", $options=null);

/* Textfield Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","google-link","http://www.inboundnow.com","URL for Google +1","This is the URL you want people to +1 on Google Plus", $options=null);

/* Textfield Example */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","download-link","http://www.LinkToDownload.com","Link to Download/Redirect URL","This is the link to the download, coupon, thank you page, or deliverable the person will receieve after they share via social networks.", $options=null);

/* Dropdown Example */
$options = array('default'=>'Default','dandy'=>'Striped', 'glass'=>'Clear Glass', 'none'=>'None');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","style","default","Style of Social Gate","Dropdown option description", $options);

/* Template Background Settings */
// Select Background Type Setting
$options = array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","background-style","color","Background Settings","Decide how you want the bodies background to be", $options);	
// Full Screen Image Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","background-image","","Background Image","Enter an URL or upload an image for the banner.", $options=null);
// Solid Backgound Color Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","background-color","ffffff","Background Color","Use this setting to change the templates background color", $options=null);
/* Template End Background Settings */		
