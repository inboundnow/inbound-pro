<?php
/*****************************************/
// Template Title: RSVP Envelope Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/';

$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

	/* Pre-load meta data into variables */
	$body_color = lp_get_value($post, $key, 'body-color');
	$text_color = lp_get_value($post, $key, 'text-color');
	$headline_color = lp_get_value($post, $key, 'headline-color');
	$form_text_color = lp_get_value($post, $key, 'form-text-color');
	$social_display = lp_get_value($post, $key, 'display-social');
	$sidebar = lp_get_value($post, $key, 'sidebar');
	$sub_headline = lp_get_value($post, $key, 'sub-headline');
	$media_example = lp_get_value($post, $key, 'media-example');
	$bg_color = lp_get_value($post, $key, 'main-bg-color');
	//prepare content
	$content = lp_content_area($post,null,true);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <title><?php wp_title(); ?></title>
        <?php // wp_enqueue_script( 'jquery');?>
        <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css" type="text/css" media="screen">
         <style>
            @font-face {
                font-family:'YanoneKaffeesatzRegular';
                src: url('<?php echo $path; ?>assets/fonts/yanonekaffeesatz-regular-webfont.eot');
                src: url('<?php echo $path; ?>assets/fonts/yanonekaffeesatz-regular-webfont.eot?#iefix') format('embedded-opentype'), url('<?php echo $path; ?>assets/fonts/yanonekaffeesatz-regular-webfont.woff') format('woff'), url('<?php echo $path; ?>assets/fonts/yanonekaffeesatz-regular-webfont.ttf') format('truetype'), url('<?php echo $path; ?>assets/fonts/yanonekaffeesatz-regular-webfont.svg#YanoneKaffeesatzRegular') format('svg');
                font-weight: normal;
                font-style: normal;
            }
            body {
                background: #ccc url('<?php echo $path; ?>assets/images/bg_out.png');
            }
            #form_wrap:before {
                background:url('<?php echo $path; ?>assets/images/before.png');
            }
            #form_wrap:after {
                background:url('<?php echo $path; ?>assets/images/after.png');
            }
            form {
                background:#f7f2ec url('<?php echo $path; ?>assets/images/letter_bg.png');
            }
            <?php if ($sidebar==="left") {
                echo"#main-content {float: right;}#wrap {float: left;}";
            }
            ?> <?php if ($body_color !="") {
                echo"body {background-color: #$body_color;} "; // change main background color
            }
            ?> <?php if ($text_color !="") {
                echo"body {color: #$text_color;}"; // change text color
            }
            ?> <?php if ($headline_color !="") {
                echo"h1 {color: #$headline_color;}"; // change headline color
            }
            ?> <?php if ($form_text_color !="") {
                echo"form {color: #$form_text_color;}"; // change form color
            }
            ?>
        </style>
        <?php wp_head(); // Load Regular WP Head
        	do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>

        <!--[if IE]>
            <script>
                $(document).ready(function () {

                    $("#form_wrap").addClass('hide');

                })
            </script>
        <![endif]-->

    </head>

    <body>
        <div id="body-container">
            <?php // echo dirname(__FILE__);?>
		<h1><?php lp_main_headline(); ?></h1>
            <div id="main-content">
                <?php echo $content; ?>
            </div>
            <div id="wrap">
                <div id="form_wrap">
                    <?php lp_conversion_area(); /* Print out form content */ ?>
                </div>
            </div>
            <?php if ($social_display==="1" ) { // Show Social Media Icons?>
            <?php lp_social_media(); // print out social media buttons?>
            <?php } ?>
        </div>
        <script type="text/javascript">
            // move form header onto form
            jQuery(document).ready(function ($) {
                jQuery('#form-header').css("text-align", "center").prependTo('form');
                jQuery("input[type='text']:first").css("margin-top", "20px");
                jQuery("input[type='submit']:first").css("margin-top", "10px");
            });
        </script>
        <?php break; endwhile; endif;
        do_action('lp_footer');
        wp_footer(); ?>
    </body>

</html>