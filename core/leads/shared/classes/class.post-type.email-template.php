<?php

if ( !class_exists('Inbound_Email_Templates_Post_Type') ) {

	class Inbound_Email_Templates_Post_Type {

		function __construct() {
			self::load_hooks();
		}

		private function load_hooks() {
			/* Register Email Templates Post Type */			
			add_action( 'init' , array( __CLASS__ , 'register_post_type' ), 11);
			add_action( 'init' , array( __CLASS__ , 'register_category_taxonomy' ), 11);
			
			
			/* Load Admin Only Hooks */
			if (is_admin()) {
			
				/* Register Email Templates on Activation */
				add_action( "inbound_shared_activate", array( __CLASS__ , 'register_templates' ) );
			
				/* Register Columns */
				add_filter( 'manage_email-template_posts_columns' , array( __CLASS__ , 'register_columns') );
				
				/* Prepare Column Data */
				add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );
			
				/* Define Sortable Columns */
				add_filter( 'manage_edit_email-template_sortable_columns', array( __CLASS__ , 'define_sortable_columns' ) );
				
				/* Filter Row Actions */
				add_filter( 'post_row_actions' , array( __CLASS__ , 'filter_row_actions' ) , 10 , 2 );
				
				/* Add Category Filter */
				add_action( 'restrict_manage_posts', array(	__CLASS__ ,'add_category_taxonomy_filter' ));
				
				/* Remove Delete from Bulk Actions */
				add_filter( 'bulk_actions-edit-email-template' , array( __CLASS__ , 'remove_bulk_actions' ) );
				
			
			} else {	
			
				/* Setup Preview */
				add_action( 'wp' , array( __CLASS__ , 'preview_template' ));
			}
			
		}

		public static function register_post_type() {

			$labels = array(
				'name' => __('Email Templates', 'leads'),
				'singular_name' => __( 'Email Templates', 'inbound-pro' ),
				'add_new' => __( 'Add New Email Templates', 'inbound-pro' ),
				'add_new_item' => __( 'Create New Email Templates' , 'inbound-pro' ),
				'edit_item' => __( 'Edit Email Templates' , 'inbound-pro' ),
				'new_item' => __( 'New Email Templates' , 'inbound-pro' ),
				'view_item' => __( 'View Email Templates' , 'inbound-pro' ),
				'search_items' => __( 'Search Email Templates' , 'inbound-pro' ),
				'not_found' =>	__( 'Nothing found' , 'inbound-pro' ),
				'not_found_in_trash' => __( 'Nothing found in Trash' , 'inbound-pro' ),
				'parent_item_colon' => ''
			);

			/* Menu to place email templates sub menu into */
			$labels = apply_filters( 'inbound_email-template_labels' , $labels );
			$post_type = apply_filters( 'inbound_email-template_submenu_placement' , 'wp-lead' );

			$args = array(
				'labels' 				=> $labels,
				'public'				=> false,
				'publicly_queryable' 	=> true,
				'show_ui' 				=> true,
				'query_var' 			=> true,
				//'menu_icon' 			=> INBOUDNOW_SHARED_URLPATH . '/images/email.png',
				'show_in_menu'			=> 'edit.php?post_type=' . $post_type ,
				'capability_type' 		=> 'post',
				'hierarchical' 			=> false,
				'menu_position' 		=> null,
				'show_in_nav_menus'		=> false,
				'supports'				=> array('title' , 'custom-fields' )
			);

			register_post_type( 'email-template' , $args );

		}
		
		/* Register Category Taxonomy */
		public static function register_category_taxonomy() {
			$args = array(
				'hierarchical' => true,
				'label' 				=> __( 'Categories' , 'leads'),
				'singular_label' 		=> __( 'Email Template Category' , 'leads'),
				'show_ui' 				=> true,
				'query_var'				=> true,
				"rewrite" 				=> true,
				'show_in_nav_menus'		=> false,
			);

			register_taxonomy('email_template_category', array('email-template'), $args);
		}
		
		/* Register Columns */
		public static function register_columns( $cols ) {

			$cols = array(
				"cb" => "<input type=\"checkbox\" />",
				"title" => __( 'Email Templates' , 'inbound-pro' ),
				"category" => __( 'Category' , 'inbound-pro' ),
				"description" => __( 'Description' , 'inbound-pro' )
			);

			$cols = apply_filters('email_template_change_columns',$cols);

			return $cols;
		}
		
		/* Prepare Column Data */
		public static function prepare_column_data( $column , $post_id ) {
		
			$post_type = get_post_type( $post_id );

			if ( $post_type !='email-template' ){
				return $column;
			}

			switch ( $column ) {
				case "title":
					echo get_the_title( $post_id );
					break;
				case "category":
					$terms = wp_get_post_terms( $post_id, 'email_template_category' );
					foreach ($terms as $term) {
						$term_link = get_term_link( $term , 'email_template_category' );
						echo '<a href="'.$term_link.'">'.$term->name.'</a> ';
					}
					break;
				case "description":
					$description = get_post_meta( $post_id , 'inbound_email_description' , true );
					echo $description;
					break;

			}

			do_action('email_template_custom_columns',$column, $post_id);
		}
		
		/* Define Sortable Columns */
		public static function define_sortable_columns($columns) {

			$columns = apply_filters('',$columns);

			return $columns;
		}
		
		/* Removes ability to delete template from row action if it's a core template */
		public static function filter_row_actions( $actions , $post ) {
			
			if ($post->post_type =="email-template"){
				if ( has_term('inbound-core','email_template_category' , $post) || has_term('wordpress-core','email_template_category' , $post) ) {
					unset($actions['trash']);
				}
			}
			
			return $actions;
		}
		
		/* Remove Bulk Actions */
		public static function remove_bulk_actions( $actions ){
			unset( $actions[ 'delete' ] );
			unset( $actions[ 'trash' ] );
			return $actions;
		}
		
		/* Adds ability to filter email templates by custom post type */
		public static function add_category_taxonomy_filter() {
			global $typenow;
		 
			// an array of all the taxonomyies you want to display. Use the taxonomy name or slug
			$taxonomies = array('email_template_category');
		 
			// must set this to the post type you want the filter(s) displayed on
			if( $typenow == 'email-template' ){
		 
				foreach ($taxonomies as $tax_slug) {
					$tax_obj = get_taxonomy($tax_slug);
					$tax_name = $tax_obj->labels->name;
					$terms = get_terms($tax_slug);
					if(count($terms) > 0) {
						echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
						echo "<option value=''>Show All $tax_name</option>";
						foreach ($terms as $term) { 
							echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; 
						}
						echo "</select>";
					}
				}
			}
		}
		
		public static function preview_template() {
			global $post;

			if ( isset($post) && $post->post_type =='email-template' ){
			
				$user = wp_get_current_user();

				$Inbound_Templating_Engine = Inbound_Templating_Engine();				
				$body = get_post_meta( $post->ID , 'inbound_email_body_template' , true );
				
				/* Prepare Demo Data */
				$args = array(
					/* Comment Data */
					array(
						'wp_comment_id' => 1,
						'wp_comment_url' => get_permalink(1).'#comments-1',
						'wp_comment_author' => 'Comment Author',
						'wp_comment_author_email' =>	'noreply@inboundnow.com' ,
						'wp_comment_author_url' =>	 'http://www.inboundnow.com/about/',
						'wp_comment_author_ip' =>	'1.1.1.1.1' ,
						'wp_comment_date' => date('F jS, Y \a\t g:ia', current_time( 'timestamp', 0 )),
						'wp_comment_content' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'
					),
					/* Post Data */
					array(
						'wp_post_id' => '1',
						'wp_post_title' => 'Hello World',
						'wp_post_url' => 'http://www.google.com/earth/',
						'wp_post_date' => date('F jS, Y \a\t g:ia', current_time( 'timestamp', 0 )),
						'wp_post_content' => 'The standard Lorem Ipsum passage, used since the 1500s

"Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."

Section 1.10.32 of "de Finibus Bonorum et Malorum", written by Cicero in 45 BC

"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?"',
						'wp_post_excerpt' => '"Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."'
					),
					/* user data */
					array(
						'wp_user_id' => $user_id,
						'wp_user_login' => stripslashes($user->user_login),
						'wp_user_email' => stripslashes($user->user_email),
						'wp_user_first_name' => stripslashes($user->first_name),
						'wp_user_last_name' => stripslashes($user->last_name),
						'wp_user_password' => stripslashes($plaintext_pass),
						'wp_user_nicename' => stripslashes($user->nice_name),
						'wp_user_displayname' => stripslashes($user->display_name),
						'wp_user_gravatar_url' => '//www.gravatar.com/avatar/00000000000000000000000000000000',
					),
					/* lead data */
					array(
						'lead_id' => '101',
						'lead_email_address' => 'example@inboundnow.com',
						'lead_first_name' => 'Example',
						'lead_last_name' => 'Lead',
						'lead_company_name' => 'Inbound Now',
						'lead_address_line_1' => '700 Grapefruit Dr.',
						'lead_address_line_2' => 'Suite 101',
						'lead_city' => 'San Francisco',
						'lead_region' => 'California',
						'form_name' => 'Call to Action Singup Form',
						'source' => 'http://www.mysite.com/some-page/'
					)
				);
				
				$_POST = array(
					'First Name' => 'Example',
					'Last Name' => 'Lead',
					'Email Address' => 'example@inboundnow.com',
				);
				
				$body = $Inbound_Templating_Engine->replace_tokens( $body , $args	);

				echo $body;
				exit;
			}
		}
		
		/**
		*  Insert Core Email Templates into database
		*/
		public static function register_templates() {
			
			/* Load Template Files */
			$inbound_email_templates = self::load_template_files();
			self::register_post_type();
			self::register_category_taxonomy();
			
			/* Create inbound-core Category Term */
			if ( !term_exists( 'inbound-core' , 'email_template_category' ) ) {
				wp_insert_term( 'inbound-core' , 'email_template_category' , array( 'description'=> 'Belongs to Inbound Now\'s set of core templates. Can be edited but not deleted.' , 'slug' => 'inbound-core' ) );
			}		
			
			/* Create wordpress-core Category Term */
			if ( !term_exists( 'wordpress-core' , 'email_template_category' ) ) {
				wp_insert_term( 'wordpress-core' , 'email_template_category' , array( 'description'=> 'Belongs to Inbound Now\'s set of	WordPress core templates. Can be edited but not deleted.' , 'slug' => 'wordpress-core' ) );
			}
			
			/* Create Default Template for Lead Conversion Notifications */
			self::create_template( array(
				'id' => 'token-test',
				'title' => __( 'Token Testing' , 'leads') ,
				'subject' => __( 'Token Testing Template - {{site-name}}', 'inbound-pro' ) ,
				'body' => $inbound_email_templates['token-test'],
				'description' => __( 'Designed for testing & debugging tokens.' , 'inbound-pro' ) ,
				'email_template_category' => 'inbound-core'
			));
			
			/* Create Default Template for Lead Conversion Notifications */
			self::create_template( array(
				'id' => 'inbound-new-lead-notification',
				'title' => __( 'New Lead Notification' , 'leads') ,
				'subject' => __( '{{site-name}} - {{form-name}} - New Lead Conversion', 'inbound-pro' ) ,
				'body' => $inbound_email_templates['inbound-new-lead-notification'],
				'description' => __( 'Designed for notifying administrator of new lead conversion when an Inbound Form is submitted.' , 'inbound-pro' ) ,
				'email_template_category' => 'inbound-core'
			));

			/* New User Account Notification - Create WP Core Template for New User Notifications */
			self::create_template( array(
				'id' => 'wp-new-user-notification',
				'title' => __( 'New User Signup Notification' , 'inbound-pro' ),
				'subject' => __( 'Your New Account - {{site-name}}' , 'inbound-pro' ),
				'body' => $inbound_email_templates['wp-new-user-notification'],
				'description' => __( 'WordPress core template for notifying	new users of their	created accounts.' , 'inbound-pro' ),
				'email_template_category' => 'wordpress-core'
			));
			
			/* New Comment Notifications - Create WP Core Template for Post Author Notifications */
			self::create_template( array(
				'id' => 'wp-notify-post-author',
				'title' => __( 'New Comment Notification' , 'inbound-pro' ),
				'subject' => __( 'New Comment Posted - {{wp-post-title}} - {{site-name}}' , 'inbound-pro' ),
				'body' => $inbound_email_templates['wp-notify-post-author'],
				'description' => __( 'WordPress core template for notifying post authors of new comments.' , 'inbound-pro' ),
				'email_template_category' => 'wordpress-core'
			));
			
			/* Comment Moderation Notifications - Create WP Core Template for Comment Moderation Notifications */
			self::create_template( array(
				'id' => 'wp-notify-moderator',
				'title' => __( 'New Comment Moderation' , 'inbound-pro' ),
				'subject' => __( 'Moderate Comment - {{wp-post-title}} - {{site-name}}' , 'inbound-pro' ),
				'body' => $inbound_email_templates['wp-notify-moderator'],
				'description' => __( 'WordPress core template for notifying post authors of new comments that need moderating.' , 'inbound-pro' ),
				'email_template_category' => 'wordpress-core'
			));
		
		}
		
		/* Creates Email Template */
		public static function create_template( $args ) {
			/* Create Default New Lead Notification Template */
			$template = get_page_by_title ( $args['title'] , OBJECT , 'email-template' );
			
			if ( !$template ) {
			
				$template_id = wp_insert_post(
					array(
						'post_title'	 =>	$args['title'],
						'post_status'	=> 'publish',
						'post_type'		=> 'email-template'
					)
				);
				
					
				add_post_meta( $template_id , 'inbound_email_subject_template', $args['subject'] );
				add_post_meta( $template_id , 'inbound_email_body_template', $args['body'] );
				add_post_meta( $template_id , 'inbound_email_description', $args['description'] );
				
				
				if ($args['email_template_category']) {
					$term = get_term_by( 'slug' , $args['email_template_category'] , 'email_template_category' , OBJECT );				
					$result = wp_set_post_terms( $template_id , $term->term_id , 'email_template_category' );
				}
				
				if ($args['id']) {					
					add_post_meta( $template_id , '_inbound_template_id', $args['id'] );
				}
			}	 
		}
		
		public static function load_template_files() {
			/* Load Email Templates Into $inbound_email_templates */			
			include_once( INBOUDNOW_SHARED_PATH . 'templates/email-templates/inbound-new-lead-notification/inbound-new-lead-notification.php');
			include_once( INBOUDNOW_SHARED_PATH . 'templates/email-templates/wp-new-user-notification/wp-new-user-notification.php');
			include_once( INBOUDNOW_SHARED_PATH . 'templates/email-templates/wp-notify-post-author/wp-notify-post-author.php');
			include_once( INBOUDNOW_SHARED_PATH . 'templates/email-templates/wp-notify-moderator/wp-notify-moderator.php');
			include_once( INBOUDNOW_SHARED_PATH . 'templates/email-templates/token-test/token-test.php');
			
			return $inbound_email_templates;
		}
	}
	
	/* Load Email Templates Post Type Pre Init */
	$GLOBALS['Inbound_Email_Templates_Post_Type'] = new Inbound_Email_Templates_Post_Type();
}
