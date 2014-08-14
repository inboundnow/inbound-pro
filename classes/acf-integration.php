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
		
		/* Save fields under correct variation */
		//add_filter( 'acf/update_value' , array( __CLASS__ , 'update_acf_fields' ) , 10 , 3 );
		//add_filter( 'acf/save_post' , array( __CLASS__ , 'save_acf_fields' ) , 1 , 1 );
		
		/* Load corect variation value in backend */
		//add_filter( 'acf/get_fields' , array( __CLASS__ , 'prepare_acf_fields' ) , 10 , 1 );

		//add_filter( 'acf/load_value' , array( __CLASS__ , 'load_values' ) , 10 , 1 );
		
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
	
	
	/**
	*  This filter runs as an action hook when field is update and meta pair is created
	*/
	public static function update_acf_fields(  $value , $post_id, $field  ) {
		global $post;
		
		if ( !isset($post) || $post->post_type != 'landing-page' )  {
			return;
		}
		
		$vid = lp_ab_testing_get_current_variation_id();
		
		update_post_meta( $post_id , $field['name'] .'-'. $vid , $value );
						
		return $value;
	}
		
	
	/**
	*  Save ACF fields under variation
	*/
	public static function save_acf_fields(  $value, $post_id, $field  ) {
		global $post;
		
		if ( !isset($post) || $post->post_type != 'landing-page' || !isset($_POST['acf']) ) {
			return;
		}
		
		/* get variation */
		$vid = lp_ab_testing_get_current_variation_id();
		$fields =   get_field_objects( $post_id );
		
		//print_r( $_POST['acf']);
		//print_r($fields);exit;
		
		foreach ( $_POST['acf'] as $key => $value ) {
			
			$field = acf_get_field( $key );
			update_post_meta( $post_id , 'acf-' . $field['name'] , $value );
		}
		
		if (!$vid) {
			update_post_meta( $post_id , 'acf-0' , $_POST['acf'] );
		}								
	
	}
	
	
	public static function prepare_acf_fields( $fields ) {
		global $post;
		
		
		if ( !isset($post) || $post->post_type != 'landing-page' ) {
			return $fields;
		}
		
		//print_r($fields);exit;
		/* Get correct field value */		
		foreach ($fields as $key => $field) {
			//$fields[$key]['value'] =  self::get_value( $field['name'] , $post );
		}
		
		return $fields;
	}
	
	public static function load_values() {
	
	}
	
	public static function get_value( $name , $post ) {
		//echo $name; 
	}
}


add_action( 'admin_init' , 'inbound_load_acf_integration' );
function inbound_load_acf_integration() {
	$GLOBALS['Inbound_ACF'] = new Inbound_ACF();
}
}