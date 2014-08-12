<?php
/**
* Template Name:  Clean Professional
*
* @package  WordPress Landing Pages
* @author   David Wells
* @link(homepage, http://www.inboundnow.com)
* @version  1.4
* @example link to example page
*/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');


//echo LANDINGPAGES_UPLOADS_URLPATH;exit;
/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

// wp_register_style('normalize', $path . 'assets/css/normalize.css');
// wp_enqueue_style('normalize', $path . 'assets/css/normalize.css');
// Convert Hex to RGB Value for submit button
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
/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');

// Sub Headline Text: Sub headline text goes here
$sub_headline = lp_get_value($post, $key, 'sub-headline');
// Top & Middle Background Color: Sub headline text goes here
$top_color = lp_get_value($post, $key, 'top-color');
// Footer Background Color: Sub headline text goes here
$bottom_color = lp_get_value($post, $key, 'bottom-color');
// Display Social Media Share Buttons: Sub headline text goes here
$display_social = lp_get_value($post, $key, 'display-social');
// Text Above Form: Sub headline text goes here
$form_headline = lp_get_value($post, $key, 'form-headline');
// Background Image: Sub headline text goes here
$bg_image = lp_get_value($post, $key, 'bg-image');
// Main BG Color: Sub headline text goes here
$main_bg_color = lp_get_value($post, $key, 'main-bg-color');
// Logo Image: Sub headline text goes here
$logo = lp_get_value($post, $key, 'logo');
$link_area = lp_get_value($post, $key, 'link-area');
$top_text_color = lp_get_value($post, $key, 'top-text-color');
$middle_text_color = lp_get_value($post, $key, 'middle-text-color');
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');
$footer_text = lp_get_value($post, $key, 'footer-text');


$RBG_array = lp_Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];

?>


<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <!--  Define page title -->
  <title><?php wp_title(); ?></title>
  <meta charset="utf-8" />

<link rel="stylesheet" href="<?php echo $path; ?>assets/css/normalize.css">
<link rel="stylesheet" href="<?php echo $path; ?>assets/css/main.css">
<link href="<?php echo $path; ?>assets/css/font.css" rel="stylesheet" type="text/css">
<?php wp_enqueue_script("jquery");
wp_enqueue_script('modernizr', $path . 'assets/js/modernizr.js');
?>

<style type="text/css">
footer {background-color: #<?php echo $bottom_color;?>}
body .share #inbound-social-inbound-social-buttons {
	margin-left:auto;
	margin-right:auto;
}
<?php if ( $bg_image != "" ) {
	echo '.middle-background { background: url('.$bg_image.') no-repeat;}';}

if ( $top_color != "" ) {
	// do something for Top & Middle Background Color option
	 echo ".topwrapper, #middle-area { background:#$top_color;}";
	}
if ( $bottom_color != "" ) {
	// do something for Footer Background Color option
	echo "footer, body {background-color: #$bottom_color;}";
	}
echo $bottom_color;
if ( $display_social != "1" ) {
	// do something for Display Social Media Share Buttons option
	}
if ( $top_text_color != "" ) {
	echo "#share h2, #share, #the-content {color: #$top_text_color;}";
	}
if ( $middle_text_color != "" ) {
	echo "#headline-area h1, .lp-form-area, #lp_container {color: #$middle_text_color;}";
	} ?>
	<?php if ($submit_button_color != "") {
		  echo"input[type='submit'] {
		       background: -moz-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
		       background: -ms-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
		       background: -o-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
		       background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 1)));
		       background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
		       background: linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));}";
            }?>
  #inbound-conversion-area .gform_wrapper .top_label input.medium, #inbound-conversion-area .gform_wrapper .top_label select.medium {
  width: 100%;
  }
  #inbound-conversion-area .gform_wrapper .gform_footer {
  padding: 0px 0 10px 0;}
  #inbound-conversion-area .gform_wrapper .gform_footer input.button, #inbound-conversion-area .gform_wrapper .gform_footer input[type=submit] {
  font-size: 1.5em;
  }
  #inbound-conversion-area h3, #inbound-conversion-area h2, #inbound-conversion-area h4, #inbound-conversion-area span, #inbound-conversion-area p {
  	text-align: center;
  }
</style>

<?php wp_head(); // Load Regular WP Head
	 do_action('lp_head'); // Load Custom Landing Page Specific Header Items
?>
</head>

<body class="clean-prof-lander" data-twttr-rendered="true">


		<!--[if lt IE 7]>
			<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->

		<!-- Add your site or application content here -->
		<div class="wrapper topwrapper">
			<div id="headerwrap">
			<header class="clearfix">
				<div class="logo">

						<h1><img src="<?php echo $logo; ?>"></h1>

				</div>
				<div class="top-right-area">
					<?php echo $link_area;?>
				</div>
			</header>
		</div>
		</div>

		<div class="middle-background">

			<div class="wrapper middlewrap" id="result">
				<div id="headline-area" style="position: relative; overflow: hidden; direction: ltr;" class="">
					<h1><?php the_title(); ?></h1>
				</div>

				<div class="lp-form-area" id="inbound-conversion-area">
					  <?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
				</div>


			</div>
		</div>

		<div id="middle-area" class="wrapper">
			<div class="share">
				<h2><?php echo $sub_headline; ?></h2>
				<!-- AddThis Button BEGIN -->
				 <?php lp_social_media(); // print out social media buttons?>
				<!-- AddThis Button END -->
				<div id="the-content"><?php echo do_shortcode( $content ); ?></div>
			</div>

		</div>

		<footer>
			<div class="bottomwrap clearfix">
				<div class="normal">
					<div class="footer-text">
						<p><?php echo get_bloginfo('name') . " &copy; " . date("Y");?></p>
					</div>
					<div class="tag-line-area">
						<p><?php echo $footer_text;?></p>
					</div>
				</div>
			</div>
		</footer>

<?php break; endwhile; endif;

do_action('lp_footer');
wp_footer();
?>
	</body></html>