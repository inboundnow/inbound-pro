<?php
/**
*	Divider Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['divider'] = array(
		'no_preview' => true,
		'options' => array(
			'style' => array(
				'name' => __('Border Style', 'inbound-pro' ),
				'desc' => __('Select the style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'none' => __('No Border', 'inbound-pro' ),
					'dashed' => __('Dashed', 'inbound-pro' ),
					'dotted' => __('Dotted', 'inbound-pro' ),
					'double' => __('Double', 'inbound-pro' ),
					'solid' => __('Solid', 'inbound-pro' )
				),
				'std' => 'none'
			),
			'color' => array(
				'name' => __('Border Color', 'inbound-pro' ),
				'desc' => __('Enter a hex color code.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '#ebebea'
			),
			'margin_top' => array(
				'name' => __('Top Margin', 'inbound-pro' ),
				'desc' => __('Enter the top margin value.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '0px'
			),
			'margin_bottom' => array(
				'name' => __('Bottom Margin', 'inbound-pro' ),
				'desc' => __('Enter the bottom margin value.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '0px'
			)
		),
		'shortcode' => '[divider style="{{style}}" color="{{color}}" margin_top="{{margin_top}}" margin_bottom="{{margin_bottom}}"]',
		'popup_title' => 'Insert Divider Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['divider'] = array(
		'name' => __('Divider', 'inbound-pro' ),
		'size' => 'one_full',
		'options' => array(
			'style' => array(
				'name' => __('Border Style', 'inbound-pro' ),
				'desc' => __('Select the style.', 'inbound-pro' ),'type' => 'select',
				'options' => array(
					'none' => __('No Border', 'inbound-pro' ),
					'dashed' => __('Dashed', 'inbound-pro' ),
					'dotted' => __('Dotted', 'inbound-pro' ),
					'double' => __('Double', 'inbound-pro' ),
					'solid' => __('Solid', 'inbound-pro' )
				),
				'std' => 'none',
				'class' => '',
				'is_content' => '0'
			),
			'color' => array(
				'name' => __('Border Color', 'inbound-pro' ),
				'desc' => __('Enter a hex color code.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '#ebebea',
				'class' => '',
				'is_content' => '0'
			),
			'margin_top' => array(
				'name' => __('Margin Top', 'inbound-pro' ),
				'desc' => __('Enter the top margin value.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '0px',
				'class' => '',
				'is_content' => '0'
			),
			'margin_bottom' => array(
				'name' => __('Margin Bottom', 'inbound-pro' ),
				'desc' => __('Enter the bottom margin value.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '0px',
				'class' => '',
				'is_content' => '0'
			)
		)
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('divider', 'inbound_shortcode_divider');
	if (!function_exists('inbound_shortcode_divider')) {
		function inbound_shortcode_divider( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'style' => '',
				'margin_top' => '',
				'margin_bottom' => '',
				'color' => '',
				'class' => ''
			), $atts));

			$margin_top = ($margin_top) ? $margin_top : 0;
			$margin_bottom = ($margin_bottom) ? $margin_bottom : 0;
			$color = ($color) ? $color : '#eaeaea';
			$class = ($class) ? " $class" : '';

			return '<div class="divider '. $style . $class .'" style="margin-top:'. $margin_top .';margin-bottom:'. $margin_bottom .';border-color:'. $color .'"></div>';
		}
	}