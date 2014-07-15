<?php
/*
Plugin Name: Inbound Extension - HubSpot Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides HubSpot support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.2
Author: Hudson Atwell, David Wells
Author URI: http://www.inboundnow.com/
*/

/* 
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/ 

define('INBOUNDNOW_HUBSPOT_CURRENT_VERSION', '1.0.2' );
define('INBOUNDNOW_HUBSPOT_LABEL' , 'HubSpot Integration' );
define('INBOUNDNOW_HUBSPOT_SLUG' , __FILE__ );
define('INBOUNDNOW_HUBSPOT_SLUG' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_HUBSPOT_REMOTE_ITEM_NAME', 'hubspot-integration' );
define('INBOUNDNOW_HUBSPOT_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('INBOUNDNOW_HUBSPOT_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );

/* load core files */
switch (is_admin()) :
	case true : 
		/* loads admin files */	
		include_once('modules/module.extension-setup.php');
		include_once('modules/module.hubspot-connect.php');
		include_once('modules/module.global-settings.php');
		include_once('modules/module.form-settings.php');
		include_once('modules/module.metaboxes.php');
		include_once('modules/module.bulk-export.php');
		include_once('modules/module.subscribe.php');
		break;
		
	case false :
		/* loads frontend files */				
		include_once('modules/module.hubspot-connect.php');		
		include_once('modules/module.subscribe.php');

		break;
		
endswitch;

		