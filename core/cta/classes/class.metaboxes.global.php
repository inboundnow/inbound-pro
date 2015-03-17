<?php

/**
 * Metaboxes that apply to other post types
 *
 * @package	Calls To Action
 * @subpackage	Metaboxes
*/


if (!class_exists('CTA_Metaboxes_Global')) {

	class CTA_Metaboxes_Global {

		/**
		*  initializes class
		*/
		function __construct() {
			self::load_hooks();
		}
		
		/**
		*  Loads hooks and filters
		*/
		public static function load_hooks() {
			/* Add metaboxes */
			add_action('add_meta_boxes', array( __CLASS__ , 'load_metaboxes' ) );	
						
			/* Saves all all incoming POST data as meta pairs */
			add_action( 'save_post' , array( __CLASS__ , 'save_data' ) );
			
		}
		
		/**
		*  Defines post types to not add CTA setup metaboxes to.
		*/
		public static function get_excluded_post_types() {
			
			$exclude[] = 'attachment';
			$exclude[] = 'revisions';
			$exclude[] = 'nav_menu_item';
			$exclude[] = 'wp-lead';
			$exclude[] = 'automation';
			$exclude[] = 'rule';
			$exclude[] = 'list';
			$exclude[] = 'wp-call-to-action';
			$exclude[] = 'tracking-event';
			$exclude[] = 'inbound-forms';
			$exclude[] = 'email-template';
			$exclude[] = 'inbound-log';
			$exclude[] = 'inbound-email';
			$exclude[] = 'landing-page';
			$exclude[] = 'edd-license';
			$exclude[] = 'acf-field-group';
			
			$exclude = apply_filters( 'cta_excluded_post_types' , $exclude);
			
			return $exclude;
		}
		
		/**
		*  	Loads Metaboxes 
		*/
		public static function load_metaboxes() {
			$post_types= get_post_types('','names');

			$exclude = self::get_excluded_post_types();
			
			/*  Display's CTA Placement Metabox on post types	*/
			foreach ($post_types as $value ) {
				$priority = ($value === 'landing-page') ? 'core' : 'high';
				
				if (!in_array($value,$exclude)) {
					add_meta_box(
						'wp-cta-inert-to-post', 
						__( 'Insert Call to Action Template into Content' , 'cta' ) ,
						array( __CLASS__ , 'display_cta_placement_metabox' ) , 
						$value, 
						'normal', 
						$priority 
					);
				}
			}
		}
	
		/**
		*  Display's CTA Placement Metabox
		*/
		public static function display_cta_placement_metabox() {
			global $post;
	
			$args = array(
				'posts_per_page'  => -1,
				'post_type'=> 'wp-call-to-action'
			);
			
			$cta_list = get_posts($args);
			
			$cta_display_list = get_post_meta($post->ID ,'cta_display_list', true);
			$cta_display_list = ($cta_display_list != '') ? $cta_display_list : array();

			?>
			<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				function format(state) {
					if (!state.id) return state.text; // optgroup
					var href = jQuery("#cta-" + state.id).attr("href");
					return state.text + "<a class='thickbox cta-select-preview-link' href='" + href + "'>(view)</a>";
				}
				jQuery("#cta_template_selection").select2({
					placeholder: " <?php _e( 'Select one or more calls to action to rotate through' , 'cta' ); ?>",
					allowClear: true,
					formatResult: format,
					formatSelection: format,
					escapeMarkup: function(m) { return m; }
				});
				// show conditional fields
				jQuery('select#cta_content_placement').on('change', function () {
					var this_val = jQuery(this).val();
					jQuery(".dynamic-visable-on").hide();
					console.log(this_val);
					jQuery('.reveal-' + this_val).removeClass('inbound-hidden-row').show().addClass('dynamic-visable-on');
				});
				var onload = jQuery('select#cta_content_placement').val();
				jQuery('.reveal-' + onload).removeClass('inbound-hidden-row').show().addClass('dynamic-visable-on');
			});
			</script>
			<style type="text/css">
				.select2-container {
					width: 100%;
					padding-top: 15px;
				}
				.inbound-hidden-row {
					display: none;
				}
				.wp-cta-option-row {
					padding-top: 5px;
					padding-bottom: 5px;
				}
				.wp_cta_label.cta-per-page-option, .wp-cta-option-area.cta-per-page-option {
				display: inline-block;
				}
				label.cta-per-page-option {
					width: 190px;
					padding-left: 12px;
					display: inline-block;
				}
				.cta-options-label {
					width: 190px;

					display: inline-block;
					vertical-align: top;
					padding-top: 20px;
				}
				.cta-options-row {
				width: 65%;
				display: inline-block;
				}
				.cta-select-preview-link {
					font-size: 10px;
					 padding-left: 5px;
					vertical-align: middle;
				}
				.select2-highlighted a.cta-select-preview-link {
					color: #fff !important;
				}
				.cta-links-hidden {
					display: none;
				}
			</style>
			<div class='wp_cta_select_display'>
				<div class="inside">
					<div class="wp-cta-option-row">
						<div class='cta-options-label'>
							<label for=keyword>
							<?php _e( 'Call to Action Template' , 'cta' ); ?>
							</label>
						</div>
						<div class='cta-options-row'>
						<?php
						 foreach ( $cta_list as $cta ) {
							$this_id = $cta->ID;
							$this_link = get_permalink( $this_id );
							$this_link = preg_replace('/\?.*/', '', $this_link); ?>

							<a class='thickbox cta-links-hidden' id="cta-<?php echo $this_id;?>" href='<?php echo $this_link;?>?wp-cta-variation-id=0&wp_cta_iframe_window=on&post_id=<?php echo $cta->ID; ?>&TB_iframe=true&width=640&height=703'>Preview</a>

						<?php } ?>
						<select multiple name='cta_display_list[]' id="cta_template_selection" style='display:none;'>
						<?php
						foreach ( $cta_list as $cta  ) {
							$this_id = $cta->ID;
							$this_link = get_permalink( $this_id );
							$title = $cta->post_title;
							$selected = (in_array($this_id, $cta_display_list)) ? " selected='selected'" : "";

							echo '<option', $selected, ' value="'.$this_id.'" rel="work?" >'.$title.'</option>';

						} ?>
						</select><br /><span class="description"><?php _e( 'Click the above select box to select call to action templates to insert' , 'cta' ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<?php
			/* Renders extended settings */
			self::render_additional_settings();
		
		}
		
		/**
		*  Looks for extended settings and renders them
		*/
		public static function render_additional_settings() {
			$CTAExtensions = CTA_Load_Extensions();
			$extension_data = $CTAExtensions->definitions;
			
			foreach ($extension_data['wp-cta-controller']['settings'] as $key=>$field)
			{
				if ( isset($field['region']) && $field['region'] =='cta-placement-controls')
				{
					self::render_setting($field);
				}
			}
		}
		
		/** 
		*  Renders setting from extended field data
		*  @param ARRAY $field
		*/
		public static function render_setting($field) {
			global $post, $wpdb;

			$meta = get_post_meta($post->ID, $field['id'], true);

			if ( !isset( $field['default'] ) ) {
				$field['default'] = '';
			}

			$final['value'] = ( !empty($meta) || is_numeric( $meta ) ) ? $meta : $field['default'];

			$meta_class = (isset($field['class'])) ? " " . $field['class'] : '';
			$dynamic_hide = (isset($field['reveal_on'])) ? ' inbound-hidden-row' : '';
			$reveal_on = (isset($field['reveal_on'])) ? ' reveal-' . $field['reveal_on'] : '';

			// begin a table row with
			$no_label = array('html-block');

			echo '<div id='.$field['id'].' class="wp-cta-option-row '.$meta_class. $dynamic_hide.	$reveal_on.'">';
			if (!in_array($field['type'],$no_label)) {
				echo'<div class="wp_cta_label'.$meta_class. $dynamic_hide.	$reveal_on.'"><label class="'.$meta_class.'" for="'.$field['id'].'">'.$field['label'].'</label></div>';
			}
			echo '<div class="wp-cta-option-area '.$meta_class.' field-'.$field['type'].'">';
				switch($field['type']) {
					// text
					case 'text':
						echo '<input type="text" class="'.$meta_class.'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$final['value'].'" size="30" />
								<div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
						break;
					case 'colorpicker':
						echo '<input type="text" class="jpicker '.$meta_class.'" style="background-color:#'.$final['value'].'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$final['value'].'" size="5" />
								<div class="wp_cta_tooltip tool_color" title="'.$field['description'].'"></div>';
						break;
					case 'html-block':
						echo '<div class="'.$meta_class.'">'.$field['description'].'</div>';
						break;
					case 'dropdown':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'" class="'.$meta_class.'">';
						foreach ($field['options'] as $value=>$label) {
							echo '<option', $final['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
						}
						echo '</select><div class="wp_cta_tooltip" title="'.$field['description'].'"></div>';
						break;
					// select
					case 'image-select':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'" class="'.$meta_class.'">';
						foreach ($field['options'] as $value=>$label) {
							echo '<option', $final['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
						}
						echo '</select><br /><div class="wp-cta-image-container"></div></br><span class="description">'.$field['description'].'</span>';

						break;
					// textarea
					case 'textarea':
						echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="250" rows="6">'.$final['value'].'</textarea>
								<br /><span class="description">'.$field['description'].'</span>';
						break;
					// checkbox
					case 'checkbox':
						echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ' , $final['value'] ? ' checked="checked"' : '','/>
								<label for="'.$field['id'].'">'.$field['description'].'</label>';
						break;
					// radio
					case 'radio':
						foreach ( $field['options'] as $option ) {
							echo '<input type="radio" name="'.$field['id'].'" id="'.$option['value'].'" value="'.$option['value'].'" ',$final['value'] == $option['value'] ? ' checked="checked"' : '',' />
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '<span class="description">'.$field['description'].'</span>';
						break;
					// checkbox_group
					case 'checkbox_group':
						foreach ($field['options'] as $option) {
							echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$final['value'] && in_array($option['value'], $final['value']) ? ' checked="checked"' : '',' />
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '<span class="description">'.$field['description'].'</span>';
						break;
					case 'meta_vals':
						$post_type = 'wp-lead';
						$query = "
							SELECT DISTINCT($wpdb->postmeta.meta_key)
							FROM $wpdb->posts
							LEFT JOIN $wpdb->postmeta
							ON $wpdb->posts.ID = $wpdb->postmeta.post_id
							WHERE $wpdb->posts.post_type = 'wp-lead'
							AND $wpdb->postmeta.meta_key != ''
							AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
							AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
						";
						$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta;
						$meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
						// print_r($fields);
						$list = get_post_meta( $post->ID, 'wp_cta_global_bt_values', true);
						//print_r($list);
						echo '<select multiple name="'.$field['id'].'[]" class="inbound-multi-select" id="'.$field['id'].'">';
							$nice_names = array(
							"wpleads_first_name" => "First Name",
							"wpleads_last_name" => "Last Name",
							"wpleads_email_address" => "Email Address",
							"wpleads_city" => "City",
							"wpleads_areaCode" => "Area Code",
							"wpleads_country_name" => "Country Name",
							"wpleads_region_code" => "State Abbreviation",
							"wpleads_region_name" => "State Name",
							"wp_lead_status" => "Lead Status",
							"events_triggered" => "Number of Events Triggered",
							"lp_page_views_count" => "Page View Count",
							"wpl-lead-conversion-count" => "Number of Conversions"
						);

						foreach ($meta_keys as $meta_key)
						{
							if (array_key_exists($meta_key, $nice_names)) {
								$label = $nice_names[$meta_key];


								(in_array($meta_key, $list)) ? $selected = " selected='selected'" : $selected ="";

								echo '<option', $selected, ' value="'.$meta_key.'" rel="" >'.$label.'</option>';

							}
						}
						echo "</select><br><span class='description'>'".$field['description']."'</span>";
					break;

					case 'multiselect':

						$selected_lists = $final['value'];

						echo '<select multiple name="'.$field['id'].'[]" class="inbound-multi-select" id="'.$field['id'].'">';


						foreach ( $field['options'] as $id => $value )
						{
							(in_array($id, $selected_lists)) ? $selected = " selected='selected'" : $selected ="";
							echo '<option', $selected, ' value="'.$id.'" rel="" >'.$value.'</option>';

						}
						echo "</select><br><span class='description'>'".$field['description']."'</span>";
						break;

				} //end switch
			echo '</div></div>';
		}
		
		/**
		*  Saves related metadata
		*/
		public static function save_data( $post_id ) {
			global $post;

			if (!isset($post)){
				return;
			}
			
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
				return;
			}

			$exclude = self::get_excluded_post_types();

			if (in_array($post->post_type , $exclude)) {
				return;
			}

				
			$CTAExtensions = CTA_Load_Extensions();
			$extension_data = $CTAExtensions->definitions;
			
			foreach ($extension_data['wp-cta-controller']['settings'] as $key=>$field)
			{
				( isset($field['global']) && $field['global'] ) ? $field['id'] : $field['id'] = $field['id'];	
						
				if($field['type'] == 'tax_select'){
					continue;
				}		
				
				$old = get_post_meta($post_id, $field['id'], true);
				(isset($_POST[$field['id']])) ? $new = $_POST[$field['id']] : $new = null;
				
				/*
				echo $field['id'].' old:'.$old.'<br>';
				echo $field['id'].' new:'.$new.'<br>';
				*/
				
				if (isset($new) && $new != $old ) {
					update_post_meta($post_id, $field['id'], $new);
				} elseif ('' == $new && $old) {
					delete_post_meta($post_id, $field['id'], $old);
				}
				
			}

			if ( isset($_POST['cta_display_list']) ) {
				update_post_meta($post_id, "cta_display_list", $_POST['cta_display_list'] );
			} else {
				delete_post_meta($post_id, "cta_display_list" ); // remove empty checkboxes
			}

			if ( isset($_POST['cta_alignment']) ) { // if we get new data
				update_post_meta($post_id, "cta_alignment", $_POST['cta_alignment'] );
			} else {
				delete_post_meta($post_id, "cta_alignment" );
			}
		}
	}

	$GLOBALS['CTA_Metaboxes_Global'] = new CTA_Metaboxes_Global;
}

