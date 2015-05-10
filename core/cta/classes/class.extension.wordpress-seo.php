<?php

if ( !class_exists('CTA_WordPress_SEO') ) {

	class CTA_WordPress_SEO {

		function __construct() {
			/* Remove SEO Page Analysis from wp-call-to-action post type */
			if ( (isset($_GET['post_type']) && ($_GET['post_type'] == 'wp-call-to-action') ) ) {
				add_filter( 'wpseo_use_page_analysis', '__return_false' );
			}
		}
	}

	/* Load Post Type Pre Init */
	$GLOBALS['CTA_WordPress_SEO'] = new CTA_WordPress_SEO();
}
