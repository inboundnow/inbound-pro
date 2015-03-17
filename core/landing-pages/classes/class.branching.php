<?php

if ( !class_exists('Landing_Pages_Branching')	) {

	class Landing_Pages_Branching {
		
		
		/**
		* Load class instance
		*/
		public function __construct() {
			self::load_hooks();
		}

		/**
		*  Load hooks and filters
		*/
		public static function load_hooks() {			
			
			/* adds branching capabilities to branching API for landing pages */
			add_filter( 'inbound_plugin_branches' , array( __CLASS__ ,  'add_branches' )  , 10 , 1 );
			
			/* reset active branch during svn update */
			//add_action( 'activate_landing_pages' , array( __CLASS__ , 'reset_branch_status' ) , 10 , 1 );
		}
		
		/**
		* Add branches to branching api for landing pages plugin
		*
		*/
		public static function add_branches( $branches ) {
			$branches['landing-pages'] = array(
				'git' => 'https://codeload.github.com/inboundnow/landing-pages/zip/master',
				'svn' => 'https://downloads.wordpress.org/plugin/landing-pages.'.LANDINGPAGES_CURRENT_VERSION.'.zip'
			);
			
			return $branches;
		}
		
		
	}

	$GLOBALS['Landing_Pages_Branching'] = new Landing_Pages_Branching;
}
