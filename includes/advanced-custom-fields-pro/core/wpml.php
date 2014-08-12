<?php 

class acf_wpml_compatibility {
	
	var $lang = '';
	
	
	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions and filters
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// vars
		$this->lang = ICL_LANGUAGE_CODE;
		
		
		// actions
		add_action('acf/update_field_group',		array($this, 'update_field_group'), 1, 1);
		add_action('icl_make_duplicate',			array($this, 'icl_make_duplicate'), 10, 4);
		add_action('acf/field_group/admin_head',	array($this, 'admin_head'));
		add_action('acf/input/admin_head',			array($this, 'admin_head'));
		
		
		// filters
		add_filter('acf/settings/save_json',		array($this, 'settings_save_json'));
		add_filter('acf/settings/load_json',		array($this, 'settings_load_json'));
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
		
		global $sitepress;
		
		$this->lang = $sitepress->get_language_for_element($field_group['ID'], 'post_acf-field-group');
		
	}

	
	/*
	*  settings_save_json
	*
	*  description
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function settings_save_json( $path ) {
		
		// bail early if dir does not exist
		if( !is_writable($path) ) {
			
			return $path;
			
		}
		
		
		// remove trailing slash
		$path = untrailingslashit( $path );

			
		// ammend
		$path = $path . '/' . $this->lang;
		
		
		// make dir if does not exist
		if( !file_exists($path) ) {
			
			mkdir($path, 0777, true);
			
		}
		
		
		// return
		return $path;
		
	}
	
	
	/*
	*  settings_load_json
	*
	*  description
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function settings_load_json( $paths ) {
		
		if( !empty($paths) ) {
			
			foreach( $paths as $i => $path ) {
				
				// remove trailing slash
				$path = untrailingslashit( $path );
				
				
				// ammend
				$paths[ $i ] = $path . '/' . $this->lang;
			
			}
		}
		
		
		// return
		return $paths;
		
	}
	
	
	
	/*
	*  icl_make_duplicate
	*
	*  description
	*
	*  @type	function
	*  @date	26/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function icl_make_duplicate( $master_post_id, $lang, $postarr, $id ) {
		
		// validate
		if( $postarr['post_type'] != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// duplicate field group
		acf_duplicate_field_group( $master_post_id, $id );
		
		
		// always translate independately to avoid many many bugs!
		// - translation post gets a new key (post_name) when origional post is saved
		// - local json creates new files due to changed key
		global $iclTranslationManagement;
		
		$iclTranslationManagement->reset_duplicate_flag( $id );

	}
	
	
	/*
	*  admin_head
	*
	*  description
	*
	*  @type	function
	*  @date	27/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_head() {
		
		?>
		<script type="text/javascript">
				
		acf.add_filter('prepare_for_ajax', function( args ){
			
			if( typeof icl_this_lang != 'undefined' )
			{
				args.lang = icl_this_lang;
			}
			
			return args;
			
		});
		
		</script>
		<?php
		
	}
	
}

new acf_wpml_compatibility();

?>
