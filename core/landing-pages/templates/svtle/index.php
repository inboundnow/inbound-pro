<?php
/*
 * Template Name: Svtle Template
 * @package  WordPress Landing Pages
 * @author   David Wells
 * @version  1.0
 * @since    1.0
 */

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

    $sidebar_color = lp_get_value($post, $key, 'sidebar-color');
    $sidebar_text_color = lp_get_value($post, $key, 'sidebar-text-color');
    $header_color = lp_get_value($post, $key, 'header-color');
    $body_color = lp_get_value($post, $key, 'body-color');
    $text_color = lp_get_value($post, $key, 'page-text-color');
    $headline_color = lp_get_value($post, $key, 'headline-color');
    $logo = lp_get_value($post, $key, 'logo');
    $sidebar = lp_get_value($post, $key, 'sidebar');
    $social_display = lp_get_value($post, $key, 'display-social');
    $bg_image = lp_get_value($post, $key, 'bg-image');
    $mobile_form = lp_get_value($post, $key, 'mobile-form');
    $submit_button_color = lp_get_value($post, $key, 'submit-button-color');
    $content = lp_get_value($post, $key, 'main-content');
    $conversion_area = lp_get_value($post, $key, 'conversion-area-content');

    // Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
        $hex = preg_replace("/#/", "", $hex);
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
$RBG_array = Hex_2_RGB($submit_button_color);
$red = (isset($RBG_array['r'])) ? $RBG_array['r'] : '0';
$green = (isset($RBG_array['g'])) ? $RBG_array['g'] : '0';
$blue = (isset($RBG_array['b'])) ? $RBG_array['b'] : '0';

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US" class="no-js wf-proximanova-i4-active wf-proximanova-n7-active wf-proximanova-n4-active wf-freightsanspro-n7-active wf-freightsanspro-n4-active wf-active">
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
            echo "@media (max-width: 630px) { #lp_container {display:none;}}"; // css rule for mobile devices
        }
        ?> <?php if ($sidebar_color !="") {
            echo "body, header#sidebar {background-color: #$sidebar_color;}"; // change sidebar color
           echo "@media (max-width: 900px) { body { background-color: #$sidebar_color;} }";
        }
        ?> <?php if ($header_color !="") {
            echo "header#begin {background: #$header_color;}"; // change header color
        }
        ?> <?php if ($body_color !="") {
            echo "article.post {background-color: #$body_color;} section#river {background-color: #$body_color;}"; // Change Body BG color
        }
        ?>
        <?php if ($sidebar_text_color !="") {
            echo "header#sidebar {color: #$sidebar_text_color ;}
            input[type=\"text\"], input[type=\"email\"], button[type='submit'] {
                border: 1px solid #$sidebar_text_color;
            border: 1px solid #$sidebar_text_color;
            opacity: 0.8;}"; // Change Body BG color
        }
        ?>
        <?php if ($text_color !="") {
            echo "p {color: #$text_color;} html, button, input, select, textarea {color: #$text_color;}
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

            #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
            width: 90%;
            color:#000;
            }
        }

    </style>

      <?php wp_head(); // Load Regular WP Head
            do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
    <script type="text/javascript" src="<?php echo $path; ?>assets/js/modernizr.js"></script>
    <script type="text/javascript" src="<?php echo $path; ?>assets/js/jquery-picture-min.js"></script>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(function () {
                $('figure, picture').picture();
            });
            var window_size = jQuery(document).height();
            jQuery("#river").height(window_size);
        });
    </script>
    <?php if ($sidebar === "right" ) { echo
    "<link rel='stylesheet' href='". $path . "assets/css/flipped-layout.css' type='text/css' media='screen'>"; } ?>
</head>
<body class="home blog">
    <header id="sidebar">
        <aside id="logo" class="clearfix">
            <figure data-media="<?php echo $logo; ?>" data-media440="<?php echo $logo; ?>"
            data-media600="<?php echo $logo; ?>" title="<?php lp_main_headline(); ?>">
                <img src="<?php echo $logo; ?>" alt="<?php lp_main_headline(); ?>">
            </figure>
        </aside>
        <aside id="form-area">
            <?php echo do_shortcode( $conversion_area ); ?>
        </aside>
    </header>
    <section id="river" role="main">
        <?php if ($social_display==="1" ) { // Show Social Media Icons?>
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
             <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="entry-content">
                <?php echo do_shortcode( wpautop($content) ); ?>
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
    <?php if ($mobile_form==="on" ) { // Show form below content on mobile view ?>
        <script type="text/javascript">
            // move form to bottom of content on mobile
            jQuery(document).ready(function ($) {
                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
                    jQuery("#lp_container").addClass("form-move").appendTo("article").show();
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
                                jQuery("#lp_container").addClass("form-move").appendTo("article").show();
                            } else {
                                // Put the form back up top
                                jQuery("#lp_container").removeClass("form-move").appendTo("#form-area").show();
                            }
                        }, pause);
                    });
                    jQuery(window).resize();
                });
            });
        </script>
    <?php } // end mobile form conditional
    break; endwhile; endif;
    do_action('lp_footer'); // load landing pages footer hook
    wp_footer(); // load normal wordpress footer ?>
</body>

</html>