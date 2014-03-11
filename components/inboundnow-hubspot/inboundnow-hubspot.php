<?php
/*
Plugin Name: InboundNow Extension - HubSpot Integration
Plugin URI: http://www.inboundnow.com/market/hubspot-integration/
Description: Provides HubSpot support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.2
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*/

/*
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/

if(!defined('INBOUNDNOW_HUBSPOT_CURRENT_VERSION')) { define('INBOUNDNOW_HUBSPOT_CURRENT_VERSION', '1.0.2' ); }
if(!defined('INBOUNDNOW_HUBSPOT_LABEL')) { define('INBOUNDNOW_HUBSPOT_LABEL' , 'HubSpot Integration' ); }
if(!defined('INBOUNDNOW_HUBSPOT_FILE')) { define('INBOUNDNOW_HUBSPOT_FILE' , __FILE__ ); }
if(!defined('INBOUNDNOW_HUBSPOT_SLUG')) { define('INBOUNDNOW_HUBSPOT_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_HUBSPOT_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_HUBSPOT_REMOTE_ITEM_NAME', 'hubspot-integration' ); }
if(!defined('INBOUNDNOW_HUBSPOT_URLPATH')) { define('INBOUNDNOW_HUBSPOT_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }
if(!defined('INBOUNDNOW_HUBSPOT_PATH')) { define('INBOUNDNOW_HUBSPOT_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }


if (!class_exists('Inbound_HubSpot')) {
class Inbound_HubSpot {
	static $add_hubspot;

	static function init() {
		add_action('admin_init', array(__CLASS__, 'inboundnow_hubspot_extension_setup'));
		/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
		add_filter('lp_define_global_settings', array(__CLASS__, 'inboundnow_hubspot_add_global_settings'));
		add_filter('wpleads_define_global_settings', array(__CLASS__, 'inboundnow_hubspot_add_global_settings'));
		add_filter('wp_cta_define_global_settings', array(__CLASS__, 'inboundnow_hubspot_add_global_settings'));

		add_filter('inboundnow_forms_settings',  array(__CLASS__, 'inboundnow_hubspot_add_form_settings' , 10 , 1));

		/* Provide backwards compatibility for older data array model */
		add_filter('lp_extension_data', array(__CLASS__, 'inboundnow_hubspot_add_metaboxes'));
		add_filter('wp_cta_extension_data', array(__CLASS__, 'inboundnow_hubspot_add_metaboxes'));
		//add options to bulk edit
		add_action('admin_footer-edit.php',  array(__CLASS__, 'inboundnow_hubspot_bulk_actions_add_options'));
		add_action('load-edit.php',  array(__CLASS__, 'wpleads_bulk_action_hubspot'));
		/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
		add_action('inbound_store_lead_post', array(__CLASS__, 'inboundnow_hubspot_landing_page_integratation'));
		/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
		add_action('inboundnow_form_submit_actions', array(__CLASS__, 'inboundnow_hubspot_inboundnow_form_integratation' , 10 , 2 ));
	}

	static function inboundnow_hubspot_extension_setup() {
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'INBOUNDNOW_EXTEND' ) )
			$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_HUBSPOT_FILE , INBOUNDNOW_HUBSPOT_LABEL , INBOUNDNOW_HUBSPOT_SLUG , INBOUNDNOW_HUBSPOT_CURRENT_VERSION  , INBOUNDNOW_HUBSPOT_REMOTE_ITEM_NAME ) ;

		/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
		$hubspot_api_key = get_option('inboundnow_hubspot_api_key' , '' );
		$hubspot_portal_id =  get_option('inboundnow_hubspot_portal_id' , '' );
		if ( !$hubspot_api_key || !$hubspot_portal_id ) {
			add_action( 'admin_notices', 'inboundnow_hubspot_admin_notice' );
			function inboundnow_hubspot_admin_notice() {
				$admin_url =  '<a href="'.admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-extensions').'">Enter one here</a>';
			?>
			<div class="updated">
				<p><?php _e( 'InboundNow HubSpot Extension requires a HubSpot API Key and HubSpot Portal ID to opperate. '.$admin_url .'', 'inbound-now' ); ?></p>
			</div>
			<?php
			}
		}
	}

	static function inboundnow_hubspot_connect( $nature = null ) {
		require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.exception.php';

		$hubspot_api_key = get_option('inboundnow_hubspot_api_key' , '' );
		$hubspot_portal_id =  get_option('inboundnow_hubspot_portal_id' , '' );

		if ( !$hubspot_api_key || !$hubspot_portal_id )
			return null;

		switch($nature) {

			case "lists":

				require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.lists.php';

				return new HubSpot_Lists($hubspot_api_key ,  $hubspot_portal_id );

				break;
			case "contacts":
				require_once INBOUNDNOW_HUBSPOT_PATH.'includes/haPiHP-master/class.contacts.php';

				return new HubSpot_Contacts($hubspot_api_key , $hubspot_portal_id);

				break;
		}

		return null;
	}

	static function inboundnow_hubspot_get_hubspot_lists() {

		$hubspot_lists = get_transient('inboundnow_hubspot_lists');

		if ($hubspot_lists)
			return $hubspot_lists;

		$lists = self::inboundnow_hubspot_connect('lists');

		if (!$lists)
			return null;

		/* Get Static Lists */
		$static_lists = $lists->get_static_lists(null);

		foreach($static_lists->lists as $key => $hubspot_list) {
			//var_dump($hubspot_list);
			$options[$hubspot_list->internalListId] = $hubspot_list->name;
		}

		if (!isset($options))
			$options['0'] = "No lists discovered.";

		set_transient( 'inboundnow_hubspot_lists', $options, 60*5 );

		return $options;
	}

	static function inboundnow_hubspot_add_subscriber( $lead_data , $target_list ) {
		$contacts = self::inboundnow_hubspot_connect('contacts');
		$lists = self::inboundnow_hubspot_connect('lists');

		/*check if contact exists */
		$contact = $contacts->get_contact_by_email($lead_data['email']);
		if (isset($contact->vid)) {
			$contact_id = $contact->vid;
		} else {
			/* create contact if does not exist*/
			$lead_data = array('email'=> $lead_data['wpleads_email_address'],
							'firstname'=> $lead_data['wpleads_first_name'],
							'lastname'=> $lead_data['wpleads_last_name']
							);

			$lead_data = apply_filters('inboundnow_hubspot_lead_data',$lead_data);

			$createdContact = $contacts->create_contact($lead_data);

			$contact_id = $createdContact->{'vid'};
		}

		/* add contact to list */
		$contacts_to_add = array($contact_id);
		$added_contacts = $lists->add_contacts_to_list( $contacts_to_add , $target_list  );
	}

	/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
	static function inboundnow_hubspot_add_global_settings($global_settings) {
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
				'id'  => 'inboundnow_header_hubspot',
				'type'  => 'header',
				'default'  => __('<h4>HubSpot API Key</h4>', 'inbound-now'),
				'options' => null
			);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_hubspot_portal_id',
					'option_name'  => 'inboundnow_hubspot_portal_id',
					'label' => __('HubSpot Portal ID', 'inbound-now'),
					'description' => __('Get your HubSpot API Key at https://app.hubspot.com/keys/get.', 'inbound-now'),
					'type'  => 'text',
					'default'  => ''
				);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_hubspot_api_key',
					'option_name'  => 'inboundnow_hubspot_api_key',
					'label' => 'HubSpot API Key',
					'description' => "Get your HubSpot API Key at https://app.hubspot.com/keys/get.",
					'type'  => 'text',
					'default'  => ''
				);

		return $global_settings;
	}


	static function inboundnow_hubspot_add_form_settings($fields) {
		$fields['forms']['options']['hubspot_enable'] =   array(
	                                                'name' => __('Enable Hubspot Sync', 'inbound-now'),
	                                                'desc' => __('Enable/Disable Hubspot Integration for this form.', 'inbound-now'),
	                                                'type' => 'checkbox',
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );

		$hubspot_lists = self::inboundnow_hubspot_get_hubspot_lists();
		$fields['forms']['options']['hubspot_list_id'] =   array(
	                                                'name' => __('Hubspot List', 'inbound-now'),
	                                                'desc' => __('Send submissions to this Hubspot list', 'inbound-now'),
	                                                'type' => 'select',
	                                                'options' => $hubspot_lists,
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );
		return $fields;
	}


	/* Provide backwards compatibility for older data array model */
	static function inboundnow_hubspot_add_metaboxes( $metabox_data ) {
		$lists = self::inboundnow_hubspot_get_hubspot_lists();

		$metabox_data['inboundnow-hubspot']['info']['data_type'] = 'metabox';
		$metabox_data['inboundnow-hubspot']['info']['position'] = 'side';
		$metabox_data['inboundnow-hubspot']['info']['priority'] = 'default';
		$metabox_data['inboundnow-hubspot']['info']['label'] = 'HubSpot Integration';

		$metabox_data['inboundnow-hubspot']['settings'] = array(
			//ADD METABOX - SELECTED TEMPLATE
			array(
				'id'  => 'hubspot_integration',
				'label' => 'Enable:',
				'description' => "Enable this setting to send email related conversion data to hubspot list. Email must be present in conversion form for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => '0',
				'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
			),
			array(
				'id'  => 'hubspot_list',
				'label' => 'Target list:',
				'description' => "Select the hubspot list that converted data will be sent to. Must have setup a hubspot api key & portal id for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
				'default'  => '',
				'options' => $lists
			)
		);

		return $metabox_data;
	}


	//add options to bulk edit
	static function inboundnow_hubspot_bulk_actions_add_options() {
	  	if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead') {

			$lists = self::inboundnow_hubspot_get_hubspot_lists();

			$html = "<select id='hubspot_list_select' name='action_hubspot_list_id'>";
			foreach ($lists as $key=>$value) {
				$html .= "<option value='".$key."'>".$value."</option>";
			}
			$html .="</select>";
			?>
			<script type="text/javascript">
			  jQuery(document).ready(function() {
				jQuery('<option>').val('export-hubspot').text('<?php _e('Export to HubSpot List')?>').appendTo("select[name='action']");
				jQuery('<option>').val('export-hubspot').text('<?php _e('Export to HubSpot List')?>').appendTo("select[name='action2']");

				jQuery(document).on('change','select[name=action]', function() {
					var this_id = jQuery(this).val();
					//alert(this_id);
					if (this_id.indexOf("export-hubspot") >= 0) {
						var html  = "<?php echo $html; ?>";
						jQuery("select[name='action']").after(html);
					} else {
						jQuery('#hubspot_list_select').remove();
					}
				});
			  });
			</script>
			<?php
		}
	}


	static function wpleads_bulk_action_hubspot() {

		if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])) {
			// 1. get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();


			if ( !current_user_can('manage_options') ) {
				die();
			}

			$post_ids = array_map('intval', $_REQUEST['post']);

			switch($action) {
				case 'export-hubspot':


					$target_list = $_REQUEST['action_hubspot_list_id'];

					$exported = 0;
					foreach( $post_ids as $post_id ) {
						/* get lead data */
						$lead_data['first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
						$lead_data['last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
						$lead_data['email'] =  get_post_meta($post_id,'wpleads_email_address', true);
						self::inboundnow_hubspot_add_subscriber( $lead_data  , $target_list);
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
	static function inboundnow_hubspot_landing_page_integratation($lead_data) {

		if (get_post_meta($data['lp_id'],'inboundnow-hubspot-hubspot_integration',false)) {
			/* get target list */
			$target_list = get_post_meta($data['lp_id'],'inboundnow-hubspot-hubspot_list',true);
			self::inboundnow_hubspot_add_subscriber( $data , $target_list );
		}
	}


	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	static function inboundnow_hubspot_inboundnow_form_integratation($form_post_data , $form_meta_data ) {
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

		if ($form_settings['inbound_shortcode_hubspot_enable']=='on') {
			$target_list = $form_settings['inbound_shortcode_hubspot_list_id'];
			self::inboundnow_hubspot_add_subscriber($subscriber , $target_list);
		}
	}
}

Inbound_HubSpot::init();

}
