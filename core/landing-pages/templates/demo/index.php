<?php
/**
* Template Name:	Demo Template
*
* @package	WordPress Landing Pages
* @author	Inbound Now
* @link http://www.inboundnow.com
* @version	1.0
*/


/* get the name of the template folder */
$key = basename(dirname(__FILE__));

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH.'templates/'.$key.'/config.php');

/* discover the absolute path of where this template is located. Core templates are loacted in /wp-content/plugins/landing-pages/templates/ while custom templates belong in /wp-content/uploads/landing-pages/tempaltes/ */
$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/';

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load Regular WordPress $post data and start the loop */
if (have_posts()) : while (have_posts()) : the_post();

/* load ACF Input values */
$wysiwyg = get_field( 'wysiwyg',$post->ID , false );
$text = get_field( 'text',$post->ID , false );
$textarea = get_field( 'textarea',$post->ID , false );
$number = get_field( 'number',$post->ID , false );
$email = get_field( 'email',$post->ID , false );
$url = get_field( 'url',$post->ID , false );
$password = get_field( 'password',$post->ID , false );
$oembed = get_field( 'oembed',$post->ID , false );
$image = get_field( 'image', $post->ID , false ); /* images need formatting set false for non ACF Pro templates */
$file = get_field( 'file', $post->ID , false ); /* file urls need formatting set false for non ACF Pro templates */
$gallery = get_field( 'gallery',$post->ID , false );
$select = get_field( 'select',$post->ID , false );
$checkbox = get_field( 'checkbox',$post->ID , false );
$radio = get_field( 'radio',$post->ID , false );
$truefalse = get_field( 'truefalse',$post->ID , false );
$googlemap = get_field( 'googlemap',$post->ID , false );
$datepicker = get_field( 'datepicker',$post->ID , false );
$colorpicker = get_field( 'colorpicker',$post->ID , false );

?>
<!DOCTYPE html>

<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>	<html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>	<html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<!--	Define page title -->
	<title><?php wp_title(); ?></title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />

	<!-- Included CSS Files -->
	<link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

	<!-- Included JS Files -->
	<script src="<?php echo $path; ?>assets/js/modernizr.js"></script>

	<!-- Inline Style Block for implementing css changes based off user settings -->
	<style type="text/css">
	<?php
	// If color changed, apply new hex color
	if ($colorpicker != "") {
	echo "table	{ border: 1px solid {$colorpicker};} ";
	}
	?>
	</style>

	<?php

	/* Load Normal WordPress wp_head() function */
	do_action('wp_head');

	/* Load Landing Pages's custom pre-load hook for 3rd party plugin integration */
	do_action('lp_head');

	?>

</head>

<!-- lp_body_class(); Defines Custom Body Classes for Advanced User CSS Customization -->
<body <?php body_class(); ?>>


<div id="content-wrapper">
	<h1>Demo Landing Page - Field Value Examples</h1>
	<table>
		<tr>
			<td class="field-label">
				Text Field Content
			</td>
			<td class="field-value">
				<?php echo $text; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				WYSIWYG Content
			</td>
			<td class="field-value">
				<?php echo $wysiwyg; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Text Area Content
			</td>
			<td class="field-value">
				<?php echo $textarea; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Number
			</td>
			<td class="field-value">
				<?php echo $number; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Email
			</td>
			<td class="field-value">
				<?php echo $email; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				URL
			</td>
			<td class="field-value">
				<?php echo $url; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Image
			</td>
			<td class="field-value">
				<?php echo $image; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				File
			</td>
			<td class="field-value">
				<?php echo $file; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Gallery
			</td>
			<td class="field-value">
				<pre><?php print_r($gallery); ?></pre>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Select
			</td>
			<td class="field-value">
				<?php echo $select; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Checkbox
			</td>
			<td class="field-value">
				<?php print_r($checkbox); ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Radio
			</td>
			<td class="field-value">
				<?php echo $radio; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				True/False
			</td>
			<td class="field-value">
				<?php echo $truefalse; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Google Map
			</td>
			<td class="field-value">
				<?php print_r($googlemap); ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Datepicker
			</td>
			<td class="field-value">
				<?php echo $datepicker; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Colorpicker
			</td>
			<td class="field-value">
				<?php echo $colorpicker; ?>
			</td>
		</tr>
		<tr>
			<td class="field-label">
				Tracked link
			</td>
			<td class="field-value">
				<?php
				$link = "<a href='https://www.inboundnow.com' class='inbound-track-link'>Inbound Now Home</a>";

				echo ''.htmlentities($link).'<br><br>';

				echo Inbound_Tracking::prepare_tracked_links( $link );
				?>
			</td>
		</tr>
	</table>


</div>


<?php
break;
endwhile; endif;
?>
<footer>
	<?php
	do_action('lp_footer');
	do_action('wp_footer');
	?>
</footer>
</body>
</html>