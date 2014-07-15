<?php
/*
Plugin Name: Inbound Extension - GetResponse Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides GetResponse support for Landing Pages, Leads, and Calls to Action plugin. For information about how to disable double-optin see: http://support.getresponse.com/faq/how-i-edit-opt-in-settings.
Version: 1.0.5
Author: Hudson Atwell, David Wells
Author URI: http://www.inboundnow.com/
*/

/* 
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/ 

define('INBOUNDNOW_GETRESPONSE_CURRENT_VERSION', '1.0.5' );
define('INBOUNDNOW_GETRESPONSE_LABEL' , 'GetResponse Integration' );
define('INBOUNDNOW_GETRESPONSE_FILE' , __FILE__ );
define('INBOUNDNOW_GETRESPONSE_SLUG' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_GETRESPONSE_REMOTE_ITEM_NAME', 'getresponse-integration' );
define('INBOUNDNOW_GETRESPONSE_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('INBOUNDNOW_GETRESPONSE_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );

/* load core files */
switch (is_admin()) :
	case true : 
		/* loads admin files */	
		include_once('modules/module.extension-setup.php');
		include_once('modules/module.getresponse-connect.php');
		include_once('modules/module.global-settings.php');
		include_once('modules/module.form-settings.php');
		include_once('modules/module.metaboxes.php');
		include_once('modules/module.bulk-export.php');
		include_once('modules/module.subscribe.php');
		break;
		
	case false :
		/* loads frontend files */				
		include_once('modules/module.getresponse-connect.php');		
		include_once('modules/module.subscribe.php');

		break;
		
endswitch;

		