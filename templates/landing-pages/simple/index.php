<?php
/**
* Template Name: simple
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

$logo = lp_get_value($post, $key, 'logo' );
$headline_text_color = lp_get_value($post, $key, 'headline-text-color' );
$subheadline_text_color = lp_get_value($post, $key, 'subheadline-text-color' );
$sub_headline = lp_get_value($post, $key, 'sub-headline' );
$content_area = lp_get_value($post, $key, 'content-area' );
$submit_bg_color = lp_get_value($post, $key, 'submit-bg-color' );
$submit_text_color = lp_get_value($post, $key, 'submit-text-color' );
$bottom_text_color = lp_get_value($post, $key, 'bottom-text-color' );
$conversion_area = lp_get_value($post, $key, 'conversion-area' );
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

 ?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<title><?php wp_title(); ?></title>
  <link href="<?php echo $path; ?>css/style.css" rel="stylesheet" />
  <link href="<?php echo $path; ?>css/nprogress.css" rel="stylesheet" />
<meta name="viewport" content="width=device-width" />
  <link href="<?php echo $path; ?>css/css" rel="stylesheet" type="text/css" />
<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
.rule {
  display: inline-block;
  text-decoration: none;
  background: #eee;
  color: #777;
  border-radius: 2px;
  padding: 8px 10px;
  font-weight: 700;
  text-align: left;
}
#inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email],  #inbound-form-wrapper input[type=tel],  #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
 width: 100%;
 color: #3c3c3c;
 font-family: Helvetica, Arial, sans-serif;
 font-weight: 500;
 font-size: 18px;
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
#inbound-form-wrapper input[type=text]:focus,  #inbound-form-wrapper input[type=url]:focus, #inbound-form-wrapper input[type=email]:focus, #inbound-form-wrapper input[type=tel]:focus, #inbound-form-wrapper input[type=number]:focus, #inbound-form-wrapper input[type=password]:focus {
background: #fff;
box-shadow: 0;
border: 3px solid #3498db;
color: #3498db;
outline: none;
padding: 13px;
}
#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button  {
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
.controls #inbound-form-wrapper {

overflow: hidden;
}
#inbound-bottom-area {

  max-width: 980px;
  margin: auto;
  margin-top: 40px;
}
html, body {<?php echo $bg_style;?>}
<?php
if ( $headline_text_color != "" ) {
echo ".page-header h1 { color: #$headline_text_color;}";
}

if ( $subheadline_text_color != "" ) {
echo "p.brief, .page-header { color: #$subheadline_text_color;}";
}

if ( $submit_bg_color != "" ) {
echo ".contents #inbound-form-wrapper input[type='submit'], .contents #inbound-form-wrapper button{ background-color: #$submit_bg_color;}";
echo ".contents #inbound-form-wrapper input[type=text]{ border: 3px solid #$submit_bg_color; }";
}

if ( $submit_text_color != "" ) {
echo ".contents #inbound-form-wrapper input[type='submit'], .contents #inbound-form-wrapper button { color: #$submit_text_color;}";
}

if ( $bottom_text_color != "" ) {
echo "#inbound-bottom-area, #inbound-bottom-area p { color: #$bottom_text_color;}";
}

?>
</style>
</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
  <header class="page-header">
    <img src="<?php echo lp_get_value($post, $key, "logo"); ?>" />
    <h1 class="inbound_the_title" data-eq-selector=".page-header h1:eq(0)" data-count-size="1" data-css-selector=".page-header h1" data-js-selector=".page-header h1">
<?php lp_main_headline(); // Main Headline ?>
</h1>
    <p class="fade brief big inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".page-header .fade.brief.big:eq(0)" data-count-size="1" data-css-selector=".page-header .fade.brief.big" data-js-selector=".page-header .fade.brief.big" data-option-name="Sub Headline" data-option-kind="wysiwyg" inbound-option-name="Sub Headline"><?php echo lp_get_value($post, $key, "sub-headline"); ?></p>
  </header>
<div class="contents fade outline-element">
    <div class="controls inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".contents.fade .controls:eq(0)" data-count-size="1" data-css-selector=".contents.fade .controls" data-js-selector=".contents.fade .controls" data-option-name="Content Area" data-option-kind="wysiwyg" inbound-option-name="Content Area"><?php echo lp_get_value($post, $key, "content-area"); ?></div>
    <div id="inbound-bottom-area" class="actions inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".contents.fade .actions:eq(0)" data-count-size="1" data-css-selector=".contents.fade .actions" data-js-selector=".contents.fade .actions" data-option-name="Conversion Area" data-option-kind="wysiwyg" inbound-option-name="Conversion Area"><?php $bottom = lp_get_value($post, $key, "conversion-area"); echo wpautop($bottom); ?></div>
    <div class="hr-rule"></div>

  </div>
<div id="inbound-template-name" style="display:none;">simple</div>

<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>