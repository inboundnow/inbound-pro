<?php
/*
 * @package   GravityFormsLeads\Addon
 * @copyright 2014 gravity+
 * @license   GPL-2.0+
 * @since     2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GravityFormsLeads_Addon
 *
 * Adds field mapping UI and creates lead when form is submitted
 *
 * @since 2.0.0
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GravityFormsLeads_Addon extends GFFeedAddOn {

	/**
	 * @var string Version number of the Add-On
	 */
	protected $_version;

	/**
	 * @var string Gravity Forms minimum version requirement
	 */
	protected $_min_gravityforms_version;

	/**
	 * @var string URL-friendly identifier used for form settings, add-on settings, text domain localization...
	 */
	protected $_slug;

	/**
	 * @var string Relative path to the plugin from the plugins folder
	 */
	protected $_path;

	/**
	 * @var string Full path to the plugin. Example: __FILE__
	 */
	protected $_full_path;

	/**
	 * @var string URL to the App website.
	 */
	protected $_url;

	/**
	 * @var string Title of the plugin to be used on the settings page, form settings and plugins page.
	 */
	protected $_title;

	/**
	 * @var string Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
	 */
	protected $_short_title;

	/**
	 * @var array Members plugin integration. List of capabilities to add to roles.
	 */
	protected $_capabilities = array();

	// ------------ Permissions -----------
	/**
	 * @var string|array A string or an array of capabilities or roles that have access to the form settings
	 */
	protected $_capabilities_form_settings = array();

	/**
	 * @var string|array A string or an array of capabilities or roles that can uninstall the plugin
	 */
	protected $_capabilities_uninstall = array();

	/**
	 * Information to send to WordPress Leads
	 *
	 * @since
	 *
	 * @var array
	 */
	private $map = array();

	function __construct ( $args ) {
		$this->_version                    = $args[ 'version' ];
		$this->_slug                       = $args[ 'plugin_slug' ];
		$this->_min_gravityforms_version   = $args[ 'min_gf_version' ];
		$this->_path                       = $args[ 'path' ];
		$this->_full_path                  = $args[ 'full_path' ];
		$this->_url                        = $args[ 'url' ];
		$this->_title                      = $args[ 'title' ];
		$this->_short_title                = $args[ 'short_title' ];
		$this->_capabilities               = $args[ 'capabilities' ];
		$this->_capabilities_form_settings = $args[ 'capabilities_form_settings' ];
		$this->_capabilities_uninstall     = $args[ 'capabilities_uninstall' ];

		parent::__construct();
	}

	public function feed_settings_fields () {

		$feed_field_name = array(
			'label'   => __( 'Name', 'inbound-gravityforms' ),
			'type'    => 'text',
			'name'    => 'feedName',
			'tooltip' => __( 'Name for this feed', 'inbound-gravityforms' ),
			'class'   => 'medium'
		);

		$feed_field_lead_fields = array(
			'name'      => 'gravityformsleads',
			'label'     => __( 'Lead Fields', 'inbound-gravityforms' ),
			'type'      => 'field_map',
			'field_map' => $this->get_lead_fields()
		);

		$feed_field_lead_list = array(
			'label'   => __( 'Sort into list', 'inbound-gravityforms' ),
			'type'    => 'select',
			'name'    => 'gravityformsleads_list_id',
			'tooltip' => __( 'Sort into list', 'inbound-gravityforms' ),
			'choices' => $this->get_lead_lists()
		);

		return array(
			array(
				'title'  => 'WordPress Leads Feed Settings',
				'fields' => array(
					$feed_field_name,
					$feed_field_lead_fields,
					$feed_field_lead_list
				)
			)
		);
	}

	protected function field_map_title () {
		return __( 'Lead Field', 'inbound-gravityforms' );
	}

	protected function feed_list_columns () {
		return array(
			'feedName' => __( 'Name', 'inbound-gravity-forms' )
		);
	}

	private function get_lead_fields () {
		$leads_fields = array();

		$fields = Leads_Field_Map::build_map_array();
		unset( $fields[''] );

		foreach ( $fields as $key => $label ) {
			$leads_fields[ ] = array( 'name'     => $key,
									  'label'    => $label,
									  'required' => in_array( $key, array( 'wpleads_email_address', 'wpleads_first_name' ) ) ? true : false
			);
		}

		return $leads_fields;
	}

	private function get_lead_lists () {
		$lead_lists = array();

		$lead_lists[ ] = array( 'label' => __( 'None', 'inbound-gravityforms' ),
								'value' => '' );

		$lists = wpleads_get_lead_lists_as_array();
		foreach ( $lists as $id => $label ) {
			$lead_lists[ ] = array( 'label' => $label,
									'value' => $id );
		}

		return $lead_lists;
	}

	protected function get_mapped_field_value ( $setting_name, $form, $entry, $settings = false ) {

		$field_id = $this->get_setting( $setting_name, '', $settings );

		$value = rgar( $entry, $field_id );

		return $value;
	}

	public function process_feed ( $feed, $entry, $form ) {

		$this->build_map( $feed, $entry, $form );

		if ( empty( $this->map[ 'wpleads_first_name' ] ) ) {
					return;
				}

		if ( empty( $this->map[ 'wpleads_email_address' ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'wp_lead_uid' ] ) ) {
			$this->map[ 'wp_lead_uid' ] = $_COOKIE[ 'wp_lead_uid' ];
		}
		else {
			$this->map[ 'wp_lead_uid' ] = md5( $this->map[ 'wpleads_email_address' ] );
			setcookie( 'wp_lead_uid', $this->map[ 'wp_lead_uid' ], time() + ( 20 * 365 * 24 * 60 * 60 ), '/' );
		}

		/* account for company name */
		if ( empty( $this->map[ 'wpleads_company_name' ] ) ) {
			$this->map[ 'wpleads_company_name' ] = 'Not Provided';
		}

		$lead_id = inbound_store_lead( $this->map );
	
		if ( $lead_id ) {
			$list_id = $this->get_setting( 'gravityformsleads_list_id', '', $feed['meta'] );
			if ( ! empty( $list_id ) ) {
				wpleads_add_lead_to_list( $list_id, $lead_id );
			}
		}
		
	}

	function build_map ( $feed, $entry, $form ) {
		$lead_fields = $this->get_field_map_fields( $feed, 'gravityformsleads' );

		foreach ( $lead_fields as $name => $value ) {
			if ( 'list_id' !== $name ) {
				$this->map[ $name ] = $this->get_mapped_field_value( "gravityformsleads_{$name}", $form, $entry, $feed[ 'meta' ] );
				if ( empty( $this->map[ $name ] ) ) {
					unset( $this->map[ $name ] );
				}
			}
		}
	}

}