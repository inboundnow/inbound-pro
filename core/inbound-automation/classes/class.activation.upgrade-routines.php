<?php

/**
 *   Public methods in this class will be run at least once during plugin activation script.
 *   Updater methods fired are stored in transient to prevent repeat processing
 *
 */

if ( !class_exists('Inbound_Automation_Activation_Update_Routines') ) {

    class Inbound_Automation_Activation_Update_Routines {

        public static function create_automation_queue_table() {
            /* ignore if not applicable */
            $previous_installed_version = get_transient('automation_current_version');

            if ( version_compare($previous_installed_version , "2.0.0") === 1 )  {
                return;
            }

            Inbound_Automation_Activation::create_automation_queue_table();
        }

        /**
         * @introduced: 2.0.1
         * @migration-type: batch lead processing / data migration into inbound_events table
         * @details: Moving form submissions, cta clicks, custom events into events table.
         * @details: 112015 represents date added in
         */
        public static function batch_import_legacy_rules7x7x() {

            /* ignore if not applicable */
            $previous_installed_version = get_transient('automation_current_version');

            if ( version_compare($previous_installed_version , "1.0.1") === 1 )  {
                return;
            }

            /* create flag for batch uploader */
            $processing_jobs = get_option('automation_batch_processing');
            $processing_jobs = ($processing_jobs) ? $processing_jobs : array();
            $processing_jobs['import_legacy_rules'] = array(
                'method' => 'import_legacy_rules', 	/* tells batch processor which method to run */
                'posts_per_page' => -1, 					/* leads per query */
                'offset' => 0 								/* initial page offset */
            );

            /* create flag for batch uploader */
            update_option(
                'automation_batch_processing', 		/* db option name - lets batch processor know it's needed */
                $processing_jobs,
                0 , 							/* depreciated leave as 0 */
                false 							/* autoload true */
            );

        }

    }

}
