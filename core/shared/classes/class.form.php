<?php
/**
 * Creates Inbound Form Shortcode
 */

if (!class_exists('Inbound_Forms')) {
    class Inbound_Forms {
        static $add_script;
        //=============================================
        // Hooks and Filters
        //=============================================
        static function init()	{

            add_shortcode('inbound_form', array(__CLASS__, 'inbound_forms_create'));
            add_shortcode('inbound_forms', array(__CLASS__, 'inbound_short_form_create'));
            add_action('init', array(__CLASS__, 'register_script'));
            add_action('wp_footer', array(__CLASS__, 'print_script'));
            add_action('wp_footer', array(__CLASS__, 'inline_my_script'));
            add_action( 'init',	array(__CLASS__, 'do_actions'));
            add_filter( 'inbound_replace_email_tokens' , array( __CLASS__ , 'replace_tokens' ) , 10 , 3 );

        }

        /* Create Longer shortcode for [inbound_form] */
        static function inbound_forms_create( $atts, $content = null ) {

            global $post;

            self::$add_script = true;

            $email = get_option('admin_email');

            extract(shortcode_atts(array(
                'id' => '',
                'name' => '',
                'layout' => '',
                'notify' => $email,
                'notify_subject' => '{{site-name}} {{form-name}} - New Lead Conversion',
                'labels' => '',
                'font_size' => '', // set default from CSS
                'width' => '',
                'redirect' => '',
                'icon' => '',
                'lists' => '',
                'submit' => 'Submit',
                'submit_colors' => '',
                'submit_text_color' => '',
                'submit_bg_color' => ''
            ), $atts));


            if ( !$id && isset($_GET['post']) ) {
                $id = $_GET['post'];
            }


            $form_name = $name;
            //$form_name = strtolower(str_replace(array(' ','_', '"', "'"),'-',$form_name));
            $form_layout = $layout;
            $form_labels = $labels;
            $form_labels_class = (isset($form_labels)) ? "inbound-label-".$form_labels : 'inbound-label-inline';
            $submit_button = ($submit != "") ? $submit : 'Submit';
            $icon_insert = ($icon != "" && $icon != 'none') ? '<i class="fa-'. $icon . '" font-awesome fa"></i>' : '';

            // Set submit button colors
            if(isset($submit_colors) && $submit_colors === 'on'){
                $submit_bg = " background:" . $submit_bg_color . "; border: 5px solid ".$submit_bg_color."; border-radius: 3px;";
                $submit_color = " color:" . $submit_text_color . ";";

            } else {
                $submit_bg = "";
                $submit_color = "";
            }

            if (preg_match("/px/", $font_size)){
                $font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
            } else if (preg_match("/%/", $font_size)) {
                $font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
            } else if (preg_match("/em/", $font_size)) {
                $font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
            } else if ($font_size == "") {
                $font_size = '';
            } else {
                $font_size = (isset($font_size)) ? " font-size:" . $font_size . "px;" : '';
            }

            // Check for image in submit button option
            if (preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i',$submit_button)) {
                $image_button = ' color: rgba(0, 0, 0, 0);border: none;box-shadow: none;background: transparent; border-radius:0px;padding: 0px;';
                $inner_button = "<img src='$submit_button' width='100%'>";
                $icon_insert = '';
                $submit_button = '';
            } else {
                $image_button = '';
                $inner_button = '';

            }

            /* Sanitize width input */
            if (preg_match('/px/i',$width)) {
                $fixed_width = str_replace("px", "", $width);
                $width_output = "width:" . $fixed_width . "px;";
            } elseif (preg_match('/%/i',$width)) {
                $fixed_width_perc = str_replace("%", "", $width);
                $width_output = "width:" . $fixed_width_perc . "%;";
            } else {
                $width_output = "width:" . $width . "px;";
            }

            $form_width = ($width != "") ? $width_output : '';

            //if (!preg_match_all("/(.?)\[(inbound_field)\b(.*?)(?:(\/))?\](?:(.+?)\[\/inbound_field\])?(.?)/s", $content, $matches)) {
            if (!preg_match_all('/(.?)\[(inbound_field)(.*?)\]/s',$content, $matches)) {

                return '';

            } else {

                for($i = 0; $i < count($matches[0]); $i++) {
                    $matches[3][$i] = shortcode_parse_atts($matches[3][$i]);
                }
                //print_r($matches[3]);
                // matches are $matches[3][$i]['label']
                $clean_form_id = preg_replace("/[^A-Za-z0-9 ]/", '', trim($name));
                $form_id = strtolower(str_replace(array(' ','_'),'-',$clean_form_id));


                $form = '<div id="inbound-form-wrapper" class="">';
                $form .= '<form class="inbound-now-form wpl-track-me inbound-track" method="post" id="'.$form_id.'" action="" style="'.$form_width.'">';
                $main_layout = ($form_layout != "") ? 'inbound-'.$form_layout : 'inbound-normal';

                for($i = 0; $i < count($matches[0]); $i++)	{

                    $label = (isset($matches[3][$i]['label'])) ? $matches[3][$i]['label'] : '';


                    $clean_label = preg_replace("/[^A-Za-z0-9 ]/", '', trim($label));
                    $formatted_label = strtolower(str_replace(array(' ','_'),'-',$clean_label));
                    $field_placeholder = (isset($matches[3][$i]['placeholder'])) ? $matches[3][$i]['placeholder'] : '';

                    $placeholder_use = ($field_placeholder != "") ? $field_placeholder : $label;

                    if ($field_placeholder != "") {
                        $form_placeholder = "placeholder='".$placeholder_use."'";
                    } else if (isset($form_labels) && $form_labels === "placeholder") {
                        $form_placeholder = "placeholder='".$placeholder_use."'";
                    } else {
                        $form_placeholder = "";
                    }

                    $description_block = (isset($matches[3][$i]['description'])) ? $matches[3][$i]['description'] : '';
                    $field_container_class = (isset($matches[3][$i]['field_container_class'])) ? $matches[3][$i]['field_container_class'] : '';
                    $field_input_class = (isset($matches[3][$i]['field_input_class'])) ? $matches[3][$i]['field_input_class'] : '';
                    $required = (isset($matches[3][$i]['required'])) ? $matches[3][$i]['required'] : '0';
                    $req = ($required === '1') ? 'required data-required="true"' : '';
                    $exclude_tracking = (isset($matches[3][$i]['exclude_tracking'])) ? $matches[3][$i]['exclude_tracking'] : '0';
                    $et_output = ($exclude_tracking === '1') ? ' data-ignore-form-field="true"' : '';
                    $req_label = ($required === '1') ? '<span class="inbound-required">*</span>' : '';
                    $map_field = (isset($matches[3][$i]['map_to'])) ? $matches[3][$i]['map_to'] : '';
                    if ($map_field != "") {
                        $field_name = $map_field;
                    } else {
                        //$label = self::santize_inputs($label);
                        $field_name = strtolower(str_replace(array(' ','_'),'-',$label));
                    }

                    $data_mapping_attr = ($map_field != "") ? ' data-map-form-field="'.$map_field.'" ' : '';

                    /* Map Common Fields */
                    (preg_match( '/Email|e-mail|email/i', $label, $email_input)) ? $email_input = " inbound-email" : $email_input = "";

                    // Match Phone
                    (preg_match( '/Phone|phone number|telephone/i', $label, $phone_input)) ? $phone_input = " inbound-phone" : $phone_input = "";

                    // match name or first name. (minus: name=, last name, last_name,)
                    (preg_match( '/(?<!((last |last_)))name(?!\=)/im', $label, $first_name_input)) ? $first_name_input = " inbound-first-name" : $first_name_input =	"";

                    // Match Last Name
                    (preg_match( '/(?<!((first)))(last name|last_name|last)(?!\=)/im', $label, $last_name_input)) ? $last_name_input = " inbound-last-name" : $last_name_input =	"";

                    $input_classes = $email_input . $first_name_input . $last_name_input . $phone_input;

                    $type = (isset($matches[3][$i]['type'])) ? $matches[3][$i]['type'] : '';
                    $show_labels = true;

                    if ($type === "hidden" || $type === "honeypot" || $type === "html-block" || $type === "divider") {
                        $show_labels = false;
                    }

                    // added by kirit dholakiya for validation of multiple checkbox
                    $div_chk_req = '';
                    if($type=='checkbox' && $required=='1') {
                        $div_chk_req =' checkbox-required ';
                    }

                    $form .= '<div class="inbound-field '.$div_chk_req.$main_layout.' label-'.$form_labels_class.' '.$form_labels_class.' '.$field_container_class.'">';

                    if ($show_labels && $form_labels != "bottom" || $type === "radio") {
                        $form .= '<label for="'. $field_name .'" class="inbound-label '.$formatted_label.' '.$form_labels_class.' inbound-input-'.$type.'" style="'.$font_size.'">' . html_entity_decode($matches[3][$i]['label']) . $req_label . '</label>';
                    }

                    if ($type === 'textarea') {
                        $form .=	'<textarea placeholder="'.$placeholder_use.'" class="inbound-input inbound-input-textarea '.$field_input_class.'" name="'.$field_name.'" id="'.$field_name.'" '.$data_mapping_attr.$et_output.' '.$req.'/></textarea>';

                    } else if ($type === 'dropdown') {

                        $dropdown_fields = array();
                        $dropdown = $matches[3][$i]['dropdown'];
                        $dropdown_fields = explode(",", $dropdown);

                        $form .= '<select name="'. $field_name .'" class="'.$field_input_class.'"'.$data_mapping_attr.$et_output.' '.$req.'>';

                        if ($placeholder_use) {
                            $form .= '<option value="" disabled selected>'.str_replace( '%3F' , '?' , $placeholder_use).'</option>';
                        }

                        foreach ($dropdown_fields as $key => $value) {
                            $drop_val_trimmed =	trim($value);
                            $dropdown_val = strtolower(str_replace(array(' ','_'),'-',$drop_val_trimmed));

                            //check for label-value separator (pipe)
                            $pos = strrpos($value, "|");

                            //if not found, use standard replacement (lowercase and spaces become dashes)
                            if ($pos === false) {
                                $form .= '<option value="'. trim(str_replace('"', '\"' , $dropdown_val)) .'">'. $drop_val_trimmed .'</option>';
                            } else {
                                //otherwise left side of separator is label, right side is value
                                $option = explode("|", $value);
                                $form .= '<option value="'. trim(str_replace('"', '\"' , trim($option[1]))) .'">'. trim($option[0]) .'</option>';
                            }
                        }
                        $form .= '</select>';

                    } else if ($type === 'dropdown_countries') {

                        $dropdown_fields = self::get_countries_array();

                        $form .= '<select name="'. $field_name .'" class="'.$field_input_class.'" '.$req.'>';

                        if ($field_placeholder) {
                            $form .= '<option value="" disabled selected>'.$field_placeholder.'</option>';
                        }

                        foreach ($dropdown_fields as $key => $value) {
                            $form .= '<option value="'.$key.'">'. utf8_encode($value) .'</option>';
                        }
                        $form .= '</select>';

                    } else if ($type === 'date-selector') {

                        $m = date('m');
                        $d = date('d');
                        $y = date('Y');

                        $months = self::get_date_selectons('months');
                        $days = self::get_date_selectons('days');
                        $years = self::get_date_selectons('years');

                        $form .= '<div class="dateSelector">';
                        $form .= '	<select id="formletMonth" name="'. $field_name .'[month]" >';
                        foreach ($months as $key => $value) {
                            ( $m == $key ) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletDays" name="'. $field_name .'[day]" >';
                        foreach ($days as $key => $value) {
                            ( $d == $key ) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletYears" name="'. $field_name .'[year]" >';
                        foreach ($years as $key => $value) {
                            ( $y == $key ) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                        }
                        $form .= '	</select>';
                        $form .= '</div>';

                    } else if ($type === 'date') {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .=	'<input class="inbound-input inbound-input-text '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" type="'.$type.'"'.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'time') {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .=	'<input class="inbound-input inbound-input-text '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" type="'.$type.'"'.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'radio') {

                        $radio_fields = array();
                        $radio = $matches[3][$i]['radio'];
                        $radio_fields = explode(",", $radio);
                        // $clean_radio = str_replace(array(' ','_'),'-',$value) // clean leading spaces. finish

                        foreach ($radio_fields as $key => $value) {
                            $radio_val_trimmed = trim($value);
                            $radio_val = strtolower(str_replace(array(' ','_'),'-',$radio_val_trimmed));

                            //check for label-value separator (pipe)
                            $pos = strrpos($value, "|");

                            //if not found, use standard replacement (lowercase and spaces become dashes)
                            if ($pos === false) {
                                $form .= '<span class="radio-'.$main_layout.' radio-'.$form_labels_class.' '.$field_input_class.'"><input type="radio" name="'. $field_name .'" value="'. $radio_val .'">'. $radio_val_trimmed .'</span>';
                            } else {
                                //otherwise left side of separator is label, right side is value
                                $option = explode("|", $value);
                                $form .= '<span class="radio-'.$main_layout.' radio-'.$form_labels_class.' '.$field_input_class.'"><input type="radio" name="'. $field_name .'" value="'. trim(str_replace('"', '\"' , trim($option[1]))) .'">'. trim($option[0]) .'</span>';
                            }

                        }

                    } else if ($type === 'checkbox') {

                        $checkbox_fields = array();

                        $checkbox = $matches[3][$i]['checkbox'];
                        $checkbox_fields = explode(",", $checkbox);
                        foreach ($checkbox_fields as $key => $value) {

                            $value = html_entity_decode($value);
                            $checkbox_val_trimmed =	trim($value);
                            $checkbox_val =	strtolower(str_replace(array(' ','_'),'-',$checkbox_val_trimmed));

                            //check for label-value separator (pipe)
                            $pos = strrpos($value, "|");

                            //if not found, use standard replacement (lowercase and spaces become dashes)
                            if ($pos === false) {
                                $form .= '<input class="checkbox-'.$main_layout.' checkbox-'.$form_labels_class.' '.$field_input_class.'" type="checkbox" name="'. $field_name .'[]" value="'. $checkbox_val .'" >'.$checkbox_val_trimmed.'<br>';
                            } else {
                                //otherwise left side of separator is label, right side is value
                                $option = explode("|", $value);
                                $form .= '<input class="checkbox-'.$main_layout.' checkbox-'.$form_labels_class.' '.$field_input_class.'" type="checkbox" name="'. $field_name .'[]" value="'. trim(str_replace('"', '\"' , trim($option[1]))) .'" >'. trim($option[0]) .'<br>';
                            }
                        }
                    } else if ($type === 'html-block') {

                        $html = $matches[3][$i]['html'];
                        //echo $html;
                        $form .= "<div class={$field_input_class}>";
                        $form .= do_shortcode(html_entity_decode($html));
                        $form .= "</div>";

                    } else if ($type === 'divider') {

                        $divider = $matches[3][$i]['divider_options'];
                        //echo $html;
                        $form .= "<div class='inbound-form-divider {$field_input_class}'>" . $divider . "<hr></div>";

                    } else if ($type === 'editor') {
                        //wp_editor(); // call wp editor
                    } else if ($type === 'honeypot') {

                        $form .= '<input type="hidden" name="stop_dirty_subs" class="stop_dirty_subs" value="">';

                    } else if ($type === 'datetime-local')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .=	'<input type="datetime-local" class="inbound-input inbound-input-datetime-local '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'url')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .=	'<input type="url" class="inbound-input inbound-input-url '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'tel')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .=	'<input type="tel" class="inbound-input inbound-input-tel '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'email')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .=	'<input type="email" class="inbound-input inbound-input-email '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'range')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .=	'<input type="range" class="inbound-input inbound-input-range '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else if ($type === 'text')  {

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $input_type = ( $email_input ) ? 'email' : 'text';
                        $form .=	'<input type="'.$input_type .'" class="inbound-input inbound-input-text '.$formatted_label . $input_classes.' '.$field_input_class.'" name="'.$field_name.'" '.$form_placeholder.' id="'.$field_name.'" value="'.$fill_value.'" '.$data_mapping_attr.$et_output.' '.$req.'/>';

                    } else {
                        $form = apply_filters('inbound_form_custom_field', $form, $matches[3][$i] , $form_id );
                    }

                    if ($show_labels && $form_labels === "bottom" && $type != "radio") {
                        $form .= '<label for="'. $field_name .'" class="inbound-label '.$formatted_label.' '.$form_labels_class.' inbound-input-'.$type.'" style="'.$font_size.'">' . $matches[3][$i]['label'] . $req_label . '</label>';
                    }

                    if ($description_block != "" && $type != 'hidden'){
                        $form .= "<div class='inbound-description'>".$description_block."</div>";
                    }

                    $form .= '</div>';
                }
                // End Loop

                $current_page =  "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $form .= '<div class="inbound-field '.$main_layout.' inbound-submit-area"><button type="submit" class="inbound-button-submit inbound-submit-action" value="'.$submit_button.'" name="send" id="inbound_form_submit" data-ignore-form-field="true" style="'.$submit_bg.$submit_color.$image_button.'">
							'.$icon_insert.''.$submit_button.$inner_button.'</button></div><input data-ignore-form-field="true" type="hidden" name="inbound_submitted" value="1">';
                // <!--<input type="submit" '.$submit_button_type.' class="button" value="'.$submit_button.'" name="send" id="inbound_form_submit" />-->

                $form .= '<input type="hidden" name="inbound_form_n" class="inbound_form_n" value="'.$form_name.'"><input type="hidden" name="inbound_form_lists" id="inbound_form_lists" value="'.$lists.'" data-map-form-field="inbound_form_lists"><input type="hidden" name="inbound_form_id" class="inbound_form_id" value="'.$id.'"><input type="hidden" name="inbound_current_page_url" value="'.$current_page.'"><input type="hidden" name="inbound_furl" value="'. base64_encode($redirect) .'"><input type="hidden" name="inbound_notify" value="'. base64_encode($notify) .'"><input type="hidden" class="inbound_params" name="inbound_params" value=""></form></div>';
                $form .= "<style type='text/css'>.inbound-button-submit{ {$font_size} }</style>";
                $form = preg_replace('/<br class="inbr".\/>/', '', $form); // remove editor br tags

                return $form;
            }
        }

        /**
         *  Sanitizes form inputs
         */
        static function santize_inputs($content) {
            // Strip HTML Tags
            $clear = strip_tags($content);
            // Clean up things like &amp;
            $clear = html_entity_decode($clear);
            // Strip out any url-encoded stuff
            $clear = urldecode($clear);
            // Replace non-AlNum characters with space
            $clear = preg_replace('/[^A-Za-z0-9]/', ' ', $clear);
            // Replace Multiple spaces with single space
            $clear = preg_replace('/ +/', ' ', $clear);
            // Trim the string of leading/trailing space
            $clear = trim($clear);
            return $clear;
        }

        /**
         *  Create shorter shortcode for [inbound_forms]
         */
        static function inbound_short_form_create( $atts, $content = null ) {
            extract(shortcode_atts(array(
                'id' => '',
            ), $atts));

            $shortcode = get_post_meta( $id, 'inbound_shortcode', TRUE );

            // If form id missing add it
            if (!preg_match('/id="/', $shortcode)) {
                $shortcode = str_replace("[inbound_form", "[inbound_form id=\"" . $id . "\"", $shortcode);
            }
            if ($id === 'default_3'){
                $shortcode = '[inbound_form name="Form Name" layout="vertical" labels="top" submit="Submit" ][inbound_field label="Email" type="text" required="1" ][/inbound_form]';
            }
            if ($id === 'default_1'){
                $shortcode = '[inbound_form name="3 Field Form" layout="vertical" labels="top" submit="Submit" ][inbound_field label="First Name" type="text" required="0" ][inbound_field label="Last Name" type="text" required="0" ][inbound_field label="Email" type="text" required="1" placeholder="Enter Your Email Address" ][/inbound_form]';
            }
            if ($id === 'default_2'){
                $shortcode = '[inbound_form name="Standard Company Form" layout="vertical" labels="top" submit="Submit" ]

							[inbound_field label="First Name" type="text" required="0" placeholder="Enter Your First Name" ]

							[inbound_field label="Last Name" type="text" required="0" placeholder="Enter Your Last Name" ]

							[inbound_field label="Email" type="text" required="1" placeholder="Enter Your Email Address" ]

							[inbound_field label="Company Name" type="text" required="0" placeholder="Enter Your Company Name" ]

							[inbound_field label="Job Title" type="text" required="0" placeholder="Enter Your Job Title" ]

							[/inbound_form]';
            }
            if (empty($shortcode)) {
                $shortcode = "Form ID: " . $id . " Not Found";
            }
            if ($id === 'none'){
                $shortcode = "";
            }

            return do_shortcode( $shortcode );
        }

        /**
         *  Enqueue JS & CSS
         */
        static function register_script() {
            wp_enqueue_style( 'inbound-shortcodes' );
        }

        /**
         * Needs more documentation
         */
        static function print_script() {
            if ( ! self::$add_script ) {
                return;
            }
            wp_enqueue_style( 'inbound-shortcodes' );
        }

        /**
         *  Needs more documentation
         */
        static function inline_my_script() {
            if ( ! self::$add_script ) {
                return;
            }
            /* TODO remove this */
            echo '<script type="text/javascript">

				jQuery(document).ready(function($){

					jQuery("form").submit(function(e) {

						// added below condition for check any of checkbox checked or not by kirit dholakiya
						if( jQuery(\'.checkbox-required\')[0] && jQuery(\'.checkbox-required input[type=checkbox]:checked\').length==0)
						{
							jQuery(\'.checkbox-required input[type=checkbox]:first\').focus();
							alert("' . __( 'Oops! Looks like you have not filled out all of the required fields!' , INBOUNDNOW_TEXT_DOMAIN ) .'");
							e.preventDefault();
							e.stopImmediatePropagation();
						}
						jQuery(this).find("input").each(function(){
							if(!jQuery(this).prop("required")){
							} else if (!jQuery(this).val()) {
							alert("' . __( 'Oops! Looks like you have not filled out all of the required fields!' , INBOUNDNOW_TEXT_DOMAIN ) .'");

							e.preventDefault();
							e.stopImmediatePropagation();
							return false;
							}
						});
					});

					jQuery("#inbound_form_submit br").remove(); // remove br tags
					function validateEmail(email) {

						var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
						return re.test(email);
					}
					var parent_redirect = parent.window.location.href;
					jQuery("#inbound_parent_page").val(parent_redirect);


					// validate email
					jQuery("input.inbound-email").on("change keyup", function (e) {
						var $this = jQuery(this);
						var email = $this.val();
						jQuery(".inbound_email_suggestion").remove();
						if (validateEmail(email)) {
							$this.css("color", "green");
							$this.addClass("inbound-valid-email");
							$this.removeClass("inbound-invalid-email");
						} else {
							$this.css("color", "red");
							$this.addClass("inbound-invalid-email");
							$this.removeClass("inbound-valid-email");
						}
						if($this.hasClass("inbound-valid-email")) {
							$this.parent().parent().find("#inbound_form_submit").removeAttr("disabled");
						}
					});

					/* Trims whitespace on advancing to the next input */
					jQuery("input[type=\'text\']").on("blur" , function() {
						var value = jQuery.trim( $(this).val() );
						jQuery(this).val( value );
					})


				});
				</script>';

        }

        /**
         *  Replaces tokens in automated email
         */
        public static function replace_tokens( $content , $form_data = null , $form_meta_data = null ) {

            /* replace core tokens */
            $content = str_replace('{{site-name}}', get_bloginfo( 'name' ) , $content);
            //$content = str_replace('{{form-name}}', $form_data['inbound_form_n']		, $content);

            foreach ($form_data as $key => $value) {
                $token_key = str_replace('_','-', $key);
                $token_key = str_replace('inbound-','', $token_key);

                $content = str_replace( '{{'.trim($token_key).'}}' , $value , $content );
            }

            return $content;
        }

        /**
         *  Stores conversion activity into form metadata
         */
        static function store_form_stats($form_id, $email) {

            //$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
            // $wordpress_date_time = date("Y-m-d G:i:s", $time);
            $form_conversion_num = get_post_meta($form_id, 'inbound_form_conversion_count', true);
            $form_conversion_num++;
            update_post_meta( $form_id, 'inbound_form_conversion_count', $form_conversion_num );

            // Add Lead Email to Conversions List
            $lead_conversion_list = get_post_meta( $form_id, 'lead_conversion_list', TRUE );
            $lead_conversion_list = json_decode($lead_conversion_list,true);
            if (is_array($lead_conversion_list)) {
                $lead_count = count($lead_conversion_list);
                $lead_conversion_list[$lead_count]['email'] = $email;
                // $lead_conversion_list[$lead_count]['date'] = $wordpress_date_time;
                $lead_conversion_list = json_encode($lead_conversion_list);
                update_post_meta( $form_id, 'lead_conversion_list', $lead_conversion_list );
            } else {
                $lead_conversion_list = array();
                $lead_conversion_list[0]['email'] = $email;
                //	$lead_conversion_list[0]['date'] = $wordpress_date_time;
                $lead_conversion_list = json_encode($lead_conversion_list);
                update_post_meta( $form_id, 'lead_conversion_list', $lead_conversion_list );
            }

        }

        /**
         *  Perform Actions After a Form Submit
         */
        static function do_actions(){

            if(isset($_POST['inbound_submitted']) && $_POST['inbound_submitted'] === '1') {
                $form_post_data = array();
                if(isset($_POST['stop_dirty_subs']) && $_POST['stop_dirty_subs'] != "") {
                    wp_die( $message = 'Die You spam bastard' );
                    return false;
                }
                /* get form submitted form's meta data */
                $form_meta_data = get_post_meta( $_POST['inbound_form_id'] );

                if(isset($_POST['inbound_furl']) && $_POST['inbound_furl'] != "") {
                    $redirect = base64_decode($_POST['inbound_furl']);
                } else if (isset($_POST['inbound_current_page_url'])) {
                    $redirect = $_POST['inbound_current_page_url'];
                }



                //print_r($_POST);
                foreach ( $_POST as $field => $value ) {

                    if ( get_magic_quotes_gpc() && is_string($value) ) {
                        $value = stripslashes( $value );
                    }

                    $field = strtolower($field);

                    if (preg_match( '/Email|e-mail|email/i', $field)) {
                        $field = "wpleads_email_address";
                        if(isset($_POST['inbound_form_id']) && $_POST['inbound_form_id'] != "") {
                            self::store_form_stats($_POST['inbound_form_id'], $value);
                        }
                    }


                    $form_post_data[$field] = (!is_array($value)) ?  strip_tags( $value ) : $value;

                }

                $form_meta_data['post_id'] = $_POST['inbound_form_id']; // pass in form id

                /* Send emails if passes spam check returns false */
                if ( !apply_filters( 'inbound_check_if_spam' , false ,  $form_post_data ) ) {
                    self::send_conversion_admin_notification($form_post_data , $form_meta_data);
                    self::send_conversion_lead_notification($form_post_data , $form_meta_data);
                }

                /* hook runs after form actions are completed and before page redirect */
                do_action('inboundnow_form_submit_actions', $form_post_data, $form_meta_data);

                /* redirect now */
                if ($redirect != "") {
                    wp_redirect( $redirect );
                    exit();
                }

            }

        }

        /**
         *  Sends Notification of New Lead Conversion to Admin & Others Listed on the Form Notification List
         */
        public static function send_conversion_admin_notification( $form_post_data , $form_meta_data ) {

            if ( $template = self::get_new_lead_email_template()) {

                add_filter( 'wp_mail_content_type', 'inbound_set_html_content_type' );
                function inbound_set_html_content_type() {
                    return 'text/html';
                }

                /* Rebuild Form Meta Data to Load Single Values	*/
                foreach( $form_meta_data as $key => $value ) {
                    if ( isset($value[0]) ) {
                        $form_meta_data[$key] = $value[0];
                    }
                }

                /* If there's no notification email in place then bail */
                if ( !isset($form_meta_data['inbound_notify_email']) ) {
                    return;
                }

                /* Get Email We Should Send Notifications To */
                $email_to = $form_meta_data['inbound_notify_email'];

                /* Check for Multiple Email Addresses */
                $addresses = explode(",", $email_to);
                if(is_array($addresses) && count($addresses) > 1) {
                    $to_address = $addresses;
                } else {
                    $to_address[] = $email_to;
                }

                /* Look for Custom Subject Line ,	Fall Back on Default */
                $subject = (isset($form_meta_data['inbound_notify_email_subject'])) ? $form_meta_data['inbound_notify_email_subject'] :	$template['subject'];

                /* Discover From Email Address */
                foreach ($form_post_data as $key => $value) {
                    if (preg_match('/email|e-mail/i', $key)) {
                        $reply_to_email = $form_post_data[$key];
                    }
                }
                $domain = get_option( 'siteurl');
                $domain = str_replace('http://', '', $domain);
                $domain = str_replace('https://', '', $domain);
                $domain = str_replace('www', '', $domain);
                $email_default = 'wordpress@' . $domain;

                /* Leave here for now
                switch( get_option('inbound_forms_enable_akismet' , 'noreply' ) ) {
                    case 'noreply':
                        BREAK;

                    case 'lead':

                        BREAK;
                }
                */

                $from_email = get_option( 'admin_email' , $email_default );
                $from_email = apply_filters( 'inbound_admin_notification_from_email' , $from_email );
                $reply_to_email = (isset($reply_to_email)) ? $reply_to_email : $from_email;
                /* Prepare Additional Data For Token Engine */
                $form_post_data['redirect_message'] = (isset($form_post_data['inbound_redirect']) && $form_post_data['inbound_redirect'] != "") ? "They were redirected to " . $form_post_data['inbound_redirect'] : '';

                /* Discover From Name */
                $from_name = get_option( 'blogname' , '' );
                $from_name = apply_filters( 'inbound_admin_notification_from_name', $from_name  );

                $Inbound_Templating_Engine = Inbound_Templating_Engine();
                $subject = $Inbound_Templating_Engine->replace_tokens( $subject, array($form_post_data, $form_meta_data));
                $body = $Inbound_Templating_Engine->replace_tokens( $template['body'] , array($form_post_data, $form_meta_data )	);

                /* Fix broken HTML tags from wp_mail garbage */
                // $body = '<tbody> <t body> <tb ody > <tbo dy> <tbod y> < t d class = "test" > < / td > ';
                $body = preg_replace("/ \>/", ">", $body);
                $body = preg_replace("/\/ /", "/", $body);
                $body = preg_replace("/\< /", "<", $body);
                $body = preg_replace("/\= /", "=", $body);
                $body = preg_replace("/ \=/", "=", $body);
                $body = preg_replace("/t d/", "td", $body);
                $body = preg_replace("/t r/", "tr", $body);
                $body = preg_replace("/t h/", "th", $body);
                $body = preg_replace("/t body/", "tbody", $body);
                $body = preg_replace("/tb ody/", "tbody", $body);
                $body = preg_replace("/tbo dy/", "tbody", $body);
                $body = preg_replace("/tbod y/", "tbody", $body);

                $headers = 'From: '. $from_name .' <'. $from_email .'>' . "\r\n";
                $headers = "Reply-To: ".$reply_to_email . "\r\n";
                $headers = apply_filters( 'inbound_lead_notification_email_headers' , $headers );

                foreach ($to_address as $key => $recipient) {
                    $result = wp_mail( $recipient, $subject, $body, $headers , apply_filters('inbound_lead_notification_attachments' , false)  );
                }

            }

        }

        /**
         *  Sends An Email to Lead After Conversion
         */
        public static function send_conversion_lead_notification( $form_post_data , $form_meta_data ) {


            /* If Notifications Are Off Then Exit */
            if ( !isset($form_meta_data['inbound_email_send_notification'][0]) || $form_meta_data['inbound_email_send_notification'][0] != 'on' ){
                return;
            }

            /* Get Lead Email Address */
            $lead_email = false;
            foreach ($form_post_data as $key => $value) {
                if (preg_match('/email|e-mail/i', $key)) {
                    $lead_email = $form_post_data[$key];
                }
            }

            /* Redundancy */
            if (!$lead_email) {
                if (isset($form_post_data['email'])) {
                    $lead_email = $form_post_data['email'];
                } else if (isset($form_post_data['e-mail'])) {
                    $lead_email = $form_post_data['e-mail'];
                } else if (isset($form_post_data['wpleads_email_address'])) {
                    $lead_email = $form_post_data['wpleads_email_address'];
                } else {
                    $lead_email = 'null map email field';
                }
            }

            if ( !$lead_email ) {
                return;
            }


            $Inbound_Templating_Engine = Inbound_Templating_Engine();
            $form_id = $form_meta_data['post_id']; //This is page id or post id
            $template_id = $form_meta_data['inbound_email_send_notification_template'][0];

            /* Rebuild Form Meta Data to Load Single Values	*/
            foreach( $form_meta_data as $key => $value ) {
                $form_meta_data[$key] = $value[0];
            }

            /* If Email Template Selected Use That */
            if ( $template_id && $template_id != 'custom' ) {

                $template_array = self::get_email_template( $template_id );
                $confirm_subject = $template_array['subject'];
                $confirm_email_message = $template_array['body'];

                /* Else Use Custom Template */
            } else {

                $template = get_post($form_id);
                $content = $template->post_content;
                $confirm_subject = get_post_meta( $form_id, 'inbound_confirmation_subject', TRUE );
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);

                $confirm_email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;' . get_option('blog_charset') . '" /></head><body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0"><table cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" border="0"><tr>';
                $confirm_email_message .= $content;
                $confirm_email_message .= '</tr></table></body></html>';
            }



            $confirm_subject = $Inbound_Templating_Engine->replace_tokens( $confirm_subject, array($form_post_data, $form_meta_data ));

            /* add default subject if empty */
            if (!$confirm_subject) {
                $confirm_subject = __( 'Thank you!' , INBOUNDNOW_TEXT_DOMAIN );
            }

            $confirm_email_message = $Inbound_Templating_Engine->replace_tokens( $confirm_email_message , array( $form_post_data, $form_meta_data )	);


            $from_name = get_option( 'blogname' , '' );
            $from_email = get_option( 'admin_email' );

            $headers	= "From: " . $from_name . " <" . $from_email . ">\n";
            $headers .= 'Content-type: text/html';

            wp_mail( $lead_email, $confirm_subject , $confirm_email_message, $headers );

        }

        /**
         *  Get Email Template for New Lead Notification
         */
        static function get_new_lead_email_template( ) {

            $email_template = array();

            $templates = get_posts(array(
                'post_type' => 'email-template',
                'posts_per_page' => 1,
                'meta_key' => '_inbound_template_id',
                'meta_value' => 'inbound-new-lead-notification'
            ));

            foreach ( $templates as $template ) {
                $email_template['ID'] = $template->ID;
                $email_template['subject'] = get_post_meta( $template->ID , 'inbound_email_subject_template' , true );
                $email_template['body'] = get_post_meta( $template->ID , 'inbound_email_body_template' , true );
            }

            return $email_template;
        }

        /**
         *  Get Email Template by ID
         */
        public static function get_email_template( $ID ) {

            $email_template = array();

            $template = get_post($ID);

            $email_template['ID'] = $template->ID;
            $email_template['subject'] = get_post_meta( $template->ID , 'inbound_email_subject_template' , true );
            $email_template['body'] = get_post_meta( $template->ID , 'inbound_email_body_template' , true );

            return $email_template;
        }

        /**
         *  Prepare an array of days, months, years. Make i18n ready
         *  @param STRING $case lets us know which array to return
         *
         *  @returns ARRAY of data
         */
        public static function get_date_selectons( $case ) {

            switch( $case ) {

                case 'months':
                    return array(
                        '01' => __( 'Jan' , INBOUNDNOW_TEXT_DOMAIN ),
                        '02' => __( 'Feb' , INBOUNDNOW_TEXT_DOMAIN ),
                        '03' => __( 'Mar' , INBOUNDNOW_TEXT_DOMAIN ),
                        '04' => __( 'Apr' , INBOUNDNOW_TEXT_DOMAIN ),
                        '05' => __( 'May' , INBOUNDNOW_TEXT_DOMAIN ),
                        '06' => __( 'Jun' , INBOUNDNOW_TEXT_DOMAIN ),
                        '07' => __( 'Jul' , INBOUNDNOW_TEXT_DOMAIN ),
                        '08' => __( 'Aug' , INBOUNDNOW_TEXT_DOMAIN ),
                        '09' => __( 'Sep' , INBOUNDNOW_TEXT_DOMAIN ),
                        '10' => __( 'Oct' , INBOUNDNOW_TEXT_DOMAIN ),
                        '11' => __( 'Nov' , INBOUNDNOW_TEXT_DOMAIN ),
                        '12' => __( 'Dec' , INBOUNDNOW_TEXT_DOMAIN )
                    );
                    break;
                case 'days' :
                    return array (
                        '01' => '01',	'02' => '02',	'03' => '03',	'04' => '04',	'05' => '05',
                        '06' => '06',	'07' => '07',	'08' => '08',	'09' => '09',	'10' => '10',
                        '11' => '11',	'12' => '12',	'13' => '13',	'14' => '14',	'15' => '15',
                        '16' => '16',	'17' => '17',	'18' => '18',	'19' => '19',	'20' => '20',
                        '21' => '21',	'22' => '22',	'23' => '23',	'24' => '24',	'25' => '25',
                        '26' => '26',	'27' => '27',	'28' => '28',	'29' => '29',	'30' => '30',
                        '31' => '31'
                    );
                    break;
                case 'years' :

                    for ($i=1920;$i<2101;$i++) {
                        $years[$i] = $i;
                    }

                    return $years;
                    break;
            }
        }

        /**
         *  Prepare an array of country codes and country names. Make i18n ready
         */
        public static function get_countries_array() {
            return array (
                __( 'AF' , 'leads') => __( 'Afghanistan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AX' , 'leads') => __( 'Aland Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AL' , 'leads') => __( 'Albania' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DZ' , 'leads') => __( 'Algeria' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AS' , 'leads') => __( 'American Samoa' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AD' , 'leads') => __( 'Andorra' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AO' , 'leads') => __( 'Angola' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AI' , 'leads') => __( 'Anguilla' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AQ' , 'leads') => __( 'Antarctica' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AG' , 'leads') => __( 'Antigua and Barbuda' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AR' , 'leads') => __( 'Argentina' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AM' , 'leads') => __( 'Armenia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AW' , 'leads') => __( 'Aruba' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AU' , 'leads') => __( 'Australia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AT' , 'leads') => __( 'Austria' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AZ' , 'leads') => __( 'Azerbaijan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BS' , 'leads') => __( 'Bahamas' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BH' , 'leads') => __( 'Bahrain' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BD' , 'leads') => __( 'Bangladesh' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BB' , 'leads') => __( 'Barbados' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BY' , 'leads') => __( 'Belarus' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BE' , 'leads') => __( 'Belgium' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BZ' , 'leads') => __( 'Belize' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BJ' , 'leads') => __( 'Benin' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BM' , 'leads') => __( 'Bermuda' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BT' , 'leads') => __( 'Bhutan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BO' , 'leads') => __( 'Bolivia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BA' , 'leads') => __( 'Bosnia and Herzegovina' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BW' , 'leads') => __( 'Botswana' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BV' , 'leads') => __( 'Bouvet Island' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BR' , 'leads') => __( 'Brazil' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IO' , 'leads') => __( 'British Indian Ocean Territory' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BN' , 'leads') => __( 'Brunei Darussalam' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BG' , 'leads') => __( 'Bulgaria' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BF' , 'leads') => __( 'Burkina Faso' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BI' , 'leads') => __( 'Burundi' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KH' , 'leads') => __( 'Cambodia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CM' , 'leads') => __( 'Cameroon' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CA' , 'leads') => __( 'Canada' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CV' , 'leads') => __( 'Cape Verde' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BQ' , 'leads') => __( 'Caribbean Netherlands ' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KY' , 'leads') => __( 'Cayman Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CF' , 'leads') => __( 'Central African Republic' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TD' , 'leads') => __( 'Chad' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CL' , 'leads') => __( 'Chile' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CN' , 'leads') => __( 'China' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CX' , 'leads') => __( 'Christmas Island' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CC' , 'leads') => __( 'Cocos (Keeling) Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CO' , 'leads') => __( 'Colombia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KM' , 'leads') => __( 'Comoros' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CG' , 'leads') => __( 'Congo' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CD' , 'leads') => __( 'Congo, Democratic Republic of' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CK' , 'leads') => __( 'Cook Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CR' , 'leads') => __( 'Costa Rica' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CI' , 'leads') => __( 'Cote d\'Ivoire' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HR' , 'leads') => __( 'Croatia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CU' , 'leads') => __( 'Cuba' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CW' , 'leads') => __( 'Curacao' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CY' , 'leads') => __( 'Cyprus' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CZ' , 'leads') => __( 'Czech Republic' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DK' , 'leads') => __( 'Denmark' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DJ' , 'leads') => __( 'Djibouti' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DM' , 'leads') => __( 'Dominica' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DO' , 'leads') => __( 'Dominican Republic' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'EC' , 'leads') => __( 'Ecuador' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'EG' , 'leads') => __( 'Egypt' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SV' , 'leads') => __( 'El Salvador' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GQ' , 'leads') => __( 'Equatorial Guinea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ER' , 'leads') => __( 'Eritrea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'EE' , 'leads') => __( 'Estonia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ET' , 'leads') => __( 'Ethiopia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FK' , 'leads') => __( 'Falkland Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FO' , 'leads') => __( 'Faroe Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FJ' , 'leads') => __( 'Fiji' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FI' , 'leads') => __( 'Finland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FR' , 'leads') => __( 'France' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GF' , 'leads') => __( 'French Guiana' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PF' , 'leads') => __( 'French Polynesia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TF' , 'leads') => __( 'French Southern Territories' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GA' , 'leads') => __( 'Gabon' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GM' , 'leads') => __( 'Gambia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GE' , 'leads') => __( 'Georgia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'DE' , 'leads') => __( 'Germany' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GH' , 'leads') => __( 'Ghana' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GI' , 'leads') => __( 'Gibraltar' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GR' , 'leads') => __( 'Greece' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GL' , 'leads') => __( 'Greenland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GD' , 'leads') => __( 'Grenada' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GP' , 'leads') => __( 'Guadeloupe' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GU' , 'leads') => __( 'Guam' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GT' , 'leads') => __( 'Guatemala' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GG' , 'leads') => __( 'Guernsey' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GN' , 'leads') => __( 'Guinea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GW' , 'leads') => __( 'Guinea-Bissau' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GY' , 'leads') => __( 'Guyana' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HT' , 'leads') => __( 'Haiti' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HM' , 'leads') => __( 'Heard and McDonald Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HN' , 'leads') => __( 'Honduras' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HK' , 'leads') => __( 'Hong Kong' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'HU' , 'leads') => __( 'Hungary' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IS' , 'leads') => __( 'Iceland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IN' , 'leads') => __( 'India' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ID' , 'leads') => __( 'Indonesia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IR' , 'leads') => __( 'Iran' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IQ' , 'leads') => __( 'Iraq' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IE' , 'leads') => __( 'Ireland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IM' , 'leads') => __( 'Isle of Man' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IL' , 'leads') => __( 'Israel' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'IT' , 'leads') => __( 'Italy' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'JM' , 'leads') => __( 'Jamaica' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'JP' , 'leads') => __( 'Japan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'JE' , 'leads') => __( 'Jersey' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'JO' , 'leads') => __( 'Jordan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KZ' , 'leads') => __( 'Kazakhstan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KE' , 'leads') => __( 'Kenya' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KI' , 'leads') => __( 'Kiribati' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KW' , 'leads') => __( 'Kuwait' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KG' , 'leads') => __( 'Kyrgyzstan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LA' , 'leads') => __( 'Lao People\'s Democratic Republic' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LV' , 'leads') => __( 'Latvia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LB' , 'leads') => __( 'Lebanon' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LS' , 'leads') => __( 'Lesotho' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LR' , 'leads') => __( 'Liberia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LY' , 'leads') => __( 'Libya' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LI' , 'leads') => __( 'Liechtenstein' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LT' , 'leads') => __( 'Lithuania' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LU' , 'leads') => __( 'Luxembourg' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MO' , 'leads') => __( 'Macau' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MK' , 'leads') => __( 'Macedonia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MG' , 'leads') => __( 'Madagascar' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MW' , 'leads') => __( 'Malawi' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MY' , 'leads') => __( 'Malaysia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MV' , 'leads') => __( 'Maldives' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ML' , 'leads') => __( 'Mali' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MT' , 'leads') => __( 'Malta' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MH' , 'leads') => __( 'Marshall Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MQ' , 'leads') => __( 'Martinique' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MR' , 'leads') => __( 'Mauritania' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MU' , 'leads') => __( 'Mauritius' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'YT' , 'leads') => __( 'Mayotte' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MX' , 'leads') => __( 'Mexico' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'FM' , 'leads') => __( 'Micronesia, Federated States of' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MD' , 'leads') => __( 'Moldova' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MC' , 'leads') => __( 'Monaco' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MN' , 'leads') => __( 'Mongolia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ME' , 'leads') => __( 'Montenegro' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MS' , 'leads') => __( 'Montserrat' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MA' , 'leads') => __( 'Morocco' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MZ' , 'leads') => __( 'Mozambique' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MM' , 'leads') => __( 'Myanmar' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NA' , 'leads') => __( 'Namibia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NR' , 'leads') => __( 'Nauru' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NP' , 'leads') => __( 'Nepal' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NC' , 'leads') => __( 'New Caledonia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NZ' , 'leads') => __( 'New Zealand' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NI' , 'leads') => __( 'Nicaragua' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NE' , 'leads') => __( 'Niger' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NG' , 'leads') => __( 'Nigeria' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NU' , 'leads') => __( 'Niue' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NF' , 'leads') => __( 'Norfolk Island' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KP' , 'leads') => __( 'North Korea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MP' , 'leads') => __( 'Northern Mariana Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NO' , 'leads') => __( 'Norway' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'OM' , 'leads') => __( 'Oman' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PK' , 'leads') => __( 'Pakistan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PW' , 'leads') => __( 'Palau' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PS' , 'leads') => __( 'Palestinian Territory, Occupied' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PA' , 'leads') => __( 'Panama' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PG' , 'leads') => __( 'Papua New Guinea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PY' , 'leads') => __( 'Paraguay' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PE' , 'leads') => __( 'Peru' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PH' , 'leads') => __( 'Philippines' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PN' , 'leads') => __( 'Pitcairn' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PL' , 'leads') => __( 'Poland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PT' , 'leads') => __( 'Portugal' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PR' , 'leads') => __( 'Puerto Rico' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'QA' , 'leads') => __( 'Qatar' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'RE' , 'leads') => __( 'Reunion' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'RO' , 'leads') => __( 'Romania' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'RU' , 'leads') => __( 'Russian Federation' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'RW' , 'leads') => __( 'Rwanda' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'BL' , 'leads') => __( 'Saint Barthelemy' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SH' , 'leads') => __( 'Saint Helena' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KN' , 'leads') => __( 'Saint Kitts and Nevis' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LC' , 'leads') => __( 'Saint Lucia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VC' , 'leads') => __( 'Saint Vincent and the Grenadines' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'MF' , 'leads') => __( 'Saint-Martin (France)' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SX' , 'leads') => __( 'Saint-Martin (Pays-Bas)' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'WS' , 'leads') => __( 'Samoa' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SM' , 'leads') => __( 'San Marino' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ST' , 'leads') => __( 'Sao Tome and Principe' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SA' , 'leads') => __( 'Saudi Arabia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SN' , 'leads') => __( 'Senegal' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'RS' , 'leads') => __( 'Serbia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SC' , 'leads') => __( 'Seychelles' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SL' , 'leads') => __( 'Sierra Leone' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SG' , 'leads') => __( 'Singapore' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SK' , 'leads') => __( 'Slovakia (Slovak Republic)' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SI' , 'leads') => __( 'Slovenia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SB' , 'leads') => __( 'Solomon Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SO' , 'leads') => __( 'Somalia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ZA' , 'leads') => __( 'South Africa' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GS' , 'leads') => __( 'South Georgia and the South Sandwich Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'KR' , 'leads') => __( 'South Korea' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SS' , 'leads') => __( 'South Sudan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ES' , 'leads') => __( 'Spain' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'LK' , 'leads') => __( 'Sri Lanka' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'PM' , 'leads') => __( 'St. Pierre and Miquelon' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SD' , 'leads') => __( 'Sudan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SR' , 'leads') => __( 'Suriname' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SJ' , 'leads') => __( 'Svalbard and Jan Mayen Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SZ' , 'leads') => __( 'Swaziland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SE' , 'leads') => __( 'Sweden' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'CH' , 'leads') => __( 'Switzerland' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'SY' , 'leads') => __( 'Syria' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TW' , 'leads') => __( 'Taiwan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TJ' , 'leads') => __( 'Tajikistan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TZ' , 'leads') => __( 'Tanzania' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TH' , 'leads') => __( 'Thailand' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'NL' , 'leads') => __( 'The Netherlands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TL' , 'leads') => __( 'Timor-Leste' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TG' , 'leads') => __( 'Togo' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TK' , 'leads') => __( 'Tokelau' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TO' , 'leads') => __( 'Tonga' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TT' , 'leads') => __( 'Trinidad and Tobago' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TN' , 'leads') => __( 'Tunisia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TR' , 'leads') => __( 'Turkey' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TM' , 'leads') => __( 'Turkmenistan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TC' , 'leads') => __( 'Turks and Caicos Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'TV' , 'leads') => __( 'Tuvalu' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'UG' , 'leads') => __( 'Uganda' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'UA' , 'leads') => __( 'Ukraine' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'AE' , 'leads') => __( 'United Arab Emirates' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'GB' , 'leads') => __( 'United Kingdom' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'US' , 'leads') => __( 'United States' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'UM' , 'leads') => __( 'United States Minor Outlying Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'UY' , 'leads') => __( 'Uruguay' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'UZ' , 'leads') => __( 'Uzbekistan' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VU' , 'leads') => __( 'Vanuatu' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VA' , 'leads') => __( 'Vatican' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VE' , 'leads') => __( 'Venezuela' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VN' , 'leads') => __( 'Vietnam' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VG' , 'leads') => __( 'Virgin Islands (British)' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'VI' , 'leads') => __( 'Virgin Islands (U.S.)' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'WF' , 'leads') => __( 'Wallis and Futuna Islands' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'EH' , 'leads') => __( 'Western Sahara' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'YE' , 'leads') => __( 'Yemen' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ZM' , 'leads') => __( 'Zambia' , INBOUNDNOW_TEXT_DOMAIN ) ,
                __( 'ZW' , 'leads') => __( 'Zimbabwe' , INBOUNDNOW_TEXT_DOMAIN )
            );
        }

        /**
         *  Gets dataset of form settings by form id
         */
        public static function get_form_settings( $form_id ) {

            $meta = get_post_meta( $form_id );
            $meta = ($meta) ? $meta : array();
            foreach ($meta as $key => $value ) {
                $meta[ $key ] = $value[0];
            }

            return $meta;
        }
    }

    Inbound_Forms::init();
}
