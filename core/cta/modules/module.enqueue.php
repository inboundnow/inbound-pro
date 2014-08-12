<?php

add_action('wp_enqueue_scripts','wp_cta_fontend_enqueue_scripts');
function wp_cta_fontend_enqueue_scripts($hook)
{
	global $post;
	global $wp_query;


	if (!isset($post)) {
		return;
	}

	$post_type = $post->post_type;
	$post_id = $post->ID;

	$current_page_id = $wp_query->get_queried_object_id();

	(isset($_SERVER['REMOTE_ADDR'])) ? $ip_address = $_SERVER['REMOTE_ADDR'] : $ip_address = '0.0.0.0.0';

	// Load Script on All Frontend Pages
	wp_enqueue_script('jquery');

	/* Global Lead Data */
	$lead_cpt_id = (isset($_COOKIE['wp_lead_id'])) ? $_COOKIE['wp_lead_id'] : false;
    $lead_email = (isset($_COOKIE['wp_lead_email'])) ? $_COOKIE['wp_lead_email'] : false;
    $lead_unique_key = (isset($_COOKIE['wp_lead_uid'])) ? $_COOKIE['wp_lead_uid'] : false;

	$lead_data_array = array();

	if ($lead_cpt_id) {
		$lead_data_array['lead_id'] = $lead_cpt_id;
		$type = 'wplid';}
	if ($lead_email) {
		$lead_data_array['lead_email'] = $lead_email;
		$type = 'wplemail';}
	if ($lead_unique_key) {
		$lead_data_array['lead_uid'] = $lead_unique_key;
		$type = 'wpluid';
	}

	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$wordpress_date_time = date("Y-m-d G:i:s T", $time);


	// Load on Non CTA Pages
	if (isset($post)&&$post->post_type !=='wp-call-to-action') {
		wp_enqueue_style('cta-css', WP_CTA_URLPATH . 'css/cta-load.css');
	}

	if ( current_user_can( 'manage_options' )) {
	wp_enqueue_script('frontend-cta-admin', WP_CTA_URLPATH . 'js/admin/frontend-admin-cta.js');
	}

	if (isset($post)&&$post->post_type=='wp-call-to-action')
	{

		if (isset($_GET['template-customize']) &&$_GET['template-customize']=='on') {
			// wp_register_script('lp-customizer-load-js', WP_CTA_URLPATH . 'js/customizer.load.js', array('jquery'));
			// wp_enqueue_script('lp-customizer-load-js');
			echo "<style type='text/css'>#variation-list{background:#eaeaea !important; top: 26px !important; height: 35px !important;padding-top: 10px !important;}#wpadminbar {height: 29px !important;}</style>"; // enqueue styles not firing
		}

		if (isset($_GET['live-preview-area'])) {
			show_admin_bar( false );
			wp_register_script('lp-customizer-load-js', WP_CTA_URLPATH . 'js/customizer.load.js', array('jquery'));
			wp_enqueue_script('lp-customizer-load-js');

		}
	}

}

