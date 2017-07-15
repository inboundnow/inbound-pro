<?php

/**
 * Class for defining and running activation routines
 *
 * @package Leads
 * @subpackage Activation
 */


class Leads_Activation {

	static $version_wp;
	static $version_php;
	static $version_lp;
	static $version_cta;

	public static function activate() {
		self::load_static_vars();
		self::run_version_checks();
		self::activate_plugin();
		self::run_updates();
	}

	public static function deactivate() {
		/* Disabled Lead UI */
		delete_option( 'Leads_Activated');
	}

	public static function load_static_vars() {

		self::$version_wp = '3.6';
		self::$version_php = '5.2';
		self::$version_lp = '1.3.6';
		self::$version_cta = '2.0.0';
	}

	public static function activate_plugin() {

		/* Update DB Markers for Plugin */
		self::store_version_data();

		/* Set Default Settings */
		self::set_default_settings();

		/* Activate shared components */
		self::activate_shared();

		/* Mark Active */
		add_option( 'Leads_Activated' , true );

	}

	/**
	*This method loads public methods from the Leads_Activation_Update_Routines class and automatically runs them if they have not been run yet.
	* We use transients to store the data, which may not be the best way but I don't have to worry about save/update/create option and the auto load process
	*/

	public static function run_updates() {
		/* remove update_post_meta hook to cut down on resources */
		remove_action('updated_post_meta', array( 'Leads_Post_Type' , 'record_meta_update'), 10, 4);

		/* Get list of updaters from Leads_Activation_Update_Routines class */
		$updaters = get_class_methods('Leads_Activation_Update_Routines');

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'leads_completed_upgrade_routines' ) ) ?  get_option( 'leads_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		/* Loop through updaters and run updaters that have not been ran */
		foreach ( $remaining as $updater ) {

			Leads_Activation_Update_Routines::$updater();
			$completed[] = $updater;

		}
            
		/* Update this transient value with list of completed upgrade processes */
		update_option( 'leads_completed_upgrade_routines' , $completed );

	}

	/**
	*  This method checks if there are upgrade routines that have not been executed yet and notifies the administror if there are
	*
	*/
	public static function run_upgrade_routine_checks() {

		/* Listen for a manual upgrade call */
		if (isset($_GET['plugin_action']) && $_GET['plugin_action'] == 'upgrade_routines' && $_GET['plugin'] =='leads' ) {
			self::run_updates();
			wp_redirect(wp_get_referer());
			exit;
		}

		/* Get list of updaters from Leads_Activation_Update_Routines class */
		$updaters = get_class_methods('Leads_Activation_Update_Routines');

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'leads_completed_upgrade_routines' ) ) ?  get_option( 'leads_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		if (count($remaining)>0) {
			add_action( 'admin_notices', array( __CLASS__ , 'display_upgrade_routine_notice' ) );
		}
	}


	/* Checks if plugin is compatible with current server PHP version */
	public static function run_version_checks() {

		global $wp_version;

		/* Check PHP Version */
		if ( version_compare( phpversion(), self::$version_php, '<' ) ) {
			self::abort_activation(
				array(
					'title' => 'Installation aborted',
					'message' => __('Leads plugin could not be installed' , 'landing-pages'),
					'details' => array(
						__( 'Server PHP Version' , 'landing-pages' ) => phpversion(),
						__( 'Required PHP Version' , 'landing-pages' ) => self::$version_php
					),
					'solultion' => sprintf( __( 'Please contact your hosting provider to upgrade PHP to %s or greater' , 'landing-pages' ) , self::$version_php )
				)
			);
		}
	}

	public static function display_upgrade_routine_notice() {
		?>
		<div class="error">
			<p><?php _e( 'Leads plugin requires a database upgrade. Please note that this could take awhile. ', 'inbound-pro' ); ?> <a href='?plugin=leads&plugin_action=upgrade_routines'> <?php _e('Run Upgrade Processes' , 'inbound-pro' ); ?></a></p>
		</div>
		<?php
	}


	/* Creates transient records of past and current version data */
	public static function store_version_data() {

		$old = get_transient('leads_current_version');
		set_transient( 'leads_previous_version' , $old );
		set_transient( 'leads_current_version' , WPL_CURRENT_VERSION );

	}

	public static function set_default_settings() {

	}

	/**
	*  Tells Inbound Shared to run activation commands
	*/
	public static function activate_shared() {
		update_option( 'Inbound_Activate', true );
	}

	/* Aborts activation and details
	* @param ARRAY $args array of message details
	*/
	public static function abort_activation( $args ) {
		echo $args['title'] . '<br>';
		echo $args['message'] . '<br>';
		echo 'Details:<br>';
		print_r ($args['details']);
		echo '<br>';
		echo $args['solution'];

		deactivate_plugins( WPL_FILE );
		exit;
	}

}

/* Add Activation Hook */
register_activation_hook( WPL_FILE , array( 'Leads_Activation' , 'activate' ) );
register_deactivation_hook( WPL_FILE , array( 'Leads_Activation' , 'deactivate' ) );

/* Add listener for uncompleted upgrade routines */
add_action( 'admin_init' , array( 'Leads_Activation' , 'run_upgrade_routine_checks' ) );
