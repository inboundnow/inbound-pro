<?php

/* REMOVE DEFAULT METABOXES */
add_filter('default_hidden_meta_boxes', 'wplead_hide_metaboxes', 10, 2);
function wplead_hide_metaboxes($hidden, $screen) {

	global $post;
	if ( isset($post) && $post->post_type == 'wp-lead' ) {
		//print_r($hidden);exit;
		$hidden = array(
			'postexcerpt',
			'slugdiv',
			'postcustom',
			'trackbacksdiv',
			'lead-timelinestatusdiv',
			'lead-timelinesdiv',
			'authordiv',
			'revisionsdiv',
			'wpseo_meta',
			'wp-advertisement-dropper-post',
			'postdivrich'
		);

	}
	return $hidden;
}

/* REMOVE WYSIWYG */
add_filter( 'user_can_richedit', 'wplead_disable_for_cpt' );
function wplead_disable_for_cpt( $default ) {
    global $post;
    if ( isset ($post) && $post->post_type == 'wp-lead' ) {
      // echo 1; exit;
	   return false;
	}
    return $default;
}



function wp_leads_get_search_keywords($url = '') {
	// Get the referrer
	//$referrer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';

	// Parse the referrer URL

  	$parsed_url = parse_url($url);
  	$host = $parsed_url['host']; // base url
    $se_match = array("google", "yahoo", "bing");

		foreach($se_match as $val) {
		  if (preg_match("/" . $val . "/", $url)){
		  	$is_search_engine = stripslashes($bl);
		  }
		}

	$query_str = (!empty($parsed_url['query'])) ? $parsed_url['query'] : '';
	$query_str = (empty($query_str) && !empty($parsed_url['fragment'])) ? $parsed_url['fragment'] : $query_str;

	// Parse the query string into a query array
	parse_str($query_str, $query);
	$empty_keywords = __( 'Empty' , 'leads' ) . $is_search_engine;
	// Check some major search engines to get the correct query var
	$search_engines = array(
		'q' => 'alltheweb|aol|ask|ask|bing|google',
		'p' => 'yahoo',
		'wd' => 'baidu'
	);
	foreach ($search_engines as $query_var => $se)
	{
		$se = trim($se);
		preg_match('/(' . $se . ')\./', $host, $matches);
		if (!empty($matches[1]) && !empty($query[$query_var])) {
			return "From". $is_search_engine ." ". $query[$query_var];
		} else {
			return "From". $is_search_engine ." ". $empty_keywords;
		}
	}
	// return false;
}
//echo wp_leads_get_search_keywords('http://www.google.co.th/url?sa=t&rct=j&q=keywordsssss&esrc=s&source=web&cd=4&ved=0CE8QFjAD&url=http%3A%2F%2Fwww.inboundnow.com%2Fhow-to-properly-set-up-wordpress-301-redirects%2F&ei=FMHDUZPqBMztiAfi_YCoBA&usg=AFQjCNFuh3aH04u2Z4xXl2XNb3emE95p5Q&sig2=yrdyyZz83KfGte6SNZL7gA&bvm=bv.48293060,d.aGc');

/* Add quick stats box */
add_action('add_meta_boxes', 'wplead_display_quick_stat_metabox');
function wplead_display_quick_stat_metabox() {
	global $post;
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	add_meta_box(
	'wplead-quick-stats-metabox',
	__( "Lead Stats", 'leads' ),
	'wplead_quick_stats_metabox',
	'wp-lead' ,
	'side',
	'high' );
}

function leads_time_diff($date1, $date2) {
	$time_diff = array();
	$diff = abs(strtotime($date2) - strtotime($date1));
	$years = floor($diff / (365*60*60*24));
	$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
	$minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60) / 60);
	$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60));

	$time_diff['years'] = $years;
	$time_diff['y-text'] = ($years > 1) ? __('Years' , 'leads') : __('Year' , 'leads');
	$time_diff['months'] = $months;
	$time_diff['m-text'] = ($months > 1) ? __('Months' , 'leads') : __('Month' , 'leads');
	$time_diff['days'] = $days;
	$time_diff['d-text'] = ($days > 1) ? __('Days' , 'leads') : __('Day' , 'leads');
	$time_diff['hours'] = $hours;
	$time_diff['h-text'] = ($hours > 1) ? __('Hours' , 'leads') : __('Hour' , 'leads');
	$time_diff['minutes'] = $minutes;
	$time_diff['mm-text'] = ($minutes > 1) ? __('Minutes' , 'leads') : __('Minute' , 'leads');
	$time_diff['seconds'] = $seconds;
	$time_diff['sec-text'] = ($seconds > 1) ? __('Seconds' , 'leads') : __('Second' , 'leads');

	return $time_diff;
}

function wplead_quick_stats_metabox() {
	global $post;
	global $wpdb;

	$form_data = get_post_meta($post->ID,'FormData', true);

	$last_conversion = get_post_meta($post->ID,'wpleads_conversion_data', true);
	$last_conversion = json_decode($last_conversion, true);


	(is_array($last_conversion)) ? $count_conversions = count($last_conversion) : $count_conversions = get_post_meta($post->ID,'wpleads_conversion_count', true);

	$the_date = $last_conversion[$count_conversions]['datetime']; // actual

	$email = get_post_meta( $post->ID , 'wpleads_email_address', true );
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );

	$page_views = get_post_meta($post->ID,'page_views', true);
    $page_view_array = json_decode($page_views, true);

    $main_count = 0;
    $page_view_count = 0;

	if (is_array($page_view_array)) {
		foreach($page_view_array as $key=>$val) {
			$page_view_count += count($page_view_array[$key]);
		}
		update_post_meta($post->ID,'wpleads_page_view_count', $page_view_count);
	} else {
		$page_view_count = get_post_meta($post->ID,'wpleads_page_view_count', true);
	}

	?>
	<div>
		<div class="inside" style='margin-left:-8px;text-align:center;'>
			<div id="quick-stats-box">

			<?php do_action('wpleads_before_quickstats', $post);?>
			<div id="page_view_total"><?php _e('Total Page Views ' , 'leads' );?><span id="p-view-total"><?php echo $page_view_count; ?></span>
			</div>

			<div id="conversion_count_total"><?php _e( '# of Conversions ' , 'leads' ); ?><span id="conversion-total"><?php echo $count_conversions; ?></span>
			</div>

		<?php
		if (!empty($the_date)) {
			$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
			$wordpress_date_time = date("Y-m-d G:i:s", $time);

			$today = new DateTime($wordpress_date_time);
			$today = $today->format('Y-m-d G:i:s');
			$date_obj = leads_time_diff($the_date, $today);
			$wordpress_timezone = get_option('gmt_offset');
			$years = $date_obj['years'];
			$months = $date_obj['months'];
			$days = $date_obj['days'];
			$hours = $date_obj['hours'];
			$minutes = $date_obj['minutes'];
			$year_text = $date_obj['y-text'];
			$month_text = $date_obj['m-text'];
			$day_text = $date_obj['d-text'];
			$hours_text = $date_obj['h-text'];
			$minute_text = $date_obj['mm-text'];

			?>
				<div id="last_touch_point"><?php _e('Time Since Last Conversion' , 'leads');?>

					<span id="touch-point">

						<?php

						echo "<span class='touchpoint-year'><span class='touchpoint-value'>" . $years . "</span> ".$year_text." </span><span class='touchpoint-month'><span class='touchpoint-value'>" . $months."</span> ".$month_text." </span><span class='touchpoint-day'><span class='touchpoint-value'>".$days."</span> ".$day_text." </span><span class='touchpoint-hour'><span class='touchpoint-value'>".$hours."</span> ".$hours_text." </span><span class='touchpoint-minute'><span class='touchpoint-value'>".$minutes."</span> ".$minute_text."</span>";
						?>
					</span>
				</div>
			<?php
		}
		?>
			<div id="time-since-last-visit"></div>
			<div id="lead-score"></div><!-- Custom Before Quick stats and After Hook here for custom fields shown -->
			</div>
				<?php do_action('wpleads_after_quickstats'); // Custom Action for additional data after quick stats ?>
		</div>
	</div>
	<?php
}


/* ADD IP ADDRESS METABOX TO SIDEBAR */
add_action('add_meta_boxes', 'wplead_display_ip_address_metabox');
function wplead_display_ip_address_metabox() {
	global $post;

	add_meta_box(
	'lp-ip-address-sidebar-preview',
	__( 'Last Conversion Activity Location', 'leads' ),
	'wplead_ip_address_metabox',
	'wp-lead' ,
	'side',
	'low' );
}

function wplead_ip_address_metabox() {
	global $post;

	$ip_address = get_post_meta( $post->ID , 'wpleads_ip_address', true );
	$geo_result = wp_remote_get('http://www.geoplugin.net/php.gp?ip='.$ip_address);
	$geo_result_body = $geo_result['body'];
	$geo_array = unserialize($geo_result_body);
	$city = get_post_meta($post->ID, 'wpleads_city', true);
	$state = get_post_meta($post->ID, 'wpleads_region_name', true);
	$latitude = $geo_array['geoplugin_latitude'];
	$longitude = $geo_array['geoplugin_longitude'];

	?>
	<div >
		<div class="inside" style='margin-left:-8px;text-align:left;'>
			<div id='last-conversion-box'>
				<div id='lead-geo-data-area'>

				<?php
				if (is_array($geo_array)) {
					unset($geo_array['geoplugin_status']);
					unset($geo_array['geoplugin_credit']);
					unset($geo_array['geoplugin_request']);
					unset($geo_array['geoplugin_currencyConverter']);
					unset($geo_array['geoplugin_currencySymbol_UTF8']);
					unset($geo_array['geoplugin_currencySymbol']);
					unset($geo_array['geoplugin_dmaCode']);
					if (isset($geo_array['geoplugin_city']) && $geo_array['geoplugin_city'] != ""){
					echo "<div class='lead-geo-field'><span class='geo-label'>".__('City:' , 'leads')."</span>" . $geo_array['geoplugin_city'] . "</div>"; }
					if (isset($geo_array['geoplugin_regionName']) && $geo_array['geoplugin_regionName'] != ""){
					echo "<div class='lead-geo-field'><span class='geo-label'>".__('State:' , 'leads')."</span>" . $geo_array['geoplugin_regionName'] . "</div>";
					}
					if (isset($geo_array['geoplugin_areaCode']) && $geo_array['geoplugin_areaCode'] != ""){
					echo "<div class='lead-geo-field'><span class='geo-label'>".__('Area Code:' , 'leads')."</span>" . $geo_array['geoplugin_areaCode'] . "</div>";
					}
					if (isset($geo_array['geoplugin_countryName']) && $geo_array['geoplugin_countryName'] != ""){
					echo "<div class='lead-geo-field'><span class='geo-label'>".__('Country:' , 'leads')."</span>" . $geo_array['geoplugin_countryName'] . "</div>";
					}
					if (isset($geo_array['geoplugin_regionName']) && $geo_array['geoplugin_regionName'] != ""){
					echo "<div class='lead-geo-field'><span class='geo-label'>".__('IP Address:' , 'leads')."</span>" . $ip_address . "</div>";
					}
					/*
					foreach ($geo_array as $key=>$val)
					{
						$key = str_replace('geoplugin_','',$key);
						echo "<tr class='lp-geo-data'>";
						echo "<td class='lp-geo-key'><em><small>$key</small></em></td>";
						echo "<td class='lp-geo-val'><em><small>$val</small></em></td>";
						echo "</tr>";
					} */
				}
				if (($latitude != 0) && ($longitude != 0)) {
					echo '<a class="maps-link" href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$latitude.','.$longitude.'&z=12" target="_blank">'.__('View Map:' , 'leads').'</a>';
					echo '<div id="lead-google-map">
							<iframe width="278" height="276" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q='.$latitude.','.$longitude.'&amp;aq=&amp;output=embed&amp;z=11"></iframe>
							</div>';
				} else {
					echo "<h2>".__('No Geo data collected' , 'leads')."</h2>";
				}
				?>
				</div>
			</div>
		</div>
	</div>
	<?php
}


