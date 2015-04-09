<?php

/*  Generate Lead Rule Processing Batch */
add_action('wp_ajax_automation_run_automation_on_all_leads', 'wpleads_lead_automation_build_queue');
add_action('wp_ajax_nopriv_automation_run_automation_on_all_leads', 'wpleads_lead_automation_build_queue');

function wpleads_lead_automation_build_queue() {
	global $wpdb;

	$automation_id = $_POST['automation_id'];
	$automation_queue = get_option( 'automation_queue');
	$automation_queue = json_decode( $automation_queue , true);

	if ( !is_array($automation_queue) ) {
		$automation_queue = array();
	}

	if ( !in_array( $automation_id , $automation_queue ) ) {
		/* get all lead ids */
		$sql = "SELECT distinct(ID) FROM {$wpdb->prefix}posts WHERE post_status='publish'  AND post_type = 'wp-lead' ";
		$result = mysql_query($sql);

		$batch = 1;
		$row = 0;

		while ($lead = mysql_fetch_array($result))
		{
			if ($row>1000)
			{
				$batch++;
				$row=0;
			}

			$automation_queue[$automation_id][$batch][] = $lead['ID'];

			$row++;
		}
	}

	$automation_queue = json_encode( $automation_queue);
	update_option( 'automation_queue' , $automation_queue);

	var_dump($automation_queue);
	die();
}

