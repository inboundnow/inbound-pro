<?php
/*
Trigger Name: WordPress Post Categorization Event
Hook Reference: http://hookr.io/4.4/actions/added_term_relationship/
Trigger Description: This fires whenever a category is added to a post
Trigger Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Trigger_Set_Object_Terms' ) ) {

	class Inbound_Automation_Trigger_Set_Object_Terms {

        static $trigger;

		function __construct() {
            self::$trigger = 'set_object_terms';
			add_filter( 'inbound_automation_triggers' , array( __CLASS__ , 'define_trigger' ) , 1 , 1);
            //add_action( 'activate/inbound-automation' , array( __CLASS__ , 'create_dummy_event' ) );
		}

		/* Build Trigger Definitions */
		public static function define_trigger( $triggers ) {

			/* Set & Extend Trigger Argument Filters */
			$arguments = apply_filters('trigger/added_term_relationship/trigger_arguments/' , array(
					'object_id' => array(
						'id' => 'object_id',
						'label' => __( 'Post ID' , 'inbound-pro')
					),
					'terms' => array(
						'id' => 'terms',
						'label' => __( 'Terms' , 'inbound-pro'),
					),
					'tt_ids' => array(
						'id' => 'tt_ids',
						'label' => __( 'Terms Taxonomy IDs' , 'inbound-pro'),
					),
					'taxonomy' => array(
						'id' => 'taxonomy',
						'label' => __( 'Taxonomy slug' , 'inbound-pro'),
					),
					'append' => array(
						'id' => 'append',
						'label' => __( 'Append modifier.' , 'inbound-pro'),
					),
					'old_tt_ids' => array(
						'id' => 'old_tt_ids',
						'label' => __( 'Old array of term taxonomy IDs.' , 'inbound-pro'),
					)
			) );

			/* Set & Extend Action DB Lookup Filters */
			/* no db filters */
			$db_lookup_filters = apply_filters( 'trigger/added_term_relationship/db_arguments' , array (

			));

			/* Set & Extend Available Actions */
			$actions = apply_filters('trigger/added_term_relationship/actions' , array(
				'send_email' , 'wait' , 'relay_data'
			) );

			$triggers[ self::$trigger ] = array (
				'label' => __('On tag or categorize event' , 'inbound-pro'),
				'description' => __('This trigger fires whenever a tag or category is added to a post object.' , 'inbound-pro'),
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

		public static function get_taxonomy_data( $ttid ) {
			return get_term_by( 'term_taxonomy_id', $ttid, 'category', $output, $filter );
		}
	}

	/* Load Trigger */
	new Inbound_Automation_Trigger_Set_Object_Terms;

}