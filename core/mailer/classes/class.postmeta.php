<?php
/**
 * Class Inbound_Email_Meta provides a dat interface for storing and retrieving `inbound-email` CPT meta data
 *
 * @package Mailer
 * @subpackage  DataInterface
 */

class Inbound_Email_Meta {
	
	/** 
	*  Gets email settings
	*  @param INT $email_id
	*  @return ARRAY $email_settings
	*/
	public static function get_settings( $email_id ) {
		
		$email_settings = get_post_meta( $email_id , 'inbound_settings' , true );
			
		if (!$email_settings) {
			$email_settings = array();
		}
		
		return $email_settings;
	}
	
	/** 
	*  Updates inbound_settings post meta
	*  @param INT $email_id
	*  @param ARRAY $settings
	*/
	public static function update_settings( $email_id , $settings ) {
		
		/* Save settings array */
		update_post_meta( $email_id , 'inbound_settings' , $settings );
	}

}	