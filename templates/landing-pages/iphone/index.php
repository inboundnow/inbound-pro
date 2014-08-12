<?php
/**
* Template Name: iphone
* @package  WordPress Landing Pages
* @author   Inbound Template Generator!
*/
/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');


/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();
$googlelink = lp_get_value($post, $key, 'googlelink' );
$applelink = lp_get_value($post, $key, 'applelink' );
$headline_color = lp_get_value($post, $key, 'headline-color' );
$sub_headline_color = lp_get_value($post, $key, 'sub-headline-color' );
$background_style = lp_get_value($post, $key, 'background-style' );
$background_image = lp_get_value($post, $key, 'background-image' );
$background_color = lp_get_value($post, $key, 'background-color' );
$twitterlink = lp_get_value($post, $key, 'twitterlink' );
$googlepluslink = lp_get_value($post, $key, 'googlepluelink' );
$facebooklink = lp_get_value($post, $key, 'facebooklink' );
if ( $background_style === "fullscreen" ) {
  $bg_style = 'background: url('.$background_image.') no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
  -ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';

} else if ( $background_style === "color" ) {
  $bg_style = 'background: #'.$background_color.';';
} else if ( $background_style === "tile" ) {
  $bg_style = 'background: url('.$background_image.') repeat; ';
} else if ( $background_style === "repeat-x" ) {
  $bg_style = 'background: url('.$background_image.') repeat-x; ';
} else if ( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
} else if( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
}

?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<!--<![endif]--><head>
<meta charset="UTF-8" />
<title><?php wp_title(); ?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="<?php echo $path; ?>css/normalize.css" />
				<link rel="stylesheet" media="screen" href="<?php echo $path; ?>css/bootstrap.min.css" />
				<link rel="stylesheet" href="<?php echo $path; ?>css/bootstrap-responsive.min.css" />
				<link rel="stylesheet" href="<?php echo $path; ?>css/font-awesome.min.css" />
				<!--[if IE 7]>
					<link rel="stylesheet" href="plugin/font-awesome/css/font-awesome-ie7.min.css">
				<![endif]-->
        <link rel="stylesheet" href="<?php echo $path; ?>css/main.css" />

    <?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
.info-container.right {
margin-top: 15%;
}
#inbound-top-area .slogan {<?php echo $bg_style;?>}
h1, h2, h3, h4, h5, h6 {
	color: #<?php echo $headline_color;?>;
}
h1 small, h2 small, h3 small, h4 small, h5 small, h6 small {
	color: #<?php echo $sub_headline_color;?>;
}
#apple-link, #google-link {
	border-radius: 13px;
	}
</style>
</head>
    <body class="lp_ext_customizer_on single-area-edit-on">
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
<!--==========================================================================
     Header Unit (LOGO & Slogan)
    ==========================================================================-->
<div id="inbound-top-area" class="row-fluid">
	<div class="span12 slogan text-center">
		<img class="logo inbound_option_area" src="<?php echo lp_get_value($post, $key, 'logo'); ?>" alt="logo" data-eq-selector=".span12.slogan.text-center .logo:eq(0)" data-count-size="1" data-css-selector=".span12.slogan.text-center .logo" data-js-selector=".span12.slogan.text-center .logo" data-option-name="Logo" data-option-kind="media" onError="this.onerror=null;this.src='<?php echo $path; ?>images/inbound-logo.png';" inbound-option-name="Logo" />
		<h1 class=""><?php lp_main_headline();?><br /> <small class="inbound_option_area" data-eq-selector=".span12.slogan.text-center small:eq(0)" data-count-size="1" data-css-selector=".span12.slogan.text-center small" data-js-selector=".span12.slogan.text-center small" data-option-name="Subheadline" data-option-kind="text" inbound-option-name="Subheadline"><?php echo lp_get_value($post, $key, "subheadline"); ?></small></h1>
	</div>
</div>
<!--==========================================================================
			Information Unit
				-In these (two) information balloons you have to tell (in a simple way)
					like "what are your app going to do/change?"
    ==========================================================================-->
<div class="row-fluid outline-element">
<div class="span4 info-container left">
		<div class="hero-unit info inbound_option_area" data-eq-selector=".span4.info-container.left .hero-unit.info:eq(0)" data-count-size="1" data-css-selector=".span4.info-container.left .hero-unit.info" data-js-selector=".span4.info-container.left .hero-unit.info" data-option-name="Left Content" data-option-kind="wysiwyg" inbound-option-name="Left Content"><?php echo lp_get_value($post, $key, "left-content"); ?></div>
	</div>
<div class="span4 text-center" style="margin-top:-5%;"><img src="<?php echo lp_get_value($post, $key, 'iphone-image'); ?>" alt="iphone white" class="inbound_option_area" data-eq-selector=".span4.text-center img:eq(0)" data-count-size="1" data-css-selector=".span4.text-center img" data-js-selector=".span4.text-center img" data-option-name="Iphone Image" data-option-kind="media" onError="this.onerror=null;this.src='<?php echo $path; ?>images/iwhite.png';" inbound-option-name="Iphone Image" /></div>
<div class="span4 info-container right">
		<div class="hero-unit info inbound_option_area" data-eq-selector=".span4.info-container.right .hero-unit.info:eq(0)" data-count-size="1" data-css-selector=".span4.info-container.right .hero-unit.info" data-js-selector=".span4.info-container.right .hero-unit.info" data-option-name="Right Content" data-option-kind="wysiwyg" inbound-option-name="Right Content"><?php echo lp_get_value($post, $key, "right-content"); ?></div>
	</div>
</div>
<!--==========================================================================
     Connect your app with url to store.
    ==========================================================================-->
<div class="row-fluid">
	<div class="span12 text-center" style="margin-top:4%;">
		<a id="apple-link" href="<?php echo $applelink;?>" class="wpl-track-me"><img class="span2 offset4" src="<?php echo $path; ?>images/app-store.png" alt="App store" style="margin-right:2%;" onError="this.onerror=null;this.src='<?php echo $path; ?>images/app-store.png';" /></a>
		<a id="google-link" href="<?php echo $googlelink;?>"  class="wpl-track-me"><img class="span2" src="<?php echo $path; ?>images/google-play.png" alt="Google play" onError="this.onerror=null;this.src='<?php echo $path; ?>images/google-play.png';" /></a>
	</div>
</div>
<!--==========================================================================
     Social Network Unit
    ==========================================================================-->
<div class="row-fluid">
	<div class="span12 text-center" style="margin-top:5%; margin-bottom: 50px;">
			<a class="social" href="<?php echo $twitterlink;?>"><i class="icon-twitter icon-2x"></i></a>
			<a class="social" href="<?php echo $googlepluslink;?>"><i class="icon-google-plus icon-2x"></i></a>
			<a class="social" href="<?php echo $facebooklink;?>"><i class="icon-facebook icon-2x"></i></a>
	</div>
</div>
<!--==========================================================================
     Footer Unit
    ==========================================================================-->
<footer>

</footer>

<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>