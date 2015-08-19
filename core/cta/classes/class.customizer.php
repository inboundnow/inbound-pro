<?php
/**
 * - Editor
 * - Preview
 * - Parent ( Customizer )
 */

class CTA_Customizer {

	/**
	*	Initiates class CTA_Customizer
	*/
	public function	__construct() {

		self::load_hooks();

		/* If preview mode in effect then kill admin bar */
		if (isset($_GET['cache_bust'])) {
			show_admin_bar( false );
		}
	}

	/**
	*	Loads hooks and filters
	*/
	public static function load_hooks() {

		/* Load popup iframe preview not in customizer
		    TODO: Move elsewhere
		*/
		if (isset($_GET['inbound_popup_preview'])) 	{
			/* Enqueue Scripts  */
			add_action( 'admin_enqueue_scripts', array(__CLASS__,'popup_preview_scripts'));
			/* Loads Preview Iframe in wp_head */
			add_action('wp_head', array(__CLASS__, 'toggle_between_variations'));
		}

		/* Load customizer Parent Window. 'inbound-editor' & 'inbound-preview' live inside */
		if (isset($_GET['inbound-customizer']) && $_GET['inbound-customizer']=='on') {
			add_filter('wp_head', array(__CLASS__, 'launch_customizer'));
			add_action('wp_enqueue_scripts', array(__CLASS__, 'customizer_parent_scripts'));
		}

		/* Load customizer editor */
		if (isset($_GET['inbound-editor']) && $_GET['inbound-editor'] === 'true') {
			add_action('admin_enqueue_scripts', array(__CLASS__, 'customizer_editor_scripts'));
			add_filter('admin_body_class', array(__CLASS__, 'add_editor_body_class'));
		}
		/* Load customizer preview */
		if (isset($_GET['inbound-preview'])) {
			add_action('wp_enqueue_scripts', array(__CLASS__, 'customizer_preview_scripts'));

		}
		/* Add hidden inputs */
		add_action( 'edit_form_after_title', array(__CLASS__, 'add_hidden_inputs'));

	}
	public static function popup_preview_scripts() {
		wp_enqueue_style('wp_cta_ab_testing_customizer_css', WP_CTA_URLPATH . 'assets/css/customizer-ab-testing.css');
	}
	/* Load Scripts for Preview Window */
	public static function customizer_preview_scripts() {
		show_admin_bar( false );
		wp_enqueue_style('inbound-preview', WP_CTA_URLPATH . 'assets/css/iframe-preview.css');
		/*wp_register_script('inbound-customizer-load-js', WP_CTA_URLPATH . 'assets/js/iframe-preview.js', array('jquery'));
			wp_enqueue_script('inbound-customizer-load-js');*/

	}

	/* Add customizer class to body for CSS overrides */
	public static function add_editor_body_class($classes) {
			global $post;
			$post_type = get_post_type( $post->ID );
			$classes .= 'inbound-customizer';
	        return $classes;
	}

	/* Keep Customizer Active on post save */
	public static function add_hidden_inputs() {

		if((isset($_REQUEST['inbound-editor']) && $_REQUEST['inbound-editor'] === 'true')
			|| isset($_GET['inbound-editor']) && $_GET['inbound-editor'] === 'true' ) {
			echo '<input type="hidden" name="inbound-editor" id="inbound-editor-status" value="true" />';
		}
	}

	public static function customizer_editor_scripts() {

		$screen = get_current_screen();
		if ( ( isset($screen) && $screen->post_type != 'wp-call-to-action' ) ) {
			return;
		}
		wp_enqueue_script('inbound-customizer-editor-js', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-editor.js');
		wp_enqueue_style('inbound-customizer-editor-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/customizer-editor.css');
		//wp_enqueue_style('cta-customizer-admin', WP_CTA_URLPATH . 'assets/css/new-customizer-admin.css');
		wp_enqueue_script('cta-frontend-editor-js', WP_CTA_URLPATH . 'assets/js/customizer.save.js');
		wp_enqueue_script('cta-customizer-admin', WP_CTA_URLPATH . 'assets/js/admin/new-customizer-admin.js');

	}

	/* Adds CTA Preview Iframe */
	public static function toggle_between_variations() {
		/* Way to toggle between Variations */
	}

	public static function customizer_parent_scripts() {
		wp_enqueue_style('inbound-customizer-parent-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/customizer-parent.css');
		wp_enqueue_script('inbound-customizer-parent-js', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/customizer-parent.js');
		/* todo enqueue script */
	}
	public static function launch_customizer() {

		global $post;

		$post_id = $post->ID;
		$post_type = $post->post_type;
		$permalink = get_permalink($post_id);

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
		$wp_cta_variation = (isset($_GET['wp-cta-variation-id'])) ? $_GET['wp-cta-variation-id'] : '0';

		$params = '?wp-cta-variation-id='.$wp_cta_variation.'&cache_bust='.$randomString.'&inbound-preview='.$randomString;

		$preview_link = add_query_arg( array(  'cache_bust' => $randomString, 'inbound-preview' => 'true', 'wmode' => 'opaque'), $permalink);


		$customizer_link = add_query_arg(
								array( 'wp-cta-variation-id' => $wp_cta_variation,
										'action' => 'edit',
										'inbound-editor' => 'true' ),
										admin_url() .'post.php?post='.$post_id );

		$version = $_GET['wp-cta-variation-id'];

		/*$current_post_id = $wp_query->get_queried_object_id();*/

		$width = get_post_meta($post_id, 'wp_cta_width-'.$version, true);
		$height = get_post_meta($post_id, 'wp_cta_height-'.$version, true);

		$correct_height = self::get_correct_dimensions($height, 'height');
		$correct_height = (!$correct_height) ? 'auto' : $correct_height;
		$correct_width = 'width:100%;';

		?>
		</head>
		<!-- http://stackoverflow.com/questions/7816372/make-iframes-resizable-dynamically -->
		<body>
			<div id="inbound-customizer-overlay" class="wp-cta-load-overlay"
			style="display:none;"></div>

			<table style="width:100%">
				<tr>
					<td style="width:35%">
						<iframe id="wp_cta_customizer_options" class="inbound-customizer-editor"
						src="<?php echo $customizer_link;?>" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>
					</td>

					<td style="width:55%">
						<iframe id="wp-cta-live-preview" class="inbound-customizer-preview" scrolling="no" src="<?php echo $preview_link; ?>"></iframe>
					</td>
				</tr>
			</table>

		<?php wp_footer(); ?>
		</body>
		<?php exit;
	}

	/**
	*  Looks at user inputed width and height and prepares correct format
	*/
	public static function get_correct_dimensions($input, $css_prop) {

		if (preg_match("/px/i", $input)){
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else if (preg_match("/%/", $input)) {
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else if (preg_match("/em/", $input)) {
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else {
		   $input = " ".$css_prop.": $input" . "px;";
 		}

		return $input;
	}

}

$CTA_Customizer = new CTA_Customizer();