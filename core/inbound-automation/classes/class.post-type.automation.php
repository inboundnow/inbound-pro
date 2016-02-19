<?php

if ( !class_exists('Inbound_Automation_Post_Type') ) {

	class Inbound_Automation_Post_Type {

		function __construct() {
			self::load_hooks();
		}

		private function load_hooks() {
			/* Register Automation Post Type */
			add_action( 'init' , array( __CLASS__ , 'register_post_type' ), 11);

			/* Load Admin Only Hooks */
			if (is_admin()) {

				/* Register Columns */
				add_filter( 'manage_automation_posts_columns' , array( __CLASS__ , 'register_columns') );

				/* Prepare Column Data */
				add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );

				/* Define Sortable Columns */
				//add_filter( 'manage_edit-automation_sortable_columns', array( __CLASS__ , 'define_sortable_columns' ) );

				/* */
				add_action( 'admin_enqueue_scripts' , array(__CLASS__ , 'enqueue_admin_scripts' ) );

				add_action( 'admin_menu'  , array( __CLASS__ , 'setup_menus' ));
			}
		}

		public static function register_post_type() {

			$labels = array(
				'name' => __('Automation', 'leads'),
				'singular_name' => __( 'Rule', 'leads' ),
				'add_new' => __( 'New Rule', 'leads' ),
				'add_new_item' => __( 'Create New Rule' , 'leads' ),
				'edit_item' => __( 'Edit Rule' , 'leads' ),
				'new_item' => __( 'New Rules' , 'leads' ),
				'view_item' => __( 'View Rules' , 'leads' ),
				'search_items' => __( 'Search Rules' , 'leads' ),
				'not_found' =>	__( 'Nothing found' , 'leads' ),
				'not_found_in_trash' => __( 'Nothing found in Trash' , 'leads' ),
				'parent_item_colon' => ''
			);

			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'query_var' => true,
				'menu_icon' => '',
				'show_in_menu'	=> true,
				'show_in_nav_menus'	=> false,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => 35,
				'supports' => array('title')
			);

			register_post_type( 'automation' , $args );

		}

		/* Register Columns */
		public static function register_columns( $cols ) {

			$cols = array(
				"cb" => "<input type=\"checkbox\" />",
				"title" => __( 'Automation' , 'leads' ),
				"ma-automation-status" => __( 'Automation Status' , 'leads' )
			);

			$cols = apply_filters('automation_change_columns',$cols);

			return $cols;
		}

		/* Prepare Column Data */
		public static function prepare_column_data( $column , $post_id ) {

			global $post;

			if ($post->post_type !='automation'){
				return $column;
			}

			switch ( $column ) {
				case "title":
					$automation_name = get_the_title( $post_id );

					$automation_name = apply_filters('automation_name',$automation_name);

					echo $automation_name;
					break;

				case "ma-automation-status":
					$status = get_post_meta($post_id,'automation_active',true);
					echo $status;
					break;

			}

			do_action('automation_custom_columns',$column, $post_id);
		}

		/* Define Sortable Columns */
		public static function define_sortable_columns($columns) {

			$columns = apply_filters('',$columns);

			return $columns;
		}

		/**
		 * load admin scripts and styles
		 */
		public static function enqueue_admin_scripts() {
			wp_enqueue_style( 'automation-global-css' , INBOUND_AUTOMATION_URLPATH . 'assets/css/admin/style.css' );
		}

		public static function setup_menus() {
			if ( !current_user_can('manage_options')) {
				remove_menu_page( 'edit.php?post_type=automation' );
				return;
			}
		}
	}

	/* Load Automation Post Type Pre Init */
	$Inbound_Automation_Post_Type = new Inbound_Automation_Post_Type();
}
