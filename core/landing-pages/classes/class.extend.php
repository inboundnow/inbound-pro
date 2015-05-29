<?php


class Landing_Pages_Load_Extensions {
	
	/**
	*  Initializes Landing_Pages_Load_Extensions
	*/
	public function __construct() {
		
		/* Load core landing page config.php files */
		self::load_core_template_configurations();
		
		/* Load uploaded landing page config.php files */
		self::load_uploaded_template_configurations();
		
		/* load hooks & filters  */
		self::load_hooks();
	}

	/**
	*  Loads hooks and filiters
	*/
	public static function load_hooks() {
		
		/* Adds core metabox settings to extension data array */
		add_filter( 'lp_extension_data' , array( __CLASS__ , 'add_core_setting_data' ) , 1 , 1);
		
		/* Modifies legacy template data key names for old, un-updated legacy templates */
		add_filter( 'lp_extension_data', array( __CLASS__ , 'add_legacy_data_support') , 10 , 1 );
		
		/* Add license key inputs to all uploaded templates */
		add_filter( 'lp_define_global_settings' , array( __CLASS__ , 'prepare_license_keys' ), 99, 1);
	}
	
	/**
	*  Adds core metaboxes setting data using lp_extension_data filter
	*/
	public static function add_core_setting_data( $data ) {
		
		if ( !is_admin() ) {
			return;
		}
		
		$data[ 'lp' ]['settings'] = 	array(
			array(
				'id'  => 'selected-template',
				'label' => __( 'Select Template' , 'landing-pages'),
				'description' =>  __( "This option provides a placeholder for the selected template data." , 'landing-pages'),
				'type'  => 'radio', // this is not honored. Template selection setting is handled uniquely by core.
				'default'  => 'default',
				'options' => null // this is not honored. Template selection setting is handled uniquely by core.
			),
			array(
				'id'  => 'main-headline',
				'label' => __('Set Main Headline' , 'landing-pages'),
				'description' => __( "Set Main Headline" , 'landing-pages'),
				'type'  => 'text', // this is not honored. Main Headline Input setting is handled uniquely by core.
				'default'  => '',
				'options' => null
			),
		);
		
		return $data;
	}
	
	/**
	*  Looks for occurances of 'options' in template & extension data arrays and replaces key with 'settings'
	*/
	public static function add_legacy_data_support( $data ) {
		if ( !is_admin() ) {
			return;
		}
		
		foreach ($data as $parent_key => $subarray)
		{
			if (is_array($subarray))
			{
				foreach ($subarray as $k=>$subsubarray)
				{
					/* change 'options' key to 'settings' */
					if ($k=='options')
						$data[$parent_key]['settings'] = $subsubarray;

					if ($k=='category')
						$data[$parent_key]['info']['category'] = $subsubarray;

					if ($k=='version')
						$data[$parent_key]['info']['version'] = $subsubarray;

					if ($k=='label')
						$data[$parent_key]['info']['label'] = $subsubarray;

					if ($k=='description')
						$data[$parent_key]['info']['description'] = $subsubarray;
				}
			}
		}

		return $data;
	}
	
	/**
	*  Adds licensing & automatic updates to uploaded templates
	*  
	*  @param ARRAY $global_settings contains all global setting data
	*  
	*  @retuns ARRAY $global_settings contains modified global setting data
	*/
	public static function prepare_license_keys( $global_settings ) {
		
		if ( !is_admin() ) {
			return;
		}
		
		$lp_data = self::get_extended_data();

		$global_settings['lp-license-keys']['settings'][] = 	array(
				'id'  => 'template-license-keys-header',
				'description' => __( "Head to http://www.inboundnow.com/ to retrieve your license key for this template." , 'landing-pages') ,
				'type'  => 'header',
				'default' => '<h3 class="lp_global_settings_header">' . __('Template Licensing' , 'landing-pages') .'</h3>'
		);

		/* get master license key */
		$inboundnow_master_key = get_option('inboundnow_master_license_key' , '');

		/* Loop through all setting data and add licensing for uploaded templates only */
		foreach ($lp_data as $key=>$data)
		{

			$array_core_templates = array('simple-solid-lite','countdown-lander','default','demo','dropcap','half-and-half','simple-two-column','super-slick','svtle','tubelar','rsvp-envelope', 'three-column-lander');

			if ($key == 'lp' || substr($key,0,4) == 'ext-' ) {
				continue;
			}
			
			if (isset($data['info']['data_type']) && $data['info']['data_type']=='metabox') {
				continue;
			}
			
			if (in_array($key,$array_core_templates)) {
				continue;
			}

			$template_name = $lp_data[$key]['info']['label'];
			$global_settings['lp-license-keys']['settings'][$key] = 	array(
				'id'  => $key,
				'label' => $template_name,
				'slug' => $key,
				'description' => __( "Head to http://www.inboundnow.com/ to retrieve your license key for this template." , 'landing-pages') ,
				'type'  => 'license-key'
			);
		}

		return $global_settings;
	}
	
