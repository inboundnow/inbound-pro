<?php
/**
* Inbound Lead Storage
*
* - Handles lead creation and data storage
*/

if (!class_exists('LeadStorage')) {
	class LeadStorage {
		static $mapped_fields;
		static $is_ajax;

		/**
		*	Initialize class
		*/
		static function init() {
			/* determines if in ajax mode */
			self::set_mode();

			/* sets up ajax listeners */
			add_action('wp_ajax_inbound_lead_store', array(__CLASS__, 'inbound_lead_store'), 10, 1);
			add_action('wp_ajax_nopriv_inbound_lead_store', array(__CLASS__, 'inbound_lead_store'), 10, 1);

			/* filters name data to build a more comprehensive data set */
			add_filter( 'inboundnow_store_lead_pre_filter_data',	array(__CLASS__, 'improve_lead_name'), 10 , 1);
		}

		/**
		*	Checks if running in ajax mode
		*/
		static function set_mode( $mode = 'auto' ) {
			// http://davidwalsh.name/detect-ajax
			switch( $mode ) {
				case 'auto':
					self::$is_ajax =	( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? true : false;
					BREAK;
				case 'ajax':
					self::$is_ajax = true;
					BREAK;
				case 'return':
					self::$is_ajax = false;
					BREAK;
			}
		}

		/**
		*	Stores lead
		*/
		static function inbound_lead_store( $args ) {
			global $user_ID, $wpdb;
			if (!is_array($args)) { $args = array(); }

			/* Mergs $args with POST request for support of ajax and direct calls */
			if(isset($_POST)){
				$args = array_merge( $args, $_POST );
			}

			$lead = array();
			if(isset($user_ID)){
				$lead['user_ID'] = $user_ID;
			}
			/* Current wordpress time from settings */
			$time = current_time( 'timestamp', 0 );
			$lead['wordpress_date_time'] = date("Y-m-d G:i:s T", $time);

			$lead['email'] = str_replace("%40", "@", self::check_val('email', $args));
			$lead['name'] = str_replace("%20", " ", self::check_val('full_name', $args));
			$lead['first_name'] = str_replace("%20", "", self::check_val('first_name', $args));
			$lead['last_name'] = str_replace("%20", "", self::check_val('last_name', $args));
			$lead['page_id'] = self::check_val('page_id', $args);
			$lead['page_views'] = self::check_val('page_views', $args);
			$lead['raw_params'] = self::check_val('raw_params', $args);

			$lead['mapped_params'] = self::check_val('mapped_params', $args);
			$lead['url_params'] = self::check_val('url_params', $args);
			$lead['variation'] = self::check_val('variation', $args);
			$lead['source'] = self::check_val('source', $args);
			$lead['ip_address'] = self::lookup_ip_address();

			if($lead['mapped_params']){
				parse_str($lead['mapped_params'], $mappedData);
			} else {
				$mappedData = array();
			}

			$mappedData = self::improve_mapping($mappedData, $lead);

			/* prepate lead lists */
			$lead['lead_lists'] = (isset($args['lead_lists'])) ? $args['lead_lists'] : null;
			if ( !$lead['lead_lists'] && array_key_exists('inbound_form_lists', $mappedData) ) {
				$lead['lead_lists'] = explode(",", $mappedData['inbound_form_lists']);
			}

			/* Look for direct key matches & clean up $lead_data */
			$lead = apply_filters( 'inboundnow_store_lead_pre_filter_data', $lead, $args);

			/* TODO have fallbacks for existing lead ID or Lead UID lookups*/
			/* check for set email */
			if ( (isset($lead['email']) && !empty($lead['email']) && strstr($lead['email'] ,'@'))) {


				$leadExists = self::lookup_lead_by_email($lead['email']);

				/* Update Lead if Exists else Create New Lead */
				if ( $leadExists ) {
					$lead['id'] = $leadExists;
					/* action hook on existing leads only */
					do_action('wpleads_existing_lead_update', $lead);
				} else {
				/* Create new lead if one doesnt exist */
					$lead['id'] = self::store_new_lead($lead);
				}

				/* do everything else for lead storage */
				self::update_common_meta($lead);

				do_action('wpleads_after_conversion_lead_insert', $lead['id']); // action hook on all lead inserts

				/* Add Leads to List on creation */
				if(!empty($lead['lead_lists']) && is_array($lead['lead_lists'])){
					global $Inbound_Leads;
					$Inbound_Leads->add_lead_to_list($lead['id'], $lead['lead_lists'], 'wplead_list_category');
				}

				/* Store page views for people with ajax tracking off */
				$ajax_tracking_off = false; // get_option
				if($lead['page_views'] && $ajax_tracking_off ) {
					self::store_page_views($lead);
				}

				/* Store Mapped Form Data */
				if(!empty($mappedData)){
					self::store_mapped_data($lead, $mappedData);
				}

				/* Store past search history */
				if(isset($lead['search_data'])){
					self::store_search_history($lead);
				}

				/* Store ConversionData */
				if ( isset($lead['page_id']) && $lead['page_id']  ) {
					self::store_conversion_data($lead);
				}

				/* Store Lead Source */
				if ( isset($lead['source']) ) {
					self::store_referral_data($lead);
				}

				/* Store URL Params */
				if($lead['url_params']) {
					$param_array = json_decode(stripslashes($lead['url_params']));
					//print_r($param_array); exit;
					if(is_array($param_array)){

					}
				}

				/* Store Conversion Data to LANDING PAGE/CTA DATA	*/
				if (isset($lead['page_id'])) {
					self::store_conversion_stats($lead);
				}

				/* Store IP addresss & Store GEO Data */
				if ($lead['ip_address']) {
					self::store_geolocation_data($lead);
				}

				if ( self::$is_ajax ) {
					echo $lead['id'];
					header('HTTP/1.1 200 OK');
					do_action('inbound_store_lead_post', $lead );
					exit;
				} else {
					do_action('inbound_store_lead_post', $lead );
					return $lead['id'];
				}

			}
		}

		/**
		*	Creates new lead in wp-lead post type
		*/
		static function store_new_lead($lead){
			/* Create New Lead */
			$post = array(
				'post_title'		=> $lead['email'],
				//'post_content'		=> $json,
				'post_status'		=> 'publish',
				'post_type'		=> 'wp-lead',
				'post_author'		=> 1
			);

			//$post = add_filter('lp_leads_post_vars',$post);
			$id = wp_insert_post($post);
			/* specific updates for new leads */
			update_post_meta( $id, 'wpleads_email_address', $lead['email'] );
			/* new lead run simple page_view storage */
			update_post_meta( $id, 'page_views', $lead['page_views']);
			/* dont need update_post_meta( $id, 'wpleads_page_view_count', $lead['page_view_count']); */

			do_action('wpleads_new_lead_insert', $lead ); // action hook on new leads only
			return $id;
		}

		/**
		*	Updates pages viewed object
		*/
		static function store_page_views($lead){
			$page_view_data = get_post_meta( $lead['id'], 'page_views', TRUE );
			$page_view_data = json_decode($page_view_data,true);

			// If page_view meta exists do this
			if (is_array($page_view_data)) {
				$new_page_views = self::json_array_merge( $page_view_data, $lead['page_views']);
				$page_views = json_encode($new_page_views);
			} else {
			// Create page_view meta if it doesn't exist
				$page_views = $lead['page_views'];
				$page_views = json_encode($page_views);
			}
			update_post_meta($lead['id'], 'page_views', $page_views );
		}

		/**
		*	Prefixes keys with wpleads_ if key is not prepended with wpleads_
		*/
		static function store_mapped_data($lead, $mappedData){

			foreach ($mappedData as $key => $value) {
				
				if (!$value) {
					continue;
				}
				
				/* sanitise inputs */
				if (is_string($value)) {
					$value = strip_tags( $value );
				}

				update_post_meta($lead['id'], $key, $value);
				
				/* Old convention with wpleads_ prefix */
				if( !strstr($key,'wpleads_') ) {
					update_post_meta($lead['id'], 'wpleads_'.$key, $value);
				} else {
					update_post_meta($lead['id'], $key, $value);
				}

			}
		}

		/**
		*	Updates search history object
		*/
		static function store_search_history($lead){
				$search = $lead['search_data'];
				$search_data = get_post_meta( $lead['id'], 'wpleads_search_data', TRUE );
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
				update_post_meta($lead['id'], 'wpleads_search_data', $search_data); // Store search object
		}

		/**
		*		updates conversion data object
		*/
		static function store_conversion_data( $lead ) {

				$conversion_data = get_post_meta( $lead['id'], 'wpleads_conversion_data', TRUE );
				$conversion_data = json_decode($conversion_data,true);
				$variation = $lead['variation'];

				if ( is_array($conversion_data)) {
					$c_count = count($conversion_data) + 1;
					$conversion_data[$c_count]['id'] = $lead['page_id'];
					$conversion_data[$c_count]['variation'] = $variation;
					$conversion_data[$c_count]['datetime'] = $lead['wordpress_date_time'];
				} else {
					$c_count = 1;
					$conversion_data[1]['id'] = $lead['page_id'];
					$conversion_data[1]['variation'] = $variation;
					$conversion_data[1]['datetime'] = $lead['wordpress_date_time'];
					$conversion_data[1]['first_time'] = 1;
				}

				$lead['conversion_data'] = json_encode($conversion_data);
				update_post_meta($lead['id'],'wpleads_conversion_count', $c_count); // Store conversions count
				update_post_meta($lead['id'], 'wpleads_conversion_data', $lead['conversion_data']);// Store conversion obj

		}
		/**
		*	Store Conversion Data to LANDING PAGE/CTA DATA
		*/
		static function store_conversion_stats($lead){
			$page_conversion_data = get_post_meta( $lead['page_id'], '_inbound_conversion_data', TRUE );
			$page_conversion_data = json_decode($page_conversion_data,true);
			$version = ($lead['variation'] != 'default') ? $lead['variation'] : '0';
			if (is_array($page_conversion_data)) {
				$convert_count = count($page_conversion_data) + 1;
				$page_conversion_data[$convert_count]['lead_id'] = $lead['id'];
				$page_conversion_data[$convert_count]['variation'] = $version;
				$page_conversion_data[$convert_count]['datetime'] = $lead['wordpress_date_time'];
			} else {
				$page_conversion_data[1]['lead_id'] = $lead['id'];
				$page_conversion_data[1]['variation'] = $version;
				$page_conversion_data[1]['datetime'] = $lead['wordpress_date_time'];
			}
			$page_conversion_data = json_encode($page_conversion_data);
			update_post_meta($lead['page_id'], '_inbound_conversion_data', $page_conversion_data);
		}

		/**
		*	Stores referral data
		*/
		static function store_referral_data($lead) {
			$referral_data = get_post_meta( $lead['id'], 'wpleads_referral_data', TRUE );

			// Parse referral for additional data
			include_once( INBOUDNOW_SHARED_PATH. 'assets/includes/Snowplow/RefererParser/INBOUND_Parser.php');
			include_once( INBOUDNOW_SHARED_PATH .'assets/includes/Snowplow/RefererParser/INBOUND_Referer.php');
			include_once(INBOUDNOW_SHARED_PATH . 'assets/includes/Snowplow/RefererParser/INBOUND_Medium.php');
			// intialized the parser class
			$parser = new INBOUND_Parser();
			//$array = array('http://google.com', 'http://twitter.com', 'http://tumblr.com?query=test', '');
			$referer = $parser->parse($lead['source']);

			 if ( $referer->isKnown() ) {
					$ref_type = $referer->getMedium();

			 } else {
			 	// check if ref exists
			 	$ref_type = ($lead['source'] === "Direct Traffic") ? 'Direct Traffic' : 'referral';
			 }

			$referral_data = json_decode($referral_data,true);
			if (is_array($referral_data)){
				$r_count = count($referral_data) + 1;
				$referral_data[$r_count]['source'] = $lead['source'];
				$referral_data[$r_count]['type'] = $ref_type;
				$referral_data[$r_count]['datetime'] = $lead['wordpress_date_time'];
			} else {
				$referral_data[1]['source'] = $lead['source'];
				$referral_data[1]['type'] = $ref_type;
				$referral_data[1]['datetime'] = $lead['wordpress_date_time'];
				$referral_data[1]['original_source'] = 1;
			}

			$lead['referral_data'] = json_encode($referral_data);
			//echo $lead['referral_data']; exit;
			update_post_meta($lead['id'], 'wpleads_referral_data', $lead['referral_data']); // Store referral object
			update_post_meta($lead['id'], 'wpleads_referral_type', $ref_type); // Store referral object
		}

		/**
		*		Loop trough lead_data array and update post meta
		*/
		static function update_common_meta($lead) {

			//print_r($lead);

			if (!empty($lead['user_ID'])) {
				/* Update user_ID if exists */
				update_post_meta( $lead['id'], 'wpleads_wordpress_user_id', $lead['user_ID'] );
			}

			/* Update wp_lead_uid if exist */
			if (!empty($lead['wp_lead_uid'])) {
				update_post_meta( $lead['id'], 'wp_leads_uid', $lead['wp_lead_uid'] );
			}

			/* Update email address */
			if (!empty($lead['email'])) {
				update_post_meta( $lead['id'], 'wplead_email_address', $lead['email'] );
			}

			/* Update mappable fields that have a value associated with them */
			$lead_fields = Leads_Field_Map::build_map_array();
			foreach ( $lead_fields as $key => $value ) {
				$shortkey = str_replace('wpleads_' , '' , $key );
				if (isset($lead[$shortkey])) {
					update_post_meta( $lead['id'], $key, $lead[$shortkey] );
				}
			}
			//exit;
		}

		/**
		 *	Connects to geoplugin.net and gets data on IP address and sets it into historical log
		 *	@param ARRAY $lead_data
		 */
		static function store_geolocation_data( $lead ) {

			$ip_addresses = get_post_meta( $lead['id'], 'wpleads_ip_address', true );
			$ip_addresses = json_decode( stripslashes($ip_addresses) , true);

			if (!$ip_addresses) {
				$ip_addresses = array();
			}

			$new_record[ $lead['ip_address'] ]['ip_address'] = $lead['ip_address'];


			/* ignore for local environments */
			if ($lead['ip_address']!= "127.0.0.1"){ // exclude localhost
				$response = wp_remote_get('http://www.geoplugin.net/php.gp?ip='.$lead['ip_address']);
				if ( isset($response['body']) ) {
					$geo_array = @unserialize($response['body']);
					$new_record[ $lead['ip_address'] ]['geodata'] = $geo_array;
				}

			}

			$ip_addresses = array_merge( $new_record, $ip_addresses );
			$ip_addresses = json_encode( $ip_addresses );

			update_post_meta( $lead['id'], 'wpleads_ip_address', $ip_addresses );
		}

		/**
		*	Updates raw form data object
		*/
		static function store_raw_form_data($lead){
			/* Raw Form Values Store */
			if ($lead_data['form_input_values']) {
				$raw_post_data = get_post_meta($$lead['id'],'wpleads_raw_post_data', true);
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

					if (stristr($key,'company')) {
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
		}

		/**
		*	Parses & improves lead name
		*/
		static function improve_lead_name( $lead ) {

			/* if last name empty and full name present */
			if ( empty($lead['last_name']) && $lead['name'] ) {
				$parts = explode(' ' , $lead['name']);

				/* Set first name */
				$lead['first_name'] = $parts[0];

				/* Set last name */
				if (isset($parts[1])) {
					$lead['last_name'] = $parts[1];
				}
			}
			/* if last name empty and first name present */
			else if (empty($lead['last_name']) && $lead['first_name'] ) {
				$parts = explode(' ' , $lead['first_name']);

				/* Set First Name */
				$lead['first_name'] = $parts[0];

				/* Set Last Name */
				if (isset($parts[1])) {
					$lead['last_name'] = $parts[1];
				}
			}

			/* set full name */
			if (!$lead['name'] && $lead['first_name'] && $lead['last_name'] ) {
				$lead['name'] = $lead['first_name'] .' '. $lead['last_name'];
			}

			return $lead;
		}

		/**
		*	Uses mapped data if not programatically set
		*/
		static function improve_mapping($mappedData, $lead) {
			
			/* remove instances of wpleads_ */
			$newMap = array();
			foreach ($mappedData as $key=>$value) {
				$key = str_replace('wpleads_','',$key);
				$newMap[$key] = $value;
			}
			
			/* Set names if not mapped */
			$newMap['first_name'] = (!isset($newMap['first_name'])) ? $lead['first_name'] : $newMap['first_name'];
			$newMap['last_name'] = (!isset($newMap['last_name'])) ? $lead['last_name'] : $newMap['last_name'];
			
			/* improve mapped names */
			$newMap = self::improve_lead_name( $newMap );
			
			return $newMap;
		}

		/**
		*	Search lead by email
		*/
		static function lookup_lead_by_email($email){
			global $wpdb;
			$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
				$email
			);
			$wpdb->query( $query );
			if ( $wpdb->num_rows ) {
				$lead_id = $wpdb->get_var( $query );
				return $lead_id;
			} else {
				return false;
			}

		}

		static function check_val($key, $args) {
			$val = (isset($args[$key])) ? $args[$key] : false;
			return $val;
		}
		static function json_array_merge( $arr1, $arr2 ) {
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
		*	Discover session IP address
		*/
		static function lookup_ip_address() {
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

	}

	LeadStorage::init();
}

/**
* Legacy function used by some extensions
* @param ARRAY $args legacy dataset of mapped lead fields
* @param BOOL $return set to true to disable printing of lead id
*/
if (!function_exists('inbound_store_lead')) {
	function inbound_store_lead( $args , $return = true	) {
		global $user_ID, $wpdb;

		if (!is_array($args)) {
			$args = array();
		}

		/* Mergs $args with POST request for support of ajax and direct calls */
		$args = array_merge( $args , $_POST );

		/* wpleads_email_address becomes wpleads_email */
		$args['email'] = $args['wpleads_email_address'];

		/* loop through and remove wpleads_ (we will add them back in the new method ) */
		foreach ($args as $key => $value) {
			$newkey = str_replace( 'wpleads_' , '' , $key );
			unset($args[$key]);
			$args[$newkey] = $value;
		}

		/* Send data through new method */
		$Leads = new LeadStorage();
		if ($return) {
			$Leads->set_mode('return');
		} else {
			$Leads->set_mode('ajax');
		}

		/* prepare lead lists as array */
		if (isset($args['lead_lists']) && !is_array($args['lead_lists'])) {
			$args['lead_lists'] = explode(',',$args['lead_lists']);
		}

		$lead_id = $Leads::inbound_lead_store( $args );

		return $lead_id;


	}
}


/**
*  Legacy functions for adding conversion to lead profile
*  @param INT $lead_id
*  @param ARRAY dataset of lead informaiton
*/
if (!function_exists('inbound_add_conversion_to_lead')) {
	function inbound_add_conversion_to_lead( $lead_id , $lead_data ) {
	
		
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
