<?php
/**
* Template Name: Lead Generation 1
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
$text_color = lp_get_value($post, $key, 'text-color' );
$content_background = lp_get_value($post, $key, 'content-background' );
$form_text_color = lp_get_value($post, $key, 'form-text-color' );
$background_style = lp_get_value($post, $key, 'background-style' );
$background_image = lp_get_value($post, $key, 'background-image' );
$background_color = lp_get_value($post, $key, 'background-color' );
$header_bg_color = lp_get_value($post, $key, 'header-bg-color' );
$form_text_color = lp_get_value($post, $key, 'form-text-color' );
$submit_button_text_color = lp_get_value($post, $key, 'submit-button-text-color' );
$submit_button_color = lp_get_value($post, $key, 'submit-button-color' );

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
<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>css/widget118.css" media="all" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="<?php echo $path; ?>css/datatables.css" />
    <link rel="stylesheet" href="<?php echo $path; ?>css/bootstrap.datatables.css" />
    <link rel="stylesheet" href="<?php echo $path; ?>css/chosen.css" />
    <link rel="stylesheet" href="<?php echo $path; ?>css/font-awesome.css" />
    <link rel="stylesheet" href="<?php echo $path; ?>css/app.css" />
    <link href="<?php echo $path; ?>css/css" rel="stylesheet" type="text/css" />



    <!--[if lt IE 9]>
    @javascript html5shiv respond.min
    <![endif]-->



    <title><?php wp_title(); ?></title>


  <?php wp_head();
  do_action('lp_head');
  ?>
  <style type="text/css">
  .jqstooltip { position: absolute;left: 0px;top: 0px;visibility: hidden;background: rgb(0, 0, 0) transparent;background-color: rgba(0,0,0,0.6);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";color: white;font: 10px arial, san serif;text-align: left;white-space: nowrap;padding: 5px;border: 1px solid white;z-index: 10000;}
  .jqsfield { color: white;font: 10px arial, san serif;text-align: left;}
  #inbound-form-wrapper input[type=submit], #inbound-form-wrapper button {
  display: block;
  border-radius: 0px;
  border-bottom: 1px solid #292D38;
  -webkit-box-shadow: inset 0px 1px 0px 0px #434756;
  box-shadow: inset 0px 1px 0px 0px #434756;
  color: #fff;
  font-size: 18px;
  font-weight: 300;
  text-transform: uppercase;
  padding: 15px 18px;
  border-bottom: 1px solid #145b32;
  background-color: #27ae60;
  -webkit-box-shadow: inset 0px 1px 2px 0px rgba(255,255,255,0.2), 0px 2px 2px 0px rgba(0,0,0,0.3);
  box-shadow: inset 0px 1px 2px 0px rgba(255,255,255,0.2), 0px 2px 2px 0px rgba(0,0,0,0.3);
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  -o-border-radius: 4px;
  -ms-border-radius: 4px;
  border-radius: 4px;
  }
  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
  width: 88%;
  padding: 15px 18px;
  }
  .inbound-label.inbound-label-top, .inbound-label.inbound-label-bottom {
  display: block;
  color: #27ae60;
  padding: 6px 0px;
  font-size: 17px;
  }
  .inbound-field.inbound-submit-area input.inbound-button, .inbound-field.inbound-submit-area input[type=submit] ,  #inbound-form-wrapper input[type=submit], #inbound-form-wrapper button{
  font-size: 1.3em;
  width: 100%;
  }
  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
  width: 100% !important;
  }
  .inbound-field.inbound-submit-area input.inbound-button, .inbound-field.inbound-submit-area input[type=submit], #inbound-form-wrapper button {

  margin-top: 25px;
  }
  body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 16px;}
  #inbound-list {
    margin-bottom: 20px;
  }
  p {
  margin: 0 0 15px;
  }
  .side-bar-wrapper {
  margin-bottom: 20px;
  }
  .content-wrapper .main-content {
  background-color: #fff;
  min-height: 500px;}
  @media (max-width: 992px) {
  #inbound-form-wrapper {
  overflow: inherit;
  margin: 10px 0;
  max-width: 98%;
  width: 92% !important;
  margin: auto !important;
  }
  .content-wrapper .page-header h1 {
    text-align: center;
    font-size: 24px;
  }


  }
  /*.content-wrapper .page-header.page-header-green {
    background-color: ;
  } */
  body { <?php echo $bg_style; ?> }
  <?php
      if ( $text_color != "" ) {
      echo ".content-wrapper .main-content { color: #$text_color;}";
      }
      if ( $content_background != "" ) {
      echo ".content-wrapper .main-content { background-color: #$content_background;}";
      }
      if ( $background_color != "" ) {
      echo ".css_element { color: #$background_color;}";
      }
      if ( $header_bg_color != "" ) {
      echo ".content-wrapper .page-header.page-header-green { background-color: #$header_bg_color;}";
      }
      if ( $form_text_color != "" ) {
      echo "#inbound-form-wrapper label, #inbound-form-wrapper h1,#inbound-form-wrapper h2,#inbound-form-wrapper h3,#inbound-form-wrapper h5, #inbound-form-wrapper h6, #inbound-form-wrapper p, #inbound-form-wrapper span { color: #$form_text_color;}";
      }
      if ( $submit_button_text_color != "" ) {
      echo "#inbound-form-wrapper input[type=submit], #inbound-form-wrapper button { color: #$submit_button_text_color;}";
      }
      if ( $submit_button_color != "" ) {
      echo "#inbound-form-wrapper input[type=submit], #inbound-form-wrapper button { border-bottom: 1px solid #$submit_button_color; background-color: #submit_button_color;}";
      }

  ?>
  </style>

</head>
  <body class="lp_ext_customizer_on single-area-edit-on">
    <div class="all-wrapper">
      <div class="row outline-element">
        <div class="col-md-3">
           <div class="text-center">

          </div>
          <div class="side-bar-wrapper collapse navbar-collapse navbar-ex1-collapse inbound_the_conversion_area" data-eq-selector=".col-md-3 .side-bar-wrapper.collapse.navbar-collapse.navbar-ex1-collapse:eq(0)" data-count-size="1" data-css-selector=".col-md-3 .side-bar-wrapper.collapse.navbar-collapse.navbar-ex1-collapse" data-js-selector=".col-md-3 .side-bar-wrapper.collapse.navbar-collapse.navbar-ex1-collapse"><div id="inbound-form-wrapper" class="">

          <?php echo do_shortcode( $conversion_area ); ?>

          </div></div>
        </div>
        <div class="col-md-9">
          <div class="content-wrapper">
            <div class="content-inner">
              <div class="page-header page-header-green">

                <div class="header-links hidden-xs">


                                  </div>
                <h1 class="inbound_the_title" data-eq-selector=".page-header.page-header-green h1:eq(0)" data-count-size="1" data-css-selector=".page-header.page-header-green h1" data-js-selector=".page-header.page-header-green h1"><?php lp_main_headline(); ?></h1>
              </div>
              <div class="main-content">
<div class="widget inbound_the_content" data-eq-selector=".main-content .widget:eq(1)" data-count-size="6" data-css-selector=".main-content .widget" data-js-selector=".main-content .widget:eq(1)"><?php echo do_shortcode( $content ); ?></div>
<link rel="stylesheet" href="<?php echo $path; ?>css/orangebox.css" type="text/css" />
</div>
          </div>
          <br /><br />
        </div>
      </div>
    </div>
    </div>

<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>