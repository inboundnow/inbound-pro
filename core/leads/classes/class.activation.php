<?php


if ( !class_exists('Leads_Activation') ) {

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
			wp_redirect(admin_url('edit.php?post_type=wp-lead'));
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
	
	public static function display_upgrade_routine_notice() {
		?>
		<div class="error">
			<p><?php _e( 'Leads plugin requires a database upgrade :', 'leads' ); ?> <a href='?plugin=leads&plugin_action=upgrade_routines'> <?php _e('Run Upgrade Processes' , 'leads' ); ?></a></p>
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
		
		/* Check WP Version */
		if ( version_compare( $wp_version , self::$version_wp, '<' ) ) {
			self::abort_activation( array( 
					'title' => 'Installation aborted',				
					'message' => __('Leads plugin could not be installed' , 'landing-pages'),
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
					'message' => __('Leads plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'Leads Version' , 'landing-pages' ) => LANDINGPAGES_CURRENT_VERSION,
									__( 'Required Leads Version' , 'landing-pages' ) => self::$version_lp
								),
					'solultion' => sprintf( __( 'Please update Leads to version %s or greater.' , 'landing-pages' ) , self::$version_lp )
				)
			);			
		}
		
		/* Check Calls to Action Version */
		if ( defined('WP_CTA_CURRENT_VERSION') && version_compare( WP_CTA_CURRENT_VERSION , self::$version_cta , '<' ) ) {
			self::abort_activation( array( 
					'title' => 'Installation aborted',				
					'message' => __('Leads Plugin could not be installed' , 'landing-pages'),
					'details' => array(
									__( 'Calls to Action Version' , 'landing-pages' ) => WPL_CURRENT_VERSION,
									__( 'Required Calls to Action Version' , 'landing-pages' ) => self::$version_cta
								),
					'solution' => sprintf( __( 'Please update Calls to Action to version %s or greater.' , 'landing-pages' ) , self::$version_cta )
				)
			);			
		}
		

	}
}

/* Add Activation Hook */
register_activation_hook( WPL_FILE , array( 'Leads_Activation' , 'activate' ) );
register_deactivation_hook( WPL_FILE , array( 'Leads_Activation' , 'deactivate' ) );

/* Add listener for uncompleted upgrade routines */
add_action( 'admin_init' , array( 'Leads_Activation' , 'run_upgrade_routine_checks' ) );

}
