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
	public static function create_email_queue_table_aaa() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "inbound_email_queue";

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "DROP TABLE $table_name";
		dbDelta( $sql );

		$sql = "CREATE TABLE $table_name (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`email_id` mediumint(9) NOT NULL,
			`variation_id` mediumint(9) NOT NULL,
			`lead_id` mediumint(9) NOT NULL,
			`token` tinytext NOT NULL,
			`type` tinytext NOT NULL,
			`status` tinytext NOT NULL,
			`datetime` DATETIME NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";


		dbDelta( $sql );
	}


}
