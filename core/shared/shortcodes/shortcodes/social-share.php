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
				'desc' => __('Style of Icons', 'inbound-pro' ),
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
				'name' => __('Align Icons', 'inbound-pro' ),
				'desc' => __('Alignment Settings', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					"horizontal" => "Horizontal",
					"vertical" => "Vertical",
					),
				'std' => 'inline-block'
			),

			'facebook' => array(
				'name' => __('Facebook', 'inbound-pro' ),
				'desc' => __('Show facebook share icon', 'inbound-pro' ),
				'type' => 'checkbox',
				'std' => '1'
			),
			'twitter' => array(
				'name' => __('Twitter', 'inbound-pro' ),
				'desc' => __('Show twitter share icon', 'inbound-pro' ),
				'type' => 'checkbox',
				'std' => '1'
			),
			'google_plus' => array(
				'name' => __('Google+', 'inbound-pro' ),
				'desc' => __('Show google plus share icon', 'inbound-pro' ),
				'type' => 'checkbox',
				'std' => '1'
			),
			'linkedin' => array(
				'name' => __('Linkedin', 'inbound-pro' ),
				'desc' => __('Show linkedin share icon', 'inbound-pro' ),
				'type' => 'checkbox',
				'std' => '1'
			),
			'pinterest' => array(
				'name' => __('Pinterest', 'inbound-pro' ),
				'desc' => __('Show pinterest share icon', 'inbound-pro' ),
				'type' => 'checkbox',
				'std' => '1',
			),

			'text' => array(
				'name' => __('Custom Share Text', 'inbound-pro' ),
				'desc' => __('Optional setting. Enter your custom share text', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Custom Share Text. Title of page used by default',
			),
			'link' => array(
				'name' => __('Custom Share URL', 'inbound-pro' ),
				'desc' => __('Optional setting. Enter your custom share link URL', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Custom URL. Page permalink used by default',
			),
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Optional setting.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'placeholder' => 'Optional Header Text',
			),
			'header-align' => array(
				'name' => __('Heading Align', 'inbound-pro' ),
				'desc' => __('Heading Alignment Settings', 'inbound-pro' ),
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