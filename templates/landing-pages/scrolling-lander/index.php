<?php
/**
 * Template Name:  Scrolling Lander Template
 * @package  WordPress Landing Pages
 * @author   David Wells
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
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');

$main_bg_image= lp_get_value($post, $key, 'main-bg-image');

$logo_img = lp_get_value($post, $key, 'logo-img-id');

$main_title_color=lp_get_value($post, $key, 'main-title-text-color');

$top_title_text=lp_get_value($post, $key, 'top-title-text');

$top_title_color=lp_get_value($post, $key, 'title-text-color');

$top_content_area=lp_get_value($post, $key, 'top-content-area');

$top_content_area_color=lp_get_value($post, $key, 'top-content-area-text-color');

$bottom_content_bgcolor=lp_get_value($post, $key, 'bottom-content-bgcolor');

$bottom_content_text_color=lp_get_value($post, $key, 'bottom-content-text-color');

$display_bottom_form=lp_get_value($post, $key, 'display-bottom-form');

$social_display = lp_get_value($post, $key, 'display-social');

$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

function lp_Hex_2_RGB($hex) {
	$hex = @ereg_replace("#", "", $hex);
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php wp_title();?></title>
<?php /* Load all functions hooked to lp_head including global js and global css */
	wp_head(); // Load Regular WP Head
?>
<link rel="stylesheet" type="text/css" href="<?php echo $path;?>style.css"><style type="text/css"></style>

<style type="text/css">
#hero {
	min-width:960px;
	background:url(<?php echo $main_bg_image;?>) top center no-repeat;
}
<?php if(!empty($main_title_color)){ ?>
	  .header-container h1{ color: #<?php echo $main_title_color;?>; }
<?php } 
	  if(!empty($top_title_color)){ ?>
	  .h2-content h2 { color: #<?php echo $top_title_color;?>; }
<?php }
	  if(!empty($top_content_area_color)){ ?>
	  .header-container{ color: #<?php echo $top_content_area_color;?>; }
<?php }
	  if(!empty($bottom_content_bgcolor)){ ?>
	  #content .container {background-color: #<?php echo $bottom_content_bgcolor;?>;}
	  #content, #bottom-content, header#begin, html { background: #<?php echo $bottom_content_bgcolor;?>; }
<?php }
	  if(!empty($bottom_content_text_color)){ ?>
	  #content .container {color: #<?php echo $bottom_content_text_color;?>;}
	  	
<?php } ?>
<?php if ($submit_button_color != "") {
  echo"input[type='submit'] {
	   background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 1));
	   background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 1));
	   background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 1));
	   background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 1)));
	   background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 1));
	   background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 1));}";
}?> 
</style>
<?php 
	do_action('lp_head'); // Load Custom Landing Page Specific Header Items
?>
</head>
<body>

	<div id="header">
        <div class="container">
        	<h1><img src="<?php echo $logo_img;?>" alt="<?php the_title();?>"/></h1>
        </div><!--container ends-->
    </div><!--header ends-->
    
    <div id="hero-back"></div><!--hero back ends-->
    
    <div id="hero">
        <div class="header-container">
            <h1><?php the_title(); ?></h1>
            <div id="top-area">
            <?php echo $top_content_area;?>
        	</div>
            <?php echo do_shortcode( $conversion_area ); /* echos out form/conversion area content */ ?>
            <div style="clear:both;">&nbsp;</div>
        </div><!--header container ends-->
    </div><!--hero ends-->
    
    
	<div id="content">
        <div class="container">
        	 <?php if ($social_display==="1" ) { // Show Social Media Icons?>
            <header id="begin">
                <?php lp_social_media(); // print out social media buttons?>
            </header>
            <div style="clear:both;">&nbsp;</div>
            <?php } ?>
        	<div class="h2-content">
        		<h2><?php echo $top_title_text; ?></h2>
        	</div>
           
            
            <?php 

				echo do_shortcode( $content );
			?>
            
        </div><!--container ends-->
        
  
<?php if ($display_bottom_form==="1" ) { // Show Social Media Icons?>   
    <div id="bottom-content">
    	<div class="container">
		<?php lp_conversion_area(); /* echos out form/conversion area content */ ?>
        </div><!--container ends-->
    </div><!--bottom-content ends-->
       <?php } ?>
  </div><!--content ends-->
<script type="text/javascript">
	jQuery(document).ready(function(){
		var height = parseInt(jQuery("#hero").css("height"));
		jQuery("#hero, #hero-back").css("min-height", height + "px");
		var newheight = height;
		jQuery("#content").css("margin-top", newheight + "px");
		var windowhieght = jQuery(window).height();
		jQuery("#content").css("min-height", windowhieght + "px");
	});
</script>
<?php break;
endwhile; endif; // End wordpress loop

do_action('lp_footer');
wp_footer();
?> 	    
</body></html>