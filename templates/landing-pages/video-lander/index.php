<?php
/*
 * Template Name:  Youtube Lander Template
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

// Background Settings: Decide how you want the bodies background to be
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Decide how you want the bodies background to be
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Decide how you want the bodies background to be
$background_color = lp_get_value($post, $key, 'background-color');
// Submit Button Color: Decide how you want the bodies background to be
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

$text_color = lp_get_value($post, $key, 'text-color');
$form_text_color = lp_get_value($post, $key, 'form-text-color');

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

// Scan content for video iframe and adjust template accordingly
$editor_contents = get_the_content();
if (strstr($editor_contents,'<iframe'))
	{
if (preg_match("/(?<=width\=\")(.*?)(?=\")/s", $editor_contents)) {
	 preg_match("/(?<=width\=\")(.*?)(?=\")/s", $editor_contents, $matches);
	 $youtube_width = $matches[0];
	} else {
	$youtube_width = "560";
}
} else {
	$youtube_width = "560";
}
?>

<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">

	<title><?php wp_title(); ?></title>
	<?php wp_head(); // Load Regular WP Head ?>
	 <!-- Included CSS Files -->
  <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

<style type="text/css">
body { <?php echo $bg_style; ?> }
<?php if ($text_color != "") { ?>
#heading-wrap { color: #<?php echo $text_color;?>;}
<?php } ?>
<?php if ($form_text_color != "") { echo "#form-wrap {color: #$form_text_color;}"; } ?>
#page-wrap {width:<?php echo $youtube_width;?>px;}

#form-wrap .gform_wrapper .top_label input.medium, #form-wrap .gform_wrapper .top_label select.medium {
width: 100%;
height: 30px;
font-size: 20px;
color: #5E5D5D;
padding-left: 5px;
}
#form-wrap .gform_wrapper .gform_footer {
padding: 0px 0 10px 0; }
</style>
<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
</head>

<body>
<div id="body-wrapper">
<div id="heading-wrap">
<h1><?php the_title();?></h1>
</div><!-- end heading wrap -->
    <div id="page-wrap">
          <?php echo do_shortcode( $content );?>
    </div><!-- end #page-wrape -->
	  <div id="form-wrap">

	  	<?php  echo do_shortcode( $conversion_area ); ?>

	  </div>
</div><!-- end #body-wrapper -->

<?php
break;
endwhile; endif;
do_action('lp_footer'); // Load custom landing footer hook for 3rd party extensions
wp_footer(); // Load normal wordpress footer
?>
</body></html>