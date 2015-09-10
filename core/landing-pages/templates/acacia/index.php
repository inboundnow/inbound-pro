<?php
/**
* Template Name: Acacia
* @package  WordPress Landing Pages
* @author   Inbound Template Generator
*/

/* Declare Template Key */
$key = basename(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();


/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH.'templates/'.$key.'/config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('wp_head');

if (have_posts()) : while (have_posts()) : the_post();

$post_id = get_the_ID();
?>

<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>  <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>  <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]> <html class="no-js" lang="en"> <!<![endif]-->

  <head>
	<!--  Define page title -->
    <title><?php wp_title(); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="LandingSumo.com">

	<!-- don't need a favicon here
	<link rel="icon" href="<?php //echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/img/favicon.ico' ?>">
	-->

    <!-- Bootstrap core CSS -->
    <link href="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/css/bootstrap.css' ?>" rel="stylesheet">

    <!-- Custom styles for this template -->
	<link rel="stylesheet" type="text/css" href="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/css/font-awesome.min.css' ?>">
    <link href="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/css/style.css' ?>" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/js/ie10-viewport-bug-workaround.js' ?>"></script>

	<!-- Load Normal WordPress wp_head() function -->
    <?php wp_head(); ?>

    <!-- Load Landing Pages's custom pre-load hook for 3rd party plugin integration -->
    <?php do_action("lp_head"); ?>
  </head>

  <body>

	  <?php
