<?php
/**
*	Teaser Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['teaser'] = array(
		'no_preview' => true,
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Enter the heading text', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'style' => array(
				'name' => __('Style', 'inbound-pro' ),
				'desc' => __('Select the style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'' => __('Default', 'inbound-pro' ),
					'nested' => __('Nested', 'inbound-pro' ),
					'centered' => __('Centered', 'inbound-pro' )
				),
				'std' => ''
			),
			'column' => array(
				'name' => __('Column', 'inbound-pro' ),
				'desc' => __('Select the number of column(s).', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'inbound-pro' ),
					'2' => __('2 Columns', 'inbound-pro' ),
					'3' => __('3 Columns', 'inbound-pro' ),
					'4' => __('4 Columns', 'inbound-pro' ),
					'5' => __('5 Columns', 'inbound-pro' )
				),
				'std' => '3'
			)
		),
		'child' => array(
			'options' => array(
				'title' => array(
					'name' => __('Title', 'inbound-pro' ),
					'desc' => __('Enter the title.', 'inbound-pro' ),
					'type' => 'text',
					'std' => ''
				),
				'subtitle' => array(
					'name' => __('Sub Title', 'inbound-pro' ),
					'desc' => __('Enter the sub title.', 'inbound-pro' ),
					'type' => 'text',
					'std' => ''
				),
				'icon' => array(
					'name' => __('Icon', 'inbound-pro' ),
					'desc' => __('Select an icon.', 'inbound-pro' ),
					'type' => 'select',
					'options' => $fontawesome,
					'std' => ''
				),
				'image' => array(
					'name' => __('Image URL', 'inbound-pro' ),
					'desc' => __('Enter your image url, it will override the icon above', 'inbound-pro' ),
					'type' => 'text',
					'std' => '',
					'class' => ''
				),
				'link' => array(
					'name' => __('Link', 'inbound-pro' ),
					'desc' => __('The title link destination URL.', 'inbound-pro' ),
					'type' => 'text',
					'std' => ''
				),
				'content' => array(
					'name' => __('Teaser Content', 'inbound-pro' ),
					'desc' => __('Enter the content.', 'inbound-pro' ),
					'type' => 'textarea',
					'std' => ''
				)
			),
			'shortcode' => '[block title="{{title}}" subtitle="{{subtitle}}" icon="{{icon}}" link="{{link}}" ]{{content}}[/block]',
			'clone' => __('Add More Block',	'cta' )
		),
		'shortcode' => '[teaser heading="{{heading}}" style="{{style}}" column="{{column}}"]{{child}}[/teaser]',
		'popup_title' => 'Insert Teaser Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['teaser'] = array(
		'name' => __('Teaser', 'inbound-pro' ),
		'size' => 'one_full',
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'style' => array(
				'name' => __('Style', 'inbound-pro' ),
				'desc' => __('Select the style.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'' => __('Default', 'inbound-pro' ),
					'nested' => __('Nested', 'inbound-pro' ),
					'centered' => __('Centered', 'inbound-pro' )
				),
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'column' => array(
				'name' => __('Column', 'inbound-pro' ),
				'desc' => __('Select the column.', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'inbound-pro' ),
					'2' => __('2 Columns', 'inbound-pro' ),
					'3' => __('3 Columns', 'inbound-pro' ),
					'4' => __('4 Columns', 'inbound-pro' ),
					'5' => __('5 Columns', 'inbound-pro' )
				),
				'std' => '3',
				'class' => '',
				'is_content' => 0
			)
		),
		'child' => array(
			'icon' => array(
				'name' => __('Icon', 'inbound-pro' ),
				'desc' => __('Select an icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none',
				'class' => '',
				'is_content' => 0
			),
			'image' => array(
				'name' => __('Image URL', 'inbound-pro' ),
				'desc' => __('Enter your image url, it will override the icon above', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'title' => array(
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => 0
			),
			'subtitle' => array(
				'name' => __('Sub Title', 'inbound-pro' ),
				'desc' => __('Enter the sub title.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => 0
			),
			'link' => array(
				'name' => __('Link', 'inbound-pro' ),
				'desc' => __('The title link destination URL.', 'inbound-pro' ),
				'type' => 'text',
				'class' => '',
				'is_content' => 0
			),
			'content' => array(
				'name' => __('Content', 'inbound-pro' ),
				'desc' => __('Enter the content.', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => '',
				'class' => '',
				'is_content' => 1
			)
		),
		'child_code' => 'block'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('teaser', 'inbound_shortcode_teaser');

	function inbound_shortcode_teaser( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'heading' => '',
			'style' => '',
			'column' => '4'
		), $atts));

		$out = '';

		$grid = ' grid full';
		if ($column == '2') $grid = ' grid one-half';
		if ($column == '3') $grid = ' grid one-third';
		if ($column == '4') $grid = ' grid one-fourth';
		if ($column == '5') $grid = ' grid one-fifth';

		$style = ($style != '') ? ' '. $style : '';

		if (!preg_match_all("/(.?)\[(block)\b(.*?)(?:(\/))?\](?:(.+?)\[\/block\])?(.?)/s", $content, $matches)) {
			return do_shortcode($content);
		}
		else {

			for($i = 0; $i < count($matches[0]); $i++) {
				$matches[3][$i] = shortcode_parse_atts($matches[3][$i]);
			}

			$out .= '<div class="row">';

				if ($heading != '') {
					$out .= '<div class="grid full"><div class="heading"><h3>'.$heading.'</h3><div class="sep"></div></div></div>';
				}

				for($i = 0; $i < count($matches[0]); $i++) {
					$title = ( $matches[3][$i]['link'] ) ? '<a class="reserve" href="'. $matches[3][$i]['link'] .'">'. $matches[3][$i]['title'] .'</a>' : $matches[3][$i]['title'];

					$out .= '<aside class="teaser'. $grid . $style .'">';

						if( $matches[3][$i]['image'] ) {
							$out .= '<div class="teaser-image"><img src="'. $matches[3][$i]['image'] .'" alt="" /></div>';
						}
						elseif ( $matches[3][$i]['icon'] ) {
							$out .= '<div class="teaser-icon"><i class="icon-'. $matches[3][$i]['icon'] .'"></i></div>';
						}

						$out .= '<header class="teaser-header">';

							$out .= '<h3 class="teaser-title">'.$title.'</h3>';

							if( $matches[3][$i]['subtitle'] ) {
								$out .= '<div class="teaser-subtitle">'. $matches[3][$i]['subtitle'] .'</div>';
							}
						$out .= '</header>';

						if( $matches[5][$i] ) {
							$out .= '<div class="teaser-content">'.do_shortcode( trim($matches[5][$i]) ).'</div>';
						}
					$out .= '</aside>';
				}

				if( $i == $column - 1 ) {
					$out .= '<div class="clear"></div>';
				}

			$out .= '</div>';
		}

		return $out;
	}