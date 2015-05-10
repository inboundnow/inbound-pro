<?php
/**
*	Testimonial Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['testimonial'] = array(
		'no_preview' => true,
		'options' => array(
			'heading' => array(
				'name' => __('Heading Text', 'leads'),
				'desc' => __('Enter the heading text.', 'leads'),
				'type' => 'text',
				'std' => 'Testimonial'
			),
			'column' => array(
				'name' => __('Column', 'leads'),
				'desc' => __('Select the number of column(s).', 'leads'),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'leads'),
					'2' => __('2 Columns', 'leads'),
					'3' => __('3 Columns', 'leads'),
					'4' => __('4 Columns', 'leads'),
					'5' => __('5 Columns', 'leads')
				),
				'std' => '1'
			)
		),
		'child' => array(
			'options' => array(
				'author' => array(
					'name' => __('Testimony Author',	'leads'),
					'desc' => __('Enter the testimony author name.',	'leads'),
					'type' => 'text',
					'std' => ''
				),
				'meta' => array(
					'name' => __('Testimony Author Meta', 'leads'),
					'desc' => __('The author job, company or website name.', 'leads'),
					'type' => 'text',
					'std' => ''
				),
				'content' => array(
					'name' => __('Testimony Content',	'leads'),
					'desc' => __('Put the content here.',	'leads'),
					'type' => 'textarea',
					'std' => ''
				)
			),
			'shortcode' => '[testimony author="{{author}}" meta="{{meta}}"]{{content}}[/testimony]',
			'clone' => __('Add More Testimony',	'cta' )
		),
		'shortcode' => '[testimonial heading="{{heading}}"	column="{{column}}"]{{child}}[/testimonial]',
		'popup_title' => 'Insert Testimonial Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['testimonial'] = array(
		'name' => __('Testimonial', 'leads'),
		'size' => 'one_half',
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'leads'),
				'desc' => __('Enter the heading text.', 'leads'),
				'type' => 'text',
				'std' => 'Testimonial',
				'class' => '',
				'is_content' => 0
			),
			'column' => array(
				'name' => __('Column', 'leads'),
				'desc' => __('Select the number of column(s).', 'leads'),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'leads'),
					'2' => __('2 Columns', 'leads'),
					'3' => __('3 Columns', 'leads'),
					'4' => __('4 Columns', 'leads'),
					'5' => __('5 Columns', 'leads')
				),
				'std' => '3',
				'class' => '',
				'is_content' => 0
			)
		),
		'child' => array(
			'author' => array(
				'name' => __('Testimony Author', 'leads'),
				'desc' => __('Enter the testimony author name.', 'leads'),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'meta' => array(
				'name' => __('Testimony Author Meta', 'leads'),
				'desc' => __('The author job, company or website name.', 'leads'),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'content' => array(
				'name' => __('Testimony Text', 'leads'),
				'desc' => __('Put the content here.', 'leads'),
				'type' => 'textarea',
				'std' => '',
				'class' => '',
				'is_content' => 1
			)
		),
		'child_code' => 'testimony'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('testimonial', 'inbound_shortcode_testimonial');

	function inbound_shortcode_testimonial( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'heading' => __('Testimonial', 'leads'),
			'column' => 1,
		), $atts));

		$grid = ' grid full';
		if ($column == '2') $grid = ' grid one-half';
		if ($column == '3') $grid = ' grid one-third';
		if ($column == '4') $grid = ' grid one-fourth';
		if ($column == '5') $grid = ' grid one-fifth';
		$out = '';


		$out .= '<div class="testimonial row">';
		if ($heading != '') {
			$out .= '<div class="grid full"><div class="heading"><h3>'.$heading.'</h3><div class="sep"></div></div></div>';
		}

		if (!preg_match_all("/(.?)\[(testimony)\b(.*?)(?:(\/))?\](?:(.+?)\[\/testimony\])?(.?)/s", $content, $matches)) {
			return do_shortcode($content);
		}
		else {

			for($i = 0; $i < count($matches[0]); $i++) {
				$matches[3][$i] = shortcode_parse_atts($matches[3][$i]);
			}

			for($i = 0; $i < count($matches[0]); $i++) {
				$out .= '<div class="'.$grid.'">';
					$out .= '<div class="fancy-quote">';
						$out .= '<div class="quote-text">';
							$out .= '<div class="triangle"></div>';
							$out .= '<p>'.do_shortcode(trim($matches[5][$i])).'</p>';
						$out .= '</div>';

						$out .= '<div class="quote-author">';
							if( $matches[3][$i]['author'] ) {
								$out .= '<span class="quote-author-name">'.$matches[3][$i]['author'].'</span>';
							}

							if( $matches[3][$i]['meta'] ){
								$out .= ' - <span class="quote-author-meta">'.$matches[3][$i]['meta'].'</span>';
							}
						$out .= '</div>';
					$out .= '</div>';
				$out .= '</div>';

				if( $i == $column - 1 ) {
					$out .= '<div class="clear"></div>';
				}
			}
		}

		$out .= '</div>';

		return $out;
	}