<?php
/*****************************************/
// Template Title: Three Column Landing Page
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Declare Template Key */
$key = basename(dirname(__FILE__)); // unique ID associated with this template
$path = LANDINGPAGES_URLPATH . 'templates/' . $key . '/'; // path to template folder
$url = plugins_url();

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH . 'templates/' . $key . '/config.php');

/* Define Landing Pages's custom pre-load do_action('lp_init'); hook for 3rd party plugin integration */
do_action('lp_init');

function enqueue_landing_page_scripts() {
	$key = basename(dirname(__FILE__));
	$urlpath = LANDINGPAGES_URLPATH . 'templates/' . $key . '/';
	
    wp_enqueue_script('jquery');
	wp_enqueue_script( 'threecol-bootstrap-js', $urlpath . 'assets/js/bootstrap.min.js', '','', true );
	wp_enqueue_script( 'threecol-modernizr-js', $urlpath . 'assets/js/modernizr-2.6.2.min.js', '','', true );
	wp_enqueue_script( 'threecol-custom-js', $urlpath . 'assets/js/custom.js', '','', true );
	
	wp_enqueue_style( 'threecol-bootstrap-css', $urlpath . 'assets/css/bootstrap.css' );
	wp_enqueue_style( 'threecol-style-css', $urlpath . 'assets/css/style.css' );
	
}
add_action('wp_head','enqueue_landing_page_scripts');

/* Start WordPress Loop and Load $post data */
if (have_posts()) : while (have_posts()) :
the_post();

/* Pre-load meta data into variables. These are defined in the templates config.php file */
$conversion_area_placement	= get_field('three-column-lander-conversions_area', $post->ID , false );
$left_content_bg_color		= get_field('three-column-lander-left-content-bg-color', $post->ID , false );
$left_content_text_color	= get_field('three-column-lander-left-content-text-color', $post->ID , false );
$left_content_area			= get_field('three-column-lander-left-content-area', $post->ID , false );
$middle_content_bg_color	= get_field('three-column-lander-middle-content-bg-color', $post->ID , false );
$middle_content_text_color	= get_field('three-column-lander-middle-content-text-color', $post->ID , false );
$right_content_bg_color		= get_field('three-column-lander-right-content-bg-color', $post->ID , false );
$right_content_text_color	= get_field('three-column-lander-right-content-text-color', $post->ID , false );
$right_content_area			= get_field('three-column-lander-right-content-area', $post->ID , false );
$submit_button_color		= get_field('three-column-lander-submit-button-color', $post->ID , false );
$content					= get_field('three-column-lander-main-content', $post->ID , false );
$conversion_area			= get_field('three-column-lander-conversion-area', $post->ID , false );
$main_headline				= get_field('lp-main-headline', $post->ID , false );

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
        <?php wp_title();  ?>
    </title>
    <?php do_action('wp_head'); ?>
    <?php do_action('lp_head');   ?>
	
	<!-- commenting styles and scripts as they have been enqueued with the standard WP way
    <link rel="stylesheet" href="<?php //echo $path; ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?php //echo $path; ?>assets/css/style.css">
    <script src="<?php //echo $path; ?>assets/js/modernizr-2.6.2.min.js"></script>
    <script src="<?php //echo $path; ?>assets/js/custom.js"></script> -->
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
		/* this has been all moved inline
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
		 * 
		 */

        ?>
        #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
            width: 100% !important;
        }

        .main .inbound-now-form {
            width: 50%;
            margin: auto;
        }
		
		#inbound-form-wrapper button {
			min-height: 60px;
			border-radius: 40px;
			line-height: 24px;
			padding: 16px 30px 20px;
			border: none;
			//color: <?php echo $form_button_text_color; ?>;
			background-color: <?php echo $submit_button_color; ?>;
		}
		
		.left-area #inbound-form-wrapper label, 
		.left-area #inbound-form-wrapper .radio-inbound-label-top,
		.left-area #inbound-form-wrapper .label-inbound-label-top {
			color: <?php echo $left_content_text_color; ?>;
		}
		
		.center-area #inbound-form-wrapper label, 
		.center-area #inbound-form-wrapper .radio-inbound-label-top,
		.center-area #inbound-form-wrapper .label-inbound-label-top {
			color: <?php echo $middle_content_text_color; ?>;
		}
		
		.right-area #inbound-form-wrapper label, 
		.right-area #inbound-form-wrapper .radio-inbound-label-top,
		.right-area #inbound-form-wrapper .label-inbound-label-top {
			color: <?php echo $right_content_text_color; ?>;
		}
		
		#inbound-form-wrapper input, #inbound-form-wrapper textarea, #inbound-form-wrapper select {
			//background-color: <?php //echo $form_fields_bg_color; ?>;
		}
		
		#inbound-form-wrapper textarea:placeholder {
			color: #a9a9a9 !important;
		}

    </style>
