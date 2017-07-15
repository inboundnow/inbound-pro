<?php

/**
 * DERPRECIATED Class for defining activation routines specific to automation. Public methods in this class will be run at least once during plugin activation script. Updater methods fired are stored in transient to prevent repeat processing
 * @package     Automation
 * @subpackage  Activation
 * @see: depreciated
 */
class Inbound_Automation_Activation_Update_Routines {

    public static function create_automation_queue_table() {
        /* ignore if not applicable */
        $previous_installed_version = get_transient('automation_current_version');

        if (!$previous_installed_version ||  version_compare($previous_installed_version , "2.0.1") === 1 )  {
            return;
        }

        Inbound_Automation_Activation::create_automation_queue_table();
    }

}

