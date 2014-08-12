<?php
/**
* Template Name: curvy
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
$headline_text_color = lp_get_value($post, $key, 'headline-text-color' );
$headline_background_color = lp_get_value($post, $key, 'headline-background-color' );
$content_text_color = lp_get_value($post, $key, 'content-text-color' );
$content_background_color = lp_get_value($post, $key, 'content-background-color' );
$bottom_color = lp_get_value($post, $key, 'bottom-color' );
$default_content = lp_get_value($post, $key, 'default-content' );
$logo = lp_get_value($post, $key, 'logo' );
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
$scheme_color = inbound_color_scheme($headline_background_color, 'hex' );

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
<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>css/style.css" />
<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">

html {<?php echo $bg_style;?>}
#logo {
  display: block;
  text-align: center;
  margin: auto;
}
#wrapper #inbound-form-wrapper input[type=text], #wrapper #inbound-form-wrapper input[type=url], #wrapper #inbound-form-wrapper input[type=email], #wrapper #inbound-form-wrapper input[type=tel], #wrapper #inbound-form-wrapper input[type=number], #wrapper #inbound-form-wrapper input[type=password] {
 width: 100%;
 color: #3c3c3c;
 font-family: Helvetica, Arial, sans-serif;
 font-weight: 500;
 font-size: 12px;
 border-radius: 0;
 line-height: 22px;
 background-color: #fbfbfb;
 padding: 13px;
 margin-bottom: 10px;
 width: 100%;
 -webkit-box-sizing: border-box;
 -moz-box-sizing: border-box;
 -ms-box-sizing: border-box;
 box-sizing: border-box;
 border: 3px solid rgba(0,0,0,0);
 border: 3px solid #3498db;
}
#wrapper #inbound-form-wrapper input[type=text]:focus, #wrapper #inbound-form-wrapper input[type=url]:focus, #wrapper #inbound-form-wrapper input[type=email]:focus, #wrapper #inbound-form-wrapper input[type=tel]:focus, #wrapper #inbound-form-wrapper input[type=number]:focus, #wrapper #inbound-form-wrapper input[type=password]:focus {
background: #fff;
box-shadow: 0;
border: 3px solid #3498db;
color: #3498db;
outline: none;
padding: 13px;
}
#wrapper #inbound-form-wrapper input[type="submit"], #wrapper #inbound-form-wrapper button  {
  align-items: flex-start;
  text-align: center;
  font-family: 'Montserrat', Arial, Helvetica, sans-serif;
  float: left;
  width: 100%;
  border: #fbfbfb solid 4px;
  cursor: pointer;
  background-color: #3498db;
  color: white;
  font-size: 24px;
  padding-top: 22px;
  padding-bottom: 22px;
  -webkit-transition: all 0.3s;
  -moz-transition: all 0.3s;
  transition: all 0.3s;
  margin-top: -4px;
  font-weight: 700;
  border: none;
}
<?php
if ( $headline_text_color != "" ) {
echo "#title-area h1 { color: #$headline_text_color;}";
}

if ( $headline_background_color != "" ) {
echo "#header { background: #$headline_background_color;}";
}

if ( $content_text_color != "" ) {
echo "body { color: #$content_text_color;}";
}

if ( $content_background_color != "" ) {
echo "#content, #sidebar { background: #$content_background_color;}";
}

if ( $bottom_color != "" ) {
echo "#footer { color: #$bottom_color;}";
}

if ( $submit_bg_color != "" ) {
echo "#wrapper #inbound-form-wrapper input[type='submit'], #wrapper #inbound-form-wrapper button { background-color: #$submit_bg_color;}";
echo "#wrapper #inbound-form-wrapper input[type=text], #wrapper #inbound-form-wrapper input[type=url], #wrapper #inbound-form-wrapper input[type=email], #wrapper #inbound-form-wrapper input[type=tel], #wrapper #inbound-form-wrapper input[type=number], #wrapper #inbound-form-wrapper input[type=password] {border: 3px solid #$submit_bg_color;}";
}
if ( $submit_text_color != "" ) {
echo "#wrapper #inbound-form-wrapper input[type='submit'], #wrapper #inbound-form-wrapper button  { color: #$submit_text_color;}";
}

?>
#header {

  border-top: solid 1px <?php echo $scheme_color[30];?>;
  border-bottom: solid 1px <?php echo $scheme_color[80];?>;

}
@media only screen and (max-width: 580px) {

  #title-area h1 {
    font-size: 30px !important;
  }
  #header {
  padding: 20px;
  }

}
@media only screen and (max-width: 870px) {

  #wrapper {

  padding: 15px 0 0 0;
  width: 98%;
  }
  #inbound-logo img {
    max-width: 100%;
  }
  #page {
  position: relative;
  margin: 20px 0 20px 0;
  padding: 0;
  width: 100%;
  }
  #content {
  float: none;
  padding: 5%;
  margin: auto;
  width: 89%;}
  #sidebar {
  background: #EAEEF2;
  width: 89%;

  margin: auto;
  padding: 5%;
margin-top: 20px;}
}
</style>
</head>
<body <?php body_class(); ?>>
<div id="wrapper" class="">
  <div id="header" class="">
    <div id="inbound-logo">
      <?php echo lp_get_value($post, $key, "logo"); ?>
    </div>
    <div id="title-area">
    <h1><?php lp_main_headline(); // Main Headline ?></h1>
      <br class="clearfix" />
    </div>
  </div>
  <div id="page" class="">
    <div id="content" class="inbound_the_content" data-eq-selector="#content:eq(0)" data-count-size="1" data-css-selector="#content" data-js-selector="#content">
      <div class='box'>
        <?php echo do_shortcode( $content ) // Main Content ?>
      </div>
    </div>
    <div id="sidebar" class="inbound_the_conversion_area" data-eq-selector="#sidebar:eq(0)" data-count-size="1" data-css-selector="#sidebar" data-js-selector="#sidebar"><?php echo do_shortcode( $conversion_area ); // Conversion Area ?></div>
    <br class="clearfix" />
  </div>
</div>
<div id="footer" class="inbound_option_area outline-processed-active current-inbound-option-area" data-selector-on="true" data-eq-selector="#footer:eq(0)" data-count-size="1" data-css-selector="#footer" data-js-selector="#footer" data-option-name="Bottom Text" data-option-kind="textarea" inbound-option-name="Bottom Text"><?php echo lp_get_value($post, $key, "bottom-text"); ?></div>
<div id="inbound-template-name" style="display:none;">curvy</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>