<?php
/*
Plugin Name: Inbound Now Professional
Plugin URI: http://www.inboundnow.com/leads/
Description: Pro Version of Inbound Now Plugins
Author: Inbound Now
Version: 1.0.0
Author URI: http://www.inboundnow.com/
Text Domain: inbound-now-pro
Domain Path: shared/languages/leads/
*/

if(!defined('INBOUND_NOW_ACTIVATE')) { define('INBOUND_NOW_ACTIVATE', __FILE__ ); }
if(!defined('INBOUND_NOW_CURRENT_VERSION')) { define('INBOUND_NOW_CURRENT_VERSION', '1.0.0' ); }
if(!defined('INBOUND_NOW_URL')) {  define('INBOUND_NOW_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) ); }
if(!defined('INBOUND_NOW_PATH')) {  define('INBOUND_NOW_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) ); }
if(!defined('INBOUND_NOW_CORE')) {  define('INBOUND_NOW_CORE', plugin_basename( __FILE__ ) );}
if(!defined('INBOUND_NOW_SLUG')) {  define('INBOUND_NOW_SLUG', plugin_basename( __FILE__ ) );}
if(!defined('INBOUND_NOW_FILE')) {  define('INBOUND_NOW_FILE',  __FILE__ );}
if(!defined('INBOUND_NOW_STORE_URL')) {  define('INBOUND_NOW_STORE_URL', 'http://www.inboundnow.com' );}
$uploads = wp_upload_dir();
if(!defined('INBOUND_NOW_UPLOADS_PATH')) {  define('INBOUND_NOW_UPLOADS_PATH', $uploads['basedir'].'/inbound-pro/' );}
if(!defined('INBOUND_NOW_UPLOADS_URLPATH')) {  define('INBOUND_NOW_UPLOADS_URLPATH', $uploads['baseurl'].'/inbound-pro/' );}

/* load core files */
include_once('components/lead-revisit-notifications/LeadRevisitClass.php');
include_once('components/inboundnow-zapier/inboundnow-zapier.php');
include_once('components/inboundnow-aweber/inboundnow-aweber.php');
//include_once('components/inboundnow-home-page-lander/inboundnow-home-page-lander.php');
include_once('classes/pro-welcome.class.php');

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