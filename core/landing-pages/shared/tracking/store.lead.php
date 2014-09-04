<?php
/**
* Inbound Lead Storage
*
* - Handles lead creation and data storage
*/


if (!function_exists('inbound_store_lead_search')) {

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
}

if (!function_exists('inbound_store_lead')) {

add_action('wp_ajax_inbound_store_lead', 'inbound_store_lead');
add_action('wp_ajax_nopriv_inbound_store_lead', 'inbound_store_lead');

function inbound_store_lead( $args = array() ) {
	global $user_ID, $wpdb;
	/**
	// simulate ajax fail
	header('HTTP/1.0 404 Not found'); exit;
	/**/

	// Grab form values
	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$lead_data['user_ID'] = $user_ID;
	$lead_data['wordpress_date_time'] = date("Y-m-d G:i:s T", $time);
	$lead_data['wpleads_email_address'] = (isset($_POST['emailTo'])) ? $_POST['emailTo'] : false;
	$lead_data['page_views'] = (isset($_POST['page_views'])) ?  $_POST['page_views'] : false;
	$lead_data['form_input_values'] = (isset($_POST['form_input_values'])) ? $_POST['form_input_values'] : false; // raw post data
	$lead_data['Mapped_Data'] = (isset($_POST['Mapped_Data'] )) ? $_POST['Mapped_Data'] : false; // mapped data
	($lead_data['Mapped_Data']) ? $mapped_data = json_decode(stripslashes($lead_data['Mapped_Data']), true ) : $mapped_data = array(); // mapped data array
	$lead_data['page_view_count'] = (array_key_exists('page_view_count', $mapped_data)) ? $mapped_data['page_view_count'] : 0;
	$lead_data['source'] = (array_key_exists('source', $mapped_data)) ? $mapped_data['source'] : 'NA';
	$lead_data['page_id'] = (array_key_exists('page_id', $mapped_data)) ? $mapped_data['page_id'] : '0';
	$lead_data['variation'] = (array_key_exists('variation', $mapped_data)) ? $mapped_data['variation'] : '0';
	$lead_data['post_type'] = (array_key_exists('post_type', $mapped_data)) ? $mapped_data['post_type'] : 'na';
	$lead_data['wp_lead_uid'] = (array_key_exists('wp_lead_uid', $mapped_data)) ? $mapped_data['wp_lead_uid'] : false;
	$lead_data['lead_lists'] = (array_key_exists('leads_list', $mapped_data)) ? explode(",", $mapped_data['leads_list']) : false;
	$lead_data['ip_address'] = (array_key_exists('ip_address', $mapped_data)) ? $mapped_data['ip_address'] : false;


	/* POST Vars */
	$lead_data['page_id'] = ( !$lead_data['page_id'] && isset($_POST['page_id'])) ? $_POST['page_id'] : $lead_data['page_id'] ;
	$lead_data['variation'] = (array_key_exists('variation', $mapped_data)) ? $mapped_data['variation'] : '0';

	$raw_search_data = (isset($_POST['Search_Data'])) ? $_POST['Search_Data'] : false;
	$search_data = json_decode(stripslashes($raw_search_data), true ); // mapped data array
	$lead_data['search_data'] = $search_data;

	$lead_data['wpleads_full_name'] = (isset($_POST['full_name'])) ?  $_POST['full_name'] : "";
	$lead_data['wpleads_first_name'] = (isset($_POST['first_name'])) ?  $_POST['first_name'] : "";

	$lead_data['wpleads_last_name'] = (isset($_POST['last_name'])) ? $_POST['last_name'] : "";
	$lead_data['wpleads_company_name'] = (isset($_POST['company_name'] )) ? $_POST['company_name'] : "";
	$lead_data['wpleads_mobile_phone'] = (isset($_POST['phone'])) ? $_POST['phone'] : "";
	$lead_data['wpleads_address_line_1'] = (isset($_POST['address'])) ? $_POST['address'] : "";
	$lead_data['wpleads_address_line_2'] = (isset($_POST['address_2'])) ? $_POST['address_2'] : "";
	$lead_data['wpleads_city'] = (isset($_POST['city'])) ? $_POST['city'] : "";
	$lead_data['wpleads_region_name'] = (isset($_POST['region'])) ? $_POST['region'] : "";
	$lead_data['wpleads_zip'] = (isset($_POST['zip'])) ? $_POST['zip'] : "";

	/* Legacy - Phasing Out *
	$lead_data['first_name'] = (isset($_POST['first_name'])) ?  $_POST['first_name'] : "";
	$lead_data['last_name'] = (isset($_POST['last_name'])) ? $_POST['last_name'] : "";
	$lead_data['email'] = (isset($_POST['emailTo'])) ? $_POST['emailTo'] : false;
	$lead_data['lp_variation'] = (array_key_exists('variation', $mapped_data)) ? $mapped_data['variation'] : '0'; //legacy for landing pages
	*/

	/* NEW MAPPING Loop In Progress */
	/**
	$check_map = array(
		"phone" => 'wpleads_work_phone',
		"company" => "wpleads_company_name",
		'first_name' => 'wpleads_first_name',
		'last_name' => 'wpleads_last_name',
		'address' => 'wpleads_address_line_1'
		);
	// have filter
	foreach ($mapped_data as $key => $value) {
		if (array_key_exists($key, $check_map)) {
			//$lead_data['source'] = '';
		   //update_post_meta( $lead_data['lead_id'], $check_map[$key], $value);
		}
		//update_post_meta( $lead_data['lead_id'], 'FormData', $key); // replace times
	}

	if( array_key_exists('leads_list', $mapped_data)) {
		$lead_data['lead_lists'] = explode(",", $mapped_data['leads_list']);
	}
	/* END NEW MAPPING In Progress */

	if ($args){
		$lead_data = array_merge( $lead_data , $args );
	}

	$lead_data = apply_filters( 'inboundnow_store_lead_pre_filter_data' , $lead_data);

	do_action('inbound_store_lead_pre' , $lead_data); // Global lead storage action hook

	// check for set email
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
			update_post_meta( $lead_id, 'wpleads_wordpress_user_id', $user_ID );

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
			update_post_meta( $lead_id, 'wpleads_ip_address', $lead_data['ip_address'] );
			if ($lead_data['ip_address'] != "127.0.0.1"){ // exclude localhost
			$geo_array = @unserialize(wp_remote_get('http://www.geoplugin.net/php.gp?ip='.$lead_data['ip_address']));
			(isset($geo_array['geoplugin_areaCode'])) ? update_post_meta( $lead_id, 'wpleads_areaCode', $geo_array['geoplugin_areaCode'] ) : null;
			(isset($geo_array['geoplugin_city'])) ? update_post_meta( $lead_id, 'wpleads_city', $geo_array['geoplugin_city'] ) : null;
			(isset($geo_array['geoplugin_regionName'])) ? update_post_meta( $lead_id, 'wpleads_region_name', $geo_array['geoplugin_regionName'] ) : null;
			(isset($geo_array['geoplugin_regionCode'])) ? update_post_meta( $lead_id, 'wpleads_region_code', $geo_array['geoplugin_regionCode'] ) : null;
			(isset($geo_array['geoplugin_countryName'])) ? update_post_meta( $lead_id, 'wpleads_country_name', $geo_array['geoplugin_countryName'] ) : null;
			(isset($geo_array['geoplugin_countryCode'])) ? update_post_meta( $lead_id, 'wpleads_country_code', $geo_array['geoplugin_countryCode'] ) : null;
			(isset($geo_array['geoplugin_latitude'])) ? update_post_meta( $lead_id, 'wpleads_latitude', $geo_array['geoplugin_latitude'] ) : null;
			(isset($geo_array['geoplugin_longitude'])) ? update_post_meta( $lead_id, 'wpleads_longitude', $geo_array['geoplugin_longitude'] ) : null;
			(isset($geo_array['geoplugin_currencyCode'])) ? update_post_meta( $lead_id, 'wpleads_currency_code', $geo_array['geoplugin_currencyCode'] ) : null;
			(isset($geo_array['geoplugin_currencySymbol_UTF8'])) ? update_post_meta( $lead_id, 'wpleads_currency_symbol', $geo_array['geoplugin_currencySymbol_UTF8'] ) : null;
			}
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


		/* Store Conversion Data to LANDING PAGE/CTA DATA  */
		if ($lead_data['post_type'] == 'landing-page' || $lead_data['post_type'] == 'wp-call-to-action')
		{
			$page_conversion_data = get_post_meta( $lead_data['page_id'], 'inbound_conversion_data', TRUE );
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
			update_post_meta($lead_data['page_id'], 'inbound_conversion_data', $page_conversion_data);
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

		if (!$args) {

			echo $lead_id;
			die();

		} else {
			return $lead_id;
		}
	}
}
}

