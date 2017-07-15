<?php
/**
 * Class defines and loads JS and CSS assets
 * @package Mailer
 * @subpackage Management
 */
class Inbound_Mailer_Enqueues {

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
		add_action('wp_enqueue_scripts', array( __CLASS__ , 'load_all_frontend_enqueues' ) );
		add_action('admin_enqueue_scripts', array( __CLASS__ , 'load_all_backend_enqueues' ) );
	}

	/**
	 *  Load Frontend Enqueues
	 */
	public static function load_all_frontend_enqueues() {
		global $post , $wp_query;

		if (!isset($post)) {
			return;
		}

		self::load_frontend_global_enqueue();
		self::load_frontend_inbound_email_enqueue();
	}

	/**
	 *  Loads frontend enqueues for global frontend
	 */
	public static function load_frontend_global_enqueue() {
		global $post;

		/* enqueue jquery if not available */
		wp_enqueue_script('jquery');

		/* Enqueues css for unsubscribe page */
		wp_enqueue_style('mailer-unsubsribe-css', INBOUND_EMAIL_URLPATH . 'assets/css/frontend/style-unsubscribe.css');

		/* Enqueues js for unsubscribe page */
		wp_enqueue_script('mailer-unsubsribe-js', INBOUND_EMAIL_URLPATH . 'assets/js/frontend/unsubscribe.js');
	}

	/**
	 *  Loads frontend enqueues when inbound-email post type is being loaded
	 */
	public static function load_frontend_inbound_email_enqueue() {
		global $post;

		if ( isset($post) && $post->post_type == 'inbound-email' ) {
			return;
		}
	}


	/**
	 *  Load backened enqueues
	 */
	public static function load_all_backend_enqueues( $hook ) {
		global $post;

		//self::dequeue_3rd_party_scripts();

		/* Load enqueues directly related to inbound-email post type */
		self::load_inbound_email_post_type_enqueues( $hook );
		self::load_frontend_editor_enqueus( $hook );
	}


	/**
	 *  Enqueues scripts and styles related to inbound-email post type and cta settings pages
	 */
	public static function load_inbound_email_post_type_enqueues( $hook ) {
		global $post;

		$Templates = Inbound_Mailer_Load_Templates();
		$screen = get_current_screen();

		if (  isset($screen) && ( $screen->post_type == 'post' || $screen->post_type == 'page' ) ){
			/* Enqueue Sweet Alert support for Posts and Pages  */
			wp_enqueue_script('sweet-alert-js', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.js');
			wp_enqueue_style('sweet-alert-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.css');
		}

		if ( ( isset($screen) && $screen->post_type != 'inbound-email' ) ){
			return;
		}

		/* Enqueue dependancies */
		wp_enqueue_script(array('jquery', 'jqueryui', 'editor', 'thickbox', 'media-upload'));

		/* Enqueue jpicker for color selectors  */
		wp_enqueue_script('jpicker', INBOUND_EMAIL_URLPATH . 'assets/libraries/jpicker/jpicker-1.1.6.min.js');
		wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => INBOUND_EMAIL_URLPATH.'assets/libraries/jpicker/images/' ));
		wp_enqueue_style('jpicker-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/jpicker/css/jPicker-1.1.6.min.css');

		/* Enqueue datepicker support */
		wp_enqueue_script('jquery-datepicker', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/jquery.timepicker.min.js');
		wp_enqueue_script('jquery-datepicker-functions', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/picker_functions.js');
		wp_enqueue_script('jquery-datepicker-base', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/lib/base.js');
		wp_enqueue_script('jquery-datepicker-datepair', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/lib/datepair.js');
		wp_localize_script( 'jquery-datepicker', 'jquery_datepicker', array( 'thispath' => INBOUND_EMAIL_URLPATH.'assets/libraries/lib/jquery-datepicker/' ));

		/* Enqueue timepicker support */
		wp_enqueue_style('jquery-timepicker-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/jquery.timepicker.css');
		wp_enqueue_style('jquery-datepicker-base.css', INBOUND_EMAIL_URLPATH . 'assets/libraries/jquery-datepicker/lib/base.css');

		/* Enqueue select2 support */
		wp_enqueue_script('select2', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/Select2/select2.min.js');
		wp_enqueue_style('select2', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/Select2/select2.css');
		wp_enqueue_style('select2-bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/Select2/select2.css');

		/* Enqueue Sweet Alert support  */
		wp_enqueue_script('sweet-alert-js', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.js');
		wp_enqueue_style('sweet-alert-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.css');

		/*  Enqueue supporting js for Global Settings page */
		if (isset($_GET['page']) && $_GET['page'] === 'inbound_email_global_settings') {
			wp_enqueue_script('cta-settings-js', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.global-settings.js');
		}

		/* Enqueue scripts required on create cta page and edit cta page */
		if ( isset($hook) && $hook == 'post-new.php' || $hook == 'post.php') {

			/* Set the default editor mode */
			add_filter( 'wp_default_editor', array( __CLASS__ , 'set_default_editor_mode' ) );/* force visual editor to open in text mode */

			/* dequeue autosave */
			wp_dequeue_script( 'autosave' );

			/* Enqueue UI assisting js */
			wp_enqueue_script('mailer-post-edit-ui', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.post-edit.js');
			wp_localize_script( 'mailer-post-edit-ui', 'inbound_email_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID , 'wp_call_to_action_meta_nonce' => wp_create_nonce('inbound-email-meta-nonce'), 'wp_call_to_action_template_nonce' => wp_create_nonce('mailer-nonce') ) );

			/* Enqueue supportive js for template switching */
			wp_enqueue_script('mailer-js-metaboxes', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.metaboxes.js');
			$template_data = $Templates->definitions;
			$template_data = json_encode($template_data);
			$template = get_post_meta($post->ID, 'mailer-selected-template', true);
			$template = apply_filters('inbound_email_selected_template',$template);
			$template = strtolower($template);
			$params = array('selected_template'=>$template, 'templates'=>$template_data);
			wp_localize_script('mailer-js-metaboxes', 'data', $params);

			wp_enqueue_style('admin-post-edit-css', INBOUND_EMAIL_URLPATH . 'assets/css/admin-post-edit.css');
		}


		/* Enqueue scripts & styles for creation page alone */
		if ( $hook == 'post-new.php'){
			wp_enqueue_script('mailer-js-create-new', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.post-new.js', array('jquery'), '1.0', true );
			wp_enqueue_style('mailer-css-post-new', INBOUND_EMAIL_URLPATH . 'assets/css/admin-post-new.css');
		}

		/* Enqueue scripts and styles for mailer listing page */
		if ( $hook == 'edit.php' ){
			wp_enqueue_style('mailer-list', INBOUND_EMAIL_URLPATH . 'assets/css/admin-post-listing.css');
			wp_enqueue_script('mailer-list', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.list.js', array('jquery'), '1.0', true );
		}
	}

	/**
	 *  Loads CSS & JS applied to frontend editor mode
	 */
	public static function load_frontend_editor_enqueus() {

		if (!isset($_GET['page'])||$_GET['page']!='mailer-frontend-editor') {
			return;
		}


		/* dequeue heartbeat */
		wp_deregister_script('heartbeat');
		wp_dequeue_script( 'autosave' );

		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		wp_dequeue_script('jquery-cookie');
		wp_enqueue_script('jquery-cookie', INBOUND_EMAIL_URLPATH . 'assets/js/jquery.cookie.js');
		wp_enqueue_style( 'wp-admin' );
		wp_admin_css('thickbox');
		add_thickbox();

		wp_enqueue_script('mailer-post-edit-ui', INBOUND_EMAIL_URLPATH . 'assets/js/admin/admin.post-edit.js');
		wp_localize_script( 'mailer-post-edit-ui', 'inbound_email_post_edit_ui', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'wp_call_to_action_meta_nonce' => wp_create_nonce('inbound-email-meta-nonce') ) );

		//jpicker - color picker
		wp_enqueue_script('jpicker', INBOUND_EMAIL_URLPATH . 'assets/libraries/jpicker/jpicker-1.1.6.min.js');
		wp_localize_script( 'jpicker', 'jpicker', array( 'thispath' => INBOUND_EMAIL_URLPATH.'assets/libraries/jpicker/images/' ));
		wp_enqueue_style('jpicker-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/jpicker/css/jPicker-1.1.6.min.css');
		wp_enqueue_style('jpicker-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/jpicker/css/jPicker.css');
		wp_enqueue_style('mailer-customizer-frontend', INBOUND_EMAIL_URLPATH . 'assets/css/customizer.frontend.css');
		wp_enqueue_script('jquery-easing', INBOUND_EMAIL_URLPATH . 'assets/js/jquery.easing.min.js');
	}

	/**
	*  Sets default editor mode
	*/
	public static function set_default_editor_mode() {
		//allowed: tinymce, html, test
		return 'html';
	}

}


/**
*  Loads Class Pre-Init
*/
$Inbound_Mailer_Enqueues = new Inbound_Mailer_Enqueues();