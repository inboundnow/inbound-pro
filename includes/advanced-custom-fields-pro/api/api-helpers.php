<?php 

/*
*  acf_e
*
*  This function wraps the `_e` in extra logic
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_e() {
	
	// vars
	$args = func_get_args();
	
	
	// acf__
	echo call_user_func_array('acf__', $args);
	
}


function acf__() {
	
	// vars
	$args = func_get_args();
	$domain = 'acf';
	
	
	// __()
	foreach( $args as $k => $v )
	{
		$args[ $k ] = __( $v, $domain );
	}
	
	
	// string
	$string = $args[0];
	
	
	// sprintf
	if( count($args) > 1 )
	{
		$string = call_user_func_array('sprintf', $args);
	}
	
	
	// replace backticks
	$string = preg_replace("/`(.*?)`/s", '<pre>$1</pre>', $string);
	
	
	// return
	return $string;
	
}


/*
*  acf_get_setting
*
*  This function will return a value from the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string) the setting name to return
*  @return	(mixed)
*/

function acf_get_setting( $name, $allow_filter = true ) {
	
	// vars
	$r = null;
	
	
	// load from ACF if available
	if( isset( acf()->settings[ $name ] ) )
	{
		$r = acf()->settings[ $name ];
	}
	
	
	// filter for 3rd party customization
	if( $allow_filter )
	{
		$r = apply_filters( "acf/settings/{$name}", $r );
	}
	
	
	// return
	return $r;
}


/*
*  acf_update_setting
*
*  This function will update a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_update_setting( $name, $value )
{
	acf()->settings[ $name ] = $value;
}


/*
*  acf_append_setting
*
*  This function will add a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_append_setting( $name, $value ) {
	
	// createa array if needed
	if( ! isset(acf()->settings[ $name ]) )
	{
		acf()->settings[ $name ] = array();
	}
	
	// append to array
	acf()->settings[ $name ][] = $value;
}


/*
*  acf_get_path
*
*  This function will return the path to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_path( $path )
{
	return acf_get_setting('path') . $path;
}


/*
*  acf_get_dir
*
*  This function will return the url to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_dir( $path )
{
	return acf_get_setting('dir') . $path;
}


/*
*  acf_include
*
*  This function will include a file
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_include( $file ) {
	
	$path = acf_get_path( $file );
	
	if( file_exists($path) ) {
		
		include_once( $path );
		
	}
	
}


/*
*  acf_parse_args
*
*  This function will merge together 2 arrays and also convert any numeric values to ints
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @param	$defaults (array)
*  @return	$args (array)
*/

function acf_parse_args( $args, $defaults = array() ) {
	
	// $args may not be na array!
	if( !is_array($args) )
	{
		$args = array();
	}
	
	
	// parse args
	$args = wp_parse_args( $args, $defaults );
	
	
	// parse types
	$args = acf_parse_types( $args );
	
	
	// return
	return $args;
	
}


/*
*  acf_require_arg
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

function acf_require_arg( $array, $arg, $value = false ) {
	
	// isset
	if( !isset($array[ $arg ]) ) {
		
		$array[ $arg ] = $value;
		
	}
	
	
	// return
	return $array;
	
} 


/*
*  acf_parse_types
*
*  This function will convert any numeric values to int and trim strings
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	$var (mixed)
*/

function acf_parse_types( $var )
{
	// is value another array?
	if( is_array($var) )
	{
		// some keys are restricted
		$restricted = array(
			'label',
			'name',
			'value',
			'instructions',
			'nonce'
		);
		
		
		// loop through $var carful not to parse any restricted keys
		foreach( array_keys($var) as $k )
		{
			// bail early for restricted pieces
			if( in_array($k, $restricted, true) )
			{
				continue;
			}
			
			$var[ $k ] = acf_parse_types( $var[ $k ] );
		}	
	}
	else
	{
		// string
		if( is_string($var) )
		{
			$var = trim( $var );
		}
		
		
		// numbers
		if( is_numeric($var) )
		{
			// check for non numeric characters
			if( preg_match('/[^0-9]/', $var) )
			{
				// leave value if it contains such characters: . + - e
				//$value = floatval( $value );
			}
			else
			{
				$var = intval( $var );
			}
		}
	}
	
	
	// return
	return $var;
}


