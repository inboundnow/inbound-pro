<?php

if (is_admin())
{
	//define main tabs and bind display functions
	if (isset($_GET['page'])&&($_GET['page']=='wp_cta_global_settings'&&$_GET['page']=='wp_cta_global_settings'))
	{
		add_action('admin_init','wp_cta_global_settings_enqueue');
		function wp_cta_global_settings_enqueue()
		{
			wp_enqueue_style('wp-cta-css-global-settings-here', WP_CTA_URLPATH . 'css/admin-global-settings.css');
		}
	}


	function wp_cta_get_global_settings()
	{
		global $wp_cta_global_settings;

		// Setup navigation and display elements

		$tab_slug = 'wp-cta-main';
		$wp_cta_global_settings[$tab_slug]['label'] = 'Global Settings';


		$wp_cta_global_settings[$tab_slug]['settings'] =
		array(
			array(
				'id'  => 'cta-global-settings-main-header',
				'type'  => 'header',
				'default'  => __( '<h4>CTA Core Settings</h4>' , 'cta' ),
				'description' => "<a id='clear-cta-cookies' class='button'>".__( 'Clear & Reset all Call to Action Cookies' , 'cta' ) ."</a><div class='wp_cta_tooltip tool_radio' title='". __( 'This will reset all CTA cookies to make popups work again etc. For testing purposes.' , 'cta' ) ."'></div>",
				'options' => null
			),			
			array(
				'id'  => 'disable-ajax-variation-discovery',
				'label' => __( 'Disable Split Testing.' , 'cta' ),
				'description' => __( 'Enabling this setting may improve server performance at the loss of split testing. Only version A will be displayed for every CTA.' , 'cta' ),
				'type'  => 'radio',
				'default'  => '0',
				'options' => array( 0 => 'Off' , 1 => 'On' )
			),			
			array(
				'id'  => 'global-css',
				'label' => __( 'Global CSS overrides.' , 'cta' ),
				'description' => __( 'This will place custom CSS across all Call to Actions' , 'cta' ),
				'type'  => 'textarea',
				'default'  => '',
				'options' => null
			),
		);

		/* Setup License Keys Tab */
		$tab_slug = 'wp-cta-license-keys';
		$wp_cta_global_settings[$tab_slug]['label'] = __( 'License Keys' , 'cta' );


		/* Setup Extensions Tab */
		$tab_slug = 'wp-cta-extensions';
		$wp_cta_global_settings[$tab_slug]['label'] = __( 'Extensions' , 'cta' );

		$wp_cta_global_settings = apply_filters('wp_cta_define_global_settings',$wp_cta_global_settings);

		return $wp_cta_global_settings;
	}

	function wp_cta_display_global_settings_js()
	{
		if (isset($_GET['tab']))
		{
			$default_id = $_GET['tab'];
		}
		else
		{
			$default_id ='wp-cta-main';
		}
		?>
		<script type='text/javascript'>
			jQuery(document).ready(function($) {
				//jQuery('#<? echo $default_id; ?>').css('display','block');
				//jQuery('#<? echo $default_id; ?>').css('display','block');
				 setTimeout(function() {
	     			var getoption = document.URL.split('&option=')[1];
					var showoption = "#" + getoption;
					jQuery(showoption).click();
    			}, 100);
				var getCookieByMatch = function(regex) {
				  var cs=document.cookie.split(/;\s*/), ret=[], i;
				  for (i=0; i<cs.length; i++) {
				    if (cs[i].match(regex)) {
				      ret.push(cs[i]);
				    }
				  }
				  return ret;
				};

				jQuery("body").on('click', '#clear-cta-cookies', function () {
				 	$.removeCookie('wp_cta_global', { path: '/' }); // remove global cookie
				 	var cta_cookies = getCookieByMatch(/^wp_cta_\d+=/);
				 	var length = cta_cookies.length,
				 	    element = null;
				 	for (var i = 0; i < length; i++) {
				 	  element = cta_cookies[i];
				 	  cookie_name = element.split(/=/);
				 	  cookie_name = cookie_name[0];
				 	  $.removeCookie( cookie_name, { path: '/' }); // remove each id cookie
				 	}

				});
				jQuery('.wp-cta-nav-tab').live('click', function() {
					var this_id = this.id.replace('tabs-','');
					//alert(this_id);
					jQuery('.wp-cta-tab-display').css('display','none');
					jQuery('#'+this_id).css('display','block');
					jQuery('.wp-cta-nav-tab').removeClass('nav-tab-special-active');
					jQuery('.wp-cta-nav-tab').addClass('nav-tab-special-inactive');
					jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');
					jQuery('#id-open-tab').val(this_id);
				});

			});
		</script>
		<?php
	}

	function wp_cta_display_global_settings()
	{
		global $wpdb;
		$wp_cta_global_settings = wp_cta_get_global_settings();

		//print_r($wp_cta_global_settings);
		$active_tab = 'wp-cta-main';
		if (isset($_REQUEST['open-tab']))
		{
			$active_tab = $_REQUEST['open-tab'];
		}

		//echo $active_tab;exit;

		wp_cta_display_global_settings_js();
		wp_cta_save_global_settings();

		echo '<h2 class="nav-tab-wrapper">';

		foreach ($wp_cta_global_settings as $key => $data)
		{
			?>
			<a  id='tabs-<?php echo $key; ?>' class="wp-cta-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $data['label']; ?></a>
			<?php
		}
		echo "</h2><div class='wp-cta-settings-tab-sidebar'><div class='wp-cta-sidebar-settings'><h2 style='font-size:17px;'>". __( 'Like the Plugin? Leave us a review' , 'cta' ) ."</h2><center><a class='review-button' href='http://wordpress.org/support/view/plugin-reviews/cta?rate=5#postform' target='_blank'>". __( 'Leave a Review' , 'cta' ) ."</a></center><small>". __( 'Reviews help constantly improve the plugin & keep us motivated! <strong>Thank you for your support!</strong>' , 'cta' )."</small></div><div class='wp-cta-sidebar-settings'><h2>". __( 'Help keep the plugin up to date, awesome & free!' , 'cta' ) ."</h2><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
			<input type='hidden' name='cmd' value='_s-xclick'>
			<input type='hidden' name='hosted_button_id' value='GKQ2BR3RKB3YQ'>
			<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
			<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'></form>
			<small>". __( 'Spare some change? Buy us a coffee/beer.<strong> We appreciate your continued support.</strong>' , 'cta' ) >"</small></div><div class='wp-cta-sidebar-settings'><h2 style='font-size:18px;'>Follow Updates on Facebook</h2><iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'></iframe></div></div>";
		echo "<form action='edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings' method='POST'>
		<input type='hidden' name='nature' value='wp-cta-global-settings-save'>
		<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";

		if ($wp_cta_global_settings)
		{
			foreach ($wp_cta_global_settings as $key => $data)
			{
				if (isset($data['settings'])) {
				wp_cta_render_global_settings($key,$data['settings'], $active_tab);
				}
			}
		}

		echo '<div style="float:left;padding-left:9px;padding-top:20px;">
				<input type="submit" value="Save Settings" tabindex="5" id="wp-cta-button-create-new-group-open" class="button-primary" >
			</div>';
		echo "</form>";
		?>
		<!--
		<div id="wp-cta-additional-resources" class="clear">
			<hr>
		<div id="more-templates">
			<center>
			<a href="http://www.inboundnow.com/landing-pages/downloads/category/templates/" target="_blank"><img src="<?php echo WP_CTA_URLPATH;?>/images/templates-image.png"></a>

			</center>
		</div>
		<div id="more-addons">
			<center>
			<a href="http://www.inboundnow.com/landing-pages/downloads/category/add-ons/" target="_blank"><img src="<?php echo WP_CTA_URLPATH;?>/images/add-on-image.png"></a>
		</center>
		</div>
		<div id="custom-templates">
			<center><a href="http://www.inboundnow.com/landing-pages/custom-wordpress-landing-page-setup/" target=="_blank"><img src="<?php echo WP_CTA_URLPATH;?>/images/custom-setup-image.png"></a>
			</center>
		</div>
		</div>-->
		<div class="clear" id="php-sql-wp-cta-version">
		 <h3><?php _e( 'Installation Status' , 'cta' ); ?></h3>
              <table class="form-table" id="wp-cta-wordpress-site-status">

                <tr valign="top">
                   <th scope="row"><label><?php _e( 'PHP Version' , 'cta' ); ?></label></th>
                    <td class="installation_item_cell">
                        <strong><?php echo phpversion(); ?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare(phpversion(), '5.0.0', '>')){
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/cross.png"/>
                                <span class="installation_item_message"><?php _e( "Gravity Forms requires PHP 5 or above." , "cta"); ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                   <th scope="row"><label>MySQL Version</label></th>
                    <td class="installation_item_cell">
                        <strong><?php echo $wpdb->db_version();?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare($wpdb->db_version(), '5.0.0', '>')){
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/cross.png"/>
                                <span class="installation_item_message"><?php _e( "Gravity Forms requires MySQL 5 or above." , "cta"); ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                   <th scope="row"><label>WordPress Version</label></th>
                    <td class="installation_item_cell">
                        <strong><?php echo get_bloginfo("version"); ?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare(get_bloginfo("version"), '3.3', '>')){
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo WP_CTA_URLPATH;?>/images/cross.png"/>
                                <span class="installation_item_message"><?php _e( 'landing pages requires version X or higher' , 'cta' ) ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                 <tr valign="top">
                   <th scope="row"><label><?php _e( 'WordPress CTA Version' , 'cta' ); ?></label></th>
                    <td class="installation_item_cell">
                        <strong>Version <?php echo WP_CTA_CURRENT_VERSION;?></strong>
                    </td>
                    <td>

                    </td>
                </tr>
            </table>
        </div>
	<?php
	}

	function wp_cta_save_global_settings()
	{

		$wp_cta_global_settings = wp_cta_get_global_settings();

		if (!isset($_POST['nature']))
			return;


		foreach ($wp_cta_global_settings as $key=>$data)
		{
			if (isset($wp_cta_global_settings[$key]['settings'])) {
			$tab_settings = $wp_cta_global_settings[$key]['settings'];

			// loop through fields and save the data
			foreach ($tab_settings as $field)
			{

				$field['id'] = $key."-".$field['id'];

				if (array_key_exists('option_name',$field) && $field['option_name'] )
					$field['id'] = $field['option_name'];

				$field['old_value'] = get_option($field['id']);
				(isset($_POST[$field['id']]))? $new = $_POST[$field['id']] : $new = null;


				if ((isset($new) && ($new !== $field['old_value'] ) )|| !isset($field['old_value']) )
				{
					//echo $field['id'];exit;
					$bool = update_option($field['id'],$new);

					if ($field['type']=='license-key')
					{
						// retrieve the license from the database
						$license = trim( get_option( 'edd_sample_license_key' ) );

						// data to send in our API request
						$api_params = array(
							'edd_action'=> 'activate_license',
							'license' 	=> $new,
							'item_name' =>  $field['slug'] // the name of our product in EDD
						);

						// Call the custom API.
						$response = wp_remote_get( add_query_arg( $api_params, WP_CTA_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

						// make sure the response came back okay
						if ( is_wp_error( $response ) )
							break;

						// decode the license data
						$license_data = json_decode( wp_remote_retrieve_body( $response ) );

						//echo $license_data->license;
						//echo $field['value']['slug'];exit;

						// $license_data->license will be either "active" or "inactive"
						$license_status = update_option('wp_cta_license_status-'.$field['slug'], $license_data->license);
					}
				}
				elseif (!$new && $field['old_value'])
				{
					if ($field['type']=='license-key')
					{
						$master_key = get_option('inboundnow_master_license_key' , '');
						if ($master_key) 
						{
							$bool = update_option($field['id'], $master_key );
						}
						else
						{
							update_option($field['id'], '' );
						}	
					}
					else
					{
						$bool = update_option($field['id'],$field['default']);
					}	
				}
				else
				{
					//print_r($field);
					if ($field['type']=='license-key'&& $new )
					{

						$license_status = get_option('wp_cta_license_status-'.$field['slug']);

						if ($license_status=='valid' && $new == $field['old_value'])
						{
							continue;
						}

						// retrieve the license from the database
						$license = trim( get_option( 'edd_sample_license_key' ) );

						// data to send in our API request
						$api_params = array(
							'edd_action'=> 'activate_license',
							'license' 	=> $new,
							'item_name' =>  $field['slug'] // the name of our product in EDD
						);

						// Call the custom API.
						$response = wp_remote_get( add_query_arg( $api_params, WP_CTA_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
						//print_r($response);
						//echo "<br>";

						// make sure the response came back okay
						if ( is_wp_error( $response ) )
							break;

						// decode the license data
						$license_data = json_decode( wp_remote_retrieve_body( $response ) );

						// $license_data->license will be either "active" or "inactive"
						$license_status = update_option('wp_cta_license_status-'.$field['slug'], $license_data->license);
					}
				}
				//exit;
				do_action('wp_cta_save_global_settings',$field);
			} // end foreach

		}
		//exit;
		}
	}


	function wp_cta_render_global_settings($key,$custom_fields,$active_tab)
	{

		//Check if active tab
		if ($key==$active_tab)
		{
			$display = 'block';
		}
		else
		{
			$display = 'none';
		}

		if (!$custom_fields)
			return;
		//echo $display;

		// Use nonce for verification
		echo "<input type='hidden' name='wp_cta_{$key}_custom_fields_nonce' value='".wp_create_nonce('wp-cta-nonce')."' />";

		// Begin the field table and loop
		echo '<table class="wp-cta-tab-display" id="'.$key.'" style="display:'.$display.'">';
		//print_r($custom_fields);exit;
		foreach ($custom_fields as $field) {
			//echo $field['type'];exit;
			// get value of this field if it exists for this post
			if (isset($field['default']))
			{
				$default = $field['default'];
			}
			else
			{
				$default = null;
			}

			$field['id'] = $key."-".$field['id'];

			if (array_key_exists('option_name',$field) && $field['option_name'] )
				$field['id'] = $field['option_name'];

			$field['value'] = get_option($field['id'], $default);

			// begin a table row with
			echo '<tr><th class="wp-cta-gs-th options-'.$field['id'].'" valign="top">';
				if ($field['type']=='header')
				{
					echo $field['default'];
				}
				else
				{
					echo "<small>".$field['label']."</small>";
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
									<div class="wp_cta_tooltip tool_color" title="'.$field['description'].'"></div>';
							break;
						case 'header':
							$extra = (isset($field['description'])) ? $field['description'] : '';
							echo $extra;
							break;
						case 'datepicker':
							echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="8" />
									<div class="wp_cta_tooltip tool_date" title="'.$field['description'].'"></div><p class="description">'.$field['description'].'</p>';
							break;
						case 'license-key':
							$license_status = wp_cta_check_license_status($field);
							$master_key = get_option('inboundnow_master_license_key' , '');
						
							if ($master_key)
							{							
								$field['value'] = $master_key;
								$input_type = 'hidden';
							}
							else
							{
								$input_type = 'text';
							}
							
							//echo $license_status;exit;
							echo '<input type="hidden" name="wp_cta_license_status-'.$field['slug'].'" id="'.$field['id'].'" value="'.$license_status.'" size="30" />
							<input type="'.$input_type.'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />';

							if ($license_status=='valid')
							{
								echo '<div class="wp_cta_license_status_valid">Valid</div>';
							}
							else
							{
								echo '<div class="wp_cta_license_status_invalid">Invalid</div>';
							}
							
							echo '<div class="wp_cta_tooltip tool_text" title="'.$field['description'].'"></div>';
							break;
						case 'text':
							echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />
									<div class="wp_cta_tooltip tool_text" title="'.$field['description'].'"></div>';
							break;
						// textarea
						case 'textarea':
							echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6">'.$field['value'].'</textarea>
									<div class="wp_cta_tooltip tool_textarea" title="'.$field['description'].'"></div>';
							break;
						// wysiwyg
						case 'wysiwyg':
							wp_editor( $field['value'], $field['id'], $settings = array() );
							echo	'<span class="description">'.$field['description'].'</span><br><br>';
							break;
						// media
							case 'media':
							//echo 1; exit;
							echo '<label for="upload_image">';
							echo '<input name="'.$field['id'].'"  id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
							echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
							echo '<br /><div class="wp_cta_tooltip tool_media" title="'.$field['description'].'"></div>';
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
								if ($i==5||$i==1)
								{
									echo "<tr>";
									$i=1;
								}
									echo '<td><input type="checkbox" name="'.$field['id'].'[]" id="'.$field['id'].'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','/>';
									echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
								if ($i==4)
								{
									echo "</tr>";
								}
								$i++;
							}
							echo "</table>";
							echo '<br><div class="wp_cta_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
						break;
						// radio
						case 'radio':
							foreach ($field['options'] as $value=>$label) {
								//echo $meta.":".$field['id'];
								//echo "<br>";
								echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
								echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
							}
							echo '<div class="wp_cta_tooltip tool_radio" title="'.$field['description'].'"></div>';
						break;
						// select
						case 'dropdown':
							echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
							foreach ($field['options'] as $value=>$label) {
								echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
							}
							echo '</select><br /><div class="wp_cta_tooltip tool_dropdown" title="'.$field['description'].'"></div>';
						break;
						case 'html':
							//print_r($field);
							echo $field['value'];
							echo '<br /><div class="wp_cta_tooltip tool_dropdown" title="'.$field['description'].'"></div>';
						break;



					} //end switch


					do_action('wp_cta_render_global_settings',$field);

			echo '</td></tr>';
		} // end foreach
		echo '</table>'; // end table
	}	
	
	function wp_cta_check_license_status($field)
	{
		//print_r($field);exit;
		$date = date("Y-m-d");
		$cache_date = get_option($field['id']."-expire");
		$license_status = get_option('wp_cta_license_status-'.$field['slug']);

		if (isset($cache_date)&&($date<$cache_date)&&$license_status=='valid')
		{
			return "valid";
		}

		$license_key = get_option($field['id']);

		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license_key,
			'item_name' => urlencode( $field['slug'] )
		);
		//print_r($api_params);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, WP_CTA_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
		//print_r($response);

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		//echo $license_data;exit;

		if( $license_data->license == 'valid' ) {
			$newDate = date('Y-m-d', $license_data->expires );
			update_option($field['id']."-expire", $newDate);
			return 'valid';
			// this license is still valid
		} else {
			return 'invalid';
		}
	}
}