<?php
/**
 * License handler for Landing Pages
 *
 * This class should simplify the process of adding license information
 * to new LP extensions.
 *
 * @author  Daniel J Griffiths
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LP_EXTENSION_LICENSE' ) ) :

/**
 * LP_EXTENSION_LICENSE Class
 */
class LP_EXTENSION_LICENSE {
	private $item_slug;
	private $item_shortname;
	private $version;
	private $global_license;
	
	/**
	 * Class constructor
	 *
	 * @global  array $edd_options
	 * @param string  $_file
	 * @param string  $_item_slug
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_item_label, $_item_slug ) {
		global $edd_options;

		$this->item_label      = $_item_label;
		$this->item_slug      = $_item_slug;
		$this->global_license = get_option('inboundnow_master_license_key' , '');
		
		// Setup hooks		
		$this->hooks();
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	 
	private function hooks() {
		// Register settings
		add_filter( 'lp_define_global_settings', array( $this, 'settings' ), 2 );

	}

	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $lp_global_settings ) {
		$lp_global_settings['lp-license-keys']['settings'][] = array(

				'id'      => $this->item_slug,
				'slug'      => $this->item_slug,
				'label'    => sprintf( __( '%1$s', 'lp' ), $this->item_label ),
				'description'    => 'Head to http://www.inboundnow.com/ to retrieve your license key for Landing Page Customizer for Landing Pages',
				'type'    => 'license-key',
				'default'    => $this->global_license
			);
		
		//print_r($lp_global_settings);exit;
		return $lp_global_settings;
	}


}

endif; // end class_exists check

