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
				'name' => __('Box Color', 'leads'),
				'desc' => __('Select the color.', 'leads'),
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
				'name' => __('Content', 'leads'),
				'desc' => __('Enter the content.', 'leads'),
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
		'name' => __('Content Box', 'leads'),
		'size' => 'one_third',
		'options' => array(
			'color' => array(
				'name' => __('Box Color', 'leads'),
				'desc' => __('Select the color.', 'leads'),
				'type' => 'select',
				'options' => array(
					'default' => __('Default', 'leads'),
					'blue' => __('Blue', 'leads'),
					'green' => __('Green', 'leads'),
					'red' => __('Red', 'leads'),
					'yellow' => __('Yellow', 'leads')
				),
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'content' => array(
				'name' => __('Content', 'leads'),
				'desc' => __('Enter the content', 'leads'),
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