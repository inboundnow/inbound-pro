<?php

/**
*	Shows Preview of Inbound Email
*/
class Inbound_Email_Preview {

	/**
	*	Initializes class
	*/
	function __construct() {
		self::load_hooks();
	}

	/**
	*	Loads hooks and filters
	*/
	public function load_hooks() {
		add_filter( 'single_template' , array( __CLASS__ , 'load_email' ) , 11 );
	}

	/**
	*	Detects request to view inbound-email post type and loads correct email template
	*/
	public static function load_email() {

		global $wp_query, $post, $query_string, $Inbound_Mailer_Variations;

		if ( $post->post_type != "inbound-email" ) {
			return;
		}

		/* Load email templates */
		Inbound_Mailer_Load_Templates();

		$vid = $Inbound_Mailer_Variations->get_current_variation_id();
		$template = $Inbound_Mailer_Variations->get_current_template( $post->ID , $vid );

		if (!isset($template)) {
			return;
		}


		if (file_exists(INBOUND_EMAIL_PATH.'templates/'.$template.'/index.php')) {
			return INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php';
		} else if (file_exists(INBOUND_EMAIL_UPLOADS_PATH .$template.'/index.php')) {
			return INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php';
		} else if (file_exists(INBOUND_EMAIL_THEME_TEMPLATES_PATH .$template.'/index.php')) {
			return INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php';
		}


		return $single;
	}
}

$Inbound_Email_Preview = new Inbound_Email_Preview();