/* Start acacia_template_body Flexible Content Area Output */

	if(function_exists('have_rows')) :
		if(have_rows('acacia_template_body')) :
			 while(have_rows('acacia_template_body')) : the_row();
				 switch( get_sub_field('acf_fc_layout')) :
				// start layout hero_box
				case 'hero_box' :
					$hero_bg_color = get_sub_field("hero_bg_color");
					$add_media = get_sub_field("add_media");
					$hero_image = get_sub_field("hero_image");
					$hero_video = get_sub_field("hero_video");
					$hero_headline = get_sub_field("hero_headline");
					$hero_headline_color = get_sub_field("hero_headline_color");
					$hero_sub_headline = get_sub_field("hero_sub_headline");
					$hero_sub_headline_color = get_sub_field("hero_sub_headline_color");
					$hero_button_text = get_sub_field("hero_button_text");
					$hero_button_text_color = get_sub_field("hero_button_text_color");
					$hero_button_link = get_sub_field("hero_button_link");
					$hero_button_bg_color = get_sub_field("hero_button_bg_color");
					//$hero_media = ($add_media == 'image') ? $hero_image_url : $hero_video;
					?>
					<div id="is" style="background-color:<?php echo $hero_bg_color; ?>">
						<div class="container">
							<div class="row">
								<div class="col-lg-6 col-md-6 centered">
									<?php if ( 'image' == $add_media) {
										echo '<img style="max-width: 100%;" alt="" src="'. $hero_image .'">';
									} else {
										echo $hero_video;
									} ?>
								</div>
								<div class="col-lg-6 col-md-6 centered" style="">
									<h1 style="color:<?php echo $hero_headline_color; ?>"><?php echo $hero_headline;  ?></h1>
									<p style="color:<?php echo $hero_sub_headline_color; ?>"><?php echo $hero_sub_headline;  ?></p>
									<a href="<?php echo $hero_button_link; ?>" style="border: 4px solid <?php echo $hero_button_bg_color; ?>; background-color:<?php echo $hero_button_bg_color; ?>; color:<?php echo $hero_button_text_color; ?>" class="btn btn-lg btn-standard mtb"><?php echo $hero_button_text; ?></a>
								</div>
							</div><!--/row -->
						</div><!--/container -->
					</div><!-- /.IS Wrap -->
				<?php
				break;
				/* start layout three_columns_text */
				case 'three_columns_text' :
					$three_col_bg_color = get_sub_field("three_col_bg_color");
					$three_col_text_color = get_sub_field("three_col_text_color");
					$three_col_headline = get_sub_field("three_col_headline");
					$three_col_column_1_text = get_sub_field("three_col_column_1_text");
					$three_col_column_1_icon = get_sub_field("three_col_column_1_icon");
					$three_col_column_2_text = get_sub_field("three_col_column_2_text");
					$three_col_column_2_icon = get_sub_field("three_col_column_2_icon");
					$three_col_column_3_text = get_sub_field("three_col_column_3_text");
					$three_col_column_3_icon = get_sub_field("three_col_column_3_icon");
			?>
					<div id="three-col" style="background-color:<?php echo $three_col_bg_color; ?>">
						<div class="container">
							<div class="row mtb centered">
								<h2 style="color:<?php echo $three_col_text_color; ?>"><?php echo $three_col_headline; ?></h2>
								<div class="icons-white-bg mt">
									<div class="col-md-4">
										<i class="fa <?php echo $three_col_column_1_icon; ?>" style="color:<?php echo $three_col_text_color; ?>"></i>
										<p style="color:<?php echo $three_col_text_color; ?>"><?php echo $three_col_column_1_text; ?></p>
									</div><!--/col-md-4 -->

									<div class="col-md-4">
										<i class="fa <?php echo $three_col_column_2_icon; ?>" style="color:<?php echo $three_col_text_color; ?>"></i>
										<p style="color:<?php echo $three_col_text_color; ?>"><?php echo $three_col_column_2_text; ?></p>
									</div><!--/col-md-4 -->

									<div class="col-md-4">
										<i class="fa <?php echo $three_col_column_3_icon; ?>" style="color:<?php echo $three_col_text_color; ?>"></i>
										<p style="color:<?php echo $three_col_text_color; ?>"><?php echo $three_col_column_3_text; ?></p>
									</div><!--/col-md-4 -->
								</div><!--/icons-white-bg -->

							</div><!--/row -->
						</div><!--/container -->
					</div>
				<?php break;
				/* start layout testimonials */
				case 'testimonials' :
					$testimonials_bg_color = get_sub_field("testimonials_bg_color");
					?>
					<div id="mint" class="carousel slide" data-ride="carousel" style="background-color:<?php echo $testimonials_bg_color ?>">
						<div class="col-md-6 col-md-offset-3">
							<!-- Carousel
							================================================== -->
							<!-- Indicators -->
							<ol class="carousel-indicators">
					<?php
					/* Start testimonial Repeater Output Carousel */
					if ( have_rows( "testimonial" ) )  { ?>

						<?php $slide = 0;
						$class = 'active'; ?>
						<?php while ( have_rows( "testimonial" ) ) : the_row();
							?>
							<li data-target="#mint" data-slide-to="<?php echo $slide; ?>" class="<?php echo $class; ?>"></li>
							<?php
							$slide += 1;
							$class = ''; //only the first item has class 'active'
						endwhile;
					}

					?>
							</ol>
							<div class="carousel-inner">
					 <?php

					$class = 'active';
					/* Start testimonial Repeater Output */
					if ( have_rows( "testimonial" ) )  { ?>

						<?php while ( have_rows( "testimonial" ) ) : the_row();
							$testimonial_image = get_sub_field("testimonial_image");
							$testimonial_text = get_sub_field("testimonial_text");
							$testimonial_name = get_sub_field("testimonial_name");
							$testimonial_name_link = get_sub_field("testimonial_name_link");
						?>
									<div class="item <?php echo $class; ?>">
										<div class="centered mtb">
											<img src="<?php echo $testimonial_image; ?>" class="img-circle" height="100" width="100" alt="slide">
											<h4><?php echo $testimonial_text; ?></h4>
											<p><?php echo strtoupper($testimonial_name); ?></p>
										</div>
									</div><!-- /item -->
									<?php
									$class = '';
						endwhile; ?>

					<?php } /* end if have_rows(testimonial) */
					/* End testimonial Repeater Output */
								?>
								</div><!--/carousel-inner -->
							</div><!--/col-md-6 -->
						</div><!--/mint -->

				<?php break;
				/* start layout faq_section */
				case 'faq_section' :
					$faq_bg_color = get_sub_field("faq_bg_color");
					$faq_text_color = get_sub_field("faq_text_color");
					$faq_headline = get_sub_field("faq_headline");
					/* Start faqs Repeater Output */
					if ( have_rows( "faqs" ) )  { ?>

						<div id="faq" style="background-color: <?php echo $faq_bg_color; ?>;">
							<div class="container">
								<div class="row mtb">
									<h2 class="centered" style="color:<?php echo $faq_text_color; ?>"><?php echo $faq_headline; ?></h2>

							<?php while ( have_rows( "faqs" ) ) : the_row();
									$faq_title = get_sub_field("faq_title");
									$faq_content = get_sub_field("faq_content");
							?>

									<div class="col-md-6 mt">
										<h4 style="color:<?php echo $faq_text_color; ?>"><?php echo $faq_title; ?></h4>
										<p style="color:<?php echo $faq_text_color; ?>"><?php echo $faq_content; ?></p>
									</div><!--/col-md-6 -->
								
							<?php endwhile;?>


					<?php } /* end if have_rows(faqs) */
					/* End faqs Repeater Output */
					$more_questions_button_text = get_sub_field("more_questions_button_text");
					$more_questions_button_link = get_sub_field("more_questions_button_link");
					$more_questions_button_color = get_sub_field("more_questions_button_color");
					$more_questions_button_text_color = get_sub_field("more_questions_button_text_color");
					?>
							
								<div class="col-md-12 centered mtb" style="margin-top: 0;">
									<a href="<?php echo $more_questions_button_link; ?>" class="btn btn-lg btn-standard mt" style="color:<?php echo $more_questions_button_text_color; ?>; background-color:<?php echo $more_questions_button_color; ?>; border: 4px solid <?php echo $more_questions_button_color; ?>;"><?php echo $more_questions_button_text; ?></a>
								</div><!--/centered -->
								
							</div><!--/row -->
						</div><!-- /container -->
					</div><!-- /faq -->

			<?php break;
				endswitch; /* end switch statement */
			endwhile; /* end while statement */
		 endif; /* end have_rows */
	endif;  /* end function_exists */
