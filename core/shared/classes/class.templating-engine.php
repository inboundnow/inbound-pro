<?php

if ( !class_exists('Inbound_Templating_Engine') ) {

function Inbound_Templating_Engine() {
	return Inbound_Templating_Engine::instance();
}

class Inbound_Templating_Engine {

	public static $instance;
	private $defaults;

	/* Load Singleton Instance */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Inbound_Automation_Load_Extensions ) )
		{
			self::$instance = new Inbound_Templating_Engine;
			self::define_default_tokens();
		}

		return self::$instance;

	}

	/* Set Default Token Values */
	public static function define_default_tokens() {
		self::$instance->defaults = array (
			'admin-email-address' => get_option( 'admin_email', '' ),
			'admin-url' => admin_url(),
			'site-name' => get_option( 'blogname', '' ),
			'site-tagline' => get_option( 'blogdescription', '' ),
			'site-url' => get_option( 'siteurl', '' ) ,
			'date-time' =>  date( 'Y-m-d H:i:s A', current_time( 'timestamp' ) )
		);

		/* Plugin specific constants */
		if ( defined( 'LANDINGPAGES_URLPATH' ) ) {
			self::$instance->defaults['landingpages-urlpath'] = LANDINGPAGES_URLPATH;
		}

		if ( defined( 'WPL_URLPATH' ) ) {
			self::$instance->defaults['leads-urlpath'] = WPL_URLPATH;
		}

		if ( defined( 'WP_CTA_URLPATH' ) ) {
			self::$instance->defaults['callstoaction-urlpath'] = WP_CTA_URLPATH;
		}
	}

	/* Replace Tokens */
	public static function replace_tokens( $template, $args ) {

		/* Add default key/value pairs to front of $args array */
		array_unshift( $args, self::$instance->defaults );

		/* Loop through arguments in $args and replacec template tokens with values found in arguments */
		foreach ($args as $arg) {

			/* Lets look for certain nested arrays and pull their content into the main $arg array */
			if ( isset($arg['Mapped_Data']) ) {
				$arg_json = json_decode( stripslashes($arg['Mapped_Data']), true);
				foreach ($arg_json as $k=>$v) {
					$arg[$k] = $v;
				}
			}

			foreach ($arg as $key => $value ) {

				/* ignore child elements that are arrays */
				if ( is_array($value) ) {
					continue;
				}

				/* prepare/re-map keys */
				$key = str_replace( 'inbound_current_page_url', 'source', $key );
				$key = str_replace( 'inbound_form_n', 'form_name', $key );
				$key = str_replace( 'inbound_', '', $key );
				$key = str_replace( 'wpleads_', 'lead_', $key );
				$key = str_replace( '_', '-', $key );


				/* replace tokens in template */
				$template = str_replace( '{{'.$key.'}}', $value, $template );

			}

		}

		/* Replace All Leftover Tokens */
		$template = preg_replace( '/{{(.*?)}}/si', '', $template, -1 );

		return do_shortcode($template);
	}

}

}
