<?php
/*****************************************/
// Template Title: Super Slick
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Sharrreme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_URLPATH.'templates/'.$key.'/';
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load Regular WordPress $post data and start the loop */
if (have_posts()) : while (have_posts()) : the_post();

/**
 * Step 2: Pre-load meta data into variables.
 * - These are defined in this templates config.php file
 * - The config.php values create the metaboxes visible to the user.
 * - We define those meta-keys here to use them in the template.
 */
	//prepare content
	$content = lp_content_area($post,null,true);
	$headline_color = lp_get_value($post, $key, 'headline-color');
	$sub_headline_color = lp_get_value($post, $key, 'sub-headline-color');
	$sub_headline = lp_get_value($post, $key, 'sub-headline');
	$top_color = lp_get_value($post, $key, 'top-color');
	$top_text_color = lp_get_value($post, $key, 'top-text-color');
	$bottom_text_color = lp_get_value($post, $key, 'bottom-text-color');
	$bottom_color = lp_get_value($post, $key, 'bottom-color');
	$bottom_content = lp_get_value($post, $key, 'wysiwyg-content');
	$form_placement = lp_get_value($post, $key, 'form-placement');
	$social_display = lp_get_value($post, $key, 'display-social');
	$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

	// Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
        $hex = preg_replace("/#/", "", $hex);
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head profile="http://gmpg.org/xfn/11"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title><?php wp_title(); ?></title>
	<?php /* Load all functions hooked to lp_head including global js and global css */
		wp_head(); // Load Regular WP Head
		do_action('lp_head'); // Load Custom Landing Page Specific Header Items
?>

<link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css" type="text/css" media="screen">

<link rel="stylesheet" id="farbtastic-css" href="<?php echo $path; ?>assets/css/farbtastic.css" type="text/css" media="all">
<style type="text/css">
.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}
  #logotopend { width: 940px;}
<?php if ($top_color !="") {
            echo "#body_wrapper {background-color: #$top_color;} #footer h1 {border-bottom: 1px solid #$top_color;}"; }?>
<?php if ($top_text_color !="") {
            echo "#slideshow-inner {color: #$top_text_color;}"; }?>
<?php if ($headline_color !="") {
			// change headline color
            echo"h1 {color: #$headline_color;}";  } ?>
<?php if ($sub_headline_color !="") {
                echo"#tagline {color: #$sub_headline_color;} #header {border-bottom: 1px solid #$sub_headline_color;}"; // change tagline color
         }
        ?>


<?php if ($form_placement==="right") { echo"#slideshow-inleft {float: right;} #slideshow1 {float: left;}";}?>

<?php if ($submit_button_color != "") {
		  echo"input[type='submit'] {
		       background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
		       background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
		       background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
		       background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
		       background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
		       background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));}";
            }?>
<?php if ($bottom_text_color !="") {
			// change bottom text color
            echo"#footer {color: #$bottom_text_color;}";  } ?>
<?php if ($bottom_color !="") {
			// Change Body BG color
            echo "body, #footer-outside {background-color: #$bottom_color;}"; } ?>
 </style>
<!--
               -->
  <script src="<?php echo $path; ?>assets/js/jquery.widtherize.js"></script>
  <script src="<?php echo $path; ?>assets/js/poetry.js"></script>

<script>
jQuery(document).ready(
    function() {
     render_minis(jQuery('.mini_verse'));
     jQuery('#hi p').widtherize({'width': 200});
     jQuery('#logotopend h1').widtherize({'width': 980});

     var lineheight = parseInt(jQuery('#logotopend h1').css("font-size"));
     var newlineheight = lineheight * .851;
     jQuery('#logotopend h1').css("line-height", newlineheight + "px" );
     var newfontsize = lineheight * .766;
     jQuery('#tagline').css("font-size", newfontsize + "px");

}
);
</script>
</head>

<body class="home blog slick-lander">
<div id="body_wrapper">
<div id="header">


	<div class="social">

		<div class="social-icon-rss">
		<a href="http://wp-themes.com/?feed=rss2"></a>
		</div>




	</div>

	<div id="logotopend"><h1><?php lp_main_headline(); ?></h1>
	</div>
	<div id="tagline"><?php echo $sub_headline;?></div>

</div>



<div id="slideshow-inner">

	<div id="slideshow-inleft">

<?php lp_conversion_area(); /* echos out form/conversion area content */ ?>

		</div>
<div id="slideshow1">

		<div id="slideshow-inright">

		<?php if ($content != "") {
            echo $content;
        }
     else {
			echo "<img src='/wp-content/plugins/landing-pages/templates/super-slick/assets/images/placeholder-hero.png'>";
		} ?>

		</div>
	</div>
<?php if ($social_display==="1" ) { // Show Social Media Icons?>
	<?php lp_social_media(); // print out social media buttons?>
	<style type="text/css">
	#lp-social-buttons {clear: both;
	margin: auto;
	width: 460px;}
	  #lp-social-buttons {width: 517px;
margin: auto;
}
.sharrre .googleplus {
width: 90px !important;
}
.sharrre .pinterest {
    width: 75px !important;
}
.twitter {
    width: 111px;
}
.sharrre .button {
width: 106px;}
.linkedin {
margin-right: -14px;}
	#slideshow-inner {
	margin-left: auto;
	margin-right: auto;
	padding-top: 15px;
	width: 960px;
	padding-bottom: 10px;
	}
	</style>
<?php  } ?>

	</div>

</div> <!-- end body_wrapper -->


<div id="footer-outside" class="slick-footer">

<div id="footer">

<div id="bottom-content">
	 <?php echo  wpautop($bottom_content);?>
</div> <!-- end bottom-content -->

</div> <!-- end footer -->

</div> <!-- end footer-outside -->


<?php
break; endwhile; endif; // End wordpress loop

do_action('lp_footer');
wp_footer();
?>

</body></html>