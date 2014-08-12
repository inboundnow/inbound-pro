<?php
/**
* Template Name: splash
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
$top_background_image = lp_get_value($post, $key, 'top-background-image' );
$scheme_color = lp_get_value($post, $key, 'scheme-color' );
$headline_color = lp_get_value($post, $key, 'headline-color' );
$sub_text_color = lp_get_value($post, $key, 'sub-text-color' );
$subheadline = lp_get_value($post, $key, 'subheadline' );
$show_top = lp_get_value($post, $key, 'show_top' );
$bottom_content = lp_get_value($post, $key, 'bottom-content' );
$turn_off_editor = lp_get_value($post, $key, 'turn-off-editor' );
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$scheme = inbound_color_scheme($scheme_color, 'hex' );

?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<title><?php wp_title(); ?></title>
<style type='text/css'>/*! normalize.css v2.1.2 | MIT License | git.io/normalize */
/* ==========================================================================
   HTML5 display definitions
   ========================================================================== */
/**
 * Correct `block` display not defined in IE 8/9.
 */
article,
aside,
details,
figcaption,
figure,
footer,
header,
hgroup,
main,
nav,
section,
summary {
    display: block;
}
/**
 * Correct `inline-block` display not defined in IE 8/9.
 */
audio,
canvas,
video {
    display: inline-block;
}
/**
 * Prevent modern browsers from displaying `audio` without controls.
 * Remove excess height in iOS 5 devices.
 */
audio:not([controls]) {
    display: none;
    height: 0;
}
/**
 * Address styling not present in IE 8/9.
 */
[hidden] {
    display: none;
}
/* ==========================================================================
   Base
   ========================================================================== */
/**
 * 1. Set default font family to sans-serif.
 * 2. Prevent iOS text size adjust after orientation change, without disabling
 *    user zoom.
 */
html {
    font-family: sans-serif; /* 1 */
    -ms-text-size-adjust: 100%; /* 2 */
    -webkit-text-size-adjust: 100%; /* 2 */
}
/**
 * Remove default margin.
 */
body {
    margin: 0;
}
/* ==========================================================================
   Links
   ========================================================================== */
/**
 * Address `outline` inconsistency between Chrome and other browsers.
 */
a:focus {
    outline: thin dotted;
}
/**
 * Improve readability when focused and also mouse hovered in all browsers.
 */
a:active,
a:hover {
    outline: 0;
}
/* ==========================================================================
   Typography
   ========================================================================== */
/**
 * Address variable `h1` font-size and margin within `section` and `article`
 * contexts in Firefox 4+, Safari 5, and Chrome.
 */
h1 {
    font-size: 2em;
    margin: 0.67em 0;
}
/**
 * Address styling not present in IE 8/9, Safari 5, and Chrome.
 */
abbr[title] {
    border-bottom: 1px dotted;
}
/**
 * Address style set to `bolder` in Firefox 4+, Safari 5, and Chrome.
 */
b,
strong {
    font-weight: bold;
}
/**
 * Address styling not present in Safari 5 and Chrome.
 */
dfn {
    font-style: italic;
}
/**
 * Address differences between Firefox and other browsers.
 */
hr {
    -moz-box-sizing: content-box;
    box-sizing: content-box;
    height: 0;
}
/**
 * Address styling not present in IE 8/9.
 */
mark {
    background: #ff0;
    color: #000;
}
/**
 * Correct font family set oddly in Safari 5 and Chrome.
 */
code,
kbd,
pre,
samp {
    font-family: monospace, serif;
    font-size: 1em;
}
/**
 * Improve readability of pre-formatted text in all browsers.
 */
pre {
    white-space: pre-wrap;
}
/**
 * Set consistent quote types.
 */
q {
    quotes: "\201C" "\201D" "\2018" "\2019";
}
/**
 * Address inconsistent and variable font size in all browsers.
 */
small {
    font-size: 80%;
}
/**
 * Prevent `sub` and `sup` affecting `line-height` in all browsers.
 */
