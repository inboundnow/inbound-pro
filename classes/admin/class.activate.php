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
		add_action('update_option_active_plugins', array( __CLASS__ , 'deactivate_standalone_plugins') );
	}



	public static function activate() {
		/* tell environment we are activating */
		$GLOBALS['is_activating'] = true;

		/* Activate shared components */
		self::activate_shared();

		/* Runs core plugin activations */
		self::activate_core_components();

		/* create extension upload folders if not exist */
		self::create_upload_folders();

		/* defines roles and capabilities */
		self::add_roles();

		/* Setup extras */
		self::run_extras();


	}

	public static function deactivate() {

	}

	/**
	 *  Runs Core Activation Processes
	 */
	public static function activate_core_components() {
		if (class_exists('Landing_Pages_Activation')) {
			Landing_Pages_Activation::activate();
		}
		if (class_exists('CTA_Activation')) {
			CTA_Activation::activate();
		}
		if (class_exists('Leads_Activation')) {
			Leads_Activation::activate();
		}

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
		//self::install_extensions();

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

	public static function add_roles() {
		/**/
		//remove_role('inbound_marketer');
		$result = add_role( 'inbound_marketer', __('Inbound Marketer' , 'inbound-pro') ,
			array(
				'activate_plugins' => false,
				'delete_others_pages' => false,
				'delete_others_posts' => false,
				'delete_pages' => true,
				'delete_posts' => true,
				'delete_private_pages' => false,
				'delete_private_posts' => false,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'edit_dashboard' => true,
				'edit_others_pages' => false,
				'edit_others_posts' => false,
				'edit_pages' => true,
				'edit_posts' => true,
				'edit_private_pages' => true,
				'edit_private_posts' => true,
				'edit_published_pages' => true,
				'edit_published_posts' => true,
				'edit_theme_options' => false,
				'export' => true,
				'import' => true,
				'list_users' => true,
				'manage_categories' => true,
				'moderate_comments' => true,
				'promote_users' => false,
				'read'         => true,
				'read_private_pages'         => true,
				'read_private_posts'         => true,
				'switch_themes'         => false,
				'upload_files'   => true,
				'delete_posts' => true,
			)
		);


	}

	/**
	 *  Tells Inbound Shared to run activation commands
	 */
	public static function activate_shared() {
		update_option( 'Inbound_Activate', true );
	}

	/**
	 * Deactivate stand alone Inbound Now plugins
	 */
	public static function deactivate_standalone_plugins() {
		if ( !is_admin() || !get_option( 'Inbound_Activate' ) ) {
			return;
		}

		/* deactivate landing pages if active */
		if( is_plugin_active('landing-pages/landing-pages.php') ) {
			deactivate_plugins('landing-pages/landing-pages.php');
		}

		/* deactivate calls to action if active */
		if( is_plugin_active('cta/calls-to-action.php') ) {
			deactivate_plugins('cta/calls-to-action.php');
		}

		/* deactivate leads if active */
		if( is_plugin_active('leads/leads.php') ) {
			deactivate_plugins('leads/leads.php');
		}
	}

	/**
	 * Automatically install certain extensions on pro activation
	 */
	public static function install_extensions() {

		$extension = array();

		/*
		$extensions = array(
			'use-landing-page-as-homepage'
		);
		*/


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
			$download['download_location'] = Inbound_API_Wrapper::get_download_zip( $download );

			/* bail if fail */
			if (!strstr($download['download_location'] , 'http' )) {
				return;
			}

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
