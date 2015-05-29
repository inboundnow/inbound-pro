<?php

if ( !class_exists('Leads_Field_Map') ) {

	class Leads_Field_Map {

		static $field_map;

		/* Define Default Lead Fields */
		public static function get_lead_fields() {

			$lead_fields = array(
				array(
					'label' => __( 'First Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_first_name',
					'priority' => 20,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Last Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 30,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Email' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 40,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Website' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_website',
					'priority' => 50,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Job Title' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 60,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Company Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 70,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Mobile Phone' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 80,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Work Phone' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 90,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 100,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address Continued' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 110,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'City' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_city',
					'priority' => 120,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'State/Region' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 130,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Zip-code' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_zip',
					'priority' => 140,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Country' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 150,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing First Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_first_name',
					'priority' => 160,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Last Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_last_name',
					'priority' => 120,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Company' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_company_name',
					'priority' => 170,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_address_line_1',
					'priority' => 180,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address Continued' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_address_line_2',
					'priority' => 190,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing City' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_city',
					'priority' => 200,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing State/Region' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_region_name',
					'priority' => 210,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Zip-code' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_zip',
					'priority' => 220,
					'type'  => 'text',
					'nature' => 'core'
				),

				array(
					'label' => __( 'Billing Country' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_billing_country_code',
					'priority' => 230,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping First Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_first_name',
					'priority' => 240,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Last Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_last_name',
					'priority' => 250,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Company Name' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_company_name',
					'priority' => 260,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 270,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address Continued' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 280,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping City' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 290,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping State/Region' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 300,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Zip-code' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 310,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Country' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 320,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Related Websites' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_websites',
					'priority' => 330,
					'type'  => 'links',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Notes' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_notes',
					'priority' => 340,
					'type'  => 'textarea',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Twitter Account' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_twitter',
					'priority' => 350,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Youtube Account' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 360,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Facebook Account' , INBOUNDNOW_TEXT_DOMAIN ) ,
					'key'  => 'wpleads_social_facebook',
					'priority' => 370,
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
			$field_map[''] = 'No Mapping'; // default empty
			foreach ($lead_fields as $key=>$field) {
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

			return apply_filters( 'wp_leads_field_types' , array(
				'text' => __( 'text' , INBOUNDNOW_TEXT_DOMAIN ),
				'textarea' => __( 'textarea' , INBOUNDNOW_TEXT_DOMAIN ),
				'radio' => __( 'radio' , INBOUNDNOW_TEXT_DOMAIN ),
				'checkbox' => __( 'chekcbox' , INBOUNDNOW_TEXT_DOMAIN ),
				'dropdown' => __( 'dropdown' , INBOUNDNOW_TEXT_DOMAIN ),
				'dropdown-country' => __( 'dropdown-country' , INBOUNDNOW_TEXT_DOMAIN ),
				'links' => __( 'links' , INBOUNDNOW_TEXT_DOMAIN ),
				'wysiwyg' => __( 'wysiwyg' , INBOUNDNOW_TEXT_DOMAIN ),
				'media' => __( 'media' , INBOUNDNOW_TEXT_DOMAIN )
			) );
		}

		/**
		*  Priorize Lead Fields Array
		*  @param ARRAY $fields simplified id => label array of lead fields
		*  @param STRING $sort_flags default = SORT_ASC
		*/
		public static function prioritize_lead_fields( $fields ,  $sort_flags=SORT_ASC) {

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
		public static function get_field( $lead_id , $field_key ) {
			return get_post_meta( $lead_id , $field_key , true);
		}

	}

}