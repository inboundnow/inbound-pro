<?php

/**
 * Class for extending hard coded mapped fields into a user programable state
 * @package     InboundPro
 * @subpackage  FieldMapping
 */
class Inbound_Leads_Custom_fields {

	static $custom_field_map;

	/**
	 *  initialize class
	 */
	public function __construct() {
		self::get_custom_fields();
		self::load_hooks();
	}

	/**
	 *  load hooks and filters
	 */
	public static function load_hooks() {
		add_filter( 'wp_leads_add_lead_field' , array( __CLASS__ , 'merge_fields' ) , 99  );
	}

	/**
	 *  Get mappable fields
	 */
	public static function get_custom_fields() {
		global $inbound_settings;

		self::$custom_field_map = (isset($inbound_settings['leads-custom-fields']['fields'])) ?  $inbound_settings['leads-custom-fields']['fields'] : Leads_Field_Map::get_lead_fields();

	}

	/**
	 *  Merge the user's custom fields into the hard coded default fields
	 *  @param ARRAY $mappable_fields
	 */
	public static function merge_fields( $mappable_fields ) {

		$combined = array();
		/* loop through memory and change labels for core fields */
		foreach( self::$custom_field_map as $key => $field ) {

			if (!isset($field['key']) && !is_numeric($key)) {
				$field['key'] = $key;
			}

			$present = false;

			foreach ($mappable_fields as $i => $f) {

				if ( $f['key'] != $field['key']) {
					continue;
				}

				$combined[$field['key']] = $f;
				$combined[$field['key']]['priority'] = (is_numeric($field['priority'])) ? $field['priority'] : 99;
				$combined[$field['key']]['label'] = $field['label'];
				$combined[$field['key']]['enable'] = (isset($field['enable'])) ? $field['enable'] : 'on';
				$combined[$field['key']]['enable'] = (isset($field['enable'])) ? $field['enable'] : 'on';
				$present = true;
			}


			/* if custom field detected add field to field map */
			if (!$present) {
				$combined[$field['key']] = $field;
			}
		}

		return  $combined;
	}


}

add_action( 'init' , 'load_Inbound_Leads_Custom_fields' );
function load_Inbound_Leads_Custom_fields() {
	new Inbound_Leads_Custom_fields;
}