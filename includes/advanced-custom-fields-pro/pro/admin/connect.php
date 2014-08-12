<?php 

class acf_pro_connect {
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// override requests for plugin information
        add_filter('plugins_api', array($this, 'inject_info'), 20, 3);
        
        
		// insert our update info into the update array maintained by WP
		add_filter('site_transient_update_plugins', array($this, 'inject_update'));
		
		
		// add custom message when PRO not activated but update available
		add_action('in_plugin_update_message-' . acf_get_setting('basename'), array($this, 'in_plugin_update_message'), 10, 2 );
	}
	
	
	/*
	*  inject_info
	*
	*  description
	*
	*  @type	function
	*  @date	17/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function inject_info( $res, $action = null, $args = null ) {
		
		// vars
		$slug = acf_get_setting('slug');
        
        
		// validate
    	if( isset($args->slug) && $args->slug == $slug )
    	{
	    	$info = acf_pro_get_remote_info();
	    	$sections = acf_extract_vars($info, array(
	    		'description',
	    		'installation',
	    		'changelog',
	    		'upgrade_notice',
	    	));
	    	
	    	$obj = new stdClass();
		
		    foreach( $info as $k => $v )
		    {
		        $obj->$k = $v;
		    }
		    
		    $obj->sections = $sections;

		    return $obj;
		    
    	}
    	
    	
    	// return        
        return $res;
        
	}
	
	
	/*
	*  inject_update
	*
	*  description
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function inject_update( $transient ) {
		
		// bail early if not admin
		if( !is_admin() ) {
			
			return $transient;
			
		}
		
		
		// bail early if no update available
		if( !acf_pro_is_update_available() ) {
			
			return $transient;
			
		}
		
		 
        // vars
        $info = acf_pro_get_remote_info();
        $basename = acf_get_setting('basename');
        $slug = acf_get_setting('slug');

		
        // create new object for update
        $obj = new stdClass();
        $obj->slug = $slug;
        $obj->new_version = $info['version'];
        $obj->url = $info['homepage'];
        $obj->package = '';
        
        
        // license
		if( acf_pro_is_license_active() ) {
			
			$obj->package = acf_pro_get_remote_url( 'download', array( 'k' => acf_pro_get_license() ) );
		
		}
		
        
        // add to transient
        $transient->response[ $basename ] = $obj;
        
		
		// return 
        return $transient;
	}
	
	
	/*
	*  in_plugin_update_message
	*
	*  Displays an update message for plugin list screens.
	*  Shows only the version updates from the current until the newest version
	*
	*  @type	function
	*  @date	5/06/13
	*
	*  @param	{array}		$plugin_data
	*  @param	{object}	$r
	*/

	function in_plugin_update_message( $plugin_data, $r ) {
		
		// validate
		if( acf_pro_is_license_active() )
		{
			return;
		}
		
		$m = __('To enable updates, please enter your license key on the <a href="%s">Updates</a> page. If you don\'t have a licence key, please see <a href="%s">details & pricing</a>', 'acf');
		
		echo '<br />' . sprintf( $m, admin_url('edit.php?post_type=acf-field-group&page=acf-settings-updates'), 'http://www.advancedcustomfields.com/pro');
	
	}
	
	
}


// initialize
new acf_pro_connect();

?>
