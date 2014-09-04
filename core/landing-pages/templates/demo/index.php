<?php
/**
* Template Name:	Demo Template
*
* @package	WordPress Landing Pages
* @author	David Wells
* @link http://www.inboundnow.com
* @version	1.0
*/

/* Step 1: Declare Template Key. This will be automatically detected for you */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = (preg_match("/uploads/", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . $key .'/' : LANDINGPAGES_URLPATH.'templates/'.$key.'/'; // This defines the path to your template folder. /wp-content/uploads/landing-pages/templates by default

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load Regular WordPress $post data and start the loop */
if (have_posts()) : while (have_posts()) : the_post();

/**
 * Step 2: Pre-load meta data into variables.
 * - These are defined in this templates config.php file
 * - The config.php values create the metaboxes visible to the user.
 * - We define those meta-keys here to use them in the template.
 */

// Text Field Label: Text field Description. Defined in config.php on line 44
$text_box_id = lp_get_value($post, $key, 'text-box-id');
// Textarea Label: Text field Description. Defined in config.php on line 50
$textarea_id = lp_get_value($post, $key, 'textarea-id');
// Template body color: Text field Description. Defined in config.php on line 56
$color_picker_id = lp_get_value($post, $key, 'color-picker-id');
// Radio Label: Text field Description. Defined in config.php on line 62
$radio_id_here = lp_get_value($post, $key, 'radio-id-here');
// Example Checkbox Label: Text field Description. Defined in config.php on line 70
$checkbox_id_here = lp_get_value($post, $key, 'checkbox-id-here');
// Dropdown Label: Text field Description. Defined in config.php on line 78
$dropdown_id_here = lp_get_value($post, $key, 'dropdown-id-here');
// Date Picker Label: Text field Description. Defined in config.php on line 85
$date_picker = lp_get_value($post, $key, 'date-picker');
// Main Content Box 2: Text field Description. Defined in config.php on line 91
$wysiwyg_id = lp_get_value($post, $key, 'wysiwyg-id');
// File/Image Upload Label: Text field Description. Defined in config.php on line 97
$media_id = lp_get_value($post, $key, 'media-id');
// The main content if you want to show default placeholders. 
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');


// alternatively you can use default wordpress get_post_meta.
// You will need to add your template $key to the meta id. Example "text-box-id" becomes "demo-text-box-id"
// example: $text_box_id = get_post_meta($post->ID, 'demo-text-box-id', true);
?>
<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
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
	if ($color_picker_id != "") {
	echo "body	{ background-color: #$color_picker_id;} ";
	}
	?>
	</style>

<!-- Load Normal WordPress wp_head() function -->
<?php wp_head(); ?>
<!-- Load Landing Pages's custom pre-load hook for 3rd party plugin integration -->
<?php do_action('lp_head'); ?>

</head>

<!-- lp_body_class(); Defines Custom Body Classes for Advanced User CSS Customization -->
<body <?php body_class(); ?>>

<div id="wrapper">

<!-- example of conditional statment -->
<?php if ( $checkbox_id_here === "on" ) {
		// do something for Example Checkbox Label option
		}
?>

<div id="content-wrapper">
	<div id="content">
	<!-- Use the_title(); to print out the main headline -->
	<h1><?php the_title(); ?></h1>

	<?php echo do_shortcode( $content ); ?>

		<div id="demo-hide">
		<!-- Echoed out values from the metaboxes from config.php -->
		<?php echo "Here is the Text Box content:" . $text_box_id . "<br>";
			echo "Here is the Textarea content:" . $textarea_id . "<br>";
			echo "Here is the Color Picker Hex:" . $color_picker_id . "<br>";
			echo "Here is the Radio Value:" . $radio_id_here . "<br>";
			echo "Here is the Checkbox Value:" . $checkbox_id_here . "<br>";
			echo "Here is the Dropdown Value:" . $dropdown_id_here . "<br>";
			echo "Here is the Date Picker Value:" . $date_picker . "<br>";
			echo "Here is the WYSIWYG editor content:" . $wysiwyg_id . "<br>";
			echo "Here is the Media upload path:" . $media_id; ?>

		</div>
	</div>


	<div id="sidebar">
		<br><br>
		<div id="form-area">
			<b>Conversion Area Content</b>
			<?php echo do_shortcode( $conversion_area );	// Print out conversion area metabox content ?>

		</div>

	</div>


</div>


<?php
break;
endwhile; endif;
do_action('lp_footer'); // Load custom landing footer hook for 3rd party extensions
wp_footer(); // Load normal wordpress footer
?>
</body>
</html>