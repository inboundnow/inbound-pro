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
					$req = ($required === '1') ? 'required' : '';
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
							alert("' . __( 'Oops! Looks like you have not filled out all of the required fields!' , 'inbound-pro' ) .'");
							e.preventDefault();
							e.stopImmediatePropagation();
						}
						jQuery(this).find("input").each(function(){
							if(!jQuery(this).prop("required")){
							} else if (!jQuery(this).val()) {
							alert("' . __( 'Oops! Looks like you have not filled out all of the required fields!' , 'inbound-pro' ) .'");

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
				$confirm_subject = __( 'Thank you!' , 'inbound-pro' );
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
						'01' => __( 'Jan' , 'inbound-pro' ),
						'02' => __( 'Feb' , 'inbound-pro' ),
						'03' => __( 'Mar' , 'inbound-pro' ),
						'04' => __( 'Apr' , 'inbound-pro' ),
						'05' => __( 'May' , 'inbound-pro' ),
						'06' => __( 'Jun' , 'inbound-pro' ),
						'07' => __( 'Jul' , 'inbound-pro' ),
						'08' => __( 'Aug' , 'inbound-pro' ),
						'09' => __( 'Sep' , 'inbound-pro' ),
						'10' => __( 'Oct' , 'inbound-pro' ),
						'11' => __( 'Nov' , 'inbound-pro' ),
						'12' => __( 'Dec' , 'inbound-pro' )
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
				 __( 'AF' , 'leads') => __( 'Afghanistan' , 'inbound-pro' ) ,
				 __( 'AX' , 'leads') => __( 'Aland Islands' , 'inbound-pro' ) ,
				 __( 'AL' , 'leads') => __( 'Albania' , 'inbound-pro' ) ,
				 __( 'DZ' , 'leads') => __( 'Algeria' , 'inbound-pro' ) ,
				 __( 'AS' , 'leads') => __( 'American Samoa' , 'inbound-pro' ) ,
				 __( 'AD' , 'leads') => __( 'Andorra' , 'inbound-pro' ) ,
				 __( 'AO' , 'leads') => __( 'Angola' , 'inbound-pro' ) ,
				 __( 'AI' , 'leads') => __( 'Anguilla' , 'inbound-pro' ) ,
				 __( 'AQ' , 'leads') => __( 'Antarctica' , 'inbound-pro' ) ,
				 __( 'AG' , 'leads') => __( 'Antigua and Barbuda' , 'inbound-pro' ) ,
				 __( 'AR' , 'leads') => __( 'Argentina' , 'inbound-pro' ) ,
				 __( 'AM' , 'leads') => __( 'Armenia' , 'inbound-pro' ) ,
				 __( 'AW' , 'leads') => __( 'Aruba' , 'inbound-pro' ) ,
				 __( 'AU' , 'leads') => __( 'Australia' , 'inbound-pro' ) ,
				 __( 'AT' , 'leads') => __( 'Austria' , 'inbound-pro' ) ,
				 __( 'AZ' , 'leads') => __( 'Azerbaijan' , 'inbound-pro' ) ,
				 __( 'BS' , 'leads') => __( 'Bahamas' , 'inbound-pro' ) ,
				 __( 'BH' , 'leads') => __( 'Bahrain' , 'inbound-pro' ) ,
				 __( 'BD' , 'leads') => __( 'Bangladesh' , 'inbound-pro' ) ,
				 __( 'BB' , 'leads') => __( 'Barbados' , 'inbound-pro' ) ,
				 __( 'BY' , 'leads') => __( 'Belarus' , 'inbound-pro' ) ,
				 __( 'BE' , 'leads') => __( 'Belgium' , 'inbound-pro' ) ,
				 __( 'BZ' , 'leads') => __( 'Belize' , 'inbound-pro' ) ,
				 __( 'BJ' , 'leads') => __( 'Benin' , 'inbound-pro' ) ,
				 __( 'BM' , 'leads') => __( 'Bermuda' , 'inbound-pro' ) ,
				 __( 'BT' , 'leads') => __( 'Bhutan' , 'inbound-pro' ) ,
				 __( 'BO' , 'leads') => __( 'Bolivia' , 'inbound-pro' ) ,
				 __( 'BA' , 'leads') => __( 'Bosnia and Herzegovina' , 'inbound-pro' ) ,
				 __( 'BW' , 'leads') => __( 'Botswana' , 'inbound-pro' ) ,
				 __( 'BV' , 'leads') => __( 'Bouvet Island' , 'inbound-pro' ) ,
				 __( 'BR' , 'leads') => __( 'Brazil' , 'inbound-pro' ) ,
				 __( 'IO' , 'leads') => __( 'British Indian Ocean Territory' , 'inbound-pro' ) ,
				 __( 'BN' , 'leads') => __( 'Brunei Darussalam' , 'inbound-pro' ) ,
				 __( 'BG' , 'leads') => __( 'Bulgaria' , 'inbound-pro' ) ,
				 __( 'BF' , 'leads') => __( 'Burkina Faso' , 'inbound-pro' ) ,
				 __( 'BI' , 'leads') => __( 'Burundi' , 'inbound-pro' ) ,
				 __( 'KH' , 'leads') => __( 'Cambodia' , 'inbound-pro' ) ,
				 __( 'CM' , 'leads') => __( 'Cameroon' , 'inbound-pro' ) ,
				 __( 'CA' , 'leads') => __( 'Canada' , 'inbound-pro' ) ,
				 __( 'CV' , 'leads') => __( 'Cape Verde' , 'inbound-pro' ) ,
				 __( 'BQ' , 'leads') => __( 'Caribbean Netherlands ' , 'inbound-pro' ) ,
				 __( 'KY' , 'leads') => __( 'Cayman Islands' , 'inbound-pro' ) ,
				 __( 'CF' , 'leads') => __( 'Central African Republic' , 'inbound-pro' ) ,
				 __( 'TD' , 'leads') => __( 'Chad' , 'inbound-pro' ) ,
				 __( 'CL' , 'leads') => __( 'Chile' , 'inbound-pro' ) ,
				 __( 'CN' , 'leads') => __( 'China' , 'inbound-pro' ) ,
				 __( 'CX' , 'leads') => __( 'Christmas Island' , 'inbound-pro' ) ,
				 __( 'CC' , 'leads') => __( 'Cocos (Keeling) Islands' , 'inbound-pro' ) ,
				 __( 'CO' , 'leads') => __( 'Colombia' , 'inbound-pro' ) ,
				 __( 'KM' , 'leads') => __( 'Comoros' , 'inbound-pro' ) ,
				 __( 'CG' , 'leads') => __( 'Congo' , 'inbound-pro' ) ,
				 __( 'CD' , 'leads') => __( 'Congo, Democratic Republic of' , 'inbound-pro' ) ,
				 __( 'CK' , 'leads') => __( 'Cook Islands' , 'inbound-pro' ) ,
				 __( 'CR' , 'leads') => __( 'Costa Rica' , 'inbound-pro' ) ,
				 __( 'CI' , 'leads') => __( 'Cote d\'Ivoire' , 'inbound-pro' ) ,
				 __( 'HR' , 'leads') => __( 'Croatia' , 'inbound-pro' ) ,
				 __( 'CU' , 'leads') => __( 'Cuba' , 'inbound-pro' ) ,
				 __( 'CW' , 'leads') => __( 'Curacao' , 'inbound-pro' ) ,
				 __( 'CY' , 'leads') => __( 'Cyprus' , 'inbound-pro' ) ,
				 __( 'CZ' , 'leads') => __( 'Czech Republic' , 'inbound-pro' ) ,
				 __( 'DK' , 'leads') => __( 'Denmark' , 'inbound-pro' ) ,
				 __( 'DJ' , 'leads') => __( 'Djibouti' , 'inbound-pro' ) ,
				 __( 'DM' , 'leads') => __( 'Dominica' , 'inbound-pro' ) ,
				 __( 'DO' , 'leads') => __( 'Dominican Republic' , 'inbound-pro' ) ,
				 __( 'EC' , 'leads') => __( 'Ecuador' , 'inbound-pro' ) ,
				 __( 'EG' , 'leads') => __( 'Egypt' , 'inbound-pro' ) ,
				 __( 'SV' , 'leads') => __( 'El Salvador' , 'inbound-pro' ) ,
				 __( 'GQ' , 'leads') => __( 'Equatorial Guinea' , 'inbound-pro' ) ,
				 __( 'ER' , 'leads') => __( 'Eritrea' , 'inbound-pro' ) ,
				 __( 'EE' , 'leads') => __( 'Estonia' , 'inbound-pro' ) ,
				 __( 'ET' , 'leads') => __( 'Ethiopia' , 'inbound-pro' ) ,
				 __( 'FK' , 'leads') => __( 'Falkland Islands' , 'inbound-pro' ) ,
				 __( 'FO' , 'leads') => __( 'Faroe Islands' , 'inbound-pro' ) ,
				 __( 'FJ' , 'leads') => __( 'Fiji' , 'inbound-pro' ) ,
				 __( 'FI' , 'leads') => __( 'Finland' , 'inbound-pro' ) ,
				 __( 'FR' , 'leads') => __( 'France' , 'inbound-pro' ) ,
				 __( 'GF' , 'leads') => __( 'French Guiana' , 'inbound-pro' ) ,
				 __( 'PF' , 'leads') => __( 'French Polynesia' , 'inbound-pro' ) ,
				 __( 'TF' , 'leads') => __( 'French Southern Territories' , 'inbound-pro' ) ,
				 __( 'GA' , 'leads') => __( 'Gabon' , 'inbound-pro' ) ,
				 __( 'GM' , 'leads') => __( 'Gambia' , 'inbound-pro' ) ,
				 __( 'GE' , 'leads') => __( 'Georgia' , 'inbound-pro' ) ,
				 __( 'DE' , 'leads') => __( 'Germany' , 'inbound-pro' ) ,
				 __( 'GH' , 'leads') => __( 'Ghana' , 'inbound-pro' ) ,
				 __( 'GI' , 'leads') => __( 'Gibraltar' , 'inbound-pro' ) ,
				 __( 'GR' , 'leads') => __( 'Greece' , 'inbound-pro' ) ,
				 __( 'GL' , 'leads') => __( 'Greenland' , 'inbound-pro' ) ,
				 __( 'GD' , 'leads') => __( 'Grenada' , 'inbound-pro' ) ,
				 __( 'GP' , 'leads') => __( 'Guadeloupe' , 'inbound-pro' ) ,
				 __( 'GU' , 'leads') => __( 'Guam' , 'inbound-pro' ) ,
				 __( 'GT' , 'leads') => __( 'Guatemala' , 'inbound-pro' ) ,
				 __( 'GG' , 'leads') => __( 'Guernsey' , 'inbound-pro' ) ,
				 __( 'GN' , 'leads') => __( 'Guinea' , 'inbound-pro' ) ,
				 __( 'GW' , 'leads') => __( 'Guinea-Bissau' , 'inbound-pro' ) ,
				 __( 'GY' , 'leads') => __( 'Guyana' , 'inbound-pro' ) ,
				 __( 'HT' , 'leads') => __( 'Haiti' , 'inbound-pro' ) ,
				 __( 'HM' , 'leads') => __( 'Heard and McDonald Islands' , 'inbound-pro' ) ,
				 __( 'HN' , 'leads') => __( 'Honduras' , 'inbound-pro' ) ,
				 __( 'HK' , 'leads') => __( 'Hong Kong' , 'inbound-pro' ) ,
				 __( 'HU' , 'leads') => __( 'Hungary' , 'inbound-pro' ) ,
				 __( 'IS' , 'leads') => __( 'Iceland' , 'inbound-pro' ) ,
				 __( 'IN' , 'leads') => __( 'India' , 'inbound-pro' ) ,
				 __( 'ID' , 'leads') => __( 'Indonesia' , 'inbound-pro' ) ,
				 __( 'IR' , 'leads') => __( 'Iran' , 'inbound-pro' ) ,
				 __( 'IQ' , 'leads') => __( 'Iraq' , 'inbound-pro' ) ,
				 __( 'IE' , 'leads') => __( 'Ireland' , 'inbound-pro' ) ,
				 __( 'IM' , 'leads') => __( 'Isle of Man' , 'inbound-pro' ) ,
				 __( 'IL' , 'leads') => __( 'Israel' , 'inbound-pro' ) ,
				 __( 'IT' , 'leads') => __( 'Italy' , 'inbound-pro' ) ,
				 __( 'JM' , 'leads') => __( 'Jamaica' , 'inbound-pro' ) ,
				 __( 'JP' , 'leads') => __( 'Japan' , 'inbound-pro' ) ,
				 __( 'JE' , 'leads') => __( 'Jersey' , 'inbound-pro' ) ,
				 __( 'JO' , 'leads') => __( 'Jordan' , 'inbound-pro' ) ,
				 __( 'KZ' , 'leads') => __( 'Kazakhstan' , 'inbound-pro' ) ,
				 __( 'KE' , 'leads') => __( 'Kenya' , 'inbound-pro' ) ,
				 __( 'KI' , 'leads') => __( 'Kiribati' , 'inbound-pro' ) ,
				 __( 'KW' , 'leads') => __( 'Kuwait' , 'inbound-pro' ) ,
				 __( 'KG' , 'leads') => __( 'Kyrgyzstan' , 'inbound-pro' ) ,
				 __( 'LA' , 'leads') => __( 'Lao People\'s Democratic Republic' , 'inbound-pro' ) ,
				 __( 'LV' , 'leads') => __( 'Latvia' , 'inbound-pro' ) ,
				 __( 'LB' , 'leads') => __( 'Lebanon' , 'inbound-pro' ) ,
				 __( 'LS' , 'leads') => __( 'Lesotho' , 'inbound-pro' ) ,
				 __( 'LR' , 'leads') => __( 'Liberia' , 'inbound-pro' ) ,
				 __( 'LY' , 'leads') => __( 'Libya' , 'inbound-pro' ) ,
				 __( 'LI' , 'leads') => __( 'Liechtenstein' , 'inbound-pro' ) ,
				 __( 'LT' , 'leads') => __( 'Lithuania' , 'inbound-pro' ) ,
				 __( 'LU' , 'leads') => __( 'Luxembourg' , 'inbound-pro' ) ,
				 __( 'MO' , 'leads') => __( 'Macau' , 'inbound-pro' ) ,
				 __( 'MK' , 'leads') => __( 'Macedonia' , 'inbound-pro' ) ,
				 __( 'MG' , 'leads') => __( 'Madagascar' , 'inbound-pro' ) ,
				 __( 'MW' , 'leads') => __( 'Malawi' , 'inbound-pro' ) ,
				 __( 'MY' , 'leads') => __( 'Malaysia' , 'inbound-pro' ) ,
				 __( 'MV' , 'leads') => __( 'Maldives' , 'inbound-pro' ) ,
				 __( 'ML' , 'leads') => __( 'Mali' , 'inbound-pro' ) ,
				 __( 'MT' , 'leads') => __( 'Malta' , 'inbound-pro' ) ,
				 __( 'MH' , 'leads') => __( 'Marshall Islands' , 'inbound-pro' ) ,
				 __( 'MQ' , 'leads') => __( 'Martinique' , 'inbound-pro' ) ,
				 __( 'MR' , 'leads') => __( 'Mauritania' , 'inbound-pro' ) ,
				 __( 'MU' , 'leads') => __( 'Mauritius' , 'inbound-pro' ) ,
				 __( 'YT' , 'leads') => __( 'Mayotte' , 'inbound-pro' ) ,
				 __( 'MX' , 'leads') => __( 'Mexico' , 'inbound-pro' ) ,
				 __( 'FM' , 'leads') => __( 'Micronesia, Federated States of' , 'inbound-pro' ) ,
				 __( 'MD' , 'leads') => __( 'Moldova' , 'inbound-pro' ) ,
				 __( 'MC' , 'leads') => __( 'Monaco' , 'inbound-pro' ) ,
				 __( 'MN' , 'leads') => __( 'Mongolia' , 'inbound-pro' ) ,
				 __( 'ME' , 'leads') => __( 'Montenegro' , 'inbound-pro' ) ,
				 __( 'MS' , 'leads') => __( 'Montserrat' , 'inbound-pro' ) ,
				 __( 'MA' , 'leads') => __( 'Morocco' , 'inbound-pro' ) ,
				 __( 'MZ' , 'leads') => __( 'Mozambique' , 'inbound-pro' ) ,
				 __( 'MM' , 'leads') => __( 'Myanmar' , 'inbound-pro' ) ,
				 __( 'NA' , 'leads') => __( 'Namibia' , 'inbound-pro' ) ,
				 __( 'NR' , 'leads') => __( 'Nauru' , 'inbound-pro' ) ,
				 __( 'NP' , 'leads') => __( 'Nepal' , 'inbound-pro' ) ,
				 __( 'NC' , 'leads') => __( 'New Caledonia' , 'inbound-pro' ) ,
				 __( 'NZ' , 'leads') => __( 'New Zealand' , 'inbound-pro' ) ,
				 __( 'NI' , 'leads') => __( 'Nicaragua' , 'inbound-pro' ) ,
				 __( 'NE' , 'leads') => __( 'Niger' , 'inbound-pro' ) ,
				 __( 'NG' , 'leads') => __( 'Nigeria' , 'inbound-pro' ) ,
				 __( 'NU' , 'leads') => __( 'Niue' , 'inbound-pro' ) ,
				 __( 'NF' , 'leads') => __( 'Norfolk Island' , 'inbound-pro' ) ,
				 __( 'KP' , 'leads') => __( 'North Korea' , 'inbound-pro' ) ,
				 __( 'MP' , 'leads') => __( 'Northern Mariana Islands' , 'inbound-pro' ) ,
				 __( 'NO' , 'leads') => __( 'Norway' , 'inbound-pro' ) ,
				 __( 'OM' , 'leads') => __( 'Oman' , 'inbound-pro' ) ,
				 __( 'PK' , 'leads') => __( 'Pakistan' , 'inbound-pro' ) ,
				 __( 'PW' , 'leads') => __( 'Palau' , 'inbound-pro' ) ,
				 __( 'PS' , 'leads') => __( 'Palestinian Territory, Occupied' , 'inbound-pro' ) ,
				 __( 'PA' , 'leads') => __( 'Panama' , 'inbound-pro' ) ,
				 __( 'PG' , 'leads') => __( 'Papua New Guinea' , 'inbound-pro' ) ,
				 __( 'PY' , 'leads') => __( 'Paraguay' , 'inbound-pro' ) ,
				 __( 'PE' , 'leads') => __( 'Peru' , 'inbound-pro' ) ,
				 __( 'PH' , 'leads') => __( 'Philippines' , 'inbound-pro' ) ,
				 __( 'PN' , 'leads') => __( 'Pitcairn' , 'inbound-pro' ) ,
				 __( 'PL' , 'leads') => __( 'Poland' , 'inbound-pro' ) ,
				 __( 'PT' , 'leads') => __( 'Portugal' , 'inbound-pro' ) ,
				 __( 'PR' , 'leads') => __( 'Puerto Rico' , 'inbound-pro' ) ,
				 __( 'QA' , 'leads') => __( 'Qatar' , 'inbound-pro' ) ,
				 __( 'RE' , 'leads') => __( 'Reunion' , 'inbound-pro' ) ,
				 __( 'RO' , 'leads') => __( 'Romania' , 'inbound-pro' ) ,
				 __( 'RU' , 'leads') => __( 'Russian Federation' , 'inbound-pro' ) ,
				 __( 'RW' , 'leads') => __( 'Rwanda' , 'inbound-pro' ) ,
				 __( 'BL' , 'leads') => __( 'Saint Barthelemy' , 'inbound-pro' ) ,
				 __( 'SH' , 'leads') => __( 'Saint Helena' , 'inbound-pro' ) ,
				 __( 'KN' , 'leads') => __( 'Saint Kitts and Nevis' , 'inbound-pro' ) ,
				 __( 'LC' , 'leads') => __( 'Saint Lucia' , 'inbound-pro' ) ,
				 __( 'VC' , 'leads') => __( 'Saint Vincent and the Grenadines' , 'inbound-pro' ) ,
				 __( 'MF' , 'leads') => __( 'Saint-Martin (France)' , 'inbound-pro' ) ,
				 __( 'SX' , 'leads') => __( 'Saint-Martin (Pays-Bas)' , 'inbound-pro' ) ,
				 __( 'WS' , 'leads') => __( 'Samoa' , 'inbound-pro' ) ,
				 __( 'SM' , 'leads') => __( 'San Marino' , 'inbound-pro' ) ,
				 __( 'ST' , 'leads') => __( 'Sao Tome and Principe' , 'inbound-pro' ) ,
				 __( 'SA' , 'leads') => __( 'Saudi Arabia' , 'inbound-pro' ) ,
				 __( 'SN' , 'leads') => __( 'Senegal' , 'inbound-pro' ) ,
				 __( 'RS' , 'leads') => __( 'Serbia' , 'inbound-pro' ) ,
				 __( 'SC' , 'leads') => __( 'Seychelles' , 'inbound-pro' ) ,
				 __( 'SL' , 'leads') => __( 'Sierra Leone' , 'inbound-pro' ) ,
				 __( 'SG' , 'leads') => __( 'Singapore' , 'inbound-pro' ) ,
				 __( 'SK' , 'leads') => __( 'Slovakia (Slovak Republic)' , 'inbound-pro' ) ,
				 __( 'SI' , 'leads') => __( 'Slovenia' , 'inbound-pro' ) ,
				 __( 'SB' , 'leads') => __( 'Solomon Islands' , 'inbound-pro' ) ,
				 __( 'SO' , 'leads') => __( 'Somalia' , 'inbound-pro' ) ,
				 __( 'ZA' , 'leads') => __( 'South Africa' , 'inbound-pro' ) ,
				 __( 'GS' , 'leads') => __( 'South Georgia and the South Sandwich Islands' , 'inbound-pro' ) ,
				 __( 'KR' , 'leads') => __( 'South Korea' , 'inbound-pro' ) ,
				 __( 'SS' , 'leads') => __( 'South Sudan' , 'inbound-pro' ) ,
				 __( 'ES' , 'leads') => __( 'Spain' , 'inbound-pro' ) ,
				 __( 'LK' , 'leads') => __( 'Sri Lanka' , 'inbound-pro' ) ,
				 __( 'PM' , 'leads') => __( 'St. Pierre and Miquelon' , 'inbound-pro' ) ,
				 __( 'SD' , 'leads') => __( 'Sudan' , 'inbound-pro' ) ,
				 __( 'SR' , 'leads') => __( 'Suriname' , 'inbound-pro' ) ,
				 __( 'SJ' , 'leads') => __( 'Svalbard and Jan Mayen Islands' , 'inbound-pro' ) ,
				 __( 'SZ' , 'leads') => __( 'Swaziland' , 'inbound-pro' ) ,
				 __( 'SE' , 'leads') => __( 'Sweden' , 'inbound-pro' ) ,
				 __( 'CH' , 'leads') => __( 'Switzerland' , 'inbound-pro' ) ,
				 __( 'SY' , 'leads') => __( 'Syria' , 'inbound-pro' ) ,
				 __( 'TW' , 'leads') => __( 'Taiwan' , 'inbound-pro' ) ,
				 __( 'TJ' , 'leads') => __( 'Tajikistan' , 'inbound-pro' ) ,
				 __( 'TZ' , 'leads') => __( 'Tanzania' , 'inbound-pro' ) ,
				 __( 'TH' , 'leads') => __( 'Thailand' , 'inbound-pro' ) ,
				 __( 'NL' , 'leads') => __( 'The Netherlands' , 'inbound-pro' ) ,
				 __( 'TL' , 'leads') => __( 'Timor-Leste' , 'inbound-pro' ) ,
				 __( 'TG' , 'leads') => __( 'Togo' , 'inbound-pro' ) ,
				 __( 'TK' , 'leads') => __( 'Tokelau' , 'inbound-pro' ) ,
				 __( 'TO' , 'leads') => __( 'Tonga' , 'inbound-pro' ) ,
				 __( 'TT' , 'leads') => __( 'Trinidad and Tobago' , 'inbound-pro' ) ,
				 __( 'TN' , 'leads') => __( 'Tunisia' , 'inbound-pro' ) ,
				 __( 'TR' , 'leads') => __( 'Turkey' , 'inbound-pro' ) ,
				 __( 'TM' , 'leads') => __( 'Turkmenistan' , 'inbound-pro' ) ,
				 __( 'TC' , 'leads') => __( 'Turks and Caicos Islands' , 'inbound-pro' ) ,
				 __( 'TV' , 'leads') => __( 'Tuvalu' , 'inbound-pro' ) ,
				 __( 'UG' , 'leads') => __( 'Uganda' , 'inbound-pro' ) ,
				 __( 'UA' , 'leads') => __( 'Ukraine' , 'inbound-pro' ) ,
				 __( 'AE' , 'leads') => __( 'United Arab Emirates' , 'inbound-pro' ) ,
				 __( 'GB' , 'leads') => __( 'United Kingdom' , 'inbound-pro' ) ,
				 __( 'US' , 'leads') => __( 'United States' , 'inbound-pro' ) ,
				 __( 'UM' , 'leads') => __( 'United States Minor Outlying Islands' , 'inbound-pro' ) ,
				 __( 'UY' , 'leads') => __( 'Uruguay' , 'inbound-pro' ) ,
				 __( 'UZ' , 'leads') => __( 'Uzbekistan' , 'inbound-pro' ) ,
				 __( 'VU' , 'leads') => __( 'Vanuatu' , 'inbound-pro' ) ,
				 __( 'VA' , 'leads') => __( 'Vatican' , 'inbound-pro' ) ,
				 __( 'VE' , 'leads') => __( 'Venezuela' , 'inbound-pro' ) ,
				 __( 'VN' , 'leads') => __( 'Vietnam' , 'inbound-pro' ) ,
				 __( 'VG' , 'leads') => __( 'Virgin Islands (British)' , 'inbound-pro' ) ,
				 __( 'VI' , 'leads') => __( 'Virgin Islands (U.S.)' , 'inbound-pro' ) ,
				 __( 'WF' , 'leads') => __( 'Wallis and Futuna Islands' , 'inbound-pro' ) ,
				 __( 'EH' , 'leads') => __( 'Western Sahara' , 'inbound-pro' ) ,
				 __( 'YE' , 'leads') => __( 'Yemen' , 'inbound-pro' ) ,
				 __( 'ZM' , 'leads') => __( 'Zambia' , 'inbound-pro' ) ,
				 __( 'ZW' , 'leads') => __( 'Zimbabwe' , 'inbound-pro' )
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
