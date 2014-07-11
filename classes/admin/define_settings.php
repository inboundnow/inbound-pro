<?php
/*
	Register Pro Settings screen
 */

if ( ! class_exists('Inbound_Now_Settings') )
{
	class Inbound_Now_Settings {
		private $settings;
		private $pageHook;

		public function __construct() {
			require_once dirname( __FILE__ ) . '/class.settings-api.php';
			$this->settings = cnSettingsAPI::getInstance();

			add_action( 'admin_menu', array( &$this , 'loadSettingsPage' ) );
			add_action( 'plugins_loaded', array( &$this , 'init') );
		}

		public function init() {
			/*
			 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
			 * Init the registered settings.
			 * NOTE: The init method must be run after registering the tabs, sections and fields.
			 */
			add_filter( 'cn_register_settings_tabs' , array( &$this , 'tabs' ) );
			add_filter( 'cn_register_settings_sections' , array( &$this , 'sections' ) );
			add_filter( 'cn_register_settings_fields' , array( &$this , 'fields' ) );
			$this->settings->init();
		}

		public function loadSettingsPage() {
			//$this->pageHook = add_options_page( 'Settings API', 'Settings API', 'manage_options', 'settings_inbound_now', array( &$this , 'showPage' ) );
			$this->pageHook = add_menu_page( 'Inbound', 'Inbound', 'manage_options', 'settings_inbound_now', array( &$this , 'showPage' ), plugins_url( 'inbound-now-pro/assets/images/shortcodes-blue.png' ), 3);
		}

		public function tabs( $tabs ) {
			// Register the core tab banks.
			 $tabs[] = array(
				'id' => 'manage_addons' ,
				'position' => 0 ,
				'title' => __( 'Manage Addons' , 'inbound-now' ) ,
				'page_hook' => $this->pageHook
			);
			/*$tabs[] = array(
				'id' => 'basic' ,
				'position' => 10 ,
				'title' => __( 'Basic' , 'inbound-now' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'other' ,
				'position' => 20 ,
				'title' => __( 'Other' , 'inbound-now' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'advanced' ,
				'position' => 30 ,
				'title' => __( 'Advanced' , 'inbound-now' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'custom' ,
				'position' => 30 ,
				'title' => __( 'custom' , 'inbound-now' ) ,
				'page_hook' => $this->pageHook,
				'type' => 'custom'
			);*/

			return $tabs;
		}

		public function sections( $sections ) {

			$sections[] = array(
				'tab' => 'manage_addons' ,
				'id' => 'manage_inbound_addons' ,
				'position' => 0 ,
				'title' => __( 'Manage Addons' , 'inbound-now' ) ,
				'callback' => 'inbound_manage_addon_screen',
				'page_hook' => $this->pageHook
			);
			/* $sections[] = array(
				'tab' => 'basic' ,
				'id' => 'basic_one' ,
				'position' => 10 ,
				'title' => __( 'Test Section One' , 'inbound-now' ) ,
				'callback' => create_function( '', "_e( 'Test Section One Description.' , 'inbound-now' );" ) ,
				'page_hook' => $this->pageHook
			);
			$sections[] = array(
				'tab' => 'basic' ,
				'id' => 'basic_two' ,
				'position' => 20 ,
				'title' => __( 'Test Section Two' , 'inbound-now' ) ,
				'callback' => create_function( '', "_e( 'Test Section Two Description.' , 'inbound-now' );" ) ,
				'page_hook' => $this->pageHook
			); */

			return $sections;
		}

		static function test_call_back() {
			echo "Hi";
		}

		public function fields( $fields ) {
			// Test Fields -- Remove before release.
			/*$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'checkbox_test',
				'position' => 5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Checkbox', 'inbound-now'),
				'desc' => __('Checkbox Label.', 'inbound-now'),
				'help' => __('testing'),
				'type' => 'checkbox',
				'default' => 1
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'function_test',
				'position' => 5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('test', 'inbound-now'),
				'desc' => 'work_please();',
				'help' => __('testing'),
				'options' => array(
					'one' => 'One',
					'two' => 'Two',
					'three' => 'Three',
					'four' => 'Four'
				),
				'type' => 'custom_function',
				'default' => 'test()'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'textarea_test',
				'position' => 30,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Rich Text Area', 'inbound-now'),
				'desc' => __('This is a test of the RTE.', 'inbound-now'),
				'help' => __('ttttttttt'),
				'type' => 'rte',
				'size' => 'large',
				'default' => '<span style="text-decoration: underline;">Default <strong>text</strong> with <em>style</em>!</span>'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'textarea_large',
				'position' => 29,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Large Text Area', 'inbound-now'),
				'desc' => __('Text Area', 'inbound-now'),
				'help' => __(''),
				'type' => 'textarea',
				'size' => 'large',
				'default' => 'LARGE TEXT AREA'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'text_regular',
				'position' => 28,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Regular Text', 'inbound-now'),
				'desc' => __('Regular Text Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'regular',
				'default' => 'Regular'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'text_small',
				'position' => 27,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Small Text', 'inbound-now'),
				'desc' => __('Small Text Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'small',
				'default' => 'SML'

			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'text_large',
				'position' => 29,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Large Text', 'inbound-now'),
				'desc' => __('Large Text Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'large',
				'default' => 'LARGE'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'quicktag',
				'position' => 29.5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Quicktag Text', 'inbound-now'),
				'desc' => __('Quicktag Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'quicktag',
				'default' => 'Quicktag Textarea!'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'multicheck_test',
				'position' => 21,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Multi-Checkbox', 'inbound-now'),
				'desc' => __('Multi-Checkbox Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'multicheckbox',
				'options' => array(
					'one' => 'One',
					'two' => 'Two',
					'three' => 'Three',
					'four' => 'Four'
				),
				'default' => array( 'one' , 'three' )
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'radio_test',
				'position' => 22,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Radio', 'inbound-now'),
				'desc' => __('Radio Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'radio',
				'options' => array(
					'yes' => 'Yes',
					'no' => 'No'
				),
				'default' => 'yes'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'select_test',
				'position' => 23,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Select', 'inbound-now'),
				'desc' => __('Select Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'select',
				'options' => array(
					'one' => 'One',
					'two' => 'Two',
					'three' => 'Three',
					'four' => 'Four'
				),
				'default' => 'two'
			);
			$fields[] = array(
				'plugin_id' => 'inbound-now',
				'id' => 'multi_select_test',
				'position' => 24,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Multi-Select', 'inbound-now'),
				'desc' => __('Multi-Select Label', 'inbound-now'),
				'help' => __(''),
				'type' => 'multiselect',
				'options' => array(
									'one' => 'One',
									'two' => 'Two',
									'three' => 'Three',
									'four' => 'Four',
									'five' => 'Five',
									'six' => 'Six',
									'seven' => 'Seven',
									'eight' => 'Eight',
									'nine' => 'Nine',
									'ten' => 'Ten'
				),
				'default' => array( 'two' , 'four' )
			); */

			return $fields;
		}

		public function showPage() {
			echo '<div class="wrap">';

			$args = array(
				'page_icon' => '',
				'page_title' => 'Inbound Now Pro Settings',
				'tab_icon' => 'options-general'
				);

			$this->settings->form( $this->pageHook , $args );

			echo '</div>';
		}
	}

	global $Inbound_Now_Settings;
	$Inbound_Now_Settings = new Inbound_Now_Settings();
}

