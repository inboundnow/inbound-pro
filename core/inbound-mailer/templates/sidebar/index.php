<?php
/**
* Template Name: Sidebar
* @package  Inbound Email
* @author   Inbound Now
*/
/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

/* Main content */
$post_id = get_the_ID();
$logo_url = get_field('logo_url', $post_id);
$header_bg_color = get_field('header_bg_color', $post_id);
$callout_text = get_field('callout_text', $post_id);

/* Footer */
$footer_bg_color = get_field('footer_bg_color', $post_id);
$facebook_page_url = get_field('facebook_page', $post_id);
$twitter_handle = get_field('twitter_handle', $post_id);
$google_plus_url = get_field('google_plus', $post_id);
$phone_number = get_field('phone_number', $post_id);
$email = get_field('email', $post_id);
$terms_page_url = get_field('terms_page_url', $post_id);
$privacy_page_url = get_field('privacy_page_url', $post_id);

/* Sidebar */
$sidebar_bg_color = get_field('sidebar_bg_color', $post_id);
$sidebar_header = get_field('sidebar_header', $post_id);
$sidebar_subheader = get_field('sidebar_subheader', $post_id);
$sidebar_header_url = get_field('sidebar_header_link', $post_id);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- If you delete this meta tag, Earth will fall into the sun. -->
<meta name="viewport" content="width=device-width" />

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title >Sidebar Template</title>

