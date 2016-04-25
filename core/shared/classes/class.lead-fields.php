<?php

if ( !class_exists('Leads_Field_Map') ) {

	class Leads_Field_Map {

		static $field_map;

		/* Define Default Lead Fields */
		public static function get_lead_fields() {

			$lead_fields = array(
				array(
					'label' => __( 'First Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_first_name',
					'priority' => 1,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Last Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 2,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Email', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 3,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Website', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_website',
					'priority' => 4,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Job Title', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 5,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Company Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 6,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Mobile Phone', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 7,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Work Phone', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 8,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 9,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address Continued', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 10,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'City', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_city',
					'priority' => 11,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'State/Region', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 12,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Zip-code', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_zip',
					'priority' => 13,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Country', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 14,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing First Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_first_name',
					'priority' => 15,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Last Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_last_name',
					'priority' => 16,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Company', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_company_name',
					'priority' => 17,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_address_line_1',
					'priority' => 18,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address Continued', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_address_line_2',
					'priority' => 19,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing City', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_city',
					'priority' => 20,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing State/Region', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_region_name',
					'priority' => 21,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Zip-code', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_zip',
					'priority' => 22,
					'type'  => 'text',
					'nature' => 'core'
				),

				array(
					'label' => __( 'Billing Country', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_country_code',
					'priority' => 23,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping First Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_first_name',
					'priority' => 24,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Last Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_last_name',
					'priority' => 25,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Company Name', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_company_name',
					'priority' => 26,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 27,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address Continued', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 28,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping City', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 29,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping State/Region', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 30,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Zip-code', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 31,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Country', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 32,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Related Websites', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_websites',
					'priority' => 33,
					'type'  => 'links',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Notes', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_notes',
					'priority' => 34,
					'type'  => 'textarea',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Twitter Account', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_twitter',
					'priority' => 35,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Youtube Account', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 36,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Facebook Account', INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_facebook',
					'priority' => 37,
					'type'  => 'text',
					'nature' => 'core'
				)

			);

			$lead_fields = apply_filters('wp_leads_add_lead_field',$lead_fields);

			return $lead_fields;
		}



		/**
		*  		Builds key=>label array of lead fields
		*/
		public static function build_map_array() {

			$lead_fields = Leads_Field_Map::get_lead_fields();
			$lead_fields = Leads_Field_Map::prioritize_lead_fields( $lead_fields );

			$field_map = array();
			$field_map[''] = 'No Mapping'; /* default empty */
			foreach ($lead_fields as $key=>$field) {
				if (!isset($field['key'])) {
					continue;
				}

				$label = $field['label'];
				$key = $field['key'];
				$field_map[$key] = $label;
			}

			return $field_map;
		}

		/**
		*  	Builds array of available field types
		*/
		public static function build_field_types_array() {

			return apply_filters( 'wp_leads_field_types', array(
				'text' => __( 'text', INBOUNDNOW_TEXT_DOMAIN ),
				'textarea' => __( 'textarea', INBOUNDNOW_TEXT_DOMAIN ),
				'radio' => __( 'radio', INBOUNDNOW_TEXT_DOMAIN ),
				'checkbox' => __( 'chekcbox', INBOUNDNOW_TEXT_DOMAIN ),
				'dropdown' => __( 'dropdown', INBOUNDNOW_TEXT_DOMAIN ),
				'dropdown-country' => __( 'dropdown-country', INBOUNDNOW_TEXT_DOMAIN ),
				'links' => __( 'links', INBOUNDNOW_TEXT_DOMAIN ),
				'wysiwyg' => __( 'wysiwyg', INBOUNDNOW_TEXT_DOMAIN ),
				'media' => __( 'media', INBOUNDNOW_TEXT_DOMAIN )
			) );
		}

		/**
		*  Priorize Lead Fields Array
		*  @param ARRAY $fields simplified id => label array of lead fields
		*  @param STRING $sort_flags default = SORT_ASC
		*/
		public static function prioritize_lead_fields( $fields,  $sort_flags=SORT_ASC) {

			$prioritized = array();
			foreach ($fields as $key => $value) {
				while (isset($prioritized[$value['priority']])) {
					$value['priority']++;
				}
				$prioritized[$value['priority']] = $value;
			}

			ksort($prioritized, $sort_flags);

			return array_values($prioritized);

		}

		/**
		*  Gets lead field
		*  @param $lead_id
		*  @param $field_key
		*/
		public static function get_field( $lead_id, $field_key ) {
			return get_post_meta( $lead_id, $field_key, true);
		}

	}

}