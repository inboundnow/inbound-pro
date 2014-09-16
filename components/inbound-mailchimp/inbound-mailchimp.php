<?php
/*
Plugin Name: Inbound Extension - MailChimp Integration
Plugin URI: http://www.inboundnow.com/market/
Description: Provides MailChimp support for Landing Pages, Leads, and Calls to Action plugin.
Version: 1.0.7
Author: Hudson Atwell, David Wells
Author URI: http://www.inboundnow.com/
Text Domain: inboundnow-mailchimp
Domain Path: lang
*/

/* 
---------------------------------------------------------------------------------------------------------
- Define constants & include core files
---------------------------------------------------------------------------------------------------------
*/ 

define('INBOUNDNOW_MAILCHIMP_CURRENT_VERSION' , '1.0.7' );
define('INBOUNDNOW_MAILCHIMP_LABEL' , 'MailChimp Integration' );
define('INBOUNDNOW_MAILCHIMP_FILE' , __FILE__ );
define('INBOUNDNOW_MAILCHIMP_SLUG' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_MAILCHIMP_TEXT_DOMAIN' , plugin_basename( dirname(__FILE__) ) );
define('INBOUNDNOW_MAILCHIMP_REMOTE_ITEM_NAME' , 'mailchimp-integration' );
define('INBOUNDNOW_MAILCHIMP_URLPATH' , WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('INBOUNDNOW_MAILCHIMP_PATH' , WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );


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
		
		if (!class_exists('MailChimp'))
			include_once('includes/mailchimp-api-master/MailChimp.class.php');		
		
		BREAK;
	case false :
		/* loads frontend files */				
		include_once('modules/module.subscribe.php');
		
		if (!class_exists('MailChimp'))
			include_once('includes/mailchimp-api-master/MailChimp.class.php');		
		
		BREAK;
endswitch;

		//include_once('modules/module.subscribe.php');