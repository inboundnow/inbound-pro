<?php

add_action('wp_enqueue_scripts', 'wpleads_enqueuescripts_header');

function wpleads_enqueuescripts_header() {
	global $post;

	$post_type = isset($post) ? get_post_type( $post ) : null;

	// Load Tracking Scripts
	if($post_type != "wp-call-to-action") {

		/* load jquery */
		wp_enqueue_script('jquery');

		// Load form pre-population
		$form_prepopulation = get_option( 'wpl-main-form-prepopulation' , 1); // Check lead settings
		$lp_form_prepopulation = get_option( 'lp-main-landing-page-prepopulate-forms' , 1);
		if ($lp_form_prepopulation === "1") {
			$form_prepopulation = "1";
		}

		if ($form_prepopulation === "1") {
			wp_enqueue_script('form-population', WPL_URLPATH . 'js/wpl.form-population.js', array( 'jquery','jquery-cookie'));
		} else {
			wp_dequeue_script('form-population');
		}

		// Load form tracking class
		$form_ids = get_option( 'wpl-main-tracking-ids' , 1);
		$form_exclude_ids = get_option( 'wpl-main-exclude-tracking-ids');
		if ($form_ids || $form_exclude_ids) {
			wp_enqueue_script('wpl-assign-class', WPL_URLPATH . '/js/wpl.assign-class.js', array( 'jquery'));
			wp_localize_script( 'wpl-assign-class', 'wpleads', array( 'form_ids' => $form_ids, 'form_exclude_ids' => $form_exclude_ids ) );
		}

	}
}
