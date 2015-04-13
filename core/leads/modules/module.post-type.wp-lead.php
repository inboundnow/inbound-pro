<?php

/* KEEP DATETIME TRACK OF UPDATED POST META FOR WP-LEAD CPT */
add_action( 'added_post_meta', 'wpleads_after_post_meta_change', 10, 4 );
add_action( 'updated_post_meta', 'wpleads_after_post_meta_change', 10, 4 );
function wpleads_after_post_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {
	$ignore = array ('_edit_lock', '_edit_last');
	$post_type = get_post_type($post_id);
    if ( $post_type == 'wp-lead' && !in_array( $meta_key , $ignore ) ) {
		/*
		echo "Meta_id: $meta_id<br>";
		echo "Meta_key: $meta_key<br>";
		*/
		remove_action( 'updated_post_meta' , 'wpleads_after_post_meta_change' , 10 );
		remove_action( 'added_post_meta' , 'wpleads_after_post_meta_change' , 10 );

		$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
		$wordpress_date_time =  date_i18n($timezone_format);

		update_post_meta( $post_id , 'wpleads_last_updated' , $wordpress_date_time );
		do_action( 'wpleads_after_post_meta_change' , $post_id );

    }
}

/*********PREPARE COLUMNS FOR LEADS***************/

// Change the columns for the edit CPT screen
add_filter( "manage_wp-lead_posts_columns", "wpleads_change_columns" );
function wpleads_change_columns( $cols ) {

	$cols = array(
		"cb" => "<input type=\"checkbox\" />",
		"lead-picture" => "Lead",
		"first-name" => "First Name",
		"last-name" => "Last Name",
		"title" => "Email",
	);
	if (isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != "") {
		$the_val = $_GET['wp_leads_filter_field'];
		$nice_names = wpl_nice_field_names();
		if(array_key_exists($the_val, $nice_names)){
			$the_val = $nice_names[$the_val];
		}
		$cols['custom'] =  $the_val;
	}

	$cols_merge = array("status" => "Status",
		// "company" => "Company", Custom Column
		'conversion-count' => "Conversion Count",
		"page-views" => "Total Page Views",
		/*'modified' => "Latest Activity", */
		"date" => "Created"
	);
	$cols = array_merge($cols, $cols_merge);
	return $cols;
}


add_action( "manage_posts_custom_column", "wpleads_custom_columns", 10, 2 );
function wpleads_custom_columns( $column, $post_id ) {
	global $post;

	if ($post->post_type !='wp-lead'){
		return $column;
	}

	switch ( $column ) {
		case "lead-picture":
		$email = get_post_meta( $post_id , 'wpleads_email_address', true );
		$size = 50;
		$url = site_url();
		$default = WPL_URLPATH . '/images/gravatar_default_50.jpg'; // doesn't work for some sites
		
		$gravatar = "//www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
		$extra_image = get_post_meta( $post_id , 'lead_main_image', true );
	    /*
		Super expensive call. Need more elegant solution
	 	$response = get_headers($gravatar);
		if ($response[0] === "HTTP/1.0 302 Found"){
			$gravatar = $url . '/wp-content/plugins/leads/images/gravatar_default_50.jpg';
		} else {
			$gravatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
		}
		*/
		// Fix for localhost view
		if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
		    $gravatar = $default;
		}
		if(preg_match("/gravatar_default_/", $gravatar) && $extra_image != ""){
			$gravatar = $extra_image;
			$gravatar2 = $extra_image;
		}
		  echo'<img class="lead-grav-img" width="50" height="50" src="'.$gravatar.'">';
		  break;
		case "first-name":
		  $first_name = get_post_meta( $post_id, 'wpleads_first_name', true);
		  if (!$first_name || $first_name == 'false') {
		  	$first_name = __('n/a' , 'leads');
		  }
		  echo $first_name;
		  break;
		case "last-name":
		  $last_name = get_post_meta( $post_id, 'wpleads_last_name', true);
		   if (!$last_name) {
		  	$last_name = __('n/a' , 'leads');
		  }
		  echo $last_name;
		  break;
		case "status":
		  $lead_status = get_post_meta( $post_id, 'wp_lead_status', true);
		  echo $lead_status;
		  break;
		  case "conversion-count":
		  $last_conversion = get_post_meta($post_id,'wpleads_conversion_data', true);
		  $last_conversion = json_decode($last_conversion, true);
		  	if (is_array($last_conversion)){
		  	$count_conversions = count($last_conversion);
		  	} else {
		  	$count_conversions = get_post_meta($post_id,'wpleads_conversion_count', true);
		  	}
		  echo $count_conversions;
		  break;
	  	case "custom":
	  	if (isset($_GET['wp_leads_filter_field'])) {
	  		$the_val = $_GET['wp_leads_filter_field'];
	  	}
	    $custom_val = get_post_meta( $post_id, $the_val, true);
	     if (!$custom_val) {
	    	$custom_val = 'N/A';
	    }
	    echo $custom_val;
	    break;
		case "page-views":
			$page_views = get_post_meta($post_id,'page_views', true);
		    $page_view_array = json_decode($page_views, true);
		    $page_view_count = 0;
			    if (is_array($page_view_array)){
			    	foreach($page_view_array as $key=>$val) {
			         $page_view_count += count($page_view_array[$key]);
			        }
			   	} else {
			      	$page_view_count = get_post_meta($post_id,'wpleads_page_view_count', true);
			    }
		  echo $page_view_count;
		  break;
		case "company":
		  $company = get_post_meta( $post_id, 'wpleads_company_name', true);
		  echo $company;
		  break;
	}
}


