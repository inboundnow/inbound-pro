<?php

/**
 * Creates Global Settings
 *
 * @package	Inbouns Mailer
 * @subpackage	Global Settings
*/

if ( !class_exists('Inbound_Mailer_Settings') ) {

	class Inbound_Mailer_Settings {

		static $core_settings;
		static $active_tab;

		/**
		*	Initializes class
		*/
		public function __construct() {
			self::add_hooks();
		}

		/**
		*	Loads hooks and filters
		*/
		public static function add_hooks() {
			/*  Add settings to inbound pro  */
			add_filter('inbound_settings/extend', array( __CLASS__  , 'define_pro_settings' ) );

			/* Support single installation settings */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
		}

		/**
		*  Adds pro admin settings
		*/
		public static function define_pro_settings( $settings ) {
			$settings['inbound-pro-setup'][] = array(
				'group_name' => INBOUND_EMAIL_SLUG ,
				'keywords' => __('email,mailer,marketing automation' , 'inbound-pro'),
				'fields' => array (
					array(
						'id'  => 'header_mailer',
						'type'  => 'header',
						'default'  => __('Inbound Mailer Settings', 'inbound-pro' ),
						'options' => null
					),
					array(
						'id'  => 'unsubscribe-page',
						'label'  => __('Unsubscribe Location', 'inbound-email' ),
						'description'  => __( 'Where to send readers to unsubscribe. We auto create an unsubscribe page on activation, but you can use our shortcode on any page [inbound-email-unsubscribe]. ' , 'inbound-email' ),
						'type'  => 'dropdown',
						'default'  => '',
						'options' => Inbound_Mailer_Settings::get_pages_array()
					),
					array(
						'id'  => 'mandrill-key',
						'label'  => __('Mandrill API Key', 'inbound-email' ),
						'description'  => __( 'Enter in your maindrill API Key here.' , 'inbound-email' ),
						'type'  => 'text',
						'default'  => '',
						'options' => null
					),
					array(
						'id'  => 'mandrill_setup_instructions',
						'type'  => 'ol',
						'label' => __( 'Setup Instructions:' , 'inbound-email' ),
						'options' => array(
							sprintf( __( 'Register for an account over at %s and create a new application.' , 'inbound-email' ), '<a href="https://mandrill.com/signup/" target="_blank">Mandrill.com</a>' ),
							sprintf( __( 'Create an API Key for this website in your %s.' , 'inbound-email' ), '<a href="https://mandrillapp.com/settings/index" target="_blank">Mandrill Settings Area</a>' ),
							__( 'Mailer requires that your Mandrill account have a positive balance in order to schedule emails.' , 'inbound-email' )
						)
					)
				)

			);


			return $settings;

		}


		/**
		*	Get global setting data
		*/
		public static function define_legacy_settings() {


			/* Setup Main Navigation Tab and Settings */
			$tab_slug = 'inbound-mail-settings';
			$inbound_email_global_settings[$tab_slug]['label'] = 'Global Settings';

			$pages_array = Inbound_Mailer_Settings::get_pages_array();

			$inbound_email_global_settings[$tab_slug]['settings'] =	array(
				array(
					'id'  => 'unsubscribe-page',
					'label'  => 'Unsubscribe Location',
					'description'  => __( 'Where to send readers to unsubscribe. We auto create an unsubscribe page on activation, but you can use our shortcode on any page [inbound-email-unsubscribe]. ' , 'inbound-email' ),
					'option_name'  => 'unsubscribe-page',
					'type'  => 'dropdown',
					'default'  => '',
					'options' => $pages_array
				),
				array(
						'id'  => 'mandrill-key',
						'label'  => __('Mandrill API Key', 'inbound-email' ),
						'description'  => __( 'Enter in your maindrill API Key here.' , 'inbound-email' ),
						'type'  => 'text',
						'default'  => '',
						'options' => null
				),
				array(
					'id'  => 'mandrill_setup_instructions',
					'type'  => 'html',
					'label' => __( 'Setup Instructions:' , 'inbound-email' ),
					'description' => __( 'Use links above for help with setup!' , 'inbound-email' ),
					'default' => '<ol>'.
						'<li>'.sprintf( __( 'Register for an account over at %s and create a new application.' , 'inbound-email' ), '<a href="https://mandrill.com/signup/" target="_blank">Mandrill.com</a>' ) . '</li>' .
						'<li>'.sprintf( __( 'Create an API Key for this website in your %s.' , 'inbound-email' ), '<a href="https://mandrillapp.com/settings/index" target="_blank">Mandrill Settings Area</a>' ) . '</li>'.
						'</ol>'
				)
			);

			$inbound_email_global_settings = apply_filters('inbound_email_define_global_settings',$inbound_email_global_settings);


			self::$core_settings = $inbound_email_global_settings;

		}

		/**
		*  Gets array of pages with ID => Label format
		*
		*/
		public static function get_pages_array() {
			$pages = get_pages();

			$pages_array = array() ;

			foreach ($pages as $page) {
				$pages_array[ $page->ID ] = $page->post_title;
			}

			return $pages_array;
		}


		/**
		*	Load CSS & JS
		*/
		public static function enqueue_scripts() {
			$screen = get_current_screen();

			if ( ( isset($screen) && $screen->base != 'inbound-email_page_inbound_email_global_settings' ) ){
				return;
			}

			wp_enqueue_style('inbound-mailer-css-global-settings-here', INBOUND_EMAIL_URLPATH . 'css/admin-global-settings.css');
		}

		/**
		*	Displays nav tabs
		*/
		public static function display_navigation() {

			self::$active_tab = 'inbound-mail-settings';
			if (isset($_REQUEST['open-tab'])) {
				self::$active_tab = $_REQUEST['open-tab'];
			}

			echo '<h2 class="nav-tab-wrapper">';

			foreach (self::$core_settings	as $key => $data)
			{
				?>
				<a	id='tabs-<?php echo $key; ?>' class="inbound-mailer-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $data['label']; ?></a>
				<?php
			}
			echo "</h2>";

			echo "<form action='edit.php?post_type=inbound-email&page=inbound_email_global_settings' method='POST'>
			<input type='hidden' name='nature' value='inbound-mailer-global-settings-save'>
			<input type='hidden' name='open-tab' id='id-open-tab' value='". self::$active_tab ."'>";

		}


		/**
		*	Display sidebar
		*/
		public static function display_sidebar() {
			?>

			<div class='inbound-mailer-settings-tab-sidebar'>
				<div class='inbound-mailer-sidebar-settings'>
					<h2 style='font-size:18px;'>
						<?php _e( 'Follow Updates on Facebook' , 'inbound-email' ); ?>
					</h2>
					<iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'>
					</iframe>
				</div>
			</div>
			<?php
		}

		/**
		*	Display global settings
		*/
		public static function display_global_settings()	{
			global $wpdb;

			self::define_legacy_settings();
			self::inline_js();
			self::save_settings();

			self::display_navigation();
			self::display_sidebar();

			foreach ( self::$core_settings as $key => $data)
			{
				if (isset($data['settings'])) {
					self::render_setting($key , $data['settings']);
				}
			}


			echo '<div style="float:left;padding-left:9px;padding-top:20px;">
					<input type="submit" value="Save Settings" tabindex="5" id="inbound-mailer-button-create-new-group-open" class="button-primary" >
				</div>';
			echo "</form>";
			?>

			<div class="clear" id="php-sql-inbound-mailer-version">
			<h3><?php _e( 'Installation Status' , 'inbound-email' ); ?></h3>
					<table class="form-table" id="inbound-mailer-wordpress-site-status">

					<tr valign="top">
						<th scope="row"><label><?php _e( 'PHP Version' , 'inbound-email' ); ?></label></th>
						<td class="installation_item_cell">
							<strong><?php echo phpversion(); ?></strong>
						</td>
						<td>
							<?php
								if(version_compare(phpversion(), '5.0.0', '>')){
									?>
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/tick.png"/>
									<?php
								}
								else{
									?>
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/cross.png"/>
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
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/tick.png"/>
									<?php
								}
								else{
									?>
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/cross.png"/>
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
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/tick.png"/>
									<?php
								}
								else{
									?>
									<img src="<?php echo INBOUND_EMAIL_URLPATH;?>/images/cross.png"/>
									<span class="installation_item_message"><?php _e( 'landing pages requires version X or higher' , 'inbound-email' ) ?></span>
									<?php
								}
							?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e( 'WordPress CTA Version' , 'inbound-email' ); ?></label></th>
						<td class="installation_item_cell">
							<strong>Version <?php echo INBOUND_EMAIL_CURRENT_VERSION;?></strong>
						</td>
						<td>

						</td>
					</tr>
				</table>
			</div>
		<?php
		}


		/**
		*	Renders setting field
		*	@param STRING $key tab key
		*	@param ARRAY $custom_fields field settings
		*/
		public static function render_setting($key , $custom_fields ) {

			( $key==self::$active_tab ) ? $display = 'block' : 	$display = 'none';

			if (!$custom_fields) {
				return;
			}


			// Use nonce for verification
			echo "<input type='hidden' name='inbound_email_{$key}_custom_fields_nonce' value='".wp_create_nonce('inbound-mailer-nonce')."' />";

			// Begin the field table and loop
			echo '<table class="inbound-mailer-tab-display" id="'.$key.'" style="display:'.$display.'">';

			foreach ($custom_fields as $field) {
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

				$field['value'] = Inbound_Options_API::get_option( 'inbound-email' , $field['id'] , $field['default'] );

				// begin a table row with
				echo '<tr><th class="inbound-mailer-gs-th options-'.$field['id'].'" valign="top">';
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
										<div class="inbound_email_tooltip tool_color" title="'.$field['description'].'"></div>';
								break;
							case 'header':
								$extra = (isset($field['description'])) ? $field['description'] : '';
								echo $extra;
								break;
							case 'datepicker':
								echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="8" />
										<div class="inbound_email_tooltip tool_date" title="'.$field['description'].'"></div><p class="description">'.$field['description'].'</p>';
								break;
							case 'license-key':
								$license_status = self::check_license_status($field);
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

								echo '<input type="hidden" name="inbound_email_license_status-'.$field['slug'].'" id="'.$field['id'].'" value="'.$license_status.'" size="30" />
								<input type="'.$input_type.'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />';

								if ($license_status=='valid')
								{
									echo '<div class="inbound_email_license_status_valid">Valid</div>';
								}
								else
								{
									echo '<div class="inbound_email_license_status_invalid">Invalid</div>';
								}

								echo '<div class="inbound_email_tooltip tool_text" title="'.$field['description'].'"></div>';
								break;
							case 'text':
								echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />
										<div class="inbound_email_tooltip tool_text" title="'.$field['description'].'"></div>';
								break;
							// textarea
							case 'textarea':
								echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6">'.$field['value'].'</textarea>
										<div class="inbound_email_tooltip tool_textarea" title="'.$field['description'].'"></div>';
								break;
							// wysiwyg
							case 'wysiwyg':
								wp_editor( $field['value'], $field['id'], $settings = array() );
								echo	'<span class="description">'.$field['description'].'</span><br><br>';
								break;
							// media
								case 'media':

								echo '<label for="upload_image">';
								echo '<input name="'.$field['id'].'"	id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
								echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
								echo '<br /><div class="inbound_email_tooltip tool_media" title="'.$field['description'].'"></div>';
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
								echo '<br><div class="inbound_email_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
							break;
							// radio
							case 'radio':
								foreach ($field['options'] as $value=>$label) {
									//echo $meta.":".$field['id'];
									//echo "<br>";
									echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
									echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
								}
								echo '<div class="inbound_email_tooltip tool_radio" title="'.$field['description'].'"></div>';
							break;
							// select
							case 'dropdown':
								echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
								foreach ($field['options'] as $value=>$label) {
									echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
								}
								echo '</select><br /><div class="inbound_email_tooltip tool_dropdown" title="'.$field['description'].'"></div>';
							break;
							case 'html':
								//print_r($field);
								echo $field['value'];
								echo '<br /><div class="inbound_email_tooltip tool_dropdown" title="'.$field['description'].'"></div>';
							break;



						} //end switch


						do_action('inbound_email_render_global_settings',$field);

				echo '</td></tr>';
			} // end foreach
			echo '</table>'; // end table
		}

		/**
		*	Renders supporting JS
		*/
		public static function inline_js() {

			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {

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

						jQuery.removeCookie('inbound_email_global', { path: '/' }); // remove global cookie
						var inbound_email_cookies = getCookieByMatch(/^inbound_email_\d+=/);
						var length = inbound_email_cookies.length,
							element = null;
						for (var i = 0; i < length; i++) {
							element = inbound_email_cookies[i];
							cookie_name = element.split(/=/);
							cookie_name = cookie_name[0];
							jQuery.removeCookie( cookie_name, { path: '/' }); // remove each id cookie
						}

					});

					jQuery('.inbound-mailer-nav-tab').live('click', function() {
						var this_id = this.id.replace('tabs-','');
						//alert(this_id);
						jQuery('.inbound-mailer-tab-display').css('display','none');
						jQuery('#'+this_id).css('display','block');
						jQuery('.inbound-mailer-nav-tab').removeClass('nav-tab-special-active');
						jQuery('.inbound-mailer-nav-tab').addClass('nav-tab-special-inactive');
						jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');
						jQuery('#id-open-tab').val(this_id);
					});

				});
			</script>
			<?php

		}


		/**
		*	Listens for POST & saves settings chaWnges
		*/
		public static function save_settings() {

			if (!isset($_POST['nature'])) {
				return;
			}


			/* Loop through post vars and save as global setting */
			foreach ($_POST as $key => $value ) {
				Inbound_Options_API::update_option( 'inbound-email' , $key , $value );
			}
		}

		/**
		*  Gets settings value depending on if Inbound Pro or single installation.
		*/
		public static function get_settings() {
			if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
				$keys['unsubscribe_page'] = Inbound_Options_API::get_option( 'inbound-email' , 'unsubscribe-page' , null);
				$keys['api_key'] = Inbound_Options_API::get_option( 'inbound-email' , 'mandrill-key' , null);
			} else {
				$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
				$keys['api_key'] =  ( isset($settings[ INBOUND_EMAIL_SLUG ][ 'mandrill-key' ]) ) ? $settings[ INBOUND_EMAIL_SLUG ][ 'mandrill-key' ] : '';
				$keys['unsubscribe_page'] = ( isset($settings[ INBOUND_EMAIL_SLUG ][ 'unsubscribe-page' ]) ) ? $settings[ INBOUND_EMAIL_SLUG ][ 'unsubscribe-page' ] : '';
			}

			return $keys;

		}
		
		/**
		*  Get Settings URL
		*/
		public static function get_settings_url() {
			
			if (!defined('INBOUND_PRO_CURRENT_VERSION')) {				
				$settings_url = admin_url('edit.php?post_type=inbound-email&page=inbound_email_global_settings');
			} else {
				$settings_url = admin_url('admin.php?page=inbound-pro&setting=email');
			}
			
			return $settings_url;
		}
	}



	/**
	*	Loads Inbound_Mailer_Settings on admin_init
	*/
	function load_Inbound_Mailer_Settings() {
		$Inbound_Mailer_Settings = new Inbound_Mailer_Settings;
	}
	add_action( 'admin_init' , 'load_Inbound_Mailer_Settings' );

}

