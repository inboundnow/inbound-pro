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
	*  Create example email (turned off)
	*/
	public static function create_example_email_ballzzzzz() {

		// Set the post ID so that we know the post was created successfully
		$email_id = wp_insert_post(
			array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_title'		=>	__( 'Inbound Now Example Email' , 'inbound-email'),
				'post_status'		=>	'sent',
				'post_type'			=>	'inbound-email'
			)
		);

		$email_settings = '{"variations":[{"selected_template":"simple-responsive","user_ID":"2","subject":"Subject A","from_name":"a@inboundnow.com","from_email":"a@inboundnow.com","reply_email":"a@inboundnow.com","variation_status":"active","content":"","mm":"06","jj":"22","aa":"2015","hh":"13","mn":"08","ss":"43","acf":{"field_544ebf0aa4133":"http:\/\/inboundsoon.dev\/wp-content\/plugins\/_inbound-pro\/core\/inbound-mailer\/templates\/simple-responsive\/images\/logo-wide-3.png","field_544ebfe4a4135":"Dear [lead-field id=\"wpleads_first_name\" default=\"Subscriber\"],\r\n\r\nThank you for taking the time to read this email.\r\n\r\nWarm regards from Inbound Now"}},{"selected_template":"simple-responsive","user_ID":"2","subject":"Subject B","from_name":"b@inboundnow.com","from_email":"b@inboundnow.com","reply_email":"b@inboundnow.com","variation_status":"active","content":"","mm":"06","jj":"22","aa":"2015","hh":"13","mn":"09","ss":"21","acf":{"field_544ebf0aa4133":"http:\/\/inboundsoon.dev\/wp-content\/plugins\/_inbound-pro\/core\/inbound-mailer\/templates\/simple-responsive\/images\/logo-wide-3.png","field_544ebfe4a4135":"Dear [lead-field id=\"wpleads_first_name\" default=\"Subscriber\"],\r\n\r\nThank you for being a part of Inbound Now\r\n\r\nWarm regards from Inbound Now"}}],"send_datetime":"","timezone":"MDT-UTC-7","email_type":"batch","is_sample_email":"true"}';
		$email_settings = json_decode( $email_settings , true );


		/* Save Email Settings */
		Inbound_Email_Meta::update_settings( $email_id , $email_settings );

		/* Insert required acf field maps */
		update_post_meta( $email_id , '_logo_url' , 'field_544ebf0aa4133');
		update_post_meta( $email_id , '_main_email_content' , 'field_544ebfe4a4135');


		/* add tags */

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
