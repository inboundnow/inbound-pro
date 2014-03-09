<?php
/*
Plugin Name: InboundNow Extension - Aweber Integration
Plugin URI: http://www.inboundnow.com/market/support-will-complete
Description: Provides Aweber support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.4
Author: Hudson Atwell, David Wells
Author URI: http://www.inboundnow.com/
*/

/*
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/

if(!defined('INBOUNDNOW_AWEBER_CURRENT_VERSION')) { define('INBOUNDNOW_AWEBER_CURRENT_VERSION', '1.0.4' ); }
if(!defined('INBOUNDNOW_AWEBER_LABEL')) { define('INBOUNDNOW_AWEBER_LABEL' , 'Aweber Integration' ); }
if(!defined('INBOUNDNOW_AWEBER_SLUG')) { define('INBOUNDNOW_AWEBER_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_AWEBER_FILE')) { define('INBOUNDNOW_AWEBER_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME' , 'aweber-integration' ); }
if(!defined('INBOUNDNOW_AWEBER_URLPATH')) { define('INBOUNDNOW_AWEBER_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }
if(!defined('INBOUNDNOW_AWEBER_PATH')) { define('INBOUNDNOW_AWEBER_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

/* load core files */
switch (is_admin()) :
	case true :
		/* loads admin files */
		include_once('modules/module.extension-setup.php');
		include_once('modules/module.global-settings.php');
		include_once('modules/module.form-settings.php');
		include_once('modules/module.metaboxes.php');
		include_once('modules/module.bulk-export.php');
		include_once('modules/module.subscribe.php');

		if (!class_exists('AWeberAPI'))
			include_once('includes/aweber_api/aweber_api.php');

		BREAK;
	case false :
		/* loads frontend files */
		include_once('modules/module.subscribe.php');

		if (!class_exists('AWeberAPI'))
			include_once('includes/aweber_api/aweber_api.php');

		BREAK;
endswitch;