if (!function_exists('inbound_manage_addon_screen')) {
	function inbound_manage_addon_screen() { ?>
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
			</table></div>";
	}
}

add_action('wp_ajax_inbound_toggle_addons_ajax', 'inbound_toggle_addons_ajax');
add_action('wp_ajax_nopriv_inbound_toggle_addons_ajax', 'inbound_toggle_addons_ajax');

function inbound_toggle_addons_ajax() {
      // Post Values
      $the_addon = (isset( $_POST['the_addon'] )) ? $_POST['the_addon'] : "";
      $toggle = (isset( $_POST['toggle'] )) ? $_POST['toggle'] : "";

    /* Store Script Data to Post */
    $toggled_addon_files = get_transient( 'inbound-now-active-addons' );

    if(is_array($toggled_addon_files)) {

        if($toggle === 'on') {
          // add or remove from list
          $toggled_addon_files[] = $the_addon;
        } else {
          unset($toggled_addon_files[$the_addon]);
          $toggled_addon_files = array_diff($toggled_addon_files, array($the_addon));
        }

    } else {
      // Create the first item in array
      if($toggle === 'on') {
      	$toggled_addon_files[0] = $the_addon;
      }
    }

    set_transient('inbound-now-active-addons', $toggled_addon_files );

    //
    $output =  array('encode'=> 'end' );

    echo json_encode($output,JSON_FORCE_OBJECT);
    wp_die();
 }

?>