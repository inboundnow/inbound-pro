<?php
/**
 * Class for defining and executing general activation routines for Mailer component
 * @package Mailer
 * @subpackage Activation
 */

class Inbound_Mailer_Activation {

	/**
	 * Initiate class
	 */
	public function __construct() {

		/* add http auth support for fast cgi */
		if ( strstr(php_sapi_name() , 'fcgi') ) {
			add_action('mod_rewrite_rules', array(__CLASS__, 'add_rewrite_rules'));
		}

		/* Run activation */
		self::run_activation();
	}


	public static function run_activation() {
		self::store_version_data();
		self::run_updates();
		delete_option('inbound_activate_mailer');
	}


	/**
	 *This method loads public methods from the Inbound_Mailer_Activation_Update_Routines class and automatically runs them if they have not been run yet.
	 * We use transients to store the data, which may not be the best way but I don't have to worry about save/update/create option and the auto load process
	 */

	public static function run_updates() {

		/* Get list of updaters from Inbound_Mailer_Activation_Update_Routines class */
		$updaters = get_class_methods('Inbound_Mailer_Activation_Update_Routines');

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'inbound_email_completed_upgrade_routines' ) ) ?  get_option( 'inbound_email_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		/* Loop through updaters and run updaters that have not been ran */
		foreach ( $remaining as $updater ) {

			Inbound_Mailer_Activation_Update_Routines::$updater();
			$completed[] = $updater;

		}

		/* Update this transient value with list of completed upgrade processes */
		update_option( 'inbound_email_completed_upgrade_routines' , $completed );

	}

	/**
	 *  This method checks if there are upgrade routines that have not been executed yet and notifies the administror if there are
	 *
	 */
	public static function run_upgrade_routine_checks() {

		/* Listen for a manual upgrade call */
		if (isset($_GET['plugin_action']) && $_GET['plugin_action'] == 'upgrade_routines' && $_GET['plugin'] =='inbound-email' ) {
			self::run_updates();
			wp_redirect(admin_url('edit.php?post_type=inbound-email'));
			exit;
		}

		/* Get list of updaters from Inbound_Mailer_Activation_Update_Routines class */
		$updaters = get_class_methods('Inbound_Mailer_Activation_Update_Routines');
		$updaters = (is_array($updaters)) ? $updaters : array();

		/* Get transient list of completed update processes */
		$completed = ( get_option( 'inbound_email_completed_upgrade_routines' ) ) ?  get_option( 'inbound_email_completed_upgrade_routines' ) : array();

		/* Get the difference between the two arrays */
		$remaining = array_diff( $updaters , $completed );

		if (count($remaining)>0) {
			add_action( 'admin_notices', array( __CLASS__ , 'display_upgrade_routine_notice' ) );
		}
	}

	public static function display_upgrade_routine_notice() {
		?>
		<div class="error">
			<p><?php _e( 'Inbound Email Component plugin requires  a database upgrade:', 'inbound-pro' ); ?> <a href='?plugin=inbound-email&plugin_action=upgrade_routines'><?php _e('Upgrade Database Now' , 'inbound-pro' ); ?></a></p>
		</div>
		<?php
	}


	/* Creates transient records of past and current version data */
	public static function store_version_data() {
		$old = get_transient('inbound_email_current_version');
		set_transient( 'inbound_email_previous_version' , $old );
		set_transient( 'inbound_email_current_version' , INBOUND_EMAIL_CURRENT_VERSION );
	}

	/**
	 * @param $rules
	 * @return string
	 */
	public static function add_rewrite_rules( $rules ) {
		if (stristr($rules, '[E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]')) {
			return $rules;
		}

		$rules_array = preg_split('/$\R?^/m', $rules);

		if (count($rules_array) < 3) {
			$rules_array = explode("\n", $rules);
			$rules_array = array_filter($rules_array);
		}

		$i = 0;
		foreach ($rules_array as $key => $val) {

			if (!trim($val)) {
				continue;
			}

			if (stristr($val, "RewriteEngine On")) {
				$new_val = "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]";
				$rules_array[$i] = $new_val;
				$i++;
				$rules_array[$i] = $val;
				$i++;
			} else {
				$rules_array[$i] = $val;
				$i++;
			}
		}

		$rules = implode("\n", $rules_array);


		return $rules;
	}

	/**
	 * @migration-type: db modification
	 * @mirgration: creates wp_inbound_email_queue table
	 */
	public static function create_email_queue_table() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "inbound_email_queue";

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`email_id` mediumint(9) NOT NULL,
			`variation_id` mediumint(9) NOT NULL,
			`lead_id` mediumint(9) NOT NULL,
			`post_id` mediumint(9) NOT NULL,
			`list_ids` text NOT NULL,
			`job_id` mediumint(9) NOT NULL,
			`rule_id` mediumint(9) NOT NULL,
			`token` tinytext NOT NULL,
			`type` tinytext NOT NULL,
			`tokens` mediumtext NOT NULL,
			`status` tinytext NOT NULL,
			`datetime` DATETIME NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";


		dbDelta( $sql );
	}
}



/* listen for activation command */
if (get_option('inbound_activate_mailer' , false) ) {
	new Inbound_Mailer_Activation();
}

/* Add listener for uncompleted upgrade routines */
add_action( 'admin_init' , array( 'Inbound_Mailer_Activation' , 'run_upgrade_routine_checks' ) );

