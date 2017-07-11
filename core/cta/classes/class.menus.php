<?php
/**
 * Class for adding CTA to the left wp-admin menu
 * @package CTA
 * @subpackage Menus
 */
class CTA_Menus {

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
		add_action('admin_menu', array(__CLASS__, 'add_sub_menus'));
		add_action('admin_print_footer_scripts' , array( __CLASS__ , 'print_scripts' ) );
		add_action('admin_init', array(__CLASS__, 'redirect_inbound_pro_settings'));
	}

	/**
	*  Adds sub-menus to 'Calls to Action'
	*/
	public static function add_sub_menus() {


		if ( !class_exists('Inbound_Pro_Plugin') ) {
			add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Settings', 'inbound-pro' ), __( 'Settings', 'inbound-pro' ), 'edit_ctas', 'wp_cta_global_settings', array( 'CTA_Settings', 'display_stand_alone_settings'));
			add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Upgrade to Pro', 'inbound-pro' ), __( 'Upgrade to Pro', 'inbound-pro' ), 'edit_ctas', 'wp_cta_store', array( __CLASS__ , 'store_display'));
		} else {
			add_submenu_page('edit.php?post_type=wp-call-to-action', __('Settings', 'inbound-pro'), __('Settings', 'inbound-pro'), 'edit_ctas', 'inbound-pro-cta', array( 'CTA_Menus' , 'redirect_settings' ));

		}

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Upload Templates', 'inbound-pro' ), __( 'Upload Templates', 'inbound-pro' ), 'edit_ctas', 'wp_cta_manage_templates', array( 'CTA_Template_Manager', 'display_management_page'));

	}

	public static function store_display() {

	}

	public static function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery('#menu-posts-wp-call-to-action a[href*="wp_cta_store"]').each(function() {
					jQuery(this).attr('target','_blank');
					jQuery(this).attr('href','http://www.inboundnow.com/upgrade');
				});
			});
		</script>
		<?php
	}


	/**
	 * redirects settings link to Inbound Pro settings page with Landing Pages settings pre-loaded
	 */
	public static function redirect_inbound_pro_settings() {

		if ( !isset($_GET['page']) || $_GET['page'] != 'inbound-pro-cta') {
			return;
		}

		header('Location: ' . admin_url('admin.php?page=inbound-pro&setting=cta'));
		exit;

	}
}

/**
*  Loads Class Pre-Init
*/
new CTA_Menus();
