<?php

if ( !class_exists('Inbound_Automation_Post_Type') ) {

	class Inbound_Automation_Post_Type {

		static $queue;

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

				/* Define Sortable Columns
				add_filter( 'manage_edit-automation_sortable_columns', array( __CLASS__ , 'define_sortable_columns' ) );
				*/
				add_action( 'admin_enqueue_scripts' , array(__CLASS__ , 'enqueue_admin_scripts' ) );

				add_action( 'admin_menu'  , array( __CLASS__ , 'setup_menus' ));

				/* Adds quick actions to row */
				add_filter('post_row_actions', array(__CLASS__, 'add_row_actions'),8,2);

				/* Setup Ajax Listeners - Enable/Diable rules */
				add_action( 'wp_ajax_automation_rule_toggle_status', array(__CLASS__, 'ajax_toggle_rule_status'));

				/* Setup Ajax Listeners - Clear tasks related to a rule */
				add_action( 'wp_ajax_automation_rule_remove_taks', array(__CLASS__, 'ajax_clear_rule_tasks'));

				/* setup bulk edit options */
				add_action('admin_footer-edit.php', array(__CLASS__, 'register_bulk_edit_fields'));

				/* process bulk actions  */
				add_action('load-edit.php', array(__CLASS__, 'process_bulk_actions'));
			}
		}

		public static function register_post_type() {

			$labels = array(
				'name' => __('Automation', 'inbound-pro' ),
				'singular_name' => __( 'Rule', 'inbound-pro' ),
				'add_new' => __( 'New Rule', 'inbound-pro' ),
				'add_new_item' => __( 'Create New Rule' , 'inbound-pro' ),
				'edit_item' => __( 'Edit Rule' , 'inbound-pro' ),
				'new_item' => __( 'New Rules' , 'inbound-pro' ),
				'view_item' => __( 'View Rules' , 'inbound-pro' ),
				'search_items' => __( 'Search Rules' , 'inbound-pro' ),
				'not_found' =>	__( 'Nothing found' , 'inbound-pro' ),
				'not_found_in_trash' => __( 'Nothing found in Trash' , 'inbound-pro' ),
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

		/**
		 * Get Automation Rules as Array
		 * @return array
		 */
		public static function get_rules_as_array() {
			$rules = get_posts( array(
				'post_type' => 'automation',
				'posts_per_page' => -1
			));

			$rules_array = array();
			foreach ($rules as $rule) {
				$rules_array[$rule->ID] = $rule->post_title;
			}

			return $rules_array;
		}

		/* Register Columns */
		public static function register_columns( $cols ) {

			/* sneak in - get rule queue */
			self::$queue = Inbound_Automation_Processing::load_queue();

			$cols = array(
				"cb" => "<input type=\"checkbox\" />",
				"title" => __( 'Automation' , 'inbound-pro' ),
				"status" => __( 'Automation Status' , 'inbound-pro' )
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

				case "status":
					$rule = get_post_meta($post->ID, 'inbound_rule', true);
					$status = ( !isset($rule['status']) || $rule['status'] == 'on' ) ? 'on' : 'off';
					?>
					<label class="switch switch-green">
						<input type="checkbox" class="switch-input toggle-rule-status" data-rule-id="<?php echo $post->ID;?>" <?php echo ($status == 'on') ? 'checked' : ''; ?>>
						<span class="switch-label " data-on="On" data-off="Off" ></span>
						<span class="switch-handle "></span>
					</label>
					<?php
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
		public static function enqueue_admin_scripts( $hook ) {
			$screen = get_current_screen();

			if (isset($screen) && $screen->id == 'edit-automation' ) {
				/* loads scripts and stylings into automation list view */
				wp_enqueue_style( 'automation-list' , INBOUND_AUTOMATION_URLPATH . 'assets/css/admin/admin.list.css' );
				wp_enqueue_script( 'automation-list' , INBOUND_AUTOMATION_URLPATH . 'assets/js/admin.rules-list.js' );

				/* load Sweet Alert */
				wp_enqueue_script('sweetalert', INBOUND_AUTOMATION_URLPATH . 'assets/libraries/SweetAlert/dist/sweetalert.min.js');
				wp_enqueue_style('sweetalert', INBOUND_AUTOMATION_URLPATH . 'assets/libraries/SweetAlert/dist/sweetalert.css');

			}
		}

		public static function setup_menus() {
			if ( !current_user_can('manage_options')) {
				remove_menu_page( 'edit.php?post_type=automation' );
			}
		}

		public static function calculate_tasks($rule_id) {

			global $wpdb;

			$table_name = $wpdb->prefix . "inbound_automation_queue";


			$query = 'SELECT * FROM '.$table_name.' WHERE rule_id = "'.intval($rule_id).'"';
			$results = $wpdb->get_results( $query , ARRAY_A );

			return count($results);;

		}

		/**
		 *	Adds quick links to row listing
		 */
		public static function add_row_actions($actions, $post) {

			if ( $post->post_type != 'automation' ) {
				return $actions;
			}

			$actions['clear'] = '<a class="clear-queued-tasks" data-rule-id="'.$post->ID.'" href="#clearing" title="'
				. esc_attr(__( 'Clear Queued Tasks', 'inbound-pro' ))
				. '">' .	__( 'Clear Queued Tasks', 'inbound-pro' ) . ' (' . self::calculate_tasks( $post->ID ) .')' . '</a>';

			return $actions;
		}

		/**
		 * Adds additional options to bulk edit fields
		 */
		public static function register_bulk_edit_fields() {
			global $post_type;

			if ($post_type != 'automation') {
				return;
			}

			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {

					jQuery('<option>').val('turn-off').text('<?php _e('Turn Off Rule', 'inbound-pro' ) ?>').appendTo("select[name='action']");
					jQuery('<option>').val('turn-off').text('<?php _e('Turn Off Rule', 'inbound-pro' ) ?>').appendTo("select[name='action2']");
					jQuery('<option>').val('turn-on').text('<?php _e('Turn On Rule', 'inbound-pro' ) ?>').appendTo("select[name='action']");
					jQuery('<option>').val('turn-on').text('<?php _e('Turn On Rule', 'inbound-pro' ) ?>').appendTo("select[name='action2']");
				});
			</script>
			<?php
		}

		/**
		 * process bulk actions for wp-lead post type
		 */
		public static function process_bulk_actions() {

			if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != 'automation' || !isset($_REQUEST['post'])) {
				return;
			}

			if (!current_user_can('manage_options')) {
				die();
			}

			$rule_ids = array_map('intval', $_REQUEST['post']);

			switch ($_REQUEST['action']) {
				case 'turn-off':
					$added = 0;
					foreach ($rule_ids as $rule_id) {
						self::ajax_toggle_rule_status( $rule_id , 'off');
						$added++;
					}
					$sendback = add_query_arg(array('added' => $added, 'post_type' => 'automation', 'ids' => join(',', $rule_ids)), $sendback);
					break;
				case 'turn-on':
					$added = 0;
					foreach ($rule_ids as $rule_id) {
						self::ajax_toggle_rule_status( $rule_id , 'on');
						$added++;
					}
					$sendback = add_query_arg(array('added' => $added, 'post_type' => 'automation', 'ids' => join(',', $rule_ids)), $sendback);
					break;
			}

			// 4. Redirect client
			wp_redirect($sendback);
			exit();

		}

		/**
		 * @param $rule_id
		 */
		public static function delete_rule_tasks( $rule_id ) {
			global $wpdb;

			$table_name = $wpdb->prefix . "inbound_automation_queue";

			$args = array(
				'rule_id' => $rule_id
			);

			$wpdb->delete( $table_name , $args );

		}

		/**
		 * Ajax handler to toggle rule status
		 */
		public static function ajax_toggle_rule_status( $rule_id = null , $status = 'on') {
			$rule_id = (isset($_REQUEST['rule_id'])) ? intval($_REQUEST['rule_id']) : $rule_id;
			$rule = get_post_meta($rule_id, 'inbound_rule', true);
			$status = ( isset($_REQUEST['status']) ) ? sanitize_text_field($_REQUEST['status']) : $status;
			$rule['status'] = $status;

			if (defined('DOING_AJAX') && DOING_AJAX) {
				echo update_post_meta($rule_id, 'inbound_rule', $rule);
				exit;
			} else {
				return update_post_meta($rule_id, 'inbound_rule', $rule);
			}

		}

		/**
		 * Delete all rules related
		 */
		public static function ajax_clear_rule_tasks() {
			$rule_id = intval($_REQUEST['rule_id']);

			self::delete_rule_tasks($rule_id);
			echo $rule_id;
			exit;
		}
	}

	/* Load Automation Post Type Pre Init */
	new Inbound_Automation_Post_Type();
}
