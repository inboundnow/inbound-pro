<?php
/**
 * Action Name: Add/Remove from lead list
 * Action Description: Adds or remove lead from list
 * Action Author: Inbound Now
 */

if ( !class_exists( 'Inbound_Automation_Action_Add_Remove_Tag' ) ) {

    class Inbound_Automation_Action_Add_Remove_Tag {

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


            /* Get Lead Tags */
            $lead_tags = Inbound_Leads::get_lead_tags_as_array();

            /* Build Action */
            $actions['add_remove_lead_tag'] = array (
                'class_name' => get_class(),
                'id' => 'add_remove_lead_tag',
                'label' => __( 'Add/Remove Lead Tag' , 'inbound-pro' ),
                'description' => __( 'Action to add or remove a tag from a Lead.' , 'inbound-pro' ),
                'settings' => array (
                    array (
                        'id' => 'remove_from_lead_tags',
                        'label' => __( 'Remove these tags:' , 'inbound-pro' ),
                        'type' => 'select2',
                        'hidden' => false,
                        'options' => $lead_tags
                    ),
                    array (
                        'id' => 'add_to_lead_tags',
                        'label' => __( 'Add these tags:' , 'inbound-pro' ),
                        'type' => 'select2',
                        'hidden' => false,
                        'options' => $lead_tags
                    )
                )
            );

            return $actions;
        }


        /**
        * Run the action
        */
        public static function run_action( $action , $arguments ) {

            $added = array();
            $removed = array();
            $skipped = array();
            $final_args = array();

            /* Break multiple arrays into single array */
            foreach ($arguments as $key => $argument) {
                if (is_array($argument) ) {
                    foreach ($argument as $k => $value ) {
                        $final_args[ $k ] = $value;
                    }
                } else {
                    $final_args[ $key ] = $argument;
                }
            }

            /**
             * If no lead id is present bu a user_id is present then try to descover lead id
             */

            if ( !isset($final_args['id']) && isset($final_args['user_id']) ) {
                $user = new WP_User($final_args['user_id']);

                /* look for lead */
                $final_args['id'] = LeadStorage::lookup_lead_by_email($user->data->user_email);
            }

            /**
             * If there is still no lead id for some reason, lets throw an error log message and complete the action.
             */
            if ( empty($final_args['id']) ) {

                /* log the action event */
                inbound_record_log(
                    __( 'Tag Change - Fail' , 'inbound-pro') ,
                    '<h2>'.__('Failed - bad lead id', 'inbound-pro') .'</h2><pre>'.print_r(arguments,true).'</pre>' .
                    $action['rule_id'] ,
                    $action['job_id'] ,
                    'action_event'
                );

                return;

            }

            /*  for Tag Adds */
            if ( isset($action['add_to_lead_tags']) && is_array($action['add_to_lead_tags']) ) {
                

                foreach ( $action['add_to_lead_tags'] as $tag_id ) {

                    Inbound_Leads::add_tag_to_lead( $final_args['id'] , intval($tag_id) );
                    $added[] = $tag_id;
                }
            }

            /* for Tag Removes */
            if ( isset($action['remove_from_lead_tags']) && is_array($action['remove_from_lead_tags']) ) {
                foreach ( $action['remove_from_lead_tags'] as $tag_id ) {

                    Inbound_Leads::remove_tag_from_lead( $final_args['id'] , intval($tag_id) );
                    $removed[] = $tag_id;
                }
            }

            /* log the action event */
            inbound_record_log(
                __( 'Tag Change' , 'inbound-pro') ,
                '<h2>'.__('Addded to', 'inbound-pro') .'</h2><pre>'.print_r($added,true).'</pre>' .
                '<h2>'.__('Removed from' , 'inbound-pro') .'</h2><pre>'. print_r($removed,true).'</pre>'.
                $action['rule_id'] ,
                $action['job_id'] ,
                'action_event'
            );

        }


    }

    /* Load Action */
    new Inbound_Automation_Action_Add_Remove_Tag();

}
