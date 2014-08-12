<?php

/*
*  ACF Message Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_message
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_message') ) :

class acf_field_message extends acf_field {
	
	
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
		$this->name = 'message';
		$this->label = __("Message",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'message'	=> '',
		);
		
		
		// do not delete!
    	parent::__construct();
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
	
		echo wpautop( $field['message'] );
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Message','acf'),
			'instructions'	=> __('Please note that all text will first be passed through the wp function ','acf') . '<a href="http://codex.wordpress.org/Function_Reference/wpautop" target="_blank">wpautop()</a>',
			'type'			=> 'textarea',
			'name'			=> 'message',
		));
		
	}
	
}

new acf_field_message();

endif;

?>
