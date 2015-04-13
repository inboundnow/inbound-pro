<?php
/*
Inbound Scripts and CSS Enqueue
*/

if (!class_exists('Inbound_Asset_Loader')) {
	class Inbound_Asset_Loader {
		static $load_assets;

		static function load_inbound_assets() {
		  self::$load_assets = true;
		  add_action('wp_enqueue_scripts', array(__CLASS__, 'register_scripts_and_styles'), 101);
		  add_action('admin_enqueue_scripts', array(__CLASS__, 'register_scripts_and_styles'), 101);
		}

		/**
		 * Registers and enqueues stylesheets for the administration panel and the
		 * public facing site.
		 *
		 * Example:
		 * self::enqueue_shared_file('SCRIPT-ID',  INBOUDNOW_SHARED_PATH . 'assets/js/frontend/path-in-shared-assets.js', 'localized_var_name', $localized_array_values, $dependancies_array );
		 */
		static function register_scripts_and_styles() {
			/* Frontent and Backend Files */


			/* Conditionals for admin or frontend */
			if(is_admin()) {

				//self::enqueue_shared_file('inbound-analytics', 'assets/js/frontend/analytics/inboundAnalytics.js', array( 'jquery' ), 'inbound_settings', self::localize_lead_data());

				self::enqueue_shared_file('jquery-cookie', 'assets/js/global/jquery.cookie.js', array( 'jquery' ));
				self::enqueue_shared_file('jquery-total-storage', 'assets/js/global/jquery.total-storage.min.js', array( 'jquery' ));
				$inbound_now_screens = Inbound_Compatibility::return_inbound_now_screens(); // list of inbound now screens
				$screen = get_current_screen();

				/* Target Specific screen with // echo $screen->id; */

				if ( $screen->id == 'wp-call-to-action') {
					self::enqueue_shared_file('image-picker-js', 'assets/js/admin/image-picker.js');
					self::enqueue_shared_file('image-picker-css', 'assets/css/admin/image-picker.css');
				}
				/* Metabox CSS */
				self::enqueue_shared_file('inbound-metaboxes', 'assets/css/admin/inbound-metaboxes.css');
				self::enqueue_shared_file('inbound-global-styles', 'assets/css/admin/global-inbound-admin.css');

			} else {

				global $wp_scripts;

				if ( !empty( $wp_scripts->queue ) ) {
					  $store = $wp_scripts->queue; // store the scripts
					  foreach ( $wp_scripts->queue as $handle ) {
						  wp_dequeue_script( $handle );
					  }
				}
				
				/* unminified source available */
				self::enqueue_shared_file('inbound-analytics', 'assets/js/frontend/analytics/inboundAnalytics.min.js', array( 'jquery' ), 'inbound_settings', self::localize_lead_data());

				if (is_array($store)) {
					foreach ( $store as $handle ) {
						wp_enqueue_script( $handle );
					}
				}

			}
		} // end register_scripts_and_styles

		/**
		 * Helper function for registering and enqueueing scripts and styles.
		 *
		 * @name	The 	ID to register with WordPress
		 * @file_path		The path to the actual file inside /shared/assets/
		 * @localize_array	Optional argument for the localized array
		 * @deps 			js dependancies by name example 'jquery'
		 * @localize_var 	the localized variable name
		 */
		static function enqueue_shared_file($name, $path, $deps = array(), $localize_var = null, $localize_array = array()) {
			$is_script = false;
			$deps = (empty($deps)) ? array() : $deps;
			$url = INBOUDNOW_SHARED_URLPATH . $path;
			$file = INBOUDNOW_SHARED_PATH . $path;

			$file_type = strpos($path, '.js');
			if (!(false === $file_type)) { $is_script = true; }

			if(file_exists($file)) {
				if($is_script) {
					wp_register_script($name, $url, $deps);
					wp_enqueue_script($name);

					if ($localize_var != null) {
						wp_localize_script( $name , $localize_var, $localize_array );
					}
				} else {
					wp_register_style($name, $url);
					wp_enqueue_style($name);
				}
			}

		}

		/* Global Specific localize functions */
		static function localize_lead_data() {
			global $post;
			$post_id = null;
			$id_check = false;
			$page_tracking = 'on';
			$search_tracking = 'on';
			$comment_tracking = 'on';
			$post_type = isset($post) ? get_post_type( $post ) : null;
			$current_page = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			$ip_address = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0.0';
			$lead_id = (isset($_COOKIE['wp_lead_id'])) ? $_COOKIE['wp_lead_id'] : false;
			$lead_email = (isset($_COOKIE['wp_lead_email'])) ? $_COOKIE['wp_lead_email'] : false;
			$lead_uid = (isset($_COOKIE['wp_lead_uid'])) ? $_COOKIE['wp_lead_uid'] : false;
			$custom_map_values = array();
			$custom_map_values = apply_filters( 'inboundnow_custom_map_values_filter' , $custom_map_values);
			// Get correct post ID

			global $wp_query;
			$current_page_id = $wp_query->get_queried_object_id();
			$post_id = $current_page_id;
			$id_check = ($post_id != null) ? true : false;

			if (!is_archive() && !$id_check){
			   $post_id = (isset($post)) ? $post->ID : false;
			   $id_check = ($post_id != null) ? true : false;
			}
			if (!$id_check) {
				$post_id = wpl_url_to_postid($current_page);
				$id_check = ($post_id != null) ? true : false;
			}
			if(!$id_check){
				$post_id = wp_leads_get_page_final_id();
				$id_check = ($post_id != null) ? true : false;
			}

			// If page tracking on
			$lead_page_view_tracking = get_option( 'wpl-main-page-view-tracking', 1);
			$lead_search_tracking = get_option( 'wpl-main-search-tracking', 1);
			$lead_comment_tracking = get_option( 'wpl-main-comment-tracking', 1);
			if (!$lead_search_tracking) {
				$search_tracking = 'off';
			}
			if (!$lead_comment_tracking) {
				$comment_tracking = 'off';
			}
			if (!$lead_page_view_tracking) {
				$page_tracking = 'off';
			}

			// Localize lead data
			$lead_data_array = array();
			$lead_data_array['lead_id'] = ($lead_id) ? $lead_id : null;
			$lead_data_array['lead_email'] = ($lead_email) ? $lead_email : null;
			$lead_data_array['lead_uid'] = ($lead_uid) ? $lead_uid : null;
			$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
			$wordpress_date_time = date("Y/m/d G:i:s", $time);
			$inbound_track_include = get_option( 'wpl-main-tracking-ids');
			$inbound_track_exclude = get_option( 'wpl-main-exclude-tracking-ids');
			
			/* get variation id */
			if (function_exists('lp_ab_testing_get_current_variation_id')) {
				$variation = lp_ab_testing_get_current_variation_id();
			}
			
			$variation = (isset($variation)) ? $variation : 0;
			
			$inbound_localized_data = array(
				'post_id' => $post_id,
				'variation_id' => $variation,
				'ip_address' => $ip_address,
				'wp_lead_data' => $lead_data_array,
				'admin_url' => admin_url('admin-ajax.php'),
				'track_time' => $wordpress_date_time,
				'post_type' => $post_type,
				'page_tracking' => $page_tracking,
				'search_tracking' => $search_tracking,
				'comment_tracking' => $comment_tracking,
				'custom_mapping' => $custom_map_values,
				'inbound_track_exclude' => $inbound_track_exclude,
				'inbound_track_include' => $inbound_track_include
			);

			return apply_filters( 'inbound_analytics_localized_data' , $inbound_localized_data);
		} // end localize lead data

	} // end class
}

Inbound_Asset_Loader::load_inbound_assets();