// Add category/list query filters for easy lead searching
add_action( 'restrict_manage_posts', 'wp_lead_taxonomy_filter_restrict_manage_posts' );
// Add category sort to lead list page
function wp_lead_taxonomy_filter_restrict_manage_posts() {
    global $typenow;

	if ($typenow === "wp-lead" ) {
    $post_types = get_post_types( array( '_builtin' => false ) );
    if ( in_array( $typenow, $post_types ) ) {
    	$filters = get_object_taxonomies( $typenow );
    	// categories don't exist
        foreach ( $filters as $tax_slug ) {
            $tax_obj = get_taxonomy( $tax_slug );
            (isset($_GET[$tax_slug])) ? $current = $_GET[$tax_slug] : $current = 0;
            wp_dropdown_categories( array(
                'show_option_all' => __($tax_obj->label),
                'taxonomy' 	  => $tax_slug,
                'name' 		  => $tax_obj->name,
                'orderby' 	  => 'name',
                'selected' 	  => $current,
                'hierarchical' 	  => $tax_obj->hierarchical,
                'show_count' 	  => true,
                'hide_empty' 	  => false
            ) );
	        }
	    }
	}
}


add_filter('parse_query','lead_category_id_to_taxonomy_term_in_query');
function lead_category_id_to_taxonomy_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if( $pagenow=='edit.php' && isset($qv['wplead_list_category']) && is_numeric($qv['wplead_list_category']) ) {
		if($qv['wplead_list_category'] != 0){
		$term = get_term_by('id',$qv['wplead_list_category'],'wplead_list_category');
		$qv['wplead_list_category'] = $term->slug;
		}
	}
}

add_filter('parse_query','lead_tag_id_to_taxonomy_term_in_query');
function lead_tag_id_to_taxonomy_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if( $pagenow=='edit.php' && isset($qv['lead-tags']) && is_numeric($qv['lead-tags']) ) {
		if($qv['lead-tags'] != 0){
		$term = get_term_by('id',$qv['lead-tags'],'lead-tags');
		$qv['lead-tags'] = $term->slug;
		}
	}
}

// Show leads in specific time range
add_action( 'admin_init', 'wp_leads_lead_today_filter' );