/*
*  acf_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_get_view( $view_name = '', $args = array() ) {

	// vars
	$path = acf_get_path("admin/views/{$view_name}.php");
	
	if( file_exists($path) )
	{
		include( $path );
	}
}


/*
*  acf_render_field_wrap
*
*  This function will render the complete HTML wrap with label & field
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array) must be a valid ACF field array
*  @param	$el (string) modifys the rendered wrapping elements. Default to 'div', but can be 'tr', 'ul', 'ol', 'dt' or custom
*  @param	$instruction (string) specifys the placement of the instructions. Default to 'label', but can be 'field'
*  @param	$atts (array) an array of custom attributes to render on the $el
*  @return	N/A
*/

function acf_render_field_wrap( $field, $el = 'div', $instruction = 'label', $atts = array() ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// prepare field for input
	$field = acf_prepare_field( $field );
	
	
	// el
	$elements = apply_filters('acf/render_field_wrap/elements', array(
		'div'	=> 'div',
		'tr'	=> 'td',
		'ul'	=> 'li',
		'ol'	=> 'li',
		'dl'	=> 'dt',
		'td'	=> 'div' // special case for sub field!
	));
	
	
	// validate $el
	if( !array_key_exists($el, $elements) )
	{
		$el = 'div';
	}
	
	
	// atts
	$atts = acf_parse_args($atts, array(
		'class'		=> '',
		'data-name'	=> $field['name'],
		'data-type'	=> $field['type'],
	));
	
	
	// add to atts
	$atts['class'] .= " acf-field field_type-{$field['type']}";
	
	
	// add key
	if( $field['key'] )
	{
		$atts['class'] .= " field_key-{$field['key']}";
		$atts['data-key'] = $field['key'];
	}
	
	
	// add required
	if( $field['required'] )
	{
		$atts['data-required'] = 1;
	}
	
	
	// vars
	$show_label = true;
	
	if( $el == 'td' )
	{
		$show_label = false;
	}
	
	
	?><<?php echo $el; ?> <?php echo acf_esc_attr($atts); ?>>
		<?php if( $show_label ): ?>
		<<?php echo $elements[ $el ]; ?> class="acf-label">
			
			<label for="<?php echo $field['id']; ?>"><?php echo acf_get_field_label($field); ?></label>
			
			<?php if( $instruction == 'label' && $field['instructions'] ): ?>
				<p class="description"><?php echo $field['instructions']; ?></p>
			<?php endif; ?>
			
		</<?php echo $elements[ $el ]; ?>>
		<?php endif; ?>
		<<?php echo $elements[ $el ]; ?> class="acf-input">
		
			<?php acf_render_field( $field ); ?>
			
			<?php if( $instruction == 'field' && $field['instructions'] ): ?>
				<p class="description"><?php echo $field['instructions']; ?></p>
			<?php endif; ?>
			
			<?php if( !empty($field['conditional_logic'])): ?>
			<script type="text/javascript">
			(function($) {
				
			if( typeof acf !== 'undefined' )
			{
				acf.conditional_logic.add( '<?php echo $field['key']; ?>', <?php echo json_encode($field['conditional_logic']); ?>);
			}
				
			})(jQuery);	
			</script>
			<?php endif; ?>
			
		</<?php echo $elements[ $el ]; ?>>
	</<?php echo $el; ?>><?php
}


/*
*  acf_render_fields
*
*  This function will render an array of fields for a given form.
*  Becasue the $field's values have not been loaded yet, this function will also load values
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int) the post to load values from
*  @param	$fields (array) the fields to render
*  @param	$el (string) the wrapping element type
*  @param	$instruction (int) the instructions position
*  @return	n/a
*/

function acf_render_fields( $post_id = 0, $fields, $el = 'div', $instruction = 'label' ) {
		
	if( !empty($fields) ) {
		
		foreach( $fields as $field ) {
			
			// load value
			if( $field['value'] === null ) {
				
				$field['value'] = acf_get_value( $post_id, $field );
				
			} 
			
			
			// set prefix for correct post name (prefix + key)
			$field['prefix'] = 'acf';
			
			
			// render
			acf_render_field_wrap( $field, $el, $instruction );
		}
		
	}
		
}


/*
*  acf_get_field_label
*
*  This function will return the field label with appropriate required label
*
*  @type	function
*  @date	4/11/2013
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$label (string)
*/

function acf_get_field_label( $field ) {
	
	// vars
	$label = $field['label'];
	
	
	if( $field['required'] )
	{
		$label .= ' <span class="acf-required">*</span>'; 
	}
	
	
	// return
	return $label;

}

function acf_the_field_label( $field ) {

	echo acf_get_field_label( $field );
	
}


