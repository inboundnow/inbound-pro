<?php


if ( !class_exists('Inbound_Analytics_Template_Loader') ) {

	class Inbound_Analytics_Template_Loader {
		
		public function __construct() {
			
			/* Load and Dislay Correct Template */
			add_action('admin_init' , array( __CLASS__ , 'display_template' ) );
			
		}
		
		public static function display_template() {
			
			if ( !isset( $_REQUEST['action'] )  || $_REQUEST['action'] != 'inbound_generate_report' ) {
				return;
			}
			
			$args = array( 
				'date-range' => $_REQUEST['date_range']
			);
			
			$_REQUEST['report_class_name']::load_template( $args );
			
			exit;
			
		}
		
	}
	
	$GLOBALS['Inbound_Analytics_Template_Loader'] = new Inbound_Analytics_Template_Loader;
}