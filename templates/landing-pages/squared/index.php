<?php
/**
* Template Name: squared
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
$headline_color = lp_get_value($post, $key, 'headline-color' );
$content_bg_color = lp_get_value($post, $key, 'content-bg-color' );
$content_text_color = lp_get_value($post, $key, 'content-text-color' );
$form_bg_color = lp_get_value($post, $key, 'form-bg-color' );
$form_text_color = lp_get_value($post, $key, 'form-text-color' );
$submit_bg_color = lp_get_value($post, $key, 'submit-bg-color' );
$submit_text_color = lp_get_value($post, $key, 'submit-text-color' );
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

$scheme_color = inbound_color_scheme($content_bg_color, 'hex' );
$scheme_button = inbound_color_scheme($submit_bg_color, 'hex' );

?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<title><?php wp_title(); ?></title>
<meta name="viewport" content="max-width=device-width, initial-scale=1.0" />
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $path; ?>css/style.css" />

			<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<link href="<?php echo $path; ?>css/css" rel="stylesheet" type="text/css" />
<style type="text/css">
h1,h2,h3,h4,h5,h6, .total-comments, #header_area { font-family: 'Oswald', sans-serif;}

</style>

<style type="text/css">
body {font-family: 'Droid Serif', sans-serif;}
</style>
<style type="text/css">

body {<?php echo $bg_style;?>}

#content { float: left; border-left: 0; border-right: 1px solid #E8E8E8; }
.sidebar #inbound-form-wrapper input[type=text], .sidebar #inbound-form-wrapper input[type=url], .sidebar #inbound-form-wrapper input[type=email], .sidebar #inbound-form-wrapper input[type=tel], .sidebar #inbound-form-wrapper input[type=number], .sidebar #inbound-form-wrapper input[type=password] {
width: 90%;
padding: 1em;
padding-left: 5px;
}
.sidebar button {
	margin-top: 20px;
	display: block;
	width: 95%;
	line-height: 2em;
	background: #72d4ca;
	border-radius: 5px;
	border: 0;
	border-top: 1px solid #B2ECE6;
	box-shadow: 0 0 0 1px #46A294, 0 2px 2px #808389;
	color: #FFFFFF;
	font-size: 1.5em;
	text-shadow: 0 1px 2px #21756A;

}
.inbound-now-form.wpl-track-me {
	padding-left: 2px;
}
#content_box, #sidebars {
background-color: #F5F5F5;
}
.headline_area h1, .headline_area h2 {
	color: #494949;
}
#content { background:#fff;}
</style>

<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php

if ( $headline_color != "" ) {
echo ".headline_area h1, .headline_area h2 { color: #$headline_color;}";
}

if ( $content_bg_color != "" ) {
echo "#content { background: #$content_bg_color;}";
}

if ( $content_text_color != "" ) {
echo "#content { color: #$content_text_color;}";
}

if ( $form_bg_color != "" ) {
echo "#content_box, #sidebars { background: #$form_bg_color;}";
}

if ( $form_text_color != "" ) {
echo "#content_box, #sidebars { color: #$form_text_color;}";
}

if ( $submit_bg_color != "" ) {
echo ".sidebar button  { background: #$submit_bg_color;}";
}

if ( $submit_text_color != "" ) {
echo ".sidebar button { color: #$submit_text_color;}";
}

?>
#content {
	border-right: 1px solid  <?php echo $scheme_color[20];?>;
}
.sidebar button {
	border-top: 1px solid <?php echo $scheme_button[20];?>;
	box-shadow: 0 0 0 1px #46A294, 0 2px 2px <?php echo $scheme_button[70];?>;
	color: #FFFFFF;
	font-size: 1.5em;
	text-shadow: 0 1px 2px <?php echo $scheme_button[70];?>;
}
.sidebar #inbound-form-wrapper {

max-width: 100%;
}
</style>
</head>
<body <?php body_class('single single-post postid-18681 single-format-standard main cat-8-id lp_ext_customizer_on single-area-edit-on'); ?> data-twttr-rendered="true">

<div id="header_area">
<div class="page">
			<header id="header">
				<h2 id="logo">
								<a href=""></a>
							</h2>

				<div style="float: right;">
</div>
</header>
		</div>

	</div>
<div id="content_area" class="outline-element">
		<div class="page">
			<div id="content_box" class=""><div id="content" class="single">
		<article class="article">
		<div id="post-18681" class="post-18681 post type-post status-publish format-standard hentry category-other post_box cat-8-id has_thumb" style="overflow: visible;" data-eq-selector="#post-18681:eq(0)" data-count-size="1" data-css-selector="#post-18681" data-js-selector="#post-18681">

			<header style="overflow: visible;">
				<div class="headline_area" style="overflow: visible;">
					<h2 class="entry-title inbound_the_title" data-selector-on="true" data-eq-selector="#post-18681 .headline_area .entry-title:eq(0)" data-count-size="1" data-css-selector="#post-18681 .headline_area .entry-title" data-js-selector="#post-18681 .headline_area .entry-title">
<?php lp_main_headline(); // Main Headline ?>
</h2>
										<div class="headline_meta" style="overflow: visible;">


					</div>
									</div>
			</header>

			<div class="format_text entry-content inbound_the_content"><?php echo do_shortcode( $content ); // Main Content ?></div>
</div>
</article>
</div>
<aside class="sidebar">
	<div id="sidebars" class="">
		<div class="sidebar">
		<?php echo do_shortcode( $conversion_area ); // Conversion Area ?>
		</div>
</div>
</aside>
</div></div>
</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>