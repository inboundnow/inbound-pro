<?php
/**
* Template Name: lovely-sales-page
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
$subheadline = lp_get_value($post, $key, 'subheadline' );
$subheadline_color = lp_get_value($post, $key, 'subheadline-color' );
$top_content = lp_get_value($post, $key, 'top-content' );
$main_content = lp_get_value($post, $key, 'main-content' );
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
$scheme = inbound_color_scheme($submit_bg_color, 'hex' );

?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php wp_title(); ?></title>
<link href='http://fonts.googleapis.com/css?family=Open+Sans|Maven+Pro:500' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700)' rel='stylesheet' type='text/css'>
<style>* {
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
}


html, body {
  height:100%;
}
body {
  margin:0;
  background:#14b5d1;
  font-size:1.3em;
  color:#333;
  font-family:'Open Sans', sans-serif;
}
section {
  float:left;
  width:100%;
  background:#fff;
  position:relative;
  box-shadow:0 0 5px 0px #333;
}
/* the important styles */
.arrow-wrap {
  position:absolute;
  z-index:1;
  left:50%;
  top:-5em;
  margin-left:-5em;
  background:#111;
  width:10em;
  height:10em;
  padding:4em 2em;
  border-radius:50%;
  font-size:0.5em;
  display:block;
  box-shadow:0px 0px 5px 0px #333;
}
.arrow {
  float:left;
  position:relative;
  width: 0px;
height: 0px;
border-style: solid;
border-width: 3em 3em 0 3em;
border-color: #ffffff transparent transparent transparent;
  -webkit-transform:rotate(360deg)
}
.arrow:after {
  content:'';
  position:absolute;
  top:-3.2em;
  left:-3em;
  width: 0px;
height: 0px;
border-style: solid;
border-width: 3em 3em 0 3em;
border-color: #111 transparent transparent transparent;
  -webkit-transform:rotate(360deg)
}
.hint {
  position:absolute;
  top:0.6em;
  width:100%;
  left:0;
  font-size:2em;
  font-style:italic;
  text-align:center;
  color:#fff;
  opacity:0;
}
.arrow-wrap:hover .hint {
  opacity:1;
}
@-webkit-keyframes arrows {
    0% { top:0; }
    10% { top:12%; }
    20% { top:0; }
    30% { top:12%; }
    40% { top:-12%; }
    50% { top:12%; }
    60% { top:0; }
    70% { top:12%; }
    80% { top:-12%; }
    90% { top:12%; }
    100% { top:0; }
  }
.arrow-wrap .arrow {
    -webkit-animation: arrows 2.8s 0.4s;
    -webkit-animation-delay: 3s;
  }
