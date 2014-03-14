<?php
/* Admin Toggle screen for which addons to have on/off */


if (!class_exists('Inbound_Now_Component_Admin')) {
class Inbound_Now_Component_Admin {
	static $add_script;

	static function init() {
		add_shortcode('myshortcode', array(__CLASS__, 'handle_shortcode'));
		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts) {
		self::$add_script = true;

		// actual shortcode handling here
	}

	static function register_script() {
		wp_register_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
	}

	static function print_script() {
		if ( ! self::$add_script )
			return;

		wp_print_scripts('my-script');
	}
}

Inbound_Now_Component_Admin::init();

}