function wp_leads_lead_today_filter() {
	global $pagenow, $typenow;
	if( 'edit.php' == $pagenow && 'wp-lead' == $typenow && isset($_GET['current_date']) ) {
		add_action( 'parse_query', 'wp_leads_get_today' );
	}
	if( 'edit.php' == $pagenow && 'wp-lead' == $typenow && isset($_GET['current_month']) ) {
		add_action( 'parse_query', 'wp_leads_get_month' );
	}
}

function wp_leads_get_month() {
	$timezone_month = _x('m', 'timezone date format');
	$wordpress_date_month =  date_i18n($timezone_month);
	set_query_var('monthnum', $wordpress_date_month ); // Show only leads from today
	  return;
}

function wp_leads_get_today() {
	$timezone_day = _x('d', 'timezone date format');
	$wordpress_date_day =  date_i18n($timezone_day);
	set_query_var('day', $wordpress_date_day ); // Show only leads from today
	  return;
}


add_action('admin_init', 'run_custom_lead_queries');
// Run queries after admin init
function run_custom_lead_queries(){
	add_filter( 'parse_query', 'wpl_admin_posts_meta_filter' );
	add_filter( 'parse_query', 'wp_leads_lead_email_filter' );
}

function wpl_admin_posts_meta_filter( $query ) {
		global $pagenow;
		$screen = get_current_screen(); //@this function is not working on some wp installation. Look more into this.

		if (!$screen)
			return;

		$screen_id = $screen->id;

		if ( is_admin() && $pagenow=='edit.php' && $screen_id=='edit-wp-lead' && isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != '') {
			$query->query_vars['meta_key'] = $_GET['wp_leads_filter_field'];
		if (isset($_GET['wp_leads_filter_field_val']) && $_GET['wp_leads_filter_field_val'] != '')
			$query->query_vars['meta_value'] = $_GET['wp_leads_filter_field_val'];
		}
}


	function wp_leads_lead_email_filter( $query ) {
			global $pagenow;
			$screen = get_current_screen(); //@this function is not working on some wp installation. Look more into this.

			if (!$screen)
				return;

			$screen_id = $screen->id;

			if ( is_admin() && $pagenow=='edit.php' && $screen_id=='edit-wp-lead' && isset($_GET['lead-email']) && $_GET['lead-email'] != '') {
				$query->query_vars['meta_key'] = 'wpleads_email_address';
			if (isset($_GET['lead-email']) && $_GET['lead-email'] != '')
				$query->query_vars['meta_value'] = $_GET['lead-email'];
			}
	}

	// Redirect clicks from lead emails to lead profiles.
	add_action('admin_init', 'wp_lead_redirect_with_email');
	function wp_lead_redirect_with_email() {
		global $wpdb;
		if (is_admin() && isset($_GET['lead-email-redirect']) && $_GET['lead-email-redirect'] != '') {

			$lead_id = 	$_GET['lead-email-redirect'];
			$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
				$lead_id
			);

			$wpdb->query( $query );

			if ( $wpdb->num_rows ) {
				$lead_ID = $wpdb->get_var( $query );
				$url = admin_url();
				$redirect = $url . 'post.php?post='. $lead_ID . '&action=edit';
				wp_redirect( $redirect, 301 ); exit;
			}
		}
	}

	function wpl_nice_field_names() {
		$nice_names = array(
			"wpleads_company_name" => "Company Name",
			"wpleads_city" => "City",
			"wpleads_areaCode" => "Area Code",
			"wpleads_country_name" => "Country Name",
			"wpleads_region_code" => "State Abbreviation",
			"wpleads_region_name" => "State Name",
			"wp_lead_status" => "Lead Status",
			"events_triggered" => "Number of Events Triggered",
			"lp_page_views_count" => "Page View Count",
			"wpleads_conversion_count" => "Number of Conversions"
		);

		$nice_names = apply_filters('wpleads_sort_by_custom_field_nice_names',$nice_names);
		return $nice_names;
	}

	add_action( 'restrict_manage_posts', 'wpl_admin_posts_filter_restrict_manage_posts' );
	function wpl_admin_posts_filter_restrict_manage_posts() {
		global $wpdb;
		$screen = get_current_screen();
		$screen_id = $screen->id;
		if ( $screen_id=='edit-wp-lead') {


			$query = "
				SELECT DISTINCT($wpdb->postmeta.meta_key)
				FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta
				ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE $wpdb->posts.post_type = '%s'
				AND $wpdb->postmeta.meta_key != ''
				AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
				AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
			";

			$fields = $wpdb->get_col($wpdb->prepare($query, 'wp-lead'));
			//print_r($fields);
			// $fields = $wpdb->get_results($sql, ARRAY_N);
			?>
				<select name="wp_leads_filter_field" id="lead-meta-filter">
				<option value="" class='lead-meta-empty'><?php _e('Filter By Custom Fields', 'baapf'); ?></option>
				<?php
					$current = isset($_GET['wp_leads_filter_field'])? $_GET['wp_leads_filter_field']:'';
					$current_v = isset($_GET['wp_leads_filter_field_val'])? $_GET['wp_leads_filter_field_val']:'';


					$nice_names = wpl_nice_field_names();

					foreach ($fields as $field) {
						//echo $field;
						if (array_key_exists($field, $nice_names)) {
							$label = $nice_names[$field];
							echo "<option value='$field' ".selected( $current, $field ).">$label</option>";
						}

					}
				?>
				</select><span class='lead_meta_val'><?php _e('Value:', 'baapf'); ?></span><input type="TEXT" name="wp_leads_filter_field_val" class="lead_meta_val" placeholder="Leave Blank to Search All" value="<?php echo $current_v; ?>" />
				<?php
		}
}

