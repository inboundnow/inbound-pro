<?php
/*
Plugin Name: Leads Revisit Noticiations
Plugin URI: http://www.inboundnow.com/leads/
Description: Immediately get notified when a lead revisits your site via email. Close more deals with faster follow up times
Author: Inbound Now
Version: 1.3.4
Author URI: http://www.inboundnow.com/
Text Domain: landing-pages
Domain Path: shared/languages/leads/
*/

/**
 * Lead_Revisit_Notifications Class
 */

if (!class_exists('Lead_Revisit_Notifications')) {
class Lead_Revisit_Notifications {
	static $run_addon;

	static function init() {
		self::$run_addon = true;
		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_head', array(__CLASS__, 'print_script'));
		add_action('wp_ajax_lead_revisit_notifications', array(__CLASS__, 'lead_revisit_notifications' ));
		add_action('wp_ajax_nopriv_lead_revisit_notifications', array(__CLASS__, 'lead_revisit_notifications' ));
		/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
		add_filter('lp_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
		add_filter('wpleads_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
		add_filter('wp_cta_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
	}

	static function register_script() {
		wp_register_script('lead-revist', plugins_url('js/lead-revist.js', __FILE__), array('jquery'), '1.0', true);
	}


	static function inboundnow_getresponse_add_global_settings($global_settings) {

		$global_settings['wpl-main']['settings'][] =
			array(
				'id'  => 'inboundnow_header_getresponse',
				'type'  => 'header',
				'default'  => __('<h4>Lead Revisit Notification Settings</h4>', 'inbound-now'),
				'options' => null
			);

		$global_settings['wpl-main']['settings'][] =
				array(
					'id'  => 'inboundnow_lead_revisit_emails',
					'option_name'  => 'inboundnow_lead_revisit_notify_email',
					'label' => 'Email to notify on lead revisit',
					'description' => "Enter in the email(s) you want to notify on lead revists. Comma seperated values",
					'type'  => 'text',
					'default'  => ''
				);

		return $global_settings;
	}

	static function print_script() {
		if ( ! self::$run_addon ) {
			return;
		} ?>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
		   var lead_email = $.cookie('wp_lead_email') || false;
		   var run_check = $.cookie('lead_revisit_expire');

			   if (lead_email && run_check != 'true') {
				   	jQuery.ajax({
				   	    type: "POST",
				   	    url: inbound_ajax.admin_url,
				   	    data: {
				   	    	lead_email: lead_email,
				   	        action: "lead_revisit_notifications"
				   	    },
				   	    success: function (e) {
				   	       console.log('Lead Revisit Notification Sent');
				   	    }
				   	});
			   } else {
			   	/* lead revisit cookie */

			   }
			   /* Set timeout */
			   var e_date = new Date(); // Current date/time
			   var e_minutes = 30; // 30 minute timeout to reset sessions
			   e_date.setTime(e_date.getTime() + (e_minutes * 60 * 1000)); // Calc 30 minutes from now
			   jQuery.cookie("lead_revisit_expire", true, {expires: e_date, path: '/' }); // Set cookie on page loads

		 });
		</script>

	<?php

	}
	static function addon_options(){

	}
	static function route_lead($lead_email){
		// Route lead email to correct person
	}
	/* Ajax call */
	public function lead_revisit_notifications() {
		global $wp, $post;
		$email = (isset($_POST['lead_email'])) ? $_POST['lead_email'] : false;
		$admin_email = get_option( 'admin_email' );
		$notify = get_option( 'inboundnow_lead_revisit_notify_email', $default = $admin_email );
		// Only proceed if lead exists

		if ( ( isset( $email ) && !empty( $email ) && strstr( $email ,'@') )) {
			$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
				$email
			);
			$wpdb->query( $query );
			// if lead exists in DB
			if ( $wpdb->num_rows ) {
				$lead_data = array();
				/* Update Existing Lead */
				$lead_data['email'] = $email;
				$lead_id = $wpdb->get_var( $query );
				// do meta lookup for correct email to send to
				$first_name = get_post_meta( $lead_id, 'wpleads_first_name', true );
				if (!empty( $first_name )) {
					$lead_data['first_name'] = $first_name;
				}
				$last_name = get_post_meta( $lead_id, 'wpleads_last_name', true );
				if (!empty($last_name)) {
					$lead_data['last_name'] = $first_name;
				}
				$work_phone = get_post_meta( $lead_id, 'wpleads_work_phone', true );
				if (!empty($work_phone)) {
					$lead_data['work_phone'] = $first_name;
				}
				$mobile_phone = get_post_meta( $lead_id, 'wpleads_mobile_phone', true );
				if (!empty($mobile_phone)) {
					$lead_data['mobile_phone'] = $first_name;
				}
				$company_name = get_post_meta( $lead_id, 'wpleads_company_name', true );
				if (!empty($company_name)) {
					$lead_data['company_name'] = $first_name;
				}
				$link_to_lead = admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $email );

				$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
				$data_time = date('F jS, Y \a\t g:ia', $time);
				$lead_data['date'] = $data_time;
			}

			$to = $notify;
			$subject = 'Lead Revisit Notification';
			$message = self::email_content($lead_data);

			$mail = wp_mail($to, $subject, $message);

			if($mail) {
				echo 'Your message has been sent!';
			} else {
				echo 'There was a problem sending your message. Please try again.';
			}

		}
	}

	static function email_content($lead_data) {
		$form_email = $lead_data['email'];
		$email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		  <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
		<style type="text/css">
		  html {
		    background: #EEEDED;
		  }
		</style>
		</head>
		<body style="margin: 0px; background-color: #FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#FFFFFF" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">

		<table cellpadding="0" width="600" bgcolor="#FFFFFF" cellspacing="0" border="0" align="center" style="width:100%!important;line-height:100%!important;border-collapse:collapse;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
		  <tbody><tr>
		    <td valign="top" height="20">&nbsp;</td>
		  </tr>
		  <tr>
		    <td valign="top">
		      <table cellpadding="0" bgcolor="#ffffff" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px;font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;border-radius:3px;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
		  <tbody><tr>
		    <td valign="top">
		        <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;width:100%;border-radius:3px 3px 0 0;font-size:1px;line-height:3px;height:3px;border-top-color:#0298e3;border-right-color:#0298e3;border-bottom-color:#0298e3;border-left-color:#0298e3;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-width:1px;border-right-width:1px;border-bottom-width:1px;border-left-width:1px">
		          <tbody><tr>
		            <td valign="top" style="font-family:Arial,sans-serif;background-color:#5ab8e7;border-top-width:1px;border-top-color:#8ccae9;border-top-style:solid" bgcolor="#5ab8e7">&nbsp;</td>
		          </tr>
		        </tbody></table>
		      <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;width:600px;border-radius:0 0 3px 3px;border-top-color:#8c8c8c;border-right-color:#8c8c8c;border-bottom-color:#8c8c8c;border-left-color:#8c8c8c;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-width:0;border-right-width:1px;border-bottom-width:1px;border-left-width:1px">
		        <tbody><tr>
		          <td valign="top" style="font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;border-radius:0 0 3px 3px;padding-top:3px;padding-right:30px;padding-bottom:15px;padding-left:30px">

		  <h1 style="margin-top:20px;margin-right:0;margin-bottom:20px;margin-left:0; font-size:28px; line-height: 28px; color:#000;">Lead Revisit Notification '.$lead_data['email'].'</h1>
		  <p style="margin-top:20px;margin-right:0;margin-bottom:20px;margin-left:0">
		  A lead just revisited your site on <strong>'.$lead_data['date'].'</strong>. Get in touch with them ASAP.
		  </p>
		<!-- NEW TABLE -->
		<table class="heavyTable" style="width: 100%;
		    max-width: 600px;
		    border-collapse: collapse;
		    border: 1px solid #cccccc;
		    background: white;
		   margin-bottom: 20px;">
		   <tbody>
		     <tr style="background: #3A9FD1; height: 54px; font-weight: lighter; color: #fff;border: 1px solid #3A9FD1;text-align: left; padding-left: 10px;">
		             <td  align="left" width="600" style="-webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;">
		              <h1 style="font-size: 30px; display: inline-block;margin-top: 15px;margin-left: 10px; margin-bottom: 0px; letter-spacing: 0px; word-spacing: 0px; font-weight: 300;">Lead Information</h1>
		              <div style="float:right; margin-top: 5px; margin-right: 15px;"><!--[if mso]>
		                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email ).'" style="height:40px;v-text-anchor:middle;width:130px;font-size:18px;" arcsize="10%" stroke="f" fillcolor="#ffffff">
		                  <w:anchorlock/>
		                  <center>
		                <![endif]-->
		                    <a href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email ).'"
		              style="background-color:#ffffff;border-radius:4px;color:#3A9FD1;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:130px;-webkit-text-size-adjust:none;">View Lead</a>
		                <!--[if mso]>
		                  </center>
		                </v:roundrect>
		              <![endif]-->
		              </div>
		             </td>
		     </tr>';
		// working!
		     $exclude_array = array('Inbound Redirect', 'Inbound Submitted', 'Inbound Notify', 'Inbound Parent Page', 'Send', 'Inbound Furl' );

		     $main_count = 0;
		     $url_request = "";

		     foreach ($lead_data as $key => $value)
		     {
		     	//array_push($action_categories, $ctaw_cat->category_nicename);
		     	$urlparam = ($main_count < 1 ) ?  "?" : "&";
		     	$url_request .= $urlparam . $key . "=" . urlencode($value);
		     	$name = str_replace(array('-','_'),' ', $key);
		     	$name = ucwords($name);


		     	$field_data = ($lead_data[$key] != "") ? $lead_data[$key] : "<span style='color:#949494; font-size: 10px;'>(Field left blank)</span>";

		     	if(!in_array($name, $exclude_array)) {
		     	$email_message .= '
				<tr style="border-bottom: 1px solid #cccccc;">
				<td width="600" style="border-right: 1px solid #cccccc; padding: 10px; padding-bottom: 5px;">
				<div style="padding-left:5px; display:inline-block; padding-bottom: 5px; font-size: 16px; color:#555;">
		     	<strong>'.$name.':</strong></div>
		     	<div style="padding-left:5px; display:inline-block; font-size: 14px; color:#000;">'.$field_data.'</div>
		     	</td>
		     	</tr>';
		     	}
		     	$main_count++;
		     }

		   $email_message .= '<!-- IF CHAR COUNT OVER 50 make label display block -->

		   </tbody>
		 </table>
		 <!-- END NEW TABLE -->
		<!-- Start 3 col -->
		<table style="margin-bottom: 20px; border: 1px solid #cccccc; border-collapse: collapse;" width="100%" border="1" BORDERWIDTH="1" BORDERCOLOR="CCCCCC" cellspacing="0" cellpadding="5" align="left" valign="top" borderspacing="0" >

		<tbody valign="top">
		 <tr valign="top" border="0">
		  <td width="160" height="50" align="center" valign="top" border="0">
		     <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email . '&tab=tabs-wpleads_lead_tab_conversions' ).'">View Lead Activity</a></h3>
		  </td>

		  <td width="160" height="50" align="center" valign="top" border="0">
		     <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email . '&scroll-to=wplead_metabox_conversion' ).'">Pages Viewed</a></h3>
		  </td>

		 <td width="160" height="50" align="center" valign="top" border="0">
		    <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email . '&tab=tabs-wpleads_lead_tab_raw_form_data' ).'">View Form Data</a></h3>
		 </td>
		 </tr>
		</tbody></table>
		<!-- end 3 col -->
		 <!-- Start half/half -->
		 <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
		     <tbody><tr>
		      <td align="center" width="250" height="30" cellpadding="5">
		         <div><!--[if mso]>
		           <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email ).'" style="height:40px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#7490af" fillcolor="#3A9FD1">
		             <w:anchorlock/>
		             <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">View Lead</center>
		           </v:roundrect>
		         <![endif]--><a href="'.admin_url( 'edit.php?post_type=wp-lead&lead-email-redirect=' . $form_email ).'"
		         style="background-color:#3A9FD1;border:1px solid #7490af;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:250px;-webkit-text-size-adjust:none;mso-hide:all;" title="View the full Lead details in WordPress">View Full Lead Details</a>
		       </div>
		      </td>

		       <td align="center" width="250" height="30" cellpadding="5">
		         <div><!--[if mso]>
		           <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="mailto:'.$form_email.'?subject=RE:Hello There&body=Thanks for coming back to our site" style="height:40px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#558939" fillcolor="#59b329">
		             <w:anchorlock/>
		             <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">Reply to Lead Now</center>
		           </v:roundrect>
		         <![endif]--><a href="mailto:'.$form_email.'?subject=RE:Hello There&body=Thanks for coming back to our site"
		         style="background-color:#59b329;border:1px solid #558939;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:250px;-webkit-text-size-adjust:none;mso-hide:all;" title="Email This Lead now">Reply to Lead Now</a></div>

		       </td>
		     </tr>
		   </tbody>
		 </table>
		<!-- End half/half -->

		          </td>
		        </tr>
		      </tbody></table>
		    </td>
		  </tr>
		</tbody></table>
		<table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px;font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
		  <tbody><tr>
		    <td valign="top" width="30" style="color:#272727">&nbsp;</td>
		    <td valign="top" height="18" style="height:18px;color:#272727"></td>
		      <td style="color:#272727">&nbsp;</td>
		    <td style="color:#545454;text-align:right" align="right">&nbsp;</td>
		    <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
		  </tr>
		  <tr>
		    <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
		      <td width="50" height="40" valign="middle" align="left" style="color:#272727">
				<img src="'. INBOUND_FORMS . 'images/inbound-email.png" height="40" width="40" alt=" " style="outline:none;text-decoration:none;max-width:100%;display:block;width:40px;min-height:40px;border-radius:20px">
		      </td>
		    <td style="color:#272727">
		      <b>Leads</b>
		       from Inbound Now
		    </td>
		    <td valign="middle" align="left" style="color:#545454;text-align:right">'.$lead_data['date'].'</td>
		    <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
		  </tr>
		  <tr>
		    <td valign="top" height="6" style="color:#272727;line-height:1px">&nbsp;</td>
		    <td style="color:#272727;line-height:1px">&nbsp;</td>
		      <td style="color:#272727;line-height:1px">&nbsp;</td>
		    <td style="color:#545454;text-align:right;line-height:1px" align="right">&nbsp;</td>
		    <td valign="middle" width="30" style="color:#272727;line-height:1px">&nbsp;</td>
		  </tr>
		</tbody></table>

		      <table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px">
		        <tbody><tr>
		          <td valign="top" style="color:#b1b1b1;font-size:11px;line-height:16px;font-family:Arial,sans-serif;text-align:center" align="center">
		            <p style="margin-top:1em;margin-right:0;margin-bottom:1em;margin-left:0"></p>
		          </td>
		        </tr>
		      </tbody></table>
		    </td>
		  </tr>
		  <tr>
		    <td valign="top" height="20">&nbsp;</td>
		  </tr>
		</tbody></table>
		</body>';

		return $email_message;
	}
}

Lead_Revisit_Notifications::init();

}