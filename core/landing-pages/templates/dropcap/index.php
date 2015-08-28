<?php
/*****************************************/
// Template Title:	Dropcap
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

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
// Text color: Use this setting to change the Text Color
$text_color = lp_get_value($post, $key, 'text-color');
// Content color: Use this setting to change the Content BG Color
$content_background = lp_get_value($post, $key, 'content-background');
// Form Text color: Use this setting to change the Form Text Color
$form_text_color = lp_get_value($post, $key, 'form-text-color');
// Background Settings: Use this setting to change the Text Color
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Use this setting to change the Text Color
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Use this setting to change the Text Color
$background_color = lp_get_value($post, $key, 'background-color');


if ( $background_style === "fullscreen" ) {
	$bg_style = 'background: url('.$background_image.') no-repeat center center fixed;
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
	-ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';
} else if( $background_style === "color" ) {
	$bg_style = 'background: #'.$background_color.';';

} else if( $background_style === "tile" ) {
	$bg_style = 'background: url('.$background_image.') repeat; ';
} else if( $background_style === "repeat-x" ) {
	$bg_style = 'background: url('.$background_image.') repeat-x; ';
} else if( $background_style === "repeat-y" ) {
	$bg_style = 'background: url('.$background_image.') repeat-y; ';
} else if( $background_style === "repeat-y" ) {
	$bg_style = 'background: url('.$background_image.') repeat-y; ';
}


?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title><?php wp_title(); ?></title>
<link href="<?php echo $path; ?>assets/css/style.css" rel="stylesheet">


	<?php wp_head(); // Load Regular WP Head ?>


<style type="text/css">
@font-face {font-family: Chunk;
	src: url('<?php echo $path; ?>assets/fonts/Chunkfive-webfont.eot');
	src: url('<?php echo $path; ?>assets/fonts/Chunkfive-webfont.eot?#iefix') format('embedded-opentype'),
		url('<?php echo $path; ?>assets/fonts/Chunkfive-webfont.woff') format('woff'),
		url('<?php echo $path; ?>assets/fonts/Chunkfive-webfont.ttf') format('truetype'),
		url('<?php echo $path; ?>assets/fonts/Chunkfive-webfont.svg#ChunkFiveRegular') format('svg');}

body { <?php echo $bg_style; ?> }
<?php if ($text_color != "") { ?>
#textspot p { color: #<?php echo $text_color;?>;}
<?php } ?>
<?php if ($content_background != "") { ?>
#content { background: url('<?php echo LANDINGPAGES_URLPATH; ?>images/image.php?hex=<?php echo $content_background;?>'); border-radius: 8px; }
<?php } ?>
<?php if ($form_text_color != "") { echo "#lp_container {color: #$form_text_color;}"; } ?>
p {	margin-bottom: 20px;font-weight: 100;}
#wrapper {padding-top: 70px;}
body { font-family: 'Open Sans', sans-serif;}
#textspot p {font-family: "Chunk", Sans-Serif; letter-spacing: 1px;}
ul { margin-bottom: 20px;}

#main-content-area {
	padding-left: 0px;
	width: 89%;
	margin: auto;
}
</style>
<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
</head>



<body <?php lp_body_class();?>>
<div id="wrapper">
<div id="content">
<div id="textspot">
	<p><?php lp_main_headline(); ?></p>
</div>
<div id="main-content-area">
	<?php echo do_shortcode( $content ); ?>
	<?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
</div>
</div>
</div>
<?php break; endwhile; endif; // end wordpress loop

do_action('lp_footer'); // Load Landing Page Footer Hook
wp_footer();
?>
	<link href="<?php echo $path; ?>assets/css/form.css" rel="stylesheet">
	<script type="text/javascript">
	jQuery(document).ready(function($) {
	   		$("p:empty").remove();
	 });

	</script>
</body></html>
