<?php

/*
TODO:
- Get multiple list query working.
- Fix the actionat the bottom and jquery


 */
define('BATCH_PATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ) );

function lead_dropdown_generator() {
global $wpdb;

	$post_type = 'wp-lead';
	$query = "
		SELECT DISTINCT($wpdb->postmeta.meta_key)
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'wp-lead'
		AND $wpdb->postmeta.meta_key != ''
		AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
		AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
	";
	$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta;
	$fields = $wpdb->get_col($wpdb->prepare($query, $post_type));

	?>
	<select multiple name="wp_leads_filter_field" id="lead-meta-filter">

	<?php
		$current = isset($_REQUEST['wp_leads_filter_field'])? $_REQUEST['wp_leads_filter_field']:'';
		$current_v = isset($_REQUEST['wp_leads_filter_field_val'])? $_REQUEST['wp_leads_filter_field_val']:'';

		$nice_names = array(
			"wpleads_first_name" => "First Name",
			"wpleads_last_name" => "Last Name",
			"wpleads_email_address" => "Email Address",
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

		foreach ($fields as $field) {
			//echo $field;
			if (array_key_exists($field, $nice_names)) {
				$label = $nice_names[$field];
				echo "<option value='$field' ".selected( $current, $field ).">$label</option>";
			}

		}
	?>
	</select>
	<style type="text/css" media="screen">
	/*<![CDATA[*/
		.select2-container {
			width: 50%;
			padding-top: 15px;
		}
	/*]]>*/
	</style>
	<?php
}


function test_query(){

}

function leads_count_posts_in_term($taxonomy, $term, $type="post"){
	global $wpdb;

	$query = "
	SELECT COUNT( DISTINCT cat_posts.ID ) AS post_count
	FROM $wpdb->term_taxonomy AS cat_term_taxonomy INNER JOIN $wpdb->terms AS cat_terms ON
	cat_term_taxonomy.term_id = cat_terms.term_id
	INNER JOIN $wpdb->term_relationships AS cat_term_relationships
	ON cat_term_taxonomy.term_taxonomy_id = cat_term_relationships.term_taxonomy_id
	INNER JOIN $wpdb->posts AS cat_posts
	ON cat_term_relationships.object_id = cat_posts.ID
	WHERE cat_posts.post_status = 'publish'
	AND cat_posts.post_type = '".$type."'
	AND cat_term_taxonomy.taxonomy = '".$taxonomy."'
	AND cat_terms.slug IN ('".$term."')
	";

	return $wpdb->get_var($query);
}

function lead_select_taxonomy_dropdown($taxonomy, $select_type = 'multiple', $custom_class = '[]') {
	$type = ($select_type === 'multiple') ? 'multiple' : '';
	$id = 'cat';
	if ($select_type == 'single'){
		$id = 'bottom-cat';
	}
	?>
	<select <?php echo $type;?> name="wplead_list_category<?php echo $custom_class;?>" id="<?php echo $id;?>" class="postform <?php echo $custom_class;?>">
	<?php

	(isset($_REQUEST['wplead_list_category']) && $_REQUEST['wplead_list_category'][0] === 'all' ) ? $all_selected = 'selected="selected"' : $all_selected = '';

	if ($select_type != 'single')
	{
		?>
		<option class="" value="all" <?php echo $all_selected;?>><?php _e( 'All Leads in Database' , 'leads' ); ?></option>
		<?php
	}

	$args = array(
	    'hide_empty' => false,
	);

	$terms = get_terms($taxonomy, $args);
	if (isset($_REQUEST['wplead_list_category'])) {
		$list_array = $_REQUEST['wplead_list_category'];
	}

	foreach ($terms as $term)
	{
		$count = leads_count_posts_in_term('wplead_list_category', $term->slug, 'wp-lead');
		$selected = (isset($_REQUEST['wplead_list_category']) && in_array($term->term_id, $list_array) ) ? 'selected="selected"' : '';

		echo '<option class="" value="'.$term->term_id.'" '.$selected.'>'. $term->name.' ('.$count.')</option>';
	}
	echo '</select>';

}



add_action('admin_enqueue_scripts', 'lead_management_js');
function lead_management_js() {
		$screen = get_current_screen();

		if ( $screen->id != 'wp-lead_page_lead_management')
		        return; // exit if incorrect screen id
		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		wp_enqueue_script('selectjs', WPL_URLPATH . '/shared/assets/js/admin/select2.min.js');
		wp_enqueue_style('selectjs', WPL_URLPATH . '/shared/assets/css/admin/select2.css');
		wp_enqueue_script('tablesort', WPL_URLPATH . '/js/management/tablesort.min.js');

		wp_enqueue_script('light-table-filter', WPL_URLPATH . '/js/management/light-table-filter.min.js');
		wp_register_script( 'modernizr', WPL_URLPATH . '/js/management/modernizr.custom.js' );
		wp_enqueue_script( 'modernizr' );
		wp_enqueue_script('tablesort', WPL_URLPATH . '/js/management/tablesort.min.js');
		wp_enqueue_script('jquery-dropdown', WPL_URLPATH . '/js/management/jquery.dropdown.js');
		wp_enqueue_script('bulk-manage-leads', WPL_URLPATH . '/js/management/admin.js');
		wp_localize_script( 'bulk-manage-leads' , 'bulk_manage_leads', array( 'admin_url' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_script('jqueryui');
		wp_enqueue_script('jquery-ui-selectable'); // FINSIH THIS http://jqueryui.com/selectable/
		wp_enqueue_style('wpleads-list-css', WPL_URLPATH.'/css/admin-management.css');
		wp_admin_css('thickbox');
		add_thickbox();
}

function lead_management_admin_screen() {
	global $wpdb;
	Inbound_Compatibility::inbound_compatibilities_mode(); // Load only our scripts
	if (isset($_REQUEST['testthis'])) {
       test_query();
	}

	// Maybe make this an option some time
	$per_page = 60;
	$paged = empty($_REQUEST['paged']) ? 1 : intval($_REQUEST['paged']);

	$orderbys = array(
		'Date First Created'   => 'date',
		'Date Last Modified' => 'modified',
		'Alphabetical Sort'         => 'title',
		'Status'        => 'post_status'
	);
	$orderbys_flip = array_flip($orderbys);

	// Sorting
	$orderby = '';
	if (isset($_REQUEST['orderby'])) {
		$orderby = $_REQUEST['orderby'];
	}
	$order = "";
	if (isset($_REQUEST['order'])) {
		$order = strtoupper($_REQUEST['order']);
	}



	$_POST = stripslashes_deep($_POST);
	$_REQUEST = stripslashes_deep($_REQUEST);

	$posts = array();


	if (isset($_REQUEST['num'])) {
		$num = intval($_REQUEST['num']);
	} else {
		$num = 0;
	}

	if (isset($_REQUEST['what'])) {
	$what = htmlentities($_REQUEST['what']);
	} else {
		$what = "";
	}
	if (isset($_REQUEST['on'])) {
	$on = htmlentities($_REQUEST['on']);
	} else {
	$on = "";
	}

	$message = '';
	// Deal with any update messages we might have:
	if (isset($_REQUEST['done'])) {
		switch ( $_REQUEST['done'] ) {
			case 'add':
				$message = sprintf(__("Added %d posts to the list &ldquo;$what&rdquo;." , 'leads' ) , $num );
				break;
			case 'remove':
				$message = sprintf(_("Removed %d posts from the list &ldquo;$what&rdquo;." , 'leads' ) , $num );
				break;
			case 'tag':
				$message = sprintf(__("Tagged %d posts with &ldquo;$what&rdquo; on $on." , 'leads' ) , $num );
				break;
			case 'untag':
				$message = sprintf(__("Untagged %d posts with &ldquo;$what&rdquo;." , 'leads' ) , $num );
				break;
			case 'delete_leads':
				$message = sprintf(__("%d  leads permanently deleted" , 'leads' ) , $num );
				break;

		}
	}

	if ( !empty($message) ) {
		echo "<div id='message' class='updated fade'><p><strong>".$message."</strong></p></div>";
		echo '<style type="text/css">
		#display-lead-count, .starter-text, #filters{display:none !important;}
		</style>';
	}
	$filter = ""; // default
	// Create the hidden input which passes our current filter to the action script.
	if ( !empty($_REQUEST['wplead_list_category']) )
		$filter = '<input id="hidden-cat" type="hidden" name="cat_two" value="' . urlencode(implode(',', $_REQUEST['wplead_list_category'])) . '" />';
	if ( isset($_REQUEST['s']) && !empty($_REQUEST['s']) )
		$filter = '<input type="hidden" name="s" value="' . urlencode($_REQUEST['s']) . '" />';
	if ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) )
		$filter = '<input type="hidden" name="t" value="' . urlencode($_REQUEST['t']) . '" />';

	echo '<div class="wrap">
			<h2>'. __('Lead Management - Bulk Update Leads' , 'leads') .'</h2>';

	if ( empty($_REQUEST['wplead_list_category']) && empty($_REQUEST['s']) && empty($_REQUEST['t']) ){
		echo '<p class="starter-text">'. __('To get started, select the lead criteria below to see all matching leads.' , 'leads' ) .'</p>';
	}

	$page_output = (isset($_REQUEST['paged'])) ? $_REQUEST['paged'] : '1';
	echo "<div id='paged-current'>" . $page_output . "</div>";
	// Filtering

	echo '
	<div id="filters" class="inbound-lead-filters">
		<form id="lead-management-form" method="get" action="edit.php">
		<input type="hidden" name="page" value="lead_management" />
		<input type="hidden" name="post_type" value="wp-lead" />
	';
	// Category drop-down
	echo '<div id="top-filters"><div  id="inbound-lead-lists-dropdown">
		<label for="cat">'. __('Select Lead List(s):' , 'leads' ) .'</label>';
		//wp_lead_lists_dropdown();
		lead_select_taxonomy_dropdown( 'wplead_list_category' );
	echo '</div>';

	if (isset($_REQUEST['relation'])) {
		$relation  = $_REQUEST['relation'];
	} else {
		$relation = 'AND';
	}
	echo '
		<div id="and-or-params">
		<label for="orderby">'.__('Match:', 'leads' ).'</label>
			<select name="relation" id="relation">

						<option value="AND"' . ( $relation == 'AND' ? ' selected="selected"' : '' ) . '>'. __('(ONLY) Leads that are in <u>ALL</u> of the selected lists' , 'leads' ) .'</option>
						<option value="OR"' . ( $relation == 'OR' ? ' selected="selected"' : '' ) . '>'. __('(ANY) Leads in at least 1 of the selected Lists' , 'leads' ) .'</option>

			</select>
		</div></div>';
	// Sorting
	echo '<div id="bottom-filters">
		<div class="filter" id="lead-sort-by">
			<label for="orderby">'. __( 'Sort by:' , 'leads' ) .'</label>
			<select name="orderby" id="orderby">
	';
	foreach ( $orderbys as $title => $value ) {
		$selected = ( $orderby == $value ) ? ' selected="selected"' : '';
		echo "<option value='$value'$selected>$title</option>\n";
	}
	echo '
			</select>
			<select name="order" id="order">
			<option value="asc"' . ( $order == 'ASC' ? ' selected="selected"' : '' ) . '>Asc.</option>
			<option value="desc"' . ( $order == 'DESC' ? ' selected="selected"' : '' ) . '>Desc.</option>
			</select>
		</div>
	';


	if (isset($_REQUEST['s'])) {
		$s  = $_REQUEST['s'];
	} else {
		$s = '';
	}

	// ...then the keyword search.
	echo '
		<div class="filter" style="display:none;">
			<label for="s">'. __('Keyword:' , 'leads') .'</label>
			<input type="text" name="s" id="s" value="' . htmlentities($s) . '" title="'.__('Use % for wildcards.' , 'leads') .'" />
		</div>
	';

	if (isset($_REQUEST['t'])) {
		$t  = $_REQUEST['t'];
	} else {
		$t = '';
	}
	// ...then the tag filter.
	echo '
		<div class="filter" id="lead-tag-filter">
			<label for="s">Tag:</label>
			<input type="text" name="t" id="t" value="' . htmlentities($t) . '" title="\'foo, bar\': posts tagged with \'foo\' or \'bar\'. \'foo+bar\': posts tagged with both \'foo\' and \'bar\'" />
		</div>
	';

	echo '
		<div class="filter">
			<input type="submit" class="button-primary" value="'.__('Search Leads' , 'leads') .'" name="submit" />
		</div>';

	echo '</div>
	</form>';


	// Fetch our posts.

	if ( !empty($_REQUEST['wplead_list_category']) || !empty($_REQUEST['s']) || !empty($_REQUEST['t']) || !empty($_REQUEST['on']) ) {
		// A cat has been given; fetch posts that are in that category.
		$q = "paged=$paged&posts_per_page=$per_page&orderby=$orderby&order=$order&post_type=wp-lead";
		if ( !empty($_REQUEST['wplead_list_category']) ) {
			$cat = $_REQUEST['wplead_list_category'];
			$prefix = '';
			$final_cats = "";
			foreach ($cat as $key => $value) {
			    $final_cats .= $prefix . $value;
			    $prefix = ', ';
			}

		}


		//print_r($final_cats); exit;
		// A keyword has been given; get posts whose content contains that keyword.
		if ( !empty($_REQUEST['s']) ) {
			$q .= "&s=" . urlencode($_REQUEST['s']);
		}

		// A tag has been given; get posts tagged with that tag.
		if ( !empty($_REQUEST['t']) ) {
			$t = preg_replace('#[^a-z0-9\-\,\+]*#i', '', $_REQUEST['t']);
			$q .= "&tag=$t";
		}


		//$query = new WP_Query;
		//$posts = $query->query($q);

		$args = array(
			'post_type' => 'wp-lead',
			'order' => $order,
			'orderby' => $orderby,
			'posts_per_page' => $per_page,
		);
		// if finished show results
		if (isset($_REQUEST['on'])){
			$on_val = explode(",", $on);
			$prefix = '';
			$final_on = "";
			foreach ($on_val as $key => $value) {
			    $final_on .= $prefix . $value;
			    $prefix = ', ';
			}
			$args['post__in'] = $on_val;
			$args['order'] = 'DESC';
			$args['orderby'] = 'date';
			//$args['posts_per_page'] = -1;

		}


		if ((isset($_REQUEST['wplead_list_category'])) && $_REQUEST['wplead_list_category'][0] != "all" ){
			/*$args['tax_query'] = array(
							'relation' => 'AND',
									array(
										'taxonomy' => 'wplead_list_category',
										'field' => 'id',
										'terms' => array( $final_cats ),
									)
								); */
				/* Dynamic tax query */
				$tax_query = array( 'relation' => $relation );
				$taxonomy_array = $_REQUEST['wplead_list_category'];
				foreach($taxonomy_array as $taxonomy_array_value){

				        $tax_query[] = array(
				        'taxonomy' => 'wplead_list_category',
				        'field'    => 'id',
				        'terms'    => array($taxonomy_array_value)

				    );

				}
			    $args['tax_query'] = $tax_query;
		}

	   // echo "<pre>";
	  /* print_r($args);
	   echo "<br><br>";
	   print_r($arg_s); exit;*/
		// Add tag to query
		if ((isset($_REQUEST['t'])) && $_REQUEST['t'] != "" ){
			$args['tag'] = $_REQUEST['t'];
		}
		if ((isset($_REQUEST['paged'])) && $_REQUEST['paged'] != "1" ){
			$args['paged'] = $paged;
		}

		$query = new WP_Query( $args );
		$posts = $query->posts;
		//	print_r($posts); exit;
		// Pagination
		$pagination = '';
		if ( $query->max_num_pages > 1 )
		{
			$current = preg_replace('/&?paged=[0-9]+/i', '', strip_tags($_SERVER['REQUEST_URI'])); // I'll happily take suggestions on a better way to do this, but it's 3am so

			$pagination .= "<div class='tablenav-pages'>";

			if ( $paged > 1 ) {
				$prev = $paged - 1;
				$pagination .= "<a class='prev page-numbers' href='$current&amp;paged=$prev'>&laquo; ". __( 'Previous' , 'leads' )."</a>";
			}

			for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
				if ( $i == $paged ) {
					$pagination .= "<span class='page-numbers current'>$i</span>";
				} else {
					$pagination .= "<a class='page-numbers' href='$current&amp;paged=$i'>$i</a>";
				}
			}

			if ( $paged < $query->max_num_pages ) {
				$next = $paged + 1;
				$pagination .= "<a class='next page-numbers' href='$current&amp;paged=$next'>".__( 'Next' , 'leads' ) ." &raquo;</a>";
			}

			$pagination .= "</div>";
		}

		echo $pagination;
	}

	echo "</div>"; // tablenav
	//lead_dropdown_generator();
	// No posts have been fetched, let's tell the user:
	if ( empty($_REQUEST['wplead_list_category']) && empty($_REQUEST['s']) && empty($_REQUEST['t']) && !isset($_REQUEST['on']) )
	{
		echo '';
		// List all leads?
	}
	else
	{
		// Criteria were given, but no posts were matched.
		if ( empty($posts) ) {
			echo '
				<p>'. __('No posts matched that criteria, sorry! Try again with something different.' , 'leads' ) .'</p>
			';
		}
		// Criteria were given, posts were matched... let's go!
		else {
			$all_cats = (isset($_REQUEST['wplead_list_category'])) ? $_REQUEST['wplead_list_category'] : 0;
			$prefix = "";
			$name = "";
			if (isset($_REQUEST['wplead_list_category']) && $_REQUEST['wplead_list_category'][0] != 'all') {
				foreach ($all_cats as $key => $value) {
					$term = get_term( $_REQUEST['wplead_list_category'][$key], 'wplead_list_category' );
				    $name .= $prefix . $term->name;
				    $prefix = ' <span>and</span> ';
				}
			} else {
				$name = __( "Total" , 'leads' );
			}

			echo '
				<form method="post" id="man-table" action="'.admin_url( 'admin.php' ).'">
				<input type="hidden" name="action" value="lead_action" />
				<div id="posts">

				<table class="widefat" id="lead-manage-table">';
				if (!isset($_REQUEST['on'])){
				echo'<caption style="margin-top:0px;">' .
						sprintf(
							'<h2 class="found-text"><strong><span id="lead-total-found">%s</span></strong> Leads Found in <strong>%s</strong></h2><strong>Additional Search Criteria:</strong> tagged with <strong><u>%s</u></strong>, %s ordered by <strong><u>%s</u></strong> %s.',
							$query->found_posts,
							!empty($_REQUEST['wplead_list_category']) ? $name : __( 'any category' , 'leads') ,
							!empty($_REQUEST['t']) ? htmlentities($_REQUEST['t']) : __( 'any tag' , 'leads' ) ,
							!empty($_REQUEST['s']) ? __('containing the string' , 'leads' ).' <strong>' . htmlentities($_REQUEST['s']) . '</strong>, ' : '',
							strtolower($orderbys_flip[$orderby]),
							$order == 'asc' ? 'ascending' : 'descending'
						);
				} else {
					echo'<caption style="margin-top:0px;">';
				}
				echo '<div><input type="search" class="light-table-filter" data-table="widefat" placeholder="Filter Results Below" /><span id="search-icon"></span>
					<span style="float:right;margin-top: 19px;margin-right: 3px;" id="display-lead-count">
						<i class="lead-spinner"></i><span id="lead-count-text">'.__( 'Grabbing Matching Leads' , 'leads' ) .'</span></span></div>
				</caption>

					<thead>
						<tr>
							<th class="checkbox-header no-sort" scope="col"><input type="checkbox" id="toggle" title="Select all posts" /></th>
							<th class="count-sort-header" scope="col">#</th>
							<th scope="col">'. __( 'Date' , 'leads' ) .'</th>
							<th scope="col">'. __( 'Email' , 'leads' ) .'</th>
							<th scope="col">'. __( 'Current Lists' , 'leads' ) .'</th>
							<th scope="col">'. __( 'Current Tags' , 'leads' ) .'</th>
							<th scope="col" class="no-sort">'. __( 'View' , 'leads' ).'</th>
							<th scope="col">'. __( 'ID' , 'leads' ) .'</th>
						</tr>
					</thead>
					<tbody id="the-list">
			';
			$loop_count = 1;
			$i = 0;
			foreach ( (array) $posts as $post ) {

				//$categories = wp_get_post_categories($post->ID);

				$terms = wp_get_post_terms( $post->ID, 'wplead_list_category', 'id' );
				$cats = '';
				$lead_ID = $post->ID;
	         	foreach ( $terms as $term ) {
				  	$term_link = get_term_link( $term, 'wplead_list_category' );
				    if( is_wp_error( $term_link ) )
				        continue;
				    //We successfully got a link. Print it out.

				    $cats .= '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the '.$term->name.' list" class="remove-from-list" data-lead-id="'.$lead_ID.'" data-list-id="'.$term->term_id.'"></i></span> ';
				}


				$_tags = wp_get_post_terms( $post->ID, 'lead-tags', 'id' );
				$tags = '';
				foreach ( $_tags as $tag ) {
					$tags .= "<a title='Click to Edit Lead Tag Name' target='_blank' href='".admin_url('edit-tags.php?action=edit&taxonomy=lead-tags&tag_ID='.$tag->term_id.'&post_type=wp-lead')."'>$tag->name</a>, ";
				}
				$tags = substr($tags, 0, strlen($tags) - 2);
				if ( empty ($tags) ) {
					$tags = 'No Tags';
				}

				echo '
						<tr' . ( $i++ % 2 == 0  ? ' class="alternate"' : '' ) .'>
							<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>
							<td class="count-sort"><span>'.$loop_count.'</span></td>
							<td>

				';

				if ( '0000-00-00 00:00:00' == $post->post_date ) {
					_e('Unpublished');
				} else {

				  echo date(__('Y/m/d'), strtotime($post->post_date));

				}

				echo '</td>
							<td><span class="lead-email">' . $post->post_title . '</span></td>
							<td class="list-column-row">' . $cats . '</td>
							<td>' . $tags . '</td>

							<td><a class="thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">View</a></td>

							<td>' . $post->ID . '</td>
						</tr>
				';
				$loop_count++;
			}
			echo '
					</tbody>
				</table>
			';

			// Now, our actions.
			echo '
			<div id="all-actions" class="tablenav">

			<div id="inbound-lead-management"><span class="lead-actions-title">'. __( 'What do you want to do with the selected leads?' , 'leads' ) .'</span>

			<div id="controls">';
			lead_management_drop_down();
			echo '
			</div>
				' . $filter . '
				<div id="lead-action-triggers">


				<div class="action" id="lead-export">
					<input type="submit" class="manage-remove button-primary button" name="export_leads" value="'. __( 'Export Leads as CSV' , 'leads' ) .'" title="Exports selected leads into a CSV format." />

				</div>

				<div class="action" id="lead-update-lists">
					<label for="lead-update-lists" >Choose List:</label>';
					lead_select_taxonomy_dropdown( 'wplead_list_category', 'single', '_action' );
					echo '<input type="submit" class="button-primary button" name="add" value="'. __('Add to' , 'leads' ) .'" title="Add the selected posts to this category." />
					<input type="submit" class="manage-remove button-primary button" name="remove" value="'. __( 'Remove from' , 'leads' ) .'" title="Remove the selected posts from this category." />
				</div>

				<div class="action" id="lead-update-tags">
					<label for="lead-update-tags">Tags:</label>
					<input type="text" id="inbound-lead-tags-input" name="tags" placeholder="'. __( 'Separate multiple tags with commas. ' , 'leads' ) .'" title="Separate multiple tags with commas." />
					<input type="submit" name="replace_tags" class="manage-tag-replace button-primary button" value="'. __( 'Replace' , 'leads' ) .'" title="Replace the selected leads\' current tags with these ones. Warning this will delete current tags and replace them" />
					<input type="submit" name="tag" class="manage-tag-add button-primary button" value="'. __( 'Add' , 'leads' ) .'" title="Add tags to the selected leads without altering the leads\' existing tags." />
					<input type="submit" name="untag" class="manage-remove button-primary button" value="'. __( 'Remove' , 'leads' ) .'" title="Remove these tags from the selected leads." />
				</div>

				<div class="action" id="lead-update-meta">
					<label for="lead-update-meta">Meta:</label>
					<input type="text" name="meta_val" title="Separate multiple tags with commas." />
					<input type="submit" name="replace_meta" value="'. __( 'Replace' , 'leads' ) .'" title="Replace the selected posts\' current meta values with these ones." />
					<input type="submit" name="meta" value="'. __( 'Add' , 'leads' ) .'" title="Add these meta values to the selected posts without altering the posts\' existing tags." />
					<input type="submit" name="unmeta" value="'. __( 'Remove' , 'leads' ) .'" title="Remove these meta values from the selected posts." />
				</div>

				<div class="action" id="lead-delete">
					<label for="lead-delete" id="del-label"><span style="color:red;">Delete Selected Leads (Warning! There is no UNDO):</span></label>

					<input type="submit" class="manage-remove button-primary button" name="delete_leads" value="'. __( 'Permanently Delete Selected Leads' , 'leads' ) .'" title="This will delete the selected leads from your database. There is no undo." />

				</div>
				</div>

				' . $pagination . '
			</div>
			';

			wp_nonce_field('lead_management-edit');
			echo '
				</form>
				</div>
			';
		}
	}
}

//add_action('wp_lead_before_dashboard', 'marketing_dashboard_before_callback');

function lead_management_drop_down() {

	$url = get_option('siteurl');
	?>
	<section id="set-3">
		<div class="fleft">
			<select id="cd-dropdown" class="cd-select">
				<option value="-1" selected class="db-drop-label"><?php _e('Choose action to apply to selected leads' , 'leads' ); ?></option>
				<option value="lead-export"  class="action-symbol lead-export-symbol db-drop-label"><?php _e( 'Export Selected Leads as CSV' , 'leads' ); ?></option>
				<option value="lead-update-lists"  class="action-symbol lead-update-lists-symbol db-drop-label"><?php _e( 'Add or Remove Selected Leads from Lists' , 'leads' ); ?></option>
				<option value="lead-update-tags"  class="action-symbol lead-update-tags-symbol db-drop-label"><?php _e( 'Add or Remove Tags to Selected Leads' , 'leads' ); ?></option>
				<option value="lead-delete"  class="action-symbol lead-update-delete-symbol db-drop-label"><?php _e( 'Permanently Delete Selected Leads' , 'leads' ); ?></option>
				</select>
		</div>


	</section>
	<style type="text/css">

	</style>
	<script>
		jQuery(document).ready(function($) {
			$( function() {

						$( '#cd-dropdown' ).dropdown();

			});

			// bind change event to select
			jQuery("body").on('click', '.cd-dropdown li', function () {
				 var value = $(this).attr('data-value'); // get selected value
				 console.log(value);

				 if (value) { // require a URL
				  $(".action").hide();
				  $("#" + value).show();
			  }
			  return false;
			});
		});
	</script>
<?php
}

function add_lead_to_list_tax($lead_id, $list_id, $tax = 'wplead_list_category') {

	$current_lists = wp_get_post_terms( $lead_id, $tax, 'id' );

	$all_term_ids = array();
	$all_term_slugs = array();

	foreach ($current_lists as $term )
	{
		$add = $term->term_id;
		$slug = $term->slug;
		$all_term_ids[] = $add;
		$all_term_slugs[] = $slug;
	}

	$tag_check = strpos($list_id, ",");
	if ($tag_check !== false)
	{
		// Set terms for lead tags taxomony
		$list_array = explode(",", $list_id);
		if(is_array($list_array))
		{
			foreach ($list_array as $key => $value)
			{
				$trim = trim(strtolower($value));
				$add_slug = preg_replace('/\s+/', '-', $trim);
				if ( !in_array($add_slug, $all_term_slugs) ) {
					$all_term_slugs[] = $add_slug;
					wp_set_object_terms( $lead_id, $all_term_slugs, $tax);
				}
			}
		}
	}
	else
	{
		/* Set terms for list taxomony */
		if ( !in_array($list_id, $all_term_ids) ) {
			$all_term_ids[] = $list_id;
			wp_set_object_terms( $lead_id, $all_term_ids, $tax);
		}
	}
}

function remove_lead_from_list_tax($lead_id, $list_id,  $tax = 'wplead_list_category') {
	$current_terms = wp_get_post_terms( $lead_id, $tax, 'id' );

	$all_remove_terms = '';
	foreach ($current_terms as $term )
	{
		$add = $term->term_id;
		$all_remove_terms .= $add . ' ,';
	}

	$final = explode(' ,', $all_remove_terms);
	$final = array_filter($final, 'strlen');

	if (in_array($list_id, $final) )
	{
		$new = array_flip ( $final );
		unset($new[$list_id]);
		$save = array_flip ( $new );
		wp_set_object_terms( $lead_id, $save, $tax);
	}
}

add_action( 'edit_term', 'wp_leads_sync_lead_tag_slug', 10, 3 );
function wp_leads_sync_lead_tag_slug( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;
		//print_r($taxonomy); exit;

		$whitelist  = array( 'lead-tags' ); /* maybe this needs to include attachment, revision, feedback as well? */
		if ( !in_array( $taxonomy, $whitelist ) ) {
			return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );
		}

		$the_term = get_term_by( 'id', $term_id, $taxonomy );

		if ( $the_term && $_POST )
		{
			/* ran into some issues when define('WP_CACHE', true); is set */
			$slug = sanitize_title( filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING ), $the_term->term_id );
			$wpdb->update( $wpdb->terms, compact( 'slug' ), compact( 'term_id' ) );
		}

		return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );
}

