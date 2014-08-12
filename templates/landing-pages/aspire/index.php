<?php
/**
* Template Name: aspire
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

$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$logo_image = lp_get_value($post, $key, 'logo-image' );
$top_right_link = lp_get_value($post, $key, 'top-right-link' );
$sub_headline = lp_get_value($post, $key, 'sub-headline' );
$middle_left_content = lp_get_value($post, $key, 'middle-left-content' );
$middle_right_content = lp_get_value($post, $key, 'middle-right-content' );
$full_width_bottom_content_1 = lp_get_value($post, $key, 'full-width-bottom-content-1' );
$full_width_bottom_content_2 = lp_get_value($post, $key, 'full-width-bottom-content-2' );
$full_width_bottom_content_3 = lp_get_value($post, $key, 'full-width-bottom-content-3' );
$copyright_text = lp_get_value($post, $key, 'copyright-text' );
$submit_bg_color = lp_get_value($post, $key, 'submit-bg-color' );
$submit = inbound_color_scheme($submit_bg_color, 'hex' );
$submit_text_color = lp_get_value($post, $key, 'submit-text-color' );
$scheme = lp_get_value($post, $key, 'scheme' );

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
    <!--[if gt IE 8]><!--> <html class="no-js"><!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php wp_title(); ?></title>
<link href="<?php echo $path; ?>css/bootstrap.css" rel="stylesheet" />
<link href="<?php echo $path; ?>css/main.css" rel="stylesheet" />
    <link href="<?php echo $path; ?>css/font-awesome.min.css" rel="stylesheet" />
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  <style type="text/css">
  .admin-bar .navbar-fixed-top {
	top: 33px;
	}
	#green {
	background-color: #74cfae;
	padding-top: 25px;}
	img {
		max-width: 100%;
	}
	.inbound-bottom-area h1, .inbound-bottom-area h2, .inbound-bottom-area h3 {
		text-align: center;
	}
	#area_2 {

		margin-top: 50px;
	}
	#social {
	width: 100%;
	padding-top: 50px;
	padding-bottom: 50px;
	}
	#inbound-bottom-row-last {
		margin: 15px auto;
		padding: 20px;
		width: 100%;
		position: relative;
		padding-bottom: 52.25%;
		padding-top: 30px;
		height: 0;
		color: #333;
		overflow: hidden;}
		#inbound-bottom-row-last iframe, #inbound-bottom-row-last object, #inbound-bottom-row-last embed {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		padding: 2%;
		}
	.col-lg-6 {

	font-size: 20px;
	font-weight: 200;
	line-height: 1.4;
	}
	#hidden-wrapper {
	    width: 420px;
	    	    margin: auto;
	    margin-top: 30px;
	}
	#hidden-wrapper #inbound-form-wrapper input[type=text], #hidden-wrapper #inbound-form-wrapper input[type=url], #hidden-wrapper #inbound-form-wrapper input[type=email], #hidden-wrapper #inbound-form-wrapper input[type=tel], #hidden-wrapper #inbound-form-wrapper input[type=number], #hidden-wrapper #inbound-form-wrapper input[type=password] {
	 width: 100%;

	}
	#hello {
		padding-top: 100px;
		padding-bottom:50px;
	}
	#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button {
	  display:block;
	  margin: auto;
	  text-align: center;
	  border-radius:4px;
	  text-decoration: none;
	  font-family:'Lucida Grande',helvetica;
	  background: #69c773;

	  -webkit-box-shadow: 0 4px 0 0 #51a65f;
	  -moz-box-shadow: 0 4px 0 0 #51a65f;
	  box-shadow: 0 4px 0 0 #51a65f;
	  font-size: 20px !important;
	  padding: 15px 20px;
	  width: 250px;
	  color: #FFF;
	  border: none;
	}
	#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
	  color: #51A65F;
	}
	#inbound-form-wrapper button:focus {
	outline: none;
	}
	<?php if ( $background_style === "fullscreen" ) { ?>
	#hello { <?php echo $bg_style;?> }
	<?php } else { ?>
	body { <?php echo $bg_style;?> }
	<?php } ?>

	@media only screen and (max-width: 580px) {

	  #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {

	 }
	    #hidden-wrapper {
	    width: 88%;

	  }
	  #hello h1 {
	  font-size: 46px;}

	}
	@media only screen and (max-width: 870px) {


	}
  </style>
  <?php wp_head(); do_action('lp_head');?>
<style id="inbound-style-overrides" type="text/css">
<?php

if ( $scheme != "" ) {
echo "#green, .btn-theme, #f, .navbar-default .navbar-nav>.active>a, .navbar-default .navbar-nav>.active>a:focus, .navbar-default .navbar-nav>.active>a:hover { background-color: #$scheme;}";
echo "#hello h1 {color: #$scheme; }";

}
if ( $submit_text_color != "" ) {
echo "#inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button { color: #$submit_text_color;}";
}
if ( $submit_bg_color != "" ) {
echo "#inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button { background: #$submit_bg_color;}";
}
?>
.bottom.outline-element {
<?php echo $bg_style;?>
}
.btn:hover {
box-shadow: 0 0 0 10px <?php echo $scheme_color[40];?>,0px 0 0 20px white,0px 0 15px 0 rgba(0,0,0,.5),0px 0 10px 20px rgba(0,0,0,.25);
}
#inbound-form-wrapper input[type="submit"], #inbound-form-wrapper button {

    -webkit-box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
    -moz-box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
    box-shadow: 0 4px 0 0 <?php echo $submit[60];?>;
}
#inbound-form-wrapper input[type="submit"]:hover, #inbound-form-wrapper button:hover {
  color: <?php echo $submit[60];?>;
}

</style>
</head>
<body <?php body_class('lp_ext_customizer_on single-area-edit-on'); ?>>
<div class="navbar navbar-default navbar-fixed-top">
      <div class="container" data-eq-selector="body .container:eq(0)" data-count-size="7" data-css-selector="body .container" data-js-selector="body .container:eq(0)">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand inbound_option_area outline-processed-active current-inbound-option-area" href="" data-eq-selector=".navbar-header .navbar-brand:eq(0)" data-count-size="1" data-css-selector=".navbar-header .navbar-brand" data-js-selector=".navbar-header .navbar-brand" data-option-name="Logo Image" data-option-kind="media" inbound-option-name="Logo Image"><img class="not-image inbound-media navbar-brand inbound_option_area outline-processed-active current-inbound-option-area" src="<?php echo lp_get_value($post, $key, "logo-image"); ?>" /></a>
        </div>
        <div class="navbar-collapse collapse">

          <?php echo lp_get_value($post, $key, "top-right-link"); ?>

        </div>
      </div>
    </div>
<div id="hello" class="">
	    <div class="container" data-eq-selector="body .container:eq(1)" data-count-size="7" data-css-selector="body .container" data-js-selector="body .container:eq(1)">
	    	<div class="row outline-element">
	    		<div class="col-lg-8 col-lg-offset-2 centered">
	    			<h1 class="inbound_the_title" data-eq-selector="#hello .col-lg-8.col-lg-offset-2.centered h1:eq(0)" data-count-size="1" data-css-selector="#hello .col-lg-8.col-lg-offset-2.centered h1" data-js-selector="#hello .col-lg-8.col-lg-offset-2.centered h1">
<?php lp_main_headline(); // Main Headline ?>
</h1>
	    			<h2 class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#hello .col-lg-8.col-lg-offset-2.centered h2:eq(0)" data-count-size="1" data-css-selector="#hello .col-lg-8.col-lg-offset-2.centered h2" data-js-selector="#hello .col-lg-8.col-lg-offset-2.centered h2" data-option-name="Sub headline" data-option-kind="textarea" inbound-option-name="Sub headline"><?php echo lp_get_value($post, $key, "sub-headline"); ?></h2>
	    			   <div id="hidden-wrapper">
	    			<?php echo do_shortcode( $conversion_area ); ?>
	    			   </div>
	    		</div>
	    	</div>
	    </div>
	</div>

	<div id="green" class="">
		<div class="container" data-eq-selector="body .container:eq(2)" data-count-size="7" data-css-selector="body .container" data-js-selector="body .container:eq(2)">
			<div class="row">
				<div class="col-lg-6 centered inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#green .row .col-lg-5.centered:eq(0)" data-count-size="1" data-css-selector="#green .row .col-lg-5.centered" data-js-selector="#green .row .col-lg-5.centered" data-option-name="Middle Left Content" data-option-kind="wysiwyg" inbound-option-name="Middle Left Content"><?php echo wpautop( $middle_left_content ) ?></div>

				<div class="col-lg-6 inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#green .row .col-lg-7.centered:eq(0)" data-count-size="1" data-css-selector="#green .row .col-lg-7.centered" data-js-selector="#green .row .col-lg-7.centered" data-option-name="Middle Right Content" data-option-kind="wysiwyg" inbound-option-name="Middle Right Content"><?php echo wpautop( $middle_right_content ) ?></div>
			</div>
		</div>
	</div>

	<div id="area_2" class="container inbound_option_area outline-processed-active current-inbound-option-area inbound-bottom-area" data-eq-selector="body .container:eq(3)" data-count-size="7" data-css-selector="body .container" data-js-selector="body .container:eq(3)" data-option-name="Full Width Bottom Content 1" data-option-kind="wysiwyg" inbound-option-name="Full Width Bottom Content 1">
		<div class='row'>
		<?php echo wpautop( $full_width_bottom_content_1 ); ?>
		</div>
	</div>

	<div id="skills" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#skills:eq(0)" data-count-size="1" data-css-selector="#skills" data-js-selector="#skills" data-option-name="Full Width Bottom Content 2" data-option-kind="wysiwyg" inbound-option-name="Full Width Bottom Content 2">
	<div class='container inbound-bottom-area' data-eq-selector='body .container:eq(4)' data-count-size='7' data-css-selector='body .container' data-js-selector='body .container:eq(4)'>

		<div class='row'>
		<?php echo wpautop( $full_width_bottom_content_2 ); ?>
		</div>
	</div>
	</div>

	<section id="contact"></section>
	<div class='container inbound-bottom-area' data-eq-selector='body .container:eq(5)' data-count-size='7' data-css-selector='body .container' data-js-selector='body .container:eq(5)'>

	<div id="social" class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#social:eq(0)" data-count-size="1" data-css-selector="#social" data-js-selector="#social" data-option-name="Full Width Bottom Content 3" data-option-kind="wysiwyg" inbound-option-name="Full Width Bottom Content 3">
		<div class='row'  id="inbound-bottom-row-last">
		<?php echo wpautop( $full_width_bottom_content_3 ); ?>
		</div>
	</div>
	</div>
	<div id="f" class="">
		<div class="container" data-eq-selector="body .container:eq(6)" data-count-size="7" data-css-selector="body .container" data-js-selector="body .container:eq(6)">
			<div class="row">
			<p class="inbound_option_area outline-processed-active current-inbound-option-area" data-eq-selector="#f .row p:eq(0)" data-count-size="1" data-css-selector="#f .row p" data-js-selector="#f .row p" data-option-name="Copyright Text" data-option-kind="textarea" inbound-option-name="Copyright Text"><?php echo lp_get_value($post, $key, "copyright-text"); ?></p>
			</div>
		</div>
	</div>
<!-- Bootstrap core JavaScript
    ================================================== -->


  <div id="inbound-template-name" style="display:none;">aspire</div>
<?php break; endwhile; endif;
 do_action('lp_footer');
 wp_footer();?>
 <!-- Bootstrap core JavaScript
   ================================================== -->
   <!-- Placed at the end of the document so the pages load faster -->
   <script src="<?php echo $path;?>/js/bootstrap.js"></script>
</body></html>