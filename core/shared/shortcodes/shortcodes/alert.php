<?php
/**
*	Alert Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['alert'] = array(
		'no_preview' => true,
		'options' => array(
			'color' => array(
				'name' => __('Color Style', 'leads'),
				'desc' => __('Select the style.', 'leads'),
				'type' => 'select',
				'options' => array(
					'default' => __('Default', 'leads'),
					'blue' => __('Blue', 'leads'),
					'green' => __('Green', 'leads'),
					'red' => __('Red', 'leads'),
					'yellow' => __('Yellow', 'leads')
				),
				'std' => ''
			),
			'content' => array(
				'name' => __('Message', 'leads'),
				'desc' => __('Your message here.', 'leads'),
				'type' => 'textarea',
				'std' => ''
			)
		),
		'shortcode' => '[alert color="{{color}}"]{{content}}[/alert]',
		'popup_title' => 'Insert Alert Message Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('alert', 'inbound_shortcode_alert');
	if (!function_exists('inbound_shortcode_alert')) {
		function inbound_shortcode_alert( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'color' => ''
			), $atts));

			return '<div class="alert-message '.$color.'">'.do_shortcode($content).'</div>';
		}
	}