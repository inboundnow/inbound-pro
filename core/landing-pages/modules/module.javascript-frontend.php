<?php

add_action('wp_enqueue_scripts','lp_fontend_enqueue_scripts');

function lp_fontend_enqueue_scripts($hook) {
	global $post;

	if (!isset($post)) {
		return;
	}

	/* dequeue third party scripts */
	global $wp_scripts;
	$store = '';
	if ( !empty( $wp_scripts->queue ) ) {

	    $store = $wp_scripts->queue; // store the scripts

		foreach ( $wp_scripts->queue as $handle ) {
	          wp_dequeue_script( $handle );
	    }

	}

	/* Load jQuery */
	wp_enqueue_script('jquery');

	if (isset($post)&&$post->post_type=='landing-page') {

		if (isset($_GET['template-customize']) &&$_GET['template-customize']=='on') {
			echo "<style type='text/css'>#variation-list{background:#eaeaea !important; top: 26px !important; height: 35px !important;padding-top: 10px !important;}#wpadminbar {height: 32px !important;}</style>"; // enqueue styles not firing
		}
		if (isset($_GET['live-preview-area'])) {
			show_admin_bar( false );
			wp_register_script('lp-customizer-load-js', LANDINGPAGES_URLPATH . 'js/customizer.load.js', array('jquery'));
			wp_enqueue_script('lp-customizer-load-js');

		}
	}
	/* Requeue third party scripts */
	if(is_array($store)) {
		foreach ( $store as $handle ) {
		    wp_enqueue_script( $handle );
		}
		/* TEMP FIX - this neeeds fixing in CTA plugin */
		wp_dequeue_script('lp-customizer-load-js');

	}

}

/* CLEAN URL OF VARIATION GET TAGS */
add_action('wp_head', 'lp_header_load');
function lp_header_load(){
	global $post;
	if (isset($post) && $post->post_type=='landing-page') {
		wp_enqueue_style('inbound-wordpress-base', LANDINGPAGES_URLPATH . 'css/frontend/global-landing-page-style.css');
		wp_enqueue_style('inbound-shortcodes', INBOUND_FORMS.'css/frontend-render.css');
		if ( isset($_GET['live-preview-area']) ) { ?>
		<style type="text/css">
			html, html.no-js, html[dir="ltr"] {
				margin-top: 0px !important;
			}
		</style>
		<?php } ?>
		<?php if (isset($_GET['lp-variation-id']) && !isset($_GET['template-customize']) && !isset($_GET['iframe_window']) && !isset($_GET['live-preview-area'])) {
			do_action('landing_page_header_script');
		?>
		<script type="text/javascript">
			/* For Iframe previews to stop saving page views */
			var dont_save_page_view = _inbound.Utils.getParameterVal('dont_save', window.location.href);
			if (dont_save_page_view) {
				//console.log('turn off page tracking');
				window.inbound_settings.page_tracking = 'off';
			}
		</script>
		<?php if(!defined('Inbound_Now_Disable_URL_CLEAN')) { ?>
		<script type="text/javascript">
		/* Then strip params if pushstate exists */
		if (typeof window.history.pushState == 'function') {
				var cleanparams=window.location.href.split("?");
				var clean_url=cleanparams[0];history.replaceState({},"landing page",clean_url);
		}
		</script>
		<?php } ?>
		<?php }
	}
}