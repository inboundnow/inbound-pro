<?php
/**
* WordPress Landing Page Config File
* Template Name:  elegance-lander Template
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
$lp_data[$key]['category'] = "Premium"; 
// Add version control to your template.
$lp_data[$key]['version'] = "2.0.0"; 
// Add description visible to the user
$lp_data[$key]['description'] = "This is Elegance Landing Page Template"; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/elegance-lander-preview/"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("elegance-lander Template"); 
//EDIT - END

//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; //do not edit this
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); //do not edit this

//*************************************************************************************************
/* Add User Options to Your Landing Page Template Below */
// For more on adding meta-boxes to templates head to:
// http://plugins.inboundnow.com/docs/dev/creating-templates/template-config/
//*************************************************************************************************

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
	lp_add_option($key,"media","logo-image-id","/wp-content/uploads/landing-pages/templates/elegance-lander/images/logo.png","Logo Image","Upload Logo Image of site", $options=null);

// Add Intro text area
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"textarea","intro-text-area",'Seconardy Headline text that lives right below the main headline','Intro Text Area','Write intro text here',$options=null);


/* Image Area 1 */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","image-area1","/wp-content/uploads/landing-pages/templates/elegance-lander/images/image1.jpg","Image Area 1","Upload Image Area 1 image", $options=null);

$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","image-link1",'#','Image 1 Hyperlink (optional)','Hyperlink the first image',$options=null);	
// Add Image text area 1
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","image-text-area1",'<header>
											<h2>Put something here</h2>
											<span class="byline">Maybe here as well I think</span>
										</header>
										<p>You can put all kinds of stuff here, though I’m not sure what. Maybe a 
										little bit about one of those things you do that’s so cool?</p>','Image Text Area 1','Write text here for Image area 1',$options=null);

/* Image Area 2 */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","image-area2","/wp-content/uploads/landing-pages/templates/elegance-lander/images/image2.jpg","Image Area 2","Upload Image Area 2 image", $options=null);

$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","image-link2",'#','Image 2 Hyperlink (optional)','Hyperlink the second image',$options=null);
// Add Image text area 2
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","image-text-area2",'<header>
											<h2>An interesting title</h2>
											<span class="byline">This is also an interesting subtitle</span>
										</header>
										<p>You can put all kinds of stuff here, though I’m not sure what. Maybe a 
										little bit about one of those things you do that’s so cool?</p>','Image Text Area 2','Write text here for Image area 2',$options=null);

/* Image Area 3 */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","image-area3","/wp-content/uploads/landing-pages/templates/elegance-lander/images/image3.jpg","Image Area 3","Upload Image Area 3 image", $options=null);
/* image link 3 */		
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","image-link3",'#','Image 3 Hyperlink (optional)','Hyperlink the third image',$options=null);

// Add Image text area 3
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","image-text-area3",'<header>
											<h2>Oh, and finally ...</h2>
											<span class="byline">Here\'s another intriguing subtitle</span>
										</header>
										<p>You can put all kinds of stuff here, though I’m not sure what. Maybe a 
										little bit about one of those things you do that’s so cool?</p>','Image Text Area 3','Write text here for Image area 3',$options=null);


// Add Secondary conversion Area
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"wysiwyg","secondary-conversion-area",'<div class="row">
												<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.</p>
											</div>','Bottom Content Area','Bottom Content Area',$options=null);


// Add Copy Right Text
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"text","copy-right-textarea",'Copy Right Text Area','Copy Right Text Area','Write copy right text here',$options=null);

// Add a Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","heading-color","444444","Heading Color","Use this setting to change the template's Heading Color", $options=null);	

/* Add Content Background Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","content-background-color","FFFFFF","Content Background Color","Use this setting to change the template's Content background color", $options=null);	

/* Add Content Background Color */
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","content-text-color","696969","Content Text Color","Use this setting to change the template's Content text color", $options=null);	

/* Template Background Settings 
 Select Background Type Setting */
 
 $options = array('default' => 'Default','fullscreen'=>'Fullscreen Image','tile'=>'Tile Background Image','color'=>'Solid Color','repeat-x'=>'Repeat Image Horizontally','repeat-y'=>'Repeat Image Vertically','custom'=>'Custom Css');
 
 $lp_data[$key]['settings'][] = 
	lp_add_option($key,"dropdown","background-style","fullscreen","Background Settings","Decide how you want the bodies background to be", $options);	
	
//Fullscreen Image Setting
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"media","body-background-image","/wp-content/uploads/landing-pages/templates/elegance-lander/images/background.jpg","Background Image","Enter an URL or upload image for the background", $options=null);		
//Solid Background Color
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","solid-background-color","ffffff","Solid Background Color","Use this setting to change the template's Background Color", $options=null);	
	
// Add Colorpicker
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","f2cc1f","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);	

// Add a Radio button
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] = 
	lp_add_option($key,"radio","display-social","1","Display Social Media Share Buttons","Toggle social sharing on and off", $options);		