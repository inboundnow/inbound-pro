<?php
/*
Plugin Name: InboundNow Extension - Zapier Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides Zapier support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.2
Author: Inbound Now
Author URI: http://www.inboundnow.com/
Text Domain: inboundnow-zapier
Domain Path: lang
*/

/* Define constants */
if(!defined('INBOUND_NOW_ACTIVATE')) { define('INBOUND_NOW_ACTIVATE', __FILE__ ); }
if(!defined('INBOUNDNOW_ZAPIER_CURRENT_VERSION')) { define('INBOUNDNOW_ZAPIER_CURRENT_VERSION', '1.0.2' ); }
if(!defined('INBOUNDNOW_ZAPIER_LABEL')) { define('INBOUNDNOW_ZAPIER_LABEL' , 'Zapier Integration' ); }
if(!defined('INBOUNDNOW_ZAPIER_FILE')) { define('INBOUNDNOW_ZAPIER_FILE' , __FILE__ ); }
if(!defined('INBOUNDNOW_ZAPIER_SLUG')) { define('INBOUNDNOW_ZAPIER_SLUG' , plugin_basename( basename(__DIR__) ));}
if(!defined('INBOUNDNOW_ZAPIER_TEXT_DOMAIN')) { define('INBOUNDNOW_ZAPIER_TEXT_DOMAIN' , plugin_basename( dirname(__FILE__) ) );}
if(!defined('INBOUNDNOW_ZAPIER_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_ZAPIER_REMOTE_ITEM_NAME' , 'zapier-integration' ); }
if(!defined('INBOUNDNOW_ZAPIER_URLPATH')) { define('INBOUNDNOW_ZAPIER_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }
if(!defined('INBOUNDNOW_ZAPIER_PATH')) { define('INBOUNDNOW_ZAPIER_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );}
//echo INBOUNDNOW_ZAPIER_TEXT_DOMAIN;
if (!class_exists('Inbound_Zapier')) {

class Inbound_Zapier {
	static $launch_zap;
	static $plugin_slug = INBOUNDNOW_ZAPIER_SLUG;

	static function init() {
		add_action('admin_init', array(__CLASS__, 'inboundnow_zapier_extension_setup'));
		/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
		add_filter('lp_define_global_settings', array(__CLASS__, 'inboundnow_zapier_add_global_settings'));
		add_filter('wpleads_define_global_settings', array(__CLASS__, 'inboundnow_zapier_add_global_settings'));
		add_filter('wp_cta_define_global_settings',array(__CLASS__, 'inboundnow_zapier_add_global_settings'));
		/*  Ajax Call for Fake Lead Data */
		add_action('wp_ajax_inbound_zap_generate_lead', array(__CLASS__, 'inbound_zap_generate_lead'));
		add_action('wp_ajax_nopriv_inbound_zap_generate_lead', array(__CLASS__, 'inbound_zap_generate_lead'));
		/* Add settings to inbound forms */
		add_filter('inboundnow_forms_settings', array(__CLASS__, 'inboundnow_zapier_add_form_settings'), 10 , 1);
		/* Provide backwards compatibility for older data array model */
		add_filter('lp_extension_data', array(__CLASS__, 'inboundnow_zapier_add_metaboxes'));
		add_filter('wp_cta_extension_data', array(__CLASS__, 'inboundnow_zapier_add_metaboxes'));
		/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
		add_action('inbound_store_lead_post', array(__CLASS__, 'inboundnow_zapier_landing_page_integratation'));
		/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
		add_action('inboundnow_form_submit_actions', array(__CLASS__, 'inboundnow_zapier_inboundnow_form_integratation'), 10 , 2 );
		/* add options to bulk edit */
		add_action('admin_footer-edit.php', 'inboundnow_zapier_bulk_actions_add_options');
		add_action('load-edit.php', array(__CLASS__, 'wpleads_bulk_action_zapier'));
	}

	static function inboundnow_zapier_extension_setup() {
		self::$launch_zap = true;
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'INBOUNDNOW_EXTEND' ) )
			$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_ZAPIER_FILE , INBOUNDNOW_ZAPIER_LABEL , INBOUNDNOW_ZAPIER_SLUG , INBOUNDNOW_ZAPIER_CURRENT_VERSION  , INBOUNDNOW_ZAPIER_REMOTE_ITEM_NAME ) ;


		/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
		$inboundnow_zapier_webhook_url = get_option('inboundnow_zapier_webhook_url' , '' );
		if ( !$inboundnow_zapier_webhook_url) {

			add_action( 'admin_notices', 'inboundnow_zapier_admin_notice' );
			function inboundnow_zapier_admin_notice() { ?>

			<div class="updated">
			<?php $admin_url =  '<a href="'.admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-extensions').'">Enter one here</a>'; ?>
			<p><?php _e( 'InboundNow Zapier Extension requires a Zapier Webhook URL to opperate. Enter one here '.$admin_url.'', 'inboundnow' ); ?></p>
			</div>

			<?php
			}
		}
	}

	static function inboundnow_zapier_send_data( $lead_data_encoded , $webhook_url ) {
		//echo $lead_data_encoded; exit;
		$form_data = array("sslverify" => false, "ssl" => true, "body" => $lead_data_encoded, "headers" => array() );

		$response = wp_remote_post($webhook_url, $form_data);
		if (is_wp_error($response)) {
			var_dump($response); exit;
		}
		else {
			//print_r($response);exit;
		}

		return null;

	}


	static function inboundnow_zapier_add_subscriber( $lead_data , $webhook_url ) {

		$lead_data = apply_filters('inboundnow_zapier_lead_data',$lead_data);

		$lead_data_encoded = json_encode( $lead_data );

		inboundnow_zapier_send_data( $lead_data_encoded , $webhook_url );
	}


	public function inbound_zap_generate_lead() {
		// Sample Lead Data
	    $lead_data = array(
	    	"user_ID" => 1,
	    	"wordpress_date_time" => "2013-12-11 16:05:08 UTC",
	    	"wpleads_email_address" => "email@email.com",
	    	"element_type" => "FORM",
	    	"wp_lead_uid" => "Lqnf6O5wJ4ql93sScTSoOJlkahambsD3BJv",
	    	"raw_post_values_json" => "{\"first-name\":\"First Name\",\"last-name\":\"Last Name\",\"email\":\"email@email.com\",\"company-name\":\"Company Name\",\"job-title\":\"Job Title\"}",
	    	"wpleads_first_name" => "First Name",
	    	"wpleads_last_name" => "Last Name",
	    	"wpleads_company_name" => "Company Name",
	    	"wpleads_mobile_phone" => "111-111-1111",
	    	"wpleads_address_line_1" => "123 fake street",
	    	"ip_address" => "127.0.0.1",
	    	"lp_id" => "99",
	    	"post_type" => "page",
	    	"lp_variation" => "0",
	    	"page_views" => '{\"99\":"\"2013-12-11 16:04:51 UTC\",\"2013-12-11 16:04:52 UTC\"}',
	    	"page_view_count" => "15",
	    	"lead_id" => "826",
	    	"conversion_data" => '{"1":{"id":"99","variation":"","datetime":"2013-12-11 16:05:08 UTC","first_time":1}}'
	    	);

	    $webhook_urls = get_option( 'inboundnow_zapier_webhook_url' );
		$webhook_urls =  preg_split("/[\r\n,]+/", $webhook_urls, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($webhook_urls as $webhook_url)
		{
			inboundnow_zapier_add_subscriber( $lead_data, $webhook_url );
		}
	}


	static function inboundnow_zapier_add_global_settings($global_settings) {
		switch (current_filter())
		{
			case "lp_define_global_settings":
				$tab_slug = 'lp-extensions';
				break;
			case "wpleads_define_global_settings":
				$tab_slug = 'wpleads-extensions';
				break;
			case "wp_cta_define_global_settings":
				$tab_slug = 'wp-cta-extensions';
				break;
		}

		$global_settings[$tab_slug]['settings'][] =
			array(
				'id'  => 'inboundnow_header_zapier',
				'type'  => 'header',
				'default'  => __('<h4>Zapier Setup</h4>', 'leads-pro'),
				'options' => null
			);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_zapier_webhook_url',
					'option_name'  => 'inboundnow_zapier_webhook_url',
					'label' => __('Zapier Webhook URL(s)', 'leads-pro'),
					'description' => __('One URL per line. Get your Zapier API WebHook URLs at https://app.zapier.com/keys/get.', 'leads-pro'),
					'type'  => 'textarea',
					'default'  => ''
				);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_generate_test_lead',
					'option_name'  => 'inboundnow_generate_test_lead',
					'label' => __('Send Test Lead Zapier for Zap Setup', 'leads-pro'),
					'description' => __('When creating a zap in zapier you need to pass in sample Data. Click this button and you will pass a test lead into zapier!', 'leads-pro'),
					'type'  => 'html',
					'default'  => '<span id="generate-test-lead" class="button">Generate Test Lead</span>

						<script type="text/javascript">
							jQuery(document).ready(function($) {
							   jQuery("body").on("click", "#generate-test-lead", function () {
								console.log("clcickckckc");
							   		        jQuery.ajax({
							   		            type: "POST",
							   		            url: ajaxadmin.ajaxurl,
							   		            data: {
							   		                action: "inbound_zap_generate_lead"
							   		            },
							   		            success: function (e) {
							   		                var t = this;
							   		                var n = e;
							   		                var r = JSON.parse(n);
							   		                jQuery("#generate-test-lead").after("<span>  Lead Sent to Zapier!</span>");
							   		                $(".copy-right-area").html(r.replace)
							   		            }
							   		        })
							   		    });
							 });
						</script>'
				);

		return $global_settings;
	}


	static function inboundnow_zapier_add_form_settings($fields) {
		$fields['forms']['options']['zapier_enable'] =   array(
	                                                'name' => __('Enable Zapier Sync', 'leads-pro'),
	                                                'desc' => __('Enable/Disable Zapier Integration for this form.', 'leads-pro'),
	                                                'type' => 'checkbox',
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );

		return $fields;
	}

	static function inboundnow_zapier_add_metaboxes( $metabox_data ) {

		$metabox_data['inboundnow-zapier']['info']['data_type'] = 'metabox';
		$metabox_data['inboundnow-zapier']['info']['position'] = 'side';
		$metabox_data['inboundnow-zapier']['info']['priority'] = 'default';
		$metabox_data['inboundnow-zapier']['info']['label'] = 'Zapier Integration';

		$metabox_data['inboundnow-zapier']['settings'] = array(
			//ADD METABOX - SELECTED TEMPLATE
			array(
				'id'  => 'zapier_integration',
				'label' => 'Enable:',
				'description' => "Enable this setting to send email related conversion data to zapier list. Email must be present in conversion form for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => '0',
				'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
			)
		);

		return $metabox_data;
	}

	/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
	static function inboundnow_zapier_landing_page_integratation($lead_data) {
		if (get_post_meta( $lead_data['lp_id'] ,'inboundnow-zapier-zapier_integration' , true ))
		{
			$webhook_urls = get_option( 'inboundnow_zapier_webhook_url' );
			$webhook_urls =  preg_split("/[\r\n,]+/", $webhook_urls, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($webhook_urls as $webhook_url)
			{
				inboundnow_zapier_add_subscriber( $lead_data , $webhook_url );
			}
		}
	}

	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	static function inboundnow_zapier_inboundnow_form_integratation($form_post_data , $form_meta_data ) {

		$form_settings = $form_meta_data['inbound_form_values'][0];
		parse_str($form_settings, $form_settings);

		if ($form_settings['inbound_shortcode_zapier_enable']=='on') {
			$webhook_urls = get_option( 'inboundnow_zapier_webhook_url' );
			$webhook_urls =  preg_split("/[\r\n,]+/", $webhook_urls, -1, PREG_SPLIT_NO_EMPTY);

			foreach ($webhook_urls as $webhook_url) {

				inboundnow_zapier_add_subscriber( $form_post_data , $webhook_url );

			}
		}
	}


	static function inboundnow_zapier_bulk_actions_add_options() {
	  global $post_type;

	  if($post_type == 'wp-lead') { ?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
			jQuery('<option>').val('export-zapier').text('<?php _e('Export to Zapier List')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-zapier').text('<?php _e('Export to Zapier List')?>').appendTo("select[name='action2']");
		  });
		</script>
		<?php
	  }
	}

	static function wpleads_bulk_action_zapier() {

		if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])) {
			// 1. get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();


			if ( !current_user_can('manage_options') ) {
				die();
			}

			$post_ids = array_map('intval', $_REQUEST['post']);

			switch($action) {
				case 'export-zapier':

					$exported = 0;
					foreach( $post_ids as $post_id ) {

						/* get lead data */
						$lead_data['first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
						$lead_data['last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
						$lead_data['email'] =  get_post_meta($post_id,'wpleads_email_address', true);

						inboundnow_zapier_add_subscriber( $lead_data );

						$exported++;
					}

					$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
					wp_redirect($sendback);
					exit();

				break;
			}
		}
	}
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}
	/* Handler for Activation. Fired when the plugin is activated. */
	public static function activate( $network_wide ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide  ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();

					foreach ( $blog_ids as $blog_id ) {

						switch_to_blog( $blog_id );
						self::single_activate();
					}

					restore_current_blog();

				} else {
					self::single_activate();
				}

			} else {
				self::single_activate();
			}

		}

		/* Handler for Deactivation. Fired when the plugin is deactivated. */
		public static function deactivate( $network_wide ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();

					foreach ( $blog_ids as $blog_id ) {

						switch_to_blog( $blog_id );
						self::single_deactivate();

					}

					restore_current_blog();

				} else {
					self::single_deactivate();
				}

			} else {
				self::single_deactivate();
			}

		}
		/* Fired for each blog when the plugin is activated. */
		private static function single_activate() {
			$slug = self::$plugin_slug;
			$transient = str_replace('-', "_", $slug);
			set_transient( '_'.$transient.'_activation_redirect', true, 30 );
		}

		/* Fired for each blog when the plugin is deactivated. */
		private static function single_deactivate() {
			$slug = self::$plugin_slug;
			$transient = str_replace('-', "_", $slug);
			delete_transient( '_'.$transient.'_activation_redirect', true, 30 );
		}

}
/* End Class Functions */

register_activation_hook( INBOUND_NOW_ACTIVATE, array( 'Inbound_Zapier', 'activate' ) );
register_deactivation_hook( INBOUND_NOW_ACTIVATE, array( 'Inbound_Zapier', 'deactivate' ) );
Inbound_Zapier::init(); // Launch Zapier and only once

	/* Welcome Launch on Activation Class */
	if (is_file(WP_PLUGIN_DIR . '/leads/shared/classes/welcome.class.php')) {
		include_once(WP_PLUGIN_DIR . '/leads/shared/classes/welcome.class.php');  // Inbound Welcome Class
	} elseif (is_file(WP_PLUGIN_DIR . '/cta/shared/classes/welcome.class.php')) {
		include_once(WP_PLUGIN_DIR . '/cta/shared/classes/welcome.class.php');  // Inbound Welcome Class
	} elseif (is_file(WP_PLUGIN_DIR . '/landing-pages/shared/classes/welcome.class.php')) {
		include_once(WP_PLUGIN_DIR . '/landing-pages/shared/classes/welcome.class.php');  // Inbound Welcome Class
	}

	new Inbound_Now_Welcome('inboundnow-zapier', 'Zapier Integration', plugin_basename( dirname(__FILE__) ));

}