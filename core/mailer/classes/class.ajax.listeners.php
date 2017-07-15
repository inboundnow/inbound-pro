<?php

/**
 * Class loads miscellaneous WordPress AJAX listeners related to email component
 * @package Mailer
 * @subpackage Management
 */

class Inbound_Mailer_Ajax_Listeners {
	static $needle;

	/**
	 *	Initializes class
	 */
	public function __construct() {
		self::load_hooks();
	}

	/**
	 *	Loads hooks and filters
	 */
	public static function load_hooks() {


		/* Adds listener to save email data */
		add_action( 'wp_ajax_save_inbound_email', array( __CLASS__ , 'save_email' ) );

		/* Adds listener for email variation send statistics */
		add_action( 'wp_ajax_inbound_load_email_row_stats' , array( __CLASS__ , 'get_email_row_statistics' ) );

		/* Adds listener to send test email */
		add_action( 'wp_ajax_inbound_send_test_email' , array( __CLASS__ , 'send_test_email' ) );

		/* Adds listener to schedule email */
		add_action( 'wp_ajax_inbound_schedule_email' , array( __CLASS__ , 'schedule_email' ) );

		/* Adds listener to schedule email */
		add_action( 'wp_ajax_inbound_unschedule_email' , array( __CLASS__ , 'unschedule_email' ) );

		/* Adds listener to set scheduled email to sent */
		add_action( 'wp_ajax_inbound_mark_sent' , array( __CLASS__ , 'mark_sent' ) );

		/* Adds listener to schedule email */
		add_action( 'wp_ajax_inbound_prepare_batch_email' , array( __CLASS__ , 'prepare_batch_email' ) );

		/* Adds listener to clear email stats */
		add_action( 'wp_ajax_clear_email_stats' , array( __CLASS__ , 'clear_stats' ) );

		/* Adds listener to update range email */
		add_action( 'wp_ajax_inbound_email_update_range' , array( __CLASS__ , 'update_range' ) );

		/* Adds listener to update job id memory */
		add_action( 'wp_ajax_inbound_email_update_job_id' , array( __CLASS__ , 'update_job_id' ) );
	}

	/**
	 *	Saves meta pair values give cta ID, meta key, and meta value
	 */
	public static function save_email() {
		global $wpdb;

		if ( !isset($_POST) ) {
			return;
		}

		/* error_log( print_r( $_POST , true ) ); */

		/* update post type */
		wp_update_post( array(
			'ID' => $_POST['post_ID'],
			'post_status' => isset($_POST['post_status']) ? $_POST['post_status'] : $_POST['hidden_post_status'],
			'post_title' => $_POST['post_title'],
		));

		/* get current email settings */
		$email_settings = Inbound_Email_Meta::get_settings( $_POST['post_ID'] );

		/* Set the call to action variation into a session variable */
		$_SESSION[ $_POST['post_ID'] . '-variation-id'] = (isset($_POST[ 'inbvid'])) ? $_POST[ 'inbvid'] : '0';

		/* save all post vars as meta */
		foreach ($_POST as $key => $value) {

			if ( substr( $key , 0 , 8 ) == 'inbound_' ){
				$key = str_replace( 'inbound_' , '' , $key );
				$email_settings[ $key ] = $value;
			} else {
				if (self::check_whitelist( $key )) {
					$email_settings['variations'][ $_POST[ 'inbvid'] ][ $key ] = $value;
				}
			}
		}

		/* Update Settings */
		Inbound_Email_Meta::update_settings( $_POST['post_ID'] , $email_settings );

		/* Update Tags */
		if ( isset( $_POST['tax_input'] ) ) {
			foreach ( $_POST['tax_input']  as $tax => $terms ) {
				wp_set_post_terms( $_POST['post_ID'], $terms, $tax, false );
			}
		}

		header('HTTP/1.1 200 OK');
		exit;
	}

