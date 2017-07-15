<?php

/**
 * Class for integrating ACF and Mailer component
 * @package ACF
 */

class Inbound_Mailer_ACF {

	/**
	 * Initialize Inbound_Mailer_ACF Class
	 */
	public function __construct() {
		self::load_hooks();
	}


	/**
	 * Load Hooks & Filters
	 */
	public static function load_hooks() {

		/* Load ACF Fields On ACF powered Email Template */
		add_filter( 'acf/location/rule_match/template_id' , array( __CLASS__ , 'load_acf_on_template' ) , 10 , 3 );

		/* Intercept load custom field value request and hijack it */
		add_filter( 'acf/load_value' , array( __CLASS__ , 'load_value' ) , 10 , 3 );

		/* add new location rule to ACF Field UI */
		add_filter('acf/location/rule_types', array( __CLASS__ , 'define_location_rule_types' ) );

		/* add new location rule values to ACF Field UI */
		add_filter('acf/location/rule_values/template_id', array( __CLASS__ , 'define_location_rule_values' ) );


	}

	/**
	 * @param $choices
	 * @return mixed
	 */
	public static function define_location_rule_types( $choices ) {

		if (!isset($choices['Basic']['template_id'])) {
			$choices['Basic']['template_id'] = __('Template ID', 'landing-page');
		}

		return $choices;
	}

	public static function define_location_rule_values( $choices ) {
		$uploaded_templates = Inbound_Mailer_Load_Templates::get_uploaded_templates();
		$core_templates = Inbound_Mailer_Load_Templates::get_core_templates();
		$template_ids = $uploaded_templates + $core_templates;

		if( $template_ids )	{
			foreach( $template_ids as $template_id )	{

				/* template ID by template name here */
				$choices[ $template_id ] = $template_id;

			}
		}

		return $choices;
	}

	/**
	 * Finds the correct value given the variation
	 *
	 * @param MIXED $value contains the non-variation value
	 * @param INT $post_id ID of landing page being loaded
	 * @param ARRAY $field wide array of data belonging to custom field (not leveraged in this method)
	 *
	 * @returns MIXED $new_value value mapped to variation.
	 */
	public static function load_value( $value, $post_id, $field ) {
		global $post;

		if ( !isset($post) || $post->post_type != 'inbound-email' ) {
			return $value;
		}


		$vid = Inbound_Mailer_Variations::get_current_variation_id();

		$settings = get_post_meta( $post_id , 'inbound_settings' , true);

		$variations = ( isset($settings['variations']) ) ? $settings['variations'] : null;

		if (!$variations) {
			return $value;
		}

		if ( isset( $variations[ $vid ][ 'acf' ] ) ) {
			$new_value = self::search_field_array( $variations[ $vid ][ 'acf' ] , $field );

			/* sometimes value is an array count when new_value believes it should be an array in this case get new count */
			if (!is_array($value) && is_array($new_value)) {
				$value = count($new_value);
			} else if( $new_value) {
				$value = $new_value;
			}


			if ( !is_admin() && is_string($value) ) {
				$value = do_shortcode($value);
			}
		}

		/**
		var_dump($new);
		echo "\r\n";echo "\r\n";echo "\r\n";
		/**/
		return self::format_value( $value , $field );

	}

