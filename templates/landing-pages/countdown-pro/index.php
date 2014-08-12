<?php
/**
 * Template Name:  CountDown Pro Template
 * @package  WordPress Landing Pages
 * @author   Your Name Here!
 * @example  example.com/landing-page
 * @version  1.0
 * @since    1.0
 */

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');

// Headline Text Color: Use this setting to change the Heading Text Color
$headline_color = lp_get_value($post, $key, 'headline-color');
// Countdown Date: Use this setting to change the Heading Text Color
$date_picker = lp_get_value($post, $key, 'date-picker');
// Content Area Text Color: Use this setting to change the Heading Text Color
$text_color = lp_get_value($post, $key, 'text-color');
// Content Background Color: Use this setting to change the Heading Text Color
$content_background = lp_get_value($post, $key, 'content-background');
// Opacity of Content Background: Use this setting to change the Heading Text Color
$opacity = lp_get_value($post, $key, 'opacity');
// Form Submit Button Color: Use this setting to change the Heading Text Color
$form_submit_color = lp_get_value($post, $key, 'form-submit-color');
// Countdown Style: Use this setting to change the Heading Text Color
$style = lp_get_value($post, $key, 'style');
// Days Circle Color: Use this setting to change the Heading Text Color
$circle_days = lp_get_value($post, $key, 'circle-days');
// Hours Circle Color: Use this setting to change the Heading Text Color
$circle_hours = lp_get_value($post, $key, 'circle-hours');
// Minutes Circle Color: Use this setting to change the Heading Text Color
$circle_minutes = lp_get_value($post, $key, 'circle-minutes');
// Seconds Circle Color: Use this setting to change the Heading Text Color
$circle_seconds = lp_get_value($post, $key, 'circle-seconds');
// Background Settings: Use this setting to change the Heading Text Color
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Use this setting to change the Heading Text Color
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Use this setting to change the Heading Text Color
$background_color = lp_get_value($post, $key, 'background-color');
$countdown_text = lp_get_value($post, $key, 'countdown-text');

// Date Formatting
$new_value = str_replace('-',' ', $date_picker);
$js_date = str_replace(':',' ', $new_value);
$res = preg_replace('/[^a-z0-9åäö\s]/ui', '', $js_date);
$arr = preg_split('/\s+/', $res, 6);
$imploded = implode(',', array_slice($arr, 0, 5));
$date_array = explode(",", $imploded);

    // Convert Hex to RGB Value for submit button
function lp_Hex_2_RGB($hex) {
        $hex = @ereg_replace("#", "", $hex);
        $color = array();

        if(strlen($hex) == 3) {
            $color['r'] = hexdec(substr($hex, 0, 1) . $r);
            $color['g'] = hexdec(substr($hex, 1, 1) . $g);
            $color['b'] = hexdec(substr($hex, 2, 1) . $b);
        }
        else if(strlen($hex) == 6) {
            $color['r'] = hexdec(substr($hex, 0, 2));
            $color['g'] = hexdec(substr($hex, 2, 2));
            $color['b'] = hexdec(substr($hex, 4, 2));
        }

        return $color;

}
// form
$RBG_array = lp_Hex_2_RGB($form_submit_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];
// content RBG
$content_rbg_array = lp_Hex_2_RGB($content_background);
$cred = $content_rbg_array['r'];
$cgreen = $content_rbg_array["g"];
$cblue = $content_rbg_array["b"];
// circle days
$circle_days_array = lp_Hex_2_RGB($circle_days);
$circle_days_red = $circle_days_array['r'];
$circle_days_green = $circle_days_array["g"];
$circle_days_blue = $circle_days_array["b"];
// circle hours
$circle_hours_array = lp_Hex_2_RGB($circle_hours);
$circle_hours_red = $circle_hours_array['r'];
$circle_hours_green = $circle_hours_array["g"];
$circle_hours_blue = $circle_hours_array["b"];
// circle minutes
$circle_minutes_array = lp_Hex_2_RGB($circle_minutes);
$circle_minutes_red = $circle_minutes_array['r'];
$circle_minutes_green = $circle_minutes_array["g"];
$circle_minutes_blue = $circle_minutes_array["b"];
// circle seconds
$circle_seconds_array = lp_Hex_2_RGB($circle_seconds);
$circle_seconds_red = $circle_seconds_array['r'];
$circle_seconds_green = $circle_seconds_array["g"];
$circle_seconds_blue = $circle_seconds_array["b"];

if ( $background_style === "fullscreen" ) {
    $bg_style = 'background: url('.$background_image.') no-repeat center center fixed;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
    filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
    -ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';
    };

if ( $background_style === "color" ) {
    $bg_style = 'background: #'.$background_color.';';
    };

if ( $background_style === "tile" ) {
    $bg_style = 'background: url('.$background_image.') repeat; ';
    };

if ( $background_style === "repeat-x" ) {
    $bg_style = 'background: url('.$background_image.') repeat-x; ';
    };

