<?php
/*
Plugin Name: InboundNow Extension - Aweber Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides Aweber support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.4
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*/

/*
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/

if(!defined('INBOUNDNOW_AWEBER_CURRENT_VERSION')) { define('INBOUNDNOW_AWEBER_CURRENT_VERSION', '1.0.4' ); }
if(!defined('INBOUNDNOW_AWEBER_LABEL')) { define('INBOUNDNOW_AWEBER_LABEL' , 'Aweber Integration' ); }
if(!defined('INBOUNDNOW_AWEBER_SLUG')) { define('INBOUNDNOW_AWEBER_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_AWEBER_FILE')) { define('INBOUNDNOW_AWEBER_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME' , 'aweber-integration' ); }
if(!defined('INBOUNDNOW_AWEBER_URLPATH')) { define('INBOUNDNOW_AWEBER_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }
if(!defined('INBOUNDNOW_AWEBER_PATH')) { define('INBOUNDNOW_AWEBER_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

if (!class_exists('Inbound_Aweber')) {

if (!class_exists('AWeberAPI')){
		include_once('includes/aweber_api/aweber_api.php');
}
class Inbound_Aweber {
	static $add_aweber;

	static function init() {
		add_action('admin_init', array(__CLASS__, 'inboundnow_aweber_extension_setup'));
		/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
		add_filter('lp_define_global_settings', array(__CLASS__, 'inboundnow_aweber_add_global_settings'));
		add_filter('wpleads_define_global_settings', array(__CLASS__, 'inboundnow_aweber_add_global_settings'));
		add_filter('wp_cta_define_global_settings', array(__CLASS__, 'inboundnow_aweber_add_global_settings'));
		//authorize aweber and store token
		add_action('lp_save_global_settings', array(__CLASS__, 'inboundnow_aweber_save_data', 10, 2));
		add_action('wpleads_save_global_settings', array(__CLASS__, 'inboundnow_aweber_save_data', 10, 2));
		add_action('wp_cta_save_global_settings', array(__CLASS__, 'inboundnow_aweber_save_data', 10, 2));

		add_action('admin_init' , array(__CLASS__, 'inboundnow_aweber_process_oauth_code'));
		add_filter('inboundnow_forms_settings', array(__CLASS__, 'inboundnow_aweber_add_form_settings' , 10 , 1));

		/* Provide backwards compatibility for older data array model */
		add_filter('lp_extension_data', array(__CLASS__, 'inboundnow_aweber_add_metaboxes'));
		add_filter('wp_cta_extension_data', array(__CLASS__, 'inboundnow_aweber_add_metaboxes'));
		//add options to bulk edit
		add_action('admin_footer-edit.php', array(__CLASS__, 'inboundnow_aweber_bulk_actions_add_options'));
		add_action('load-edit.php', 'wpleads_bulk_action_aweber');

		/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
		add_action('inbound_store_lead_post', array(__CLASS__, 'inboundnow_aweber_landing_page_integratation'));

		/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
		add_action('inboundnow_form_submit_actions', array(__CLASS__, 'inboundnow_aweber_inboundnow_form_integratation' , 10 , 2 ));
	}


	static function inboundnow_aweber_extension_setup() {
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'INBOUNDNOW_EXTEND' ) )
			$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_AWEBER_FILE , INBOUNDNOW_AWEBER_LABEL , INBOUNDNOW_AWEBER_SLUG , INBOUNDNOW_AWEBER_CURRENT_VERSION  , INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME ) ;

		/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
		$app_id = get_option('inboundnow_aweber_app_id' , '' );
		$consumer_key = get_option('inboundnow_aweber_consumer_key' , '' );
		$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );


		if ( !$consumer_key || !$consumer_secret || !$app_id ) {
			add_action( 'admin_notices', 'inboundnow_aweber_admin_notice' );
			function inboundnow_aweber_admin_notice()  {
			$admin_url =  '<a href="'.admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-extensions').'">Enter one here</a>';
				?>
			<div class="updated">
				<p><?php _e( 'InboundNow Aweber Extension requires a Aweber App ID, an Aweber Consumer Key, and an Aweber Consumer Secret to opperate. '.$admin_url.'', INBOUNDNOW_LABEL ); ?></p>
			</div>
			<?php
			}
		}
	}

	/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
	static function inboundnow_aweber_add_global_settings($global_settings) {
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
				'id'  => 'inboundnow_header_aweber',
				'type'  => 'header',
				'default'  => __('<h4>Aweber API Key</h4>', 'inbound-now'),
				'options' => null
			);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_aweber_app_id',
					'option_name'  => 'inboundnow_aweber_app_id',
					'label' => __('Aweber API ID', 'inbound-now'),
					'description' => __('The first thing we need is our App ID. Singup at https://labs.aweber.com/ and create your first application. Your App ID can be found on your MyApps page and is right next to your consumer keys. The App ID is used when creating public applications.', 'inbound-now'),
					'type'  => 'text',
					'default'  => ''
				);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_aweber_consumer_key',
					'option_name'  => 'inboundnow_aweber_consumer_key',
					'label' => 'Aweber Consumer Key',
					'description' => "Find your consumer key here: https://labs.aweber.com/apps.",
					'type'  => 'text',
					'default'  => ''
				);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_aweber_consumer_secret',
					'option_name'  => 'inboundnow_aweber_consumer_secret',
					'label' => 'Aweber Consumer Secret',
					'description' => "Find your consumer secret here: https://labs.aweber.com/apps.",
					'type'  => 'text',
					'default'  => ''
				);

		$global_settings[$tab_slug]['settings'][] =
				array(
					'id'  => 'inboundnow_aweber_oauth_reset',
					'option_name'  => 'inboundnow_aweber_oauth_reset',
					'label' => 'Oauth Reset',
					'description' => "Check this box to re-authorize your aweber account and update your access keys. ",
					'type'  => 'checkbox',
					'options'  => array('1'=>'')
				);

		return $global_settings;
	}


	//authorize aweber and store token
	static function inboundnow_aweber_save_data( $field ) {
		global $debug;

		if ($field['option_name'] == 'inboundnow_aweber_app_id' ) {

			$check = get_option('inboundnow_aweber_oauth_accessKey' , 0);

			$today = date("YmdHis");
			$expiration_date = get_option('inboundnow_aweber_oauth_access_token_date',0);

			/*
			echo "AccessKey: $check<br>";
			echo "Today: $today <br>";
			echo "Expiration Date: $expiration_date<br>";
			*/

			//print_r($_POST);exit;
			if ( !$check || ( $today>$expiration_date ) || ( isset($_POST['inboundnow_aweber_oauth_reset'][0])&&$_POST['inboundnow_aweber_oauth_reset'][0]==1 )) {
				update_option('inboundnow_aweber_consumer_key' , $_POST['inboundnow_aweber_consumer_key'] );
				update_option('inboundnow_aweber_consumer_secret' , $_POST['inboundnow_aweber_consumer_secret'] );
				update_option('inboundnow_aweber_oauth_reset' , 0 );

				$projected_time = date('YmdHis', strtotime("$today + 1 year"));
				update_option('inboundnow_aweber_oauth_access_token_date' , $projected_time );
				?>
				<br><br><br>
				<a href='https://auth.aweber.com/1.0/oauth/authorize_app/<?php echo $_POST['inboundnow_aweber_app_id']; ?>' target='_blank'>Click here to authorization code from Aweber. Save the code once you get it!</a>

				<br><br>
				Type authorization code here:

				<form action='edit.php' method='get'>
					<input name='inboundnow_aweber_oauth_code' size='170'>
					<input type='hidden' name='post_type' value='<?php echo $_GET['post_type']; ?>'>
					<input type='hidden' name='page' value='<?php echo $_GET['page']; ?>'>
					<input type='submit' value='submit'>
				</form>
				<?php
				exit;

			}
		}
	}


	static function inboundnow_aweber_process_oauth_code() {
		if (isset($_GET['inboundnow_aweber_oauth_code'])) {
			$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
			$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );

			/*
			echo 'Aweber Authorized!';
			echo $consumer_key;
			echo '<br>';
			echo $consumer_secret;
			echo '<br>';
			echo $_GET['inboundnow_aweber_oauth_code'];
			echo '<br>';
			*/

			$aweber = new AWeberAPI($consumer_key , $consumer_secret);
			$credentials = $aweber->getDataFromAweberID($_GET['inboundnow_aweber_oauth_code']);
			list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $credentials;

			//print_r($credentials);

			update_option('inboundnow_aweber_oauth_consumerKey' , $consumerKey );
			update_option('inboundnow_aweber_oauth_consumerSecret' , $consumerSecret );
			update_option('inboundnow_aweber_oauth_accessKey' , $accessKey );
			update_option('inboundnow_aweber_oauth_accessSecret' , $accessSecret );

			//exit;
		}


	}


	static function inboundnow_aweber_add_form_settings($fields) {
		$fields['forms']['options']['aweber_enable'] =   array(
	                                                'name' => __('Enable Aweber Sync', 'inbound-now'),
	                                                'desc' => __('Enable/Disable Aweber Integration for this form.', 'inbound-now'),
	                                                'type' => 'checkbox',
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );

		$aweber_lists = self::inboundnow_aweber_get_aweber_lists();
		$fields['forms']['options']['aweber_list_id'] =   array(
	                                                'name' => __('Aweber List', 'inbound-now'),
	                                                'desc' => __('Send submissions to this Aweber list', 'inbound-now'),
	                                                'type' => 'select',
	                                                'options' => $aweber_lists,
	                                                'std' => '',
	                                                'class' => 'main-form-settings exclude-from-refresh' );
		return $fields;
	}


	static function inboundnow_aweber_add_metaboxes($metabox_data) {
		$lists = self::inboundnow_aweber_get_aweber_lists();

		$metabox_data['inboundnow-aweber']['info']['data_type'] = 'metabox';
		$metabox_data['inboundnow-aweber']['info']['position'] = 'side';
		$metabox_data['inboundnow-aweber']['info']['priority'] = 'default';
		$metabox_data['inboundnow-aweber']['info']['label'] = 'Aweber Integration';

		$metabox_data['inboundnow-aweber']['settings'] = array(
			//ADD METABOX - SELECTED TEMPLATE
			array(
				'id'  => 'aweber_integration',
				'label' => 'Enable:',
				'description' => "Enable this setting to send email related conversion data to aweber list. Email must be present in conversion form for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => '0',
				'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
			),
			array(
				'id'  => 'aweber_list',
				'label' => 'Target list:',
				'description' => "Select the aweber list that converted data will be sent to. Must have setup a aweber api key and enabled the setting above for this feature to work.",
				'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
				'default'  => '',
				'options' => $lists
			)
		);

		return $metabox_data;
	}


	static function inboundnow_aweber_get_aweber_lists() {
		$aweber_lists = get_transient('inboundnow_aweber_lists');

		if ($aweber_lists) {
			return $aweber_lists;
		}

		$consumer_key = get_option('inboundnow_aweber_consumer_key' , '' );
		$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
		$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
		$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );

		if ( !$consumer_key || !$consumer_secret || !$accessKey || !$accessSecret )
			return null;


		//echo "Consumer Key: {$consumer_key} <br>\r\n";
		//echo "Consumer Secret: $consumer_secret <br>\r\n";
		$aweber = new AWeberAPI($consumer_key , $consumer_secret);

		$account = $aweber->getAccount($accessKey, $accessSecret);
		$object = $account->lists;
		$data = $object->data;
		$lists = $data['entries'];

		//print_r($lists);exit;

		foreach($lists as $key => $list) {
			$options[$list['id']] = $list['name'];
		}

		if (!isset($options))
			$options['0'] = "No lists discovered.";


		set_transient( 'inboundnow_aweber_lists', $options, 60*5 );

		return $options;
	}

	//add options to bulk edit
	static function inboundnow_aweber_bulk_actions_add_options() {

		if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead') {

		$lists = self::inboundnow_aweber_get_aweber_lists();
		$html = "<select id='aweber_list_select' name='action_aweber_list_id'>";

		foreach ($lists as $key=>$value) {
			$html .= "<option value='".$key."'>".$value."</option>";
		}

		$html .="</select>"; ?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
			jQuery('<option>').val('export-aweber').text('<?php _e('Export to Aweber List')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-aweber').text('<?php _e('Export to Aweber List')?>').appendTo("select[name='action2']");

			jQuery(document).on('change','select[name=action]', function() {
				var this_id = jQuery(this).val();
				//alert(this_id);
				if (this_id.indexOf("export-aweber") >= 0) {
					var html  = "<?php echo $html; ?>";
					jQuery("select[name='action']").after(html);
				} else {
					jQuery('#aweber_list_select').remove();
				}
			});
		  });
		</script>
		<?php
	  }
	}


	static function wpleads_bulk_action_aweber() {

		if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])) {
			// 1. get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();

			if ( !current_user_can('manage_options') ) {
				die();
			}

			$post_ids = array_map('intval', $_REQUEST['post']);

			switch($action) {
				case 'export-aweber':

					$target_list = $_REQUEST['action_aweber_list_id'];
					$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
					$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
					$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
					$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );
					$aweber = new AWeberAPI($consumer_key , $consumer_secret);
					$account = $aweber->getAccount($accessKey, $accessSecret);
					$account_data = $account->data;
					$account_id = $account_data['id'];
					//$api_url = "https://api.aweber.com/1.0/accounts/{$account_id}/lists/{$target_list}";
					//var_dump($account);
					//echo $account_id;exit;
					//echo $api_url;
					//$list = $account->loadFromUrl($api_url);
					$list = $account->loadFromUrl("/accounts/{$account_id}/lists/{$target_list}");
					$subscribers = $list->subscribers;
					//var_dump($subscribers);exit;
					$exported = 0;

					foreach( $post_ids as $post_id ) {
						$lead_first_name = get_post_meta($post_id,'wpleads_first_name', true);
						$lead_last_name =  get_post_meta($post_id,'wpleads_last_name', true);
						$lead_email =  get_post_meta($post_id,'wpleads_email_address', true);
						# create a subscriber
						$params = array(
							'email' => $lead_email,
							//'ip_address' => '127.0.0.1',
							//'ad_tracking' => 'client_lib_example',
							//'last_followup_message_number_sent' => 1,
							//'misc_notes' => 'my cool app',
							'name' => "$lead_first_name $lead_last_name"
						);

						$params = apply_filters('inboundnow_aweber_subscriber_params',$params);
						$new_subscriber = $subscribers->create($params);
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

	/* FUNCTION TO SEND SUBSCRIBER TO aweber */
	static function inboundnow_aweber_add_subscriber($target_list , $subscriber) {
		/* prepare api access codes */
		$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
		$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
		$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
		$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );

		/* initialize aweber api wrapper */
		$aweber = new AWeberAPI($consumer_key , $consumer_secret);

		/* get account id */
		$account = $aweber->getAccount($accessKey, $accessSecret);
		$account_data = $account->data;
		$account_id = $account_data['id'];

		/* load subscriber object */
		$list = $account->loadFromUrl("/accounts/{$account_id}/lists/{$target_list}");
		$subscribers = $list->subscribers;

		/* prepare subscriber details for transfer */
		$params = array(
			'email' => $subscriber['wpleads_email_address'],
			'name' => $subscriber['wpleads_first_name']." ".$subscriber['wpleads_last_name']
		);

		$params = apply_filters('inboundnow_aweber_subscriber_params',$params);

		/* add new subscriber to the api */
		$new_subscriber = $subscribers->create($params);

		//var_dump($new_subscriber);
	}

	/* ADD SUBSCRIBER ON LANDING PAGE CONVERSION / CTA CONVERSION */
	static function inboundnow_aweber_landing_page_integratation($data) {
		if (get_post_meta($data['lp_id'],'inboundnow-aweber-aweber_integration',false)) {
			/* get target list */
			$target_list = get_post_meta($data['lp_id'],'inboundnow-aweber-aweber_list',true);
			self::inboundnow_aweber_add_subscriber( $target_list , $data );
		}
	}

	/* ADD SUBSCRIBER ON INBOUNDNOW FORM SUBMISSION */
	static function inboundnow_aweber_inboundnow_form_integratation($form_post_data , $form_meta_data ) {

		if (isset($form_post_data['email']))
			$subscriber['wpleads_email_address'] = $form_post_data['email'];

		if (isset($form_post_data['wpleads_email_address']))
			$subscriber['wpleads_email_address'] = $form_post_data['wpleads_email_address'];

		if (isset($form_post_data['first-name']))
			$subscriber['wpleads_first_name'] = $form_post_data['first-name'];

		if (isset($form_post_data['wpleads_first_name']))
			$subscriber['wpleads_first_name'] = $form_post_data['wpleads_first_name'];

		if (isset($form_post_data['last-name']))
			$subscriber['wpleads_last_name'] = $form_post_data['last-name'];

		if (isset($form_post_data['wpleads_last_name']))
			$subscriber['wpleads_last_name'] = $form_post_data['wpleads_last_name'];

		$form_settings = $form_meta_data['inbound_form_values'][0];
		parse_str($form_settings, $form_settings);

		if ($form_settings['inbound_shortcode_aweber_enable']=='on') {
			$target_list = $form_settings['inbound_shortcode_aweber_list_id'];
			self::inboundnow_aweber_add_subscriber($target_list , $subscriber);
		}
	}

}

Inbound_Aweber::init();

}
