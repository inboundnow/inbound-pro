<?php
 
// Tracking impressions and conversions
add_action('inboundnow_store_lead_pre_filter_data','lp_set_conversion',10,1);
function lp_set_conversion($data) {

    if (!isset( $data['page_id'] ) ) {
        return;
    }

	$post = get_post( $data['page_id'] );
	if ($post) {
		$data['post_type'] = $post->post_type;
	}

	$do_not_track = apply_filters('inbound_analytics_stop_track' , false );

	if ( $do_not_track ) {
		return;
	}

	/* increment content conversion count */
	if( isset($data['post_type']) && $data['post_type'] === 'landing-page' ) {

		$lp_conversions = get_post_meta( $data['page_id'], 'lp-ab-variation-conversions-'.$data['variation'], true );
		$lp_conversions++;
		update_post_meta(  $data['page_id'], 'lp-ab-variation-conversions-'.$data['variation'], $lp_conversions );


	} else  {
		$conversions = get_post_meta( $data['page_id'], '_inbound_conversions_count', true );
		$conversions++;
		update_post_meta(  $data['page_id'], '_inbound_conversions_count', $conversions );
	}

	return $data;
}

function lp_get_page_views($postID) {
    $count_key = 'lp_page_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return;
   }
   return $count;
}

?>
