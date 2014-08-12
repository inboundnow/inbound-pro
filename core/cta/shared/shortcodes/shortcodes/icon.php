<?php
/**
*   Icon Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['icon'] = array(
		'options' => array(
			'icon' => array(
				'name' => __('Icon', 'leads'),
				'desc' => __('Select the icon.', 'leads'),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none'
			),
			'size' => array(
				'name' => __('Size', 'leads'),
				'desc' => __('Select the icon size.', 'leads'),
				'type' => 'select',
				'options' => array(
					'normal' => __('Normal Size', 'leads'),
					'large' => __('Large Size', 'leads'),
					'2x' => __('2x Size', 'leads'),
					'3x' => __('3x Size', 'leads'),
					'4x' => __('4x Size', 'leads')
				),
				'std' => 'normal'
			),
			'style' => array(
				'name' => __('Style', 'leads'),
				'desc' => __('Select the icon style.', 'leads'),
				'type' => 'select',
				'options' => array(
					'normal' => __('Normal', 'leads'),
					'muted' => __('Muted', 'leads'),
					'border' => __('Border', 'leads'),
					'spin' => __('Spin', 'leads')
				),
				'std' => 'normal'
			),
		),
		'shortcode' => '[icon icon="{{icon}}" size="{{size}}" style="{{style}}"]',
		'popup_title' => 'Insert Icon Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('icon', 'inbound_shortcode_icon');

	function inbound_shortcode_icon( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'icon' => '',
			'size' => '',
			'style' => ''
		), $atts));

		return '<i class="icon-'. $icon .' icon-'. $size .' icon-'. $style .'"></i>';
	}