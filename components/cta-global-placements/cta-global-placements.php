<?php
/*
Plugin Name: Calls to Action Extension - Global Placements
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Expands Call to Action placement options.
Version: 1.0.1
Author: InboundNow
Author URI: http://www.inboundnow.com/
*/

/* 
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/ 

define('CTA_PLACEMENTS_CURRENT_VERSION', '1.0.1' );
define('CTA_PLACEMENTS_LABEL' , 'ConstantContact Integration' );
define('CTA_PLACEMENTS_FILE' , __FILE__ );
define('CTA_PLACEMENTS_SLUG' , plugin_basename( dirname(__FILE__) ) );
define('CTA_PLACEMENTS_TEXT_DOMAIN' , plugin_basename( dirname(__FILE__) ) );
define('CTA_PLACEMENTS_REMOTE_ITEM_NAME' , 'cta-global-placements' );
define('CTA_PLACEMENTS_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('CTA_PLACEMENTS_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );

/* load core files */
switch (is_admin()) :
	case true : 
		/* loads admin files */	
		include_once('modules/module.extension-setup.php');
		include_once('modules/module.metaboxes.wp-call-to-action.php');		

		BREAK;		
	case false :
		/* loads frontend files */						
		include_once('modules/module.cta-placement.php');
		BREAK;
endswitch;

		