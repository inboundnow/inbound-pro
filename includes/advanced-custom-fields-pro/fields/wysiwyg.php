<?php

/*
*  ACF WYSIWYG Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_wysiwyg
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_wysiwyg') ) :

class acf_field_wysiwyg extends acf_field {
	
	var $exists = 0;
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->name = 'wysiwyg';
		$this->label = __("Wysiwyg Editor",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'toolbar'		=>	'full',
			'media_upload' 	=>	1,
			'default_value'	=>	'',
		);
		
		
		// filters
    	add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins'), 20, 1 );
    	
    	
    	// Create an acf version of the_content filter (acf_the_content)
		if(	!empty($GLOBALS['wp_embed']) ) {
		
			add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
			add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
			
		}
		
		add_filter( 'acf_the_content', 'capital_P_dangit', 11 );
		add_filter( 'acf_the_content', 'wptexturize' );
		add_filter( 'acf_the_content', 'convert_smilies' );
		add_filter( 'acf_the_content', 'convert_chars' );
		add_filter( 'acf_the_content', 'wpautop' );
		add_filter( 'acf_the_content', 'shortcode_unautop' );
		add_filter( 'acf_the_content', 'prepend_attachment' );
		add_filter( 'acf_the_content', 'do_shortcode', 11);
		

		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  mce_external_plugins
	*
	*  This filter will add in the tinyMCE 'code' plugin which is missing in WP 3.9
	*
	*  @type	function
	*  @date	18/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function mce_external_plugins( $plugins ){
		
		// global
   		global $wp_version;
   		
   		
   		// WP 3.9 an above
   		if( version_compare($wp_version, '3.9', '>=' ) ) {
			
			// add code
			$plugins['code'] = acf_get_dir('inc/tinymce/plugins/code/plugin.min.js');
		
		}
		
		
		// return
		return $plugins;
		
	}
	
	
	/*
	*  get_toolbars
	*
	*  This function will return an array of toolbars for the WYSIWYG field
	*
	*  @type	function
	*  @date	18/04/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
   	function get_toolbars() {
   		
   		// global
   		global $wp_version;
   		
   		
   		// vars
   		$toolbars = array();
   		$editor_id = 'acf_content';
   		
   		
   		if( version_compare($wp_version, '3.9', '>=' ) ) {
   		
   			// Full
	   		$toolbars['Full'] = array(
	   			
	   			1 => apply_filters( 'mce_buttons', array('bold', 'italic', 'strikethrough', 'bullist', 'numlist', 'blockquote', 'hr', 'alignleft', 'aligncenter', 'alignright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ), $editor_id ),
	   			
	   			2 => apply_filters( 'mce_buttons_2', array( 'formatselect', 'underline', 'alignjustify', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help', 'code' ), $editor_id ),
	   			
	   			3 => apply_filters('mce_buttons_3', array(), $editor_id),
	   			
	   			4 => apply_filters('mce_buttons_4', array(), $editor_id),
	   			
	   		);
	   		
	   		
	   		// Basic
	   		$toolbars['Basic'] = array(
	   			
	   			1 => apply_filters( 'teeny_mce_buttons', array('bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'alignleft', 'aligncenter', 'alignright', 'undo', 'redo', 'link', 'unlink', 'fullscreen'), $editor_id ),
	   			
	   		);
	   		  		
   		} else {
	   		
	   		// Full
	   		$toolbars['Full'] = array(
	   			
	   			1 => apply_filters( 'mce_buttons', array('bold', 'italic', 'strikethrough', 'bullist', 'numlist', 'blockquote', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ), $editor_id ),
	   			
	   			2 => apply_filters( 'mce_buttons_2', array( 'formatselect', 'underline', 'justifyfull', 'forecolor', 'pastetext', 'pasteword', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help', 'code' ), $editor_id ),
	   			
	   			3 => apply_filters('mce_buttons_3', array(), $editor_id),
	   			
	   			4 => apply_filters('mce_buttons_4', array(), $editor_id),
	   			
	   		);

	   		
	   		// Basic
	   		$toolbars['Basic'] = array(
	   			
	   			1 => apply_filters( 'teeny_mce_buttons', array('bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'justifyleft', 'justifycenter', 'justifyright', 'undo', 'redo', 'link', 'unlink', 'fullscreen'), $editor_id ),
	   			
	   		);
	   		
   		}
   		
   		
   		// Filter for 3rd party
   		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', $toolbars );
   		
   		
   		// return
	   	return $toolbars;
   	}
   	
   	
   	/*
   	*  input_form_data
   	*
   	*  This function is called once on the input page between the head and footer
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$post_id (int)
   	*  @return	$post_id (int)
   	*/
   	
   	function input_form_data( $args ) {
	   	
	   	// vars
		$json = array();
		$toolbars = $this->get_toolbars();

		
		// loop through toolbars
		if( !empty($toolbars) ) {
			
			foreach( $toolbars as $label => $rows ) {
				
				// vars
				$label = sanitize_title( $label );
				$label = str_replace('-', '_', $label);
				
				
				// append to $json
				$json[ $label ] = array();
				
				
				// convert to strings
				if( !empty($rows) ) {
					
					foreach( $rows as $i => $row ) { 
						
						$json[ $label ][ 'theme_advanced_buttons' . $i ] = implode(',', $row);
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
		?>
		<script type="text/javascript">
		(function($) {
		
			acf.fields.wysiwyg.toolbars = <?php echo json_encode( $json ); ?>;
		
		})(jQuery);	
		</script>
		
		<?php
	
   	}
   	
   	
   	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// enqueue
		acf_enqueue_uploader();
		
		
		// vars
		$id = 'wysiwyg-' . $field['id'] . '-' . uniqid();
		
		
		// filter value for editor
		remove_all_filters( 'acf_the_editor_content' );
		
		if( user_can_richedit() ) {
			
			add_filter('acf_the_editor_content', 'wp_richedit_pre');
			
		} else {
			
			add_filter('acf_the_editor_content', 'wp_htmledit_pre');
			
		}
		
		
		$field['value'] = apply_filters( 'acf_the_editor_content', $field['value'] );
		
		
		?>
		<div id="wp-<?php echo $id; ?>-wrap" class="acf-wysiwyg-wrap wp-core-ui wp-editor-wrap tmce-active" data-toolbar="<?php echo $field['toolbar']; ?>" data-upload="<?php echo $field['media_upload']; ?>">
			<?php if( user_can_richedit() && $field['media_upload'] ): ?>
				<div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools hide-if-no-js">
					<div id="wp-<?php echo $id; ?>-media-buttons" class="wp-media-buttons">
						<?php do_action( 'media_buttons' ); ?>
					</div>
				</div>
			<?php endif; ?>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>"><?php echo $field['value']; ?></textarea>
			</div>
		</div>
		<?php
				
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// vars
		$toolbars = $this->get_toolbars();
		$choices = array();
		
		if( !empty($toolbars) ) {
		
			foreach( $toolbars as $k => $v ) {
				
				$label = $k;
				$name = sanitize_title( $label );
				$name = str_replace('-', '_', $name);
				
				$choices[ $name ] = $label;
			}
		}
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// toolbar
		acf_render_field_setting( $field, array(
			'label'			=> __('Toolbar','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'toolbar',
			'layout'		=> 'horizontal',
			'choices'		=> $choices
		));
		
		
		// media_upload
		acf_render_field_setting( $field, array(
			'label'			=> __('Show Media Upload Buttons?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'media_upload',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				1				=>	__("Yes",'acf'),
				0				=>	__("No",'acf'),
			)
		));

	}
		
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
			
			return $value;
		
		}
		
		
		// apply filters
		$value = apply_filters( 'acf_the_content', $value );
		
		
		// follow the_content function in /wp-includes/post-template.php
		$value = str_replace(']]>', ']]&gt;', $value);
		
	
		return $value;
	}
	
}

new acf_field_wysiwyg();

endif;

?>