</head>
<body <?php body_class(); ?>>
<!--[if lt IE 7]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please
    <a href="http://browsehappy.com/">upgrade your browser</a>or
    <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a>to improve your
    experience.</p>
<![endif]-->
<div class="wrapper container-fluid">
	<div class="row" style="height:100%;">
				
		<div class="col-md-3 col-sm-3 hidden-xs sidebar-3col left-area" style="background-color:<?php echo $left_content_bg_color; ?>; color:<?php echo $left_content_text_color; ?>; height: 100%;">
			<?php echo $left_content_area; ?>
			<?php if ($conversion_area_placement === "left") {
				echo $conversion_area;
			} ?>
		</div>
		
		<div class="col-md-6 col-sm-6 hidden-xs main-3col center-area" style="background-color:<?php echo $middle_content_bg_color; ?>; color:<?php echo $middle_content_text_color; ?>; height: 100%;">
			
			<!--
			<a href="#" class="btn left"><span class="entypo-left-open"></span>More</a>
			<a href="#" class="btn right"><span class="entypo-right-open"></span>More</a> -->

			<h2><?php

				echo $main_headline;

				?></h2>
			<?php echo $content; ?>
			<?php if ($conversion_area_placement === "middle") {
				echo $conversion_area;
			} ?>
		</div>

		<div class="col-md-3 col-sm-3 hidden-xs sidebar-3col right-area" style="background-color:<?php echo $right_content_bg_color; ?>; color:<?php echo $right_content_text_color; ?>; height: 100%;">
			<?php echo $right_content_area; ?>
			<?php if ($conversion_area_placement === "right") {
				echo $conversion_area;
			} ?>
		</div>
		
		<!-- START OF MOBILE SECTION. These column are visible only on screens <768px -->
		
		<div class="col-xs-12 hidden-sm hidden-md hidden-lg main-3col center-area" style="background-color:<?php echo $middle_content_bg_color; ?>; color:<?php echo $middle_content_text_color; ?>;">
		
			<h2><?php

				echo $main_headline;

				?></h2>
			<?php echo $content; ?>
			<?php if ($conversion_area_placement === "middle") {
				echo $conversion_area;
			} ?>
		</div>
		
		<div class="col-xs-12 hidden-sm hidden-md hidden-lg sidebar-3col left-area" style="background-color:<?php echo $left_content_bg_color; ?>; color:<?php echo $left_content_text_color; ?>;">
			<?php echo $left_content_area; ?>
			<?php if ($conversion_area_placement === "left") {
				echo $conversion_area;
			} ?>
		</div>

		<div class="col-xs-12 hidden-sm hidden-md hidden-lg sidebar-3col right-area" style="background-color:<?php echo $right_content_bg_color; ?>; color:<?php echo $right_content_text_color; ?>;">
			<?php echo $right_content_area; ?>
			<?php if ($conversion_area_placement === "right") {
				echo $conversion_area;
			} ?>
		</div>
		
		<!-- END OF MOBILE SECTION -->
		
	</div>

</div>
<!-- end .wrapper -->
<?php break;
endwhile;
endif; // end WordPress Loop

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
<footer>
	<?php
	do_action('lp_footer');
	do_action('wp_footer');
	?>
</footer>
</body>
</html>