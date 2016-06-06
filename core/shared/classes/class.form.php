<?php
/**
 * Creates Inbound Form Shortcode
 */

if (!class_exists('Inbound_Forms')) {
    class Inbound_Forms {
        static $add_script;


        static function init() {

            add_shortcode('inbound_form', array(__CLASS__, 'inbound_forms_create'));
            add_shortcode('inbound_forms', array(__CLASS__, 'inbound_short_form_create'));
            add_action('init', array(__CLASS__, 'register_script'));
            add_action('wp_footer', array(__CLASS__, 'print_script'));
            add_action('wp_footer', array(__CLASS__, 'inline_my_script'));
            add_action('init', array(__CLASS__, 'do_actions'));
            add_filter('inbound_replace_email_tokens', array(__CLASS__, 'replace_tokens'), 10, 3);

        }

        /* Create Longer shortcode for [inbound_form] */
        static function inbound_forms_create($atts, $content = null) {

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
                'font_size' => '', /* set default from CSS */
                'width' => '',
                'redirect' => '',
                'icon' => '',
                'lists' => '',
                'submit' => 'Submit',
                'submit_colors' => '',
                'submit_text_color' => '',
                'submit_bg_color' => ''
            ), $atts));


            if (!$id && isset($_GET['post'])) {
                $id = $_GET['post'];
            }


            $form_name = $name;
            /*$form_name = strtolower(str_replace(array(' ','_', '"', "'"),'-',$form_name)); */
            $form_layout = $layout;
            $form_labels = $labels;
            $form_labels_class = (isset($form_labels)) ? "inbound-label-" . $form_labels : 'inbound-label-inline';
            $submit_button = ($submit != "") ? $submit : 'Submit';
            $icon_insert = ($icon != "" && $icon != 'none') ? '<i class="fa-' . $icon . ' font-awesome fa"></i>' : '';

            /* Set submit button colors */
            if (isset($submit_colors) && $submit_colors === 'on') {
                $submit_bg = " background:" . $submit_bg_color . "; border: 5px solid " . $submit_bg_color . "; border-radius: 3px;";
                $submit_color = " color:" . $submit_text_color . ";";

            } else {
                $submit_bg = "";
                $submit_color = "";
            }

            if (preg_match("/px/", $font_size)) {
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

            /* Check for image in submit button option */
            if (preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $submit_button)) {
                $image_button = ' color: rgba(0, 0, 0, 0);border: none;box-shadow: none;background: transparent; border-radius:0px;padding: 0px;';
                $inner_button = "<img src='$submit_button' width='100%'>";
                $icon_insert = '';
                $submit_button = '';
            } else {
                $image_button = '';
                $inner_button = '';

            }

            /* Sanitize width input */
            if (preg_match('/px/i', $width)) {
                $fixed_width = str_replace("px", "", $width);
                $width_output = "width:" . $fixed_width . "px;";
            } elseif (preg_match('/%/i', $width)) {
                $fixed_width_perc = str_replace("%", "", $width);
                $width_output = "width:" . $fixed_width_perc . "%;";
            } else {
                $width_output = "width:" . $width . "px;";
            }

            $form_width = ($width != "") ? $width_output : '';

            /*if (!preg_match_all("/(.?)\[(inbound_field)\b(.*?)(?:(\/))?\](?:(.+?)\[\/inbound_field\])?(.?)/s", $content, $matches)) { */
            if (!preg_match_all('/(.?)\[(inbound_field)(.*?)\]/s', $content, $matches)) {

                return '';

            } else {

                for ($i = 0; $i < count($matches[0]); $i++) {
                    $matches[3][$i] = shortcode_parse_atts($matches[3][$i]);
                }
                /*print_r($matches[3]); */
                /* matches are $matches[3][$i]['label'] */
                $clean_form_id = preg_replace("/[^A-Za-z0-9 ]/", '', trim($name));
                $form_id = strtolower(str_replace(array(' ', '_'), '-', $clean_form_id));


                $form = '<div id="inbound-form-wrapper" class="inbound-form-wrapper">';
                $form .= '<form class="inbound-now-form wpl-track-me inbound-track" method="post" id="' . $form_id . '" action="" style="' . $form_width . '">';
                $main_layout = ($form_layout != "") ? 'inbound-' . $form_layout : 'inbound-normal';

                for ($i = 0; $i < count($matches[0]); $i++) {

                    $label = (isset($matches[3][$i]['label'])) ? $matches[3][$i]['label'] : '';


                    $clean_label = preg_replace("/[^A-Za-z0-9 ]/", '', trim($label));
                    $formatted_label = strtolower(str_replace(array(' ', '_'), '-', $clean_label));
                    $field_placeholder = (isset($matches[3][$i]['placeholder'])) ? $matches[3][$i]['placeholder'] : '';

                    $placeholder_use = ($field_placeholder != "") ? $field_placeholder : $label;

                    if ($field_placeholder != "") {
                        $form_placeholder = "placeholder='" . $placeholder_use . "'";
                    } else if (isset($form_labels) && $form_labels === "placeholder") {
                        $form_placeholder = "placeholder='" . $placeholder_use . "'";
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
                        /*$label = self::santize_inputs($label); */
                        $field_name = strtolower(str_replace(array(' ', '_'), '-', $label));
                    }

                    $data_mapping_attr = ($map_field != "") ? ' data-map-form-field="' . $map_field . '" ' : '';

                    /* Map Common Fields */
                    (preg_match('/Email|e-mail|email/i', $label, $email_input)) ? $email_input = " inbound-email" : $email_input = "";

                    /* Match Phone */
                    (preg_match('/Phone|phone number|telephone/i', $label, $phone_input)) ? $phone_input = " inbound-phone" : $phone_input = "";

                    /* match name or first name. (minus: name=, last name, last_name,) */
                    (preg_match('/(?<!((last |last_)))name(?!\=)/im', $label, $first_name_input)) ? $first_name_input = " inbound-first-name" : $first_name_input = "";

                    /* Match Last Name */
                    (preg_match('/(?<!((first)))(last name|last_name|last)(?!\=)/im', $label, $last_name_input)) ? $last_name_input = " inbound-last-name" : $last_name_input = "";

                    $input_classes = $email_input . $first_name_input . $last_name_input . $phone_input;

                    $type = (isset($matches[3][$i]['type'])) ? $matches[3][$i]['type'] : '';
                    $show_labels = true;

                    if ($type === "hidden" || $type === "honeypot" || $type === "html-block" || $type === "divider") {
                        $show_labels = false;
                    }

                    /* added by kirit dholakiya for validation of multiple checkbox */
                    $div_chk_req = '';
                    if ($type == 'checkbox' && $required == '1') {
                        $div_chk_req = ' checkbox-required ';
                    }

                    /* prepare dynamic values if exists */
                    $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                    $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                    $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';
                    $dynamic_value = (!$dynamic_value && isset($_COOKIE[$hidden_param])) ? $_COOKIE[$hidden_param] : $dynamic_value;


                    $form .= '<div class="inbound-field ' . $div_chk_req . $main_layout . ' label-' . $form_labels_class . ' ' . $form_labels_class . ' ' . $field_container_class . '">';

                    if ($show_labels && $form_labels != "bottom" || $type === "radio") {
                        $form .= '<label for="' . $field_name . '" class="inbound-label ' . $formatted_label . ' ' . $form_labels_class . ' inbound-input-' . $type . '" style="' . $font_size . '">' . html_entity_decode($matches[3][$i]['label']) . $req_label . '</label>';
                    }

                    if ($type === 'textarea') {
                        $form .= '<textarea placeholder="' . $placeholder_use . '" class="inbound-input inbound-input-textarea ' . $field_input_class . '" name="' . $field_name . '" id="' . $field_name . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/></textarea>';

                    } else if ($type === 'dropdown') {

                        $dropdown_fields = array();
                        $dropdown = $matches[3][$i]['dropdown'];
                        $dropdown_fields = explode(",", $dropdown);

                        $form .= '<select name="' . $field_name . '" class="' . $field_input_class . '"' . $data_mapping_attr . $et_output . ' ' . $req . '>';

                        if ($placeholder_use) {
                            $form .= '<option value="" disabled selected>' . str_replace('%3F', '?', $placeholder_use) . '</option>';
                        }

                        foreach ($dropdown_fields as $key => $value) {
                            $drop_val_trimmed = trim($value);
                            $dropdown_val = strtolower(str_replace(array(' ', '_'), '-', $drop_val_trimmed));

                            /*check for label-value separator (pipe) */
                            $pos = strrpos($value, "|");

                            /*if not found, use standard replacement (lowercase and spaces become dashes) */
                            if ($pos === false) {
                                $form .= '<option value="' . trim(str_replace('"', '\"', $dropdown_val)) . '">' . $drop_val_trimmed . '</option>';
                            } else {
                                /*otherwise left side of separator is label, right side is value */
                                $option = explode("|", $value);
                                $form .= '<option value="' . trim(str_replace('"', '\"', trim($option[1]))) . '">' . trim($option[0]) . '</option>';
                            }
                        }
                        $form .= '</select>';

                    } else if ($type === 'dropdown_countries') {

                        $dropdown_fields = self::get_countries_array();

                        $form .= '<select name="' . $field_name . '" class="' . $field_input_class . '" ' . $req . '>';

                        if ($field_placeholder) {
                            $form .= '<option value="" disabled selected>' . $field_placeholder . '</option>';
                        }

                        foreach ($dropdown_fields as $key => $value) {
                            $form .= '<option value="' . $key . '">' . utf8_encode($value) . '</option>';
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
                        $form .= '	<select id="formletMonth" name="' . $field_name . '[month]" >';
                        foreach ($months as $key => $value) {
                            ($m == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletDays" name="' . $field_name . '[day]" >';
                        foreach ($days as $key => $value) {
                            ($d == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletYears" name="' . $field_name . '[year]" >';
                        foreach ($years as $key => $value) {
                            ($y == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '</div>';

                    } else if ($type === 'date') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .= '<input class="inbound-input inbound-input-text ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" type="' . $type . '"' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'time') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .= '<input class="inbound-input inbound-input-text ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" type="' . $type . '"' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'radio') {

                        $radio_fields = array();
                        $radio = $matches[3][$i]['radio'];
                        $radio_fields = explode(",", $radio);
                        /* $clean_radio = str_replace(array(' ','_'),'-',$value) /* clean leading spaces. finish */

                        foreach ($radio_fields as $key => $value) {
                            $radio_val_trimmed = trim($value);
                            $radio_val = strtolower(str_replace(array(' ', '_'), '-', $radio_val_trimmed));

                            /*check for label-value separator (pipe) */
                            $pos = strrpos($value, "|");
                            if ($required) {
                                $reqTag = "required";
                            } else {
                                $reqTag = "";
                            }
                            /*if not found, use standard replacement (lowercase and spaces become dashes) */
                            if ($pos === false) {
                                $form .= '<span class="radio-' . $main_layout . ' radio-' . $form_labels_class . ' ' . $field_input_class . '"><input type="radio" name="' . $field_name . '" value="' . $radio_val . '" ' . $reqTag . '>' . $radio_val_trimmed . '</span>';
                            } else {
                                /*otherwise left side of separator is label, right side is value */
                                $option = explode("|", $value);
                                $form .= '<span class="radio-' . $main_layout . ' radio-' . $form_labels_class . ' ' . $field_input_class . '"><input type="radio" name="' . $field_name . '" value="' . trim(str_replace('"', '\"', trim($option[1]))) . '">' . trim($option[0]) . '</span>';
                            }

                        }

                    } else if ($type === 'checkbox') {

                        $checkbox_fields = array();

                        $checkbox = $matches[3][$i]['checkbox'];
                        $checkbox_fields = explode(",", $checkbox);
                        foreach ($checkbox_fields as $key => $value) {

                            $value = html_entity_decode($value);
                            $checkbox_val_trimmed = trim($value);
                            $checkbox_val = strtolower(str_replace(array(' ', '_'), '-', $checkbox_val_trimmed));

                            /*check for label-value separator (pipe) */
                            $pos = strrpos($value, "|");

                            /*if not found, use standard replacement (lowercase and spaces become dashes) */
                            if ($pos === false) {
                                $form .= '<input class="checkbox-' . $main_layout . ' checkbox-' . $form_labels_class . ' ' . $field_input_class . '" type="checkbox" name="' . $field_name . '[]" value="' . $checkbox_val . '" >' . $checkbox_val_trimmed . '<br>';
                            } else {
                                /*otherwise left side of separator is label, right side is value */
                                $option = explode("|", $value);
                                $form .= '<input class="checkbox-' . $main_layout . ' checkbox-' . $form_labels_class . ' ' . $field_input_class . '" type="checkbox" name="' . $field_name . '[]" value="' . trim(str_replace('"', '\"', trim($option[1]))) . '" >' . trim($option[0]) . '<br>';
                            }
                        }
                    } else if ($type === 'html-block') {

                        $html = $matches[3][$i]['html'];
                        /*echo $html; */
                        $form .= "<div class={$field_input_class}>";
                        $form .= do_shortcode(html_entity_decode($html));
                        $form .= "</div>";

                    } else if ($type === 'divider') {

                        $divider = $matches[3][$i]['divider_options'];
                        /*echo $html; */
                        $form .= "<div class='inbound-form-divider {$field_input_class}'>" . $divider . "<hr></div>";

                    } else if ($type === 'editor') {
                        /*wp_editor(); /* call wp editor */
                    } else if ($type === 'honeypot') {

                        $form .= '<input type="hidden" name="stop_dirty_subs" class="stop_dirty_subs" value="">';

                    } else if ($type === 'datetime-local') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .= '<input type="datetime-local" class="inbound-input inbound-input-datetime-local ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'url') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .= '<input type="url" class="inbound-input inbound-input-url ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'tel') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .= '<input type="tel" class="inbound-input inbound-input-tel ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'email') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }
                        $form .= '<input type="email" class="inbound-input inbound-input-email ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'range') {
                        $range = $matches[3][$i]['range'];
                        $options = explode('|', $range);
                        $options[0] = (isset($options[0])) ? $options[0] : 1;
                        $options[1] = (isset($options[1])) ? $options[1] : 100;
                        $options[2] = (isset($options[2])) ? $options[2] : 1;

                        $hidden_param = (isset($matches[3][$i]['dynamic'])) ? $matches[3][$i]['dynamic'] : '';
                        $fill_value = (isset($matches[3][$i]['default'])) ? $matches[3][$i]['default'] : '';
                        $dynamic_value = (isset($_GET[$hidden_param])) ? $_GET[$hidden_param] : '';

                        $form .= '<input type="range" min="' . $options[0] . '" max="' . $options[1] . '" step="' . $options[2] . '" class="inbound-input inbound-input-range ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'text') {
                        if ($dynamic_value) {
                            $fill_value = $dynamic_value;
                        }

                        $input_type = ($email_input) ? 'email' : 'text';
                        $form .= '<input type="' . $input_type . '" class="inbound-input inbound-input-text ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'hidden') {

                        if ($dynamic_value) {
                            $fill_value = $dynamic_value;
                        }
                        $form .= '<input type="hidden" class="inbound-input inbound-input-text ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else {
                        $form = apply_filters('inbound_form_custom_field', $form, $matches[3][$i], $form_id);
                    }

                    if ($show_labels && $form_labels === "bottom" && $type != "radio") {
                        $form .= '<label for="' . $field_name . '" class="inbound-label ' . $formatted_label . ' ' . $form_labels_class . ' inbound-input-' . $type . '" style="' . $font_size . '">' . $matches[3][$i]['label'] . $req_label . '</label>';
                    }

                    if ($description_block != "" && $type != 'hidden') {
                        $form .= "<div class='inbound-description'>" . $description_block . "</div>";
                    }

                    $form .= '</div>';
                }
                /* End Loop */

                $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $form .= '<div class="inbound-field ' . $main_layout . ' inbound-submit-area"><button type="submit" class="inbound-button-submit inbound-submit-action" value="' . $submit_button . '" name="send" id="inbound_form_submit" data-ignore-form-field="true" style="' . $submit_bg . $submit_color . $image_button . '">' . $icon_insert . '' . $submit_button . $inner_button . '</button></div><input data-ignore-form-field="true" type="hidden" name="inbound_submitted" value="1">';
                /* <!--<input type="submit" '.$submit_button_type.' class="button" value="'.$submit_button.'" name="send" id="inbound_form_submit" />--> */

                $form .= '<input type="hidden" name="inbound_form_n" class="inbound_form_n" value="' . $form_name . '"><input type="hidden" name="inbound_form_lists" id="inbound_form_lists" value="' . $lists . '" data-map-form-field="inbound_form_lists"><input type="hidden" name="inbound_form_id" class="inbound_form_id" value="' . $id . '"><input type="hidden" name="inbound_current_page_url" value="' . $current_page . '"><input type="hidden" name="page_id" value="' . (isset($post->ID) ? $post->ID : '0') . '"><input type="hidden" name="inbound_furl" value="' . base64_encode($redirect) . '"><input type="hidden" name="inbound_notify" value="' . base64_encode($notify) . '"><input type="hidden" class="inbound_params" name="inbound_params" value=""></form></div>';
                $form .= "<style type='text/css'>.inbound-button-submit{ {$font_size} }</style>";
                $form = preg_replace('/<br class="inbr".\/>/', '', $form); /* remove editor br tags */

                return $form;
            }
        }

        /**
         *  Sanitizes form inputs
         */
        static function santize_inputs($content) {
            /* Strip HTML Tags */
            $clear = strip_tags($content);
            /* Clean up things like &amp; */
            $clear = html_entity_decode($clear);
            /* Strip out any url-encoded stuff */
            $clear = urldecode($clear);
            /* Replace non-AlNum characters with space */
            $clear = preg_replace('/[^A-Za-z0-9]/', ' ', $clear);
            /* Replace Multiple spaces with single space */
            $clear = preg_replace('/ +/', ' ', $clear);
            /* Trim the string of leading/trailing space */
            $clear = trim($clear);
            return $clear;
        }

        /**
         *  Create shorter shortcode for [inbound_forms]
         */
        static function inbound_short_form_create($atts, $content = null) {
            extract(shortcode_atts(array(
                'id' => '',
            ), $atts));

            $id = str_replace('form_' , '' , $id );
            $shortcode = get_post_meta($id, 'inbound_shortcode', TRUE);

            /* If form id missing add it */
            if (!preg_match('/id="/', $shortcode)) {
                $shortcode = str_replace("[inbound_form", "[inbound_form id=\"" . $id . "\"", $shortcode);
            }
            if ($id === 'default_3') {
                $shortcode = '[inbound_form name="Form Name" layout="vertical" labels="top" submit="Submit" ][inbound_field label="Email" type="text" required="1" ][/inbound_form]';
            }
            if ($id === 'default_1') {
                $shortcode = '[inbound_form name="3 Field Form" layout="vertical" labels="top" submit="Submit" ][inbound_field label="First Name" type="text" required="0" ][inbound_field label="Last Name" type="text" required="0" ][inbound_field label="Email" type="text" required="1" placeholder="Enter Your Email Address" ][/inbound_form]';
            }
            if ($id === 'default_2') {
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
            if ($id === 'none') {
                $shortcode = "";
            }

            return do_shortcode($shortcode);
        }

        /**
         *  Enqueue JS & CSS
         */
        static function register_script() {
            wp_enqueue_style('inbound-shortcodes');
        }

        /**
         * Needs more documentation
         */
        static function print_script() {
            if (!self::$add_script) {
                return;
            }
            wp_enqueue_style('inbound-shortcodes');
        }

        /**
         *  Needs more documentation
         */
        static function inline_my_script() {
            if (!self::$add_script) {
                return;
            }
            /* TODO remove this */
            echo '<script type="text/javascript">

				jQuery(document).ready(function($){

					jQuery("form").submit(function(e) {

						/* added below condition for check any of checkbox checked or not by kirit dholakiya */
						if( jQuery(\'.checkbox-required\')[0] && jQuery(\'.checkbox-required input[type=checkbox]:checked\').length==0)
						{
							jQuery(\'.checkbox-required input[type=checkbox]:first\').focus();
							alert("' . __('Oops! Looks like you have not filled out all of the required fields!', INBOUNDNOW_TEXT_DOMAIN) . '");
							e.preventDefault();
							e.stopImmediatePropagation();
						}
						jQuery(this).find("input").each(function(){
							if(!jQuery(this).prop("required")){
							} else if (!jQuery(this).val()) {
							alert("' . __('Oops! Looks like you have not filled out all of the required fields!', INBOUNDNOW_TEXT_DOMAIN) . '");

							e.preventDefault();
							e.stopImmediatePropagation();
							return false;
							}
						});
					});

					jQuery("#inbound_form_submit br").remove(); /* remove br tags */
					function validateEmail(email) {

						var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
						return re.test(email);
					}
					var parent_redirect = parent.window.location.href;
					jQuery("#inbound_parent_page").val(parent_redirect);


					/* validate email */
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
					jQuery("input[type=\'text\']").on("blur", function() {
						var value = jQuery.trim( $(this).val() );
						jQuery(this).val( value );
					})


				});
				</script>';

        }

        /**
         *  Replaces tokens in automated email
         */
        public static function replace_tokens($content, $form_data = null, $form_meta_data = null) {

            /* replace core tokens */
            $content = str_replace('{{site-name}}', get_bloginfo('name'), $content);
            $content = str_replace('{{form-name}}', $form_data['inbound_form_n'], $content);

            /* clean possible encoding issues */
            $von = array("ä", "ö", "ü", "ß", "Ä", "Ö", "Ü", "é");  //to correct double whitepaces as well
            $zu = array("&auml;", "&ouml;", "&uuml;", "&szlig;", "&Auml;", "&Ouml;", "&Uuml;", "&#233;");
            $content = str_replace($von, $zu, $content);

            foreach ($form_data as $key => $value) {
                $token_key = str_replace('_', '-', $key);
                $token_key = str_replace('inbound-', '', $token_key);

                $content = str_replace('{{' . trim($token_key) . '}}', $value, $content);
            }

            foreach ($_POST as $key => $value) {
                $token_key = str_replace('_', '-', $key);
                $token_key = str_replace('inbound-', '', $token_key);

                $content = str_replace('{{' . trim($token_key) . '}}', $value, $content);
            }


            return $content;
        }

        /**
         *  Stores conversion activity into form metadata
         */
        static function store_form_stats($form_id, $email) {

            /* $time = current_time( 'timestamp', 0 ); Current wordpress time from settings */
            /* $wordpress_date_time = date("Y-m-d G:i:s", $time); */
            $form_conversion_num = get_post_meta($form_id, 'inbound_form_conversion_count', true);
            $form_conversion_num++;
            update_post_meta($form_id, 'inbound_form_conversion_count', $form_conversion_num);

            /* Add Lead Email to Conversions List */
            $lead_conversion_list = get_post_meta($form_id, 'lead_conversion_list', TRUE);
            $lead_conversion_list = json_decode($lead_conversion_list, true);
            if (is_array($lead_conversion_list)) {
                $lead_count = count($lead_conversion_list);
                $lead_conversion_list[$lead_count]['email'] = $email;
                /* $lead_conversion_list[$lead_count]['date'] = $wordpress_date_time; */
                $lead_conversion_list = json_encode($lead_conversion_list);
                update_post_meta($form_id, 'lead_conversion_list', $lead_conversion_list);
            } else {
                $lead_conversion_list = array();
                $lead_conversion_list[0]['email'] = $email;
                /*	$lead_conversion_list[0]['date'] = $wordpress_date_time; */
                $lead_conversion_list = json_encode($lead_conversion_list);
                update_post_meta($form_id, 'lead_conversion_list', $lead_conversion_list);
            }

        }

        /**
         *  Perform Actions After a Form Submit
         */
        static function do_actions() {

            if (isset($_POST['inbound_submitted']) && $_POST['inbound_submitted'] === '1') {
                $form_post_data = array();
                if (isset($_POST['stop_dirty_subs']) && $_POST['stop_dirty_subs'] != "") {
                    wp_die($message = 'Die You spam bastard');
                    return false;
                }
                /* get form submitted form's meta data */
                $form_meta_data = get_post_meta($_POST['inbound_form_id']);

                if (isset($_POST['inbound_furl']) && $_POST['inbound_furl'] != "") {
                    $redirect = base64_decode($_POST['inbound_furl']);
                } else if (isset($_POST['inbound_current_page_url'])) {
                    $redirect = $_POST['inbound_current_page_url'];
                }


                /*print_r($_POST); */
                foreach ($_POST as $field => $value) {

                    if (get_magic_quotes_gpc() && is_string($value)) {
                        $value = stripslashes($value);
                    }

                    $field = strtolower($field);

                    if (preg_match('/Email|e-mail|email/i', $field)) {
                        $field = "wpleads_email_address";
                        if (isset($_POST['inbound_form_id']) && $_POST['inbound_form_id'] != "") {
                            self::store_form_stats($_POST['inbound_form_id'], $value);
                        }
                    }


                    $form_post_data[$field] = (!is_array($value)) ? strip_tags($value) : $value;

                }

                $form_meta_data['post_id'] = $_POST['inbound_form_id']; /* pass in form id */

                /* Send emails if passes spam check returns false */
                if (!apply_filters('inbound_check_if_spam', false, $form_post_data)) {
                    self::send_conversion_admin_notification($form_post_data, $form_meta_data);
                    self::send_conversion_lead_notification($form_post_data, $form_meta_data);

                    /* hook runs after form actions are completed and before page redirect */
                    do_action('inboundnow_form_submit_actions', $form_post_data, $form_meta_data);
                }


                /* redirect now */
                if ($redirect != "") {
                    $redirect = str_replace('%3F', '/', html_entity_decode($redirect));
                    wp_redirect($redirect);
                    exit();
                }

            }

        }

        /**
         *  Sends Notification of New Lead Conversion to Admin & Others Listed on the Form Notification List
         */
        public static function send_conversion_admin_notification($form_post_data, $form_meta_data) {

            if ($template = self::get_new_lead_email_template()) {

                add_filter('wp_mail_content_type', 'inbound_set_html_content_type');
                function inbound_set_html_content_type() {
                    return 'text/html';
                }

                /* Rebuild Form Meta Data to Load Single Values	*/
                foreach ($form_meta_data as $key => $value) {
                    if (isset($value[0])) {
                        $form_meta_data[$key] = $value[0];
                    }
                }

                /* If there's no notification email in place then bail */
                if (!isset($form_meta_data['inbound_notify_email'])) {
                    return;
                }

                /* Get Email We Should Send Notifications To */
                $email_to = $form_meta_data['inbound_notify_email'];

                /* Check for Multiple Email Addresses */
                $addresses = explode(",", $email_to);
                if (is_array($addresses) && count($addresses) > 1) {
                    $to_address = $addresses;
                } else {
                    $to_address[] = $email_to;
                }

                /* Look for Custom Subject Line ,	Fall Back on Default */
                $subject = (isset($form_meta_data['inbound_notify_email_subject'])) ? $form_meta_data['inbound_notify_email_subject'] : $template['subject'];

                /* Discover From Email Address */
                foreach ($form_post_data as $key => $value) {
                    if (preg_match('/email|e-mail/i', $key)) {
                        $reply_to_email = $form_post_data[$key];
                    }
                }
                $domain = get_option('siteurl');
                $domain = str_replace('http://', '', $domain);
                $domain = str_replace('https://', '', $domain);
                $domain = str_replace('www', '', $domain);
                $email_default = 'wordpress@' . $domain;

                /* Leave here for now
                switch( get_option('inbound_forms_enable_akismet', 'noreply' ) ) {
                    case 'noreply':
                        BREAK;

                    case 'lead':

                        BREAK;
                }
                */

                $from_email = get_option('admin_email', $email_default);
                $from_email = apply_filters('inbound_admin_notification_from_email', $from_email);
                $reply_to_email = (isset($reply_to_email)) ? $reply_to_email : $from_email;
                /* Prepare Additional Data For Token Engine */
                $form_post_data['redirect_message'] = (isset($form_post_data['inbound_redirect']) && $form_post_data['inbound_redirect'] != "") ? "They were redirected to " . $form_post_data['inbound_redirect'] : '';

                /* Discover From Name */
                $from_name = get_option('blogname', '');
                $from_name = apply_filters('inbound_admin_notification_from_name', $from_name);

                $Inbound_Templating_Engine = Inbound_Templating_Engine();
                $subject = $Inbound_Templating_Engine->replace_tokens($subject, array($form_post_data, $form_meta_data));
                $body = $Inbound_Templating_Engine->replace_tokens($template['body'], array($form_post_data, $form_meta_data));

                /* Fix broken HTML tags from wp_mail garbage */
                /* $body = '<tbody> <t body> <tb ody > <tbo dy> <tbod y> < t d class = "test" > < / td > '; */
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

                $headers = 'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n";
                $headers .= "Reply-To: " . $reply_to_email . "\r\n";
                $headers = apply_filters('inbound_email_response/headers', $headers);

                foreach ($to_address as $key => $recipient) {
                    $result = wp_mail($recipient, $subject, $body, $headers, apply_filters('inbound_lead_notification_attachments', false));
                }

            }

        }

        /**
         *  Sends An Email to Lead After Conversion
         */
        public static function send_conversion_lead_notification($form_post_data, $form_meta_data) {


            /* If Notifications Are Off Then Exit */
            if (!isset($form_meta_data['inbound_email_send_notification'][0]) || $form_meta_data['inbound_email_send_notification'][0] != 'on') {
                return;
            }

            /* Listen for Inbound Mailer takeover */
            if (apply_filters('inbound-forms/email-reponse-hijack', false, $form_meta_data, $form_post_data)) {
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

            if (!$lead_email) {
                return;
            }


            $Inbound_Templating_Engine = Inbound_Templating_Engine();
            $form_id = $form_meta_data['post_id']; /*This is page id or post id */

            /* Rebuild Form Meta Data to Load Single Values	*/
            foreach ($form_meta_data as $key => $value) {
                $form_meta_data[$key] = $value[0];
            }

            $template = get_post($form_id);
            $content = $template->post_content;
            $confirm_subject = get_post_meta($form_id, 'inbound_confirmation_subject', TRUE);
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);

            $confirm_email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;' . get_option('blog_charset') . '" /></head><body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0"><table cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" border="0"><tr>';
            $confirm_email_message .= $content;
            $confirm_email_message .= '</tr></table></body></html>';


            $confirm_subject = apply_filters('inbound_lead_conversion/subject', $confirm_subject, $form_meta_data, $form_post_data);
            $confirm_email_message = apply_filters('inbound_lead_conversion/body', $confirm_email_message, $form_meta_data, $form_post_data);

            $confirm_subject = $Inbound_Templating_Engine->replace_tokens($confirm_subject, array($form_post_data, $form_meta_data));

            /* add default subject if empty */
            if (!$confirm_subject) {
                $confirm_subject = __('Thank you!', INBOUNDNOW_TEXT_DOMAIN);
            }

            $confirm_email_message = $Inbound_Templating_Engine->replace_tokens($confirm_email_message, array($form_post_data, $form_meta_data));


            $from_name = get_option('blogname', '');
            $from_email = get_option('admin_email');

            $headers = "From: " . $from_name . " <" . $from_email . ">\n";
            $headers .= 'Content-type: text/html';
            $headers = apply_filters('inbound_lead_conversion/headers', $headers);

            wp_mail($lead_email, $confirm_subject, $confirm_email_message, $headers);

        }

        /**
         *  Get Email Template for New Lead Notification
         */
        static function get_new_lead_email_template() {

            if (get_option('inbound_admin_notification_inboundnow_link',true)) {
                $credit = '<tr>
                        <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
                          <td width="50" height="40" valign="middle" align="left" style="color:#272727">
                            <a href="http://www.inboundnow.com" target="_blank"><img src="{{leads-urlpath}}assets/images/inbound-email.png" height="40" width="40" alt=" " style="outline:none;text-decoration:none;max-width:100%;display:block;width:40px;min-height:40px;border-radius:20px"></a>
                          </td>
                        <td style="color:#272727">
                            <a style="color:#272727;text-decoration:none;" href="http://www.inboundnow.com" target="_blank">
                            ' . __('<b>Leads</b> from Inbound Now', 'inbound-pro') . '
                            </a>
                        </td>
                        <td valign="middle" align="left" style="color:#545454;text-align:right">{{date-time}}</td>
                        <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
                      </tr>';
            } else {
                $credit = '';
            }


            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html>
                    <head>
                      <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
                    <style type="text/css">
                      html {
                        background: #EEEDED;
                      }
                    </style>
                    </head>
                    <body style="margin: 0px; background-color: #FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#FFFFFF" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">

                    <table cellpadding="0" width="600" bgcolor="#FFFFFF" cellspacing="0" border="0" align="center" style="width:100%!important;line-height:100%!important;border-collapse:collapse;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
                      <tbody><tr>
                        <td valign="top" height="20">&nbsp;</td>
                      </tr>
                      <tr>
                        <td valign="top">
                          <table cellpadding="0" bgcolor="#ffffff" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px;font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;border-radius:3px;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                      <tbody><tr>
                        <td valign="top">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;width:100%;border-radius:3px 3px 0 0;font-size:1px;line-height:3px;height:3px;border-top-color:#0298e3;border-right-color:#0298e3;border-bottom-color:#0298e3;border-left-color:#0298e3;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-width:1px;border-right-width:1px;border-bottom-width:1px;border-left-width:1px">
                              <tbody><tr>
                                <td valign="top" style="font-family:Arial,sans-serif;background-color:#5ab8e7;border-top-width:1px;border-top-color:#8ccae9;border-top-style:solid" bgcolor="#5ab8e7">&nbsp;</td>
                              </tr>
                            </tbody></table>
                          <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;width:600px;border-radius:0 0 3px 3px;border-top-color:#8c8c8c;border-right-color:#8c8c8c;border-bottom-color:#8c8c8c;border-left-color:#8c8c8c;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-width:0;border-right-width:1px;border-bottom-width:1px;border-left-width:1px">
                            <tbody><tr>
                              <td valign="top" style="font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;border-radius:0 0 3px 3px;padding-top:3px;padding-right:30px;padding-bottom:15px;padding-left:30px">

                      <h1 style="margin-top:20px;margin-right:0;margin-bottom:20px;margin-left:0; font-size:28px; line-height: 28px; color:#000;"> ' . __('New Lead on {{form-name}}', 'inbound-pro') . '</h1>
                      <p style="margin-top:20px;margin-right:0;margin-bottom:20px;margin-left:0">' . __('There is a new lead that just converted on <strong>{{date-time}}</strong> from page: <a href="{{source}}">{{source}}</a> {{redirect-message}}', 'inbound-pro') . '</p>

                    <!-- NEW TABLE -->
                    <table class="heavyTable" style="width: 100%;
                        max-width: 600px;
                        border-collapse: collapse;
                        border: 1px solid #cccccc;
                        background: white;
                       margin-bottom: 20px;">
                       <tbody>
                         <tr style="background: #3A9FD1; height: 54px; font-weight: lighter; color: #fff;border: 1px solid #3A9FD1;text-align: left; padding-left: 10px;">
                                 <td  align="left" width="600" style="-webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;">
                                  <h1 style="font-size: 30px; display: inline-block;margin-top: 15px;margin-left: 10px; margin-bottom: 0px; letter-spacing: 0px; word-spacing: 0px; font-weight: 300;">' . __('Lead Information', 'inbound-pro') . '</h1>
                                  <div style="float:right; margin-top: 5px; margin-right: 15px;"><!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}" style="height:40px;v-text-anchor:middle;width:130px;font-size:18px;" arcsize="10%" stroke="f" fillcolor="#ffffff">
                                      <w:anchorlock/>
                                      <center>
                                    <![endif]-->
                                        <a href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}"
                                  style="background-color:#ffffff;border-radius:4px;color:#3A9FD1;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:130px;-webkit-text-size-adjust:none;">' . __('View Lead', 'inbound-pro') . '</a>
                                    <!--[if mso]>
                                      </center>
                                    </v:roundrect>
                                  <![endif]-->
                                  </div>
                                 </td>
                         </tr>

                         <!-- LOOP THROUGH POST PARAMS -->
                         [inbound-email-post-params]

                         <!-- END LOOP -->

                         <!-- IF CHAR COUNT OVER 50 make label display block -->

                       </tbody>
                     </table>
                     <!-- END NEW TABLE -->
                    <!-- Start 3 col -->
                    <table style="margin-bottom: 20px; border: 1px solid #cccccc; border-collapse: collapse;" width="100%" border="1" BORDERWIDTH="1" BORDERCOLOR="CCCCCC" cellspacing="0" cellpadding="5" align="left" valign="top" borderspacing="0" >

                    <tbody valign="top">
                     <tr valign="top" border="0">
                      <td width="160" height="50" align="center" valign="top" border="0">
                         <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}&tab=tabs-wpleads_lead_tab_conversions">' . __('View Lead Activity', 'inbound-pro') . '</a></h3>
                      </td>

                      <td width="160" height="50" align="center" valign="top" border="0">
                         <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}&scroll-to=wplead_metabox_conversion">' . __('Pages Viewed', 'inbound-pro') . '</a></h3>
                      </td>

                     <td width="160" height="50" align="center" valign="top" border="0">
                        <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}&tab=tabs-wpleads_lead_tab_raw_form_data">' . __('View Form Data', 'inbound-pro') . '</a></h3>
                     </td>
                     </tr>
                    </tbody></table>
                    <!-- end 3 col -->
                     <!-- Start half/half -->
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
                         <tbody><tr>
                          <td align="center" width="250" height="30" cellpadding="5">
                             <div><!--[if mso]>
                               <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}" style="height:40px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#7490af" fillcolor="#3A9FD1">
                                 <w:anchorlock/>
                                 <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">' . __('View Lead', 'inbound-pro') . '</center>
                               </v:roundrect>
                             <![endif]--><a href="{{admin-url}}edit.php?post_type=wp-lead&lead-email-redirect={{lead-email-address}}"
                             style="background-color:#3A9FD1;border:1px solid #7490af;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:250px;-webkit-text-size-adjust:none;mso-hide:all;" title="' . __('View the full Lead details in WordPress', 'inbound-pro') . '">' . __('View Full Lead Details', 'inbound-pro') . '</a>
                           </div>
                          </td>

                           <td align="center" width="250" height="30" cellpadding="5">
                             <div><!--[if mso]>
                               <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="mailto:{{lead-email-address}}?subject=RE:{{form-name}}&body=' . __('Thanks for filling out our form.', 'inbound-pro') . '" style="height:40px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#558939" fillcolor="#59b329">
                                 <w:anchorlock/>
                                 <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">' . __('Reply to Lead Now', 'inbound-pro') . '</center>
                               </v:roundrect>
                             <![endif]--><a href="mailto:{{lead-email-address}}?subject=RE:{{form-name}}&body=' . __('Thanks for filling out our form on {{current-page-url}}', 'inbound-pro') . '"
                             style="background-color:#59b329;border:1px solid #558939;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:18px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:250px;-webkit-text-size-adjust:none;mso-hide:all;" title="' . __('Email This Lead now', 'inbound-pro') . '">' . __('Reply to Lead Now', 'inbound-pro') . '</a></div>

                           </td>
                         </tr>
                       </tbody>
                     </table>
                    <!-- End half/half -->

                              </td>
                            </tr>
                          </tbody></table>
                        </td>
                      </tr>
                    </tbody></table>
                    <table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px;font-size:13px;line-height:20px;color:#545454;font-family:Arial,sans-serif;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                      <tbody><tr>
                        <td valign="top" width="30" style="color:#272727">&nbsp;</td>
                        <td valign="top" height="18" style="height:18px;color:#272727"></td>
                          <td style="color:#272727">&nbsp;</td>
                        <td style="color:#545454;text-align:right" align="right">&nbsp;</td>
                        <td valign="middle" width="30" style="color:#272727">&nbsp;</td>
                      </tr>
                      '.$credit.'
                      <tr>
                        <td valign="top" height="6" style="color:#272727;line-height:1px">&nbsp;</td>
                        <td style="color:#272727;line-height:1px">&nbsp;</td>
                          <td style="color:#272727;line-height:1px">&nbsp;</td>
                        <td style="color:#545454;text-align:right;line-height:1px" align="right">&nbsp;</td>
                        <td valign="middle" width="30" style="color:#272727;line-height:1px">&nbsp;</td>
                      </tr>
                    </tbody></table>

                          <table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse:collapse;width:600px">
                            <tbody><tr>
                              <td valign="top" style="color:#b1b1b1;font-size:11px;line-height:16px;font-family:Arial,sans-serif;text-align:center" align="center">
                                <p style="margin-top:1em;margin-right:0;margin-bottom:1em;margin-left:0"></p>
                              </td>
                            </tr>
                          </tbody></table>
                        </td>
                      </tr>
                      <tr>
                        <td valign="top" height="20">&nbsp;</td>
                      </tr>
                    </tbody></table>
                    </body>';


            $email_template['subject'] = apply_filters('inbound_new_lead_notification/subject', '');
            $email_template['body'] = apply_filters('inbound_new_lead_notification/body', $html);


            return $email_template;
        }

        /**
         *  Prepare an array of days, months, years. Make i18n ready
         * @param STRING $case lets us know which array to return
         *
         * @returns ARRAY of data
         */
        public static function get_date_selectons($case) {

            switch ($case) {

                case 'months':
                    return array(
                        '01' => __('Jan', INBOUNDNOW_TEXT_DOMAIN),
                        '02' => __('Feb', INBOUNDNOW_TEXT_DOMAIN),
                        '03' => __('Mar', INBOUNDNOW_TEXT_DOMAIN),
                        '04' => __('Apr', INBOUNDNOW_TEXT_DOMAIN),
                        '05' => __('May', INBOUNDNOW_TEXT_DOMAIN),
                        '06' => __('Jun', INBOUNDNOW_TEXT_DOMAIN),
                        '07' => __('Jul', INBOUNDNOW_TEXT_DOMAIN),
                        '08' => __('Aug', INBOUNDNOW_TEXT_DOMAIN),
                        '09' => __('Sep', INBOUNDNOW_TEXT_DOMAIN),
                        '10' => __('Oct', INBOUNDNOW_TEXT_DOMAIN),
                        '11' => __('Nov', INBOUNDNOW_TEXT_DOMAIN),
                        '12' => __('Dec', INBOUNDNOW_TEXT_DOMAIN)
                    );
                    break;
                case 'days' :
                    return array(
                        '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10',
                        '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15',
                        '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20',
                        '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25',
                        '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30',
                        '31' => '31'
                    );
                    break;
                case 'years' :

                    for ($i = 1920; $i < 2101; $i++) {
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
            return array(
                __('AF', 'inbound-pro' ) => __('Afghanistan', INBOUNDNOW_TEXT_DOMAIN),
                __('AX', 'inbound-pro' ) => __('Aland Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('AL', 'inbound-pro' ) => __('Albania', INBOUNDNOW_TEXT_DOMAIN),
                __('DZ', 'inbound-pro' ) => __('Algeria', INBOUNDNOW_TEXT_DOMAIN),
                __('AS', 'inbound-pro' ) => __('American Samoa', INBOUNDNOW_TEXT_DOMAIN),
                __('AD', 'inbound-pro' ) => __('Andorra', INBOUNDNOW_TEXT_DOMAIN),
                __('AO', 'inbound-pro' ) => __('Angola', INBOUNDNOW_TEXT_DOMAIN),
                __('AI', 'inbound-pro' ) => __('Anguilla', INBOUNDNOW_TEXT_DOMAIN),
                __('AQ', 'inbound-pro' ) => __('Antarctica', INBOUNDNOW_TEXT_DOMAIN),
                __('AG', 'inbound-pro' ) => __('Antigua and Barbuda', INBOUNDNOW_TEXT_DOMAIN),
                __('AR', 'inbound-pro' ) => __('Argentina', INBOUNDNOW_TEXT_DOMAIN),
                __('AM', 'inbound-pro' ) => __('Armenia', INBOUNDNOW_TEXT_DOMAIN),
                __('AW', 'inbound-pro' ) => __('Aruba', INBOUNDNOW_TEXT_DOMAIN),
                __('AU', 'inbound-pro' ) => __('Australia', INBOUNDNOW_TEXT_DOMAIN),
                __('AT', 'inbound-pro' ) => __('Austria', INBOUNDNOW_TEXT_DOMAIN),
                __('AZ', 'inbound-pro' ) => __('Azerbaijan', INBOUNDNOW_TEXT_DOMAIN),
                __('BS', 'inbound-pro' ) => __('Bahamas', INBOUNDNOW_TEXT_DOMAIN),
                __('BH', 'inbound-pro' ) => __('Bahrain', INBOUNDNOW_TEXT_DOMAIN),
                __('BD', 'inbound-pro' ) => __('Bangladesh', INBOUNDNOW_TEXT_DOMAIN),
                __('BB', 'inbound-pro' ) => __('Barbados', INBOUNDNOW_TEXT_DOMAIN),
                __('BY', 'inbound-pro' ) => __('Belarus', INBOUNDNOW_TEXT_DOMAIN),
                __('BE', 'inbound-pro' ) => __('Belgium', INBOUNDNOW_TEXT_DOMAIN),
                __('BZ', 'inbound-pro' ) => __('Belize', INBOUNDNOW_TEXT_DOMAIN),
                __('BJ', 'inbound-pro' ) => __('Benin', INBOUNDNOW_TEXT_DOMAIN),
                __('BM', 'inbound-pro' ) => __('Bermuda', INBOUNDNOW_TEXT_DOMAIN),
                __('BT', 'inbound-pro' ) => __('Bhutan', INBOUNDNOW_TEXT_DOMAIN),
                __('BO', 'inbound-pro' ) => __('Bolivia', INBOUNDNOW_TEXT_DOMAIN),
                __('BA', 'inbound-pro' ) => __('Bosnia and Herzegovina', INBOUNDNOW_TEXT_DOMAIN),
                __('BW', 'inbound-pro' ) => __('Botswana', INBOUNDNOW_TEXT_DOMAIN),
                __('BV', 'inbound-pro' ) => __('Bouvet Island', INBOUNDNOW_TEXT_DOMAIN),
                __('BR', 'inbound-pro' ) => __('Brazil', INBOUNDNOW_TEXT_DOMAIN),
                __('IO', 'inbound-pro' ) => __('British Indian Ocean Territory', INBOUNDNOW_TEXT_DOMAIN),
                __('BN', 'inbound-pro' ) => __('Brunei Darussalam', INBOUNDNOW_TEXT_DOMAIN),
                __('BG', 'inbound-pro' ) => __('Bulgaria', INBOUNDNOW_TEXT_DOMAIN),
                __('BF', 'inbound-pro' ) => __('Burkina Faso', INBOUNDNOW_TEXT_DOMAIN),
                __('BI', 'inbound-pro' ) => __('Burundi', INBOUNDNOW_TEXT_DOMAIN),
                __('KH', 'inbound-pro' ) => __('Cambodia', INBOUNDNOW_TEXT_DOMAIN),
                __('CM', 'inbound-pro' ) => __('Cameroon', INBOUNDNOW_TEXT_DOMAIN),
                __('CA', 'inbound-pro' ) => __('Canada', INBOUNDNOW_TEXT_DOMAIN),
                __('CV', 'inbound-pro' ) => __('Cape Verde', INBOUNDNOW_TEXT_DOMAIN),
                __('BQ', 'inbound-pro' ) => __('Caribbean Netherlands ', INBOUNDNOW_TEXT_DOMAIN),
                __('KY', 'inbound-pro' ) => __('Cayman Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('CF', 'inbound-pro' ) => __('Central African Republic', INBOUNDNOW_TEXT_DOMAIN),
                __('TD', 'inbound-pro' ) => __('Chad', INBOUNDNOW_TEXT_DOMAIN),
                __('CL', 'inbound-pro' ) => __('Chile', INBOUNDNOW_TEXT_DOMAIN),
                __('CN', 'inbound-pro' ) => __('China', INBOUNDNOW_TEXT_DOMAIN),
                __('CX', 'inbound-pro' ) => __('Christmas Island', INBOUNDNOW_TEXT_DOMAIN),
                __('CC', 'inbound-pro' ) => __('Cocos (Keeling) Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('CO', 'inbound-pro' ) => __('Colombia', INBOUNDNOW_TEXT_DOMAIN),
                __('KM', 'inbound-pro' ) => __('Comoros', INBOUNDNOW_TEXT_DOMAIN),
                __('CG', 'inbound-pro' ) => __('Congo', INBOUNDNOW_TEXT_DOMAIN),
                __('CD', 'inbound-pro' ) => __('Congo, Democratic Republic of', INBOUNDNOW_TEXT_DOMAIN),
                __('CK', 'inbound-pro' ) => __('Cook Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('CR', 'inbound-pro' ) => __('Costa Rica', INBOUNDNOW_TEXT_DOMAIN),
                __('CI', 'inbound-pro' ) => __('Cote d\'Ivoire', INBOUNDNOW_TEXT_DOMAIN),
                __('HR', 'inbound-pro' ) => __('Croatia', INBOUNDNOW_TEXT_DOMAIN),
                __('CU', 'inbound-pro' ) => __('Cuba', INBOUNDNOW_TEXT_DOMAIN),
                __('CW', 'inbound-pro' ) => __('Curacao', INBOUNDNOW_TEXT_DOMAIN),
                __('CY', 'inbound-pro' ) => __('Cyprus', INBOUNDNOW_TEXT_DOMAIN),
                __('CZ', 'inbound-pro' ) => __('Czech Republic', INBOUNDNOW_TEXT_DOMAIN),
                __('DK', 'inbound-pro' ) => __('Denmark', INBOUNDNOW_TEXT_DOMAIN),
                __('DJ', 'inbound-pro' ) => __('Djibouti', INBOUNDNOW_TEXT_DOMAIN),
                __('DM', 'inbound-pro' ) => __('Dominica', INBOUNDNOW_TEXT_DOMAIN),
                __('DO', 'inbound-pro' ) => __('Dominican Republic', INBOUNDNOW_TEXT_DOMAIN),
                __('EC', 'inbound-pro' ) => __('Ecuador', INBOUNDNOW_TEXT_DOMAIN),
                __('EG', 'inbound-pro' ) => __('Egypt', INBOUNDNOW_TEXT_DOMAIN),
                __('SV', 'inbound-pro' ) => __('El Salvador', INBOUNDNOW_TEXT_DOMAIN),
                __('GQ', 'inbound-pro' ) => __('Equatorial Guinea', INBOUNDNOW_TEXT_DOMAIN),
                __('ER', 'inbound-pro' ) => __('Eritrea', INBOUNDNOW_TEXT_DOMAIN),
                __('EE', 'inbound-pro' ) => __('Estonia', INBOUNDNOW_TEXT_DOMAIN),
                __('ET', 'inbound-pro' ) => __('Ethiopia', INBOUNDNOW_TEXT_DOMAIN),
                __('FK', 'inbound-pro' ) => __('Falkland Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('FO', 'inbound-pro' ) => __('Faroe Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('FJ', 'inbound-pro' ) => __('Fiji', INBOUNDNOW_TEXT_DOMAIN),
                __('FI', 'inbound-pro' ) => __('Finland', INBOUNDNOW_TEXT_DOMAIN),
                __('FR', 'inbound-pro' ) => __('France', INBOUNDNOW_TEXT_DOMAIN),
                __('GF', 'inbound-pro' ) => __('French Guiana', INBOUNDNOW_TEXT_DOMAIN),
                __('PF', 'inbound-pro' ) => __('French Polynesia', INBOUNDNOW_TEXT_DOMAIN),
                __('TF', 'inbound-pro' ) => __('French Southern Territories', INBOUNDNOW_TEXT_DOMAIN),
                __('GA', 'inbound-pro' ) => __('Gabon', INBOUNDNOW_TEXT_DOMAIN),
                __('GM', 'inbound-pro' ) => __('Gambia', INBOUNDNOW_TEXT_DOMAIN),
                __('GE', 'inbound-pro' ) => __('Georgia', INBOUNDNOW_TEXT_DOMAIN),
                __('DE', 'inbound-pro' ) => __('Germany', INBOUNDNOW_TEXT_DOMAIN),
                __('GH', 'inbound-pro' ) => __('Ghana', INBOUNDNOW_TEXT_DOMAIN),
                __('GI', 'inbound-pro' ) => __('Gibraltar', INBOUNDNOW_TEXT_DOMAIN),
                __('GR', 'inbound-pro' ) => __('Greece', INBOUNDNOW_TEXT_DOMAIN),
                __('GL', 'inbound-pro' ) => __('Greenland', INBOUNDNOW_TEXT_DOMAIN),
                __('GD', 'inbound-pro' ) => __('Grenada', INBOUNDNOW_TEXT_DOMAIN),
                __('GP', 'inbound-pro' ) => __('Guadeloupe', INBOUNDNOW_TEXT_DOMAIN),
                __('GU', 'inbound-pro' ) => __('Guam', INBOUNDNOW_TEXT_DOMAIN),
                __('GT', 'inbound-pro' ) => __('Guatemala', INBOUNDNOW_TEXT_DOMAIN),
                __('GG', 'inbound-pro' ) => __('Guernsey', INBOUNDNOW_TEXT_DOMAIN),
                __('GN', 'inbound-pro' ) => __('Guinea', INBOUNDNOW_TEXT_DOMAIN),
                __('GW', 'inbound-pro' ) => __('Guinea-Bissau', INBOUNDNOW_TEXT_DOMAIN),
                __('GY', 'inbound-pro' ) => __('Guyana', INBOUNDNOW_TEXT_DOMAIN),
                __('HT', 'inbound-pro' ) => __('Haiti', INBOUNDNOW_TEXT_DOMAIN),
                __('HM', 'inbound-pro' ) => __('Heard and McDonald Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('HN', 'inbound-pro' ) => __('Honduras', INBOUNDNOW_TEXT_DOMAIN),
                __('HK', 'inbound-pro' ) => __('Hong Kong', INBOUNDNOW_TEXT_DOMAIN),
                __('HU', 'inbound-pro' ) => __('Hungary', INBOUNDNOW_TEXT_DOMAIN),
                __('IS', 'inbound-pro' ) => __('Iceland', INBOUNDNOW_TEXT_DOMAIN),
                __('IN', 'inbound-pro' ) => __('India', INBOUNDNOW_TEXT_DOMAIN),
                __('ID', 'inbound-pro' ) => __('Indonesia', INBOUNDNOW_TEXT_DOMAIN),
                __('IR', 'inbound-pro' ) => __('Iran', INBOUNDNOW_TEXT_DOMAIN),
                __('IQ', 'inbound-pro' ) => __('Iraq', INBOUNDNOW_TEXT_DOMAIN),
                __('IE', 'inbound-pro' ) => __('Ireland', INBOUNDNOW_TEXT_DOMAIN),
                __('IM', 'inbound-pro' ) => __('Isle of Man', INBOUNDNOW_TEXT_DOMAIN),
                __('IL', 'inbound-pro' ) => __('Israel', INBOUNDNOW_TEXT_DOMAIN),
                __('IT', 'inbound-pro' ) => __('Italy', INBOUNDNOW_TEXT_DOMAIN),
                __('JM', 'inbound-pro' ) => __('Jamaica', INBOUNDNOW_TEXT_DOMAIN),
                __('JP', 'inbound-pro' ) => __('Japan', INBOUNDNOW_TEXT_DOMAIN),
                __('JE', 'inbound-pro' ) => __('Jersey', INBOUNDNOW_TEXT_DOMAIN),
                __('JO', 'inbound-pro' ) => __('Jordan', INBOUNDNOW_TEXT_DOMAIN),
                __('KZ', 'inbound-pro' ) => __('Kazakhstan', INBOUNDNOW_TEXT_DOMAIN),
                __('KE', 'inbound-pro' ) => __('Kenya', INBOUNDNOW_TEXT_DOMAIN),
                __('KI', 'inbound-pro' ) => __('Kiribati', INBOUNDNOW_TEXT_DOMAIN),
                __('KW', 'inbound-pro' ) => __('Kuwait', INBOUNDNOW_TEXT_DOMAIN),
                __('KG', 'inbound-pro' ) => __('Kyrgyzstan', INBOUNDNOW_TEXT_DOMAIN),
                __('LA', 'inbound-pro' ) => __('Lao People\'s Democratic Republic', INBOUNDNOW_TEXT_DOMAIN),
                __('LV', 'inbound-pro' ) => __('Latvia', INBOUNDNOW_TEXT_DOMAIN),
                __('LB', 'inbound-pro' ) => __('Lebanon', INBOUNDNOW_TEXT_DOMAIN),
                __('LS', 'inbound-pro' ) => __('Lesotho', INBOUNDNOW_TEXT_DOMAIN),
                __('LR', 'inbound-pro' ) => __('Liberia', INBOUNDNOW_TEXT_DOMAIN),
                __('LY', 'inbound-pro' ) => __('Libya', INBOUNDNOW_TEXT_DOMAIN),
                __('LI', 'inbound-pro' ) => __('Liechtenstein', INBOUNDNOW_TEXT_DOMAIN),
                __('LT', 'inbound-pro' ) => __('Lithuania', INBOUNDNOW_TEXT_DOMAIN),
                __('LU', 'inbound-pro' ) => __('Luxembourg', INBOUNDNOW_TEXT_DOMAIN),
                __('MO', 'inbound-pro' ) => __('Macau', INBOUNDNOW_TEXT_DOMAIN),
                __('MK', 'inbound-pro' ) => __('Macedonia', INBOUNDNOW_TEXT_DOMAIN),
                __('MG', 'inbound-pro' ) => __('Madagascar', INBOUNDNOW_TEXT_DOMAIN),
                __('MW', 'inbound-pro' ) => __('Malawi', INBOUNDNOW_TEXT_DOMAIN),
                __('MY', 'inbound-pro' ) => __('Malaysia', INBOUNDNOW_TEXT_DOMAIN),
                __('MV', 'inbound-pro' ) => __('Maldives', INBOUNDNOW_TEXT_DOMAIN),
                __('ML', 'inbound-pro' ) => __('Mali', INBOUNDNOW_TEXT_DOMAIN),
                __('MT', 'inbound-pro' ) => __('Malta', INBOUNDNOW_TEXT_DOMAIN),
                __('MH', 'inbound-pro' ) => __('Marshall Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('MQ', 'inbound-pro' ) => __('Martinique', INBOUNDNOW_TEXT_DOMAIN),
                __('MR', 'inbound-pro' ) => __('Mauritania', INBOUNDNOW_TEXT_DOMAIN),
                __('MU', 'inbound-pro' ) => __('Mauritius', INBOUNDNOW_TEXT_DOMAIN),
                __('YT', 'inbound-pro' ) => __('Mayotte', INBOUNDNOW_TEXT_DOMAIN),
                __('MX', 'inbound-pro' ) => __('Mexico', INBOUNDNOW_TEXT_DOMAIN),
                __('FM', 'inbound-pro' ) => __('Micronesia, Federated States of', INBOUNDNOW_TEXT_DOMAIN),
                __('MD', 'inbound-pro' ) => __('Moldova', INBOUNDNOW_TEXT_DOMAIN),
                __('MC', 'inbound-pro' ) => __('Monaco', INBOUNDNOW_TEXT_DOMAIN),
                __('MN', 'inbound-pro' ) => __('Mongolia', INBOUNDNOW_TEXT_DOMAIN),
                __('ME', 'inbound-pro' ) => __('Montenegro', INBOUNDNOW_TEXT_DOMAIN),
                __('MS', 'inbound-pro' ) => __('Montserrat', INBOUNDNOW_TEXT_DOMAIN),
                __('MA', 'inbound-pro' ) => __('Morocco', INBOUNDNOW_TEXT_DOMAIN),
                __('MZ', 'inbound-pro' ) => __('Mozambique', INBOUNDNOW_TEXT_DOMAIN),
                __('MM', 'inbound-pro' ) => __('Myanmar', INBOUNDNOW_TEXT_DOMAIN),
                __('NA', 'inbound-pro' ) => __('Namibia', INBOUNDNOW_TEXT_DOMAIN),
                __('NR', 'inbound-pro' ) => __('Nauru', INBOUNDNOW_TEXT_DOMAIN),
                __('NP', 'inbound-pro' ) => __('Nepal', INBOUNDNOW_TEXT_DOMAIN),
                __('NC', 'inbound-pro' ) => __('New Caledonia', INBOUNDNOW_TEXT_DOMAIN),
                __('NZ', 'inbound-pro' ) => __('New Zealand', INBOUNDNOW_TEXT_DOMAIN),
                __('NI', 'inbound-pro' ) => __('Nicaragua', INBOUNDNOW_TEXT_DOMAIN),
                __('NE', 'inbound-pro' ) => __('Niger', INBOUNDNOW_TEXT_DOMAIN),
                __('NG', 'inbound-pro' ) => __('Nigeria', INBOUNDNOW_TEXT_DOMAIN),
                __('NU', 'inbound-pro' ) => __('Niue', INBOUNDNOW_TEXT_DOMAIN),
                __('NF', 'inbound-pro' ) => __('Norfolk Island', INBOUNDNOW_TEXT_DOMAIN),
                __('KP', 'inbound-pro' ) => __('North Korea', INBOUNDNOW_TEXT_DOMAIN),
                __('MP', 'inbound-pro' ) => __('Northern Mariana Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('NO', 'inbound-pro' ) => __('Norway', INBOUNDNOW_TEXT_DOMAIN),
                __('OM', 'inbound-pro' ) => __('Oman', INBOUNDNOW_TEXT_DOMAIN),
                __('PK', 'inbound-pro' ) => __('Pakistan', INBOUNDNOW_TEXT_DOMAIN),
                __('PW', 'inbound-pro' ) => __('Palau', INBOUNDNOW_TEXT_DOMAIN),
                __('PS', 'inbound-pro' ) => __('Palestinian Territory, Occupied', INBOUNDNOW_TEXT_DOMAIN),
                __('PA', 'inbound-pro' ) => __('Panama', INBOUNDNOW_TEXT_DOMAIN),
                __('PG', 'inbound-pro' ) => __('Papua New Guinea', INBOUNDNOW_TEXT_DOMAIN),
                __('PY', 'inbound-pro' ) => __('Paraguay', INBOUNDNOW_TEXT_DOMAIN),
                __('PE', 'inbound-pro' ) => __('Peru', INBOUNDNOW_TEXT_DOMAIN),
                __('PH', 'inbound-pro' ) => __('Philippines', INBOUNDNOW_TEXT_DOMAIN),
                __('PN', 'inbound-pro' ) => __('Pitcairn', INBOUNDNOW_TEXT_DOMAIN),
                __('PL', 'inbound-pro' ) => __('Poland', INBOUNDNOW_TEXT_DOMAIN),
                __('PT', 'inbound-pro' ) => __('Portugal', INBOUNDNOW_TEXT_DOMAIN),
                __('PR', 'inbound-pro' ) => __('Puerto Rico', INBOUNDNOW_TEXT_DOMAIN),
                __('QA', 'inbound-pro' ) => __('Qatar', INBOUNDNOW_TEXT_DOMAIN),
                __('RE', 'inbound-pro' ) => __('Reunion', INBOUNDNOW_TEXT_DOMAIN),
                __('RO', 'inbound-pro' ) => __('Romania', INBOUNDNOW_TEXT_DOMAIN),
                __('RU', 'inbound-pro' ) => __('Russian Federation', INBOUNDNOW_TEXT_DOMAIN),
                __('RW', 'inbound-pro' ) => __('Rwanda', INBOUNDNOW_TEXT_DOMAIN),
                __('BL', 'inbound-pro' ) => __('Saint Barthelemy', INBOUNDNOW_TEXT_DOMAIN),
                __('SH', 'inbound-pro' ) => __('Saint Helena', INBOUNDNOW_TEXT_DOMAIN),
                __('KN', 'inbound-pro' ) => __('Saint Kitts and Nevis', INBOUNDNOW_TEXT_DOMAIN),
                __('LC', 'inbound-pro' ) => __('Saint Lucia', INBOUNDNOW_TEXT_DOMAIN),
                __('VC', 'inbound-pro' ) => __('Saint Vincent and the Grenadines', INBOUNDNOW_TEXT_DOMAIN),
                __('MF', 'inbound-pro' ) => __('Saint-Martin (France)', INBOUNDNOW_TEXT_DOMAIN),
                __('SX', 'inbound-pro' ) => __('Saint-Martin (Pays-Bas)', INBOUNDNOW_TEXT_DOMAIN),
                __('WS', 'inbound-pro' ) => __('Samoa', INBOUNDNOW_TEXT_DOMAIN),
                __('SM', 'inbound-pro' ) => __('San Marino', INBOUNDNOW_TEXT_DOMAIN),
                __('ST', 'inbound-pro' ) => __('Sao Tome and Principe', INBOUNDNOW_TEXT_DOMAIN),
                __('SA', 'inbound-pro' ) => __('Saudi Arabia', INBOUNDNOW_TEXT_DOMAIN),
                __('SN', 'inbound-pro' ) => __('Senegal', INBOUNDNOW_TEXT_DOMAIN),
                __('RS', 'inbound-pro' ) => __('Serbia', INBOUNDNOW_TEXT_DOMAIN),
                __('SC', 'inbound-pro' ) => __('Seychelles', INBOUNDNOW_TEXT_DOMAIN),
                __('SL', 'inbound-pro' ) => __('Sierra Leone', INBOUNDNOW_TEXT_DOMAIN),
                __('SG', 'inbound-pro' ) => __('Singapore', INBOUNDNOW_TEXT_DOMAIN),
                __('SK', 'inbound-pro' ) => __('Slovakia (Slovak Republic)', INBOUNDNOW_TEXT_DOMAIN),
                __('SI', 'inbound-pro' ) => __('Slovenia', INBOUNDNOW_TEXT_DOMAIN),
                __('SB', 'inbound-pro' ) => __('Solomon Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('SO', 'inbound-pro' ) => __('Somalia', INBOUNDNOW_TEXT_DOMAIN),
                __('ZA', 'inbound-pro' ) => __('South Africa', INBOUNDNOW_TEXT_DOMAIN),
                __('GS', 'inbound-pro' ) => __('South Georgia and the South Sandwich Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('KR', 'inbound-pro' ) => __('South Korea', INBOUNDNOW_TEXT_DOMAIN),
                __('SS', 'inbound-pro' ) => __('South Sudan', INBOUNDNOW_TEXT_DOMAIN),
                __('ES', 'inbound-pro' ) => __('Spain', INBOUNDNOW_TEXT_DOMAIN),
                __('LK', 'inbound-pro' ) => __('Sri Lanka', INBOUNDNOW_TEXT_DOMAIN),
                __('PM', 'inbound-pro' ) => __('St. Pierre and Miquelon', INBOUNDNOW_TEXT_DOMAIN),
                __('SD', 'inbound-pro' ) => __('Sudan', INBOUNDNOW_TEXT_DOMAIN),
                __('SR', 'inbound-pro' ) => __('Suriname', INBOUNDNOW_TEXT_DOMAIN),
                __('SJ', 'inbound-pro' ) => __('Svalbard and Jan Mayen Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('SZ', 'inbound-pro' ) => __('Swaziland', INBOUNDNOW_TEXT_DOMAIN),
                __('SE', 'inbound-pro' ) => __('Sweden', INBOUNDNOW_TEXT_DOMAIN),
                __('CH', 'inbound-pro' ) => __('Switzerland', INBOUNDNOW_TEXT_DOMAIN),
                __('SY', 'inbound-pro' ) => __('Syria', INBOUNDNOW_TEXT_DOMAIN),
                __('TW', 'inbound-pro' ) => __('Taiwan', INBOUNDNOW_TEXT_DOMAIN),
                __('TJ', 'inbound-pro' ) => __('Tajikistan', INBOUNDNOW_TEXT_DOMAIN),
                __('TZ', 'inbound-pro' ) => __('Tanzania', INBOUNDNOW_TEXT_DOMAIN),
                __('TH', 'inbound-pro' ) => __('Thailand', INBOUNDNOW_TEXT_DOMAIN),
                __('NL', 'inbound-pro' ) => __('The Netherlands', INBOUNDNOW_TEXT_DOMAIN),
                __('TL', 'inbound-pro' ) => __('Timor-Leste', INBOUNDNOW_TEXT_DOMAIN),
                __('TG', 'inbound-pro' ) => __('Togo', INBOUNDNOW_TEXT_DOMAIN),
                __('TK', 'inbound-pro' ) => __('Tokelau', INBOUNDNOW_TEXT_DOMAIN),
                __('TO', 'inbound-pro' ) => __('Tonga', INBOUNDNOW_TEXT_DOMAIN),
                __('TT', 'inbound-pro' ) => __('Trinidad and Tobago', INBOUNDNOW_TEXT_DOMAIN),
                __('TN', 'inbound-pro' ) => __('Tunisia', INBOUNDNOW_TEXT_DOMAIN),
                __('TR', 'inbound-pro' ) => __('Turkey', INBOUNDNOW_TEXT_DOMAIN),
                __('TM', 'inbound-pro' ) => __('Turkmenistan', INBOUNDNOW_TEXT_DOMAIN),
                __('TC', 'inbound-pro' ) => __('Turks and Caicos Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('TV', 'inbound-pro' ) => __('Tuvalu', INBOUNDNOW_TEXT_DOMAIN),
                __('UG', 'inbound-pro' ) => __('Uganda', INBOUNDNOW_TEXT_DOMAIN),
                __('UA', 'inbound-pro' ) => __('Ukraine', INBOUNDNOW_TEXT_DOMAIN),
                __('AE', 'inbound-pro' ) => __('United Arab Emirates', INBOUNDNOW_TEXT_DOMAIN),
                __('GB', 'inbound-pro' ) => __('United Kingdom', INBOUNDNOW_TEXT_DOMAIN),
                __('US', 'inbound-pro' ) => __('United States', INBOUNDNOW_TEXT_DOMAIN),
                __('UM', 'inbound-pro' ) => __('United States Minor Outlying Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('UY', 'inbound-pro' ) => __('Uruguay', INBOUNDNOW_TEXT_DOMAIN),
                __('UZ', 'inbound-pro' ) => __('Uzbekistan', INBOUNDNOW_TEXT_DOMAIN),
                __('VU', 'inbound-pro' ) => __('Vanuatu', INBOUNDNOW_TEXT_DOMAIN),
                __('VA', 'inbound-pro' ) => __('Vatican', INBOUNDNOW_TEXT_DOMAIN),
                __('VE', 'inbound-pro' ) => __('Venezuela', INBOUNDNOW_TEXT_DOMAIN),
                __('VN', 'inbound-pro' ) => __('Vietnam', INBOUNDNOW_TEXT_DOMAIN),
                __('VG', 'inbound-pro' ) => __('Virgin Islands (British)', INBOUNDNOW_TEXT_DOMAIN),
                __('VI', 'inbound-pro' ) => __('Virgin Islands (U.S.)', INBOUNDNOW_TEXT_DOMAIN),
                __('WF', 'inbound-pro' ) => __('Wallis and Futuna Islands', INBOUNDNOW_TEXT_DOMAIN),
                __('EH', 'inbound-pro' ) => __('Western Sahara', INBOUNDNOW_TEXT_DOMAIN),
                __('YE', 'inbound-pro' ) => __('Yemen', INBOUNDNOW_TEXT_DOMAIN),
                __('ZM', 'inbound-pro' ) => __('Zambia', INBOUNDNOW_TEXT_DOMAIN),
                __('ZW', 'inbound-pro' ) => __('Zimbabwe', INBOUNDNOW_TEXT_DOMAIN)
            );
        }

        /**
         *  Gets dataset of form settings by form id
         */
        public static function get_form_settings($form_id) {

            $meta = get_post_meta($form_id);
            $meta = ($meta) ? $meta : array();
            foreach ($meta as $key => $value) {
                $meta[$key] = $value[0];
            }

            return $meta;
        }
    }

    Inbound_Forms::init();
}
