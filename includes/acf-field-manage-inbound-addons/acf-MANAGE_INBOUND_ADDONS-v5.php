<?php

class acf_field_MANAGE_INBOUND_ADDONS extends acf_field {


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

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'MANAGE_INBOUND_ADDONS';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('MANAGE_INBOUND_ADDONS', 'acf-MANAGE_INBOUND_ADDONS');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'inbound_now';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array(
			'font_size'	=> 14,
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('MANAGE_INBOUND_ADDONS', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'acf-MANAGE_INBOUND_ADDONS'),
		);


		// do not delete!
    	parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		acf_render_field_setting( $field, array(
			'label'			=> __('Font Size','acf-MANAGE_INBOUND_ADDONS'),
			'instructions'	=> __('Customise the input font size','acf-MANAGE_INBOUND_ADDONS'),
			'type'			=> 'number',
			'name'			=> 'font_size',
			'prepend'		=> 'px',
		));

	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {


		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/
		/*
		echo '<pre>';
			print_r( $field );
		echo '</pre>';

		/**/
		/*
		*  Create a simple text input using the 'font_size' setting.
		*/

		?>
				<style type="text/css">cite{display:none !important;}

				#inbound-addon-toggles .toggleswitch {
					position: relative;
					margin: 10px;
					width: 80px;
					display: inline-block;
					vertical-align: top;
					-webkit-user-select: none;
					-moz-user-select:    none;
					-ms-user-select:     none;
				}

