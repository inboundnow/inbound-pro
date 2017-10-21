<?php

/**
 * Class for managing Unsubscribe features
 * @package Mailer
 * @subpackage Unsubscribes
 */

class Inbound_Mailer_Unsubscribe {

	/**
	 *  Initialize class
	 */
	public function __construct() {

		self::load_hooks();
	}

	/**
	 *  Loads hooks and filters
	 */
	public function load_hooks() {

		/* Add processing listeners  */
		add_action( 'init' , array( __class__ , 'process_unsubscribe' ) , 20 );

		/* Shortcode for displaying unsubscribe page */
		add_shortcode( 'inbound-email-unsubscribe' , array( __CLASS__, 'display_unsubscribe_page' ), 1 );

	}

	/**
	 * Display unsubscribe options
	 */
	public static function display_unsubscribe_page( $atts ) {
		global $inbound_settings;

		$html = "";
		$usubscribe_notice_automation_series = (isset($inbound_settings['mailer']['unsubscribe-notice-automation-series'])) ? $inbound_settings['mailer']['unsubscribe-notice-automation-series'] : __( 'You have unsubscribed!<br> Additional options below.', 'inbound-pro');
		$unsubscribe_header_text = (isset($inbound_settings['mailer']['unsubscribe-header-text'])) ? $inbound_settings['mailer']['unsubscribe-header-text'] : __( 'Unsubscribe:', 'inbound-pro');
		$usubscribe_button_text = (isset($inbound_settings['mailer']['unsubscribe-button-text'])) ? $inbound_settings['mailer']['unsubscribe-button-text'] : __( 'Unsubscribe', 'inbound-pro');
		$usubscribe_show_lists = (isset($inbound_settings['mailer']['unsubscribe-show-lists'])) ? $inbound_settings['mailer']['unsubscribe-show-lists'] : 'on';
		$mute_header_text = (isset($inbound_settings['mailer']['mute-header-text'])) ? $inbound_settings['mailer']['mute-header-text'] : __( 'Mute:', 'inbound-pro');
		$automation_unsubscribed_confirmation_message = (isset($inbound_settings['mailer']['automation-unsubscribe-confirmation-message'])) ? $inbound_settings['mailer']['automation-unsubscribe-confirmation-message'] : __( 'You have been unsubscribed from this series!', 'inbound-pro');
		$unsubscribed_confirmation_message = (isset($inbound_settings['mailer']['unsubscribe-confirmation-message'])) ? $inbound_settings['mailer']['unsubscribe-confirmation-message'] : __( 'Thank You!', 'inbound-pro');
		$comments_header_1 = (isset($inbound_settings['mailer']['unsubscribe-comments-header-1'])) ? $inbound_settings['mailer']['unsubscribe-comments-header-1'] : __( 'Please help us improve by providing us with feedback.' , 'inbound-pro' );
		$comments_header_2 = (isset($inbound_settings['mailer']['unsubscribe-comments-header-2'])) ? $inbound_settings['mailer']['unsubscribe-comments-header-2'] : __( 'Comments:' , 'inbound-pro' );
		$all_lists = (isset($inbound_settings['mailer']['unsubscribe-all-lists-label'])) ? $inbound_settings['mailer']['unsubscribe-all-lists-label'] : __( 'All Lists' , 'inbound-pro' );
		$month_1 = (isset($inbound_settings['mailer']['unsubscribe-1-months'])) ? $inbound_settings['mailer']['unsubscribe-1-months'] : __( '1 Month' , 'inbound-pro' );
		$month_3 = (isset($inbound_settings['mailer']['unsubscribe-3-months'])) ? $inbound_settings['mailer']['unsubscribe-3-months'] : __( '3 Month' , 'inbound-pro' );
		$month_6 = (isset($inbound_settings['mailer']['unsubscribe-6-months'])) ? $inbound_settings['mailer']['unsubscribe-6-months'] : __( '6 Month' , 'inbound-pro' );
		$month_12 = (isset($inbound_settings['mailer']['unsubscribe-12-months'])) ? $inbound_settings['mailer']['unsubscribe-12-months'] : __( '12 Month' , 'inbound-pro' );

		/* create/get maintenance list id - returns array */
		$maintenance_lists = Inbound_Maintenance_Lists::get_lists();


		if ( isset( $_GET['unsubscribed'] ) ) {
			$confirm =  "<span class='unsubscribed-message'>". $unsubscribed_confirmation_message ."</span>";
			$confirm = apply_filters( 'mailer/unsubscribe/confirmation-html' , $confirm );
			return $confirm;
		}

		$token = ( isset( $_GET['token'] ) ) ? sanitize_text_field($_GET['token']) : '';

		/* get all lead lists */
		$lead_lists = Inbound_Leads::get_lead_lists_as_array();

		/* decode token */
		$params = Inbound_API::get_args_from_token( $token );

		/* legacy token backup */
		if (!$params) {
			$params = self::legacy_decode_unsubscribe_token($token);
		}
		//print_r($params);

		/* if token has failed or isn't present check for logged in user */
		if (!$params) {
			$params = array();

			/* get logged in user object */
			$current_user = wp_get_current_user();

			/* Get lead id from email */
			$params['lead_id'] = Inbound_Leads::get_lead_id_by_email($current_user->user_email);
			$params['list_ids'] = array();

			/* retrieve lists from lead id if available */
			$params['list_ids'] = array();
			if ($params['lead_id']) {
				$params['list_ids'] = array_flip(Inbound_Leads::get_lead_lists_by_lead_id($params['lead_id']));
				$usubscribe_show_lists = 'on';
			}
		}

		if ( !isset( $params['lead_id'] ) || !$params['lead_id'] ) {
			return __( 'Oops. Something is wrong with the unsubscribe token. Please log in and reload this page.' , 'inbound-pro' );
		}


		/* check email was sent directly to lead via automation series and cancel event if so */

		if (isset($params['job_id']) && $params['job_id'] && (!isset($params['list_ids']) || !array_filter($params['list_ids']) ) ) {

			Inbound_Automation_Post_Type::mark_jobs_cancelled( array('job_id' => $params['job_id']) );

			$params['event_details'] = array();
			$params['event_details']['comments'] = __('Lead unsubscribed from automated email series' , 'inbound-pro');
			$params['event_details']['job_id'] = $params['job_id'];
			$params['event_details']['rule_id'] = $params['rule_id'];

			/* record unsubscribe event */
			Inbound_Events::store_unsubscribe_event( $params );

			echo '<div class="automation-unsubscribe-message success">'. $automation_unsubscribed_confirmation_message.'</div><br>';
		}

		/* Add header */
		$html .= "<div class='unsubscribe-header'>" .$unsubscribe_header_text . "</div>";

		/* Begin unsubscribe html inputs */
		$html .= "<form action='?unsubscribed=true' name='unsubscribe' method='post'>";
		$html .= "<input type='hidden' name='token' value='".strip_tags($token)."' >";
		$html .= "<input type='hidden' name='action' value='inbound_unsubscribe_event' >";

		/* loop through lists and show unsubscribe inputs */
		if ( isset($params['list_ids']) && $usubscribe_show_lists == 'on' ) {
			foreach ($params['list_ids'] as $list_id ) {
				if ($list_id == '-1' || !$list_id ) {
					continue;
				}

				/* ignore lists belonging to maintenance list */
				if (term_is_ancestor_of( $maintenance_lists['parent']['id'] , $list_id , 'wplead_list_category')) {
					continue;
				}

				/* make sure not to reveal unrelated lists */
				if (has_term($list_id, 'wplead_list_category' , $params['lead_id'] )) {
					$html .= "<span class='unsubscribe-span'><label class='lead-list-label'><input type='checkbox' name='list_id[]' value='" . $list_id . "' class='lead-list-class'> " . $lead_lists[$list_id] . '</label></span>';
				}
			}
		}

		$html .= "<span class='unsubscribe-span'><label class='lead-list-label'><input name='lists_all' type='checkbox' value='all' ". ( $usubscribe_show_lists == 'off' ? 'checked="true"' : '' ) ."> " . $all_lists . "</label></span>";
		$html .= "<div class='unsubscribe-div unsubsribe-comments'>";
		$html .= "	<span class='unsubscribe-comments-message'>". $comments_header_1 ."</span>";
		$html .= "	<span class='unsubscribe-comments-label'>". $comments_header_2 ."<br><textarea rows='8' cols='60' name='comments'></textarea></span>";
		$html .= "</div>";
		$html .= "<div class='unsubscribe-div unsubscribe-options'>";
		$html .= "	<span class='unsubscribe-action-label'>". $mute_header_text ."</span>";
		$html .= "	<div class='mute-buttons'>";
		$html .= "		<span class='mute-1-span'>
							<label class='unsubscribe-label'>
								<input name='mute-1' type='submit' value='". $month_1 ."' class='inbound-button-submit inbound-submit-action'>
							</label>
						</span>";
		$html .= "		<span class='mute-3-span'>
							<label class='unsubscribe-label'>
								<input name='mute-3' type='submit' value='". $month_3 ."' class='inbound-button-submit inbound-submit-action'>
							</label>
						</span>";
		$html .= "		<span class='mute-6-span'>
							<label class='unsubscribe-label'>
								<input name='mute-6' type='submit' value='". $month_6 ."' class='inbound-button-submit inbound-submit-action'>
							</label>
						</span>";
		$html .= "		<span class='mute-12-span'>
							<label class='unsubscribe-label'>
								<input name='mute-12' type='submit' value='". $month_12 ."' class='inbound-button-submit inbound-submit-action'>
							</label>
						</span>";
		$html .= "	</div>";
		$html .= "	<span class='unsubscribe-action-label'>".$usubscribe_button_text .":</span>";
		$html .= "	<div class='unsubscribe-button'>";
		$html .= "		<span class='unsub-span'>
							<label class='unsubscribe-label'>
								<input name='unsubscribe' type='submit' value='". $usubscribe_button_text ."' class='inbound-button-submit inbound-submit-action'>
							</label>
						</span>";
		$html .= "	</div>";
		$html .= "</div>";
		$html .= "</form>";

		return $html;

	}

