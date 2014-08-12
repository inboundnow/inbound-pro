<?php
/**
* Template Name: fade
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
$headline_color = lp_get_value($post, $key, 'headline-color' );
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
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php wp_title(); ?></title>
<style>body,
html {
  height: 100%;
  background: #fff;
}
body {
  font-family: Gotham, Sans-Serif;
  margin: 0;
  padding: 0;
}
header {

  background-size: cover;
  -webkit-transform: translatez(0);
}
header h1 {
  margin: 0;
  padding: 200px 0;
  text-align: center;
  text-transform: uppercase;
  font-size: 65px;
  -webkit-transform: translate3d(0, 0, 0);
}
section {
  max-width: 65em;
  margin: 0 auto;
  padding: 2.0em;
}
*, *:before, *:after {-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
figure,footer,header,main,nav,section{display: block;}
html{font-family: sans-serif;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;}
html,body {font-size: 100%;font-family: "helvetica neue", helvetica, arial, sans-serif;}
body,div,h1,h2,h3,h4,h5,h6,pre,p,blockquote{margin:0;padding:0;font-size:1rem}

hr.sep {
  display: block;
  width: 40%;
  margin: 2em auto 1.5em;
  border: 2px solid #D9E4EA;
}

.left{
  text-align: left;
  float: left;
}

.right{
  text-align: right;
  float: right;
}

a{
  color: #0c1d31;
  text-decoration: none;
  line-height:inherit;
  -webkit-transition: color .6s ease-in;
  -moz-transition: color .6s ease-in;
  -o-transition: color .6s ease-in;
  transition: color .6s ease-in;
}

a:hover{
  color: #106686;
  text-decoration: none;
}

p{
  color: #555555;
  font-family:georgia, "helvetica neue", helvetica, arial, sans-serif;

 font-size: 1.5em;
 line-height: 1.5;
  letter-spacing: 1;
  margin-bottom:1.4em;
}
#inbound_main_content a.inbound-button, #inbound_main_content a.inbound-button.inbound-special-class {
  font-family: helvetica, arial, sans-serif;
}
h1 {
  text-align: center;
  font-size: 5.5em;
  font-size: 7vw;
  font-family: helvetica, arial, sans-serif;
  color: rgba(255,255,255,0.95);
}

h2{
  text-align: center;
  font-size: 2.5em;
  font-size: 35px;
  padding: 0px;
  color: #555555;

  margin-bottom: .5em;
}

#inbound_main_content h1 {
  color:#555555;
  font-size: 45px;

  margin-bottom: .5em;
}
#inbound_main_content #inbound-list {
overflow: hidden;
margin-bottom: 2em;
}
#top-bar{
  position: fixed;
  width: 100%;
  padding: 1em 1em ;
  margin: 0;
  z-index: 99;
}

#top-bar #name{
  font-family: "helvetica neue", helvetica, arial, sans-serif;
  color: rgba(255,255,255,0.6);
  font-size: 2vw;
  margin: 0;
}

#mast{
  position: relative;
  top: 0;

  background-attachment: fixed;
  background-size: 120%;
  height: 100%;
}

#hero{
  padding: 13% 10% 14%;
  min-height: 15em;
  position: relative;
  top: 1em;
  display: -webkit-box;
  display: -moz-box;
  display: -ms-box;
  display: box;
  -webkit-box-orient: vertical;
  -moz-box-orient: vertical;
  -ms-box-orient: vertical;
  box-orient: vertical;
  -webkit-box-pack: center;
  -moz-box-pack: center;
  -ms-box-pack: center;
  box-pack: center;
  height: 100%;
}

main{
  padding: 3em 5vw 4em;
}

main p{
  text-align: center;
  padding: .5em;
}

footer{
  background: #b8dce5;
  padding: 3em 10% 1em;
}

footer p{
  text-align: center;
  font-size: 2.5vw;
}
#inbound_main_content #inbound-form-wrapper input[type=text], #inbound_main_content #inbound-form-wrapper input[type=url], #inbound_main_content #inbound-form-wrapper input[type=email], #inbound_main_content #inbound-form-wrapper input[type=tel], #inbound_main_content #inbound-form-wrapper input[type=number], #inbound_main_content #inbound-form-wrapper input[type=password] {
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
#inbound_main_content #inbound-form-wrapper input[type=text]:focus, #inbound_main_content #inbound-form-wrapper input[type=url]:focus, #inbound_main_content #inbound-form-wrapper input[type=email]:focus, #inbound_main_content #inbound-form-wrapper input[type=tel]:focus, #inbound_main_content #inbound-form-wrapper input[type=number]:focus, #inbound_main_content #inbound-form-wrapper input[type=password]:focus {
background: #fff;
box-shadow: 0;
border: 3px solid #3498db;
color: #3498db;
outline: none;
padding: 13px;
}
#inbound_main_content #inbound-form-wrapper input[type="submit"], #inbound_main_content #inbound-form-wrapper button  {
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
#inbound_main_content #inbound-form-wrapper {
overflow: inherit;
margin: 10px 0;
max-width: 60%;
display: block;
clear: both;
overflow: hidden;
margin: auto;
margin-bottom: 2em;
}
@media only screen and (max-width: 48em) {
#mast{background-size: 170%;}
}
@media only screen and (max-width: 580px) {

 #content #inbound-form-wrapper input[type=text], #content #inbound-form-wrapper input[type=url], #content #inbound-form-wrapper input[type=email], #content #inbound-form-wrapper input[type=tel], #content #inbound-form-wrapper input[type=number], #content #inbound-form-wrapper input[type=password] {

 }
 #inbound_main_content a.inbound-button, #inbound_main_content a.inbound-button.inbound-special-class {
  font-family: helvetica, arial, sans-serif;
  font-size: 15px !important;
  }
  #inbound_main_content #inbound-form-wrapper {

  max-width: 100%;}
  header h1 {
  margin: 0;
  padding: 200px 0;
  text-align: center;
  text-transform: uppercase;
  font-size: 30px;}
  #inbound_main_content h1 {
  color: #555555;
  font-size: 27px;
}
p {

font-size: 1em;}
}
@media only screen and (max-width: 870px) {



}
header { <?php echo $bg_style;?> }
</style>
<style id="inbound-style-overrides" type="text/css">
<?php
if ( $headline_color != "" ) {
echo "header h1 { color: #$headline_color;}";
}
?>
</style>
<?php wp_head(); do_action('lp_head');?>

</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
<header>
  <h1 data-eq-selector="body h1:eq(0)" data-count-size="2" data-css-selector="body h1" data-js-selector="body h1:eq(0)" class="" style="-webkit-transform: translate3d(0, 0px, 0); opacity: 1;"><?php the_title(); ?></h1>
</header>
<section id="inbound_main_content" class="inbound_the_content" data-selector-on="true" data-eq-selector=".-webkit-.yui3-js-enabled section:eq(0)" data-count-size="3" data-css-selector=".-webkit-.yui3-js-enabled section" data-js-selector=".-webkit-.yui3-js-enabled section:eq(0)">
    <?php the_content(); // Main Content ?>
</section>
<div id="yui3-css-stamp" style="position: absolute !important; visibility: hidden !important" class=""></div>
<div id="inbound-template-name" style="display:none;">fade</div>
<script src="<?php echo $path;?>/js/yui.js"></script>
  <script>
  causeRepaintsOn = jQuery("h1, h2, h3, p, a");

  jQuery(window).resize(function() {
     causeRepaintsOn.css("z-index", 1);
  });

    YUI().use('node', function (Y) {
  Y.on('domready', function () {

    var scrolling = false,
        lastScroll,
        i = 0;

    Y.on('scroll', function () {
      if (scrolling === false) {
        fade();
      }
      scrolling = true;
      setTimeout(function () {
        scrolling = false;
        fade();
      }, 100);
    });

    function fade() {

      lastScroll = window.scrollY;

      Y.one('h1').setStyles({
        'transform' : 'translate3d(0,' + Math.round(lastScroll/2) + 'px,0)',
        'opacity' : (100 - lastScroll/4.5)/100
      });

      if (scrolling === true) {
        window.requestAnimationFrame(fade);
      }
    }

  });
});

  </script>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>