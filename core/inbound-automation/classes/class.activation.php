<?php


if ( !class_exists('Inbound_Automation_Activation') ) {

class Inbound_Automation_Activation {

	static $version_wp;
	static $version_php;
	static $version_lp;
	static $version_leads;

	public static function activate() {
		self::load_static_vars();
		self::run_version_checks();
		self::activate_plugin();
		self::run_updates();
	}

	public static function deactivate() {

	}

	public static function load_static_vars() {

		self::$version_wp = '3.6';
		self::$version_php = '5.3';
		self::$version_lp = '1.3.6';
		self::$version_leads = '1.2.1';
	}

	public static function activate_plugin() {


		/* Update DB Markers for Plugin */
		self::store_version_data();

		/* Set Default Settings */
		self::set_default_settings();

		/* Activate shared components */
		self::activate_shared();

		/* create automation queue table */
		self::create_automation_queue_table();

		/* custom activation hook */
		do_action('activate/inbound-automation');
	}

	/**
	*This method loads public methods from the Inbound_Automation_Activation_Update_Routines class and automatically runs them if they have not been run yet.
	* We use transients to store the data, which may not be the best way but I don't have to worry about save/update/create option and the auto load process
	*/

	public static function run_updates() {


		/* Get list of updaters from Inbound_Automation_Activation_Update_Routines class */
		$updaters = get_class_methods('Inbound_Automation_Activation_Update_Routines');

		/* return if no routines */
		if (!$updaters) {
			return;
		}

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'ma_completed_upgrade_routines' ) ) ?  get_option( 'ma_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		/* Loop through updaters and run updaters that have not been ran */
		foreach ( $remaining as $updater ) {

			Inbound_Automation_Activation_Update_Routines::$updater();
			$completed[] = $updater;

		}

		/* Update this transient value with list of completed upgrade processes */
		update_option( 'ma_completed_upgrade_routines' , $completed );

	}

	/**
	*  This method checks if there are upgrade routines that have not been executed yet and notifies the administror if there are
	*
	*/
	public static function run_upgrade_routine_checks() {

		/* Listen for a manual upgrade call */
		if (isset($_GET['plugin_action']) && $_GET['plugin_action'] == 'upgrade_routines' && $_GET['plugin'] =='marketing-automation' ) {
			self::run_updates();
			wp_redirect(admin_url('edit.php?post_type=automation'));
			exit;
		}

		/* Get list of updaters from Inbound_Automation_Activation_Update_Routines class */
		$updaters = get_class_methods('Inbound_Automation_Activation_Update_Routines');

		/* return if no routines */
		if (!$updaters) {
			return;
		}

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'ma_completed_upgrade_routines' ) ) ?  get_option( 'ma_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		if (count($remaining)>0) {
			add_action( 'admin_notices', array( __CLASS__ , 'display_upgrade_routine_notice' ) );
		}
	}

	public static function display_upgrade_routine_notice() {

		?>
		<div class="error">
			<p><?php _e( 'We\'ve noticed that <strong>Automation Component</strong> requires a <strong>database upgrades</strong>. Please click the following link:', 'marketing-automation' ); ?> <a href='?plugin=marketing-automation&plugin_action=upgrade_routines'><?php _e('Run Upgrade Processes' , 'marketing-automation' ); ?></a></p>
		</div>
		<?php
	}


	/* Creates transient records of past and current version data */
	public static function store_version_data() {

		$old = get_transient('automation_current_version');
		set_transient( 'automation_previous_version' , $old );
		set_transient( 'automation_current_version' , INBOUND_AUTOMATION_CURRENT_VERSION );

	}

	public static function set_default_settings() {

	}

	/**
	*  Tells Inbound Shared to run activation commands
	*/
	public static function activate_shared() {
		set_transient( 'Inbound_Activate', true );
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

		deactivate_plugins( WP_CTA_FILE );
		exit;
	}

	public static function create_automation_queue_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . "inbound_automation_queue";
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `rule_id` varchar(255) NOT NULL,
			  `tasks` text NOT NULL,
			  `trigger_data` text NOT NULL,
			  `datetime` datetime NOT NULL,
			  `status` varchar(255) NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/* Checks if plugin is compatible with current server PHP version */
	public static function run_version_checks() {

		global $wp_version;

		/* Check PHP Version */
		if ( version_compare( phpversion(), self::$version_php, '<' ) ) {
			self::abort_activation(
				array(
					'title' => 'Installation aborted',
					'message' => __('Calls to Action plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'Server PHP Version' , 'landing-pages' ) => phpversion(),
									__( 'Required PHP Version' , 'landing-pages' ) => self::$version_php
								),
					'solultion' => sprintf( __( 'Please contact your hosting provider to upgrade PHP to %s or greater' , 'landing-pages' ) , self::$version_php )
				)
			);
		}

		/* Check WP Version */
		if ( version_compare( $wp_version , self::$version_wp, '<' ) ) {
			self::abort_activation( array(
					'title' => 'Installation aborted',
					'message' => __('Calls to Action plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'WordPress Version' , 'landing-pages' ) => $wp_version,
									__( 'Required WordPress Version' , 'landing-pages' ) => self::$version_wp
								),
					'solultion' => sprintf( __( 'Please update landing pages to version %s or greater.' , 'landing-pages' ) , self::$version_wp )
				)
			);
		}

		/* Check Landing Pages Version */
		if ( defined('LANDINGPAGES_CURRENT_VERSION') && version_compare( LANDINGPAGES_CURRENT_VERSION , self::$version_lp , '<' ) ) {
			self::abort_activation( array(
					'title' => 'Installation aborted',
					'message' => __('Calls to Action plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'Calls to Action Version' , 'landing-pages' ) => LANDINGPAGES_CURRENT_VERSION,
									__( 'Required Calls to Action Version' , 'landing-pages' ) => self::$version_lp
								),
					'solultion' => sprintf( __( 'Please update Calls to Action to version %s or greater.' , 'landing-pages' ) , self::$version_lp )
				)
			);
		}

		/* Check Leads Version */
		if ( defined('WPL_CURRENT_VERSION') && version_compare( WPL_CURRENT_VERSION , self::$version_leads , '<' ) ) {
			self::abort_activation( array(
					'title' => 'Installation aborted',
					'message' => __('Calls to Action plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'Leads Version' , 'landing-pages' ) => WPL_CURRENT_VERSION,
									__( 'Required Leads Version' , 'landing-pages' ) => self::$version_leads
								),
					'solultion' => sprintf( __( 'Please update Leads to version %s or greater.' , 'landing-pages' ) , self::$version_leads )
				)
			);
		}


	}
}

/* Add Activation Hook */
register_activation_hook( INBOUND_AUTOMATION_FILE , array( 'Inbound_Automation_Activation' , 'activate' ) );
register_deactivation_hook( INBOUND_AUTOMATION_FILE , array( 'Inbound_Automation_Activation' , 'deactivate' ) );

/* Add listener for uncompleted upgrade routines */
add_action( 'admin_init' , array( 'Inbound_Automation_Activation' , 'run_upgrade_routine_checks' ) );

}

