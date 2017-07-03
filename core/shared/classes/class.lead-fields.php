<?php

if ( !class_exists('Leads_Field_Map') ) {

	class Leads_Field_Map {

		static $field_map;

		/* Define Default Lead Fields */
		public static function get_lead_fields() {

			$lead_fields = array(
				array(
					'label' => __( 'First Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_first_name',
					'priority' => 1,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Last Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 2,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Email', 'inbound-pro' ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 3,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Website', 'inbound-pro' ) ,
					'key'  => 'wpleads_website',
					'priority' => 4,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Job Title', 'inbound-pro' ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 5,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Company Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 6,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Mobile Phone', 'inbound-pro' ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 7,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Work Phone', 'inbound-pro' ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 8,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address', 'inbound-pro' ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 9,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address Continued', 'inbound-pro' ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 10,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'City', 'inbound-pro' ) ,
					'key'  => 'wpleads_city',
					'priority' => 11,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'State/Region', 'inbound-pro' ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 12,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Zip-code', 'inbound-pro' ) ,
					'key'  => 'wpleads_zip',
					'priority' => 13,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Country', 'inbound-pro' ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 14,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing First Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_first_name',
					'priority' => 15,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Last Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_last_name',
					'priority' => 16,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Company', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_company_name',
					'priority' => 17,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_address_line_1',
					'priority' => 18,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address Continued', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_address_line_2',
					'priority' => 19,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing City', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_city',
					'priority' => 20,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing State/Region', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_region_name',
					'priority' => 21,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Zip-code', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_zip',
					'priority' => 22,
					'type'  => 'text',
					'nature' => 'core'
				),

				array(
					'label' => __( 'Billing Country', 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_country_code',
					'priority' => 23,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping First Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_first_name',
					'priority' => 24,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Last Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_last_name',
					'priority' => 25,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Company Name', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_company_name',
					'priority' => 26,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 27,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address Continued', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 28,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping City', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 29,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping State/Region', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 30,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Zip-code', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 31,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Country', 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 32,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Related Websites', 'inbound-pro' ) ,
					'key'  => 'wpleads_websites',
					'priority' => 33,
					'type'  => 'links',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Notes', 'inbound-pro' ) ,
					'key'  => 'wpleads_notes',
					'priority' => 34,
					'type'  => 'textarea',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Twitter Account', 'inbound-pro' ) ,
					'key'  => 'wpleads_social_twitter',
					'priority' => 35,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Youtube Account', 'inbound-pro' ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 36,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Facebook Account', 'inbound-pro' ) ,
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
			$field_map[''] = __('Not set.','inbound-pro'); /* default empty */
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
				'text' => __( 'text', 'inbound-pro' ),
				'textarea' => __( 'textarea', 'inbound-pro' ),
				'radio' => __( 'radio', 'inbound-pro' ),
				'checkbox' => __( 'checkbox', 'inbound-pro' ),
				'dropdown' => __( 'dropdown', 'inbound-pro' ),
				'dropdown-country' => __( 'dropdown-country', 'inbound-pro' ),
				'links' => __( 'links', 'inbound-pro' ),
				'wysiwyg' => __( 'wysiwyg', 'inbound-pro' ),
				'media' => __( 'media', 'inbound-pro' )
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