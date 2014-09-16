<?php
add_action('wp_footer', 'lp_click_callback');

function lp_click_callback() {
	global $post;
	if (!isset($post))
		return;
	$id = $post->ID;
	if(get_post_type( $id ) == 'landing-page'){
	$variation = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : 0;
		// Footer script for link rewrites ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
		var lead_cpt_id = jQuery.cookie("wp_lead_id");
	    var lead_email = jQuery.cookie("wp_lead_email");
	    var lead_unique_key = jQuery.cookie("wp_lead_uid");

		if (typeof (lead_cpt_id) != "undefined" && lead_cpt_id !== null) {
        string = "&wpl_id=" + lead_cpt_id + "&l_type=wplid";
	    } else if (typeof (lead_email) != "undefined" && lead_email !== null && lead_email !== "") {
	        string = "&wpl_id=" + lead_email + "&l_type=wplemail";;
	    } else if (typeof (lead_unique_key) != "undefined" && lead_unique_key !== null && lead_unique_key !== "") {
	        string = "&wpl_id=" + lead_unique_key + "&l_type=wpluid";;
	    } else {
	        string = "";
	    }
		var external = RegExp('^((f|ht)tps?:)?//(?!' + location.host + ')');
		jQuery('.link-click-tracking a, .inbound-special-class').not("#wpadminbar a").each(function () {
			jQuery(this).attr("data-event-id", '<?php echo $id; ?>').attr("data-cta-varation", '<?php echo $variation;?>');
		        var orignalurl = jQuery(this).attr("href");
		        //jQuery("a[href*='http://']:not([href*='"+window.location.hostname+"'])"); // rewrite external links
		        var link_is = external.test(orignalurl);
		        if (link_is === true) {
		            base_url = window.location.origin;
		        } else {
		            base_url = orignalurl;
		        }
		        var cta_variation = "&wp-cta-v=" + jQuery(this).attr("data-cta-varation");
		        var this_id = jQuery(this).attr("data-event-id");
		        var newurl = base_url + "?lp_redirect_" + this_id + "=" + orignalurl + cta_variation + string;
		        jQuery(this).attr("href", newurl);
		    });
		});
		</script>
<?php }
}

// Register CTA Clicks
add_action('init', 'lp_click_track_redirect', 11); // Click Tracking init'
function lp_click_track_redirect() {
	global $wpdb;
	if ($qs = $_SERVER['REQUEST_URI']) {
		parse_str($qs, $output);
		(isset($output['l_type'])) ? $type = $output['l_type'] : $type = "";
		(isset($output['wpl_id'])) ? $lead_id = $output['wpl_id'] : $lead_id = "";
		(isset($output['wp-cta-v'])) ? $cta_variation = $output['wp-cta-v'] : $cta_variation = null;
		$pos = strpos($qs, 'lp_redirect');
		if (!(false === $pos)) {
			$link = substr($qs, $pos);
			$link = str_replace('lp_redirect=', '', $link); // clean url

			// Extract the ID and get the link
			$pattern = '/lp_redirect_(\d+?)\=/';
			preg_match($pattern, $link, $matches);
			$link = preg_replace($pattern, '', $link);
			$landing_page_id = $matches[1]; // Event ID
			$lead_ID = false;
			$append = true;
			// If lead post id exists
			if ($type === 'wplid') {
				$lead_ID = $lead_id;
			}
			// If lead email exists
			elseif ($type === 'wplemail') {
				$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
				$lead_id
				);
				$wpdb->query( $query );
				if ( $wpdb->num_rows ) {
					$lead_ID = $wpdb->get_var( $query );
				}
			}
			// If lead wp_uid exists
			elseif ($type === 'wpluid') {
				$query = $wpdb->prepare(
				'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta
				WHERE meta_value = %s',
				$lead_id
				);
				$wpdb->query( $query );
				if ( $wpdb->num_rows ) {
					$lead_ID = $wpdb->get_var( $query );
				} else {
					$lead_ID = $lead_id;
					$append = false;
				}
			}

			// Save click!
			lp_store_click_data( $landing_page_id, $lead_ID, $cta_variation); // Store CTA data to CTA CPT

			if( $lead_ID && $append != false ) {
			/* Add landing page click to lead profile */
			lp_store_click_data_to_lead($landing_page_id, $lead_ID, $cta_variation);
			}
			$link = preg_replace('/(?<=wpl_id)(.*)(?=&)/s', '', $link); // clean url
			$link = preg_replace('/&wpl_id&l_type=(\D*)/', '', $link); // clean url2
			$link = preg_replace('/&wp-cta-v=(\d*)/', '', $link); // clean url3
			// Redirect
			header("HTTP/1.1 302 Temporary Redirect");
			header("Location:" . $link);
			// I'm outta here!
			exit(1);
		}
	}
}

