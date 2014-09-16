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
				'name' => __('Border Style', 'leads'),
				'desc' => __('Select the style.', 'leads'),
				'type' => 'select',
				'options' => array(
					'none' => __('No Border', 'leads'),
					'dashed' => __('Dashed', 'leads'),
					'dotted' => __('Dotted', 'leads'),
					'double' => __('Double', 'leads'),
					'solid' => __('Solid', 'leads')
				),
				'std' => 'none'
			),
			'color' => array(
				'name' => __('Border Color', 'leads'),
				'desc' => __('Enter a hex color code.', 'leads'),
				'type' => 'text',
				'std' => '#ebebea'
			),
			'margin_top' => array(
				'name' => __('Top Margin', 'leads'),
				'desc' => __('Enter the top margin value.', 'leads'),
				'type' => 'text',
				'std' => '0px'
			),
			'margin_bottom' => array(
				'name' => __('Bottom Margin', 'leads'),
				'desc' => __('Enter the bottom margin value.', 'leads'),
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
		'name' => __('Divider', 'leads'),
		'size' => 'one_full',
		'options' => array(
			'style' => array(
				'name' => __('Border Style', 'leads'),
				'desc' => __('Select the style.', 'leads'),'type' => 'select',
				'options' => array(
					'none' => __('No Border', 'leads'),
					'dashed' => __('Dashed', 'leads'),
					'dotted' => __('Dotted', 'leads'),
					'double' => __('Double', 'leads'),
					'solid' => __('Solid', 'leads')
				),
				'std' => 'none',
				'class' => '',
				'is_content' => '0'
			),
			'color' => array(
				'name' => __('Border Color', 'leads'),
				'desc' => __('Enter a hex color code.', 'leads'),
				'type' => 'text',
				'std' => '#ebebea',
				'class' => '',
				'is_content' => '0'
			),
			'margin_top' => array(
				'name' => __('Margin Top', 'leads'),
				'desc' => __('Enter the top margin value.', 'leads'),
				'type' => 'text',
				'std' => '0px',
				'class' => '',
				'is_content' => '0'
			),
			'margin_bottom' => array(
				'name' => __('Margin Bottom', 'leads'),
				'desc' => __('Enter the bottom margin value.', 'leads'),
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