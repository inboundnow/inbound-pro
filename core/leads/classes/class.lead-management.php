<?php

/*
TODO:
- Get multiple list query working.
- Fix the actionat the bottom and jquery
 */

if (!class_exists('Leads_Manager')) {

	class Leads_Manager {

		static $relation;
		static $page;
		static $per_page;
		static $paged;
		static $order;
		static $orderby;
		static $orderbys;
		static $orderbys_flip;
		static $num; /* number of leads affected */
		static $on;
		static $what;
		static $tag;
		static $keyword;
		static $query; /* query object */
		static $taxonomies; /* array of wp-lead taxonomies */

		/**
		*  Initiate class
		*/
		public function __construct() {

			self::load_static_vars();
			self::load_hooks();

		}

		/**
		*  Load hooks and filters
		*/
		public static function load_hooks() {

			/* load static vars */
			add_action( 'admin_init' , array( __CLASS__ , 'load_static_vars' ) );
			/* load admin scripts */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_admin_scripts' ) );
			/* perform lead manage actions */
			add_action( 'admin_action_lead_action', array( __CLASS__ , 'perform_actions' ) );
			/* ajax listener for loading more leads */
			add_action('wp_ajax_leads_ajax_load_more_leads', array( __CLASS__ ,'ajax_load_more_leads' ) );
			/* ajax listener for deleting lead from list */
			add_action('wp_ajax_leads_delete_from_list', array( __CLASS__ , 'ajax_delete_from_list' ) );

		}

		/**
		*  Load constants
		*/
		public static function load_static_vars() {

			if ( !isset($_REQUEST['page']) || $_REQUEST['page'] != 'lead_management' ) {
				return;
			}

			/* clean POST and REQUEST arrays of added slashes */
			$_POST = stripslashes_deep($_POST);
			$_REQUEST = stripslashes_deep($_REQUEST);

			/* set ordering & paging vars */
			self::$per_page = 60;
			self::$page = empty($_REQUEST['pull_page']) ? 1 : intval($_REQUEST['pull_page']);
			self::$paged = empty($_REQUEST['paged']) ? 1 : intval($_REQUEST['paged']);
			self::$orderby = (isset($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : '';
			self::$order = (isset($_REQUEST['order'])) ? strtoupper($_REQUEST['order']) : 'ASC';

			/* set ordering vars */
			self::$orderbys = array(
				__( 'Date First Created' , 'leads' ) => 'date',
				__( 'Date Last Modified' , 'leads' ) => 'modified',
				__( 'Alphabetical Sort' , 'leads' ) => 'title',
				__( 'Status' , 'leads' ) => 'post_status'
			);

			/* set ordering vars */
			self::$orderbys_flip = array_flip(self::$orderbys);

			/* number of leads affected by action if any */
			self::$num = (isset($_REQUEST['num'])) ? intval($_REQUEST['num']) : 0;

			self::$what = (isset($_REQUEST['what'])) ? htmlentities($_REQUEST['what']) : "";

			self::$relation =  (isset($_REQUEST['relation'])) ? htmlentities($_REQUEST['relation']) : "AND";

			self::$on =  (isset($_REQUEST['on'])) ? htmlentities($_REQUEST['on']) : "";

			self::$tag = (isset($_REQUEST['t'])) ? $_REQUEST['t'] : '';

			self::$keyword = (isset($_REQUEST['s'])) ? $_REQUEST['s'] : '';

			self::$taxonomies = get_object_taxonomies( 'wp-lead' , 'objects' );
		}

		/**
		*  Enqueues admin scripts
		*/
		public static function enqueue_admin_scripts() {
			$screen = get_current_screen();

			if ( $screen->id != 'wp-lead_page_lead_management') {
				return;
			}

			wp_enqueue_script( array('jquery', 'jqueryui' , 'jquery-ui-selectable' , 'editor', 'thickbox', 'media-upload') );
			wp_enqueue_script( 'selectjs', WPL_URLPATH . '/shared/assets/js/admin/select2.min.js');
			wp_enqueue_style( 'selectjs', WPL_URLPATH . '/shared/assets/css/admin/select2.css');
			wp_enqueue_script( 'tablesort', WPL_URLPATH . '/js/management/tablesort.min.js');

			wp_enqueue_script( 'light-table-filter', WPL_URLPATH . '/js/management/light-table-filter.min.js');
			wp_register_script( 'modernizr', WPL_URLPATH . '/js/management/modernizr.custom.js' );
			wp_enqueue_script( 'modernizr' );
			wp_enqueue_script( 'tablesort', WPL_URLPATH . '/js/management/tablesort.min.js');
			wp_enqueue_script( 'jquery-dropdown', WPL_URLPATH . '/js/management/jquery.dropdown.js');
			wp_enqueue_script( 'bulk-manage-leads', WPL_URLPATH . '/js/management/admin.js');
			wp_localize_script( 'bulk-manage-leads' , 'bulk_manage_leads', array( 'admin_url' => admin_url( 'admin-ajax.php' ) , 'taxonomies' => self::$taxonomies ));
			wp_enqueue_style( 'wpleads-list-css', WPL_URLPATH.'/css/admin-management.css');
			wp_admin_css( 'thickbox' );
			add_thickbox();
		}

		/**
		*  Displays main UI container
		*/
		public static function display_ui() {
			global $wpdb;

			/* Load only our scripts */
			Inbound_Compatibility::inbound_compatibilities_mode();

			/* listen for and display notications */
			self::display_notifications();
			/* display header */
			self::display_headers();
			/* display filters */
			self::display_filters();
			/* build query */
			self::build_query();
			/* display pagination if applicable */
			self::display_pagination();
			/* display query reseults messages */
			self::display_results_message();
			/* display results table */
			self::display_results_table();
			/* display actions */
			self::display_row_actions();

		}

		/**
		*  display notifications
		*/
		public static function display_notifications() {

			// Deal with any update messages we might have:
			if (!isset($_REQUEST['done'])) {
				return;
			}

			switch ( $_REQUEST['done'] ) {
				case 'add':
					$message = sprintf(__("Added %d posts to the list '%s'" , 'leads' ) , self::$num , self::$what );
					break;
				case 'remove':
					$message = sprintf(__("Removed %d posts from the list '%s'." , 'leads' ) , self::$num , self::$what );
					break;
				case 'tag':
					$message = sprintf(__("Tagged %d posts with &ldquo; %s &rdquo; on $on." , 'leads' ) , self::$num , self::$what );
					break;
				case 'untag':
					$message = sprintf(__("Untagged %d posts with '%s'" , 'leads' ) , self::$num , self::$what );
					break;
				case 'delete_leads':
					$message = sprintf(__("%d leads permanently deleted" , 'leads' ) , self::$num );
					break;
			}

			?>
			<div id='message' class='updated'>
				<p><strong><?php echo $message; ?></strong></p>
			</div>
			<?php
		}

		/**
		*  display headers
		*/
		public static function display_headers() {

			?>
			<div class="wrap">
				<h2><?php _e('Lead Bulk Management' , 'leads'); ?></h2>

			<?php

			/* echo starter text if search not being ran yet */
			if ( !isset($_REQUEST['submit']) ){
				echo '<p class="starter-text">'. __('To get started, select the lead criteria below to see all matching leads.' , 'leads' ) .'</p>';
			}

			/* hide current page div */
			echo "<div id='paged-current'>" . self::$paged . "</div>";

		}

		/**
		*  Display filters
		*/
		public static function display_filters() {
			?>
			<div id="filters" class="inbound-lead-filters">
				<form id="lead-management-form" method="get" action="edit.php">
					<input type="hidden" name="page" value="lead_management" />
					<input type="hidden" name="post_type" value="wp-lead" />

					<div id="top-filters"><?php
					foreach (self::$taxonomies as $key => $taxonomy ) {
						if ( !$taxonomy->hierarchical) {
							continue;
						}
						?>

						<div  id="inbound-filter">
							<div class="filter-label"><label for="taxonomy"><?php _e( sprintf( 'Select By %s:' , $taxonomy->labels->singular_name ) , 'leads' ); ?></label></div>
							<?php echo self::build_taxonomy_select( $taxonomy , 'multiple' ); ?>
						</div>
						<?php
					}
					?>
						<div id="inbound-filter">
							<div class="filter-label"><label for="orderby"><?php _e( 'Match Condition:' , 'leads' ); ?></label></div>
							<select name="relation" id="relation">
									<option value="AND" <?php echo ( self::$relation == 'AND' ? ' selected="selected"' : '' ); ?>><?php _e('Match All' , 'leads' ); ?></option>
									<option value="OR" <?php echo ( self::$relation == 'OR' ? ' selected="selected"' : 'test' ); ?>><?php _e('Match Any' , 'leads' ); ?></option>

							</select>
						</div>
					</div>
					<div id="bottom-filters">
						<div class="filter" id="lead-sort-by">
							<div class="filter-label"><label for="orderby"><?php _e( 'Sort by:' , 'leads' ); ?></label></div>
							<select name="orderby" id="orderby">
							<?php
							foreach ( self::$orderbys as $title => $value ) {
								$selected = ( self::$orderby == $value ) ? ' selected="selected"' : '';
								echo "<option value='$value'$selected>$title</option>\n";
							}
							?>
							</select>
							<select name="order" id="order">
								<option value="asc" <?php ( self::$order == 'ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Ascending' , 'leads' ); ?></option>
								<option value="desc" <?php ( self::$order == 'DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Descending' , 'leads' ); ?></option>
							</select>
						</div>




						<div class="filter" id="lead-keyword-filter">
							<label for="s"><?php _e('Keyword:' , 'leads'); ?></label>
							<input type="text" name="s" id="s" value="<?php echo htmlentities(self::$keyword); ?>" title="<?php _e('Use % for wildcards.' , 'leads'); ?>" />
						</div>


						<div class="filter" id="lead-tag-filter">
							<label for="s"><?php _e( 'Tag:' , 'leads' ); ?></label>
							<input type="text" name="t" id="t" value="<?php echo htmlentities(self::$tag); ?>" title="'foo, bar': posts tagged with 'foo' or 'bar'. 'foo+bar': posts tagged with both 'foo' and 'bar'" />
						</div>

						<div class="filter">
							<input type="submit" class="button-primary" value="<?php _e('Search Leads' , 'leads'); ?>" name="submit" />
						</div>

					</div>
				</form>
			</div>
			<?php
		}

		/**
		*  Display hidden input fields
		*/
		public static function display_hidden_action_fields() {

			wp_nonce_field('lead_management-edit');

			if ( isset($_REQUEST['s']) && !empty($_REQUEST['s']) ) {
				echo '<input type="hidden" name="s" value="' . urlencode($_REQUEST['s']) . '" />';
			}

			if ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) {
				echo '<input type="hidden" name="t" value="' . urlencode($_REQUEST['t']) . '" />';
			}
		}

		/**
		*  Display pagination
		*/
		public static function display_pagination() {

			$pagination = '';
			if ( isset($query) && $query->max_num_pages > 1 ) {
				$current = preg_replace('/&?paged=[0-9]+/i', '', strip_tags($_SERVER['REQUEST_URI'])); // I'll happily take suggestions on a better way to do this, but it's 3am so

				$pagination .= "<div class='tablenav-pages'>";

				if ( self::$paged > 1 ) {
					$prev = self::$paged - 1;
					$pagination .= "<a class='prev page-numbers' href='$current&amp;paged=$prev'>&laquo; ". __( 'Previous' , 'leads' )."</a>";
				}

				for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
					if ( $i == self::$paged ) {
						$pagination .= "<span class='page-numbers current'>$i</span>";
					} else {
						$pagination .= "<a class='page-numbers' href='$current&amp;paged=$i'>$i</a>";
					}
				}

				if ( self::$paged < $query->max_num_pages ) {
					$next = self::$paged + 1;
					$pagination .= "<a class='next page-numbers' href='$current&amp;paged=$next'>".__( 'Next' , 'leads' ) ." &raquo;</a>";
				}

				$pagination .= "</div>";
			}

			echo $pagination;

		}

		/**
		*  Display results query
		*/
		public static function display_results_message() {
			// Criteria were given, but no posts were matched.
			if ( empty(self::$query->posts) ) {
				echo '<p>'. __('No posts matched that criteria, sorry! Try again with something different.' , 'leads' ) .'</p>';
				return;
			}

			echo	'<div style="margin-top:20px;font-style:italic">';
			echo	'		<div id="display-lead-total">';
			echo 	'			'. __( 'search returned ' , 'leads' ) .'<strong><span id="lead-total-found">'.self::$query->found_posts.' </span></strong>'. __( 'results' , 'leads' );
			echo	'		</div>';
			echo	'		<div id="display-lead-count">';
			echo 	'			<i class="lead-spinner"></i>';
			echo 	'			<span id="lead-count-text">'.__( 'Grabbing Matching Leads' , 'leads' ) .'</span>';
			echo	'		</div>';
			echo 	'	<div class="table-search">';
			echo 	'		<input type="search" class="light-table-filter" data-table="widefat" placeholder="'. __( 'Filter Results Below' , 'leads' ) .'" /><span id="search-icon"></span>';

			echo	'	</div>';
			echo 	'</div>';

		}


		/**
		*  Display results table
		*/
		public static function display_results_table() {

			if (!isset(self::$query->posts)) {
				return;
			}

			?>
			<form method="post" id="man-table" action="<?php echo admin_url( 'admin.php' ); ?>">
				<input type="hidden" name="action" value="lead_action" />
				<div id="posts">

				<table class="widefat" id="lead-manage-table">
					<thead>
						<tr>
							<th class="checkbox-header no-sort" scope="col"><input type="checkbox" id="toggle" title="Select all posts" /></th>
							<th class="count-sort-header" scope="col">#</th>
							<th scope="col"><?php _e( 'Date' , 'leads' ); ?></th>
							<th scope="col"><?php _e( 'Email' , 'leads' ); ?></th>
							<th scope="col"><?php _e( 'Current Lists' , 'leads' ); ?></th>
							<th scope="col"><?php _e( 'Current Tags' , 'leads' ); ?></th>
							<th scope="col" class="no-sort"><?php _e( 'View' , 'leads' ); ?></th>
							<th scope="col"><?php _e( 'ID' , 'leads' ); ?></th>
						</tr>
					</thead>
					<tbody id="the-list">
					<?php

					$loop_count = 1;
					$i = 0;

					foreach ( self::$query->posts as $post ) {

						echo '<tr' . ( $i++ % 2 == 0  ? ' class="alternate"' : '' ) .'>';

						/* show checkbox */
						echo '<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>';

						/* show count */
						echo '<td class="count-sort"><span>'.$loop_count.'</span></td>';

						/* show publish date */
						echo '<td>';
						if ( '0000-00-00 00:00:00' == $post->post_date ) {
							_e('Unpublished' , 'leads');
						} else {
							echo date(__('Y/m/d'), strtotime($post->post_date));
						}
						echo '</td>';

						/* show email */
						echo '<td>';
						echo '	<span class="lead-email">' . $post->post_title . '</span>';
						echo '</td>';

						/* show lists */
						echo '<td class="list-column-row">';
							$terms = wp_get_post_terms( $post->ID, 'wplead_list_category', 'id' );
							foreach ( $terms as $term ) {
								echo  '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the '.$term->name.' list" class="remove-from-list" data-lead-id="'.$post->ID.'" data-list-id="'.$term->term_id.'"></i></span> ';
							}
						echo '</td>';

						/* show tags */
						echo '<td class="tags-column-row">';
							$tags = wp_get_post_terms( $post->ID, 'lead-tags', 'id' );

							if ($tags) {
								foreach ( $tags as $tag ) {
									echo  "<a title='Click to Edit Lead Tag Name' target='_blank' href='".admin_url('edit-tags.php?action=edit&taxonomy=lead-tags&tag_ID='.$tag->term_id.'&post_type=wp-lead')."'>$tag->name</a>, ";
								}
							} else {
								_e( 'No tags' , 'leads' );
							}
						echo '</td>';

						/* show link to lead */
						echo '<td>';
						echo '	<a class="thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">'.__( 'View' , 'leads' ) .'</a>';
						echo '</td>';

						/* show lead id */
						echo '<td>' . $post->ID . '</td>';
						echo '</tr>';
						$loop_count++;
					}
				echo '</tbody>';
				echo '</table>';

		}

		/**
		*  Display Row Actions
		*/
		public static function display_row_actions() {
			?>
			<div id="all-actions" class="tablenav">

			<div id="inbound-lead-management"><span class="lead-actions-title"><?php _e( 'What do you want to do with the selected leads?' , 'leads' ); ?></span>

			<div id="controls">
			<?php
				self::display_action_controls();
			?>
			</div>
				<div id="lead-action-triggers">


				<div class="action" id="lead-export">
					<input type="submit" class="manage-remove button-primary button" name="export_leads" value="<?php _e( 'Export Leads as CSV' , 'leads' ); ?>" title="<?php _e( 'Exports selected leads into a CSV format.' , 'leads' ); ?>" />

				</div>

				<div class="action" id="lead-update-lists">
					<label for="lead-update-lists"><?php _e( 'Choose List:' , 'leads' ); ?></label>
					<?php

					/* get available terms in taxonomy */
					$terms = get_terms('wplead_list_category' , array( 'hide_empty' => false ));

					/* setup the select */
					echo '<select name="wplead_list_category_action">';

					/* print the first option */
					echo '<option class="" value="" selected="selected">' .  __( 'Select lead list ' , 'leads' ) .'</option>';

					/* loop through terms and create options */
					foreach ($terms as $term) {
						echo '<option class="" value="'.$term->term_id.'" >'. $term->name.' ('.$term->count.')</option>';
					}

					/* end select input */
					echo '</select>';

					?>
					<input type="submit" class="button-primary button" name="add" value="<?php _e('Add to' , 'leads' ) ?>" title="<?php _e( 'Add the selected posts to this category.' , 'leads' ); ?>" />
					<input type="submit" class="manage-remove button-primary button" name="remove" value="<?php _e( 'Remove from' , 'leads' ) ?>" title="<?php _e( 'Remove the selected posts from this category.' , 'leads' ); ?>" />
				</div>

				<div class="action" id="lead-update-tags">
					<label for="lead-update-tags"><?php _e( 'Tags:' , 'leads' ); ?></label>
					<input type="text" id="inbound-lead-tags-input" name="tags" placeholder="<?php _e( 'Separate multiple tags with commas. ' , 'leads' ); ?>" title="<?php _e( 'Separate multiple tags with commas.' , 'leads' ); ?>" />
					<input type="submit" name="replace_tags" class="manage-tag-replace button-primary button" value="<?php _e( 'Replace' , 'leads' ); ?>" title="<?php _e( 'Replace the selected leads\'s current tags with these ones. Warning this will delete current tags and replace them ' , 'leads' ); ?>" />
					<input type="submit" name="tag" class="manage-tag-add button-primary button" value="<?php _e( 'Add' , 'leads' ) ?>" title="<?php _e( 'Add tags to the selected leads without altering the leads\' existing tags' , 'leads' ); ?>" />
					<input type="submit" name="untag" class="manage-remove button-primary button" value="<?php _e( 'Remove' , 'leads' ) ?>" title="<?php _e( 'Remove these tags from the selected leads.' , 'leads' ); ?>" />
				</div>

				<div class="action" id="lead-update-meta">
					<label for="lead-update-meta"><?php _e( 'Meta:' , 'leads' ); ?></label>
					<input type="text" name="meta_val" title="<?php _e( 'Separate multiple tags with commas.' , 'leads' ); ?>" />
					<input type="submit" name="replace_meta" value="<?php _e( 'Replace' , 'leads' ); ?>" title="<?php _e( 'Replace the selected posts\' current meta values with these ones.' , 'leads' ); ?>" />
					<input type="submit" name="meta" value="<?php _e( 'Add' , 'leads' ); ?>" title="<?php _e( 'Add these meta values to the selected posts without altering the posts\' existing tags.' , 'leads' ); ?>" />
					<input type="submit" name="unmeta" value="<?php _e( 'Remove' , 'leads' ); ?>" title="<?php _e( 'Remove these meta values from the selected posts.' , 'leads' ); ?>" />
				</div>

				<div class="action" id="lead-delete">
					<label for="lead-delete" id="del-label"><span style="color:red;"><?php _e( 'Delete Selected Leads (Warning! There is no UNDO):' , 'leads' ); ?></span></label>

					<input type="submit" class="manage-remove button-primary button" name="delete_leads" value="<?php _e( 'Permanently Delete Selected Leads' , 'leads' ) ?>" title="<?php _e( 'This will delete the selected leads from your database. There is no undo.' , 'leads' ); ?>" />

				</div>
				</div>
			</div>

			<?php
			self::display_hidden_action_fields();
			?>
			</form>
			</div>
			<?php
		}

		/**
		*  Display action controls
		*/
		public static function display_action_controls() {
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
			<script>
				jQuery(document).ready(function($) {
					jQuery( function() {
						jQuery( '#cd-dropdown' ).dropdown();
					});

					jQuery("body").on('click', '.cd-dropdown li', function () {
						 var value = jQuery(this).attr('data-value'); // get selected value
						 console.log(value);

						 if (value) { // require a URL
						  jQuery(".action").hide();
						  jQuery("#" + value).show();
					  }
					  return false;
					});
				});
			</script>
		<?php
		}

		/**
		*  Build query
		*/
		public static function build_query() {
			if ( !isset($_REQUEST['submit']) && !defined( 'DOING_AJAX' ) ) {
				self::$query = null;
				return;
			}

			/* set default args */
			$args = array(
				'post_type' => 'wp-lead',
				'order' => self::$order,
				'orderby' => self::$orderby,
				'posts_per_page' => self::$per_page,
			);

			/* listen for on request - not sure what this does */
			if (isset($_REQUEST['on'])){
				$on_val = explode(",", $on);
				$args['post__in'] = $on_val;
				$args['order'] = 'DESC';
				$args['orderby'] = 'date';
			}

			/* set tax_query_relation */
			$tax_query = array( 'relation' => $_REQUEST['relation'] );

			/* loop through taxonomies and check for filter */
			foreach (self::$taxonomies as $key => $taxonomy ) {
				if ( !$taxonomy->hierarchical) {
					continue;
				}

				if ( !isset( $_REQUEST[ $taxonomy->query_var ] ) ||  !$_REQUEST[ $taxonomy->query_var ] ||  $_REQUEST[ $taxonomy->query_var ][0] == 'all'){
					continue;
				}

				/* build tax_query */

				foreach(  $_REQUEST[ $taxonomy->query_var ] as $values) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy->query_var,
						'field'    => 'id',
						'terms'    => array($values)
					);
				}
			}

			if (count($tax_query)>1) {
				$args['tax_query'] = $tax_query;
			}

			// Add tag to query
			if ((isset($_REQUEST['t'])) && $_REQUEST['t'] != "" ){
				$args['tag'] = $_REQUEST['t'];
			}

			if ((isset($_REQUEST['paged'])) && $_REQUEST['paged'] != "1" ){
				$args['paged'] = self::$paged;
			}

			self::$query = new WP_Query( $args );

		}



		/**
		*  get taxnomy select
		*/
		public static function build_taxonomy_select( $taxonomy ) {

			/* create the select input */
			echo '<select name="'. $taxonomy->query_var.'[]" id="'. $taxonomy->query_var.'" multiple class="select2 form-control">';

			/* get selected taxonomies */
			$list_array = ( isset($_REQUEST[ $taxonomy->query_var ]) ) ? $_REQUEST[ $taxonomy->query_var ] : array();

			/* print the first option */
			echo '<option class="" value="all" '. ( isset($_REQUEST[ $taxonomy->query_var ]) && $_REQUEST[ $taxonomy->query_var ][0] === 'all'  ? 'selected="selected"' : '' ). '>' .  __( 'All ' , 'leads' ) .'</option>';

			/* get available terms in taxonomy */
			$terms = get_terms( $taxonomy->query_var , array( 'hide_empty' => false	));

			/* loop through terms and create options */
			foreach ($terms as $term) {
				echo '<option class="" value="'.$term->term_id.'" '.(isset($_REQUEST[ $taxonomy->query_var ]) && in_array($term->term_id, $list_array)  ? 'selected="selected"' : '' ) .'>'. $term->name.' ('.$term->count.')</option>';
			}

			/* end select input */
			echo '</select>';
			?>
			<script type='text/javascript'>
				jQuery("#<?php echo $taxonomy->query_var; ?>").select2({
					allowClear: true,
					placeholder: '<?php _e(  sprintf( 'Select %s From List' , $taxonomy->labels->singular_name ) , 'leads' ); ?>'
				});

			</script>
			<?php

		}

		/**
		*  Perform lead actions
		*/
		public static function perform_actions() {
			global $Inbound_Leads;

			if ( !current_user_can('level_9') ){
				die ( __('User does not have admin level permissions.') );
			}

			check_admin_referer('lead_management-edit');

			$_POST = stripslashes_deep($_POST);
			$_REQUEST = stripslashes_deep($_REQUEST);

			/* bail if no ids to process */
			if ( !isset($_REQUEST['ids']) || !$_REQUEST['ids'] ) {
				return;
			}

			/* prepare array */
			$pass_ids =  (is_array($_REQUEST['ids'])) ? implode(',', $_REQUEST['ids']) : $_REQUEST['ids'];


			self::$num = count( $_REQUEST['ids'] );

			if ( !empty($_REQUEST['wplead_list_category_action']) ){
				$list_id = intval($_REQUEST['wplead_list_category_action']);
				$query = '&cat=' . $list_id;
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
					$Inbound_Leads->add_lead_to_list( $fid, $list_id ); // add to list
				}

				wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=add&what=" . $name . "&num=".self::$num.$query);
				die;
			}
			/* We've been told to remove these posts from the given category. */
			elseif ( !empty($_REQUEST['remove']) )
			{

				foreach ( (array) $_REQUEST['ids'] as $id )	{
					$Inbound_Leads->remove_lead_from_list( intval($id) , $list_id );
				}

				wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=remove&what=" . $name . "&num=".self::$num);
				die;
			}
			/* We've been told to tag these posts */
			elseif ( !empty($_REQUEST['tag']) || !empty($_REQUEST['replace_tags']) )
			{
				$tags = $_REQUEST['tags'];

				foreach ( (array) $_REQUEST['ids'] as $id )	{
					$Inbound_Leads->add_tag_to_lead( intval($id) , $tags );
				}
				wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=tag&what=$tags&num=self::$num$query&on=$pass_ids");
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
				wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=untag&what=$tags&num=self::$num$query");
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

				wp_redirect(get_option('siteurl') . "/wp-admin/edit.php?post_type=wp-lead&page=lead_management&done=delete_leads&what=" . $name . "&num=self::$num$query");
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


			die("Invalid action.");

		}


		/**
		*  Ajax listener to load more leads
		*/
		public static function ajax_load_more_leads() {

			/* build query */
			self::build_query();

			$i = 0;

			$loop_page = self::$paged - 1;
			$loop_count = $loop_page * 60;
			$loop_count = $loop_count + 1;

			foreach ( self::$query->posts as $post ) {

				echo '<tr' . ( $i++ % 2 == 0  ? ' class="alternate"' : '' ) .'>';

				/* show checkbox */
				echo '<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>';

				/* show count */
				echo '<td class="count-sort"><span>'.$loop_count.'</span></td>';

				/* show publish date */
				echo '<td>';
				if ( '0000-00-00 00:00:00' == $post->post_date ) {
					_e('Unpublished' , 'leads');
				} else {
					echo date(__('Y/m/d'), strtotime($post->post_date));
				}
				echo '</td>';

				/* show email */
				echo '<td>';
				echo '	<span class="lead-email">' . $post->post_title . '</span>';
				echo '</td>';

				/* show lists */
				echo '<td class="list-column-row">';
					$terms = wp_get_post_terms( $post->ID, 'wplead_list_category', 'id' );
					foreach ( $terms as $term ) {
						echo  '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the '.$term->name.' list" class="remove-from-list" data-lead-id="'.$post->ID.'" data-list-id="'.$term->term_id.'"></i></span> ';
					}
				echo '</td>';

				/* show tags */
				echo '<td class="tags-column-row">';
					$_tags = wp_get_post_terms( $post->ID, 'lead-tags', 'id' );

					if ($tags) {
						foreach ( $_tags as $tag ) {
							echo  "<a title='Click to Edit Lead Tag Name' target='_blank' href='".admin_url('edit-tags.php?action=edit&taxonomy=lead-tags&tag_ID='.$tag->term_id.'&post_type=wp-lead')."'>$tag->name</a>, ";
						}
					} else {
						_e( 'No tags' , 'leads' );
					}
				echo '</td>';

				/* show link to lead */
				echo '<td>';
				echo '	<a class="thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">'.__( 'View' , 'leads' ) .'</a>';
				echo '</td>';

				/* show lead id */
				echo '<td>' . $post->ID . '</td>';
				echo '</tr>';
				$loop_count++;
			}

		}


		/**
		*  Ajax listener to delete lead from list
		*/
		public static function ajax_delete_from_list() {

			$lead_id = (isset($_POST['lead_id'])) ? $_POST['lead_id'] : '';
			$list_id = (isset($_POST['list_id'])) ? $_POST['list_id'] : '';

			$id = $lead_id;

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

			if (in_array($list_id, $final)) {
				$new = array_flip ( $final );
				unset($new[$list_id]);
				$save = array_flip ( $new );
				wp_set_object_terms( $id, $save, 'wplead_list_category');
			}


		}

	}

	new Leads_Manager;
}