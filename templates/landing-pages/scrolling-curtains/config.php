<?php
/**
* WordPress Landing Page Config File
* Template Name:  Scrolling Curtain
*
* @package  WordPress Landing Pages
* @author 	Kirit Dholakiya, Hudson Atwell, David Wells
*/

do_action('lp_global_config'); // The lp_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = lp_get_parent_directory(dirname(__FILE__)); 

//echo $key;

// Add in global templata data
//EDIT - START - defines template information - helps categorize template and provides additional popup information
// Add Landing Page to a specific category. 
$lp_data[$key]['category'] = "Miscellaneous"; 
// Add version control to your template.
$lp_data[$key]['version'] = "1.0.0.1"; 
// Add description visible to the user
$lp_data[$key]['description'] = "Scrolling Curtain Template brings the curtain.js jQuery script with dynamic template elements to render a beautiful three panel action funnel based on Introduction, Information, & a Call to Action. This template was inspired by HiveMined.org."; 
// Add a live demo link to illustration the page functionality to the user
$lp_data[$key]['features'][] = lp_list_feature("Demo Link","http://demo.inboundnow.com/go/scrolling-curtain"); 
// Description of the landing page visible to the user.
$lp_data[$key]['features'][] = lp_list_feature("Scrolling reveals customized display segments."); 
$lp_data[$key]['features'][] = lp_list_feature("Three stage call to action."); 
//EDIT - END

//DO NOT EDIT - adds template to template selection dropdown 
$lp_data[$key]['value'] = $key; //do not edit this
$lp_data[$key]['label'] = ucwords(str_replace('-',' ',$key)); //do not edit this

//*************************************************************************************************
/* Add User Options to Your Landing Page Template Below */
// For more on adding meta-boxes to templates head to:
// http://plugins.inboundnow.com/docs/dev/creating-templates/template-config/
//*************************************************************************************************

/* Main Logo Image Option */
/*$lp_data[$key]['options'][] = 
	lp_add_option($key,"media","logo-image-id","/wp-content/uploads/landing-pages/templates/{$key}/images/logo.png","Logo Image","Upload Logo Image of site", $options=null);*/

/* Add Heading H1 Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","h1-color","FFFFFF","H1 Color","Use this setting to change the template's H1 color", $options=null);	

/* Add Heading H2 Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","h2-color","FFFFFF","H2 Color","Use this setting to change the template's H2 color", $options=null);	

/* Add Heading H3 Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","h3-color","F89D42","H3 Color","Use this setting to change the template's H3 color", $options=null);	

//Link Title For Splash Page 2
//$lp_data[$key]['options'][] = 	
//lp_add_option($key,"text","link-text-page2","What is It?","Link Text of Splash Page 2","Write Link text of splash page 3 Here...", $options=null);

//Link Title For Splash Page 3
//$lp_data[$key]['options'][] = 	
//lp_add_option($key,"text","link-text-page3","News","Link Text of Splash Page 3","Write Link text of splash page 3 Here...", $options=null);

// Add Splash Page 2 Content
$lp_data[$key]['options'][] = 
	lp_add_option($key,"wysiwyg","splash-page2",'
					
					<center><img src="/wp-content/uploads/landing-pages/templates/'.$key.'/images/batman-logo.png" style="max-height:200px;" ></center>
					<BR><BR>
					
					<h2>Special Midnight Showing</h2>
					
					<blockquote>
					National Cinemas will be holding a special midnight showing for The Dark Night Rises on Sunday July 15th in the following cities: 
						<ul>
							<li> Alton, Mi
							<li> Belton, Mi
							<li> Canton, Mi
							<li> Fairfax, Mi
						</ul>
						
					This is an invite only event so scroll below to request your invites! 
					</blockquote>
					<br><br><br><br>
					
					<h2>Event Rules</h2> 
					<blockquote> 
					
						<ul>
							<li>Be 13 years or older or chaperoned by an adult.</li>
							<li>Each signup is limited to 4 tickets</li>
							<li>Tickets will cost $12.00/adult and $8.00/child; 3D glasses included!</li>
						</ul>
						<div>Scroll down to register!</div>						
					</blockquote>
					
					<br><br><br><br>
					<a href="#register" class="curtain-links">[ CLICK HERE OR SCROLL DOWN ]</a>
					
					', "Content on Panel 2","Use this setting to change the template's content on the second panel", $options=null);

/* Add Splash Page 1 Background Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","splash-page1-background-color","000","Splash Page 1 Background Color","Use this setting to change the template's Splash Page background color", $options=null);	

$lp_data[$key]['options'][] = 
	lp_add_option($key,"media","splash-page1-image-id","","Splash Page 1 Background Image","Upload Splash Page 1 Background Image of site", $options=null);

/* Add Splash Page 2 Background Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","splash-page2-background-color","000","Splash Page 2 Background Color","Use this setting to change the template's Splash Page background color", $options=null);	

$lp_data[$key]['options'][] =  
	lp_add_option($key,"media","splash-page2-image-id","/wp-content/uploads/landing-pages/templates/{$key}/css/background.jpg","Splash Page 2 Background Image","Upload Splash Page 2 Background Image of site", $options=null);

$lp_data[$key]['options'][] = 	
lp_add_option($key,"text","panel-2-height","900","Panel 2 Minium Height","The minimum height we would like the content area of panel 2 to be.", $options=null);


/* Add Splash Page 3 Background Color */
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","splash-page3-background-color","ffffff","Splash Page 3 Background Color","Use this setting to change the template's Splash Page background color", $options=null);	

$lp_data[$key]['options'][] = 
	lp_add_option($key,"media","splash-page3-image-id","","Splash Page 3 Background Image","Upload Splash Page 3 Background Image of site", $options=null);

$lp_data[$key]['options'][] = 	
lp_add_option($key,"text","panel-3-height","900","Panel 3 Minimum Height","The minimum height we would like the content area of panel 3 to be.", $options=null);


// Add a Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","blockquote-transparency-color","323232","Blockquote transparency color","Use this setting to change the template's Blockquote transparency color in Splash Page 2 Content", $options=null);

// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","form-text-color","ffffff","Form Text Color","Use this setting to change the template's form text color.", $options=null);	

// Add Colorpicker
$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","submit-button-color","52aa0a","Submit Button Background Color","Use this setting to change the template's submit button color.", $options=null);	
	
// Add Splash Page 3 Gradient Setting
//$lp_data[$key]['options'][] = 
	//lp_add_option($key,"colorpicker","gradient-color1","ffffff","Splash Page 3 Gradient Color1","Use this setting to change the template's Splash Page 3 Gradient Color 1.", $options=null);	

//$lp_data[$key]['options'][] = 
	//lp_add_option($key,"colorpicker","gradient-color2","f89d42","Splash Page 3 Gradient Color2","Use this setting to change the template's Splash Page 3 Gradient Color 2.", $options=null);	
	
$lp_data[$key]['options'][] = 	
lp_add_option($key,"wysiwyg","footer","<div class='copyright'>InboundNow.com 2013 All rights reserved.</div>
					","Footer Content","Enter footer content here", $options=null);	

$lp_data[$key]['options'][] = 
	lp_add_option($key,"colorpicker","footer-color","ffffff","footer text color","Use this setting to change the template's Footer message color.", $options=null);	
