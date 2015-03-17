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
	*  Create example email
	*/
	public static function create_example_email_xxxxx() {

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

		$email_settings = 'a:8:{s:10:"variations";a:2:{i:0;a:14:{s:7:"user_ID";i:2;s:10:"auto_draft";s:1:"1";s:16:"variation_status";s:6:"active";s:17:"selected_template";s:17:"simple-responsive";s:7:"content";s:0:"";s:2:"mm";s:2:"10";s:2:"jj";s:2:"31";s:2:"aa";s:4:"2014";s:2:"hh";s:2:"11";s:2:"mn";s:2:"48";s:2:"ss";s:2:"40";s:2:"ID";i:97098;s:6:"status";N;s:3:"acf";a:2:{s:19:"field_544ebf0aa4133";s:107:"http://inboundsoon.dev/wp-content/plugins/inbound-mailer/templates/simple-responsive/images/logo-wide-3.png";s:19:"field_544ebfe4a4135";s:108:"Dear {{first-name}},

Thank you for taking the time to read this email.

Warm regards from {{site-name}}";}}i:1;a:13:{s:7:"user_ID";i:2;s:16:"variation_status";s:6:"active";s:17:"selected_template";s:17:"simple-responsive";s:7:"content";s:0:"";s:2:"mm";s:2:"10";s:2:"jj";s:2:"31";s:2:"aa";s:4:"2014";s:2:"hh";s:2:"11";s:2:"mn";s:2:"48";s:2:"ss";s:2:"40";s:3:"acf";a:2:{s:19:"field_544ebf0aa4133";s:107:"http://inboundsoon.dev/wp-content/plugins/inbound-mailer/templates/simple-responsive/images/logo-wide-3.png";s:19:"field_544ebfe4a4135";s:118:"Dear {{first-name}},

Thank you for taking the time to read this email. Version B

Warm regards from {{site-name}}";}s:2:"ID";i:97098;s:6:"status";N;}}s:15:"inbound_subject";s:35:"Welcome to the new email component.";s:17:"inbound_from_name";s:11:"Inbound Now";s:18:"inbound_from_email";s:22:"noreply@inboundnow.com";s:25:"inbound_batch_send_nature";s:5:"ready";s:21:"inbound_send_datetime";s:0:"";s:18:"inbound_email_type";s:5:"batch";s:18:"inbound_recipients";s:3:"110";}';

		$email_settings = unserialize( $email_settings ); 
		

		/* Save Email Settings */
		Inbound_Email_Meta::update_settings( $email_id , $email_settings );

		/* add statistics */
		Inbound_Email_Stats::prepare_dummy_stats( $email_id );

		/* Insert required acf field maps */
		update_post_meta( $email_id , '_logo_url' , 'field_544ebf0aa4133');
		update_post_meta( $email_id , '_main_email_content' , 'field_544ebfe4a4135');
		
		
		/* add tags */

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
