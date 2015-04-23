<?php
/**
 * Prepare Landing Page Form Metabox
 * Roll into /shared/
 */

if (!class_exists('Landing_Pages_Metaboxes')) {

	/**
	*  Adds impression and conversion tracking statistics to all pieces of content
	*/
	class Landing_Pages_Metaboxes {

		/**
		*  Initiate class
		*/
		public function __construct() {
			self::load_constants();
			self::load_hooks();
		}

		/**
		*  Load constants and static variables
		*/
		public static function load_constants() {

		}

		/**
		*  load hooks and filters
		*/
		public static function load_hooks() {
			/* add statistics metabox to non landing-page post types */
			add_action( 'add_meta_boxes' , array( __CLASS__ , 'add_meta_boxes' ) , 10 );

			/* Save custom js and css */
			add_action('save_post', array( __CLASS__ , 'save_data' ) );

			/* add main headline input */
			add_action( 'edit_form_after_title', array( __CLASS__ , 'display_main_headline' ) );

			/* Change default blank title text */
			add_filter( 'enter_title_here',  array( __CLASS__ , 'change_post_title_placeholder_text' ) , 10, 2 );

			/* Add hidden template selection box */
			add_action('admin_notices', array( __CLASS__ , 'display_template_select' ) );
		}

		/**
		*  Add metaboxes
		*/
		public static function add_meta_boxes() {

			/* add template preview */
			add_meta_box(
				'lp-thumbnail-sidebar-preview',
				__( 'Selected Template', 'landing-pages'),
				array( __CLASS__ , 'display_template_thumbnail_metabox' ) ,
				'landing-page' ,
				'side',
				'low'
			);

			/* add conversion area box */
			add_meta_box(
				'lp_2_form_content',
				__('Landing Page Form or Conversion Button - <em>click the black & blue power button icon to build forms/buttons</em>', 'landing-pages'),
				array( __CLASS__ , 'display_conversion_area' ),
				'landing-page',
				'normal',
				'high'
			);

			/* add template select */
			add_meta_box(
				'lp_metabox_select_template', // $id
				__( 'Template Setting', 'landing-pages'),
				array( __CLASS__ , 'display_template_options' ), // $callback
				'landing-page', // $page
				'normal', // $context
				'high'
			); // $priority

			/* add custom js */
			add_meta_box(
				'lp_3_custom_js',
				__('Custom JS' , 'landing-pages') ,
				array( __CLASS__ , 'display_custom_js' ),
				'landing-page',
				'normal',
				'low'
			);

			/* add custom css */
			add_meta_box(
				'lp_3_custom_css',
				__( 'Custom CSS' , 'landing-pages') ,
				array( __CLASS__ , 'display_custom_css' ),
				'landing-page',
				'normal',
				'low'
			);

			/* display template options */
			$extension_data = lp_get_extension_data();
			$current_template = self::get_selected_template();
			if ( isset($extension_data[ $current_template ] ) ) {
				add_meta_box(
					"lp_{$current_template}_custom_meta_box", // $id
					__( sprintf( "<small>%s Options:</small>" , $extension_data[ $current_template ]['label'] ) , 'landing-pages'),
					array( __CLASS__ , 'render_metabox' ),
					'landing-page',
					'normal',
					'default',
					array( 'key' => $current_template )
				);
			}

			/* render extended metaboxes */
			foreach ($extension_data as $key=>$data) {
				if ( isset($data['info']['data_type']) || $data['info']['data_type'] !='metabox' ) {
					return;
				}
					//echo 1; exit;
					$id = "metabox-".$key;


					$position = (isset($data['info']['position'])) ? $data['info']['position'] : "normal";
					$priority = (isset($data['info']['priority'])) ? $data['info']['priority'] : "default";

					add_meta_box(
						"lp_{$key}_custom_meta_box",
						__( "$name", 'landing-pages'),
						array( __CLASS__ , 'render_metabox' ),
						'landing-page',
						$position ,
						$priority ,
						array('key'=>$key)
					);


			}
		}

		/**
		*  Display Main Headline Input
		*/
		public static function display_main_headline() {
			global $post;
			$lp_variation = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : '0';
			$main_title = get_post_meta( $post->ID , 'lp-main-headline', true );
			$variation_notes = get_post_meta( $post->ID , 'lp-variation-notes', true );
			if ( empty ( $post ) || 'landing-page' !== get_post_type( $GLOBALS['post'] ) )
				return;

			if ( ! $main_title = get_post_meta( $post->ID , 'lp-main-headline',true ) )
				$main_title = '';

			if ( ! $variation_notes = get_post_meta( $post->ID , 'lp-variation-notes',true ) )
			$variation_notes = '';
			$main_title = apply_filters('lp_edit_main_headline', $main_title, 1);
			$variation_notes = apply_filters('lp_edit_variation_notes', $variation_notes, 1);
			$variation_id = apply_filters( 'lp_display_notes_input_id' , 'lp-variation-notes' );

			echo "<div id='lp-notes-area'>";
			echo "<span id='add-lp-notes'>". __('Notes' , 'landing-pages') .":</span><input placeholder='". __('Add Notes to your variation. Example: This version is testing a green submit button ' , 'landing-pages') ."' type='text' class='lp-notes' name='{$variation_id}' id='{$variation_id}' value='{$variation_notes}' size='30'>";
			echo '</div><div id="main-title-area"><input type="text" name="lp-main-headline" placeholder="'. __('Primary Headline Goes here. This will be visible on the page' , 'landing-pages') .'" id="lp-main-headline" value="'.$main_title.'" title="'. __('This headline will appear in the landing page template.' , 'landing-pages') .'"></div><div id="lp-current-view">'.$lp_variation.'</div><div id="switch-lp">0</div>';
			echo ""; ?>

			<?php
		   // Frontend params
			if(isset($_REQUEST['frontend']) && $_REQUEST['frontend'] == 'true') {
				echo('<input type="hidden" name="frontend" id="frontend-on" value="true" />');
			}

		}

		/**
		*  Display template options
		*/
		public static function display_template_options() {
			global $post;
			$template =  get_post_meta($post->ID, 'lp-selected-template', true);

			$template = apply_filters('lp_selected_template',$template);
			//echo $template;
			if (!isset($template)||isset($template)&&!$template){ $template = 'default';}

			$name = apply_filters('lp_selected_template_id','lp-selected-template');

			// Use nonce for verification
			echo "<input type='hidden' name='lp_lp_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";
			?>

			<div id="lp_template_change"><h2><a class="button" id="lp-change-template-button"><?php _e( 'Choose Another Template' , 'landing-pages'); ?></a></div>
			<input type='hidden' id='lp_select_template' name='<?php echo $name; ?>' value='<?php echo $template; ?>'>
				<div id="template-display-options"></div>

			<?php
		}

		/**
		*  Displays the selected template's thumbnail
		*/
		public static function display_template_thumbnail_metabox() {
			global $post;

			$template = self::get_selected_template();
			$vid = lp_ab_testing_get_current_variation_id();
			$permalink = add_query_arg( array( 'lp-variation-id' => $vid ) ,  get_permalink($post->ID) );
			$thumbnail = self::get_template_screenshot( $template );

			?>
			<div >
				<div class="inside" style='margin-left:-8px;'>
					<table>
						<tr>
							<td>
								<?php

									echo "<a href='$permalink' target='_blank' ><img src='$thumbnail' style='width:250px;height:250px;' title='". __( 'Preview this theme' , 'landing-pages') ." ,  ({$template})'></a>";
								?>
							</td>
						</tr>
					</table>

				</div>
			</div>
			<?php
		}

		/**
		*  Display Template Select
		*/
		public static function display_template_select() {
			global $post;

			$current_url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."";

			if (isset($post)&&$post->post_type!='landing-page'||!isset($post)){ return false; }

			( !strstr( $current_url, 'post-new.php')) ?  $toggle = "display:none" : $toggle = "";


			$extension_data = lp_get_extension_data();
			$extension_data_cats = Landing_Pages_Load_Extensions::get_template_categories();

			unset($extension_data['lp']);

			ksort($extension_data_cats);
			$uploads = wp_upload_dir();
			$uploads_path = $uploads['basedir'];
			$extended_path = $uploads_path.'/landing-pages/templates/';

			$template =  get_post_meta($post->ID, 'lp-selected-template', true);
			$template = apply_filters('lp_selected_template',$template);

			echo "<div class='lp-template-selector-container' style='{$toggle}'>";
			echo "<div class='lp-selection-heading'>";
			echo "<h1>". __( 'Select Your Landing Page Template!' , 'landing-pages') ."</h1>";
			echo '<a class="button-secondary" style="display:none;" id="lp-cancel-selection">'. __('Cancel Template Change' , 'landing-pages') .'</a>';
			echo "</div>";
				echo '<ul id="template-filter" >';
					echo '<li class="button-primary button"><a href="#" data-filter=".template-item-boxes">'. __( 'All' , 'landing-pages') .'</a></li>';
					echo '<li class="button-primary button"><a href="#" data-filter=".theme">'. __( 'Theme' , 'landing-pages' ) .'</a></li>';
					$categories = array('Theme');
					foreach ($extension_data_cats as $cat) {

						$slug = str_replace(' ','-',$cat['value']);
						$slug = strtolower($slug);
						$cat['value'] = ucwords($cat['value']);
						if (!in_array($cat['value'],$categories))
						{
							echo '<li class="button"><a href="#" data-filter=".'.$slug.'">'.$cat['value'].'</a></li>';
							$categories[] = $cat['value'];
						}

					}
				echo "</ul>";
				echo '<div id="templates-container" >';

				foreach ($extension_data as $exension_id => $data) {

					if (isset($data['info']['data_type']) && $data['info']['data_type']=='metabox' || substr($exension_id,0,4)=='ext-') {
						continue;
					}

					$cats = explode( ',' , $data['info']['category'] );

					foreach ($cats as $key => $cat)	{
						$cat = trim($cat);
						$cat = str_replace(' ', '-', $cat);
						$cats[$key] = trim(strtolower($cat));
					}

					$thumbnail = self::get_template_thumbnail( $exension_id );

					$demo_link = (isset($data['info']['demo'])) ? $data['info']['demo'] : '';
					?>
					<div id='template-item' class="<?php echo implode( ' ' , $cats); ?> template-item-boxes">
						<div id="template-box">
							<div class="lp_tooltip_templates" title="<?php echo $data['info']['description']; ?>"></div>
						<a class='lp_select_template' href='#' label='<?php echo $data['info']['label']; ?>' id='<?php echo $exension_id; ?>'>
							<img src="<?php echo $thumbnail; ?>" class='template-thumbnail' alt="<?php echo $data['info']['label']; ?>" id='lp_<?php echo $exension_id; ?>'>
						</a>
						<p>
							<div id="template-title"><?php echo $data['info']['label']; ?></div>
							<a href='#' label='<?php echo $data['info']['label']; ?>' id='<?php echo $exension_id; ?>' class='lp_select_template'><?php _e( 'Select' , 'landing-pages'); ?></a> |
							<a class='<?php echo $cat_slug;?>' target="_blank" href='<?php echo $demo_link;?>' id='lp_preview_this_template'><?php _e( 'Preview' , 'landing-pages'); ?></a>
						</p>
						</div>
					</div>
					<?php
				}
			echo '</div>';
			echo "<div class='clear'></div>";
			echo "</div>";
			echo "<div style='display:none;' class='currently_selected'>". __( 'This is Currently Selected' , 'landing-pages') ."</a></div>";
		}

		/**
		*  Display Conversion Area Metabox for default template
		*/
		public static function display_conversion_area(){

			$template = self::get_selected_template();
			$meta_box_id = 'metabox_conversion_area';
			$editor_id = 'landing-page-myeditor';

			if ( $template != 'default' ) {
				return;
			}

			//Add CSS & jQuery goodness to make this work like the original WYSIWYG
			echo "
					<style type='text/css'>
							#$meta_box_id #edButtonHTML, #$meta_box_id #edButtonPreview {background-color: #F1F1F1; border-color: #DFDFDF #DFDFDF #CCC; color: #999;}
							#$editor_id{width:100%;}
							#$meta_box_id #editorcontainer{background:#fff !important;}
							#$meta_box_id #editor_id_fullscreen{display:none;}
					</style>

					<script type='text/javascript'>
							jQuery(function($){
									$('#$meta_box_id #editor-toolbar > a').click(function(){
											$('#$meta_box_id #editor-toolbar > a').removeClass('active');
											$(this).addClass('active');
									});

									if($('#$meta_box_id #edButtonPreview').hasClass('active')){
											$('#$meta_box_id #ed_toolbar').hide();
									}

									$('#$meta_box_id #edButtonPreview').click(function(){
											$('#$meta_box_id #ed_toolbar').hide();
									});

									$('#$meta_box_id #edButtonHTML').click(function(){
											$('#$meta_box_id #ed_toolbar').show();
									});

					//Tell the uploader to insert content into the correct WYSIWYG editor
					$('#media-buttons a').bind('click', function(){
						var customEditor = $(this).parents('#$meta_box_id');
						if(customEditor.length > 0){
							edCanvas = document.getElementById('$editor_id');
						}
						else{
							edCanvas = document.getElementById('content');
						}
					});
							});
					</script>
			";

			//Create The Editor
			$conversion_area = lp_conversion_area(null,null,true,false,false);
			wp_editor($conversion_area, $editor_id);

			//Clear The Room!
			echo "<div style='clear:both; display:block;'></div>";
			echo "<div style='width:100%;text-align:right;margin-top:11px;'><div class='lp_tooltip'  title=\"". __('To help track conversions Landing Pages Plugin will automatically add a tracking class to forms. If you would like to track a link add this class to it' , 'landing-pages') ." class='wpl-track-me-link'\" ></div></div>";

		}

		/**
		*  Displays custom css
		*/
		public static function display_custom_css() {
			global $post;

			_e("<em>Custom CSS may be required to customize this landing page.</em><strong> <u>Format</u>: #element-id { display:none !important; }</strong>" , 'landing-pages');
			$custom_css_name = apply_filters('lp_custom_css_name','lp-custom-css');
			echo '<textarea name="'.$custom_css_name.'" id="lp-custom-css" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,$custom_css_name,true).'</textarea>';
		}

		/**
		*  Displays custom js
		*/
		public static function display_custom_js() {
			global $post;
			echo "<em></em>";
			$custom_js_name = apply_filters('lp_custom_js_name','lp-custom-js');
			echo '<input type="hidden" name="lp_custom_js_noncename" id="lp_custom_js_noncename" value="'.wp_create_nonce(basename(__FILE__)).'" />';
			echo '<textarea name="'.$custom_js_name.'" id="lp_custom_js" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,$custom_js_name,true).'</textarea>';
		}

		/**
		*  Changes title placeholder
		*/
		public static function change_post_title_placeholder_text( $text, $post ) {
			if ($post->post_type=='landing-page') {
				return __( 'Enter Landing Page Description' , 'landing-pages');
			} else {
				return $text;
			}
		}


		/**
		*  Get selected template
		*  @param INT $post_id optional
		*/
		public static function get_selected_template( $post_id = null ) {
			global $post;

			$post_id = ($post_id) ? $post_id : $post->ID;

			$template = get_post_meta( $post_id  , 'lp-selected-template', true);
			$template = apply_filters('lp_selected_template',$template);

			return $template;
		}

		/**
		*  Get template screenshot
		*  @param STRING $template template slug
		*/
		public static function get_template_screenshot( $template ) {

			/* if local server then show blank thumbnail screenshot else take mshot */
			if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
				if (file_exists(LANDINGPAGES_UPLOADS_PATH .  $template . '/thumbnail.png')) {
					$thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $template . '/thumbnail.png';
				} else {
					$thumbnail = LANDINGPAGES_URLPATH . 'templates/' . $template . '/thumbnail.png';
				}
			} else {
				$thumbnail = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=250';
			}

			return $thumbnail;
		}

		/**
		*  Renders extended metaboxes
		*/
		public static function render_metabox( $post , $key ) {
			$metabox_id = $key['args']['key'];

			$extension_data = lp_get_extension_data();

			if (!isset( $extension_data[ $metabox_id ]['settings'] )) {
				return;
			}
		
			// Begin the field table and loop
			echo '<div class="form-table" id="inbound-meta">';

			foreach ($extension_data[ $metabox_id ]['settings'] as $field) {

				$field_id = $metabox_id . "-" .$field['id'];
				$field_name = $field['id'];
				$label_class = $field['id'] . "-label";
				$type_class = " inbound-" . $field['type'];
				$type_class_row = " inbound-" . $field['type'] . "-row";
				$type_class_option = " inbound-" . $field['type'] . "-option";
				$option_class = (isset($field['class'])) ? $field['class'] : '';
				//$status = (isset($field['status'])) ? $field['status'] : '';
				$ink = get_option('lp-license-keys-'. $metabox_id);
				$status = get_option('lp_license_status-'. $metabox_id);
				$status_test = (isset($status) && $status != "") ? $status : 'inactive';
				// get value of this field if it exists for this post
				$meta = get_post_meta($post->ID, $field_id, true);
				$global_meta = get_post_meta($post->ID, $field_name, true);
				if(empty($global_meta)) {
					$global_meta = $field['default'];
				}

				if (!metadata_exists('post',$post->ID,$field_id))
				{
					$meta = $field['default'];
				}

				// Remove prefixes on global => true template options
				if (isset($field['global']) && $field['global'] === true) {
					$field_id = $field_name;
					$meta = get_post_meta($post->ID, $field_name, true);
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
							echo '<div id="'.$field_id.'" class="description-block">' . $field['description'].'</div>';
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
							echo '<input type="text" class="jpicker" style="background-color:#'.$meta.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="5" /><span class="button-primary new-save-lp" data-field-type="text" id="'.$field_id.$var_id.'" style="margin-left:10px; display:none;">Update</span>
									<div class="lp_tooltip tool_color" title="'.$field['description'].'"></div>';
							break;
						case 'datepicker':
							echo '<div class="jquery-date-picker inbound-datepicker" id="date-picking" data-field-type="text">
							<span class="datepair" data-language="javascript">
										Date: <input type="text" id="date-picker-'.$metabox_id.'" class="date start" /></span>
										Time: <input id="time-picker-'.$metabox_id.'" type="text" class="time time-picker" />
										<input type="hidden" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" class="new-date" value="" >
										<p class="description">'.$field['description'].'</p>
								</div>';
							break;
						case 'text':
							echo '<input type="text" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
									<div class="lp_tooltip" title="'.$field['description'].'"></div>';
							break;
						case 'number':

							echo '<input type="number" class="'.$option_class.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
									<div class="lp_tooltip" title="'.$field['description'].'"></div>';

							break;
						// textarea
						case 'textarea':
							echo '<textarea name="'.$field_id.'" id="'.$field_id.'" cols="106" rows="6" style="width: 75%;">'.$meta.'</textarea>
									<div class="lp_tooltip tool_textarea" title="'.$field['description'].'"></div>';
							break;
						// wysiwyg
						case 'wysiwyg':
							echo "<div class='iframe-options iframe-options-".$field_id."' id='".$field['id']."'>";
							wp_editor( $meta, $field_id, $settings = array( 'editor_class' => $field_name ) );
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
							echo "<table class='lp_check_box_table'>";
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
							echo '<div class="lp_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
						break;
						// radio
						case 'radio':
							foreach ($field['options'] as $value=>$label) {
								//echo $meta.":".$field_id;
								//echo "<br>";
								echo '<input type="radio" name="'.$field_id.'" id="'.$field_id.'" value="'.$value.'" ',$meta==$value ? ' checked="checked"' : '','/>';
								echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
							}
							echo '<div class="lp_tooltip" title="'.$field['description'].'"></div>';
						break;
						// select
						case 'dropdown':
							echo '<select name="'.$field_id.'" id="'.$field_id.'" class="'.$field['id'].'">';
							foreach ($field['options'] as $value=>$label) {
								echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
							}
							echo '</select><div class="lp_tooltip" title="'.$field['description'].'"></div>';
						break;



					} //end switch
				echo '</div></div>';
			} // end foreach
			echo '</div>'; // end table
			//exit;

		}


		/**
		*  Get template thumbnail
		*  @param STRING $template template slug
		*/
		public static function get_template_thumbnail( $template ) {

			$thumb = false;

			if (file_exists(LANDINGPAGES_PATH.'templates/'.$template."/thumbnail.png"))
			{
				if ($template=='default') {
					$thumbnail =  get_bloginfo('template_directory')."/screenshot.png";
				} else {
					$thumbnail = LANDINGPAGES_URLPATH.'templates/'.$template."/thumbnail.png";
				}
				$thumb = true;
			} else if (file_exists(LANDINGPAGES_UPLOADS_PATH.$template."/thumbnail.png")) {
				$thumbnail = LANDINGPAGES_UPLOADS_URLPATH.$template."/thumbnail.png";
				$thumb = true;
			} else if ($thumb === false) {
				$thumbnail = LANDINGPAGES_URLPATH.'templates/default/thumbnail.png';
			}

			return $thumbnail;
		}


		/**
		*  Save custom CSS and JS
		*/
		public static function save_data( $post_id ) {
			global $post;

			if (!isset($post) || ( isset($post) && $post->post_type!='landing-page' ) ) {
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			/* save lp conversion area */
			if(isset($_REQUEST['landing-page-myeditor'])) {
				$data = wpautop($_REQUEST['landing-page-myeditor']);
				update_post_meta($_REQUEST['post_ID'], 'lp-conversion-area', $data);
			}

			/* save custom js */
			$custom_js_name = apply_filters( 'lp_custom_js_name', 'lp-custom-js' );
			$lp_custom_js = $_POST[$custom_js_name];
			update_post_meta($post_id, 'lp-custom-js', $lp_custom_js);

			/* save custom css */
			$custom_css_name = apply_filters( 'lp_custom_css_name' , 'lp-custom-css' );
			$lp_custom_css = $_POST[$custom_css_name];
			update_post_meta($post_id, 'lp-custom-css', $lp_custom_css);

			/* save headline */
			if ( isset ( $_POST[ 'lp-main-headline' ] ) ) {
				update_post_meta( $post_id, 'lp-main-headline' , $_POST[ 'lp-main-headline' ] );
			}

			/* save notes */
			if ( isset ( $_POST[ 'lp-variation-notes' ] ) ) {
				update_post_meta( $post_id, $key, $_POST[ 'lp-variation-notes' ] );
			}
			
			/* get extended data */
			$extension_data = lp_get_extension_data();
			
		
			/* Loop through extension datasets and save updated data */
			foreach ($extension_data as $key => $data) {

				foreach ($extension_data[$key]['settings'] as $field) {
					$id = $key."-".$field['id'];
					
					if(!isset($_POST[$id])) {
						continue;
					}
					
					update_post_meta( $post_id, $id, $_POST[$id] );
				
				}
			}
		}
	}



	new Landing_Pages_Metaboxes;
}

