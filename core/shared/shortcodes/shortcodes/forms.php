<?php
/**
 *   Inbound Forms Shortcode Options
 *   Forms code found in /shared/classes/form.class.php
 *   master code
 */

if (empty($lead_list_names)){
    // if lead transient doesn't exist use defaults
    $lead_list_names = array(
        'null' => 'No Lists detected',
    );
}

if (empty($lead_tag_names)){
    // if lead transient doesn't exist use defaults
    $lead_tag_names = array(
        'null' => 'No Lists detected',
    );
}


$shortcodes_config['forms'] = array(
    'no_preview' => false,
    'options' => array(
        'insert_default' => array(
            'name' => __('Choose Starting Template', 'inbound-pro' ),
            'desc' => __('Start Building Your Form from premade templates', 'inbound-pro' ),
            'type' => 'select',
            'options' => $form_names,
            'std' => 'none',
            'class' => 'main-form-settings',
        ),
        'form_name' => array(
            'name' => __('Form Name<span class="small-required-text">*</span>', 'inbound-pro' ),
            'desc' => __('This is for internal use and is not shown to visitors', 'inbound-pro' ),
            'type' => 'text',
            'placeholder' => "Example: XYZ Whitepaper Download",
            'std' => '',
            'class' => 'main-form-settings',
        ),
        /*'confirmation' => array(
                    'name' => __('Form Layout', 'inbound-pro' ),
                    'desc' => __('Choose Your Form Layout', 'inbound-pro' ),
                    'type' => 'select',
                    'options' => array(
                        "redirect" => "Redirect After Form Completion",
                        "text" => "Display Text on Same Page",
                        ),
                    'std' => 'redirect'
        ),*/
        'redirect' => array(
            'name' => __('Redirect URL', 'inbound-pro' ),
            'desc' => __('Where do you want to send people after they fill out the form? Please note: http:// is required. Leave blank to prevent redirection.', 'inbound-pro' ),
            'type' => 'text',
            'placeholder' => "http://www.yoursite.com/thank-you",
            'std' => '',
            'reveal_on' => 'redirect',
            'class' => 'main-form-settings',
        ),
        /*'thank_you_text' => array(
                'name' => __('Field Description <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Put field description here.',  'inbound-pro' ),
                'type' => 'textarea',
                'std' => '',
                'class' => 'advanced',
                'reveal_on' => 'text'
        ), */
        'notify' => array(
            'name' => __('Notify on Form Completions<span class="small-required-text">*</span>', 'inbound-pro' ),
            'desc' => __('Who should get admin notifications on this form?<br>For multiple notifications separate email addresses with commas', 'inbound-pro' ),
            'type' => 'text',
            'placeholder' => "youremail@email.com",
            'std' => '',
            'class' => 'main-form-settings',
        ),
        'notify_subject' => array(
            'name' => __('Admin Email Subject Line<span class="small-required-text">*</span>', 'inbound-pro' ),
            'desc' => __('Customize the subject line of email notifications arriving from this form. default: {{site-name}} {{form-name}} - New Lead Conversion', 'inbound-pro' ),
            'type' => 'text',
            'std' => "{{site-name}} {{form-name}} - New Lead Conversion",
            'palceholder' => '{{site-name}} {{form-name}} - New Lead Conversion',
            'class' => 'main-form-settings',
        ),
        'lists' => array(
            'name' => __('Add to List(s)', 'inbound-pro' ),
            'desc' => __('Add the converting lead to 1 or more lead lists', 'inbound-pro' ),
            'type' => 'leadlists',
            'options' => $lead_list_names,
            'class' => 'main-form-settings exclude-from-refresh',
        ),

        'lists_hidden' => array(
            'name' => __('Hidden List Values', 'inbound-pro' ),
            'desc' => __('Hidden list values', 'inbound-pro' ),
            'type' => 'hidden',
            'class' => 'main-form-settings exclude-from-refresh',
        ),
        'tags' => array(
            'name' => __('Add Tags to Lead', 'inbound-pro' ),
            'desc' => __('Tag the lead with these tags', 'inbound-pro' ),
            'type' => 'leadtags',
            'options' => $lead_tag_names,
            'class' => 'main-form-settings exclude-from-refresh',
        ),
        'tags_hidden' => array(
            'name' => __('Hidden Tag Values', 'inbound-pro' ),
            'desc' => __('Hidden Tag values', 'inbound-pro' ),
            'type' => 'hidden',
            'class' => 'main-form-settings exclude-from-refresh',
        ),
        'helper-block-one' => array(
            'name' => __('Name Name Name',  'inbound-pro' ),
            'desc' => __('<span class="switch-to-form-insert button">Cancel Form Creation & Insert Existing Form</span>',  'inbound-pro' ),
            'type' => 'helper-block',
            'std' => '',
            'class' => 'main-form-settings',
        ),
        'heading_design' => array(
            'name' => __('Name Name Name',  'inbound-pro' ),
            'desc' => __('Layout Options',  'inbound-pro' ),
            'type' => 'helper-block',
            'std' => '',
            'class' => 'main-design-settings',
        ),
        'layout' => array(
            'name' => __('Form Layout', 'inbound-pro' ),
            'desc' => __('Choose Your Form Layout', 'inbound-pro' ),
            'type' => 'select',
            'options' => array(
                "vertical" => "Vertical",
                "horizontal" => "Horizontal",
            ),
            'std' => 'inline',
            'class' => 'main-design-settings',
        ),
        'labels' => array(
            'name' => __('Label Alignment', 'inbound-pro' ),
            'desc' => __('Choose Label Layout', 'inbound-pro' ),
            'type' => 'select',
            'options' => array(
                "top" => "Labels on Top",
                "bottom" => "Labels on Bottom",
                "inline" => "Inline",
                "placeholder" => "Use HTML5 Placeholder text only"
            ),
            'std' => 'top',
            'class' => 'main-design-settings',
        ),
        'font-size' => array(
            'name' => __('Form Font Size', 'inbound-pro' ),
            'desc' => __('Size of Label Font. This also determines default submit button size', 'inbound-pro' ),
            'type' => 'text',
            'std' => '16',
            'class' => 'main-design-settings',
        ),
        'icon' => array(
            'name' => __('Submit Button Icon', 'inbound-pro' ),
            'desc' => __('Select an icon.', 'inbound-pro' ),
            'type' => 'select',
            'options' => $fontawesome,
            'std' => 'none',
            'class' => 'main-design-settings'
        ),
        'submit' => array(
            'name' => __('Submit Button Text', 'inbound-pro' ),
            'desc' => __('Enter the text you want to show on the submit button. (or a link to a custom submit button image)', 'inbound-pro' ),
            'type' => 'text',
            'std' => 'Submit',
            'class' => 'main-design-settings',
        ),
        'submit-colors' => array(
            'name' => __('Submit Color Options', 'inbound-pro' ),
            'desc' => __('Choose Your Form Layout', 'inbound-pro' ),
            'type' => 'select',
            'options' => array(
                "on" => "Color Options On",
                "off" => "Color Options Off (use theme defaults)",
            ),
            'std' => 'off',
            'class' => 'main-design-settings',
        ),
        'submit-text-color' => array(
            'name' => __('Button Text Color', 'inbound-pro' ),
            'desc' => __('Color of text. Must toggle on "Submit Color Options" on', 'inbound-pro' ),
            'type' => 'colorpicker',
            'std' => '#434242',
            'class' => 'main-design-settings',
        ),
        'submit-bg-color' => array(
            'name' => __('Button BG Color', 'inbound-pro' ),
            'desc' => __('Background color of button.  Must toggle on "Submit Color Options" on', 'inbound-pro' ),
            'type' => 'colorpicker',
            'std' => '#E9E9E9',
            'class' => 'main-design-settings',
        ),
        'width' => array(
            'name' => __('Custom Width', 'inbound-pro' ),
            'desc' => __('Enter in pixel width or % width. Example: 400 <u>or</u> 100%', 'inbound-pro' ),
            'type' => 'text',
            'std' => '',
            'class' => 'main-design-settings',
        ),
        'custom-class' => array(
            'name' => __('Custom Class Names', 'inbound-pro' ),
            'desc' => __('Add custom classes here ', 'inbound-pro' ),
            'type' => 'text',
            'std' => '',
            'class' => 'main-design-settings',
        ),
    ),
    'child' => array(
        'options' => array(
            'label' => array(
                'name' => __('Field Label',  'inbound-pro' ),
                'desc' => '',
                'type' => 'text',
                'std' => '',
                'placeholder' => __("Enter the Form Field Label. Example: First Name" , "leads" )
            ),
            'field_type' => array(
                'name' => __('Field Type', 'inbound-pro' ),
                'desc' => __('Select an form field type', 'inbound-pro' ),
                'type' => 'select',
                'options' => array(
                    "text" => __('Single Line Text' , 'inbound-pro' ),
                    'textarea' => __('Paragraph Field' , 'inbound-pro' ),
                    'dropdown' => __('Dropdown - Custom' , 'inbound-pro' ),
                    'dropdown_countries' => __('Dropdown - Countries' , 'inbound-pro' ),
                    'radio' => __('Radio Select' , 'inbound-pro' ),
                    'checkbox' => __('Checkbox' , 'inbound-pro' ),
                    'html-block' => __('HTML Block' , 'inbound-pro' ),
                    'divider' => __('Divider' , 'inbound-pro' ),
                    'date-selector' => __('Date Dropdown Selection' , 'inbound-pro' ),
                    'date' => __('Date Picker Field' , 'inbound-pro' ),
                    'range' => __('Range Field' , 'inbound-pro' ),
                    'time' => __('Time Pick Field' , 'inbound-pro' ),
                    'hidden' => __('Hidden Field' , 'inbound-pro' ),
                    'honeypot' => __('Anti Spam Honey Pot' , 'inbound-pro' ),
                    /*
                    'url' => __('URL' , 'inbound-pro' ),
                    'email' => __( 'Email' , 'inbound-pro' ),
                    'tel' => __( 'Telephone' , 'inbound-pro' ),
                    'datetime-local' => __('Date Time Pick Selector Field' , 'inbound-pro' ),
                    'file_upload' => __('File Upload' , 'inbound-pro' ),
                    'editor' => __('HTML Editor' ,'inbound-pro' ),
                    'multi-select' => __('multi-select' ,  'inbound-pro' )
                    */
                ),
                'std' => ''
            ),
            'map_to' => array(
                'name' => __('Mapping', 'inbound-pro' ),
                'desc' => __('This is required.', 'inbound-pro' ),
                'type' => 'select',
                'options' => $lead_mapping_fields,
                'std' => 'none',
                'class' => '',
            ),
            'dropdown_options' => array(
                'name' => __('Dropdown choices',  'inbound-pro' ),
                'desc' => __('Enter Your Dropdown Options. Separate by commas. You may also use label|value to have a different value than the label stored.',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => __('Choice 1|a, Choice 2, Choice 3' , 'inbound-pro' ),
                'reveal_on' => 'dropdown' // on select choice show this
            ),
            'radio_options' => array(
                'name' => __('Radio Choices',  'inbound-pro' ),
                'desc' => __('Enter Your Radio Options. Separate by commas. You may also use label|value to have a different value than the label stored.',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => 'Choice 1|a, Choice 2',
                'reveal_on' => 'radio' // on select choice show this
            ),
            'checkbox_options' => array(
                'name' => __('Checkbox choices',  'inbound-pro' ),
                'desc' => __('Enter Your Checkbox Options. Separate by commas. You may also use label|value to have a different value than the label stored.',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => __( 'Choice 1|a, Choice 2, Choice 3', 'inbound-pro' ),
                'reveal_on' => 'checkbox' // on select choice show this
            ),
            'html_block_options' => array(
                'name' => __('HTML Block',  'inbound-pro' ),
                'desc' => __('This is a raw HTML block in the form. Insert text/HTML',  'inbound-pro' ),
                'type' => 'textarea',
                'std' => '',
                'reveal_on' => 'html-block' // on select choice show this
            ),
            'range_options' => array(
                'name' => __('Range Setup',  'inbound-pro' ),
                'desc' => __('Enter the min, max, and steps inside the range input. Separate each with a pipe, eg: min|max|steps',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => __('0|100|10' , 'inbound-pro' ),
                'reveal_on' => 'range' // on select choice show this
            ),
            'default_value' => array(
                'name' => __('Default Value',  'inbound-pro' ),
                'desc' => __('Enter the Default Value',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => 'Enter Default Value',
                'reveal_on' => 'hidden' // on select choice show this
            ),
            'divider_options' => array(
                'name' => __('Divider Text (optional)',  'inbound-pro' ),
                'desc' => __('This is the text in the divider',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'reveal_on' => 'divider' // on select choice show this
            ),
            'required' => array(
                'name' => __('Required Field? <span class="small-optional-text">(optional)</span>', 'inbound-pro' ),
                'checkbox_text' => __('Check to make field required', 'inbound-pro' ),
                'desc' => '',
                'type' => 'checkbox',
                'std' => '0',
                'class' => '',
            ),
            'helper' => array(
                'name' => __('Field Description <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('<span class="show-advanced-fields button">Show advanced options</span>',  'inbound-pro' ),
                'type' => 'helper-block',
                'std' => '',
                'class' => '',
            ),
            'exclude_tracking' => array(
                'name' => __('Exclude Tracking? <span class="small-optional-text">(optional)</span>', 'inbound-pro' ),
                'checkbox_text' => __('Check to exclude this form field from being tracked. Note this will not store in your Database', 'inbound-pro' ),
                'desc' => '',
                'type' => 'checkbox',
                'std' => '0',
                'class' => 'advanced',
            ),
            'placeholder' => array(
                'name' => __('Field Placeholder <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Put field placeholder text here. Only works for normal text inputs',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'class' => 'advanced',
            ),
            'description' => array(
                'name' => __('Field Description <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Put field description here.',  'inbound-pro' ),
                'type' => 'textarea',
                'std' => '',
                'class' => 'advanced',
            ),
            'field_container_class' => array(
                'name' => __('Field Container Classes <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Add additional class ids to the div that contains this field. Separate classes with spaces.',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'class' => 'advanced',
            ),
            'field_input_class' => array(
                'name' => __('Field Input Classes <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Add additional class ids to this input field. Separate classes with spaces.',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'class' => 'advanced',
            ),

            'hidden_input_options' => array(
                'name' => __('Dynamic Field Filling <span class="small-optional-text">(optional)</span>',  'inbound-pro' ),
                'desc' => __('Enter Your Dynamic URL parameter',  'inbound-pro' ),
                'type' => 'text',
                'std' => '',
                'placeholder' => 'enter dynamic url parameter example: utm_campaign ',
                'class' => 'advanced',
                //'reveal_on' => 'hidden' // on select choice show this
            )
        ),
        'shortcode' => '[inbound_field label="{{label}}" type="{{field_type}}" description="{{description}}" required="{{required}}" exclude_tracking={{exclude_tracking}} dropdown="{{dropdown_options}}" radio="{{radio_options}}"  checkbox="{{checkbox_options}}"  range="{{range_options}}" placeholder="{{placeholder}}" field_container_class="{{field_container_class}}"  field_input_class="{{field_input_class}}" html="{{html_block_options}}" dynamic="{{hidden_input_options}}" default="{{default_value}}" map_to="{{map_to}}" divider_options="{{divider_options}}"]',
        'clone' => __('Add Another Field',  'inbound-pro' )
    ),
    'shortcode' => '[inbound_form name="{{form_name}}" lists="{{lists_hidden}}" tags="{{tags_hidden}}" redirect="{{redirect}}" notify="{{notify}}" notify_subject="{{notify_subject}}" layout="{{layout}}" font_size="{{font-size}}"  labels="{{labels}}" icon="{{icon}}" submit="{{submit}}" submit="{{submit}}" submit_colors="{{submit-colors}}" submit_text_color="{{submit-text-color}}" submit_bg_color="{{submit-bg-color}}" custom_class="{{custom-class}}" width="{{width}}"]{{child}}[/inbound_form]',
    'popup_title' => 'Insert Inbound Form Shortcode'
);

/* CPT Lead Lists */
add_action('init', 'inbound_forms_cpt',11);
if (!function_exists('inbound_forms_cpt')) {
    function inbound_forms_cpt() {
        //echo $slug;exit;
        $labels = array(
            'name' => _x('Inbound Forms' , 'inbound-pro'),
            'singular_name' => _x('Form' , 'inbound-pro'),
            'add_new' => _x('Add New', 'Form'),
            'add_new_item' => __('Create New Form' , 'inbound-pro'),
            'edit_item' => __('Edit Form' , 'inbound-pro'),
            'new_item' => __('New Form' , 'inbound-pro'),
            'view_item' => __('View Form' , 'inbound-pro'),
            'search_items' => __('Search Forms' , 'inbound-pro'),
            'not_found' =>  __('Nothing found' , 'inbound-pro'),
            'not_found_in_trash' => __('Nothing found in Trash' , 'inbound-pro'),
            'parent_item_colon' => ''
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'query_var' => true,
            'show_in_menu'  => true,
            'capability_type' => array('inbound-form','inbound-forms'),
            'map_meta_cap' => true,
            'hierarchical' => false,
            'menu_position' => 34,
            'supports' => array('title','custom-fields', 'editor')
        );

        register_post_type( 'inbound-forms' , $args );
        //flush_rewrite_rules( false );

        /*
        add_action('admin_menu', 'remove_list_cat_menu');
        function remove_list_cat_menu() {
            global $submenu;
            unset($submenu['edit.php?post_type=wp-lead'][15]);
            //print_r($submenu); exit;
        }*/
    }

    /**
     * Register Role Capabilities
     */
    add_action( 'admin_init' , 'inbound_register_form_role_capabilities' ,999);
    function inbound_register_form_role_capabilities() {
        // Add the roles you'd like to administer the custom post types
        $roles = array('inbound_marketer','administrator');

        // Loop through each role and assign capabilities
        foreach($roles as $the_role) {

            $role = get_role($the_role);
            if (!$role) {
                continue;
            }

            $role->add_cap( 'read' );
            $role->add_cap( 'read_inbound-form');
            $role->add_cap( 'read_private_inbound-forms' );
            $role->add_cap( 'edit_inbound-form' );
            $role->add_cap( 'edit_inbound-forms' );
            $role->add_cap( 'edit_others_inbound-forms' );
            $role->add_cap( 'edit_published_inbound-forms' );
            $role->add_cap( 'publish_inbound-form' );
            $role->add_cap( 'delete_inbound-form' );
            $role->add_cap( 'delete_inbound-forms' );
            $role->add_cap( 'delete_others_inbound-forms' );
            $role->add_cap( 'delete_private_inbound-forms' );
            $role->add_cap( 'delete_published_inbound-forms' );
        }
    }
}


if (is_admin()) {
    // Change the columns for the edit CPT screen
    add_filter( "manage_inbound-forms_posts_columns", "inbound_forms_change_columns" );
    if (!function_exists('inbound_forms_change_columns')) {
        function inbound_forms_change_columns( $cols ) {
            $cols = array(
                "cb" => "<input type=\"checkbox\" />",
                'title' => "Form Name",
                "inbound-form-shortcode" => "Shortcode",
                "inbound-form-converions" => "Conversion Count",
                "date" => "Date"
            );
            return $cols;
        }
    }

    add_action( "manage_posts_custom_column", "inbound_forms_custom_columns", 10, 2 );
    if (!function_exists('inbound_forms_custom_columns')) {
        function inbound_forms_custom_columns( $column, $post_id )
        {
            switch ( $column ) {

                case "inbound-form-shortcode":
                    $shortcode = get_post_meta( $post_id , 'inbound_shortcode', true );
                    $form_name = get_the_title( $post_id );
                    if ($shortcode == "") {
                        $shortcode = 'N/A';
                    }

                    echo '<input type="text" onclick="select()" class="regular-text code short-shortcode-input" readonly="readonly" id="shortcode" name="shortcode" value=\'[inbound_forms id="'.$post_id.'" name="'.$form_name.'"]\'>';
                    break;
                case "inbound-form-converions":
                    $count = get_post_meta( $post_id, 'inbound_form_conversion_count', true);
                    if (get_post_meta( $post_id, 'inbound_form_conversion_count', true) == "") {
                        $count = 'N/A';
                    }
                    echo $count;
                    break;
            }
        }
    }
}

add_action('admin_head', 'inbound_get_form_names',16);
if (!function_exists('inbound_get_form_names')) {
    function inbound_get_form_names() {
        global $post;

        $loop = get_transient( 'inbound-form-names' );
        if ( false === $loop ) {
            $args = array(
                'posts_per_page'  => -1,
                'post_type'=> 'inbound-forms');
            $form_list = get_posts($args);
            $form_array = array();

            foreach ( $form_list as $form ) {
                $this_id = $form->ID;
                $this_link = get_permalink( $this_id );
                $title = $form->post_title;
                $form_array['form_' . $this_id] = $title;

            }

            set_transient('inbound-form-names', $form_array, 24 * HOUR_IN_SECONDS);
        }

    }
}

add_action('save_post', 'inbound_form_delete_transient', 10, 2);
add_action('edit_post', 'inbound_form_delete_transient', 10, 2);
add_action('wp_insert_post', 'inbound_form_delete_transient', 10, 2);
if (!function_exists('inbound_form_delete_transient')) {
    // Refresh transient
    function inbound_form_delete_transient($post_id){

        if(get_post_type( $post_id ) != 'inbound-forms') {
            return;
        }

        delete_transient('inbound-form-names');
        inbound_get_form_names();

    }
}

if (!function_exists('inbound_form_save')) {
    /* 	Shortcode moved to shared form class */
    add_action('wp_ajax_inbound_form_save', 'inbound_form_save');

    function inbound_form_save() {

        global $user_ID, $wpdb;
        $check_nonce = wp_verify_nonce( $_POST['nonce'], 'inbound-shortcode-nonce' );
        if( !$check_nonce ) {
            exit;
        }

        /* Post Values */
        $form_name = (isset( $_POST['name'] )) ? $_POST['name'] : "";
        $shortcode = (isset( $_POST['shortcode'] )) ? $_POST['shortcode'] : "";
        $form_settings =  (isset( $_POST['form_settings'] )) ? $_POST['form_settings'] : "";
        $form_values =  (isset( $_POST['form_values'] )) ? $_POST['form_values'] : "";
        $field_count =  (isset( $_POST['field_count'] )) ? $_POST['field_count'] : "";
        $page_id = (isset( $_POST['post_id'] )) ? $_POST['post_id'] : "";
        $post_type = (isset( $_POST['post_type'] )) ? $_POST['post_type'] : "";
        $redirect_value = (isset( $_POST['redirect_value'] )) ? $_POST['redirect_value'] : "";
        $notify_email = (isset( $_POST['notify_email'] )) ? $_POST['notify_email'] : "";
        $notify_email_subject = (isset( $_POST['notify_email_subject'] )) ? $_POST['notify_email_subject'] : "";
        $email_contents = (isset( $_POST['email_contents'] )) ? $_POST['email_contents'] : "";
        $send_email = (isset( $_POST['send_email'] )) ? $_POST['send_email'] : "off";
        $send_email_template = (isset( $_POST['send_email_template'] )) ? $_POST['send_email_template'] : "custom";
        $send_subject = (isset( $_POST['send_subject'] )) ? $_POST['send_subject'] : "off";

        if ($post_type === 'inbound-forms'){
            $post_ID = $page_id;
            $update_post = array(
                'ID'           => $post_ID,
                'post_title'   => $form_name,
                'post_status'       => 'publish',
                'post_content' => $email_contents
            );
            wp_update_post( $update_post );
            $form_settings_data = get_post_meta( $post_ID, 'form_settings', TRUE );
            update_post_meta( $post_ID, 'inbound_form_settings', $form_settings );
            update_post_meta( $post_ID, 'inbound_form_created_on', $page_id );
            $shortcode = str_replace("[inbound_form", "[inbound_form id=\"" . $post_ID . "\"", $shortcode);
            update_post_meta( $post_ID, 'inbound_shortcode', $shortcode );
            update_post_meta( $post_ID, 'inbound_form_values', $form_values );
            update_post_meta( $post_ID, 'inbound_form_field_count', $field_count );
            update_post_meta( $post_ID, 'inbound_redirect_value', $redirect_value );
            update_post_meta( $post_ID, 'inbound_notify_email', $notify_email );
            update_post_meta( $post_ID, 'inbound_notify_email_subject', $notify_email_subject );
            update_post_meta( $post_ID, 'inbound_email_send_notification', $send_email );
            update_post_meta( $post_ID, 'inbound_email_send_notification_template', $send_email_template );
            update_post_meta( $post_ID, 'inbound_confirmation_subject', $send_subject );

            $output =  array('post_id'=> $post_ID,
                'form_name'=>$form_name,
                'redirect' => $redirect_value);

            echo json_encode($output,JSON_FORCE_OBJECT);
            wp_die();
        } else {

            // If from popup run this
            $query = $wpdb->prepare(
                'SELECT ID FROM ' . $wpdb->posts . '
	            WHERE post_title = %s
	            AND post_type = \'inbound-forms\'',
                $form_name
            );
            $wpdb->query( $query );

            // If form exists
            if ( $wpdb->num_rows ) {
                $post_ID = $wpdb->get_var( $query );

                if ($post_ID != $page_id) {
                    // if form name exists already in popup mode
                    echo json_encode("Found");
                    exit;
                } else {
                    update_post_meta( $post_ID, 'inbound_form_settings', $form_settings );
                    update_post_meta( $post_ID, 'inbound_form_created_on', $page_id );
                    update_post_meta( $post_ID, 'inbound_shortcode', $shortcode );
                    update_post_meta( $post_ID, 'inbound_form_values', $form_values );
                    update_post_meta( $post_ID, 'inbound_form_field_count', $field_count );
                    update_post_meta( $post_ID, 'inbound_redirect_value', $redirect_value );
                    update_post_meta( $post_ID, 'inbound_notify_email', $notify_email );
                    update_post_meta( $post_ID, 'inbound_notify_email_subject', $notify_email_subject );
                    update_post_meta( $post_ID, 'inbound_email_send_notification', $send_email );
                    update_post_meta( $post_ID, 'inbound_email_send_notification_template', $send_email_template );
                    update_post_meta( $post_ID, 'inbound_confirmation_subject', $send_subject );
                }

            } else {

                // If form doesn't exist create it
                $post = array(
                    'post_title'        => $form_name,
                    'post_content' => $email_contents,
                    'post_status'       => 'publish',
                    'post_type'     => 'inbound-forms',
                    'post_author'       => 1
                );

                $post_ID = wp_insert_post($post);
                update_post_meta( $post_ID, 'inbound_form_settings', $form_settings );
                update_post_meta( $post_ID, 'inbound_form_created_on', $page_id );
                update_post_meta( $post_ID, 'inbound_shortcode', $shortcode );
                update_post_meta( $post_ID, 'inbound_form_values', $form_values );
                update_post_meta( $post_ID, 'inbound_form_field_count', $field_count );
                update_post_meta( $post_ID, 'inbound_redirect_value', $redirect_value );
                update_post_meta( $post_ID, 'inbound_notify_email', $notify_email );
                update_post_meta( $post_ID, 'inbound_notify_email_subject', $notify_email_subject );
                update_post_meta( $post_ID, 'inbound_email_send_notification', $send_email );
                update_post_meta( $post_ID, 'inbound_email_send_notification_template', $send_email_template );
                update_post_meta( $post_ID, 'inbound_confirmation_subject', $send_subject );
            }
            $shortcode = str_replace("[inbound_form", "[inbound_form id=\"" . $post_ID . "\"", $shortcode);
            update_post_meta( $post_ID, 'inbound_shortcode', $shortcode );

            inbound_form_delete_transient( $post_ID );


            $output =  array('post_id'=> $post_ID,
                'form_name'=>$form_name,
                'redirect' => $redirect_value);

            echo json_encode($output,JSON_FORCE_OBJECT);
            wp_die();
        }
    }
}

add_filter( 'default_content', 'inbound_forms_default_content', 10, 2 );
if (!function_exists('inbound_forms_default_content')) {
    function inbound_forms_default_content( $content, $post ) {
        if (!isset($post))
            return;
        if( $post->post_type === 'inbound-forms' ) {

            $content = 'This is the email response. Do not use shortcodes or forms here. They will not work in this email setup component. (Delete this text)';

        }

        return $content;
    }
}

/* 	Shortcode moved to shared form class */
if (!function_exists('inbound_form_get_data')) {
    add_action('wp_ajax_inbound_form_get_data', 'inbound_form_get_data');

    function inbound_form_get_data()
    {
        // Post Values
        $post_ID = (isset( $_POST['form_id'] )) ? $_POST['form_id'] : "";

        if (isset( $_POST['form_id'])&&!empty( $_POST['form_id']))
        {
            $check_nonce = wp_verify_nonce( $_POST['nonce'], 'inbound-shortcode-nonce' );
            if( !$check_nonce ) {
                //echo json_encode("Found");
                exit;
            }

            $form_settings_data = get_post_meta( $post_ID, 'inbound_form_settings', TRUE );
            $field_count = get_post_meta( $post_ID, 'inbound_form_field_count', TRUE );
            $shortcode = get_post_meta( $post_ID, 'inbound_shortcode', TRUE );
            $inbound_form_values = get_post_meta( $post_ID, 'inbound_form_values', TRUE );


            /**get stored email response info. Mainly used when selecting a form starting template**/
            $send_email = get_post_meta( $post_ID, 'inbound_email_send_notification', true);//yes/no select send response email
            $send_email = '&inbound_email_send_notification=' . $send_email;//format the data into a string which fill_form_fields() over in shortcodes.js will use to fill in the field

            $email_template_id = get_post_meta( $post_ID, 'inbound_email_send_notification_template', true );// email template id, or 'custom' email flag

            /*if a custom email response is to be used, custom will be true*/
            if($email_template_id == 'custom'){
                $content = get_post($post_ID); //the email is contained in the post content
                $content = $content->post_content;
                $custom_email_response = '&content=' . $content;

                $custom_email_subject = get_post_meta( $post_ID, 'inbound_confirmation_subject', true ); //the subject is in the meta
                $custom_email_subject = '&inbound_confirmation_subject=' . $custom_email_subject;
            }else{
                $custom_email_response = '';
                $custom_email_subject = '';
            }

            $email_template_id = '&inbound_email_send_notification_template=' . $email_template_id;

            /*concatenate into a big string and add it to $inbound_form_values*/
            $inbound_form_values .= ($send_email . $email_template_id . $custom_email_response . $custom_email_subject);


            /*   update_post_meta( $post_ID, 'inbound_form_created_on', $page_id );
                update_post_meta( $post_ID, 'inbound_shortcode', $shortcode );
                update_post_meta( $post_ID, 'inbound_form_values', $form_values );
                update_post_meta( $post_ID, 'inbound_form_field_count', $field_count );
            */

            $output =  array('inbound_shortcode'=> $shortcode,
                'field_count'=>$field_count,
                'form_settings_data' => $form_settings_data,
                'field_values'=>$inbound_form_values);

            echo json_encode($output,JSON_FORCE_OBJECT);

        }
        wp_die();
    }
}

if (!function_exists('inbound_form_auto_publish')) {
    /* 	Shortcode moved to shared form class */
    add_action('wp_ajax_inbound_form_auto_publish', 'inbound_form_auto_publish');
    //add_action('wp_ajax_nopriv_inbound_form_auto_publish', 'inbound_form_auto_publish');

    function inbound_form_auto_publish()
    {
        // Post Values
        $post_ID = (isset( $_POST['post_id'] )) ? $_POST['post_id'] : "";
        $post_title = (isset( $_POST['post_title'] )) ? $_POST['post_title'] : "";

        if (isset( $_POST['post_id'])&&!empty( $_POST['post_id']))
        {
            // Update Post status to published immediately
            // Update post 37
            $my_post = array(
                'ID'           => $post_ID,
                'post_title'   => $post_title,
                'post_status'  => 'publish'
            );

            // Update the post into the database
            wp_update_post( $my_post );
        }
        wp_die();
    }
}

if (!function_exists('inbound_form_add_lead_list')) {

    add_action('wp_ajax_inbound_form_add_lead_list', 'inbound_form_add_lead_list');

    function inbound_form_add_lead_list()
    {
        if(isset($_POST['list_val']) && !empty($_POST['list_val'])){

            $list_title = $_POST['list_val'];

            $taxonomy = 'wplead_list_category';

            $list_parent = $_POST['list_parent_val'];

            $term_array = wp_insert_term( $list_title, $taxonomy, $args = array('parent' => $list_parent) );

            if($term_array['term_id']){

                $term_id = $term_array['term_id'];

                $term = get_term( $term_id, $taxonomy );

                $name = $term->name;

                $response_arr = array('status' => true, 'term_id' => $term_id, 'name' => $name);

            } else {

                $response_arr = array('status' => false);
            }

            echo json_encode($response_arr);

        }

        wp_die();
    }
}
