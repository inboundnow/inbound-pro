<?php
/*****************************************/
// Template Title:	Elegance-Lander Template
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Shareme Library */
include_once(LANDINGPAGES_PATH.'libraries/library.shareme.php');
/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH.'/'.$key.'/';
$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');
// Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
		$hex = @ereg_replace("#", "", $hex);
		$color = array();

		if(strlen($hex) == 3) {
			$color['r'] = hexdec(substr($hex, 0, 1) . $r);
			$color['g'] = hexdec(substr($hex, 1, 1) . $g);
			$color['b'] = hexdec(substr($hex, 2, 1) . $b);
		}
		else if(strlen($hex) == 6) {
			$color['r'] = hexdec(substr($hex, 0, 2));
			$color['g'] = hexdec(substr($hex, 2, 2));
			$color['b'] = hexdec(substr($hex, 4, 2));
		}

		return $color;

}
/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
$logo_img = lp_get_value($post, $key, 'logo-image-id');
$intro_content = lp_get_value($post, $key, 'intro-text-area');
$image_area1 = lp_get_value($post, $key, 'image-area1');
$image_area2 = lp_get_value($post, $key, 'image-area2');
$image_area3 = lp_get_value($post, $key, 'image-area3');
$image_link1 = lp_get_value($post, $key, 'image-link1');
$image_link2 = lp_get_value($post, $key, 'image-link2');
$image_link3 = lp_get_value($post, $key, 'image-link3');
$image_area_text1 = lp_get_value($post, $key, 'image-text-area1');
$image_area_text2 = lp_get_value($post, $key, 'image-text-area2');
$image_area_text3 = lp_get_value($post, $key, 'image-text-area3');
$secondary_content = lp_get_value($post, $key, 'secondary-conversion-area');
//$bottom_content = lp_get_value($post, $key, 'bottom-content-area');
$copy_right_content = lp_get_value($post, $key, 'copy-right-textarea');

$heading_color = lp_get_value($post, $key, 'heading-color');
$content_background_color = lp_get_value($post, $key, 'content-background-color');
$content_text_color = lp_get_value($post, $key, 'content-text-color');
$social_display = lp_get_value($post, $key, 'display-social');
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

//Background Setting
$background_style = lp_get_value($post, $key, 'background-style');
$background_image = lp_get_value($post, $key, 'body-background-image');
$solid_background_color = lp_get_value($post, $key, 'solid-background-color');


$RBG_array = Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"];
$submit_hover = inbound_color_scheme($submit_button_color, 'hex' );
?>
<!DOCTYPE HTML>

