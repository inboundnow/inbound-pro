<?php

/**
* Inbound Mail Daemon listens for and sends scheduled emails
*/
class Inbound_Mail_Daemon {

	static $table_name; /* name of the mysql table we use for querying queued emails */
	static $send_limit; /* number of emails we send during a processing job	(wp_mail only) */
	static $timestamp; /* the current date time in ISO 8601 gmdate() */
	static $dom; /* reusable object for parsing html for link modification */
	static $row; /* current mysql row object being processed */
	static $email_settings; /* settings array of the email being processed */
	static $templates; /* array of html templates for processing */
	static $tags; /* array of html templates for processing */
	static $email; /* arg array of email being processed */
	static $results; /* results from sql query */
	static $response; /* return result after send */

	/**
	*	Initialize class
	*/
	function __construct() {

		/* Load static vars */
		self::load_static_vars();

		/* Load hooks */
		self::load_hooks();

	}

	/**
	*	Loads static variables
	*/
	public static function load_static_vars() {
		global $wpdb;

		/* Set send limit */
		self::$send_limit = 150;

		/* Set target mysql table name */
		self::$table_name = $wpdb->prefix . "inbound_email_queue";

		/* Get now timestamp */
		self::$timestamp = gmdate( "Y-m-d\\TG:i:s\\Z" );

	}

	/*
	* Load Hooks & Filters
	*/
	public static function load_hooks() {

		/* Adds mail processing to Inbound Heartbeat */
		add_action( 'inbound_heartbeat', array( __CLASS__ , 'process_mail_queue' ) );

		/* For debugging */
		add_filter( 'init', array( __CLASS__ , 'process_mail_queue' ) , 12 );

	}


	public static function process_mail_queue() {

		if ( !isset( $_GET['test'] ) && current_filter() == 'init' ) {
			return;
		}

		/* send automation emails */
		self::send_automated_emails();

		/* send batch emails */
		self::send_batch_emails();
		exit;
	}


	/**
	*	Tells WordPress to send emails as HTML
	*/
	public static function toggle_email_type() {
		add_filter( 'wp_mail_content_type', array( __CLASS__ , 'toggle_email_type_html' ) );
	}
	
	/**
	*	Set email type to html for wp_mail
	*/
	public static function toggle_email_type_html( $type ) {
		return 'text/html';
	}

	/**
	*	Loads DOMDocument class object
	*/
	public static function toggle_dom_parser() {
		self::$dom = new DOMDocument;		
	}
	
	/**
	*	Rebuild links with tracking params
	*/
	public static function rebuild_links( $html ) {
		self::$dom->loadHTML($html);
		$links = self::$dom->getElementsByTagName('a');

		//Iterate over the extracted links and display their URLs
		foreach ($links as $link){
			
			$class = $link->getAttribute('class');
			$href = $link->getAttribute('href');
			
			/* Do not modify links with 'do-not-track' class */
			if ( $class == 'do-not-track' ) {
				continue;
			}
			
			/* build utm params */
			$params = array( 
				'utm_source' => self::$email['email_title'],
				'utm_medium' => 'email',
				'utm_campaign' => '',
				'lead_id' => self::$row->lead_id,
				'lead_lists' => implode( ',' , self::$email_settings['recipients'] ),
				'email_id' =>self::$row->email_id
			);	

			$new_link = add_query_arg( $params , $href );

			
			
			$html = str_replace( $href	, $new_link , $html );
			
		}
		
		return $html;
	}

	
	/**
	*	Sends scheduled automated emails
	*/
	public static function send_automated_emails() {
		global $wpdb;

		$query = "select * from ". self::$table_name ." WHERE `status` != 'processed' && `type` = 'automated' && `datetime` <	'". self::$timestamp ."' && `email_id` = `email_id` order by email_id  ASC LIMIT " .self::$send_limit;
		self::$results = $wpdb->get_results( $query );

		if (!self::$results) {
			return;
		}

		/* get first row of result set for determining email_id */
		self::$row = self::$results[0];
		
		/* Get email title */
		self::$email['email_title'] = get_the_title( self::$row->email_id );
		
		/* Get email settings if they have not been loaded yet */
		self::$email_settings = Inbound_Email_Meta::get_settings( self::$row->email_id );
		
		/* Build array of html content for variations */
		self::get_templates();
		
		/* Get tags for this email */
		self::get_tags();
		
		/* Make sure we send emails as html */
		self::toggle_email_type();
		
		/* load dom parser class object */
		self::toggle_dom_parser();
		
		foreach( self::$results as $row ) {
			
			self::$row = $row;

			self::get_email();

			self::$response = self::send_mandrill_email( );

			self::delete_from_queue();
		}
	}

