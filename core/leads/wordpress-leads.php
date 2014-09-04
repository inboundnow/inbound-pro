<?php
/*
Plugin Name: Leads
Plugin URI: http://www.inboundnow.com/leads/
Description: Track website visitor activity, manage incoming leads, and send collected emails to your email service provider.
Author: Inbound Now
Version: 1.4.6
Author URI: http://www.inboundnow.com/
Text Domain: leads
Domain Path: lang
*/

if ( ! class_exists( 'Inbound_Leads_Plugin' ) ) {

	final class Inbound_Leads_Plugin {

		/**
		 * Main Inbound_Leads_Plugin Instance
		 *
		*/
		public function __construct() {
			self::define_constants();
			self::includes();
			self::load_shared_files();
			self::load_text_domain();
		}

		/* Setup plugin constants */
		private static function define_constants() {
			define('WPL_CURRENT_VERSION', '1.4.6' );
			define('WPL_URLPATH',  plugins_url( '/', __FILE__ ) );
			define('WPL_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
			define('WPL_CORE', plugin_basename( __FILE__ ) );
			define('WPL_SLUG', plugin_basename( dirname(__FILE__) ) );
			define('WPL_FILE',  __FILE__ );
			define('WPL_STORE_URL', 'http://www.inboundnow.com' );
			$uploads = wp_upload_dir();
			define('WPL_UPLOADS_PATH', $uploads['basedir'].'/leads/' );
			define('WPL_UPLOADS_URLPATH', $uploads['baseurl'].'/leads/' );
		}

		/* Include required files */
		private static function includes() {

			if ( is_admin() ) {


				/* Admin Includes */
				require_once('modules/module.activate.php');
				require_once('modules/module.ajax-setup.php');
				require_once('modules/module.nav-menus.php');
				require_once('modules/module.metaboxes.wp-lead.php');
				require_once('modules/module.post-type.wp-lead.php');
				require_once('modules/module.post-type.landing-pages.php');
				require_once('modules/module.lead-management.php');
				require_once('modules/module.form-integrations.php');
				require_once('modules/module.global-settings.php');
				require_once('classes/class.dashboard.php');
				require_once('modules/module.tracking.php');
				require_once('modules/module.enqueue-admin.php');
				require_once('modules/module.form-integrations.php');
				require_once('classes/class.metaboxes.email-template.php');
				require_once('classes/class.wordpress-core.email.php');

			} else {
				/* Frontend Includes */
				/* load global */
				require_once('modules/module.ajax-setup.php');
				require_once('modules/module.post-type.wp-lead.php');
				require_once('modules/module.form-integrations.php');
				require_once('classes/class.metaboxes.email-template.php');
				require_once('classes/class.wordpress-core.email.php');
				/* load frontend */
				require_once('modules/module.enqueue-frontend.php');
				require_once('modules/module.tracking.php');

			}

			//require_once INBOUND_NOW_PATH . 'includes/install.php';
		}

		/* Load Shared Files */
		private static function load_shared_files() {
			require_once('shared/classes/class.load-shared.php');
			add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 1 );
		}

		/**
		*  Loads the correct .mo file for this plugin
		*
		*/
		private static function load_text_domain() {
			add_action('init' , function() {
				load_plugin_textdomain( 'leads' , false , WPL_SLUG . '/lang/' );
			});
		}
	}

	$GLOBALS['Inbound_Leads_Plugin'] = new Inbound_Leads_Plugin;
}

/* Load Shared Files */


// Legacy function
function wpleads_check_active() {
}
