<?php
/*****************************************/
// Template Title:  Countdown Lander Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_URLPATH.'templates/'.$key.'/';
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

    /* Pre-load meta data into variables */
    $content = lp_get_value($post, $key, 'main-content');
    $conversion_area = lp_get_value($post, $key, 'conversion-area-content');
    $body_color = lp_get_value($post, $key, 'body-color');
    $headline_color = lp_get_value($post, $key, 'headline-color');
    $text_color = lp_get_value($post, $key, 'other-text-color');
    $content_color = lp_get_value($post, $key, 'content-background');
    $background_on = lp_get_value($post, $key, 'background-on');

    $date_picker = lp_get_value($post, $key, 'date-picker');
    $social_display = lp_get_value($post, $key, 'display-social');
    $countdown_message = lp_get_value($post, $key, 'countdown-message');
    $bg_image = lp_get_value($post, $key, 'bg-image');
    $submit_button_color = lp_get_value($post, $key, 'submit-button-color');

	// Date Formatting
	$new_value = str_replace('-',' ', $date_picker);
	$js_date = str_replace(':',' ', $new_value);
	$res = preg_replace('/[^a-z0-9åäö\s]/ui', '', $js_date);
	$arr = preg_split('/\s+/', $res, 6);
	$imploded = implode(',', array_slice($arr, 0, 5));
	$date_array = explode(",", $imploded);

// Convert Hex to RGB Value for submit button
function lp_Hex_2_RGB($hex) {
        $hex = @preg_replace("/#/", "", $hex);
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
$RBG_array = lp_Hex_2_RGB($submit_button_color);
$red = (isset($RBG_array['r'])) ? $RBG_array['r'] : '0';
$green = (isset($RBG_array['g'])) ? $RBG_array['g'] : '0';
$blue =  (isset($RBG_array['b'])) ? $RBG_array['b'] : '0';



?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?php wp_title(); ?></title>
		<?php /* Load all functions hooked to lp_head including global js and global css */
        wp_head(); // Load Regular WP Head
        do_action('lp_head'); // Load Custom Landing Page Specific Header Items
		?>

        <!-- Our CSS stylesheet file -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" />
        <link rel="stylesheet" href="<?php echo $path; ?>assets/css/styles.css" />
        <link rel="stylesheet" href="<?php echo $path; ?>assets/countdown/jquery.countdown.css" />
 <style type="text/css">
   #content-background{ width: 550px; padding-top: 20px; padding-bottom:20px;border-radius: 6px; margin: auto; }

<?php if ($bg_image != "") { ?>

	html { background: none;}

	body {  background: url(<?php echo $bg_image; ?>) no-repeat center center fixed;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
    filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $bg_image; ?>', sizingMethod='scale');
    ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $bg_image; ?>', sizingMethod='scale')";}
<?php } ?>
	div, p, #note, label, #lp_container  { color: #<?php echo $text_color; ?>}
    .countDiv::before, .countDiv::after {
		background-color: #<?php echo $text_color; ?>;
	}

<?php if ($headline_color != "") { echo "h1 {color: #$headline_color;}"; } ?>
<?php if ($background_on === "on") { echo "#content-background{background: url('".$path."image.php?hex=$content_color');}"; }?>
 <?php if ($submit_button_color != "") {
          echo"input[type='submit'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
                border: 1px solid #000;}";
           }
        ?>
       footer #inbound-social-inbound-social-buttons {
        text-align: center;
        background: rgba(0,0,0,0);
        padding: 0;
        margin: auto;
        margin-top: 10px;}
</style>
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>

    <body <?php lp_body_class();?>>
<div id="page-wrapper">
<div id="heading-area">
    <h1><?php lp_main_headline(); ?></h1>
</div>
<div id="content-wrapper">
<div id="content-background">

        <div id="countdown"></div>
        <p id="note"></p>
<!-- Show or hide form area -->
        <div id="form-area">
        <?php echo do_shortcode($conversion_area); /* Print out form content */ ?>
        <div id="content-area">
            <?php echo do_shortcode($content);?>
        </div> <!-- end content area -->
        </div>
</div>
</div>
  <?php if ($social_display==="1" ) { // Show Social Media Icons?>
        <footer>


        <?php lp_social_media(); // print out social media buttons?>
  <style type="text/css">
  #lp-social-buttons {width: 517px;
margin: auto;
}
.sharrre .googleplus {
width: 90px !important;
}
.sharrre .pinterest {
    width: 75px !important;
}
.twitter {
    width: 111px;
}
.sharrre .button {
width: 106px;}
.linkedin {
margin-right: -14px;
}</style>
        </footer>
   <?php } ?>
        <!-- JavaScript includes -->

        <script src="<?php echo $path; ?>assets/countdown/jquery.countdown.js"></script>

        <script>
jQuery(function(){

    var note = jQuery('#note'),
    // year, month-1, date
        ts = new Date(<?php if (isset($date_array[0])) { echo $date_array[0] ; } ?>,<?php if (isset($date_array[1])) { echo $date_array[1] - 1 ; } ?>,<?php if (isset($date_array[2])) { echo $date_array[2] ; } ?><?php if ($date_array[3] != "") { echo "," . $date_array[3] ; } ?>),
        newYear = false;

    jQuery('#countdown').countdown({
        timestamp   : ts,
        callback    : function(days, hours, minutes, seconds){

            var message = "";

            message += days + " day" + ( days==1 ? '':'s' ) + ", ";
            message += hours + " hour" + ( hours==1 ? '':'s' ) + ", ";
            message += minutes + " minute" + ( minutes==1 ? '':'s' ) + " and ";
            message += seconds + " second" + ( seconds==1 ? '':'s' ) + " <br />";

            if(newYear){
                message += "left until the new year!";
            }
            else {
                message += "until <?php echo $countdown_message;?>";
            }

            note.html(message);
        }
    });

});
/*jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", Math.max(0, ((jQuery(window).height() - this.outerHeight()) / 2) +
                                                jQuery(window).scrollTop()) + "px");
    this.css("left", Math.max(0, ((jQuery(window).width() - this.outerWidth()) / 2) +
                                                jQuery(window).scrollLeft()) + "px");
    return this;
}
jQuery('#lp_container').center();*/
        </script>
<?php
break; endwhile; endif;

do_action('lp_footer');
wp_footer();
?>
</div>


    </body>
</html>