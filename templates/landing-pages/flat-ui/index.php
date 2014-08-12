<?php
/**
* Template Name: flat-ui
* @package  WordPress Landing Pages
* @author   Inbound Template Generator!
*/
/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');


/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

$header_image = lp_get_value($post, $key, 'header-image' );
$subheadline = lp_get_value($post, $key, 'subheadline' );
$main_content = lp_get_value($post, $key, 'main-content' );
$middle_left_content = lp_get_value($post, $key, 'middle-left-content' );
$middle_right_content = lp_get_value($post, $key, 'middle-right-content' );
$bottom_left_content = lp_get_value($post, $key, 'bottom-left-content' );
$bottom_right_content = lp_get_value($post, $key, 'bottom-right-content' );
$background_style = lp_get_value($post, $key, 'background-style' );
$background_image = lp_get_value($post, $key, 'background-image' );
$background_color = lp_get_value($post, $key, 'background-color' );

if ( $background_style === "fullscreen" ) {
  $bg_style = 'background: url('.$background_image.') no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
  -ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';

} else if ( $background_style === "color" ) {
  $bg_style = 'background: #'.$background_color.';';
} else if ( $background_style === "tile" ) {
  $bg_style = 'background: url('.$background_image.') repeat; ';
} else if ( $background_style === "repeat-x" ) {
  $bg_style = 'background: url('.$background_image.') repeat-x; ';
} else if ( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
} else if( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
}

 ?>

<!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />

<title><?php wp_title(); ?></title>
  <meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; width=device-width;" />
  <link rel="stylesheet" href="<?php echo $path; ?>css/main.css" />
  <link rel="stylesheet" href="<?php echo $path; ?>css/screen.css" />
  <link href="<?php echo $path; ?>css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?php echo $path; ?>css/fonts.css" />
<style>

</style>
<style type="text/css" title="inbound-special-styles" id="inbound-special-styles">
.blank.blurb h2 {text-transform:none;}
.blank.blurb .button.button-cta {text-transform:none;}
</style><?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php
?>
body {<?php echo $bg_style;?>}
html {
margin-top: 0px !important;
}
body.admin-bar {
  padding-top: 12px !important;
}
input[type="text"], input[type="password"], textarea {
background: #f6f6f6;
border: 0;
font-family: "Open Sans", helvetica, arial, sans-serif;
padding: 1em 1.3em;
margin: 0;
margin-left: -14px;
font-weight: 100;
font-size: 1em;
}
input, textarea, keygen, select, button, isindex {

}

#inbound-form-wrapper .button, #inbound-form-wrapper input[type="button"], #inbound-form-wrapper button {
  padding: 0.3em 0.5em;
  font-size: .8em;
}
.inbound-now-form .inbound-field label {
  font-size: 18px;

margin: 0;
margin-bottom: 0.2em;
}
</style>
</head>
<body <?php body_class(); ?>>



<section class="blank blurb" data-eq-selector="body section:eq(3)" data-count-size="16" data-css-selector="body section" data-js-selector="body section:eq(3)">
		<img src="<?php echo $header_image?>" alt="Image" class="outline-element" data-eq-selector=".blank.blurb img:eq(0)" data-count-size="1" data-css-selector=".blank.blurb img" data-js-selector=".blank.blurb img" data-old="" onError="this.onerror=null;this.src='<?php echo $header_image; ?>';" />
		<div class="">
			<h1 class="inbound_the_title" data-eq-selector=".blank.blurb h1:eq(0)" data-count-size="1" data-css-selector=".blank.blurb h1" data-js-selector=".blank.blurb h1">
<?php lp_main_headline(); // Main Headline ?>
</h1>
			<h2 class="" data-eq-selector=".blank.blurb h2:eq(0)" data-count-size="1" data-css-selector=".blank.blurb h2" data-js-selector=".blank.blurb h2" data-old="The subtitle for the main text blurb"><?php echo do_shortcode($subheadline); ?></h2>
			<hr class="" />
		  <?php echo  wpautop($main_content);?>
		</div>
	</section>
<section class="blank" data-eq-selector="body section:eq(7)" data-count-size="16" data-css-selector="body section" data-js-selector="body section:eq(7)">
		<div class="item-featured" data-eq-selector=".blank .item-featured:eq(0)" data-count-size="1" data-css-selector=".blank .item-featured" data-js-selector=".blank .item-featured">

      <div class="middle-left">
        <?php echo $middle_left_content;?>
       </div>
			<div class="item-info middle-right" data-eq-selector=".item-featured .item-info:eq(0)" data-count-size="1" data-css-selector=".item-featured .item-info" data-js-selector=".item-featured .item-info">
				<?php echo $middle_right_content;?>
			</div>
			<br class="clear" />
		</div>
	</section>
<section class="blank" data-eq-selector="body section:eq(9)" data-count-size="16" data-css-selector="body section" data-js-selector="body section:eq(9)">

	</section>


<div class="container blank" data-eq-selector="body .container.blank:eq(2)" data-count-size="3" data-css-selector="body .container.blank" data-js-selector="body .container.blank:eq(2)">
		<article class="" data-selector-on="true" data-eq-selector=".container.blank article:eq(0)" data-count-size="2" data-css-selector=".container.blank article" data-js-selector=".container.blank article:eq(0)">
			<?php echo $bottom_left_content;?>
		</article>
<article class="" data-eq-selector=".container.blank article:eq(1)" data-count-size="2" data-css-selector=".container.blank article" data-js-selector=".container.blank article:eq(1)">
			<?php echo $bottom_right_content;?>
		</article>
		<div class="clear"></div>
	</div>
<div id="inbound-template-name" style="display:none;">flat-ui</div>

<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
</body></html>