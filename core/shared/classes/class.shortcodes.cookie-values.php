<?php

if ( !class_exists( 'Inbound_Shortcodes_Cookies' ) ) {

	class Inbound_Shortcodes_Cookies {

		public function __construct() {

			self::load_hooks();
		}

		public function load_hooks() {

			/* Shortcode for using cookie values */
			add_shortcode( 'inbound-cookie', array( __CLASS__, 'get_cookie' ), 1 );
		}


		/**
		* Used by leads-new-lead-notification email template to dispaly form fields the user inputted when converting on a form.
		*
		*/
		public static function get_cookie( $atts ) {


			$value = ( isset($_COOKIE[ $atts['name'] ]) ) ? $_COOKIE[ $atts['name'] ] : '';

			return $value;
		}

		/**
		* Used by wp-notify-post-author email template to show comment author gravitar
		*
		*
		*/
		public static function generate_gravitar( $atts ) {

			extract( shortcode_atts( array(
			  'email' => 'default@gravitar.com',
			  'size' => '60',
			  'default' => 'mm'
			), $atts ) );

			return "//www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;

		}
	}

	/* Initiate the logging system */
	$Inbound_Shortcodes_Cookies = new Inbound_Shortcodes_Cookies();

}