// Make these columns sortable
add_filter( "manage_edit-wp-lead_sortable_columns", "wpleads_sortable_columns" );
function wpleads_sortable_columns($columns) {

	$columns['first-name'] = 'first-name';
	$columns['last-name'] = 'last-name';
	$columns['status'] = 'status';
	$columns['company'] = 'company';
	if (isset($_GET['wp_leads_filter_field'])) {
		$the_val = $_GET['wp_leads_filter_field'];
		$columns['custom'] = $the_val;
	}
	return $columns;
}

add_filter( 'post_row_actions', 'wpleads_remove_row_actions', 10, 2 );
function wpleads_remove_row_actions( $actions, $post ) {
	if( $post->post_type == 'wp-lead' && isset($actions['edit']) )
	{
		$actions['edit'] = str_replace('Edit','View',$actions['edit']);
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}

// Mark lead as viewed
add_action( 'wp_ajax_nopriv_wp_leads_mark_as_read_save', 'wp_leads_mark_as_read_save' );
add_action( 'wp_ajax_wp_leads_mark_as_read_save', 'wp_leads_mark_as_read_save' );
function wp_leads_mark_as_read_save()
{
	global $wpdb;
	//echo "here";
	// Grab form values
	$newrules = $_POST['j_rules'];
	//echo $newrules;

	$post_id = mysql_real_escape_string($_POST['page_id']);

	add_post_meta( $post_id, 'wp_lead_status', 'Read', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
	header('HTTP/1.1 200 OK');
}

// Undo mark lead as viewed
add_action( 'wp_ajax_nopriv_wp_leads_mark_as_read_undo', 'wp_leads_mark_as_read_undo' );
add_action( 'wp_ajax_wp_leads_mark_as_read_undo', 'wp_leads_mark_as_read_undo' );
function wp_leads_mark_as_read_undo()
{
	global $wpdb;
	//echo "here";
	// Grab form values
	$newrules = "New Lead";
	//echo $newrules;

	$post_id = mysql_real_escape_string($_POST['page_id']);

	add_post_meta( $post_id, 'wp_lead_status', 'New Lead', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
	header('HTTP/1.1 200 OK');
}

// Ajax Save Raw Form Field Mapping
add_action( 'wp_ajax_nopriv_wp_leads_raw_form_map_save', 'wp_leads_raw_form_map_save' );
add_action( 'wp_ajax_wp_leads_raw_form_map_save', 'wp_leads_raw_form_map_save' );
function wp_leads_raw_form_map_save()
{
	global $wpdb;
	if ( !wp_verify_nonce( $_POST['nonce'], "wp-lead-map-nonce")) {
		 exit("Wrong nonce");
	}
	// Grab form values
	$mapped_field = $_POST['mapped_field'];
	$meta_val = $_POST['meta_val'];
	$post_id = mysql_real_escape_string($_POST['page_id']);

	add_post_meta( $post_id, $meta_val, $mapped_field, true ) or update_post_meta( $post_id, $meta_val, $mapped_field );
	header('HTTP/1.1 200 OK');
}

// Ajax Auto mark lead as read on first page view
add_action( 'wp_ajax_nopriv_wp_leads_auto_mark_as_read', 'wp_leads_auto_mark_as_read' );
add_action( 'wp_ajax_wp_leads_auto_mark_as_read', 'wp_leads_auto_mark_as_read' );
function wp_leads_auto_mark_as_read(){
	global $wpdb;

	//$mapped_field = $_POST['mapped_field'];
	$newrules = "Read";
	$post_id = mysql_real_escape_string($_POST['page_id']);

	add_post_meta( $post_id, 'wp_lead_status', 'Read', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
	header('HTTP/1.1 200 OK');
}


add_action('admin_footer-edit.php', 'wpleads_bulk_admin_footer');
function wpleads_bulk_admin_footer() {
  global $post_type;
  if($post_type == 'wp-lead') {


	$lists = wpleads_get_lead_lists_as_array();

	$html = "<select id='wordpress_list_select' name='action_wordpress_list_id'>";
	foreach ( $lists as $id => $label  )
	{
		$html .= "<option value='".$id."'>".$label."</option>";
	}
	$html .="</select>";


	?>
	<script type="text/javascript">
	  jQuery(document).ready(function() {

		jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List','lp') ?>').appendTo("select[name='action']");
		jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List' , 'lp') ?>').appendTo("select[name='action2']");

		jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action']");
		jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action2']");

		jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action']");
		jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action2']");

		jQuery(document).on('change','select[name=action]', function() {
			var this_id = jQuery(this).val();
			if (this_id.indexOf("export-csv") >= 0)
			{
				jQuery('#posts-filter').prop('target','_blank');
			}
			else if (this_id.indexOf("export-xml") >= 0)
			{
				jQuery('#posts-filter').prop('target','_blank');
			}
			else if (this_id.indexOf("add-to-list") >= 0)
			{
				var html  = "<?php echo $html; ?>";

				jQuery("select[name='action']").after(html);
			}
			else
			{
				jQuery('#posts-filter').prop('target','self');
				jQuery('#wordpress_list_select').remove();
			}
		});

	  });
	</script>
	<?php
  }
}

add_action('load-edit.php', 'wpleads_bulk_action');

function wpleads_bulk_action() {
	// ...
	if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post'])) {

		global $Inbound_Leads;

		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();


		if ( !current_user_can('manage_options') ) {
			die();
		}

		$post_ids = array_map('intval', $_REQUEST['post']);

		switch($action) {
			case 'export-csv':
				$exported = 0;

				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header('Content-Description: File Transfer');
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=leads-export-csv-".date("m.d.y").".csv");
				header("Expires: 0");
				header("Pragma: public");

				$fh = @fopen( 'php://output', 'w' );

				//get all keys
				foreach( $post_ids as $post_id ) {
					$this_lead_data = get_post_custom($post_id);

					foreach ($this_lead_data as $key => $val) {
						$lead_meta_pairs[$key] = $key;
					}
				}

				// Add a header row if it hasn't been added yet
				fputcsv($fh, array_keys($lead_meta_pairs));
				$headerDisplayed = true;



				foreach( $post_ids as $post_id ) {
					unset($this_row_data);

					$this_lead_data = get_post_custom($post_id);


					foreach ($lead_meta_pairs as $key => $val) {

						if (isset($this_lead_data[$key])) {
							$val = $this_lead_data[$key];
							if (is_array($val))
								$val = implode(';',$val);
						} else {
							$val = "";
						}

						$this_row_data[$key] = 	$val;
					}

					fputcsv($fh, $this_row_data);
					$exported++;
				}
				// Close the file
				fclose($fh);

				// build the redirect url
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );

				// Make sure nothing else is sent, our file is done
				exit;
			break;
			case 'export-xml':
				echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
				foreach( $post_ids as $post_id ) {
					$this_lead_data = get_post_custom($post_id);

					foreach ($this_lead_data as $key => $val) {

						if (is_array($val)) {
							$this_lead_data[$key] = implode(',',$val);
						}
					}

					unset($this_lead_data['_edit_lock']);
					unset($this_lead_data['_yoast_wpseo_linkdex']);

					$xml = wpleads_generate_valid_xml_from_array($this_lead_data);
					echo $xml;
				}
				// Make sure nothing else is sent, our file is done
				exit;

				// build the redirect url
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
			break;
			case 'export-list':
				$list_id = $_REQUEST['action_wordpress_list_id'];
				$exported = 0;

				foreach( $post_ids as $post_id ) {

					$list_cpt = get_post($list_id , ARRAY_A);
					$list_slug = $list_cpt['post_name'];
					$list_title = $list_cpt['post_title'];

					$wplead_cat = get_term_by( 'slug', $list_slug ,'wplead_list_category'  );
					$wplead_cat_id = $wplead_cat->term_id;

					$exported++;
				}
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
			break;
			case 'add-to-list':
				$list_id = $_REQUEST['action_wordpress_list_id'];
				$added = 0;

				foreach( $post_ids as $post_id ) {

					$list_cpt = get_post($list_id , ARRAY_A);
					$list_slug = $list_cpt['post_name'];
					$list_title = $list_cpt['post_title'];

					wpleads_add_lead_to_list($list_id, $post_id, $add = true);
					$added++;
				}
				$sendback = add_query_arg( array('added' => $added , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
			break;
			default: return;
		}

		// 4. Redirect client
		wp_redirect($sendback);
		exit();
	}
}

add_action('admin_notices', 'wpleads_bulk_admin_notices');
function wpleads_bulk_admin_notices() {
	global $post_type, $pagenow;
	if($pagenow == 'edit.php' && $post_type == 'wp-lead' && isset($_REQUEST['exported']) && (int) $_REQUEST['exported']) {
		$message = sprintf( _n( 'Lead exported.', '%s lead exported.', $_REQUEST['exported'] ), number_format_i18n( $_REQUEST['exported'] ) );
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}
	if($pagenow == 'edit.php' && $post_type == 'wp-lead' &&  isset($_REQUEST['added']) && (int) $_REQUEST['added']) {
		$message = sprintf( _n( 'Lead Added.', '%s leads added to list.', $_REQUEST['added'] ), number_format_i18n( $_REQUEST['added'] ) );
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}
}

function wpleads_generate_xml_from_array($array, $node_name) {
	$xml = '';

	if (is_array($array) || is_object($array)) {
		foreach ($array as $key=>$value) {
			if (is_numeric($key)) {
				$key = $node_name;
			}

			$xml .= '		<' . $key . '>' . "\n			" . wpleads_generate_xml_from_array($value, $node_name) . '		</' . $key . '>' . "\n";
		}
	} else {
		$xml = htmlspecialchars($array, ENT_QUOTES) . "\n";
	}

	return $xml;
}

function wpleads_generate_valid_xml_from_array($array, $node_block='lead_data', $node_name='lead_data') {

	$xml = "";
	$xml .= '	<' . $node_block . '>' . "\n";
	$xml .= "".wpleads_generate_xml_from_array($array, $node_name);
	$xml .= '	</' . $node_block . '>' . "\n";

	return $xml;
}

function wpleads_get_conversion_count ($lead_id) {
	$conversion_count = get_post_meta($post_id,'wpleads_conversion_count', true);
}
?>
