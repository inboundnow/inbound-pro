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
		add_action( 'wp_ajax_save_leads_csv_file',	array( __CLASS__, 'save_csv_file') );
		
		/* Add ajax listeners for temporarily storing mapping data	*/
		add_action( 'wp_ajax_nopriv_save_leads_mapping_rules', array( __CLASS__, 'save_mapping_rules') );
		add_action( 'wp_ajax_save_leads_mapping_rules',	array( __CLASS__, 'save_mapping_rules') );
		
		/* Add ajax listeners for processing lead batches	*/
		add_action( 'wp_ajax_nopriv_process_lead_batches', array( __CLASS__, 'process_lead_batches') );
		add_action( 'wp_ajax_process_lead_batches',	array( __CLASS__, 'process_lead_batches') );
	}

	/**
	*	Adds menu item under 'Leads'
	*/
	public static function add_sub_menus()
	{
		if (current_user_can('manage_options')) {

			add_submenu_page('edit.php?post_type=wp-lead', __( 'Import Leads' , 'inbound-pro' ), __( 'Import Leads' , 'cta' ) , 'manage_options', 'leads-import', array( __CLASS__ , 'import_ui' )) ;

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
		
		/* Generated HTML inputs for mapping */
		function build_map_input( col_name ) {

			var select = jQuery("#leads_map").clone();

			/* alter dropdown attributes */
			select.attr( 'id' , col_name );			
			select.attr( 'style' , '' );			
			select.attr( 'name' , col_name );	
			
			/* Now get HTML of dropdown */
			var select_html = select.prop('outerHTML');;
			
			/* build html */
			var html = jQuery('<div/>', { class: "row" });
			jQuery('<div/>', { class: "col-md-3", text: col_name }).appendTo(html);
			jQuery('<div/>', { class: "col-md-3", html: select_html }).appendTo(html);
			
			/* append html to map container */
			jQuery('#map-container').append(html);
			
			/* Attempt to preselect mapping params if importing a leads csv file */
			jQuery('#' + col_name + ' option').each(function() {
				if( jQuery(this).val() == col_name ) {
					jQuery(this).prop('selected' , 'selected');
				}
			});
			

		}
		
		/* Function to generate col to lead field map in step 2 */
		function build_map( col_json ) {
			var obj = jQuery.parseJSON( col_json );
		
			for(var i=0;i<obj.length;i++){
				build_map_input( obj[i] );
			}
			
		}
		
		/* Process batches and increment progress bar - ajax prep */
		function import_leads( json ) {
			var obj = jQuery.parseJSON( json );

			var batches = parseInt(obj['batches']);
			var total_leads = parseInt(obj['lead_count']);
			var batches_complete = 0;

			process_batch( 0 , batches );		
			update_progress(5);		
		}
		
		/* Process batches and increment progress bar - ajax execute */
		function process_batch( batch , cap ) {
			
			if (batch >= cap) {
				jQuery('#progressBar').text('Done!');
				return;
			}
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				async: true,
				data: {
					'action' : 'process_lead_batches' ,
					'batch' : batch
				},
				success: function(data) {
					batch++;					
					process_batch( batch , cap );
					update_progress((batch/cap)*100);
				}
			});
		}
		
		/* Alter progress bar */
		function update_progress( percentage ) {
			if(percentage > 100) percentage = 100;
			jQuery('#progressBar').css('width', percentage+'%');
			jQuery('#progressBar').html( Math.round(percentage)+'%');
		}
		
		/* function for switching visible containers */
		function toggle_display( step ) {
			jQuery('.nav-tabs li').removeClass('active');
			
			if (step == 'step-2') {
				jQuery('#navtab-step-2').addClass('active');
				jQuery('.step-1').hide();
				jQuery('.step-2').show();
			}
			if (step == 'step-3') {
				jQuery('#navtab-step-3').addClass('active');
				jQuery('.step-2').hide();
				jQuery('.step-3').show();
			}
		}
		
		jQuery( document ).ready( function() {
			
			/* Enable ladda button handlers */
			var ladda_1 = Ladda.create(document.querySelector( '.next-1' ));
			var ladda_2 = Ladda.create(document.querySelector( '.next-2' ));
			
			/* Enable ajaxForm on our uploader */
			jQuery('.csv-file-upload').ajaxForm();
			jQuery('.field-mapping-rules').ajaxForm();
			
			/* Run CSV upload events */
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
						build_map( data );
						toggle_display('step-2');
						
					},
					error: function(jqXHR, textStatus, errorThrown)	{
						//alert(jqXHR);
						alert(textStatus);
						//alert(errorThrown);
						ladda_1.toggle();
					}			
				};
				
				jQuery(this).ajaxSubmit( options ); 
				
				return false; 
			});
			
			/* Run Field Mapping events */
			jQuery('.field-mapping-rules').submit(function() { 
				ladda_2.toggle();
				
				var options = { 					
					url:	ajaxurl,
					type:	'POST',
					data: {
						'action' : 'save_leads_mapping_rules'
					},
					success: function(data, textStatus, jqXHR) {
						ladda_2.toggle();	
						toggle_display('step-3');
						import_leads(data);						
					},
					error: function(jqXHR, textStatus, errorThrown)	{
						alert(jqXHR);
						alert(textStatus);
						alert(errorThrown);
						ladda_2.toggle();	
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
		self::step_3();
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
				<h4><?php _e( 'Select CSV File' , 'inbound-pro' ); ?></h4>
				<div class="input-group">
					<span class="input-group-btn">
						<span class="btn btn-primary btn-file">
							<?php _e( 'Browse' , 'inbound-pro' ); ?>&hellip; <input type="file" name="csv_file" class='file-input' required>
						</span>
					</span>
					<input type="text" class="form-control file-name" readonly>
				</div>		
				<br>
				<h4><?php _e( 'Select Delimiter' , 'inbound-pro' ); ?></h4>
				<select name='csv_delimiter' class='form-control select-delimiter'>					
					<option value='comma'><?php _e('comma' , 'inbound-pro' ); ?></option>
					<option value='simicolon'><?php _e('simicolon' , 'inbound-pro' ); ?></option>
					<option value='tab'><?php _e('tab' , 'inbound-pro' ); ?></option>
				</select>
				<br>
				
				<div class="btn-group" data-toggle="buttons">
					
					
					<?php
					$lists = wpleads_get_lead_lists_as_array();
					if (is_array($lists) && $lists) {
					
						echo '<h4>';
						echo __( 'Sort into these lists' , 'inbound-pro' ); 
						echo '</h4>';
						foreach ( $lists as $id => $label	)
						{
							echo '	<label class="btn btn-default">';
							echo '		<input name="lead_lists[]" type="checkbox" value="' . $id . '"> ' . $label ;
							echo '	</label>';
							
						}
					}

					?>
				</div>
				<br>
				<div class='continue-button'>
					<button type="submit" class="btn btn-primary ladda-button next-1" data-style='expand-right'><?php _e( 'Next Step (upload CSV)' , 'inbound-pro' ); ?></button>
				</div>
			</form>
			<br>
		</div>
		<?php
	}
	
	public static function step_2() {
		
		/* first let's build a hidden input for cloning */
		self::generate_field_map_select();
		
		?>
		<div class='nav-container step-2 inactive'>			
			<form class='field-mapping-rules' method="post" >
				<div class='process-button'>
					<button type="submit" class="btn btn-primary ladda-button next-2" data-style='expand-right'><?php _e( 'Next Step (Start Importing)' , 'inbound-pro' ); ?></button>
				</div>
				<h4>Map Columns</h4>
				<div id='map-container' class='container-fluid'>

				</div>
				
			</form>
			<br>
			<br>
		</div>
		<?php
	}
	
	public static function step_3() {

		?>
		<div class='nav-container step-3 inactive'>
			
			<h4><?php _e('Progress' , 'inbound-pro'); ?> <span id='remaining'></span></h4>
			<i><?php _e('Do not close browser until process has reached 100%.' , 'inbound-pro'); ?></i>
			<div class="progress" style="height:38px;">
				<div id="progressBar" class="progress-bar progress-bar-info progress-bar-striped active" role='progressbar' style="height:38px;padding-top:8px;font-size:21px;"></div>
			</div>
			<br>
	
		</div>
		<?php
	}
	
	/**
	*	Echos out dropdown select of mappable lead fields
	*/
	public static function generate_field_map_select() {
		$field_map = Leads_Field_Map::build_map_array();
		
		/* Add some more */
		$field_map['wpleads_full_name'] = 'Full Name';
		$field_map['wp_leads_uid'] = 'Inbound Lead UID';
		$field_map['wpleads_latitude'] = 'Latitude';
		$field_map['wpleads_longitude'] = 'Longitude';
		$field_map['wpleads_currency_code'] = 'Currency Code';
		$field_map['wpleads_currency_symbol'] = 'Currency Symbol';
		$field_map['wp_lead_status'] = 'wp_lead_status';
		$field_map['ip_address'] = 'IP Address';
		
		$el1 = array_shift($field_map);
		asort($field_map);
		array_unshift( $field_map , $el1 );
		
		echo '<select id="leads_map" style="display:none;">';
		foreach ( $field_map as $key => $value ) {
			echo '<option value="'.$key.'">'.$value.'</option>';
		}
		echo '</select>';
	}
	
	/**
	*	Ajax listener to parse csv file & return a json list of cols discovered in csv file
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
		$csv_data['lead_lists'] = (isset($_POST['lead_lists'])) ? $_POST['lead_lists'] : array();
		$csv_data['filename'] = $_FILES["csv_file"]["name"];
		$csv_data['rows'] = $csv_array;
		
		/* get cols & add to csv data array */
		$row = $csv_array[0];
		$csv_data['cols'] = array_keys( $row );
		
		/* Save transient for 2 hours */
		$result = set_transient( 'leads_temp_csv' , json_encode($csv_data) , 60 * 60 * 2 );

		/* return col map for step 2 use */		
		echo json_encode( $csv_data['cols'] );
		die();

	}
	
	/**
	*	Ajax listener to save mapping rules
	*/
	public static function save_mapping_rules() {

		$csv_data = json_decode( get_transient( 'leads_temp_csv') , true );

		$csv_data['map_rules'] = array_filter($_POST);
		$csv_data['total_leads_count'] = count($csv_data['rows']);
		$csv_data['rows'] = array_chunk( $csv_data['rows'] , 50 , true );
		
		set_transient( 'leads_temp_csv' , json_encode( $csv_data ) , 60 * 60 * 2 );
		
		$import_data['batches'] =	count( $csv_data['rows'] );
		$import_data['lead_count'] = $csv_data['total_leads_count'];
		
		echo json_encode( $import_data );
		die();

	}
	
	/**
	*	Ajax listener to process lead batches
	*/
	public static function process_lead_batches() {

		($_POST['batch']>0) ? $target_batch = $_POST['batch'] : $target_batch = 0;
		
		$csv_data = json_decode( get_transient( 'leads_temp_csv') , true );	
		
		$this_batch = $csv_data['rows'][$target_batch];
 
		$map_rules = $csv_data['map_rules'];

		/* Adds leads to database */
		foreach ($this_batch as $key => $row) {
			/* replace column keys with mapped lead keys */
			$lead = self::replace_keys( $map_rules , $row );
			
			/* Add list options */
			$lead['lead_lists'] = $csv_data['lead_lists'];
			
			inbound_store_lead( $lead );
			//error_log( print_r( $lead , true ) );
			//exit;			
		}
		die();
	}
	
	/**
	*	Replaces row array column names with lead map keys
	*	
	*	@param ARRAY $map_rules contains array of lead mapping rules with format 'column_name' => 'lead_map_key' 
	*	@param ARRAY $row contains array of row values with format 'column_name' => 'column_value'
	*	
	*	@returns ARRAY $lead that replaces 'column_name' key with 'lead_map_key' to prepare row for lead insertion
	*/
	public static function replace_keys( $map_rules , $row ) {
		
		foreach ($row as $column_key => $column_value) {
			
			$column_key = str_replace(' ' , '_' , $column_key );

			if (!isset($map_rules[$column_key])) {
				continue;
			}
			$lead[$map_rules[$column_key]] = $column_value;
		}

		return $lead;
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