				#inbound-addon-toggles [type="checkbox"] {
					display: none;
				}

				#inbound-addon-toggles label {
					display: block;
					border-radius: 1px;
					overflow: hidden;
					cursor: pointer;
				}

				#inbound-addon-toggles label > div {
					width: 200%;
					margin-left: -100%;
					font-family:"FontAwesome";
					-webkit-transition: margin 0.1s ease-in 0s;
					-moz-transition:    margin 0.1s ease-in 0s;
					-o-transition:      margin 0.1s ease-in 0s;
					transition:         margin 0.1s ease-in 0s;
				}

				#inbound-addon-toggles label > div:before, #inbound-addon-toggles label > div:after {
					float: left;
					width: 50%;
					height: 27px;
					padding: 0;
					line-height: 27px;
					font-size: 16px;
					-webkit-box-sizing: border-box;
					-moz-box-sizing:    border-box;
					box-sizing:         border-box;
				}

				#inbound-addon-toggles label > div:before {
					content: "\f00c";
					padding-left: 14px;
					background-color: #56b78a;
					color: #fff;
				}

				#inbound-addon-toggles label > div:after {
					content: "\f00d";
					padding-right: 15px;
					background-color: #ccc; color: #666666;
					text-align: right;
				}

				#inbound-addon-toggles label span {
					width: 33px;
					margin: 3px;
					background: #fff;
					border-radius: 1px;
					position: absolute;
					top: 0;
					bottom: 0;
					right: 41px;
					-webkit-transition: all 0.1s ease-in 0s;
					-moz-transition:    all 0.1s ease-in 0s;
					-o-transition:      all 0.1s ease-in 0s;
					transition:         all 0.1s ease-in 0s;
				}

				#inbound-addon-toggles [type="checkbox"]:checked + label > div {
					margin-left: 0;
				}

				#inbound-addon-toggles [type="checkbox"]:checked + label > span {
					right: 0px;
				}
				#addon-name {

					display: block;
					font-weight: bold;
					font-size: 15px;
					color: #232222;
				}
				.addon-thumb {
					position: relative;
				}
				.component-status {
					width: 90px;
					font-size: 11px !important;
				}
				td.addon-thumb {
					padding-bottom: 0px;
					padding-top: 0px;
				}
				.addon-thumbnail-th {
					width: 100px;
				}
				#submit{
					display: none;
				}
				</style>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
				   jQuery.fn.flatcheckbox = function() {
				   	  return this.each(function() {
				         $(this).wrap('<div class="toggleswitch"></div>').after('<label for="'+$(this).attr('id')+'"><div></div><span></span>');
				       });
				   };

				   $('input:checkbox').flatcheckbox();
				 });

				jQuery(document).ready(function($) {
				   jQuery("body").on('click', '.toggleswitch label', function () {

				   	var status = jQuery(this).parent().find('input').attr('checked');
				   	if(status === 'checked') {
				   		toggle = 'off';
				   	} else {
				   		toggle = 'on';
				   	}
				   	var the_addon = jQuery(this).parent().find('input').attr('class');
				   	console.log(toggle);
				   	console.log(the_addon);

				 	jQuery.ajax({
					   	    type: 'POST',
					   	    url: ajaxurl,
					   	    context: this,
					   	    data: {
					   	        action: 'inbound_toggle_addons_ajax',
					   	        toggle: toggle,
					   	        the_addon: the_addon,
					   	    },

					   	    success: function (data) {
					   	       console.log("The script " + the_addon + " has been turned " + toggle);
					   	       var self = this;
					   	       var str = data;
					   	       var obj = JSON.parse(str);
					   	      console.log(obj);
					   	    },

					   	    error: function (MLHttpRequest, textStatus, errorThrown) {
					   	        alert("Ajax not enabled");
					   	    }
					   	});
				     });
				 });

				</script>
			<div id='inbound-addon-toggles'>

				<h3>Toggle which inbound now components you would like to run on your site</h3>
				<table class="widefat" id="lead-manage-table">


									<thead>
										<tr>

											<th scope="col" class="sort-header addon-thumbnail-th">Name</th>
											<th class="checkbox-header no-sort component-status" scope="col">
												Component Status
											</th>
											<th scope="col" class="sort-header">Description</th>

										</tr>
									</thead>
									<tbody id="the-list" class="ui-selectable">

		<?php

		    	// Set Variable if welcome folder exists
		    	$dir = INBOUND_NOW_PATH . '/components/';
		    	$toggled_addon_files = get_transient( 'inbound-now-active-addons' );
		    	//print_r($toggled_addon_files);
				if(file_exists($dir)) {
					$checked =  "";

					foreach (scandir($dir) as $item) {
						if ($item == '.' || $item == '..' || $item == '.DS_Store') continue;

						if (is_array($toggled_addon_files) && in_array($item, $toggled_addon_files)) {
							$checked =  "checked";
						} else {
							$checked = '';
						}
						echo '<tr class="">';
						$plugin_file = INBOUND_NOW_PATH . '/components/'.$item.'/'.$item.'.php';
						$plugin_data = get_plugin_data( $plugin_file, $markup = true, $translate = true );

						$name = (isset($plugin_data['Name'])) ? $plugin_data['Name'] : '';
						$description = (isset($plugin_data['Description'])) ? $plugin_data['Description'] : '';
						$thumbnail = INBOUND_NOW_PATH . '/components/'.$item.'/thumbnail.png';
						if (file_exists($thumbnail)) {
						$thumb_link = INBOUND_NOW_URL . '/components/'.$item.'/thumbnail.png';
						} else {
						$thumb_link = INBOUND_NOW_URL . '/assets/images/default-thumbnail.jpg';
						}
						echo "<td class='addon-thumb'><img width='105' src='" . $thumb_link . "'></td>";
						echo '<td><input type="checkbox" class="'.$item.'" name="'.$item.'-status" id="'.$item. '-toggle" '.$checked.' /></td>';
						//echo '<input type="checkbox" id="switch2" />';

						echo "<td><div id='addon-name'>".$name."</div>" . $description . "</td>";
						echo "</tr>";
					}
				}
				echo "</tbody>
					</table></div>"; ?>
		<input type="text" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" style="font-size:<?php echo $field['font_size'] ?>px;" />
		<?php
	}


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_enqueue_scripts() {

		$dir = plugin_dir_url( __FILE__ );


		// register & include JS
		wp_register_script( 'acf-input-MANAGE_INBOUND_ADDONS', "{$dir}js/input.js" );
		wp_enqueue_script('acf-input-MANAGE_INBOUND_ADDONS');


		// register & include CSS
		wp_register_style( 'acf-input-MANAGE_INBOUND_ADDONS', "{$dir}css/input.css" );
		wp_enqueue_style('acf-input-MANAGE_INBOUND_ADDONS');


	}

	*/


	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_head() {



	}

	*/


	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

   	/*

   	function input_form_data( $args ) {



   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {



	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_enqueue_scripts() {

	}

	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function load_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function update_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


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

	/*

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if( empty($value) ) {

			return $value;

		}


		// apply setting
		if( $field['font_size'] > 12 ) {

			// format the value
			// $value = 'something';

		}


		// return
		return $value;
	}

	*/


	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	/*

	function validate_value( $valid, $value, $field, $input ){

		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}


		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','acf-MANAGE_INBOUND_ADDONS'),
		}


		// return
		return $valid;

	}

	*/


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*

	function delete_value( $post_id, $key ) {



	}

	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {



	}

	*/


}


// create field
new acf_field_MANAGE_INBOUND_ADDONS();

?>
