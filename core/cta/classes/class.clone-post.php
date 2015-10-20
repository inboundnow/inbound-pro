<?php


class CTA_Clone_Post {

	/**
	*	Initiates class CTA_Clone_Post
	*/
	public function	__construct() {

		self::load_hooks();

	}

	/**
	*	Loads hooks and filters
	*/
	public static function load_hooks() {

		/* Adds quick actions to row */
		add_filter('post_row_actions', array( __CLASS__ , 'add_row_actions' ) ,8,2);

		/* Add listener for processing clone request */
		add_action('admin_action_cta_clone_post', array( __CLASS__ , 'clone_post' ) );
	}

	/**
	*	Adds quick links to row listing
	*/
	public static function add_row_actions($actions, $post) {

		if ( $post->post_type != 'wp-call-to-action' ) {
			return $actions;
		}

		$actions['clone'] = '<a href="'. self::build_clone_link( $post->ID , 'display', true ).'" title="'
		. esc_attr(__( 'Clone this item' , 'cta' ))
		. '">' .	__( 'Clone' , 'cta' ) . '</a>';

		return $actions;
	}

	/**
	*	Buids quick action link to clone cta
	*/
	public static function build_clone_link( $id = 0, $context = 'display', $draft = true )
	{

		if ( !$post = get_post( $id ) ) {
			return;
		}

		$action_name = "cta_clone_post";

		if ( 'display' == $context )
		$action = '?action='.$action_name.'&amp;post='.$post->ID;
		else
		$action = '?action='.$action_name.'&post='.$post->ID;

		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )
		return;

		return apply_filters( 'wp_cta_build_clone_link' , admin_url( "admin.php". $action ), $post->ID, $context );
	}

	/**
	*	Clones CTA & redirects
	*/
	public static function clone_post($status = '')
	{
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$post = get_post($id);

		// Copy the post and insert it
		if (isset($post) && $post!=null) {
			$new_id = self::clone_post_callback($post, $status);

			if ($status == ''){
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
			}
			exit;

		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die(esc_attr(__( 'Copy creation failed, could not find original:', 'cta' )) . ' ' . $id);
		}
	}

	/**
	*	Copt CTA & insert clone into databae
	*/
	public static function clone_post_callback($post, $status = '', $parent_id = '', $blank = false) {
		$prefix = "";
		$suffix = "";

		if (!is_object($post)&&is_numeric($post))
		{
			$post = get_post($post);
		}

		$status = $post->post_status;

		if ($post->post_type == 'revision') {
			return;
		}

		if ($post->post_type != 'attachment'){
			$prefix = "Copy of ";
			$suffix = "";
			$status = 'pending';
		}

		$new_post_author = wp_get_current_user();

		if ($blank==false)
		{
			$new_post = array(
				'menu_order' => $post->menu_order,
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'post_author' => $new_post_author->ID,
				'post_content' => $post->post_content,
				'post_excerpt' =>	$post->post_excerpt ,
				'post_mime_type' => $post->post_mime_type,
				'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
				'post_password' => $post->post_password,
				'post_status' => $status,
				'post_title' => $prefix.$post->post_title.$suffix,
				'post_type' => $post->post_type,
			);

			$new_post['post_date'] = $new_post_date =	$post->post_date ;
			$new_post['post_date_gmt'] = get_gmt_from_date($new_post_date);
		}
		else
		{
			$new_post = array(
				'menu_order' => $post->menu_order,
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'post_author' => $new_post_author->ID,
				'post_content' => "",
				'post_excerpt' =>	"" ,
				'post_mime_type' => $post->post_mime_type,
				'post_status' => $status,
				'post_title' => "New Blank Landing Page",
				'post_type' => $post->post_type,
				'post_date' => date('Y-m-d H:i:s')
			);
		}

		$new_post_id = wp_insert_post($new_post);

		$meta_data = self::get_post_meta($post->ID);
		foreach ($meta_data as $key=>$value)
		{
			update_post_meta($new_post_id,$key,$value);
		}

		return $new_post_id;
	}

	/**
	*	Direct query to get post meta - needs to be phased out
	*/
	public static function get_post_meta($post_id) {
		global $wpdb;
		$data	=	array();

		$wpdb->query("
			SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = $post_id
		");

		foreach($wpdb->last_result as $k => $v)
		{
			$data[$v->meta_key] =	$v->meta_value;
		}

		return $data;
	}
}

$CTA_Clone_Post = new CTA_Clone_Post();