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
				'name' => __('Color Style', 'inbound-pro' ),
				'desc' => __('Select the style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'default' => __('Default', 'inbound-pro' ),
					'blue' => __('Blue', 'inbound-pro' ),
					'green' => __('Green', 'inbound-pro' ),
					'red' => __('Red', 'inbound-pro' ),
					'yellow' => __('Yellow', 'inbound-pro' )
				),
				'std' => ''
			),
			'content' => array(
				'name' => __('Message', 'inbound-pro' ),
				'desc' => __('Your message here.', 'inbound-pro' ),
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