<?php
/*
Plugin Name: Inbound Extension - Twitter Integration
Plugin URI: http://www.inboundnow.com/
Description: Allows you to use the Full Contact API to determine a user's twitter account during a new lead save and then automatically follow that user from all authenticated twitter accounts added to this extension by the administrator. 
Version: 1.0.1
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*
*/



if ( !class_exists( 'Inbound_Auto_Follow_Leads' )) {

	class Inbound_Auto_Follow_Leads {
		
		static $full_contact_api_key;

		/**
		*  initiates class
		*/
		public function __construct() {

			global $wpdb;
			
			/* Load Twitter API Wrapper */
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );			
			include_once( 'inc/twitter-api-php-master/TwitterAPIExchange.php' );
		
			/* Define static variables */
			self::define_static_variables();
			
			/* Define constants */
			self::define_constants();
			
			/* Define hooks and filters */
			self::load_hooks();
			
			
		}
		
		/**
		*  Loads hooks and filters selectively
		*/
		public static function load_hooks() {
			
			/* Load Admin CSS */
			if ( is_admin() ){
				add_action( 'admin_head', array( __CLASS__, 'load_css') );
			}

			/* Setup Automatic Updating & Licensing */
			add_action('admin_init', array( __CLASS__ , 'license_setup') );
			
			/* Add sub-menu to Leads admin menu */
			add_action( 'admin_menu', array( __CLASS__, 'prepare_admin_menu') );
			
			/* Add lead submission hook to add new lead to twitter account(s) */
			if(self::$full_contact_api_key <> ''){			
				add_action( 'inbound_store_lead_post', array( __CLASS__, 'inbound_store_lead_post' ),	23 );
			} else {				
				add_action( 'admin_notices', array( __CLASS__, 'my_admin_notice_for_api_key_check') );
			}
			
			/* Checks if Leads plugin is active and throws notice if now */
			if (!function_exists('wpleads_check_active')){				
				add_action( 'admin_notices', array( __CLASS__, 'my_admin_notice_for_lead_plugin_check') );				
			}
			
			/* Add ajax listener to test auto follow capabilities of assigned account */
			add_action( 'wp_ajax_nopriv_inbound_test_auto_follow', array( __CLASS__, 'test_auto_follow') );
			add_action( 'wp_ajax_inbound_test_auto_follow',	array( __CLASS__, 'test_auto_follow') );
		}
		
		/**
		*  Defines static variables for class use
		*/
		public static function define_static_variables() {
			
			self::$full_contact_api_key = get_option( 'wpl-main-extra-lead-data' , "");
		}
		
		/**
		*  Defines constants
		*/
		public static function define_constants() {
			define('INBOUND_TWITTER_CURRENT_VERSION', '1.0.1' ); 
			define('INBOUND_TWITTER_LABEL' , 'Twitter Integration' ); 
			define('INBOUND_TWITTER_SLUG' , plugin_basename( dirname(__FILE__) ) ); 
			define('INBOUND_TWITTER_FILE' ,  __FILE__ ); 
			define('INBOUND_TWITTER_REMOTE_ITEM_NAME' , 'twitter-integration' ); 
			define('INBOUND_TWITTER_URLPATH', plugins_url( ' ', __FILE__ ) ); 
			define('INBOUND_TWITTER_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); 
		}
		
		/** 
		*  Throws admin notice if no full contact API key is inputted yet 
		*/
		public static function my_admin_notice_for_api_key_check() {
			
			
			echo '<div class="updated">
					<p>Please populate Leads <a href="'.admin_url().'edit.php?post_type=wp-lead&page=lead-management-twitter-plugin-admin-page/">Full Contact API Key</a> first. </p>
				</div>';
			
		}
		
		/**
		*  Throws admin notice if Leads is not activated
		*/
		public static function my_admin_notice_for_lead_plugin_check() {
			
			
			echo '<div class="updated">
					<p>Leads Plugin is either not installed or activated. Please install or activate from <a href="'.admin_url().'plugins.php">here</a>.</p>
				</div>';
			
		}
		
		/**
		* Setups Software Update API 
		*/
		public static function license_setup() {

			/*PREPARE THIS EXTENSION FOR LICESNING*/
			if ( class_exists( 'Inbound_License' ) ) {
				$license = new Inbound_License( INBOUND_TWITTER_FILE , INBOUND_TWITTER_LABEL , INBOUND_TWITTER_SLUG , INBOUND_TWITTER_CURRENT_VERSION  , INBOUND_TWITTER_REMOTE_ITEM_NAME ) ;
			}
		}

		/** 
		*  Loads admin CSS
		*/
		public static function load_css() {
			
			$screen = get_current_screen();
			
			if (isset($screen->base) && $screen->base != 'wp-lead_page_lead-management-twitter-plugin-admin-page' ) {
				return;
			}
			
			wp_enqueue_script('jquery');
			
			wp_enqueue_style('Inbound-Leads-Plugin-Twitter-admin', INBOUND_TWITTER_URLPATH . 'inc/css/inbound-leads-plugin-twitter-admin.css');

			/* Bootstrap pieces*/
			wp_enqueue_style('Inbound-Leads-Plugin-Twitter-admin-bootstrap-responsive', INBOUND_TWITTER_URLPATH . 'inc/css/bootstrap-responsive.css');
			wp_enqueue_style('Inbound-Leads-Plugin-Twitter-admin-jquery-ui-css',	INBOUND_TWITTER_URLPATH . 'inc/css/jquery-ui.css');
			
			/* jquery ui core , accordion */
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-accordion');
			
			/* jQuery Validate */
			wp_enqueue_script( 'Inbound-Leads-Plugin-Twitter-admin-jquery-validate', INBOUND_TWITTER_URLPATH . 'inc/js/jquery.validate.min.js' );
			
			/* load ladda processing buttons js */
			wp_register_script( 'ladda-js' , INBOUND_TWITTER_URLPATH . 'inc/Ladda/ladda.min.js' , array('jquery') );
			wp_enqueue_script( 'ladda-js' );
		
			/* load ladda processing buttons css */
			wp_register_style( 'ladda-css' , INBOUND_TWITTER_URLPATH . 'inc/Ladda/ladda-themeless.min.css');
			wp_enqueue_style( 'ladda-css' );
			
			/* Add page specific rules */
			wp_enqueue_script( 'Inbound-Leads-Plugin-Twitter-admin', INBOUND_TWITTER_URLPATH . 'inc/js/inbound-leads-plugin-twitter-admin.js' , array('ladda-js') );
		}

		/**
		*  Adds settings sub menu item to wp-admin Leads menu
		*/
		public static function prepare_admin_menu() {

			$leads_key = 'edit.php?post_type=wp-lead';
			
			if (current_user_can('manage_options')) {
				
				add_submenu_page($leads_key, __( 'Twitter' , 'leads' ) , __( 'Twitter' , 'leads' ), 'manage_options', 'lead-management-twitter-plugin-admin-page', array( __CLASS__, 'admin_page' ));
				

			}

		}
		
		/**
		*  Builds settings input array for settings page
		*/
		public static function wpleads_get_twitter_api_settings() {

			$wpleads_global_twitter_api_settings['settings'] =
			array(
				array(
					'id'	=> 'wpl-main-extra-lead-data',
					'label' => __('Full Contact API Key' , 'leads' ),
					'description' => sprintf( __("<p>Enter your Full contact API key. If you don't have one. Grab a free one here: %s </p>" , 'leads' ) , "<a href='https://www.fullcontact.com/developer/pricing/' target='_blank'>" , "</a>"),
					'type'	=> 'text',
					'default'	=> '',
					'options' => null
				)
			);
		
			return $wpleads_global_twitter_api_settings;
		}
		
		/**
		*  Renders admin_page
		*/
		public static function admin_page(){
			
			global $wpdb;
			
			$wpleads_global_twitter_api_settings = self::wpleads_get_twitter_api_settings();
			
			echo '<div class="wrap">
					<h2 class="nav-tab-wrapper"><a class="wpl-nav-tab nav-tab nav-tab-special-active" id="tabs-wpl-main">Auto Follow Settings</a>
					
					<button id="ltpc-test-auto-follow" href="#" class="add-new-h2 button-secondary  ladda-button"   data-style="slide-right" style="float: right;margin-left:10px;">Test Auto Follow</button>
					<a id="ltpc-add-twitter-account" href="#" class="add-new-h2 button-primary" style="float: right;">Add Twitter Acount</a>
				</h2>';
			
			if(isset($_POST['Inbound_Leads_Submit_Settings'])){
				
				if(isset($_POST['wpl-main-extra-lead-data'])){
					
					update_option('wpl-main-extra-lead-data', $_POST['wpl-main-extra-lead-data']);
					
				}
					
				$post_data = maybe_serialize($_POST);
					
				update_option('wpl-main-extra-twitter-account-lead-data', $post_data);
				
				echo '<div class="updated below-h2" id="message"><p>Settings updated.</p></div>';
				
			} 
			
			$twitter_app_data =	maybe_unserialize(get_option('wpl-main-extra-twitter-account-lead-data'));
			
			echo '<form method="POST" action="" id="ltpc-settings-form">
						<table style="display:block" id="ltpc-main" class="ltpc-tab-display">
							<tbody>';
							
							foreach($wpleads_global_twitter_api_settings['settings'] as $fields){
								
								$default = null;
								
								$fields['value'] = get_option($fields['id'], $default);
								
								echo '<tr>
									<th valign="top" style="font-weight:300;" class="wpl-gs-th"><div class="inbound-setting-label">'.$fields['label'].'</div></th>
									<td><input type="text" size="30" value="'.$fields['value'].'" id="'.$fields['id'].'" name="'.$fields['id'].'" class="required" /></td>
								</tr>';
							}
							
			
						
			echo '<tr>
					<th valign="top" style="font-weight:300;" class="wpl-gs-th"><div class="inbound-setting-label">[Doc] How to Create a Twitter Application</div></th>
					<td>
						<a href="http://docs.inboundnow.com/guide/create-twitter-application/" target="_blank">http://docs.inboundnow.com/guide/create-twitter-application/</a><BR>
					</td>
				</tr>';		
				
			echo '<tr>
					<th valign="top" style="font-weight:300;" class="wpl-gs-th"><div class="inbound-setting-label">[Doc] How to get a Full Contact API Key</div></th>
					<td>
						<a href="http://docs.inboundnow.com/guide/get-full-contact-api-key/" target="_blank">http://docs.inboundnow.com/guide/get-full-contact-api-key/</a><BR>
					</td>
				</tr>';
				
			echo '			</tbody>
						</table>';	
						
			$twitter_app_data_count = ( sizeof($twitter_app_data['ltpc-extra-lead-data-api-key']) > 0 ) ? sizeof($twitter_app_data['ltpc-extra-lead-data-api-key']) : 0;
			
			echo '		<input type="hidden" id="twitter-ac-count" value="'.$twitter_app_data_count.'" />';
						
			echo '		<div id="ltpc-twitter-account-wrap">';
			
			if( is_array($twitter_app_data) && sizeof($twitter_app_data) > 0){
				
				$count = 0;
			
				foreach($twitter_app_data['ltpc-extra-lead-data-api-key'] as $twitter_data){
					
					$counter = $count + 1;
					
					echo '	<h3>'.$twitter_app_data['ltpc-extra-lead-data-nick-name'][$count].'</h3>
							<div class="row-fluid">
								<div class="span12">
									<div class="span4"><label>Account Nick Name</label></div>
									<div class="span8"><span><input type="text" name="ltpc-extra-lead-data-nick-name[]" id="ltpc-extra-lead-data-nick-name-'. $counter.'" value="'.$twitter_app_data['ltpc-extra-lead-data-nick-name'][$count].'" class="required" /></span>
									</div>
								</div>	
								<div class="span12">
									<div class="span4"><label>API key</label></div>
									<div class="span8"><span><input type="text" name="ltpc-extra-lead-data-api-key[]" id="ltpc-extra-lead-data-api-key-'. $counter.'" value="'.$twitter_data.'" class="required" /></span>
									</div>
								</div>	
								<div class="span12">
									<div class="span4"><label>API secret</label></div>
									<div class="span8"><span><input type="text" name="ltpc-extra-lead-data-api-secret[]" id="ltpc-extra-lead-data-api-secret-'.$counter.'" value="'.$twitter_app_data['ltpc-extra-lead-data-api-secret'][$count].'" class="required" /></span></div>
								</div>
								<div class="span12"><div class="span4"><label>Access token</label></div> 
									<div class="span8"><span><input type="text" name="ltpc-extra-lead-data-access-token[]" id="ltpc-extra-lead-data-access-token-'.$counter.'" value="'.$twitter_app_data['ltpc-extra-lead-data-access-token'][$count].'" class="required" /></span></div>
								</div>
								<div class="span12"><div class="span4"><label>Access token secret</label></div> 
									<div class="span8"><span><input type="text" name="ltpc-extra-lead-data-access-token-secret[]" id="ltpc-extra-lead-data-access-token-secret-'.$counter.'" value="'.$twitter_app_data['ltpc-extra-lead-data-access-token-secret'][$count].'" class="required" /></span></div>
								</div>';
								
							if( $twitter_app_data['ltpc-extra-lead-data-enable-service-'.$counter]){
								echo 	'<div class="span12"><div class="span4"><label>Enable Service</label> </div>
											<div class="span8"><span><input type="checkbox" name="ltpc-extra-lead-data-enable-service-'.$counter.'" id="ltpc-extra-lead-data-enable-service-'.$counter.'" value="1" checked="checked" />Check or uncheck to enable/ disabling the service.</span>
											</div>
										</div>
									</div>';
							} else {
								
								echo 	'<div class="span12"><div class="span4"><label>Enable Service</label> </div>
											<div class="span8"><span><input type="checkbox" name="ltpc-extra-lead-data-enable-service-'.$counter.'" id="ltpc-extra-lead-data-enable-service-'.$counter.'" value="1" />Check or uncheck to enable/ disabling the service.</span>
											</div>
										</div>
									</div>';
							}
						
					$count++;
				}
			
			}
			
			echo '		</div>';
			
			echo '	<div style="float:left;padding-left:9px;padding-top:20px;">
							<input type="submit" class="button-primary" id="Inbound_Leads_Submit_Settings" name="Inbound_Leads_Submit_Settings" name="" tabindex="5" value="Save Settings">
						</div>
					</form>
				</div>';
					
		}
		
		/** 
		*  Actions that fire during a lead submission		
		*/
		public static function inbound_store_lead_post( $lead_data ) {
			
			if($lead_data['wpleads_email_address'] <> ''){
			
				$twitter_data =	self::get_full_contact_details( $lead_data['lead_id'] , $lead_data['wpleads_email_address']);
				
				if($twitter_data['status'] == 'true' && $twitter_data['username'] <> ''){
				
					$twitter_username = ( $twitter_data['username'] <> '' ) ? $twitter_data['username'] : null;

					$url = 'https://api.twitter.com/1.1/friendships/create.json';
					
					$postfields = array('screen_name' => $twitter_username );
					
					$requestMethod = 'POST';
					
					$twitter_username = str_replace('@','',$twitter_username);
					
					$twitter_app_data =	maybe_unserialize(get_option('wpl-main-extra-twitter-account-lead-data'));
					
					if(sizeof($twitter_app_data) > 0){
				
						$count = 0;
					
						foreach($twitter_app_data['ltpc-extra-lead-data-api-key'] as $key){
							
							$counter = $count + 1;
							
							if( $twitter_app_data['ltpc-extra-lead-data-enable-service-'.$counter]){
							
								if($twitter_app_data['ltpc-extra-lead-data-api-key'] <> '' && $twitter_app_data['ltpc-extra-lead-data-api-secret'] <> '' && $twitter_app_data['ltpc-extra-lead-data-access-token'] <> '' && $twitter_app_data['ltpc-extra-lead-data-access-token-secret'] <> ''){
						
									$TwitterAPIExchange = new TwitterAPIExchange( array(
										'oauth_access_token' => $twitter_app_data['ltpc-extra-lead-data-access-token'][$count],
										'oauth_access_token_secret' => $twitter_app_data['ltpc-extra-lead-data-access-token-secret'][$count],
										'consumer_key' => $key,
										'consumer_secret' => $twitter_app_data['ltpc-extra-lead-data-api-secret'][$count]
									));
									
									$response = $TwitterAPIExchange->setPostfields( $postfields )
													->buildOauth($url, $requestMethod)
													->performRequest();
										
									self::check_reponse_for_errors_and_save( $lead_data['lead_id'] , $response , $twitter_app_data['ltpc-extra-lead-data-access-token'][$count] );
									
								}
								
							}
							
							$count++;
							
						}
					}
				}
			}
		}
		
		/**
		*  Looks at response json for errors and throws them if no errors update lead's social data
		*/
		public static function check_reponse_for_errors_and_save( $lead_id , $response , $token ) { 
			
			$obj = json_decode($response);
			
			if (isset($obj->error)) {
				echo 'There has been an error message: " '. $obj->error . '" related to the account with access token: ' . $token;
			} 
		}
		
		/** 
		*  Makes call to Full Contact to get social details from email address
		*  
		*/
		public static function get_full_contact_details( $lead_id , $email_id){
			
			$data = array("email" => $email_id, "apiKey" => self::$full_contact_api_key);				
			$data_string = json_encode($data);	
			$params = http_build_query($data);																				
			$ch = curl_init('https://api.fullcontact.com/v2/person.json?'.$params);																		
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");																	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);																	
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);																		
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(																			
				'Content-Type: application/json',																				
				'Content-Length: ' . strlen($data_string))																		
			);																												
			
			$response_json = curl_exec($ch);

			$response_array = json_decode( $response_json );
			
			if($response_array->status == 200){
				
				update_post_meta( $lead_id , 'social_data' , json_decode( $response_json , true ) );
			
				foreach( $response_array->socialProfiles	as $socialProfile ){
					
					if($socialProfile->type == 'twitter'){
						
						$return_data = array(
							'status' => 'true',
							'username' => $socialProfile->username
						);
						
						break;
						
					} else {
						
						$return_data = array(
							'status' => 'false',
							'username' => ''
						);
						
					}
					
				}
				
			} else {
				
				$return_data = array(
							'status' => 'false',
							'username' => ''
						);
						
				
			}
			
			return $return_data;
			
		}
		
		/**
		*  Ajax listener for testing auto-follow capabilities of added accounts
		*/
		public static function test_auto_follow() {
		
			$dummy_lead = array(
				'wpleads_first_name' => 'Hudson' ,
				'wpleads_last_name' => 'Atwell',
				'wpleads_email_address' => 'atwell.publishing@gmail.com'
			);
			
			inbound_store_lead( $dummy_lead );
			
			die();
		
		}
		
	}

	/** 
	*  Load Inbound_Auto_Follow_Leads class in init
	*/
	function Load_Inbound_Auto_Follow_Leads() {
		$Inbound_Auto_Follow_Leads = new Inbound_Auto_Follow_Leads();
	}
	add_action( 'init' , 'Load_Inbound_Auto_Follow_Leads' );
}