<?php
/**
* Template Name: reveal
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

$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$scheme = lp_get_value($post, $key, 'scheme' );
$scheme_color = inbound_color_scheme($scheme, 'hex' );
$submit_bg_color = lp_get_value($post, $key, 'submit-bg-color' );
$submit = inbound_color_scheme($submit_bg_color, 'hex' );
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

 ?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width" />
<title><?php wp_title(); ?></title>
<style>/*! normalize.css v2.1.2 | MIT License | git.io/normalize */article,aside,details,figcaption,figure,footer,header,hgroup,main,nav,section,summary{display:block}audio,canvas,video{display:inline-block}audio:not([controls]){display:none;height:0}[hidden]{display:none}html{font-family:sans-serif;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;background:#fff}body{margin:0;background:#fff}a:focus{outline:thin dotted}a:active,a:hover{outline:0}h1{font-size:2em;margin:.67em 0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:700}dfn{font-style:italic}hr{-moz-box-sizing:content-box;box-sizing:content-box;height:0}mark{background:#ff0;color:#000}code,kbd,pre,samp{font-family:monospace,serif;font-size:1em}pre{white-space:pre-wrap}q{quotes:"\201C" "\201D" "\2018" "\2019"}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-.5em}sub{bottom:-.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:0}fieldset{border:1px solid silver;margin:0 2px;padding:.35em .625em .75em}legend{border:0;padding:0}button,input,select,textarea{font-family:inherit;font-size:100%;margin:0}button,input{line-height:normal}button,select{text-transform:none}button,html input[type=button],input[type=reset],input[type=submit]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}input[type=checkbox],input[type=radio]{box-sizing:border-box;padding:0}input[type=search]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type=search]::-webkit-search-cancel-button,input[type=search]::-webkit-search-decoration{-webkit-appearance:none}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}textarea{overflow:auto;vertical-align:top}table{border-collapse:collapse;border-spacing:0}</style>
<style>html {
    overflow-y: scroll;
}
html,body {
    height: 100%;
}
.top {
    background-image: url(data:image/png;base64,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);
    background-color: #3498db;
    height: 100%;
    position: absolute;
    width: 100%;
    box-shadow: 0 0 12px rgba(0,0,0,.50);
    transition: .25s top;
}
.bottom {
    background-color: #ecf0f1;
    height: 100%;
    position: absolute;
    width: 100%;
    box-shadow: 0 0 10px rgba(0,0,0,.45);
    transition: .25s top;
}
.wrapper {
    position: relative;
    height: 50%;
    min-height: 200px;
}
.slide-up {
    top: -50%;
    transition: .25s all;
}
.slide-down {
    top: 50%;
    transition: .25s all;
}
.hidden {
    position: absolute;
    height: 50%;
    width: 100%;
    z-index: -5;
    margin: auto;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    display: none;
}
.hidden h2 {
    height: 20px;
    margin: auto;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-align: center;
    font-weight: 700;
    font-family: 'Open Sans', serif;
    color: #3498db;
}
.btn {
    margin: auto;
    position: absolute;
    top: 100%;
    left: 0;
    bottom: 0;
    right: 0;
    background: #FFF;
    height: 85px;
    width: 145px;
    padding-top: 60px;
    border-radius: 100%;
    text-align: center;
    font-weight: 700;
    font-family: 'Open Sans', serif;
    text-decoration: none;
    color: #3498db;
    z-index: 100;
    transition: .25s all;
    box-shadow: 0 0 0 5px #3498db,0px 0 0 10px white,0px 0 10px 0 rgba(0,0,0,.5),0px 0 10px 10px rgba(0,0,0,.25);
    transition: .25s all;
}
.btn:hover {
    box-shadow: 0 0 0 10px #3498db,0px 0 0 20px white,0px 0 15px 0 rgba(0,0,0,.5),0px 0 10px 20px rgba(0,0,0,.25);
    transition: .25s all;
}
#hidden-wrapper {
    width: 500px;
    margin: auto;
    margin-top: 130px;
}
#hidden-wrapper #inbound-form-wrapper input[type=text], #hidden-wrapper #inbound-form-wrapper input[type=url], #hidden-wrapper #inbound-form-wrapper input[type=email], #hidden-wrapper #inbound-form-wrapper input[type=tel], #hidden-wrapper #inbound-form-wrapper input[type=number], #hidden-wrapper #inbound-form-wrapper input[type=password] {
 width: 100%;

}
.top {
    display: table;
}
.bottom {
    display: table;
    margin: auto;

}
#bottom-content {
    text-align: center;
    display: table-cell;
    vertical-align: middle;
}
#bottom-content-show, .top #top-vert-center-content {
    width: 600px;
    max-width: 600px;
    margin: auto;
    text-align: left;
}

