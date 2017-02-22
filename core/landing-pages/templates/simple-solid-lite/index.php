<?php
/**
 * Template Name: Simple-Solid
 * @package  WordPress Landing Pages
 * @author   Inbound Template Generator!
 */
/* Declare Template Key */
$key = basename(dirname(__FILE__));
$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key . '/' : LANDINGPAGES_URLPATH . 'templates/' . $key . '/';
$url = plugins_url();

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH.'templates/'.$key.'/config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');


/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) :
the_post();

$headline = get_field( 'lp-main-headline', $post->ID , false );
$headline_color = get_field( 'main-headline-color', $post->ID , false );
$content = get_field( 'simple-solid-lite-main-content', $post->ID , false );
$conversion_area = get_field( 'simple-solid-lite-conversion-area-content', $post->ID , false );
$header = get_field( 'simple-solid-lite-header-display', $post->ID , false );
$footer = get_field( 'simple-solid-lite-footer-display', $post->ID , false );
$background_style = get_field( 'simple-solid-lite-background-style', $post->ID , false );
$logo = get_field( 'simple-solid-lite-logo', $post->ID , false); /* needs to be false for acf lite users */
$old_logo = lp_get_value($post, $key, 'logo');
$logo = ($logo) ? $logo : $old_logo;
$background_image = get_field( 'simple-solid-lite-background-image', $post->ID , false); /* needs to be false for acf lite users */
$background_color = get_field( 'simple-solid-lite-background-color', $post->ID , false );
$submit_color = get_field( 'simple-solid-lite-submit-color', $post->ID , false );
$social_media_options = get_field( 'simple-solid-lite-social-media-options', $post->ID , false );
$copyright_text = get_field( 'simple-solid-lite-copyright-text', $post->ID , false );

if ($background_style === "fullscreen") {
    $bg_style = 'background: url(' . $background_image . ') no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' . $background_image . '", sizingMethod="scale");
  -ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' . $background_image . '", sizingMethod="scale")";';
} else if ($background_style === "color") {
    $bg_style = 'background: ' . $background_color . ';';
} else if ($background_style === "tile") {
    $bg_style = 'background: url(' . $background_image . ') repeat; ';
} else if ($background_style === "repeat-x") {
    $bg_style = 'background: url(' . $background_image . ') repeat-x; ';
} else if ($background_style === "repeat-y") {
    $bg_style = 'background: url(' . $background_image . ') repeat-y; ';
} else if ($background_style === "repeat-y") {
    $bg_style = 'background: url(' . $background_image . ') repeat-y; ';
}
$site_url = "http://" . $_SERVER['HTTP_HOST'];
$submit = inbound_color_scheme($submit_color, 'hex');
$test = inbound_color_scheme($background_color, 'hex');
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
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php wp_title(); ?></title>

    <link rel="stylesheet" href="<?php echo $path; ?>css/main.css" type="text/css"/>


    <!--[if IE]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <!--[if IE]>
    <script src="<?php echo $path; ?>js/selectivizr-min.js"></script><![endif]-->


    <?php
    do_action('wp_head');
    do_action('lp_head');
    ?>
    <style id="inbound-style-overrides" type="text/css">

        <?php
        $header_display = ($header != 'off') ? 'inherit' : 'none';
        $footer_display = ($footer != 'off') ? 'inherit' : 'none';
        ?>
        header {
            display: <?php echo $header_display; ?>;
        }

        footer {
            display: <?php echo $footer_display; ?>;
        }

        html, body {
        <?php echo $bg_style;?>
        }

        #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
            width: 100% !important;
        }

        .inbound-social-share-header {
            color: <?php echo $background_color;?>
        }

        input[type=submit], button[type=submit] {
            background: <?php echo $submit_color;?>;
            padding: 20px;
            width: 100%;
            margin: 0 auto 20px auto;
            font-size: 30px;
            color: #fff;
            border: none;
        }

        input[type=submit]:hover, button[type=submit]:hover {
            background: <?php echo $submit[60];?>;
        }

        footer {
            width: 100%;
            margin-bottom: 0px;
            position: fixed;
            bottom: 0px;
            background-color: <?php echo $test[60];?>;
        }

        input[type="text"]:focus, input[type="number"]:focus {
            border: 2px solid <?php echo $test[60];?>;
        }

        ::selection {
            background: <?php echo $test[65];?>;
        }

        .inbound-field label {

            text-align: left;
        }

        .form {
            width: 600px;
            margin: 0px auto 20px auto;
        }

        .logo, .logo a {
            width: 300px;
        }

        .logo a {
            margin-top: 12px;
        }

        .network.inbound_option_area {
            font-size: 23px;
        }

        .inbound-template-intro {
            text-align: left;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 37px;
            text-align: center;
            margin-top: 15px;
            color:<?php echo ($headline_color) ? $headline_color : '#fff'; ?>
        }
    </style>
</head>
<body class="lp_ext_customizer_on single-area-edit-on">
<header class="">
    <div class="inner">
        <div class="logo">
            <a href="<?php echo $site_url; ?>" class="inbound_option_area">
    <img class="not-image inbound-media inbound_option_area" src="<?php echo $logo; ?>"/>
            </a>
        </div>
        <div class="network inbound_option_area" data-eq-selector=".inner .network:eq(0)" data-count-size="1"
             data-css-selector=".inner .network" data-js-selector=".inner .network"
             data-option-name="Social Media Options" data-option-kind="text"
             inbound-option-name="Social Media Options"><?php echo $social_media_options; ?></div>
    </div>
</header>
<section class="cf container outline-element">
    <?php
        if ($headline) {
            echo '<h1>'.do_shortcode($headline).'</h1>';
        } ?>

    <div class="inbound-template-intro">
        <div class="inbound_the_content"><?php echo $content;  ?></div>
    </div>
    <div class="form inbound_the_conversion_area" data-eq-selector=".cf.container .form:eq(0)" data-count-size="1"
         data-css-selector=".cf.container .form"
         data-js-selector=".cf.container .form"><?php echo wpautop($conversion_area); ?></div>

    <div class="cf"></div>


</section>
<footer class="">
    <div class="foot-left inbound_option_area" data-eq-selector=".cf.container .foot-left:eq(0)" data-count-size="1"
         data-css-selector=".cf.container .foot-left" data-js-selector=".cf.container .foot-left"
         data-option-name="Copyright Text" data-option-kind="text"
         inbound-option-name="Copyright Text"><?php echo $copyright_text; ?></div>

    <div class="cf"></div>
</footer>
<div id="inbound-template-name" style="display:none;">Simple-Solid</div>
<?php break;
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