/*  this is the unimportant CSS used just to layout the content  */
header {
  float:left;
  width:100%;
  padding:2em 4em;
  color:#fff;
  height:calc(100% - 3em);
  text-align: center;

}
header h1 {
  margin:0;
}
header h3 {
  margin:0;
  color: #56dcee ;
}
header a {
  color:#56dcee;
  opacity:0.5;
  text-decoration:none;
}
header a:hover {
  color:#333;
  opacity:1;
}
.content {
  float:left;
  width:70%;
  margin:0 15%;
  padding:2em 0;
}
h1 {
  font-family:'Maven Pro', sans-serif;
  font-weight:300;
  font-size:2.2em;
}
h2, h3 {
  font-family:'Maven Pro', sans-serif;
  font-weight:300;
  font-size:1.5em;
  margin-top:2em;
}
pre {
  background:#ededed;
  padding:1em;
}
p {
  color:#555;
  font-size:0.9em;
}
p a {
  color:#14b5d1;
  text-decoration:none;
}
#content {
  min-height: 800px;

}
:focus {
outline: none;
}
.inbound-grid.two-third {

padding-right: 5%;
}
#content h1 {
text-align: center;
font-size: 2em;
}
#content #inbound-form-wrapper input[type=text], #content #inbound-form-wrapper input[type=url], #content #inbound-form-wrapper input[type=email], #content #inbound-form-wrapper input[type=tel], #content #inbound-form-wrapper input[type=number], #content #inbound-form-wrapper input[type=password] {
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
#content #inbound-form-wrapper input[type=text]:focus, #content #inbound-form-wrapper input[type=url]:focus, #content #inbound-form-wrapper input[type=email]:focus, #content #inbound-form-wrapper input[type=tel]:focus, #content #inbound-form-wrapper input[type=number]:focus, #content #inbound-form-wrapper input[type=password]:focus {
background: #fff;
box-shadow: 0;
border: 3px solid #3498db;
color: #3498db;
outline: none;
padding: 13px;
}
#content #inbound-form-wrapper input[type="submit"], #content #inbound-form-wrapper button  {
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
#inbound-top-area {
margin: 15px auto;
padding: 20px;
background: white;
-moz-box-shadow: 0 0 5px rgba(0,0,0,.3);
-webkit-box-shadow: 0 0 5px rgba(0,0,0,.3);
box-shadow: 0 0 5px rgba(0,0,0,.3);
width: 75%;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
position: relative;
padding-bottom: 36.25%;
padding-top: 30px;
height: 0;
color: #333;
height: 450px;
overflow: hidden;}
#inbound-top-area iframe, #inbound-top-area object, #inbound-top-area embed {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
padding: 2%;
}
body {<?php echo $bg_style;?> }
@media only screen and (max-width: 580px) {

 #content #inbound-form-wrapper input[type=text], #content #inbound-form-wrapper input[type=url], #content #inbound-form-wrapper input[type=email], #content #inbound-form-wrapper input[type=tel], #content #inbound-form-wrapper input[type=number], #content #inbound-form-wrapper input[type=password] {

 }
 #inbound-top-area iframe, #inbound-top-area object, #inbound-top-area embed {

}
#inbound-top-area {

height: 235px;}
h3 {

font-size: 1em;}

}
@media only screen and (max-width: 870px) {
    #bottom-content-show, .top #top-vert-center {
        width: 90%;
        max-width: 90%;
        margin: auto;
        text-align: left;
    }
    #inbound-top-area {
  margin-top: 35px;
    width: 100%;}
    header h1 {
    margin: 0;
    font-size: 1.7em;
    }
    #content h1 {

        font-size: 1.7em;
        }
    header {
    float: left;
    width: 100%;
    padding: 2em 2em;}
    #content .inbound-grid.two-third, #content .inbound-grid.one-third,#content .inbound-grid.inbound-3-col {
    width: 100%;
    }
    .content {
    float: left;
    width: 80%;
    margin: 0 12%;}


}
header h3 {color: <?php echo $scheme[15];?>;}

<?php
if ( $headline_color != "" ) {
echo "header h1 { color: #$headline_color;}";
}

if ( $subheadline_color != "" ) {
echo "header h3 { color: #$subheadline_color;}";
}

if ( $submit_bg_color != "" ) {
echo "#content #inbound-form-wrapper input[type='submit'], #content #inbound-form-wrapper button { background-color: #$submit_bg_color;}";
echo "#content #inbound-form-wrapper input[type=text], #content #inbound-form-wrapper input[type=url], #content #inbound-form-wrapper input[type=email], #content #inbound-form-wrapper input[type=tel], #content #inbound-form-wrapper input[type=number], #content #inbound-form-wrapper input[type=password] {border: 3px solid #$submit_bg_color;}";
}
if ( $submit_text_color != "" ) {
echo "#content #inbound-form-wrapper input[type='submit'], #content #inbound-form-wrapper button  { color: #$submit_text_color;}";
}