if ( $background_style === "repeat-y" ) {
    $bg_style = 'background: url('.$background_image.') repeat-y; ';
    };

if ( $background_style === "repeat-y" ) {
    $bg_style = 'background: url('.$background_image.') repeat-y; ';
    };
if ( $background_style === "default" ) {
    $bg_style = 'background: url("'.$path.'/img/groovepaper.png") repeat; ';
    };

// ticker style

if ( $style === "half-circle" ) {
    $circle_style = 'strokeWidth:45,
        strokeBackgroundWidth:45,';
    };

if ( $style === "full-circle" ) {
    $circle_style = 'strokeWidth:80,
        strokeBackgroundWidth:80,';
    };
if ( $style === "default" ) {
    $circle_style = 'strokeWidth:23,
        strokeBackgroundWidth:23,';
    };

?>


<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8" />
<title><?php wp_title(); ?></title>
<html lang="en">

<head>

<meta charset="utf-8">

<link rel="stylesheet" href="<?php echo $path;?>css/layout.css" type="text/css" />

<style type="text/css">
/* Inline Style Changes go here */
body { <?php echo $bg_style; ?> }
<?php if ($form_submit_color != "") {
          echo"input[type='submit'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 1)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));
               background: linear-gradient(rgba($red,$green,$blue, 0.8), rgba($red,$green,$blue, 1));}";}
if ($content_background != "") {
    echo "#countdown_container {background: rgba($cred,$cgreen,$cblue,$opacity); border: rgba($cred,$cgreen,$cblue,$opacity);}";}
if ($headline_color != "") {
    echo "h1 {color:#$headline_color;}";}
if ($text_color != "") {
    echo "#countdown_container {color:#$text_color;}";}
if ($text_color != "000000") {
    echo "body {text-shadow:none;}";}
if ($countdown_text != "000000") {
    echo "#countdown_timer li {color:#$countdown_text;}";}

?>
#inbound-conversion-area {
    width: 40%;
    margin: auto;
}
#inbound-conversion-area .gform_wrapper .top_label input.medium, #inbound-conversion-area .gform_wrapper .top_label select.medium {
width: 100%;
}
#inbound-conversion-area .gform_wrapper .gform_footer {
padding: 0px 0 10px 0;}
#inbound-conversion-area .gform_wrapper .gform_footer input.button, #inbound-conversion-area .gform_wrapper .gform_footer input[type=submit] {
font-size: 1.5em;
}
</style>

<?php wp_head(); // Load Regular WP Head ?>
<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
</head>

<body>

<div id="countdown_container">


    <h1><?php the_title(); ?></h1>

    <div id="countdown_timer"></div><!-- Countdown values -->

    <div id="countdown_clock">

        <canvas id="circular_countdown" width="800" height="180"></canvas><!-- Countdown circular shapes -->

    </div>

 <div id="content-area">  <?php echo do_shortcode( $content ); ?>
    <div id="inbound-conversion-area">
<?php  echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
</div>
</div>
</div>

<script src="<?php echo $path;?>js/countdown_plugins.js"></script>
<script src="<?php echo $path;?>js/countdown.js"></script>

