<?php
/**
 * Class adds clone feature to `inbound-email` CPT listing screen
 * @package Mailer
 * @subpackage Management
 */

class Inbound_Mailer_Clone_Post {
	
	/**
	*	Initiates class Inbound_Mailer_Clone_Post
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
		add_action('admin_action_inbound_email_clone_post', array( __CLASS__ , 'clone_post' ) );
	}

	/**
	*	Adds quick links to row listing
	*/
	public static function add_row_actions($actions, $post) {
		
		if ( $post->post_type != 'inbound-email' ) {
			return $actions;
		}

		$actions['clone'] = '<a href="'. self::build_clone_link( $post->ID , 'display', true ).'" title="'
		. esc_attr(__( 'Clone this item' , 'inbound-pro' ))
		. '">' .	__( 'Clone' , 'inbound-pro' ) . '</a>';

        $actions['view'] = '<a href="' . get_permalink($post->ID)
        . '&TB_iframe=true&width=600&height=1000" class="thickbox inbound-thickbox"'
        . 'title="' . esc_attr(__( 'Preview the email', 'inbound-pro' ))
        . '">' . __('Preview Email', 'inbound-pro') . '</a>';

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
		
		$action_name = "inbound_email_clone_post";

		if ( 'display' == $context )
		$action = '?action='.$action_name.'&amp;post='.$post->ID;
		else
		$action = '?action='.$action_name.'&post='.$post->ID;

		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )
		return;

		return apply_filters( 'inbound_email_build_clone_link' , admin_url( "admin.php". $action ), $post->ID, $context );
	}

	/**
	*	Clones CTA & redirects
	*/
	public static function clone_post($status = '')
	{
		// Get the original post
		$id = (isset($_GET['post']) ) ? intval($_GET['post']) : intval($_POST['post']);
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
			wp_die(esc_attr(__( 'Copy creation failed, could not find original:', 'inbound-pro' )) . ' ' . $id);
		}
	}
	
	/**
	*	Copt CTA & insert clone into databae
	*/
	public static function clone_post_callback($post, $status = '', $parent_id = '', $blank = false ) {
		$prefix = "";
		$suffix = "";

		if (!is_object($post)&&is_numeric($post))
		{
			$post = get_post($post);
		}

		$status = $post->post_status;

		if ($post->post_type == 'revision' || $post->post_type == 'attachment' ) {
			return;
		}


		$prefix = __( "Copy of " , 'inbound-pro' );
		$suffix = "";
		$status = ($status=='sent') ? 'unsent' : $status;
		

		$new_post_author = wp_get_current_user();

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

		$new_post_id = wp_insert_post($new_post);

		$meta_data = get_post_meta($post->ID);
		
		/* destroy any past statistics */
		unset($meta_data['inbound_statistics']);

		foreach ($meta_data as $key=>$value) {
			if ($key=='inbound_settings') {
				$value[0] = unserialize( $value[0] );

				/* clean up broken font colors */
				foreach ($value[0]['variations'] as $vid => $data) {
					foreach ($data['acf'] as $k => $v) {
						if (is_array($v) && count($v) > 1 && strstr('#',$v[1]) ) {
							$value[0]['variations'][$vid]['acf'][$k] = $v[1];
						}
					}
				}
			}

			update_post_meta($new_post_id , $key , $value[0]);
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

$Inbound_Mailer_Clone_Post = new Inbound_Mailer_Clone_Post();
