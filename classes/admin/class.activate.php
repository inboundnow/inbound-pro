<?php


class Inbound_Pro_Activation {

	/**
	 * initiate class
	 */
	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {
		add_action('admin_init' , array( __CLASS__ , 'run_pro_components_activation_check' ) );
	}


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

		/* if license valid activate pro core components */
		if ( Inbound_Pro_Plugin::get_customer_status() ) {
			self::activate_pro_components();
		}
	}

	/**
	 * Runs license protected activation functions
	 */
	public static function activate_pro_components() {

		/* automatically install certain extensions */
		self::install_extensions();

		if (class_exists('Inbound_Automation_Activation')) {
			Inbound_Automation_Activation::activate();
		}

		if (class_exists('Inbound_Mailer_Activation')) {
			Inbound_Mailer_Activation::activate();
		}

		delete_option('inbound_activate_pro_components');
	}

	/**
	 * Check to see if we should run the activation commands for our pro core components
	 */
	public static function run_pro_components_activation_check() {
		if (get_option('inbound_activate_pro_components' , false )) {
			Inbound_Pro_Activation::activate_pro_components();
		}
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


	/**
	 * Automatically install certain extensions on pro activation
	 */
	public static function install_extensions() {


		$extensions = array(
			'use-landing-page-as-homepage'
		);

		/* get pro templates dataset */
		$downloads = Inbound_Pro_Downloads::build_main_dataset();

		foreach ( $extensions as $id ) {
			/* skip extnesions that have been installed at least one time before */
			if ( get_option('inbound_installed_' . $id ) ) {
				continue;
			}

			/* get download array from */
			$download = $downloads[ $id ];

			/* get zip URL from api server */
			$download['download_location'] = Inbound_API_Wrapper::get_download_zip( array(
				'filename' => $download['zip_filename'] ,
				'type' =>  $download['download_type']
			));

			/* get upload path from download data */
			$download['extraction_path'] = Inbound_Pro_Downloads::get_upload_path( $download );

			Inbound_Pro_Downloads::install_download( $download );
			update_option('inbound_installed_' . $id , true , false );
		}

	}
}

new Inbound_Pro_Activation();

/* Add Activation Hook */
register_activation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'activate' ) );
register_deactivation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'deactivate' ) );


