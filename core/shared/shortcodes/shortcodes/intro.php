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
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'alignment' => array(
				'name' => __('Text Alignment', 'inbound-pro' ),
				'desc' => __('Enter text alignment.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'align-center' => __('Align Center', 'inbound-pro' ),
					'align-left' => __('Align Left', 'inbound-pro' ),
					'align-right' => __('Align Right', 'inbound-pro' )
				),
				'std' => 'align-left',
			),
			'content' => array(
				'name' => __('Content', 'inbound-pro' ),
				'desc' => __('Enter the content', 'inbound-pro' ),
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
		'name' => __('Intro', 'inbound-pro' ),
		'size' => 'one_full',
		'options' => array(
			'title' => array(
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => 0
			),
			'alignment' => array(
				'name' => __('Text Alignment', 'inbound-pro' ),
				'desc' => __('The text alignment', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'align-center' => __('Align Center', 'inbound-pro' ),
					'align-left' => __('Align Left', 'inbound-pro' ),
					'align-right' => __('Align Right', 'inbound-pro' )
				),
				'std' => 'align-left',
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