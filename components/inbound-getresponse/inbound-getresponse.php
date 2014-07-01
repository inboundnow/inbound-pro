<?php
/*
Plugin Name: InboundNow Extension - GetResponse Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides GetResponse support for Landing Pages, Leads, and Calls to Action plugin. For information about how to disable double-optin see: http://support.getresponse.com/faq/how-i-edit-opt-in-settings.
Version: 1.0.4
Author: Hudson Atwell, David Wells
Author URI: http://www.inboundnow.com/
*/

/*
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/


if(!defined('INBOUNDNOW_GETRESPONSE_CURRENT_VERSION')) { define('INBOUNDNOW_GETRESPONSE_CURRENT_VERSION', '1.0.4' );}
if(!defined('INBOUNDNOW_GETRESPONSE_LABEL')) { define('INBOUNDNOW_GETRESPONSE_LABEL' , 'GetResponse Integration' );}
if(!defined('INBOUNDNOW_GETRESPONSE_FILE')) { define('INBOUNDNOW_GETRESPONSE_FILE' , __FILE__ );}
if(!defined('INBOUNDNOW_GETRESPONSE_SLUG')) { define('INBOUNDNOW_GETRESPONSE_SLUG' , plugin_basename( dirname(__FILE__) ) );}
if(!defined('INBOUNDNOW_GETRESPONSE_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_GETRESPONSE_REMOTE_ITEM_NAME', 'getresponse-integration' );}
if(!defined('INBOUNDNOW_GETRESPONSE_URLPATH')) { define('INBOUNDNOW_GETRESPONSE_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );}
if(!defined('INBOUNDNOW_GETRESPONSE_PATH')) { define('INBOUNDNOW_GETRESPONSE_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );}


if (!class_exists('InboundNow_GetResponse')) {
class InboundNow_GetResponse {

	static function init() {
	add_action('admin_init',  array(__CLASS__, 'inboundnow_getresponse_extension_setup'));
	/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
	add_filter('lp_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
	add_filter('wpleads_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
	add_filter('wp_cta_define_global_settings', array(__CLASS__, 'inboundnow_getresponse_add_global_settings'));
	add_filter('inboundnow_forms_settings',  array(__CLASS__, 'inboundnow_getresponse_add_form_settings' , 10 , 1));

	/* Provide backwards compatibility for older data array model */
	add_filter('lp_extension_data', array(__CLASS__, 'inboundnow_getresponse_add_metaboxes'));
	add_filter('wp_cta_extension_data', array(__CLASS__, 'inboundnow_getresponse_add_metaboxes'));

	//add options to bulk edit
	add_action('admin_footer-edit.php',  array(__CLASS__, 'inboundnow_getresponse_bulk_actions_add_options'));

	add_action('load-edit.php',  array(__CLASS__, 'wpleads_bulk_action_getresponse'));

	/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
	add_action('inbound_store_lead_post', array(__CLASS__, 'inboundnow_getresponse_landing_page_integratation'));
	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	add_action('inboundnow_form_submit_actions', array(__CLASS__, 'inboundnow_getresponse_inboundnow_form_integratation' , 10 , 2 ));
	}

	static function inboundnow_getresponse_extension_setup() {

		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) )
			$license = new Inbound_License( INBOUNDNOW_GETRESPONSE_FILE , INBOUNDNOW_GETRESPONSE_LABEL , INBOUNDNOW_GETRESPONSE_SLUG , INBOUNDNOW_GETRESPONSE_CURRENT_VERSION  , INBOUNDNOW_GETRESPONSE_REMOTE_ITEM_NAME ) ;

		/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
		$getresponse_api_key = get_option('inboundnow_getresponse_api_key' , '' );
		if ( !$getresponse_api_key ) {
			add_action( 'admin_notices', 'inboundnow_getresponse_admin_notice' );
			function inboundnow_getresponse_admin_notice()
			{
			?>
			<div class="updated">
				<p><?php _e( 'InboundNow GetResponse Extension requires a <a href="https://app.getresponse.com/my_api_key.html" target="_blank">GetResponse API Key</a> to opperate.', INBOUNDNOW_GETRESPONSE_TEXT_DOMAIN ); ?></p>
			</div>
			<?php
			}
		}
	}

	static function inboundnow_getresponse_connect() {
		require_once INBOUNDNOW_GETRESPONSE_PATH.'includes/getresponse/GetResponseAPI.class.php';
		$getresponse_api_key = get_option('inboundnow_getresponse_api_key' , '' );

		if ( !$getresponse_api_key )
			return null;

		return new GetResponse($getresponse_api_key);
	}

	static function inboundnow_getresponse_get_lists() {

		$getresponse_lists = get_transient('inboundnow_getresponse_lists');

		if ($getresponse_lists)
			return $getresponse_lists;

		$gr = self::inboundnow_getresponse_connect();

		if (!is_object($gr))
			return;

		$campaigns 	 = $gr->getCampaigns();;

		if (!$campaigns)
			return;

		foreach($campaigns as $id=>$campaign) {
			$options[$id] = $campaign->name;
		}

		if (!isset($options))
			$options['0'] = "No lists discovered.";

		set_transient( 'inboundnow_getresponse_lists', $options, 60*5 );

		return $options;
	}

	static function inboundnow_getresponse_add_subscriber( $lead_data , $target_list ) {

		$gr = self::inboundnow_getresponse_connect();

		if (!is_object($gr)) {
			return;
		}

		$name = $lead_data['first_name'].' '.$lead_data['last_name'];
		$response = $gr->addContact($target_list, $name, $lead_data['email']);

	}

	/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
	static function inboundnow_getresponse_add_global_settings($global_settings) {
		switch (current_filter()) {
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
				'id'  => 'inboundnow_header_getresponse',
				'type'  => 'header',
				'default'  => __('<h4>GetResponse API Key</h4>', INBOUND_LABEL),
				'options' => null
			);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_getresponse_api_key',
					'option_name'  => 'inboundnow_getresponse_api_key',
					'label' => 'GetResponse API Key',
					'description' => "Get your GetResponse API Key at <a href='https://app.getresponse.com/my_api_key.html' target='_blank'>https://app.getresponse.com/my_api_key.html</a>.",
					'type'  => 'text',
					'default'  => ''
				);

		return $global_settings;
	}


	static function inboundnow_getresponse_add_form_settings($fields) {
		$fields['forms']['options']['getresponse_enable'] =   array(
	                                                'name' => __('Enable GetResponse Sync', INBOUND_LABEL),
	                                                'desc' => __('Enable/Disable GetResponse Integration for this form.', INBOUND_LABEL),
	                                                'type' => 'checkbox',
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );

		$getresponse_lists = self::inboundnow_getresponse_get_lists();
		$fields['forms']['options']['getresponse_list_id'] =   array(
	                                                'name' => __('GetResponse List', INBOUND_LABEL),
	                                                'desc' => __('Send submissions to this GetResponse list', INBOUND_LABEL),
	                                                'type' => 'select',
	                                                'options' => $getresponse_lists,
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );
		return $fields;
	}


	static function inboundnow_getresponse_add_metaboxes( $metabox_data ) {
		$lists = self::inboundnow_getresponse_get_lists();

		$metabox_data['inboundnow-getresponse']['info']['data_type'] = 'metabox';
		$metabox_data['inboundnow-getresponse']['info']['position'] = 'side';
		$metabox_data['inboundnow-getresponse']['info']['priority'] = 'default';
		$metabox_data['inboundnow-getresponse']['info']['label'] = 'GetResponse Integration';

		$metabox_data['inboundnow-getresponse']['settings'] = array(
			//ADD METABOX - SELECTED TEMPLATE
			array(
				'id'  => 'getresponse_integration',
				'label' => 'Enable:',
				'description' => "Enable this setting to send email related conversion data to getresponse list. Email must be present in conversion form for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => '0',
				'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
			),
			array(
				'id'  => 'getresponse_list',
				'label' => 'Target list:',
				'description' => "Select the getresponse list that converted data will be sent to. Must have setup a getresponse api key & portal id for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
				'default'  => '',
				'options' => $lists
			)
		);

		return $metabox_data;
	}


	static function inboundnow_getresponse_bulk_actions_add_options() {

		if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead') {

			$lists = self::inboundnow_getresponse_get_lists();

			$html = "<select id='getresponse_list_select' name='action_getresponse_list_id'>";
			foreach ($lists as $key=>$value)
			{
				$html .= "<option value='".$key."'>".$value."</option>";
			}
			$html .="</select>";
			?>
			<script type="text/javascript">
			  jQuery(document).ready(function() {
				jQuery('<option>').val('export-getresponse').text('<?php _e('Export to GetResponse List')?>').appendTo("select[name='action']");
				jQuery('<option>').val('export-getresponse').text('<?php _e('Export to GetResponse List')?>').appendTo("select[name='action2']");

				jQuery(document).on('change','select[name=action]', function() {
					var this_id = jQuery(this).val();
					//alert(this_id);
					if (this_id.indexOf("export-getresponse") >= 0)
					{
						var html  = "<?php echo $html; ?>";

						jQuery("select[name='action']").after(html);
					}
					else
					{
						jQuery('#getresponse_list_select').remove();
					}
				});
			  });
			</script>
			<?php
		}
	}


	static function wpleads_bulk_action_getresponse() {

		if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])) {
			// 1. get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();


			if ( !current_user_can('manage_options') ) {
				die();
			}

			$post_ids = array_map('intval', $_REQUEST['post']);

			switch($action) {
				case 'export-getresponse':


					$target_list = $_REQUEST['action_getresponse_list_id'];

					$exported = 0;
					foreach( $post_ids as $post_id ) {

						/* get lead data */
						$lead_data['first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
						$lead_data['last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
						$lead_data['email'] =  get_post_meta($post_id,'wpleads_email_address', true);

						self::inboundnow_getresponse_add_subscriber( $lead_data  , $target_list);

						$exported++;
					}

					$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
					wp_redirect($sendback);
					exit();

				break;
			}
		}
	}

	/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
	static function inboundnow_getresponse_landing_page_integratation($lead_data) {

		if (get_post_meta($lead_data['page_id'],'inboundnow-getresponse-getresponse_integration',false)) {
			if (isset($lead_data['email'])) {
				$subscriber['email'] = $lead_data['email'];
			}

			if (isset($lead_data['wpleads_email_address'])) {
				$subscriber['email'] = $lead_data['wpleads_email_address'];
			}

			if (isset($lead_data['first-name'])) {
				$subscriber['first_name'] = $lead_data['first-name'];
			}

			if (isset($lead_data['wpleads_first_name'])) {
				$subscriber['first_name'] = $lead_data['wpleads_first_name'];
			}

			if (isset($lead_data['last-name'])) {
				$subscriber['last_name'] = $lead_data['last-name'];
			}

			if (isset($lead_data['wpleads_last_name'])) {
				$subscriber['last_name'] = $form_post_data['wpleads_last_name'];
			}

			/* get target list */
			$target_list = get_post_meta($lead_data['page_id'],'inboundnow-getresponse-getresponse_list',true);

			self::inboundnow_getresponse_add_subscriber( $subscriber , $target_list );
		}
	}


	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	static function inboundnow_getresponse_inboundnow_form_integratation($form_post_data , $form_meta_data ) {

		if (isset($form_post_data['email']))
			$subscriber['email'] = $form_post_data['email'];

		if (isset($form_post_data['wpleads_email_address']))
			$subscriber['email'] = $form_post_data['wpleads_email_address'];

		if (isset($form_post_data['first-name']))
			$subscriber['first_name'] = $form_post_data['first-name'];

		if (isset($form_post_data['wpleads_first_name']))
			$subscriber['first_name'] = $form_post_data['wpleads_first_name'];


		if (isset($form_post_data['last-name']))
			$subscriber['last_name'] = $form_post_data['last-name'];

		if (isset($form_post_data['wpleads_last_name']))
			$subscriber['last_name'] = $form_post_data['wpleads_last_name'];

		$form_settings = $form_meta_data['inbound_form_values'][0];
		parse_str($form_settings, $form_settings);


		if ($form_settings['inbound_shortcode_getresponse_enable']=='on') {
			$target_list = $form_settings['inbound_shortcode_getresponse_list_id'];
			self::inboundnow_getresponse_add_subscriber($subscriber , $target_list);
		}
	}

}

InboundNow_GetResponse::init();
// Need to do activation hooks
}