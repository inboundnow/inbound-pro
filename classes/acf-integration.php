<?php

if (!class_exists('Inbound_ACF')) {


class Inbound_ACF {
	
	static $api;
	static $license_key;
	static $wordpress_url;
	
	/**
	* Initalize Inbound_ACF Class
	*/
	public function __construct() {			
		//self::define_constants();
		//self::load_files();
		self::load_hooks();		
	}
	
	/**
	* Define Constants
	*  
	*/
	private static function define_constants() {
		
	}
	
	/**
	*  Loads php files
	*/
	public static function load_files() {
		
	}
	
	
	/**
	* Load Hooks & Filters 
	*/
	public static function load_hooks() {
	
		/* Load ACF Fields On ACF powered Landing Pages */
		add_filter( 'acf/location/rule_match/template_id' , array( __CLASS__ , 'load_acf_on_template' ) , 10 , 3 );
		
		/* Prepare field names suitible for variations */
		add_filter( 'acf/get_fields' , array( __CLASS__ , 'prepare_acf_fields' ) ,10 , 1 );
		
	}

	/**
	*  Check if current post is a landing page using an ACF powered template
	*/
	public static function load_acf_on_template( $allow , $rule, $args ) {
	
		$template = get_post_meta($args['post_id'] , 'lp-selected-template', true);
		$template = apply_filters('lp_selected_template',$template);
	
		if ($template == $rule['value']) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function prepare_acf_fields( $fields ) {
		global $post;
		
		if ( $post->post_type != 'landing-page' ) {
			return $fields;
		}
		
		$current_variation_id = lp_ab_testing_get_current_variation_id();
		
		foreach ($fields as $key => $field) {
			$fields[$key]['name'] =  $field['name'] . '-' . $current_variation_id;
		}
		
		return $fields;
	}
}


add_action( 'admin_init' , 'inbound_load_acf_integration' );
function inbound_load_acf_integration() {
	$GLOBALS['Inbound_ACF'] = new Inbound_ACF();
}
}