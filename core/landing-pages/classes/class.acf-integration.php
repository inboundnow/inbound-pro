<?php

if (!class_exists('Landing_Pages_ACF')) {

	class Landing_Pages_ACF {

		/**
		 * Initialize Landing_Pages_ACF Class
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

			/* make sure fields are placed in the correct location */
			add_action( 'save_post', array( __CLASS__ , 'save_acf_fields' ) );

			/* Intercept load custom field value request and hijack it */
			add_filter( 'acf/load_value' , array( __CLASS__ , 'load_value' ) , 11 , 3 );

			/* extra field formatting
			add_filter( 'acf/format_value' , array( __CLASS__ , 'format_value' ) , 11 , 3 ); */

			/* make sure fields are placed in the correct location */
			add_action( 'admin_print_footer_scripts', array( __CLASS__ , 'reposition_acf_fields' ) );

			/* add default instructions to all ACF templates - legacy unused
			add_filter( 'lp_extension_data' , array( __CLASS__ , 'lp_add_instructions' ) , 11 , 1 );
			*/
		}

		/**
		 * Adds javascript to make sure ACF fields load inside template container
		 */
		public static function reposition_acf_fields() {
			global $post;

			if ( !defined('ACF_FREE') || ( !isset($post) || $post->post_type != 'landing-page' ) ) {
				return;
			}

			?>
			<script type='text/javascript'>
				jQuery('.acf_postbox').each(function(){
					jQuery('#template-display-options').append(jQuery(this));
				});
			</script>
			<?php
		}

		public static function save_acf_fields( $landing_page_id ) {

			if ( wp_is_post_revision( $landing_page_id ) ) {
				return;
			}

			if (  !isset($_POST['post_type']) || $_POST['post_type'] != 'landing-page' ) {
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}

			/* save acf settings - uses our future data array - eventually we will migrate all post meta into this data object */
			$fields = (isset($_POST['fields'])) ? $_POST['fields'] : null;
			$fields = (isset($_POST['acf'])) ? $_POST['acf'] : $fields;

			if ( $fields ) {

				$settings = Landing_Pages_Meta::get_settings( $landing_page_id );
				$variation_id = (isset($_REQUEST['lp-variation-id'])) ? $_REQUEST['lp-variation-id'] : '0';

				if (!isset($settings['variations'])) {
					$settings['variations'] = array();
				}

				$settings['variations'][$variation_id]['acf'] = $fields;
				Landing_Pages_Meta::update_settings( $landing_page_id , $settings );
			}
		}

		/**
		 * Although unused at the moment, this method can be used for filtering the return value with ACF5 fields
		 * @param $value
		 * @param $post_id
		 * @param $field
		 * @return mixed
		 */
		public static function format_value( $value, $post_id, $field ) {
			return $value;
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

			if ( !isset($post) || $post->post_type != 'landing-page' ) {
				return $value;
			}

			$vid = Landing_Pages_Variations::get_new_variation_reference_id( $post->ID );

			$settings = Landing_Pages_Meta::get_settings( $post->ID );

			$variations = ( isset($settings['variations']) ) ? $settings['variations'] : null;

			/* If there is no ACF data for this template attempt to pull values from the legacy postmeta values */

			if ( !isset( $variations[ $vid ][ 'acf' ] ) || !$variations[ $vid ][ 'acf' ]) {
				return self::load_legacy_value(  $value, $post_id, $field  );
			}

			if ( isset( $variations[ $vid ][ 'acf' ] ) ) {
				$new_value = self::search_field_array( $variations[ $vid ][ 'acf' ] , $field );

				/* sometimes value is an array count when new_value believes it should be an array in this case get new count */
				if (!is_array($value) && is_array($new_value)) {
					$value = count($new_value);
				} else if( $new_value) {
					$value = $new_value;
				}

				/* acf lite isn't processing return values correctly - ignore repeater subfields */
				if ( !is_admin() &&  defined('ACF_FREE')  ) {
					$value = self::acf_free_value_formatting( $value , $field );
				}

				if ( !is_admin() && is_string($value) ) {
					$value = do_shortcode($value);
				}

				/* handle non acf5 template return formatting */
				if (defined('ACF_PRO')) {
					$value = self::acf_check_if_acf4( $value , $field );
				}

			}

			return $value;

		}
		/**
		 * Finds the correct value given the variation - uses legacy meta system
		 *
		 * @param MIXED $value contains the non-variation value
		 * @param INT $post_id ID of landing page being loaded
		 * @param ARRAY $field wide array of data belonging to custom field (not leveraged in this method)
		 *
		 * @returns MIXED $new_value value mapped to variation.
		 */
		public static function load_legacy_value( $value, $post_id, $field ) {
			global $post;

			/* get registered field object data */
			$field = self::acf_get_registered_field( $field );

			/* if a brand new post ignore return default value */
			if ( $post->post_status != 'publish' ) {
				return ( isset($field['default_value']) ) ? do_shortcode($field['default_value']) : '' ;
			}

			$vid = Landing_Pages_Variations::get_new_variation_reference_id( $post->ID );

			if ( $vid ) {
				$value = get_post_meta( $post_id ,  $field['name'] . '-' . $vid , true );
			} else {
				$value = get_post_meta( $post_id ,  $field['name']  , true );
			}


			if ($field['type']=='image' && is_admin() ) {
				$value = self::get_image_id_from_url( $value );
			}

			if ($field['type']=='date_picker') {
				$value = str_replace('-' , '', $value);
				$value = explode(' ' , $value);
				$value = $value[0];
			}

			if ($field['type']=='color_picker') {
				if (!strstr( $value , '#' ) && $value ) {
					$value = '#'.$value;
				}
			}

			if (!is_array($value) && !is_admin() ) {
				$value = do_shortcode($value);
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

			if ( !isset($post) || $post->post_type != 'landing-page' ) {
				return $allow;
			}

			$template =	Landing_Pages_Variations::get_current_template( $args['post_id'] );

			if ($template == $rule['value']) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 *
		 * @param $image_url
		 * @return mixed
		 */
		public static function get_image_id_from_url($url) {
			$dir = wp_upload_dir();

			// baseurl never has a trailing slash
			if ( false === strpos( $url, $dir['baseurl'] . '/' ) ) {
				// URL points to a place outside of upload directory
				return false;
			}

			$file  = basename( $url );
			$query = array(
				'post_type'  => 'attachment',
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
					),
				)
			);

			$query['meta_query'][0]['key'] = '_wp_attached_file';

			// query attachments
			$ids = get_posts( $query );

			if ( ! empty( $ids ) ) {

				foreach ( $ids as $id ) {

					// first entry of returned array is the URL
					if ( $url === array_shift( wp_get_attachment_image_src( $id, 'full' ) ) )
						return $id;
				}
			}

			$query['meta_query'][0]['key'] = '_wp_attachment_metadata';

			// query attachments again
			$ids = get_posts( $query );

			if ( empty( $ids) )
				return false;

			foreach ( $ids as $id ) {

				$meta = wp_get_attachment_metadata( $id );

				foreach ( $meta['sizes'] as $size => $values ) {

					if ( $values['file'] === $file && $url === array_shift( wp_get_attachment_image_src( $id, $size ) ) )
						return $id;
				}
			}

			return false;
		}

		public static function acf_get_registered_field( $field ) {
			global $acf_register_field_group;

			if (!$acf_register_field_group) {
				return $field;
			}

			foreach ($acf_register_field_group as $key => $group) {
				foreach ( $group['fields'] as $this_field ) {
					if ( $this_field['name'] == $field['name'] ){
						return $this_field;
					}
				}
			}
		}


		/**
		 * Correct return value formatting when Pro is NOT installed
		 */
		public static function acf_free_value_formatting( $value , $field ) {

			if ($field['type'] == 'image' && $field['return_format'] == 'url' && !strstr($value , 'http' ) ) {
				$image_array = wp_get_attachment_image_src( $value , 'full' );
				return $image_array[0];
			}

			if ($field['type'] == 'file' && $field['return_format'] == 'url' && !strstr($value , 'http' ) ) {
				return wp_get_attachment_url( $value );
			}

			if ($field['type'] == 'wysiwyg') {
				$vaue = wpautop($value);
				$vaue = do_shortcode($value);
			}

			return $value;
		}

		/**
		 * checks template data type
		 * @param $value
		 * @param $field
		 * @return mixed
		 */
		public static function acf_check_if_acf4( $value , $field ) {
			global $key, $lp_data;

			if (!isset($lp_data[$key])) {
				return $value;
			}

			if ( $lp_data[$key]['info']['data_type'] == 'acf4' ) {
				return self::acf_free_value_formatting($value , $field);
			} else {
				return $value;
			}
		}

		/**
		 * adds a standard set of instructions to all acf templates via our legacy field system
		 */
		public static function lp_add_instructions( $data ) {
			foreach ($data as $key => $object ) {
				if ( isset($object['info']['data_type']) && strstr( $object['info']['data_type'] , 'acf')  ) {

				}
			}

			return $data;
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

	new Landing_Pages_ACF();


}