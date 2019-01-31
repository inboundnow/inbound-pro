<?php
/**
 * Create User
 * @name Create a user
 * @description Create a user
 * @author Inbound Now
 * @package     Automation
 * @subpackage  Actions
 */

if ( !class_exists( 'Inbound_Automation_Action_Create_User' ) ) {

    class Inbound_Automation_Action_Create_User {

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
            $actions['create_user'] = array (
                'class_name' => get_class(),
                'id' => 'create_user',
                'label' => __( 'Create User / Add Role' , 'inbound-pro' ),
                'description' => __( 'Create a WordPress user account if one does not exist.' , 'inbound-pro' ),
                'settings' => array(
                    array (
                        'id' => 'role',
                        'label' => __( 'Set role' , 'inbound-pro' ),
                        'description' => __('Set which role to add user to.', 'inbound-pro'),
                        'type' => 'dropdown',
                        'options' => self::get_roles_that_cant('activate_plugins'),
                        'default' => 'subscriber'
                    ),
                    array (
                        'id' => 'login',
                        'label' => __( 'Log user in automatically' , 'inbound-pro' ),
                        'description' => __('In order for this feature to work deferred processing must be disabled.', 'inbound-pro'),
                        'type' => 'dropdown',
                        'options' => array(
                            'yes'=>'yes',
                            'no'=> 'no'
                        ),
                        'default' => 'yes'
                    )
                )
            );

            return $actions;
        }


        /**
         * Run the action
         */
        public static function run_action( $action , $trigger_data ) {

            $email_address = (isset($trigger_data['lead_data']['email'])) ? $trigger_data['lead_data']['email'] : null ;
            $first_name = (isset($trigger_data['lead_data']['wpleads_first_name'])) ? $trigger_data['lead_data']['wpleads_first_name'] : null ;
            $last_name = (isset($trigger_data['lead_data']['wpleads_last_name'])) ? $trigger_data['lead_data']['wpleads_last_name'] : null ;

            /* check if user already exists */
            if (email_exists($email_address)) {
                /* log the action event */
                inbound_record_log(
                    __( 'User Already Exists, Skipping.' , 'inbound-pro') ,
                    $trigger_data['lead_data']['id'] . ' <pre></pre>',
                    $action['rule_id'],
                    $action['job_id'],
                    'action_event'
                );
                return;
            }

            /* create user */
            $password = wp_generate_password( 12, false );
            $userdata = array(
                'user_login' => $email_address,
                'nickname' => $email_address,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'user_email' => $email_address,
                'role'   => $action['role'],
                'user_pass' => $password
            );

            $user_id = wp_insert_user( $userdata ) ;

            // ON SUCCESS
            if ( ! is_wp_error( $user_id )) {

                /* send user notification / change password email */
                $user = new WP_User( $user_id );
                wp_new_user_notification( $user_id, null , 'both' );

                /* log the action event */
                inbound_record_log(
                    __( 'Created User' , 'inbound-pro') ,
                    $trigger_data['lead_data']['id'] . ' <pre>' . print_r($user , true) . '</pre>',
                    $action['rule_id'] ,
                    $action['job_id'] ,
                    'action_event'
                );

                /* log the user in */
                if ($action['login'] == 'yes') {
                    $creds = array();
                    $creds['user_login'] = $email_address;
                    $creds['user_password'] = $password;
                    $creds['remember'] = true;
                    $user = wp_signon( $creds, false );
                    wp_set_current_user($user->ID);

                    /* log the action event */
                    inbound_record_log(
                        __( 'Logged User In' , 'inbound-pro') ,
                        $creds['user_login'] . print_r($user,true),
                        $action['rule_id'] ,
                        $action['job_id'] ,
                        'action_event'
                    );
                }


                return;
            }

            inbound_record_log(
                __( 'Could not create user' , 'inbound-pro') ,
                $trigger_data['lead_data']['id'] . ' <pre>'.json_encode($userdata).'</pre>',
                $action['rule_id'] ,
                $action['job_id'] ,
                'action_event'
            );
        }

        public static function get_roles_that_cant($capability) {
            global $wp_roles;

            if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();

            $available_roles_names = $wp_roles->get_names();//we get all roles names

            $available_roles_capable = array();
            foreach ($available_roles_names as $role_key => $role_name) { //we iterate all the names
                $role_object = get_role( $role_key );//we get the Role Object
                $array_of_capabilities = $role_object->capabilities;//we get the array of capabilities for this role
                if(!isset($array_of_capabilities[$capability]) || $array_of_capabilities[$capability] == 0){ //we check if the upload_files capability is present, and if its present check if its 0 (FALSE in Php)
                    $available_roles_capable[$role_key] = $role_name; //we populate the array of capable roles
                }
            }
            return $available_roles_capable;
        }


    }

    /* Load Action */
    new Inbound_Automation_Action_Create_User();

}
