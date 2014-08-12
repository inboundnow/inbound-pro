<?php
/**
* Template Name: captivate
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
$show_top = lp_get_value($post, $key, 'show_top' );
$top_area = lp_get_value($post, $key, 'top-area' );
$content_background_color = lp_get_value($post, $key, 'content-background-color' );
$content_text_color = lp_get_value($post, $key, 'content-text-color' );
$submit_bg = lp_get_value($post, $key, 'submit-bg' );
$submit_text = lp_get_value($post, $key, 'submit-text' );
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

$submit = inbound_color_scheme($submit_bg, 'hex' );

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
<style>
body {
  <?php echo $bg_style;?>
  font: 1em Helvetica;
}

#captivate-container {
  width: 860px;
  margin: 25px auto;
}
#captivate-container .whysign {
  float: left;
  background-color: white;
  width: 480px;

  border-radius: 0 5px 5px 0;
  padding-top: 20px;
  padding-right: 20px;
  padding-bottom: 40px;
}
#captivate-container .inbound-signup {
  float: left;
  width: 300px;
  padding: 30px 20px;
  background-color: white;
  text-align: center;
  border-radius: 5px 0 0 5px;
}
#captivate-container #inbound-form-wrapper [type=text] {
  display: block;
  margin: 0 auto;
  width: 80%;
  border: 0;
  border-bottom: 1px solid rgba(0,0,0,.2);
  height: 45px;
  line-height: 45px;
  margin-bottom: 10px;
  font-size: 1em;
  color: rgba(0,0,0,.4);
}
#captivate-container #inbound-form-wrapper [type=submit] {
  margin-top: 25px;
  width: 80%;
  border: 0;
  background-color: #53CACE;
  border-radius: 5px;
  height: 50px;
  color: white;
  font-weight: 400;
  font-size: 1em;
}
#captivate-container #inbound-form-wrapper [type='text']:focus {
  outline: none;
  border-color: #53CACE;
}
#captivate-container h1 {
  color: rgba(0,0,0,.7);
  font-weight: 900;
  font-size: 2.5em;
  margin-top: 0px;
}
#captivate-container p {
  color: rgba(0,0,0,.6);
  font-size: 1.2em;
  margin: 50px 0 50px 0;
}
#captivate-container span {
  font-size: .75em;
  background-color: white;
  padding: 2px 5px;
  color: rgba(0,0,0,.6);
  border-radius: 2px;
  box-shadow: 1px 1px 1px rgba(0,0,0,.3); 0 0 5px black
  margin: 5px;
}
#captivate-container span:hover {
  color: #53CACE;
}

.inbound-label.inbound-label-top, .inbound-label.inbound-label-bottom {
display: block;

width: 80%;
text-align: left;
margin: 0 auto;
}
#inbound-main-content {
  width: 860px;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  background-color: #fff;
  overflow: hidden;
}
#captivate-container span.inbound-required {
  background-color: transparent;
  box-shadow: none;
}
#inbound-top-area {
  margin: 15px auto;
  padding: 20px;
  background: white;
  -moz-box-shadow: 0 0 5px rgba(0,0,0,.3);
  -webkit-box-shadow: 0 0 5px rgba(0,0,0,.3);
  box-shadow: 0 0 5px rgba(0,0,0,.3);
  width: 85%;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
    position: relative;
      padding-bottom: 56.25%;
      padding-top: 30px; height: 0; overflow: hidden;

}
#inbound-top-area iframe,
#inbound-top-area object,
#inbound-top-area embed {
    position: absolute;
    top: 0;
    left: 0;
   width: 96%;
   height: 95%;
   padding: 2%;
}
select {
  width: 80%;

  display: block;
  margin: auto;
  padding: 15px;
}
.inbound-label.inbound-label-placeholder.inbound-input-dropdown {
  width: 80%;
  text-align: left;
  margin: auto;
}

#captivate-container p {

margin: 0px;
margin-top: 15px;
}
@media only screen and (max-width: 580px) {

  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
  width: 92%;

  font-size: 18px;
 }
 .inbound-label.inbound-label-top, .inbound-label.inbound-label-bottom {
 display: block;
 width: 91%;}
 #captivate-container #inbound-form-wrapper {
 overflow: inherit;
 margin: 10px 0;
 max-width: 90%;
 }
 #captivate-container .inbound-signup {
 padding: 0px 15px;
 }
 #captivate-container .whysign {

 padding-top: 0px;}
 #captivate-container h1 {

 margin-top: 0px;
 margin-bottom: 0px;
 font-size: 26px;
 }
 #captivate-container p {

 margin: 0px;
 margin-top: 15px;
 font-size: 15px;
 line-height: 20px;
 }
 #captivate-container .whysign {
 width: 85%; }
}
@media only screen and (max-width: 870px) {

  #captivate-container, #inbound-main-content, #captivate-container .whysign, #captivate-container .inbound-signup {
  width: 100%;
  }
  #captivate-container .whysign {
    width: 90%;
    margin: auto;
    float: right;
  }
  #captivate-container .inbound-signup {


  }
  #captivate-container .whysign {
    padding-top: 0px;
  }
  #inbound-top-area {
    margin-bottom: 0px;
  }
  #captivate-container h1 {

  margin-top: 0px;
  margin-bottom: 0px;
  }
  #captivate-container p {

  margin: 0px;
  margin-top: 15px;
  }
  #captivate-container #inbound-form-wrapper {
  overflow: inherit;
  margin: 10px 0;
  max-width: 93%;
  }

}
#captivate-container .inbound-field.inbound-submit-area {
padding: 0px 0 10px 0;
margin: 0px 0 0 0;
}
<?php if ( $submit_bg != "" ) {
echo "#captivate-container #inbound-form-wrapper [type=submit] { background-color: #$submit_bg;}";
echo "#captivate-container #inbound-form-wrapper [type='text']:focus { border-color: #$submit_bg;}";
}

if ( $submit_text != "" ) {
echo "#captivate-container #inbound-form-wrapper [type=submit] { color: #$submit_text;}";
}
if ( $content_background_color != "" ) {
echo "#inbound-main-content { background-color: #$content_background_color;}";
}

if ( $content_text_color != "" ) {
echo "#inbound-main-content, #captivate-container h1, #captivate-container p { color: #$content_text_color;}";
}
?>
#captivate-container [type=submit]:hover, #captivate-container  button[type=submit]:hover {
  background: <?php echo $submit[60];?>;
}
</style>
<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php
?>
</style>
</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
<div id="captivate-container">
  <div id="inbound-main-content">
    <?php if ($show_top === 'on') { ?>
    <div id="inbound-top-area">
      <?php echo $top_area;?>
    </div>
    <?php } ?>
  <div class="inbound-signup">

     <?php echo do_shortcode($conversion_area); ?>
  </div>
  <div class="whysign">
    <h1 class="inbound_the_title" data-eq-selector=".whysign h1:eq(0)" data-count-size="1" data-css-selector=".whysign h1" data-js-selector=".whysign h1">
<?php lp_main_headline(); // Main Headline ?>
</h1>
    <?php echo do_shortcode( $content ); // Main Content ?>

  </div>
</div>
</div>
<div id="inbound-template-name" style="display:none;">captivate</div>
<script type="text/javascript">
  jQuery(document).ready(function($) {
     $( 'p:empty' ).remove();
   });

</script>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>