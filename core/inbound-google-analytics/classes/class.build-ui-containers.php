<?php


class Inbound_Analytics_UI_Containers {

	static $templates;
	static $ga_settings;
	static $ga_data;

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

		/* load settings */
		self::$ga_settings = get_option('inbound_ga' , false );

		if (!isset(self::$ga_settings['linked_profile']) ||  !self::$ga_settings['linked_profile']) {
			return;
		}

		/* disable legacy inbound statistics metaboxes */
		remove_action('init' , 'inbound_load_legacy_statistics' , 10 );

		/* Setup Automatic Updating & Licensing */
		add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'load_globals') , 99 );

		/* Setup Automatic Updating & Licensing */
		add_action( 'admin_init', array( __CLASS__ , 'load_templates') , 99 );

		/* Load Google Charting API & Inbound Analytics Styling CSS*/
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'load_scripts') );

		/* Add sidebar metabox to content administration area */
		add_action( 'add_meta_boxes' , array( __CLASS__ , 'load_metaboxes' ) );
	}


	public static function load_globals() {
		global $post;

		if (!isset($post)) {
			return;
		}

		self::$ga_settings = get_option('inbound_ga' , false);

	}

	/**
	*	Imports analytic templates & sets data into static variable
	*/
	public static function load_templates() {
		self::$templates = apply_filters( 'inbound_analytics_templates' , array( __CLASS__ , 'load_metaboxes') );
	}

	/**
	*	Removes certain inbound core elements from UI due to replacement
	*/
	public static function cleanup_operations() {


	}

	/**
	* Loads Google charting scripts
	*/
	public static function load_scripts() {
		
		global $post;
		
		
		if (!isset($post) || strstr( $post->post_type , 'inbound-' ) ) {
			return;
		}
		
		wp_register_script( 'jsapi' , 'https://www.google.com/jsapi');
		wp_enqueue_script( 'jsapi' );

		wp_register_script( 'spin-js' , INBOUND_GA_URLPATH.'assets/libraries/spinjs/spin.js');
		wp_enqueue_script( 'spin-js' );

		wp_register_script( 'spin-jquery' , INBOUND_GA_URLPATH.'assets/libraries/spinjs/jquery.spin.js');
		wp_enqueue_script( 'spin-jquery' );

		wp_register_script( 'bootstrap-js' , INBOUND_GA_URLPATH.'assets/libraries/BootStrap/bootstrap.min.js');
		wp_enqueue_script( 'bootstrap-js' );

		/* disables modal links
		wp_register_script( 'ia-content-loader' , INBOUND_GA_URLPATH.'assets/js/content.loader.js');
		wp_enqueue_script( 'ia-content-loader' );
		*/

		wp_register_style( 'bootstrap-css' , INBOUND_GA_URLPATH. 'assets/libraries/BootStrap/bootstrap.css');
		wp_enqueue_style( 'bootstrap-css' );

		wp_register_style( 'inbound-analytics-css' , INBOUND_GA_URLPATH. 'assets/css/style.css');
		wp_enqueue_style( 'inbound-analytics-css' );

	}

	/**
	*	Adds sidebar metabox to all post types
	*/
	public static function load_metaboxes() {
		$screen = get_current_screen();

		if (!isset($screen) || $screen->action == 'new' || $screen->action == 'add') {
			return;
		}

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
		$exclude[] = 'acf-field-group';
		$exclude[] = 'email';
		$exclude[] = 'inbound-email';

		/* Add metabox to post types */
		foreach ($post_types as $post_type ) {

			if (!in_array($post_type,$exclude))
			{
				add_meta_box( 'inbound-analytics', __( 'Inbound Analytics' , 'inbound-pro' ) , array( __CLASS__ , 'display_quick_view' ) , $post_type, 'side', 'high');
			}
		}
	}

	/**
	*	Displays Inbound Analytics sidebar (quick view)
	*/
	public static function display_quick_view() {
		/* sets the default quick view template */
		$template_class_name = apply_filters('inbound_ananlytics_quick_view' , 'Analytics_Template_Content_Quick_View' );

		$template_class = new $template_class_name;
		$template_class->load_template( array() );

		self::prepare_modal_container();
	}

	public static function prepare_modal_container() {
		?>

		<div class="modal" id='ia-modal-container'>
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					</div>
					<div class="modal-body">
						<iframe class='ia-frame'></iframe>
					</div>
				</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
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


new Inbound_Analytics_UI_Containers();