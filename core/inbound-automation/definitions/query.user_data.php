<?php
/*
Query Name: User Queries
Query Description: Definitions and Lookup Maps for User Data
Query Author: Inbound Now
Contributors: Hudson Atwell
*/


if ( !class_exists( 'Inbound_Automation_Query_User' ) ) {

	class Inbound_Automation_Query_User {

		/**
		*  	Build Query Loopup Dataset
		*/
		public static function get_key_map( ) {

			$queries['user_role'] = __( 'User Role' , 'inbound-pro' );

			return $queries;
		}


		/* Gets Page View Count for Lead
		* @param ARRAY $trigger_data dataset of arguments sent over by trigger. One of the arguments must contain key 'lead_id'
		*
		* @return page_views INT
		*/

		public static function query_user_role( $argument_id , $arguments ) {

			$user_id = $arguments[ 'user_id' ];

			if ( !$user_id ) {
				return null;
			}


			$user = new WP_User( $user_id );
			$roles = array();

			if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
				foreach ( $user->roles as $role ) {
					$roles[] = $role;
				}
			}

			return json_encode($roles);
		}

	}
}