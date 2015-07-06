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


add_action('lp_record_impression', 'lp_ab_testing_record_impression', 10, 3);
function lp_ab_testing_record_impression($post_id, $post_type = 'landing-page', $variation_id = 0) {

    /* If Landing Page Post Type */
    if ($post_type == 'landing-page') {
        $meta_key = 'lp-ab-variation-impressions-' . $variation_id;
    } /* If Non Landing Page Post Type */ else {
        $meta_key = '_inbound_impressions_count';
    }

    $impressions = get_post_meta($post_id, $meta_key, true);

    if (!is_numeric($impressions)) {
        $impressions = 1;
    } else {
        $impressions++;
    }

    update_post_meta($post_id, $meta_key, $impressions);
}