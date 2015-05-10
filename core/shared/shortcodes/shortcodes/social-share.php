<?php
/**
*   Social Share Shortcode
*   Built from http://www.mojotech.com/social-builder
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['social-share'] = array(
		'no_preview' => false,
		'options' => array(
			'style' => array(
				'name' => 'Style of Icons',
				'desc' => __('Style of Icons', 'leads'),
				'type' => 'select',
				'options' => array(
					"bar" => "Bar",
					"circle" => "Circle",
					'square' => "Square",
					'black' => "Black",

					),
				'std' => 'bar'
			),
			'align' => array(
				'name' => __('Align Icons', 'leads'),
				'desc' => __('Alignment Settings', 'leads'),
				'type' => 'select',
				'options' => array(
					"horizontal" => "Horizontal",
					"vertical" => "Vertical",
					),
				'std' => 'inline-block'
			),

			'facebook' => array(
				'name' => __('Facebook', 'leads'),
				'desc' => __('Show facebook share icon', 'leads'),
				'type' => 'checkbox',
				'std' => '1'
			),
			'twitter' => array(
				'name' => __('Twitter', 'leads'),
				'desc' => __('Show twitter share icon', 'leads'),
				'type' => 'checkbox',
				'std' => '1'
			),
			'google_plus' => array(
				'name' => __('Google+', 'leads'),
				'desc' => __('Show google plus share icon', 'leads'),
				'type' => 'checkbox',
				'std' => '1'
			),
			'linkedin' => array(
				'name' => __('Linkedin', 'leads'),
				'desc' => __('Show linkedin share icon', 'leads'),
				'type' => 'checkbox',
				'std' => '1'
			),
			'pinterest' => array(
				'name' => __('Pinterest', 'leads'),
				'desc' => __('Show pinterest share icon', 'leads'),
				'type' => 'checkbox',
				'std' => '1',
			),

			'text' => array(
				'name' => __('Custom Share Text', 'leads'),
				'desc' => __('Optional setting. Enter your custom share text', 'leads'),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Custom Share Text. Title of page used by default',
			),
			'link' => array(
				'name' => __('Custom Share URL', 'leads'),
				'desc' => __('Optional setting. Enter your custom share link URL', 'leads'),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Custom URL. Page permalink used by default',
			),
			'heading' => array(
				'name' => __('Heading', 'leads'),
				'desc' => __('Optional setting.', 'leads'),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Optional Header Text',
			),
			'header-align' => array(
				'name' => __('Heading Align', 'leads'),
				'desc' => __('Heading Alignment Settings', 'leads'),
				'type' => 'select',
				'options' => array(
					"inline" => "Inline",
					"above" => "Above",

					),
				'std' => 'inline'
			),
		),
		'shortcode' => '[social_share style="{{style}}" align="{{align}}" heading_align="{{header-align}}" text="{{text}}" heading="{{heading}}" facebook="{{facebook}}" twitter="{{twitter}}" google_plus="{{google_plus}}" linkedin="{{linkedin}}" pinterest="{{pinterest}}" link="{{link}}" /]',
		'popup_title' => 'Insert Social Share Shortcode'
	);