<style type='css/text'>
	/* -------------------------------------
			GLOBAL
	------------------------------------- */
	* {
		margin:0;
		padding:0;
	}
	* { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }

	img {
		max-width: 100%;
	}
	.collapse {
		margin:0;
		padding:0;
	}
	body {
		-webkit-font-smoothing:antialiased;
		-webkit-text-size-adjust:none;
		width: 100%!important;
		height: 100%;
	}


	/* -------------------------------------
			ELEMENTS
	------------------------------------- */
	a { color: #2BA6CB;}

	.btn {
		text-decoration:none;
		color: #FFF;
		background-color: #666;
		padding:10px 16px;
		font-weight:bold;
		margin-right:10px;
		text-align:center;
		cursor:pointer;
		display: inline-block;
	}

	div.callout {
		padding:15px;
		background-color:<?php echo (!empty($callout_bg_color) ? $callout_bg_color : '#ffffff');  ?>;
		margin-bottom: 15px;
	}
	.callout a {
		font-weight:bold;
		color: #2BA6CB;
	}

	table.social {
	/* 	padding:15px; */
		background-color: <?php echo $footer_bg_color; ?>;

	}
	.social .soc-btn {
		padding: 3px 7px;
		font-size:12px;
		margin-bottom:10px;
		text-decoration:none;
		color: #FFF;font-weight:bold;
		display:block;
		text-align:center;
	}
	a.fb { background-color: #3B5998!important; }
	a.tw { background-color: #1daced!important; }
	a.gp { background-color: #DB4A39!important; }
	a.ms { background-color: #000!important; }

	.sidebar .soc-btn {
		display:block;
		width:100%;
	}

	/* -------------------------------------
			HEADER
	------------------------------------- */
	table.head-wrap { width: 100%;}

	.header.container table td.logo { padding: 15px; }
	.header.container table td.label { padding: 15px; padding-left:0px;}


	/* -------------------------------------
			BODY
	------------------------------------- */
	table.body-wrap { width: 100%;}


	/* -------------------------------------
			FOOTER
	------------------------------------- */
	table.footer-wrap { width: 100%;	clear:both!important;
	}
	.footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
	.footer-wrap .container td.content p {
		font-size:10px;
		font-weight: bold;

	}


	/* -------------------------------------
			TYPOGRAPHY
	------------------------------------- */
	h1,h2,h3,h4,h5,h6 {
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;
	}
	h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }

	h1 { font-weight:200; font-size: 44px;}
	h2 { font-weight:200; font-size: 37px;}
	h3 { font-weight:500; font-size: 27px;}
	h4 { font-weight:500; font-size: 23px;}
	h5 { font-weight:900; font-size: 17px;}
	h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}

	.collapse { margin:0!important;}

	p, ul {
		margin-bottom: 10px;
		font-weight: normal;
		font-size:14px;
		line-height:1.6;
	}
	p.lead { font-size:17px; }
	p.last { margin-bottom:0px;}

	ul li {
		margin-left:5px;
		list-style-position: inside;
	}

	/* -------------------------------------
			SIDEBAR
	------------------------------------- */
	ul.sidebar {
		background:<?php echo $sidebar_bg_color; ?>;
		display:block;
		list-style-type: none;
	}
	ul.sidebar li { display: block; margin:0;}
	ul.sidebar li a {
		text-decoration:none;
		color: #666;
		padding:10px 16px;
	/* 	font-weight:bold; */
		margin-right:10px;
	/* 	text-align:center; */
		cursor:pointer;
		border-bottom: 1px solid #777777;
		border-top: 1px solid #FFFFFF;
		display:block;
		margin:0;
	}
	ul.sidebar li a.last { border-bottom-width:0px;}
	ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}



	/* ---------------------------------------------------
			RESPONSIVENESS
			Nuke it from orbit. It's the only way to be sure.
	------------------------------------------------------ */

	/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
	.container {
		display:block!important;
		max-width:600px!important;
		margin:0 auto!important; /* makes it centered */
		clear:both!important;
	}

	/* This should also be a block element, so that it will fill 100% of the .container */
	.content {
		padding:15px;
		max-width:600px;
		margin:0 auto;
		display:block;
	}

	/* Let's make sure tables in the content area are 100% wide */
	.content table { width: 100%; }


	/* Odds and ends */
	.column {
		width: 300px;
		float:left;
	}
	.column tr td { padding: 15px; }
	.column-wrap {
		padding:0!important;
		margin:0 auto;
		max-width:600px!important;
	}
	.column table { width:100%;}
	.social .column {
		width: 280px;
		min-width: 279px;
		float:left;
	}

	/* Be sure to place a .clear element after each set of columns, just to be safe */
	.clear { display: block; clear: both; }


	/* -------------------------------------------
			PHONE
			For clients that support media queries.
			Nothing fancy.
	-------------------------------------------- */
	@media only screen and (max-width: 600px) {

		a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

		div[class="column"] { width: auto!important; float:none!important;}

		table.social div[class="column"] {
			width:auto!important;
		}

	}
</style>

</head>

<body bgcolor="#FFFFFF">

<!-- HEADER -->
<table class="head-wrap" bgcolor="<?php  echo $header_bg_color; ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
	<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
		<td class="header container" style="margin: 0 auto!important;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;display: block!important;max-width: 600px!important;clear: both!important;">

			<div class="content" style="margin: 0 auto;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;max-width: 600px;display: block;">
				<table bgcolor="<?php  echo $header_bg_color; ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
					<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
						<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"><?php if ($logo_url) { ?>
						<img src="<?php  echo $logo_url; ?>" width="188" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;max-width: 100%;"/>
						<?php } ?></td>
						<td align="right" height="70" class="viewWebsite" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
						<p style="font-family: Arial, Helvetica, sans-serif;color: #555555;font-size: 10px;padding: 0;margin: 0;margin-bottom: 10px;font-weight: normal;line-height: 1.6;">Trouble viewing? Read this <a href="<?php echo get_permalink( $post_id ); ?>" style="color: #990000;margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;" class="do-not-tracks"><?php _e('online' , 'inbound-email' ); ?></a>.</p>
						</td>
					</tr>
				</table>
			</div>

		</td>
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
	</tr>
</table><!-- /HEADER -->

<!-- BODY -->
<table class="body-wrap" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
	<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
		<td class="container" bgcolor="#FFFFFF" style="margin: 0 auto!important;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;display: block!important;max-width: 600px!important;clear: both!important;">

			<div class="column-wrap" style="margin: 0 auto;padding: 0!important;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;max-width: 600px!important;">

				<div class="column" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 300px;float: left;">
				<table style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
				<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
					<td style="margin: 0;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">

						<?php
						if ( function_exists('have_rows') ) {
							if (have_rows('email_body')) {
								while ( have_rows('email_body')) {
									the_row();

									switch( get_sub_field('acf_fc_layout') ) {
										case 'email_content':
											$main_email_content = get_sub_field('main_content');
											echo $main_email_content;
											break;

										case 'button':
											$button_link = get_sub_field('button_link');
											$button_text = get_sub_field('button_text');
											$style = 'color: ';
											if ( $button_text_color = get_sub_field('button_text_color') ) {
												$style .= $button_text_color . ';';
											} else { $style .= '#fff;'; }
											$style .= 'background-color: ';
											if ( $button_bg_color = get_sub_field('button_bg_color') ) {
												$style .= $button_bg_color . ';';
											} else { $style .= '#666;'; }
											?>
											<a href="<?php echo $button_link; ?>" style="<?php echo $style; ?>
												 margin: 0;padding: 10px 16px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;text-decoration: none;font-weight: bold;margin-right: 10px;text-align: center;cursor: pointer;display: inline-block;margin: 0;color: #FFF;background-color: #666;" class="btn"><?php echo $button_text; ?></a>
											<?php
											break;

										case 'callout':
											$callout_text = get_sub_field('callout_text');
											$callout_bg_color = get_sub_field('callout_bg_color');
											//$callout_bg_color = $callout_bg_color_array[1];
											?>
											<div class="callout" style="margin: 0;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;background-color: <?php echo (!empty($callout_bg_color) ? $callout_bg_color : '#ffffff');?> ;margin-bottom: 15px;">
												<?php echo $callout_text; ?>
											</div><!-- /Callout Panel -->
											<?php
											break;
									}
								}
							}

							if(!have_rows('email_body')) {
								echo '<div class="container">';
								the_content();
								echo "</div>";
							}
						}

					?>

					</td>
				</tr>
			</table>
			</div>

				<div class="column" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 300px;float: left;">
				<table style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
				<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
					<td style="margin: 0;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">

						<ul class="sidebar" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-bottom: 10px;font-weight: normal;font-size: 14px;line-height: 1.6;background: <?php echo $sidebar_bg_color;?> ;display: block;list-style-type: none;">
							<li style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-left: 5px;list-style-position: inside;display: block;">
								<a href="<?php echo $sidebar_header_url; ?>" style="margin: 0;padding: 10px 16px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #666;text-decoration: none;margin-right: 10px;cursor: pointer;border-bottom: 1px solid #777777;border-top: 1px solid #FFFFFF;display: block;">
									<h5 style="margin: 0;padding: 0;font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif;line-height: 1.1;margin-bottom: 0!important;color: #000;font-weight: 900;font-size: 17px;"><?php echo $sidebar_header; ?></h5>
									<?php echo $sidebar_subheader; ?>
								</a>
							</li>
							<?php

							if ( @have_rows('sidebar_sections') ) {

								while( have_rows('sidebar_sections') ){
									the_row();

									$section_link_text = get_sub_field('section_link_text');
									$section_link_url = get_sub_field('section_link_url');
							?>
								<li style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-left: 5px;list-style-position: inside;display: block;">
									<a href="<?php echo $section_link_url; ?>" class="" style="margin: 0;padding: 10px 16px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #666;text-decoration: none;margin-right: 10px;cursor: pointer;border-bottom: 1px solid #777777;border-top: 1px solid #FFFFFF;display: block;"><?php echo $section_link_text; ?></a>
								</li>
							<?php
								}
							}
							?>

						</ul>

						<!-- social & contact -->
						<table class="social" width="100%" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;background-color: <?php echo $footer_bg_color;?> ;width: 100%;">
							<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
								<td style="margin: 0;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
									<h6 class="" style="margin: 0;padding: 0;font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif;line-height: 1.1;margin-bottom: 15px;color: #444;font-weight: 900;font-size: 14px;text-transform: uppercase;">Connect with Us:</h6>
									<p class="" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-bottom: 10px;font-weight: normal;font-size: 14px;line-height: 1.6;">
										<?php if ($facebook_page_url) { ?>
											<a href="<?php echo $facebook_page_url; ?>" class="soc-btn fb" style="margin: 0;padding: 3px 7px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #FFF;font-size: 12px;margin-bottom: 10px;text-decoration: none;font-weight: bold;display: block;text-align: center;background-color: #3B5998!important;">Facebook</a>
										<?php } ?>
										<?php if ($twitter_handle) { ?>
											<a href="<?php echo $twitter_handle; ?>" class="soc-btn tw" style="margin: 0;padding: 3px 7px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #FFF;font-size: 12px;margin-bottom: 10px;text-decoration: none;font-weight: bold;display: block;text-align: center;background-color: #1daced!important;">Twitter</a>
										<?php } ?>
										<?php if ($google_plus_url) { ?>
											<a href="<?php echo $google_plus_url; ?>" class="soc-btn gp" style="margin: 0;padding: 3px 7px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #FFF;font-size: 12px;margin-bottom: 10px;text-decoration: none;font-weight: bold;display: block;text-align: center;background-color: #DB4A39!important;">Google+</a>
										<?php } ?>
									</p>

									<?php if ( $phone_number || $email ) { ?>
										<h6 class="" style="margin: 0;padding: 0;font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif;line-height: 1.1;margin-bottom: 15px;color: #444;font-weight: 900;font-size: 14px;text-transform: uppercase;">Contact Info:</h6>
										<?php if ( $phone_number ) { ?>
											<p style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-bottom: 10px;font-weight: normal;font-size: 14px;line-height: 1.6;">Phone: <strong style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"><?php echo $phone_number; ?></strong><br/>
										<?php } ?>
										<?php if ( $email ) { ?>
											Email: <strong><a href="emailto:<?php echo $email; ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #2BA6CB;"><?php echo $email; ?></a></strong></p>
										<?php } ?>
									<?php } ?>
								</td>
							</tr>
						</table><!-- /social & contact -->

					</td>
				</tr>
			</table>
			</div>

				<div class="clear" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;display: block;clear: both;"></div>

			</div>

		</td>
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
	</tr>
</table><!-- /BODY -->

<!-- FOOTER -->
<table class="footer-wrap" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;clear: both!important;">
	<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
		<td class="container" style="margin: 0 auto!important;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;display: block!important;max-width: 600px!important;clear: both!important;">

			<!-- content -->
			<div class="content" style="margin: 0 auto;padding: 15px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;max-width: 600px;display: block;">
				<table style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;width: 100%;">
				<tr style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
					<td align="center" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">
						<p style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;margin-bottom: 10px;font-weight: normal;font-size: 14px;line-height: 1.6;">
							<?php if ( $terms_page_url ) { ?>
								<a href="<?php echo $terms_page_url; ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #2BA6CB;">Terms</a> |
							<?php } ?>
							<?php if ( $privacy_page_url ) { ?>
								<a href="<?php echo $privacy_page_url; ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #2BA6CB;">Privacy</a> |
							<?php } ?>
							<a href="<?php echo do_shortcode('[unsubscribe-link]'); ?>" style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #2BA6CB;"><?php _e('Unsubscribe from this list' , 'inbound-mailer' ); ?></a>
						</p>
					</td>
				</tr>
				</table>
			</div><!-- /content -->

		</td>
		<td style="margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"></td>
	</tr>
</table><!-- /FOOTER -->

</body>
</html>

<?php

endwhile; endif;