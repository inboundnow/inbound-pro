<?php
/**
 * ACF 4 Field Class
 *
 * This file holds the class required for our field to work with ACF 4
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */

/**
 * ACF 4 Google Font Selector Class
 *
 * The Google Font selector class enables users to select webfonts from
 * The Google Fonts service. This is the class that is used for ACF 4.
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
class acf_field_google_font_selector extends acf_field {

	var $settings;
	var $defaults;


	/**
	 * Field Constructor
	 *
	 * Sets basic properties and runs the parent constructor
	 *
	 * @author Daniel Pataki
	 * @since 3.0.0
	 *
	 */
	function __construct() {

		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => plugin_dir_url( __FILE__ ),
			'version' => '1.0.0'
		);

		$this->name = 'google_font_selector';
		$this->label = __( 'Google Font Selector', 'acf-google-font-selector-field');
		$this->category = __( 'Choice' , 'acf' );

		$this->defaults = array(
			'include_web_safe_fonts' => true,
			'enqueue_font'           => true,
			'default_font'           => 'Droid Sans',
		);

		parent::__construct();

		add_action( 'wp_ajax_acfgfs_get_font_details', 'acfgfs_action_get_font_details' );

		if( !defined( 'ACFGFS_NOENQUEUE' ) ) {
			add_action( 'wp_enqueue_scripts', 'acfgfs_google_font_enqueue' );
		}
	}

	/**
	 * Field Options
	 *
	 * Creates the options for the field, they are shown when the user
	 * creates a field in the back-end. Currently there are three fields.
	 *
	 * The Web Safe Fonts setting allows you to add regular fonts available
	 * in any browser to the list
	 *
	 * The Enqueue Font setting will load the fonts on the appropriate page
	 * when checked.
	 *
	 * The default font settings allows you to specify the font set as the
	 * default for the field.
	 *
	 * @param array $field The details of this field
	 * @author Daniel Pataki
	 * @since 3.0.0
	 *
	 */
	function create_options( $field ) {

		$field = array_merge($this->defaults, $field);
		$key = $field['name'];

	?>
	<!-- Web Safe Fonts Field -->
	<tr class="field_option field_option_<?php echo $this->name; ?>">
		<td class="label">
			<label><?php _e("Web Safe Fonts?",'acf-google-font-selector-field'); ?></label>
		</td>
		<td>
			<?php

			do_action('acf/create_field', array(
				'type'		=>	'true_false',
				'name'		=>	'fields['.$key.'][include_web_safe_fonts]',
				'value'		=>	$field['include_web_safe_fonts'],
				'layout'	=>	'horizontal',
				'message'    	=> __('Include web safe fonts?','acf-google-font-selector-field'),
			));

			?>
		</td>
	</tr>

	<!-- Enqueue Fonts Field -->
	<tr class="field_option field_option_<?php echo $this->name; ?>">
		<td class="label">
			<label><?php _e("Enqueue Font?",'acf-google-font-selector-field'); ?></label>
		</td>
		<td>
			<?php

			do_action('acf/create_field', array(
				'type'		=>	'true_false',
				'name'		=>	'fields['.$key.'][enqueue_font]',
				'value'		=>	$field['enqueue_font'],
				'layout'	=>	'horizontal',
				'message'    	=> __('Automatically load font?','acf-google-font-selector-field'),
			));

			?>
		</td>
	</tr>

	<!-- Default Font Field -->
	<tr class="field_option field_option_<?php echo $this->name; ?>">
		<td class="label">
			<label><?php _e("Default Font",'acf-google-font-selector-field'); ?></label>
		</td>
		<td>
			<?php

			do_action('acf/create_field', array(
				'type'		=>	'select',
				'name'		=>	'fields['.$key.'][default_font]',
				'value'		=>	$field['default_font'],
				'choices'   => acfgfs_get_font_dropdown_array()
			));

			?>
		</td>
	</tr>

		<?php

	}


	/**
	 * Field Display
	 *
	 * This function takes care of displaying our field to the users, taking
	 * the field options into account.
	 *
	 * @param array $field The details of this field
	 * @author Daniel Pataki
	 * @since 3.0.0
	 *
	 */
	function create_field( $field ){

		$current_font_family = ( empty( $field['value'] ) ) ? $field['default_font'] : $field['value']['font'];
		?>
		<div class="acfgfs-font-selector">
			<div class="acfgfs-loader"></div>
			<div class="acfgfs-form-control acfgfs-font-family">
				<div class="acfgfs-form-control-title"><?php _e('Font Family', 'acf-google-font-selector-field') ?></div>

				<select name="<?php echo esc_attr($field['name']) ?>">
					<?php
					$options = acfgfs_get_font_dropdown_array( $field );
					foreach( $options as $option ) {
						echo '<option ' . selected( $option, $current_font_family ) . ' value="' . $option . '">' . $option . '</option>';
					}
					?>
				</select>

				<?php $font = str_replace( ' ', '+', $current_font_family ); ?>
				<div class='acfgfs-preview'>
					<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $font ?>">

					<div style='font-family:<?php echo $current_font_family ?>'>
						<?php _e( 'This is a preview of the selected font', 'acf-google-font-selector-field' ) ?>
					</div>
				</div>

			</div>

			<div class="acfgfs-form-control acfgfs-font-variants">
				<div class="acfgfs-form-control-title"><?php _e('Variants', 'acf-google-font-selector-field') ?></div>
				<div class="acfgfs-list">
					<?php acfgfs_display_variant_list( $field ) ?>
				</div>

			</div>

			<div class="acfgfs-form-control acfgfs-font-subsets">
				<div class="acfgfs-form-control-title"><?php _e('Subsets', 'acf-google-font-selector-field') ?></div>
				<div class="acfgfs-list">
					<?php acfgfs_display_subset_list( $field ) ?>
				</div>

			</div>

			<textarea name="acfgfs-font-data" class="acfgfs-font-data"><?php echo json_encode( $field ) ?></textarea>

		</div>


	<?php
	}


	/**
	 * Enqueue Assets
	 *
	 * This function enqueues the scripts and styles needed to display the
	 * field
	 *
	 * @author Daniel Pataki
	 * @since 3.0.0
	 *
	 */
	function input_admin_enqueue_scripts() {

		wp_enqueue_script( 'acf-input-google_font_selector', $this->settings['dir'] . 'js/input.js', array('acf-input'), $this->settings['version'] );

		wp_enqueue_style( 'acf-input-google_font_selector', $this->settings['dir'] . 'css/input.css', array('acf-input'), $this->settings['version'] );

	}

	/**
	 * Pre-Save Value Modification
	 *
	 * This filter is applied to the $value before it is updated in the db
	 *
	 * @param mixed $value The value which will be saved in the database
	 * @param int $post_id The $post_id of which the value will be saved
	 * @param array $field The field array holding all the field options
	 * @return mixed The new value
	 * @author Daniel Pataki
	 * @since 3.0.0
	 *
	 */
	function update_value( $value, $post_id, $field ) {
		$new_value = array();
		$new_value['font'] = $value;
		if( empty( $_POST[$field['key'] . '_variants'] ) ) {
			$_POST[$field['key'] . '_variants'] = acfgfs_get_font_variant_array( $new_value['font'] );
		}

		if( empty( $_POST[$field['key'] . '_subsets'] ) ) {
			$_POST[$field['key'] . '_subsets'] = acfgfs_get_font_subset_array( $new_value['font'] );
		}

		$new_value['variants'] = $_POST[$field['key'] . '_variants'];
		$new_value['subsets'] = $_POST[$field['key'] . '_subsets'];
		return $new_value;
	}


}


// create field
new acf_field_google_font_selector();

?>
