<?php
/**
* Template Name: Newsletter
* @package  Inbound Email
* @author   Inbound Now
*/

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

/* Header */
$post_id		  = get_the_ID();
$logo_url		  = get_field('header_logo', $post_id);
$header_bg_color  = get_field('header_bg_color', $post_id);
$header_bg_image  = get_field('header_bg_image', $post_id);
$issue_title	  = get_field('issue_title', $post_id);
$issue_date		  = get_field('issue_date', $post_id);
$title_date_color = get_field('title_date_color', $post_id);
$home_page_url	  = get_field('home_page_url', $post_id);

/* Email Body */
$thumbnails_align   = get_field('thumbnails_alignment');
$thumbnails_align == 'left' ? ($img_float = $news_margin = 'left') : ($img_float = $news_margin = 'right');
$thumbnails_align == 'left' ? ($img_margin = 'right') : ($img_margin = 'left');

/* Footer */
$terms_page_url	  = get_field('terms_page_url', $post_id);
$privacy_page_url = get_field('privacy_page_url', $post_id);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta name="viewport" content="width=device-width,initial-scale=1"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Newsletter image right</title>

<style type='css/text'>
  .reverse-row .text-wrap{ float:right; width:512px; }
  .reverse-row .img-wrap{ float:left !important; margin-left:0 !important; margin-right:30px; }
  .reverse-row .pcont-text { margin-right:0 !important;}

  @media screen and (max-width: 480px), only screen and (device-aspect-ratio: 40/71), only screen and (device-aspect-ratio: 2/3)

   {
      .logo-cell-with-bg { background-position:-300px center; }
      .logo-cell { display:none;}
      #header-cell { margin-bottom:20px; margin-top:0;}

      .img-wrap { float:none !important; margin:0 0 10px !important; text-align:center; display:block;}
      .img-wrap a { display:inline-block; width:100%;}

    .gap-cell { width:14px !important;}
    .post-cell { background:#fff; padding:19px 19px 10px; border:1px solid #ccc;
    border-radius:6px;
    -webkit-border-radius:6px;
    display:block; clear:both;
    }
    .post-cell h1 { font-size:20px !important; line-height:25px !important;}
    .post-cell p { font-size:14px; line-height:21px; }
    .post-cell img{ width:100%; max-width:100%; height:auto;}

    .pcont-text { margin-right:0px !important;}

    .top-unsub-txt,
    .title-cell, .bottom-gap , .shd-cell, .sec-separator{ display:none;}
    .hhs-block { width:100% !important;}

      .nl-issue-row table, .nl-issue-row td{ width:100% !important; background:inherit;}
      .issue-cell span { font-weight:bold; font-size:14px !important; padding-right:10px; }

    .header-table, .content-table { width:100% !important; max-width:100%;}
    .content-table { background:#dfdfdf;}

    .art-img-row { display:none;}
    .art-img-row td{ height:auto;}
    .art-img-row img { width:100% !important; height:auto !important;}
    .bottom-space { height:14px !important;}

    .fb-img-lnk img, .rss-img-lnk img, .tw-img-lnk img{display:none;}


    .footer-row br { display:none;}
    .footer-row * { font-weight:normal !important;}
    .footer-row *, .footer-row td{ font-size:4px !important; }
    .text-chunk-hide { display:none;}
    .view-online { display:none;}
    .header-cell-table { margin-top:20px;}
    .footer-support-row { display:none; }

    table  {max-width:100%; width:100%;}


  }
</style>

<?php do_action('inbound-mailer/email/header'); ?>
</head>

<body style="padding:0; margin:0;">

<table style="width:100%;float:left;background-color:#dfdfdf;font-family:Arial, sans serif;">

	<tr>
		<td align="center">

			<table cellspacing="0" cellpadding="0" border="0">

				<tr class="view-online">
					<td>
						<table width="100%">
							<tr>
								<td width="100"></td>
								<td class="viewWebsite" align="center" height="60" valign="middle">
									<p style="font-family: Arial, Helvetica, sans-serif; color: #555555; font-size: 10px; padding: 0; margin: 0;">Trouble viewing? Read this <a href="<?php echo get_permalink( $post_id ); ?>" style="color: #990000;" class='do-not-tracks'><?php _e('online' , 'inbound-email' ); ?></a>.</p>
								</td>
								<td align="right" height="60" valign="middle" width="100"></td>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td  width="730" class="header-table">
						<!-- header-content table -->
						<table style="width:730px; border:0;" class="header-table header-cell-table" id="header-cell" cellspacing="0" cellpadding="0" >

							<tr class="image-row">
								<td class="logo-cell-with-bg" style="background-color:<?php echo $header_bg_color ?>; background-image:url(<?php echo $header_bg_image ?>); height:100px; vertical-align:middle;">

									<table style="width:100%; vertical-align:middle;" cellspacing="0" cellpadding="0">
										<tr>
											<td width="2%" class="title-cell"></td>

											<td class="title-cell" width="30%" align="left" style="white-space:nowrap;font-size:13px; font-weight:bold;font-family:Arial, sans serif;color:<?php echo $title_date_color ?>; ">
												<b><span><?php echo $issue_title ?></span></b>
											</td>
											<td width="10"></td>

											<td style="margin:auto" class="logo-cell">
												<?php if ($logo_url) { ?>
													<div style="margin:auto;width:200px">
														<a target="_blank" style="display:inline-block;" href="<?php  echo $home_page_url; ?>">
														<img style="border:0;width:200px" alt="" src="<?php  echo $logo_url; ?>"/>
														</a>
													</div>
												<?php } ?>
											</td>

											<td class="issue-cell" align="right"  style="width:30%; color:<?php echo $title_date_color ?>; white-space:nowrap; font-size:13px; font-weight:bold;font-family:Arial, sans serif;">
												<strong><?php echo $issue_date ?></strong>
											</td>

											<td width="2%"></td>

										</tr>
									</table>

								</td>
							</tr>

						</table>
						<!-- content-table -->
						<table width="730" class="content-table" cellspacing="0" cellpadding="0" bgcolor="#ffffff">

							<?php
							if ( function_exists('have_rows') ) {
								if (have_rows('news_line')) {
									while ( have_rows('news_line')) {
										the_row();

										switch( $layout = get_sub_field('acf_fc_layout') ) {
											case 'news_line':
												$news_title			= get_sub_field('news_title');
												$news_url			= get_sub_field('news_url');
												$news_excerpt		= get_sub_field('news_excerpt');
												$featured_image		= get_sub_field('featured_image');
												$featured_image_url = get_sub_field('featured_image_url');
												?>
												<!-- top gap -->
												<tr class="sec-separator"><td height="18"></td></tr>
												<!-- entry content cell -->
												<tr>
													<td>
													<table width="100%" cellspacing="0" cellpadding="0" >
														<tr>
															<td width="30" class="gap-cell"></td>
															<td class="post-cell" valign="top" style="color:#333333; font-size:14px; line-height:20px;font-family:Arial, sans serif;">
																<div class="img-wrap" style="float:<?php echo $img_float ?>; margin-<?php echo $img_margin ?>:30px;">
																	<a target="_blank" href="<?php echo $featured_image_url ?>">
																		<img style="margin-top:4px;" width="150" border="0" height="150" alt="" align="right" src="<?php echo $featured_image ?>" />
																	</a>
																</div>
																<h1 style='font-family:"HelveticaNeueBold", "HelveticaNeue-Bold", "Helvetica Neue Bold", helvetica, arial, sans serif;color:#201f1f; font-size:22px; font-weight:bold; line-height:25px; margin:0 0 -10px 0;'>
																	<a style='text-decoration: none;font-family:"HelveticaNeueBold", "HelveticaNeue-Bold", "Helvetica Neue Bold", helvetica, arial, sans serif;color:#201f1f; font-size:22px; font-weight:bold; line-height:25px; margin:0 0 -10px 0;' target="_blank" href="<?php echo $news_url ?>"><?php echo $news_title ?></a>
																</h1>
																<div style="margin-<?php echo $news_margin ?>:140px;" class="pcont-text">
																	<p><?php echo $news_excerpt ?></p>
																</div>
															</td>
															<td width="30" class="gap-cell"></td>
														</tr>
													</table>
													</td>
												</tr>
												<!-- bottom space -->
												<tr class="bottom-space"><td height="18"></td></tr>
												<?php
												break;

											case 'callout':
												$callout_text     = get_sub_field('callout_text');
												$callout_bg_color = get_sub_field('callout_bg_color');
												$bg_color_dec	  = hexdec(substr($callout_bg_color, 1));
												$border_color	  = dechex($bg_color_dec - 1184274);
												?>
												<tr class="sec-separator"><td height="30"></td></tr>
												<tr>
													<td>
														<table width="100%" cellpadding="10" cellspacing="0">
															<tr>
																<td width="10"></td>
																<td bgcolor="<?php echo $callout_bg_color; ?>" align="center" style="font-size:15px; border:1px solid #<?php echo $border_color; ?>; color:#000; font-weight:bold; font-style:italic;"><?php echo $callout_text; ?></td>
																<td width="10"></td>
															</tr>
														</table>
													</td>
												</tr>
												<!-- bottom space -->
												<tr class="bottom-space"><td height="18"></td></tr>
												<?php
												break;
										}
										?>
										<!-- separator -->
										<tr class="sec-separator">
											<td>
												<table width="100%" cellspacing="0" cellpadding="0">
													<tr>
														<td bgcolor="#eeeeee" height="1" ></td>
													</tr>
												</table>
											</td>
										</tr>
										<?php
									}
								}

								if(!have_rows('news_line')) {
									echo '<div class="container">';
									the_content();
									echo "</div>";
								}
							}
							?>

							<tr class="sec-separator"><td height="60"></td></tr>

							<tr bgcolor="#dfdfdf"><td height="25"></td></tr>

							<tr class="footer-row"  bgcolor="#dfdfdf">
							  <td align="center" style="font-family:Arial, sans serif; font-size:12px; font-weight:bold; color:#666;">
								<?php if ( $terms_page_url ) { ?>
									<a href="<?php echo $terms_page_url; ?>" style="color:#666;text-decoration:none;margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">Terms</a>&nbsp; | &nbsp;
								<?php } ?>
								<?php if ( $privacy_page_url ) { ?>
									<a href="<?php echo $privacy_page_url; ?>" style="color:#666;text-decoration:none;margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;">Privacy</a>&nbsp; | &nbsp;
								<?php } ?>
								<a href="<?php echo do_shortcode('[unsubscribe-link]'); ?>" style="color:#666;text-decoration:none;margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"><?php _e('Unsubscribe from this list' , 'inbound-mailer' ); ?></a>
							  </td>
							</tr>

						</table> <!-- end of content table -->
					</td>

				</tr>

			</table>

		</td> <!-- main inner cell -->
	</tr>

	<tr class="bottom-gap"><td height="30"></td></tr>

</table>
<?php do_action('inbound-mailer/email/footer'); ?>
</body>
</html>

<?php

endwhile; endif;