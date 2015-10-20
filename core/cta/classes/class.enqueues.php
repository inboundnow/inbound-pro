<?php

/**
*  Scripts and stylesheet that have not been segmented into classes or have no other home are enqueued here.
*/
class CTA_Enqueues {

	static $scripts_queue; /* Holds 3rd party scripts queue

	/**
	*  Initializes class
	*/
	public function __construct() {
		self::load_hooks();
	}

	/**
	*  Loads hooks and filters
	*/
	public static function load_hooks() {
		add_action('wp_enqueue_scripts', array( __CLASS__ , 'frontend_enqueues' ) );
		add_action('admin_enqueue_scripts', array( __CLASS__ , 'backend_enqueues' ) );
	}

	/**
	 *  Load Frontend Enqueues
	 */
	public static function frontend_enqueues() {
		global $post , $wp_query;

		if (!isset($post)) {
			return;
		}

		wp_enqueue_script('jquery');

		self::frontend_cta_enqueue();
		self::frontend_non_cta_enqueue();
	}

	/**
	 *  Loads frontend enqueues when call to action post type is being loaded
	 */
	public static function frontend_cta_enqueue() {
		global $post;

		if ( isset($post) && $post->post_type == 'wp-call-to-action' ) {
			return;
		}
	}

	/**
	 *  Enqueues front end scripts on all post types that are not calls to actions
	 */
	public static function frontend_non_cta_enqueue() {
		global $post;
		if ( $post->post_type != 'wp-call-to-action' ) {
			return;
		}

		/* Loads alignment definitions */
		wp_enqueue_style('cta-css', WP_CTA_URLPATH . 'css/cta-load.css');

		/* Add edit cta pills to rendered calls to action */
		if ( current_user_can( 'manage_options' )) {
			wp_enqueue_script('frontend-cta-admin', WP_CTA_URLPATH . 'js/admin/frontend-admin-cta.js');
			wp_localize_script( 'frontend-cta-admin', 'ctafrontend', array('ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		}

	}

	/**
	 *  Load backened enqueues
	 */
	public static function backend_enqueues( $hook ) {
		global $post;

		self::dequeue_3rd_party_scripts();

		/* Enqueues general & unorganized admin stylings */
		wp_enqueue_style('wp-cta-admin-css', WP_CTA_URLPATH . 'css/admin-style.css');

		/* Enqueue select2 support */
		wp_enqueue_script('select2', WP_CTA_URLPATH . 'js/select2.min.js');

		/* Load enqueues directly related to wp-call-to-action post type */
		self::backend_cta_enqueues( $hook );
		self::frontend_editor_enqueues( $hook );
		self::reenqueue_3rd_party_scripts( $hook );
	}


	/**
	 *  Enqueues scripts and styles related to wp-call-to-action post type and cta settings pages
	 */
	public static function backend_cta_enqueues( $hook ) {
		global $post;

		$CTAExtensions = CTA_Load_Extensions();
		$screen = get_current_screen();

		if ( ( isset($screen) && $screen->post_type != 'wp-call-to-action' ) ){
			return;
		}

		/* Enqueue dependancies */
		wp_enqueue_script(array('jquery', 'jqueryui', 'editor', 'thickbox', 'media-upload'));

		/* Enqueue jpicker for color selectors  */
		wp_enqueue_script('jpicker', WP_CTA_URLPATH . 'js/libraries/jpicker/jpicker-1.1.6.min.js');
		wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jpicker/images/' ));
		wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker-1.1.6.min.css');

		/* Enqueue qtip support */
		wp_dequeue_script('jquery-qtip');
		wp_enqueue_script('jquery-qtip', WP_CTA_URLPATH . 'js/libraries/jquery-qtip/jquery.qtip.min.js');
		wp_enqueue_script('load-qtip', WP_CTA_URLPATH . 'js/libraries/jquery-qtip/load.qtip.js', array('jquery-qtip'));
		wp_enqueue_style('qtip-css', WP_CTA_URLPATH . 'css/jquery.qtip.min.css');

		/* Enqueue datepicker support */
		wp_enqueue_script('jquery-datepicker', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.min.js');
		wp_enqueue_script('jquery-datepicker-functions', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/picker_functions.js');
		wp_enqueue_script('jquery-datepicker-base', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/base.js');
		wp_enqueue_script('jquery-datepicker-datepair', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/datepair.js');
		wp_localize_script( 'jquery-datepicker', 'jquery_datepicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jquery-datepicker/' ));

		/* Enqueue timepicker support */
		wp_enqueue_style('jquery-timepicker-css', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/jquery.timepicker.css');
		wp_enqueue_style('jquery-datepicker-base.css', WP_CTA_URLPATH . 'js/libraries/jquery-datepicker/lib/base.css');

		/* Enqueue CSS rules for wp-call-to-action post type */
		wp_enqueue_style('wp-cta-only-cpt-admin-css', WP_CTA_URLPATH . 'css/admin-wp-cta-cpt-only-style.css');

		/* Enqueues support for clear stat buttons */
		wp_enqueue_script( 'wp-cta-admin-clear-stats-ajax-request', WP_CTA_URLPATH . 'js/ajax.clearstats.js', array( 'jquery' ) );
		wp_localize_script( 'wp-cta-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'wp_call_to_action_clear_nonce' => wp_create_nonce('wp-call-to-action-clear-nonce') ) );

		/*  Enqueue supporting js for Global Settings page */
		if (isset($_GET['page']) && $_GET['page'] === 'wp_cta_global_settings') {
			wp_enqueue_script('cta-settings-js', WP_CTA_URLPATH . 'js/admin/admin.global-settings.js');
		}

		/* enqueue scripts and styles for CTA listing page */
		if ( $screen->id == 'edit-wp-call-to-action') {
			wp_enqueue_script('wp-call-to-action-list', WP_CTA_URLPATH . 'js/admin/admin.wp-call-to-action-list.js');
			wp_enqueue_style('wp-call-to-action-list-css', WP_CTA_URLPATH.'css/admin-wp-call-to-action-list.css');
			wp_admin_css('thickbox');
			add_thickbox();
		}

		/* Enqueue scripts required on create cta page and edit cta page */
		if ( isset($hooks) && $hook == 'post-new.php' || $hook == 'post.php') {

			/* Set the default editor mode */
			add_filter( 'wp_default_editor', array( __CLASS__ , 'set_default_editor_mode' ) );/* force visual editor to open in text mode */

			/* Enqueue UI assisting js */
			wp_enqueue_script('wp-cta-post-edit-ui', WP_CTA_URLPATH . 'js/admin/admin.post-edit.js');
			wp_localize_script( 'wp-cta-post-edit-ui', 'wp_cta_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_call_to_action_meta_nonce' => wp_create_nonce('wp-call-to-action-meta-nonce'), 'wp_call_to_action_template_nonce' => wp_create_nonce('wp-cta-nonce') ) );

			/* Enqueue supportive js for template switching */
			wp_enqueue_script('wp-cta-js-metaboxes', WP_CTA_URLPATH . 'js/admin/admin.metaboxes.js');
			$template_data = $CTAExtensions->definitions;
			$template_data = json_encode($template_data);
			$template = get_post_meta($post->ID, 'wp-cta-selected-template', true);
			$template = apply_filters('wp_cta_selected_template',$template);
			$template = strtolower($template);
			$params = array('selected_template'=>$template, 'templates'=>$template_data);
			wp_localize_script('wp-cta-js-metaboxes', 'data', $params);

		}

		/* Enqueue scripts & styles for cta edit page alone */
		if ($hook == 'post.php') {
			wp_enqueue_style('admin-post-edit-css', WP_CTA_URLPATH . 'css/admin-post-edit.css');
		}

		/* Enqueue scripts & styles for cta creation page alone */
		if ( $hook == 'post-new.php'){
			wp_enqueue_script('wp-cta-js-create-new', WP_CTA_URLPATH . 'js/admin/admin.post-new.js', array('jquery'), '1.0', true );
			wp_enqueue_style('wp-cta-css-post-new', WP_CTA_URLPATH . 'css/admin-post-new.css');
		}
	}

	/**
	 *  Loads CSS & JS applied to frontend editor mode
	 */
	public static function frontend_editor_enqueues() {

		if (!isset($_GET['page'])||$_GET['page']!='wp-cta-frontend-editor') {
			return;
		}

		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		wp_dequeue_script('jquery-cookie');
		wp_enqueue_script('jquery-cookie', WP_CTA_URLPATH . 'js/jquery.cookie.js');
		wp_enqueue_style( 'wp-admin' );
		wp_admin_css('thickbox');
		add_thickbox();

		wp_enqueue_style('wp-cta-admin-css', WP_CTA_URLPATH . 'css/admin-style.css');

		wp_enqueue_script('wp-cta-post-edit-ui', WP_CTA_URLPATH . 'js/admin/admin.post-edit.js');
		wp_localize_script( 'wp-cta-post-edit-ui', 'wp_cta_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'wp_call_to_action_meta_nonce' => wp_create_nonce('wp-call-to-action-meta-nonce') ) );
		wp_enqueue_script('wp-cta-frontend-editor-js', WP_CTA_URLPATH . 'js/customizer.save.js');

		//jpicker - color picker
		wp_enqueue_script('jpicker', WP_CTA_URLPATH . 'js/libraries/jpicker/jpicker-1.1.6.min.js');
		wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => WP_CTA_URLPATH.'js/libraries/jpicker/images/' ));
		wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker-1.1.6.min.css');
		wp_enqueue_style('jpicker-css', WP_CTA_URLPATH . 'js/libraries/jpicker/css/jPicker.css');
		wp_enqueue_style('wp-cta-customizer-frontend', WP_CTA_URLPATH . 'css/customizer.frontend.css');
		wp_dequeue_script('form-population');
		wp_dequeue_script('inbound-analytics');
		wp_enqueue_script('jquery-easing', WP_CTA_URLPATH . 'js/jquery.easing.min.js');
	}

	public static function set_default_editor_mode() {
		//allowed: tinymce, html, test
		return 'html';
	}

	/**
	 *  stores 3rd party script enqueues in a static var and temporarily dequeues
	 */
	public static function dequeue_3rd_party_scripts() {
		global $wp_scripts;

		if ( !empty( $wp_scripts->queue ) ) {
		    self::$scripts_queue = $wp_scripts->queue; // store the scripts
		    foreach ( $wp_scripts->queue as $handle ) {
		          wp_dequeue_script( $handle );
		    }
		}
	}

	/**
	 *  re-enqueues 3rd party scripts
	 */
	public static function reenqueue_3rd_party_scripts() {

		if(isset(self::$scripts_queue)) {
			foreach ( self::$scripts_queue as $handle ) {
			    wp_enqueue_script( $handle );
			}
		}
	}

}

/**
*  Loads Class Pre-Init
*/
$CTA_Enqueues = new CTA_Enqueues();