/*
*  acf_render_field_option
*
*  This function will render a tr element containing a label and field cell, but also setting the tr_class for use with AJAX 
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$type (string) the origional field_type (not $field['type'])
*  @param	$field (array)
*  @return	N/A
*/

function acf_render_field_option( $type, $field )
{
	// vars
	$atts = array( 
		'data-setting' => $type
	);
	
	
	// render
	acf_render_field_wrap( $field, 'tr', 'label', $atts );
}


/*
*  acf_render_field_setting
*
*  This function will render a tr element containing a label and field cell, but also setting the tr data attribute for AJAX 
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array) the origional field being edited
*  @param	$setting (array) the settings field to create
*  @return	n/a
*/

function acf_render_field_setting( $field, $setting, $global = false ) {
	
	// vars
	$atts = array();
	
	
	// if this setting is not global, add a data attribute
	if( ! $global ) {
		
		$atts['data-setting'] = $field['type'];
		
	}
	
	
	// copy across prefix
	$setting['prefix'] = $field['prefix'];
		
		
	// copy across the $setting value
	// Note: tab field contains no name for it's $setting (its just a message)
	if( isset($setting['name'], $field[ $setting['name'] ]) ) {
		
		$setting['value'] = $field[ $setting['name'] ];
		
	}
	
	
	// render
	acf_render_field_wrap( $setting, 'tr', 'label', $atts );
	
}


/*
*  acf_get_field_types
*
*  This function will return all available field types
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_types() {

	return apply_filters('acf/get_field_types', array());
	
}


/*
*  acf_get_field_type_label
*
*  This function will return the label of a field type
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_type_label( $field_type ) {

	// vars
	$field_types = acf_get_field_types();
	
	
	// loop through categories
	foreach( $field_types as $category ) {
		
		if( isset( $category[ $field_type ] ) ) {
		
			return $category[ $field_type ];
			
		}
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_field_type_exists
*
*  This function will check if the field_type is available
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field_type (string)
*  @return	(boolean)
*/

function acf_field_type_exists( $field_type ) {

	// vars
	$label = acf_get_field_type_label( $field_type );
	
	
	// return true if label exists
	if( !empty( $label ) ) {
		
		return true;
		
	}
		
	
	// return
	return false;
}


/*
*  acf_esc_attr
*
*  This function will return a render of an array of attributes to be used in markup
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_attr( $atts )
{
	// is string?
	if( is_string($atts) )
	{
		$atts = trim( $atts );
		return esc_attr( $atts );
	}
	
	
	// validate
	if( empty($atts) )
	{
		return '';
	}
	
	
	// vars
	$e = array();
	
	
	// loop through and render
	foreach( $atts as $k => $v )
	{
		if( is_array($v) || is_object($v) || is_bool($v) )
		{
			$v = '';
		}
		
		if( is_string($v) )
		{
			$v = trim( $v );
		}
		
		$e[] = $k . '="' . esc_attr( $v ) . '"';
	}
	
	
	// echo
	return implode(' ', $e);
}

function acf_esc_attr_e( $atts ) {
	echo acf_esc_attr( $atts );
}


/*
*  acf_hidden_input
*
*  description
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_hidden_input( $atts ) {
	
	$atts['type'] = 'hidden';
	
	return '<input ' . acf_esc_attr( $atts ) . ' />';
	
}

function acf_hidden_input( $atts ) {
	
	echo acf_get_hidden_input( $atts );
	
}


/*
*  acf_extract_var
*
*  This function will remove the var from the array, and return the var
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$array (array)
*  @param	$key (string)
*  @return	(mixed)
*/

function acf_extract_var( &$array, $key ) {
	
	// vars
	$r = null;
	
	
	// check if exists
	if( array_key_exists($key, $array) ) {
	
		$r = $array[ $key ];
		unset( $array[ $key ] );
	
	}
	
	
	// return
	return $r;
}


/*
*  acf_extract_vars
*
*  This function will remove the vars from the array, and return the vars
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_extract_vars( &$array, $keys )
{
	$r = array();
	
	foreach( $keys as $key )
	{
		$r[ $key ] = acf_extract_var( $array, $key );
	}
	
	return $r;
}


/*
*  acf_get_post_types
*
*  This function will return an array of available post types
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$exclude (array)
*  @param	$include (array)
*  @return	(array)
*/