if (!function_exists('inbound_add_conversion_to_lead')) {
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
}

if (!function_exists('inbound_update_common_meta'))
{
	function inbound_update_common_meta($lead_data)
	{

		if (!empty($lead_data['user_ID'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_wordpress_user_id', $lead_data['user_ID'] );
		}
		if (!empty($lead_data['wpleads_first_name'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_first_name', $lead_data['wpleads_first_name'] );
		}
		if (!empty($lead_data['wpleads_last_name'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_last_name', $lead_data['wpleads_last_name'] );
		}
		if (!empty($lead_data['wpleads_mobile_phone'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_work_phone', $lead_data['wpleads_mobile_phone'] );
		}
		if (!empty($lead_data['wpleads_company_name'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_company_name', $lead_data['wpleads_company_name'] );
		}
		if (!empty($lead_data['wpleads_address_line_1'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_address_line_1', $lead_data['wpleads_address_line_1'] );
		}
		if (!empty($lead_data['wpleads_address_line_2'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_address_line_2', $lead_data['wpleads_address_line_2'] );
		}
		if (!empty($lead_data['wpleads_city'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_city', $lead_data['wpleads_city'] );
		}
		if (!empty($lead_data['wpleads_region_name'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_region_name', $lead_data['wpleads_region_name'] );
		}
		if (!empty($lead_data['wpleads_zip'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_zip', $lead_data['wpleads_zip'] );
		}
		if (!empty($lead_data['wpleads_country'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_country', $lead_data['wpleads_country'] );
		}
		if (!empty($lead_data['wpleads_shipping_address_line_1'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_shipping_address_line_1', $lead_data['wpleads_shipping_address_line_1'] );
		}
		if (!empty($lead_data['wpleads_shipping_address_line_2'])) {
			update_post_meta( $lead_data['lead_id'], 'wpleads_shipping_address_line_2', $lead_data['wpleads_shipping_address_line_2'] );
		}
		if (!empty($lead_data['wp_lead_uid'])) {
			update_post_meta( $lead_data['lead_id'], 'wp_leads_uid', $lead_data['wp_lead_uid'] );
		}
	}
}

if (!function_exists('inbound_json_array_merge')) {
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
}



/* Custom Mappings coming soon
add_filter('inboundnow_custom_map_values_filter', 'inbound_map_custom_fields', 10, 1);
if (!function_exists('inbound_map_custom_fields')) {
function inbound_map_custom_fields($custom_map_values) {

 	$new_fields =  array(
				        'field_label' => 'Timmmm Company',
				        'field_name'  => 'wpleads_ip_addressy',
				        'map_to' => 'wpleads_ip_address'
				    );

		foreach ($new_fields as $key => $value) {
			array_push($custom_map_values, $new_fields[$key]);
		}

        return $custom_map_values;

}
}

add_action('wp_head', 'custom_js_insert');
function custom_js_insert() { ?>
<script type="text/javascript">
// Ensure global inbound_data has been initialized.
var inbound_data = inbound_data || {};
inbound_data['custom_map_val'] = 'hi hi hi';
</script>
<?php }
*/