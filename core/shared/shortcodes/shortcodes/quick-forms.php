<?php
/**
*   Inbound Forms Shortcode Options
*   Forms code found in /shared/classes/form.class.php
*/

	$shortcodes_config['quick-forms'] = array(
		'no_preview' => false,
		'options' => array(
			'insert_default' => array(
						'name' => __('Insert Saved Form', 'inbound-pro' ),
						'desc' => __('Insert a Saved Form', 'inbound-pro' ),
						'type' => 'select',
						'options' => $form_names,
						'std' => 'none',
						'class' => 'main-form-settings',
			),
			/* Disable this to encourage ussing 'Form Management' to build and edit forms and reduce bugs 
			'helper-block-one' => array(
					'name' => __('Name Name Name',  'leads'),
					'desc' => __('<span class="switch-to-form-builder button">Build a New Form</span>',  'leads'),
					'type' => 'helper-block',
					'std' => '',
					'class' => 'helper-div',
			),
			*/
			'form_name' => array(
				'name' => __('Form Name<span class="small-required-text">*</span>', 'inbound-pro' ),
				'desc' => __('This is not shown to visitors', 'inbound-pro' ),
				'type' => 'text',
				'placeholder' => "Example: XYZ Whitepaper Download",
				'std' => '',
				'class' => 'hidden-form-settings',
			),
		),
		'shortcode' => '[inbound_forms id="{{insert_default}}" name="{{form_name}}"]',
		'popup_title' => __('Quick Insert Inbound Form Shortcode',  'leads')
	);
