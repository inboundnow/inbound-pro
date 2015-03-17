<?php

/**
 * Extension hooks and filters as well as default settings for core components
 *
 * @package	Calls To Action
 * @subpackage	Extensions
*/
if( !class_exists('CTA_Load_Extensions') ) {

	class CTA_Load_Extensions {
		private static $instance;
		public $definitions;
		public $template_categories;

		public static function instance()
		{
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CTA_Load_Extensions ) )
			{
				self::$instance = new CTA_Load_Extensions;

				/* if frontend load transient data - this data will update on every wp-admin call so you can use an admin call as a cache clear */
				if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX &&  isset($_POST['action']) && $_POST['action'] != 'wp_cta_get_template_meta' )  )
				{
					self::$instance->template_definitions = get_transient('wp_cta_template_definitions');

					if ( self::$instance->template_definitions ) {
						return self::$instance;
					}
				}

				self::$instance->include_template_files();
				self::$instance->add_core_definitions();
				self::$instance->load_definitions();
				self::$instance->read_template_categories();
			}

			return self::$instance;
		}

		function include_template_files()
		{
			/* load templates from wp-content/plugins/calls-to-action/templates/ */

			$core_templates = self::$instance->get_core_templates();

			foreach ($core_templates as $name)
			{
				if ($name != ".svn"){
					include_once(WP_CTA_PATH."templates/$name/config.php");
				}
			}

			/* load templates from uploads folder */
			$uploaded_templates = self::$instance->get_uploaded_templates();

			foreach ($uploaded_templates as $name)
			{
				include_once( WP_CTA_UPLOADS_PATH."$name/config.php");
			}

			/* parse template markup */
			foreach ($wp_cta_data as $key => $data)
			{
				if (isset($data['markup']))
				{
					$parsed = self::$instance->parse_markup($data['markup']);
					$wp_cta_data[$key]['css-template'] = $parsed['css-template'];
					$wp_cta_data[$key]['html-template'] = $parsed['html-template'];
				}
			}

			self::$instance->template_definitions = $wp_cta_data;


		}

		function parse_markup($markup)
		{
			if(strstr($markup,'</style>'))
			{
				$pieces = explode('</style>' , $markup);
				$parsed['css-template'] = strip_tags($pieces[0]);
				$parsed['html-template'] = $pieces[1];
			}
			else
			{
				$parsed['css-template'] = "";
				$parsed['html-template'] = $markup;
			}

			return $parsed;
		}

		function get_core_templates()
		{
			$core_templates = array();
			$template_path = WP_CTA_PATH."templates/" ;
			$results = scandir($template_path);

			//scan through templates directory and pull in name paths
			foreach ($results as $name) {
				if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

				if (is_dir($template_path . '/' . $name)) {
					$core_templates[] = $name;
				}
			}

			return $core_templates;
		}

		function get_uploaded_templates()
		{
			//scan through templates directory and pull in name paths
			$uploaded_templates = array();

			if (!is_dir( WP_CTA_UPLOADS_PATH ))
			{
				wp_mkdir_p( WP_CTA_UPLOADS_PATH );
			}

			$templates = scandir( WP_CTA_UPLOADS_PATH );


			//scan through templates directory and pull in name paths
			foreach ($templates as $name) {
				if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

				if ( is_dir( WP_CTA_UPLOADS_PATH . '/' . $name ) ) {
					$uploaded_templates[] = $name;
				}
			}

			return $uploaded_templates;
		}

		/* collects & loads extension array data */
		function load_definitions()
		{
			$wp_cta_data = self::$instance->template_definitions;
			self::$instance->definitions = apply_filters( 'wp_cta_extension_data' , $wp_cta_data);
			set_transient('wp_cta_extension_definitions' , $wp_cta_data ,  60*60*24 );
		}

		/* filters to add in core definitions to the calls to action extension definitions array */
		function add_core_definitions()
		{
			add_filter('save_post' , array( $this , 'store_template_data_as_transient') , 1  );
			add_filter('wp_cta_extension_data' , array( $this , 'add_default_metaboxes') , 1  );
			add_filter('wp_cta_extension_data' , array( $this , 'add_width_and_height_to_templates') , 1  );
			add_filter('wp_cta_extension_data' , array( $this , 'add_default_advanced_settings') , 1  );
			add_filter('wp_cta_extension_data' , array( $this , 'add_default_page_settings') , 1  );

		}

		function store_template_data_as_transient( $post_id )
		{
			global $post;

			if (!isset($post)) {
				return;
			}
			
			if ($post->post_type=='revision' ||  'trash' == get_post_status( $post_id )) {
				return;
			}

			if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )||( isset($_POST['post_type']) && $_POST['post_type']=='revision' )) {
				return;
			}

			if ($post->post_type=='wp-call-to-action') {
				set_transient('wp_cta_template_definitions' , self::$instance->template_definitions  , 60*60*24 );
			}
		}

		/* adds default metabox to all calls to action */
		function add_default_metaboxes($wp_cta_data)
		{
			/* this is a special key that targets CTA metaboxes */
			$parent_key = 'wp-cta';

			$wp_cta_data[$parent_key]['settings'][] = array(
						'data_type'  => 'metabox',
						'id'  => 'selected-template',
						'label' => __( 'Select Template' , 'cta' ),
						'description' => __( 'This option provides a placeholder for the selected template data.' , 'cta' ),
						'type'  => 'radio', // this is not honored. Template selection setting is handled uniquely by core.
						'default'  => 'blank-template',
						'options' => null // this is not honored. Template selection setting is handled uniquely by core.
					);

			//IMPORT ALL EXTERNAL DATA

			return $wp_cta_data;

		}

		/* adds default settings to Advanced Settings metabox */
		function add_default_advanced_settings($wp_cta_data) {
			/* this is a special key that targets CTA metaboxes */
			$parent_key = 'wp-cta';

			$wp_cta_data[$parent_key]['settings']['advanced-core-options-header'] =   array(
				'datatype' => 'setting',
				'region' => 'advanced',
				'description'  => __( '<h3>CTA Settings</h3>' , 'cta' ),
				'id'    => 'advanced-core-options-header',
				'type'  => 'html-block'
			);

			$wp_cta_data[$parent_key]['settings']['link-open-option'] = array(
					'data_type'  => 'metabox',
					'region' => 'advanced',
					'label' => __( 'Open Links' , 'cta' ),
					'description' => __( 'How do you want links on the call to action to work?' , 'cta' ),
					'id'  => 'link-open-option', // called in template's index.php file with lp_get_value($post, $key, 'checkbox-id-here');
					'type'  => 'dropdown',
					'default'  => 'this_window',
					'options' => array('this_window' => __('Open Links in Same Window (default)' , 'cta' ) ,'new_tab'=> __( 'Open Links in New Tab' , 'cta' )),
					'context'  => 'normal'
					);

			//IMPORT ALL EXTERNAL DATA
			return $wp_cta_data;
		}


		/* adds_width and height settings to templates */
		function add_width_and_height_to_templates($wp_cta_data){
			foreach ($wp_cta_data as $key => $data )
			{
				if (!isset($data['info']['data_type']) || $data['info']['data_type']!='template'){
					continue;
				}

				$width =  array(
					'label' => __( 'CTA Width' , 'cta' ),
					'description' => __( 'Enter the Width of the CTA in pixels. Example: 100% or 300px' , 'cta' ) ,
					'id'  => 'wp_cta_width',
					'type'  => 'width-height',
					'default'  => '100%',
					'class' => 'cta-width',
					'context'  => 'priority',
					'global' => true
				);

				$height = array(
					'label' => __( 'CTA Height' , 'cta' ),
					'description' => __( 'Enter the Height of the CTA in pixels. Example: auto or 300px' , 'cta' ),
					'id'  => 'wp_cta_height',
					'type'  => 'width-height',
					'default'  => 'auto',
					'class' => 'cta-height',
					'context'  => 'priority',
					'global' => true
				);

				array_unshift($wp_cta_data[$key]['settings'] , $width, $height);

			}

			return $wp_cta_data;
		}

		/* adds default settings to Advanced Settings metabox */
		function add_default_page_settings($wp_cta_data)
		{
			/* this is a special key that targets CTA metaboxes */
			$parent_key = 'wp-cta-controller';

			$wp_cta_data[$parent_key]['settings'] = array(
				array(
					'data_type' => 'setting',
					'region' => 'cta-placement-controls',
					'label' => __( 'Placement on Page' , 'cta' ),
					'description' => __( 'Where would you like to insert the CTA on this page?' , 'cta' ),
					'id'  => 'cta_content_placement',
					'type'  => 'dropdown',
					'default'  => 'off',
					'options' => array(
										'below' => __( 'Below Content' , 'cta' ),
										'middle' => __( 'Middle of Content' , 'cta' ),
										'above'=> __( 'Above Content' , 'cta' ),
										'widget_1' => __( 'Use Dynamic Sidebar Widget' , 'cta' ),
										'popup' => __( 'Popup' , 'cta' )
									  ),
					'context'  => 'normal',
					'class' => 'cta-per-page-option'
					),
				array(
					'data_type' => 'setting',
					'region' => 'cta-placement-controls',
					'label' => __( 'Sidebar Message' , 'cta' ),
					'description' => "<div style='margin-top:10px; margin-left:10px;'><p>". __( 'This option will place the selected CTA templates into the dynamic sidebar widget on this page. Make sure you have added the dynamic Call to Action widget to the sidebar of this page for this option to work.</p><p>To add the dynamic sidebar widget to this page, go into appearance > widgets and add the widget to the sidebar of your choice' , 'cta' ) ."</p></div>",
					'id'  => 'sidebar_message',
					'type'  => 'html-block',
					'default'  => '',
					'context'  => 'normal',
					'class' => '',
					'reveal_on' => 'widget_1'
					),
				array(
					'data_type' => 'setting',
					'region' => 'cta-placement-controls',
					'label' => __( 'Below Message' , 'cta' ),
					'description' => "<div style='margin-top:10px; margin-left:10px;'><p>". __( 'Your Call to Action will be inserted into the bottom of the page/post.' , 'cta' ) ."</p></div>",
					'id'  => 'below_message',
					'type'  => 'html-block',
					'default'  => '',
					'context'  => 'normal',
					'class' => '',
					'reveal_on' => 'below'
					),
				array(
					'data_type' => 'setting',
					'region' => 'cta-placement-controls',
					'label' => __( 'Above Message' , 'cta' ),
					'description' => "<div style='margin-top:10px; margin-left:10px;'><p>". __( 'Your Call to Action will be inserted into the top of the page/post.' , 'cta' ) ."</p></div>",
					'id'  => 'above_message',
					'type'  => 'html-block',
					'default'  => '',
					'context'  => 'normal',
					'class' => '',
					'reveal_on' => 'above'
					),
				array(
					'data_type' => 'setting',
					'region' => 'cta-placement-controls',
					'label' => __( 'Middle Message' , 'cta' ),
					'description' => "<div style='margin-top:10px; margin-left:10px;'><p>". __( 'Your Call to Action will be inserted into the middle of the page/post\'s content.' , 'cta' ) ."</p></div>",
					'id'  => 'above_message',
					'type'  => 'html-block',
					'default'  => '',
					'context'  => 'normal',
					'class' => '',
					'reveal_on' => 'middle'
					),
			);


			//IMPORT ALL EXTERNAL DATA
			return $wp_cta_data;
		}



		function read_template_categories()
		{

			$template_cats = array();

			if ( !isset(self::$instance->definitions ) ) {
				return;
			}

			//print_r($extension_data);
			foreach (self::$instance->definitions as $key=>$val)
			{

				if (strstr($key,'wp-cta') || !isset($val['info']['category']))
					continue;

				/* allot for older lp_data model */
				if (isset($val['category']))
				{
					$cats = $val['category'];
				}
				else
				{
					if (isset($val['info']['category']))
					{
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

			self::$instance->template_categories = $template_cats;
		}
	}


	function CTA_Load_Extensions()
	{
		return CTA_Load_Extensions::instance();
	}
}

