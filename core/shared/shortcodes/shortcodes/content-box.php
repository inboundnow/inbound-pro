<?php
/**
*	Content Box Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['content_box'] = array(
		'no_preview' => true,
		'options' => array(
			'color' => array(
				'name' => __('Box Color', 'inbound-pro' ),
				'desc' => __('Select the color.', 'inbound-pro' ),
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
				'name' => __('Content', 'inbound-pro' ),
				'desc' => __('Enter the content.', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => ''
			)
		),
		'shortcode' => '[content_box color="{{color}}"]{{content}}[/content_box]',
		'popup_title' => 'Insert Content Box Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['content_box'] = array(
		'name' => __('Content Box', 'inbound-pro' ),
		'size' => 'one_third',
		'options' => array(
			'color' => array(
				'name' => __('Box Color', 'inbound-pro' ),
				'desc' => __('Select the color.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'default' => __('Default', 'inbound-pro' ),
					'blue' => __('Blue', 'inbound-pro' ),
					'green' => __('Green', 'inbound-pro' ),
					'red' => __('Red', 'inbound-pro' ),
					'yellow' => __('Yellow', 'inbound-pro' )
				),
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'content' => array(
				'name' => __('Content', 'inbound-pro' ),
				'desc' => __('Enter the content', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => '',
				'class' => '',
				'is_content' => 1
			)
		)
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('content_box', 'inbound_shortcode_content_box');
	if (!function_exists('inbound_shortcode_content_box')) {
		function inbound_shortcode_content_box( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'color' => 'default'
			), $atts));

			return '<div class="content-box '.$color.'">'.do_shortcode($content).'</div>';
		}
	}