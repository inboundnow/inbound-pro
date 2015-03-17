<?php
/**
* Inbound Lead Storage
*
* - Handles lead creation and data storage
*/


/* This AJAX listener listens for site searches that a lead performs */
add_action('wp_ajax_inbound_store_lead_search', 'inbound_store_lead_search');
add_action('wp_ajax_nopriv_inbound_store_lead_search', 'inbound_store_lead_search');
function inbound_store_lead_search($args = array()) {
	global $wpdb;

	$search = (isset($_POST['search_data'] )) ? $_POST['search_data'] : null; // mapped data
	$email = (isset($_POST['email'] )) ? $_POST['email'] : null; // mapped data
	$date = (isset($_POST['date'] )) ? $_POST['date'] : null; // mapped data

	if ( ( isset( $email ) && !empty( $email ) && strstr( $email ,'@') )) {
		$query = $wpdb->prepare(
			'SELECT ID FROM ' . $wpdb->posts . '
			WHERE post_title = %s
			AND post_type = \'wp-lead\'',
			$email
		);

		$wpdb->query( $query );

		// Add lookup fallbacks
		if ( $wpdb->num_rows ) {
			/* Update Existing Lead */
			$lead_id = $wpdb->get_var( $query );
			//update_post_meta($lead_id, 'wpleads_search_data', ""); // Store search object
			/* Store Search History Data */
			$count = 1;
			$search_array = array();
			foreach ($search as $key => $value) {
				$search_array[$count] = array( 'date' => $search[$count]['date'],
												'value' => $search[$count]['value']);
				$count++;
			}

			$search_data = get_post_meta( $lead_id, 'wpleads_search_data', TRUE );
			$search_data = json_decode($search_data,true);
			if (is_array($search_data)){
				$s_count = count($search_data) + 1;
				$loop_count = 1;
				foreach ($search as $key => $value) {
					$search_data[$s_count]['date'] = $search[$loop_count]['date'];
					$search_data[$s_count]['value'] = $search[$loop_count]['value'];
					$s_count++; $loop_count++;
				}

			} else {
				// Create search obj
				$s_count = 1;
				$loop_count = 1;
				foreach ($search as $key => $value) {
					$search_data[$s_count]['date'] = $search[$loop_count]['date'];
					$search_data[$s_count]['value'] = $search[$loop_count]['value'];
					$s_count++; $loop_count++;
				}
			}

		$search_data = json_encode($search_data);
		update_post_meta($lead_id, 'wpleads_search_data', $search_data); // Store search object

		}

	}
}


add_action('wp_ajax_inbound_store_lead', 'inbound_store_lead' , 10 , 1);
add_action('wp_ajax_nopriv_inbound_store_lead', 'inbound_store_lead' ,10 , 1);

/**
 *	This method needs to be rebuilt
 */