<html>
	<head>
		<title><?php wp_title();?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<?php /* Load all functions hooked to lp_head including global js and global css */
		wp_head(); // Load Regular WP Head
		do_action('lp_head'); // Load Custom Landing Page Specific Header Items
		?>
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,800" rel="stylesheet" type="text/css" />
		<link href="http://fonts.googleapis.com/css?family=Oleo+Script:400" rel="stylesheet" type="text/css" />


			<link rel="stylesheet" href="<?php echo $path;?>css/5grid/core.css" />

			<link rel="stylesheet" href="<?php echo $path;?>css/style.css" />
			<link rel="stylesheet" href="<?php echo $path;?>css/style-desktop.css" />
		</noscript>
		<!--[if lte IE 8]><link rel="stylesheet" href="<?php echo $path;?>css/ie8.css" /><![endif]-->
		<!--[if lte IE 7]><link rel="stylesheet" href="<?php echo $path;?>css/ie7.css" /><![endif]-->
		<style type="text/css">

		<?php if(!empty($heading_color)){ ?>
					h1, h2, h3, h4, h5, h6 {
 				color: #<?php echo $heading_color;?>; }
		<?php	} ?>
		<?php if(!empty($content_text_color)){ ?>
					#banner, .inner, #main-wrapper {
 				color: #<?php echo $content_text_color;?>; }
		<?php	} ?>
		<?php if(!empty($content_background_color)){ ?>
					.box,#main-wrapper {
 				background-color: #<?php echo $content_background_color;?>; }
		<?php	} ?>

		<?php if($background_style=='fullscreen'){
				$bgstyle='background:url('.$background_image.') no-repeat center center fixed;
							-webkit-background-size:cover;
							-moz-background-size:cover;
							-o-background-size:cover;
							background-size:cover;
							filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
							ms-filter:"progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';
			}
			if($background_style=='color'){
				$bgstyle='background: #'.$solid_background_color.';';
			}
			if($background_style=='tile'){
				$bgstyle='background:url('.$background_image.') repeat';
			}
			if($background_style=='repeat-x'){
				$bgstyle='background:url('.$background_image.') repeat-x';
			}
			if($background_style=='repeat-y'){
				$bgstyle='background:url('.$background_image.') repeat-y';
			}
			if($background_style=='default'){
				$bgstyle='background:#EEEEEE';
			}
				?>
		body{<?php echo $bgstyle;?>}
		#shareme .button {

		background: none;
		color: #fff;
		text-decoration: none;
		border-radius: none;
		font-weight: normal;
		outline: 0;
		-moz-transition: none;
		-webkit-transition: none
		-o-transition: none;
		-ms-transition: none;
		transition: none;
		}

		/* Landing page form styles */
		.lp-form {margin-left: 5px;}
		input[type="text"], input[type="email"] {
		width: 60%;
		padding: 8px 4px 8px 10px;
		margin-bottom: 15px;
		border: 1px solid #4E3043;
		border: 1px solid rgba(78, 48, 67, 0.8);
		background: rgba(0, 0, 0, 0.15);
		border-radius: 2px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2), inset 0 1px 1px rgba(0, 0, 0, 0.1);
		-webkit-transition: all 0.3s ease-out;
		-moz-transition: all 0.3s ease-out;
		-ms-transition: all 0.3s ease-out;
		-o-transition: all 0.3s ease-out;
		transition: all 0.3s ease-out;
		font-family: Helvetica,Arial;
		color: $484848;
		font-size: 13px;
		}
		input[type="submit"] {
		width: 280px;
		padding: 8px 5px;
		background: #EAEAEA;
		background: -moz-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
		background: -ms-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
		background: -o-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
		background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba(99, 64, 86, 0.5)), to(rgba(76, 49, 65, 0.7)));
		background: -webkit-linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
		background: linear-gradient(rgba(99, 64, 86, 0.5), rgba(76, 49, 65, 0.7));
		border-radius: 5px;
		border: 1px solid #4E3043;
		box-shadow: inset 0 1px rgba(255, 255, 255, 0.4), 0 2px 1px rgba(0, 0, 0, 0.1);
		cursor: pointer;
		-webkit-transition: all 0.3s ease-out;
		-moz-transition: all 0.3s ease-out;
		-ms-transition: all 0.3s ease-out;
		-o-transition: all 0.3s ease-out;
		transition: all 0.3s ease-out;
		color: #444444;
		text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
		font-size: 22px;
		font-weight: bold;
		font-family: Helvetica,Arial;
		}
		#banner .button {
