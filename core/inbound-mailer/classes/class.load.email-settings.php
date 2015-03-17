<?php

/**
 * Extension hooks and filters as well as default settings for core components
 *
 * @package	Inbouns Mailer
 * @subpackage	Extensions
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
			'label' => __( 'Subject Line' , 'inbound-email' ),
			'description' => __( 'Subject line of the email' , 'inbound-email' ) ,
			'id'  => 'subject',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => false
		);
		
		self::$instance->settings['email-settings']['from_name'] =  array(
			'label' => __( 'From Name' , 'inbound-email' ),
			'description' => __( 'The name of the sender.' , 'inbound-email' ) ,
			'id'  => 'from_name',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => true
		);
		
		self::$instance->settings['email-settings']['from_email'] =  array(
			'label' => __( 'From Email' , 'inbound-email' ),
			'description' => __( 'The email address of the sender.' , 'inbound-email' ) ,
			'id'  => 'from_email',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => true
		);	
		
		self::$instance->settings['email-settings']['reply_email'] =  array(
			'label' => __( 'Reply Email' , 'inbound-email' ),
			'description' => __( 'The email address recipients can reply to.' , 'inbound-email' ) ,
			'id'  => 'reply_email',
			'type'  => 'text',
			'default'  => '',
			'class' => '',
			'disable_variants' => true
		);			

	}

	
		
	/**
	* adds batch send settings
	*/
	function add_batch_send_settings() {

		$lead_lists = Inbound_Leads::get_lead_lists_as_array();
		
		self::$instance->settings['batch-send-settings']['recipients'] = array(
			'id'  => 'recipients',
			'label' => __( 'Select recipients' , 'inbound-email' ),
			'description' => __( 'This option provides a placeholder for the selected template data.' , 'inbound-email' ),
			'type'  => 'select2', 
			'default' => '',
			'placeholder' => __( 'Select lists to send mail to.' , 'inbound-email' ),
			'options' => $lead_lists,
			'disable_variants' => true
		);

		$tz = Inbound_Mailer_Scheduling::get_current_timezone();

		self::$instance->settings['batch-send-settings']['send_datetime'] = array(
			'id'  => 'send_datetime',
			'label' => __( 'Send Date/Time' , 'inbound-email' ),
			'description' => __( 'Select the date and time you would like this message to send.' , 'inbound-email' ),
			'type'  => 'datepicker', 
			'default' => '',
			'default_timezone_abbr' =>  $tz['abbr'] . '-UTC' . $tz['offset'] ,
			'placeholder' => __( 'Select lists to send mail to.' , 'inbound-email' ),
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
}

/**
*  Allows quick calling of instance
*/
function Inbound_Mailer_Common_Settings() {
	return Inbound_Mailer_Common_Settings::instance();
}
