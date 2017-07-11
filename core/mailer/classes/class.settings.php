<?php

/**
 * Adds Mailer Component settings to Inbound Pro Setting Area
 *
 * @package Mailer
 * @subpackage	Settings
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

			$service = (isset($inbound_settings['mailer']['mail-service'])) ? $inbound_settings['mailer']['mail-service'] : 'sparkpost';
			$inbound_settings['mailer']['mail-service'] = $service;

			$settings['inbound-pro-setup'][] = array(
				'group_name' => INBOUND_EMAIL_SLUG ,
				'keywords' => __('email,mailer,marketing automation' , 'inbound-pro'),
				'fields' => array (
					array(
						'id'  => 'header-mailer',
						'type'  => 'header',
						'default'  => __('Inbound Mailer Settings', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'subheader-unsubscribe',
						'type'  => 'sub-header',
						'default'  => __('Unsubscribe Page', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'unsubscribe-shortcode',
						'label'  => __('Shortcode:', 'inbound-pro' ),
						'description'  => __( 'This shortcode can be used to produce an unsubscribe form. Inbound Now automatically creates an Unsubscribe page with this shortcode on activation.' , 'inbound-pro' ),
						'type'  => 'text',
						'readonly'  => true,
						'default'  => '[inbound-email-unsubscribe]',
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
						'id'  => 'unsubscribe-show-lists',
						'label'  => __('Show Lists', 'inbound-pro' ),
						'description'  => __( 'Leave this set to on to give the reader the option to unsubscribe from individual lists.' , 'inbound-pro' ),
						'type'  => 'dropdown',
						'default'  => 'on',
						'options' => array(
							'on' => __('On' , 'inbound-pro'),
							'off' => __('Off' , 'inbound-pro'),
						)
					),
					array(
						'id'  => 'subheader-language',
						'type'  => 'sub-header',
						'default'  => __('Unsubscribe Labels', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'unsubscribe-header-text',
						'label'  => __('Unsubscribe Header', 'inbound-pro' ),
						'description'  => __( 'This text will display inside the unsubscribe button' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __('Unsubscribe:','inbound-pro')

					),
					array(
						'id'  => 'unsubscribe-button-text',
						'label'  => __('Unsubscribe Button', 'inbound-pro' ),
						'description'  => __( 'This text will display inside the unsubscribe button' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __('Unsubscribe','inbound-pro')

					),
					array(
						'id'  => 'mute-header-text',
						'label'  => __('Mute Section Header', 'inbound-pro' ),
						'description'  => __( 'This text will display inside the mute button' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __('Mute:','inbound-pro')

					),
					array(
						'id'  => 'unsubscribe-confirmation-message',
						'label'  => __('Confirmation Message #1', 'inbound-pro' ),
						'description'  => __( 'This message will show after a reader has unsubscribed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __('Thank you!','inbound-pro')

					),
					array(
						'id'  => 'automation-unsubscribe-confirmation-message',
						'label'  => __('Confirmation Message #2', 'inbound-pro' ),
						'description'  => __( 'This message will show after a reader has clicked unsubscribe from an automated email series.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( 'You have been unsubscribed from this series!' , 'inbound-pro' )
					),
					/*
					array(
						'id'  => 'unsubscribe-notice-automation-series',
						'label'  => __('Confirmation Message #2', 'inbound-pro' ),
						'description'  => __( 'This message will show after a reader has clicked unsubscribe within an email automated series.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( 'You have unsubscribed!<br>Additional options below.', 'inbound-pro')

					),*/
					array(
						'id'  => 'unsubscribe-comments-header-1',
						'label'  => __('Comment Prompt', 'inbound-pro' ),
						'description'  => __( 'This message is meant to encourage the reader to leave an unsubscribe comments.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( 'Please help us improve by providing us with feedback.' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-comments-header-2',
						'label'  => __('Comment Area Header', 'inbound-pro' ),
						'description'  => __( 'This is a general header denoting the comment textarea below.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( 'Comments' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-all-lists-label',
						'label'  => __('"All Lists" label', 'inbound-pro' ),
						'description'  => __( 'When readers are given the option to unsubscribe from all lists, this label is displayed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( 'All Lists' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-1-months',
						'label'  => __('1 Month Label', 'inbound-pro' ),
						'description'  => __( 'When readers are given the option to unsubscribe for 1 month, this label is displayed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( '1 Month' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-3-months',
						'label'  => __('3 Months Label', 'inbound-pro' ),
						'description'  => __( 'When readers are given the option to unsubscribe for 3 months, this label is displayed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( '3 Months' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-6-months',
						'label'  => __('6 Months Label', 'inbound-pro' ),
						'description'  => __( 'When readers are given the option to unsubscribe for 6 months, this label is displayed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( '6 Months' , 'inbound-pro' )
					),
					array(
						'id'  => 'unsubscribe-12-months',
						'label'  => __('12 Months Label', 'inbound-pro' ),
						'description'  => __( 'When readers are given the option to unsubscribe for 12 months, this label is displayed.' , 'inbound-pro' ),
						'type'  => 'text',
						'default'  => __( '12 Months' , 'inbound-pro' )
					),
					array(
						'id'  => 'subheader-unsubscribe',
						'type'  => 'sub-header',
						'default'  => __('Processing Engine', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'processing-limit',
						'label'  => __('Processing Limit', 'inbound-pro' ),
						'description'  => __( 'We will schedule/process this many emails to be sent to your email service every two minutes. If your server is having trouble handling 30 emails per cycle, please try reducing the number. Note that this will slow down the send process. To make up for delayed sends please schedule your email to be sent in the future. ' , 'inbound-pro' ),
						'type'  => 'number',
						'default'  => '30'
					),
					array(
						'id'  => 'processing-threads',
						'label'  => __('Processing Threads', 'inbound-pro' ),
						'description'  => __( '(Inactive) We can increase this number to multiply our processing powers. Be very careful though, this could cause your server to overwork and damage your sending efforts. If you\'re not sure what you are doing leave this to 1.' , 'inbound-pro' ),
						'type'  => 'number',
						'default'  => '1'
					),

					array(
						'id'  => 'subheader-unsubscribe',
						'type'  => 'sub-header',
						'default'  => __('Mail Service Setup', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'mail-service',
						'label'  => __('Mail Service', 'inbound-pro' ),
						'description'  => __( 'Choose which email service will power the email component.' , 'inbound-pro' ),
						'type'  => 'dropdown',
						'default'  => 'none',
						'options' => array(
							'none' => __( 'none' ),
							'sparkpost' => __( 'SparkPost' , 'inbound-pro' )
						),
						'hidden' => false,
						'reveal' => array(
							'selector' => null ,
							'value' => null
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

			$keys['unsubscribe_page'] = (isset($inbound_settings['mailer']['unsubscribe-page'])) ? $inbound_settings['mailer']['unsubscribe-page'] : null;
			$keys['sparkpost-key'] = (isset($inbound_settings['mailer']['sparkpost-key'])) ? $inbound_settings['mailer']['sparkpost-key'] : null;
			$keys['mail-service'] = (isset($inbound_settings['mailer']['mail-service'])) ? $inbound_settings['mailer']['mail-service'] : null;

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

