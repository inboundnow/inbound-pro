<?php
/**
*   Call Out Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['callout'] = array(
		'no_preview' => true,
		'options' => array(
			'title' => array(
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'subtitle' => array(
				'name' => __('Sub Title', 'inbound-pro' ),
				'desc' => __('Enter the sub title.', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => ''
			),
			'button_color' => array(
				'name' => __('Button Color', 'inbound-pro' ),
				'desc' => __('Select the button color.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'default' => 'Default',
					'black' => 'Black',
					'blue' => 'Blue',
					'brown' => 'Brown',
					'green' => 'Green',
					'orange' => 'Orange',
					'pink' => 'Pink',
					'purple' => 'Purple',
					'red' => 'Red',
					'silver' => 'Silver',
					'yellow' => 'Yellow',
					'white' => 'White'
				),
				'std' => 'default'
			),
			'button_label' => array(
				'name' => __('Button Text Label', 'inbound-pro' ),
				'desc' => __('Enter the button text label.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'button_icon' => array(
				'name' => __('Button Icon', 'inbound-pro' ),
				'desc' => __('Select an icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none'
			),
			'link' => array(
				'name' => __('Link', 'inbound-pro' ),
				'desc' => __('Enter the button link destination URL.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			)
		),
		'shortcode' => '[callout title="{{title}}" subtitle="{{subtitle}}" button_label="{{button_label}}" button_icon="{{button_icon}}" link="{{link}}"]',
		'popup_title' => 'Insert Call Out Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['callout'] = array(
		'name' => __('Call Out', 'inbound-pro' ),
		'size' => 'one_full',
		'options' => array(
			'title' => array(
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => '0'
			),
			'subtitle' => array(
				'name' => __('Sub Title', 'inbound-pro' ),
				'desc' => __('Enter the sub title.', 'inbound-pro' ),
				'type' => 'textarea',
				'class' => '',
				'is_content' => '0'
			),
			'button_color' => array(
				'name' => __('Button Color', 'inbound-pro' ),
				'desc' => __('Select the button color.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'default' => 'Default',
					'black' => 'Black',
					'blue' => 'Blue',
					'brown' => 'Brown',
					'green' => 'Green',
					'orange' => 'Orange',
					'pink' => 'Pink',
					'purple' => 'Purple',
					'red' => 'Red',
					'silver' => 'Silver',
					'yellow' => 'Yellow',
					'white' => 'White'
				),
				'std' => 'default',
				'class' => '',
				'is_content' => '0'
			),
			'button_label' => array(
				'name' => __('Button Text Label', 'inbound-pro' ),
				'desc' => __('Enter the button text label.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => '0'
			),
			'button_icon' => array(
				'name' => __('Button Icon', 'inbound-pro' ),
				'desc' => __('Select an icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none',
				'class' => '',
				'is_content' => '0'
			),
			'link' => array(
				'name' => __('Link', 'inbound-pro' ),
				'desc' => __('Enter the button link destination URL.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => '0'
			)
		)
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('callout', 'inbound_shortcode_callout');
	if (!function_exists('inbound_shortcode_callout')) {
		function inbound_shortcode_callout( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'title' => '',
				'subtitle' => '',
				'button_color' => '',
				'button_label' => '',
				'button_icon' => '',
				'link' => ''
			), $atts));

			$icon = ($button_icon) ? '<i class="icon-'. $button_icon .'"></i>&nbsp;&nbsp;' : '';

			$out = '';
			$out .= '<div class="callout clearfix">';
				$out .= '<div class="left">';
					$out .= '<h2>'. $title .'</h2>';
					$out .= '<div class="subtitle">'. $subtitle .'</div>';
				$out .= '</div>';

				if ($link ) :
				$out .= '<div class="right">';
					$out .= '<a class="button '. $button_color .'" href="'. $link .'">'. $icon . $button_label .'</a>';
				$out .= '</div>';
				endif;
			$out .= '</div>';

			return $out;
		}
	}