function inbound_store_lead( $args = array( ) , $return = false ) {
	global $user_ID, $wpdb;

	if (!is_array($args)) {
		$args = array();
	}

	/* Mergs $args with POST request for support of ajax and direct calls */
	$args = array_merge( $args , $_POST );

	/* Grab form values */
	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$lead_data['user_ID'] = $user_ID;
	$lead_data['wordpress_date_time'] = date("Y-m-d G:i:s T", $time);
	$lead_data['wpleads_email_address'] = (isset($args['emailTo'])) ? $args['emailTo'] : false;
	$lead_data['page_views'] = (isset($args['page_views'])) ?	$args['page_views'] : false;
	$lead_data['form_input_values'] = (isset($args['form_input_values'])) ? $args['form_input_values'] : false; // raw post data

	/* Attempt to populate lead data through mappped fields */
	$lead_data['Mapped_Data'] = (isset($args['Mapped_Data'] )) ? $args['Mapped_Data'] : false; // mapped data
	($lead_data['Mapped_Data']) ? $mapped_data = json_decode(stripslashes($lead_data['Mapped_Data']), true ) : $mapped_data = array(); // mapped data array
	$lead_data['page_view_count'] = (array_key_exists('page_view_count', $mapped_data)) ? $mapped_data['page_view_count'] : 0;
	$lead_data['source'] = (array_key_exists('source', $mapped_data)) ? $mapped_data['source'] : 'NA';
	$lead_data['page_id'] = (array_key_exists('page_id', $mapped_data)) ? $mapped_data['page_id'] : '0';
	$lead_data['variation'] = (array_key_exists('variation', $mapped_data)) ? $mapped_data['variation'] : '0';
	$lead_data['post_type'] = (array_key_exists('post_type', $mapped_data)) ? $mapped_data['post_type'] : false;
	$lead_data['wp_lead_uid'] = (array_key_exists('wp_lead_uid', $mapped_data)) ? $mapped_data['wp_lead_uid'] : false;
	$lead_data['lead_lists'] = (array_key_exists('leads_list', $mapped_data)) ? explode(",", $mapped_data['leads_list']) : false;
	$lead_data['ip_address'] = inbound_get_ip_address();


	/* Check first level for data related to lead submit */
	$lead_data['page_id'] = ( !$lead_data['page_id'] && isset($args['page_id'])) ? $args['page_id'] : $lead_data['page_id'] ;
	$lead_data['post_type'] = ( !$lead_data['post_type'] && isset($args['post_type'])) ? $args['post_type'] : $lead_data['post_type'] ;
	$lead_data['variation'] = (array_key_exists('variation', $mapped_data)) ? $mapped_data['variation'] : '0';

	/* look for search data */
	$raw_search_data = (isset($args['Search_Data'])) ? $args['Search_Data'] : false;
	$search_data = json_decode(stripslashes($raw_search_data), true ); // mapped data array
	$lead_data['search_data'] = $search_data;

	/* Legacy - needs to be phased out - search for alias key matches */
	$lead_data['wpleads_full_name'] = (isset($args['full_name'])) ?	$args['full_name'] : "";
	$lead_data['wpleads_first_name'] = (isset($args['first_name'])) ?	$args['first_name'] : "";
	$lead_data['wpleads_last_name'] = (isset($args['last_name'])) ? $args['last_name'] : "";
	$lead_data['wpleads_company_name'] = (isset($args['company_name'] )) ? $args['company_name'] : "";
	$lead_data['wpleads_mobile_phone'] = (isset($args['phone'])) ? $args['phone'] : "";
	$lead_data['wpleads_address_line_1'] = (isset($args['address'])) ? $args['address'] : "";
	$lead_data['wpleads_address_line_2'] = (isset($args['address_2'])) ? $args['address_2'] : "";
	$lead_data['wpleads_city'] = (isset($args['city'])) ? $args['city'] : "";
	$lead_data['wpleads_region_name'] = (isset($args['region'])) ? $args['region'] : "";
	$lead_data['wpleads_zip'] = (isset($args['zip'])) ? $args['zip'] : "";

	/* Look for direct key matches & clean up $lead_data */
	$lead_data = apply_filters( 'inboundnow_store_lead_pre_filter_data' , $lead_data , $args);

	do_action('inbound_store_lead_pre' , $lead_data , $args ); // Global lead storage action hook

	/* bail if spam */
	if (apply_filters( 'inbound_check_if_spam' , false ,	$lead_data )) {
		exit;
	}


	/* check for set email */
	if ( ( isset( $lead_data['wpleads_email_address'] ) && !empty( $lead_data['wpleads_email_address'] ) && strstr( $lead_data['wpleads_email_address'] ,'@') ))
	{
		$query = $wpdb->prepare(
			'SELECT ID FROM ' . $wpdb->posts . '
			WHERE post_title = %s
			AND post_type = \'wp-lead\'',
			$lead_data['wpleads_email_address']
		);
		$wpdb->query( $query );

		/* Update Lead if Exists else Create New Lead */
		if ( $wpdb->num_rows ) {
			/* Update Existing Lead */
			$lead_data['lead_id'] = $wpdb->get_var( $query );
			$lead_id = $lead_data['lead_id'];
			inbound_update_common_meta($lead_data);

			do_action('wpleads_existing_lead_update', $lead_data ); // action hook on existing leads only

		} else {
			/* Create New Lead */
			$post = array(
				'post_title'		=> $lead_data['wpleads_email_address'],
				//'post_content'		=> $json,
				'post_status'		=> 'publish',
				'post_type'		=> 'wp-lead',
				'post_author'		=> 1
			);

			//$post = add_filter('lp_leads_post_vars',$post);
			$lead_data['lead_id'] = wp_insert_post($post);
			$lead_id = $lead_data['lead_id'];

			/* updates common meta for new leads */
			inbound_update_common_meta($lead_data);

			/* specific updates for new leads */
			update_post_meta( $lead_id, 'wpleads_email_address', $lead_data['wpleads_email_address'] );
			update_post_meta( $lead_id, 'page_views', $lead_data['page_views'] ); /* Store Page Views Object */
			update_post_meta( $lead_id, 'wpleads_page_view_count', $lead_data['page_view_count']);

			do_action('wpleads_new_lead_insert', $lead_data ); // action hook on new leads only
		}

		/***
		* Run Processes for all Leads below
		***/
		do_action('wpleads_after_conversion_lead_insert',$lead_id); // action hook on all lead inserts

		update_post_meta( $lead_id, 'wpleads_inbound_form_mapped_data', $lead_data['Mapped_Data']);

		/* Add Leads to List on creation */
		if(!empty($lead_data['lead_lists']) && is_array($lead_data['lead_lists'])){
			global $Inbound_Leads;
			$Inbound_Leads->add_lead_to_list($lead_id, $lead_data['lead_lists'], 'wplead_list_category');
		}

		/* Store past search history */
		if($lead_data['search_data']){
			$search = $lead_data['search_data'];
			$search_data = get_post_meta( $lead_id, 'wpleads_search_data', TRUE );
			$search_data = json_decode($search_data,true);
			if (is_array($search_data)){
				$s_count = count($search_data) + 1;
				$loop_count = 1;
				foreach ($search as $key => $value) {
				$search_data[$s_count]['date'] = $search[$loop_count]['date'];
				$search_data[$s_count]['value'] = $search[$loop_count]['value'];
				$s_count++; $loop_count++;
				}
			} else {
			// Create search obj
				$s_count = 1;
				$loop_count = 1;
				foreach ($search as $key => $value) {
				$search_data[$s_count]['date'] = $search[$loop_count]['date'];
				$search_data[$s_count]['value'] = $search[$loop_count]['value'];
				$s_count++; $loop_count++;
				}
			}
			$search_data = json_encode($search_data);
			update_post_meta($lead_id, 'wpleads_search_data', $search_data); // Store search object
		}

		/* Store IP addresss & Store GEO Data */
		if ($lead_data['ip_address']) {
			inbound_update_geolocation_data( $lead_data );
		}

		/* Store Conversion Data to Lead */
		inbound_add_conversion_to_lead( $lead_id, $lead_data );

		/* Store Lead Referral Source Data */
		$referral_data = get_post_meta( $lead_id, 'wpleads_referral_data', TRUE );
		$referral_data = json_decode($referral_data,true);
		if (is_array($referral_data)){
			$r_count = count($referral_data) + 1;
			$referral_data[$r_count]['source'] = $lead_data['source'];
			$referral_data[$r_count]['datetime'] = $lead_data['wordpress_date_time'];
		} else {
			$referral_data[1]['source'] = $lead_data['source'];
			$referral_data[1]['datetime'] = $lead_data['wordpress_date_time'];
			$referral_data[1]['original_source'] = 1;
		}
		$lead_data['referral_data'] = json_encode($referral_data);
		update_post_meta($lead_id, 'wpleads_referral_data', $lead_data['referral_data']); // Store referral object


		/* Store Conversion Data to LANDING PAGE/CTA DATA	*/
		if ($lead_data['post_type'] == 'landing-page' || $lead_data['post_type'] == 'wp-call-to-action')
		{
			$page_conversion_data = get_post_meta( $lead_data['page_id'], '_inbound_conversion_data', TRUE );
			$page_conversion_data = json_decode($page_conversion_data,true);
			$version = ($lead_data['variation'] != 'default') ? $lead_data['variation'] : '0';

			if (is_array($page_conversion_data))
			{
				$convert_count = count($page_conversion_data) + 1;
				$page_conversion_data[$convert_count]['lead_id'] = $lead_id;
				$page_conversion_data[$convert_count]['variation'] = $version;
				$page_conversion_data[$convert_count]['datetime'] = $lead_data['wordpress_date_time'];

			} else {

				$convert_count = 1;
				$page_conversion_data[$convert_count]['lead_id'] = $lead_id;
				$page_conversion_data[$convert_count]['variation'] = $version;
				$page_conversion_data[$convert_count]['datetime'] = $lead_data['wordpress_date_time'];

			}

			$page_conversion_data = json_encode($page_conversion_data);
			update_post_meta($lead_data['page_id'], '_inbound_conversion_data', $page_conversion_data);
		}


		/* Store page views for page tracking off */
		$page_tracking_status = get_option('wpl-main-page-view-tracking', 1);
		if($lead_data['page_views'] && $page_tracking_status == 0) {
			$page_view_data = get_post_meta( $lead_id, 'page_views', TRUE );
			$page_view_data = json_decode($page_view_data,true);

			// If page_view meta exists do this
			if (is_array($page_view_data)) {
				$new_page_views = inbound_json_array_merge( $page_view_data, $lead_data['page_views']);
				$page_views = json_encode($new_page_views);
			} else {
			// Create page_view meta if it doesn't exist
				$page_views = $lead_data['page_views'];
				$page_views = json_encode($page_views);
			}
			// View count
			$view_count = get_post_meta( $lead_id, 'wpleads_page_view_count', TRUE );
			if ($view_count){
				$page_view_count = $lead_data['page_view_count'] + $view_count;
			} else {
				$page_view_count = $lead_data['page_view_count'];
			}
			// update meta
			if ($lead_data['page_view_count']){
			update_post_meta($lead_id,'wpleads_page_view_count', $page_view_count);
			}
			update_post_meta($lead_id, 'page_views', $page_views );
		}

		/* Raw Form Values Store */
		if ($lead_data['form_input_values'])
		{
			$raw_post_data = get_post_meta($lead_id,'wpleads_raw_post_data', true);
			$a1 = json_decode( $raw_post_data, true );
			$a2 = json_decode( stripslashes($lead_data['form_input_values']), true );
			$exclude_array = array('card_number','card_cvc','card_exp_month','card_exp_year'); // add filter
			$lead_mapping_fields = Leads_Field_Map::build_map_array();

			foreach ($a2 as $key=>$value)
			{
				if (array_key_exists( $key , $exclude_array )) {
					unset($a2[$key]);
					continue;
				}
				if (preg_match("/\[\]/", $key)) {
					$key = str_replace("[]", "", $key); // fix array value keys
				}
				if (array_key_exists($key, $lead_mapping_fields)) {
					update_post_meta( $lead_id, $key, $value );
				}

				if (stristr($key,'company'))
				{
					update_post_meta( $lead_id, 'wpleads_company_name', $value );
				}
				else if (stristr($key,'website'))
				{
					$websites = get_post_meta( $lead_id, 'wpleads_websites', $value );
					if(is_array($websites)) {
						$array_websites = explode(';',$websites);
					}
					$array_websites[] = $value;
					$websites = implode(';',$array_websites);
					update_post_meta( $lead_id, 'wpleads_websites', $websites );
				}
			}
			// Merge form fields if exist
			if (is_array($a1)) {
				$new_raw_post_data = array_merge_recursive( $a1, $a2 );
			} else {
				$new_raw_post_data = $a2;
			}
			$new_raw_post_data = json_encode( $new_raw_post_data );
			update_post_meta( $lead_id,'wpleads_raw_post_data', $new_raw_post_data );
		}

		setcookie('wp_lead_id' , $lead_id, time() + (20 * 365 * 24 * 60 * 60),'/');

		do_action('inbound_store_lead_post', $lead_data );
		do_action('wp_cta_store_lead_post', $lead_data );
		do_action('wpl_store_lead_post', $lead_data );
		do_action('lp_store_lead_post', $lead_data );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && !$return ) {

			echo $lead_id;
			die();

		} else {
			return $lead_id;
		}
	}
}



