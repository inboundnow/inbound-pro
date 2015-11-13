<?php
/**
* Template Name: Photographer
* @package  Inbound Email
* @author   Inbound Now
*/

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* discover the absolute path of where this template is located. Core templates are loacted in /wp-content/plugins/inbound-mailer/templates/ while custom templates belong in /wp-content/uploads/inbound-email/templates/ */
$path = (preg_match("/uploads/", dirname(__FILE__))) ? INBOUND_EMAIL_UPLOADS_URLPATH . $key .'/' : INBOUND_EMAIL_URLPATH.'templates/'.$key.'/';

$urlpath = (preg_match("/uploads/", dirname(__FILE__))) ? INBOUND_EMAIL_UPLOADS_URLPATH . $key .'/' : INBOUND_EMAIL_URLPATH.'templates/'.$key.'/';

/* Include ACF Field Definitions  */
//include_once($path .'config.php');

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

$post_id		  = get_the_ID();

/* Header */
$logo_image		  = get_field("logo_image", $post_id);
$header_bg_color  = get_field("header_bg_color", $post_id);
$header_bg_image  = get_field("header_bg_image", $post_id);
$issue_date		  = get_field("issue_date", $post_id);
$issue_date_color = get_field("issue_date_color", $post_id);
$home_page_url	  = get_field("home_page_url", $post_id);


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta content="telephone=no" name="format-detection" />
	<title>Photographer Template</title>

	<style type="text/css" media="screen">
		/* Linked Styles */
		body { padding:0 !important; margin:0 !important; display:block !important; -webkit-text-size-adjust:none; background-image:url(<?php echo $urlpath . 'assets/images/bg.jpg'; ?>); background-position:0 0; background-repeat:no-repeat repeat-y }
		a { color:#51a17d; text-decoration:none }
		h2 a, .h2 a { color:#7c7c7c; text-decoration:none }
		.footer a { color:#999999; text-decoration:underline }

		/* Campaign Monitor wraps the text in editor in paragraphs. In order to preserve design spacing we remove the padding/margin */
		p { padding:0 !important; margin:0 !important }
	</style>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"></link>
</head>
<body class="body" style="padding:0 !important; margin:0 !important; display:block !important; -webkit-text-size-adjust:none; background-image:url(<?php echo $urlpath . 'assets/images/bg.jpg'; ?>); background-position:0 0; background-repeat:no-repeat repeat-y">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-image: url(<?php echo $urlpath . 'assets/images/bg.jpg'; ?>); background-position: 0 0; background-repeat: no-repeat repeat-y;">
	<tr>
		<td align="center" valign="top">
			<!-- Top -->
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #121212; ">
				<tr class="view-online">
					<td>
						<table width="100%">
							<tr>
								<td width="100"></td>
								<td class="viewWebsite" align="center" height="60" valign="middle">
									<p style="font-family: Arial, Helvetica, sans-serif; color: #555555; font-size: 12px; padding: 0; margin: 0;">Trouble viewing? Read this <a href="<?php echo get_permalink( $post_id ); ?>" style="color: #990000;" class='do-not-tracks'><?php _e('online' , 'inbound-email' ); ?></a>.</p>
								</td>
								<td align="right" height="60" valign="middle" width="100"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<div style="font-size:0pt; line-height:0pt; height:1px; background:#000000; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

			<div style="font-size:0pt; line-height:0pt; height:1px; background:#3d3e3e; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

			<!-- END END
			<div style="font-size:0pt; line-height:0pt; height:30px"><img src="<?php //echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="30" style="height:30px" alt="" /></div> -->


			<!-- Header -->
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center">
						<table width="620" border="0" cellspacing="0" cellpadding="0" style="padding:30px 10px; <?php ! $header_bg_image ? '' :'background-image: url(' . $header_bg_image . '); background-size: cover;'; ?> background-color:<?php echo $header_bg_color; ?>; ">
							<tr>
								<td class="img" style="max-width:274px; font-size:0pt; line-height:0pt; text-align:left"><a href="<?php echo $home_page_url; ?>" target="_blank"><img src="<?php echo $logo_image; ?>" alt="" border="0" /></a></td>
								<td class="date" style="color:<?php echo $issue_date_color; ?>; font-family:'Trebuchet MS'; font-size:17px; line-height:21px; text-align:right"><?php echo $issue_date; ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- END Header -->
			<div style="font-size:0pt; line-height:0pt; height:20px"></div>

<?php
			/* Start featured_area Flexible Content Area Output */
				if(function_exists('have_rows')) :
					if(have_rows('featured_area')) :
						while(have_rows('featured_area')) : the_row();
							switch(get_sub_field('acf_fc_layout')) :
							/* start layout featured_content */
							case 'featured_content' :
								$featured_image			= get_sub_field("featured_image");
								$cta_side				= get_sub_field("cta_side");
								$cta_bg_color			= get_sub_field("cta_bg_color");
								$cta_border_color		= get_sub_field("cta_border_color");
								$cta_title				= get_sub_field("cta_title");
								$cta_text				= get_sub_field("cta_text");
								$cta_text_color			= get_sub_field("cta_text_color");
								$cta_button_text		= get_sub_field("cta_button_text");
								$cta_button_text_color	= get_sub_field("cta_button_text_color");
								$cta_button_color		= get_sub_field("cta_button_color");
								$cta_button_url			= get_sub_field("cta_button_url");
						?>

								<!-- Featured Content -->
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td align="center">
											<?php
											if ( ! $featured_image ) {
												?>
												<table border="0" cellspacing="20" cellpadding="0" style="font-family: 'Trebuchet MS'; line-height:21px; width:620px; height:300px; background-image: url(<?php echo $urlpath . 'assets/images/featured.jpg'; ?>); background-size: cover;">
											<?php } else { ?>
												<table border="0" cellspacing="20" cellpadding="0" style="font-family: 'Trebuchet MS'; line-height:21px; width:620px; height:300px; background-image: url(<?php echo $featured_image; ?>); background-size: cover;">
											<?php } ?>
												<tr>
													<?php
													if ( 'Left' == $cta_side ) {
														?>
														<td style="border: 2px solid <?php echo $cta_border_color; ?>; width:240px; background-color:<?php echo $cta_bg_color; ?>; color:<?php echo $cta_text_color; ?>; text-align:left; padding:20px;" >
															<div>
															<h2 style="color:<?php echo $cta_text_color; ?>;"><?php echo $cta_title; ?></h2>
															<p style="font-size: 14px;"><?php echo $cta_text; ?></p></br>
															<a style="padding:8px 20px; border:none; text-decoration: none; color:<?php echo $cta_button_text_color; ?>; background-color:<?php echo $cta_button_color; ?>" href="<?php echo $cta_button_url; ?>" target="_blank"><?php echo $cta_button_text; ?></a>
															</div>
														</td>
													<?php } ?>
													<td></td>
													<?php
													if ( 'Right' == $cta_side ) {
														?>
														<td style="border: 2px solid <?php echo $cta_border_color; ?>; width:240px; background-color:<?php echo $cta_bg_color; ?>; color:<?php echo $cta_text_color; ?>; text-align:left; padding:20px;">
															<div>
															<h3 style="color:<?php echo $cta_text_color; ?>;"><?php echo $cta_title; ?></h3>
															<p style="font-size: 14px;"><?php echo $cta_text; ?></p></br>
															<a style="padding:8px 20px; border:none; text-decoration: none; color:<?php echo $cta_button_text_color; ?>; background-color:<?php echo $cta_button_color; ?>" href="<?php echo $cta_button_url; ?>" target="_blank"><?php echo $cta_button_text; ?></a>
															</div>
														</td>
													<?php } ?>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<!-- END Featured Content -->

						<?php break;
							endswitch; /* end switch statement */
						endwhile; /* end while statement */
					 endif; /* end have_rows */
				endif;  /* end function_exists */
			/* End featured_area Flexible Content Area Output */
				?>

			<div style="font-size:0pt; line-height:0pt; height:20px"></div>


			<!-- Content -->
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center">
						<table width="620" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td bgcolor="#ffffff">
									<div class="img" style="font-size:0pt; line-height:0pt; text-align:left"><img src="<?php echo $urlpath . 'assets/images/mainbox_top.jpg'; ?>" alt="" border="0" width="620" height="3" /></div>
									<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="15"></td>
											<td valign="top" width="368" class="text" style="color:#5f5f60; font-family:'Trebuchet MS'; font-size:12px; line-height:16px; text-align:left; background:#ffffff !important">
						<?php
						/* Start content_box Flexible Content Area Output */
							if(function_exists('have_rows')) :
								if(have_rows('content_box')) :
									while(have_rows('content_box')) : the_row();
										switch(get_sub_field('acf_fc_layout')) :
										/* start layout  */
										case 'content_box' : 
											$title		 = get_sub_field("title");
											$main_photo  = get_sub_field("main_photo");
											$thumbnail_1 = get_sub_field("thumbnail_1");
											$thumbnail_2 = get_sub_field("thumbnail_2");
											$thumbnail_3 = get_sub_field("thumbnail_3");
											$thumbnail_4 = get_sub_field("thumbnail_4");
											$description = get_sub_field("description");
											$button_text = get_sub_field("button_text");
											$button_url  = get_sub_field("button_url");
									?>

											
												<div>
													<div class="h2" style="color:#201f1f; font-family:'Trebuchet MS'; font-size:17px; line-height:21px; text-align:left; font-weight:bold; background:#ffffff !important">
														<div><?php echo $title; ?></div>
													</div>
													<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


													<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#6f7070">
														<tr>
															<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																<a href="#" target="_blank">
																	<?php
																	if ( ! $main_photo ) {
																		?>
																		<img src="<?php echo $urlpath . 'assets/images/img1.jpg'; ?>" alt="" border="0" width="366" height="209" />
																	<?php } else { ?>
																		<img src="<?php echo $main_photo; ?>" alt="" border="0" width="366" height="209" />
																	<?php } ?>
																</a>
															</td>
														</tr>
													</table>
													<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td align="left">
																<table width="100%" border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td width="82">
																			<table border="0" cellspacing="0" cellpadding="1" bgcolor="#a8a8a8">
																				<tr>
																					<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																						<a href="#" target="_blank">
																							<?php
																							if ( ! $thumbnail_1 ) {
																								?>
																								<img src="<?php echo $urlpath . 'assets/images/thumb1.jpg'; ?>" alt="" border="0" width="80" height="50" />
																							<?php } else { ?>
																								<img src="<?php echo $thumbnail_1; ?>" alt="" border="0" width="80" height="50" />
																							<?php } ?>
																						</a>
																					</td>
																				</tr>
																			</table>
																		</td>
																		<td width="13"></td>
																		<td width="82">
																			<table border="0" cellspacing="0" cellpadding="1" bgcolor="#a8a8a8">
																				<tr>
																					<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																						<a href="#" target="_blank">
																							<?php
																							if ( ! $thumbnail_2 ) {
																								?>
																								<img src="<?php echo $urlpath . 'assets/images/thumb2.jpg'; ?>" alt="" border="0" width="80" height="50" />
																							<?php } else { ?>
																								<img src="<?php echo $thumbnail_2; ?>" alt="" border="0" width="80" height="50" />
																							<?php } ?>
																						</a>
																					</td>
																				</tr>
																			</table>
																		</td>
																		<td width="13"></td>
																		<td width="82">
																			<table border="0" cellspacing="0" cellpadding="1" bgcolor="#a8a8a8">
																				<tr>
																					<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																						<a href="#" target="_blank">
																							<?php
																							if ( ! $thumbnail_3 ) {
																								?>
																								<img src="<?php echo $urlpath . 'assets/images/thumb3.jpg'; ?>" alt="" border="0" width="80" height="50" />
																							<?php } else { ?>
																								<img src="<?php echo $thumbnail_3; ?>" alt="" border="0" width="80" height="50" />
																							<?php } ?>
																						</a>
																					</td>
																				</tr>
																			</table>
																		</td>
																		<td width="13"></td>
																		<td width="82">
																			<table border="0" cellspacing="0" cellpadding="1" bgcolor="#a8a8a8">
																				<tr>
																					<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																						<a href="#" target="_blank">
																							<?php
																							if ( ! $thumbnail_4 ) {
																								?>
																								<img src="<?php echo $urlpath . 'assets/images/thumb4.jpg'; ?>" alt="" border="0" width="80" height="50" />
																							<?php } else { ?>
																								<img src="<?php echo $thumbnail_4; ?>" alt="" border="0" width="80" height="50" />
																							<?php } ?>
																						</a>
																					</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
													<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


													<div>
														<?php echo $description; ?>
													</div>
													<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


													<div style="margin-top:5px ;font-size:14px;font-weight: 700; text-align:left">
														<a style="padding:8px 20px; border:none; text-decoration: none; background-color: #A8A8A8; color:white;" href="<?php echo $button_url; ?>" target="_blank"><?php echo $button_text; ?></a>
													</div>
													<div style="font-size:0pt; line-height:0pt; height:30px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="30" style="height:30px" alt="" /></div>

													<div style="font-size:0pt; line-height:0pt; height:1px; background:#bfbfbf; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

													<div style="font-size:0pt; line-height:0pt; height:25px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="25" style="height:25px" alt="" /></div>

												</div>	

											
									<?php break;
										endswitch; /* end switch statement */ 
									endwhile; /* end while statement */
								 endif; /* end have_rows */
							endif;  /* end function_exists */
						/* End content_box Flexible Content Area Output */
						?>
												</td>
											
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="22"></td>
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="1" bgcolor="#bfbfbf"></td>
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="21"></td>
											<td valign="top" class="text" style="color:#5f5f60; font-family:'Trebuchet MS'; font-size:12px; line-height:16px; text-align:left; background:#ffffff !important" width="170">

							<div>												
						<?php
						/* Start sidebar Flexible Content Area Output */
							if(function_exists('have_rows')) :
								if(have_rows('sidebar')) :
									 while(have_rows('sidebar')) : the_row();
										 switch(get_sub_field('acf_fc_layout')) :
										/* start layout sidebar_list */
										 case 'sidebar_list' : 
											$sidebar_title		 = get_sub_field("sidebar_title");
											$sidebar_description = get_sub_field("sidebar_description");
											/* Start sidebar_links Repeater Output */
											?>
											<div class="h2" style="color:#201f1f; font-family:'Trebuchet MS'; font-size:17px; line-height:21px; text-align:left; font-weight:bold; background:#ffffff !important">
												<div><?php echo $sidebar_title; ?></div>
											</div>
											<div style="font-size:0pt; line-height:0pt; height:5px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="5" style="height:5px" alt="" /></div>

											<div><?php echo $sidebar_description; ?></div>
											<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>

											<div style="font-size:0pt; line-height:0pt; height:1px; background:#bfbfbf; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

											<div style="font-size:0pt; line-height:0pt; height:5px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="5" style="height:5px" alt="" /></div>

											<?php
											if ( have_rows( "sidebar_links" ) )  { ?>

												<?php while ( have_rows( "sidebar_links" ) ) : the_row();
														$sidebar_link_title = get_sub_field("sidebar_link_title");
														$sidebar_link_url = get_sub_field("sidebar_link_url");
												?>				
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="8"><img src="<?php echo $urlpath.'assets/images/bullet2.jpg'; ?>" alt="" border="0" width="3" height="6" /></td>
															<td class="text" style="color:#5f5f60; font-family:'Trebuchet MS'; font-size:12px; line-height:16px; text-align:left; background:#ffffff !important">
																<div><a href="<?php echo $sidebar_link_url; ?>" target="_blank" class="link" style="color:#51a17d; text-decoration:none"><span class="link" style="color:#51a17d; text-decoration:none"><?php echo $sidebar_link_title; ?></span></a></div>
															</td>
														</tr>
													</table>
													<div style="font-size:0pt; line-height:0pt; height:7px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="7" style="height:7px" alt="" /></div>

													<div style="font-size:0pt; line-height:0pt; height:1px; background:#bfbfbf; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

													<div style="font-size:0pt; line-height:0pt; height:5px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="5" style="height:5px" alt="" /></div>
			
												

												<?php endwhile; ?>

											<?php } /* end if have_rows(sidebar_links) */
											/* End sidebar_links Repeater Output */
									?>

										</div>

									<?php break;
										/* start layout extra_sidebar_section */
										 case 'extra_sidebar_section' : 
											$extra_section_title = get_sub_field("extra_section_title");
											$extra_photo		 = get_sub_field("extra_photo");
											$extra_content		 = get_sub_field("extra_content");
											$extra_button_text	 = get_sub_field("extra_button_text");
											$extra_button_url	 = get_sub_field("extra_button_url");
									?>

									<div>
										<div class="h2" style="color:#201f1f; font-family:'Trebuchet MS'; font-size:17px; line-height:21px; text-align:left; font-weight:bold; background:#ffffff !important">
											<div><?php echo $extra_section_title; ?></div>
										</div>
										<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


										<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#ececec">
											<tr>
												<td>
													<table width="100%" border="0" cellspacing="0" cellpadding="5" bgcolor="#ffffff">
														<tr>
															<td class="img" style="font-size:0pt; line-height:0pt; text-align:left">
																<a href="#" target="_blank">
																	<?php
																	if ( ! $extra_photo ) {
																		?>
																		<img src="<?php echo $urlpath . 'assets/images/sidebar_img1.jpg'; ?>" alt="" border="0" width="157" height="100" />
																	<?php } else { ?>
																		<img src="<?php echo $extra_photo; ?>" alt="" border="0" width="157" height="100" />
																	<?php } ?>
																</a>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
										<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>


										<div>
											<?php echo $extra_content; ?>
										</div>
										<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>

										<div style="margin-top:5px ;font-size:14px;font-weight: 700; text-align:left">
											<a style="padding:8px 20px; border:none; text-decoration: none; background-color: #A8A8A8; color:white;" href="<?php echo $extra_button_url; ?>" target="_blank"><?php echo $extra_button_text; ?></a>
										</div>
										<div style="font-size:0pt; line-height:0pt; height:25px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="25" style="height:25px" alt="" /></div>

									</div>

									<?php break;
										/* start layout advertising_section */
										 case 'advertising_section' : 
											$advertising_title	= get_sub_field("advertising_title");
											$advertising_banner = get_sub_field("advertising_banner");
											$banner_url			= get_sub_field("banner_url");
									?>

									<div>
										<div class="h2" style="color:#201f1f; font-family:'Trebuchet MS'; font-size:17px; line-height:21px; text-align:left; font-weight:bold; background:#ffffff !important">
											<div><?php echo $advertising_title; ?></div>
										</div>
										<div style="font-size:0pt; line-height:0pt; height:10px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="10" style="height:10px" alt="" /></div>

										<div class="img" style="font-size:0pt; line-height:0pt; text-align:left">
											<a href="<?php echo $banner_url; ?>" target="_blank">
												<?php
												if ( ! $advertising_banner ) {
													?>
													<img src="<?php echo $urlpath . 'assets/images/sidebar_banner.jpg'; ?>" alt="" border="0" width="121" height="452" />
												<?php } else { ?>
													<img src="<?php echo $advertising_banner; ?>" style="max-width: 157px;" alt="" border="0" />
												<?php } ?>
												
											</a>
										</div>
									</div>

									<?php break;
										endswitch; /* end switch statement */ 
									endwhile; /* end while statement */
								 endif; /* end have_rows */
							endif;  /* end function_exists */
						/* End sidebar Flexible Content Area Output */

						?>										
											</td>
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="23"></td>
										</tr>
									</table>
									<div style="font-size:0pt; line-height:0pt; height:30px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="30" style="height:30px" alt="" /></div>

									<div style="font-size:0pt; line-height:0pt; height:1px; background:#bfbfbf; "><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

									<div style="font-size:0pt; line-height:0pt; height:1px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="1" style="height:1px" alt="" /></div>

									<!-- FOOTER SECTION -->
									
									<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f9f9f9">
										<tr>
											<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="25"></td>
											<td>
												<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td align="left">
															<table border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td class="h3" style="color:#51a17d; font-family:'Trebuchet MS'; font-size:16px; line-height:20px; text-align:left; font-weight:bold"><div>Follow Me</div></td>
																	<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="18"></td>
																<?php
																/* Start social_media Repeater Output */
																if ( have_rows( "social_media" ) )  { ?>

																	<?php while ( have_rows( "social_media" ) ) : the_row();
																			$social_media_logo = get_sub_field("social_media_logo");
																			$social_media_name = get_sub_field("social_media_name");
																			$social_profile_url = get_sub_field("social_profile_url");
																	?>

																	<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="25"><div><a href="<?php echo $social_profile_url; ?>" target="_blank" class="link2" style="color:#7c7c7c; text-decoration:none"><i class="fa <?php echo $social_media_logo; ?>" style="font-size:20px;"></i></a></div></td>
																	<td class="social" style="color:#7c7c7c; font-family:'Trebuchet MS'; font-size:11px; line-height:15px; text-align:left; text-transform:uppercase; background:#fafafa !important"><div><a href="<?php echo $social_profile_url; ?>" target="_blank" class="link2" style="color:#7c7c7c; text-decoration:none"><span class="link2" style="color:#7c7c7c; text-decoration:none"><?php echo $social_media_name; ?></span></a></div></td>
																	<td class="img" style="font-size:0pt; line-height:0pt; text-align:left" width="18"></td>

																	<?php endwhile; ?>

																<?php } /* end if have_rows(social_media) */
																/* End social_media Repeater Output */
																
																$your_company_name = get_field("your_company_name", $post_id);
																
																?>								
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

											</td>

										</tr>
									</table>
									<div class="img" style="font-size:0pt; line-height:0pt; text-align:left"><img src="<?php echo $urlpath . 'assets/images/mainbox_bottom.jpg'; ?>" alt="" border="0" width="620" height="3" /></div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- END Content -->
			<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

			<!-- Bottom Content -->
			
			
			<!-- Footer -->
			<table width="100%" border="0" cellspacing="0" cellpadding="0" >
				<tr>
					<td class="footer" style="color:#999999; font-family:Arial; font-size:11px; line-height:17px; text-align:center">
						<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

						<div><div>This newsletter was sent to you from <span><?php echo $your_company_name; ?></span> because you subscribed.</div></div>
						Rather not receive our newsletter anymore? <a class="link3-u" style="color:#999999; text-decoration:underline" target="_blank" href="<?php echo do_shortcode('[unsubscribe-link]'); ?>">Unsubscribe instantly</a>
						<div style="font-size:0pt; line-height:0pt; height:15px"><img src="<?php echo $urlpath . 'assets/images/empty.gif'; ?>" width="1" height="15" style="height:15px" alt="" /></div>

					</td>
				</tr>
			</table>
			<!-- END Footer -->
		</td>
	</tr>
</table>

</body>
</html>

<?php

endwhile; endif;