function lp_store_click_data($landing_page_id, $lead_ID, $cta_variation){
	// If leads_triggered meta exists do this
	$event_trigger_log = get_post_meta($landing_page_id,'leads_triggered',true);
	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$wordpress_date_time = date("Y-m-d G:i:s T", $time);
	$conversion_count = get_post_meta($landing_page_id,'lp-ab-variation-conversions-'.$cta_variation ,true);
	$conversion_count++;
	update_post_meta($landing_page_id, 'lp-ab-variation-conversions-'.$cta_variation, $conversion_count);
	/*	Update for log tracking
	if ($event_trigger_log) {
			$event_trigger_log = json_decode($event_trigger_log,true);
			// increment trigger count
			if(isset($event_trigger_log[$lead_ID])){
				$current_count = $event_trigger_log[$lead_ID]['count'];
			} else {
				$current_count = 0;
			}
			$event_trigger_log[$lead_ID]['count'] = $current_count + 1;
			$event_trigger_log[$lead_ID]['datetime'] = $wordpress_date_time;
			$count = count($event_trigger_log[$lead_ID]['date']) + 1;
			$event_trigger_log[$lead_ID]['date'][$count] = $wordpress_date_time;
			$event_trigger_log = json_encode($event_trigger_log);
			update_post_meta($landing_page_id, 'leads_triggered', $event_trigger_log);
		} else {
			// Create leads_triggered meta
			$event_trigger_log = array();
			$event_trigger_log[$lead_ID]['count'] = 1;
			$event_trigger_log[$lead_ID]['datetime'] = $wordpress_date_time;
			$event_trigger_log[$lead_ID]['date'][1] = $wordpress_date_time;
			$event_trigger_log = json_encode($event_trigger_log);
			update_post_meta($landing_page_id, 'leads_triggered', $event_trigger_log);
		}
	*/
		update_post_meta($landing_page_id, 'lp_last_triggered', $wordpress_date_time ); // update last fired date
}

// Store Event Trigger to Lead profile
function lp_store_click_data_to_lead($landing_page_id, $lead_ID, $lp_variation) {

	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$wordpress_date_time = date("Y-m-d G:i:s T", $time);

	if ( $lead_ID ) {
		$conversion_data = get_post_meta( $lead_ID, 'wpleads_conversion_data', TRUE );
		$individual_event_count = get_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, TRUE );
		$individual_event_count = ($individual_event_count != "") ? $individual_event_count : 0;
		$individual_event_count++;
		$meta = get_post_meta( $lead_ID, 'times', TRUE ); // replace times
		$meta++;
		$conversions_count = get_post_meta($lead_ID,'wpl-lead-conversion-count', true);
		$conversions_count++;
		if ($conversion_data) {

				$conversion_data = json_decode($conversion_data,true);
				$conversion_data[$meta]['id'] = $landing_page_id;
				$conversion_data[$meta]['variation'] = $lp_variation;
				$conversion_data[$meta]['datetime'] = $wordpress_date_time;
				$conversion_data = json_encode($conversion_data);
				update_post_meta( $lead_ID, 'wpleads_conversion_data', $conversion_data );
				update_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, $individual_event_count );
		//	update_post_meta( $lead_ID, 'lt_event_tracked_'.$landing_page_id, $individual_event_count );
		} else {
			$conversion_data[1]['id'] = $landing_page_id;
			$conversion_data[1]['variation'] = $lp_variation;
			$conversion_data[1]['datetime'] = $wordpress_date_time;
			$conversion_data[1]['first_time'] = 1;
			// Add in exact link url clicked
			$conversion_data = json_encode($conversion_data);
			update_post_meta( $lead_ID, 'wpleads_conversion_data', $conversion_data );
			update_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, $individual_event_count );
		//	update_post_meta( $lead_ID, 'lt_event_tracked_'.$landing_page_id, $individual_event_count );
		}
		update_post_meta( $lead_ID, 'times', $meta );
		update_post_meta( $lead_ID, 'wpl-lead-conversion-count', $meta );
		// Need to call conversion paths too
	}
}

?>