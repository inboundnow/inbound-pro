<?php
/**
*   Content Box Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['lists'] = array(
		'no_preview' => false,
		'options' => array(
			'icon' => array(
							'name' => __('List Icon', 'inbound-pro' ),
							'desc' => __('Select an icon for the List', 'inbound-pro' ),
							'type' => 'select',
							'options' => $fontawesome,
							'std' => 'check'
						),
			'font-size' => array(
							'name' => __('Font Size', 'inbound-pro' ),
							'desc' => __('Size of List Font', 'inbound-pro' ),
							'type' => 'text',
							'std' => '16'
						),
			'bottom-margin' => array(
							'name' => __('Bottom Margin', 'inbound-pro' ),
							'desc' => __('space between list items', 'inbound-pro' ),
							'type' => 'text',
							'std' => '10'
						),
			'icon-color' => array(
							'name' => __('Icon Color', 'inbound-pro' ),
							'desc' => __('Color of Icon', 'inbound-pro' ),
							'type' => 'colorpicker',
							'std' => '000000'
						),
			'text-color' => array(
							'name' => __('Text Color', 'inbound-pro' ),
							'desc' => __('Color of Text in List', 'inbound-pro' ),
							'type' => 'colorpicker',
							'std' => ''
						),
			'columns' => array(
						'name' => __('Number of Columns', 'inbound-pro' ),
						'desc' => __('Number of Columns', 'inbound-pro' ),
						'type' => 'select',
						'options' => array(
							"1" => "Single Column (default)",
							"2" => "2 Column",
							"3" => "3 Column",
							"4" => "4 Column",
							"5" => "5 Column",
							),
						'std' => '1',
					),


		),
		'shortcode' => '[list icon="{{icon}}" font_size="{{font-size}}" icon_color="{{icon-color}}" text_color="{{text-color}}" columns="{{columns}}" bottom_margin="{{bottom-margin}}"](Insert Your Unordered List Here. Use the List insert button in the editor. Delete this text)[/list]',
		'popup_title' => __('Insert Styled List Shortcode', 'inbound-pro' )
	);