sub,
sup {
    font-size: 75%;
    line-height: 0;
    position: relative;
    vertical-align: baseline;
}
sup {
    top: -0.5em;
}
sub {
    bottom: -0.25em;
}
/* ==========================================================================
   Embedded content
   ========================================================================== */
/**
 * Remove border when inside `a` element in IE 8/9.
 */
img {
    border: 0;
}
/**
 * Correct overflow displayed oddly in IE 9.
 */
svg:not(:root) {
    overflow: hidden;
}
/* ==========================================================================
   Figures
   ========================================================================== */
/**
 * Address margin not present in IE 8/9 and Safari 5.
 */
figure {
    margin: 0;
}
/* ==========================================================================
   Forms
   ========================================================================== */
/**
 * Define consistent border, margin, and padding.
 */
fieldset {
    border: 1px solid #c0c0c0;
    margin: 0 2px;
    padding: 0.35em 0.625em 0.75em;
}
/**
 * 1. Correct `color` not being inherited in IE 8/9.
 * 2. Remove padding so people aren't caught out if they zero out fieldsets.
 */
legend {
    border: 0; /* 1 */
    padding: 0; /* 2 */
}
/**
 * 1. Correct font family not being inherited in all browsers.
 * 2. Correct font size not being inherited in all browsers.
 * 3. Address margins set differently in Firefox 4+, Safari 5, and Chrome.
 */
button,
input,
select,
textarea {
    font-family: inherit; /* 1 */
    font-size: 100%; /* 2 */
    margin: 0; /* 3 */
}
/**
 * Address Firefox 4+ setting `line-height` on `input` using `!important` in
 * the UA stylesheet.
 */
button,
input {
    line-height: normal;
}
/**
 * Address inconsistent `text-transform` inheritance for `button` and `select`.
 * All other form control elements do not inherit `text-transform` values.
 * Correct `button` style inheritance in Chrome, Safari 5+, and IE 8+.
 * Correct `select` style inheritance in Firefox 4+ and Opera.
 */
button,
select {
    text-transform: none;
}
/**
 * 1. Avoid the WebKit bug in Android 4.0.* where (2) destroys native `audio`
 *    and `video` controls.
 * 2. Correct inability to style clickable `input` types in iOS.
 * 3. Improve usability and consistency of cursor style between image-type
 *    `input` and others.
 */
button,
html input[type="button"], /* 1 */
input[type="reset"],
input[type="submit"] {
    -webkit-appearance: button; /* 2 */
    cursor: pointer; /* 3 */
}
/**
 * Re-set default cursor for disabled elements.
 */
button[disabled],
html input[disabled] {
    cursor: default;
}
/**
 * 1. Address box sizing set to `content-box` in IE 8/9.
 * 2. Remove excess padding in IE 8/9.
 */
input[type="checkbox"],
input[type="radio"] {
    box-sizing: border-box; /* 1 */
    padding: 0; /* 2 */
}
/**
 * 1. Address `appearance` set to `searchfield` in Safari 5 and Chrome.
 * 2. Address `box-sizing` set to `border-box` in Safari 5 and Chrome
 *    (include `-moz` to future-proof).
 */
input[type="search"] {
    -webkit-appearance: textfield; /* 1 */
    -moz-box-sizing: content-box;
    -webkit-box-sizing: content-box; /* 2 */
    box-sizing: content-box;
}
/**
 * Remove inner padding and search cancel button in Safari 5 and Chrome
 * on OS X.
 */
input[type="search"]::-webkit-search-cancel-button,
input[type="search"]::-webkit-search-decoration {
    -webkit-appearance: none;
}
/**
 * Remove inner padding and border in Firefox 4+.
 */
button::-moz-focus-inner,
input::-moz-focus-inner {
    border: 0;
    padding: 0;
}
/**
 * 1. Remove default vertical scrollbar in IE 8/9.
 * 2. Improve readability and alignment in all browsers.
 */