add_action( 'admin_action_lead_action', 'lead_action_admin_action' );
function lead_action_admin_action() {
	global $Inbound_Leads;

	if ( !current_user_can('level_9') ){
		die ( __('User does not have admin level permissions.') );
	}

	$_POST = stripslashes_deep($_POST);
	$_REQUEST = stripslashes_deep($_REQUEST);

	// Check if we've been submitted a tag/remove.
	if ( !empty($_REQUEST['ids']) )
	{
		check_admin_referer('lead_management-edit');

		(is_array($_REQUEST['ids'])) ? $pass_ids = implode(',', $_REQUEST['ids']) : $pass_ids = $_REQUEST['ids'];

		$cat = intval($_REQUEST['wplead_list_category_action']);
		$num = count($_REQUEST['ids']);


		if ( !empty($_REQUEST['wplead_list_category_action']) ){
			$query = '&cat=' . $_REQUEST['wplead_list_category_action'];
		}

		if ( !empty($_REQUEST['s']) ) {
			$query = '&s=' . $_REQUEST['s'];
		}

		if ( !empty($_REQUEST['t']) ){
			$query = '&t=' . $_REQUEST['t'];
		}

		$term = get_term( $_REQUEST['wplead_list_category_action'], 'wplead_list_category' );
		$name = $term->slug;
		$this_tax = "wplead_list_category";

		/* We've been told to tag these posts with the given category. */
		if ( !empty($_REQUEST['add']) )
		{

			foreach ( $_REQUEST['ids'] as $id )
			{
				$fid = intval($id);
				$Inbound_Leads->add_lead_to_list( $fid, $cat ); // add to list
			}

			wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=add&what=" . $name . "&num=$num$query");
			die;
		}
		/* We've been told to remove these posts from the given category. */
		elseif ( !empty($_REQUEST['remove']) )
		{

			foreach ( (array) $_REQUEST['ids'] as $id )
			{
				$fid = intval($id);
				remove_lead_from_list_tax($fid, $cat);
			}

			wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=remove&what=" . $name . "&num=$num");
			die;
		}
		/* We've been told to tag these posts */
		elseif ( !empty($_REQUEST['tag']) || !empty($_REQUEST['replace_tags']) )
		{
			$tags = $_REQUEST['tags'];

			foreach ( (array) $_REQUEST['ids'] as $id )
			{
				$lead_ID = intval($id);
				$append = empty($_REQUEST['replace_tags']);
				$Inbound_Leads->add_tag_to_lead( $lead_ID , $tags );
			}
			wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=tag&what=$tags&num=$num$query&on=$pass_ids");
			die;
		}
		/* We've been told to untag these posts */
		elseif ( !empty($_REQUEST['untag']) )
		{
			$tags = explode(',', $_REQUEST['tags']);

			foreach ( (array) $_REQUEST['ids'] as $id )
			{
				$id = intval($id);
				$existing = wp_get_post_tags($id);
				$new = array();

				foreach ( (array) $existing as $_tag )
				{
					foreach ( (array) $tags as $tag )
					{
						if ( $_tag->name != $tag ) {
							$new[] = $_tag->name;
						}
					}
				}
				wp_set_post_tags($id, $new);
			}

			$tags = join(', ', $tags);
			wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=untag&what=$tags&num=$num$query");
			die;
		}
		/* Delete selected leads */
		elseif ( !empty($_REQUEST['delete_leads']) )
		{
			foreach ( (array) $_REQUEST['ids'] as $id )
			{
				$id = intval($id);
				wp_delete_post( $id, true);
			}

			wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=delete_leads&what=" . $name . "&num=$num$query");
			die;

		}
		/* Export Selected Leads to CSV */
		elseif ( !empty($_REQUEST['export_leads']) )
		{
			$exported = 0;

			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Description: File Transfer');
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=leads-export-csv-".date("m.d.y").".csv");
			header("Expires: 0");
			header("Pragma: public");

			$fh = @fopen( 'php://output', 'w' );

			//get all keys
			foreach ( (array) $_REQUEST['ids'] as $post_id ) {
				$this_lead_data = get_post_custom($post_id);
				unset($this_lead_data['page_views']);
				unset($this_lead_data['wpleads_inbound_form_mapped_data']);
				unset($this_lead_data['wpleads_referral_data']);
				unset($this_lead_data['wpleads_conversion_data']);
				unset($this_lead_data['wpleads_raw_post_data']);

				foreach ($this_lead_data as $key => $val) {
					$lead_meta_pairs[$key] = $key;
				}
			}

			// Add a header row if it hasn't been added yet
			fputcsv($fh, array_keys($lead_meta_pairs));
			$headerDisplayed = true;



			foreach ( (array) $_REQUEST['ids'] as $post_id ) {
				unset($this_row_data);

				$this_lead_data = get_post_custom($post_id);
				unset($this_lead_data['page_views']);
				unset($this_lead_data['wpleads_inbound_form_mapped_data']);
				unset($this_lead_data['wpleads_referral_data']);
				unset($this_lead_data['wpleads_conversion_data']);
				unset($this_lead_data['wpleads_raw_post_data']);

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

			// Make sure nothing else is sent, our file is done
			exit;

		}
	}


	die("Invalid action.");
}

?>