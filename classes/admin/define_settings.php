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
			$this->pageHook = add_menu_page( 'Inbound Now', 'Inbound Now', 'manage_options', 'settings_inbound_now', array( &$this , 'showPage' ), plugins_url( 'inbound-now-pro/assets/images/shortcodes-blue.png' ), 10 );
		}



		public function register_my_custom_menu_page(){
		    add_menu_page( 'custom menu title', 'custom menu', 'manage_options', 'myplugin/myplugin-admin.php', '', plugins_url( 'myplugin/images/icon.png' ), 6 );
		}

		public function tabs( $tabs ) {
			// Register the core tab banks.
			$tabs[] = array(
				'id' => 'basic' ,
				'position' => 10 ,
				'title' => __( 'Basic' , 'connections_settings_api' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'other' ,
				'position' => 20 ,
				'title' => __( 'Other' , 'connections_settings_api' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'advanced' ,
				'position' => 30 ,
				'title' => __( 'Advanced' , 'connections_settings_api' ) ,
				'page_hook' => $this->pageHook
			);

			$tabs[] = array(
				'id' => 'custom' ,
				'position' => 30 ,
				'title' => __( 'custom' , 'connections_settings_api' ) ,
				'page_hook' => $this->pageHook,
				'type' => 'custom'
			);

			return $tabs;
		}

		public function sections( $sections ) {

			$sections[] = array(
				'tab' => 'basic' ,
				'id' => 'manage_inbound_addons' ,
				'position' => 0 ,
				'title' => __( 'Manage Addons' , 'connections_settings_api' ) ,
				'callback' => 'inbound_manage_addon_screen',
				'page_hook' => $this->pageHook
			);
			$sections[] = array(
				'tab' => 'basic' ,
				'id' => 'basic_one' ,
				'position' => 10 ,
				'title' => __( 'Test Section One' , 'connections_settings_api' ) ,
				'callback' => create_function( '', "_e( 'Test Section One Description.' , 'connections_settings_api' );" ) ,
				'page_hook' => $this->pageHook
			);
			$sections[] = array(
				'tab' => 'basic' ,
				'id' => 'basic_two' ,
				'position' => 20 ,
				'title' => __( 'Test Section Two' , 'connections_settings_api' ) ,
				'callback' => create_function( '', "_e( 'Test Section Two Description.' , 'connections_settings_api' );" ) ,
				'page_hook' => $this->pageHook
			);

			return $sections;
		}

		static function test_call_back() {
			echo "Hi";
		}

		public function fields( $fields ) {
			// Test Fields -- Remove before release.
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'checkbox_test',
				'position' => 5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Checkbox', 'connections_settings_api'),
				'desc' => __('Checkbox Label.', 'connections_settings_api'),
				'help' => __('testing'),
				'type' => 'checkbox',
				'default' => 1
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'function_test',
				'position' => 5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('test', 'connections_settings_api'),
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
				'plugin_id' => 'connections_settings_api',
				'id' => 'textarea_test',
				'position' => 30,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Rich Text Area', 'connections_settings_api'),
				'desc' => __('This is a test of the RTE.', 'connections_settings_api'),
				'help' => __('ttttttttt'),
				'type' => 'rte',
				'size' => 'large',
				'default' => '<span style="text-decoration: underline;">Default <strong>text</strong> with <em>style</em>!</span>'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'textarea_large',
				'position' => 29,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Large Text Area', 'connections_settings_api'),
				'desc' => __('Text Area', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'textarea',
				'size' => 'large',
				'default' => 'LARGE TEXT AREA'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'text_regular',
				'position' => 28,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Regular Text', 'connections_settings_api'),
				'desc' => __('Regular Text Label', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'regular',
				'default' => 'Regular'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'text_small',
				'position' => 27,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Small Text', 'connections_settings_api'),
				'desc' => __('Small Text Label', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'small',
				'default' => 'SML'

			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'text_large',
				'position' => 29,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Large Text', 'connections_settings_api'),
				'desc' => __('Large Text Label', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'text',
				'size' => 'large',
				'default' => 'LARGE'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'quicktag',
				'position' => 29.5,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Quicktag Text', 'connections_settings_api'),
				'desc' => __('Quicktag Label', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'quicktag',
				'default' => 'Quicktag Textarea!'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'multicheck_test',
				'position' => 21,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Multi-Checkbox', 'connections_settings_api'),
				'desc' => __('Multi-Checkbox Label', 'connections_settings_api'),
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
				'plugin_id' => 'connections_settings_api',
				'id' => 'radio_test',
				'position' => 22,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Radio', 'connections_settings_api'),
				'desc' => __('Radio Label', 'connections_settings_api'),
				'help' => __(''),
				'type' => 'radio',
				'options' => array(
					'yes' => 'Yes',
					'no' => 'No'
				),
				'default' => 'yes'
			);
			$fields[] = array(
				'plugin_id' => 'connections_settings_api',
				'id' => 'select_test',
				'position' => 23,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Select', 'connections_settings_api'),
				'desc' => __('Select Label', 'connections_settings_api'),
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
				'plugin_id' => 'connections_settings_api',
				'id' => 'multi_select_test',
				'position' => 24,
				'page_hook' => 'toplevel_page_settings_inbound_now',
				'tab' => 'basic',
				'section' => 'basic_one',
				'title' => __('Multi-Select', 'connections_settings_api'),
				'desc' => __('Multi-Select Label', 'connections_settings_api'),
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
			);

			return $fields;
		}

		public function showPage() {
			echo '<div class="wrap">';

			$args = array(
				'page_icon' => '',
				'page_title' => 'Connections : Settings API Test Page',
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


		</script>
<?php
    	// Set Variable if welcome folder exists
    	$dir = INBOUND_NOW_PATH . '/components/';

		if(file_exists($dir)) {
			$checked =  "";
			echo "<div id='inbound-addon-toggles'>";
			foreach (scandir($dir) as $item) {
				if ($item == '.' || $item == '..' || $item == '.DS_Store') continue;
				$plugin_file = INBOUND_NOW_PATH . '/components/'.$item.'/'.$item.'.php';
				$plugin_data = get_plugin_data( $plugin_file, $markup = true, $translate = true );
				//print_r($plugin_data);
				$description = (isset($plugin_data['Description'])) ? $plugin_data['Description'] : '';
				$thumbnail = INBOUND_NOW_PATH . '/components/'.$item.'/thumbnail.png';
				echo '<input type="checkbox" name="'.$item.'-status"" id="'.$item. '-toggle" '.$checked.' />';
				//echo '<input type="checkbox" id="switch2" />';
				if (file_exists($thumbnail)) {
				$thumb_link = INBOUND_NOW_URL . '/components/'.$item.'/thumbnail.png';
				echo "<img width='105' src='" . $thumb_link . "'>";
				} else {
				$thumb_link = INBOUND_NOW_URL . '/assets/images/default-thumbnail.jpg';
				echo "<img width='105' src='" . $thumb_link . "'>";
				}
				echo $description . "<br>";
			}
		}
		echo "</div>";

/*
		$inbound_load_files = array_unique(array_merge($toggled_addon_files, $default_pro_files));
		if (isset($inbound_load_files) && is_array($inbound_load_files)) {
			foreach ($inbound_load_files as $key => $value) {
				include_once('components/'.$value.'/'.$value.'.php'); // include each toggled on
			}
		}*/
	}
}

?>