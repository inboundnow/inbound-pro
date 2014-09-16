<?php

// replacing wp_cta_render_metabox
function inbound_template_metabox_render( $plugin , $key , $custom_fields , $post)
{
	switch ($plugin) {
		case "cta" :
			$prefix = 'wp_cta';
			$prefix_dash = 'wp-cta';
			$CTAExtensions = CTA_Load_Extensions();
			$extension_data = $CTAExtensions->definitions;
			break;
	}


	// Use nonce for verification
	echo "<input type='hidden' name='{$prefix}_{$key}_custom_fields_nonce' value='".wp_create_nonce(''.$prefix_dash.'-nonce')."' />";

	// Begin the field table and loop
	echo '<div class="form-table" id="inbound-meta">';

	//print_r($custom_fields);exit;
	$current_var = wp_cta_ab_testing_get_current_variation_id();
	
	foreach ($custom_fields as $field) 
	{
		$field_id = $key . "-" .$field['id'];
		$label_class = $field['id'] . "-label";
		$type_class = " inbound-" . $field['type'];
		$type_class_row = " inbound-" . $field['type'] . "-row";
		$type_class_option = " inbound-" . $field['type'] . "-option";
		$option_class = (isset($field['class'])) ? $field['class'] : ''; 

		$meta = get_post_meta($post->ID, $field_id, true);

		//print_r($field);
		if (!metadata_exists('post',$post->ID,$field_id))
		{
			$meta = $field['default'];
		}

        // Remove prefixes on global => true template options
        if (isset($field['global']) && $field['global'] === true) {
			$field_id = $field['id'];
			$meta = get_post_meta($post->ID, $field['id'] , true);
        }

		// begin a table row with
		echo '<div class="'.$field['id'].$type_class_row.' div-'.$option_class.' wp-call-to-action-option-row inbound-meta-box-row">';
				if ($field['type'] != "description-block" && $field['type'] != "custom-css" ) {
				echo '<div id="inbound-'.$field_id.'" data-actual="'.$field_id.'" class="inbound-meta-box-label wp-call-to-action-table-header '.$label_class.$type_class.'"><label for="'.$field_id.'">'.$field['label'].'</label></div>';
				}

				echo '<div class="wp-call-to-action-option-td inbound-meta-box-option '.$type_class_option.'" data-field-type="'.$field['type'].'">';
				switch($field['type']) {
					// default content for the_content
					case 'default-content':
						echo '<span id="overwrite-content" class="button-secondary">Insert Default Content into main Content area</span><div style="display:none;"><textarea name="'.$field_id.'" id="'.$field_id.'" class="default-content" cols="106" rows="6" style="width: 75%; display:hidden;">'.$meta.'</textarea></div>';
						break;
					case 'description-block':
						echo '<div id="'.$field_id.'" class="description-block">'.$field['description'].'</div>';
						break;
					case 'custom-css':
						echo '<style type="text/css">'.$field['default'].'</style>';
						break;
					// text
					case 'colorpicker':
						if (!$meta)
						{
							$meta = $field['default'];
						}
						$var_id = (isset($_GET['new_meta_key'])) ? "-" . $_GET['new_meta_key'] : '';
						echo '<input type="text" class="jpicker" style="background-color:#'.$meta.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="5" /><span class="button-primary new-save-wp-cta" data-field-type="text" id="'.$field_id.$var_id.'" style="margin-left:10px; display:none;">Update</span>
								<div class="wp_cta_tooltip tool_color" title="'.$field['description'].'"></div>';
						break;
					case 'datepicker':
						echo '<div class="jquery-date-picker inbound-datepicker" id="date-picking" data-field-type="text">
						<span class="datepair" data-language="javascript">
									Date: <input type="text" id="date-picker-'.$key.'" class="date start" /></span>
									Time: <input id="time-picker-'.$key.'" type="text" class="time time-picker" />
									<input type="hidden" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" class="new-date" value="" >
									<p class="description">'.$field['description'].'</p>
							</div>';
						break;
					case 'text':
						echo '<input type="text" class="'.$option_class.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
								<div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
						break;
					case 'number':

						echo '<input type="number" class="'.$option_class.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
								<div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';

						break;
					// textarea
					case 'textarea':
						echo '<textarea name="'.$field_id.'" id="'.$field_id.'" cols="106" rows="6" style="width: 75%;">'.$meta.'</textarea>
								<div class="wp_cta_tooltip tool_textarea" title="'.$field['description'].'"></div>';
						break;
					// wysiwyg
					case 'wysiwyg':
						echo "<div class='iframe-options iframe-options-".$field_id."' id='".$field['id']."'>";
						wp_editor( $meta, $field_id, $settings = array( 'editor_class' => $field['id'] ) );
						echo	'<p class="description">'.$field['description'].'</p></div>';
						break;
					// media
					case 'media':
						//echo 1; exit;
						echo '<label for="upload_image" data-field-type="text">';
						echo '<input name="'.$field_id.'"  id="'.$field_id.'" type="text" size="36" name="upload_image" value="'.$meta.'" />';
						echo '<input class="upload_image_button" id="uploader_'.$field_id.'" type="button" value="Upload Image" />';
						echo '<p class="description">'.$field['description'].'</p>';
						break;
					// checkbox
					case 'checkbox':
						$i = 1;
						echo "<table class='wp_cta_check_box_table'>";
						if (!isset($meta)){$meta=array();}
						elseif (!is_array($meta)){
							$meta = array($meta);
						}
						foreach ($field['options'] as $value=>$label) {
							if ($i==5||$i==1)
							{
								echo "<tr>";
								$i=1;
							}
								echo '<td data-field-type="checkbox"><input type="checkbox" name="'.$field_id.'[]" id="'.$field_id.'" value="'.$value.'" ',in_array($value,$meta) ? ' checked="checked"' : '','/>';
								echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
							if ($i==4)
							{
								echo "</tr>";
							}
							$i++;
						}
						echo "</table>";
						echo '<div class="wp_cta_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
					break;
					// radio
					case 'radio':
						foreach ($field['options'] as $value=>$label) {
							//echo $meta.":".$field_id;
							//echo "<br>";
							echo '<input type="radio" name="'.$field_id.'" id="'.$field_id.'" value="'.$value.'" ',$meta==$value ? ' checked="checked"' : '','/>';
							echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
						}
						echo '<div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
					break;
					// select
					case 'dropdown':
						echo '<select name="'.$field_id.'" id="'.$field_id.'" class="'.$field['id'].'">';
						foreach ($field['options'] as $value=>$label) {
							echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
						}
						echo '</select><div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
					break;
					case 'image-select':
						echo '<select name="'.$field_id.'" id="'.$field_id.'" class="image-picker">';
						foreach ($field['options'] as $value=>$label) {
							echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="'.$value.'" data-img-src="'.$extension_data[$key]['info']['urlpath'].'assets/img/'.$value.'.'.$field['image_type'].'" >'.$label.'</option>';
						}
						echo '</select><div class="wp-cta-image-container" style="display:inline;min-height:200px;margin-top:10px;"></div><div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
					break;



				} //end switch
		echo '</div></div>';
	} // end foreach
	echo '</div>'; // end table
	//exit;
}