	/**
	 *	Checks meta key for variation setting qualification
	 *	@returns BOOLEAN $key false for skip true for save
	 */
	public static function check_whitelist( $key ) {
		/* do not save post_ related keys */
		if ( substr( $key , 0 , 5 ) == 'post_' ) {
			return false;
		}

		/* do not save hidden custom fields */
		if ( substr( $key , 0 , 1 ) == '_' ) {
			return false;
		}

		/* do not save hidden custom fields */
		if ( substr( $key , 0 , 7 ) == 'hidden_' ) {
			return false;
		}

		/* do not save hidden custom fields */
		if ( substr( $key , 0 , 4 ) == 'cur_' ) {
			return false;
		}

		/* do not save hidden custom fields */
		if ( strstr( $key , 'nonce' ) ) {
			return false;
		}

		/* do not save hidden custom fields */
		if ( in_array( $key , array('inbvid', 'email_action' , 'originalaction','action','original_publish','publish','original_post_status', 'referredby', 'meta-box-order-nonce', 'comment_status','ping_status','post_mime_type','newtag','tax_input','post_password' ,'visibility','wp-preview'	) ) ) {
			return false;
		}

		return true;
	}

	/**
	 *  Gets JSON object containing email send statistics return cached data if cached
	 */
	public static function get_email_row_statistics() {
		global $inbound_settings;

		$stats = get_transient( 'inbound-email-stats-cache');

		if (!is_array($stats)) {
			$stats = array();
		}

        /* get email id */
		$email_id = intval($_REQUEST['email_id']);

        /* get job id if applicable */
        $job_id = get_user_option(
            'inbound_mailer_screen_option_automated_email_report',
            get_current_user_id()
        );

        /* if job id is set to 'last_send' then source the last job id */
        $job_id = ($job_id == 'last_send' ) ? Inbound_Mailer_Post_Type::get_last_job_id($email_id) : $job_id;

		if (isset($stats[$email_id])) {
			echo json_encode($stats[$email_id]);
			header('HTTP/1.1 200 OK');
			exit;
		}

		switch ($inbound_settings['mailer']['mail-service']) {
			case "sparkpost":
				$stats[$email_id] = Inbound_SparkPost_Stats::get_sparkpost_inbound_events( $email_id , $vid = null , $job_id );
				break;
		}

		set_transient('inbound-email-stats-cache' , $stats , 60* 5);

		echo json_encode($stats[$email_id]);
		header('HTTP/1.1 200 OK');
		exit;
	}

	/**
	 *  Sends test email
	 */
	public static function send_test_email() {

		/* see if there is a lead associated with the test email */
		$lead_id = LeadStorage::lookup_lead_by_email($_REQUEST['email_address']);

		$response = Inbound_Mail_Daemon::send_solo_email( array(
			'email_address' => sanitize_text_field($_REQUEST['email_address']) ,
			'email_id' => intval($_REQUEST['email_id']) ,
			'vid' => intval($_REQUEST['variation_id']),
			'from_name' => 'test@inboundnow.com',
			'lead_id' => ( $lead_id ) ? intval($lead_id) : 0,
			'is_test' => true
		));

		_e( 'Here are your send results:' , 'inbound-pro' );

		echo "\r\n";

		print_r($response);

		exit;
	}


	/**
	 *  Schedule email
	 */
	public static function schedule_email() {
		$response = Inbound_Mailer_Scheduling::schedule_email(intval($_POST['email_id']));
		echo $response;
		exit;
	}

	/**
	 *  Unschedule email
	 */
	public static function unschedule_email() {
		do_action('mailer/unschedule-email' , $_REQUEST['email_id'] );
		exit;
	}

	/**
	 *  Mark Email Sent
	 */
	public static function mark_sent() {
		$args = array(
			'ID' => intval($_REQUEST['email_id']),
			'post_status' => 'sent',
		);

		wp_update_post( $args );
		exit;
	}

