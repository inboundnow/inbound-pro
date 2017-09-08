<?php

/**
 * Class for setting up and executing activation routines
 * @package     InboundPro
 * @subpackage  Activation
 */

class Inbound_Pro_Activation {

	/**
	 * initiate class
	 */
	public function __construct() {
		self::load_hooks();
	}

	public static function load_hooks() {
		add_action('update_option_active_plugins', array( __CLASS__ , 'deactivate_standalone_plugins') );
	}



	public static function activate() {
		/* tell environment we are activating */
		$GLOBALS['is_activating'] = true;

		/* Activate shared components */
		self::activate_shared();

		/* Runs core plugin activations */
		self::activate_core_components();

		/* Import Stand Alone Options */
		self::import_stand_alone_settings();

		/* create extension upload folders if not exist */
		self::create_upload_folders();

		/* defines roles and capabilities */
		self::add_roles();

		/* Setup extras */
		self::run_extras();

		/* Schedule Events */
		self::schedule_events();

		/* update db version numbers */
		self::store_version_data();


	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'inbound_mailer_heartbeat' );
		wp_clear_scheduled_hook( 'inbound_automation_heartbeat' );
		wp_clear_scheduled_hook( 'inbound_heartbeat' ); /* legacy */
	}

	/**
	 * Creates transient records of past and current version data
	 */
	public static function store_version_data() {
		$old = get_option('inbound_pro_current_version');
		update_option( 'inbound_pro_previous_version' , $old , false);
		update_option( 'inbound_pro_current_version' , INBOUND_PRO_CURRENT_VERSION , false);
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
		if ( INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9 ) {
			self::activate_pro_components();
		}
	}

	/**
	 * Runs license protected activation functions
	 */
	public static function activate_pro_components() {
		update_option('inbound_activate_automation' , true);
		update_option('inbound_activate_mailer' , true);
	}



	/**
	 *  Runs extras & fires hook
	 */
	public static function run_extras() {

		do_action( 'inbound_pro_activate' );

		/* set inbound pro welcome page */
		set_transient( '_inbound_pro_welcome', true, 10 );

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

		if (!is_dir( INBOUND_PRO_UPLOADS_PATH . 'assets' )) {
			wp_mkdir_p( INBOUND_PRO_UPLOADS_PATH . 'assets' );
		}

		if (!is_dir( INBOUND_PRO_UPLOADS_PATH . 'assets/images/' )) {
			wp_mkdir_p( INBOUND_PRO_UPLOADS_PATH . 'assets/images' );
		}

		if (!is_dir( INBOUND_PRO_UPLOADS_PATH . 'assets/images/' )) {
			wp_mkdir_p( INBOUND_PRO_UPLOADS_PATH . 'assets/lang' );
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
	 * Schedule cronjob events
	 */
	public static function schedule_events() {
		if (! wp_next_scheduled ( 'inbound-pro/check-for-updates' )) {
			wp_schedule_event(time(), 'twicedaily', 'inbound-pro/check-for-updates');
		}
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

		/* deactivate leads if active */
		if( is_plugin_active('leads/leads.php') ) {
			deactivate_plugins('leads/leads.php');
		}

		/* deactivate acf4 if active */
		if( is_plugin_active('advanced-custom-fields/acf.php') ) {
			deactivate_plugins('advanced-custom-fields/acf.php');
		}

		/* deactivate acf5 if active */
		if( is_plugin_active('advanced-custom-fields-pro/acf.php') ) {
			deactivate_plugins('advanced-custom-fields-pro/acf.php');
		}
	}

	/*
	 * Import Stand Alone Plugin Settings - Runs on first install
	 * @introduced: 1.7.4.8.4
	*/
	public static function import_stand_alone_settings() {

		if (get_option('inbound_pro_settings_imported')) {
			return;
		}

		$inbound_settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());

		/* Import Landing Pages Settings */
		if (!isset($inbound_settings['landing-pages'])) {
			$landing_page_permalink_prefix = get_option(  'lp-main-landing-page-permalink-prefix', 'go' );
			$sticky_variations = get_option( 'lp-main-landing-page-rotation-halt', '0' );
			$disable_variant_testing = get_option( 'lp-main-landing-page-disable-turn-off-ab', '0' );

			$inbound_settings['landing-pages']['landing-page-permalink-prefix'] = $landing_page_permalink_prefix;
			$inbound_settings['landing-pages']['landing-page-rotation-halt'] = $sticky_variations;
			$inbound_settings['landing-pages']['landing-page-disable-turn-off-ab'] = $disable_variant_testing;
		}

		/* Import Leads Settings */
		if (!isset($inbound_settings['leads'])) {
			$tracking_ids = get_option('wpl-main-tracking-ids', '');
			$exclude_tracking_ids = get_option('wpl-main-exclude-tracking-ids', '');
			$page_view_tracking = get_option('wpl-main-page-view-tracking', 1);
			$search_tracking = get_option('wpl-main-search-tracking', 1);
			$comment_tracking = get_option('wpl-main-comment-tracking', 1);
			$enable_dashboard = get_option('wpl-main-enable-dashboard', 1);
			$disable_widgets = get_option('wpl-main-disable-widgets', 1);
			$full_contact = get_option('wpl-main-extra-lead-data', '');
			$inbound_admin_notification_inboundnow_link = get_option('wpl-main-inbound_admin_notification_inboundnow_link', 1);
			$inbound_forms_enable_akismet = get_option('wpl-main-inbound_forms_enable_akismet', 0);

			$inbound_settings['leads']['tracking-ids'] = $tracking_ids;
			$inbound_settings['leads']['exclude-tracking-ids'] = $exclude_tracking_ids;
			$inbound_settings['leads']['page-view-tracking'] = $page_view_tracking;
			$inbound_settings['leads']['search-tracking'] = $search_tracking;
			$inbound_settings['leads']['comment-tracking'] = $comment_tracking;
			$inbound_settings['leads']['enable-dashboard'] = $enable_dashboard;
			$inbound_settings['leads']['disable-widgets'] = $disable_widgets;
			$inbound_settings['leads']['extra-lead-data'] = $full_contact;
			$inbound_settings['leads']['inbound_admin_notification_inboundnow_link'] = $inbound_admin_notification_inboundnow_link;
			$inbound_settings['leads']['inbound_forms_enable_akismet'] = $inbound_forms_enable_akismet;
		}

		/* Import Call to Action Settings */
		if (!isset($inbound_settings['cta'])) {
			$disable_variant_testing = get_option('wp-cta-main-disable-ajax-variation-discovery', '0');
			$inbound_settings['cta']['main-disable-ajax-variation-discovery'] = $disable_variant_testing;
		}

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $inbound_settings );

		update_option( 'inbound_pro_settings_imported' , true );
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

	/**
	 * Check if plugin update occured and run activation routines
	 */
	public static function inbound_pro_activate_on_update(){
		$old = get_option('inbound_pro_current_version');
		if(!$old || $old != INBOUND_PRO_CURRENT_VERSION ) {
			Inbound_Pro_Activation::activate();
		}
	}
}

new Inbound_Pro_Activation();

/* Add Activation Hook */
register_activation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'activate' ) );
register_deactivation_hook( INBOUND_PRO_FILE , array( 'Inbound_Pro_Activation' , 'deactivate' ) );


/* Make sure activation runs on update */
add_action('admin_init',array( 'Inbound_Pro_Activation' ,'inbound_pro_activate_on_update'));