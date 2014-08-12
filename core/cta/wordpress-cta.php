<?php
/*
Plugin Name: Calls to Action
Plugin URI: http://www.inboundnow.com/cta/
Description: Display Targeted Calls to Action on your WordPress site.
Version: 2.1.3
Author: InboundNow
Author URI: http://www.inboundnow.com/
Text Domain: cta
Domain Path: lang
*/

if (!class_exists('Inbound_Calls_To_Action_Plugin')) {

	final class Inbound_Calls_To_Action_Plugin {

		/**
		* Main Inbound_Calls_To_Action_Plugin Instance
		*
		*/
		public function __construct() {
			self::define_constants();
			self::includes();
			self::load_shared_files();
			self::load_text_domain();
		}

		/*
		* Setup plugin constants
		*
		*/
		private static function define_constants() {

			define('WP_CTA_CURRENT_VERSION', '2.1.3' );
			define('WP_CTA_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('WP_CTA_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('WP_CTA_SLUG', plugin_basename( dirname(__FILE__) ) );
			define('WP_CTA_FILE', __FILE__ );

			$uploads = wp_upload_dir();
			define('WP_CTA_UPLOADS_PATH', $uploads['basedir'].'/calls-to-action/templates/' );
			define('WP_CTA_UPLOADS_URLPATH', $uploads['baseurl'].'/calls-to-action/templates/' );
			define('WP_CTA_STORE_URL', 'http://www.inboundnow.com/cta/' );

		}

		/* Include required plugin files */
		private static function includes() {

			switch (is_admin()) :
				case true :
					/* loads admin files */
					include_once('classes/class.activation.php');
					include_once('classes/class.activation.upgrade-routines.php');
					include_once('classes/class.post-type.wp-call-to-action.php');
					include_once('classes/class.extension.wp-lead.php');
					include_once('classes/class.extension.wordpress-seo.php');
					include_once('classes/class.metaboxes.wp-call-to-action.php');
					include_once('modules/module.admin-menus.php');
					include_once('modules/module.ajax-setup.php');
					include_once('modules/module.enqueue.php');
					include_once('modules/module.global-settings.php');
					include_once('modules/module.clone.php');
					include_once('modules/module.install.php');
					include_once('classes/class.cta.variations.php');
					include_once('modules/module.widgets.php');
					include_once('classes/class.cta.render.php');
					include_once('modules/module.load-extensions.php');
					include_once('modules/module.metaboxes-global.php');
					include_once('modules/module.templates.php');
					include_once('modules/module.store.php');
					include_once('modules/module.utils.php');
					include_once('modules/module.customizer.php');
					include_once('modules/module.track.php');

					BREAK;

				case false :
					/* load front-end files */
					include_once('modules/module.load-extensions.php');
					include_once('classes/class.post-type.wp-call-to-action.php');
					include_once('classes/class.extension.wp-lead.php');
					include_once('classes/class.extension.wordpress-seo.php');
					include_once('modules/module.enqueue.php');
					include_once('modules/module.track.php');
					include_once('modules/module.click-tracking.php');
					include_once('modules/module.ajax-setup.php');
					include_once('modules/module.widgets.php');
					include_once('modules/module.cookies.php');
					include_once('classes/class.cta.variations.php');
					include_once('classes/class.cta.render.php');
					include_once('modules/module.utils.php');
					include_once('modules/module.customizer.php');

					BREAK;
			endswitch;
		}

		/**
		 *  Loads components shared between Inbound Now plugins
		 *
		 */
		private static function load_shared_files() {
			require_once('shared/classes/class.load-shared.php');
			add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 3 );
		}

		/**
		*  Loads the correct .mo file for this plugin
		*
		*/
		private static function load_text_domain() {
			add_action('init' , function() {
				load_plugin_textdomain( 'cta' , false , WP_CTA_SLUG . '/lang/' );
			});
		}

	}

	/* Initiate Plugin */
	$GLOBALS['Inbound_Calls_To_Action_Plugin'] = new Inbound_Calls_To_Action_Plugin;

}
