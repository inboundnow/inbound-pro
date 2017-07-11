<?php

/**
 * Class for loading functions to store and retrieve data from wp_options table
 * @package     InboundPro
 * @subpackage  DataInterface
 */


if ( ! class_exists( 'Inbound_Options_API' ) ) {

	class Inbound_Options_API {

		/**
		*  Gets option value in name space object
		*  @param STRING $namespace option_name
		*  @param STRING $key target key in dataset to get data outof
		*  @param MIXED $default default data to return if no data exists
		*/
		public static function get_option( $namespace , $key , $default = null ) {
			$options =  get_option( $namespace , array() ) ;

			if (!isset( $options[ $key ] )) {
				add_option( $namespace , '', '', 'no' );
				return $default;
			} else {
				return $options[ $key ];
			}
		}

		/**
		*  Updates option value in name space object
		*  @param STRING $namespace option_name
		*  @param STRING $key target key in dataset to set data into
		*  @param MIXED $value value to set into key
		*  @param STRING $autoload (optional) defaults to no but can be set to yes for creating new cachable options.
		*/
		public static function update_option( $namespace , $key , $value , $autoload = 'no' ) {

			$options = get_option( $namespace , array() );

			if (!$options || !is_array( $options ) ) {
				add_option( $namespace , '', '', $autoload  );
				$options = array();
			}

			$options[$key] = $value;

			update_option( $namespace ,  $options ) ;
		}

	}


}