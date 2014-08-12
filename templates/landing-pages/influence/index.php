<?php
/**
* Template Name: influence
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
$headline_text_color = lp_get_value($post, $key, 'headline-text-color' );
$sub_headline = lp_get_value($post, $key, 'sub-headline' );
$sub_headline_text_color = lp_get_value($post, $key, 'sub-headline-text-color' );
$content_bg_color = lp_get_value($post, $key, 'content-bg-color' );
$content_text_color = lp_get_value($post, $key, 'content-text-color' );
$top_nav_menu = lp_get_value($post, $key, 'top-nav-menu' );
$content_area_1 = lp_get_value($post, $key, 'content-area-1' );
$content_area_2 = lp_get_value($post, $key, 'content-area-2' );
$content_area_3 = lp_get_value($post, $key, 'content-area-3' );
$footer = lp_get_value($post, $key, 'footer' );

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

$rbga_color = inbound_Hex_2_RGB($content_bg_color);
 ?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php wp_title(); ?></title>
<link href="<?php echo $path; ?>css/bootstrap.css" rel="stylesheet" />
<link href="<?php echo $path; ?>css/business-casual.css" rel="stylesheet" />
<style id="inbound-style-overrides" type="text/css">
body {
  background: url(<?php echo $path;?>images/bg.jpg) no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
.navbar-default .navbar-collapse, .navbar-default .navbar-form {
border-color: #e7e7e7;
}
.navbar-default, .box, footer {
background: #<?php echo $content_bg_color; ?>;
background: rgba(<?php echo $rbga_color['r'];?>,<?php echo $rbga_color['g'];?>,<?php echo $rbga_color['b'];?>,0.9);
border: none;
}
h1 small, h2 small, h3 small, h4 small, h5 small, h6 small, .h1 small, .h2 small, .h3 small, .h4 small, .h5 small, .h6 small, h1 .small, h2 .small, h3 .small, h4 .small, h5 .small, h6 .small, .h1 .small, .h2 .small, .h3 .small, .h4 .small, .h5 .small, .h6 .small {
font-weight: normal;
line-height: 1;
color: #333333;
}
body {
  color: #333333;
  }
  img {
  vertical-align: middle;
  max-width: 100%;
  }
  .brand {
  display: inherit;
  font-weight: 700;
  font-size: 5em;
  line-height: normal;
  text-align: center;
  margin: 0;
  padding: 30px 0 10px;
  color: #fff;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
  }
  .navbar-nav {
  float: none;
  margin: 0 auto;
  display: table;
  table-layout: fixed;
  font-size: 1.25em;
  }
  @media only screen and (max-width: 580px) {

  .collapse {
  display: block;
  }
  .navbar-nav {
  text-transform: none;

  text-align: center;
  }
  .brand {

  font-size: 3em;
  }
  .address-bar {

  font-size: 1.5em;
  line-height: 1.3em;
  width: 80%;
  margin: auto;
  text-align: center;
  margin-bottom: 10px;
  }
  .brand-name {
  font-weight: 700;
  font-size: 1em;
  }
 }

<?php
if ( $headline_text_color != "" ) {
echo ".brand { color: #$headline_text_color;}";
}

if ( $sub_headline_text_color != "" ) {
echo ".address-bar { color: #$sub_headline_text_color;}";
}

if ( $content_bg_color != "" ) {
echo ".css_element { color: #$content_bg_color;}";
}

if ( $content_text_color != "" ) {
echo ".navbar-default, .box, footer, .navbar-default .navbar-nav > li > a, h1 small, h2 small, h3 small, h4 small, h5 small, h6 small, .h1 small, .h2 small, .h3 small, .h4 small, .h5 small, .h6 small, h1 .small, h2 .small, h3 .small, h4 .small, h5 .small, h6 .small, .h1 .small, .h2 .small, .h3 .small, .h4 .small, .h5 .small, .h6 .small, p { color: #$content_text_color;}";
}

?>
body {<?php echo $bg_style;?>}
</style>
  <?php wp_head(); do_action('lp_head');?>

</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>

    <div class="brand inbound_the_title" data-eq-selector="body .brand:eq(0)" data-count-size="1" data-css-selector="body .brand" data-js-selector="body .brand">

<?php lp_main_headline(); // Main Headline ?>
</div>
    <div class="address-bar inbound_option_area outline-processed-active current-inbound-option-area" data-selector-on="true" data-eq-selector="body .address-bar:eq(0)" data-count-size="1" data-css-selector="body .address-bar" data-js-selector="body .address-bar" data-option-name="Sub headline" data-option-kind="textarea" inbound-option-name="Sub headline"><?php echo lp_get_value($post, $key, "sub-headline"); ?></div>
<nav class="navbar navbar-default" role="navigation" style="overflow: visible;">
      <div class="container" style="overflow: visible;">

<div class="collapse navbar-collapse navbar-ex1-collapse inbound_option_area outline-processed-active current-inbound-option-area" style="overflow: visible;" data-eq-selector=".container .collapse.navbar-collapse.navbar-ex1-collapse:eq(0)" data-count-size="1" data-css-selector=".container .collapse.navbar-collapse.navbar-ex1-collapse" data-js-selector=".container .collapse.navbar-collapse.navbar-ex1-collapse" data-option-name="Top Nav Menu" data-option-kind="wysiwyg" inbound-option-name="Top Nav Menu"><?php echo lp_get_value($post, $key, "top-nav-menu"); ?></div>
      </div>
    </nav>
<div class="container">
<div class="row">
        <div class="box">
          <div class="col-lg-12 text-center inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".box .col-lg-12:eq(0)" data-count-size="3" data-css-selector=".box .col-lg-12" data-js-selector=".box .col-lg-12:eq(0)" data-option-name="Content Area 1" data-option-kind="wysiwyg" inbound-option-name="Content Area 1">
            <?php echo wpautop($content_area_1); ?></div>
        </div>
      </div>

      <div class="row">
        <div class="box">
          <div class="col-lg-12 inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".box .col-lg-12:eq(1)" data-count-size="3" data-css-selector=".box .col-lg-12" data-js-selector=".box .col-lg-12:eq(1)" data-option-name="Content Area 2" data-option-kind="wysiwyg" inbound-option-name="Content Area 2">
            <?php echo wpautop($content_area_2); ?></div>
        </div>
      </div>
<div class="row">
        <div class="box">
          <div class="col-lg-12 inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".box .col-lg-12:eq(2)" data-count-size="3" data-css-selector=".box .col-lg-12" data-js-selector=".box .col-lg-12:eq(2)" data-option-name="Content Area 3" data-option-kind="wysiwyg" inbound-option-name="Content Area 3">
            <?php echo wpautop($content_area_3); ?></div>
        </div>
      </div>
</div>

    <footer>
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <p class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector=".col-lg-12.text-center p:eq(0)" data-count-size="1" data-css-selector=".col-lg-12.text-center p" data-js-selector=".col-lg-12.text-center p" data-option-name="Footer" data-option-kind="textarea" inbound-option-name="Footer">
              <?php echo lp_get_value($post, $key, "footer"); ?></p>
          </div>
        </div>
      </div>
    </footer>
<div id="inbound-template-name" style="display:none;">influence</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>