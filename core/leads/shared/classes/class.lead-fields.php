<?php

if ( !class_exists('Leads_Field_Map') ) {

	class Leads_Field_Map {
		
		static $field_map;	
		
		/* Define Default Lead Fields */
		public static function get_lead_fields() {
		
			$lead_fields = array(
				array(
					'label' => __( 'First Name' , 'leads' ) ,
					'key'  => 'wpleads_first_name',
					'priority' => 15,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Last Name' , 'leads' ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 45,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Email' , 'leads' ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 60,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Website' , 'leads' ) ,
					'key'  => 'wpleads_website',
					'priority' => 60,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Job Title' , 'leads' ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 68,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Company Name' , 'leads' ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 75,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Mobile Phone' , 'leads' ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 90,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Work Phone' , 'leads' ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 105,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Address' , 'leads' ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 120,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Address Continued' , 'leads' ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 135,
					'type'  => 'text'
					),
				array(
					'label' => __( 'City' , 'leads' ) ,
					'key'  => 'wpleads_city',
					'priority' => 150,
					'type'  => 'text'
					),
				array(
					'label' => __( 'State/Region' , 'leads' ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 165,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Zip-code' , 'leads' ) ,
					'key'  => 'wpleads_zip',
					'priority' => 180,
					'type'  => 'text'
					),	

				array(
					'label' => __( 'Country' , 'leads' ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 195,
					'type'  => 'text'
					),			
				array(
					'label' => __( 'Shipping Address' , 'leads' ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 200,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Address Continued' , 'leads' ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 201,
					'type'  => 'text'
					),
					array(
					'label' => __( 'Shipping City' , 'leads' ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 202,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping State/Region' , 'leads' ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 203,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Zip-code' , 'leads' ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 204,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Country' , 'leads' ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 205,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Related Websites' , 'leads' ) ,
					'key'  => 'wpleads_websites',
					'priority' => 220,
					'type'  => 'links'
					),
				array(
					'label' => __( 'Notes' , 'leads' ) ,
					'key'  => 'wpleads_notes',
					'priority' => 225,
					'type'  => 'textarea'
					),

			);

			$lead_fields = apply_filters('wp_leads_add_lead_field',$lead_fields);

			return $lead_fields;
		}
		
		
		/* Builds key=>label array of lead fields */
		public static function build_map_array() {
			$lead_fields = Leads_Field_Map::get_lead_fields();
			
			
			$field_map = array();
			$field_map[''] = 'No Mapping'; // default empty
			foreach ($lead_fields as $key=>$field) {
					$label = $field['label'];
					$key = $field['key'];
					$field_map[$key] = $label;
			}
			
			return $field_map;
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

/**
add_filter('wp_leads_add_lead_field', 'custom_add_more_lead_fields', 10, 1);
function custom_add_more_lead_fields($lead_fields) {

 $new_fields =  array(
 					array(
				        'label' => __( 'Style' , 'leads' ) ,
				        'key'  => 'wpleads_style',
				        'priority' => 1,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'Lead Source' , 'leads' ) ,
				        'key'  => 'wpleads_lead_source',
				        'priority' => 19,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'New Field' , 'leads' ) ,
				        'key'  => 'wpleads_lead_source',
				        'priority' => 19,
				        'type'  => 'text'
				        ),
 					array(
				        'label' => __( 'Description' , 'leads' ) ,
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
?>