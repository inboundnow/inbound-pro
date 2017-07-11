<?php

/**
 * Class adds row actions to landing-page CPT listing page. Should be moved into class.post-type.landing-pages.php
 * @package LandingPages
 * @subpackage Management
 */

class Landing_Pages_Row_Actions {

    /**
     * Initiate class
     */
    public function __construct() {
        self::load_hooks();
    }

    /**
     * Loads hooks and filters
     */
    public static function load_hooks() {
        /* add admin action to clone post */
        add_action('admin_action_clone_landing_page', array( __CLASS__ , 'clone_landing_page' ) );

        /* adds 'clone' links to posts */
        add_filter('post_row_actions', array( __CLASS__ ,'add_clone_link' ), 10, 2);
        
        /* adds 'clear stats' links to posts */
        add_filter('post_row_actions', array( __CLASS__ ,'add_clear_stats_link' ), 10, 2);
    }

    /**
     * Adds close links to quick actions in a post types listing area
     * @param $actions
     * @param $post
     * @return mixed
     */
    public static function add_clone_link($actions, $post) {

        if ($post->post_type != 'landing-page' ) {
            return $actions;
        }

        $actions['clone'] = '<a href="'.self::get_clone_link( $post->ID ).'" title="'
            . esc_attr(__("Clone this item", 'landing-pages'))
            . '">' .  __('Clone', 'landing-pages') . '</a>';

        return $actions;
    }

    /**
     * Builds the clone action link
     * @param int $id
     * @param bool|true $draft
     */
    public static function get_clone_link( $id = 0 ) {

        if ( !$post = get_post( $id ) ) {
            return;
        }


        $link = add_query_arg( array('action'=>'clone_landing_page' , 'post' => $post->ID ) , admin_url("admin.php"));

        return $link;
    }


    /**
     * Creates cloned landing page and opens it for user
     * @param string $status
     */
    public static function clone_landing_page($status = 'pending') {

        /* Get the original post */
        $id = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']) );
        $post = get_post($id);

        /* Copy the post and insert it */
        if (!isset($post) || !$post) {
            $post_type_obj = get_post_type_object( $post->post_type );
            wp_die(esc_attr(__('Copy creation failed, could not find original:', 'landing-pages')) . ' ' . $id);
        }

        if (!is_object($post)&&is_numeric($post)) {
            $post = get_post($post);
        }

        $status = $post->post_status;

        /* We don't want to clone revisions */
        if ($post->post_type == 'revision') {
            return;
        }


        $prefix = "Copy of ";
        $suffix = "";
        $status = 'pending';


        $new_post_author = self::get_current_user();


        $new_post = array(
            'menu_order' => $post->menu_order,
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_author' => $new_post_author->ID,
            'post_content' => $post->post_content,
            'post_excerpt' =>  $post->post_excerpt ,
            'post_mime_type' => $post->post_mime_type,
            'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
            'post_password' => $post->post_password,
            'post_status' => $status,
            'post_title' => $prefix.$post->post_title.$suffix,
            'post_type' => $post->post_type,
        );

        $new_post['post_date'] = $new_post_date = $post->post_date;
        $new_post['post_date_gmt'] = get_gmt_from_date($new_post_date);

        $new_post_id = wp_insert_post($new_post);

        $meta_data = self::get_meta($post->ID);
        foreach ($meta_data as $key => $value) {
            update_post_meta($new_post_id, $key, $value);
        }


        wp_redirect(admin_url('edit.php?post_type=' . $post->post_type));

        exit;
    }

    /**
     * Get current user
     * @return OBJECT $user
     */
    public static function get_current_user() {

        if (function_exists('wp_get_current_user')) {
            return wp_get_current_user();
        } else if (function_exists('wp_get_current_user')) {
            global $userdata;
            $userdata = wp_get_current_user();
            return $userdata;
        } else {
            $user_login = $_COOKIE[USER_COOKIE];
            $current_user = $wpdb->get_results("SELECT * FROM $wpdb->users WHERE user_login='$user_login'");
            return $current_user;
        }

    }

    /**
     * Gets meta data of landing page from landing page id
     * @param $post_id
     * @return array
     */
    public static function get_meta($landing_page_id) {


        global $wpdb;
        $data = array();
        $wpdb->query("
            SELECT `meta_key`, `meta_value`
            FROM $wpdb->postmeta
            WHERE `post_id` = $landing_page_id
	    ");
        foreach ($wpdb->last_result as $k => $v) {
            $data[$v->meta_key] = $v->meta_value;
        };
        return $data;
    }
    
    /**
     * Adds clear stats link to quick actions in a post types listing area
     * @param $actions
     * @param $post
     * @return mixed
     */
    public static function add_clear_stats_link($actions, $post) {

        if ($post->post_type != 'landing-page' ) {
            return $actions;
        }
	// .clear_stats is listened to by ajax.clearstats.js
        $actions['clear_the_stats'] = '<a id="'.$post->ID.'" title="'
            . esc_attr(__("Clear the stats?", 'landing-pages'))
            . '"class="clear_stats"'
            . 'style="cursor:pointer;">' .  __('Clear Stats', 'landing-pages') . '</a>';

        return $actions;
    }
}

new Landing_Pages_Row_Actions;
