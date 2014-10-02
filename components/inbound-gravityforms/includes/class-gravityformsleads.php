<?php
/*
 * @package   GravityFormsLeads
 * @copyright 2014 gravity+
 * @license   GPL-2.0+
 * @since     2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'GravityFormsLeads' ) ) {


	/**
	 * Class GravityFormsLeads
	 *
	 * Loads the plugin
	 *
	 * @since
	 */
	class GravityFormsLeads {

		/**
		 * Minimum Gravity Forms version allowed for this plugin
		 *
		 * @since 2.0.0
		 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
		 *
		 * @var string
		 */
		private $min_gf_version = '1.8.16';

		public function __construct () {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			/* Setup Automatic Updating & Licensing */
			add_action( 'admin_init', array( $this, 'license_setup' ) );
		}

		/**
		 * Load language files and Gravity Forms Add-On class
		 *
		 * @since 2.0.0
		 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
		 */
		public function plugins_loaded () {

			if ( class_exists( 'GFForms' ) && defined( 'WPL_CURRENT_VERSION' ) ) {
				if ( ! class_exists( 'GFFeedAddOn' ) ) {
					GFForms::include_feed_addon_framework();
				}

				require_once( 'class-addon.php' );
				$gf_leads_addon = new GravityFormsLeads_Addon( array( 'version'                    => INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION,
																	  'min_gf_version'             => $this->min_gf_version,
																	  'plugin_slug'                => INBOUNDNOW_GRAVITYFORMS_SLUG,
																	  'path'                       => INBOUNDNOW_GRAVITYFORMS_PATH,
																	  'full_path'                  => INBOUNDNOW_GRAVITYFORMS_FILE,
																	  'title'                      => 'Gravity Forms — WordPress Leads',
																	  'short_title'                => 'WordPress Leads',
																	  'url'                        => 'http://www.inboundnow.com/market/gravityforms-integration/',
																	  'capabilities'               => array( 'inboundnow_gravityforms_form_settings', 'inboundnow_gravityforms_plugin_page', 'inboundnow_gravityforms_uninstall' ),
																	  'capabilities_form_settings' => array( 'inboundnow_gravityforms_form_settings' ),
																	  'capabilities_uninstall'     => array( 'inboundnow_gravityforms_uninstall' )
															   ) );
			}
		}

		/**
		 * Setups Software Update API
		 *
		 * @since
		 */
		public function license_setup () {
			/*PREPARE THIS EXTENSION FOR LICESNING*/
			if ( class_exists( 'Inbound_License' ) ) {
				$license = new Inbound_License( INBOUNDNOW_GRAVITYFORMS_FILE, INBOUNDNOW_GRAVITYFORMS_LABEL, INBOUNDNOW_GRAVITYFORMS_SLUG, INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION, INBOUNDNOW_GRAVITYFORMS_REMOTE_ITEM_NAME );
			}
		}
	}

	$gf_leads = new GravityFormsLeads();

}