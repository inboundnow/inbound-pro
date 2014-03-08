<?php
/*
Plugin Name: Leads Pro
Plugin URI: http://www.inboundnow.com/leads/
Description: Track website visitor activity, manage incoming leads, and send collected emails to your email service provider.
Author: Inbound Now
Version: 1.0.0
Author URI: http://www.inboundnow.com/
Text Domain: leads-pro
Domain Path: shared/languages/leads/
*/

define('WPL_PRO_CURRENT_VERSION', '1.0.0' );
define('WPL_PRO_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define('WPL_PRO_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
define('WPL_PRO_CORE', plugin_basename( __FILE__ ) );
define('WPL_PRO_SLUG', plugin_basename( __FILE__ ) );
define('WPL_PRO_FILE',  __FILE__ );
define('WPL_PRO_STORE_URL', 'http://www.inboundnow.com' );
$uploads = wp_upload_dir();
define('WPL_PRO_UPLOADS_PATH', $uploads['basedir'].'/leads-pro/' );
define('WPL_PRO_UPLOADS_URLPATH', $uploads['baseurl'].'/leads-pro/' );

/* load core files */
include_once('components/lead-revisit-notifications/LeadRevisitClass.php');

/* load cron definitions - must be loaded outside of is_admin() conditional */
//include_once('modules/module.cron.lead-rules.php');

/* Inbound Core Shared Files. Lead files take presidence */
add_action( 'plugins_loaded', 'inbound_load_leads_pro' );
function inbound_load_leads_pro()
{
	/* Check if Shared Files Already Loaded */
	if (defined('INBOUDNOW_LEADS_PRO'))
		return;

	/* Define Shared Constant for Load Prevention*/
	define('INBOUDNOW_LEADS_PRO','loaded');

//	include_once('shared/tracking/store.lead.php'); // Lead Storage from landing pages

}