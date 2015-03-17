<?php
/**
*   Intro Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['intro'] = array(
		'no_preview' => true,
		'options' => array(
			'title' => array(
				'name' => __('Title', 'leads'),
				'desc' => __('Enter the heading text.', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'alignment' => array(
				'name' => __('Text Alignment', 'leads'),
				'desc' => __('Enter text alignment.', 'leads'),
				'type' => 'select',
				'options' => array(
					'align-center' => __('Align Center', 'leads'),
					'align-left' => __('Align Left', 'leads'),
					'align-right' => __('Align Right', 'leads')
				),
				'std' => 'align-left',
			),
			'content' => array(
				'name' => __('Content', 'leads'),
				'desc' => __('Enter the content', 'leads'),
				'type' => 'textarea',
				'std' => ''
			)
		),
		'shortcode' => '[intro title="{{title}}" alignment="{{alignment}}"]{{content}}[/intro]',
		'popup_title' => 'Insert Intro Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['intro'] = array(
		'name' => __('Intro', 'leads'),
		'size' => 'one_full',
		'options' => array(
			'title' => array(
				'name' => __('Title', 'leads'),
				'desc' => __('Enter the heading text.', 'leads'),
				'type' => 'text',
				'class' => '',
				'is_content' => 0
			),
			'alignment' => array(
				'name' => __('Text Alignment', 'leads'),
				'desc' => __('The text alignment', 'leads'),
				'type' => 'select',
				'options' => array(
					'align-center' => __('Align Center', 'leads'),
					'align-left' => __('Align Left', 'leads'),
					'align-right' => __('Align Right', 'leads')
				),
				'std' => 'align-left',
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
	add_shortcode('intro', 'inbound_shortcode_intro');

	function inbound_shortcode_intro( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'title' => '',
			'alignment' => ''
		), $atts));

		$out = '';
		$out .= '<div class="intro clearfix '. $alignment .'">';
		$out .= '<h1>'. $title .'</h1>';
		$out .= '<div class="intro-content">'. do_shortcode($content) .'</div>';
		$out .= '</div>';

		return $out;
	}