#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button {
  display:block;
  margin: auto;
  text-align: center;
  border-radius:4px;
  text-decoration: none;
  font-family:'Lucida Grande',helvetica;
  background: #69c773;

  -webkit-box-shadow: 0 4px 0 0 #51a65f;
  -moz-box-shadow: 0 4px 0 0 #51a65f;
  box-shadow: 0 4px 0 0 #51a65f;
  font-size: 20px !important;
  padding: 15px 20px;
  width: 250px;
  color: #FFF;
  border: none;
}
#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
  color: #51A65F;
}
#inbound-form-wrapper button:focus {
outline: none;
}
@import url(http://fonts.googleapis.com/css?family=Lato:300,400,700);
.top #top-vert-center {
    text-align: center;
    display: table-cell;
    vertical-align: middle;

}
.top h1 {

   color: #ffffff;
   font-family: 'Lato', Calibri, Arial, sans-serif;
    font-size: 3em;
    font-weight: 300;
    width: 80%;
    margin: auto;
}
@media only screen and (max-width: 580px) {

  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {

 }
    #hidden-wrapper {
    width: 88%;

}
#hidden-wrapper {

margin-top: 95px;
min-height: 500px;
}

#bottom-content-show, .top #top-vert-center {
    width: 90%;
    max-width: 90%;
    margin: auto;
    text-align: left;
}
.top {
display: block;
}
.top #top-vert-center {

display: block;
}
.top h1 {

font-size: 25px;
padding-top: 20px;
text-align: center;
}
#hidden-wrapper {
    background:#fff;
}
.bottom {

box-shadow: none;
}
@media only screen and (max-width: 870px) {
    #bottom-content-show, .top #top-vert-center {
        width: 90%;
        max-width: 90%;
        margin: auto;
        text-align: left;
    }


}
</style>
<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php
if ( $scheme != "" ) {
echo ".top { background-color: #$scheme;}";
echo ".btn {color: #$scheme; box-shadow: 0 0 0 5px #$scheme,0px 0 0 10px white,0px 0 10px 0 rgba(0,0,0,.5),0px 0 10px 10px rgba(0,0,0,.25)}";

}
if ( $submit_text_color != "" ) {
echo "#inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button { color: #$submit_text_color;}";
}
?>
.bottom.outline-element {
<?php echo $bg_style;?>
}
.btn:hover {
box-shadow: 0 0 0 10px <?php echo $scheme_color[40];?>,0px 0 0 20px white,0px 0 15px 0 rgba(0,0,0,.5),0px 0 10px 20px rgba(0,0,0,.25);
}
#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button {

    -webkit-box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
    -moz-box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
    box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
}
#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
  color: <?php echo $submit[60];?>;
}
</style>
</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
<div class="wrapper" data-selector-on="true" data-eq-selector=".-webkit- .wrapper:eq(0)" data-count-size="2" data-css-selector=".-webkit- .wrapper" data-js-selector=".-webkit- .wrapper:eq(0)">
  <div class="top">
    <div id="top-vert-center">
        <h1><?php the_title(); ?></h1>
    <div id="top-vert-center-content">


    <?php echo lp_get_value($post, $key, "top-content"); ?>
    </div>
    </div>
    <a class="btn inbound_option_area outline-processed-active current-inbound-option-area" href="" id="split-me" data-eq-selector="#split-me:eq(0)" data-count-size="1" data-css-selector="#split-me" data-js-selector="#split-me" data-option-name="Text on Button" data-option-kind="text" inbound-option-name="Text on Button"><?php echo lp_get_value($post, $key, "text-on-button"); ?></a>
  </div>
</div>
<div class="hidden">
    <div id="hidden-wrapper">
 <?php echo do_shortcode( $conversion_area ); ?>
    </div>
</div>
<div class="wrapper" data-eq-selector=".-webkit- .wrapper:eq(1)" data-count-size="2" data-css-selector=".-webkit- .wrapper" data-js-selector=".-webkit- .wrapper:eq(1)">
  <div class="bottom outline-element">

      <div id="bottom-content">
        <div id="bottom-content-show">
            <?php echo lp_get_value($post, $key, "bottom-content"); ?>
        </div>
      </div>

  </div>
</div>
<div id="inbound-template-name" style="display:none;">reveal</div>
<script type="text/javascript">
jQuery(document).ready(function($) {


    $('#split-me').on('touchstart click', function(e){
        var test = $(this).hasClass('active');
        if (!test) {
            $('.hidden').css('z-index', 9999999);
            $('.hidden').fadeIn(300);
            setTimeout(function() {

            }, 300);

           $('.btn').css('z-index', 99999999);
        } else {
           $('.hidden').css('z-index', -4);
           $('.btn').css('z-index', 100);
        }
        e.preventDefault();
          $(this).toggleClass('active');
          $('.top').toggleClass('slide-up');
          $('.bottom').toggleClass('slide-down');

      });


    });

</script>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>