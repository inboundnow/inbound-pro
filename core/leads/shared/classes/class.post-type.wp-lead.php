<?php


if ( !class_exists('Inbound_Leads') ) {

	class Inbound_Leads {

		/**
		*  Initialize Inbound_Leads class
		*/
		function __construct() {
			self::load_hooks();
		}

		/**
		*  Load action hooks & filters
		*/
		private function load_hooks() {
			/* Register Leads Post Type */
			add_action( 'init' , array( __CLASS__ , 'register_post_type' ));
			add_action( 'init' , array( __CLASS__ , 'register_taxonomies' ));

			/* Modify columns on lead list creation page */
			add_filter( 'manage_edit-wplead_list_category_columns' , array( __CLASS__ , 'register_lead_list_columns' ));
			add_filter( 'manage_wplead_list_category_custom_column' , array( __CLASS__ , 'support_lead_list_columns' ), 10, 3);

			if (is_admin()) {
				add_action( 'edit_form_after_title', array( __CLASS__ , 'install_leads_prompt' ) );

				/* Remove lead tags menu item */
				add_filter( 'admin_menu' , array( __CLASS__ , 'remove_menus' ) );
			}
		}
		/**
		*	Register wp-lead post type
		*/
		public static function register_post_type() {
		
			$lead_active = ( defined('WPL_CURRENT_VERSION') ) ? true : false ; // Check if leads is activated

			$labels = array(
				'name' => _x('Leads', 'post type general name'),
				'singular_name' => _x('Lead', 'post type singular name'),
				'add_new' => _x('Add New', 'Lead'),
				'add_new_item' => __('Add New Lead'),
				'edit_item' => __('Edit Lead'),
				'new_item' => __('New Leads'),
				'view_item' => __('View Leads'),
				'search_items' => __('Search Leads'),
				'not_found' =>	__('Nothing found'),
				'not_found_in_trash' => __('Nothing found in Trash'),
				'parent_item_colon' => ''
			);

			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'menu_icon' => INBOUDNOW_SHARED_URLPATH . 'assets/images/global/leads.png',
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => 31,
				'supports' => array('custom-fields','thumbnail')
			);

			$args['show_in_menu'] = ($lead_active) ? true : false;

			register_post_type( 'wp-lead' , $args );

		}

		/**
		*	Register Category Taxonomy
		*/
		public static function register_taxonomies() {
			
			/* bail if taxonomy already registered */
			if (taxonomy_exists('wplead_list_category')) {
				return;
			}
			
			/* Register lead lists */
			$list_labels = array(
				'name'						=> __( 'Lead Lists', 'inbound-pro' ),
				'singular_name'				=> __( 'Lead List', 'inbound-pro' ),
				'search_items'				=> __( 'Search Lead Lists' , 'inbound-pro' ),
				'popular_items'				=> __( 'Popular Lead Lists' , 'inbound-pro' ),
				'all_items'					=> __( 'All Lead Lists' , 'inbound-pro' ),
				'parent_item'				=> null,
				'parent_item_colon'			=> null,
				'edit_item'					=> __( 'Edit Lead List' , 'inbound-pro' ),
				'update_item'				=> __( 'Update Lead List' , 'leads'	),
				'add_new_item'				=> __( 'Add New Lead List' , 'leads'	),
				'new_item_name'				=> __( 'New Lead List' , 'leads'	),
				'separate_items_with_commas' => __( 'Separate Lead Lists with commas' , 'leads'	),
				'add_or_remove_items'		=> __( 'Add or remove Lead Lists' , 'leads'	),
				'choose_from_most_used'		=> __( 'Choose from the most used lead List' , 'inbound-pro' ),
				'not_found'					=> __( 'No Lead Lists found.' , 'leads'	),
				'menu_name'					=> __( 'Lead Lists' , 'leads'	),
			);

			$list_args = array(
				'hierarchical'			=> true,
				'labels'				=> $list_labels,
				'singular_label'		=> __( 'List Management' , 'inbound-pro' ),
				'show_ui'				=> true,
				'show_in_menu'			=> true,
				'show_in_nav_menus'		=> false,
				'show_admin_column'		=> true,
				'query_var'				=> true,
				'rewrite'				=> false,
			);

			register_taxonomy('wplead_list_category','wp-lead', $list_args );

			/* Register Lead Tags Taxonomy */
			$labels = array(
				'name'						=> _x( 'Lead Tags', 'taxonomy general name' ),
				'singular_name'				=> _x( 'Lead Tag', 'taxonomy singular name' ),
				'search_items'				=> __( 'Search Lead Tags' ),
				'popular_items'				=> __( 'Popular Lead Tags' ),
				'all_items'					=> __( 'All Lead Tags' ),
				'parent_item'				=> null,
				'parent_item_colon'			=> null,
				'edit_item'					=> __( 'Edit Lead Tag' ),
				'update_item'				=> __( 'Update Lead Tag' ),
				'add_new_item'				=> __( 'Add New Lead Tag' ),
				'new_item_name'				=> __( 'New Lead Tag' ),
				'separate_items_with_commas'=> __( 'Separate Lead Tags with commas' ),
				'add_or_remove_items'		=> __( 'Add or remove Lead Tags' ),
				'choose_from_most_used'		=> __( 'Choose from the most used lead tags' ),
				'not_found'					=> __( 'No lead tags found.' ),
				'menu_name'					=> __( 'Lead Tags' ),
			);

			$args = array(
				'hierarchical'			=> false,
				'labels'				=> $labels,
				'show_ui'				=> true,
				'show_admin_column'		=> true,
				'show_in_menus'			=> false,
				'show_in_nav_menus'		=> false,
				'update_count_callback' => '_update_post_term_count',
				'query_var'				=> true,
				'rewrite'				=> array( 'slug' => 'lead-tag' ),
			);

			register_taxonomy( 'lead-tags', 'wp-lead', $args );
		}

		/**
		 *  Adds ID column to lead-tags WP List Table
		 */
		public static function register_lead_list_columns( $cols ) {
			$new_columns = array(
				'cb' => '<input type="checkbox" />',
				'lead_id' => __('ID' , 'leads'),
				'name' => __('Name' , 'leads'),
				'description' => __('Description' , 'leads'),
				'slug' => __('Slug' , 'leads'),
				'posts' => __('Posts' , 'leads')
				);
			return $new_columns;
		}

		/**
		 *  Helps ID column display lead list ID
		 */
		public static function support_lead_list_columns( $out, $column_name, $term_id ) {
			if ($column_name != 'lead_id' ) {
				return $out;
			}

			$out .= $term_id;

			return $out;
		}

		/**
		*	Make sure that all list ids are intval
		*
		*	@param MIXED $lists
		*	@return ARRAY
		*
		*/
		public static function intval_list_ids( $lists ) {

			if (is_array($lists)) {
				foreach ($lists as $key => $id) {
					$lists[ $key ] = intval($id);
				}
			} else {
				$lists = intval($lists);
			}

			return $lists;
		}


		/**
		* Adds lead to list
		*
		* @param lead_id INT
		* @param list_id MIXED INT,ARRAY
		*
		*/
		public static function add_lead_to_list( $lead_id , $list_id ) {

			/* intval list ids */
			$list_id = Inbound_Leads::intval_list_ids( $list_id );

			wp_set_object_terms( $lead_id, $list_id , 'wplead_list_category', true );
			do_action('add_lead_to_lead_list' , $lead_id , $list_id );
		}


		/**
		* Removes lead from list
		*
		* @param lead_id INT
		* @param list_id MIXED INT, ARRAY
		*
		*/
		public static function remove_lead_from_list( $lead_id , $list_id ) {
			/* intval list ids */
			$list_id = Inbound_Leads::intval_list_ids( $list_id );

			wp_remove_object_terms( $lead_id, $list_id , 'wplead_list_category', true );
			do_action('remove_lead_from_list' , $lead_id , $list_id );
		}

		/**
		* Get an array of all lead lists belonging to lead id
		*
		* @param INT $lead_id ID of lead
		*
		* @returns ARRAY of lead lists with term id as key and list name as value
		*/
		public static function get_lead_lists_by_lead_id( $lead_id ) {

			$args = array(
				'hide_empty' => false
			);

			$terms = get_the_terms( $lead_id , 'wplead_list_category' );

			if (!$terms) {
				return array();
			}

			foreach ( $terms as $term	) {
				$array[$term->term_id] = $term->name;
			}

			return $array;
		}

		/**
		 *  Adds a new lead list
		 */
		public static function create_lead_list( $args ) {

			$params = array();

			/* if no list name is present then return null */
			if ( !isset( $args['name'] )) {
				return null;
			}

			if (isset( $args['description'] )) {
				$params['description'] = $args['description'];
			}

			if (isset( $args['parent'] )) {
				$params['parent'] = $args['parent'];
			} else {
				$params['parent'] = 0;
			}

			$term = term_exists(  $args['name'] , 'wplead_list_category' , $params['parent'] );

			/* if term does not exist then create it */
			if ( !$term ) {
				$term = wp_insert_term(	$args['name'], 'wplead_list_category', $params );
			}

			if ( is_array($term) && isset( $term['term_id'] ) ) {
				return array( 'id' => $term['term_id'] );
			} else if ( is_numeric($term) ) {
				return array( 'id' => $term );
			} else {
				return $term;
			}
		}

		/**
		 *  updates a lead list
		 *  @param ARRAY $args
		 *  @retun ARRAY ontaining list id
		 */
		public static function update_lead_list( $args ) {

			/* id is required */
			if (!isset($args['id'])) {
				return null;
			}

			if (isset( $args['name'] )) {
				$params['name'] = $args['name'];
			}

			if (isset( $args['description'] )) {
				$params['description'] = $args['description'];
			}

			if (isset( $args['parent'] )) {
				$params['parent'] = $args['parent'];
			}

			$term = get_term_by( 'id' , $args['id'] , 'wplead_list_category' , ARRAY_A );

			if ( $term ) {
				$term = wp_update_term( $args['id'] , 'wplead_list_category', $params	);
			}

			if ( is_array($term) && isset( $term['term_id'] ) ) {
				return array( 'list_id' => $term['term_id'] );
			} else if ( is_numeric($term) ) {
				return array( 'list_id' => $term );
			} else {
				return $term;
			}
		}

		/**
		 *  Deletes a lead list
		 */
		public static function delete_lead_list( $id = null ) {

			/* id is required */
			if (!isset($id)) {
				return array( 'error' => __( 'must include an id parameter' , 'inbound-pro' ) );
			}

			wp_delete_term( $id , 'wplead_list_category' );
			
			return array( 'message' => __( 'lead list deleted' , 'inbound-pro' ) );
		}

		/**
		 *  Deletes a lead list
		 */


		/**
		* Get an array of all lead lists
		*
		* @returns ARRAY of lead lists with term id as key and list name as value
		*/
		public static function get_lead_lists_as_array() {
			self::register_taxonomies();
			
			$array = array();

			$args = array(
				'hide_empty' => false,
			);

			$terms = get_terms('wplead_list_category', $args);

			foreach ( $terms as $term	) {
				$array[$term->term_id] = $term->name;
			}

			return $array;
		}

		/**
		*  Get lead list infomration
		*
		*  @param STRING $search accepts 'id' , 'slug' , 'name' or 'term_taxonomy_id'
		*  @param INT $list_id
		*
		*  @returns ARRAY
		*/
		public static function get_lead_list_by( $search , $list_id ) {
			return  get_term_by( $search , $list_id , 'wplead_list_category', ARRAY_A);
		}

		/**
		* Adds tag to lead
		*
		* @param lead_id INT
		* @param tag_id MIXED INT, STRING, ARRAY
		*
		*/
		public static function add_tag_to_lead( $lead_id , $tag ) {
			wp_set_object_terms( $lead_id, $tag , 'lead-tags', true );
		}

		/**
		* Remove tag from lead
		*
		* @param lead_id INT
		* @param tag_id MIXED INT,STRING,ARRAY
		*
		*/
		public static function remove_tag_from_lead( $lead_id , $list_id ) {
			wp_remove_object_terms( $lead_id, $list_id , 'lead-tags', true );
		}

		/**
		* Shows message to install leads when leads is not installed or activated
		*
		*/
		public static function install_leads_prompt() {
			global $post;

			if ( empty ( $post ) || 'wp-lead' !== get_post_type( $GLOBALS['post'] ) ) {
				return;
			}

			if (!wpleads_check_active()) {
				_e( 'WordPress Leads is not currently installed/activated to view and manage leads please turn it on.' , 'inbound-pro' );
			}
		}

		/**
		* Gets number of leads in list
		*
		* @param list_id INT of lead list taxonomy object
		*
		*/
		public static function get_leads_count_in_list( $list_id ) {

			$query = new WP_Query( array(
					'post_type' => 'wp-lead',
					'tax_query' => array (
						'relation' => 'AND',
						array (
							'taxonomy' => 'wplead_list_category' ,
							'field' => 'id' ,
							'terms' => array(	$list_id )
						)
					),
					'posts_per_page' => -1
			) );

			$count = $query->post_count;

			return sprintf( __( '%d leads' , 'inbound-pro' ) , $count );

		}

		public static function remove_menus() {
			global $submenu;

			if (!current_user_can('activate_plugins') ) {
				return;
			}

			//print_r($submenu);exit;
			// This needs to be set to the URL for the admin menu section (aka "Menu Page")
			$menu_page = 'edit.php?post_type=wp-lead';

			// This needs to be set to the URL for the admin menu option to remove (aka "Submenu Page")
			$taxonomy_admin_page = 'edit-tags.php?taxonomy=lead-tags&amp;post_type=wp-lead';

			if ( !isset($submenu[$menu_page]) ) {
				return;
			}

			// This removes the menu option but doesn't disable the taxonomy
			foreach($submenu[$menu_page] as $index => $submenu_item) {
				if ($submenu_item[2]==$taxonomy_admin_page) {
					unset($submenu[$menu_page][$index]);
				}
			}
		}

	}

	/**
	*  	Register 'wp-lead' CPT
	*/
	$GLOBALS['Inbound_Leads'] = new Inbound_Leads();


}