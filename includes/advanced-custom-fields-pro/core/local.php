<?php 

class acf_local {
	
	// vars
	var $enabled	= true,
		$groups 	= array(),
		$fields 	= array(),
		$parents 	= array();
		
		
	function __construct() {
		
		add_action('acf/get_field_groups', array($this, 'get_field_groups'), 10, 1);
		
	}
	
	
	/*
	*  get_field_groups
	*
	*  This function will override and add field groups to the `acf_get_field_groups()` results
	*
	*  @type	filter (acf/get_field_groups)
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$field_groups (array)
	*  @return	$field_groups
	*/
	
	function get_field_groups( $field_groups ) {
		
		// validate
		if( !acf_have_local_field_groups() ) {
			
			return $field_groups;
			
		}
		
		
		// vars
		$ignore = array();
		
		
		// overrride field groups and populate ignore list
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $k => $group ) {
				
				// override
				if( acf_is_local_field_group( $group['key'] ) ) {
					
					$field_groups[ $k ] = acf_get_local_field_group( $group['key'] );
					
				}
				
				$ignore[] = $group['key'];
			}
			
		}
		
		
		// append field groups
		$groups = acf_get_local_field_groups();
		
		foreach( $groups as $group ) {
			
			if( !in_array($group['key'], $ignore) ) {
				
				$field_groups[] = $group;
				
			}
			
		}
		
		
		// order field groups based on menu_order, title
		$menu_order = array();
		$title = array();
		
		foreach( $field_groups as $key => $row ) {
			
		    $menu_order[ $key ] = $row['menu_order'];
		    $title[ $key ] = $row['title'];
		}
		
		
		// sort the array with menu_order ascending
		array_multisort( $menu_order, SORT_ASC, $title, SORT_ASC, $field_groups );
		
		
		// return
		return $field_groups;
		
	}
	
	
	/*
	*  add_field_group
	*
	*  This function will add a $field group to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function add_field_group( $field_group ) {
		
		// validate
		$field_group = acf_get_valid_field_group($field_group);
		
		
		// don't allow overrides
		if( acf_is_local_field_group($field_group['key']) ) {
			
			return;	
			
		}
		
		
		// remove fields
		$fields = acf_extract_var($field_group, 'fields');
		
		
		// format fields
		$fields = acf_prepare_fields_for_import( $fields );
		
		
		// add field group
		$this->groups[ $field_group['key'] ] = $field_group;
		
		
		// add fields
		foreach( $fields as $field ) {
			
			// add parent
			if( empty($field['parent']) ) {
				
				$field['parent'] = $field_group['key'];
				
			}
			
			
			// add field group reference
			//$field['field_group'] = $field_group['key'];
			
			
			// add field
			$this->add_field( $field );
			
		}
		
	}
	
	
	/*
	*  add_field
	*
	*  This function will add a $field to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	n/a
	*/
	
	function add_field( $field ) {
		
		// validate
		$field = acf_get_valid_field($field);
		
		
		// don't allow overrides
		// edit: some manually created fields (via .php) used duplicate keys (copy of origional field).
		/*
if( acf_is_local_field($field['key']) ) {
			
			return;	
			
		}
*/

		
		
		// vars
		$parent = $field['parent'];
		
		
		// append $parents
		$this->parents[ $parent ][] = $field['key'];
		
		
		// add in menu order
		$field['menu_order'] = count( $this->parents[ $parent ] ) - 1;
		
		
		// find ancestors
		//$field['ancestors'] = array();
		
		
		while( acf_is_local_field($parent) ) {
		
			//$field['ancestors'][] = $parent;
			
			$parent = acf_get_local_field( $parent );
			$parent = $parent['parent'];
			
		}
		
		//$field['ancestors'][] = $field['field_group'];

		
		// add field
		$this->fields[ $field['key'] ] = $field;
		
	}
	
}


/*
*  acf_local
*
*  This function will return the one true acf_local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	acf_local (object)
*/

function acf_local() {
	
	// globals
	global $acf_local;
	
	
	// instantiate
	if( !isset($acf_local) )
	{
		$acf_local = new acf_local();
	}
	
	
	// return
	return $acf_local;
}


/*
*  acf_disable_local
*
*  This function will disable the local functionality for DB only interaction
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_disable_local() {
	
	acf_local()->enabled = false;
	
}


/*
*  acf_enable_local
*
*  This function will enable the local functionality
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enable_local() {
	
	acf_local()->enabled = true;
	
}


/*
*  acf_is_local_enabled
*
*  This function will return true|false if the local functionality is enabled
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_is_local_enabled() {
	
	// validate
	if( !acf_get_setting('local', false) ) {
		
		return false;
		
	}
	
	
	if( !acf_local()->enabled ) {
		
		return false;
		
	}
	
	
	// return
	return true;
	
}


/*
*  acf_have_local_field_groups
*
*  This function will return true if fields exist for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(bolean)
*/

function acf_have_local_field_groups() {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check for groups
	if( !empty(acf_local()->groups) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_groups
*
*  This function will return an array of fields for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field_groups() {
	
	// vars
	$groups = array();
	
	
	// acf_local
	foreach( acf_local()->groups as $group ) {
		
		$groups[] = $group;
		
	}
	
	
	// return
	return $groups;
	
}


/*
*  acf_add_local_field_group
*
*  This function will add a $field group to the local placeholder
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_local_field_group( $field_group ) {
	
	acf_local()->add_field_group( $field_group );
	
}


/*
*  acf_is_local_field_group
*
*  This function will return true if the field group has been added as local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_is_local_field_group( $key ) {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check groups
	if( isset( acf_local()->groups[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_group
*
*  This function will return a local field group for a given key
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field_group( $key ) {
	
	return acf_local()->groups[ $key ];
	
}


/*
*  acf_add_local_field
*
*  This function will add a $field to the local placeholder
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_local_field( $field ) {
	
	acf_local()->add_field( $field );
	
}


/*
*  acf_is_local_field
*
*  This function will return true if the field has been added as local
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_is_local_field( $key ) {
	
	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check fields
	if( isset( acf_local()->fields[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_field_group
*
*  This function will return a local field for a given key
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_field( $key ) {
	
	return acf_local()->fields[ $key ];
	
}


/*
*  acf_have_local_fields
*
*  This function will return true if fields exist for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_have_local_fields( $key ) {

	// validate
	if( !acf_is_local_enabled() ) {
		
		return false;
		
	}
	
	
	// check parents
	if( isset( acf_local()->parents[ $key ] ) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_local_fields
*
*  This function will return an array of fields for a given 'parent' key (field group key or field key)
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	(bolean)
*/

function acf_get_local_fields( $key ) {

	$fields = array();
	
	foreach( acf_local()->parents[ $key ] as $key ) {
		
		$fields[] = acf_get_field( $key );
		
	}
	
	return $fields;
	
}

?>
