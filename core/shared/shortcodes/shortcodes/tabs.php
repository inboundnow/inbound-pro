<?php
/**
*	Tabs Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['tabs'] = array(
		'no_preview' => true,
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Enter the heading text', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			)
		),
		'child' => array(
			'options' => array(
				'title' => array(
					'name' => __('Tab Title',	'leads'),
					'desc' => __('Enter the tab title.',	'leads'),
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
				'content' => array(
					'name' => __('Tab Content',	'leads'),
					'desc' => __('Put the content here.',	'leads'),
					'type' => 'textarea',
					'std' => ''
				)
			),
			'shortcode' => '[tab title="{{title}}" icon="{{icon}}"]{{content}}[/tab]',
			'clone' => __('Add More Tab',	'cta' )
		),
		'shortcode' => '[tabs]{{child}}[/tabs]',
		'popup_title' => 'Insert Tabs Shortcode'
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['tabs'] = array(
		'name' => __('Tabs', 'inbound-pro' ),
		'size' => 'one_half',
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Enter the heading text', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			)
		),
		'child' => array(
			'title' => array(
				'name' => __('Title', 'inbound-pro' ),
				'desc' => __('Enter the tab title', 'inbound-pro' ),
				'type' => 'text',
				'std' => '',
				'class' => '',
				'is_content' => 0
			),
			'icon' => array(
				'name' => __('Icon', 'inbound-pro' ),
				'desc' => __('Select an icon.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $fontawesome,
				'std' => 'none',
				'class' => '',
				'is_content' => 0
			),
			'content' => array(
				'name' => __('Content', 'inbound-pro' ),
				'desc' => __('Enter the tab content', 'inbound-pro' ),
				'type' => 'textarea',
				'class' => '',
				'is_content' => 1
			)
		),
		'child_code' => 'tab'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('tabs', 'inbound_shortcode_tabs');

	function inbound_shortcode_tabs( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'heading' => ''
		), $atts));

		$out = '';

		if (!preg_match_all("/(.?)\[(tab)\b(.*?)(?:(\/))?\](?:(.+?)\[\/tab\])?(.?)/s", $content, $matches)) {
			return do_shortcode($content);
		}
		else {

			for($i = 0; $i < count($matches[0]); $i++) {
				$matches[3][$i] = shortcode_parse_atts($matches[3][$i]);
			}

			if( $heading != '' ) $out .= '<div class="heading"><h3>'.$heading.'</h3><div class="sep"></div></div>';
			$out .= '<div class="tabs-content">';
				$out .= '<ul class="tabs-nav clearfix">';
				for($i = 0; $i < count($matches[0]); $i++) {
					$icon = ($matches[3][$i]['icon'] != '') ? '<i class="tab-icon icon-'.$matches[3][$i]['icon'].'"></i>' : '';


					$out .= '<li><a id="tab_'.$i.'_nav" title="'.$matches[3][$i]['title'].'" href="#tab_'.$i.'">'.$icon.'<span>'.$matches[3][$i]['title'].'<span></a></li>';
				}
				$out .= '</ul>';

				$out .= '<div class="tabs">';
				for($i = 0; $i < count($matches[0]); $i++) {
					$out .= '<div id="tab_'.$i.'">' . do_shortcode(trim($matches[5][$i])) .'</div>';
				}
				$out .= '</div>';
			$out .= '</div>';

			return $out;
		}
	}