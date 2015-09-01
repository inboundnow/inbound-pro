<?php
/*****************************************/
// Template Title: Three Column Landing Page
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__)); // unique ID associated with this template
$path = LANDINGPAGES_URLPATH . 'templates/' . $key . '/'; // path to template folder
$url = plugins_url();

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH . 'templates/' . $key . '/config.php');

/* Define Landing Pages's custom pre-load do_action('lp_init'); hook for 3rd party plugin integration */
do_action('lp_init');

/* Start WordPress Loop and Load $post data */
if (have_posts()) : while (have_posts()) :
the_post();

/* Pre-load meta data into variables. These are defined in the templates config.php file */
$conversion_area_placement = get_field('three-column-lander-conversion_area', $post->ID);
$left_content_bg_color = get_field('three-column-lander-left-content-bg-color', $post->ID);
$left_content_text_color = get_field('three-column-lander-left-content-text-color', $post->ID);
$left_content_area = get_field('three-column-lander-left-content-area', $post->ID);
$middle_content_bg_color = get_field('three-column-lander-middle-content-bg-color', $post->ID);
$middle_content_text_color = get_field('three-column-lander-middle-content-text-color', $post->ID);
$right_content_bg_color = get_field('three-column-lander-right-content-bg-color', $post->ID);
$right_content_text_color = get_field('three-column-lander-right-content-text-color', $post->ID);
$right_content_area = get_field('three-column-lander-right-content-area', $post->ID);
$submit_button_color = get_field('three-column-lander-submit-button-color', $post->ID);
$content = get_field('three-column-lander-main-content', $post->ID);
$conversion_area = get_field('three-column-lander-conversion-area-content', $post->ID);
$main_headline = get_field('lp-main-headline', $post->ID);

?>
<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>
        <?php wp_title(); // Load Normal WordPress Page Title
        ?>
    </title>

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <script src="<?php echo $path; ?>assets/js/modernizr-2.6.2.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.5, minimum-scale=0.5">
    <style type="text/css">
        body {
            font-family: "Open Sans", sans-serif;
        }

        #lp_container_form {
            text-align: center;
        }

        /* Inline Styling for Template Changes based off user input */
        input[type=submit], button[type=submit] {
            background: #33B96B;
            padding: 10px;
            width: 100%;
            margin: 0 auto 20px auto;
            font-size: 30px;
            color: #fff;
            border: none;
        }

        <?php
        if ($left_content_bg_color != "") {
            echo ".sidebar.left { background: $left_content_bg_color;} "; // change sidebar color
        }
        if ($left_content_text_color != "") {
            echo ".sidebar.left { color: $left_content_text_color;} "; // change sidebar color
        }
        if ($right_content_bg_color != "") {
            echo ".sidebar.right { background: $right_content_bg_color;} "; // change sidebar color
        }
        if ($right_content_text_color != "") {
            echo ".sidebar.right { color: $right_content_text_color;} "; // change sidebar color
        }
        if ($middle_content_bg_color != "") {
            echo ".main {background: $middle_content_bg_color;}"; // change content background color
        }
        if ($middle_content_text_color != "") {
            echo ".main, .btn {color: $middle_content_text_color;}"; // change content background color
        }
        if ($submit_button_color !=""){
            echo ".input[type=submit], button[type=submit] {background: $submit_button_color;}"; // change content background color
        }

        ?>
        #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
            width: 100% !important;
        }

        .main .inbound-now-form {
            width: 50%;
            margin: auto;
        }

    </style>
    <?php wp_head(); // Load Regular WP Head
    ?>
    <?php do_action('lp_head'); // Load Landing Page Specific Header Items
    ?>
</head>
<body <?php body_class(); ?>>
<!--[if lt IE 7]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please
    <a href="http://browsehappy.com/">upgrade your browser</a>or
    <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a>to improve your
    experience.</p>
<![endif]-->
<div class="wrapper">
    <div class="main">

        <a href="#" class="btn left"><span class="entypo-left-open"></span>More</a> </a>
        <a href="#" class="btn right"><span class="entypo-right-open"></span>More</a> </a>

        <h2><?php

            echo $main_headline;

            ?></h2>
        <?php echo $content; ?>
        <?php if ($conversion_area_placement === "middle") {
            echo $conversion_area;
        } ?>
    </div>

    <div class="sidebar left">
        <?php echo $left_content_area; ?>
        <?php if ($conversion_area_placement === "left") {
            echo $conversion_area;
        } ?>
    </div>

    <div class="sidebar right">
        <?php echo $right_content_area; ?>
        <?php if ($conversion_area_placement === "right") {
            echo $conversion_area;
        } ?>
    </div>

</div>
<!-- end .wrapper -->
<?php break;
endwhile;
endif; // end WordPress Loop
do_action('lp_footer'); // load landing pages footer hook
wp_footer(); // load normal wordpress footer
?>
<script type="text/javascript">
    jQuery(function ($) {

        $('.btn.left').click(function (event) {
            event.preventDefault();
            $('body').toggleClass('left');
        });

        $('.btn.right').click(function (event) {
            event.preventDefault();
            $('body').toggleClass('right');
        });

    });
</script>
</body>
</html>