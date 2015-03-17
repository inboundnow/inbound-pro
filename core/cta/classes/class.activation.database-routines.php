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
		
			}
		}
		
		/**
		*  Creates an example call to action
		* @introduced: 2.0.0
		* 
		* 
		*/
		public static function default_content() {
			
			$results = new WP_Query( array(
				's' => __( 'A/B Testing Call To Action Example' , 'cta' )						
			) );
			
			/* Make sure post does not exist before continuing */
			if ( $results->have_posts() ) {
				return;
			}
			
			$current_user = wp_get_current_user();
			
			$default_lander = wp_insert_post(
					array(
						'post_title'     => __( 'A/B Testing Call To Action Example' , 'cta' ),
						'post_content'   => '',
						'post_status'    => 'publish',
						'post_author'    => $current_user->ID,
						'post_type'      => 'wp-call-to-action',
						'comment_status' => 'closed'
					)
				);
			/* Variation A */
			add_post_meta($default_lander, 'wp-cta-selected-template', 'flat-cta');
			add_post_meta($default_lander, 'wp_cta_width-0', '310');
			add_post_meta($default_lander, 'wp_cta_height-0', '300');
			add_post_meta($default_lander, 'flat-cta-header-text', __( 'Snappy Headline' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-sub-header-text', __('Awesome Subheadline Text Goes here' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-text-color', '000000');
			add_post_meta($default_lander, 'flat-cta-content-color', '60BCF0');
			add_post_meta($default_lander, 'flat-cta-content-text-color', 'ffffff');
			add_post_meta($default_lander, 'flat-cta-submit-button-color', 'ffffff');
			add_post_meta($default_lander, 'flat-cta-submit-button-text', __( 'Download Now' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-link_url', 'http://www.inboundnow.com');

			/* Variation B */
			add_post_meta($default_lander, 'wp-cta-selected-template-1', 'flat-cta');
			add_post_meta($default_lander, 'wp_cta_width-1', '310');
			add_post_meta($default_lander, 'wp_cta_height-1', '300');
			add_post_meta($default_lander, 'flat-cta-header-text-1', __( 'Great Offer' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-sub-header-text-1', __( 'Amazing Deals Await!<br> Click below to find<br> amazing deals' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-text-color-1', '000000');
			add_post_meta($default_lander, 'flat-cta-content-color-1', 'f22424');
			add_post_meta($default_lander, 'flat-cta-content-text-color-1', 'ffffff');
			add_post_meta($default_lander, 'flat-cta-submit-button-color-1', 'ffffff');
			add_post_meta($default_lander, 'flat-cta-submit-button-text-1', __( 'Learn More' , 'cta' ));
			add_post_meta($default_lander, 'flat-cta-link_url-1', 'http://www.inboundnow.com');

			/* Add A/B Testing meta */
			add_post_meta($default_lander, 'wp-cta-variations', '{ "0":{"status":"active"} , "1":{"status":"active"} }');
			add_post_meta($default_lander, 'wp-cta-ab-variation-impressions-0', 115);
			add_post_meta($default_lander, 'wp-cta-ab-variation-impressions-1', 113);
			add_post_meta($default_lander, 'wp-cta-ab-variation-conversions-0', 15);
			add_post_meta($default_lander, 'wp-cta-ab-variation-conversions-1', 27);

			add_post_meta($default_lander, 'link_open_option', 'this_window');
			
		
		}
	}

}