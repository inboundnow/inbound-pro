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

			/* Intercept load custom field value request and hijack it */
			add_filter( 'acf/load_value' , array( __CLASS__ , 'load_value' ) , 10 , 3 );

			/* on landing page save - not needed
			add_action( 'save_post' , array( __CLASS__ , 'save_acf_fields' ) , 10 , 1 );
			*/
		}


		/**
		 *	Save ACF fields under variation
		 */
		public static function save_acf_fields(	$post_id ) {
			global $post;

			if ( !isset($post) || $post->post_type != 'landing-page' || !isset($_POST['acf']) ) {
				return;
			}

			/* get variation */
			$vid = Inbound_Mailer_Variations::get_current_variation_id();

			/* Update special variation object */
			update_post_meta( $post_id , 'acf-' . $vid , $_POST['acf'] );

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

			if (!$variations[ $vid ][ 'acf' ]) {
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

			$vid = Landing_Pages_Variations::get_new_variation_reference_id( $post->ID );

			if ( $vid ) {
				$value = get_post_meta( $post_id ,  $field['name'] . '-' . $vid , true );
			} else {
				$value = get_post_meta( $post_id ,  $field['name']  , true );
			}


			if ($field['type']=='image') {
				$value = self::get_image_id_from_url( $value );
			}

			if ($field['type']=='date_picker') {
				$value = str_replace('-' , '', $value);
				$value = explode(' ' , $value);
				$value = $value[0];
			}

			if ($field['type']=='color_picker') {
				if (!strstr( $value , '#' )) {
					$value = '#'.$value;
				}
			}

			/**
			var_dump($new);
			echo "\r\n";echo "\r\n";echo "\r\n";
			/**/
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

			if (!$matches) {
				return false;
			}

			$pointer = str_replace('_' , '' , $matches[0]);

			$i = 0;
			foreach ($array as $key => $value) {
				if (isset($value[ $field['key'] ])	&& $pointer == $i ) {
					return $value[ $field['key'] ];
				}

				$i++;
			}

			return false;
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

			if ($post->post_type != 'landing-page' ) {
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

	}

	/**
	 *	Initialize ACF Integrations
	 */

	new Landing_Pages_ACF();


}
