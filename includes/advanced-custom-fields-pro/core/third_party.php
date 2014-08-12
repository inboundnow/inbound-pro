<?php

/*
*  ACF 3rd Party Compatibility Class
*
*  All the logic for 3rd party functionality
*
*  @class 		acf_third_party
*  @package		ACF
*  @subpackage	Core
*/

if( ! class_exists('acf_third_party') ) :

class acf_third_party {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// Tabify Edit Screen - http://wordpress.org/extend/plugins/tabify-edit-screen/
		add_action('admin_head-settings_page_tabify-edit-screen', array($this, 'admin_head_tabify'));
		
		
		// Post Type Switcher - http://wordpress.org/extend/plugins/post-type-switcher/
		add_filter('pts_post_type_filter', array($this, 'pts_post_type_filter'));
		
	}
	
	
	/*
	*  admin_head_tabify
	*
	*  description
	*
	*  @type	action (admin_head)
	*  @date	9/10/12
	*  @since	3.5.1
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_head_tabify() {
		
		// remove ACF from the tabs
		add_filter('tabify_posttypes',			array($this, 'tabify_posttypes'));
		
		
		// add acf metaboxes to list
		add_action('tabify_add_meta_boxes',		array($this, 'tabify_add_meta_boxes'));
		
	}
	
	
	/*
	*  tabify_posttypes
	*
	*  This function removes ACF post types from the tabify edit screen (post type selection sidebar)
	*
	*  @type	function
	*  @date	9/10/12
	*  @since	3.5.1
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function tabify_posttypes( $posttypes ) {
		
		// unset
		unset( $posttypes['acf-field-group'] );
		unset( $posttypes['acf-field'] );
		
		
		// return
		return $posttypes;
	}
	
	
	/*
	*  tabify_add_meta_boxes
	*
	*  This function creates dummy metaboxes on the tabify edit screen page
	*
	*  @type	function
	*  @date	9/10/12
	*  @since	3.5.1
	*
	*  @param	$post_type (string)
	*  @return	n/a
	*/
	
	function tabify_add_meta_boxes( $post_type ) {
		
		// get field groups
		$field_groups = acf_get_field_groups();
		
		
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $field_group ) {
				
				// vars
				$id = "acf-{$field_group['key']}";
				$title = 'ACF: ' . $field_group['title'];

				
				
				// add meta box
				add_meta_box( $id, $title, '__return_true', $post_type );
				
			}
			
		}
		
	}
	
	
	/*
	*  pts_post_type_filter
	*
	*  This function modifies the args for the ACF post types
	*
	*  @type	function
	*  @date	19/11/12
	*  @since	3.5.3
	*
	*  @param	$args (array)
	*  @return	$args
	*/
	
	function pts_post_type_filter( $args ) {
		
		// global
		global $typenow;
		
		
		// check post type
		if( $typenow == 'acf-field' || $typenow == 'acf-field-group' ) {
		
			$args = array(
				'public'  => false,
				'show_ui' => true
			);
			
		}
		
		
		// return
		return $args;
	}
	
	
}

new acf_third_party();

endif;

?>