	/**
	* Loads core template config.php files
	*
	* @returns ARRAY contains template setting data
	*/
	public static function load_core_template_configurations() {
		
		if ( !is_admin() ) {
			return;
		}
		
		$template_ids = self::get_core_template_ids();

		//Now load all config.php files with their custom meta data
		if (count($template_ids)>0)
		{
			foreach ($template_ids as $name)
			{
				if ($name != ".svn" && $name != ".git"){
				include_once( LANDINGPAGES_PATH . "/templates/$name/config.php");
				}
			}
		}
		
		
		/* Store all template config files in global */
		$GLOBALS['lp_data'] = $lp_data;
		
		return $lp_data;
	}
	
	/**
	* Loads uploaded template config.php files
	*
	*/
	public static function load_uploaded_template_configurations() {
		global $lp_data;
		
		$template_ids = self::get_uploaded_template_ids();

		/* loop through template ids and include their config file */
		foreach ($template_ids as $name)
		{
			$match = FALSE;
			if (strpos($name, 'tmp') !== FALSE || strpos($name, 'template-generator') !== FALSE) {
				$match = TRUE;
			}
			if ($name != ".svn" && $name != ".git" && $name != 'template-generator' && $match === FALSE){
				if (file_exists( LANDINGPAGES_UPLOADS_PATH . "$name/config.php")) {
					include_once( LANDINGPAGES_UPLOADS_PATH . "$name/config.php");					
				}
			}
		}
		
		
		return $lp_data;		

	}
	
	/**
	* Gets array of uploaded template paths
	*
	* @returns ARRAY $template_ids array of uploaded template ids
	*/
	public static function get_uploaded_template_ids()
	{
		$template_ids = array();

		if (!is_dir( LANDINGPAGES_UPLOADS_PATH )) {
			wp_mkdir_p( LANDINGPAGES_UPLOADS_PATH );
		}

		$results = scandir( LANDINGPAGES_UPLOADS_PATH );

		foreach ($results as $name) {
			if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

			if (is_dir( LANDINGPAGES_UPLOADS_PATH . '/' . $name)) {
				$template_ids[] = $name;
			}
		}

		return $template_ids;
	}
	
	/**
	* Gets array of uploaded template paths
	*
	* @returns ARRAY $template_ids array of uploaded template ids
	*/
	public static function get_core_template_ids()
	{
		$template_ids = array();
		
		$template_path = LANDINGPAGES_PATH."/templates/" ;
		$results = scandir($template_path);

		//scan through templates directory and pull in name paths
		foreach ($results as $name) {
			if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

			if (is_dir($template_path . '/' . $name)) {
				$template_ids[] = $name;
			}
		}

		return $template_ids;
	}
	
	/**
	*  Get's array of template categories from loaded templates
	*  
	*  @returns ARRAY $template_cats array if template categories
	*/	
	public static function get_template_categories()
	{
		$template_settings = self::get_extended_data();	
		
		foreach ($template_settings as $key=>$val)
		{
			if ( $key=='lp' || substr($key,0,4)=='ext-' || isset($val['info']['data_type']) && $val['info']['data_type']=='metabox' ) {
				continue;
			}
			
			/* account for legacy data models */
			if (isset($val['category'])) {
				$cats = $val['category'];
			} else	{
				if (isset($val['info']['category'])) {
					$cats = $val['info']['category'];
				}
			}

			$cats = explode(',',$cats);

			foreach ($cats as $cat_value)
			{
				$cat_value = trim($cat_value);
				$name = str_replace(array('-','_'),' ',$cat_value);
				$name = ucwords($name);

				if (!isset($template_cats[$cat_value]))
				{
					$template_cats[$cat_value]['count'] = 1;
				}
				else
				{
					$template_cats[$cat_value]['count']++;
				}

				$template_cats[$cat_value]['value'] = $cat_value;
				$template_cats[$cat_value]['label'] = "$name";
			}
		}

		return $template_cats;
	}
	
	/**
	 *  Get's template and extension setting data
	 *  
	 *  @retuns ARRAY of template & extension data
	 */
	public static function get_extended_data() {
		global $lp_data;
		
		$lp_data = apply_filters( 'lp_extension_data' , $lp_data);
		
		return $lp_data;
	}
	

}

new Landing_Pages_Load_Extensions;

/* Get data array of template settings */
function lp_get_extension_data() {
	return Landing_Pages_Load_Extensions::get_extended_data();	
}





