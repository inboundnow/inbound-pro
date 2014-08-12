<?php

add_action('wp_footer', 'wpcta_click_callback');

function wpcta_click_callback()
{
	global $post;
	if (!isset($post))
		return;
	$id = $post->ID;
	if(get_post_type( $id ) == 'wp-call-to-action'){
	$variation = (isset($_GET['wp-cta-variation-id'])) ? $_GET['wp-cta-variation-id'] : 0;

	// Footer script for link rewrites ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
		var lead_cpt_id = jQuery.cookie("wp_lead_id");
	    var lead_email = jQuery.cookie("wp_lead_email");
	    var lead_unique_key = jQuery.cookie("wp_lead_uid");

	    // turn off link rewrites for custom ajax triggers
	    if (typeof (wp_cta_settings) != "undefined" && wp_cta_settings !== null) {
	        return false;
	    }
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
		jQuery('a').not("#wpadminbar a").each(function () {
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
		        var newurl = base_url + "?wp_cta_redirect_" + this_id + "=" + orignalurl + cta_variation + string;
		        jQuery(this).attr("href", newurl);
		    });
		});
		</script>
<?php }
}

// Register CTA Clicks
add_action('init', 'wp_cta_click_track_redirect', 11); // Click Tracking init
function wp_cta_click_track_redirect()
{
	global $wpdb;
	if ($qs = $_SERVER['REQUEST_URI']) {
		parse_str($qs, $output);
		(isset($output['l_type'])) ? $type = $output['l_type'] : $type = "";
		(isset($output['wpl_id'])) ? $lead_id = $output['wpl_id'] : $lead_id = "";
		(isset($output['wp-cta-v'])) ? $cta_variation = $output['wp-cta-v'] : $cta_variation = null;
		$pos = strpos($qs, 'wp_cta_redirect');
		if (!(false === $pos)) {
			$link = substr($qs, $pos);
			$link = str_replace('wp_cta_redirect=', '', $link); // clean url

			// Extract the ID and get the link
			$pattern = '/wp_cta_redirect_(\d+?)\=/';
			preg_match($pattern, $link, $matches);
			$link = preg_replace($pattern, '', $link);
			$event_id = $matches[1]; // Event ID

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
				}
			}

			// Save click!
			wp_cta_store_click_data( $event_id, $lead_ID, $cta_variation); // Store CTA data to CTA CPT

			/* Add event to lead profile */
			wp_cta_store_click_data_to_lead($event_id, $lead_ID, 'clicked-link');

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

function wp_cta_store_click_data($event_id, $lead_ID, $cta_variation)
{
	// If leads_triggered meta exists do this
	$event_trigger_log = get_post_meta($event_id,'leads_triggered',true);
	$timezone_format = 'Y-m-d G:i:s T';
	$wordpress_date_time =  date_i18n($timezone_format);
	$conversion_count = get_post_meta($event_id,'wp-cta-ab-variation-conversions-'.$cta_variation ,true);
	$conversion_count++;
	update_post_meta($event_id, 'wp-cta-ab-variation-conversions-'.$cta_variation, $conversion_count);
	/**
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
			update_post_meta($event_id, 'leads_triggered', $event_trigger_log);
		} else {
			// Create leads_triggered meta
			$event_trigger_log = array();
			$event_trigger_log[$lead_ID]['count'] = 1;
			$event_trigger_log[$lead_ID]['datetime'] = $wordpress_date_time;
			$event_trigger_log[$lead_ID]['date'][1] = $wordpress_date_time;
			$event_trigger_log = json_encode($event_trigger_log);
			update_post_meta($event_id, 'leads_triggered', $event_trigger_log);
		}

		// Update Trigger Count
		if(get_post_custom_keys($event_id)&&in_array('wp_cta_trigger_count',get_post_custom_keys($event_id))){
			$wp_cta_trigger_count = get_post_meta($event_id,'wp_cta_trigger_count',true);
		}
		if (!isset($wp_cta_trigger_count)){
			$wp_cta_trigger_count = 0;
		}
		$wp_cta_trigger_count++;
		update_post_meta($event_id, 'wp_cta_trigger_count', $wp_cta_trigger_count); // update trigger count
		*/
		update_post_meta($event_id, 'wp_cta_last_triggered', $wordpress_date_time ); // update last fired date
}

// Store Event Trigger to Lead profile
function wp_cta_store_click_data_to_lead($event_id, $lead_ID, $event_type)
{
	$timezone_format = 'Y-m-d G:i:s T';
	$wordpress_date_time =  date_i18n($timezone_format);

	if ( $lead_ID ) {
		$event_data = get_post_meta( $lead_ID, 'call_to_action_clicks', TRUE );
		$event_count = get_post_meta( $lead_ID, 'wp_cta_trigger_count', TRUE );
		$event_count++;
		$individual_event_count = get_post_meta( $lead_ID, 'lt_event_tracked_'.$event_id, TRUE );
		$individual_event_count = ($individual_event_count != "") ? $individual_event_count : 0;
		$individual_event_count++;

		if ($event_data) {
			$event_data = json_decode($event_data,true);
			$event_data[$event_count]['id'] = $event_id;
			$event_data[$event_count]['datetime'] = $wordpress_date_time;
			$event_data[$event_count]['type'] = $event_type;
			$event_data = json_encode($event_data);
			update_post_meta( $lead_ID, 'call_to_action_clicks', $event_data );
			update_post_meta( $lead_ID, 'wp_cta_trigger_count', $event_count );
		//	update_post_meta( $lead_ID, 'lt_event_tracked_'.$event_id, $individual_event_count );
		} else {
			$event_data[1]['id'] = $event_id;
			$event_data[1]['datetime'] = $wordpress_date_time;
			$event_data[1]['type'] = $event_type;
			$event_data = json_encode($event_data);
			update_post_meta( $lead_ID, 'call_to_action_clicks', $event_data );
			update_post_meta( $lead_ID, 'wp_cta_trigger_count', 1 );
		//	update_post_meta( $lead_ID, 'lt_event_tracked_'.$event_id, $individual_event_count );
		}
	}
}
