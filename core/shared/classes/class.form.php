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
                'tags' => '',
                'submit' => 'Submit',
                'submit_colors' => '',
                'submit_text_color' => '',
                'submit_bg_color' => '',
                'custom_class' => ''
            ), $atts));

            if (!$id && isset($_GET['post'])) {
                $id = intval($_GET['post']);
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
                $form .= '<form class="inbound-now-form wpl-track-me inbound-track '.$custom_class.'" method="post" id="' . $form_id . '" action="" style="' . $form_width . '">';
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
                    $dynamic_value = (isset($_GET[$hidden_param])) ? sanitize_text_field($_GET[$hidden_param]) : '';
                    $dynamic_value = (!$dynamic_value && isset($_COOKIE[$hidden_param])) ? $_COOKIE[$hidden_param] : $dynamic_value;

                    if ($type != 'honeypot') {
                        $form .= '<div class="inbound-field ' . $div_chk_req . $main_layout . ' label-' . $form_labels_class . ' ' . $form_labels_class . ' ' . $field_container_class . '">';
                    }
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
                        $form .= '	<select id="formletMonth" class="formletMonth" name="' . $field_name . '[month]" >';
                        foreach ($months as $key => $value) {
                            ($m == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletDays" class="formletDays" name="' . $field_name . '[day]" >';
                        foreach ($days as $key => $value) {
                            ($d == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '	<select id="formletYears" class="formletYears" name="' . $field_name . '[year]" >';
                        foreach ($years as $key => $value) {
                            ($y == $key) ? $sel = 'selected="selected"' : $sel = '';
                            $form .= '<option value="' . $key . '" ' . $sel . '>' . $value . '</option>';
                        }
                        $form .= '	</select>';
                        $form .= '</div>';
                        $form .= '<script>
                                    if (typeof inbf_daysInMonth != "function") {

                                        function inbf_minTwoDigits(n) {
                                          return (n < 10 ? \'0\' : \'\') + n;
                                        }

                                        function inbf_daysInMonth(month,year) {
                                             return new Date(year, month, 0).getDate();
                                        }

                                        jQuery("body").on("change", ".formletMonth, .formletYears" ,function() {

                                             /* get current selected day */
                                             var selected_date = jQuery(this).parent().find( "#formletDays" ).find(":selected").val();

                                             /* remove day options */
                                             jQuery(this).parent().find( "#formletDays" ).find("option").remove();

                                             /* get more supportive variables  */
                                             var month = jQuery(this).parent().find("#formletMonth option:selected").val();
                                             var year = jQuery(this).parent().find("#formletYears option:selected").val();
                                             var days_in_month = inbf_daysInMonth(month,year);


                                             /* build new option set */
                                             for (var i = 1; i <= days_in_month; i++) {
                                                  jQuery(this).parent().find( ".formletDays" ).append(jQuery("<option></option>").attr("value", i).text(inbf_minTwoDigits(i)));
                                             }

                                             /* set date to original selection */
                                             jQuery(this).parent().find(".formletDays option[value="+selected_date+"]").prop("selected", true)
                                        });

                                    }

                                     /* trigger update to set day value correctly */
                                    jQuery(".formletYears:last-child").trigger("change");
                                   </script>';

                    } else if ($type === 'date') {

                        if ($dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
                        }

                        $form .= '<input class="inbound-input inbound-input-text ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" type="' . $type . '"' . $data_mapping_attr . $et_output . ' ' . $req . '/>';
                        $form .= '  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
                                    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
                                    <script>
                                        jQuery(function(){
                                            if( navigator.userAgent.toLowerCase().indexOf(\'firefox\') > -1) {
                                                 jQuery(\'input[type="date"]\').datepicker( {dateFormat: "mm-dd-yy" } );
                                            }
                                        });
                                    </script>';

                    } else if ($type === 'time') {

                        if ( $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
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

                        $form .= '<input style="display:none" name="phone_xoxo" class="phone_xoxo" value="">';

                    } else if ($type === 'datetime-local') {

                        if ($type === 'hidden' && $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        }

                        $form .= '<input type="datetime-local" class="inbound-input inbound-input-datetime-local ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'url') {

                        if ( $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
                        }

                        $form .= '<input type="url" class="inbound-input inbound-input-url ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'tel') {

                        if ( $dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
                        }

                        $form .= '<input type="tel" class="inbound-input inbound-input-tel ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'email') {

                        if ($dynamic_value != "") {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
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
                        $dynamic_value = (isset($_GET[$hidden_param])) ? sanitize_text_field($_GET[$hidden_param]) : '';

                        $form .= '<input type="range" min="' . $options[0] . '" max="' . $options[1] . '" step="' . $options[2] . '" class="inbound-input inbound-input-range ' . $formatted_label . $input_classes . ' ' . $field_input_class . '" name="' . $field_name . '" ' . $form_placeholder . ' id="' . $field_name . '" value="' . $fill_value . '" ' . $data_mapping_attr . $et_output . ' ' . $req . '/>';

                    } else if ($type === 'text') {
                        if ($dynamic_value) {
                            $fill_value = $dynamic_value;
                        } else {
                            $fill_value = '';
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
                        $form .= "<div class='inbound-description'>" . html_entity_decode($description_block) . "</div>";
                    }
                    if ($type != 'honeypot') {
                        $form .= '</div>';
                    }
                }
                /* End Loop */

                if ( is_ssl()) {
                    $current_page = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                }else {
                    $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                }
                $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $form .= '<div class="inbound-field ' . $main_layout . ' inbound-submit-area">';
                $form .= '<button type="submit" class="inbound-button-submit inbound-submit-action" value="' . $submit_button . '" name="send" id="inbound_form_submit" data-ignore-form-field="true" style="' . $submit_bg . $submit_color . $image_button . 'position:relative;">' . $icon_insert . '' . $submit_button . $inner_button . '</button>';
                $form .= '</div>';
                $form .= '<input data-ignore-form-field="true" type="hidden" name="inbound_submitted" value="1">';
                $form .= '<input type="hidden" name="inbound_form_n" class="inbound_form_n" value="' . $form_name . '">';
                $form .= '<input type="hidden" name="inbound_form_lists" id="inbound_form_lists" value="' . $lists . '" data-map-form-field="inbound_form_lists">';
                $form .= '<input type="hidden" name="inbound_form_tags" id="inbound_form_tags" value="' . $tags . '" data-map-form-field="inbound_form_tags">';
                $form .= '<input type="hidden" name="inbound_form_id" class="inbound_form_id" value="' . $id . '">';
                $form .= '<input type="hidden" name="inbound_current_page_url" value="' . $current_page . '">';
                $form .= '<input type="hidden" name="page_id" value="' . (isset($post->ID) ? $post->ID : '0') . '">';
                $form .= '<input type="hidden" name="inbound_furl" value="' . base64_encode(trim($redirect)) . '">';
                $form .= '<input type="hidden" name="inbound_notify" value="' . base64_encode($notify) . '">';
                $form .= '<input type="hidden" name="inbound_nonce" value="' . wp_create_nonce(SECURE_AUTH_KEY) . '">';
                $form .= '<input type="hidden" class="inbound_params" name="inbound_params" value="">';
                $form .= '</div>';
                $form .= '</form>';
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
            wp_enqueue_script('spin.min', INBOUNDNOW_SHARED_URLPATH .  '/shortcodes/js/spin.min.js', null, null, true);
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
            ?>
            <script type="text/javascript">

                function inbound_additional_checks( data ) {
                    /* make sure event is defined */
                    if (typeof event == 'undefined') {
                        var event = {};
                        event.target = data.event;
                    }

                    /*make sure all of this form's required checkboxes are checked*/
                    var checks = jQuery(event.target).find('.checkbox-required');
                    for(var a = 0; a < checks.length; a++){
                        if( checks[a] && jQuery(checks[a]).find('input[type=checkbox]:checked').length==0){
                            jQuery(jQuery(checks[a]).find('input')).focus();
                            alert("<?php _e('Oops! Looks like you have not filled out all of the required fields!', 'inbound-pro') ; ?> ");
                            throw new Error("<?php _e('Oops! Looks like you have not filled out all of the required fields!', 'inbound-pro') ; ?>");
                        }
                    }

                    jQuery(this).find("input").each(function(){
                        if(!jQuery(this).prop("required")){
                        } else if (!jQuery(this).val()) {
                            alert("<?php  _e('Oops! Looks like you have not filled out all of the required fields!', 'inbound-pro'); ?>");
                            throw new Error('<?php _e('Oops! Looks like you have not filled out all of the required fields!', 'inbound-pro') ; ?>');
                        }
                    });

                    /*Disable button and add spinner to form*/
                    var target = jQuery(event.target).find("#inbound_form_submit"),
                        spinnerColor = jQuery(target).css("color"),
                        buttonWidth = jQuery(target).css("width"),
                        buttonHeight = jQuery(target).css("height"),
                        scale = jQuery(target).css("font-size");
                    scale = scale.replace("px", "");
                    scale = scale / 40;


                    /* spinner param setup */
                    var opts = {
                        lines: 8 // The number of lines to draw
                        , length: 0 // The length of each line
                        , width: 7 // The line thickness
                        , radius: 25 // The radius of the inner circle
                        , scale: scale // Scales overall size of the spinner
                        , corners: 1 // Corner roundness (0..1)
                        , color: spinnerColor // #rgb or #rrggbb or array of colors
                        , opacity: 0.25 // Opacity of the lines
                        , rotate: 0 // The rotation offset
                        , direction: 1 // 1: clockwise, -1: counterclockwise
                        , speed: 1 // Rounds per second
                        , trail: 60 // Afterglow percentage
                        , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
                        , zIndex: 2e9 // The z-index (defaults to 2000000000)
                        , className: "inbound-form-spinner" // The CSS class to assign to the spinner
                        , top: "50%" // Top position relative to parent
                        , left: "50%" // Left position relative to parent
                        , shadow: false // Whether to render a shadow
                        , hwaccel: false // Whether to use hardware acceleration
                        , position: "absolute" // Element positioning
                    };

                    jQuery(target).prop("disabled",true).html("").css({"width" : buttonWidth, "height" : buttonHeight});

                    var spinner = new Spinner(opts).spin(target[0]);

                }

                /* helper function for validating email */
                function inboundFormsVaidateEmail(email) {
                    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(email);
                }

                /* Adding helpful listeners - may need to move all this into the analytics engine */
                jQuery(document).ready(function($){

                    /* add checkbox requirement checks */
                    _inbound.add_action( 'form_before_submission', inbound_additional_checks, 9);

                    /* remove br tags */
                    jQuery("#inbound_form_submit br").remove();

                    /* validate email */
                    jQuery("input.inbound-email").on("change keyup", function (e) {
                        var $this = jQuery(this);
                        var email = $this.val();
                        jQuery(".inbound_email_suggestion").remove();
                        if (inboundFormsVaidateEmail(email)) {
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
                    jQuery("input[type='text']").on("blur", function() {
                        var value = jQuery.trim( $(this).val() );
                        jQuery(this).val( value );
                    })

                });
            </script>
            <?php

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


            /* only process actions when told to */
            if (!isset($_POST['inbound_submitted']) || (!$_POST['inbound_submitted'] || $_POST['inbound_submitted'] =='false' ) ) {
                return;
            }

            /* if POST does not contain correct nonce then bail */
            check_ajax_referer( SECURE_AUTH_KEY , 'inbound_nonce' );

            $form_post_data = array();
            if (isset($_POST['phone_xoxo']) && $_POST['phone_xoxo'] != "") {
                wp_die($message = 'Die Die Die');
                return false;
            }
            /* get form submitted form's meta data */
            $form_meta_data = get_post_meta($_POST['inbound_form_id']);

            if (isset($_POST['inbound_furl']) && $_POST['inbound_furl'] != "") {
                $redirect = base64_decode($_POST['inbound_furl']);
            } else if (isset($_POST['inbound_current_page_url'])) {
                $redirect = $_POST['inbound_current_page_url'];
            } else {
                $redirect = "";
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

        /**
         *  Sends Notification of New Lead Conversion to Admin & Others Listed on the Form Notification List
         */
        public static function send_conversion_admin_notification($form_post_data, $form_meta_data) {

            /* Get Lead Email Address */
            $lead_email = self::get_email_from_post_data($form_post_data);

            if (!$lead_email) {
                return;
            }

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
                if (!isset($form_meta_data['inbound_notify_email']) || !trim($form_meta_data['inbound_notify_email'])) {
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
            $lead_email = self::get_email_from_post_data($form_post_data);

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

            //$confirm_email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;' . get_option('blog_charset') . '" /></head><body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0"><table cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" border="0"><tr>';
            $confirm_email_message = $content;
            //$confirm_email_message .= '</tr></table></body></html>';


            $confirm_subject = apply_filters('inbound_lead_conversion/subject', $confirm_subject, $form_meta_data, $form_post_data);
            $confirm_email_message = apply_filters('inbound_lead_conversion/body', $confirm_email_message, $form_meta_data, $form_post_data);

            $confirm_subject = $Inbound_Templating_Engine->replace_tokens($confirm_subject, array($form_post_data, $form_meta_data));

            /* add default subject if empty */
            if (!$confirm_subject) {
                $confirm_subject = __('Thank you!', 'inbound-pro');
            }

            $confirm_email_message = $Inbound_Templating_Engine->replace_tokens($confirm_email_message, array($form_post_data, $form_meta_data));


            $from_name = get_option('blogname', '');
            $from_email = get_option('admin_email');

            $headers = "From: " . $from_name . " <" . $from_email . ">\n";
            $headers .= 'Content-type: text/html';
            $headers = apply_filters('inbound_lead_conversion/headers', $headers);

            wp_mail($lead_email, $confirm_subject, $confirm_email_message, $headers);

        }

        public static function get_email_from_post_data( $form_post_data ) {
            /* Get Lead Email Address */
            $lead_email = '';
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
                }
            }

            $lead_email = str_replace('%40' , '@' , $lead_email);

            if ($lead_email == 'false') {
                $lead_email = false;
            }
            return $lead_email;
        }

        /**
         *  Get Email Template for New Lead Notification
         *
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
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}" style="height:40px;v-text-anchor:middle;width:130px;font-size:18px;" arcsize="10%" stroke="f" fillcolor="#ffffff">
                                      <w:anchorlock/>
                                      <center>
                                    <![endif]-->
                                        <a href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}"
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
                         <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}&tab=tabs-wpleads_lead_tab_conversions">' . __('View Lead Activity', 'inbound-pro') . '</a></h3>
                      </td>

                      <td width="160" height="50" align="center" valign="top" border="0">
                         <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}&scroll-to=wplead_metabox_conversion">' . __('Pages Viewed', 'inbound-pro') . '</a></h3>
                      </td>

                     <td width="160" height="50" align="center" valign="top" border="0">
                        <h3 style="color:#2e2e2e;font-size:15px;"><a style="text-decoration: none;" href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}&tab=tabs-wpleads_lead_tab_raw_form_data">' . __('View Form Data', 'inbound-pro') . '</a></h3>
                     </td>
                     </tr>
                    </tbody></table>
                    <!-- end 3 col -->
                     <!-- Start half/half -->
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
                         <tbody><tr>
                          <td align="center" width="250" height="30" cellpadding="5">
                             <div><!--[if mso]>
                               <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{admin-url}}edit.php?post_type=wp-lead&s={{lead-email-address}}" style="height:40px;v-text-anchor:middle;width:250px;" arcsize="10%" strokecolor="#7490af" fillcolor="#3A9FD1">
                                 <w:anchorlock/>
                                 <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">' . __('View Lead', 'inbound-pro') . '</center>
                               </v:roundrect>
                             <![endif]--><a href="{{admin-url}}edit.php?post_status=all&post_type=wp-lead&s={{lead-email-address}}"
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
                        '01' => __('Jan', 'inbound-pro'),
                        '02' => __('Feb', 'inbound-pro'),
                        '03' => __('Mar', 'inbound-pro'),
                        '04' => __('Apr', 'inbound-pro'),
                        '05' => __('May', 'inbound-pro'),
                        '06' => __('Jun', 'inbound-pro'),
                        '07' => __('Jul', 'inbound-pro'),
                        '08' => __('Aug', 'inbound-pro'),
                        '09' => __('Sep', 'inbound-pro'),
                        '10' => __('Oct', 'inbound-pro'),
                        '11' => __('Nov', 'inbound-pro'),
                        '12' => __('Dec', 'inbound-pro')
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
                __('AF', 'inbound-pro' ) => __('Afghanistan', 'inbound-pro'),
                __('AX', 'inbound-pro' ) => __('Aland Islands', 'inbound-pro'),
                __('AL', 'inbound-pro' ) => __('Albania', 'inbound-pro'),
                __('DZ', 'inbound-pro' ) => __('Algeria', 'inbound-pro'),
                __('AS', 'inbound-pro' ) => __('American Samoa', 'inbound-pro'),
                __('AD', 'inbound-pro' ) => __('Andorra', 'inbound-pro'),
                __('AO', 'inbound-pro' ) => __('Angola', 'inbound-pro'),
                __('AI', 'inbound-pro' ) => __('Anguilla', 'inbound-pro'),
                __('AQ', 'inbound-pro' ) => __('Antarctica', 'inbound-pro'),
                __('AG', 'inbound-pro' ) => __('Antigua and Barbuda', 'inbound-pro'),
                __('AR', 'inbound-pro' ) => __('Argentina', 'inbound-pro'),
                __('AM', 'inbound-pro' ) => __('Armenia', 'inbound-pro'),
                __('AW', 'inbound-pro' ) => __('Aruba', 'inbound-pro'),
                __('AU', 'inbound-pro' ) => __('Australia', 'inbound-pro'),
                __('AT', 'inbound-pro' ) => __('Austria', 'inbound-pro'),
                __('AZ', 'inbound-pro' ) => __('Azerbaijan', 'inbound-pro'),
                __('BS', 'inbound-pro' ) => __('Bahamas', 'inbound-pro'),
                __('BH', 'inbound-pro' ) => __('Bahrain', 'inbound-pro'),
                __('BD', 'inbound-pro' ) => __('Bangladesh', 'inbound-pro'),
                __('BB', 'inbound-pro' ) => __('Barbados', 'inbound-pro'),
                __('BY', 'inbound-pro' ) => __('Belarus', 'inbound-pro'),
                __('BE', 'inbound-pro' ) => __('Belgium', 'inbound-pro'),
                __('BZ', 'inbound-pro' ) => __('Belize', 'inbound-pro'),
                __('BJ', 'inbound-pro' ) => __('Benin', 'inbound-pro'),
                __('BM', 'inbound-pro' ) => __('Bermuda', 'inbound-pro'),
                __('BT', 'inbound-pro' ) => __('Bhutan', 'inbound-pro'),
                __('BO', 'inbound-pro' ) => __('Bolivia', 'inbound-pro'),
                __('BA', 'inbound-pro' ) => __('Bosnia and Herzegovina', 'inbound-pro'),
                __('BW', 'inbound-pro' ) => __('Botswana', 'inbound-pro'),
                __('BV', 'inbound-pro' ) => __('Bouvet Island', 'inbound-pro'),
                __('BR', 'inbound-pro' ) => __('Brazil', 'inbound-pro'),
                __('IO', 'inbound-pro' ) => __('British Indian Ocean Territory', 'inbound-pro'),
                __('BN', 'inbound-pro' ) => __('Brunei Darussalam', 'inbound-pro'),
                __('BG', 'inbound-pro' ) => __('Bulgaria', 'inbound-pro'),
                __('BF', 'inbound-pro' ) => __('Burkina Faso', 'inbound-pro'),
                __('BI', 'inbound-pro' ) => __('Burundi', 'inbound-pro'),
                __('KH', 'inbound-pro' ) => __('Cambodia', 'inbound-pro'),
                __('CM', 'inbound-pro' ) => __('Cameroon', 'inbound-pro'),
                __('CA', 'inbound-pro' ) => __('Canada', 'inbound-pro'),
                __('CV', 'inbound-pro' ) => __('Cape Verde', 'inbound-pro'),
                __('BQ', 'inbound-pro' ) => __('Caribbean Netherlands ', 'inbound-pro'),
                __('KY', 'inbound-pro' ) => __('Cayman Islands', 'inbound-pro'),
                __('CF', 'inbound-pro' ) => __('Central African Republic', 'inbound-pro'),
                __('TD', 'inbound-pro' ) => __('Chad', 'inbound-pro'),
                __('CL', 'inbound-pro' ) => __('Chile', 'inbound-pro'),
                __('CN', 'inbound-pro' ) => __('China', 'inbound-pro'),
                __('CX', 'inbound-pro' ) => __('Christmas Island', 'inbound-pro'),
                __('CC', 'inbound-pro' ) => __('Cocos (Keeling) Islands', 'inbound-pro'),
                __('CO', 'inbound-pro' ) => __('Colombia', 'inbound-pro'),
                __('KM', 'inbound-pro' ) => __('Comoros', 'inbound-pro'),
                __('CG', 'inbound-pro' ) => __('Congo', 'inbound-pro'),
                __('CD', 'inbound-pro' ) => __('Congo, Democratic Republic of', 'inbound-pro'),
                __('CK', 'inbound-pro' ) => __('Cook Islands', 'inbound-pro'),
                __('CR', 'inbound-pro' ) => __('Costa Rica', 'inbound-pro'),
                __('CI', 'inbound-pro' ) => __('Cote d\'Ivoire', 'inbound-pro'),
                __('HR', 'inbound-pro' ) => __('Croatia', 'inbound-pro'),
                __('CU', 'inbound-pro' ) => __('Cuba', 'inbound-pro'),
                __('CW', 'inbound-pro' ) => __('Curacao', 'inbound-pro'),
                __('CY', 'inbound-pro' ) => __('Cyprus', 'inbound-pro'),
                __('CZ', 'inbound-pro' ) => __('Czech Republic', 'inbound-pro'),
                __('DK', 'inbound-pro' ) => __('Denmark', 'inbound-pro'),
                __('DJ', 'inbound-pro' ) => __('Djibouti', 'inbound-pro'),
                __('DM', 'inbound-pro' ) => __('Dominica', 'inbound-pro'),
                __('DO', 'inbound-pro' ) => __('Dominican Republic', 'inbound-pro'),
                __('EC', 'inbound-pro' ) => __('Ecuador', 'inbound-pro'),
                __('EG', 'inbound-pro' ) => __('Egypt', 'inbound-pro'),
                __('SV', 'inbound-pro' ) => __('El Salvador', 'inbound-pro'),
                __('GQ', 'inbound-pro' ) => __('Equatorial Guinea', 'inbound-pro'),
                __('ER', 'inbound-pro' ) => __('Eritrea', 'inbound-pro'),
                __('EE', 'inbound-pro' ) => __('Estonia', 'inbound-pro'),
                __('ET', 'inbound-pro' ) => __('Ethiopia', 'inbound-pro'),
                __('FK', 'inbound-pro' ) => __('Falkland Islands', 'inbound-pro'),
                __('FO', 'inbound-pro' ) => __('Faroe Islands', 'inbound-pro'),
                __('FJ', 'inbound-pro' ) => __('Fiji', 'inbound-pro'),
                __('FI', 'inbound-pro' ) => __('Finland', 'inbound-pro'),
                __('FR', 'inbound-pro' ) => __('France', 'inbound-pro'),
                __('GF', 'inbound-pro' ) => __('French Guiana', 'inbound-pro'),
                __('PF', 'inbound-pro' ) => __('French Polynesia', 'inbound-pro'),
                __('TF', 'inbound-pro' ) => __('French Southern Territories', 'inbound-pro'),
                __('GA', 'inbound-pro' ) => __('Gabon', 'inbound-pro'),
                __('GM', 'inbound-pro' ) => __('Gambia', 'inbound-pro'),
                __('GE', 'inbound-pro' ) => __('Georgia', 'inbound-pro'),
                __('DE', 'inbound-pro' ) => __('Germany', 'inbound-pro'),
                __('GH', 'inbound-pro' ) => __('Ghana', 'inbound-pro'),
                __('GI', 'inbound-pro' ) => __('Gibraltar', 'inbound-pro'),
                __('GR', 'inbound-pro' ) => __('Greece', 'inbound-pro'),
                __('GL', 'inbound-pro' ) => __('Greenland', 'inbound-pro'),
                __('GD', 'inbound-pro' ) => __('Grenada', 'inbound-pro'),
                __('GP', 'inbound-pro' ) => __('Guadeloupe', 'inbound-pro'),
                __('GU', 'inbound-pro' ) => __('Guam', 'inbound-pro'),
                __('GT', 'inbound-pro' ) => __('Guatemala', 'inbound-pro'),
                __('GG', 'inbound-pro' ) => __('Guernsey', 'inbound-pro'),
                __('GN', 'inbound-pro' ) => __('Guinea', 'inbound-pro'),
                __('GW', 'inbound-pro' ) => __('Guinea-Bissau', 'inbound-pro'),
                __('GY', 'inbound-pro' ) => __('Guyana', 'inbound-pro'),
                __('HT', 'inbound-pro' ) => __('Haiti', 'inbound-pro'),
                __('HM', 'inbound-pro' ) => __('Heard and McDonald Islands', 'inbound-pro'),
                __('HN', 'inbound-pro' ) => __('Honduras', 'inbound-pro'),
                __('HK', 'inbound-pro' ) => __('Hong Kong', 'inbound-pro'),
                __('HU', 'inbound-pro' ) => __('Hungary', 'inbound-pro'),
                __('IS', 'inbound-pro' ) => __('Iceland', 'inbound-pro'),
                __('IN', 'inbound-pro' ) => __('India', 'inbound-pro'),
                __('ID', 'inbound-pro' ) => __('Indonesia', 'inbound-pro'),
                __('IR', 'inbound-pro' ) => __('Iran', 'inbound-pro'),
                __('IQ', 'inbound-pro' ) => __('Iraq', 'inbound-pro'),
                __('IE', 'inbound-pro' ) => __('Ireland', 'inbound-pro'),
                __('IM', 'inbound-pro' ) => __('Isle of Man', 'inbound-pro'),
                __('IL', 'inbound-pro' ) => __('Israel', 'inbound-pro'),
                __('IT', 'inbound-pro' ) => __('Italy', 'inbound-pro'),
                __('JM', 'inbound-pro' ) => __('Jamaica', 'inbound-pro'),
                __('JP', 'inbound-pro' ) => __('Japan', 'inbound-pro'),
                __('JE', 'inbound-pro' ) => __('Jersey', 'inbound-pro'),
                __('JO', 'inbound-pro' ) => __('Jordan', 'inbound-pro'),
                __('KZ', 'inbound-pro' ) => __('Kazakhstan', 'inbound-pro'),
                __('KE', 'inbound-pro' ) => __('Kenya', 'inbound-pro'),
                __('KI', 'inbound-pro' ) => __('Kiribati', 'inbound-pro'),
                __('KW', 'inbound-pro' ) => __('Kuwait', 'inbound-pro'),
                __('KG', 'inbound-pro' ) => __('Kyrgyzstan', 'inbound-pro'),
                __('LA', 'inbound-pro' ) => __('Lao People\'s Democratic Republic', 'inbound-pro'),
                __('LV', 'inbound-pro' ) => __('Latvia', 'inbound-pro'),
                __('LB', 'inbound-pro' ) => __('Lebanon', 'inbound-pro'),
                __('LS', 'inbound-pro' ) => __('Lesotho', 'inbound-pro'),
                __('LR', 'inbound-pro' ) => __('Liberia', 'inbound-pro'),
                __('LY', 'inbound-pro' ) => __('Libya', 'inbound-pro'),
                __('LI', 'inbound-pro' ) => __('Liechtenstein', 'inbound-pro'),
                __('LT', 'inbound-pro' ) => __('Lithuania', 'inbound-pro'),
                __('LU', 'inbound-pro' ) => __('Luxembourg', 'inbound-pro'),
                __('MO', 'inbound-pro' ) => __('Macau', 'inbound-pro'),
                __('MK', 'inbound-pro' ) => __('Macedonia', 'inbound-pro'),
                __('MG', 'inbound-pro' ) => __('Madagascar', 'inbound-pro'),
                __('MW', 'inbound-pro' ) => __('Malawi', 'inbound-pro'),
                __('MY', 'inbound-pro' ) => __('Malaysia', 'inbound-pro'),
                __('MV', 'inbound-pro' ) => __('Maldives', 'inbound-pro'),
                __('ML', 'inbound-pro' ) => __('Mali', 'inbound-pro'),
                __('MT', 'inbound-pro' ) => __('Malta', 'inbound-pro'),
                __('MH', 'inbound-pro' ) => __('Marshall Islands', 'inbound-pro'),
                __('MQ', 'inbound-pro' ) => __('Martinique', 'inbound-pro'),
                __('MR', 'inbound-pro' ) => __('Mauritania', 'inbound-pro'),
                __('MU', 'inbound-pro' ) => __('Mauritius', 'inbound-pro'),
                __('YT', 'inbound-pro' ) => __('Mayotte', 'inbound-pro'),
                __('MX', 'inbound-pro' ) => __('Mexico', 'inbound-pro'),
                __('FM', 'inbound-pro' ) => __('Micronesia, Federated States of', 'inbound-pro'),
                __('MD', 'inbound-pro' ) => __('Moldova', 'inbound-pro'),
                __('MC', 'inbound-pro' ) => __('Monaco', 'inbound-pro'),
                __('MN', 'inbound-pro' ) => __('Mongolia', 'inbound-pro'),
                __('ME', 'inbound-pro' ) => __('Montenegro', 'inbound-pro'),
                __('MS', 'inbound-pro' ) => __('Montserrat', 'inbound-pro'),
                __('MA', 'inbound-pro' ) => __('Morocco', 'inbound-pro'),
                __('MZ', 'inbound-pro' ) => __('Mozambique', 'inbound-pro'),
                __('MM', 'inbound-pro' ) => __('Myanmar', 'inbound-pro'),
                __('NA', 'inbound-pro' ) => __('Namibia', 'inbound-pro'),
                __('NR', 'inbound-pro' ) => __('Nauru', 'inbound-pro'),
                __('NP', 'inbound-pro' ) => __('Nepal', 'inbound-pro'),
                __('NC', 'inbound-pro' ) => __('New Caledonia', 'inbound-pro'),
                __('NZ', 'inbound-pro' ) => __('New Zealand', 'inbound-pro'),
                __('NI', 'inbound-pro' ) => __('Nicaragua', 'inbound-pro'),
                __('NE', 'inbound-pro' ) => __('Niger', 'inbound-pro'),
                __('NG', 'inbound-pro' ) => __('Nigeria', 'inbound-pro'),
                __('NU', 'inbound-pro' ) => __('Niue', 'inbound-pro'),
                __('NF', 'inbound-pro' ) => __('Norfolk Island', 'inbound-pro'),
                __('KP', 'inbound-pro' ) => __('North Korea', 'inbound-pro'),
                __('MP', 'inbound-pro' ) => __('Northern Mariana Islands', 'inbound-pro'),
                __('NO', 'inbound-pro' ) => __('Norway', 'inbound-pro'),
                __('OM', 'inbound-pro' ) => __('Oman', 'inbound-pro'),
                __('PK', 'inbound-pro' ) => __('Pakistan', 'inbound-pro'),
                __('PW', 'inbound-pro' ) => __('Palau', 'inbound-pro'),
                __('PS', 'inbound-pro' ) => __('Palestinian Territory, Occupied', 'inbound-pro'),
                __('PA', 'inbound-pro' ) => __('Panama', 'inbound-pro'),
                __('PG', 'inbound-pro' ) => __('Papua New Guinea', 'inbound-pro'),
                __('PY', 'inbound-pro' ) => __('Paraguay', 'inbound-pro'),
                __('PE', 'inbound-pro' ) => __('Peru', 'inbound-pro'),
                __('PH', 'inbound-pro' ) => __('Philippines', 'inbound-pro'),
                __('PN', 'inbound-pro' ) => __('Pitcairn', 'inbound-pro'),
                __('PL', 'inbound-pro' ) => __('Poland', 'inbound-pro'),
                __('PT', 'inbound-pro' ) => __('Portugal', 'inbound-pro'),
                __('PR', 'inbound-pro' ) => __('Puerto Rico', 'inbound-pro'),
                __('QA', 'inbound-pro' ) => __('Qatar', 'inbound-pro'),
                __('RE', 'inbound-pro' ) => __('Reunion', 'inbound-pro'),
                __('RO', 'inbound-pro' ) => __('Romania', 'inbound-pro'),
                __('RU', 'inbound-pro' ) => __('Russian Federation', 'inbound-pro'),
                __('RW', 'inbound-pro' ) => __('Rwanda', 'inbound-pro'),
                __('BL', 'inbound-pro' ) => __('Saint Barthelemy', 'inbound-pro'),
                __('SH', 'inbound-pro' ) => __('Saint Helena', 'inbound-pro'),
                __('KN', 'inbound-pro' ) => __('Saint Kitts and Nevis', 'inbound-pro'),
                __('LC', 'inbound-pro' ) => __('Saint Lucia', 'inbound-pro'),
                __('VC', 'inbound-pro' ) => __('Saint Vincent and the Grenadines', 'inbound-pro'),
                __('MF', 'inbound-pro' ) => __('Saint-Martin (France)', 'inbound-pro'),
                __('SX', 'inbound-pro' ) => __('Saint-Martin (Pays-Bas)', 'inbound-pro'),
                __('WS', 'inbound-pro' ) => __('Samoa', 'inbound-pro'),
                __('SM', 'inbound-pro' ) => __('San Marino', 'inbound-pro'),
                __('ST', 'inbound-pro' ) => __('Sao Tome and Principe', 'inbound-pro'),
                __('SA', 'inbound-pro' ) => __('Saudi Arabia', 'inbound-pro'),
                __('SN', 'inbound-pro' ) => __('Senegal', 'inbound-pro'),
                __('RS', 'inbound-pro' ) => __('Serbia', 'inbound-pro'),
                __('SC', 'inbound-pro' ) => __('Seychelles', 'inbound-pro'),
                __('SL', 'inbound-pro' ) => __('Sierra Leone', 'inbound-pro'),
                __('SG', 'inbound-pro' ) => __('Singapore', 'inbound-pro'),
                __('SK', 'inbound-pro' ) => __('Slovakia (Slovak Republic)', 'inbound-pro'),
                __('SI', 'inbound-pro' ) => __('Slovenia', 'inbound-pro'),
                __('SB', 'inbound-pro' ) => __('Solomon Islands', 'inbound-pro'),
                __('SO', 'inbound-pro' ) => __('Somalia', 'inbound-pro'),
                __('ZA', 'inbound-pro' ) => __('South Africa', 'inbound-pro'),
                __('GS', 'inbound-pro' ) => __('South Georgia and the South Sandwich Islands', 'inbound-pro'),
                __('KR', 'inbound-pro' ) => __('South Korea', 'inbound-pro'),
                __('SS', 'inbound-pro' ) => __('South Sudan', 'inbound-pro'),
                __('ES', 'inbound-pro' ) => __('Spain', 'inbound-pro'),
                __('LK', 'inbound-pro' ) => __('Sri Lanka', 'inbound-pro'),
                __('PM', 'inbound-pro' ) => __('St. Pierre and Miquelon', 'inbound-pro'),
                __('SD', 'inbound-pro' ) => __('Sudan', 'inbound-pro'),
                __('SR', 'inbound-pro' ) => __('Suriname', 'inbound-pro'),
                __('SJ', 'inbound-pro' ) => __('Svalbard and Jan Mayen Islands', 'inbound-pro'),
                __('SZ', 'inbound-pro' ) => __('Swaziland', 'inbound-pro'),
                __('SE', 'inbound-pro' ) => __('Sweden', 'inbound-pro'),
                __('CH', 'inbound-pro' ) => __('Switzerland', 'inbound-pro'),
                __('SY', 'inbound-pro' ) => __('Syria', 'inbound-pro'),
                __('TW', 'inbound-pro' ) => __('Taiwan', 'inbound-pro'),
                __('TJ', 'inbound-pro' ) => __('Tajikistan', 'inbound-pro'),
                __('TZ', 'inbound-pro' ) => __('Tanzania', 'inbound-pro'),
                __('TH', 'inbound-pro' ) => __('Thailand', 'inbound-pro'),
                __('NL', 'inbound-pro' ) => __('The Netherlands', 'inbound-pro'),
                __('TL', 'inbound-pro' ) => __('Timor-Leste', 'inbound-pro'),
                __('TG', 'inbound-pro' ) => __('Togo', 'inbound-pro'),
                __('TK', 'inbound-pro' ) => __('Tokelau', 'inbound-pro'),
                __('TO', 'inbound-pro' ) => __('Tonga', 'inbound-pro'),
                __('TT', 'inbound-pro' ) => __('Trinidad and Tobago', 'inbound-pro'),
                __('TN', 'inbound-pro' ) => __('Tunisia', 'inbound-pro'),
                __('TR', 'inbound-pro' ) => __('Turkey', 'inbound-pro'),
                __('TM', 'inbound-pro' ) => __('Turkmenistan', 'inbound-pro'),
                __('TC', 'inbound-pro' ) => __('Turks and Caicos Islands', 'inbound-pro'),
                __('TV', 'inbound-pro' ) => __('Tuvalu', 'inbound-pro'),
                __('UG', 'inbound-pro' ) => __('Uganda', 'inbound-pro'),
                __('UA', 'inbound-pro' ) => __('Ukraine', 'inbound-pro'),
                __('AE', 'inbound-pro' ) => __('United Arab Emirates', 'inbound-pro'),
                __('GB', 'inbound-pro' ) => __('United Kingdom', 'inbound-pro'),
                __('US', 'inbound-pro' ) => __('United States', 'inbound-pro'),
                __('UM', 'inbound-pro' ) => __('United States Minor Outlying Islands', 'inbound-pro'),
                __('UY', 'inbound-pro' ) => __('Uruguay', 'inbound-pro'),
                __('UZ', 'inbound-pro' ) => __('Uzbekistan', 'inbound-pro'),
                __('VU', 'inbound-pro' ) => __('Vanuatu', 'inbound-pro'),
                __('VA', 'inbound-pro' ) => __('Vatican', 'inbound-pro'),
                __('VE', 'inbound-pro' ) => __('Venezuela', 'inbound-pro'),
                __('VN', 'inbound-pro' ) => __('Vietnam', 'inbound-pro'),
                __('VG', 'inbound-pro' ) => __('Virgin Islands (British)', 'inbound-pro'),
                __('VI', 'inbound-pro' ) => __('Virgin Islands (U.S.)', 'inbound-pro'),
                __('WF', 'inbound-pro' ) => __('Wallis and Futuna Islands', 'inbound-pro'),
                __('EH', 'inbound-pro' ) => __('Western Sahara', 'inbound-pro'),
                __('YE', 'inbound-pro' ) => __('Yemen', 'inbound-pro'),
                __('ZM', 'inbound-pro' ) => __('Zambia', 'inbound-pro'),
                __('ZW', 'inbound-pro' ) => __('Zimbabwe', 'inbound-pro')
            );
        }

        /**
         *
         */
        public static function get_inbound_forms() {
            $args = array(
                'posts_per_page'  => -1,
                'post_type'=> 'inbound-forms'
            );

            $form_list = get_posts($args);
            $form_array = array();

            foreach ( $form_list as $form ) {
                $this_id = $form->ID;
                $this_link = get_permalink( $this_id );
                $title = $form->post_title;
                $form_array[$form->ID] = $form->post_title;

            }

            return $form_array;
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