textarea {
    overflow: auto; /* 1 */
    vertical-align: top; /* 2 */
}
/* ==========================================================================
   Tables
   ========================================================================== */
/**
 * Remove most spacing between table cells.
 */
table {
    border-collapse: collapse;
    border-spacing: 0;
}</style>
<style>@import url(http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700);
body {
  font-family: "Open Sans";
  background: #27ae60;
  height: 100vh;
}
input:focus,
select:focus,
textarea:focus,
button:focus {
  outline: none;
}
.topslot {
  background: url("<?php echo $path;?>images/background.jpg") #229955 center;
  background-size: cover;
  padding: 1rem 0rem 6rem 0rem;
  box-shadow: 0em -4em 8em -4em rgba(0, 0, 0, 0.5) inset;
  border-bottom: 0.3rem solid #1e8449;
}
#inbound-main-headline {
  display: block;
  color: #fff;
  font-size: 4rem;
  font-weight: 400;
  text-align: center;
  margin: 2rem 0rem 0rem;
}
#inbound-splash-container #inbound-main-headline h1 {
  font-size: 5rem;
  margin: 0rem;
}
#inbound-splash-container #inbound-main-headline p {
  margin: 0rem;
  font-style: italic;
  font-weight: 300;
  font-size: 1.09rem;
}
form {
  padding: 1rem;
  background: #fff;
  border-radius: 0.25rem;
  box-shadow: 0rem 0.3rem 1rem 0.1rem rgba(0, 0, 0, 0.35);
}
input {
  margin-bottom: 1rem;
  display: block;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
input:not([type=submit]) {

  padding: 0.5rem 0.5rem;
  border-radius: 0.15rem;
  border: 2px solid #2cc36b;
  -webkit-transition: border 0.5s ease;
  -moz-transition: border 0.5s ease;
  -o-transition: border 0.5s ease;
  transition: border 0.5s ease;
  box-shadow: 0rem 0.25rem 0.25rem 0rem rgba(0, 0, 0, 0.15) inset;
  font-size: 1rem;
  font-weight: 300;
  width: 100%;
}
input:not([type=submit]):focus {
  border-left-width: 0.5rem;
}
.signup-fields {
  display: none;
}
input[type=submit], #conversion-area #inbound-form-wrapper [type="submit"] {
  border: 0rem;
  width: 100%;
  background-color: #27ae60;
  color: white;
  margin-bottom: 0rem;
  font-size: 1.4rem;
  padding: 0.3rem 0rem;
}
input[type=submit]:hover, #conversion-area #inbound-form-wrapper [type="submit"]:hover {
  cursor: pointer;
  background-color: #36d278;
  -webkit-transition: background 0.5s ease;
}
input[type=submit]:active, #conversion-area #inbound-form-wrapper [type=submit]:active {
  -webkit-transition-duration: 0s;
  background-color: #229955;
}
#signup.on {
  color: #fff;
}
.main {
  margin: -2.5rem auto 0rem;
  width: 16rem;
}
.main .extras {
  text-align: center;
  font-size: 0.8rem;
  margin-top: 1rem;
}
.main .extras a {
  text-decoration: none;
  font-weight: 300;
  color: #75e0a2;
}
.main .extras a:hover {
  color: #fff;
  text-decoration: underline;
}
#inbound-main-content {
background-color: #ffffff;
}
#splash-container {
width: 860px;
margin: 25px auto;
}
#inbound-main-content {
display: inline-block;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
background-color: #fff;
overflow: hidden;
margin: auto;
padding: 20px;
margin: -2.5rem auto 0rem;
width: 625px;
vertical-align: top;
padding-bottom: 50px;
box-shadow: 0rem 0.3rem 1rem 0.1rem rgba(0, 0, 0, 0.35);
}
#conversion-area {
  display: inline-block;
  vertical-align: top;
  margin-right: 25px;
}
#inbound-form-holder {
  margin: -1rem auto 1rem;
  width: 980px;
}
#inbound-splash-container h1 {
color: rgba(0,0,0,.7);
font-weight: 900;
font-size: 2.5em;
margin-top: 0px;
}
#inbound-splash-container #inbound-main-headline h1, #inbound-splash-container #inbound-main-headline p {
  color: #fff;
}
#inbound-splash-container #inbound-main-headline {
  color: #fff;
}
#inbound-main-content, #inbound-splash-container h1, #inbound-splash-container p {
  color: #404040;
}
#inbound-splash-container p {
margin: 0px;
margin-top: 15px;
font-size: 1.1em;
}
#inbound-splash-container #inbound-form-wrapper input[type=text], #inbound-splash-container #inbound-form-wrapper input[type=url], #inbound-splash-container #inbound-form-wrapper input[type=email],#inbound-splash-container #inbound-form-wrapper input[type=tel],#inbound-splash-container #inbound-form-wrapper input[type=number],#inbound-splash-container #inbound-form-wrapper input[type=password] {
 width: 100%;


}

