<?php
/**
 * Action Name: Add/Remove from lead list
 * Action Description: Adds or remove lead from list
 * Action Author: Inbound Now
 */

if ( !class_exists( 'Inbound_Automation_Action_Add_Remove_List' ) ) {

    class Inbound_Automation_Action_Add_Remove_List {

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


            /* Get Lead Lists */
            $lead_lists = Inbound_Leads::get_lead_lists_as_array();

            /* Build Action */
            $actions['add_remove_lead_list'] = array (
                'class_name' => get_class(),
                'id' => 'add_remove_lead_list',
                'label' => __( 'Add/Remove from list' , 'inbound-pro' ),
                'description' => __( 'Action to add or remove a lead from a lead list.' , 'inbound-pro' ),
                'settings' => array (
                    array (
                        'id' => 'remove_from_lead_lists',
                        'label' => __( 'Remove from these lists:' , 'inbound-pro' ),
                        'type' => 'select2',
                        'hidden' => false,
                        'options' => $lead_lists
                    ),
                    array (
                        'id' => 'add_to_lead_lists',
                        'label' => __( 'Add to these lists:' , 'inbound-pro' ),
                        'type' => 'select2',
                        'hidden' => false,
                        'options' => $lead_lists
                    ),
                    array (
                        'id' => 'message',
                        'label' => '',
                        'type' => 'html',
                        'hidden' => false,
                        'default' => '<i>' . __( 'If a user has unsubscribed from a list then automation will not resubscribe them. They will have to fill out an inbound form adding them to the list or be re-added manually by an administrator.' , 'inbound-pro' ) . '</i>'
                    )
                )
            );

            return $actions;
        }


        /**
        * Run the action
        */
        public static function run_action( $action , $arguments ) {
            global $Inbound_Leads;

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
                    __( 'List Change - Fail' , 'inbound-pro') ,
                    '<h2>'.__('Failed - bad lead id', 'inbound-pro') .'</h2><pre>'.print_r(arguments,true).'</pre>' .
                    $action['rule_id'] ,
                    $action['job_id'] ,
                    'action_event'
                );

                return;

            }

            /* Listen for List Adds */
            if ( isset($action['add_to_lead_lists']) && is_array($action['add_to_lead_lists']) ) {

                /* get stop sort rules for lead */
                $stop_rules = Inbound_Mailer_Unsubscribe::get_stop_rules( $final_args['id'] );

                foreach ( $action['add_to_lead_lists'] as $list_id ) {

                    /* check if there is a stop rule on this list */
                    if ( isset($stop_rules[$list_id]) && ( $stop_rules[$list_id] == 'unsubscribed' || $stop_rules[$list_id] === true ) ) {
                        $skipped[] = $list_id;
                        continue;
                    }

                    $Inbound_Leads->add_lead_to_list( $final_args['id'] , intval($list_id) );
                    $added[] = $list_id;
                }
            }

            /* Listen for List Removes */
            if ( isset($action['remove_from_lead_lists']) && is_array($action['remove_from_lead_lists']) ) {
                foreach ( $action['remove_from_lead_lists'] as $list_id ) {

                    $Inbound_Leads->remove_lead_from_list( $final_args['id'] , intval($list_id) );
                    $removed[] = $list_id;
                }
            }

            /* log the action event */
            inbound_record_log(
                __( 'List Change' , 'inbound-pro') ,
                '<h2>'.__('Addded to', 'inbound-pro') .'</h2><pre>'.print_r($added,true).'</pre>' .
                '<h2>'.__('Removed from' , 'inbound-pro') .'</h2><pre>'. print_r($removed,true).'</pre>'.
                '<h2>'.__('Skipped due to prior unsubscribe' , 'inbound-pro') .'</h2><pre>'. print_r($skipped,true).'</pre>',
                $action['rule_id'] ,
                $action['job_id'] ,
                'action_event'
            );

        }


    }

    /* Load Action */
    $Inbound_Automation_Action_Add_Remove_List = new Inbound_Automation_Action_Add_Remove_List();

}
