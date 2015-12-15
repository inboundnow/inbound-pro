<?php
if ( ! class_exists( 'Inbound_Options_API' ) ) {

	class Inbound_Options_API {

		/**
		*  Gets option value in name space object
		*/
		public static function get_option( $namespace, $key, $default = null ) {
			$option = get_option( $namespace );
			$option = (is_array($option)) ? $option : json_decode( stripslashes( $option ), true ) ;

			if (!isset( $option[ $key ] )) {
				add_option( $namespace, '', '', 'no' );
				return $default;
			} else {
				return $option[ $key ];
			}
		}

		/**
		*  Updates option value in name space object
		*/
		public static function update_option( $namespace, $key, $value ) {

			$options = json_decode( stripslashes( get_option( $namespace , '' ) ), true ) ;

			if (!$options) {
				add_option( $namespace, '', '', 'no' );
				$options = array();
			}

			$options[$key] = $value;

			update_option( $namespace, json_encode( $options ) );
		}

	}


}