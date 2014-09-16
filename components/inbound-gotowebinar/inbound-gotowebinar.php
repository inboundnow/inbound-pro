<?php
/*
Plugin Name: Inbound Extension - GoToWebinar Integration
Plugin URI: http://www.inboundnow.com/
Description: Connects GoToWebinar to Inbound Form. 
Version: 1.1.2
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*/


if (!class_exists('Inbound_GoToWebinar')) {

class Inbound_GoToWebinar {

	/* Initialize class */
	public function __construct() {
		
		self::load_constants();
		self::authorization_listener_backend();
		self::authorization_listener_frontend();
		self::load_hooks();
		
	}
	
	/**
	*  Define constants
	*/
	public static function load_constants() {
		define('INBOUND_GOTOWEBINAR_CURRENT_VERSION', '1.1.2' ); 
		define('INBOUND_GOTOWEBINAR_LABEL' , 'GoToWebinar Integration' ); 
		define('INBOUND_GOTOWEBINAR_SLUG' , plugin_basename( dirname(__FILE__) ) ); 
		define('INBOUND_GOTOWEBINAR_FILE' ,  __FILE__ ); 
		define('INBOUND_GOTOWEBINAR_REMOTE_ITEM_NAME' , 'gotowebinar-integration' ); 
		define('INBOUND_GOTOWEBINAR_URLPATH', plugins_url( ' ', __FILE__ ) ); 
		define('INBOUND_GOTOWEBINAR_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
	}

	/**
	*  Loads hooks and filters
	*/
	public static function load_hooks() {
	
		/* Define global settings */
		add_filter( 'lp_define_global_settings', array( __CLASS__ , 'define_global_settings' ) , 10, 1);
		add_filter( 'wpleads_define_global_settings', array( __CLASS__ , 'define_global_settings' ) , 10, 1);
		add_filter( 'wp_cta_define_global_settings', array( __CLASS__ , 'define_global_settings' ) , 10, 1);
		
		/* authorize citrix api and store token */
		add_action( 'lp_save_global_settings' , array( __CLASS__ , 'save_api_key' ), 10, 2);
		add_action( 'wpleads_save_global_settings' , array( __CLASS__ , 'save_api_key' ), 10, 2);
		add_action( 'wp_cta_save_global_settings' , array( __CLASS__ , 'save_api_key' ), 10, 2);
		
		/* Check if API Key has been entered yet and if not throw admin notice */
		add_action('admin_notices', array( __CLASS__ , 'throw_admin_notice' ));
		
		/* Adds gotowebinar hookups to inboundnow forms */
		add_filter( 'inboundnow_forms_settings' , array( __CLASS__ , 'add_form_settings' ) , 10 , 1);
		
		/* Connect lead store system to gotowebinar integration */
		add_action( 'inboundnow_form_submit_actions' , array( __CLASS__ , 'add_lead_to_meeting' ) , 10 , 2 );
	}
	
	/**
	*  Admin listeners for gotowebinar authorization routine process
	*/
	public static function authorization_listener_backend() {
		if (!isset($_GET['nature']) || $_GET['nature']!='lp-gotowebinar' || !isset($_GET['code']) || !is_admin() ) {
			return;
		}
		
		self::authorize_next_steps();
	}

	/**
	*  Admin listeners for gotowebinar authorization routine process
	*/
	public static function authorization_listener_frontend() {
		if ( !get_transient('lp_gotowebinar_oauth') || !isset($_GET['code']) ) {
			return;
		}
		
		self::authorize_next_steps();
	}

	/**
	*  Runs next step in authorization process
	*/
	public static function authorize_next_steps() {
		/* Include GoToWebinar Wrapper Class */
		include_once(INBOUND_GOTOWEBINAR_PATH.'includes/class.gotowebinar_authorize.php');
		
		$target_url = admin_url()."edit.php?post_type=landing-page&page=lp_global_settings&tab=gotowebinar";
		$date = date("YmdHis"); 
		$api_key = get_option('gotowebinar-api-key',0);				


		$citrix = new LP_CitrixAPI();
		$oauth = $citrix->getOAuthToken( $api_key, $target_url, 2);
		if ($oauth->error||$oauth->int_err_code) {
			echo "apikey: $api_key <br>";
			echo "current_url: $target_url<br>";
			echo "Warning! Citirix returned this error message:<br><br>";
			var_dump($oauth);exit;
		}
		
		$oauth = json_decode($oauth,true);

		if (isset($oauth['access_token']))	{
			update_option('lp_gotowebinar_access_token_date',$date);
			update_option('lp_gotowebinar_code',$_GET['code']);
			update_option('lp_gotowebinar_access_token',$oauth['access_token']);
			update_option('lp_gotowebinar_access_refresh_token',$oauth['refresh_token']);
			update_option('lp_gotowebinar_access_organizer_key',$oauth['organizer_key']);
			update_option('lp_gotowebinar_access_account_key',$oauth['account_key']);
			update_option('gotowebinar-reset-oauth', null);
		}		
		
		?>
		   <a href='<?php echo $target_url; ?>'>Finalize Authorization</a>
		<?php 
		exit;
		
	}
	/**
	*  Adds admin settings
	*/
	public static function define_global_settings( $lp_global_settings ) {
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
		
		$lp_global_settings[$tab_slug]['settings'][] = 
		array(
			'id'  => 'gotowebinar_header',			
			'type'  => 'header', 
			'default'  => '<h4>GoToWebinar Integration</h4>',
			'options' => null
		);
		
		$lp_global_settings[$tab_slug]['settings'][] = 
		array(
			'id'  => 'gotowebinar-api-key',			
			'option_name'  => 'gotowebinar-api-key',			
			'type'  => 'text', 						
			'label'  => 'API Key',			
			'description'  => 'You must setup your own API Key! See: https://developer.citrixonline.com/ for creating your own API key.',		
			'options' => null
		);
		
		$lp_global_settings[$tab_slug]['settings'][] = 
		array(
			'id'  => 'gotowebinar-reset-oauth',			
			'option_name'  => 'gotowebinar-reset-oauth',			
			'label'  => 'Oauth Reset',			
			'description'  => 'Check this checkbox to reset and reauthorize your Citrix Account.',			
			'type'  => 'checkbox', 
			'default'  => '0', 
			'options' =>  array('1'=>'')
		);
			
		/*ADD LICENSE KEY*/
		$tab_slug = 'lp-license-keys'; //all extension license keys should be placed into this tab id
		$lp_global_settings[$tab_slug]['label'] = __('License Keys'); //Make sure to include the label in case no other extensions have defined this section tab label yet. No need to change the label. 
		
		//notice our 'type' is set to 'license-key'. This is mandatory for licence keys to work. Landign Pages Core handles license key activations. 
		$lp_global_settings[$tab_slug]['options'][] = lp_add_option($tab_slug,"license-key","wordpress-gotowebinar-plugin","",__("Go2Webinar License Key","gotowebinar"),__("Head to http://www.inboundnow.com/landing-pages/account/ to retrieve your license key for Landing Page Customizer for Landing Pages","webinar"), $options=null);
		//print_r($lp_global_settings);exit;
		return $lp_global_settings;
	}
	
	/**
	*  Listens for settings save and if oauth process is needed then fire it.
	*/
	public static function save_api_key( $field ) {
		if ($field['id'] != 'gotowebinar-api-key' ) {
			return;
		}
		
		update_option('gotowebinar-api-key',$_POST[$field['id']]);
		include_once(INBOUND_GOTOWEBINAR_PATH.'includes/class.gotowebinar_authorize.php');

		$check = get_option('lp_gotowebinar_code',0);
		$today = date("YmdHis"); 
		$old_time = get_option('lp_gotowebinar_access_token_date',0);
		$projected_time = date('YmdHis', strtotime("$old_time + 1 year"));

		//print_r($_POST);exit;
		if ( !$check || ( $today>$projected_time ) || ( isset($_POST['gotowebinar-reset-oauth'][0])&&$_POST['gotowebinar-reset-oauth'][0]==1 )) {

			$current_url = admin_url()."edit.php?post_type=landing-page&nature=lp-gotowebinar&page=lp_global_settings"; 
			//echo $current_url;exit;
			$citrix = new LP_CitrixAPI();
			$oauth = $citrix->getOAuthToken($_POST['gotowebinar-api-key'], $current_url, 1);
		}
	}
	
	/**
	*  Display admin notice if no API key available
	*/
	public static function throw_admin_notice() {
		if (get_option('gotowebinar-api-key',0)) {
			return;
		}
		
		echo '<div class="updated">
			   <p><b>Go2Webinar Action Required!</b> '.__('API Key must be entered for extension to work! Visit ','gotowebinar').' <a href="'.admin_url().'edit.php?post_type=landing-page&page=lp_global_settings&tab=lgotowebinar">Landing Pages -> Global Settings -> GoToWebinar '.__('to input license key','gotowebinar').'</a> </p>
			</div>';
				
	}
	
	/**
	*  Adds GoToWebinar connection settings to inbound forms
	*/
	public static function add_form_settings( $fields ) {
		$fields['forms']['options']['gotowebinar_enable'] =   array(
			'name' => __('Enable GoToWebinar Sync', 'inbound-pro'),
			'desc' => __('Enable/Disable GoToWebinar integration for this form.', 'inbound-pro'),
			'type' => 'checkbox',
			'std' => '',
			'class' => 'main-form-settings exclude-from-refresh' 
		);

		$mailchimp_lists = inboundnow_mailchimp_get_mailchimp_lists();
		$fields['forms']['options']['gotowebinar_webinar_id'] =   array(
			'name' => __('Enter Webinar ID', 'inbound-pro'),
			'id' => 'gotowebinar_webinar_id',
			'desc' => __('You can find your webinar key by going to "My Webinars", opening the details of the webinar you want to reigister users on and looking in the address bar for ?webinar= or /register/. The number following ?webinar= or /register/ is your webinar id.', 'inbound-pro'),
			'type' => 'text',
			'std' => '',
			'class' => 'main-form-settings exclude-from-refresh' 
		);
		
		return $fields;
	
	}
	
	public static function add_lead_to_meeting( $form_post_data , $form_meta_data) {
		
		$form_settings = $form_meta_data['inbound_form_values'][0];
		parse_str($form_settings, $form_settings);
		
		if ($form_settings['inbound_shortcode_gotowebinar_enable']!='on') {
			return;
		} 
		
		$subscriber['wpleads_email_address'] = (isset($form_post_data['wpleads_email_address'])) ? $form_post_data['wpleads_email_address'] : '';
		$subscriber['wpleads_first_name'] = (isset($form_post_data['wpleads_first_name'])) ? $form_post_data['wpleads_first_name'] : '';	
		$subscriber['wpleads_last_name'] = (isset($form_post_data['wpleads_last_name'])) ? $form_post_data['wpleads_last_name'] : "";
		
		foreach ($form_settings as $key => $setting) {
			if ( strstr( $key , 'inbound_shortcode_gotowebinar_webinar_id' ) ) {
				$webinar_id = $form_settings[ $key ];
				break;
			}
		}
		
	
		$api_key = get_option('gotowebinar-api-key',0);
		$organizer_key = get_option('lp_gotowebinar_access_organizer_key',0);
		$access_token = get_option('lp_gotowebinar_access_token',"");
		
		include INBOUND_GOTOWEBINAR_PATH."includes/class.gotowebinar.php";
		
		$citrix = new LP_Citrix($api_key);

		$citrix->set_organizer_key($organizer_key);
		$citrix->set_access_token($access_token);
		
		try	{
			$response = $citrix->citrixonline_create_registrant_of_webinar($webinar_id, $data = array('first_name' => $subscriber['wpleads_first_name'], 'last_name' => $subscriber['wpleads_last_name'], 'email'=> $subscriber['wpleads_email_address'])) ;
			$citrix->pr($response);
			
			$debug=0;
			if ($debug==1) 	{
				echo "Webinar ID: $webinar_id \r\n";
				echo "Organizer Key: $organizer_key \r\n";
				echo "Organizer Token: $access_token \r\n";						
				var_dump($response);
				print_r($data);
			}
			
		} catch (Exception $e) {	
			$citrix->pr($e->getMessage());
			var_dump($e->getMessage());
		}
	
	}
}

$Inbound_GoToWebinar = new Inbound_GoToWebinar();
}