/* Top Metabox */
add_action( 'edit_form_after_title', 'wp_leads_header_area' );
function wp_leads_header_area() {
   global $post;

	$first_name = get_post_meta( $post->ID , 'wpleads_first_name', true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	$lead_status = 'wp_lead_status';

    if ( empty ( $post ) || 'wp-lead' !== get_post_type( $GLOBALS['post'] ) ){
        return;
	}

    if ( ! $content = get_post_meta( $post->ID , 'wpleads_first_name',true ) ){
        $content = '';
	}

    if ( ! $status_content = get_post_meta( $post->ID, $lead_status, TRUE ) ){
        $status_content = '';
	}

    echo "<div id='lead-top-area'>";
		echo "<div id='lead-header'><h1>".$first_name.' '.$last_name. "</h1></div>";

		$values = get_post_custom( $post->ID );
		$selected = isset( $values['wp_lead_status'] ) ? esc_attr( $values['wp_lead_status'][0] ) : "";
		?>
		<!-- REWRITE FOR FILTERS -->
		<div id='lead-status'>
			<label for="wp_lead_status"><?php _e( 'Lead Status:' , 'leads' ); ?></label>
			<select name="wp_lead_status" id="wp_lead_status">
				<option value="Read" <?php selected( $selected, 'Read' ); ?>><?php _e( 'Read/Viewed' , 'leads' ); ?></option>
				<option value="New Lead" <?php selected( $selected, 'New Lead' ); ?>><?php _e( 'New Lead' , 'leads' ); ?></option>
				<option value="Contacted" <?php selected( $selected, 'Contacted' ); ?>><?php _e( 'Contacted' , 'leads' ); ?></option>
				<option value="Active" <?php selected( $selected, 'Active' ); ?>><?php _e( 'Active' , 'leads' ); ?></option>
				<option value="Lost" <?php selected( $selected, 'Lost' ); ?>><?php _e( 'Disqualified/Lost' , 'leads' ); ?></option>
				<option value="Customer" <?php selected( $selected, 'Customer' ); ?>><?php _e( 'Customer' , 'leads' ); ?></option>
				<option value="Archive" <?php selected( $selected, 'Archive' ); ?>><?php _e( 'Archive' , 'leads' ); ?></option>
				<!-- Action hook here for custom lead status addon -->
			</select>
		</div>
		<span id="current-lead-status" style="display:none;"><?php echo get_post_meta( $post->ID, $lead_status, TRUE );?></span>
	</div>
    <?php
}


add_action( 'save_post', 'wp_leads_save_header_area' );
function wp_leads_save_header_area( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $key = 'wp_lead_status';

    if (isset($_POST[$key])) {
        return update_post_meta( $post_id, $key, $_POST[ $key ] );
    }

    delete_post_meta( $post_id, $key );
}

function wp_leads_grab_extra_data() {

    // do not load on admin
    if (!is_admin() ) {
        return;
    }
    global $post;
    $email = get_post_meta($post->ID , 'wpleads_email_address', true );
    $api_key = get_option( 'wpl-main-extra-lead-data' , "");

    if($api_key === "" || empty($api_key)) {
    	echo "<div class='lead-notice'>Please <a href='".esc_url( admin_url( add_query_arg( array( 'post_type' => 'wp-lead', 'page' => 'wpleads_global_settings' ), 'edit.php' ) ) )."'>enter your Full Contact API key</a> for additional lead data. <a href='http://www.inboundnow.com/collecting-advanced-lead-intelligence-wordpress-free/' target='_blank'>Read more</a></div>" ;
    	return;
    }

    if ((isset($post->post_type)&&$post->post_type=='wp-lead') && !empty($email)) {

        $social_data = get_post_meta($post->ID , 'social_data', true );
        $person_obj = $social_data;
        // check for social data
        if (empty($social_data)) {

            $args = array('sslverify' => false
            );

            $api_call = "https://api.fullcontact.com/v2/person.json?email=".urlencode($email)."&apiKey=$api_key";

            $response = wp_remote_get($api_call, $args );

            // error. bail.
            if (is_wp_error($response ) ) {
                return;
            }

            $status_code = $response['response']['code']; // Check for API limit

            if ($status_code === 200) {
                // if api still good. parse return values
                $person_obj = json_decode($response['body'], true);
                $image = (isset($person_obj['photos'][0]['url'])) ?$person_obj['photos'][0]['url'] : "";
                update_post_meta($post->ID, 'lead_main_image', $image );
                update_post_meta($post->ID, 'social_data', $person_obj );

            } elseif ($status_code === 404) {
                $person_obj = array(); // return empty on failure
            } else {
                $person_obj = array(); // return empty on failure
            }

        }

        return $person_obj;
    }
}



function wp_lead_display_extra_data($values, $type) {

	$person_obj = $values;
	//print_r($person_obj);
	$confidence_level = (isset($person_obj['likelihood'])) ? $person_obj['likelihood'] : "";

	$photos = (isset($person_obj['photos'])) ? $person_obj['photos'] : "No Photos";
	$fullname = (isset($person_obj['contactInfo']['fullName'])) ? $person_obj['contactInfo']['fullName'] : "";
	$websites = (isset($person_obj['contactInfo']['websites'])) ? $person_obj['contactInfo']['websites'] : "N/A";
	$chats = (isset($person_obj['contactInfo']['chats'])) ? $person_obj['contactInfo']['chats'] : "No";
	$social_profiles = (isset($person_obj['socialProfiles'])) ? $person_obj['socialProfiles'] : "No Profiles Found";
	$organizations = (isset($person_obj['organizations'])) ? $person_obj['organizations'] : "No Organizations Found";
	$demographics = (isset($person_obj['demographics'])) ? $person_obj['demographics'] : "N/A";
	$interested_in = (isset($person_obj['digitalFootprint']['topics'])) ? $person_obj['digitalFootprint']['topics'] : "N/A";

	$image = (isset($person_obj['photos'][0]['url'])) ?$person_obj['photos'][0]['url'] : "/wp-content/plugins/leads/images/gravatar_default_150.jpg";
	$klout_score = (isset($person_obj['digitalFootprint']['scores'][0]['value'])) ? $person_obj['digitalFootprint']['scores'][0]['value'] : "N/A";

	//echo "<img src='" . $image . "'><br>";
	//echo "<h2>Extra social Data <span class='confidence-level'>".$confidence_level."</span></h2>";
	//echo $fullname;

	// Get All Photos associated with the person
	if($type === 'photo' && isset($photos) && is_array($photos)) {
		foreach($photos as $photo) {
			//print_r($photo);
			echo $photo['url'] . " from " . $photo['typeName'] . "<br>";
		}
	}

	// Get All Websites associated with the person
	elseif ($type === 'website' && isset($websites) && is_array($websites)) {
			echo "<div id='lead-websites'><h4>". __( 'Websites' , 'leads' ) ."</h4>";
			//print_r($websites);
			foreach($websites as $site)
			{
				echo "<a href='". $site['url'] . "' target='_blank'>".$site['url']."</a><br>";
			}
			echo "</div>";
	}
	// Get All Social Media Account associated with the person
	elseif ($type === 'social' && isset($social_profiles) && is_array($social_profiles)) {
			echo "<div id='lead-social-profiles'><h4>". __( 'Social Media Profiles' , 'leads' ) ."</h4>";
			//print_r($social_profiles);
			foreach($social_profiles as $profiles) {
				$network = (isset($profiles['typeName'])) ? $profiles['typeName'] : "";
				$username = (isset($profiles['username'])) ? $profiles['username'] : "";
				($network == 'Twitter' ) ? $echo_val = "@" . $username : $echo_val = "";
				echo "<a href='". $profiles['url'] . "' target='_blank'>".$profiles['typeName']."</a> ". $echo_val ."<br>";
			}
			echo "</div>";
	}
	// Get All Work Organizations associated with the person
	elseif ($type === 'work' && isset($organizations) && is_array($organizations)) {
		echo "<div id='lead-work-history'>";

		foreach($organizations as $org) {
			$title = (isset($org['title'])) ? $org['title'] : "";
			$org_name = (isset($org['name'])) ? $org['name'] : "";
			(isset($org['name'])) ? $at_org = "<span class='primary-work-org'>" . $org['name'] . "</span>" : $at_org = ""; // get primary org
			($org['isPrimary'] === true) ? $print = "<span id='primary-title'>" . $title ."</span> at " . $at_org : $print = "";
			($org['isPrimary'] === true) ? $hideclass = "work-primary" : $hideclass = "work-secondary";
			echo $print;
			echo "<span class='lead-work-label ".$hideclass."'>" . $title . " at ". $org_name ."</span>";
		}
		echo "<span id='show-work-history'>". __( 'View past work' , 'leads' ) ."</span></div>";
	}
	// Get All demo graphic info associated with the person
	elseif ($type === 'demographics' && isset($demographics) && is_array($demographics)) {
		echo "<div id='lead-demographics'><h4>". __( 'Demographics' ,'leads' ) ."</h4>";
		$location = (isset($demographics['locationGeneral'])) ? $demographics['locationGeneral'] : "";
		$age = (isset($demographics['age'])) ? $demographics['age'] : "";
		$ageRange = (isset($demographics['ageRange'])) ? $demographics['ageRange'] : "";
		$gender = (isset($demographics['gender'])) ? $demographics['gender'] : "";
		echo $gender . " in " . $location;
		echo "</div>";
	}
	// Get All Topics associated with the person
	elseif ($type === 'topics' && isset($interested_in) && is_array($interested_in)) {
		echo "<div id='lead-topics'><h4>". __( 'Interests' , 'leads' ) ."</h4>";
		foreach($interested_in as $topic) {
			echo "<span class='lead-topic-tag'>". $topic['value'] . "</span>";
		}
		echo "</div>";
	}

}
/* ADD MAIN METABOX */
//Add select template meta box
add_action('add_meta_boxes', 'wplead_add_metabox_main');
function wplead_add_metabox_main() {
	global $post;

	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );

	add_meta_box(
		'wplead_metabox_main', // $id
		__( 'Lead Overview', 'leads' ),
		'wpleads_display_metabox_main', // $callback
		'wp-lead', // $page
		'normal', // $context
		'high'); // $priority
}

