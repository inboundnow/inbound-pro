<?php

add_action('admin_enqueue_scripts','lp_admin_enqueue');

function lp_admin_enqueue($hook) {
	global $post, $plugin_page;
	$screen = get_current_screen();
	$store = array();

	/* dequeue third party scripts */
	global $wp_scripts;

	if ( !empty( $wp_scripts->queue ) ) {
	      $store = $wp_scripts->queue; // store the scripts
	      foreach ( $wp_scripts->queue as $handle ) {
	          wp_dequeue_script( $handle );
	      }
	}


	//enqueue styles and scripts
	wp_enqueue_style('lp-admin-css', LANDINGPAGES_URLPATH . 'css/admin-style.css');


	// Frontend Editor
	if ( $plugin_page === 'lp-frontend-editor' ) {
	}

	if ( $plugin_page === 'install-inbound-plugins' ) {
		wp_enqueue_script('inbound-install-plugins', LANDINGPAGES_URLPATH . 'js/admin/admin.install-plugins.js');
		wp_enqueue_style('inbound-install-plugins-css', LANDINGPAGES_URLPATH . 'css/admin-install-plugins.css');
	}

	// Store Options Page
	if ( in_array( $plugin_page, array( 'lp_store', 'lp_addons' ) ) ) {
		wp_dequeue_script('easyXDM');
		wp_enqueue_script('easyXDM', LANDINGPAGES_URLPATH . 'js/libraries/easyXDM.debug.js');
		wp_enqueue_script('lp-js-store', LANDINGPAGES_URLPATH . 'js/admin/admin.store.js');
	}

	// Admin enqueue - Landing Page CPT only
	if ((isset($post) && 'landing-page'== $post->post_type)|| (isset($_GET['post_type']) && $_GET['post_type']=='landing-page' )) {

		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		wp_enqueue_script('jpicker', LANDINGPAGES_URLPATH . 'js/libraries/jpicker/jpicker-1.1.6.min.js');
		wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => LANDINGPAGES_URLPATH.'js/libraries/jpicker/images/' ));
		wp_enqueue_style('jpicker-css', LANDINGPAGES_URLPATH . 'js/libraries/jpicker/css/jPicker-1.1.6.min.css');
		wp_dequeue_script('jquery-qtip');
		wp_enqueue_script('jquery-qtip', LANDINGPAGES_URLPATH . 'js/libraries/jquery-qtip/jquery.qtip.min.js');
		wp_enqueue_script('load-qtip', LANDINGPAGES_URLPATH . 'js/libraries/jquery-qtip/load.qtip.js', array('jquery-qtip'));
		wp_enqueue_style('qtip-css', LANDINGPAGES_URLPATH . 'css/jquery.qtip.min.css'); //Tool tip css
		wp_enqueue_style('lp-only-cpt-admin-css', LANDINGPAGES_URLPATH . 'css/admin-lp-cpt-only-style.css');
		wp_enqueue_script( 'lp-admin-clear-stats-ajax-request', LANDINGPAGES_URLPATH . 'js/ajax.clearstats.js', array( 'jquery' ) );
		wp_localize_script( 'lp-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'lp_clear_nonce' => wp_create_nonce('lp-clear-nonce') ) );

		// Add New and Edit Screens
		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			add_filter( 'wp_default_editor', 'lp_ab_testing_force_default_editor' ); // force html view
			//admin.metaboxes.js - Template Selector - Media Uploader
			wp_enqueue_script('lp-js-metaboxes', LANDINGPAGES_URLPATH . 'js/admin/admin.metaboxes.js');

			$template_data = lp_get_extension_data();
			$template_data = json_encode($template_data);
			$template = get_post_meta($post->ID, 'lp-selected-template', true);
			$template = apply_filters('lp_selected_template',$template);
			$template = strtolower($template);
			$params = array('selected_template'=>$template, 'templates'=>$template_data);
			wp_localize_script('lp-js-metaboxes', 'data', $params);

			// Conditional TINYMCE for landing pages
			//wp_dequeue_script('jquery-tinymce');
			//wp_enqueue_script('jquery-tinymce', LANDINGPAGES_URLPATH . 'js/libraries/tiny_mce/jquery.tinymce.js');
			wp_enqueue_style('inbound-metaboxes', LANDINGPAGES_URLPATH . 'shared/assets/css/admin/inbound-metaboxes.css');

		}

		if ( $plugin_page === 'lp_global_settings' ) {
			wp_enqueue_script('lp-settings-js', LANDINGPAGES_URLPATH . 'js/admin/admin.global-settings.js');
		}
		// Edit Screen
		if ( $hook == 'post.php' ){
			wp_enqueue_script('jquery-zoomer', LANDINGPAGES_URLPATH . 'js/libraries/jquery.zoomer.js');
			wp_enqueue_script('lp-post-edit-ui', LANDINGPAGES_URLPATH . 'js/admin/admin.post-edit.js');
			wp_localize_script( 'lp-post-edit-ui', 'lp_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_landing_page_meta_nonce' => wp_create_nonce('wp-landing-page-meta-nonce'),  'lp_template_nonce' => wp_create_nonce('lp-nonce') ) );
			wp_enqueue_style('admin-post-edit-css', LANDINGPAGES_URLPATH . 'css/admin-post-edit.css');
			wp_enqueue_script('jqueryui');

			// jquery datepicker
			wp_enqueue_script('jquery-datepicker', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.min.js');
			wp_enqueue_script('jquery-datepicker-base', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/lib/base.js');
			wp_enqueue_script('jquery-datepicker-datepair', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/lib/datepair.js');
			wp_localize_script( 'jquery-datepicker', 'jquery_datepicker', array( 'thispath' => LANDINGPAGES_URLPATH.'js/libraries/jquery-datepicker/' ));
			wp_enqueue_script('jquery-datepicker-functions', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/picker_functions.js');
			wp_enqueue_style('jquery-timepicker-css', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.css');
			wp_enqueue_style('jquery-datepicker-base.css', LANDINGPAGES_URLPATH . 'js/libraries/jquery-datepicker/lib/base.css');

			// New frontend editor
			if (isset($_GET['frontend']) && $_GET['frontend'] === 'true') {
				//show_admin_bar( false ); // doesnt work

				wp_enqueue_style('lp-customizer-admin', LANDINGPAGES_URLPATH . 'css/new-customizer-admin.css');
				wp_enqueue_script('lp-customizer-admin', LANDINGPAGES_URLPATH . 'js/admin/new-customizer-admin.js');

			}
		}

		// Add New Screen
		if ( $hook == 'post-new.php'  ) {
			// Create New Landing Jquery UI
			wp_enqueue_script('lp-js-create-new-lander', LANDINGPAGES_URLPATH . 'js/admin/admin.post-new.js', array('jquery'), '1.0', true );
			wp_localize_script( 'lp-js-create-new-lander', 'lp_post_new_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_landing_page_meta_nonce' => wp_create_nonce('lp_nonce')  , 'LANDINGPAGES_URLPATH' => LANDINGPAGES_URLPATH ) );
			wp_enqueue_style('lp-css-post-new', LANDINGPAGES_URLPATH . 'css/admin-post-new.css');
		}

		// List Screen
		if ( $screen->id == 'edit-landing-page' ) {
			wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
			wp_enqueue_script('landing-page-list', LANDINGPAGES_URLPATH . 'js/admin/admin.landing-page-list.js');
			wp_enqueue_style('landing-page-list-css', LANDINGPAGES_URLPATH.'css/admin-landing-page-list.css');
			wp_enqueue_script('jqueryui');
			wp_admin_css('thickbox');
			add_thickbox();
		}

	} else {

		if ($hook == 'post.php') {

			wp_enqueue_style('lp-ab-testing-admin-css', LANDINGPAGES_URLPATH . 'css/admin-ab-testing.css');

		}
	}
	/* Requeue third party scripts */
	if(is_array($store)) {
		foreach ( $store as $handle ) {
		    wp_enqueue_script( $handle );
		}
		/* TEMP FIX - this neeeds fixing in CTA plugin */
		wp_dequeue_script('new-customizer-admin');
	}
}