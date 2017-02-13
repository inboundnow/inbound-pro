<?php
/*****************************************/
// Template Title:  Simple Two Column Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'assets/libraries/shareme/library.shareme.php');

/* Declare Template Key */
$key = basename(dirname(__FILE__));
$path = LANDINGPAGES_URLPATH.'templates/'.$key.'/';
$url = plugins_url();

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH.'templates/'.$key.'/config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
$main_headline = get_field( 'lp-main-headline', $post->ID , false );
$content = get_field( 'simple-two-column-main-content', $post->ID , false );
$conversion_area = get_field( 'simple-two-column-conversion-area-content', $post->ID , false );
$content_color = get_field( 'simple-two-column-content-color', $post->ID , false );
$body_color = get_field( 'simple-two-column-body-color', $post->ID , false );
$sidebar_color = get_field( 'simple-two-column-sidebar-color', $post->ID , false );
$text_color = get_field( 'simple-two-column-content-text-color', $post->ID , false );
$sidebar_text_color = get_field( 'simple-two-column-sidebar-text-color', $post->ID , false );
$headline_color = get_field( 'simple-two-column-headline-color', $post->ID , false );
$sidebar = get_field( 'simple-two-column-sidebar', $post->ID , false );
$social_display = get_field( 'simple-two-column-display-social', $post->ID , false );
$submit_button_color = get_field( 'simple-two-column-submit-button-color', $post->ID , false );

// Get Colorscheme
$submit_color_scheme = inbound_color_scheme($submit_button_color, 'int');

// Get lighter submit color
$top_grad_submit = inbound_color($submit_color_scheme, 35);

$RBG_array = inbound_Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];

$RBG_array_1 = inbound_Hex_2_RGB($top_grad_submit);
$red_1 = $RBG_array_1['r'];
$green_1 = $RBG_array_1["g"];
$blue_1 = $RBG_array_1["b"];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head profile="http://gmpg.org/xfn/11"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title(); ?></title>
<link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css" type="text/css" media="screen">
<?php wp_enqueue_script('sharrre', LANDINGPAGES_URLPATH . 'assets/libraries/sharrre/jquery.sharrre-1.3.3.min.js', array('jquery')); ?>

<style media="screen" type="text/css">

<?php

if ($sidebar_color !="") {
	echo "#right { background-color: $sidebar_color;}"; // change sidebar color
}
if ($content_color !="") {
	echo "#left {background-color: $content_color;}"; // change header color
}

if ($body_color !="") {
	echo "body {background-color: $body_color;}"; // Change Body BG color
}
if ($text_color !="") {
	echo "#left-content {color: $text_color;}";
}
?>
<?php if ($sidebar_text_color !="") {
	echo "#right-content {color: $sidebar_text_color;} input[type=\"text\"], input[type=\"email\"] {
								border: 1px solid $sidebar_text_color;
								opacity: 0.8;} ";
}
?>
<?php
if ($sidebar === "left" ) {
	echo "#right {left:0px;} #left {right: 0;} #left-content {padding-left: 40px;} #social-share-buttons {margin-left: -115px !important;}";
} else {
	echo "#left {left: 0;}";
}

if ($submit_button_color != "") {
	echo"input[type='submit'] {
	   background: -moz-linear-gradient(rgba($red_1,$green_1,$blue_1, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -ms-linear-gradient(rgba($red_1,$green_1,$blue_1, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -o-linear-gradient(rgba($red_1,$green_1,$blue_1, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red_1,$green_1,$blue_1, 0.5)), to(rgba($red,$green,$blue, 0.7)));
	   background: -webkit-linear-gradient(rgba($red_1,$green_1,$blue_1, 0.5), rgba($red,$green,$blue, 0.7));
	   background: linear-gradient(rgba($red_1,$green_1,$blue_1, 0.5), rgba($red,$green,$blue, 0.7));
		border: 1px solid #000;}";
   }
?>
#inbound-social-inbound-social-buttons {
	text-align: center;
	background: rgba(0,0,0,.6) !important;
	padding: 0;
	margin-top: 10px !important;
	padding-bottom: 10px !important;
	padding-top: 10px !important;
	width: 470px !important;
	z-index: 99999999999999;
	/* position: fixed; */
	/* bottom: 0px; */
	margin: auto !important;
}
#social-holder {
	position: fixed;
	bottom: 0px;
	width: 100%;
	z-index: 9999999999999999;

}
.inbound-social-facebook.inbound-social-button {
	padding-left: 12px;
}


</style>
<?php /* Load all functions hooked to lp_head including global js and global css */
			do_action('wp_head'); // Load Regular WP Head
			do_action('lp_head'); // Load Custom Landing Page Specific Header Items
		?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
		var h = jQuery(document).height();
		$("#right").height(h + 200);
		$("#left").height(h + 200);
    });

</script>
</head>
<body>
<?php
if ($social_display==="1" ) { // Show Social Media Icons?>
<div id="social-holder">
   <?php lp_social_media(); // print out social media buttons?>
</div>
<?php
}
?>
<div class="container">


<div id="content-wrapper">

<div id="left">
	<div id="left-content">
		<h1><?php echo $main_headline; ?></h1>
		<?php echo wpautop($content); ?>
	</div> <!-- end left-content -->
</div> <!-- end left -->

<div id="right">
	<div id="right-content">
 <?php echo  wpautop($conversion_area); /* Print out form content */ ?>
	</div> <!-- end right-content -->
</div> <!-- end left-content -->


<style type="text/css">

.sharrre .button {
	width: 60px;
	padding: 4px;
}

</style>


</div><!-- end content-wrapper -->

 </div><!-- end container -->
<?php
 break;
 endwhile;
 endif; // end wordpress loop

?>
<footer>
	<?php
	do_action('lp_footer');
	do_action('wp_footer');
	?>
</footer>

</body>
</html>