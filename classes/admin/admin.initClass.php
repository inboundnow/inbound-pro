<?php
/*
*  Inbound Pro Admin Class
*/


if (!class_exists('Inbound_Pro_Admin')) {
class Inbound_Pro_Admin {
	static $add_script;

	static function init() {
		add_shortcode('myshortcode', array(__CLASS__, 'handle_shortcode'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts) {
		self::$add_script = true;

		// actual shortcode handling here
	}

	static function admin_enqueue() {

		wp_enqueue_script('inbound-pro-js', plugins_url('js/inbound-pro.js', __FILE__), array('jquery'), '1.0', true);
		wp_enqueue_style('inbound-pro-css', plugins_url('css/inbound-pro.css', __FILE__));
	}

	static function print_script() {
		if ( ! self::$add_script )
			return;

		wp_print_scripts('my-script');
	}
}

Inbound_Pro_Admin::init();

}