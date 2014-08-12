<?php 

if( !class_exists('acf_pro') ):

class acf_pro {
	
	/*
	*  __construct
	*
	*  
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// update setting
		acf_update_setting( 'pro', true );
		acf_update_setting( 'name', __('Advanced Custom Fields PRO', 'acf') );
		

		// api
		acf_include('pro/api/api-pro.php');
		acf_include('pro/api/api-options-page.php');
		
		
		// admin
		if( is_admin() ) {
			
			// options page
			acf_include('pro/admin/options-page.php');
			
			// connect (update)
			acf_include('pro/admin/connect.php');
				
			// settings
			acf_include('pro/admin/settings-updates.php');
			
		}
		
		
		// fields
		acf_include('pro/fields/repeater.php');
		acf_include('pro/fields/flexible-content.php');
		acf_include('pro/fields/gallery.php');
		
		
		// actions
		add_action('init',										array($this, 'wp_init'));
		add_action('acf/input/admin_enqueue_scripts',			array($this, 'input_admin_enqueue_scripts'));
		add_action('acf/field_group/admin_enqueue_scripts',		array($this, 'field_group_admin_enqueue_scripts'));
		add_action('acf/field_group/admin_l10n',				array($this, 'field_group_admin_l10n'));
		add_action('acf/render_field_settings', 				array($this, 'render_field_settings'));
		
		
		// filters
		add_filter('acf/update_field',							array($this, 'update_field'), 1, 1);
		add_filter('acf/prepare_field_for_export', 				array($this, 'prepare_field_for_export'));
		add_filter('acf/prepare_field_for_import', 				array($this, 'prepare_field_for_import'));
		
		
		// add-ons
		add_filter('acf/is_add_on_active/slug=acf-pro',			'__return_true');
	}
	
	
	/*
	*  wp_init
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_init() {
		
		// min
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		
		// register scripts
		wp_register_script( 'acf-pro-input', acf_get_dir( "pro/js/pro-input{$min}.js" ), false, acf_get_setting('version') );
		wp_register_script( 'acf-pro-field-group', acf_get_dir( "pro/js/pro-field-group{$min}.js" ), false, acf_get_setting('version') );
		
		
		// register styles
		wp_register_style( 'acf-pro-input', acf_get_dir( 'pro/css/pro-input.css' ), false, acf_get_setting('version') ); 
		wp_register_style( 'acf-pro-field-group', acf_get_dir( 'pro/css/pro-field-group.css' ), false, acf_get_setting('version') ); 
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script(array(
			'acf-pro-input',	
		));
	
	
		// styles
		wp_enqueue_style(array(
			'acf-pro-input',	
		));
		
	}
	
	
	/*
	*  field_group_admin_l10n
	*
	*  description
	*
	*  @type	function
	*  @date	1/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_l10n( $l10n ) {
		
		// append
		$l10n['flexible_content'] = array(
			'layout_warning' => __('Flexible Content requires at least 1 layout','acf')
		);
		
		
		// return
		return $l10n;
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script(array(
			'acf-pro-field-group',	
		));
	
	
		// styles
		wp_enqueue_style(array(
			'acf-pro-field-group',	
		));
		
	}
	
	
	/*
	*  update_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function update_field( $field ) {
		
		// don't use acf_get_field. Instead, keep a global record of ID from each update_field and use this to get the parent ID => key 
		if( $field['parent'] ) {
		
			if( acf_is_field_key($field['parent']) ) {
			
				$parent = acf_get_field( $field['parent'] );
				
				$field['parent'] = $parent['ID'];
				
			}
			
		}
		
		return $field;
	}
	
	
	/*
	*  prepare_field_for_export
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_export( $field ) {
		
		// sub field (parent_layout)
		acf_extract_var( $field, 'parent_layout');
		
		
		// sub fields
		if( $field['type'] == 'repeater' ) {
			
			$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
			
		}
		elseif( $field['type'] == 'flexible_content' ) {
			
			foreach( $field['layouts'] as $l => $layout ) {
				
				$field['layouts'][ $l ]['sub_fields'] = acf_prepare_fields_for_export( $layout['sub_fields'] );
			
			}

		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_import
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_import( $field ) {
		
		// var
		$extra = array();
		
		
		// sub fields
		if( $field['type'] == 'repeater' ) {
			
			// extract sub fields
			$sub_fields = acf_extract_var( $field, 'sub_fields');
			
			
			// reset field setting
			$field['sub_fields'] = array();
			
			
			if( !empty($sub_fields) ) {
			
				foreach( array_keys($sub_fields) as $i ) {
					
					// extract sub field
					$sub_field = acf_extract_var( $sub_fields, $i );
							
					
					// attributes
					$sub_field['parent'] = $field['key'];
					
					
					// append to extra
					$extra[] = $sub_field;
					
				}
				
			}
			
		} elseif( $field['type'] == 'flexible_content' ) {
			
			// extract layouts
			$layouts = acf_extract_var( $field, 'layouts');
			
			
			// reset field setting
			$field['layouts'] = array();
			
			
			// validate layouts
			if( !empty($layouts) ) {
				
				// loop over layouts
				foreach( array_keys($layouts) as $i ) {
					
					// extract layout
					$layout = acf_extract_var( $layouts, $i );
					
					
					// get valid layout (fixes ACF4 export code bug undefined index 'key')
					if( empty($layout['key']) ) {
						
						$layout['key'] = uniqid();
						
					}
					
					
					// extract sub fields
					$sub_fields = acf_extract_var( $layout, 'sub_fields');
					
					
					// validate sub fields
					if( !empty($sub_fields) ) {
						
						// loop over sub fields
						foreach( array_keys($sub_fields) as $j ) {
							
							// extract sub field
							$sub_field = acf_extract_var( $sub_fields, $j );
							
							
							// attributes
							$sub_field['parent'] = $field['key'];
							$sub_field['parent_layout'] = $layout['key'];
							
							
							// append to extra
							$extra[] = $sub_field;
							
						}
						
					}
					
					
					// append to layout
					$field['layouts'][] = $layout;
				
				}
				
			}

		}
		
		
		// extra
		if( !empty($extra) ) {
			
			array_unshift($extra, $field);
			
			return $extra;
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  render_field_settings
	*
	*  description
	*
	*  @type	function
	*  @date	15/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_settings( $field ) {
		
		// bail early if doing ajax
		/*
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			
			return;
			
		}
		
		
*/
		// vars
		$setting = array(
			'label'		=> __('Column Width','acf'),
			'type'		=> 'number',
			'name'		=> 'column_width',
			'append'	=> '%',
			'disabled'	=> 1
		);
		
		
		// add setting
		acf_render_field_setting( $field, $setting );
		
	}
	 
}


// instantiate
new acf_pro();


// end class
endif;

?>