	/**
	 * Solves issues with return values
	 * @param $value
	 * @param $field
	 * @return mixed
	 */
	public static function format_value( $value , $field ){
		if ($field['type']=='color_picker' && is_array($value)){
			$value = $value[1];

			if (!strstr($value,'#')) {
				$value = $field['default_value'];
			}

		}
		return $value;
	}
	/**
	 * Searches ACF variation array and returns the correct field value given the field key
	 *
	 * @param ARRAY $array of custom field keys and values stored for variation
	 * @param STRING $needle acf form field key
	 *
	 * @return $feild value
	 */
	public static function search_field_array( $array , $field ) {

		$needle = $field['key'];

		foreach ($array as $key => $value ){

			if ($key === $needle && !is_array($value) ) {
				return $value;
			}

			/* Arrays could be repeaters or any custom field with sets of multiple values */
			if ( is_array($value) ) {

				/* Check if this array contains a repeater field layouts. If it does then return layouts, else this array is a non-repeater value set so return it */
				if ( $key === $needle ) {

					$repeater_array = self::get_repeater_layouts( $value );
					if ($repeater_array) {
						return $repeater_array;

					} else	{
						return $value;
					}

				}

				/* Check if array is repeater fields and determine correct value given a parsed field name with field key */
				$repeater_value = self::get_repeater_values( $value , $field );

				/* If target key is not in these repeater fields, or this array is not determined to be a repeater field then move on. */
				if ($repeater_value) {
					return $repeater_value;
				}


			}

		}

		return false;
	}

	/**
	 *	Searches an array assumed to be a repeater field dataset and returns an array of repeater field layout definitions
	 *
	 *	@retuns ARRAY $fields this array will either be empty of contain repeater field layout definitions.
	 */
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
	 *	Searches an array assumed to be a repeater field dataset and returns an array of repeater field layout definitions
	 *
	 *	@retuns ARRAY $fields this array will either be empty of contain repeater field layout definitions.
	 */
	public static function get_repeater_values( $array , $field ) {

		/* Discover correct repeater pointer by parsing field name */
		preg_match('/(_\d_)/', $field['name'], $matches, 0);

		/* if not a repeater subfield then bail */
		if (!$matches) {
			return false;
		}

		$pointer = str_replace('_' , '' , $matches[0]);
		$repeater_key = self::key_search($array, $field , true ); /* returns parent flexible content field key using sub field key */

		if (isset($array[$repeater_key][$pointer][$field['key']])){
			return $array[$repeater_key][$pointer][$field['key']];
		}

		return '';

	}

	/**
	 *	Check if current post is a landing page using an ACF powered template
	 *
	 *	@filter acf/location/rule_match/template_id
	 *
	 *	@returns BOOL declaring if current page is a landing page with an ACF template loaded or not
	 */
	public static function load_acf_on_template( $allow , $rule, $args ) {
		global $post;

		if (!isset($post) || $post->post_type != 'inbound-email' ) {
			return $allow;
		}

		$template =	Inbound_Mailer_Variations::get_current_template( $args['post_id'] );

		if ($template == $rule['value']) {
			return true;
		} else {
			return false;
		}
	}

	public static function load_javascript_on_admin_edit_post_page() {
		global $parent_file;

		// If we're on the edit post page.
		if ( $parent_file == 'edit.php?post_type=inbound-email' ) {
			echo "
				<script>
				jQuery('#publish').on('click', function() {
				jQuery('#publish').removeClass('disabled');
				jQuery('.spinner').show();
				jQuery('#publish').val('".__('Saving','inbound-email')."');
				});
				</script>
			";
		}
	}

	/**
	 * This is a complicated array search method for working with ACF repeater fields.
	 * @param $array
	 * @param $field
	 * @param bool|false $get_parent if get_parent is set to true to will return the parent field group key of the repeater fields
	 * @param mixed $last_key placeholder for storing the last key...
	 * @return bool|int|string
	 */
	public static function key_search($array, $field , $get_parent = false , $last_key = false) {
		$value = false;

		foreach ($array as $key => $item) {
			if ($key === $field['key'] ) {
				$value = $item;
			} else {
				if (is_array($item)) {
					$last_key = ( !is_numeric($key)) ? $key : $last_key;
					$value = self::key_search($item, $field , $get_parent , $last_key );
				}
			}

			if ($value) {
				if (!$get_parent) {
					return $value;
				} else {
					return $last_key;
				}

			}
		}

		return false;
	}
}

/**
 *	Initialize ACF Integrations
 */
if (!function_exists('inbound_mailer_acf_integration')) {
	add_action( 'init' , 'inbound_mailer_acf_integration' );

	function inbound_mailer_acf_integration() {
		new Inbound_Mailer_ACF();
	}
}
