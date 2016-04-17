<?php
/*
 * Template Name: Svtle Template
 * @package  WordPress Landing Pages
 * @author   Inbound Now
 * @version  1.0.1
 * @since    1.0.0
 */

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH . 'assets/libraries/shareme/library.shareme.php');

/* Declare Template Key */
$key = basename(dirname(__FILE__));
$path = LANDINGPAGES_URLPATH . 'templates/' . $key . '/';
$url = plugins_url();


/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH . 'templates/' . $key . '/config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) :
the_post();

/* Pre-load meta data into variables */
$main_headline = get_field('lp-main-headline', $post->ID , false );
$sidebar_color = get_field('svtle-sidebar-color', $post->ID , false );
$sidebar_text_color = get_field('svtle-sidebar-text-color', $post->ID , false );
$header_color = get_field('svtle-header-color', $post->ID , false );
$body_color = get_field('svtle-body-color', $post->ID , false );
$text_color = get_field('svtle-page_text-color', $post->ID , false );
$headline_color = get_field('svtle-headline-color', $post->ID , false );
$logo = get_field('svtle-logo', $post->ID, false);
$sidebar = get_field('svtle-sidebar', $post->ID , false );
$social_display = get_field('svtle-display-social', $post->ID , false );
$mobile_form = get_field('svtle-mobile-form', $post->ID , false );
$submit_button_color = get_field('svtle-submit-button-color', $post->ID , false );
$content = get_field('svtle-main-content', $post->ID , false );
$conversion_area = get_field('svtle-conversion-area-content', $post->ID , false );

// Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
    $hex = preg_replace("/#/", "", $hex);
    $color = array();

    if (strlen($hex) == 3) {
        $color['r'] = hexdec(substr($hex, 0, 1) . $r);
        $color['g'] = hexdec(substr($hex, 1, 1) . $g);
        $color['b'] = hexdec(substr($hex, 2, 1) . $b);
    } else if (strlen($hex) == 6) {
        $color['r'] = hexdec(substr($hex, 0, 2));
        $color['g'] = hexdec(substr($hex, 2, 2));
        $color['b'] = hexdec(substr($hex, 4, 2));
    }

    return $color;

}

$RBG_array = Hex_2_RGB($submit_button_color);
$red = (isset($RBG_array['r'])) ? $RBG_array['r'] : '0';
$green = (isset($RBG_array['g'])) ? $RBG_array['g'] : '0';
$blue = (isset($RBG_array['b'])) ? $RBG_array['b'] : '0';

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US"
      class="no-js wf-proximanova-i4-active wf-proximanova-n7-active wf-proximanova-n4-active wf-freightsanspro-n7-active wf-freightsanspro-n4-active wf-active">