/* End acacia_template_body Flexible Content Area Output */
	?>






    <div id="f">
    	<div class="container">
    		<div class="row centered mtb">
    			<h2>Get In Touch With Us</h2>
    			<h5>Mellentesque habitant morbi tristique senectus et netus<br/> et malesuada famesac turpis egestas.</h5>
    			<div class="col-md-6 col-md-offset-3 mt">
					<form role="form" action="register.php" method="post" enctype="plain">
	    				<input type="email" name="email" class="subscribe-input" placeholder="Enter your e-mail address..." required>
						<button class='btn btn-green2 subscribe-submit' type="submit">Subscribe</button>
					</form>
    			</div><!--/col-md-6 -->
    		</div><!--/row -->
    		<div class="col-md-6 col-md-offset-3">
    			<div class="social-icons">
    				<a href="#"><i class="fa fa-dribbble"></i></a>
    				<a href="#"><i class="fa fa-instagram"></i></a>
    				<a href="#"><i class="fa fa-twitter"></i></a>
    			</div>
    		</div>
    	</div><!--/container -->
    </div><!--/f -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/js/jquery.min.js' ?>"></script>
    <script src="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/js/bootstrap.min.js' ?>"></script>
    <script src="<?php echo LANDINGPAGES_URLPATH. 'templates/' . $key .'/assets/js/retina-1.1.0.js' ?>"></script>

	<?php
	do_action('lp_footer');
	do_action('wp_footer');
	?>
  </body>
</html>

<?php

endwhile; endif;