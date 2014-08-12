<?php

/* Public methods in this class will be run at least once during plugin activation script. */ 
/* Updater methods fired are stored in transient to prevent repeat processing */

if ( !class_exists('CTA_Activation_Update_Routines') ) {

	class CTA_Activation_Update_Routines {
		
		/**
		* @introduced: 2.0.8
		* @migration-type: Meta pair migragtion
		* @mirgration: convert meta key cta_ab_variations to wp-cta-variations & delete cta_ab_variations
		* @mirgration: convert meta key wp-cta-variation-notes to a sub key of wp-cta-variations object
		* @migration: convert all meta keys that do not have an -{{vid}} suffix to a -0 suffix
		*/
		public static function create_variation_objects() {
			$ctas = get_posts( array(
				'post_type' => 'wp-call-to-action',
				'posts_per_page' => -1
			));

			/* loop through ctas and migrate data */
			foreach ($ctas as $cta) {
				$variations = array();
				
				/* If CTA already has our upgraded meta key then continue to next cta*/ 
				if ( get_post_meta( $cta->ID , 'wp-cta-variations' , true ) ) {
					continue;
				}
					
				$legacy_value = get_post_meta( $cta->ID , 'cta_ab_variations' , true );
				$variation_ids_array = explode(',' , $legacy_value );
				$variation_ids_array = ($variation_ids_array) ? $variation_ids_array : array(0=>0);
				
				foreach ( $variation_ids_array as $vid ) {
					
					/* get variation status */
					$status = get_post_meta( $cta->ID , 'wp_cta_ab_variation_status' , true);
					
					/* Get variation notes & alter key for variations with vid=0 */
					if (!$vid) {
						
						/* for each meta without an variation id add one */
						$meta = get_post_meta( $cta->ID ); 
						$notes = get_post_meta( $cta->ID , 'wp-cta-variation-notes' , true );
						
						foreach ( $meta as $key => $value ) {
							if ( !is_numeric( substr( $key , -1) ) ) {
								add_post_meta( $cta->ID , $key . '-0' , $value[0] , true );
								//echo $cta->ID . ' ' .  $key . '-0 ' . $value[0] . '<br>';
							}
						}

					} else {
						$notes =  get_post_meta( $cta->ID , 'wp-cta-variation-notes-' . $vid , true);
					}
					
					if ( $status == 2 ) {
						$status = 'paused';
					} else {
						$status = 'active';
					}
					
					$variations[ $vid ][ 'status' ] = $status;
					$variations[ $vid ][ 'notes' ] = $notes;
				}
				
				CTA_Variations::update_variations ( $cta->ID , $variations );
		
				//echo '<hr>';
			}
			//exit;
		}
		

	}

}