	/**
	 *  Generates unsubscribe link given lead id and lists
	 *  @param ARRAY $params contains: lead_id (INT ), list_ids (MIXED), email_id (INT)
	 *  @return STRING $unsubscribe_link
	 */
	public static function generate_unsubscribe_link( $params ) {


		if (!isset($params['lead_id']) || !$params['lead_id']) {
			return __( '#unsubscribe-not-available-in-online-mode' , 'inbound-pro' );
		}

		if (isset($_GET['lead_lists']) && !is_array($_GET['lead_lists'])){
			$params['list_ids'] = explode( ',' , $_GET['lead_lists']);
		} else if (isset($params['list_ids']) && !is_array($params['list_ids'])) {
			$params['list_ids'] = explode( ',' , $params['list_ids']);
		}


		$args = array_merge( $params , $_GET );

		$token = Inbound_API::analytics_get_tracking_code( $args );

		/* decode token - testing */
		//$params = Inbound_API::get_args_from_token( $token );

		$settings = Inbound_Mailer_Settings::get_settings();

		if ( empty($settings['unsubscribe_page']) )  {
			$post = get_page_by_title( __( 'Unsubscribe' , 'inbound-pro' ) );
			$settings['unsubscribe_page'] =  $post->ID;
		}

		$base_url = get_permalink( $settings['unsubscribe_page']  );

		return add_query_arg( array( 'token'=>$token ) , $base_url );

	}