width: 100%;}
		p, .lp-span, label {font-size: 16px;}
		.lp-span {text-transform: none; display: inline; margin-bottom: 8px;}

		#lp_container .lp-form{
			text-align:center;
		}
		label {
			width: 20%;
			display: inline-block;
		}
		#lp_container_form {
			width: 425px;
		}
		/* end form styles */
		<?php if ($submit_button_color != "") {
			echo"input[type='submit'] {
				background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
				background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
				background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
				background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
				background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
				background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));}";
		}?>
		#social_wrapper{
			text-align:center;
			width:100%;
			background-color:none;
		}
		#logo h1{
			background:none;
		}
		#footer-wrapper{
			padding:0 0 2em;
		}
		#shareme .buttons {
			width: 568px;
			margin: auto;
			margin-left: 15%;
		}
		.linkedin {
		margin-right: -17px;
		margin-top: 15px !important;
		margin-bottom: -20px;
		}
		.facebook {
			margin-top: 14px !important;
		margin-bottom: -5px;
		}
		.googleplus {
			margin-right: 94px;
		}
		.page-wrapper {
			width: 980px;
			margin:auto;
		}
		.four-col {
		width: 31%;
		display: inline-block;
		margin-right: 10px;

		margin-left: 10px !important;
		}
			.four-col:first-child {
			margin-left: 0px
		}
		.four-col:last-child {
			margin-right: 0px
		}
		.footarea {
			border-radius:6px;
		}
		.main-grid-class .gform_wrapper .top_label input.medium, .main-grid-class .gform_wrapper .top_label select.medium {
		width: 100%;
		}
		.main-grid-class .gform_wrapper .gform_footer {
		padding: 0px 0 10px 0; }
		input[type=submit]:hover, button[type=submit]:hover {
			background: <?php echo $submit_hover[30];?>;
		}
		</style>
	</head>
	<body class="homepage">


		<div class="page-wrapper">
		<!-- Banner Wrapper -->
			<div id="banner-wrapper">
				<div class="5grid-layout">
					<div class="row">
						<div class="twelve_col">

							<!-- Banner -->
								<div id="banner" class="box">
										<h1><?php the_title();?></h1>

									<div class="main-grid-class 5grid-layout">
										<div class="row">
											<div class="seven_grid">

												<p><?php echo $intro_content;?></p>
												<div id="content-area">
												<?php echo do_shortcode( $content ); ?>
											</div>
											</div>
											<div class="five_grid">
												<?php echo do_shortcode( $conversion_area ); /* echos out form/conversion area content */ ?>
											</div>
										</div>
									</div>

								</div>

						</div>
					</div>
				</div>
			</div>

		<!-- Features Wrapper -->
			<div id="features-wrapper">
				<div class="5grid-layout main-grid-class">
					<div class="row">
						<div class="four-col">

							<!-- Box -->
								<section class="box box-feature">
									<a href="<?php echo $image_link1; ?>" class="image image-full"><img src="<?php echo $image_area1;?>" alt="" /></a>
									<div class="inner">
										<?php echo $image_area_text1;?>
									</div>
								</section>

						</div>
						<div class="four-col">

							<!-- Box -->
								<section class="box box-feature">
									<a href="<?php echo $image_link2; ?>" class="image image-full"><img src="<?php echo $image_area2;?>" alt="" /></a>
									<div class="inner">
										<?php echo $image_area_text2;?>
									</div>
								</section>

						</div>
						<div class="four-col">

							<!-- Box -->
								<section class="box box-feature last">
									<a href="<?php echo $image_link3; ?>" class="image image-full"><img src="<?php echo $image_area3;?>" alt="" /></a>
									<div class="inner">
										<?php echo $image_area_text3;?>
									</div>
								</section>

						</div>
					</div>
				</div>
			</div>

		<!-- Main Wrapper -->
			<div id="main-wrapper" class="footarea">
				<div class="5grid-layout">
					<div class="row">

						<div class="twelve_col">

							<!-- Content -->
								<div id="secondary-content">
									<section class="last">
										<?php echo $secondary_content;?>
									</section>
								</div>
							<div class="row">
									<div class="twelve_col">
										<div id="copyright">
											<?php if ($social_display == "1" ) { // Show Social Media Icons?>
											<div id="social_wrapper">
													<?php lp_social_media(); // print out social media buttons?>
											</div>
											<?php }?>
											<?php echo $copy_right_content;?>
										</div>
									</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			</div>

		<!-- Footer Wrapper -->

<?php
endwhile; endif; // End wordpress loop

do_action('lp_footer');
wp_footer();
?>
	</body>
</html>