<?php

/**
*	This class loads miscellaneous WordPress AJAX listeners 
*/
class CTA_Ajax_Listeners {
	
	/**
	*	Initializes classs
	*/
	public function __construct() {
		self::load_hooks();
	}

	/**
	*	Loads hooks and filters
	*/
	public static function load_hooks() {
		
		/* Add listener to clear "all" CTA statistics */
		add_action( 'wp_ajax_wp_cta_clear_all_cta_stats', array( __CLASS__ , 'clear_all_stats') );
		
		/* Adds listener to clear CTA stats */
		add_action( 'wp_ajax_wp_cta_clear_stats_action', array( __CLASS__ , 'clear_stats' ) );

		/* Adds listener to clear CTA Variation stats	*/
		add_action( 'wp_ajax_wp_cta_clear_variation_stats', array( __CLASS__ , 'clear_variation_stats' ) );
		
		/* Adds listener to record CTA Variation impression */
		add_action('wp_ajax_wp_cta_record_impressions', array( __CLASS__ , 'record_impression' ) );
		add_action('wp_ajax_nopriv_wp_cta_record_impressions', array( __CLASS__ , 'record_impression' ) );
		
		/* Adds listener to record CTA variation conversions */
		add_action('wp_ajax_wp_cta_record_conversion', array( __CLASS__ , 'record_conversion' ) );
		add_action('wp_ajax_nopriv_wp_cta_record_conversion', array( __CLASS__ , 'record_conversion' ) );

		/* Adds listener to save CTA post meta */
		add_action( 'wp_ajax_wp_wp_call_to_action_meta_save', array( __CLASS__ , 'save_meta' ) );
		
		/* Adds listener to serve next cta variation in line & update markers */
		add_action( 'wp_ajax_nopriv_cta_get_variation', array( __CLASS__ , 'serve_varition' ) );
	}
	
	/**
	* Clears all CTA Stats
	*/
	public static function clear_all_stats() {
		global $wpdb, $CTA_Call_To_Action_Post_Type;

		$CTA_Call_To_Action_Post_Type->clear_all_cta_stats();
		
		header('HTTP/1.1 200 OK');
		exit;
	}
	
	/**
	*	Clears stats for CTA given ID
	*/
	public static function clear_stats() {
		global $wpdb, $CTA_Call_To_Action_Post_Type;

		$newrules = "0";
		$post_id = mysql_real_escape_string($_POST['page_id']);

		$CTA_Call_To_Action_Post_Type->clear_cta_stats( $post_id );
		
		header('HTTP/1.1 200 OK');
		exit;
	}
	
	/**
	*	Clears stats for CTA variations given CTA ID and variation ID
	*/
	public static function clear_variation_stats() {
		global $wpdb, $CTA_Call_To_Action_Post_Type;

		$post_id = mysql_real_escape_string($_POST['page_id']);
		$vid = $_POST['variation'];

		$CTA_Call_To_Action_Post_Type->clear_cta_variation_stats( $post_id , $vid );

		header('HTTP/1.1 200 OK');
		exit;
	}
	
	/**
	*	Record impressions for CTA variation(s) given CTA ID(s) and variation ID(s)
	*/
	public static function record_impression() {
		global $wpdb; // this is how you get access to the database
		global $user_ID;
		
		if (!isset($_POST['ctas'])) {
			return;
		}
		
		$do_not_track = apply_filters('inbound_analytics_stop_track' , false );

		if ( $do_not_track ) {
			return;
		}	
		
		foreach ( $_POST['ctas'] as $cta_id => $vid ) {
			do_action('wp_cta_record_impression' , $cta_id , $vid );
		}

		//print_r($ctas);
		header('HTTP/1.1 200 OK');
		exit;
	}
	
	
	/**
	*	Record conversion for CTA variation given CTA ID and variation ID
	*/
	public static function record_conversion() {
		global $wpdb; // this is how you get access to the database
		global $user_ID;

		$cta_id = trim($_POST['cta_id']);
		$variation_id = trim($_POST['variation_id']);

		$do_not_track = apply_filters('inbound_analytics_stop_track' , false );

		if ( $do_not_track ) {
			return;
		}	
	
		do_action('wp_cta_record_conversion', $cta_id, $variation_id);

		print $cta_id;
		header('HTTP/1.1 200 OK');
		exit;
	}
	