	/**
	 *  Decodes unsubscribe encoded reader id into a lead id
	 *  @param STRING $reader_id Encoded lead id.
	 *  @return ARRAY $unsubscribe array of unsubscribe data
	 */
	public static function legacy_decode_unsubscribe_token( $token ) {

		$token = str_replace( array('-', '_', '^'), array('+', '/', '=') , $token);

		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted_string =
			trim(
				mcrypt_decrypt(
					MCRYPT_RIJNDAEL_256 ,  substr( SECURE_AUTH_KEY , 0 , 16 )   ,  base64_decode( $token ) , MCRYPT_MODE_ECB, $iv
				)
			);

		return json_decode($decrypted_string , true);

	}


	/**
	 *  Removes a list id to a leads unsubscribed list
	 *  @param INT $lead_id
	 *  @param INT $list_id
	 */
	public static function remove_stop_rule( $lead_id , $list_id ) {
		$stop_rules = get_post_meta( $lead_id , 'inbound_unsubscribed' , true );

		if ( !$stop_rules ) {
			return;
		}

		if (!isset($stop_rules[$list_id])) {
			return;
		}

		unset( $stop_rules[$list_id] );

		update_post_meta( $lead_id , 'inbound_unsubscribed' , $stop_rules );
	}

	/**
	 *  Listener & unsubscribe actions
	 */
	public static function process_unsubscribe() {

		if (!isset($_POST['action']) || $_POST['action'] != 'inbound_unsubscribe_event' ) {
			return;
		}

		/* cancel if nothing selected */
		if (!isset($_POST['list_id']) && !isset($_POST['lists_all'])) {
			return;
		}

		/* decode token */
		$params = Inbound_API::get_args_from_token( $_POST['token'] );

		/* if no token is present or is a bad token then automatically discover lead id */
		if (!isset($params['lead_id']) || !$params['lead_id'] || !$params ) {
			/* get logged in user object */
			$current_user = wp_get_current_user();

			/* Get lead id from email */
			$params['lead_id'] = Inbound_Leads::get_lead_id_by_email($current_user->user_email);
		}

		/* prepare all token */
		$all = (isset($_POST['lists_all']) && $_POST['lists_all']  ) ? true : false;

		/* add comments */
		$params['event_details']['comments'] = ( isset( $_POST['comments'] ) ) ? $_POST['comments'] : '';
		$params['event_details']['list_ids'] = $_POST['list_id'];

		if (isset($_POST['mute-1'])) {
			self::mute_lead_emails( $params , $all , 1 );
		} else if (isset($_POST['mute-3'])) {
			self::mute_lead_emails( $params , $all , 3 );
		} else if (isset($_POST['mute-6'])) {
			self::mute_lead_emails( $params , $all , 6 );
		} else if (isset($_POST['mute-12'])) {
			self::mute_lead_emails( $params , $all , 12 );
		} else if (isset($_POST['unsubscribe'])) {
			self::unsubscribe_lead( $params , $all );
		}

	}

