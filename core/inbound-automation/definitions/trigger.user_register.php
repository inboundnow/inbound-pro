<?php
/*
Trigger Name: WordPress user creation event
Trigger Description: This fires whenever a new user is created
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Trigger_User_Register' ) ) {

	class Inbound_Automation_Trigger_User_Register {

        static $trigger;

		function __construct() {
            self::$trigger = 'user_register';
			add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
            //add_action( 'activate/inbound-automation' , array( __CLASS__ , 'create_dummy_event' ) );
		}

		/* Build Trigger Definitions */
		public static function define_trigger( $triggers ) {

			/* Set & Extend Trigger Argument Filters */
			$arguments = apply_filters('trigger/user_register/trigger_arguments/' , array(
					'user_id' => array(
						'id' => 'user_id',
						'label' => __( 'User ID' , 'inbound-pro')
					)
			) );

			/* Set & Extend Action DB Lookup Filters */
			$db_lookup_filters = apply_filters( 'trigger/uer_register/db_arguments' , array (
				array(
                    'id' => 'user_data',
                    'label' => __( 'Validate User Data', 'inbound-pro' ),
                    'class_name' => 'Inbound_Automation_Query_User'
                )
			));

			/* Set & Extend Available Actions */
			$actions = apply_filters('trigger/user_register/actions' , array(
				'send_email' , 'wait' , 'relay_data'
			) );

			$triggers[ self::$trigger ] = array (
				'label' => __('On new WP user creation' , 'inbound-pro'),
				'description' => __('This trigger fires whenever new user is created inside the WordPress system.' , 'inbound-pro'),
				'action_hook' => self::$trigger ,
				'scheduling' => false,
				'arguments' => $arguments,
				'db_lookup_filters' => $db_lookup_filters,
				'actions' => $actions
			);

			return $triggers;
		}

        /**
         * Simulate trigger - perform on plugin activation
         */
        public static function create_dummy_event() {


            $event = array (
                'user_id' => 1
            );

            $inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );
            $inbound_arguments = ( $inbound_arguments  ) ?  $inbound_arguments : array();
            $inbound_arguments[self::$trigger]['user_id'] = $lead;

            Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' ,  $inbound_arguments );
        }
	}

	/* Load Trigger */
	$Inbound_Automation_Trigger_User_Register = new Inbound_Automation_Trigger_User_Register;

}