?>
#content #inbound-form-wrapper input[type=text]:focus, #content #inbound-form-wrapper input[type=url]:focus, #content #inbound-form-wrapper input[type=email]:focus, #content #inbound-form-wrapper input[type=tel]:focus, #content #inbound-form-wrapper input[type=number]:focus, #content #inbound-form-wrapper input[type=password]:focus {
  border: 3px solid <?php echo $scheme[30];?>;
}
#content #inbound-form-wrapper input[type='submit']:hover, #content #inbound-form-wrapper button:hover {
  background-color:  <?php echo $scheme[40];?>;
}
</style>
<?php wp_head(); do_action('lp_head');?>

</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>

<header class="outline-element">
  <h1 class="inbound_the_title" data-eq-selector="body h1:eq(0)" data-count-size="3" data-css-selector="body h1" data-js-selector="body h1:eq(0)">
<?php lp_main_headline(); // Main Headline ?>
</h1>
  <h3 class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="body h3:eq(0)" data-count-size="1" data-css-selector="body h3" data-js-selector="body h3" data-option-name="Subheadline" data-option-kind="textarea" inbound-option-name="Subheadline"><?php echo lp_get_value($post, $key, "subheadline"); ?></h3>

  <div id="inbound-top-area">
      <?php echo wpautop($top_content); ?>
  </div>
</header>
<section class="main">
  <a class="arrow-wrap" href="#content">
<span class="arrow"></span>
</a>
<div class="content inbound_option_area outline-processed-active current-inbound-option-area" id="content" data-selector-on="true" data-eq-selector="#content:eq(0)" data-count-size="1" data-css-selector="#content" data-js-selector="#content" data-option-name="Main Content" data-option-kind="wysiwyg" inbound-option-name="Main Content"><?php echo do_shortcode(wpautop($main_content)); ?></div>
</section>
<div id="inbound-template-name" style="display:none;">lovely-sales-page</div>
<script type="text/javascript">

    //this is where we apply opacity to the arrow
    jQuery(window).scroll( function(){

      //get scroll position
      var topWindow = jQuery(window).scrollTop();
      //multipl by 1.5 so the arrow will become transparent half-way up the page
      var topWindow = topWindow * 1.5;

      //get height of window
      var windowHeight = jQuery(window).height();

      //set position as percentage of how far the user has scrolled
      var position = topWindow / windowHeight;
      //invert the percentage
      position = 1 - position;

      //define arrow opacity as based on how far up the page the user has scrolled
      //no scrolling = 1, half-way up the page = 0
      jQuery('.arrow-wrap').css('opacity', position);

    });






    //Code stolen from css-tricks for smooth scrolling:

    jQuery(document).ready(function() {
      jQuery('.inbound-button-submit br').remove();
      function filterPath(string) {
      return string
        .replace(/^\//,'')
        .replace(/(index|default).[a-zA-Z]{3,4}$/,'')
        .replace(/\/$/,'');
      }
      var locationPath = filterPath(location.pathname);
      var scrollElem = scrollableElement('html', 'body');

      jQuery('a[href*=#]').each(function() {
        var thisPath = filterPath(this.pathname) || locationPath;
        if (  locationPath == thisPath
        && (location.hostname == this.hostname || !this.hostname)
        && this.hash.replace(/#/,'') ) {
          var $target = jQuery(this.hash), target = this.hash;
          if (target) {
            var targetOffset = $target.offset().top;
            jQuery(this).click(function(event) {
              event.preventDefault();
              jQuery(scrollElem).animate({scrollTop: targetOffset}, 400, function() {
                location.hash = target;
              });
            });
          }
        }
      });

      // use the first element that is "scrollable"
      function scrollableElement(els) {
        for (var i = 0, argLength = arguments.length; i <argLength; i++) {
          var el = arguments[i],
              $scrollElement = jQuery(el);
          if ($scrollElement.scrollTop()> 0) {
            return el;
          } else {
            $scrollElement.scrollTop(1);
            var isScrollable = $scrollElement.scrollTop()> 0;
            $scrollElement.scrollTop(0);
            if (isScrollable) {
              return el;
            }
          }
        }
        return [];
      }

    });



</script>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>