<?php
/**
*	Button Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['button'] = array(
		'no_preview' => false,

		'options' => array(
			/**
			'style' => array(
				'name' => __('Button Style', 'inbound-pro' ),
				'desc' => __('Select the button style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'default' => 'Default',
					'flat' => 'flat',
					'sunk' => 'sunk'
				),
				'std' => 'default'
			),*/
			'content' => array(
				'name' => __('Button Text', 'inbound-pro' ),
				'desc' => __('Enter the button text label.', 'inbound-pro' ),
				'type' => 'text',
				'std' => 'Button Text'
			),
			'url' => array(
				'name' => __('Button Link', 'inbound-pro' ),
				'desc' => __('Enter the destination URL.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'font-size' => array(
							'name' => __('Font Size', 'inbound-pro' ),
							'desc' => __('Size of Button Font. This also determines default button size', 'inbound-pro' ),
							'type' => 'text',
							'std' => '20'
			),
			/**
			'color' => array(
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
			), */
			'color' => array(
							'name' => __('Button Color', 'inbound-pro' ),
							'desc' => __('Color of button', 'inbound-pro' ),
							'type' => 'colorpicker',
							'std' => '#c8232b'
						),
			'text-color' => array(
							'name' => __('Button Text Color', 'inbound-pro' ),
							'desc' => __('Color of text', 'inbound-pro' ),
							'type' => 'colorpicker',
							'std' => '#ffffff'
						),
			'icon' => array(
				'name' => __('Icon', 'inbound-pro' ),
				'desc' => __('Select an icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => ''
			),

			'width' => array(
				'name' => __('Custom Width', 'inbound-pro' ),
				'desc' => __('Enter in pixel width or % width. Example: 200 <u>or</u> 100%', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'class' => 'main-design-settings',
			),
			'target' => array(
				'name' => __('Open Link in New Tab?', 'inbound-pro' ),
				'checkbox_text' => __('Do you want to open links in this window or a new one?', 'inbound-pro' ),
				'desc' => '',
				'type' => 'select',
				'options' => array(
					'_self' => 'Open Link in Same Window',
					'_blank' => 'Open Link in New Tab',

				),
				'std' => '_self'
			),
		),
		// style="{{style}}"
		'shortcode' => '[inbound_button font_size="{{font-size}}" color="{{color}}" text_color="{{text-color}}" icon="{{icon}}" url="{{url}}" width="{{width}}" target="{{target}}"]{{content}}[/inbound_button]',
		'popup_title' =>'Insert Button Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */