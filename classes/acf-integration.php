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
		
		/* Save fields under correct variation meta pair */
		add_filter( 'acf/update_value' , array( __CLASS__ , 'update_acf_fields' ) , 10 , 3 );
		
		/* Save acf variation field map */
		add_filter( 'acf/save_post' , array( __CLASS__ , 'save_acf_fields' ) , 99 , 3 );
		
		/* Load corect variation value in backend */
		//add_filter( 'acf/get_fields' , array( __CLASS__ , 'prepare_acf_fields' ) , 10 , 1 );

		/* Intercept load request and hijack it */
		//add_filter( 'acf/load_field' , array( __CLASS__ , 'load_field' ) , 10 , 1 );
		add_filter( 'acf/load_value' , array( __CLASS__ , 'load_value' ) , 10 , 3 );
		
	}

	
	/**
	*  Save ACF fields under variation
	*/
	public static function save_acf_fields(  $post_id ) {
		global $post;
		
		if ( !isset($post) || $post->post_type != 'landing-page' || !isset($_POST['acf']) ) {
			return;
		}
		
		/* get variation */
		$vid = lp_ab_testing_get_current_variation_id();

		/* Update special variation object */
		update_post_meta( $post_id , 'acf-' . $vid , $_POST['acf'] );
	
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
		
		update_post_meta( $post_id , 'acf-inbound-' . $field['name'] . '-' . $vid , $value );
						
		return $value;
	}
		
	
	
	
	public static function prepare_acf_fields( $fields ) {
		global $post;
		
		
		if ( !isset($post) || $post->post_type != 'landing-page' ) {
			return $fields;
		}
		
		print_r($fields);exit;
		/* Get correct field value */		
		foreach ($fields as $key => $field) {
			//$fields[$key]['value'] =  self::get_value( $field['name'] , $post );
		}
		
		return $fields;
	}
	
	/**
	* Finds the value
	*/
	public static function load_field( $field ) {
		global $post;
		
		if ($field) {
			return $field;
		}
		
		$vid = lp_ab_testing_get_current_variation_id();
		$field = get_post_meta( $post->ID , 'acf-' . $vid );
		$value = self::search_array( $field , $field_key );
		
		return $value;
	}
	
	
	/**
	* Finds the value
	*/
	public static function load_value( $value, $post_id, $field ) {
		global $post;
	
		
		$vid = lp_ab_testing_get_current_variation_id();
		
		$acf = get_post_meta( $post->ID , 'acf-' . $vid );
		
		/*
		print_r($acf);		
		var_dump($field['key']);
		echo "\r\n";
		var_dump($value);
		echo "\r\n";
		*/
		
		$new_value = self::search_array( $acf , $field['key'] );		
		
		/*
		var_dump($new_value);
		echo "\r\n";echo "\r\n";echo "\r\n";
		*/
		
		return $new_value;
	}
	
	/**
	* Searches ACF variation array and returns the correct field value given the field key
	*
	* @param ARRAY $array of custom field keys and values stored for variation
	* @param STRING $needle acf form field key 
	*
	* @return $feild value
	*/
	public static function search_array( $array , $needle ) {

	
		foreach ($array as $key => $value ){

			if ($key === $needle && !is_array($value) ) {
				return $value;
			}
			
			/* Arrays could be repeaters,tags */
			if ( is_array($value) ) {
				
				/* Check if this array contains a repeater field layouts. If it does then return layouts, else this array is a non-repeater value set so return it */
				if ( $key === $needle ) {					
					
					$repeater_array = self::get_repeater_layouts( $value );
					if ($repeater_array) {
						return $repeater_array;
					
					} else  {
						return $value;
					}
					
				}
				
				/* Check if array is a value set by making sure there are no repeater field deinitions */
				if ( $key === $needle ) {
					return $value;
				}
				
				/* else search repeater fields for key */
				$field = self::search_array( $value, $needle );
				if ($field) {
					return $field;
				}
			}	
			
		}
		
		return false;
	}
	
	public static function get_value( $name , $post ) {
		//echo $name; 
	}
	
	public static function get_repeater_layouts( $array ) {
		
		$fields = array();

		foreach ($array as $key => $value) {
			if ( isset( $value['acf_fc_layout'] ) ) {
				$fields[] = $value['acf_fc_layout'];
			}
		}
		
		return $fields;
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
	
}


add_action( 'init' , 'inbound_load_acf_integration' );
function inbound_load_acf_integration() {
	$GLOBALS['Inbound_ACF'] = new Inbound_ACF();
}
}