	/**
	 *
	 */
	public static function prepare_batch_email() {


		$email_id = $_POST['email_id'];
		$post_id = $_POST['post_id'];

		if (!$email_id) {
			return;
		}

		$post = get_post($email_id);

		if (function_exists('wp_get_current_user')) {
			$new_post_author = wp_get_current_user();
			$author_id = $new_post_author->ID;
		} else {
			$author_id = get_current_user_id();
		}


		$new_post = array(
			'menu_order' => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_author' => $author_id,
			'post_content' => $post->post_content,
			'post_excerpt' =>	$post->post_excerpt ,
			'post_mime_type' => $post->post_mime_type,
			'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
			'post_password' => $post->post_password,
			'post_status' => 'unsent',
			'post_title' => get_the_title($post_id),
			'post_type' => $post->post_type,
		);

		$new_post['post_date'] = $new_post_date =	$post->post_date ;
		$new_post['post_date_gmt'] = get_gmt_from_date($new_post_date);

		$new_email_id = wp_insert_post($new_post);

		$meta_data = get_post_meta($post->ID);

		/* destroy any past statistics */
		unset($meta_data['inbound_statistics']);


		/* replace id in meta fields */
		foreach ($meta_data as $key=>$value) {
			if ($key=='inbound_settings') {
				$value[0] = unserialize( $value[0] );
			}


			/* set email type to batch */
			if ($key == 'inbound_settings') {
				$value[0]['email_type'] = 'batch';
				$variations = $value[0]['variations'];

				foreach ($variations as $vid => $settings) {
					$value[0]['variations'][$vid]['acf'] = self::replace_id($value[0]['variations'][$vid]['acf'], $post_id);
				}


			} else {
				/* replace shortcode ids */
				$value[0] = self::replace_id($value[0], $post_id);
			}



			update_post_meta($new_email_id , $key , $value[0]);
		}


		echo $new_email_id;
		exit;
	}

	/**
	 *
	 */
	public static function replace_id( $mixed , $post_id ) {

		if (is_array($mixed)) {

			foreach ($mixed as $k => $v ) {

				if (is_array($v)) {
					$mixed[$k] = self::replace_id($v,$post_id);
				}else {
					preg_match("/\sid=\d+/", $v , $output_array);
					foreach ($output_array as $kk=>$match) {
						$mixed[$k] = str_replace($match , ' id=' . $post_id , $v);
					}
				}
			}


		} else {

			preg_match("/\sid=\d+/", $mixed , $output_array);
			foreach ($output_array as $k=>$match) {
				$mixed = str_replace($match , ' id=' . $post_id , $mixed);
			}
		}

		return $mixed;
	}

	/**
	 *  Schedule email
	 */
	public static function clear_stats() {
		global $wpdb;

		$email_id = intval($_REQUEST['email_id']);
		$table_name = $wpdb->prefix . "inbound_events";

		/* delete the meta stats object */
		delete_post_meta( $email_id , 'inbound_statistics' );


		/* delete enteries from inbound_events table */
		$where = array(
			'email_id' => $email_id
		);

		$wpdb->delete( $table_name, $where, $where_format = null );

		echo 1;
		exit;
	}

	/**
	 *  Updates user preferred statistic reporting date range
	 */
	public static function update_range() {
		if (!isset($_POST['range'])) {
			return;
		}

		$response = update_user_option(
			get_current_user_id(),
			'inbound_mailer_screen_option_range',
			intval($_POST['range'])
		);
		echo $response;
		exit;
	}


	/**
	 *  Updates user preferred statistic reporting date range
	 */
	public static function update_job_id() {
		if (!isset($_POST['job_id'])) {
			return;
		}

		$response = update_user_option(
			get_current_user_id(),
			'inbound_mailer_reporting_job_id_' . intval($_POST['email_id']),
			sanitize_text_field($_POST['job_id'])
		);

		echo sanitize_text_field($_POST['job_id']);
		exit;
	}
}

/* Loads Inbound_Mailer_Ajax_Listeners pre init */
new Inbound_Mailer_Ajax_Listeners();
