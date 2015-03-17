<?php

/* Public methods in this class will be run at least once during plugin activation script. */ 
/* Updater methods fired are stored in transient to prevent repeat processing */

if ( !class_exists('Landing_Pages_Activation_Update_Routines') ) {

	class Landing_Pages_Activation_Update_Routines {
		
		/* 
		* @introduced: 1.5.7
		* @migration-type: Meta pair migragtion
		* @migration: convert meta key lp-conversion-area to template-name-conversion-area-content-{vid}
		*/
		public static function migrate_legacy_conversion_area_contents() {
		
			/* for all landing pages load variations */
			$landing_pages = get_posts( array (
				'post_type' => 'landing-page',
				'posts_per_page' => -1
			));
			
			foreach ($landing_pages as $post) {
			
				/* for all variations loop through and migrate_data */				
				( get_post_meta($post->ID,'lp-ab-variations', true) ) ? $variations = get_post_meta($post->ID,'lp-ab-variations', true) : $variations = array( '0' => '0' );
				
				if (!is_array($variations) && strlen($variations) > 1 ) {
					$variations = explode(',',$variations);
				}
				
				foreach ($variations as $key=>$vid) {					
					
					($vid) ? $suffix = '-' . $vid : $suffix = '';
					
					$selected_template = get_post_meta( $post->ID , 'lp-selected-template' . $suffix , true );

					if ( !$selected_template ) {
						continue;
					}
						
					/* discover legacy main content */
					( $vid ) ? $conversion_area_content = get_post_meta( $post->ID , 'conversion-area-content' . $suffix , true ) : $conversion_area_content = get_post_meta( $post->ID , 'lp-conversion-area' , true );
					
					/* Now if the new key is not already poplated, copy the content to the new key */
					$check = get_post_meta( $post->ID , $selected_template .'-conversion-area-content' . $suffix , true );
					if (!$check) {
						update_post_meta( $post->ID , $selected_template .'-conversion-area-content' . $suffix , $conversion_area_content );
					}
					
				}
				
			}
		}
		
		/* 
		* @introduced: 1.7.5
		* @migration-type: Meta key rename
		* @migration: renames all instances of inbound_conversion_data to _inbound_conversion_data

		*/
		public static function meta_key_change_conversion_object() {
			global $wpdb;
			
			$wpdb->query("UPDATE $wpdb->postmeta SET `meta_key` = REPLACE (`meta_key` , 'inbound_conversion_data', '_inbound_conversion_data')");
		}
		
		/* 
		* @introduced: 1.5.7
		* @migration-type: Meta pair migragtion
		* @migration: mirgrates post_content and content-{vid} values to template-name-main-content-{vid}

		*/
		public static function migrate_legacy_main_content() {

			/* for all landing pages load variations */
			$landing_pages = get_posts( array (
				'post_type' => 'landing-page',
				'posts_per_page' => -1
			));
			
			foreach ($landing_pages as $post) {
			
				/* for all variations loop through and migrate_data */				
				( get_post_meta($post->ID,'lp-ab-variations', true) ) ? $variations = get_post_meta($post->ID,'lp-ab-variations', true) : $variations = array( '0' => '0' );
				
				if (!is_array($variations) && strlen($variations) > 1 ) {
					$variations = explode(',',$variations);
				}
				
				foreach ($variations as $key=>$vid) {					
					
					($vid) ? $suffix = '-' . $vid : $suffix = '';
					
					$selected_template = get_post_meta( $post->ID , 'lp-selected-template' . $suffix , true );
					if ( !$selected_template ) {
						continue;
					}
						
					/* discover legacy main content */
					( $vid ) ? $content = get_post_meta( $post->ID , 'content' . $suffix , true ) : $content = $post->post_content;
					
					/* Now if the new key is not already poplated, copy the content to the new key */
					$check = get_post_meta( $post->ID , $selected_template .'-main-content' . $suffix , true );
					if (!$check) {
						update_post_meta( $post->ID , $selected_template .'-main-content' . $suffix , $content );
					}
					
				}
				
			}
		}
		
		/* 
		* UPDATE METHOD
		* Moves legacy templates to uploads folder 
		*/
		public static function updater_move_legacy_templates() {
		
			/* move copy of legacy core templates to the uploads folder and delete from core templates directory */
			$templates_to_move = array('rsvp-envelope','super-slick');
			chmod(LANDINGPAGES_UPLOADS_PATH, 0755);

			$template_paths = Landing_Pages_Load_Extensions::get_core_template_ids();
			if (count($template_paths)>0)
			{
				foreach ($template_paths as $name)
				{
					if (in_array( $name, $templates_to_move ))
					{
						$old_path = LANDINGPAGES_PATH."templates/$name/";
						$new_path = LANDINGPAGES_UPLOADS_PATH."$name/";

						/*
						echo "oldpath: $old_path<br>";
						echo "newpath: $new_path<br>";
						*/

						@mkdir($new_path , 0775);
						chmod($old_path , 0775);

						self::move_files( $old_path , $new_path );

						rmdir($old_path);
					}
				}
			}
		}
		
		/* Private Method - Moves files from one folder the older. This is not an updater process */
		private static function move_files(	$old_path , $new_path	) {
			
			$files = scandir($old_path);
			
			if (!$files) {
				return;
			}
			
			foreach ($files as $file) {
				if (in_array($file, array(".",".."))) continue;

				if ($file==".DS_Store")
				{
					unlink($old_path.$file);
					continue;
				}

				if (is_dir($old_path.$file))
				{
					@mkdir($new_path.$file.'/' , 0775);
					chmod($old_path.$file.'/' , 0775);
					lp_move_template_files( $old_path.$file.'/' , $new_path.$file.'/' );
					rmdir($old_path.$file);
					continue;
				}

				/*
				echo "oldfile:".$old_path.$file."<br>";
				echo "newfile:".$new_path.$file."<br>";
				*/

				if (copy($old_path.$file, $new_path.$file)) {
					unlink($old_path.$file);
				}
			}
			
			$delete = (isset($delete)) ? $delete : false;
			
			if (!$delete) {
				return;
			}
		}
		
	}

}


/* Declare Helper Functions here */
function lp_move_template_files( $old_path , $new_path )
{

	

}