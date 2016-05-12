<?php

if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array (
        'key' => 'group_5733a7e9ec745',
        'title' => 'Email Smart Bar',
        'fields' => array (
            array (
                'key' => 'field_5733a7fb94506',
                'label' => 'Enable smartbar',
                'name' => 'smartbar_enable',
                'type' => 'true_false',
                'instructions' => 'Smartbar adds a customizable header bar to the public version of your email. ',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
            ),
            array (
                'key' => 'field_5733a87694507',
                'label' => 'Smartbar content',
                'name' => 'smartbar_content',
                'type' => 'wysiwyg',
                'instructions' => 'Insert a form shortcode connected to a lead list here.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array (
                'key' => 'field_5733a93d94508',
                'label' => 'Background color',
                'name' => 'smartbar_background_color',
                'type' => 'color_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#3FB7E4',
            ),
            array (
                'key' => 'field_5733ab4b30af1',
                'label' => 'Default font color',
                'name' => 'smartbar_font_color',
                'type' => 'color_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
            ),
            array (
                'key' => 'field_5733a96d94509',
                'label' => 'Smartbar CSS',
                'name' => 'smartbar_padding',
                'type' => 'wysiwyg',
                'instructions' => 'Build your smartbar CSS here.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'body nav .subscribe-prompt{
		 text-align:center;
		 padding:10px;
}

body nav .subscribe-prompt{
		 text-align:center;
		 padding:10px;
}',
                'tabs' => 'text',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
            array (
                'key' => 'field_5733aae69450a',
                'label' => 'Smartbar JS',
                'name' => 'smartbar_js',
                'type' => 'wysiwyg',
                'instructions' => 'Accepts jQuery. ',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '<script type=\'text/css\'>
</script>',
                'tabs' => 'text',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'inbound-email',
                ),
            ),
        ),
        'menu_order' => 888,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));

endif;