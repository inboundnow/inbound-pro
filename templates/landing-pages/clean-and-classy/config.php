<?php
/**
* WordPress Landing Page Config File
* Template Name:  Clean and Classy Template
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
$lp_data[$key]['category'] = "miscellaneous";
$lp_data[$key]['version'] = "2.0.0";
// Add description visible to the user
$lp_data[$key]['description'] = "This is your template's description..";
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/clean-and-classy/");
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("This template was created with the workless framework from ikreativ and is HTML5 and CSS3. Nice clean and classy.");
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

// Add a media uploader field to your landing page options
$lp_data[$key]['settings'][] =
	lp_add_option($key,"media","logo","/wp-content/uploads/landing-pages/templates/clean-and-classy/assets/img/inbound-logo-horizontal.png","Logo Image","Enter an URL or upload an image for the banner. Recommended width 260px", $options=null);

/* disabled for now. Perhaps in future versions
// Add Colorpicker
$lp_data[$key]['settings'][] =
	lp_add_option($key,"colorpicker","content-color","ffffff","Content Background Color","Use this setting to change the template's main content area color", $options=null);

// Add Colorpicker
$lp_data[$key]['settings'][] =
	lp_add_option($key,"colorpicker","content-text-color","000000","Content Text Color","Use this setting to change the content text color", $options=null);
*/
// Add a dropdown toggle to the landing page options panel
$options = array('right'=>'Conversion Area on right','left'=>'Conversion Area on left');
$lp_data[$key]['settings'][] =
	lp_add_option($key,"dropdown","sidebar","right","Page Layout","Align Conversion/Form Area to the left or the right", $options);

// Add Colorpicker
$lp_data[$key]['settings'][] =
	lp_add_option($key,"colorpicker","submit-button-color","5baa1e","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);

/* WYSIWYG Example */
$lp_data[$key]['settings'][] =
	lp_add_option($key,"wysiwyg","link-area","","Top Right Links","Top Link Area. Leave Blank for no links. Make sure links are in a list format", $options=null);

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

// Add a text input field to the landing page options panel
$lp_data[$key]['settings'][] =
	lp_add_option($key,"text","text-box-id","Footer Text. Company ©","Text Field Label","Text field Description", $options=null);

// Add a radio button option to your theme's options panel.
$options = array('1' => 'on','0'=>'off');
$lp_data[$key]['settings'][] =
	lp_add_option($key,"radio","display-social","1","Display Social Media Share Buttons","Toggle social sharing on and off", $options);

