<?php
/**
 * Template Name:  Minimal Responsive Template
 * @package  WordPress Landing Pages
 * @author   Dholakiya kirit
 * @example  example.com/landing-page
 * @version  1.2
 * @since    1.0
 */

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

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
//global $wpquery;
//query_posts('showposts=5');
//echo $GLOBALS['wp_query']->request;
/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */

// ColorScheme: Choose the Main Color Scheme
$text_color_picker = lp_get_value($post, $key, 'text-color-picker');

$meta_description=lp_get_value($post, $key, 'meta-description-text-id');

$meta_keyword=lp_get_value($post, $key, 'meta-keyword-text-id');

$logo=lp_get_value($post, $key, 'logo-file-id');

$page_intro = lp_get_value($post, $key, 'page-intro-text-id');

$main_shot_hero_content=lp_get_value($post, $key, 'main-hero-shot-id');

$secondary_content = lp_get_value($post, $key, 'seconday-content-area-id');

$bottom_content = lp_get_value($post, $key, 'bottom-content-area-id');

$copy_right_text = lp_get_value($post, $key, 'copy-right-text-id');

$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

$facebook_link=lp_get_value($post, $key, 'facebook-link');
  $linkedin_link=lp_get_value($post, $key, 'linkedin-link');
  $twitter_link=lp_get_value($post, $key, 'twitter-link');

//  $background_color = lp_get_value($post, $key, 'body-background');

 // $alt_text_color = lp_get_value($post, $key, 'alt-text-color');


//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');

$RBG_array = lp_Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8" />
<title><?php wp_title(); ?></title>
<link rel="stylesheet" href="<?php echo $path; ?>css/inuit.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/fluid-grid16-1100px.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/eve-styles.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/custom.css" />
<?php wp_head(); // Load Regular WP Head ?>
<style type="text/css">
   <?php if ($text_color_picker != "") {
            echo "h1, h2, h3, h4, h5, h6, a { color: #$text_color_picker;}
            #footer {background-color: #$text_color_picker; } ";
            echo "a.button {
                    background-color: #$text_color_picker;
                    background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#$text_color_picker), to(#$text_color_picker));
                    background-image: -moz-linear-gradient(top, #$text_color_picker, #$text_color_picker);
                    background-image: -o-linear-gradient(top, #$text_color_picker, #$text_color_picker);
                    background-image: -webkit-linear-gradient(top, #$text_color_picker, #$text_color_picker);
                    background-image: -ms-linear-gradient(top, #$text_color_picker, #$text_color_picker);
                    border: 1px solid #$text_color_picker;
                    box-shadow: 0 1px 0 0 #$text_color_picker inset;}";
        }
        if ($submit_button_color != "") {
          echo"input[type='submit'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
                border: 1px solid #000;}";
           }
           ?>
           #inbound-conversion-area .gform_wrapper .top_label input.medium, #inbound-conversion-area .gform_wrapper .top_label select.medium {
           width: 95%;
           }
           #inbound-conversion-area .gform_wrapper .gform_footer {
           padding: 0px 0 10px 0;}
           #inbound-conversion-area .gform_wrapper .gform_footer input.button, #inbound-conversion-area .gform_wrapper .gform_footer input[type=submit] {
           font-size: 1.5em;
           }
           #inbound-conversion-area .gform_wrapper {

           padding-left: 20px;
           padding-right: 20px;
           }
</style>

<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
<script src="<?php echo $path; ?>js/respond-min.js" type="text/javascript"></script>
<script src="<?php echo $path; ?>js/jquery.flexslider-min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $path; ?>css/flexslider.css" />

<!--Hide the hr img because of ugly borders in IE7. You can change the color of border-top to display a line -->
<!--[if lte IE 7]>

	<style>
		hr { display:block; height:1px; border:0; border-top:1px solid #fff; margin:1em 0; padding:0; }
		.grid-4{ width:22% }
	</style>
<![endif]-->


</head>

<body>
<div class="wrapper"><img src="<?php echo $logo;?>" alt="something" />

  <!--These are just samples, use your own icons. If you use larger ones, make sure too change the css-file to fit them in.
                       Dont´t forget to place your links -->
  <div class="social"> <a href="<?php if(!empty($facebook_link)){ echo $facebook_link;}else{ echo '#';}?>" title="facebook"><img src="<?php echo $path; ?>img/facebook.png" width="20" height="20" alt="facebook"></a> <a href="<?php echo $twitter_link;?>" title="twitter"><img src="<?php echo $path; ?>img/twitter.png" width="20" height="20" alt="twitter"></a> <a href="<?php echo $linkedin_link;?>" title="linkedin"><img src="<?php echo $path; ?>img/linkedin.png" width="20" height="20" alt="linkedin"></a> </div>

</div>
<!--end of wrapper div-->
<div class="clear"></div>

<!--========================================================================== Intro and FlexSlider =====================================================================================-->

<div class="wrapper">
  <div class="grids top">
    <div class="grid-6 grid intro">
      <h2>
        <?php lp_main_headline(); ?>
      </h2>
      <p><?php echo $page_intro;?> </p>
    </div>
    <!--end of slogan div-->

    <div class="grid-10 grid hero-shot"> <?php echo do_shortcode($main_shot_hero_content);?> </div>
    <!--end of div grid-10-->
  </div>
  <!--end of div grids-->
  <!--<span class="slidershadow"></span>-->

</div>
<!--end of div wrapper-->

<!--========================================================================== Content Part 1 =====================================================================================-->

<div id="content-area" class="wrapper">
<div class="grids">
<div class="grid-10 grid"> <?php echo do_shortcode( $content ); ?> </div>
<!--end of grid-10-->

<div class="grid-6 grid grey" id="inbound-conversion-area">
  <?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
  <div> </div>
  <!--end of grids-->

</div>
<!--end of wrapper-->
<hr />

<!--========================================================================== Content Part 2 =====================================================================================-->
<div class="wrapper">
  <div class="grids"> <?php echo $secondary_content;?> </div>
  <!--end of grids-->
</div>
<!--end of wrapper-->
<hr />

<!--========================================================================== Content Part 3 =====================================================================================-->
<div class="wrapper"> <?php echo $bottom_content;?> </div>
<!--end of wrapper-->

<!--========================================================================== Footer =====================================================================================-->

<script type="text/javascript">
<!--Outdated browsers warning/message and link to Browser-Update. Comment or delete it if you don´t want to use it-->
var $buoop = {}
$buoop.ol = window.onload;
window.onload=function(){
 try {if ($buoop.ol) $buoop.ol();}catch (e) {}
 var e = document.createElement("script");
 e.setAttribute("type", "text/javascript");
 e.setAttribute("src", "http://browser-update.org/update.js");
 document.body.appendChild(e);
}
</script>
<?php break; endwhile; endif; // end WordPress Loop
do_action('lp_footer'); // Load custom landing page footer items
wp_footer(); // Load regular WordPress Footer
?>
</body>
</html>