	/**
	*	Sends scheduled batch emails
	*/
	public static function send_batch_emails() {
		global $wpdb;

		/* Get results for singular email id */
		$query = "select * from ". self::$table_name ." WHERE `status` != 'processed' && `type` = 'batch' && email_id = email_id order by email_id ASC LIMIT " .self::$send_limit;
		self::$results = $wpdb->get_results( $query );

		if (!self::$results) {
			return;
		}

		/* get first row of result set for determining email_id */
		self::$row = self::$results[0];
		
		/* Get email title */
		self::$email['email_title'] = get_the_title( self::$row->email_id );
		
		/* Get email settings if they have not been loaded yet */
		self::$email_settings = Inbound_Email_Meta::get_settings( self::$row->email_id );
		
		/* Build array of html content for variations */
		self::get_templates();
		
		/* Get tags for this email */
		self::get_tags();
		
		/* Make sure we send emails as html */
		self::toggle_email_type();
		
		/* load dom parser class object */
		self::toggle_dom_parser();

		$send_count = 1;
		foreach( self::$results as $row ) {

			self::$row = $row;

			/* make sure not to try and send more than wp can handle */
			if (	$send_count > self::$send_limit ){
				return;
			}

			self::get_email();

			self::send_mandrill_email();
			
			self::delete_from_queue();
			
			$send_count++;
		}
		
		/* mark batch email as sent if no more emails with this email id exists */
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM ". self::$table_name ." where email_id = '". self::$row->email_id ."'");
		if ($count<1) {
			self::mark_email_sent();
		}
	}
	
	
	/**
	*	Send email by lead id
	*/
	public static function send_solo_email( $args	) {
		global $wpdb;
		
		if ( !$args['email_id'] || !$args['email_address'] ) {
			return;
		}

		/* setup test tags */
		self::$tags[ $args['email_id'] ] = (isset($args['tags'])) ? $args['tags'] : array('test');
		
		/* setup email send params */
		self::$row = new stdClass();
		self::$row->email_id = $args['email_id'];
		self::$row->variation_id = $args['vid'];
		self::$row->lead_id = (isset($args['lead_id'])) ? $args['lead_id'] : 0;
		self::$row->datetime = gmdate( 'Y-m-d h:i:s \G\M\T');
		
		/* load extras */
		self::$email_settings = Inbound_Email_Meta::get_settings( self::$row->email_id );
		self::$email_settings['recipients'] = (isset($args['lead_lists'])) ? $args['lead_lists'] : array();
		self::get_templates();
		self::toggle_dom_parser();
		
		/* build email */
		self::$email['send_address'] = $args['email_address'];	
		self::$email['subject'] = self::get_variation_subject();
		self::$email['from_name'] = self::$email_settings['from_name'];
		self::$email['from_email'] = self::$email_settings['from_email'];
		self::$email['email_title'] = get_the_title( self::$row->email_id );
		self::$email['reply_email'] = self::$email_settings['reply_email'];
		self::$email['body'] = self::get_email_body();
		
		/* send email */
		self::send_mandrill_email();
	
		/* return mandrill response */
		//error_log(print_r(self::$response,true));
		return self::$response;
		
	}

	/**
	*	Sends email using Inbound Now's mandrill sender
	*/
	public static function send_mandrill_email() {
		$mandrill = new Mandrill();
		
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
			//'tracking_domain' => false,
			//'signing_domain' => null,
			//'return_path_domain' => null,
			//'merge' => true,
			//'merge_language' => 'mailchimp',
			//'global_merge_vars' => array(
			//	array(
			//		'name' => 'merge1',
			//		'content' => 'merge1 content'
			//	)
			//),
			//'merge_vars' => array(
			//	array(
			//		'rcpt' => 'recipient.email@example.com',
			//		'vars' => array(
			//			array(
			//				'name' => 'merge2',
			//				'content' => 'merge2 content'
			//			)
			//		)
			//	)
			//),
			'tags' => self::$tags[ self::$row->email_id ],
			'subaccount' => InboundNow_Connection::get_licence_key(),
			//'google_analytics_domains' => array('example.com'),
			//'google_analytics_campaign' => 'message.from_email@example.com',
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
			//'attachments' => array(
			//	array(
			//		'type' => 'text/plain',
			//		'name' => 'myfile.txt',
			//		'content' => 'ZXhhbXBsZSBmaWxl'
			//	)
			//),
			//'images' => array(
			//	array(
			//		'type' => 'image/png',
			//		'name' => 'IMAGECID',
			//		'content' => 'ZXhhbXBsZSBmaWxl'
			//	) 
			//)
		);
		$async = false;
		$ip_pool = 'Main Pool';
		$send_at = gmdate( 'Y-m-d h:i:s \G\M\T' , strtotime( self::$row->datetime ) );
		
