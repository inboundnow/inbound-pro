<?php
/*
Plugin Name: Calls to Action - Behavioral Targeting
Plugin URI: http://www.inboundnow.com/landing-pages/downloads/template-customizer/
Description: Personalize CTAs based on the list visitors belong to and more. 
Version: 1.0.1
Author: InboundNow
Author URI: http://www.inboudnow.com/
*/

//checks to make sure landing page plugin is active


if ( !class_exists('CTA_Behavioral_Plugin') ) {

	class CTA_Behavioral_Plugin {

		public function __construct() {
		
		

			define( 'CTA_BT_URLPATH' , WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define( 'CTA_BT_API', 'https://www.inboundnow.com' );
			define( 'CTA_BT_LABEL', 'Calls to Action - Behavioral Targeting' );
			define( 'CTA_BT_REMOTE_ITEM_NAME' , 'behavioral-calls-to-action' );
			define( 'CTA_BT_SLUG', plugin_basename( dirname(__FILE__) ) );
			define( 'CTA_BT_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CTA_BT_FILE', __FILE__ );
			define( 'CTA_BT_VERSION_NUMBER', '1.0.1' );


			/* load core files */
			switch (is_admin()) :
				case true :
					/* loads admin files */
					include_once('modules/module.metaboxes.php');
					include_once('modules/module.widgets.php');
					include_once('modules/module.cta-placement.php');
					include_once('modules/module.extension-setup.php');

					BREAK;

				case false :
					/* load front-end files */					
					include_once('modules/module.widgets.php');
					include_once('modules/module.cta-placement.php');
					
					BREAK;
			endswitch;
			
		}
		
	}
	
	$GLOBALS['CTA_Behavioral_Plugin'] = new CTA_Behavioral_Plugin;
	
}
?>