function inbound_add_conversion_to_lead( $lead_id, $lead_data ) {


	if ( $lead_data['page_id'] ) {
		$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
		$lead_data['wordpress_date_time'] = date("Y-m-d G:i:s T", $time);
		$conversion_data = get_post_meta( $lead_id, 'wpleads_conversion_data', TRUE );
		$conversion_data = json_decode($conversion_data,true);
		$variation = $lead_data['variation'];

		if ( is_array($conversion_data)) {
			$c_count = count($conversion_data) + 1;
			$conversion_data[$c_count]['id'] = $lead_data['page_id'];
			$conversion_data[$c_count]['variation'] = $variation;
			$conversion_data[$c_count]['datetime'] = $lead_data['wordpress_date_time'];
		} else {
			$c_count = 1;
			$conversion_data[$c_count]['id'] = $lead_data['page_id'];
			$conversion_data[$c_count]['variation'] = $variation;
			$conversion_data[$c_count]['datetime'] = $lead_data['wordpress_date_time'];
			$conversion_data[$c_count]['first_time'] = 1;

		}

		$lead_data['conversion_data'] = json_encode($conversion_data);
		update_post_meta($lead_id,'wpleads_conversion_count', $c_count); // Store conversions count
		update_post_meta($lead_id, 'wpleads_conversion_data', $lead_data['conversion_data']); // Store conversion object

	}
}


