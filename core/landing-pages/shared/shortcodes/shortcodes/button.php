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
				'name' => __('Button Style', 'leads'),
				'desc' => __('Select the button style.', 'leads'),
				'type' => 'select',
				'options' => array(
					'default' => 'Default',
					'flat' => 'flat',
					'sunk' => 'sunk'
				),
				'std' => 'default'
			),*/
			'content' => array(
				'name' => __('Button Text', 'leads'),
				'desc' => __('Enter the button text label.', 'leads'),
				'type' => 'text',
				'std' => 'Button Text'
			),
			'url' => array(
				'name' => __('Button Link', 'leads'),
				'desc' => __('Enter the destination URL.', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'font-size' => array(
							'name' => __('Font Size', 'leads'),
							'desc' => __('Size of Button Font. This also determines default button size', 'leads'),
							'type' => 'text',
							'std' => '20'
			),
			/**
			'color' => array(
				'name' => __('Button Color', 'leads'),
				'desc' => __('Select the button color.', 'leads'),
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
							'name' => __('Button Color', 'leads'),
							'desc' => __('Color of button', 'leads'),
							'type' => 'colorpicker',
							'std' => '#c8232b'
						),
			'text-color' => array(
							'name' => __('Button Text Color', 'leads'),
							'desc' => __('Color of text', 'leads'),
							'type' => 'colorpicker',
							'std' => '#ffffff'
						),
			'icon' => array(
				'name' => __('Icon', 'leads'),
				'desc' => __('Select an icon.', 'leads'),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => ''
			),

			'width' => array(
				'name' => __('Custom Width', 'leads'),
				'desc' => __('Enter in pixel width or % width. Example: 200 <u>or</u> 100%', 'leads'),
				'type' => 'text',
				'std' => '',
				'class' => 'main-design-settings',
			),
			'target' => array(
				'name' => __('Open Link in New Tab?', 'leads'),
				'checkbox_text' => __('Do you want to open links in this window or a new one?', 'leads'),
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
		'shortcode' => '[button font_size="{{font-size}}" color="{{color}}" text_color="{{text-color}}" icon="{{icon}}" url="{{url}}" width="{{width}}" target="{{target}}"]{{content}}[/button]',
		'popup_title' =>'Insert Button Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */