<?php

/**
*	This class loads miscellaneous WordPress AJAX listeners 
*/
if (!class_exists()) {

	class Inbound_Ajax {
		
		/**
		*	Initializes classs
		*/
		public function __construct() {
			self::load_hooks();
		}

		/**
		*	Loads hooks and filters
		*/
		public static function load_hooks() {
			
			/* Ajax that runs on pageload */
			add_action( 'wp_ajax_nopriv_inbound_ajax', array( __CLASS__ , 'run_ajax_actions') );
			add_action( 'wp_ajax_inbound_ajax', array( __CLASS__ , 'run_ajax_actions') );
			
		}
		
		/**
		* Executes hook that runs all ajax actions
		*/
		public static function run_ajax_actions() {
			
		}
		
			
	}

	/* Loads Inbound_Ajax pre init */
	$Inbound_Ajax = new Inbound_Ajax();
}