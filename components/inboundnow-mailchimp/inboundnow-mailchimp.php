<?php
/*
Plugin Name: InboundNow Extension - MailChimp Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides MailChimp support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.5
Author: Inbound Now
Author URI: http://www.inboundnow.com/
Text Domain: inboundnow-mailchimp
Domain Path: lang
*/

/*
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/

if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_CURRENT_VERSION' , '1.0.5' );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_LABEL' , 'MailChimp Integration' );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_FILE' , __FILE__ );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_SLUG' , plugin_basename( dirname(__FILE__) ) );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_TEXT_DOMAIN' , plugin_basename( dirname(__FILE__) ) );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_REMOTE_ITEM_NAME' , 'mailchimp-integration' );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_URLPATH' , WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );}
if(!defined('FOO')) { define('INBOUNDNOW_MAILCHIMP_PATH' , WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );}


if (!class_exists('Inbound_MailChimp')) {

if (!class_exists('MailChimp')) {
	include_once('includes/mailchimp-api-master/MailChimp.class.php');
}

class Inbound_MailChimp {
	static $add_mailchimp;

	static function init() {
		
		/* Setup Licensing & Component Upgrading */
		add_action('admin_init', array(__CLASS__, 'extension_setup'));
		
		/* Add Global Settings */
		add_filter('lp_define_global_settings', array(__CLASS__, 'add_global_settings'));
		add_filter('wpleads_define_global_settings', array(__CLASS__, 'add_global_settings'));
		add_filter('wp_cta_define_global_settings', array(__CLASS__, 'add_global_settings'));
		
		/* Add Form Settings */
		add_filter('inboundnow_forms_settings', array(__CLASS__, 'add_form_settings' , 10 , 1));
		
		
		/* Provide backwards compatibility for older data array model */
		add_filter('lp_extension_data', array(__CLASS__, 'add_metaboxes'));
		add_filter('wp_cta_extension_data', array(__CLASS__, 'add_metaboxes'));
		
		/* Setup Bulk Exporting */
		add_action('admin_footer-edit.php', array(__CLASS__, 'bulk_actions_add_options'));
		add_action('load-edit.php', array(__CLASS__, 'wpleads_bulk_action_mailchimp'));
		
		/* Setup Service Integration on 'inbound_store_lead_post' */
		add_action('inbound_store_lead_post', array(__CLASS__, 'landing_page_integratation'));
		
		/* Setup Service Integration on Inbound Form Submissions */
		add_action('inboundnow_form_submit_actions', array(__CLASS__, 'inboundnow_form_integratation' , 10 , 2 ));

	}


	static function extension_setup() {
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'INBOUNDNOW_EXTEND' ) ) {
			$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_MAILCHIMP_FILE , INBOUNDNOW_MAILCHIMP_LABEL , INBOUNDNOW_MAILCHIMP_SLUG , INBOUNDNOW_MAILCHIMP_CURRENT_VERSION  , INBOUNDNOW_MAILCHIMP_REMOTE_ITEM_NAME ) ;
		}

		$apikey = get_option('inboundnow_mailchimp_api_key', false);
		if (!$apikey) {
			add_action( 'admin_notices', array( __CLASS__ , 'admin_notice' ) );
		}
	}
	
	public function admin_notice() {
		$admin_url =  '<a href="'.admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-extensions').'">Enter one here</a>';
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow MailChimp Extension requires a MailChimp API Key to opperate. '.$admin_url.'', 'inbound-now' ); ?></p>
		</div>
		<?php
	}

	static function add_global_settings($global_settings) {
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
				'id'  => 'inboundnow_header_mailchimp',
				'type'  => 'header',
				'default'  => __('<h4>MailChimp API Key</h4>', 'inbound-now'),
				'options' => null
			);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_mailchimp_api_key',
					'option_name'  => 'inboundnow_mailchimp_api_key',
					'label' => __('MailChimp API Key', 'inbound-now'),
					'description' => __("Enter Mailchimp API Key to power extension: http://kb.mailchimp.com/article/where-can-i-find-my-api-key.", 'inbound-now'),
					'type'  => 'text',
					'default'  => ''
				);

		return $global_settings;
	}


	static function add_form_settings($fields) {
		self::$add_mailchimp = true;
		$fields['forms']['options']['mailchimp_enable'] =   array(
	                                                'name' => __('Enable MailChimp Sync', 'inbound-now'),
	                                                'desc' => __('Enable/Disable MailChimp Integration for this form.', 'inbound-now'),
	                                                'type' => 'checkbox',
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );

		$mailchimp_lists = self::get_mailchimp_lists();
		$fields['forms']['options']['mailchimp_list_id'] =   array(
	                                                'name' => __('MailChimp List', 'inbound-now'),
	                                                'desc' => __('Send submissions to this MailChimp list', 'inbound-now'),
	                                                'type' => 'select',
	                                                'options' => $mailchimp_lists,
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );
		return $fields;
	}

	/* Provide backwards compatibility for older data array model */
	static function add_metaboxes( $metabox_data ) {
		$lists = self::get_mailchimp_lists();

		$metabox_data['inboundnow-mailchimp']['info']['data_type'] = 'metabox';
		$metabox_data['inboundnow-mailchimp']['info']['position'] = 'side';
		$metabox_data['inboundnow-mailchimp']['info']['priority'] = 'default';
		$metabox_data['inboundnow-mailchimp']['info']['label'] = 'Mailchimp Integration';

		$metabox_data['inboundnow-mailchimp']['settings'] = array(
			//ADD METABOX - SELECTED TEMPLATE
			array(
				'id'  => 'mailchimp_integration',
				'label' => 'Enable:',
				'description' => "Enable this setting to send email related conversion data to mailchimp list. Email must be present in conversion form for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => '0',
				'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
			),
			array(
				'id'  => 'mailchimp_list',
				'label' => 'Target list:',
				'description' => "Select the mailchimp list that converted data will be sent to. Must have setup a mailchimp api key and enabled the setting above for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
				'default'  => '',
				'options' => $lists
			)
		);

		return $metabox_data;
	}


	//add_action('admin_menu', 'add_other_metaboxes');
	static function add_other_metaboxes() {
			//add_meta_box( INBOUNDNOW_MAILCHIMP_SLUG, 'Enable Mailchump on Forms', 'add_other_metaboxes_display' , 'post', 'side', 'default' );
			//add_meta_box(INBOUNDNOW_MAILCHIMP_SLUG, 'Enable Mailchump on Forms', 'add_other_metaboxes_display' , 'page', 'side', 'default' );

	}


	static function add_other_metaboxes_display() {
			global $post, $table_prefix;
			?>
			<table class="form-table">
				<tbody>
				<tr class="inboundnow-mailchimp-mailchimp_integration mailchimp_integration landing-page-option-row">
					<th class="landing-page-table-header mailchimp_integration-label"><label for="inboundnow-mailchimp-mailchimp_integration">Enable:</label></th>
					<td class="landing-page-option-td"><select class="mailchimp_integration" id="inboundnow-mailchimp-mailchimp_integration" name="inboundnow-mailchimp-mailchimp_integration"><option value="1" selected="selected">on</option><option value="0">off</option></select><div title="Enable this setting to send email related conversion data to mailchimp list. Email must be present in conversion form for this feature to work." class="lp_tooltip"></div></td></tr><tr class="inboundnow-mailchimp-mailchimp_list mailchimp_list landing-page-option-row">
					<th class="landing-page-table-header mailchimp_list-label"><label for="inboundnow-mailchimp-mailchimp_list">Target list:</label></th>
					<td class="landing-page-option-td">
						<select class="mailchimp_list" id="inboundnow-mailchimp-mailchimp_list" name="inboundnow-mailchimp-mailchimp_list">
							<option value="ce29f86f54">AlphaBeta</option>
							<option value="b03ad1c87e" selected="selected">List Beta</option>
							<option value="08d0a87a77">List Alpha</option>
						</select>
					<div title="Select the mailchimp list that converted data will be sent to. Must have setup a mailchimp api key and enabled the setting above for this feature to work." class="lp_tooltip"></div></td>
				</tr>
				</tbody>
			</table>
			<?php
	}

	static function get_mailchimp_lists() {

		$mailchimp_lists = get_transient('inboundnow_mailchimp_lists');

		if ($mailchimp_lists)
			return $mailchimp_lists;

		$apikey = get_option('inboundnow_mailchimp_api_key' , true);

		if (!$apikey)
			return;

		$MailChimp = new MailChimp($apikey);

		$lists = $MailChimp->call('lists/list');

		if ( isset($lists['total']) && $lists['total'] >0 ) {
			foreach ( $lists['data'] as $list ) {
				$options[$list['id']] = $list['name'];
			}
		}

		if (!isset($options))
			$options['0'] = "No lists discovered.";

		set_transient( 'inboundnow_mailchimp_lists', $options, 60*5 );

		return $options;
	}

	//add options to bulk edit
	static function bulk_actions_add_options() {

	 	if( isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead' ) {

			$lists = self::get_mailchimp_lists();
			$html = "<select id='mailchimp_list_select' name='action_mailchimp_list_id'>";
			foreach ($lists as $key=>$value) {
				$html .= "<option value='".$key."'>".$value."</option>";
			}
			$html .="</select>"; ?>
			<script type="text/javascript">
			  jQuery(document).ready(function() {
				jQuery('<option>').val('export-mailchimp').text('<?php _e('Export to MailChimp List')?>').appendTo("select[name='action']");
				jQuery('<option>').val('export-mailchimp').text('<?php _e('Export to MailChimp List')?>').appendTo("select[name='action2']");

				jQuery(document).on('change','select[name=action]', function() {
					var this_id = jQuery(this).val();
					//alert(this_id);
					if (this_id.indexOf("export-mailchimp") >= 0) {
						var html  = "<?php echo $html; ?>";

						jQuery("select[name='action']").after(html);
					} else {
						jQuery('#mailchimp_list_select').remove();
					}
				});
			  });
			</script>
			<?php
		}
	}


	static function wpleads_bulk_action_mailchimp() {

			if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])){
				// 1. get the action
				$wp_list_table = _get_list_table('WP_Posts_List_Table');
				$action = $wp_list_table->current_action();

				if ( !current_user_can('manage_options') ) {
					die();
				}

				$post_ids = array_map('intval', $_REQUEST['post']);

				switch($action) {
					case 'export-mailchimp':
						$apikey = get_option('inboundnow_mailchimp_api_key' , true);
						$target_list = $_REQUEST['action_mailchimp_list_id'];
						$exported = 0;

						foreach( $post_ids as $post_id ) {
							$lead_first_name = get_post_meta($post_id,'wpleads_first_name', true);
							$lead_last_name =  get_post_meta($post_id,'wpleads_last_name', true);
							$lead_email =  get_post_meta($post_id,'wpleads_email_address', true);

							$MailChimp = new MailChimp($apikey);
							$result = $MailChimp->call('lists/subscribe', array(
								'id'                => $target_list,
								'email'             => array('email'=>$lead_email),
								'merge_vars'        => array('FNAME'=>$lead_first_name, 'LNAME'=>$lead_last_name),
								'double_optin'      => false,
								'update_existing'   => true,
								'replace_interests' => false,
								'send_welcome'      => false,
							));

							$exported++;
						}
						$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
						// 4. Redirect client
						wp_redirect($sendback);
						exit();

					break;
				}
			}
	}

	/* FUNCTION TO SEND SUBSCRIBER TO MAILCHIMP */
	static function add_subscriber($target_list , $subscriber) {
		$api_key = get_option( 'inboundnow_mailchimp_api_key' , 0 );

		if (!$api_key) {
			return;
		}

		$MailChimp = new MailChimp($api_key);

		$args = array(
			'id'                => $target_list,
			'email'             => array('email'=>$subscriber['email']),
			'merge_vars'        => array('FNAME'=>$subscriber['FNAME'], 'LNAME'=>$subscriber['LNAME']),
			'double_optin'      => false,
			'update_existing'   => true,
			'replace_interests' => false,
			'send_welcome'      => false,
		);

		$args = apply_filters('inboundnow_mailchimp_args' , $args);

		$debug = 0;
		if ($debug==1) {
			var_dump($args);
		}

		$result = $MailChimp->call('lists/subscribe', $args );
	}

	static function map_data( $data ) {
		if (isset($data['email'])) {
			$subscriber['email'] = $data['email'];
		}
		
		if (isset($data['wpleads_email_address'])) {
			$subscriber['email'] = $data['wpleads_email_address'];
		}

		if (isset($data['first-name'])) {
			$subscriber['FNAME'] = $data['first-name'];
		}
		
		if (isset($data['wpleads_first_name'])) {
			$subscriber['FNAME'] = $data['wpleads_first_name'];
		}

		if (isset($data['last-name'])) {
			$subscriber['LNAME'] = $data['last-name'];
		}
		
		if (isset($data['wpleads_last_name'])) {
			$subscriber['LNAME'] = $data['wpleads_last_name'];
		}
		
		$subscriber['optin_time'] = date('Y-m-d H:i:s');
		$subscriber['optin_ip'] = $_SERVER['REMOTE_ADDR'];
		
		return $subscriber;
	}

	/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
	static function landing_page_integratation($data) {
		if (get_post_meta($data['lp_id'],'inboundnow-mailchimp-mailchimp_integration',true)) {
			$target_list = get_post_meta($data['lp_id'],'inboundnow-mailchimp-mailchimp_list',true);
			
			$subscriber = self::map_data( $data );
			$subscriber = apply_filters( 'inbound_mailchimp_subscriber_vars' , $subscriber , $data );
			
			self::add_subscriber( $target_list , $subscriber );
		}
	}

	

	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	static function inboundnow_form_integratation($form_post_data , $form_meta_data ) {
		
		$subscriber = self::map_data( $form_post_data );
		
		/* See http://apidocs.mailchimp.com/api/2.0/lists/subscribe.php merge_vars for acceptible mailchimp parameters */
		$subscriber = apply_filters( 'inbound_mailchimp_subscriber_vars' , $subscriber , $form_post_data , $form_meta_data );
		
		$form_settings = $form_meta_data['inbound_form_values'][0];
		parse_str($form_settings, $form_settings);

		if ($form_settings['inbound_shortcode_mailchimp_enable']=='on') {
			$target_list = $form_settings['inbound_shortcode_mailchimp_list_id'];
			self::add_subscriber($target_list , $subscriber);
		}
	}

}

Inbound_MailChimp::init();

}