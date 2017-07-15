<?php

/**
 * Class that provides "common settings" area in email edit page. Settings include subject and email headers setup
 *
 * @package Mailer
 * @subpackage Management
*/

class Inbound_Mailer_Common_Settings {
	private static $instance;
	public $settings;

	public static function instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Inbound_Mailer_Common_Settings ) )
		{
			self::$instance = new Inbound_Mailer_Common_Settings;		
			self::$instance->add_common_settings();		
			self::$instance->load_settings();
		}

		return self::$instance;
	}


	/**
	*  	filters to add in core definitions to the calls to action extension definitions array 
	*/
	function add_common_settings() {
		self::add_addressing_settings();
		self::add_batch_send_settings();

	}
	
	/**
	*  Adds default mail settings
	*/
	function add_addressing_settings(){
	

		self::$instance->settings['email-settings']['subject'] =  array(
			'description' => __( 'Subject line of the email. This field is variation dependant!' , 'inbound-pro' ) ,
			'label' => __( 'Subject Line' , 'inbound-pro' ),
			'id'  => 'subject',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => false
		);
		
		self::$instance->settings['email-settings']['from_name'] =  array(
			'label' => __( 'From Name' , 'inbound-pro' ),
			'description' => __( 'The name of the sender. This field is variation dependant!' , 'inbound-pro' ) ,
			'id'  => 'from_name',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => false
		);
		
		self::$instance->settings['email-settings']['from_email'] =  array(
			'label' => __( 'From Email' , 'inbound-pro' ),
			'description' => __( 'The email address of the sender. This field is variation dependant!' , 'inbound-pro' ) ,
			'id'  => 'from_email',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => false
		);	
		
		self::$instance->settings['email-settings']['reply_email'] =  array(
			'label' => __( 'Reply Email' , 'inbound-pro' ),
			'description' => __( 'The email address recipients can reply to. This field is variation dependant!' , 'inbound-pro' ) ,
			'id'  => 'reply_email',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => false
		);			

	}

	
		
	/**
	* adds batch send settings
	*/
	function add_batch_send_settings() {

		$lead_lists = self::get_lead_lists_as_array();
		
		self::$instance->settings['batch-send-settings']['recipients'] = array(
			'id'  => 'recipients',
			'label' => __( 'Recipient Lists' , 'inbound-pro' ),
			'description' => __( 'Add the lists you would like to target with this email.' , 'inbound-pro' ),
			'type'  => 'select2', 
			'default' => '',
			'placeholder' => __( 'Select lists to send mail to.' , 'inbound-pro' ),
			'options' => $lead_lists,
			'disable_variants' => true
		);

		$tz = Inbound_Mailer_Scheduling::get_current_timezone();

		self::$instance->settings['batch-send-settings']['send_datetime'] = array(
			'id'  => 'send_datetime',
			'label' => __( 'Send Date/Time' , 'inbound-pro' ),
			'description' => __( 'Select the date and time you would like this message to send.' , 'inbound-pro' ),
			'type'  => 'datepicker', 
			'default' => '',
			'default_timezone_abbr' =>  $tz['abbr'] . '-UTC' . $tz['offset'] ,
			'placeholder' => __( 'Select lists to send mail to.' , 'inbound-pro' ),
			'options' => $lead_lists,
			'disable_variants' => true
		);


	}
	
	/**
	*  Makes template definitions filterable
	*/
	function load_settings() {
		self::$instance->settings = apply_filters( 'inbound_email_common_settings' , self::$instance->settings );
	}
	
	/**
	* Get an array of all lead lists
	*
	* @returns ARRAY of lead lists with term id as key and list name as value
	*/
	public static function get_lead_lists_as_array() {
		
		$array = array();

		$args = array(
			'hide_empty' => false,
		);

		$terms = get_terms('wplead_list_category', $args);

		foreach ( $terms as $term	) {
			$array[$term->term_id] = $term->name . " (".$term->count.")";
		}

		return $array;
	}
}

/**
*  Allows quick calling of instance
*/
function Inbound_Mailer_Common_Settings() {
	return Inbound_Mailer_Common_Settings::instance();
}
