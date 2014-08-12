<?php
/**
* Template Name: boxey
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

$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$logo_image = lp_get_value($post, $key, 'logo-image' );
$top_right_navigation = lp_get_value($post, $key, 'top-right-navigation' );
$headline_color = lp_get_value($post, $key, 'headline-color' );
$content_color = lp_get_value($post, $key, 'content-color' );
$subheadline = lp_get_value($post, $key, 'subheadline' );
$subheadline_color = lp_get_value($post, $key, 'subheadline-color' );
$bottom_left_column = lp_get_value($post, $key, 'bottom-left-column' );
$bottom_right_column = lp_get_value($post, $key, 'bottom-right-column' );
$cv_bg_color = lp_get_value($post, $key, 'cv-bg-color' );
$cv_text_color = lp_get_value($post, $key, 'cv-text-color' );
$submit_bg_color = lp_get_value($post, $key, 'submit-bg-color' );
$submit_text_color = lp_get_value($post, $key, 'submit-text-color' );
$copyright_bottom = lp_get_value($post, $key, 'copyright-bottom' );
$nav_color = lp_get_value($post, $key, 'nav-color' );

$background_style = lp_get_value($post, $key, 'background-style' );
$background_image = lp_get_value($post, $key, 'background-image' );
$background_color = lp_get_value($post, $key, 'background-color' );
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

$submit = inbound_color_scheme($submit_bg_color, 'hex' );
 ?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<title><?php wp_title(); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link href="<?php echo $path; ?>css/css" rel="stylesheet" type="text/css" />
<link href="<?php echo $path; ?>css/css" rel="stylesheet" type="text/css" />
<link href="<?php echo $path; ?>css/style.css" rel="stylesheet" type="text/css" />


<style type="text/css">
	#inbound-form-wrapper .inbound-field input[type=text],#inbound-form-wrapper .inbound-field input[type=url],#inbound-form-wrapper .inbound-field input[type=email], #inbound-form-wrapper .inbound-field input[type=tel],#inbound-form-wrapper .inbound-field input[type=number], #inbound-form-wrapper .inbound-field input[type=password] {
	width: 94%;
	padding: 0.5em;
	font-size: 1em;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	border: none;
	}
	#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button {
	display: block;
	margin: 0 auto;
	padding: 16px;
	padding: 1rem;
	width: 102%;
	-moz-transition: all 0.1s ease-in-out;
	-webkit-transition: all 0.1s ease-in-out;
	transition: all 0.1s ease-in-out;
	border-radius: 3px;
	background-color: #3BAF3A;
	border: none;
	color: #fff;
	font-size: 2em;
	margin-top: 25px;
	}
	#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
		background-color: #46CA45;
	}
	#inbound-form-wrapper .inbound-field label {
	margin: 10px 0 4px 0;
	font-weight: 200;
	line-height: 1.3em;
	clear: both;

	font-family: Arvo, serif;

	letter-spacing: 0px;
	font-size: 1.3em;
	}
</style>

    <?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
body {
	font-size: 12pt;
	line-height: 1.75em;
	font-family: Georgia, serif;
	background: #ad8667 url('<?php echo $path; ?>images/bg.jpg');
	color: #ebe1d9;
}

h2, h3, h4 {

}
<?php
if ( $headline_color != "" ) {
echo "h1, #main { color: #$headline_color;}";
}

if ( $content_color != "" ) {
echo "#content{ background-color: #$content_color;}";
}

if ( $subheadline_color != "" ) {
echo "h2, h3, h4 { color: #$subheadline_color;}";
}

if ( $cv_bg_color != "" ) {
echo "#sidebar { background: #$cv_bg_color;}";
}

if ( $cv_text_color != "" ) {
echo "#sidebar { color: #$cv_text_color;}";
}

if ( $submit_bg_color != "" ) {
echo "#inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button { background-color: #$submit_bg_color;}";
}

if ( $submit_text_color != "" ) {
echo "#inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button { color: #$submit_text_color;}";
}

if ( $nav_color != "" ) {
echo "#nav a, #copyright { color: #$nav_color;}";
}

?>
#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
  background: <?php echo $submit[45];?>;
}
body {<?php echo $bg_style;?>}


@media only screen and (max-width: 870px) {
	#main, #outer {
		width: 96%;
	}

  #box3 {
  width: 100%;

  margin: 0px;
  }
  #sidebar {
  width: 24%;
  float: left;
  padding: 15px;
  padding-left: 15px;}
  #inbound-form-wrapper .inbound-field input[type=text], #inbound-form-wrapper .inbound-field input[type=url], #inbound-form-wrapper .inbound-field input[type=email], #inbound-form-wrapper .inbound-field input[type=tel], #inbound-form-wrapper .inbound-field input[type=number], #inbound-form-wrapper .inbound-field input[type=password] {
  width: 94%;
  padding: .7em;
  font-size: .7em;}
  #inbound-form-wrapper .inbound-field label {

  font-size: 1em;}


}

@media only screen and (max-width: 1180px) {
 #main, #outer {
 	width: 96%;
 }
 #box3 {
 width: 100%;
 overflow: hidden;
 margin: 0px;
 }
 #box2 {
 width: 100%;}
}
@media only screen and (max-width: 580px) {


 #sidebar {
	width: 94%;
	float: none;
	margin-bottom: 20px;
 }
 #main h1 {
 font-size: 2em;
 }
 #content {
 	width: 99%;
 	margin-left: 0px;
 	padding: 3%;}
	#header {

	text-align: center;
	padding-bottom: 0px;
	}
	#nav {
	display: block;
	float: none;
	}
	#inbound-form-wrapper .inbound-field input[type=text], #inbound-form-wrapper .inbound-field input[type=url], #inbound-form-wrapper .inbound-field input[type=email], #inbound-form-wrapper .inbound-field input[type=tel], #inbound-form-wrapper .inbound-field input[type=number], #inbound-form-wrapper .inbound-field input[type=password] {
	width: 90%;
	padding: 1em;
	font-size: 1em;}
	#inbound-form-wrapper .inbound-field label {

	font-size: 1.3em;}
	#nav ul {
	list-style: none;
	overflow: hidden;
	margin-bottom: 0px;
	}
	#nav ul li {
	float: none;
	}
}
</style>
</head>
    <body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
		<div id="bg">
			<div id="outer" class="">
				<div id="header" class="">
					<div id="logo">
						<h1 class="">
							<a href="" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#header a:eq(0)" data-count-size="7" data-css-selector="#header a" data-js-selector="#header a:eq(0)" data-option-name="Logo Image" data-option-kind="media" inbound-option-name="Logo Image"><img class="not-image inbound-media inbound_option_area outline-processed-active current-inbound-option-area" src="<?php echo lp_get_value($post, $key, "logo-image"); ?>" /></a>
						</h1>
					</div>
					<div id="nav" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#nav:eq(0)" data-count-size="1" data-css-selector="#nav" data-js-selector="#nav" data-option-name="Top Right Navigation" data-option-kind="wysiwyg" inbound-option-name="Top Right Navigation"><?php echo lp_get_value($post, $key, "top-right-navigation"); ?></div>
				</div>
				<div id="main" class="">
					<div id="sidebar" class="">
						<?php echo do_shortcode( $conversion_area ); ?>
					</div>
					<div id="content" class="">
						<div id="box1" class="outline-element">
							<h1><?php lp_main_headline(); // Main Headline ?>.</h1>
							<h2 class="inbound_the_title" data-eq-selector="#box1 h2:eq(0)" data-count-size="1" data-css-selector="#box1 h2" data-js-selector="#box1 h2">
							<?php echo lp_get_value($post, $key, "subheadline"); ?>
							</h2>

							<div class="inbound_the_content" data-eq-selector="#box1 p:eq(0)" data-count-size="1" data-css-selector="#box1 p" data-js-selector="#box1 p"><?php echo do_shortcode( $content ); // Main Content ?>
							</div>
						</div>
						<div id="box2" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#box2:eq(0)" data-count-size="1" data-css-selector="#box2" data-js-selector="#box2" data-option-name="Bottom Left Column" data-option-kind="wysiwyg" inbound-option-name="Bottom Left Column"><?php echo lp_get_value($post, $key, "bottom-left-column"); ?></div>
						<div id="box3" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#box3:eq(0)" data-count-size="1" data-css-selector="#box3" data-js-selector="#box3" data-option-name="Bottom Right Column" data-option-kind="wysiwyg" inbound-option-name="Bottom Right Column"><?php echo lp_get_value($post, $key, "bottom-right-column"); ?></div>
						<br class="clear" />
					</div>
					<br class="clear" />
				</div>
			</div>
			<div id="copyright" class="inbound_option_area outline-processed-active current-inbound-option-area" data-selector-on="true" data-eq-selector="#copyright:eq(0)" data-count-size="1" data-css-selector="#copyright" data-js-selector="#copyright" data-option-name="Copyright bottom" data-option-kind="textarea" inbound-option-name="Copyright bottom"><?php echo lp_get_value($post, $key, "copyright-bottom"); ?></div>
		</div>
    <div id="inbound-template-name" style="display:none;">boxey</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>