<?php

/**
* Inbound Mail Daemon listens for and sends scheduled emails
*/
class Inbound_Mailer_Mandrill extends Inbound_Mail_Daemon {


	/**
	*	Sends email using Inbound Now's mandrill sender
	*/
	public static function send_email( $send_now = false) {
		$settings = Inbound_Mailer_Settings::get_settings();

		$mandrill = new Inbound_Mandrill(  $settings['api_key'] );

		$message = array(
			'html' => self::$email['body'],
			//'text' => self::get_text_version(),
			'subject' => self::$email['subject'],
			'from_email' => self::$email['from_email'],
			'from_name' => self::$email['from_name'],
			'to' => array(
				array(
					'email' => self::$email['send_address'],
					//'name' => 'Recipient Name',
					'type' => 'to'
				)
			),
			'headers' => array('Reply-To' => self::$email['reply_email']),
			'important' => false,
			'track_opens' => true,
			'track_clicks' => true,
			'auto_text' => true,
			'auto_html' => false,
			'inline_css' => false,
			'url_strip_qs' => false,
			'preserve_recipients' => false,
			'view_content_link' => true,
			'bcc_address' => false,
			/*
			'tracking_domain' => false,
			'signing_domain' => null,
			'return_path_domain' => null,
			'merge' => true,
			'merge_language' => 'mailchimp',
			'global_merge_vars' => array(
				array(
					'name' => 'merge1',
					'content' => 'merge1 content'
				)
			),
			'merge_vars' => array(
				array(
					'rcpt' => 'recipient.email@example.com',
					'vars' => array(
						array(
							'name' => 'merge2',
							'content' => 'merge2 content'
						)
					)
				)
			),
			*/
			'tags' => self::$tags[ self::$row->email_id ],
			/*
			'subaccount' => InboundNow_Connection::get_licence_key(),
			'google_analytics_domains' => array('example.com'),
			'google_analytics_campaign' => 'message.from_email@example.com',
			*/
			'metadata' => array(
				'email_id' => self::$row->email_id,
				'lead_id' => self::$row->lead_id,
				'variation_id' => self::$row->variation_id,
				'nature' => self::$email_settings['email_type']
			),
			'recipient_metadata' => array(
				array(
					'rcpt' => self::$email['send_address'],
					'values' => array(
						'lead_id' => self::$row->lead_id
					)
				)
			),
			/*
			'attachments' => array(
				array(
					'type' => 'text/plain',
					'name' => 'myfile.txt',
					'content' => 'ZXhhbXBsZSBmaWxl'
				)
			),
			'images' => array(
				array(
					'type' => 'image/png',
					'name' => 'IMAGECID',
					'content' => 'ZXhhbXBsZSBmaWxl'
				)
			)
			*/
		);
		$async = false;
		$ip_pool = 'Main Pool';

		if ( !$send_now ) {
			$send_at = gmdate( 'Y-m-d h:i:s \G\M\T' , strtotime( self::$row->datetime ) );
		} else {
			$send_at = false;
		}

		/* error_log( print_r( $message , true ) ); */
		self::$response = $mandrill->messages->send($message, $async, $ip_pool, $send_at );

		do_action( 'inbound_mandrill_send_event' , $message , $send_at );
	}




	/**
	*  Checks to see if meta fields have been created in Mandrill yet.
	*/
	public static function check_meta_fields() {
		/* get api key */
		$settings = Inbound_Mailer_Settings::get_settings();


		/* see if fields have been created for this api key yet */
		$history =  Inbound_Options_API::get_option( 'inbound-email' , 'mandrill-metafields-created' , array());
		if ( in_array( $settings['api_key'] , $history ) ) {
			return;
		}

		/* create them if they don't */
		if ( self::create_metafields_in_mandrill(  $settings['api_key'] ) ) {
			$history[] = $settings['api_key'];
			Inbound_Options_API::update_option( 'inbound-email' , 'mandrill-metafields-created' , $history );
		}
	}

	/**
	*  Runs a command to create metafields in Manrill
	*/
	public static function create_metafields_in_mandrill( $api_key ) {
		if (!$api_key) {
			return;
		}
		$mandrill = new Inbound_Mandrill(  $api_key );
		self::$response = $mandrill->metadata->add( 'email_id' , '{{value}}');
		self::$response = $mandrill->metadata->add( 'lead_id' , '{{value}}');
		self::$response = $mandrill->metadata->add( 'variation_id' , '{{value}}');
		self::$response = $mandrill->metadata->add( 'nature' , '{{value}}');

		if ( isset(self::$response['status']) && self::$response['status'] != 'error' || self::$response['name'] == 'ValidationError' ) {
			return 'created';
		} else {
			return false;
		}
	}
}