// Render select template box
function wpleads_display_metabox_main() {
	//echo 1; exit;
	global $post;

	global $wpdb;

	//define tabs
	$tabs[] = array('id'=>'wpleads_lead_tab_main','label'=>'Lead Information');
	$tabs[] = array('id'=>'wpleads_lead_tab_conversions','label'=>'Activity');
	$tabs[] = array('id'=>'wpleads_lead_tab_raw_form_data','label'=>'Logs');

	$tabs = apply_filters('wpl_lead_tabs',$tabs);

	//define open tab
	$active_tab = 'wpleads_lead_tab_main';
	if (isset($_REQUEST['open-tab'])) {
		$active_tab = $_REQUEST['open-tab'];
	}


	//print jquery for tab switching
	wpl_manage_lead_js($tabs);

	$wpleads_user_fields = Leads_Field_Map::get_lead_fields();
	foreach ($wpleads_user_fields as $key=>$field) {
			$wpleads_user_fields[$key]['value'] = get_post_meta( $post->ID , $wpleads_user_fields[$key]['key'] ,true );
			if ( !$wpleads_user_fields[$key]['value'] && isset($wpleads_user_fields[$key]['default']) )
				$wpleads_user_fields[$key]['value'] = $wpleads_user_fields[$key]['default'];
	}

	// Use nonce for verification
	echo "<input type='hidden' name='wplead_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";
	echo "<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";
	?>
	<div class="metabox-holder split-test-ui">
		<div class="meta-box-sortables ui-sortable">
		<h2 id="lp-st-tabs" class="nav-tab-wrapper">
			<?php
			foreach ($tabs as $key=>$array) { ?>
				<a id='tabs-<?php echo $array['id']; ?>' class="wpl-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $array['id'] ? '-active' : '-inactive'; ?>"><?php echo $array['label']; ?></a>
				<?php
			}
			?>
		</h2>
		<div class="wpl-tab-display" id='wpleads_lead_tab_main'>
			<div id="wpleads_lead_tab_main_inner">
			<div id='toggle-lead-fields'><a class="preview button" href="#" id="show-hidden-fields"><?php _e( 'Show Hidden/Empty Fields' , 'leads' ); ?></a></div>
			<?php

			$social_values = wp_leads_grab_extra_data(); // Get extra data on lead
			$email = get_post_meta( $post->ID , 'wpleads_email_address', true );
			$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
			$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
			$extra_image = get_post_meta( $post->ID , 'lead_main_image', true );
			$size = 150;
			$size_small = 36;
			$url = site_url();
			$default = WPL_URLPATH . '/images/gravatar_default_150.jpg';

			$gravatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
			$gravatar2 = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size_small;

			if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
			    $gravatar = $default;
			   	$gravatar2 = WPL_URLPATH . '/images/gravatar_default_32-2x.png';
			}
			// If social picture exists use it
			if(preg_match("/gravatar_default_/", $gravatar) && $extra_image != ""){
				$gravatar = $extra_image;
				$gravatar2 = $extra_image;
			}
			?>
			<div id="lead_image">
				<div id="lead_image_container">
				<?php if ($first_name != "" && $last_name != ""){ ?>
				<div id="lead_name_overlay"><?php echo $first_name . " " . $last_name;?>
				</div>
				<?php } ?>
					<?php
						if(preg_match("/gravatar_default_/", $gravatar) && $extra_image != ""){
							$gravatar = $extra_image;
						}
						echo'<img src="'.$gravatar.'" width="150" id="lead-main-image" title="'.$first_name.' '.$last_name.'"></a>';
						wp_lead_display_extra_data($social_values, 'work'); // Display extra data work history
						wp_lead_display_extra_data($social_values, 'social'); // Display extra social
					?>
				</div>
				<?php
				/* Display WP USer edit link */
				$wp_user_id = get_post_meta( $post->ID , 'wpleads_wordpress_user_id' , true );
				if ( isset($wp_user_id) && ($wp_user_id != 1) ) {
					$edit_user_link = get_edit_user_link( $wp_user_id );
					echo '<a  target="_blank" href="'.$edit_user_link.'">'. __( 'Edit User Profile' , 'leads' ) .'</a>';
				}
				?>
			</div>
			<style type="text/css">.icon32-posts-wp-lead {background-image: url("<?php echo $gravatar2;?>") !important;}</style>
			<div id="leads-right-col">
			<?php
			//print_r($wpleads_user_fields);exit;
			do_action('wpleads_before_main_fields'); // Custom Action for additional info above Lead list

			wpleads_render_setting($wpleads_user_fields);

			wp_lead_display_extra_data($social_values, 'website'); // Display websites

			wp_lead_display_extra_data($social_values, 'demographics'); // Display demographics

			wp_lead_display_extra_data($social_values, 'topics'); // Display extra topics

			$tags = wpl_tag_cloud(); // get content tags
				if (!empty($tags)){
				echo '<div id="lead-tag-cloud"><h4>'. __( 'Tag cloud of content consumed' , 'leads' ) .'</h4>';
				foreach ($tags as $key => $value) {
					echo "<a href='#' rel='$value'>$key</a>";
				}
				echo "</div>";
			}

			//wp_lead_display_extra_data($values, 'photo'); // Display extra photos
			echo "<div id='wpl-after-main-fields'>";


			do_action('wpleads_after_main_fields'); // Custom Action for additional info above Lead list
			echo "</div>";

			?>
			</div><!-- end #leads-right-col div-->

		</div><!-- end wpleads_metabox_main_inner -->
		</div><!-- end wpleads_metabox_main AKA Tab 1-->
		<div class="wpl-tab-display" id="wpleads_lead_tab_conversions" style="display: <?php if ($active_tab == 'wpleads_lead_tab_conversions') { echo 'block;'; } else { echo 'none;'; } ?>">
			<div id="conversions-data-display">
				<?php $conversions = get_post_meta($post->ID,'wpleads_conversion_data', true);
					   $conversions_array = json_decode($conversions, true);
					   $conversion_count = count($conversions_array);
					   /* Comments Count */
			   			$wpleads_search_data = get_post_meta($post->ID,'wpleads_search_data', true);
			   			$wpleads_search_data = json_decode($wpleads_search_data, true);
		    			if (is_array($wpleads_search_data)){
		    				$search_count = count($wpleads_search_data);

		    			} else {
		    				$search_count = 0;
		    			}

		    			$page_view_badge = get_post_meta($post->ID,'wpleads_page_view_count', true);

					   /* Comments Count */
					   $comments_query = new WP_Comment_Query;
					   $comments_array = $comments_query->query( array('author_email' => $email) );
					   $conversions = get_post_meta($post->ID,'wpleads_conversion_data', true);
					   $conversions_array = json_decode($conversions, true);

					   if ($comments_array) {
					   		$comment_count = count($comments_array);
					   } else {
					   		$comment_count = 0;
					   } ?>
				<?php //define activity toggles. Filterable
					$nav_items = array(
								     array('id'=>'lead-conversions',
								     	   'label'=> __( 'Conversions' , 'leads' ),
								     	   'count' => $conversion_count ),
								     array('id'=>'lead-page-views',
								     	   'label'=> __( 'Page Views' , 'leads' ),
								     	   'count' => $page_view_badge ),
								     array('id'=>'lead-comments',
								     	   'label'=> __( 'Comments' , 'leads' ),
								     	   'count' => $comment_count ),
								     array('id'=>'lead-searches',
								     	   'label'=> __( 'Searches' , 'leads' ),
								     	   'count' => $search_count )
								     );

					$nav_items = apply_filters('wpl_lead_activity_tabs',$nav_items); ?>

			<div class="nav-container">
				<nav>
			      <ul id="lead-activity-toggles">
			        <li class="active"><a href="#all" class="lead-activity-show-all"><?php _e( 'All' , 'leads' ); ?></a></li>
			        <?php
			        	// Print toggles
						foreach ($nav_items as $key=>$array) {
							$count = (isset($array['count'])) ? $array['count'] : '0';
							?>
							<li><a href='#<?php echo $array['id']; ?>' class="lead-activity-toggle"><?php echo $array['label']; ?><span class="badge"><?php echo $count;?></span></a></li>
							<?php
						}
					?>
			      </ul>
			    </nav>
			</div>
			<ul class="event-order-list" data-change-sort='#all-lead-history'>
			Sort by:
		    <li id="newest-event" class='lead-sort-active'><?php _e( 'Most Recent' , 'leads' ); ?></li> |
		    <li id="oldest-event"><?php _e( 'Oldest' , 'leads' ); ?></li>
			   <!-- <li id="highest">Highest Rated</li>
			    <li id="lowest">Lowest Rated</li> -->
			</ul>
			<div id="all-lead-history"><ol></ol></div>
			<div id="lead-conversions" class='lead-activity'>
				<h2><?php _e('Landing Page Conversions' , 'leads' ); ?></h2>

			<?php


            // Sort Array by date
			function leads_sort_array_datetime($a,$b){
			        return strtotime($a['datetime'])<strtotime($b['datetime'])?1:-1;
			};

			if (is_array($conversions_array)) {
				uasort($conversions_array,'leads_sort_array_datetime'); // Date sort
				$conversion_count = count($conversions_array);

 				$i = $conversion_count;
				foreach ($conversions_array as $key => $value) {
					//print_r($value);

						$converted_page_id  = $value['id'];
						$converted_page_permalink   = get_permalink($converted_page_id);
						$converted_page_title = get_the_title($converted_page_id);

						if (array_key_exists('datetime', $value)) {
							$converted_page_time = $value['datetime'];
						} else {
							$converted_page_time = $wordpress_date_time;
						}

						$conversion_date_raw = new DateTime($converted_page_time);
						$date_of_conv = $conversion_date_raw->format('F jS, Y \a\t g:ia (l)');
						$conversion_clean_date = $conversion_date_raw->format('Y-m-d H:i:s');

						// Display Data
						echo '<div class="lead-timeline recent-conversion-item landing-page-conversion" data-date="'.$conversion_clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<img src="/wp-content/plugins/leads/images/page-view.png" alt="" width="50" height="50" />
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
									  <p><span class="lead-item-num">'.$i.'.</span><span class="lead-helper-text">Converted on landing page/form: </span><a href="'.$converted_page_permalink.'" id="lead-session-'.$i.'" rel="'.$i.'" target="_blank">'.$converted_page_title.'</a><span class="conversion-date">'.$date_of_conv.'</span> <!--<a rel="'.$i.'" href="#view-session-"'.$i.'">('. __( 'view visit path' , 'leads') .')</a>--></p>
									</div>
								</div>
							</div>';
						$i--;

				}

			} else {
				echo "<span id='wpl-message-none'>". __( 'No conversions found!' , 'leads' ) ."</span>";
			}

			?>

			</div> <!-- end lead conversions -->
			<div id="lead-comments" class='lead-activity'>
				<h2>Lead Comments</h2>

			<?php

           	//print_r($conversions);
            // Sort Array by date


			if ($comments_array) {
				//uasort($conversions_array,'leads_sort_array_datetime'); // Date sort
					$comment_count = count($comments_array);

 					$c_i = $comment_count;
					foreach ( $comments_array as $comment ) {
						//print_r($comment);
						$comment_date_raw = new DateTime($comment->comment_date);
						$date_of_comment = $comment_date_raw->format('F jS, Y \a\t g:ia (l)');
						$comment_clean_date = $comment_date_raw->format('Y-m-d H:i:s');

						$commented_page_permalink   = get_permalink($comment->comment_post_ID);
						$commented_page_title = get_the_title($comment->comment_post_ID);
						$comment_id = "#comment-" . $comment->comment_ID;

						//comment_author_url
						//comment_content

						// Display Data
						echo '<div class="lead-timeline recent-conversion-item lead-comment-conversion" data-date="'.$comment_clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<img src="/wp-content/plugins/leads/images/comment.png" alt="" width="50" height="50" />
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text lead-comment-div">
									  <p><span class="lead-item-num">'.$c_i.'.</span><span class="lead-helper-text">Comment on </span><a title="'.__( 'View and respond to the comment' ,'leads' ).'" href="'.$commented_page_permalink. $comment_id .'" id="lead-session-'.$c_i.'" rel="'.$c_i.'" target="_blank">'.$commented_page_title.'</a><span class="conversion-date">'.$date_of_comment.'</span> <!--<a rel="'.$c_i.'" href="#view-session-"'.$c_i.'">(view visit path)</a>--></p>
									  <p class="lead-comment">"'.$comment->comment_content.'" - <a target="_blank" href="'.$comment->comment_author_url.'">'.$comment->comment_author.'</a></p>
									</div>
								</div>
							</div>';
						$c_i--;

				}

			} else {
				echo "<span id='wpl-message-none'>No comments found!</span>";
			}

			?>

			</div> <!-- end lead comments -->

			<div id="lead-searches" class='lead-activity'>
				<h2>Lead Searches</h2>

			<?php if (is_array($wpleads_search_data)){
				//uasort($conversions_array,'leads_sort_array_datetime'); // Date sort
					$search_count = count($wpleads_search_data);

 					$c_i = $search_count;
					foreach ($wpleads_search_data as $key => $value) {
						//print_r($comment);
						$search_date_raw = new DateTime($value['date']);
						$date_of_search = $search_date_raw->format('F jS, Y \a\t g:ia (l)');
						$search_clean_date = $search_date_raw->format('Y-m-d H:i:s');
						$search_query = $value['value'];


						//comment_author_url
						//comment_content

						// Display Data
						echo '<div class="lead-timeline recent-conversion-item lead-search-conversion" data-date="'.$search_clean_date.'">
								<a class="lead-timeline-img" href="#non">

								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text lead-search-div">
									  <p><span class="lead-item-num">'.$c_i.'.</span><span class="lead-helper-text">Search for "</span><strong>'.$search_query.'"</strong> on <span class="conversion-date">'.$date_of_search.'</span> <!--<a rel="'.$c_i.'" href="#view-session-"'.$c_i.'">(view visit path)</a>--></p>

									</div>
								</div>
							</div>';
						$c_i--;

				}

			} else {
				echo "<span id='wpl-message-none'>No searches found!</span>";
			}

			?>

			</div> <!-- end searches comments -->

			<div id="lead-page-views" class='lead-activity'>
				<h2><?php _e( 'Page Views' , 'leads' ); ?></h2>
			 <?php

		 		$page_views = get_post_meta($post->ID,'page_views', true);
		 	   	$page_view_array = json_decode($page_views, true);

		 	   	if ($page_view_array) {

			 	    $new_array = array();
			 	    $loop = 0;
			 		// Combine and loop through all page view objects
			 		 foreach($page_view_array as $key=>$val)
			 	      {
			 	      	foreach($page_view_array[$key] as $test){
			 	      			$new_array[$loop]['page'] = $key;
			 	      			$new_array[$loop]['date'] = $test;
			 	      	      	$loop++;
			 	      	}

			 	      }
			 	    // Merge conversion and page view json objects
			 	   // print_r($new_array);
			 	    //$timeout = 1800; // thirty minutes in seconds
			 	    //$test = abs(strtotime(Last Time) - strtotime(NEW TIME));
			 	   	/* $test = abs(strtotime("2013-11-19 12:58:12") - strtotime("2013-11-20 4:57:02 UTC")); */
			 	  	uasort($new_array,'c_table_leads_sort_array_datetime_reverse'); // Date sort

			 	  	//print_r($new_array);
			 	  	$new_key_array = array();
			 	  	$num = 0;
			 	  	foreach ( $new_array as $key => $val ) {
			 			$new_key_array[ $num ] = $val;
			 			$num++;
			 	  	}
			 	  	//print_r($new_key_array);
			 	  	$new_loop = 1;
			 	  	$total_session_count = 0;
			 	    foreach ($new_key_array as $key => $value) {

			 	    	$last_item = $key - 1;

			 	    	$next_item = $key + 1;
			 	    	$conversion = (isset($new_key_array[$key]['conversion'])) ? 'lead-conversion-mark' : '';
			 	    	$conversion_text = (isset($new_key_array[$key]['conversion'])) ? '<span class="conv-text">(Conversion Event)</span>' : '';
			 	    	$close_div = ($total_session_count != 0) ? '</div></div>' : '';
			 	    	//echo $new_key_array[$new_loop]['date'];
			 	    	if(isset($new_key_array[$last_item]['date'])){
			 	    		$timeout = abs(strtotime($new_key_array[$last_item]['date']) - strtotime($new_key_array[$key]['date']));
			 	    	} else{
			 	    		$timeout = 3601;
			 	    	}

			 	    	$date =  date_create($new_key_array[$key]['date']);
			 	    	$page_id = $new_key_array[$key]['page'];
			 	    	$this_post_type = '';
			 	    		if (strpos($page_id,'cat_') !== false) {
			 	        	  	$cat_id = str_replace("cat_", "", $page_id);
			 	        	  	$page_name = get_cat_name($cat_id) . " Category Page";
			 	        	  	$tag_names = '';
			 	        	  	$page_permalink = get_category_link( $cat_id );
			 	    	  	} elseif (strpos($page_id,'tag_') !== false) {
			 	        	  	$tag_id = str_replace("tag_", "", $page_id);
			 	        	  	$tag = get_tag( $tag_id );
			 	        	  	$page_name = $tag->name . " - Tag Page";
			 	        	  	$tag_names = '';
			 	        	  	$page_permalink = get_tag_link($tag_id);
			 	    	  	} else {
			 	    		$page_title = get_the_title($page_id);
			 	    		$page_name = ($page_id != 0) ? $page_title : 'N/A';
			 	    		$page_permalink = get_permalink($page_id);
			 	    		$this_post_type = get_post_type($page_id);
			 	    	}

			 	    	$timeon_page = $timeout / 60;
			 	    	$date_print = date_create($new_key_array[$key]['date']);
			 	    	//$last_date = date_create($new_key_array[$last_item]['date']);
			 	    	//$date_raw = new DateTime($new_key_array[$key]['date']);
			 	    	//

			 	    	//echo "<br>".$timeout."<-timeout on key " . $new_loop . " " . $new_key_array[$key]['date'] . ' on page: ' . $new_key_array[$key]['page'];
			 	    	//$second_diff = abs(date_format($last_date, 's') - date_format($date_print, 's'));
			 	    	if(isset($new_key_array[$last_item]['date'])){
			 	    	$second_diff = leads_time_diff($new_key_array[$last_item]['date'], $new_key_array[$key]['date']);
			 	    	} else {
			 	    	$second_diff['minutes'] = 0;
			 	    	$second_diff['seconds'] = 0;
			 	    	}
			 	    	//print_r($second_diff);
			 	    	//$second_diff = date('i:s',$second_diff);
			 	    	$minute = ($second_diff['minutes'] != 0) ? "<strong>" . $second_diff['minutes'] . "</strong> " : '';
			 	    	$minute_text = ($second_diff['minutes'] != 0) ? $second_diff['mm-text'] . " " : '';
			 	    	$second = ($second_diff['seconds'] != 0) ? "<strong>" . $second_diff['seconds'] . "</strong> " : 'Less than 1 second';
			 	    	$second_text = ($second_diff['seconds'] != 0) ? $second_diff['sec-text'] . " " : '';


			 	    	$clean_date = date_format($date_print, 'Y-m-d H:i:s');

                   		// Display Data
                   		 echo '<div class="lead-timeline recent-conversion-item page-view-item '.$this_post_type.'" title="'.$page_permalink.'"  data-date="'.$clean_date.'">
								<a class="lead-timeline-img page-views" href="#non">

								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
									  <p><span class="lead-item-num">'.$new_loop.'.</span><span class="lead-helper-text">Viewed page: </span><a href="'.$page_permalink.'" id="lead-session" rel="" target="_blank">'.$page_title .'</a><span class="conversion-date">'.date_format($date_print, 'F jS, Y \a\t g:i:s a').'</span></p>
									</div>
								</div>
							</div>';

			 	    	$new_loop++;

			 	     }



            } else {
                echo "<span id='wpl-message-none'>". __( 'No Page View History Found' , 'leads' ) ."</span>";
            }

            ?>
			</div>
			<?php do_action('wpleads_after_activity_log'); // Custom Action for additional info at bottom of activity log?>
			</div> <!-- end #activites AKA Tab 2 -->
		</div>

		<div class="wpl-tab-display" id="wpleads_lead_tab_raw_form_data" style="display:  <?php if ($active_tab == 'wpleads_lead_tab_raw_form_data') { echo 'block;'; } else { echo 'none;'; } ?>;">
			<div id="raw-data-display">
			<div class="nav-container">
				<nav>
			      <ul>
			        <li class="active"><a href="index.html"><?php _e( 'All' , 'leads' ); ?></a></li>
			        <li><a href="index.html"><?php _e( 'Form Data' , 'leads' ); ?></a></li>
			        <li><a href="index.html"><?php _e( 'Page Data' , 'leads' ); ?></a></li>
			        <li><a href="index.html"><?php _e( 'Event Data' , 'leads' ); ?></a></li>
			      </ul>
			    </nav>
			</div>

			<?php

			// Get Raw form Data
			$raw_data = get_post_meta($post->ID,'wpleads_raw_post_data', true);

			if ($raw_data)
			{
				$raw_data = json_decode($raw_data, true);
				echo "<h2>". __( 'Form Inputs with Values' , 'leads' ) ."</h2>";
				echo "<span id='click-to-map'></span>";
				 echo "<div id='wpl-raw-form-data-table'>";
				foreach($raw_data as  $key=>$value)
				{
					?>
					<div class="wpl-raw-data-tr">
						<span class="wpl-raw-data-td-label">
							<?php echo __( 'Input name:' , 'leads' ) ." <span class='lead-key-normal'>". $key . "</span> &rarr; values:"; ?>
						</span>
						<span class="wpl-raw-data-td-value">
							<?php
							if (is_array($value))
							{
								$value = array_filter($value);
								$value = array_unique($value);
								$num_loop = 1;
								foreach($value as $k=>$v)
								{
									echo "<span class='".$key. "-". $num_loop." possible-map-value'>".$v."</span>";
									$num_loop++;
								}
							}
							else
							{
								echo "<span class='".$key."-1 possible-map-value'>".$value."</span>";
							}
							?>
						</span>
						<span class="map-raw-field"><span class="map-this-text">Map this field to lead</span><span style="display:none;" class='lead_map_select'><select name="NOA" class="field_map_select"></select></span><span class="apply-map button button-primary" style="display:none;">Apply</span></span>
					</div>
				<?php

				}
				echo "<div id='raw-array'>";
				echo "<h2>". __( 'Raw Form Data Array' , 'leads' ) ."</h2>";
				echo "<pre>";
				print_r($raw_data);
				echo "</pre>";
				echo "</div>";
				echo "</div>";
			}
			else
			{
				echo "<span id='wpl-message-none'>". __( 'No raw data found!' ,'leads') ."</span>";
			}

			?>

			</div> <!-- end #raw-data-display -->
		</div>

		<?php
		do_action('wpl_print_lead_tab_sections');
		?>

		</div><!-- end .meta-box-sortables -->
	</div><!-- end .metabox-holder -->
	<?php
}