	/**
	*	Saves meta pair values give cta ID, meta key, and meta value
	*/
	public static function save_meta() {
		global $wpdb;

		if ( !wp_verify_nonce( $_POST['nonce'], "wp-call-to-action-meta-nonce")) {
			exit("Wrong nonce");
		}

		$new_meta_val = $_POST['new_meta_val'];
		$meta_id = $_POST['meta_id'];
		$post_id = mysql_real_escape_string($_POST['page_id']);

		if ($meta_id === "main_title") {
			$my_post = array();
			$my_post['ID'] = $post_id;
			$my_post['post_title'] = $new_meta_val;

			// Update the post into the database
			wp_update_post( $my_post );
		}

		if ($meta_id === "the_content") {
			$title_save = get_post_meta($post_id, "wp-cta-main-headline", true); // fix content from removing title
			$my_post = array();
			$my_post['ID'] = $post_id;
			$my_post['post_content'] = $new_meta_val;

			// Update the post into the database
			wp_update_post( $my_post );
			add_post_meta( $post_id, "wp-cta-main-headline", $title_save, true ) or update_post_meta( $post_id, "wp-cta-main-headline", $title_save ); // fix main headline removal
		} else {
			add_post_meta( $post_id, $meta_id, $new_meta_val, true ) or update_post_meta( $post_id, $meta_id, $new_meta_val );
		}

		header('HTTP/1.1 200 OK');
		exit;
	}
	
	/**
	*  Get current variation for CTA
	*/
	public static function serve_varition() {

		/* Make Sure the right GET param is attached to continue */
		if ( !isset($_GET['cta_id']) || !is_numeric($_GET['cta_id']) ) {
			echo 'x';
			exit;
		} else 	{
			$cta_id = $_GET['cta_id'];
		}

		$variations = get_post_meta( $_GET['cta_id'] , 'wp-cta-variations' , true);
		$variations_array = json_decode( $variations , true );
		$variation_marker = get_post_meta( $_GET['cta_id'] , '_cta_ab_variation_marker' , true );
		
		if (!is_numeric($variation_marker)) {
			$variation_marker = 0;
		}

		/* get array of live variations */
		if ($variations_array) 	{
			foreach ($variations_array as $vid => $variation ) 	{
				if (!isset($variation['status']) || $variation['status'] == 'active'  ){
					$live_variations[] = $vid;
				}
			}
		}
		
		/* if no live variation return 0 */
		if (!$live_variations) {
			echo 'x';
			exit;
		}

		/* if only one live variation return the vid */
		if (count($live_variations)==1) {
			echo $live_variations[0];
			exit;
		}

		/* flip keys with values*/
		$keys_as_values = array_flip($live_variations);
		reset($keys_as_values);

		/* get vid current position */
		if (!isset($keys_as_values[$variation_marker]))	{
			$variation_marker = reset($keys_as_values);
		}

		$i = 0;
		if ( key($keys_as_values) != $variation_marker)	{
			while ((next($keys_as_values) != $variation_marker )){
				if ($i>100) {
					break;
				}
				$i++;
			}
		}


		$key = next($keys_as_values);
		$variation_marker = $live_variations[$key];
		

		if (!$variation_marker) {
			$variation_marker = reset($keys_as_values);
		}


		update_post_meta( $_GET['cta_id'] ,  '_cta_ab_variation_marker', $variation_marker);
		echo $variation_marker;
		exit;

	
	}
		
}

/* Loads CTA_Ajax_Listeners pre init */
$CTA_Ajax_Listeners = new CTA_Ajax_Listeners();