<!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css"
          type="text/css" media="screen">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.5, minimum-scale=0.5">
    <title><?php wp_title(); ?></title>

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <style type="text/css">
        <?php if ($mobile_form==="on") {
            echo "@media (max-width: 630px) { #form-area {display:none;}}"; // css rule for mobile devices
        }
        ?>
        <?php if ($sidebar_color !="") {
                   echo "body, header#sidebar {background-color: $sidebar_color;}"; // change sidebar color
                  echo "@media (max-width: 900px) { body { background-color: $sidebar_color;} }";
               }
               ?>
        <?php if ($header_color !="") {
                   echo "header#begin {background: $header_color;}"; // change header color
               }
               ?>
        <?php if ($body_color !="") {
                   echo "article.post {background-color: $body_color;} section#river {background-color: $body_color;}"; // Change Body BG color
               }
               ?>
        <?php if ($sidebar_text_color !="") {
            echo "header#sidebar {color: $sidebar_text_color ;}
            input[type=\"text\"], input[type=\"email\"], button[type='submit'] {
                border: 1px solid $sidebar_text_color;
            border: 1px solid $sidebar_text_color;
            opacity: 0.8;}"; // Change Body BG color
        }
        ?>
        <?php if ($text_color !="") {
            echo "p {color: $text_color;} html, button, input, select, textarea {color: $text_color;}
            ";
        } ?>
        <?php if ($submit_button_color != "") {
          echo"input[type='submit'], button[type='submit'] {
               background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
               background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
               background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
                border: 1px solid #000;}";
           }
        ?>

    </style>
    <?php
    do_action('wp_head'); // Load Regular WP Head
    do_action('lp_head'); // Load Custom Landing Page Specific Header Items
    ?>
    <script type="text/javascript" src="<?php echo $path; ?>assets/js/modernizr.js"></script>
    <script type="text/javascript" src="<?php echo $path; ?>assets/js/jquery-picture-min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(function () {
                $('figure, picture').picture();
            });
            var window_size = jQuery(document).height();
            jQuery("#river").height(window_size);
        });
    </script>
    <?php if ($sidebar === "right") {
        echo "<link rel='stylesheet' href='" . $path . "assets/css/flipped-layout.css' type='text/css' media='screen'>";
    } ?>
    <style type="text/css">
        #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password], .inbound-field input[type=text], .inbound-field input[type=url], .inbound-field input[type=email], .inbound-field input[type=tel], .inbound-field input[type=number], .inbound-field input[type=password] {
            width: 90%;
            color: #000;
        }

    </style>

    </style>
    </head>
    <body class="home blog">
    <header id="sidebar">
    <aside id="logo" class="clearfix">
    <figure data-media="<?php echo $logo; ?>" data-media440="<?php echo $logo; ?>"
    data-media600="<?php echo $logo; ?>" title="<?php echo $main_headline; ?>">
    <img src="<?php echo $logo; ?>" alt="<?php echo $main_headline; ?>">
    </figure>
      </aside>
        <aside id="form-area">
    <?php echo $conversion_area; ?>
    </aside>
      </header>
        <section id="river" role="main">
    <?php if ($social_display === "1") { // Show Social Media Icons?>
    <header id="begin">
    <?php lp_social_media(); // print out social media buttons?>
    </header>
      <?php } else { ?>
      <style type="text/css">
    article.post {
        padding-top: 0px;
    }
    </style>
    <?php } ?>
    <article id="main-landing-content" class="post">
        <h1 class="entry-title"><?php echo $main_headline; ?></h1>

        <div class="entry-content">
            <?php
            echo $content;
            ?>
        </div>
    </article>
    </section>
    <div>
        <!--[if IE]>
        <style type="text/css">
            .widget {
                background-color: black
            }
        </style>
        <![endif]-->
    </div>
    <?php if ($mobile_form === "on") { // Show form below content on mobile view ?>
    <script type="text/javascript">
        // move form to bottom of content on mobile
        jQuery(document).ready(function ($) {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
                jQuery("#form-area").addClass("form-move").appendTo("article").show();
            }
            var delay = (function () {
                var timer = 0;
                return function (callback, ms) {
                    clearTimeout(timer);
                    timer = setTimeout(callback, ms);
                };
            })();
            jQuery(function () {
                var pause = 100; // will only process code within delay(function() { ... }) every 100ms.
                jQuery(window).resize(function () {
                    delay(function () {
                        var width = jQuery(window).width();
                        // If on desktop and window is less than 630px show the form at bottom
                        if (width < 630) {
                            jQuery("#form-area").addClass("form-move").appendTo("article").show();
                        } else {
                            // Put the form back up top
                            jQuery("#form-area").removeClass("form-move").appendTo("#form-area").show();
                        }
                    }, pause);
                });
                jQuery(window).resize();
            });
        });
    </script>
    <?php } // end mobile form conditional
    break;
    endwhile;
    endif;
    ?>
    <footer>
        <?php
        do_action('lp_footer');
        do_action('wp_footer');
        ?>
    </footer>
    </body>
</html>