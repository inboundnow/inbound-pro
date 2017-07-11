<?php

/**
 * Class Inbound_Mailer_SparkPost extends Inbound_Mail_Daemon to power SparkPost sends
 * @package Mailer
 * @subpackage SparkPost
 */
class Inbound_Mailer_SparkPost extends Inbound_Mail_Daemon {

	/**
	 *	Sends email to SparkPost
	 */
	public static function send_email( $send_now = false) {
		$settings = Inbound_Mailer_Settings::get_settings();

		$sparkpost = new Inbound_SparkPost(  $settings['sparkpost-key'] );

		if ( !$send_now && self::$row->datetime ) {

			$send_at = date( 'c' , strtotime( self::$row->datetime ) );

			if (isset(self::$email_settings['timezone'])) {
				$date_parts = explode('+' , $send_at );
				$timezone_parts = explode('UTC' , self::$email_settings['timezone'] );
				$send_at = $date_parts[0] .$timezone_parts[1].':00';
			}
		} else {
			$send_at = 'now';
		}

		/* create campaign id */
		if (isset(self::$email['is_test'])) {
			$campaign_id = 'test';
		} else {
			$campaign_id = self::$row->email_id . '_' . self::$row->variation_id;
		}

		$message_args = array(
			'recipients'         => array( // json array
				array(
					'address'           => array( // string|json object
						'email'     => self::$email['send_address'], // string
						'name'      => null, // string
						'header_to' => null, // string
					),
					'return_path'       => null, // string    Elite only
					'tags'              => self::$tags[ self::$row->email_id ], // json array
					'metadata'          => null, // json object
					'substitution_data' => null, // json object
				),
			),
			'content'            => array( // json object
				'html'          => self::$email['body'], // string
				'text'          => null, // string
				'subject'       => self::$email['subject'], // string
				'from'          => array( // string|json object
					'email' => self::$email['from_email'],
					'name'  => self::$email['from_name'],
				),
				'reply_to'      => null, // string
				'headers'       => null, // json obect
				'attachments'   => array(), // json array
				'inline_images' => array(), // json array
			),
			// Options
			'options'            => array( // json object
				'start_time'       => $send_at, // string  YYYY-MM-DDTHH:MM:SS+-HH:MM
				'open_tracking'    => true, // bool
				'click_tracking'   => true, // bool
				'transactional'    => true, // bool
				'sandbox'          => false, // bool
				'skip_suppression' => null, // bool
				'inline_css'       => null, // bool
			),
			'headers'            => array(
				'Content-type'  => 'application/json',
				'Authorization' =>  $settings['sparkpost-key'],
				'User-Agent'    => 'sparkpost-inbound',
			),
			'description'        => null, // string
			'campaign_id'        => $campaign_id , // string
			'metadata'           => array(
				'email_id' => self::$row->email_id,
				'lead_id' => self::$row->lead_id,
				'variation_id' => self::$row->variation_id,
				'rule_id' => self::$row->rule_id,
				'job_id' => self::$row->job_id,
				'nature' => self::$email_settings['email_type']
			), // json object
			'substitution_data'  => null, // json object
			'return_path'        => null, // string    Elite only
			'template_id'        => null, // string
			'use_draft_template' => null, // bool
		);

		/* error_log( print_r( $message , true ) ); */
		self::$response = $sparkpost->send( $message_args );

		do_action( 'mailer/sparkpost/send' , $message_args  );
	}

}