<?php
/**
 * Template Name:  Paper Template
 * @package  WordPress Landing Pages
 * @author   Your Name Here!
 * @example  example.com/landing-page
 * @version  1.0
 * @since    1.0
 */

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__)); 
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
// Headline Text Color: Use this setting to change the template's headline text color
$headline_color = lp_get_value($post, $key, 'headline-color');
// Sub Headline Text: Use this setting to change the template's headline text color
$sub_headline = lp_get_value($post, $key, 'sub-headline');
// Sub Headline Text Color: Use this setting to change the template's headline text color
$sub_headline_color = lp_get_value($post, $key, 'sub-headline-color');
// Submit Button Background Color: Use this setting to change the template's headline text color
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');
// Background Settings: Use this setting to change the template's headline text color
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Use this setting to change the template's headline text color
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Use this setting to change the template's headline text color
$background_color = lp_get_value($post, $key, 'background-color');
// Display Social Media Share Buttons: Use this setting to change the template's headline text color
$display_social = lp_get_value($post, $key, 'display-social');
$text_box_id = lp_get_value($post, $key, 'text-box-id');
// Convert Hex to RGB Value for submit button
function lp_Hex_2_RGB($hex) {
        $hex = ereg_replace("#", "", $hex);
        $color = array();
 
        if(strlen($hex) == 3) {
            $color['r'] = hexdec(substr($hex, 0, 1) . $r);
            $color['g'] = hexdec(substr($hex, 1, 1) . $g);
            $color['b'] = hexdec(substr($hex, 2, 1) . $b);
        }
        else if(strlen($hex) == 6) {
            $color['r'] = hexdec(substr($hex, 0, 2));
            $color['g'] = hexdec(substr($hex, 2, 2));
            $color['b'] = hexdec(substr($hex, 4, 2));
        }
 
        return $color;
        
}
$RBG_array = lp_Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];

if ( $background_style === "fullscreen" ) {
	$bg_style = 'background: url('.$background_image.') no-repeat center center fixed; 
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
	-ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';
	};

if ( $background_style === "color" ) {
	$bg_style = 'background: #'.$background_color.';';
	};
if ( $background_style === "default" ) {
	$bg_style = 'background: url(/wp-content/uploads/landing-pages/templates/paper/assets/images/flooring.jpg) repeat;';
	};

if ( $background_style === "tile" ) {
	$bg_style = 'background: url('.$background_image.') repeat; ';
	};

if ( $background_style === "repeat-x" ) {
	$bg_style = 'background: url('.$background_image.') repeat-x; ';
	};	

if ( $background_style === "repeat-y" ) {
	$bg_style = 'background: url('.$background_image.') repeat-y; ';
	};

if ( $background_style === "repeat-y" ) {
	$bg_style = 'background: url('.$background_image.') repeat-y; ';
	};	

?>

<!DOCTYPE html>
<!-- From Desk theme by Nearfrog http://wordpress.org/extend/themes/desk-->
<html lang="en-US"><head profile="http://gmpg.org/xfn/11"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="UTF-8">

<title><?php wp_title(); ?></title>
		<?php wp_head(); // Load Regular WP Head
		?>


<!-- leave this for stats please -->

<link href="<?php echo $path;?>assets/css/style.css" media="screen" rel="stylesheet" type="text/css">
  <script src="<?php echo $path;?>assets/js/jquery.widtherize.js"></script>
  <script src="<?php echo $path;?>assets/js/poetry.js"></script>


	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<style type="text/css">
  	body { <?php echo $bg_style;?>}
	 <?php if ($submit_button_color != "") {
          echo"input[type='submit'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
                border: 1px solid #000;}"; } ?>
	<?php if ( $headline_color != "" ) {
			echo "#header h1 { color:#$headline_color;}"; 
			}

		if ( $sub_headline_color != "" ) {
			echo "#header h2 { color:#$sub_headline_color;}"; 
			}

		if ( $form_placement != "left" ) {
			// do something for Top Area Layout option 
		}?>
#share-toggle {
	width: 550px;
margin: auto;
}
</style>
<?php  // Load Custom Landing Page Specific Header Items
lp_head(); 	?>
</head>
<body class="home blog" style="zoom: 1;">

<div id="container">

<div class="push"></div>

<div id="wrapper">
	<div id="header">
		
		<h1><?php the_title(); ?></h1>
		<h2><?php echo $sub_headline; ?></h2>
		
		</div>
	<div id="content">
<div id="post">
		<div id="post-19" class="post-19 post type-post status-publish format-standard hentry category-uncategorized tag-boat tag-lake">
	
		<div class="entry">
		 <?php echo do_shortcode($content);?>
		</div>
			
			<div class="clear"></div>
	</div>
	</div>
	
	

<div id="sidebar">
	<?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
	
	</div><div class="clear" style="height: 35px">
</div>
</div>
<!-- /Content --></div>
<!-- /Wrapper -->
<div id="footer">
	<div id="gradfoot"></div>
		
	<div id="centfoot">	
	<div id="aligner">
		<ul id="footbar-left" class="footbar"><li></li>
			
		</ul>
				<ul id="footbar-center" class="footbar"><li></li>
			
		</ul>
	</div>
		<ul id="footbar-right" class="footbar"><li></li>
			
		</ul>
  <?php if ($display_social==="1" ) { // Show Social Media Icons?>
    <div id="share-toggle" style="padding-botton:20px;">
        <div class="share-text"></div>
        <?php lp_social_media(); // Template helper to print out social media buttons ?>
    </div>
    <?php } ?>
		
		<p class="permfooter">
			<?php echo $text_box_id; ?></p>
	</div>	
	
</div>
</div>
<!-- /Container -->
<?php break;
endwhile; endif; 

do_action('lp_footer');
wp_footer();
?> 

</body></html>