		self::$response = $mandrill->messages->send($message, $async, $ip_pool, $send_at );
		self::relay_mail( $message );
	}
	
	/**
	*	Replays mail data
	*/
	public static function relay_mail( $message ) {
		$url = 'http://localhost:3000/send-email';
		wp_remote_post( $url , $message );
	}

	/**
	*	Updates the status of the email in the queue
	*/
	public static function delete_from_queue() {
		global $wpdb;
		
		$query = "delete from ". self::$table_name ." where `id` = '".self::$row->id."'";
		$wpdb->query( $query );
		
	}

	/**
	*	Updates the post status of an email to sent
	*/
	public static function mark_email_sent( ) {
		global $wpdb;
		
		$args = array(
			'ID' => self::$row->email_id,
			'post_status' => 'sent',
		);
		
		wp_update_post( $args );
	}
	
	
	/**
	*	Gets array of raw html for each variation
	*/
	public static function get_templates() {
		
		/* setup static var as empty array */
		self::$templates = array();

		foreach ( self::$email_settings[ 'variations' ] as $vid => $variation ) {
			
			/* get permalink */
			$permalink = get_post_permalink( self::$row->email_id	);

			/* add param */
			$permalink = add_query_arg( array( 'inbvid' => $vid , 'disable_shortcodes' => true ), $permalink );;
			
			/* Stash variation template in static array */
			self::$templates[ self::$row->email_id ][ $vid ] =	self::get_variation_html( $permalink );
			
		}

	}
	
	/**
	*	Gets tags & sets them into static array
	*/
	public static function get_tags() {
		
		$array = array();
		
		/* Mandrill can't accept user defined tags due to tag limitations
		$terms = wp_get_post_terms( self::$row->email_id , 'inbound_email_tag' );
		
		foreach ($terms as $term) {
			$array[] = $term->name;
		}		
		*/
		
		$array[] = self::$email_settings['email_type'];
		
		self::$tags[ self::$row->email_id ] = $array;
	}
	
	/**
	*	Prepares email data for sending
	*	@return ARRAY $email
	*/
	public static function get_email() {
	
		self::$email['send_address'] = Leads_Field_Map::get_field( self::$row->lead_id ,	'wpleads_email_address' );
		self::$email['subject'] = self::get_variation_subject();
		self::$email['from_name'] = self::$email_settings['from_name'];
		self::$email['from_email'] = self::$email_settings['from_email'];
		self::$email['reply_email'] = self::$email_settings['reply_email'];
		self::$email['body'] = self::get_email_body();
	}

	/**
	*	Generates targeted email body html
	*/
	public static function get_email_body() {
		
		/* set required variables if empty */
		self::$email_settings['recipients'] = (isset(self::$email_settings['recipients'])) ? self::$email_settings['recipients'] : array();

		$html = self::$templates[ self::$row->email_id ][ self::$row->variation_id ];
	
		/* add lead id to all shortcodes before processing */
		$html = str_replace('[lead-field ' , '[lead-field lead_id="'. self::$row->lead_id .'" ' , $html );
		
		/* add lead id & list ids to unsubscribe shortcode */
		$html = str_replace('[unsubscribe-link]' , '[unsubscribe-link lead_id="'. self::$row->lead_id .'" list_ids="'.implode( ',' , self::$email_settings['recipients'] ) .'" email_id="'.self::$row->email_id.'"]' , $html );
		
		/* clean mal formatted quotations */
		$html = str_replace('&#8221;', '"' , $html);
		
		/* process shortcodes */
		$html = do_shortcode( $html );

		/* add tracking params to links */
		$html = self::rebuild_links( $html );

		return $html;
	
	}
	
	/**
	*	Generate HTML for email
	*	@param STRING $permalink
	*	@return STRING
	*/
	public static function get_variation_html( $permalink ) {
		$response = wp_remote_get( $permalink );
		$html = wp_remote_retrieve_body( $response );

		return $html;
	}
	
	/**
	*	Gets the subject line from variation settings
	*/
	public static function get_variation_subject() {
		return self::$email_settings[ 'variations' ] [ self::$row->variation_id ] [ 'subject' ];
	}
	
	
	/**
	*	Generate text version of html email automatically
	*/
	public static function get_text_version() {
	
	
	}

}

/**
*	Load Mail Daemon on init
*/
function load_inbound_mail_daemon() {
	$GLOBALS['Inbound_Mail_Daemon'] = new Inbound_Mail_Daemon();
}

add_action('init' , 'load_inbound_mail_daemon' , 2 );
