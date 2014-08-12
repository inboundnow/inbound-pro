<?php 
/*****************************************/
// Template Title:  ebook Landing Page Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__)); 
$path = LANDINGPAGES_UPLOADS_URLPATH.$key.'/';
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();
    
/* Pre-load meta data into variables */ 
$logo_img = lp_get_value($post, $key, 'logo-image-id'); 
$main_ebook_content = lp_get_value($post, $key, 'main-ebook-area');
$additional_ebook_content = lp_get_value($post, $key, 'additional-ebook-content'); 
$main_background_color = lp_get_value($post, $key, 'main-background-color');
$sidebar_background_color = lp_get_value($post, $key, 'sidebar-background-color');
$title_text_color = lp_get_value($post, $key, 'title-text-color');
$main_content_text_color = lp_get_value($post, $key, 'main-content-text-color');
//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$social_display = lp_get_value($post, $key, 'display-social');
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');
    
// Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
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
$RBG_array = Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"]; 
    
?>
<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title><?php wp_title();?></title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<?php /* Load all functions hooked to lp_head including global js and global css */
	wp_head(); // Load Regular WP Head
	?>
    <link rel="stylesheet" href="<?php echo $path;?>css/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $path;?>css/video-js.css" type="text/css" media="all" />

	<script src="<?php echo $path;?>js/html5.js" type="text/javascript"></script>
	<script src="<?php echo $path;?>js/functions.js" type="text/javascript"></script>
    <style type="text/css">
	
	<?php if(!empty($main_background_color)){ ?>
	  	  body{ background-color: #<?php echo $main_background_color;?>; }
	<?php } 
		  if(!empty($sidebar_background_color)){ ?>
		  #content { background-color: #<?php echo $sidebar_background_color;?>; }
	<?php }
		  if(!empty($title_text_color)){ ?>
		   #content h2{ color: #<?php echo $title_text_color;?>; }
	<?php }
		  if(!empty($main_content_text_color)){ ?>
		  body{color: #<?php echo $main_content_text_color;?>;}
	<?php } ?>
	
	/* Landing page form styles */
.lp-form {margin-left: 5px;}
input[type="text"], input[type="email"] {
width: 270px;
padding: 8px 4px 8px 10px;
margin-bottom: 15px;
border: 1px solid #4E3043;
border: 1px solid rgba(78, 48, 67, 0.8);
background: rgba(0, 0, 0, 0.15);
border-radius: 2px;
box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2), inset 0 1px 1px rgba(0, 0, 0, 0.1);
-webkit-transition: all 0.3s ease-out;
-moz-transition: all 0.3s ease-out;
-ms-transition: all 0.3s ease-out;
-o-transition: all 0.3s ease-out;
transition: all 0.3s ease-out;
font-family: Helvetica,Arial;
color: $484848;
font-size: 13px;
}
.lp-input-label {min-width: 100px;
display: inline-block;}
input[type="submit"] {
width: 270px;
margin-left: 100px;
padding: 8px 5px;
background: #EAEAEA;
background: -moz-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
background: -ms-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
background: -o-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba(99, 64, 86, 0.5)), to(rgba(76, 49, 65, 0.7)));
background: -webkit-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
background: linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
border-radius: 5px;
border: 1px solid #4E3043;
box-shadow: inset 0 1px rgba(255, 255, 255, 0.4), 0 2px 1px rgba(0, 0, 0, 0.1);
cursor: pointer;
-webkit-transition: all 0.3s ease-out;
-moz-transition: all 0.3s ease-out;
-ms-transition: all 0.3s ease-out;
-o-transition: all 0.3s ease-out;
transition: all 0.3s ease-out;
color: #fff;
text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
font-size: 22px;
font-weight: bold;
font-family: Helvetica,Arial;
}
#lp_container {margin-left: -20px;}
p, .lp-span, label {font-size: 12px;}
.lp-span {text-transform: none; margin-bottom: 8px;}
.lp-input-label .lp-span {
  _display: inline;
  color:red;
}
/* end form styles */
<?php if ($submit_button_color != "") {
  echo"input[type='submit'] {
	   background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
	   background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));}";
}?> 
#main-wrapper {
	width:900px;
	margin: auto;
	position: relative;
}	

#lp_container .lp-form{
	text-align:center;
}

</style>
<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items
	?>
</head>
<body class="type-3">


    <div id="main-wrapper">
    		<?php if ($social_display==="1" ) { // Show Social Media Icons?>
    
        <?php lp_social_media('vertical'); // print out social media buttons?>

    <?php }?>
	<!-- Shell -->
	<div class="shell">
		<!-- Sidebar -->
		<aside id="sidebar">
			<div class="item">
				<?php echo $main_ebook_content;?>
			</div>
			<?php echo $additional_ebook_content;?>
		</aside>	
		<!-- END Sidebar -->
		<!-- Content -->
		<section id="content">
			<!-- Logo -->
			<h1 id="logo"><a href="#" title="home"><img src="<?php echo $logo_img;?>" alt="lead player" title="<?php the_title();?>" /></a></h1>
            <h2 class="pull-up"><?php the_title();?></h2>
			<?php
			
			echo do_shortcode( $content );
			
            
			echo do_shortcode( $conversion_area ); /* echos out form/conversion area content */ ?>          
		
		</section>	
		<!-- END Content -->
		<div class="cl">&nbsp;</div>
        <style type="text/css">
		.sharrre .button {
			width: 60px;
			padding: 4px;
		}
		#lp-social-buttons {
		left: 920px;}


		</style>
	</div>
    <!-- END Shell -->
    </div>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		var windowhieght = jQuery(window).height();
		jQuery("#content").css("min-height", windowhieght + "px");
	});
</script>
<?php break; 
endwhile; endif; // End wordpress loop

do_action('lp_footer');
wp_footer();
?>    
</body>
</html>