function wp_leads_sort_fields($a,$b){
        return $a['priority'] > $b['priority']?1:-1;
};

function wpleads_render_setting($fields)
{
	//print_r($fields);
	uasort($fields,'wp_leads_sort_fields');
	echo "<table id='wpleads_main_container'>";

	foreach ($fields as $field)
	{
		$id = strtolower($field['key']);
		echo '<tr class="'.$id.'">
			<th class="wpleads-th" ><label for="'.$id.'">'.$field['label'].':</label></th>
			<td class="wpleads-td" id="wpleads-td-'.$id.'">';
		switch(true) {
			case strstr($field['type'],'textarea'):
				$parts = explode('-',$field['type']);
				(isset($parts[1])) ? $rows= $parts[1] : $rows = '10';
				echo '<textarea name="'.$id.'" id="'.$id.'" rows='.$rows.'" style="" >'.$field['value'].'</textarea>';
				break;
			case strstr($field['type'],'text'):
				$parts = explode('-',$field['type']);
				(isset($parts[1])) ? $size = $parts[1] : $size = 35;

				echo '<input type="text" name="'.$id.'" id="'.$id.'" value="'.$field['value'].'" size="'.$size.'" />';
				break;
			case strstr($field['type'],'links'):
				$parts = explode('-',$field['type']);
				(isset($parts[1])) ? $channel= $parts[1] : $channel = 'related';
				$links = explode(';',$field['value']);
				$links = array_filter($links);

				echo "<div style='text-align:right;float:right'><span class='add-new-link'>".__( 'Add New Link')." <img src='".WPL_URLPATH."/images/add.png' title='".__( 'add link' ) ."' align='ABSMIDDLE' class='wpleads-add-link' 'id='{$id}-add-link'></span></div>";
				echo "<div class='wpleads-links-container' id='{$id}-container'>";

				$remove_icon = WPL_URLPATH.'/images/remove.png';

				if (count($links)>0)
				{
					foreach ($links as $key=>$link)
					{
						$icon = wpleads_get_link_icon($link);
						$icon = apply_filters('wpleads_links_icon',$icon);
						echo '<span id="'.$id.'-'.$key.'"><img src="'.$remove_icon.'" class="wpleads_remove_link" id = "'.$key.'" title="Remove Link">';
						echo '<a href="'.$link.'" target="_blank"><img src="'.$icon.'" align="ABSMIDDLE" class="wpleads_link_icon"><input type="hidden" name="'.$id.'['.$key.']" value="'.$link.'" size="70"  class="wpleads_link"  />'.$link.'</a> ';
						echo "</span><br>";

					}
				}
				else
				{
					echo '<input type="text" name="'.$id.'[]" value="" size="70" />';
				}
				echo '</div>';
				break;
			// wysiwyg
			case strstr($field['type'],'wysiwyg'):
				wp_editor( $field['value'], $id, $settings = array() );
				echo	'<p class="description">'.$field['desc'].'</p>';
				break;
			// media
			case strstr($field['type'],'media'):
				//echo 1; exit;
				echo '<label for="upload_image">';
				echo '<input name="'.$id.'"  id="'.$id.'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
				echo '<input class="upload_image_button" id="uploader_'.$id.'" type="button" value="Upload Image" />';
				echo '<p class="description">'.$field['desc'].'</p>';
				break;
			// checkbox
			case strstr($field['type'],'checkbox'):
				$i = 1;
				echo "<table class='wpl_check_box_table'>";
				if (!isset($field['value'])){$field['value']=array();}
				elseif (!is_array($field['value'])){
					$field['value'] = array($field['value']);
				}
				foreach ($field['options'] as $value=>$field['label']) {
					if ($i==5||$i==1)
					{
						echo "<tr>";
						$i=1;
					}
						echo '<td><input type="checkbox" name="'.$id.'[]" id="'.$id.'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','/>';
						echo '<label for="'.$value.'">&nbsp;&nbsp;'.$field['label'].'</label></td>';
					if ($i==4)
					{
						echo "</tr>";
					}
					$i++;
				}
				echo "</table>";
				echo '<div class="wpl_tooltip tool_checkbox" title="'.$field['desc'].'"></div>';
				break;
			// radio
			case strstr($field['type'],'radio'):
				foreach ($field['options'] as $value=>$field['label']) {
					//echo $field['value'].":".$id;
					//echo "<br>";
					echo '<input type="radio" name="'.$id.'" id="'.$id.'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
					echo '<label for="'.$value.'">&nbsp;&nbsp;'.$field['label'].'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo '<div class="wpl_tooltip" title="'.$field['desc'].'"></div>';
				break;
			// select
			case $field['type'] == 'dropdown':
				echo '<select name="'.$id.'" id="'.$id.'" >';
				foreach ($field['options'] as $value=>$field['label']) {
					echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$field['label'].'</option>';
				}
				echo '</select><div class="wpl_tooltip" title="'.$field['desc'].'"></div>';
				break;
			case $field['type']=='dropdown-country':
				echo '<input type="hidden" id="hidden-country-value" value="'.$field['value'].'">';
				echo '<select name="'.$id.'" id="'.$id.'" class="wpleads-country-dropdown">';
					?>
					<option value=""><?php _e( 'Country...' , 'leads' ); ?></option>
					<option value="AF"><?php _e( 'Afghanistan' , 'leads' ); ?></option>
					<option value="AL"><?php _e( 'Albania' , 'leads' ); ?></option>
					<option value="DZ"><?php _e( 'Algeria' , 'leads' ); ?></option>
					<option value="AS"><?php _e( 'American Samoa' , 'leads' ); ?></option>
					<option value="AD"><?php _e( 'Andorra' , 'leads' ); ?></option>
					<option value="AG"><?php _e( 'Angola' , 'leads' ); ?></option>
					<option value="AI"><?php _e( 'Anguilla' , 'leads' ); ?></option>
					<option value="AG"><?php _e( 'Antigua &amp; Barbuda' , 'leads' ); ?></option>
					<option value="AR"><?php _e( 'Argentina' , 'leads' ); ?></option>
					<option value="AA"><?php _e( 'Armenia' , 'leads' ); ?></option>
					<option value="AW"><?php _e( 'Aruba' , 'leads' ); ?></option>
					<option value="AU"><?php _e( 'Australia' , 'leads' ); ?></option>
					<option value="AT"><?php _e( 'Austria' , 'leads' ); ?></option>
					<option value="AZ"><?php _e( 'Azerbaijan' , 'leads' ); ?></option>
					<option value="BS"><?php _e( 'Bahamas' , 'leads' ); ?></option>
					<option value="BH"><?php _e( 'Bahrain' , 'leads' ); ?></option>
					<option value="BD"><?php _e( 'Bangladesh' , 'leads' ); ?></option>
					<option value="BB"><?php _e( 'Barbados' , 'leads' ); ?></option>
					<option value="BY"><?php _e( 'Belarus' , 'leads' ); ?></option>
					<option value="BE"><?php _e( 'Belgium' , 'leads' ); ?></option>
					<option value="BZ"><?php _e( 'Belize' , 'leads' ); ?></option>
					<option value="BJ"><?php _e( 'Benin' , 'leads' ); ?></option>
					<option value="BM"><?php _e( 'Bermuda' , 'leads' ); ?></option>
					<option value="BT"><?php _e( 'Bhutan' , 'leads' ); ?></option>
					<option value="BO"><?php _e( 'Bolivia' , 'leads' ); ?></option>
					<option value="BL"><?php _e( 'Bonaire' , 'leads' ); ?></option>
					<option value="BA"><?php _e( 'Bosnia &amp; Herzegovina' , 'leads' ); ?></option>
					<option value="BW"><?php _e( 'Botswana' , 'leads' ); ?></option>
					<option value="BR"><?php _e( 'Brazil' , 'leads' ); ?></option>
					<option value="BC"><?php _e( 'British Indian Ocean Ter' , 'leads' ); ?></option>
					<option value="BN"><?php _e( 'Brunei' , 'leads' ); ?></option>
					<option value="BG"><?php _e( 'Bulgaria' , 'leads' ); ?></option>
					<option value="BF"><?php _e( 'Burkina Faso' , 'leads' ); ?></option>
					<option value="BI"><?php _e( 'Burundi' , 'leads' ); ?></option>
					<option value="KH"><?php _e( 'Cambodia' , 'leads' ); ?></option>
					<option value="CM"><?php _e( 'Cameroon' , 'leads' ); ?></option>
					<option value="CA"><?php _e( 'Canada' , 'leads' ); ?></option>
					<option value="IC"><?php _e( 'Canary Islands' , 'leads' ); ?></option>
					<option value="CV"><?php _e( 'Cape Verde' , 'leads' ); ?></option>
					<option value="KY"><?php _e( 'Cayman Islands' , 'leads' ); ?></option>
					<option value="CF"><?php _e( 'Central African Republic' , 'leads' ); ?></option>
					<option value="TD"><?php _e( 'Chad' , 'leads' ); ?></option>
					<option value="CD"><?php _e( 'Channel Islands' , 'leads' ); ?></option>
					<option value="CL"><?php _e( 'Chile' , 'leads' ); ?></option>
					<option value="CN"><?php _e( 'China' , 'leads' ); ?></option>
					<option value="CI"><?php _e( 'Christmas Island' , 'leads' ); ?></option>
					<option value="CS"><?php _e( 'Cocos Island' , 'leads' ); ?></option>
					<option value="CO"><?php _e( 'Colombia' , 'leads' ); ?></option>
					<option value="CC"><?php _e( 'Comoros' , 'leads' ); ?></option>
					<option value="CG"><?php _e( 'Congo' , 'leads' ); ?></option>
					<option value="CK"><?php _e( 'Cook Islands' , 'leads' ); ?></option>
					<option value="CR"><?php _e( 'Costa Rica' , 'leads' ); ?></option>
					<option value="CT"><?php _e( 'Cote D\'Ivoire' , 'leads' ); ?></option>
					<option value="HR"><?php _e( 'Croatia' , 'leads' ); ?></option>
					<option value="CU"><?php _e( 'Cuba' , 'leads' ); ?></option>
					<option value="CB"><?php _e( 'Curacao' , 'leads' ); ?></option>
					<option value="CY"><?php _e( 'Cyprus' , 'leads' ); ?></option>
					<option value="CZ"><?php _e( 'Czech Republic' , 'leads' ); ?></option>
					<option value="DK"><?php _e( 'Denmark' , 'leads' ); ?></option>
					<option value="DJ"><?php _e( 'Djibouti' , 'leads' ); ?></option>
					<option value="DM"><?php _e( 'Dominica' , 'leads' ); ?></option>
					<option value="DO"><?php _e( 'Dominican Republic' , 'leads' ); ?></option>
					<option value="TM"><?php _e( 'East Timor' , 'leads' ); ?></option>
					<option value="EC"><?php _e( 'Ecuador' , 'leads' ); ?></option>
					<option value="EG"><?php _e( 'Egypt' , 'leads' ); ?></option>
					<option value="SV"><?php _e( 'El Salvador' , 'leads' ); ?></option>
					<option value="GQ"><?php _e( 'Equatorial Guinea' , 'leads' ); ?></option>
					<option value="ER"><?php _e( 'Eritrea' , 'leads' ); ?></option>
					<option value="EE"><?php _e( 'Estonia' , 'leads' ); ?></option>
					<option value="ET"><?php _e( 'Ethiopia' , 'leads' ); ?></option>
					<option value="FA"><?php _e( 'Falkland Islands' , 'leads' ); ?></option>
					<option value="FO"><?php _e( 'Faroe Islands' , 'leads' ); ?></option>
					<option value="FJ"><?php _e( 'Fiji' , 'leads' ); ?></option>
					<option value="FI"><?php _e( 'Finland' , 'leads' ); ?></option>
					<option value="FR"><?php _e( 'France' , 'leads' ); ?></option>
					<option value="GF"><?php _e( 'French Guiana' , 'leads' ); ?></option>
					<option value="PF"><?php _e( 'French Polynesia' , 'leads' ); ?></option>
					<option value="FS"><?php _e( 'French Southern Ter' , 'leads' ); ?></option>
					<option value="GA"><?php _e( 'Gabon' , 'leads' ); ?></option>
					<option value="GM"><?php _e( 'Gambia' , 'leads' ); ?></option>
					<option value="GE"><?php _e( 'Georgia' , 'leads' ); ?></option>
					<option value="DE"><?php _e( 'Germany' , 'leads' ); ?></option>
					<option value="GH"><?php _e( 'Ghana' , 'leads' ); ?></option>
					<option value="GI"><?php _e( 'Gibraltar' , 'leads' ); ?></option>
					<option value="GB"><?php _e( 'Great Britain' , 'leads' ); ?></option>
					<option value="GR"><?php _e( 'Greece' , 'leads' ); ?></option>
					<option value="GL"><?php _e( 'Greenland' , 'leads' ); ?></option>
					<option value="GD"><?php _e( 'Grenada' , 'leads' ); ?></option>
					<option value="GP"><?php _e( 'Guadeloupe' , 'leads' ); ?></option>
					<option value="GU"><?php _e( 'Guam' , 'leads' ); ?></option>
					<option value="GT"><?php _e( 'Guatemala' , 'leads' ); ?></option>
					<option value="GN"><?php _e( 'Guinea' , 'leads' ); ?></option>
					<option value="GY"><?php _e( 'Guyana' , 'leads' ); ?></option>
					<option value="HT"><?php _e( 'Haiti' , 'leads' ); ?></option>
					<option value="HW"><?php _e( 'Hawaii' , 'leads' ); ?></option>
					<option value="HN"><?php _e( 'Honduras' , 'leads' ); ?></option>
					<option value="HK"><?php _e( 'Hong Kong' , 'leads' ); ?></option>
					<option value="HU"><?php _e( 'Hungary' , 'leads' ); ?></option>
					<option value="IS"><?php _e( 'Iceland' , 'leads' ); ?></option>
					<option value="IN"><?php _e( 'India' , 'leads' ); ?></option>
					<option value="ID"><?php _e( 'Indonesia' , 'leads' ); ?></option>
					<option value="IA"><?php _e( 'Iran' , 'leads' ); ?></option>
					<option value="IQ"><?php _e( 'Iraq' , 'leads' ); ?></option>
					<option value="IR"><?php _e( 'Ireland' , 'leads' ); ?></option>
					<option value="IM"><?php _e( 'Isle of Man' , 'leads' ); ?></option>
					<option value="IL"><?php _e( 'Israel' , 'leads' ); ?></option>
					<option value="IT"><?php _e( 'Italy' , 'leads' ); ?></option>
					<option value="JM"><?php _e( 'Jamaica' , 'leads' ); ?></option>
					<option value="JP"><?php _e( 'Japan' , 'leads' ); ?></option>
					<option value="JO"><?php _e( 'Jordan' , 'leads' ); ?></option>
					<option value="KZ"><?php _e( 'Kazakhstan' , 'leads' ); ?></option>
					<option value="KE"><?php _e( 'Kenya' , 'leads' ); ?></option>
					<option value="KI"><?php _e( 'Kiribati' , 'leads' ); ?></option>
					<option value="NK"><?php _e( 'Korea North' , 'leads' ); ?></option>
					<option value="KS"><?php _e( 'Korea South' , 'leads' ); ?></option>
					<option value="KW"><?php _e( 'Kuwait' , 'leads' ); ?></option>
					<option value="KG"><?php _e( 'Kyrgyzstan' , 'leads' ); ?></option>
					<option value="LA"><?php _e( 'Laos' , 'leads' ); ?></option>
					<option value="LV"><?php _e( 'Latvia' , 'leads' ); ?></option>
					<option value="LB"><?php _e( 'Lebanon' , 'leads' ); ?></option>
					<option value="LS"><?php _e( 'Lesotho' , 'leads' ); ?></option>
					<option value="LR"><?php _e( 'Liberia' , 'leads' ); ?></option>
					<option value="LY"><?php _e( 'Libya' , 'leads' ); ?></option>
					<option value="LI"><?php _e( 'Liechtenstein' , 'leads' ); ?></option>
					<option value="LT"><?php _e( 'Lithuania' , 'leads' ); ?></option>
					<option value="LU"><?php _e( 'Luxembourg' , 'leads' ); ?></option>
					<option value="MO"><?php _e( 'Macau' , 'leads' ); ?></option>
					<option value="MK"><?php _e( 'Macedonia' , 'leads' ); ?></option>
					<option value="MG"><?php _e( 'Madagascar' , 'leads' ); ?></option>
					<option value="MY"><?php _e( 'Malaysia' , 'leads' ); ?></option>
					<option value="MW"><?php _e( 'Malawi' , 'leads' ); ?></option>
					<option value="MV"><?php _e( 'Maldives' , 'leads' ); ?></option>
					<option value="ML"><?php _e( 'Mali' , 'leads' ); ?></option>
					<option value="MT"><?php _e( 'Malta' , 'leads' ); ?></option>
					<option value="MH"><?php _e( 'Marshall Islands' , 'leads' ); ?></option>
					<option value="MQ"><?php _e( 'Martinique' , 'leads' ); ?></option>
					<option value="MR"><?php _e( 'Mauritania' , 'leads' ); ?></option>
					<option value="MU"><?php _e( 'Mauritius' , 'leads' ); ?></option>
					<option value="ME"><?php _e( 'Mayotte' , 'leads' ); ?></option>
					<option value="MX"><?php _e( 'Mexico' , 'leads' ); ?></option>
					<option value="MI"><?php _e( 'Midway Islands' , 'leads' ); ?></option>
					<option value="MD"><?php _e( 'Moldova' , 'leads' ); ?></option>
					<option value="MC"><?php _e( 'Monaco' , 'leads' ); ?></option>
					<option value="MN"><?php _e( 'Mongolia' , 'leads' ); ?></option>
					<option value="MS"><?php _e( 'Montserrat' , 'leads' ); ?></option>
					<option value="MA"><?php _e( 'Morocco' , 'leads' ); ?></option>
					<option value="MZ"><?php _e( 'Mozambique' , 'leads' ); ?></option>
					<option value="MM"><?php _e( 'Myanmar' , 'leads' ); ?></option>
					<option value="NA"><?php _e( 'Nambia' , 'leads' ); ?></option>
					<option value="NU"><?php _e( 'Nauru' , 'leads' ); ?></option>
					<option value="NP"><?php _e( 'Nepal' , 'leads' ); ?></option>
					<option value="AN"><?php _e( 'Netherland Antilles' , 'leads' ); ?></option>
					<option value="NL"><?php _e( 'Netherlands (Holland, Europe)' , 'leads' ); ?></option>
					<option value="NV"><?php _e( 'Nevis' , 'leads' ); ?></option>
					<option value="NC"><?php _e( 'New Caledonia' , 'leads' ); ?></option>
					<option value="NZ"><?php _e( 'New Zealand' , 'leads' ); ?></option>
					<option value="NI"><?php _e( 'Nicaragua' , 'leads' ); ?></option>
					<option value="NE"><?php _e( 'Niger' , 'leads' ); ?></option>
					<option value="NG"><?php _e( 'Nigeria' , 'leads' ); ?></option>
					<option value="NW"><?php _e( 'Niue' , 'leads' ); ?></option>
					<option value="NF"><?php _e( 'Norfolk Island' , 'leads' ); ?></option>
					<option value="NO"><?php _e( 'Norway' , 'leads' ); ?></option>
					<option value="OM"><?php _e( 'Oman' , 'leads' ); ?></option>
					<option value="PK"><?php _e( 'Pakistan' , 'leads' ); ?></option>
					<option value="PW"><?php _e( 'Palau Island' , 'leads' ); ?></option>
					<option value="PS"><?php _e( 'Palestine' , 'leads' ); ?></option>
					<option value="PA"><?php _e( 'Panama' , 'leads' ); ?></option>
					<option value="PG"><?php _e( 'Papua New Guinea' , 'leads' ); ?></option>
					<option value="PY"><?php _e( 'Paraguay' , 'leads' ); ?></option>
					<option value="PE"><?php _e( 'Peru' , 'leads' ); ?></option>
					<option value="PH"><?php _e( 'Philippines' , 'leads' ); ?></option>
					<option value="PO"><?php _e( 'Pitcairn Island' , 'leads' ); ?></option>
					<option value="PL"><?php _e( 'Poland' , 'leads' ); ?></option>
					<option value="PT"><?php _e( 'Portugal' , 'leads' ); ?></option>
					<option value="PR"><?php _e( 'Puerto Rico' , 'leads' ); ?></option>
					<option value="QA"><?php _e( 'Qatar' , 'leads' ); ?></option>
					<option value="ME"><?php _e( 'Republic of Montenegro' , 'leads' ); ?></option>
					<option value="RS"><?php _e( 'Republic of Serbia' , 'leads' ); ?></option>
					<option value="RE"><?php _e( 'Reunion' , 'leads' ); ?></option>
					<option value="RO"><?php _e( 'Romania' , 'leads' ); ?></option>
					<option value="RU"><?php _e( 'Russia' , 'leads' ); ?></option>
					<option value="RW"><?php _e( 'Rwanda' , 'leads' ); ?></option>
					<option value="NT"><?php _e( 'St Barthelemy' , 'leads' ); ?></option>
					<option value="EU"><?php _e( 'St Eustatius' , 'leads' ); ?></option>
					<option value="HE"><?php _e( 'St Helena' , 'leads' ); ?></option>
					<option value="KN"><?php _e( 'St Kitts-Nevis' , 'leads' ); ?></option>
					<option value="LC"><?php _e( 'St Lucia' , 'leads' ); ?></option>
					<option value="MB"><?php _e( 'St Maarten' , 'leads' ); ?></option>
					<option value="PM"><?php _e( 'St Pierre &amp; Miquelon' , 'leads' ); ?></option>
					<option value="VC"><?php _e( 'St Vincent &amp; Grenadines' , 'leads' ); ?></option>
					<option value="SP"><?php _e( 'Saipan' , 'leads' ); ?></option>
					<option value="SO"><?php _e( 'Samoa' , 'leads' ); ?></option>
					<option value="AS"><?php _e( 'Samoa American' , 'leads' ); ?></option>
					<option value="SM"><?php _e( 'San Marino' , 'leads' ); ?></option>
					<option value="ST"><?php _e( 'Sao Tome &amp; Principe' , 'leads' ); ?></option>
					<option value="SA"><?php _e( 'Saudi Arabia' , 'leads' ); ?></option>
					<option value="SN"><?php _e( 'Senegal' , 'leads' ); ?></option>
					<option value="SC"><?php _e( 'Seychelles' , 'leads' ); ?></option>
					<option value="SL"><?php _e( 'Sierra Leone' , 'leads' ); ?></option>
					<option value="SG"><?php _e( 'Singapore' , 'leads' ); ?></option>
					<option value="SK"><?php _e( 'Slovakia' , 'leads' ); ?></option>
					<option value="SI"><?php _e( 'Slovenia' , 'leads' ); ?></option>
					<option value="SB"><?php _e( 'Solomon Islands' , 'leads' ); ?></option>
					<option value="OI"><?php _e( 'Somalia' , 'leads' ); ?></option>
					<option value="ZA"><?php _e( 'South Africa' , 'leads' ); ?></option>
					<option value="ES"><?php _e( 'Spain' , 'leads' ); ?></option>
					<option value="LK"><?php _e( 'Sri Lanka' , 'leads' ); ?></option>
					<option value="SD"><?php _e( 'Sudan' , 'leads' ); ?></option>
					<option value="SR"><?php _e( 'Suriname' , 'leads' ); ?></option>
					<option value="SZ"><?php _e( 'Swaziland' , 'leads' ); ?></option>
					<option value="SE"><?php _e( 'Sweden' , 'leads' ); ?></option>
					<option value="CH"><?php _e( 'Switzerland' , 'leads' ); ?></option>
					<option value="SY"><?php _e( 'Syria' , 'leads' ); ?></option>
					<option value="TA"><?php _e( 'Tahiti' , 'leads' ); ?></option>
					<option value="TW"><?php _e( 'Taiwan' , 'leads' ); ?></option>
					<option value="TJ"><?php _e( 'Tajikistan' , 'leads' ); ?></option>
					<option value="TZ"><?php _e( 'Tanzania' , 'leads' ); ?></option>
					<option value="TH"><?php _e( 'Thailand' , 'leads' ); ?></option>
					<option value="TG"><?php _e( 'Togo' , 'leads' ); ?></option>
					<option value="TK"><?php _e( 'Tokelau' , 'leads' ); ?></option>
					<option value="TO"><?php _e( 'Tonga' , 'leads' ); ?></option>
					<option value="TT"><?php _e( 'Trinidad &amp; Tobago' , 'leads' ); ?></option>
					<option value="TN"><?php _e( 'Tunisia' , 'leads' ); ?></option>
					<option value="TR"><?php _e( 'Turkey' , 'leads' ); ?></option>
					<option value="TU"><?php _e( 'Turkmenistan' , 'leads' ); ?></option>
					<option value="TC"><?php _e( 'Turks &amp; Caicos Is' , 'leads' ); ?></option>
					<option value="TV"><?php _e( 'Tuvalu' , 'leads' ); ?></option>
					<option value="UG"><?php _e( 'Uganda' , 'leads' ); ?></option>
					<option value="UA"><?php _e( 'Ukraine' , 'leads' ); ?></option>
					<option value="AE"><?php _e( 'United Arab Emirates' , 'leads' ); ?></option>
					<option value="GB"><?php _e( 'United Kingdom' , 'leads' ); ?></option>
					<option value="US"><?php _e( 'United States of America' , 'leads' ); ?></option>
					<option value="UY"><?php _e( 'Uruguay' , 'leads' ); ?></option>
					<option value="UZ"><?php _e( 'Uzbekistan' , 'leads' ); ?></option>
					<option value="VU"><?php _e( 'Vanuatu' , 'leads' ); ?></option>
					<option value="VS"><?php _e( 'Vatican City State' , 'leads' ); ?></option>
					<option value="VE"><?php _e( 'Venezuela' , 'leads' ); ?></option>
					<option value="VN"><?php _e( 'Vietnam' , 'leads' ); ?></option>
					<option value="VB"><?php _e( 'Virgin Islands (Brit)' , 'leads' ); ?></option>
					<option value="VA"><?php _e( 'Virgin Islands (USA)' , 'leads' ); ?></option>
					<option value="WK"><?php _e( 'Wake Island' , 'leads' ); ?></option>
					<option value="WF"><?php _e( 'Wallis &amp; Futana Is' , 'leads' ); ?></option>
					<option value="YE"><?php _e( 'Yemen' , 'leads' ); ?></option>
					<option value="ZR"><?php _e( 'Zaire' , 'leads' ); ?></option>
					<option value="ZM"><?php _e( 'Zambia' , 'leads' ); ?></option>
					<option value="ZW"><?php _e( 'Zimbabwe' , 'leads' ); ?></option>
					</select>
					<?php
				break;
		} //end switch
		echo '</td></tr>';
	}

	echo '</table>';
}


function wpleads_get_link_icon($link)
{
	switch (true){
		case strstr($link,'facebook.com'):
			$icon = WPL_URLPATH.'/images/icons/facebook.png';
			break;
		case strstr($link,'linkedin.com'):
			$icon = WPL_URLPATH.'/images/icons/linkedin.png';
			break;
		case strstr($link,'twitter.com'):
			$icon = WPL_URLPATH.'/images/icons/twitter.png';
			break;
		case strstr($link,'pinterest.com'):
			$icon = WPL_URLPATH.'/images/icons/pinterest.png';
			break;
		case strstr($link,'plus.google.'):
			$icon = WPL_URLPATH.'/images/icons/google.png';
			break;
		case strstr($link,'youtube.com'):
			$icon = WPL_URLPATH.'/images/icons/youtube.png';
			break;
		case strstr($link,'reddit.com'):
			$icon = WPL_URLPATH.'/images/icons/reddit.png';
			break;
		case strstr($link,'badoo.com'):
			$icon = WPL_URLPATH.'/images/icons/badoo.png';
			break;
		case strstr($link,'meetup.com'):
			$icon = WPL_URLPATH.'/images/icons/meetup.png';
			break;
		case strstr($link,'livejournal.com'):
			$icon = WPL_URLPATH.'/images/icons/livejournal.png';
			break;
		case strstr($link,'myspace.com'):
			$icon = WPL_URLPATH.'/images/icons/myspace.png';
			break;
		case strstr($link,'deviantart.com'):
			$icon = WPL_URLPATH.'/images/icons/deviantart.png';
			break;
		default:
			$icon = WPL_URLPATH.'/images/icons/link.png';
			break;
	}

	return $icon;
}

function wpl_tag_cloud() {
	global $post;
	$page_views = get_post_meta($post->ID,'page_views', true);
    $page_view_array = json_decode($page_views, true);
    if($page_views && is_array($page_view_array))
	{
     	// Collect all viewed page IDs
		foreach($page_view_array as $key=>$val)
		{
			$id = $key;
			$ids[] = $key;
		}

        // Get Tags from all pages viewed

        foreach($ids as $key=>$val)
		{
			//echo $val;
			$array = wp_get_post_tags( $val, array( 'fields' => 'names' ) );
			if(!empty($array))
				$tag_names[] = wp_get_post_tags( $val, array( 'fields' => 'names' ) );


		}
        // Merge and count
        $final_tags = array();
        if(!empty($tag_names)){
           	foreach($tag_names as $array){

			    foreach($array as $key=>$value){

			        $final_tags[] = $value;
			    }
			}
		}

        $return_tags = array_count_values($final_tags);
    }
	else
	{
    	$return_tags = array(); // empty
    }

	return $return_tags; // return tag array

}



function wpl_manage_lead_js($tabs)
{

	if (isset($_GET['tab']))
	{
		$default_id = $_GET['tab'];
	}
	else
	{
		$default_id ='main';
	}

	?>
	<script type='text/javascript'>
	jQuery(document).ready(function()
	{
		jQuery('.wpl-nav-tab').live('click', function() {

			var this_id = this.id.replace('tabs-','');
			//alert(this_id);
			jQuery('.wpl-tab-display').css('display','none');
			jQuery('#'+this_id).css('display','block');
			jQuery('.wpl-nav-tab').removeClass('nav-tab-special-active');
			jQuery('.wpl-nav-tab').addClass('nav-tab-special-inactive');
			jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');
			jQuery('#id-open-tab').val(this_id);
		});
	});
	</script>
	<?php
}

add_action('save_post', 'wpleads_save_user_fields');
function wpleads_save_user_fields($post_id) {

	global $post;

	if (!isset($post)||isset($_POST['split_test']))
		return;

	if ($post->post_type=='revision' ||  'trash' == get_post_status( $post_id ))
	{
		return;
	}
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )||( isset($_POST['post_type']) && $_POST['post_type']=='revision' ))
	{
		return;
	}

	if ($post->post_type=='wp-lead')
	{
		$Leads_Field_Map = new Leads_Field_Map();
		$wpleads_user_fields = $Leads_Field_Map->get_lead_fields();
		foreach ($wpleads_user_fields as $key=>$field)
		{

			$old = get_post_meta($post_id, $field['key'], true);
			if (isset($_POST[$field['key']]))
			{
				$new = $_POST[$field['key']];

				if (is_array($new))
				{
					//echo $field['name'];exit;
					array_filter($new);
					$new = implode(';',$new);
					update_post_meta($post_id, $field['key'], $new);
				}
				else if (isset($new) && $new != $old )
				{
					update_post_meta($post_id, $field['key'], $new);

					if ($field['key']=='wpleads_email_address')
					{
						$args = array( 'ID'=>$post_id , 'post_title' => $new );
						wp_update_post($args);
					}

				}
				else if ('' == $new && $old) {
					delete_post_meta($post_id, $field['key'], $old);
				}
			}
		}


	}
}


