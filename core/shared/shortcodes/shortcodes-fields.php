<?php

if ( !class_exists('Inbound_Shortcodes_Fields') ) {

	/* 	Include wp-load
	* 	----------------------------------------------------- */
	/* get_home_path causes problems
	if (function_exists('get_home_path')) {
		$path_to_wp = get_home_path();
		if ( ! file_exists( $path_to_wp . '/wp-load.php' ) ) {
	} else {*/
		$path_to_file = explode( 'wp-content', __FILE__ );
		$path_to_wp = $path_to_file[0];
	/*}*/


	require_once( $path_to_wp . '/wp-load.php' );

	/* 	The Class
	* 	----------------------------------------------------- */
	class Inbound_Shortcodes_Fields {

	/* 	Variables
	* 	----------------------------------------------------- */
		var	$popup,
			$options,
			$shortcode,
			$child_options,
			$child_shortcode,
			$popup_title,
			$no_preview,
			$has_child,
			$output,
			$errors;

	/* 	Constuctor
	* 	----------------------------------------------------- */
		function __construct( $popup ) {
			$this->popup = $popup;
			$this->show();
		}

	/* 	Show Fields
	* 	----------------------------------------------------- */
		function show() {

			global $shortcodes_config;

			$fields = apply_filters('inboundnow_forms_settings', $shortcodes_config);

			if( isset( $fields[$this->popup]['child'] ) ) {
				$this->has_child = true;
			}

			if( isset( $fields ) && is_array( $fields ) ) {

				$this->options = $fields[$this->popup]['options'];
				$this->shortcode = $fields[$this->popup]['shortcode'];
				$this->popup_title = $fields[$this->popup]['popup_title'];

				$this->append_output('<div id="_inbound_shortcodes_output" class="hidden">'.$this->shortcode.'</div>');
				$this->append_output('<div id="_inbound_shortcodes_popup" class="hidden">'.$this->popup.'</div>');
				$this->append_output('<div id="cpt-form-serialize" class="hidden"></div>');


				if( isset( $fields[$this->popup]['no_preview'] ) && $fields[$this->popup]['no_preview'] ) {
					$this->append_output( "\n" . '<div id="_inbound_shortcodes_preview" class="hidden">false</div>' );
					$this->no_preview = true;
				}
				$count = 0;
				if(is_array($this->options)) {
				foreach( $this->options as $key => $option ) {
					$first = $key;

					$key = 'inbound_shortcode_' . $key;
					$uniquekey = 'inbound_shortcode_' . $first . "_" . $count;
					$name = ( isset($option['name'])) ? $option['name'] : '';
					$desc = ( isset($option['desc'])) ? $option['desc'] : '';
					$std = ( isset($option['std']) ) ? $option['std'] : '';
					$global = ( isset($option['global']) ) ? $option['global'] : '';

					//error_log(print_r($option,true));

					if ($global) {
						$uniquekey = $key;
					}

					$placeholder = (isset($option['placeholder'])) ? $option['placeholder'] : '';
					$parent_class = (isset($option['class'])) ? $option['class'] : '';

					$row_start	= '<tbody class="inbound_tbody inbound_shortcode_parent_tbody parent-'.$key.' '.$parent_class.'">';
					if ($key === "inbound_shortcode_form_name") {
					$row_start .= '<ol class="steps">
						<li class="step-item first active" data-display-options=".main-form-settings"><a href="#inbound_shortcode_parent_tbody" class="step-link">Main Form Settings</a></li>
						<li class="step-item" data-display-options=".inbound_shortcode_child_tbody"><a href="#inbound_shortcode_child_tbody"	class="step-link">Edit/Add Fields</a></li>
						<li class="step-item last" data-display-options=".main-design-settings"><a href="#" class="step-link">Design & Layout</a></li>
					</ol>';
					}
					$row_start .= '<tr class="form-row">';
					$row_start .= '<td class="label">' . $name . '</td>';
					$row_start .= '<td class="field">';

					if( $option['type'] != 'checkbox' ) {
						$row_end = '<span class="inbound-shortcodes-form-desc">' . $desc . '</span>';
					}
					else {
						$row_end = '';
					}
					$row_end	.= '</td>';
					$row_end	.= '</tr>';
					$row_end	.= '</tbody>';

					switch( $option['type'] ) {

						case 'text':
							$output	= $row_start;
							$output .= '<input type="text" class="inbound-shortcodes-input '.$key.'" name="'. $uniquekey .'" id="'. $key .'" value="'. $std .'" size="40" placeholder="'.$placeholder.'" />';
							$output .= $row_end;
							$this->append_output($output);
							break;

						case 'hidden':
							$output	= $row_start;
							$output .= '<input type="hidden" class="inbound-shortcodes-input '.$key.'" name="'. $uniquekey .'" id="'. $key .'" value="'. $std .'" size="40" placeholder="'.$placeholder.'" />';
							$output .= $row_end;
							$this->append_output($output);
							break;

						case 'textarea' :
							$output	= $row_start;
							$output .= '<textarea class="inbound-shortcodes-input inbound-shortcodes-textarea" name="'. $key .'" id="'. $key .'" rows="5" cols="50">'. $std .'</textarea>';
							$output .= $row_end;
							$this->append_output($output);
							break;

						case 'select' :
							$output	= $row_start;
							$output .= '<select name="'. $key .'" id="'.$key.'" class="inbound-shortcodes-input select inbound-shortcodes-select">';
							if ( isset( $option['options']) && is_array($option['options'])  ) {
								foreach( $option['options'] as $val => $opt ) {
									$selected = ($std == $val) ? ' selected="selected"' : '';
									$output .= '<option'. $selected .' value="'. $val .'">'. $opt .'</option>';
								}
							}
							$output .= '</select>';
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'leadlists' :
							$output	= $row_start;
							$output .= '<select multiple name="'. $key .'" id="'.$key.'" class="inbound-shortcodes-input select inbound-shortcodes-select">';
							foreach( $option['options'] as $val => $opt ) {
								$selected = ($std == $val) ? ' selected="selected"' : '';
								$output .= '<option'. $selected .' value="'. $val .'">'. $opt .'</option>';
							}
							$output .= '</select>';
							$output .= '<div class="wp-hidden-children">
							<h4><a class="hide-if-no-js" href="#list-add" id="list-add-toggle"> + Add New Lead List </a></h4>
							<div class="list-add wp-hidden-child" id="list-add-wrap"><ul class="child-clone-row-form"><li>
							<label for="newcategory" class="screen-reader-text">Add New Lead List</label>
							<input type="text" aria-required="true" placeholder="New List Name" class="inbound-shortcodes-input inbound_shortcode_notify form-required" id="newformlist" name="newformlist" autocorrect="off" autocomplete="off" style="width: 80%;"></li>';
							$output .= '<li><label for="newlist_parent" class="screen-reader-text"> Parent List: </label><select class="postform" id="newlist_parent" name="newlist_parent"><option value="-1">&mdash; Parent List &mdash;</option>';
							$args = array('hide_empty' => false);
							$terms = get_terms('wplead_list_category', $args);
							foreach($terms as $term){
								$term_id=$term->term_id;
								$term_name =$term->name;
								$parent_level = ($term->parent == 0 ) ? '' : '-';
								$output .='<option value="'.$term_id.'" class="level-0">'.$parent_level.$term_name.'</option>';
							}
							$output .='</select></li>';
							$output .='<li><input type="button" value="Add New Lead List" class="button button-primary" data-wp-lists="add:listchecklist:list-add" id="list-add-submit"></li></ul><span id="list-ajax-response"></span></div></div>';
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'leadtags' :
							$output	= $row_start;
							$output .= '<select multiple name="'. $key .'" id="'.$key.'" class="inbound-shortcodes-input select inbound-shortcodes-select">';
							foreach( $option['options'] as $val => $opt ) {
								$selected = ($std == $val) ? ' selected="selected"' : '';
								$output .= '<option'. $selected .' value="'. $val .'">'. $opt .'</option>';
							}
							$output .= '</select>';
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'multiselect' :
							$output	= $row_start;
							$output .= '<select multiple name="'. $key .'" id="'.$key.'" class="inbound-shortcodes-input select inbound-shortcodes-select">';
							foreach( $option['options'] as $val => $opt ) {
								$selected = ($std == $val) ? ' selected="selected"' : '';
								$output .= '<option'. $selected .' value="'. $val .'">'. $opt .'</option>';
							}
							$output .= '</select>';
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'checkbox' :
							$output	= $row_start;
							$output .= '<label for="'.$key.'">';
							$output .= '<input type="checkbox" class="inbound-shortcodes-input inbound-shortcodes-checkbox" name="'.$key.'" id="'.$key.'"'. checked( $std, 1, false) .' />';
							$output .= '&nbsp;&nbsp;<span class="inbound-shortcodes-form-desc">';
							$output .= $desc .'</span></label>';
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'helper-block' :
							$output	= $row_start;
							$output .= $row_end;
							$this->append_output($output);
							break;
						case 'colorpicker':
							$output	= $row_start;
							$output .= '<input type="color" class="inbound-shortcodes-input '.$key.'" name="'. $uniquekey .'" id="'. $key .'" value="'. $std .'" size="40" placeholder="'.$placeholder.'" />';
							$output .= $row_end;
							$this->append_output($output);
							break;

						case 'cta' :
									$args = array('post_type' => 'wp-call-to-action', 'numberposts' => -1);
									$cta_post_type = get_posts($args);
									$output	= $row_start;
									$output .= '<select multiple name="insert_inbound_cta[]"" id="insert_inbound_cta">';
									foreach ($cta_post_type as $cta) {
										//setup_postdata($cta);
										$this_id = $cta->ID;
										$post_title = $cta->post_title;
										$this_link = get_permalink( $this_id );
										$this_link = preg_replace('/\?.*/', '', $this_link);
										//$output .= '<input class="checkbox" type="checkbox" value="" name="" id="" />' . $post_title . '<span id="view-cta-in-new-window">'.$this_link.'</span><br>';
										$output .= '<option value="'.$this_id.'" rel="" >'.$post_title.'</option>';
									}
								$output .= '</select></div></div>';
								$output .= $row_end;
								$this->append_output($output);
								break;
					}
					$count++;
				}
				}

				if( isset( $fields[$this->popup]['child'] ) ) {

					$this->child_options = $fields[$this->popup]['child']['options'];
					$this->child_shortcode = $fields[$this->popup]['child']['shortcode'];

					$parent_row_start	= '<tbody class="inbound_tbody inbound_shortcode_child_tbody">';
					$parent_row_start .= '<tr class="form-row has-child">';
					$parent_row_start .= '<td><a href="#" id="form-child-add" class="button button-secondary">'.$fields[$this->popup]['child']['clone'].'</a>';
					$parent_row_start .= '<div class="child-clone-rows">';
					$parent_row_start .= '<div id="_inbound_shortcodes_child_output" class="hidden">'.$this->child_shortcode.'</div>';
					$parent_row_start .= '<div id="field_instructions">Drag and drop fields to reorder.</div>';
					$parent_row_start .= '<div class="child-clone-row"><span class="form-field-row-number">1</span><span class="inbound_field_type"></span><a	class="child-clone-row-remove child-options-toggles">Remove</a><a	href="#" class="child-clone-row-shrink child-options-toggles ">Minimize</a><a	href="#" class="child-clone-row-exact child-options-toggles ">Clone</a>';
					$parent_row_start .= '<ul class="child-clone-row-form">';

					$this->append_output( $parent_row_start );
					$count = 1;
					foreach( $this->child_options as $key => $option ) {
						$first = $key;
						$uniquekey = 'inbound_shortcode_' . $first . "_" . $count;
						$hide_class = ($count > 1) ? 'minimize-class' : '';
						$text_class = ($count == 1) ? ' inbound-form-label-input' : '';
						$original_key = $key;
						$key = 'inbound_shortcode_' . $key;
						$name = ( isset($option['name'])) ? $option['name'] : '';
						$desc = ( isset($option['desc'])) ? $option['desc'] : '';
						$std = ( isset($option['std']) ) ? $option['std'] : '';
						$type = ( isset($option['type']) ) ? $option['type'] : '';
						$tab_class = (isset($option['class'])) ? " inbound-tab-class-".$option['class'] : '';
						$placeholder = (isset($option['placeholder'])) ? $option['placeholder'] : '';
						$field_class = (isset($option['class'])) ? ' ' . $option['class'] : '';
						$dynamic_hide = (isset($option['reveal_on'])) ? ' inbound-hidden-row' : '';
						$reveal_on = (isset($option['reveal_on'])) ? ' reveal-' . $option['reveal_on'] : '';

						$child_row_start	= '<li class="child-clone-row-form-row '.$hide_class . $dynamic_hide . $reveal_on. $tab_class.'">';
						$child_row_start .= '<div class="child-clone-row-label row-class-'.$type.'">';
						$child_row_start .= '<label>' . $option['name'] . '</label>';
						$child_row_start .= '</div>';
						$child_row_start .= '<div class="child-clone-row-field row-class-'.$type.' row-child-class-'.$type.'">';

						if( $option['type'] != 'checkbox' ) {
							$child_row_end		= '<span class="child-clone-row-desc">'.$desc.'</span>';
						}
						else {
							$child_row_end		= '';
						}
						$child_row_end	.= '</div>';
						$child_row_end	.= '</li>';

						switch( $option['type'] ) {

							case 'helper-block' :
								$child_output	= $child_row_start;

								$child_output .= $child_row_end;
								$this->append_output($child_output);
								break;

							case 'text' :
								$child_output	= $child_row_start;
								$child_output .= '<input type="text" data-conditional-hide="'.$reveal_on.'" class="inbound-shortcodes-child-input'.$text_class.'" name="'. $uniquekey .'" id="'. $key .'" placeholder="'.$placeholder.'" value="'. $std .'" />';
								$child_output .= $child_row_end;
								$this->append_output($child_output);
								break;

							case 'textarea' :
								$child_output	= $child_row_start;
								$child_output .= '<textarea class="inbound-shortcodes-child-input inbound-shortcodes-textarea" name="'. $uniquekey .'" id="'. $key .'">'. $std .'</textarea>';
								$child_output .= $child_row_end;
								$this->append_output($child_output);
								break;

							case 'select' :
								$child_output	= $child_row_start;
								$child_output .= '<select data-field-name="'.$original_key.'" name="'. $uniquekey .'" id="'. $key .'" class="inbound-shortcodes-child-input select inbound-shortcodes-select '.$field_class.'">';
								foreach( $option['options'] as $value => $option ) {
									$selected = ( $std == $value ) ? ' selected="selected"' : '';
									$child_output .= '<option'. $selected .' value="'. $value .'">'. $option .'</option>';
								}
								$child_output .= '</select>';
								$child_output .= $child_row_end;
								$this->append_output($child_output);
								break;

							case 'checkbox' :
								$child_output	= $child_row_start;
								$child_output .= '<label for="'.$key.'">';
								$child_output .= '<input type="checkbox" class="inbound-shortcodes-child-input inbound-shortcodes-checkbox" name="'. $uniquekey .'" id="'. $key .'" '. checked( $std, 1, false) .' />';
								$child_output .= $desc.'</label>';
								$child_output .= $child_row_end;
								$this->append_output($child_output);
								break;
						}
						$count++;
					}

					$parent_row_end	= '</ul>';
					$parent_row_end	.= '</div>';
					$parent_row_end	.= '</div>';
					$parent_row_end	.= '</td>';
					$parent_row_end	.= '</tr>';
					$parent_row_end	.= '</tbody>';

					$this->append_output( $parent_row_end );
				}
			}
		}

		function append_output( $output ) {
			$this->output = $this->output . $output;
		}

		function reset_output( $output ) {
			$this->output = '';
		}

		function append_error( $error ) {
			$this->errors = $this->errors . $error;
		}

	}

}