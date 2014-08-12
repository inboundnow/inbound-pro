<?php

/* Landing Page Boiler Plate */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();
$id = get_the_ID();
$title = get_the_title($id);
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');

$color_scheme = lp_get_value($post, $key,  'color-scheme' );
$text_color = lp_get_value($post, $key,  'text-color' );
$background_style = lp_get_value($post, $key,  'background-style' );
$background_image = lp_get_value($post, $key,  'background-image' );
$background_color = lp_get_value($post, $key,  'background-color' );
$logo = lp_get_value($post, $key,  'logo' );
$display_social = lp_get_value($post, $key,  'display-social' );
$sidebar = lp_get_value($post, $key,  'sidebar' );
$form_headline = lp_get_value($post, $key,  'form-headline' );
$sharetext = lp_get_value($post, $key,  'sharetext' );
$share_link = lp_get_value($post, $key,  'shareurl' );
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
?>
<!DOCTYPE html>
<html class="no-js">
<head>
	<title><?php wp_title(); ?></title>
	<meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width">

	<?php wp_head(); // Load Regular WP Head
		  do_action('lp_head'); // Load Custom Landing Page Specific Header Items
	?>
	<link rel="stylesheet" href="<?php echo $path;?>assets/style.css" type="text/css">
	<?php if ($share_link != ""){
		$share_link = $share_link;
	} else {
		$share_link = get_permalink( $id );
	} ?>
	<style type="text/css">
	body, html { <?php echo $bg_style; ?> }
	<?php
	if ( $text_color != "" ) {
	echo "#inbound-content, #form-main, #form-div, .feedback-input, .gform_wrapper input, .inbound-input, #inbound-content li:before, #inbound-social a, #button-blue:hover, .gform_wrapper .gform_footer input[type=submit]:hover, { color: #$text_color;}";
	}
	/* Color Options CSS helper - Add to inline style tag */
	if ( $color_scheme != "" ) {
	echo ".feedback-input, .gform_wrapper input, .inbound-input { border: 3px solid #$color_scheme;}";
	echo "#button-blue:hover, .gform_wrapper .gform_footer input[type=submit]:hover, #inbound-form-wrapper input[type='button']:hover, #inbound-form-wrapper input[type='submit']:hover, #inbound-form-wrapper button:hover  { border: 4px solid #$color_scheme;}";
	echo ".feedback-input, .gform_wrapper input, #inbound-content li:before, #inbound-social a, #button-blue:hover, .gform_wrapper .gform_footer input[type=submit]:hover, #inbound-form-wrapper input[type='submit']:hover, #inbound-form-wrapper button:hover  { color: #$color_scheme;}";
	echo "#button-blue, .gform_wrapper .gform_footer input[type=submit], #inbound-form-wrapper input[type='button'], #inbound-form-wrapper input[type='submit'], #inbound-form-wrapper button  { background-color: #$color_scheme;}";
	}

	if ( $background_color != "" ) {
	echo ".css_element { color: #$background_color;}";
	}
	if ($display_social === '0'){
		echo "#inbound-footer {display:none;}";
	}
	if ($sidebar === 'left'){
		echo "#inbound-content { float:right; width: 53%;} #form-main {float:left;}";
	}

	 ?>
	 #inbound-form-wrapper input[type=text], #inbound-form-wrapper input[type=url], #inbound-form-wrapper input[type=email], #inbound-form-wrapper input[type=tel], #inbound-form-wrapper input[type=number], #inbound-form-wrapper input[type=password] {
	 width: 94% !important;
	 }
	 #email {
	 background-image: none; }
	</style>
</head>
<body <?php body_class(); ?>>
	<!--[if lt IE 8]>
	           <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->
<div id="inbound-wrapper">
	<div id="inbound-logo"><img src="<?php echo $logo;?>"></div>
	<div id="inbound-content">
	<h1><?php lp_main_headline(); ?></h1>
	<?php echo do_shortcode($content);?>
	</div>
	<div id="form-main">
	  <div id="form-div">
	  	<?php echo $form_headline;?>
	  	<!--
	    <form class="form" id="form1">

	      <p class="name">
	        <input name="name" type="text" class="validate[required,custom[onlyLetter],length[0,100]] feedback-input" placeholder="Name" id="name" />
	      </p>

	      <p class="email">
	        <input name="email" type="text" class="validate[required,custom[email]] feedback-input" id="email" placeholder="Email" />
	      </p>

	      <p class="email">
	        <input name="email" type="text" class="validate[required,custom[email]] feedback-input" id="email" placeholder="Phone" />
	      </p>


	      <div class="submit">
	        <input type="submit" value="SEND" id="button-blue"/>
	        <div class="ease"></div>
	      </div>
	    </form> -->
	    <?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
	  </div>

</div>
</div>
<div id="inbound-footer">
	<span class="share-text"><?php echo $sharetext;?></span>
<div id="inbound-social">

	<a class='inbound-social-share' href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=<?php echo urlencode($share_link);?>&p[images][0]=&p[title]=<?php echo urlencode($title);?>&p[summary]=<?php echo urlencode($title);?>"><i class="fa fa-facebook-square"></i><i class="fa fa-square fa-stack-2x"></i></a>
	<a class='inbound-social-share' href="https://plus.google.com/share?url=<?php echo $share_link;?>" ><i class="fa fa-google-plus-square"></i><i class="fa fa-square fa-stack-2x"></i></a>
	<a class='inbound-social-share' href="http://twitter.com/intent/tweet?text=<?php echo urlencode($title);?>+<?php echo urlencode($share_link);?>"><i class="fa fa-twitter-square"></i><i class="fa fa-square fa-stack-2x"></i></a>
	<a class='inbound-social-share' href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($share_link);?>&title=<?php echo urlencode($title);?>&summary=<?php echo urlencode($title);?>"><i class="fa fa-linkedin-square"></i><i class="fa fa-square fa-stack-2x"></i></a>
</div>
</div>
<?php break;
endwhile; endif; // End wordpress loop

do_action('lp_footer');
wp_footer();
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
	   $('.inbound-social-share').click(function(event) {
	      var width  = 575,
	          height = 400,
	          left   = ($(window).width()  - width)  / 2,
	          top    = ($(window).height() - height) / 2,
	          url    = this.href,
	          opts   = 'status=1' +
	                   ',width='  + width  +
	                   ',height=' + height +
	                   ',top='    + top    +
	                   ',left='   + left;

	      window.open(url, 'twitter', opts);

	      return false;
	    });
	 });

</script>

</body>
</html>