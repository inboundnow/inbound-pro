<?php

if ( !class_exists('Inbound_Mailer_WordPress_SEO') ) {

	class Inbound_Mailer_WordPress_SEO {

		function __construct() {
			/* Remove SEO Page Analysis from inbound-email post type */
			if ( (isset($_GET['post_type']) && ($_GET['post_type'] == 'inbound-email') ) ) {
				add_filter( 'wpseo_use_page_analysis', '__return_false' ); 
			}
		}
	}
	
	/* Load Post Type Pre Init */
	$GLOBALS['Inbound_Mailer_WordPress_SEO'] = new Inbound_Mailer_WordPress_SEO();
}
