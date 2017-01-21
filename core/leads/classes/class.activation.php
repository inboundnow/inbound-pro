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
        
        /* Create Double Optin Page */
        self::create_double_optin_page();

        /* Create Double Optin List */
        self::create_double_optin_list();

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

    /**
     * Creates the "Confirm Double Optin Page" if the double optin page id is empty
     */
    public static function create_double_optin_page(){
		global $inbound_settings;

        $title = __( 'Confirm Subscription' , 'inbound-pro' );

		$double_optin_page_id = self::get_double_optin_page_id();

        // If the confirm page id isn't set
        if(empty($double_optin_page_id)) {

            /**check by name to see if the confirm page exists, if it doesn't create it**/
            if(null == get_page_by_title( $title )){
                // Set the page ID so that we know the post was created successfully
                $page_id = wp_insert_post(array(
					'comment_status'    =>  'closed',
					'ping_status'       =>  'closed',
					'post_title'        =>  $title,
					'post_status'       =>  'publish',
					'post_type'         =>  'page',
					'post_content'      =>  __('Thank you!' , 'inbound-pro')
				));
            }else{
            /*if the confirm page does exist, set the page id to its id*/
                $page_id = get_page_by_title( $title );
            }

			self::save_double_optin_page_id($page_id);
        }
        
    }

	/**
	 * Creates a maintenance list
	 */
	public static function create_double_optin_list() {
		/*get the double optin waiting list id*/
		if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
			$double_optin_list_id = get_option('list-double-optin-list-id', '');
		} else {
			$settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
			$double_optin_list_id = $settings['leads']['list-double-optin-list-id'];
		}

		// If the list doesn't already exist, create it
		if (false == get_term_by('id', $double_optin_list_id, 'wplead_list_category')) {

			/* create/get maintenance lists */
			$parent = self::create_lead_list( array(
				'name' => __( 'Maintenance' , 'inbound-pro' )
			));

			/* createget spam lists */
			$term = self::create_lead_list( array(
				'name' => __( 'Unconfirmed' , 'inbound-pro' ),
				'parent' =>$parent['id']
			));

			/*get the double optin waiting list id*/
			if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
				update_option('list-double-optin-list-id', $term['id']);
			} else {
				$settings = Inbound_Options_API::get_option('inbound-pro', 'settings');
				$settings['leads']['list-double-optin-list-id'] = $term['id'];
				Inbound_Options_API::update_option('inbound-pro', 'settings', $settings);
			}

		}
	}

	/**
	 * Retrieves double opt in page id
	 * @return mixed
	 */
	public static function get_double_optin_page_id() {
		global $inbound_settings;
		/*get the double optin confirm page id*/
		if(!defined('INBOUND_PRO_CURRENT_VERSION')){
			$double_optin_page_id = get_option('list-double-optin-page-id', '');
		}else{
			$double_optin_page_id = $inbound_settings['leads']['list-double-optin-page-id'];
		}

		return $double_optin_page_id;
	}

	/**
	 * Save Double Optin Page ID
	 * @param $page_id
	 */
	public static function save_double_optin_page_id( $page_id ) {
		global $inbound_settings;

		if(!defined('INBOUND_PRO_CURRENT_VERSION')) {
			update_option('list-double-optin-page-id', $page_id);
		} else {
			$inbound_settings['leads']['list-double-optin-page-id'] = $page_id;
			Inbound_Options_API::update_option('inbound-pro', 'settings', $settings);
		}
	}

	/**
	 *  Adds a new lead list.
	 *  @developer-note: This function is also located in Inbound_Leads class, but it's currently unreachable.
	 */
	public static function create_lead_list( $args ) {

		$params = array();

		/* if no list name is present then return null */
		if ( !isset( $args['name'] )) {
			return null;
		}

		if (isset( $args['description'] )) {
			$params['description'] = $args['description'];
		}

		if (isset( $args['parent'] )) {
			$params['parent'] = $args['parent'];
		} else {
			$params['parent'] = 0;
		}

		$term = term_exists(  $args['name'], 'wplead_list_category', $params['parent'] );

		/* if term does not exist then create it */
		if ( !$term ) {
			$term = wp_insert_term(	$args['name'], 'wplead_list_category', $params );
		}

		if ( is_array($term) && isset( $term['term_id'] ) ) {
			return array( 'id' => $term['term_id'] );
		} else if ( is_numeric($term) ) {
			return array( 'id' => $term );
		} else {
			return $term;
		}
	}

}

/* Add Activation Hook */
register_activation_hook( WPL_FILE , array( 'Leads_Activation' , 'activate' ) );
register_deactivation_hook( WPL_FILE , array( 'Leads_Activation' , 'deactivate' ) );

/* Add listener for uncompleted upgrade routines */
add_action( 'admin_init' , array( 'Leads_Activation' , 'run_upgrade_routine_checks' ) );

}
