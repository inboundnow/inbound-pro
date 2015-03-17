<?php


class Inbound_Pro_Activation {
	
	
	public static function activate() {

		/* Activate shared components */
		self::activate_shared();

		/* Runs core plugin activations */
		self::activate_core_components();
		
		/* create extension upload folders if not exist */
		self::create_upload_folders();
		
		/* Setup extras */
		self::run_extras();
		
		
	}
	
	public static function deactivate() {

	}
	
	/**
	*  Runs Core Activation Processes
	*/
	public static function activate_core_components() {
		Landing_Pages_Activation::activate();
		CTA_Activation::activate();
		Leads_Activation::activate();
		Inbound_Automation_Activation::activate();
		Inbound_Mailer_Activation::activate();	
	}
	
	/**
	*  Runs extras & fires hook
	*/
	public static function run_extras() {
		
		do_action( 'inbound_pro_activate' );
		
		/* set inbound pro welcome page */
		set_transient( '_inbound_pro_welcome', true, 30 );
		
		/* Disable Landing Page's Welcome Screen redirect */
		delete_transient( '_landing_page_activation_redirect' );
		
	}
	
	/**
	*  Make upload directories
	*/
	public static function create_upload_folders() {
		if (!is_dir( INBOUND_PRO_UPLOADS_PATH . 'extensions' )) {
			wp_mkdir_p( INBOUND_PRO_UPLOADS_PATH . 'extensions' );
		}	
	}
	
	/**
	*  Tells Inbound Shared to run activation commands
	*/
	public static function activate_shared() {
		update_option( 'Inbound_Activate', true );
	}
	

	
}

/* Add Activation Hook */
register_activation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'activate' ) );
register_deactivation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'deactivate' ) );
