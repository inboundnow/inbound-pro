<?php
/**
 * Template Name:  Clean and Classy Template
 * @package  WordPress Landing Pages
 * @author   David Wells
 * @example  example.com/landing-page
 * @version  1.0
 * @since    1.0
 */

/* Based off of the workless framework http://workless.ikreativ.com/, A project by Scott Parry. Originally based off http://www.html5boilerplate.com/ and http://twitter.github.com/bootstrap/. */

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

// Convert Hex to RGB Value for submit button
function lp_Hex_2_RGB($hex) {
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
/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */

//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
// Logo Image: Enter an URL or upload an image for the banner. Recommended width 260px
$logo = lp_get_value($post, $key, 'logo');
// Page Layout: Enter an URL or upload an image for the banner. Recommended width 260px
$sidebar = lp_get_value($post, $key, 'sidebar');
// Top Right Links: Enter an URL or upload an image for the banner. Recommended width 260px
$link_area = lp_get_value($post, $key, 'link-area');
// Background Settings: Enter an URL or upload an image for the banner. Recommended width 260px
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Enter an URL or upload an image for the banner. Recommended width 260px
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Enter an URL or upload an image for the banner. Recommended width 260px
$background_color = lp_get_value($post, $key, 'background-color');
// Text Field Label: Enter an URL or upload an image for the banner. Recommended width 260px
$text_box_id = lp_get_value($post, $key, 'text-box-id');
// Display Social Media Share Buttons: Enter an URL or upload an image for the banner. Recommended width 260px
$display_social = lp_get_value($post, $key, 'display-social');
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');


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
	$bg_style = 'background: url('.LANDINGPAGES_UPLOADS_URLPATH.'/clean-and-classy/assets/img/bg_blueish.jpg) repeat;';
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

<!doctype html>
<!-- based off http://workless.ikreativ.com/ from Scott Parry ikreativ.com -->
<!--[if lt IE 7]>
	<html class="nojs ms lt_ie7" lang="en">
<![endif]-->

<!--[if IE 7]>
	<html class="nojs ms ie7" lang="en">
<![endif]-->

<!--[if IE 8]>
	<html class="nojs ms ie8" lang="en">
<![endif]-->

<!--[if gt IE 8]>
	<html class="nojs ms" lang="en">
<![endif]-->

<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<!-- Mobile Viewport
    <meta name="viewport" content="width=device-width">-->

	<title><?php wp_title(); ?></title>
		<?php wp_head(); // Load Regular WP Head
		?>

		<link href="<?php echo $path;?>assets/css/workless.css" rel="stylesheet">
        <link href="<?php echo $path;?>assets/css/scaffolding.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/typography.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/forms.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/tables.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/alerts.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/tabs.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/pagination.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/breadcrumbs.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/icons.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/helpers.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/print.css" rel="stylesheet">
		<link href="<?php echo $path;?>assets/css/application.css" rel="stylesheet">

	<!-- All JavaScript at the bottom, except modernizr -->
  	<script type="text/javascript" src="<?php echo $path;?>assets/js/modernizr.js"></script>

    <!--[if lt IE 8]>
        <script src="assets/js/ie_font.js"></script>
    <![endif]-->
        <style type="text/css">
        body { <?php echo $bg_style;?>}

 <?php if ($submit_button_color != "") {
          echo"input[type='submit'], #inbound-form-wrapper button, #inbound-form-wrapper input[type='button'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
                border: 1px solid #000;}"; } ?>
 <?php if ($sidebar == "left") {
	echo ".two_third { width: 66%; float: right; } .one_third { width: 29%; margin-right: 0px; }";
	$leftclass = "left-class"; } else { $leftclass = "right-class";}	?>

	#inbound-conversion-area .gform_wrapper .top_label input.medium, #inbound-conversion-area .gform_wrapper .top_label select.medium , #inbound-form-wrapper .inbound-field input {
	width: 100%;
	}
	#inbound-conversion-area .gform_wrapper .gform_footer {
	padding: 0px 0 10px 0;}
	#inbound-conversion-area .gform_wrapper .gform_footer input.button, #inbound-conversion-area .gform_wrapper .gform_footer input, #inbound-form-wrapper button[type=submit] {
	font-size: 1.5em;
	}
        </style>
</head>
<?php  // Load Custom Landing Page Specific Header Items
do_action('lp_head'); 	?>

<body id="top">

<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6 -->
<!--[if lt IE 7]>
	<p>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p>
<![endif]-->

<!-- wrapper -->
<section class="boxed">

	<!-- main -->
	<section id="main">

		<!-- logo -->
		<div id="logo">
			<img src="<?php echo $logo; ?>">

        </div>
        <!-- /logo -->

        <!-- nav -->
        <nav id="primary">
			<div id="link-area"><?php echo $link_area;?></div>
		</nav>
		<!-- /nav -->

		<hr>


		<!-- header -->
		<header class="bg_shadow">
        	<h1><?php the_title(); ?></h1>
		</header>
		<!-- /header -->

        <div class="two_third <?php echo $leftclass;?>">
           <?php echo do_shortcode( $content ); ?>
        </div>

   <div class="one_third last" id="inbound-conversion-area">
          	<?php echo do_shortcode( $conversion_area); /* Print out form content */ ?>
        </div>



	   <hr>

	   <!-- footer -->
	   	   <footer id="footer" >
	   	   		  <?php if ($display_social==="1" ) { // Show Social Media Icons?>
	       <div id="share-toggle" style="margin:auto; text-align: center; width: 459px; margin-botton:20px; display: block; clear:both; padding-bottom: 20px !important;">
	           <div class="share-text"></div>
	           <style type="text/css"> #inbound-social-inbound-social-buttons{ margin: auto;}</style>
	           <?php lp_social_media(); // Template helper to print out social media buttons ?>
	       </div>
	       <?php } ?>
	   		  <p class="small muted"><?php echo $text_box_id; ?></p>

	   	   </footer>
	   <!-- /footer -->

	</section>
	<!-- /main -->

</section>
<!-- /wrapper -->

<?php break; endwhile; endif;

do_action('lp_footer');
wp_footer();
?>

</body>
</html>