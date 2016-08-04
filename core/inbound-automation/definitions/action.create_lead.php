<?php
/**
 * Action Name: Create a lead
 * Action Description: Create a lead
 * Action Author: Inbound Now
 */

if ( !class_exists( 'Inbound_Automation_Action_Create_Lead' ) ) {

    class Inbound_Automation_Action_Create_Lead {

        /**
         * Initiate class
         */
        function __construct() {
            add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_action' ) , 1 , 1);
        }

        /**
         * Build Action Definitions
         */
        public static function define_action( $actions ) {
            /* Build Action */
            $actions['create_lead'] = array (
                'class_name' => get_class(),
                'id' => 'create_lead',
                'label' => __( 'Create Lead' , 'inbound-pro' ),
                'description' => __( 'Create a lead from user creation event.' , 'inbound-pro' ),
                'settings' => array ()
            );

            return $actions;
        }


        /**
         * Run the action
         */
        public static function run_action( $action , $trigger_data ) {

            global $Inbound_Leads;
            $args = array();

            /* support for user_register hook */
            if ( isset($trigger_data['user_id']) ) {
                $user = new WP_User( $trigger_data['user_id'] );
                $trigger_data['lead_data']['email'] = $user->data->user_email;

                /* look for lead */
                $trigger_data['lead_data']['id'] = LeadStorage::lookup_lead_by_email($trigger_data['lead_data']['email']);

                /* create lead if does not exist */
                if (!$trigger_data['lead_data']['id']) {
                    $args = array(
                        'user_ID' => $trigger_data['user_id'],
                        'wpleads_email_address' => $trigger_data['lead_data']['email'],
                        'wpleads_first_name' => $user->data->display_name
                    );

                    $trigger_data['lead_data']['id'] = inbound_store_lead( $args );
                }

            }

            /* log the action event */
            inbound_record_log(
                __( 'Created Lead' , 'inbound-pro') ,
                $trigger_data['lead_data']['id'] . ' <pre>' . print_r($args , true) . '</pre>',
                $action['rule_id'] ,
                $action['job_id'] ,
                'action_event'
            );

        }


    }

    /* Load Action */
    $Inbound_Automation_Action_Create_Lead = new Inbound_Automation_Action_Create_Lead();

}
