<?php


/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
function wpleads_get_global_settings() {
	// Setup navigation and display elements
	$tab_slug = 'wpl-main';
	$wpleads_global_settings[$tab_slug]['label'] = 'Global Settings';

	$wpleads_global_settings[$tab_slug]['settings'] =
	array(
		array(
			'id'  => 'tracking-ids',
			'label' => __('IDs or Classes of forms to track' , 'leads' ),
			'description' => __("<p>Enter in a value found in a HTML form's id or class attribute to track it as a conversion as comma separated values</p><p><strong>Example ID format:</strong> #Form_ID, #Form-ID-2<br>Example Class format:</strong> .Form_class, .form-class-2</p><p>Gravity Forms, Contact Form 7, and Ninja Forms are automatically tracked (no need to add their IDs in here).</p>" , 'leads' ),
			'type'  => 'text',
			'default'  => '',
			'options' => null
		),
		array(
			'id'  => 'exclude-tracking-ids',
			'label' => __('IDs or Classes of forms <u>NOT</u> to track' , 'leads' ),
			'description' => __("Enter in a value found in a HTML form's id attribute to turn off tracking." , 'leads' ),
			'type'  => 'text',
			'default'  => '',
			'options' => null
		),
		array(
			'id'  => 'page-view-tracking',
			'label' => __('Page View Tracking' , 'leads' ),
			'description' => __("WordPress Leads automatically tracks page views of converted leads. This is extremely valuable lead intelligence and will help with your sales follow ups. However with great power comes great resposibility, this extra tracking can cause problems on high high traffic sites. You can turn off tracking if you see any issues." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'search-tracking',
			'label' => __('Search Query Tracking' , 'leads' ),
			'description' => __("WordPress Leads records searches made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'comment-tracking',
			'label' => __('Comment Tracking' , 'leads' ),
			'description' => __("WordPress Leads records comments made by leads and appends them to their lead record. Disabling this will turn this feature off." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'enable-dashboard',
			'label' => __('Show Lead/List Data in Dashboard' , 'leads' ),
			'description' => __("Turn this on to show graphical and list data about lead collection in WP Dashboard." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'disable-widgets',
			'label' => __('Disable Default WordPress Dashboard Widgets' , 'leads' ),
			'description' => __("This turns off some default widgets on the wordpress dashboard." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'extra-lead-data',
			'label' => __('Full Contact API Key' , 'leads' ),
			'description' => sprintf( __("Enter your Full contact API key. If you don't have one. Grab a free one here: %s" , 'leads' ) , "<a href='https://www.fullcontact.com/developer/pricing/' target='_blank'>" , "</a>"),
			'type'  => 'text',
			'default'  => '',
			'options' => null
		),
		array(
			'id'  => 'inbound_compatibility_mode',
			'label' => __('Turn on compatibility mode' , 'leads' ),
			'description' => __("This option turns on compatibility mode for the Inbound Now plugins. This is typically used if you are experiencing bugs caused by third party plugin conflicts." , 'leads' ),
			'type'  => 'radio',
			'default'  => '0',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'inbound_email_replace_core_template',
			'option_name'  => 'inbound_email_replace_core_template',
			'label' => __('Replace WordPress Email Templates with Inbound Now Email Templates' , 'leads' ),
			'description' => __("This option replaces frequently used core WordPress email templates with Inbound Now templates that are editable within the Leads->Email Templates area. If your website is set to a a language besides English it may be best to turn this off until test strings have been translated for your language." , 'leads' ),
			'type'  => 'radio',
			'default'  => '1',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'inbound_forms_enable_akismet',
			'option_name'  => 'inbound_forms_enable_akismet',
			'label' => __('Run form submissions through Akismet if akismet is enabled.' , 'leads' ),
			'description' => __("Enabling this option will tell Leads to run form submissions through akismet to prevent spam submissions." , 'leads' ),
			'type'  => 'radio',
			'default'  => '0',
			'options' => array('1'=>'On','0'=>'Off')
		)
		/*
		,array(
			'id'  => 'inbound_lead_notification_reply',
			'option_name'  => 'inbound_lead_notification_reply',
			'label' => __('Lead notification "Reply To" email address' , 'leads' ),
			'description' => __( "You can set the 'new lead' notification email's reply-to address to be a dummy no-reply email address or the lead's email address. The latter sometimes experiences spam-box issues so we've defaulted the reply-to email to be a dummy one: wordpress@yourdomain.com." , 'leads' ),
			'type'  => 'dropdown',
			'default'  => '0',
			'options' => array( 'noreply' => __( 'Generated Noreply Email' , 'leads' ) , 'lead'=> __( 'Use the Lead\'s Email Address' , 'leads' ) )
		)
		*/
	);

	/* Setup License Keys Tab */
	$tab_slug = 'wpleads-license-keys';
	$wpleads_global_settings[$tab_slug]['label'] = __('License Keys' , 'leads' );

	/* Setup Extensions Tab */
	$tab_slug = 'wpleads-extensions';
	$wpleads_global_settings[$tab_slug]['label'] = __('Extensions' , 'leads' );



	$wpleads_global_settings = apply_filters('wpleads_define_global_settings', $wpleads_global_settings);

	/* Setup API Keys Tab */
	if (current_user_can('activate_plugins')) {
		$tab_slug = 'wpleads-apikeys';
		$wpleads_global_settings[$tab_slug]['label'] = __('API Keys' , 'leads' );

		$wpleads_global_settings[$tab_slug]['settings'] = array(
			array(
				'id'  => 'api-keys-table',
				'label' => __('API Keys Table' , 'leads' ),
				'type'  => 'api-keys-table'
			)
		);
	}

	return $wpleads_global_settings;
}

/* Add Extensions License Key Header if Extensions are present */
add_filter('wpleads_define_global_settings', 'wpleads_add_extension_license_key_header',1,1);
function wpleads_add_extension_license_key_header($wpleads_global_settings) {
	//print_r($wpleads_global_settings);exit;
	foreach ($wpleads_global_settings as $parent_tab => $aa) {
		if (is_array($aa)) {

			foreach ($aa as $k=>$aaa) {
				/* change 'options' key to 'settings' */
				if ($k=='options') {
					if (is_array($aaa)) {
						foreach ($aaa as $kk => $aaaa) {
							$wpleads_global_settings[$parent_tab]['settings'][] = $aaaa;
						}
					}
					unset($wpleads_global_settings[$parent_tab][$k]);
				}

			}
		}
	}

	return $wpleads_global_settings;
}

function wpleads_render_global_settings($key,$custom_fields,$active_tab) {

	/* Check if active tab */
	if ($key==$active_tab) {
		$display = 'block';
	} else {
		$display = 'none';
	}

	/* add extra styling for the api tab */
	if ( $key == 'wpleads-apikeys' ) {
		$styling = 'padding:0px;';
	} else {
		$styling = '';
	}

	/* Use nonce for verification */
	echo "<input type='hidden' name='wpl_{$key}_custom_fields_nonce' value='".wp_create_nonce('wpl-nonce')."' />";

	/* Begin the field table and loop */
	echo '<table class="wpl-tab-display" id="'.$key.'" style="display:'.$display.'; ' . $styling .'">';

	foreach ($custom_fields as $field) {
		/* get value of this field if it exists for this post */
		(isset($field['default'])) ? $default = $field['default'] : $default = null;

		$field['id'] = $key.'-'.$field['id'];

		if (array_key_exists('option_name',$field) && $field['option_name'] ){
			$field['id'] = $field['option_name'];
		}

		$field['value'] = get_option($field['id'], $default);

		/* Handle the API Keys List Table separately */
		if ($field['type'] == 'api-keys-table') {
			echo '</form><tr><td>';
			$api_keys_table = new Inbound_API_Keys_Table();
			$api_keys_table->prepare_items();
			$api_keys_table->display();
			echo '</td></tr>';
			continue;
		}

		echo '<tr><th class="wpl-gs-th" valign="top" style="font-weight:300;">';
		if ($field['type']=='header'){
			echo $field['default'];
		} else {
			echo "<div class='inbound-setting-label'>".$field['label']."</div>";
		}
		echo '</th><td>';
				switch($field['type']) {
					// text
					case 'colorpicker':
						if (!$field['value'])
						{
							$field['value'] = $field['default'];
						}
						echo '<input type="text" class="jpicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="5" />
								<div class="wpl_tooltip tool_color" title="'. $field['description'] .'"></div>';
						break;
					case 'datepicker':
						echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="8" />
								<div class="wpl_tooltip tool_date" title="'. $field['description'] .'"></div><p class="description">'. $field['description'] .'</p>';
						break;
					case 'text':
						echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />
								<div class="wpl_tooltip tool_text"  title="'. $field['description'] .'"></div>';
						break;
					// textarea
					case 'textarea':
						echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6">'.$field['value'].'</textarea>
								<div class="wpl_tooltip tool_textarea" title="'. $field['description'] .'"></div>';
						break;
					// wysiwyg
					case 'wysiwyg':
						wp_editor( $field['value'], $field['id'], $settings = array() );
						echo	'<span class="description">'. $field['description'] .'</span><br><br>';
						break;
					// media
						case 'media':
						//echo 1; exit;
						echo '<label for="upload_image">';
						echo '<input name="'.$field['id'].'"  id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
						echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
						echo '<br /><div class="wpl_tooltip tool_media" title="'. $field['description'] .'"></div>';
						break;
					// checkbox
					case 'checkbox':
						$i = 1;
						echo "<table>";
						if (!isset($field['value'])){$field['value']=array();}
						elseif (!is_array($field['value'])){
							$field['value'] = array($field['value']);
						}
						foreach ($field['options'] as $value=>$label) {
							if ($i==5||$i==1) {
								echo "<tr>";
								$i=1;
							}
								echo '<td><input type="checkbox" name="'.$field['id'].'[]" id="'.$field['id'].'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','/>';
								echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
							if ($i==4) {
								echo "</tr>";
							}
							$i++;
						}
						echo "</table>";
						echo '<div class="wpl_tooltip tool_checkbox" title="'. $field['description'] .'"></div><p class="description">'. $field['description'] .'</p>';
					break;
					// radio
					case 'radio':
						foreach ($field['options'] as $value=>$label) {
							//echo $meta.":".$field['id'];
							//echo "<br>";
							echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
							echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
						}
						echo '<div class="wpl_tooltip tool_radio" title="'. $field['description'] .'"></div>';
					break;
					// select
					case 'dropdown':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
						foreach ($field['options'] as $value=>$label) {
							echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
						}
						echo '</select><div class="wpl_tooltip tool_dropdown" title="'. $field['description'] .'"></div>';
					break;
					case 'html':
						echo $field['value'];
						echo '<div class="wpl_tooltip tool_dropdown" title="'. $field['description'] .'"></div>';
					break;


				} //end switch

				do_action('wpleads_render_global_settings',$field);
		echo '</td></tr>';
	} // end foreach
	echo '</table>'; // end table
}

function wpleads_display_global_settings_js() {
	global $wpleads_global_settings;


	if (isset($_GET['tab'])) {
		$default_id = $_GET['tab'];
	} else {
		$default_id ='wpl-main';
	}
	?>

	<script type='text/javascript'>
	/* Hide sidebar when API Keys Tab is opened */
	jQuery(document).ready( function($) {

		jQuery('body').on( 'click' , '.wpl-nav-tab' , function() {

			if ( this.id == 'tabs-wpleads-apikeys' ) {
				jQuery('.lp-settings-tab-sidebar').hide();
				jQuery('#wpl-button-create-new-group-open').hide();
			} else {
				jQuery('.lp-settings-tab-sidebar').show();
				jQuery('#wpl-button-create-new-group-open').show();
			}
		});

		<?php
		if ( isset($_GET['tab']) && $_GET['tab'] == 'tabs-wpleads-apikeys' ) {
			echo "jQuery('.lp-settings-tab-sidebar').hide();";
			echo "jQuery('#wpl-button-create-new-group-open').hide();";
		}
		?>

	});

	</script>

	<?php

}

function wpleads_display_global_settings() {
	global $wpdb;
	$wpleads_global_settings = wpleads_get_global_settings();

	$active_tab = 'wpl-main';
	if (isset($_REQUEST['open-tab'])) {
		$active_tab = $_REQUEST['open-tab'];
	}


	wpleads_save_global_settings();

	echo '<h2 class="nav-tab-wrapper">';

	foreach ($wpleads_global_settings as $key => $data) {
		?>
		<a  id='tabs-<?php echo $key; ?>' class="wpl-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php _e( $data['label'] , 'leads' ); ?></a>
		<?php
	}
	echo "</h2><div class='lp-settings-tab-sidebar'>";

	echo "<div class='lp-sidebar-settings'><h2 style='font-size:16px;'>Like the Plugin? Leave us a review</h2><center><a class='review-button' href='http://wordpress.org/support/view/plugin-reviews/leads?rate=5#postform' target='_blank'>Leave a Quick Review</a></center><small>Reviews help constantly improve the plugin & keep us motivated! <strong>Thank you for your support!</strong></small></div><div class='lp-sidebar-settings'><h2>Help keep the plugin up to date, awesome & free!</h2><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
		<input type='hidden' name='cmd' value='_s-xclick'>
		<input type='hidden' name='hosted_button_id' value='GKQ2BR3RKB3YQ'>
		<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
		<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'></form>
		<small>Spare some change? Buy us a coffee/beer.<strong> We appreciate your continued support.</strong></small></div><div class='lp-sidebar-settings'><h2 style='font-size:18px;'>Follow Updates on Facebook</h2><iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'></iframe></div></div>";
	echo "<form action='edit.php?post_type=wp-lead&page=wpleads_global_settings' method='POST'>";
	echo "<input type='hidden' name='nature' value='wpl-global-settings-save'>";
	echo "<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";

	foreach ($wpleads_global_settings as $key => $array) {
		if (!array_key_exists('settings',$array)){
			continue;
		}

		$these_settings = $wpleads_global_settings[$key]['settings'];
		wpleads_render_global_settings($key, $these_settings , $active_tab);
	}
	echo '<div style="float:left;padding-left:9px;padding-top:20px;">
			<input type="submit" value="Save Settings" tabindex="5" id="wpl-button-create-new-group-open" class="button-primary" >
		</div>';
	echo "</form>";

	wpleads_display_global_settings_js();
}

function wpleads_save_global_settings() {
	//echo "here";exit;
	$wpleads_global_settings = wpleads_get_global_settings();

	if (!isset($_POST['nature'])) {
		return;
	}

	foreach ($wpleads_global_settings as $key=>$array) {

		if (!isset($wpleads_global_settings[$key]['settings']) || !$wpleads_global_settings[$key]['settings'] ) {
			continue;
		}

		/* loop through fields and save the data */
		foreach ($wpleads_global_settings[$key]['settings'] as $field) {
			//echo $field['id'].":".$_POST['main-landing-page-auto-format-forms']."<br>";
			$field['id'] = $key.'-'.$field['id'];

			if (array_key_exists('option_name',$field) && $field['option_name'] ) {
				$field['id'] = $field['option_name'];
			}

			if ( !isset($_POST[$field['id']]) ) {
				continue;
			}

			$field['old_value'] = get_option($field['id']);
			$field['new_value'] = $_POST[$field['id']];

			if ((isset($field['new_value']) && $field['new_value'] !== $field['old_value'] )|| !isset($field['old_value']) ) {
				//echo $field['id'];exit;
				$bool = update_option($field['id'],$field['new_value']);

				if ($field['type']=='license-key') {

					// data to send in our API request
					$api_params = array(
						'edd_action'=> 'activate_license',
						'license' 	=> $field['new_value'],
						'item_name' =>  $field['slug'] // the name of our product in EDD
					);
					//print_r($api_params);

					// Call the custom API.
					$response = wp_remote_get( add_query_arg( $api_params, WPWPL_STORE_URL ), array( 'timeout' => 30, 'sslverify' => false ) );
					//echo $response['body'];exit;

					// make sure the response came back okay
					if ( is_wp_error( $response ) ) {
						break;
					}

					// decode the license data
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );


					// $license_data->license will be either "active" or "inactive"
					$license_status = update_option('wpleads_license_status-'.$field['slug'], $license_data->license);

					//echo 'lp_license_status-'.$field['slug']." :".$license_data->license;exit;
				}
			} elseif ('' == $field['new_value'] && $field['old_value']) {

				if ($field['type']=='license-key') {

					$master_key = get_option('inboundnow_master_license_key' , '');

					if ($master_key) {
						$bool = update_option($field['id'], $master_key );
					} else {
						update_option($field['id'], '' );
					}

				} else {
					$bool = update_option($field['id'],$field['default']);
				}
			}

			do_action('wpleads_save_global_settings',$field);

		} // end foreach

	}

}