function acf_get_post_types( $exclude = array(), $include = array() ) {
	
	// get all custom post types
	$post_types = get_post_types();
	
	
	// core exclude
	$exclude = wp_parse_args( $exclude, array( 'acf-field', 'acf-field-group', 'revision', 'nav_menu_item' ) );
	
	
	// include
	if( !empty($include) ) {
		
		foreach( array_keys($include) as $i ) {
			
			$post_type = $include[ $i ];
			
			if( post_type_exists($post_type) ) {	
									
				$post_types[ $post_type ] = $post_type;
				
			}
			
		}
		
	}
	
	
	// exclude
	foreach( array_values($exclude) as $i ) {
		
		unset( $post_types[ $i ] );
		
	}
	
	
	// return
	return $post_types;
	
}


function acf_get_pretty_post_types( $post_types = array() ) {
	
	// get post types
	if( empty($post_types) ) {
		
		// get all custom post types
		$post_types = acf_get_post_types();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( array_keys($post_types) as $i ) {
		
		// vars
		$post_type = acf_extract_var( $post_types, $i);
		$obj = get_post_type_object($post_type);
		$name = $obj->labels->singular_name;
		
		
		// append to r
		$r[ $post_type ] = $name;
		
		
		// increase counter
		if( !isset($ref[ $name ]) ) {
			
			$ref[ $name ] = 0;
			
		}
		
		$ref[ $name ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$post_type = $r[ $i ];
		
		if( $ref[ $post_type ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_verify_nonce
*
*  This function will look at the $_POST['_acfnonce'] value and return true or false
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$nonce (string)
*  @return	(boolean)
*/

function acf_verify_nonce( $nonce, $post_id = 0 ) {
	
	// vars
	$r = false;
	
	
	// note: don't reset _acfnonce here, only when $r is set to true. This solves an issue caused by other save_post actions using this function with a different $nonce
	
	
	// check
	if( isset($_POST['_acfnonce']) )
	{

		// verify nonce 'post|user|comment|term'
		if( wp_verify_nonce($_POST['_acfnonce'], $nonce) )
		{
			$r = true;
			
			
			// remove potential for inifinite loops
			$_POST['_acfnonce'] = false;
			
		
			// if we are currently saving a revision, allow its parent to bypass this validation
			if( $post_id )
			{
				if( $parent = wp_is_post_revision($post_id) )
				{
					// revision: set parent post_id
					$_POST['_acfnonce'] = $parent;
				}
			}
		}
		
		
		if( $_POST['_acfnonce'] === $post_id )
		{
			$r = true;
			
			// remove potential for inifinite loops
			$_POST['_acfnonce'] = false;
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_add_admin_notice
*
*  This function will add the notice data to a setting in the acf object for the admin_notices action to use
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$class (string)
*  @return	(int) message ID (array position)
*/

function acf_add_admin_notice( $text, $class = '', $wrap = 'p' )
{
	// vars
	$admin_notices = acf_get_admin_notices();
	
	
	// add to array
	$admin_notices[] = array(
		'text'	=> $text,
		'class'	=> "updated {$class}",
		'wrap'	=> $wrap
	);
	
	
	// update
	acf_update_setting( 'admin_notices', $admin_notices );
	
	
	// return
	return ( count( $admin_notices ) - 1 );
	
}


/*
*  acf_get_admin_notices
*
*  This function will return an array containing any admin notices
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_admin_notices()
{
	// vars
	$admin_notices = acf_get_setting( 'admin_notices' );
	
	
	// validate
	if( !$admin_notices )
	{
		$admin_notices = array();
	}
	
	
	// return
	return $admin_notices;
}


/*
*  acf_get_image_sizes
*
*  This function will return an array of available image sizes
*
*  @type	function
*  @date	23/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_image_sizes() {
	
	// vars
	$sizes = array(
		'thumbnail'	=>	__("Thumbnail",'acf'),
		'medium'	=>	__("Medium",'acf'),
		'large'		=>	__("Large",'acf'),
		'full'		=>	__("Full Size",'acf')
	);


	// find all sizes
	$all_sizes = get_intermediate_image_sizes();
	
	
	// add extra registered sizes
	if( !empty($all_sizes) ) {
		
		foreach( $all_sizes as $size ) {
			
			// bail early if already in array
			if( isset($sizes[ $size ]) ) {
			
				continue;
				
			}
			
			
			// append to array
			$label = str_replace('-', ' ', $size);
			$label = ucwords( $label );
			$sizes[ $size ] = $label;
			
		}
		
	}
	
	
	
	// filter for 3rd party customization
	$sizes = apply_filters( 'image_size_names_choose', $sizes );
	
	
	// return
	return $sizes;
	
}


/*
*  acf_get_taxonomies
*
*  This function will return an array of available taxonomies
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_taxonomies() {

	// get all taxonomies
	$taxonomies = get_taxonomies( false, 'objects' );
	$ignore = array( 'nav_menu', 'link_category' );
	$r = array();
	
	
	// populate $r
	foreach( $taxonomies as $taxonomy )
	{
		if( in_array($taxonomy->name, $ignore) )
		{
			continue;
		
		}
		
		$r[ $taxonomy->name ] = $taxonomy->name; //"{$taxonomy->labels->singular_name}"; // ({$taxonomy->name})
	}
	
	
	// return
	return $r;
	
}


function acf_get_pretty_taxonomies( $taxonomies = array() ) {
	
	// get post types
	if( empty($taxonomies) ) {
		
		// get all custom post types
		$taxonomies = acf_get_taxonomies();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( array_keys($taxonomies) as $i ) {
		
		// vars
		$taxonomy = acf_extract_var( $taxonomies, $i);
		$obj = get_taxonomy( $taxonomy );
		$name = $obj->labels->singular_name;
		
		
		// append to r
		$r[ $taxonomy ] = $name;
		
		
		// increase counter
		if( !isset($ref[ $name ]) ) {
			
			$ref[ $name ] = 0;
			
		}
		
		$ref[ $name ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$taxonomy = $r[ $i ];
		
		if( $ref[ $taxonomy ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_get_taxonomy_terms
*
*  This function will return an array of available taxonomy terms
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$taxonomies (array)
*  @return	(array)
*/

function acf_get_taxonomy_terms( $taxonomies = array() ) {
	
	// force array
	$taxonomies = acf_force_type_array( $taxonomies );
	
	
	// get pretty taxonomy names
	$taxonomies = acf_get_pretty_taxonomies( $taxonomies );
	
	
	// vars
	$r = array();
	
	
	// populate $r
	foreach( array_keys($taxonomies) as $taxonomy ) {
		
		// vars
		$label = $taxonomies[ $taxonomy ];
		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		
		
		if( !empty($terms) ) {
			
			$r[ $label ] = array();
			
			foreach( $terms as $term ) {
			
				$k = "{$taxonomy}:{$term->slug}"; 
				$r[ $label ][ $k ] = $term->name;
				
			}
			
		}
		
	}
		
	
	// return
	return $r;
	
}


/*
*  acf_decode_taxonomy_terms
*
*  This function decodes the $taxonomy:$term strings into a nested array
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$terms (array)
*  @return	(array)
*/

function acf_decode_taxonomy_terms( $terms = false ) {
	
	// load all taxonomies if not specified in args
	if( !$terms ) {
		
		$terms = acf_get_taxonomy_terms();
		
	}
	
	
	// vars
	$r = array();
	
	
	foreach( $terms as $term ) {
		
		// vars
		$data = acf_decode_taxonomy_term( $term );
		
		
		// create empty array
		if( !array_key_exists($data['taxonomy'], $r) )
		{
			$r[ $data['taxonomy'] ] = array();
		}
		
		
		// append to taxonomy
		$r[ $data['taxonomy'] ][] = $data['term'];
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_decode_taxonomy_term
*
*  This function will convert a term string into an array of term data
*
*  @type	function
*  @date	31/03/2014
*  @since	5.0.0
*
*  @param	$string (string)
*  @return	(array)
*/

function acf_decode_taxonomy_term( $string ) {
	
	// vars
	$r = array();
	
	
	// vars
	$data = explode(':', $string);
	$taxonomy = 'category';
	$term = '';
	
	
	// check data
	if( isset($data[1]) ) {
		
		$taxonomy = $data[0];
		$term = $data[1];
		
	}
	
	
	// add data to $r
	$r['taxonomy'] = $taxonomy;
	$r['term'] = $term;
	
	
	// return
	return $r;
	
}


/*
*  acf_cache_get
*
*  This function is a wrapper for the wp_cache_get to allow for 3rd party customization
*
*  @type	function
*  @date	4/12/2013
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

/*
function acf_cache_get( $key, &$found ) {
	
	// vars
	$group = 'acf';
	$force = false;
	
	
	// load from cache
	$cache = wp_cache_get( $key, $group, $force, $found );
	
	
	// allow 3rd party customization if cache was not found
	if( !$found )
	{
		$custom = apply_filters("acf/get_cache/{$key}", $cache);
		
		if( $custom !== $cache )
		{
			$cache = $custom;
			$found = true;
		}
	}
	
	
	// return
	return $cache;
	
}
*/


/*
*  acf_force_type_array
*
*  This function will force a variable to become an array
*
*  @type	function
*  @date	4/02/2014
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	(array)
*/

function acf_force_type_array( $var ) {
	
	// is array?
	if( is_array($var) ) {
	
		return $var;
	
	}
	
	
	// bail early if empty
	if( empty($var) && !is_numeric($var) ) {
		
		return array();
		
	}
	
	
	// string 
	if( is_string($var) ) {
		
		return explode(',', $var);
		
	}
	
	
	// place in array
	return array( $var );
} 


/*
*  acf_get_posts
*
*  This function will return all posts grouped by post_type
*  This is handy for select settings
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_posts( $args ) {
	
	// vars
	$r = array();
	
	
	// defaults
	$args = acf_parse_args( $args, array(
		'posts_per_page'			=>	-1,
		'post_type'					=> 'post',
		'orderby'					=> 'menu_order title',
		'order'						=> 'ASC',
		'post_status'				=> 'any',
		'suppress_filters'			=> false,
		'update_post_meta_cache'	=> false,
	));

	
	// find array of post_type
	$post_types = acf_force_type_array( $args['post_type'] );
		
	
	// attachment doesn't work if it is the only item in an array
	if( count($post_types) == 1 ) {
	
		$args['post_type'] = current($post_types);
		
	}
	
	
	// get posts
	$posts = get_posts( $args );
	
	
	// loop
	foreach( $post_types as $post_type ) {
		
		// vars
		$this_posts = array();
		$this_group = array();
		
		
		// populate $this_posts
		foreach( array_keys($posts) as $key ) {
		
			if( $posts[ $key ]->post_type == $post_type ) {
				
				$this_posts[] = acf_extract_var( $posts, $key );
				
			}
			
		}
		
		
		// bail early if no posts for this post type
		if( empty($this_posts) ) {
		
			continue;
			
		}
		
		
		// sort into hierachial order!
		if( is_post_type_hierarchical( $post_type ) ) {
			
			// this will fail if a search has taken place because parents wont exist
			if( empty($args['s']) ) {
			
				$this_posts = get_page_children( 0, $this_posts );
				
			}
			
		}
		
		
		// populate $this_posts
		foreach( array_keys($this_posts) as $key ) {
			
			// extract post
			$post = acf_extract_var( $this_posts, $key );
			
			
			// add to group
			$this_group[ $post->ID ] = $post;
			
		}
		
		
		// group by post type
		$post_type_object = get_post_type_object( $post_type );
		$post_type_name = $post_type_object->labels->name;
		
		$r[ $post_type_name ] = $this_group;
					
	}
	
	
	// return
	return $r;
	
}


function acf_get_post_title( $post = 0 ) {
	
	// title
	$title = '';
	
	
	// load post if given an ID
	if( is_numeric($post) ) {
		
		$post = get_post($post);
		
	}
	
	
	// ancestors
	if( $post->post_type != 'attachment' ) {
		
		$ancestors = get_ancestors( $post->ID, $post->post_type );
		
		if( !empty($ancestors) ) {
		
			$title .= str_repeat('- ', count($ancestors));
			
		}
		
	}
	
	
	// title
	$title .= get_the_title( $post->ID );
	
	
	// status
	if( get_post_status( $post->ID ) != "publish" ) {
		
		$title .= ' (' . get_post_status( $post->ID ) . ')';
		
	}
	
	
	// return
	return $title;
	
}


function acf_order_by_search( $array, $search ) {
	
	// vars
	$weights = array();
	$needle = strtolower( $search );
	
	
	// add key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ '_' . $k ] = acf_extract_var( $array, $k );
		
	}


	// add search weight
	foreach( $array as $k => $v ) {
	
		// vars
		$weight = 0;
		$haystack = strtolower( $v );
		$strpos = strpos( $haystack, $needle );
		
		
		// detect search match
		if( $strpos !== false ) {
			
			// set eright to length of match
			$weight = strlen( $search );
			
			
			// increase weight if match starts at begining of string
			if( $strpos == 0 ) {
				
				$weight++;
				
			}
			
		}
		
		
		// append to wights
		$weights[ $k ] = $weight;
		
	}
	
	
	// sort the array with menu_order ascending
	array_multisort( $weights, SORT_DESC, $array );
	
	
	// remove key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ substr($k,1) ] = acf_extract_var( $array, $k );
		
	}
		
	
	// return
	return $array;
}



/*
*  acf_json_encode
*
*  This function will return pretty JSON for all PHP versions
*
*  @type	function
*  @date	6/03/2014
*  @since	5.0.0
*
*  @param	$json (array)
*  @return	(string)
*/

function acf_json_encode( $json ) {
	
	// PHP at least 5.4
	if( version_compare(PHP_VERSION, '5.4.0', '>=') )
	{
		return json_encode($json, JSON_PRETTY_PRINT);
	}

	
	
	// PHP less than 5.4
	$json = json_encode($json);
	
	
	// http://snipplr.com/view.php?codeview&id=60559
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
	
	
	// return
    return $result;
	
}


/*
*  acf_str_exists
*
*  This function will return true if a sub string is found
*
*  @type	function
*  @date	1/05/2014
*  @since	5.0.0
*
*  @param	$needle (string)
*  @param	$haystack (string)
*  @return	(boolean)
*/

function acf_str_exists( $needle, $haystack ) {
	
	// return true if $haystack contains the $needle
	if( is_string($haystack) && strpos($haystack, $needle) !== false ) {
		
		return true;
		
	}
	
	
	// return
	return false;
}


/*
*  acf_debug
*
*  description
*
*  @type	function
*  @date	2/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_debug() {
	
	// vars
	$args = func_get_args();
	$s = array_shift($args);
	$o = '';
	$nl = "\r\n";
	
	
	// start script
	$o .= '<script type="text/javascript">' . $nl;
	
	$o .= 'console.log("' . $s . '"';
	
	if( !empty($args) ) {
		
		foreach( $args as $arg ) {
			
			if( is_object($arg) || is_array($arg) ) {
				
				$arg = json_encode($arg);
				
			} elseif( is_bool($arg) ) {
				
				$arg = $arg ? 'true' : 'false';
				
			}elseif( is_string($arg) ) {
				
				$arg = '"' . $arg . '"';
				
			}
			
			$o .= ', ' . $arg;
			
		}
	}
	
	$o .= ');' . $nl;
	
	
	// end script
	$o .= '</script>' . $nl;
	
	
	// echo
	echo $o;
}

function acf_debug_start() {
	
	acf_update_setting( 'debug_start', memory_get_usage());
	
}

function acf_debug_end() {
	
	$start = acf_get_setting( 'debug_start' );
	$end = memory_get_usage();
	
	return $end - $start;
	
}


/*
*  acf_get_updates
*
*  This function will reutrn all or relevant updates for ACF
*
*  @type	function
*  @date	12/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_updates() {
	
	// cache
	$found = false;
	$cache = wp_cache_get( 'acf_get_updates', 'acf', false, $found );
	
	if( $found ) {
	
		return $cache;
		
	}
	
	
	// vars
	$updates = array();
	$plugin_version = acf_get_setting('version');
	$acf_version = get_option('acf_version');
	$path = acf_get_path('admin/updates');
	
	
	// check that path exists
	if( !file_exists( $path ) ) {
	
		return false;
		
	}
	
	
	$dir = opendir( $path );

    while(false !== ( $file = readdir($dir)) ) {
    
    	// only php files
    	if( substr($file, -4) !== '.php' ) {
    	
	    	continue;
	    	
    	}
    	
    	
    	// get version number
    	$update_version = substr($file, 0, -4);
    	
    	
    	// ignore if update is for a future version. May exist for testing
		if( version_compare( $update_version, $plugin_version, '>') ) {
		
			continue;
			
		}
		
		// ignore if update has already been run
		if( version_compare( $update_version, $acf_version, '<=') ) {
		
			continue;
			
		}
		
		
    	// append
        $updates[] = $update_version;
        
    }
    
    
    // set cache
	wp_cache_set( 'acf_get_updates', $updates, 'acf' );
	
    
    // return
    return $updates;
	
}


/*
*  acf_encode_choices
*
*  description
*
*  @type	function
*  @date	4/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_encode_choices( $array = array() ) {
	
	// bail early if already string
	if( is_string($array) ) {
		
		return $array;
	}
	
	
	// vars
	$string = '';
	
	
	// bail ealry if not array
	if( !is_array($array) ) {
		
		return $string;
	}
	
	
	if( !empty($array) ) {
		
		foreach( $array as $k => $v ) { 
			
			if( $k !== $v ) {
				
				$array[ $k ] = $k . ' : ' . $v;
				
			}
			
		}
		
		$string = implode("\n", $array);
		
	}
	
	
	// return
	return $string;
	
}

function acf_decode_choices( $string = '' ) {
	
	// bail early if already array
	if( is_array($string) ) {
		
		return $string;
	}
	
	
	// vars
	$array = array();
	
	
	// bail ealry if not string
	if( !is_string($string) ) {
		
		return $array;
	}
	
	
	// explode choices from each line
	if( !empty($string) ) {
		
		// stripslashes ("")
		$string = stripslashes_deep($string);
		
		
		// explode
		$temp = explode("\n", $string);
		
		
		// key => value
		foreach( $temp as $v ) {
			
			if( acf_str_exists(' : ', $v) ) {
			
				$v = explode(' : ', $v);
				
				$array[ trim($v[0]) ] = trim($v[1]);
				
			}
			else
			{
				$array[ trim($v) ] = trim($v);
			}
		}
	}
	
	
	// return
	return $array;
	
}



/*
*  acf_convert_date_to_php
*
*  This fucntion converts a date format string from JS to PHP
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$date (string)
*  @return	$date (string)
*/

acf_update_setting('php_to_js_date_formats', array(

	// Year
	'Y'	=> 'yy',	// Numeric, 4 digits 								1999, 2003
	'y'	=> 'y',		// Numeric, 2 digits 								99, 03
	
	
	// Month
	'm'	=> 'mm',	// Numeric, with leading zeros  					01–12
	'n'	=> 'm',		// Numeric, without leading zeros  					1–12
	'F'	=> 'MM',	// Textual full   									January – December
	'M'	=> 'M',		// Textual three letters    						Jan - Dec 
	
	
	// Weekday
	'l'	=> 'DD',	// Full name  (lowercase 'L') 						Sunday – Saturday
	'D'	=> 'D',		// Three letter name 	 							Mon – Sun 
	
	
	// Day of Month
	'd'	=> 'dd',	// Numeric, with leading zeros						01–31
	'j'	=> 'd',		// Numeric, without leading zeros 					1–31
	'S'	=> '',		// The English suffix for the day of the month  	st, nd or th in the 1st, 2nd or 15th. 

));

function acf_convert_date_to_php( $date ) {
	
	// vars
	$ignore = array();
	
	
	// conversion
	$php_to_js = acf_get_setting('php_to_js_date_formats');
	
	
	// loop over conversions
	foreach( $php_to_js as $replace => $search ) {
		
		// ignore this replace?
		if( in_array($search, $ignore) ) {
			
			continue;
			
		}
		
		
		// replace
		$date = str_replace($search, $replace, $date);
		
		
		// append to ignore
		$ignore[] = $replace;
	}
	
	
	// return
	return $date;
	
}

/*
*  acf_convert_date_to_js
*
*  This fucntion converts a date format string from PHP to JS
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_convert_date_to_js( $date ) {
	
	// vars
	$ignore = array();
	
	
	// conversion
	$php_to_js = acf_get_setting('php_to_js_date_formats');
	
	
	// loop over conversions
	foreach( $php_to_js as $search => $replace ) {
		
		// ignore this replace?
		if( in_array($search, $ignore) ) {
			
			continue;
			
		}
		
		
		// replace
		$date = str_replace($search, $replace, $date);
		
		
		// append to ignore
		$ignore[] = $replace;
	}
	
	
	// return
	return $date;
	
}


/*
*  acf_update_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_update_user_setting( $name, $value ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', false );
	
	
	// find settings
	if( isset($settings[0]) ) {
	
		$settings = $settings[0];
	
	} else {
		
		$settings = array();
		
	}
	
	
	// append setting
	$settings[ $name ] = $value;
	
	
	// update user data
	return update_metadata('user', $user_id, 'acf_user_settings', $settings);
	
	
}


/*
*  acf_get_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_user_setting( $name = '', $default = false ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', false );
	
	
	// bail arly if no settings
	if( empty($settings[0][$name]) ) {
		
		return $default;
		
	}
	
	
	// return
	return $settings[0][$name];
	
}


/*
*  acf_in_array
*
*  description
*
*  @type	function
*  @date	22/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_in_array( $value, $array ) {
	
	// bail early if not array
	if( !is_array($array) ) {
		
		return false;
		
	}
	
	
	// find value in array
	return in_array($value, $array);
	
}


/*
*  Hacks
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

add_filter("acf/settings/slug", '_acf_settings_slug');

function _acf_settings_slug( $v ) {
	
	$basename = acf_get_setting('basename');
    $slug = explode('/', $basename);
    $slug = current($slug);
	
	return $slug;
}

?>
