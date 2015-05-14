<?php
/*****************************************/
// Template Title:  Half and Half Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__)); // unique ID associated with this template
$path = LANDINGPAGES_URLPATH.'templates/'.$key.'/'; // path to template folder
$url = plugins_url();

/* Define Landing Pages's custom pre-load do_action('lp_init'); hook for 3rd party plugin integration */
do_action('lp_init');

/* Start WordPress Loop and Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables. These are defined in the templates config.php file */

	$content = lp_get_value($post, $key, 'main-content');
    $conversion_area = lp_get_value($post, $key, 'conversion-area-content');
    $social_display = lp_get_value($post, $key, 'display-social');
    $sidebar = lp_get_value($post, $key, 'sidebar');
    $logo = lp_get_value($post, $key, 'logo');
    $content_color = lp_get_value($post, $key, 'content-color');
    $content_text_color = lp_get_value($post, $key, 'content-text-color');
    $sidebar_color = lp_get_value($post, $key, 'sidebar-color');
    $sidebar_text_color = lp_get_value($post, $key, 'sidebar-text-color');
    $submit_button_color = lp_get_value($post, $key, 'submit-button-color');


?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>
        <?php wp_title(); // Load WordPress Page Title ?>
    </title>

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

    <script src="<?php echo $path; ?>assets/js/modernizr-2.6.2.min.js"></script>

    <style type="text/css">
        /* Inline Styling for Template Changes based off user input */
        <?php if ($sidebar_color != "") {
            echo "#right { background-color: #$sidebar_color;} "; // change sidebar color
        } ?>
        <?php if ($content_color != "") {
            echo "body {background-color: #$content_color;}"; // change content background color
        } ?>
        <?php if ($content_text_color != "") {
            echo "#area, ul {color: #$content_text_color; opacity: .9;}"; // change content text color
        } ?>
        <?php if ($sidebar_text_color != "") {
            echo "#right {color: #$sidebar_text_color;} "; // change sidebar text color
        } ?>
         <?php if ($submit_button_color != "") {
            echo "input[type='submit'] {background: #$submit_button_color;} "; // change sidebar text color
        } ?>
        <?php // change css layout if left selected
             if ($sidebar === "left" ) {
            echo "#right {left:0;
                 -webkit-box-shadow: inset -2em 0 3em rgba(172, 49, 16, .7);
                 -moz-box-shadow: inset -2em 0 3em rgba(50, 50, 50, .5);
                 box-shadow: inset -2em 0 3em rgba(50, 50, 50, .5);
                 border-right: .1em solid rgba(255, 255, 255, .8);}
                 #content {right: 0;}
                 #social-share-buttons {margin-left: -115px !important;}";
            } else {
            echo "#right {right: 0;}";
            }
        ?>
        #share-toggle #inbound-social-inbound-social-buttons {
        width: 476px;
        margin-bottom: 10px;
        margin-left: -6px;
        }
        #area_two #inbound-form-wrapper {

        max-width: 75%;
        margin: auto;
        }
        /* Import Google Font Stylesheet */
        @import url(http://fonts.googleapis.com/css?family=Open+Sans:400,300);
    </style>
    <?php wp_head(); // Load Regular WP Head ?>
    <?php do_action('lp_head'); // Load Landing Page Specific Header Items ?>
</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please
            <a href="http://browsehappy.com/">upgrade your browser</a>or
            <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a>to improve your experience.</p>
    <![endif]-->
    <div id="content">
        <div id="area">
             <h1><?php lp_main_headline(); // Load WordPress Post Title ?></h1>

            <?php echo do_shortcode($content); // Load Wordpress Content ?>
        </div><!--end #area -->
    </div><!--end #content -->

    <div id="right">
        <div id="area_two">
            <?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
        </div><!--end #area_two -->
    </div><!--end #right -->

    <?php if ($social_display==="1" ) { // Show Social Media Icons?>
    <div id="share-toggle">
        <div class="share-text"></div>
        <?php lp_social_media(); // Template helper to print out social media buttons ?>
    </div>
    <!--end #share-toggle -->
    <?php } ?>
    <style type="text/css">
        .gform_wrapper .gsection .gfield_label, .gform_wrapper h2.gsection_title, .gform_wrapper h3.gform_title, .gform_wrapper span.gform_description {
            font-weight: 100;
        }
        .share-text {
            text-align: center;
        }
        #share-toggle {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 465px;
            margin-left: -240px;
        }
    </style>
    <?php break; endwhile; endif; // end WordPress Loop
    do_action('lp_footer'); // load landing pages footer hook
    wp_footer(); // load normal wordpress footer
    ?>
</body>
</html>