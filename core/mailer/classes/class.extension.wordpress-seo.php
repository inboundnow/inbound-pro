<?php

/**
 * Class extends support for Yoast SEO into the `inbound-email` CPT
 * @package Mailer
 * @subpackage YoastSEO
 */

class Inbound_Mailer_WordPress_SEO {

	/**
	 * Remove SEO Page Analysis from inbound-email post type
	 */
	function __construct() {
		if ( (isset($_GET['post_type']) && ($_GET['post_type'] == 'inbound-email') ) ) {
			add_filter( 'wpseo_use_page_analysis', '__return_false' );
		}
	}
}

/* Load Post Type Pre Init */
new Inbound_Mailer_WordPress_SEO();
