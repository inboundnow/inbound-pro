<?php
/*
Plugin Name: Inbound Extension - ConstantContact Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides ConstantContact support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.4
Author: Inbound Now
Author URI: http://www.inboundnow.com/
*/

/* 
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/ 

define('INBOUNDNOW_CONSTANTCONTACT_CURRENT_VERSION', '1.0.4' );
define('INBOUNDNOW_CONSTANTCONTACT_LABEL' , 'ConstantContact Integration' );
define('INBOUNDNOW_CONSTANTCONTACT_FILE' , __FILE__ );
define('INBOUNDNOW_CONSTANTCONTACT_SLUG' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_CONSTANTCONTACT_TEXT_DOMAIN' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_CONSTANTCONTACT_REMOTE_ITEM_NAME' , 'constantcontact-integration' );
define('INBOUNDNOW_CONSTANTCONTACT_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('INBOUNDNOW_CONSTANTCONTACT_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );

/* load core files */
switch (is_admin()) :
	case true : 
		/* loads admin files */	
		include_once('modules/module.extension-setup.php');
		include_once('modules/module.global-settings.php');
		include_once('modules/module.form-settings.php');
		include_once('modules/module.metaboxes.php');
		include_once('modules/module.bulk-export.php');		
		include_once('modules/module.constantcontact-connect.php');
		include_once('modules/module.subscribe.php');

		BREAK;		
	case false :
		/* loads frontend files */						
		include_once('modules/module.constantcontact-connect.php');
		include_once('modules/module.subscribe.php');
		
		//if (!class_exists('ConstantContact'))
			//include_once('includes/constantcontact_api/autoload.php');	
		
		BREAK;
endswitch;

		