/**
 *	Loop trough lead_data array and update post meta
 */
function inbound_update_common_meta($lead_data)
{
	/* Update user_ID if exists */
	if (!empty($lead_data['user_ID'])) {
		update_post_meta( $lead_data['lead_id'], 'wpleads_wordpress_user_id', $lead_data['user_ID'] );
	}

	/* Update wp_lead_uid if exist */
	if (!empty($lead_data['wp_lead_uid'])) {
		update_post_meta( $lead_data['lead_id'], 'wp_leads_uid', $lead_data['wp_lead_uid'] );
	}

	/* Update mappable fields */
	$lead_fields = Leads_Field_Map::build_map_array();
	foreach ( $lead_fields as $key => $value ) {
		if (isset($lead_data[$key])) {
			update_post_meta( $lead_data['lead_id'], $key , $lead_data[ $key ] );
		}
	}
}

/**
 *	Connects to geoplugin.net and gets data on IP address and sets it into historical log
 *	@param ARRAY $lead_data
 */
function inbound_update_geolocation_data( $lead_data ) {

	$ip_addresses = get_post_meta( $lead_data['lead_id'] , 'wpleads_ip_address', true );
	$ip_addresses = json_decode( stripslashes($ip_addresses) , true);

	if (!$ip_addresses) {
		$ip_addresses = array();
	}

	$new_record[ $lead_data['ip_address'] ]['ip_address'] = $lead_data['ip_address'];


	/* ignore for local environments */
	if ($lead_data['ip_address']!= "127.0.0.1"){ // exclude localhost
		$response = wp_remote_get('http://www.geoplugin.net/php.gp?ip='.$lead_data['ip_address']);
		if ( isset( $response['body'] ) ) {
			$geo_array = @unserialize( $response['body'] );
			$new_record[ $lead_data['ip_address'] ]['geodata'] = $geo_array;
		}

	}

	$ip_addresses = array_merge( $new_record , $ip_addresses );
	$ip_addresses = json_encode( $ip_addresses );

	update_post_meta( $lead_data['lead_id'], 'wpleads_ip_address', $ip_addresses );
}