	/**
	 * @param $params
	 * @param bool $all
	 */
	public static function unsubscribe_lead( $params , $all = false) {
		switch ($all) {
			case true:
				self::unsubscribe_from_all_lists( $params );
				break;
			default:
				/* loop through lists and unsubscribe lead */
				foreach( $params['event_details']['list_ids'] as $list_id ) {
					Inbound_Leads::remove_lead_from_list( $params['lead_id'] , $list_id );
					Inbound_Mailer_Unsubscribe::add_stop_rules( $params['lead_id'] , $list_id );
					$event = $params;
					Inbound_Events::store_unsubscribe_event( $event );
				}
				break;
		}
	}

	/**
	 *  Unsubscribe lead from all lists
	 */
	public static function unsubscribe_from_all_lists( $params ) {

		if (!isset($params['lead_id']) || !$params['lead_id'] ) {
			return;
		}

		/* get all lead lists */
		$lead_lists = Inbound_Leads::get_lead_lists_as_array();

		foreach ( $lead_lists as $list_id => $label ) {
			Inbound_Leads::remove_lead_from_list( $params['lead_id'] , $list_id );
			Inbound_Mailer_Unsubscribe::add_stop_rules( $params['lead_id'] , $list_id );
			$params['list_id'] = $list_id;
			Inbound_Events::store_unsubscribe_event( $params );
		}

	}


	/**
	 * @param $params
	 * @param bool $all
	 */
	public static function mute_lead_emails( $params , $all = false, $time ) {

		switch ($all) {
			case true:
				self::mute_all_lists( $params , $time );
				break;
			default:
				self::mute_lists($params , $time );
				break;
		}
	}

	/**
	 *  Unsubscribe lead from all lists
	 */
	public static function mute_all_lists( $params , $time ) {
		/* get all lead lists */
		$params['event_details']['list_ids'] = Inbound_Leads::get_lead_lists_as_array();
		self::mute_lists($params , $time );
	}

	/**
	 *  Unsubscribe lead from all lists
	 */
	public static function mute_lists( $params, $time ) {

		$wordpress_date_time =  date_i18n('Y-m-d G:i:s');
		$dateTime = new DateTime($wordpress_date_time);
		$dateTime->modify('+'.$time.' months');
		$release_date = $dateTime->format('Y-m-d H:i');

		$event = $params;

		foreach ( $params['event_details']['list_ids'] as $list_id ) {
			Inbound_Mailer_Unsubscribe::add_stop_rules( $params['lead_id'] , $list_id , $release_date );
			$event['event_details']['emails_muted_for'] = $time . ' month';
			$event['event_details']['emails_muted_until'] = $release_date;
			$event['list_id'] = $list_id;
			Inbound_Events::store_mute_event( $event );
		}

	}


	/**
	 *  Adds a list id to a leads unsubscribed list
	 *  @param INT $lead_id
	 *  @param INT $list_id
	 */
	public static function add_stop_rules( $lead_id , $list_id , $nature = 'unsubscribed' ) {
		$stop_rules = self::get_stop_rules( $lead_id );

		$stop_rules[ $list_id ] = $nature;

		update_post_meta( $lead_id , 'inbound_unsubscribed' , $stop_rules );
	}

	/**
	 *  Adds a list id to a leads unsubscribed list
	 *  @param INT $lead_id
	 *  @param INT $list_id
	 */
	public static function get_stop_rules( $lead_id ) {
		$stop_rules = get_post_meta( $lead_id , 'inbound_unsubscribed' , true );

		if ( !$stop_rules ) {
			$stop_rules = array();
		}

		return $stop_rules;
	}

}

new Inbound_Mailer_Unsubscribe();