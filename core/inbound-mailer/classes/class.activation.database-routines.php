<?php

/**
*   Public methods in this class will be run at least once during plugin activation script.
*
*   Updater methods fired are stored in transient to prevent repeat processing
*/


class Inbound_Mailer_Activation_Update_Routines {

	/**
	*  Create default Unsubscribe page
	*/
	public static function create_default_unsubscribe_page_x() {

		$title = __( 'Unsubscribe' , 'inbound-email' );

		// If the page doesn't already exist, then create it
		if( null == get_page_by_title( $title ) ) {

			// Set the post ID so that we know the post was created successfully
			$post_id = wp_insert_post(
				array(
					'comment_status'	=>	'closed',
					'ping_status'		=>	'closed',
					'post_title'		=>	$title,
					'post_status'		=>	'publish',
					'post_type'			=>	'page',
					'post_content' 		=>  '[inbound-email-unsubscribe]'
				)
			);

			Inbound_Options_API::update_option( 'inbound-email' , 'unsubscribe-page' , $post_id);
		}

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


	/**
	 * Alerter table
	* @migration-type: db modification
	* @mirgration: creates wp_inbound_email_queue table
	*/
	public static function alter_inbound_email_queue_add_fields_1() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "inbound_email_queue";

		$charset_collate = $wpdb->get_charset_collate();

		/* add ip field if does not exist */
		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'list_ids'"  );
		if(empty($row)){
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD `job_id` mediumint(9) NOT NULL" );
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD `rule_id` mediumint(9) NOT NULL" );
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD `post_id` mediumint(9) NOT NULL" );
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD `list_ids` text NOT NULL" );
			$wpdb->get_results( "ALTER TABLE {$table_name} ADD `tokens` mediumtext NOT NULL" );
		}

	}

	/**
	 * Alerter table
	* @migration-type: db modification
	* @mirgration: creates wp_inbound_email_queue table
	*/
	public static function alter_inbound_email_queue_alter_fields() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "inbound_email_queue";

		$charset_collate = $wpdb->get_charset_collate();

		/* alter ip field to fix bad field types */
		$wpdb->get_results( "ALTER TABLE {$table_name} MODIFY COLUMN `tokens` MEDIUMTEXT " );



	}


}
