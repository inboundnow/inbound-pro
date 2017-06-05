<?php
/**
*	Inbound Forms Shortcode Options
*	Forms code found in /shared/classes/form.class.php
*/

	$shortcodes_config['call-to-action'] = array(
		'no_preview' => true,
		'options' => array(
			'insert_default' => array(
						'name' => __('Insert cta', 'inbound-pro' ),
						'desc' => __('Choose CTA', 'inbound-pro' ),
						'type' => 'cta',
						'std' => '',
			),
			'align' => array(
						'name' => __('CTA Alignment', 'inbound-pro' ),
						'desc' => __('Choose Your Form Layout', 'inbound-pro' ),
						'type' => 'select',
						'options' => array(
							"none" => "None (Centered)",
							"right" => "Float Right",
							"left" => "Float Left",
							),
						'std' => 'none',
			),

		),
		'shortcode' => '[cta id="{{insert_default}}" align="{{align}}"]',
		'popup_title' => __('Insert Call to Action' , 'inbound-pro' )
	);
