<?php
/**
* WordPress Landing Page Config File
* Template Name:  ebook Landing Page Template
*
* @package  WordPress Landing Pages
* @author 	Kirit Dholakiya
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
$lp_data[$key]['description'] = "This is ebook landing page Template"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://Inboundnow.com"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("ebook landing page Template"); 
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
	lp_add_option($key,"media","logo-image-id","/wp-content/uploads/landing-pages/templates/ebook-landing-page/css/images/logo.png","Logo Image","Upload Logo Image of site", $options=null);

// Add Main Ebook Graphics Area
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","main-ebook-area",'<img src="/wp-content/uploads/landing-pages/templates/ebook-landing-page/css/images/book.png" alt="" />','Main Ebook Detail','Write main ebook detail here',$options=null);


// Add Additional Ebook Information
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","additional-ebook-content",'<h2><span>Insert Title</span> Of Whatever You\'re Giving Away Here</h2>
			<p>Enter sweepstakes and receive exclusive offers from Blank.<br /> Unscribe anytime. Blank is not affiliated with the contest. <br /><a href="#">Read official rules.</a></p>',"Additional Ebook Information","Write additional ebook information here", $options=null);

/* Add Background Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","main-background-color","ffffff","Background Color","Use this setting to change the template's background color", $options=null);	

/* Add Sidebar Background Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","sidebar-background-color","ECECEC","Sidebar Background Color","Use this setting to change the template's Sidebar background color", $options=null);	

// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","title-text-color","010101","Title Text Color","Use this setting to change the template's Bottom Title text color", $options=null);

// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","main-content-text-color","314B7B","Bottom Content Text Color","Use this setting to change the template's Content Text Color", $options=null);	

// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","34ad00","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);	

// Add a Radio button
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"radio","display-social","1","Display Social Media Share Buttons","Toggle social sharing on and off", $options);	