function inbound_json_array_merge( $arr1, $arr2 ) {
	$keys = array_keys( $arr2 );
	foreach( $keys as $key ) {
		if( isset( $arr1[$key] )
			&& is_array( $arr1[$key] )
			&& is_array( $arr2[$key] )
		) {
			$arr1[$key] = my_merge( $arr1[$key], $arr2[$key] );
		} else {
			$arr1[$key] = $arr2[$key];
		}
	}
	return $arr1;
}




/**
* Loop through field map looking for key matches in array
* @param ARRAY $lead_data
* @param ARRAY $args
* @returns ARRAY $lead_data
*/
function inbound_search_args_for_mapped_data( $lead_data , $args ) {
	$lead_fields = Leads_Field_Map::build_map_array();

	foreach ($lead_fields as $key => $label) {
		if (isset($args[$key]) && !empty($args[$key])) {
			$lead_data[$key] = $args[$key];
		}
	}

	return $lead_data;
}
add_action( 'inboundnow_store_lead_pre_filter_data' , 'inbound_search_args_for_mapped_data' , 10 , 2);

/**
*	Assembles first,last, & full name from partial data
*/
function inbound_check_lead_name( $lead_data ) {

	/* if last name empty and full name present */
	if ( empty($lead_data['wpleads_last_name']) && $lead_data['wpleads_full_name'] ) {
		$parts = explode(' ' , $lead_data['wpleads_full_name']);

		/* Set first name */
		$lead_data['wpleads_first_name'] = $parts[0];

		/* Set last name */
		if (isset($parts[1])) {
			$lead_data['wpleads_last_name'] = $parts[1];
		}
	}
	/* if last name empty and first name present */
	else if (empty($lead_data['wpleads_last_name']) && $lead_data['wpleads_first_name'] ) {
		$parts = explode(' ' , $lead_data['wpleads_first_name']);

		/* Set First Name */
		$lead_data['wpleads_first_name'] = $parts[0];

		/* Set Last Name */
		if (isset($parts[1])) {
			$lead_data['wpleads_last_name'] = $parts[1];
		}
	}

	return $lead_data;
}
add_action( 'inboundnow_store_lead_pre_filter_data' , 'inbound_check_lead_name' , 10 , 1);

/**
 *	Loads correct lead UID during a login
 */
function inbound_load_tracking_cookie( $user_login, $user) {

	if (!isset($user->data->user_email)) {
		return;
	}

	global $wp_query;

	/* search leads cpt for record containing email & get UID */
	$results = new WP_Query( array( 'post_type' => 'wp-lead' , 's' => $user->data->user_email ) );

	if (!$results) {
		return;
	}

	if ( $results->have_posts() ) {
		while ( $results->have_posts() ) {

			$uid = get_post_meta( $results->post->ID , 'wp_leads_uid' , true );

			if ($uid) {
				setcookie( 'wp_lead_uid' , $uid , time() + (20 * 365 * 24 * 60 * 60),'/');
			}

			setcookie( 'wp_lead_id' , $results->post->ID , time() + (20 * 365 * 24 * 60 * 60),'/');
			return;
		}
	}
}
add_action('wp_login', 'inbound_load_tracking_cookie', 10, 2);

/**
*  Get IP Address, check for x-forwarded-for header first and falls back on the server remote address
*  @returns STRING ip address
*/
function inbound_get_ip_address() {
	if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		if(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$proxy = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$proxy = $_SERVER["REMOTE_ADDR"];
		}

		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		if(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
	}

	return $ip;
}