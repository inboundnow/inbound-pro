<?php

class Leads_CSV_Processing {

	public function __construct() {
		self::load_hooks();
	}
	
	public static function load_hooks() {
	
		/* Add sub menu to leads */
		add_action('admin_menu', array( __CLASS__ , 'add_sub_menus') , 99 );
		
		/* Load js components */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
		
		/* Add ajax listeners for temporarily storing uploaded CSV data */
		add_action( 'wp_ajax_nopriv_save_leads_csv_file', array( __CLASS__, 'save_csv_file') );
		add_action( 'wp_ajax_save_leads_csv_file',  array( __CLASS__, 'save_csv_file') );
	}

	/**
	*	Adds menu item under 'Leads'
	*/
	public static function add_sub_menus()
	{
		if (current_user_can('manage_options')) {

			add_submenu_page('edit.php?post_type=wp-lead', __( 'Import' , 'inbound-pro' ), __( 'Import' , 'cta' ) , 'manage_options', 'leads-import', array( __CLASS__ , 'import_ui' )) ;

		}
	}

	/**
	*	Enqueue JS & CSS scripts relative to CSV Importing administration screen
	*/
	public static function enqueue_scripts() {
	
		$screen = get_current_screen();


		if ( !isset($screen->base) || $screen->base != 'wp-lead_page_leads-import') {
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		
		/* load jQuery Form Plugin for ajax form submissions */
		wp_register_script( 'jquery-form' , INBOUND_CSV_IMPORTING_URLPATH .'libraries/jQueryForm/jquery.form.js' , array( 'jquery' ));
		wp_enqueue_script( 'jquery-form' );
		
		/* load bootstrap */
		wp_register_script( 'bootstrap-js' , INBOUND_CSV_IMPORTING_URLPATH .'libraries/BootStrap/bootstrap.min.js');
		wp_enqueue_script( 'bootstrap-js' );
	
		/* load bootstrap css */
		wp_register_style( 'bootstrap-css' , INBOUND_CSV_IMPORTING_URLPATH . 'libraries/BootStrap/bootstrap.css');
		wp_enqueue_style( 'bootstrap-css' );
		
		/* load ladda processing buttons js */
		wp_register_script( 'ladda-js' , INBOUND_CSV_IMPORTING_URLPATH .'libraries/Ladda/ladda.min.js');
		wp_enqueue_script( 'ladda-js' );
	
		/* load ladda processing buttons css */
		wp_register_style( 'ladda-css' , INBOUND_CSV_IMPORTING_URLPATH . 'libraries/Ladda/ladda-themeless.min.css');
		wp_enqueue_style( 'ladda-css' );
		
		/* load custom css */
		wp_register_style( 'leads-csv-css' , INBOUND_CSV_IMPORTING_URLPATH . 'css/style-admin.css');
		wp_enqueue_style( 'leads-csv-css' );
	}
	
	
	public static function inline_scripting() {
		?>
		
		<script type='text/javascript'>		
		
		/* Function to generate col to lead field map in step 2 */
		function build_map( col_json ) {
			 for(var i=0;i<col_json.length;i++){
				var obj = col_json[i];
				alert(obj);
			}
		}
		
		/* function for switching visible containers */
		function toggle_display( step ) {
			jQuery('.nav-tabs li').removeClass('active');
			
			if (step == 'step-2') {
				jQuery('#navtab-step-2').addClass('active');
				jQuery('.step-1').hide();
				jQuery('.step-2').show();
			}
		}
		
		jQuery( document ).ready( function() {
			
			/* Enable ladda button handlers */
			var ladda_1 = Ladda.create(document.querySelector( '.next-1' ));
			
			/* Enable ajaxForm on our uploader */
			jQuery('.csv-file-upload').ajaxForm();
			
			/* Run upload events */
			jQuery('.csv-file-upload').submit(function() { 
				ladda_1.toggle();
				
				var options = { 					
					url:	ajaxurl,
					type:	'POST',
					data: {
						'action' : 'save_leads_csv_file'
					},
					mimeType: "multipart/form-data",
					contentType: false,
					cache: false,
					success: function(data, textStatus, jqXHR) {
						ladda_1.toggle();
						alert( data );
						build_map( data );
						toggle_display('step-2');
						
					},
					error: function(jqXHR, textStatus, errorThrown)  {
						alert('no');
					}          
				};
				
				jQuery(this).ajaxSubmit( options ); 
				
				return false; 
			});
			
			/* Add file name to readonly input */
			jQuery('.file-input').on('change', function() {				
				var label = jQuery(this).val().replace(/\\/g, '/').replace(/.*\//, '');
				jQuery('.file-name').val( label );
				
			});
		});
		

		
		</script>
		
		<?php	
	}
	
	public static function import_ui() {
		self::step_nav();
		self::step_1();
		self::step_2();
		self::inline_scripting();
	}
	
	public static function step_nav() {
		?>
		<br>
		<ul class="nav nav-tabs nav-justified" role="tablist">
			<li class="active" id='navtab-step-1'><a><?php _e( 'Step I: Upload File' , 'inbound-pro' ); ?></a></li>
			<li id='navtab-step-2'><a ><?php _e( 'Step II: Field Mapping' , 'inbound-pro' ); ?></a></li>
			<li id='navtab-step-3'><a ><?php _e( 'Step III: Importing' , 'inbound-pro' ); ?></a></li>
		</ul>
		<?php
	}
	
	public static function step_1() {		
		?>
		<div class='nav-container step-1 active'>
			<form class='csv-file-upload' method="post" enctype="multipart/form-data">
				<h4>Select CSV File</h4>
				<div class="input-group">
					<span class="input-group-btn">
						<span class="btn btn-primary btn-file">
							Browse&hellip; <input type="file" name="csv_file" class='file-input' required>
						</span>
					</span>
					<input type="text" class="form-control file-name" readonly>
				</div>		
				<h4>Select Delimiter</h4>
				<select name='csv_delimiter' class='form-control select-delimiter'>
					<option value='simicolon'><?php _e('simicolon' , 'inbound-pro' ); ?></option>
					<option value='tab'><?php _e('tab' , 'inbound-pro' ); ?></option>
					<option value='comma'><?php _e('comma' , 'inbound-pro' ); ?></option>
				</select>
				<div class='continue-button'>
					<button type="submit" class="btn btn-primary ladda-button next-1" data-style='expand-right'><?php _e( 'Next Step (upload CSV)' , 'inbound-pro' ); ?></button>
				</div>
			</form>
		</div>
		<?php
	}
	
	public static function step_2() {
		
		
		/* first let's build a hidden input for cloning */
		self::generate_field_map_select();
		?>
		<div class='nav-container step-2 active'>
			<form class='field-mapping-rules' method="post" >
				<h4>Map Columns</h4>
				<div id='map-container'>

				</div>
			</form>
		</div>
		<?php
	}
	
	/**
	*  Echos out dropdown select of mappable lead fields
	*/
	public static function generate_field_map_select() {
		$field_map = Leads_Field_Map::build_map_array();
		
		echo '<select id="leads_map">';
		foreach ( $field_map as $key => $value ) {
			echo '<option value="'.$key.'">'.$value.'</option>';
		}
		echo '</select>';
	}
	
	/**
	*  Ajax listener to parse csv file & return a json list of cols discovered in csv file
	*/
	public static function save_csv_file() {
		/**
		error_log(print_r($_POST , true));
		error_log(print_r($_FILES , true));
		error_log( print_r($csv_array , true) );
		/**/
		
		/* get delimiter */
		$delimiter = self::get_delimiter( $_POST['csv_delimiter'] );
		
		$csv_array = self::csv_to_array( $_FILES["csv_file"]["tmp_name"] , $delimiter );
		
		
		
		/* Prepare CSV data array for transient */
		$csv_data['delimiter'] = $_POST['csv_delimiter'];
		$csv_data['filename'] = $_FILES["csv_file"]["name"];
		$csv_data['rows'] = $csv_array;
		
		/* get cols & add to csv data array */
		$row = $csv_array[0];
		$csv_data['cols'] = array_keys( $row );
		
		/* Save transient for 2 hours */
		//$csv_encoded = json_encode($csv_data);
		set_transient( 'leads_temp_csv' , $csv_data , 60 * 60 * 2 );
		
		/* return col map for step 2 use */
		
		echo json_encode( $csv_data['cols'] );
		die();

	}
	
	public static function get_delimiter( $switch ) {
		
		switch ($switch) {
			case 'comma':
				return ',';
			case 'semicolon':
				return ';';
			case 'tab':
				return '	';
		}
		
		return ',';
	}
	
	public static function csv_to_array($filename='', $delimiter=',') {
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}
}

$GLOBALS['Leads_CSV_Processing'] = new Leads_CSV_Processing;