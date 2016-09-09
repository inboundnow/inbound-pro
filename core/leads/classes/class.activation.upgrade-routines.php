<?php

/* Public methods in this class will be run at least once during plugin activation script. */
/* Updater methods fired are stored in transient to prevent repeat processing */

if ( !class_exists('Leads_Activation_Update_Routines') ) {

	class Leads_Activation_Update_Routines {

		/**
		 * @introduced: 1.5.1
		 * @migration-type: db modification
		 * @mirgration: creates wp_inbound_link_tracking table
		 */
		public static function create_link_tracking_table() {
			global $wpdb;

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if ( version_compare($previous_installed_version , "1.5.1") === 1 )  {
				return;
			}

			$table_name = $wpdb->prefix . "inbound_tracked_links";

			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			$sql = "CREATE TABLE $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `token` tinytext NOT NULL,
			  `args` text NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

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

			if ( version_compare($previous_installed_version , "1.1.0") === 1 )  {
				return;
			}

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_conversion_count' where meta_key = 'wpl-lead-conversion-count'");

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_page_view_count' where meta_key = 'wpl-lead-page-view-count'");

			$wpdb->query("update {$wpdb->prefix}postmeta set meta_key = 'wpleads_raw_post_data' where meta_key = 'wpl-lead-raw-post-data'");

		}


		/**
		 * @introduced: 2.2.4
		 * @migration-type: alter inbound_events table
		 * @mirgration: adds column list_id to events table
		 */
		public static function alter_inbound_events_table_224() {

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if ( version_compare($previous_installed_version , "2.2.4") === 1 )  {
				return;
			}

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table_name = $wpdb->prefix . "inbound_events";

			/* add columns funnel and source to legacy table */
			$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'source'"  );
			if(empty($row)){
				// do your stuff
				$wpdb->get_results( "ALTER TABLE {$table_name} ADD `funnel` text NOT NULL" );
				$wpdb->get_results( "ALTER TABLE {$table_name} ADD `source` text NOT NULL" );
			}

			/* add columns list_id inbound events table */
			$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'list_id'"  );
			if(empty($row)){
				$wpdb->get_results( "ALTER TABLE {$table_name} ADD `list_id` mediumint(20) NOT NULL" );
			}
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

			if ( version_compare($previous_installed_version , "2.0.1") === 1 )  {
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

			if ( version_compare($previous_installed_version , "2.2.2") === 1 )  {
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

		/**
		 * @introduced: 2.2.6
		 * @migration-type: batch lead processing / updating inbound events table
		 * @details: Imports page events into new inbound_page_views table
		 * @details:
		 */
		public static function batch_repair_funnel_data() {

			/* ignore if not applicable */
			$previous_installed_version = get_transient('leads_current_version');

			if ( version_compare($previous_installed_version , "2.2.6") === 1 )  {
				return;
			}

			$processing_jobs = get_option('leads_batch_processing');
			$processing_jobs = ($processing_jobs) ? $processing_jobs : array();
			$processing_jobs['repair_funnel_data'] = array(
				'method' => 'repair_funnel_data', 	/* tells batch processor which method to run */
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

	}

}
