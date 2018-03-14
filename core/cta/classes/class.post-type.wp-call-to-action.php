<?php
/**
 * Class that registers wp-call-to-action post type and extends WP post type listing page
 *
 * @package CTA
 * @subpackage PostType
 */

if ( !class_exists('CTA_Post_Type') ) {

	class CTA_Post_Type {

		static $cta_impressions;
		static $cta_conversions;
		static $cta_conversion_rate;

		function __construct() {
			self::load_hooks();
		}

		private function load_hooks() {

			add_action('admin_init', array(__CLASS__,	'rebuild_permalinks'));
			add_action('init', array(__CLASS__, 'register_post_type'));
			add_action('init', array(__CLASS__, 'register_category_taxonomy'));

			/* Load Admin Only Hooks */
			if (is_admin()) {
				add_action( 'admin_init' , array( __CLASS__ , 'register_role_capabilities' ) ,999);

				/* Register Columns */
				add_filter( 'manage_wp-call-to-action_posts_columns', array(__CLASS__, 'register_columns'));

				/* Prepare Column Data */
				add_action( "manage_posts_custom_column", array(__CLASS__, 'prepare_column_data'), 10, 2 );

				/* Define Sortable Columns */
				add_filter( 'manage_edit_wp-call-to-action_sortable_columns', array(__CLASS__, 'define_sortable_columns'));

				/* Filter Row Actions */
				add_filter( 'post_row_actions', array(__CLASS__, 'filter_row_actions'), 10, 2 );

				/* Add Category Filter */
				add_action( 'restrict_manage_posts', array(	__CLASS__ ,'add_category_taxonomy_filter'));

				/* Add Query Parsing for Filter */
				add_filter( 'parse_query' ,	array(__CLASS__, 'convert_id_to_slug'));

				/* Change the title of the excerpt box to 'summary' */
				add_action( 'admin_init', array(__CLASS__, 'change_excerpt_to_summary'));
			}

		}

		/* Rebuilds permalinks after activation */
		public static function rebuild_permalinks() {
			$activation_check = get_option('wp_cta_activate_rewrite_check',0);

			if ($activation_check) {
				global $wp_rewrite;
				$wp_rewrite->flush_rules();
				update_option( 'wp_cta_activate_rewrite_check', '0');
			}
		}


		public static function register_post_type() {

			$slug = get_option( 'wp-cta-main-wp-call-to-action-permalink-prefix', 'cta' );

			$labels = array(
				'name' => __('Calls to Action', 'inbound-pro' ),
				'singular_name' => __('Calls to Action', 'inbound-pro' ),
				'add_new' => __('Add New', 'inbound-pro' ),
				'add_new_item' => __('Add New Call to Action', 'inbound-pro' ),
				'edit_item' => __('Edit Call to Action', 'inbound-pro' ),
				'new_item' => __('New Call to Action', 'inbound-pro' ),
				'view_item' => __('View Call to Action', 'inbound-pro' ),
				'search_items' => __('Search Call to Action', 'inbound-pro' ),
				'not_found' =>	__('Nothing found', 'inbound-pro' ),
				'not_found_in_trash' => __('Nothing found in Trash', 'inbound-pro' ),
				'parent_item_colon' => ''
			);

			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'menu_icon' => '',
				'rewrite' => array("slug" => "$slug"),
				'capability_type' => array('cta','ctas'),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'menu_position' => 33,
				'show_in_nav_menus'	=> false,
				'supports' => array('title', 'editor')
			);

			register_post_type( 'wp-call-to-action', $args );

			/*flush_rewrite_rules( false );*/

		}

		/**
		 * Register Role Capabilities
		 */
		public static function register_role_capabilities() {
			// Add the roles you'd like to administer the custom post types
			$roles = array('inbound_marketer','administrator');

			// Loop through each role and assign capabilities
			foreach($roles as $the_role) {

				$role = get_role($the_role);
				if (!$role) {
					continue;
				}

				$role->add_cap( 'read' );
				$role->add_cap( 'read_cta');
				$role->add_cap( 'read_private_ctas' );
				$role->add_cap( 'edit_cta' );
				$role->add_cap( 'edit_ctas' );
				$role->add_cap( 'edit_others_ctas' );
				$role->add_cap( 'edit_published_ctas' );
				$role->add_cap( 'publish_ctas' );
				$role->add_cap( 'delete_others_ctas' );
				$role->add_cap( 'delete_private_ctas' );
				$role->add_cap( 'delete_published_ctas' );
			}
		}

		/* Register Category Taxonomy */
		public static function register_category_taxonomy() {

			register_taxonomy('wp_call_to_action_category','wp-call-to-action', array(
					'hierarchical' => true,
					'label' => __( 'Categories', 'inbound-pro' ),
					'singular_label' => __( 'Call to Action Category', 'inbound-pro' ),
					'show_ui' => true,
					'show_in_nav_menus'	=> false,
					'query_var' => true,
					"rewrite" => true
			));

		}

		/* Register Columns */
		public static function register_columns( $cols ) {

			$cols = array(
				"cb" => "<input type=\"checkbox\" />",
				"thumbnail-cta" => __( 'Preview', 'inbound-pro' ),
				"title" => __( 'Call to Action Title', 'inbound-pro' ),
				"cta_stats" => __( 'Split Testing Results', 'inbound-pro' ),
				"cta_impressions" => __( 'Overall<br>Impressions', 'inbound-pro' ),
				"cta_actions" => __( 'Oversall<br>Conversions', 'inbound-pro' ),
				"cta_cr" => __( 'Overall<br>Conversion Rate', 'inbound-pro' )
			);

			return $cols;

		}

		/* Prepare Column Data */
		public static function prepare_column_data( $column, $post_id ) {
			global $post;

			if ($post->post_type !='wp-call-to-action') {
				return $column;
			}

			if ("ID" == $column){
				echo $post->ID;
			} else if ("title" == $column) {
			} else if ("author" == $column) {
			} else if ("date" == $column)	{
			} else if ("thumbnail-cta" == $column) {
				$permalink = get_permalink($post->ID);
				$local = array('127.0.0.1', "::1");
				if(!in_array($_SERVER['REMOTE_ADDR'], $local)){
					$thumbnail = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';
				} else {
					$template = CTA_Variations::get_current_template($post->ID);
					$thumbnail = CTA_Variations::get_template_thumbnail($template);
				}

				echo "<a title='". __('Click to Preview', 'inbound-pro' ) ."' class='thickbox' href='".$permalink."&inbound_popup_preview=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'><img src='".$thumbnail."' style='width:150px;height:110px;' title='Click to Preview'></a>";

			} elseif ("cta_stats" == $column) {
				self::show_stats_data();
			} elseif ("cta_impressions" == $column) {
				if (class_exists('Inbound_Analytics')) {
					self::$cta_impressions = Inbound_Events::get_page_views_count_by('cta_id' , array('cta_id'=> $post_id) );
					?>
					<a href='<?php echo admin_url('index.php?action=inbound_generate_report&obj_key=cta_id&cta_id='.$post_id.'&class=Inbound_Impressions_Report&range=1000&tb_hide_nav=true&TB_iframe=true&width=900&height=600'); ?>' class='thickbox inbound-thickbox'>
						<?php echo self::$cta_impressions; ?>
					</a>
					<?php
				} else {
					echo self::show_aggregated_stats("cta_impressions");
				}
			} elseif ("cta_actions" == $column) {
				if (class_exists('Inbound_Analytics')) {
					self::$cta_conversions = Inbound_Events::get_cta_conversions('cta_id' , array('cta_id'=> $post_id) );
					?>
					<a href='<?php echo admin_url('index.php?action=inbound_generate_report&obj_key=cta_id&cta_id='.$post_id.'&class=Inbound_Events_Report&range=1000&exclude_events=inbound_list_add,inbound_list_remove,inbound_content_click&tb_hide_nav=true&TB_iframe=true&width=900&height=600'); ?>' class='thickbox inbound-thickbox'>
						<?php echo count(self::$cta_conversions); ?>
					</a>
					<?php
				} else {
					echo self::show_aggregated_stats("cta_actions");
				}

			} elseif ("cta_cr" == $column) {
				if (class_exists('Inbound_Analytics')) {
					if (count(self::$cta_impressions) != 0) {
						self::$cta_conversion_rate = count(self::$cta_conversions) / count(self::$cta_impressions);
					} else {
						self::$cta_conversion_rate = 0;
					}
					self::$cta_conversion_rate = round(self::$cta_conversion_rate,2) * 100;
					?>
					<a href='<?php echo admin_url('index.php?action=inbound_generate_report&cta_id='.$post_id.'&class=Inbound_Events_Report&range=1000&tb_hide_nav=true&TB_iframe=true&width=900&height=600'); ?>' class='thickbox inbound-thickbox'>
						<?php echo self::$cta_conversion_rate.'%' ?>
					</a>
					<?php
				} else {
					echo self::show_aggregated_stats("cta_cr") . "%";
				}

			} elseif ("template" == $column) {
				$template_used = get_post_meta($post->ID, 'wp-cta-selected-template', true);
				echo $template_used;
			}
		}

		/* Define Sortable Columns */
		public static function define_sortable_columns($columns) {

			return array(
				'title' 			=> 'title',
				'impressions'		=> 'impressions',
				'actions'			=> 'actions',
				'cr'				=> 'cr'
			);

		}

		/* Define Row Actions */
		public static function filter_row_actions( $actions, $post ) {

			if ($post->post_type=='wp-call-to-action') {
				$actions['clear'] = '<a href="#clear-stats" id="wp_cta_clear_'.$post->ID.'" class="clear_stats" title="'
				. __( 'Clear impression and conversion records', 'inbound-pro' )
				. '" >' .	__( 'Clear All Stats', 'cta') . '</a>';

				/* show shortcode */
				$actions['clear'] .= '<br><span style="color:#000;">' . __( 'Shortcode:', 'inbound-pro' ) .'</span> <input type="text" style="width: 60%; text-align: center; margin-top:10px;" class="regular-text code short-shortcode-input" readonly="readonly" id="shortcode" name="shortcode" value="[cta id=\''.$post->ID.'\']">';
			}

			return $actions;

		}

		/* Adds ability to filter email templates by custom post type */
		public static function add_category_taxonomy_filter() {
			global $post_type;

			if ($post_type === "wp-call-to-action") {
				$post_types = get_post_types( array( '_builtin' => false ));

				if ( in_array( $post_type, $post_types ) ) {

					$filters = get_object_taxonomies( $post_type );
					foreach ( $filters as $tax_slug ) {
						$tax_obj = get_taxonomy( $tax_slug );
						(isset($_GET[$tax_slug])) ? $current = sanitize_text_field($_GET[$tax_slug]) : $current = 0;
						wp_dropdown_categories( array(
							'show_option_all' => __('Show All '.$tax_obj->label ),
							'taxonomy' 		=> $tax_slug,
							'name' 			=> $tax_obj->name,
							'orderby' 		=> 'name',
							'selected' 		=> $current,
							'hierarchical' 		=> $tax_obj->hierarchical,
							'show_count' 		=> false,
							'hide_empty' 		=> true
						));
					}
				}
			}
		}

		/* Convert Taxonomy ID to Slug for Filter Serch */
		public static function convert_id_to_slug($query) {
			global $pagenow;
			$qv = &$query->query_vars;
			if( $pagenow=='edit.php' && isset($qv['wp_call_to_action_category']) && is_numeric($qv['wp_call_to_action_category']) ) {
				$term = get_term_by('id',$qv['wp_call_to_action_category'],'wp_call_to_action_category');
				$qv['wp_call_to_action_category'] = $term->slug;
			}
		}

		/* Changes the title of Excerpt meta box to Summary */
		public static function change_excerpt_to_summary() {
			$post_type = "wp-call-to-action";
			if ( post_type_supports($post_type, 'excerpt') ) {
				add_meta_box('postexcerpt', __( 'Short Description', 'inbound-pro' ), 'post_excerpt_meta_box', $post_type, 'normal', 'core');
			}
		}

		public static function show_stats_data() {
			global $post, $CTA_Variations;

			$permalink = get_permalink($post->ID);
			$variations = $CTA_Variations->get_variations( $post->ID );

			$admin_url = admin_url();
			$admin_url = str_replace('?frontend=false','',$admin_url);

			if ($variations) {
				/*echo "<b>".$wp_cta_impressions."</b> visits"; */
				echo "<span class='show-stats button'>". __( 'Show Variation Stats', 'inbound-pro' ) ."</span>";
				echo "<ul class='wp-cta-varation-stat-ul'>";

				$first_status = get_post_meta($post->ID,'wp_cta_ab_variation_status', true); /* Current status */
				$first_notes = get_post_meta($post->ID,'wp-cta-variation-notes', true);
				$cr_array = array();
				$i = 0;
				$impressions = 0;
				$conversions = 0;
				foreach ($variations as $vid => $variation)
				{
					$letter = $CTA_Variations->vid_to_letter( $post->ID, $vid ); /* convert to letter */
					$vid_impressions = get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true); /* get impressions */
					$vid_conversions = get_post_meta($post->ID,'wp-cta-ab-variation-conversions-'.$vid, true);
					$vid_conversions = ($vid_conversions) ? $vid_conversions : 0;

					$v_status = get_post_meta($post->ID,'cta_ab_variation_status_'.$vid, true); /* Current status */

					if ($i === 0) { $v_status = $first_status; } /* get status of first */

					$v_status = (($v_status === "")) ? "1" : $v_status; /* Get on/off status */

					$each_notes = get_post_meta($post->ID,'wp-cta-variation-notes-'.$vid, true); /* Get Notes */

					if ($i === 0) { $each_notes = $first_notes; } /* Get first notes */

					$impressions += get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true);

					$conversions += $vid_conversions;

					if ($vid_impressions != 0) {
						$conversion_rate = $vid_conversions / $vid_impressions;
					} else {
						$conversion_rate = 0;
					}

					$conversion_rate = round($conversion_rate,2) * 100;
					$cr_array[] = $conversion_rate;

					if ($v_status === "0") {
						$final_status = __( '(Paused)', 'inbound-pro' );
					} else {
						$final_status = "";
					}
					/*if ($cr_array[$i] > $largest) {
					$largest = $cr_array[$i];
					}
					(($largest === $conversion_rate)) ? $winner_class = 'wp-cta-current-winner' : $winner_class = ""; */
					$c_text = (($vid_conversions === "1")) ? 'conversion' : "conversions";
					$i_text = (($vid_impressions === "1")) ? 'view' : "views";
					$each_notes = (($each_notes === "")) ? 'No notes' : $each_notes;
					$data_letter = "data-letter=\"".$letter."\"";

					$popup = "data-notes=\"<span class='wp-cta-pop-description'>".$each_notes."</span><span class='wp-cta-pop-controls'><span class='wp-cta-pop-edit button-primary'><a href='".$admin_url."post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>Edit This Varaition</a></span><span class='wp-cta-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='".$permalink."&inbound_popup_preview=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'>Preview This Varaition</a></span><span class='wp-cta-bottom-controls'><span class='wp-cta-delete-var-stats' data-letter='".$letter."' data-vid='".$vid."' rel='".$post->ID."'>Clear These Stats</span></span></span>\"";

					echo "<li rel='".$final_status."' data-postid='".$post->ID."' data-letter='".$letter."' data-wp-cta='' class='wp-cta-stat-row-".$vid." ".$post->ID. '-'. $conversion_rate ." status-".$v_status. "'><a ".$popup." ".$data_letter." class='wp-cta-letter' title='click to edit this variation' href='".$admin_url."/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>" . $letter . "</a><span class='wp-cta-numbers'> <span class='wp-cta-visits'><span class='visit-text'>".$i_text." </span><span class='wp-cta-impress-num'>" . $vid_impressions . "</span></span> <span class='wp-cta-converstions'><span class='conversion_txt'>".$c_text."</span><span class='wp-cta-con-num'>". $vid_conversions . "</span> </span> </span><a ".$popup." ".$data_letter." class='cr-number cr-empty-".$conversion_rate."' href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>". $conversion_rate . "%</a></li>";
					$i++;
				}
				echo "</ul>";

				$winning_cr = max($cr_array); /* best conversion rate */

				if ($winning_cr != 0) {
				echo "<span class='variation-winner-is'>".$post->ID. "-".$winning_cr."</span>";
				}
				/*echo "Total Visits: " . $impressions; */
				/*echo "Total Conversions: " . $conversions; */
			} else {
				$notes = get_post_meta($post->ID,'wp-cta-variation-notes', true); /* Get Notes */
				$cr = self::show_aggregated_stats("cta_cr");
				(($notes === "")) ? $notes = 'No notes' : $notes = $notes;
				$popup = "data-notes=\"<span class='wp-cta-pop-description'>".$notes."</span><span class='wp-cta-pop-controls'><span class='wp-cta-pop-edit button-primary'><a href='".$admin_url."post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>Edit This Varaition</a></span><span class='wp-cta-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='".$permalink."?wp-cta-variation-id=0&inbound_popup_preview=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'>Preview This Varaition</a></span><span class='wp-cta-bottom-controls'><span class='wp-cta-delete-var-stats' data-letter='A' data-vid='0' rel='".$post->ID."'>Clear These Stats</span></span></span>\"";

				echo "<ul class='wp-cta-varation-stat-ul'><li rel='' data-postid='".$post->ID."' data-letter='A' data-wp-cta=''><a ".$popup." data-letter=\"A\" class='wp-cta-letter' title='click to edit this variation' href='".$admin_url."post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>A</a><span class='wp-cta-numbers'> <span class='wp-cta-impress-num'>" . self::show_aggregated_stats("cta_impressions") . "</span><span class='visit-text'>visits</span><span class='wp-cta-con-num'>". self::show_aggregated_stats("cta_actions") . "</span> conversions</span><a class='cr-number cr-empty-".$cr."' href='".$admin_url."post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>". $cr . "%</a></li></ul>";
				echo "<div class='no-stats-yet'>". __( 'No A/B Tests running for this landing page.', 'inbound-pro' ) ." <a href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=1&action=edit&new-variation=1&wp-cta-message=go'>Start one</a></div>";

			}
		}

		/**
		 * Needs Documentation
		 */
		public static function show_aggregated_stats($type_of_stat) {
			global $post, $CTA_Variations;

			$variations = $CTA_Variations->get_variations($post->ID);


			$impressions = 0;
			$conversions = 0;

			foreach ($variations as $vid => $variation) {
				$impressions +=  $CTA_Variations->get_impressions( $post->ID, $vid );
				$conversions +=  $CTA_Variations->get_conversions( $post->ID, $vid );
			}

			if ($type_of_stat === "cta_actions") {
				return $conversions;
			}
			if ($type_of_stat === "cta_impressions") {
				return $impressions;
			}
			if ($type_of_stat === "cta_cr") {
				if ($impressions != 0) {
					$conversion_rate = $conversions / $impressions;

				} else {
					$conversion_rate = 0;
				}

				$conversion_rate = round($conversion_rate,2) * 100;

				return $conversion_rate;
			}
		}


		public static function get_ctas_as_array() {
			$ctas = get_posts( array(
				'post_type' => 'wp-call-to-action',
				'posts_per_page' => -1
			));

			$ctas = array();
			foreach ($ctas as $cta) {
				$ctas[$cta->ID] = $cta->post_title;
			}

			return $ctas;
		}


	}

	/* Load Post Type Pre Init */
	new CTA_Post_Type();

}