if (is_admin()) {
	add_action('admin_enqueue_scripts','wp_cta_admin_enqueue');

	function wp_cta_admin_enqueue($hook) {
		global $post;
		$CTAExtensions = CTA_Load_Extensions();
		$screen = get_current_screen();

		global $wp_scripts; /* dequeue third party scripts */
		if ( !empty( $wp_scripts->queue ) ) {
		      $store = $wp_scripts->queue; // store the scripts
		      foreach ( $wp_scripts->queue as $handle ) {
		          wp_dequeue_script( $handle );
		      }
		}
		wp_enqueue_style('wp-cta-admin-css', WP_CTA_URLPATH . 'css/admin-style.css');

			// Frontend Editor
		if ((isset($_GET['page']) == 'wp-cta-frontend-editor')) {

		}

		//easyXDM - for store rendering
		if (isset($_GET['page']) && (($_GET['page'] == 'wp_cta_store') || ($_GET['page'] == 'wp_cta_addons'))) {
			wp_dequeue_script('easyXDM');
			wp_enqueue_script('easyXDM', WP_CTA_URLPATH . 'js/libraries/easyXDM.debug.js');
			//wp_enqueue_script('wp-cta-js-store', WP_CTA_URLPATH . 'js/admin/admin.store.js');
		}

		// Admin enqueue - Landing Page CPT only
		if (isset($post)&&'wp-call-to-action'==$post->post_type||(isset($_GET['post_type'])&&$_GET['post_type']=='wp-call-to-action' )){
		
			wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
			wp_enqueue_script('jpicker', WP_CTA_URLPATH . 'js/libraries/jpicker/jpicker-1.1.6.min.js');
			wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jpicker/images/' ));
			wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker-1.1.6.min.css');
			wp_dequeue_script('jquery-qtip');
			wp_enqueue_script('jquery-qtip', WP_CTA_URLPATH . 'js/libraries/jquery-qtip/jquery.qtip.min.js');
			wp_enqueue_script('load-qtip', WP_CTA_URLPATH . 'js/libraries/jquery-qtip/load.qtip.js', array('jquery-qtip'));
			wp_enqueue_style('qtip-css', WP_CTA_URLPATH . 'css/jquery.qtip.min.css');
			wp_enqueue_style('wp-cta-only-cpt-admin-css', WP_CTA_URLPATH . 'css/admin-wp-cta-cpt-only-style.css');
			wp_enqueue_script( 'wp-cta-admin-clear-stats-ajax-request', WP_CTA_URLPATH . 'js/ajax.clearstats.js', array( 'jquery' ) );
			wp_localize_script( 'wp-cta-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'wp_call_to_action_clear_nonce' => wp_create_nonce('wp-call-to-action-clear-nonce') ) );

			// Add New and Edit Screens
			if ($hook == 'post-new.php' || $hook == 'post.php') {
				//echo wp_create_nonce('wp-cta-nonce');exit;
				add_filter( 'wp_default_editor', 'wp_cta_ab_testing_force_default_editor' );/* force visual editor to open in text mode */
				wp_enqueue_script('wp-cta-post-edit-ui', WP_CTA_URLPATH . 'js/admin/admin.post-edit.js');
				wp_localize_script( 'wp-cta-post-edit-ui', 'wp_cta_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_call_to_action_meta_nonce' => wp_create_nonce('wp-call-to-action-meta-nonce'), 'wp_call_to_action_template_nonce' => wp_create_nonce('wp-cta-nonce') ) );

				//admin.metaboxes.js - Template Selector - Media Uploader
				wp_enqueue_script('wp-cta-js-metaboxes', WP_CTA_URLPATH . 'js/admin/admin.metaboxes.js');
				$template_data = $CTAExtensions->definitions;
				$template_data = json_encode($template_data);
				$template = get_post_meta($post->ID, 'wp-cta-selected-template', true);
				$template = apply_filters('wp_cta_selected_template',$template);
				$template = strtolower($template);
				$params = array('selected_template'=>$template, 'templates'=>$template_data);
				wp_localize_script('wp-cta-js-metaboxes', 'data', $params);

				// Conditional TINYMCE for landing pages
				//wp_dequeue_script('jquery-tinymce');
				//wp_enqueue_script('jquery-tinymce', WP_CTA_URLPATH . 'js/libraries/tiny_mce/jquery.tinymce.js');
			}

			// Edit Screen
			if ($hook == 'post.php') {
				wp_enqueue_style('admin-post-edit-css', WP_CTA_URLPATH . 'css/admin-post-edit.css');
				if (isset($_GET['frontend']) && $_GET['frontend'] === 'true') {
					//show_admin_bar( false ); // doesnt work
					wp_enqueue_style('new-customizer-admin', WP_CTA_URLPATH . 'css/new-customizer-admin.css');
					wp_enqueue_script('new-customizer-admin', WP_CTA_URLPATH . 'js/admin/new-customizer-admin.js');
				}

				wp_enqueue_script('jquery-datepicker', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.min.js');
				wp_enqueue_script('jquery-datepicker-functions', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/picker_functions.js');
				wp_enqueue_script('jquery-datepicker-base', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/base.js');
				wp_enqueue_script('jquery-datepicker-datepair', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/datepair.js');
				wp_localize_script( 'jquery-datepicker', 'jquery_datepicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jquery-datepicker/' ));
				wp_enqueue_style('jquery-timepicker-css', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.css');
				wp_enqueue_style('jquery-datepicker-base.css', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/base.css');

				/**
				wp_enqueue_script('jquery-intro', WP_CTA_URLPATH . 'js/admin/intro.js', array( 'jquery' ));
				wp_enqueue_style('intro-css', WP_CTA_URLPATH . 'css/admin-tour.css'); */
			}

			if (isset($_GET['page']) && $_GET['page'] === 'wp_cta_global_settings') {
				wp_enqueue_script('cta-settings-js', WP_CTA_URLPATH . 'js/admin/admin.global-settings.js');
			}

			// Add New Screen
			if ( $hook == 'post-new.php'){
			wp_enqueue_script('wp-cta-js-create-new', WP_CTA_URLPATH . 'js/admin/admin.post-new.js', array('jquery'), '1.0', true );
			wp_enqueue_style('wp-cta-css-post-new', WP_CTA_URLPATH . 'css/admin-post-new.css');
			}

			// List Screen
			if ( $screen->id == 'edit-wp-call-to-action') {
				wp_enqueue_script('wp-call-to-action-list', WP_CTA_URLPATH . 'js/admin/admin.wp-call-to-action-list.js');
				wp_enqueue_style('wp-call-to-action-list-css', WP_CTA_URLPATH.'css/admin-wp-call-to-action-list.css');
				wp_enqueue_script('jqueryui');
				wp_admin_css('thickbox');
				add_thickbox();
			}

		}
		/* Requeue third party scripts */
		if(is_array($store)) {
			foreach ( $store as $handle ) {
			    wp_enqueue_script( $handle );
			}
		}
	}

	// The loadtiny is specifically to load thing in the module.customizer-display.php iframe (not really working for whatever reason)
	if (isset($_GET['page'])&&$_GET['page']=='wp-cta-frontend-editor') {
		add_action('init','wp_cta_customizer_enqueue');
		add_action('wp_enqueue_scripts', 'wp_cta_customizer_enqueue');
		function wp_cta_customizer_enqueue($hook) {
			wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
			wp_dequeue_script('jquery-cookie');
			wp_enqueue_script('jquery-cookie', WP_CTA_URLPATH . 'js/jquery.cookie.js');
			wp_enqueue_style( 'wp-admin' );
			wp_admin_css('thickbox');
			add_thickbox();

			wp_enqueue_style('wp-cta-admin-css', WP_CTA_URLPATH . 'css/admin-style.css');

			wp_enqueue_script('wp-cta-post-edit-ui', WP_CTA_URLPATH . 'js/admin/admin.post-edit.js');
			wp_localize_script( 'wp-cta-post-edit-ui', 'wp_cta_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'wp_call_to_action_meta_nonce' => wp_create_nonce('wp-call-to-action-meta-nonce') ) );
			wp_enqueue_script('wp-cta-frontend-editor-js', WP_CTA_URLPATH . 'js/customizer.save.js');

			//jpicker - color picker
			wp_enqueue_script('jpicker', WP_CTA_URLPATH . 'js/libraries/jpicker/jpicker-1.1.6.min.js');
			wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jpicker/images/' ));
			wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker-1.1.6.min.css');
			wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker.css');
			wp_enqueue_style('wp-cta-customizer-frontend', WP_CTA_URLPATH . 'css/customizer.frontend.css');
			wp_dequeue_script('form-population');
			wp_dequeue_script('funnel-tracking');
			wp_enqueue_script('jquery-easing', WP_CTA_URLPATH . 'js/jquery.easing.min.js');

		}
	}
	
	function wp_cta_ab_testing_force_default_editor() {
		//allowed: tinymce, html, test
		return 'html';
	}
}
