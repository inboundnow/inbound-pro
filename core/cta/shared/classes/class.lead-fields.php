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
					'priority' => 1,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Last Name' , 'leads' ) ,
					'key'  => 'wpleads_last_name',
					'priority' => 10,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Email' , 'leads' ) ,
					'key'  => 'wpleads_email_address',
					'priority' => 20,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Website' , 'leads' ) ,
					'key'  => 'wpleads_website',
					'priority' => 30,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Job Title' , 'leads' ) ,
					'key'  => 'wpleads_job_title',
					'priority' => 40,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Company Name' , 'leads' ) ,
					'key'  => 'wpleads_company_name',
					'priority' => 50,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Mobile Phone' , 'leads' ) ,
					'key'  => 'wpleads_mobile_phone',
					'priority' => 60,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Work Phone' , 'leads' ) ,
					'key'  => 'wpleads_work_phone',
					'priority' => 70,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Address' , 'leads' ) ,
					'key'  => 'wpleads_address_line_1',
					'priority' => 80,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Address Continued' , 'leads' ) ,
					'key'  => 'wpleads_address_line_2',
					'priority' => 81,
					'type'  => 'text'
					),
				array(
					'label' => __( 'City' , 'leads' ) ,
					'key'  => 'wpleads_city',
					'priority' => 90,
					'type'  => 'text'
					),
				array(
					'label' => __( 'State/Region' , 'leads' ) ,
					'key'  => 'wpleads_region_name',
					'priority' => 100,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Zip-code' , 'leads' ) ,
					'key'  => 'wpleads_zip',
					'priority' => 110,
					'type'  => 'text'
					),	

				array(
					'label' => __( 'Country' , 'leads' ) ,
					'key'  => 'wpleads_country_code',
					'priority' => 120,
					'type'  => 'text'
					),		
				array(
					'label' => __( 'Billing First Name' , 'leads' ) ,
					'key'  => 'wpleads_billing_first_name',
					'priority' => 130,
					'type'  => 'text'
					),		
				array(
					'label' => __( 'Billing Last Name' , 'leads' ) ,
					'key'  => 'wpleads_billing_last_name',
					'priority' => 120,
					'type'  => 'text'
					),		
				array(
					'label' => __( 'Billing Company' , 'leads' ) ,
					'key'  => 'wpleads_billing_company_name',
					'priority' => 120,
					'type'  => 'text'
					),	
				array(
					'label' => __( 'Billing Address' , 'leads' ) ,
					'key'  => 'wpleads_billing_address_line_1',
					'priority' => 140,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Billing Address Continued' , 'leads' ) ,
					'key'  => 'wpleads_billing_address_line_2',
					'priority' => 150,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Billing City' , 'leads' ) ,
					'key'  => 'wpleads_billing_city',
					'priority' => 160,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Billing State/Region' , 'leads' ) ,
					'key'  => 'wpleads_billing_region_name',
					'priority' => 170,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Billing Zip-code' , 'leads' ) ,
					'key'  => 'wpleads_billing_zip',
					'priority' => 180,
					'type'  => 'text'
					),	

				array(
					'label' => __( 'Billing Country' , 'leads' ) ,
					'key'  => 'wpleads_billing_country_code',
					'priority' => 190,
					'type'  => 'text'
					),			
				array(
					'label' => __( 'Shipping First Name' , 'leads' ) ,
					'key'  => 'wpleads_shipping_first_name',
					'priority' => 200,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Last Name' , 'leads' ) ,
					'key'  => 'wpleads_shipping_last_name',
					'priority' => 210,
					'type'  => 'text'
					),	
				array(
					'label' => __( 'Shipping Company Name' , 'leads' ) ,
					'key'  => 'wpleads_shipping_company_name',
					'priority' => 210,
					'type'  => 'text'
					),			
				array(
					'label' => __( 'Shipping Address' , 'leads' ) ,
					'key'  => 'wpleads_shipping_address_line_1',
					'priority' => 220,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Address Continued' , 'leads' ) ,
					'key'  => 'wpleads_shipping_address_line_2',
					'priority' => 230,
					'type'  => 'text'
					),
					array(
					'label' => __( 'Shipping City' , 'leads' ) ,
					'key'  => 'wpleads_shipping_city',
					'priority' => 240,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping State/Region' , 'leads' ) ,
					'key'  => 'wpleads_shipping_region_name',
					'priority' => 250,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Zip-code' , 'leads' ) ,
					'key'  => 'wpleads_shipping_zip',
					'priority' => 260,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Shipping Country' , 'leads' ) ,
					'key'  => 'wpleads_shipping_country_code',
					'priority' => 270,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Related Websites' , 'leads' ) ,
					'key'  => 'wpleads_websites',
					'priority' => 280,
					'type'  => 'links'
					),
				array(
					'label' => __( 'Notes' , 'leads' ) ,
					'key'  => 'wpleads_notes',
					'priority' => 290,
					'type'  => 'textarea'
					),
				array(
					'label' => __( 'Twitter Account' , 'leads' ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 290,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Youtube Account' , 'leads' ) ,
					'key'  => 'wpleads_social_youtube',
					'priority' => 290,
					'type'  => 'text'
					),
				array(
					'label' => __( 'Facebook Account' , 'leads' ) ,
					'key'  => 'wpleads_social_facebook',
					'priority' => 290,
					'type'  => 'text'
					)

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