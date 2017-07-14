<?php
/**
 * Class to assist extensions with oAuth requirements
 * @package     InboundPro
 * @subpackage  Ouath
 *
*/

class Inbound_Pro_Oauth_Engine {

	static $settings_values;

	/**
	*	Load hooks and listners
	*/
	public static function init() {
		self::add_hooks();
	}

	/**
	*	Loads hooks and filters
	*/
	public static function add_hooks() {

		/* add js for iframe */
		add_action( 'admin_init' , array( __CLASS__ , 'inline_js' ) );

		/* add ajax listener for setting saves */
		add_action( 'admin_init' , array( __CLASS__ , 'request_access_token' ) );

		/* add ajax listener for setting saves */
		add_action( 'admin_init' , array( __CLASS__ , 'save_access_token' ) );

		/* add ajax listener for setting saves */
		add_action( 'admin_init' , array( __CLASS__ , 'display_success' ) );

		/* add ajax listener for setting saves */
		add_action( 'wp_ajax_revoke_oauth_tokens' , array( __CLASS__ , 'revoke_oauth_tokens' ) );


	}

	/**
	*  Request Access Token
	*/
	public static function request_access_token() {

		if ( !isset($_REQUEST['action']) || $_REQUEST['action'] != 'request_access_token' ) {
			return;
		}

		do_action('inbound_oauth/request_access_token');
		exit;
	}


	/**
	*  Request Access Token
	*/
	public static function save_access_token() {

		if ( !isset($_REQUEST['action']) || $_REQUEST['action'] != 'save_access_token' ) {
			return;
		}

		do_action('inbound_oauth/save_access_token');
		exit;
	}

	/**
	*  Display success page
	*/
	public static function display_success() {

		if ( !isset($_REQUEST['action']) || $_REQUEST['action'] != 'display_success' ) {
			return;
		}

		do_action('inbound_oauth/display_success');
		exit;
	}

	/**
	*  Revoke Access Token
	*/
	public static function revoke_oauth_tokens() {

		if ( !isset($_REQUEST['action']) || $_REQUEST['action'] != 'revoke_oauth_tokens' ) {
			return;
		}

		parse_str($_POST['input'] , $data );

		/* Empty oauth Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		unset($settings[ $data['fieldGroup'] ]['oauth']);

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );

		do_action('inbound_oauth/revoke_oauth_tokens');

	}


	/**
	*  Inline JS for success listening
	*/
	public static function inline_js() {

	}
}

Inbound_Pro_Oauth_Engine::init();