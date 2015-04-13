<?php

if ( !class_exists('Leads_Field_Map') ) {

	class Leads_Field_Map {

		static $field_map;

		/* Define Default Lead Fields */
		public static function get_lead_fields() {

			$lead_fields = array(
				array(
					'label' => __( 'First Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_first_name',
					'priority' => 20,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Last Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 30,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Email' , 'inbound-pro' ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 40,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Website' , 'inbound-pro' ) ,
					'key'  => 'wpleads_website',
					'priority' => 50,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Job Title' , 'inbound-pro' ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 60,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Company Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 70,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Mobile Phone' , 'inbound-pro' ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 80,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Work Phone' , 'inbound-pro' ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 90,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address' , 'inbound-pro' ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 100,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Address Continued' , 'inbound-pro' ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 110,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'City' , 'inbound-pro' ) ,
					'key'  => 'wpleads_city',
					'priority' => 120,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'State/Region' , 'inbound-pro' ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 130,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Zip-code' , 'inbound-pro' ) ,
					'key'  => 'wpleads_zip',
					'priority' => 140,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Country' , 'inbound-pro' ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 150,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing First Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_first_name',
					'priority' => 160,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Last Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_last_name',
					'priority' => 120,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Company' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_company_name',
					'priority' => 170,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_address_line_1',
					'priority' => 180,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Address Continued' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_address_line_2',
					'priority' => 190,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing City' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_city',
					'priority' => 200,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing State/Region' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_region_name',
					'priority' => 210,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Billing Zip-code' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_zip',
					'priority' => 220,
					'type'  => 'text',
					'nature' => 'core'
				),

				array(
					'label' => __( 'Billing Country' , 'inbound-pro' ) ,
					'key'  => 'wpleads_billing_country_code',
					'priority' => 230,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping First Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_first_name',
					'priority' => 240,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Last Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_last_name',
					'priority' => 250,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Company Name' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_company_name',
					'priority' => 260,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 270,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Address Continued' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 280,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping City' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 290,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping State/Region' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 300,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Zip-code' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 310,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Shipping Country' , 'inbound-pro' ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 320,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Related Websites' , 'inbound-pro' ) ,
					'key'  => 'wpleads_websites',
					'priority' => 330,
					'type'  => 'links',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Notes' , 'inbound-pro' ) ,
					'key'  => 'wpleads_notes',
					'priority' => 340,
					'type'  => 'textarea',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Twitter Account' , 'inbound-pro' ) ,
					'key'  => 'wpleads_social_twitter',
					'priority' => 350,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Youtube Account' , 'inbound-pro' ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 360,
					'type'  => 'text',
					'nature' => 'core'
				),
				array(
					'label' => __( 'Facebook Account' , 'inbound-pro' ) ,
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
				'text' => __( 'text' , 'inbound-pro' ),
				'textarea' => __( 'textarea' , 'inbound-pro' ),
				'radio' => __( 'radio' , 'inbound-pro' ),
				'checkbox' => __( 'chekcbox' , 'inbound-pro' ),
				'dropdown' => __( 'dropdown' , 'inbound-pro' ),
				'dropdown-country' => __( 'dropdown-country' , 'inbound-pro' ),
				'links' => __( 'links' , 'inbound-pro' ),
				'wysiwyg' => __( 'wysiwyg' , 'inbound-pro' ),
				'media' => __( 'media' , 'inbound-pro' )
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

/**
 * Add in custom lead fields
 *
 * This function adds additional fields to your lead profiles.
 * Label: Name of the Field
 * key: Meta key associated with data
 * priority: Where you want the fields placed. See https://github.com/inboundnow/leads/blob/master/modules/module.userfields.php#L7 for current weights
 * type: type of user area. 'text' or 'textarea'
 */

/*
add_filter('wp_leads_add_lead_field', 'custom_add_more_lead_fields', 10, 1);
function custom_add_more_lead_fields($lead_fields) {

 $new_fields =  array(
 					array(
				        'label' => __( 'Style' , 'inbound-pro' ) ,
				        'key'  => 'wpleads_style',
				        'priority' => 1,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'Lead Source' , 'inbound-pro' ) ,
				        'key'  => 'wpleads_lead_source',
				        'priority' => 19,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'New Field' , 'inbound-pro' ) ,
				        'key'  => 'wpleads_lead_source',
				        'priority' => 19,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'Description' , 'inbound-pro' ) ,
				        'key'  => 'wpleads_description',
				        'priority' => 19,
				        'type'  => 'textarea'
				        )
				    );

		foreach ($new_fields as $key => $value) {
			array_push($lead_fields, $new_fields[$key]);
		}

        return $lead_fields;

}
/**/
