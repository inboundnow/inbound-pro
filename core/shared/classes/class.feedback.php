<?php
/* Inbound Now Menu Class */

if (!class_exists('Inbound_Feedback')) {
	class Inbound_Feedback {
	static $add_feedback;

	/*	Contruct
	*	--------------------------------------------------------- */
	static function init() {
		self::$add_feedback = true;
		add_action( 'admin_footer', array(__CLASS__, 'show_feedback'));
		add_action('wp_ajax_send_inbound_feedback', array(__CLASS__, 'send_inbound_feedback'));
	}

	/*	Loads
	*	--------------------------------------------------------- */
	static function loads($hook) {
		if ( ! self::$add_feedback )
		return;
		global $wp_admin_bar;
		/* CHECK FOR ACTIVE PLUGINS */
		$leads_status = FALSE; $landing_page_status = FALSE; $cta_status = FALSE;
		if (function_exists('is_plugin_active') && is_plugin_active('leads/leads.php')) {
			$leads_status = TRUE;
			$leads_version_number = defined( 'WPL_CURRENT_VERSION' ) ? 'v' . WPL_CURRENT_VERSION : '';
		}
		if (function_exists('is_plugin_active') && is_plugin_active('landing-pages/landing-pages.php')) {
			$landing_page_status = TRUE;
			$landing_page_version_number = defined( 'LANDINGPAGES_CURRENT_VERSION' ) ? 'v' . LANDINGPAGES_CURRENT_VERSION : '';

		}
		if (function_exists( 'is_plugin_active' ) && is_plugin_active('cta/calls-to-action.php')) {
			$cta_status = TRUE;
			$cta_number = defined( 'WP_CTA_CURRENT_VERSION' ) ? 'v' . WP_CTA_CURRENT_VERSION : '';
		}

		if ( $leads_status == FALSE && $landing_page_status == FALSE && $cta_status == FALSE	) {

			return; /* end plugin is */

		}


		/* Exit if admin bar not there */
		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
			return;
		}

		/** Show these items only if Inbound Now plugin is actually installed */
		if ( $leads_status == TRUE || $landing_page_status == TRUE || $cta_status == TRUE ) {

		}

	}
	static function send_inbound_feedback(){
		/* process feedback */
			if (isset($_POST['feedback'])) {
			$firstname= 'anonymous';
			$lastname= 'anonymous';
			$email = (isset($_POST['email'])) ? $_POST['email'] : 'anonymous';
			$feedback= $_POST['feedback'];
			$page = $_POST['page'];
			$plugin = (isset($_POST['plugin'])) ? $_POST['plugin'] : 'na';

			$context = array(
					'hutk' => 'anonymous',
					'ipAddress' => 'anonymous',
					'pageUrl' => 'anonymous',
					'pageTitle' => $page
				);
			$context_json = json_encode($context);
			/*Need to populate these varilables with values from the form. */
			$str_post2 = "message=" . urlencode($feedback)
						. "&email=" . urlencode($email)
						. "&plugin=" . urlencode($plugin)
						. "&page=" . urlencode($page)
						. "&hs_context=" . urlencode($context_json);
			$endpoint2 = 'https://forms.hubspot.com/uploads/form/v2/24784/4c6efedd-40b4-438e-bb4c-050a1944c974';

			$ch2 = @curl_init();
			@curl_setopt($ch2, CURLOPT_POST, true);
			@curl_setopt($ch2, CURLOPT_POSTFIELDS, $str_post2);
			@curl_setopt($ch2, CURLOPT_URL, $endpoint2);
			@curl_setopt($ch2, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response2 = @curl_exec($ch2);	/*Log the response from HubSpot as needed. */
			@curl_close($ch2);
			echo $response2;

		}
	}
	public static function get_count($type) {
		global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE (post_status = 'publish' AND post_type = '".$type."')");
		if (0 < $count) {
			$count = number_format($count);
		}
		return $count;
	}
	public static function get_stats($type) {
		/* $pro = self::get_pro_user_data(); */
		/*
		$payload = { e: 'pageview',
		  t: '2015-05-13T00:17:49.650Z',
		  kv:
		   { url: 'http://localhost:8080/',
		     name: '',
		     referrer: '',
		     id: '781cad1f-7d7b-4493-8ec3-2b2de17c2ef1',
			domain: "site.com",
		     origin: 'localhost:8080',
		     page: 'http://localhost:8080/',
		     pro: true,
		     leads: count,
		     lp: count,
		     cta: count
		    }
		 */
	}
	public static function ispro($type) {

	}
	public static function sp($type) {

	}

	/**
	 * Counts extensions by totaling settings groups added to the Inbound Pro Extensions settings area.
	 */
	public static function  count_pro_extensions( ) {
	    /* check for premium plugins */
	    $extensions = apply_filters( 'inbound_settings/extend', array()) ;
	    if (isset($extensions['inbound-pro-settings'])) {
	        return count($extensions['inbound-pro-settings']);
	    } else {
	        return 0;
	    }
	}
	/**
	 * Counts templates by reading directories in each plugin's updload folder
	 */
	public static function count_non_core_templates( ) {
	    /* count templates in landing pages uploads folder */
	    if( is_defined('LANDINGPAGES_UPLOADS_PATH') ) {
	        $templates['landing-pages'] = self::count_templates( LANDINGPAGES_UPLOADS_PATH );
	    }
	    /* count templates in calls to action uploads folder */
	    if( is_defined('WP_CTA_UPLOADS_PATH') ) {
	        $templates['cta'] = self::count_templates( LANDINGPAGES_UPLOADS_PATH );
	    }
	    /* count templates in mailer uploads folder */
	    if( is_defined('INBOUND_EMAIL_PATH') ) {
	        $templates['mailer'] = self::count_templates( INBOUND_EMAIL_PATH );
	    }
	    return $templates;
	}
	/**
	 * Counts the number of first level child folders of a parent folder
	 * @param $directory
	 * @return array|string
	 */
	public static function count_templates( $directory ) {
	    /* count themes in landing pages uploads folder */
	    if ( !$handle = opendir( $directory ) ) {
	        return $count['error'] = "directory doesnt exist";
	    }
	    $templates = array();
	    while ( false !== ( $name = readdir($handle) ) ) {
	        if ($name == "." && $name == "..") {
	            continue;
	        }
	        if (is_dir($name)) {
	            echo "Folder => " . $name . "<br>";
	            $templates['templates'][] = $name;
	        }
	    }
	    $templates['count'] = count($templates);
	    return $templates;
	}
	/**
	 * Checks if using inbound pro and if user's license is active
	 */
	 public static function get_pro_user_data() {
	    $pro['installed'] = false;
	    $pro['active_license'] = false;
	    if (is_defined('INBOUND_PRO_PATH')) {
	        $pro['installed'] = true;
	        if (self::get_customer_status()) {
	            $pro['active_license'] = true;
	        }
	    }
	    return $pro;
	 }

	static function show_feedback() {
		if ( ! self::$add_feedback || ! is_admin()) {
			return;
		}

		$screen = get_current_screen();

		$show_array = array(
			"edit-landing-page",
			"landing-page_page_lp_global_settings",
			"landing-page",
			"landing-page_page_lp_manage_templates",
			"edit-landing_page_category",
			"edit-inbound-forms",
			"wp-lead",
			"edit-wp-lead",
			"edit-list",
			"wp-lead_page_wpleads_global_settings",
			"edit-wp-call-to-action",
			"wp-call-to-action",
			"edit-wp_call_to_action_category",
			"wp-call-to-action_page_wp_cta_manage_templates",
			"wp-call-to-action_page_wp_cta_global_settings"
		);

		$lp_page_array = array(
			"edit-landing-page",
			"landing-page_page_lp_global_settings",
			"landing-page",
			"landing-page_page_lp_manage_templates",
			"edit-landing_page_category"
		);

		$leads_page_array = array(
			"wp-lead",
			"edit-wp-lead",
			"edit-list",
			"wp-lead_page_wpleads_global_settings",
		);

		$cta_page_array = array(
			"edit-wp-call-to-action",
			"wp-call-to-action",
			"edit-wp_call_to_action_category",
			"wp-call-to-action_page_wp_cta_manage_templates",
			"wp-call-to-action_page_wp_cta_global_settings"
		);

		if (!in_array($screen->id, $show_array)) {
				return;
		}

		$plugin_name = __( 'Inbound Now Marketing Plugins', 'inbound-pro' ); /* default */
		if (in_array($screen->id, $lp_page_array)) {
			$plugin_name = __( 'Landing Pages plugin', 'inbound-pro' );
		} else if (in_array($screen->id, $cta_page_array)) {
			$plugin_name = __( 'Calls to Action plugin', 'inbound-pro' );
		} else if (in_array($screen->id, $leads_page_array)) {
			$plugin_name = __( 'Leads plugin', 'inbound-pro' );
		}

		?>
	<div id="launch-feedback" style='z-index:9999999999999; background:gray; position:fixed; bottom:0px; right:20px; width:200px; height:30px;'>
	<div id="inbound-fb-request">
	<div class="inbound-close-fb"><?php _e( 'close', 'inbound-pro' ); ?></div>
		<div id="lp-slide-toggle">
			<header id="header" class='inbound-customhead'>
			<a href="http://www.inboundnow.com" target="_blank" title="<?php _e( 'Visit Inbound Now', 'inbound-pro' ); ?>"><img src="<?php echo INBOUNDNOW_SHARED_URLPATH . 'assets/images/admin/inbound-now-logo.png';?>" width="315px"></a>
			<h3 class="main-feedback-header" ><?php _e( 'We love hearing from You!', 'inbound-pro' ); ?></h3>
			<h4><?php  _e( sprintf( 'Please leave your %sidea/feature request%s to make the %s better below! ', '<strong>', '</strong>', $plugin_name ), 'inbound-pro' ); ?></h4>
			</header>
			<section id="inbound-rules-main">
			<form accept-charset="UTF-8" method="POST" id="inbound-feedback">
			<div class="hs_message field hs-form-field">
				<label placeholder="<?php _e( 'Enter your Feature Request', 'inbound-pro' ); ?>" for="message-4c6efedd-40b4-438e-bb4c-050a1944c974"><?php _e( 'Feature Request', 'inbound-pro' ); ?><span class="hs-form-required"> * </span>
				</label>
				<div class="input">
				<textarea required="required" id="inbound-feedback-message" name="message" value=""></textarea>
				</div>
				<div class="input">
				<input id="inbound-feedback-email-field" name="email" value="" placeholder="<?php _e( 'Your Email (optional field)', 'inbound-pro' ); ?>"></textarea>
				</div>
			</div>

			<div class="inbound-feedback-actions">
				<input class="submit-inbound-feedback" type="submit" value="<?php _e( 'Send Feedback/Feature Request', 'inbound-pro' ); ?>">
			</div>
			<div class="inbound-feedback-desc" style="display: block;"><strong><?php _e( 'Please note:', 'inbound-pro' ); ?></strong> <?php _e( 'Support requests will not be handled through this form', 'inbound-pro' ); ?></div>
			</form>
			</section>
		</div>
			<div id="inbound-automation-footer" class="inbound-selectron-foot"><?php _e( 'Submit a Feature Request', 'inbound-pro' ); ?></div>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {

		jQuery("body").on('click', '#inbound-automation-footer', function () {
			jQuery("#lp-slide-toggle").slideToggle();
			jQuery("#lp-open-close").toggleClass("lp-options-up");
			jQuery("#footer").toggleClass("lp-options-on");
		});

		jQuery("body").on('click', '.inbound-close-fb', function () {
			jQuery("#lp-slide-toggle").slideToggle();
		});

		jQuery("body").on('submit', '#inbound-feedback', function (e) {
			e.preventDefault(); /* halt normal form */
			var feedback = jQuery('#inbound-feedback-message').val();
			var email = jQuery('#inbound-feedback-email-field').val();
			if (typeof (feedback) != "undefined" && feedback != null && feedback != "") {
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					timeout: 10000,
					data: {
						feedback : feedback,
						email: email,
						page: document.title,
						plugin: "<?php echo $plugin_name;?>",
						action: 'send_inbound_feedback'
					},
					success: function(user_id){
						console.log('feedback sent');
						$(".inbound-customhead").hide();
						$("#inbound-feedback").html('<h1>Thank You for your feedback!</h1><h3>Our team is hard at work to improve things for you!</h3>');
					}
				});
			} else {
				$("#lp-slide-toggle textarea").css('border', 'red');
			}
		});
		/* Stash
		count = 0;
		var test = {};
		var what = { e: 'pageview',
		  t: '2015-05-13T00:17:49.650Z',
		  kv:
		   { url: 'http://localhost:8080/',
		     name: '',
		     referrer: '',
		     id: '781cad1f-7d7b-4493-8ec3-2b2de17c2ef1',
			domain: "site.com",
		     origin: 'localhost:8080',
		     page: 'http://localhost:8080/',
		     pro: true,
		     leads: count,
		     lp: count,
		     cta: count
			}
		};
		jQuery.ajax({
			type: 'POST',
			url: 'http://localhost:3000/users',
			timeout: 10000,
			data: what,
			success: function(user_id){
				console.log('feedback sent');
				$(".inbound-customhead").hide();
				$("#inbound-feedback").html('<h1>Thank You for your feedback!</h1><h3>Our team is hard at work to improve things for you!</h3>');
			}
		});
		*/
	});
	</script>
	<style type="text/css">
	#wpfooter{display:none}.main-feedback-header{font-size:21px;padding-top:0;margin-top:14px;margin-bottom:10px;padding-bottom:0}.inbound-close-fb{font-size:10px;position:absolute;right:5px;top:-17px;cursor:pointer}.inbound-customhead{text-align:center}#inbound-fb-request{background:#fff;background:rgba(255,255,255,1);margin:0 0 -1px;padding:10px;border:1px solid #ccc;border-top-left-radius:2px;border-top-right-radius:2px;box-shadow:0 2px 6px 0 rgba(0,0,0,.2),0 25px 50px 0 rgba(0,0,0,.15)}#inbound-feedback h1{font-size:20px;color:green}#inbound-feedback h3{font-size:13px;padding-bottom:15px}#inbound-fb-request h4{padding-right:5px;text-align:left;font-weight:300;font-size:16px;line-height:22px;margin-top:13px;margin-bottom:7px}.inbound-feedback-actions{text-align:center;margin-top:10px;margin-bottom:5px}.inbound-feedback-desc{color:#000;font-weight:300;padding-bottom:5px;padding-top:5px}#inbound-fb-request{position:fixed!important;right:10px;bottom:0;width:330px}#lp-slide-toggle{margin-bottom:30px;display:none}#inbound-automation-footer.inbound-selectron-foot{color:#777;font-size:20px;padding:11px 15px 0!important;cursor:pointer;position:absolute!important;right:0!important;bottom:0!important;left:0!important;background:#E9E9E9!important;height:26px!important;z-index:8!important;text-align:center;box-shadow:0 1px 1px rgba(0,0,0,.3)}.submit-inbound-feedback{position:relative;display:block;line-height:40px;font-size:18px;font-weight:500;color:#fff;cursor:pointer;text-align:center;text-decoration:none;text-shadow:0 1px rgba(0,0,0,.1);background:#fd935c;border-bottom:2px solid #cf7e3b;border-color:rgba(0,0,0,.15);border-radius:4px;width:95%;margin:auto}#inbound-feedback-email-field,#lp-slide-toggle textarea{width:100%;padding:6px 12px;font-size:14px;line-height:1.428571429;color:#555;vertical-align:middle;background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075);-webkit-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s}#inbound-feedback-email-field{margin-top:5px}#lp-slide-toggle textarea{min-height:125px}#inbound-feedback-email-field:focus,#lp-slide-toggle textarea:focus{border-color:#66afe9;outline:0;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)}
	 </style>
	<?php }

	}
}
/*	Initialize InboundNow Menu
 *	--------------------------------------------------------- */

Inbound_Feedback::init();

?>