/* Grab all lead data and return to localstorage*/
add_action('wp_ajax_inbound_get_all_lead_data', 'inbound_get_all_lead_data');
add_action('wp_ajax_nopriv_inbound_get_all_lead_data', 'inbound_get_all_lead_data');
function inbound_get_all_lead_data() {
	$wp_lead_id = $_POST['wp_lead_id'];
	if (isset($wp_lead_id) && is_numeric($wp_lead_id)) {
		global $wpdb;
		$data   =   array();
		$wpdb->query("
		  SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = ".mysql_real_escape_string($wp_lead_id)."
		");

		foreach($wpdb->last_result as $k => $v) {
			$data[$v->meta_key] =   $v->meta_value;
		};

		echo json_encode($data,JSON_FORCE_OBJECT);
		wp_die();
	}
}

/* delete from list - lead management */
add_action('wp_ajax_leads_delete_from_list', 'leads_delete_from_list');
add_action('wp_ajax_nopriv_leads_delete_from_list', 'leads_delete_from_list');
function leads_delete_from_list() {
	//check_ajax_referer('leads_ajax_load_more_leads');

	$lead_id = (isset($_POST['lead_id'])) ? $_POST['lead_id'] : '';
	$list_id = (isset($_POST['list_id'])) ? $_POST['list_id'] : '';

	$id = $lead_id;
	// $cats = wp_get_post_terms($id, "wplead_list_category_action"); // gets all cats

	$current_terms = wp_get_post_terms( $id, 'wplead_list_category', 'id' );
	$current_terms_count = count($terms);
	//print_r($current_terms);
	$all_remove_terms = '';
	foreach ($current_terms as $term) {
		$add = $term->term_id;
		$all_remove_terms .= $add . ' ,';
	}
	$final = explode(' ,', $all_remove_terms);

	$final = array_filter($final, 'strlen');

	//$cats = wp_get_post_categories($id);
	if (in_array($list_id, $final)) {
		$new = array_flip ( $final );
		unset($new[$list_id]);
		$save = array_flip ( $new );
		wp_set_object_terms( $id, $save, 'wplead_list_category');
	}


}

/* load more leads - lead management */
add_action('wp_ajax_leads_ajax_load_more_leads', 'leads_ajax_load_more_leads');           // for logged in user
add_action('wp_ajax_nopriv_leads_ajax_load_more_leads', 'leads_ajax_load_more_leads');

function leads_ajax_load_more_leads() {
	//check_ajax_referer('leads_ajax_load_more_leads');

	$order = (isset($_POST['order'])) ? $_POST['order'] : 'DESC';
	$orderby = (isset($_POST['orderby'])) ? $_POST['orderby'] : 'date';
	$cat = (isset($_POST['cat'])) ? $_POST['cat'] : '';
	$tag = (isset($_POST['tag'])) ? $_POST['tag'] : '';
	$paged = (isset($_POST['pull_page'])) ? $_POST['pull_page'] : "";
	$relation = (isset($_POST['relation'])) ? $_POST['relation'] : "AND";
    $args = array(
    	'post_type' => 'wp-lead',
    	'order' => strtoupper($order),
    	'orderby' => $orderby,
    	'posts_per_page' => 60,

    );
    // fix the bullshit

    // magic fix http://wordpress.stackexchange.com/questions/96584/how-do-i-filter-posts-by-taxomony-using-ajax
   if ( $cat != 'all') {
   		/* OLD Tax setup
  		//$args['term'] = $cat;
    	/*$args['tax_query'] = array(
    							array(
    								'taxonomy' => 'wplead_list_category',
    								'field' => 'id',
    								'terms' => $cat,
    								'operator' => 'IN'
    							)
    						);
    	end OLD Tax setup */
		$tax_query = array( 'relation' => $relation );
		$new_cat = str_replace("%2C", ",", $cat);
		$taxonomy_array = explode(",", $new_cat); // fix array posted
		//$args['term'] = $taxonomy_array;
		//
		//echo json_encode($taxonomy_array,JSON_FORCE_OBJECT);
		//wp_die();
		$loop_count_two = 0;
		foreach($taxonomy_array as $taxonomy_array_value => $test)
		{

		        $tax_query[] = array(
		        'taxonomy' => 'wplead_list_category',
		        'field'    => 'id',
		        'terms'    => $test,
		        'operator' => 'IN'

		    );

		}

	    $args['tax_query'] = $tax_query;
    }
    //echo json_encode($args,JSON_FORCE_OBJECT);
    //wp_die();
    $term_id = $cat;
	/*
	$args = array(
   'post_type' => 'wp-lead',
   'term' => 54,
   'posts_per_page' => -1,
   'order' => 'DESC',
   'tax_query' => array(
	                 array(
	                     'taxonomy' => 'wplead_list_category',
	                     'field'    => 'id',
	                     'terms'    => 54,
	                     'operator' => 'IN'
	                     ),
     		)
    ); */

    // Add tag to query
    if (isset($tag) && $tag != "" ){
    	//$args['tag'] = $_POST['tag'];
    }
    if (isset($paged) && $paged != "" ){
    	$args['paged'] = $paged;
    }

    $output =  $args;
  	//echo json_encode($output,JSON_FORCE_OBJECT);
    //wp_die();

    $query = new WP_Query( $args );
    $posts = $query->posts;
    $i = 0;

    $loop_page = $paged - 1;

    $loop_count = $loop_page * 60;
    $loop_count = $loop_count + 1;
	foreach ( $posts as $post ) {

		//$categories = wp_get_post_categories($post->ID);
		$this_tax = "wplead_list_category";


		$terms = wp_get_post_terms( $post->ID, $this_tax, 'id' );
		$cats = '';
		$lead_ID = $post->ID;
     	foreach ( $terms as $term ) {
		  	$term_link = get_term_link( $term, $this_tax );
		    if( is_wp_error( $term_link ) )
		        continue;
		    //We successfully got a link. Print it out.
		    $cats .= '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the '.$term->name.' list" class="remove-from-list" data-lead-id="'.$lead_ID.'" data-list-id="'.$term->term_id.'"></i></span> ';
		}

		$_tags = wp_get_post_tags($post->ID);
		$tags = '';
		foreach ( $_tags as $tag ) {
			$tags .= "<a href='?page=lead_management&t=$tag->slug'>$tag->name</a>, ";
		}
		$tags = substr($tags, 0, strlen($tags) - 2);
		if ( empty ($tags) ) {
			$tags = 'No Tags';
		}
		$alt_class = ($i%2 == 0) ? ' class="alternate"' : '' ;

		echo '<tr'.$alt_class.'>
					<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>
					<td class="count-sort"><span>'.$loop_count.'</span></td>
					<td>

		';
		$i++;
		if ( '0000-00-00 00:00:00' == $post->post_date ) {

		} else {

		 echo date(__('Y/m/d'), strtotime($post->post_date));

		}

		echo '</td>
					<td><span class="lead-email">' . $post->post_title . '</span></td>
					<td>' . $cats . '</td>
					<td>' . $tags . '</td>

					<td><a class="thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">View</a></td>

					<td>' . $post->ID . '</td>
				</tr>
		';
		$loop_count++;
	}

}
