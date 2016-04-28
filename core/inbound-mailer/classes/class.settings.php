<?php

/**
 * Creates Global Settings
 *
 * @package	Inbouns Mailer
 * @subpackage	Global Settings
*/

if ( !class_exists('Inbound_Mailer_Settings') ) {

	class Inbound_Mailer_Settings {

		static $core_settings;
		static $active_tab;

		/**
		*	Initializes class
		*/
		public function __construct() {
			self::add_hooks();
		}

		/**
		*	Loads hooks and filters
		*/
		public static function add_hooks() {
			/*  Add settings to inbound pro  */
			add_filter('inbound_settings/extend', array( __CLASS__  , 'define_pro_settings' ) );

		}

		/**
		*  Adds pro admin settings
		*/
		public static function define_pro_settings( $settings ) {
			global $inbound_settings;

			$service = (isset($inbound_settings['inbound-mailer']['mail-service'])) ? $inbound_settings['inbound-mailer']['mail-service'] : 'sparkpost';

			$settings['inbound-pro-setup'][] = array(
				'group_name' => INBOUND_EMAIL_SLUG ,
				'keywords' => __('email,mailer,marketing automation' , 'inbound-pro'),
				'fields' => array (
					array(
						'id'  => 'header_mailer',
						'type'  => 'header',
						'default'  => __('Inbound Mailer Settings', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'unsubscribe-page',
						'label'  => __('Unsubscribe Location', 'inbound-pro' ),
						'description'  => __( 'Where to send readers to unsubscribe. We auto create an unsubscribe page on activation, but you can use our shortcode on any page [inbound-email-unsubscribe]. ' , 'inbound-pro' ),
						'type'  => 'dropdown',
						'default'  => '',
						'options' => Inbound_Mailer_Settings::get_pages_array()
					),
					array(
						'id'  => 'mail-service',
						'label'  => __('Mail Service', 'inbound-pro' ),
						'description'  => __( 'Choose which email service will power the email component.' , 'inbound-pro' ),
						'type'  => 'dropdown',
						'default'  => 'sparkpost',
						'options' => array(
							'sparkpost' => __( 'SparkPost (new!)' , 'inbound-pro' ),
							'mandrill' => __( 'Mandrill' , 'inbound-pro' )
						),
						'hidden' => false,
						'reveal' => array(
							'selector' => null ,
							'value' => null
						)
					),
					array(
						'id'  => 'mandrill-key',
						'label'  => __('Mandrill API Key', 'inbound-pro' ),
						'description'  => __( 'Enter in your maindrill API Key here.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => '',
						'options' => null,
						'hidden' =>  ( $service == 'mandrill' ? false : true ) ,
						'reveal' => array(
							'selector' => '#mail-service' ,
							'value' => 'mandrill'
						)
					),
					array(
						'id'  => 'mandrill-setup-instructions',
						'type'  => 'ol',
						'label' => __( 'Setup Instructions:' , 'inbound-pro' ),
						'options' => array(
							'<a href="http://docs.inboundnow.com/guide/how-to-get-your-mandrill-api-key/" target="_blank">How to get your mandrill api key.</a>'
						),
						'hidden' => ( $service == 'mandrill' ? false : true ) ,
						'reveal' => array(
							'selector' => '#mail-service' ,
							'value' => 'mandrill'
						)
					),
					array(
						'id'  => 'sparkpost-key',
						'label'  => __('SparkPost API Key', 'inbound-pro' ),
						'description'  => __( 'Enter in your SparkPost API Key here.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => '',
						'options' => null,
						'hidden' =>  ( $service == 'sparkpost' ? false : true ) ,
						'reveal' => array(
							'selector' => '#mail-service' ,
							'value' => 'sparkpost'
						)
					),
					array(
						'id'  => 'sparkpost-status-display',
						'type'  => 'html',
						'label' => __( 'Status:' , 'inbound-pro' ),
						'value' => '',
						'callback' => array( 'Inbound_SparkPost_Stats' , 'display_api_status' ),
						'hidden' => ( $service == 'sparkpost' ? false : true ) ,
						'reveal' => array(
							'selector' => '#mail-service' ,
							'value' => 'sparkpost'
						)
					),
					array(
						'id'  => 'sparkpost-setup-instructions',
						'type'  => 'ol',
						'label' => __( 'Setup Instructions:' , 'inbound-pro' ),
						'options' => array(
							'<a href="https://app.sparkpost.com/account/credentials" target="_blank">'.__('Create an API Key','inbound-pro').'</a>'
						),
						'hidden' => ( $service == 'sparkpost' ? false : true ) ,
						'reveal' => array(
							'selector' => '#mail-service' ,
							'value' => 'sparkpost'
						)
					)
				)

			);


			return $settings;

		}

		/**
		*  Gets array of pages with ID => Label format
		*
		*/
		public static function get_pages_array() {
			$pages = get_pages();

			$pages_array = array() ;

			foreach ($pages as $page) {
				$pages_array[ $page->ID ] = $page->post_title;
			}

			return $pages_array;
		}




		/**
		*  Gets settings value depending on if Inbound Pro or single installation.
		*/
		public static function get_settings() {
			global $inbound_settings;

			$keys['unsubscribe_page'] = (isset($inbound_settings['inbound-mailer']['unsubscribe-page'])) ? $inbound_settings['inbound-mailer']['unsubscribe-page'] : null;
			$keys['mandrill-key'] = (isset($inbound_settings['inbound-mailer']['mandrill-key'])) ? $inbound_settings['inbound-mailer']['mandrill-key'] : null;
			$keys['sparkpost-key'] = (isset($inbound_settings['inbound-mailer']['sparkpost-key'])) ? $inbound_settings['inbound-mailer']['sparkpost-key'] : null;
			$keys['mail-service'] = (isset($inbound_settings['inbound-mailer']['mail-service'])) ? $inbound_settings['inbound-mailer']['mail-service'] : null;

			return $keys;

		}

		/**
		*  Get Settings URL
		*/
		public static function get_settings_url() {

			if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
				$settings_url = admin_url('edit.php?post_type=inbound-email&page=inbound_email_global_settings');
			} else {
				$settings_url = admin_url('admin.php?page=inbound-pro&setting=email');
			}

			return $settings_url;
		}
	}



	/**
	*	Loads Inbound_Mailer_Settings on admin_init
	*/
	function load_Inbound_Mailer_Settings() {
		$Inbound_Mailer_Settings = new Inbound_Mailer_Settings;
	}
	add_action( 'admin_init' , 'load_Inbound_Mailer_Settings' );

}

