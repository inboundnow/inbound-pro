<?php

if ( !class_exists('CTA_Menus') ) {

/**
*  Loads admin sub-menus and performs misc menu related functions
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
		add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'print_scripts' ) );
	}

	/**
	*  Adds sub-menus to 'Calls to Action'
	*/
	public static function add_sub_menus() {
		if ( !current_user_can('manage_options')) {
			remove_menu_page( 'edit.php?post_type=wp-call-to-action' );
			return;
		}

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Forms', 'cta' ), __( 'Forms', 'cta'), 'manage_options', 'inbound-forms-redirect',100);

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Settings', 'cta'), __( 'Settings', 'cta'), 'manage_options', 'wp_cta_global_settings', array( 'CTA_Global_Settings', 'display_global_settings'));

		if ( !class_exists('Inbound_Pro_Plugin') ) {
			add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Upgrade to Pro', 'cta'), __( 'Upgrade to Pro', 'cta'), 'manage_options', 'wp_cta_store', array( __CLASS__ , 'store_display'));
		}

		add_submenu_page('edit.php?post_type=wp-call-to-action', __( 'Upload Templates', 'cta'), __( 'Upload Templates', 'cta'), 'manage_options', 'wp_cta_manage_templates', array( 'CTA_Template_Manager', 'display_management_page'));

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
}

/**
*  Loads Class Pre-Init
*/
$CTA_Menus = new CTA_Menus();

}