<?php

/**
 * Creates CTA Dynamic Widget
 *
 * @package	Calls To Action
 * @subpackage	Widgets
*/

if (!class_exists('CTA_Dynamic_Widget')) {

	class CTA_Static_Widget extends WP_Widget
	{
		private $cta_templates;

		function CTA_Static_Widget() {

			/* Widget settings. */
			$widget_ops = array( 'classname' => 'class_CTA_Static_Widget', 'description' => __('Use this widget to manually display Calls to Action in sidebars.', 'cta') );

			/* Widget control settings. */
			$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'id_wp_cta_static_widget' );

			/* Create the widget. */
			$this->WP_Widget( 'id_wp_cta_static_widget', __('Call to Action Static Widget', 'cta'), $widget_ops, $control_ops );
		}

		/**
		 * How to display the widget on the screen.
		 */
		function widget( $args, $instance )
		{
			global $wp_query; global $post;


			if (!isset($instance['cta_ids'])) {
				return;
			}

			/* get enviroment object id if available */
			$obj_id = $wp_query->get_queried_object_id();

			$CTA_Load_Extensions = CTA_Load_Extensions();
			$this->cta_templates = $CTA_Load_Extensions->template_definitions;

			$CTA_Render = CTA_Render();

			$selected_ctas = $instance['cta_ids'];

			if (!is_array($selected_ctas)) {
				return;
			}

			$cta_ids =  implode(",", $selected_ctas);
			$count = count($selected_ctas);
			$rand_key = array_rand($selected_ctas, 1);
			$cta_id = $selected_ctas[$rand_key];
			$this->cta_id = $cta_id;

			$selected_cta =  $CTA_Render->prepare_cta_dataset( array($cta_id) );

			if ( !isset($selected_cta['templates']) ) {
				return;
			}

			/* Import Correct CSS & JS from Assets folder and Enqueue */
			$loaded = array();
			foreach ($selected_cta['templates'] as $template)
			{
				if ( in_array( $template['slug'] , $loaded) ){
					continue;
				}

				$loaded[] = $template['slug'];
				$assets = $CTA_Render->get_template_asset_files($template);
				$localized_template_id = str_replace( '-' , '_' , $template['slug'] );
				foreach ($assets as $type => $file)
				{
					switch ($type)
					{
						case 'js':
							foreach ($file as $js)
							{
								wp_enqueue_script( md5($js) ,$js , array( 'jquery' ));
								wp_localize_script( md5($js) , $localized_template_id , array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ,  'post_id' => $obj_id ) );
							}
							break;
						case 'css':
							foreach ($file as $css)
							{
								wp_enqueue_style( md5($css) , $css );
							}
							break;
					}
				}
			}

			/* Load CTA CSS Templates & Load Custom CSS & Custom JS */
			foreach ($selected_cta['meta'] as $vid=>$cta) {
				($vid<1) ? $suffix = '' : $suffix = '-'.$vid;

				$template_slug = $selected_cta['meta'][$vid]['wp-cta-selected-template-'.$vid];
				$custom_css = get_post_meta( $cta_id , 'wp-cta-custom-css'.$suffix , true);
				//echo $template_slug;
				//print_r($this->cta_templates);exit;
				$dynamic_css = $this->cta_templates[$template_slug]['css-template'];

				$dynamic_css = $CTA_Render->replace_template_variables( $selected_cta , $dynamic_css , $vid );
				$css_id_preface = "#wp_cta_" . $cta_id . "_variation_" . $vid;

				$dynamic_css = $CTA_Render->parse_css_template($dynamic_css , $css_id_preface);

				$css_styleblock_class = apply_filters( 'wp_cta_styleblock_class' , '' , $cta_id , $vid );

				if (!stristr($custom_css,'<style')){
					$custom_css = strip_tags($custom_css);
				}

				/* Print Cusom CSS */
				echo '<style type="text/css" id="wp_cta_css_custom_'.$cta_id.'_'.$vid.'" class="wp_cta_css_'.$cta_id.' '.$css_styleblock_class.'">'.$custom_css.' '.$dynamic_css.'</style>';

				$custom_js = get_post_meta( $cta_id , 'wp-cta-custom-js'.$suffix, true);
				if (!stristr($custom_css,'<script') && $custom_css)	{
					echo '<script type="text/javascript">jQuery(document).ready(function($) {
					'.$custom_js.' });</script>';
				} else if ($custom_js) {
					echo $custom_js;
				}
			}


			/* get supporting widget settings */
			$selected_cta['margin-top'] = $instance['cta_margin_top'];
			$selected_cta['margin-bottom'] = $instance['cta_margin_bottom'];

			$cta_template = $CTA_Render->build_cta_content( $selected_cta );

			$cta_template = do_shortcode($cta_template);

			echo $cta_template;

			$this->load_variation();
		}

		/**
		 * Update the widget settings.
		 */
		 /** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['cta_margin_top'] = $new_instance['cta_margin_top'] ? $new_instance['cta_margin_top'] : "";
			$instance['cta_margin_bottom'] = $new_instance['cta_margin_bottom'] ? $new_instance['cta_margin_bottom'] : "";
			$instance['cta_ids'] = $new_instance['cta_ids'] ? $new_instance['cta_ids'] : "";

			return $instance;
		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function
		 * when creating your form elements. This handles the confusing stuff.
		 */
		function form($instance) {

			$args = array('post_type' => 'wp-call-to-action', 'numberposts' => -1);
			$cta_post_type = get_posts($args);

			if ( !isset($instance['cta_ids']) || !is_array($instance['cta_ids']) ) {
				$instance['cta_ids'] = array();
			}

			$width = "";
			if ( isset( $instance[ 'cta_default_width' ] ) ) {
				$width = $instance[ 'cta_default_width' ];
			}

			$height = "";
			if ( isset( $instance[ 'cta_default_height' ] ) ) {
				$height = $instance[ 'cta_default_height' ];
			}

			$margin_top = "";
			if ( isset( $instance[ 'cta_margin_top' ] ) ) {
				$margin_top = $instance[ 'cta_margin_top' ];
			}

			$margin_bottom = "";
			if ( isset( $instance[ 'cta_margin_bottom' ] ) ) {
				$margin_bottom = $instance[ 'cta_margin_bottom' ];
			}


			?>

				<div class='cta-widget-p'><strong><?php _e( 'Select Calls to Action(s):' , 'cta' ); ?></strong><br />
					<small><?php _e('If multiple calls to action are checked, they will randomly rotate. Only 1 CTA is displayed per widget' , 'cta' ); ?></small>
				<div class='cta-widget-select-options'>
				<?php
				foreach ($cta_post_type as $cta) {

				   $this_id = $cta->ID;

					$this_link = get_permalink( $this_id );
					$this_link = preg_replace('/\?.*/', '', $this_link); ?>

					<input class="checkbox" type="checkbox" <?php checked(in_array( $cta->ID , $instance['cta_ids']  ), true ); ?> value="<?php _e($cta->ID); ?>" name="<?php echo $this->get_field_name('cta_ids'); ?>[]" />
					<label for=""><?php _e($cta->post_title); ?>
						<a class='thickbox cta-links-hidden cta-widget-preview-links' id="cta-<?php echo $this_id;?>" href='<?php echo $this_link;?>?wp-cta-variation-id=0&wp_cta_iframe_window=on&post_id=<?php echo $cta->ID; ?>&TB_iframe=true&width=640&height=703'>Preview</a>
					</label>
					<br />
					<?php
				}
			?>
			</div>
			</div>

			<hr>
			<h4 class='cta-advanced-section'><?php _e( 'Advanced Options' , 'cta' ); ?></h4>
			<div class="advanced-cta-widget-options">
				<div class='cta-widget'><label for="<?php echo $this->get_field_id('cta_margin_top'); ?>">Margin Top</label>
				<input class="cta-text" type="text" value="<?php echo $margin_top; ?>" id="<?php echo $this->get_field_id('cta_margin_top'); ?>" name="<?php echo $this->get_field_name('cta_margin_top'); ?>" />px</div>
				<div class='cta-widget'><label for="<?php echo $this->get_field_id('cta_margin_bottom'); ?>">Margin Bottom</label>
				<input class="cta-text" type="text" value="<?php echo $margin_bottom; ?>" id="<?php echo $this->get_field_id('cta_margin_bottom'); ?>" name="<?php echo $this->get_field_name('cta_margin_bottom'); ?>" />px</div>
			</div>

			<?php
		}

		/**
		*  Run load variation javascript
		*/
		function load_variation()
		{
			$disable_ajax = get_option('wp-cta-main-disable-ajax-variation-discovery');

			?>
			<script>
			jQuery(document).ready(function($) {
				wp_cta_load_variation('<?php echo $this->cta_id; ?>' , null , '<?php echo $disable_ajax; ?>' );
			});
			</script>
			<?php
		}
	}

	/**
	*  Loads the static cta widget class
	*/
	function cta_load_static_widget() {
		register_widget( 'CTA_Static_Widget' );
	}
	add_action( 'widgets_init', 'cta_load_static_widget' );



}