/* ADD CONVERSIONS METABOX */
//
// Currently off for debuging
// Need to revamp this. Mysql custom table isn't cutting it
//
add_action('add_meta_boxes', 'wplead_add_conversion_path');
function wplead_add_conversion_path() {
	global $post;

	add_meta_box(
		'wplead_metabox_conversion', // $id
		__( 'Visitor Path Sessions - <span class="session-desc">(Sessions expire after 1 hour of inactivity)</span> <span class="minimize-paths button">Shrink Session View</span>' , 'leads' ),
		'wpleads_display_conversion_path', // $callback
		'wp-lead', // $page
		'normal', // $context
		'high'); // $priority
}

function c_table_leads_sort_array_datetime_reverse($a,$b) {
	return strtotime($a['date'])>strtotime($b['date'])?1:-1;
}

function c_table_leads_sort_array_datetime($a,$b){
        return strtotime($a['date'])<strtotime($b['date'])?1:-1;
}

// Render Conversion Paths
function wpleads_display_conversion_path() {
	global $post;
	global $wpdb;

	$conversions = get_post_meta($post->ID,'wpleads_conversion_data', true);
	$conversions_array = json_decode($conversions, true);
	$c_array = array();
	if (is_array($conversions_array))
	{
		uasort($conversions_array,'leads_sort_array_datetime'); // Date sort
		$conversion_count = count($conversions_array);
		//print_r($conversions_array);
		$i = $conversion_count;

		$c_count = 0;
		foreach ($conversions_array as $key => $value)
		{
			$c_array[$c_count]['page'] = $value['id'];
			$c_array[$c_count]['date'] = $value['datetime'];
			$c_array[$c_count]['conversion'] = 'yes';
			$c_array[$c_count]['variation'] = $value['variation'];
	      	$c_count++;

		}
	}

	$page_views = get_post_meta($post->ID,'page_views', true);
   	$page_view_array = json_decode($page_views, true);

   	if (!is_array($page_view_array)) {
   		echo "No Data";
   		return;
   	}

    $new_array = array();
    $loop = 0;

	// Combine and loop through all page view objects
	foreach($page_view_array as $key=>$val)
    {
      	foreach($page_view_array[$key] as $test){
      			$new_array[$loop]['page'] = $key;
      			$new_array[$loop]['date'] = $test;
      	      	$loop++;
      	}
    }

    $new_array = array_merge($c_array, $new_array); // Merge conversion and page view json objects

  	uasort($new_array,'c_table_leads_sort_array_datetime'); // Date sort

  	//print_r($new_array);
  	$new_key_array = array();
  	$num = 0;

  	foreach ( $new_array as $key => $val ) {
		$new_key_array[ $num ] = $val;
		$num++;
  	}


  	$new_loop = 1;
  	$total_session_count = 0;

    foreach ($new_key_array as $key => $value) {

    	$last_item = $key - 1;
    	$next_item = $key + 1;

    	$conversion = (isset($new_key_array[$key]['conversion'])) ? 'lead-conversion-mark' : '';
    	$conversion_text = (isset($new_key_array[$key]['conversion'])) ? '<span class="conv-text">(Conversion Event)</span>' : '';
    	$close_div = ($total_session_count != 0) ? '</div></div>' : '';


    	if(isset($new_key_array[$last_item]['date'])){
    		$timeout = abs(strtotime($new_key_array[$last_item]['date']) - strtotime($new_key_array[$key]['date']));
    	} else {
    		$timeout = 3601;
    	}

    	$date =  date_create($new_key_array[$key]['date']);
    	$break = 'off';

    	if ($timeout >= 3600) {
    		echo $close_div . '<a class="session-anchor" id="view-session-'.$total_session_count.'""></a><div id="conversion-tracking" class="wpleads-conversion-tracking-table" summary="Conversion Tracking">

    		<div class="conversion-tracking-header">
    				<h2><span class="toggle-conversion-list">-</span><strong>Visit <span class="visit-number"></span></strong> on <span class="shown_date">'.date_format($date, 'F jS, Y \a\t g:ia (l)').'</span><span class="time-on-page-label">Time spent on page</span></h2> <span class="hidden_date date_'.$total_session_count.'">'.date_format($date, 'F jS, Y \a\t g:ia:s').'</span>
    		</div><div class="session-item-holder">';

    		$total_session_count++;
    		//echo "</div>";
    		$break = "on";
    	}

    	$page_id = $new_key_array[$key]['page'];

		if (strpos($page_id,'cat_') !== false) {
			$cat_id = str_replace("cat_", "", $page_id);
			$page_name = get_cat_name($cat_id) . " Category Page";
			$tag_names = '';
			$page_permalink = get_category_link( $cat_id );

		} elseif (strpos($page_id,'tag_') !== false) {
			$tag_id = str_replace("tag_", "", $page_id);
			$tag = get_tag( $tag_id );
			$page_name = $tag->name . " - Tag Page";
			$tag_names = '';
			$page_permalink = get_tag_link($tag_id);

		} else {
			$page_title = get_the_title($page_id);
			$page_name = ($page_id != 0) ? $page_title : 'N/A';
			$page_permalink = get_permalink($page_id);
		}

    	$timeon_page = $timeout / 60;
    	$date_print = date_create($new_key_array[$key]['date']);

    	if(isset($new_key_array[$last_item]['date'])){
			$second_diff = leads_time_diff($new_key_array[$last_item]['date'], $new_key_array[$key]['date']);
    	} else {
			$second_diff['minutes'] = 0;
			$second_diff['seconds'] = 0;
    	}

    	$minute = ($second_diff['minutes'] != 0) ? "<strong>" . $second_diff['minutes'] . "</strong> " : '';
    	$minute_text = ($second_diff['minutes'] != 0) ? $second_diff['mm-text'] . " " : '';
    	$second = ($second_diff['seconds'] != 0) ? "<strong>" . $second_diff['seconds'] . "</strong> " : 'Less than 1 second';
    	$second_text = ($second_diff['seconds'] != 0) ? $second_diff['sec-text'] . " " : '';

		if ($break === "on") {
    		$minute = "";
    		$minute_text =  "";
    		$second =  "";
    		$second_text =  "Session Timeout";
    	}

    	if ($page_id != "0" && $page_id != "null") {

			$page_output = strlen($page_name) > 65 ? substr($page_name,0,65)."..." : $page_name;
			echo "<div class='lp-page-view-item ".$conversion."'>
			<span class='marker'></span> <a href='".$page_permalink."' title='View ".$page_name."' target='_blank'>".$page_output."</a> on <span>".date_format($date_print, 'F jS, Y \a\t g:i:s a')."</span>
			".$conversion_text."
			<span class='time-on-page'>". $minute . $minute_text .  $second . $second_text . "</span>
			</div>";
		}

    	$new_loop++;

    }
	?>

		</div><!-- end .conversion-session-view -->
		</div><!-- end #conversion-tracking -->

	<?php
}