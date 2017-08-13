<?php
/**
* Template Name: Simple Responsive
* @package  Mailer
* @author   Inbound Now
*/

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

$post_id = get_the_ID();
$logo_url = get_field('logo_url', $post_id);
$main_content = get_field('main_email_content', $post_id);
$unsubscribe_link_text = get_field('unsubscribe_link_text', $post_id);
$online_version_link = get_field('hide_show_email_in_browser', $post_id);
$unsubscribe_link_text = ($unsubscribe_link_text) ? $unsubscribe_link_text : __('Unsubscribe','inbound-pro');

/**
$settings = Inbound_Email_Meta::get_settings( $post_id );
print_r($settings);exit;
/**/

//echo $main_content;
//exit;
?>
<style type='css/text'>
@media only screen and (max-width : 640px) {

	table[class="container"] {
        width: 98% !important;
    }


    [class="topDiv"] img {
	    width: 95% !important;
    }

    td[class="spacer"] {
	    display: none !important;
    }
    td[class="belowFeature"] {
	    width: 95% !important;
	    display: inline-block;
	    padding-left: 15px;
	    margin-bottom: 20px;
    }
    td[class="belowFeature"] img {
	    float: left;
	    margin-right: 15px;
    }

    td[class="featuredImage"] {
	    width: 95% !important;
    }
    td[class="featuredImage"] img {
	    width: 100% !important;
    }

    [class="bottomImages"] img {
	    width: 48% !important;
    }


}

@media only screen and (min-width: 481px) and (max-width: 570px) {

	td[class="Logo"] {
		width: 560px !important;
		text-align: center;
	}

	td[class="view-in-browser"] {
		width: 560px !important;
		height: inherit !important;
		text-align: center;
	}

	td[class="view-in-browser"] {
		display: none;
	}


}

@media only screen and (min-width: 250px) and (max-width: 480px) {

	td[class="Logo"] {
		width: 480px !important;
		text-align: center;
	}

	td[class="view-in-browser"] {
		display: none;
	}
	td[class="topDiv"] {
		display: none;
	}
	td[class="bodyCopy"] p {
		padding: 0 15px !important;
		text-align: left !important;
	}

	td[class="bodyCopy"] h1 {
		padding: 0 10px !important;
	}

	h1, h2 {
		line-height: 120% !important;
	}

	[class="imageWrapper"] {
		display: inline-block;
	}
	td[class="belowFeature"] p, td[class="belowFeature"] h3 {
		padding-right: 15px;
	}

	[class="bottomImages"] {
		text-align: center !important;
	}

	[class="bottomImages"] img {
		width: 275px !important;
		margin-bottom: 15px;
		float: none;
	}

}
</style>
<?php
	do_action('mailer/email/header');
?>
<body bgcolor="#f6f6f6" style="font-family: Arial; background-color: #f6f6f6;">

<table style="max-width:630px;" align="center" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table style="width:100%;" class="container" align="center" cellpadding="0" cellspacing="0">
				<tr>
					<td width="188" class="Logo">
						<?php if ($logo_url) { ?>
						<img src="<?php  echo $logo_url; ?>" width='188'>
						<?php } ?>
					</td>

					<td height="70" class="view-in-browser" style="text-align:right;">
						<?php
						if (isset($online_version_link[0]) && $online_version_link[0] =='hide') {

						} else {
							?>
							<p style="font-family: Arial, Helvetica, sans-serif; color: #555555; font-size: 10px; padding: 0; margin: 0;"><?php echo sprintf( __( 'Trouble viewing? Read this %sonline%s' , 'inbound-pro' ) , '<a href="'.get_permalink( $post_id ).'" style="color: #990000;" class="do-not-tracks">' , '</a>'); ?>.</p>
							<?php
						}
						?>
						</td>
				</tr>
			</table>

			<table bgcolor="#fcfcfc" style="border: 1px solid #dddddd; line-height: 135%;" class="container" align="center" cellpadding="0" cellspacing="0">
				<tr>
					<td bgcolor="#fcfcfc" colspan="3" width="100%" height="10">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" height="15">&nbsp;</td>
				</tr>
				<tr>
					<td style="width:3%;"></td>
					<td width="600" class="bottomImages">
						<?php echo $main_content; ?>
					</td>
					<td style="width:2%;"></td>
				</tr>
				<tr>
					<td colspan="3" height="3">&nbsp;</td>
				</tr>
				<tr>
					<td bgcolor="#fcfcfc" colspan="3" width="100%" height="10">&nbsp;</td>
				</tr>
			</table>

			<table bgcolor="" style="line-height: 135%;text-align:center;font-size:10px;padding:10px;" class="container" align="center" cellpadding="0" cellspacing="0">
				<tr>
					<td bgcolor="" colspan="3" width="100%" height="10"><a href="<?php echo do_shortcode('[unsubscribe-link]'); ?>"><?php echo $unsubscribe_link_text; ?></a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php do_action('mailer/email/footer'); ?>
</body>
<?php

endwhile; endif;