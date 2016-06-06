<?php
/**
*	Columns Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['columns'] = array(
		'no_preview' => true,
		'options' => array(
			'gutter' => array(
				'name' => __('Gutter Width', 'inbound-pro' ),
				'desc' => __('A space between the columns.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'20' => '20px',
					'30' => '30px'
				),
				'std' => ''
			),
			'set' => array(
				'name' => __('Column Set', 'inbound-pro' ),
				'desc' => __('Select the set.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'[one_full]Content goes here[/one_full]' => '1/1',
					'[one_half]Content goes here[/one_half][one_half]Content goes here[/one_half]' => '1/2 + 1/2',
					'[one_third]Content goes here[/one_third][one_third]Content goes here[/one_third][one_third]Content goes here[/one_third]' => '1/3 + 1/3 + 1/3',
					'[two_third]Content goes here[/two_third][one_third]Content goes here[/one_third]' => '2/3 + 1/3',
					'[one_fourth]Content goes here[/one_fourth][one_fourth]Content goes here[/one_fourth][one_fourth]Content goes here[/one_fourth][one_fourth]Content goes here[/one_fourth]' => '1/4 + 1/4 + 1/4 + 1/4',
					'[one_half]Content goes here[/one_half][one_fourth]Content goes here[/one_fourth][one_fourth]Content goes here[/one_fourth]' => '1/2 + 1/4 + 1/4',
					'[three_fourth]Content goes here[/three_fourth][one_fourth]Content goes here[/one_fourth]' => '3/4 + 1/4',
					'[one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth]' => '1/5 + 1/5 + 1/5 + 1/5 + 1/5',
					'[two_fifth]Content goes here[/two_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth]' => '2/5 + 1/5 + 1/5 + 1/5',
					'[three_fifth]Content goes here[/three_fifth][one_fifth]Content goes here[/one_fifth][one_fifth]Content goes here[/one_fifth]' => '3/5 + 1/5 + 1/5',
					'[four_fifth]Content goes here[/four_fifth][one_fifth]Content goes here[/one_fifth]' => '4/5 + 1/5',
				),
				'std' => ''
			)
		),
		'shortcode' => '[columns gutter="{{gutter}}"]{{set}}[/columns]',
		'popup_title' => 'Insert Column Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['column'] = array(
		'name' => __('Column', 'inbound-pro' ),
		'size' => 'one_fifth',
		'options' => array(
			'content' => array(
				'name' => __('Column Content', 'inbound-pro' ),
				'desc' => __('Enter the column content', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => '',
				'class' => 'wide',
				'is_content' => '1'
			)
		)
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
 
if ( !defined('INBOUND_DISABLE_COLUMN_SHORTCODES') ) {

	/* Columns Wrap */
	if (!function_exists('inbound_shortcode_columns') || !is_defined('INBOUND_DISABLE_COLUMN_SHORTCODES') ) {
		function inbound_shortcode_columns( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'gutter' => '20'
			), $atts));

			if( $gutter == '30') {
				$gutter = 'inbound-row_30';
			} else {
				$gutter = 'inbound-row';
			}
			$content = preg_replace('/<br class=\'inbr\'.*\/>/', '', $content); // remove editor br tags
			return '<div class="'. $gutter .'">' . do_shortcode($content) . '</div>';
		}
		add_shortcode('columns', 'inbound_shortcode_columns');
	}
	

	/* Full column */
	if (!function_exists('inbound_shortcode_full_columns') ) {
		function inbound_shortcode_full_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid full">' . do_shortcode($content) . '</div>';
		}
		add_shortcode('one_full', 'inbound_shortcode_full_columns');
	}

	/* One Half */
	if (!function_exists('inbound_shortcode_one_half_columns')) {
		function inbound_shortcode_one_half_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid one-half">' . do_shortcode($content) . '</div>';
		}
		add_shortcode('one_half', 'inbound_shortcode_one_half_columns');
	}
	

	/* One Third */
	if (!function_exists('inbound_shortcode_one_third_columns')) {
		function inbound_shortcode_one_third_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid one-third">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('one_third', 'inbound_shortcode_one_third_columns');

	/* Two Third */
	if (!function_exists('inbound_shortcode_two_third_columns')) {
		function inbound_shortcode_two_third_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid two-third">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('two_third', 'inbound_shortcode_two_third_columns');

	/* One Fourth */
	if (!function_exists('inbound_shortcode_one_fourth_columns')) {
		function inbound_shortcode_one_fourth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid one-fourth">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('one_fourth', 'inbound_shortcode_one_fourth_columns');

	/* Three Fourth */
	if (!function_exists('inbound_shortcode_three_fourth_columns')) {
		function inbound_shortcode_three_fourth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid three-fourth">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('three_fourth', 'inbound_shortcode_three_fourth_columns');

	/* One Fifth */
	if (!function_exists('inbound_shortcode_one_fifth_columns')) {
		function inbound_shortcode_one_fifth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-grid one-fifth">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('one_fifth', 'inbound_shortcode_one_fifth_columns');

	/* Two Fifth */
	if (!function_exists('inbound_shortcode_two_fifth_columns')) {
		function inbound_shortcode_two_fifth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-inbound-grid two-fifth">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('two_fifth', 'inbound_shortcode_two_fifth_columns');

	/* Three Fifth */
	if (!function_exists('inbound_shortcode_three_fifth_columns')) {
		function inbound_shortcode_three_fifth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-inbound-grid three-fifth">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('three_fifth', 'inbound_shortcode_three_fifth_columns');

	/* Four Fifth */
	if (!function_exists('inbound_shortcode_four_fifth_columns')) {
		function inbound_shortcode_four_fifth_columns( $atts, $content = null ) {
			$content = preg_replace('/<br class="inbr".\/>/', '', $content); // remove editor br tags
			return '<div class="inbound-inbound-grid three-four">' . do_shortcode($content) . '</div>';
		}
	}
	add_shortcode('three_four', 'inbound_shortcode_four_fifth_columns');
}