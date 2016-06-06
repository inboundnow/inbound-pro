<?php
/**
*   Icon Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['icon'] = array(
		'options' => array(
			'icon' => array(
				'name' => __('Icon', 'inbound-pro' ),
				'desc' => __('Select the icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none'
			),
			'size' => array(
				'name' => __('Size', 'inbound-pro' ),
				'desc' => __('Select the icon size.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'normal' => __('Normal Size', 'inbound-pro' ),
					'large' => __('Large Size', 'inbound-pro' ),
					'2x' => __('2x Size', 'inbound-pro' ),
					'3x' => __('3x Size', 'inbound-pro' ),
					'4x' => __('4x Size', 'inbound-pro' )
				),
				'std' => 'normal'
			),
			'style' => array(
				'name' => __('Style', 'inbound-pro' ),
				'desc' => __('Select the icon style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'normal' => __('Normal', 'inbound-pro' ),
					'muted' => __('Muted', 'inbound-pro' ),
					'border' => __('Border', 'inbound-pro' ),
					'spin' => __('Spin', 'inbound-pro' )
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