@media only screen and (max-width: 580px) {

  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
  width: 92%;
  font-size: 18px;
 }
 .inbound-label.inbound-label-top, .inbound-label.inbound-label-bottom {
 display: block;
 width: 91%;}
 #conversion-area {
 margin: auto;
 width: 92% !important;
 }
 #inbound-splash-container #inbound-main-headline h1 {
 font-size: 2rem; }
 .topslot {

 padding: 1rem 0rem 3rem 0rem;
}

}
@media only screen and (max-width: 870px) {

  #inbound-form-holder {
  margin: auto;
  width: 100%;
  text-align: center;
  }
  #conversion-area {
  display: inline-block;
  vertical-align: top;
  margin-right: 0px;
  }
  #conversion-area {
  margin: auto;
  width: 75%;
  }
  #inbound-main-content {
    text-align: left;

  padding: 20px;
  margin: 0px;
  margin-bottom: 20px;
  width: 94%;}
  #inbound-splash-container #inbound-main-headline h1 {
  font-size: 3rem; }
   .topslot {

   padding: 1rem 0rem 4rem 0rem;
  }

}

</style>
<?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php
if ( $scheme_color != "" ) {
echo "body { background: #$scheme_color;}";
echo "input[type=submit], #conversion-area #inbound-form-wrapper [type=submit] { background-color: #$scheme_color;}";
echo "input:not([type=submit]) { border: 2px solid #$scheme_color; }";

}

if ( $headline_color != "" ) {
echo "#inbound-splash-container #inbound-main-headline h1 { color: #$headline_color !important; }";
}

if ( $sub_text_color != "" ) {
echo "#inbound-splash-container #inbound-main-headline p, #inbound-splash-container #inbound-main-headline { color: #$sub_text_color !important;}";
}
?>
.topslot { border-bottom: 0.3rem solid <?php echo $scheme[70];?>; }
input[type=submit]:hover, #inbound-form-holder #conversion-area #inbound-form-wrapper [type="submit"]:hover {
  background-color: <?php echo $scheme[40];?>;
}
</style>
</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>

<div class="landing" id="inbound-splash-container">

<section class="topslot inbound_option_area outline-processed-active current-inbound-option-area" data-selector-on="true" data-eq-selector=".landing .topslot:eq(0)" data-count-size="1" data-css-selector=".landing .topslot" data-js-selector=".landing .topslot" data-option-name="Background Image" data-option-kind="media" inbound-option-name="Background Image">
  <div class="wrapper">

      <div id="inbound-main-headline" class="brief">
        <h1><?php the_title();?></h1>
        <p><?php echo $subheadline;?></p>
      </div>

    </div>

</section>
<section class="outline-element">
  <div id="inbound-form-holder">
  <div class="main" id="conversion-area">
   <?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
  </div>
  <div id="inbound-main-content">

   <?php echo do_shortcode( $content ); ?>

  </div>
  </div>
</section>
<div id="splash-container">

</div>
</div>
<div id="inbound-template-name" style="display:none;">splash</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>