<?php break; endwhile; endif; // end WordPress Loop
do_action('lp_footer'); // Load custom landing page footer items
wp_footer(); // Load regular WordPress Footer
?>
<script>
jQuery(document).ready(function ($) {

(function(e){function r(){var n=e("#circular_countdown"),r=(n.width()*20/100-t.strokeWidth)/2,i=r*2+t.strokeWidth,s=(n.width()-i*4)/3,o=i/2,u=i+o+s,a=i+u+s,f=i+a+s,l=i/2;e("canvas").drawArc({layer:true,name:"days_bg",group:"backgrounds",strokeStyle:t.strokeDaysBackgroundColor,x:o,y:l}).drawArc({layer:true,name:"hours_bg",group:"backgrounds",strokeStyle:t.strokeHoursBackgroundColor,x:u,y:l}).drawArc({layer:true,name:"minutes_bg",group:"backgrounds",strokeStyle:t.strokeMinutesBackgroundColor,x:a,y:l}).drawArc({layer:true,name:"seconds_bg",group:"backgrounds",strokeStyle:t.strokeSecondsBackgroundColor,x:f,y:l}).setLayerGroup("backgrounds",{strokeWidth:t.strokeBackgroundWidth,radius:r,shadowColor:t.backgroundShadowColor,shadowBlur:t.backgroundShadowBlur}).drawArc({layer:true,name:"days",group:"counters",strokeStyle:t.strokeDaysColor,x:o,y:l}).drawArc({layer:true,name:"hours",group:"counters",strokeStyle:t.strokeHoursColor,x:u,y:l}).drawArc({layer:true,name:"minutes",group:"counters",strokeStyle:t.strokeMinutesColor,x:a,y:l}).drawArc({layer:true,name:"seconds",group:"counters",strokeStyle:t.strokeSecondsColor,x:f,y:l}).setLayerGroup("counters",{strokeWidth:t.strokeWidth,radius:r,shadowColor:t.strokeShadowColor,shadowBlur:t.strokeShadowBlur}).drawLayers()}function i(){e("canvas").animateLayer("days",{end:e("#countdown_timer ul li.days em").text()*.9863},t.countdownTickSpeed,t.countdownEasing).animateLayer("hours",{end:e("#countdown_timer ul li.hours em").text()*15},t.countdownTickSpeed,t.countdownEasing).animateLayer("minutes",{end:e("#countdown_timer ul li.minutes em").text()*6},t.countdownTickSpeed,t.countdownEasing).animateLayer("seconds",{end:e("#countdown_timer ul li.seconds em").text()*6},t.countdownTickSpeed,t.countdownEasing)}function s(){e("#countdown_timer").countdown({until:new Date(<?php if (isset($date_array[0])) { echo $date_array[0] ; } ?>,<?php if (isset($date_array[1])) { echo $date_array[1] - 1 ; } ?>,<?php if (isset($date_array[2])) { echo $date_array[2] ; } ?><?php if ($date_array[3] != "") { echo "," . $date_array[3] ; } ?>),timezone:1,format:"DHMS",layout:"<ul>"+'{d<}<li class="days"><em>{dn}</em> {dl}</li>{d>}'+'{h<}<li class="hours"><em>{hn}</em> {hl}</li>{h>}'+'{m<}<li class="minutes"><em>{mn}</em> {ml}</li>{m>}'+'{s<}<li class="seconds"><em>{sn}</em> {sl}</li>{s>}'+"</ul>",onTick:function(){i()}})}var t={strokeDaysBackgroundColor:"rgba(101,127,129,0.06)",strokeDaysColor:"rgba(101,127,129,0.2)",strokeHoursBackgroundColor:"rgba(101,127,129,0.06)",strokeHoursColor:"rgba(101,127,129,0.2)",strokeMinutesBackgroundColor:"rgba(101,127,129,0.06)",strokeMinutesColor:"rgba(101,127,129,0.2)",strokeSecondsBackgroundColor:"rgba(101,127,129,0.06)",strokeSecondsColor:"rgba(101,127,129,0.2)",strokeWidth:17,strokeBackgroundWidth:17,countdownEasing:"easeOutBounce",countdownTickSpeed:"slow",backgroundShadowColor:"rgba(0,0,0,0.2)",backgroundShadowBlur:0,strokeShadowColor:"rgba(0,0,0,0.2)",strokeShadowBlur:0};var n={init:function(n){t=e.extend(1,t,n);return this.each(function(){s();if(!(e.browser.msie&&parseInt(e.browser.version)<9)){r()}})},update:function(n){t=e.extend(1,t,n)}};e.fn.circularCountdown=function(t){if(n[t]){return n[t].apply(this,Array.prototype.slice.call(arguments,1))}else if(typeof t==="object"||!t){return n.init.apply(this,arguments)}else{e.error("No found method "+t)}}})(jQuery)

    $('#circular_countdown').circularCountdown({
        strokeDaysBackgroundColor:'rgba(0,0,0,0.06)',
        strokeDaysColor:'rgba(<?php echo $circle_days_red;?>,<?php echo $circle_days_green;?>,<?php echo $circle_days_blue;?>,0.8)',
        strokeHoursBackgroundColor:'rgba(0,0,0,0.06)',
        strokeHoursColor:'rgba(<?php echo $circle_hours_red;?>,<?php echo $circle_hours_green;?>,<?php echo $circle_hours_blue;?>,0.8)',
        strokeMinutesBackgroundColor:'rgba(0,0,0,0.06)',
        strokeMinutesColor:'rgba(<?php echo $circle_minutes_red;?>,<?php echo $circle_minutes_green;?>,<?php echo $circle_minutes_blue;?>,0.8)',
        strokeSecondsBackgroundColor:'rgba(0,0,0,0.06)',
        strokeSecondsColor:'rgba(<?php echo $circle_seconds_red;?>,<?php echo $circle_seconds_green;?>,<?php echo $circle_seconds_blue;?>,0.8)',
        // Stroke widths can be set to a number equal to 10% of the total width
        // of the countdown to get full circles instead of strokes.
        // If your countdown has a width of 800px, set the 'strokeWidth' and 'strokeBackgroundWidth' to 80
        // to get full circles. Use 70 if your countdown has a width of 700px, etc.
        <?php echo $circle_style;?>
        countdownEasing:'easeOutBounce',
        countdownTickSpeed:'fast',
        backgroundShadowColor: 'rgba(0,0,0,0.2)',
        backgroundShadowBlur: 0,
        strokeShadowColor: 'rgba(0,0,0,0.2)',
        strokeShadowBlur: 0
    });



});
</script>
</body>
</html>