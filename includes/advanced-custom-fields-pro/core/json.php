<?php 

class acf_json {
	
	function __construct() {
		
		// update setting
		acf_update_setting('save_json', get_stylesheet_directory() . '/acf-json');
		acf_append_setting('load_json', get_stylesheet_directory() . '/acf-json');
		
		
		// actions
		add_action('acf/update_field_group',		array($this, 'update_field_group'), 10, 1);
		add_action('acf/duplicate_field_group',		array($this, 'update_field_group'), 10, 1);
		add_action('acf/untrash_field_group',		array($this, 'update_field_group'), 10, 1);
		add_action('acf/trash_field_group',			array($this, 'delete_field_group'), 10, 1);
		add_action('acf/delete_field_group',		array($this, 'delete_field_group'), 10, 1);
		add_action('acf/include_fields', 			array($this, 'include_fields'), 10, 1);
		
	}
	
	
	/*
	*  update_field_group
	*
	*  This function is hooked into the acf/update_field_group action and will save all field group data to a .json file 
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function update_field_group( $field_group ) {
		
		// vars
		$path = acf_get_setting('save_json');
		$file = $field_group['key'] . '.json';
		
		
		// remove trailing slash
		$path = untrailingslashit( $path );
		
		
		// bail early if dir does not exist
		if( !is_writable($path) ) {
		
			//error_log( 'ACF failed to save field group to .json file. Path does not exist: ' . $path );
			return;
			
		}
		
		
		// load fields
		$fields = acf_get_fields( $field_group );

		
		// prepare fields
		$fields = acf_prepare_fields_for_export( $fields );
		
		
		// add to field group
		$field_group['fields'] = $fields;
		
		
		// extract field group ID
		$id = acf_extract_var( $field_group, 'ID' );
		
		
		// write file
		$f = fopen("{$path}/{$file}", 'w');
		fwrite($f, acf_json_encode( $field_group ));
		fclose($f);
			
	}
	
	
	/*
	*  delete_field_group
	*
	*  This function will remove the field group .json file
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function delete_field_group( $field_group ) {
		
		// vars
		$path = acf_get_setting('save_json');
		$file = $field_group['key'] . '.json';
		
		
		// remove trailing slash
		$path = untrailingslashit( $path );
		
		
		// bail early if file does not exist
		if( !is_readable("{$path}/{$file}") ) {
		
			//error_log( 'ACF failed to save field group to .json file. Path does not exist: ' . $path );
			return;
			
		}
		
			
		// remove file
		unlink("{$path}/{$file}");
			
	}
		
	
	/*
	*  include_fields
	*
	*  This function will include any JSON files found in the active theme
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$version (int)
	*  @return	n/a
	*/
	
	function include_fields() {
		
		// validate
		if( !acf_get_setting('json') ) {
		
			return;
			
		}
		
		
		// vars
		$paths = acf_get_setting('load_json');
		
		
		// loop through and add to cache
		foreach( $paths as $path ) {
			
			// remove trailing slash
			$path = untrailingslashit( $path );
		
		
			// check that path exists
			if( !file_exists( $path ) ) {
			
				continue;
				
			}
			
			
			$dir = opendir( $path );
	    
		    while(false !== ( $file = readdir($dir)) ) {
		    
		    	// only json files
		    	if( strpos($file, '.json') === false ) {
		    	
			    	continue;
			    	
		    	}
		    	
		    	
		    	// read json
		    	$json = file_get_contents("{$path}/{$file}");
		    	
		    	
		    	// validate json
		    	if( empty($json) )
		    	{
			    	continue;
		    	}
		    	
		    	
		    	$json = json_decode($json, true);
		    	
		    	
		    	acf_add_local_field_group( $json );
		        
		    }
		}
		
	}
	
	
}

new acf_json();

?>
