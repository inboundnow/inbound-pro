<?php

if (!class_exists('Inbound_Analytics_UI_Containers')) {


class Inbound_Analytics_UI_Containers {
	
	static $templates;
	
	/**
	* Initalize Inbound_Analytics_UI_Containers Class
	*/
	public function __construct() {		
		self::load_hooks();		
	}
		
	/**
	* Load Hooks & Filters 
	*/
	public static function load_hooks() {

		/* Setup Automatic Updating & Licensing */
		add_action( 'admin_init', array( __CLASS__ , 'load_templates') , 99 );
		
		/* Perform cleanup operations related to free core */
		add_action( 'admin_init' , array ( __CLASS__ , 'cleanup_operations' ) );
		
		/* Load Google Charting API & Inbound Analytics Styling CSS*/
		add_action('admin_enqueue_scripts' , array( __CLASS__ , 'load_scripts') );
		
		/* Add sidebar metabox to content administration area */
		add_action( 'add_meta_boxes' , array( __CLASS__ , 'load_metaboxes' ) );
	}

	/**
	*	Imports analytic templates & sets data into static variable
	*/
	public static function load_templates() {
		self::$templates = apply_filters( 'inbound_analytics_templates' , array( __CLASS__ , 'load_metaboxes') );
	}
	
	/**
	*  Removes certain inbound core elements from UI due to replacement
	*/
	public static function cleanup_operations() {
		
		/* Removes free core analytics metabox */
		remove_action('add_meta_boxes', 'lp_add_global_meta_box' , 10 );
	}
	
	/**
	* Loads Google charting scripts
	*/
	public static function load_scripts() {
		
		wp_register_script( 'jsapi' , 'https://www.google.com/jsapi');
		wp_enqueue_script( 'jsapi' );		
		
		wp_register_script( 'bootstrap-js' , INBOUND_ANALYTICS_URLPATH .'includes/BootStrap/bootstrap.min.js');
		wp_enqueue_script( 'bootstrap-js' );
		
		wp_register_script( 'bootstrap-loader' , INBOUND_ANALYTICS_URLPATH .'includes/BootStrap/bootstrap.loader.js');
		wp_enqueue_script( 'bootstrap-loader' );
	
		wp_register_style( 'bootstrap-css' , INBOUND_ANALYTICS_URLPATH . 'includes/BootStrap/bootstrap.min.css');
		wp_enqueue_style( 'bootstrap-css' );

		wp_register_style( 'inbound-analytics-css' , INBOUND_ANALYTICS_URLPATH . 'css/style.css');
		wp_enqueue_style( 'inbound-analytics-css' );

	}
	
	/**
	*	Adds sidebar metabox to all post types
	*/
	public static function load_metaboxes() {
	
		/* Get post types to add metabox to */
		$post_types= get_post_types('','names');

		/* Clean post types of known non-applicants */
		$exclude[] = 'attachment';
		$exclude[] = 'revisions';
		$exclude[] = 'nav_menu_item';
		$exclude[] = 'wp-lead';
		$exclude[] = 'automation';
		$exclude[] = 'rule';
		$exclude[] = 'list';
		$exclude[] = 'wp-call-to-action';
		$exclude[] = 'tracking-event';
		$exclude[] = 'inbound-forms';
		$exclude[] = 'email-template';
		$exclude[] = 'inbound-log';
		$exclude[] = 'landing-page';

		/* Add metabox to post types */
		foreach ($post_types as $post_type ) {
		
			if (!in_array($post_type,$exclude))
			{
				add_meta_box( 'inbound-analytics', __( 'Inbound Analytics' , 'inbound-pro' ) , array( __CLASS__ , 'display_quick_view' ) , $post_type, 'side', 'high');
			}
		}
	}
	
	/**
	*  Displays Inbound Analytics sidebar (quick view)
	*/
	public static function display_quick_view() {
		/* sets the default quick view template */
		$template_class_name = apply_filters('inbound_ananlytics_quick_view' , 'Analytics_Teamplte_Content_Quick_View' );
		
		$template_class = new $template_class_name;
		$template_class->load_template( array() );
		
		self::prepare_modal_container();
	}
	
	public static function prepare_modal_container() {
		?>
		<div class="modal fade" id="ia-modal-container" >
			<div class="modal-dialog">
			hello
			</div>
		</div>
		<?php
	}

	/**
	* Helper log function for debugging
	*
	* @since 1.2.2
	*/
	static function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

}


$GLOBALS['Inbound_Analytics_UI_Containers'] = new Inbound_Analytics_UI_Containers();

}