<?php

/**
*  Hook into the API to set tracked API link clicks into the lead profile
*/
add_action( 'inbound_track_link' , 'inbound_store_tracked_link_click'  , 10 , 1);
function inbound_store_tracked_link_click( $params ) {
	
	if ( !isset($params['id']) ) {
		return;
	}
	
	$inbound_custom_events = get_post_meta( $params['id'] , 'inbound_custom_events' , true);
	
	if ( isset($inbound_custom_events) ) {
		$inbound_custom_events = json_decode( $inbound_custom_events , true);
	} else {
		$inbound_custom_events = array();
	}
	
	$inbound_custom_events[] = array( 
		'event_type' => 'click' ,
		'datetime' => $params['datetime'],
		'tracking_id' => $params['tracking_id'],
		'url' => $params['url']
	);
	
	$inbound_custom_events = json_encode( $inbound_custom_events );
	
	update_post_meta(  $params['id'] , 'inbound_custom_events' , $inbound_custom_events) ;
	
}

/* needs documentation  - looks like a listener to set the lead id manually */
add_action( 'wp_head', 'wpleads_set_lead' );
function wpleads_set_lead() {
	if (isset($_GET['wpl_email'])) {
		$lead_id = $_GET['wpl_email'];
		wpleads_set_lead_id($lead_id);
	}
}

/* cookies lead id */
function wpleads_set_lead_id($lead_id){
	global $wpdb;

	$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
				$lead_id
				);

	$wpdb->query( $query );

	if ( $wpdb->num_rows ) {
		$lead_ID = $wpdb->get_var( $query );
		setcookie('wp_lead_id' , $lead_ID, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
}

/**
 * Sets cookie with current lists the lead is a part of
 * @param  string $lead_id - lead CPT id
 * @return sets cookie of lists lead belongs to
 */
function wp_leads_set_current_lists($lead_id) {

	$terms = get_the_terms( $lead_id , 'wplead_list_category' );
	if ( $terms && ! is_wp_error( $terms ) ) {

		$lead_list = array();
		$count = 0;
		foreach ( $terms as $term ) {
			$lead_list[] = $term->term_id;
			$count++;
		}

		//$test = serialize($lead_list);
		$list_array = json_encode(array( 'ids' => $lead_list )); ;

		setcookie('wp_lead_list' , $list_array, time() + (20 * 365 * 24 * 60 * 60),'/');
	}
}


/**
 * wp_leads_update_page_view_obj updates page_views meta for known leads
 * @param  ARRAY $lead_data   array of data associated with page view event
 */

function wp_leads_update_page_view_obj( $lead_data ) {

	if( !$lead_data['page_id']){
		return;
	}

	$current_page_view_count = get_post_meta( $lead_data['lead_id'] ,'wpleads_page_view_count', true);

	$increment_page_views = $current_page_view_count + 1;

	update_post_meta( $lead_data['lead_id'] , 'wpleads_page_view_count' , $increment_page_views ); // update count

	$time = current_time( 'timestamp' , 0 ); // Current wordpress time from settings
	$wordpress_date_time = date("Y-m-d G:i:s T", $time);

	$page_view_data = get_post_meta( $lead_data['lead_id'] , 'page_views', TRUE );
	//echo $lead_data['page_id']; // for debug

	// If page_view meta exists do this
	if ($page_view_data) {
		$current_count = 0; // default
		$timeout = 30;  // 30 Timeout analytics tracking for same page timestamps
		$page_view_data = json_decode( $page_view_data , true );

		// increment view count on page
		if(isset($page_view_data[ $lead_data['page_id'] ])) {
			$current_count = count($page_view_data[ $lead_data['page_id'] ]);
			$last_view = $page_view_data[ $lead_data['page_id'] ][$current_count];
			$timeout = abs(strtotime($last_view) - strtotime($wordpress_date_time));
		}

		// If page hasn't been viewed in past 30 seconds. Log it
		if ($timeout >= 30) {
			$page_view_data[ $lead_data['page_id'] ][ $current_count + 1 ] = $wordpress_date_time;
			$page_view_data = json_encode($page_view_data);
			update_post_meta( $lead_data['lead_id'] , 'page_views' , $page_view_data );
		}

	} else {
		// Create page_view meta if it doesn't exist
		$page_view_data = array();
		$page_view_data[ $lead_data['page_id'] ][0] = $wordpress_date_time;
		$page_view_data = json_encode( $page_view_data );
		update_post_meta( $lead_data['lead_id'] , 'page_views' , $page_view_data );
	}

	/* Run hook that tells WordPress lead data has been updated */
	do_action('wplead_page_view' , $lead_data );
}

/**
 * wpleads_check_url_for_queries disects keyword params from referring url
 * @param  string $referrer
 */
function wpleads_check_url_for_queries($referrer) {
	//now check if google
	if (strstr($referrer,'q=')) {
		//get keywords
		preg_match('/q=(.*?)(&|\z)/', $referrer,$matches);
		$keywords = $matches[1];
		$keywords = urldecode($keywords);
		$keywords = str_replace('+',' ',$keywords);

		//get search engine domain
		$parsed = parse_url($referrer);
		$domain = $parsed['host'];

		return array($keywords,$domain);

	}

	return false;
}