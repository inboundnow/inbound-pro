<?php

/**
 * Class for defining and running database upgrade routines
 *
 * @package Leads
 * @subpackage Activation
 */

if ( !class_exists('Leads_Activation_Update_Routines') ) {

	class Leads_Activation_Update_Routines {

		/**
		 * @introduced: 1.1.0
		 * @migration-type: meta key update
		 * @mirgration: standardizes meta key from old naming conversion to new naming convention
		 * @keychange: wpl-lead-conversion-count to wpleads_conversion_count
		 * @keychange: wpl-lead-page-view-count to wpleads_page_view_count
		 * @keychange: wpl-lead-raw-post-data to wpleads_raw_post_data
		 */
		public static function migrate_meta_keys() {
			global $wpdb;

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if ( !$previous_installed_version || version_compare($previous_installed_version , "1.1.0") === 1 )  {
				return;
			}

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_conversion_count' where meta_key = 'wpl-lead-conversion-count'");

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_page_view_count' where meta_key = 'wpl-lead-page-view-count'");

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_raw_post_data' where meta_key = 'wpl-lead-raw-post-data'");

		}

		/**
		 * @introduced: 2.0.1
		 * @migration-type: batch lead processing / data migration into inbound_events table
		 * @details: Moving form submissions, cta clicks, custom events into events table.
		 * @details: 112015 represents date added in
		 */
		public static function batch_import_event_data_112015() {

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if (  !$previous_installed_version || version_compare($previous_installed_version , "2.0.1") === 1 )  {
				return;
			}

			/* lets make sure the inbound_events table is created */
			if ( !class_exists('Inbound_Events')) {
				Inbound_Load_Shared::load_constants();
				include_once( INBOUNDNOW_SHARED_PATH . 'classes/class.events.php');
			}
			Inbound_Events::create_events_table();

			/* create flag for batch uploader */
			$processing_jobs = get_option('leads_batch_processing');
			$processing_jobs = ($processing_jobs) ? $processing_jobs : array();
			$processing_jobs['import_events_table_112015'] = array(
				'method' => 'import_events_table_112015', 	/* tells batch processor which method to run */
				'posts_per_page' => 100, 					/* leads per query */
				'offset' => 0 								/* initial page offset */
			);

			/* create flag for batch uploader */
			update_option(
				'leads_batch_processing', 		/* db option name - lets batch processor know it's needed */
				$processing_jobs,
				0 , 							/* depreciated leave as 0 */
				false 							/* autoload true */
			);

		}

		/**
		 * @introduced: 2.2.2
		 * @migration-type: batch lead processing / updating inbound events table
		 * @details: Imports page events into new inbound_page_views table
		 * @details: 072016 represents date added in
		 */
		public static function batch_import_event_data_07132016() {

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if (  !$previous_installed_version || version_compare($previous_installed_version , "2.2.2") === 1 )  {
				return;
			}

			$processing_jobs = get_option('leads_batch_processing');
			$processing_jobs = ($processing_jobs) ? $processing_jobs : array();
			$processing_jobs['import_event_data_07132016'] = array(
				'method' => 'import_event_data_07132016', 	/* tells batch processor which method to run */
				'posts_per_page' => 40, 					/* leads per query */
				'offset' => 0 								/* initial page offset */
			);

			/* create flag for batch uploader */
			update_option(
				'leads_batch_processing', 		/* db option name - lets batch processor know it's needed */
				$processing_jobs,
				0 , 							/* depreciated leave as 0 */
				false 							/* autoload true */
			);

		}

	}
}
