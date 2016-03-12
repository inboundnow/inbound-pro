<?php

/**
 *	Loads Admin Settings
 */

class Inbound_GA_Admin_Settings {

	static $ga_settings;
	static $api_settings;
	static $auth;

	/**
	 *	initiates class
	 */
	public function __construct() {
		
		/* load settings */
		self::$ga_settings = get_option('inbound_ga' , false);

		/* Define hooks and filters */
		self::load_hooks();

	}

	/**
	 *	Loads hooks and filters selectively
	 */
	public static function load_hooks() {

		/* Save settings */
		add_action( 'admin_init' , array( __CLASS__ , 'load_save_settings') , 10 );

		/* Save settings */
		add_action( 'admin_init' , array( __CLASS__ , 'load_oauth_connector') , 11 );

		/* Oauth confirm listeners */
		add_action( 'admin_init' , array( __CLASS__ , 'load_oauth_listeners') , 12 );

		/* Add sub-menu to Leads admin menu */
		add_action( 'admin_menu', array( __CLASS__, 'prepare_admin_menu') );

		/* Add css & inline js */
		add_action( 'admin_footer' , array( __CLASS__ , 'load_inline_css') );
		add_action( 'admin_footer' , array( __CLASS__ , 'load_inline_js') );

		/* Enqueue JS * CSS */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts') );

		/* add ajax listener for setting saves */
		add_action( 'wp_ajax_inbound_ga_toggle_insert' , array( __CLASS__ , 'toggle_insert_mode' ) );


	}

	/**
	 *	Loads Settings
	 */
	public static function load_save_settings() {

		/* load settings in settings page and during ouath process */
		if ( !isset($_GET['inbound_ga_oauth']) ) {
			if ( !isset($_GET['page']) || $_GET['page'] != 'inbound-ga-settings' ) {
				return;
			}
		}

		if (!self::$ga_settings) {
			self::$ga_settings = array();
		}

		/* update settings if doing save */
		if ( isset($_POST['save_credentials']) ) {
			self::$ga_settings = array_merge( self::$ga_settings , $_POST );
			self::save_settings();
		}

		/* update settings if doing save */
		if ( isset($_POST['delete_credentials']) ) {
			self::$ga_settings['analyticAccessToken'] = "";
			self::$ga_settings['profiles'] = "";
			self::$ga_settings['linked_profile'] = "";
			self::save_settings();
		}

		/* Update linked account */
		if ( isset( $_POST['link_profile'] ) ) {
			self::$ga_settings = array_merge( self::$ga_settings , $_POST );
			self::save_settings();
		}
		
		/* Update js snippet */
		if ( isset( $_POST['js_insert'] ) ) {
			self::$ga_settings = array_merge( self::$ga_settings , $_POST );
			self::save_settings();
		}

		if (isset($_GET['debug'])) {
			print_r(self::$ga_settings);
		}
	}

	/**
	 *	Load oauth connector if access token unavailable & client credentials available
	 */
	public static function load_oauth_connector() {
		global $google_oauth;
		
		/* prepare connection if api client details available	*/
		if ( !isset(self::$ga_settings['clientsecret']) || !self::$ga_settings['clientsecret'] ) {
			return;
		}

		if ( !isset(self::$ga_settings['clientid']) || !self::$ga_settings['clientid'] ) {
			return;
		}
		
		

		/* Craft a coded param for oauth redirect uri */
		$args = array( 'page' => 'inbound-ga-settings' );
		$args = base64_encode(json_encode($args));

		//http://www.jensbits.com/demos/analytics_oauth2/index.php?logout=1
		$api_settings = array(
			"clientid" => trim(self::$ga_settings['clientid']),
			"clientsecret" => trim(self::$ga_settings['clientsecret']),
			"redirecturi" => admin_url('edit.php?inbound_ga_oauth=1') ,
			"scope" => "https://www.googleapis.com/auth/analytics.readonly",
			"accesstype" => "offline",
			"approval_prompt" => "force"
		);

		$google_oauth = new Inbound_GA_GoogleOauth2($api_settings);
	}


	/**
	 *	Handle data returned by google
	 */
	public static function load_oauth_listeners() {


		if ( !isset($_GET['inbound_ga_oauth']) ) {
			return;
		}
		
		global $google_oauth;

		foreach($_GET as $key => $value){
			switch($key){
				case "error":
					//If user refuses to grant access to app, url param of "error" is returned by Google
					$errors["Access Error"] = $value;
					self::$ga_settings['analyticAccessToken'] = "";
					self::save_settings();
					break;
				case "logout":
					self::$ga_settings['analyticAccessToken'] = "";
					self::save_settings();
					break;
				case "code":
				
					/* Oauth2 code for access token so multiple calls can be made to api */
					$tokens = $google_oauth->getOauth2Token($_GET["code"]);

					self::check_oauth_token_error($tokens);						
					self::$ga_settings['analyticAccessToken'] = $tokens->access_token;
					
					if (isset($tokens->refresh_token)) {
						self::$ga_settings['refreshToken'] =  $tokens->refresh_token;
					}
					self::save_settings();

					/* reload for 'clean' url */
					header("Location:". admin_url('admin.php?page=inbound-ga-settings') );
					exit;
					break;
			}
		}

		
	}

	/**
	 * Load latest settings
	 */
	public static function load_profiles() {
		
		
		/* create google analytics data object */
		$gaData = new Inbound_GA_Gadata(self::$ga_settings);
		
		/* hold in session to prevent additional requests for profiles (profiles don't change too often) */
		if ( !isset(self::$ga_settings['profiles']) || !self::$ga_settings['profiles'] ){
			self::$ga_settings['profiles'] = $gaData->parseProfileList();
			self::save_settings();
		}
		

		/* If profiles have error then discard oauth details */
		if ( isset(self::$ga_settings['profiles'][0]['error']) ) {
			self::$ga_settings['profiles'] = "";
			self::save_settings();
			print_r(self::$ga_settings);exit;
		}
		//exit;
	}

	/**
	 *	Loads admin CSS
	 */
	public static function load_inline_css() {

		$screen = get_current_screen();

		if (isset($screen->base) && $screen->base != 'wp-lead_page_inbound-ga-settings' ) {
			return;
		}

		?>
		<style type='text/css'>

		</style>
		<?php

	}

	/**
	 *	Load JS
	 */
	public static function load_inline_js() {
		?>
		<script>
			jQuery('document').ready( function() {

				jQuery('.section_title').click(function(){
					jQuery(this).siblings('.section_content').toggleClass('active');
				});


				jQuery('#disable_tracking_insert').click(function(){

					jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						context: this,
						data: {
							action: 'inbound_ga_toggle_insert',
							toggle: jQuery(this).is(":checked")
						},

						success: function(data){
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							alert("Ajax not enabled");
						}
					});

				});

			});
		</script>
		<?php
	}



	/**
	 *	Save settings
	 */
	public static function save_settings() {
		update_option( 'inbound_ga' ,  self::$ga_settings );
	}

	/**
	 *	Enqueues scripts
	 */
	public static function enqueue_scripts() {

		$screen = get_current_screen();

		if (isset($screen->base) && ( $screen->base != 'toplevel_page_inbound-ga-settings' && $screen->base != 'inbound-now_page_inbound-ga-settings' ) ) {
			return;
		}

		/** CSS for admin settings page */
		wp_enqueue_style('inbound-ga-settings', INBOUND_GA_URLPATH . 'assets/css/admin.settings.css');
	}

	/**
	 *	Adds settings sub menu item to wp-admin Leads menu
	 */
	public static function prepare_admin_menu() {

		if (!current_user_can('manage_options')) {
			return;
		}

		if (class_exists('Inbound_Pro_Plugin')) {
			/* Manage Settings */
			add_submenu_page(
				'inbound-pro',
				__('Google Analytics', 'inbound-pro'),
				__('Google Analytics', 'inbound-pro'),
				'manage_options',
				'inbound-ga-settings',
				array( __CLASS__ , 'admin_page' )
			);
		} else {
			add_menu_page(
				__('Google Analytics', 'inbound-pro'),
				__('Google Analytics', 'inbound-pro'),
				'manage_options',
				'inbound-ga-settings',
				array(__CLASS__, 'admin_page')
			);
		}

	}

	/**
	 *	Defines settings to be used
	 */
	public static function define_settings() {

		$settings = array();

		/* Client ID */
		$settings['clientid'] =	array(
			'id'	=> 'clientid',
			'label' => __('API Client ID' , INBOUNDNOW_TEXT_DOMAIN ),
			'description' => __('Description here' , 'inbound-pro'),
			'type'	=> 'text',
			'default'	=> '',
			'options' => null
		);

		/* Client Secret */
		$settings['clientsecret'] =	array(
			'id'	=> 'clientsecret',
			'label' => __('API Client Secret' , INBOUNDNOW_TEXT_DOMAIN ),
			'description' => __('Description here' , 'inbound-pro'),
			'type'	=> 'text',
			'default'	=> '',
			'options' => null
		);


		return $settings;

	}

	/**
	 *	Renders admin_page
	 */
	public static function admin_page(){
		?>

		<div class="wrap">
			<h2>Google Anlytics</h2>

			<?php

			self::display_step_1();
			self::display_step_2();
			/* self::display_js_insert(); */

			?>
		</div>
		<?php
	}

	/**
	 *	Displays set 1
	 */
	public static function display_step_1() {
		$settings = self::define_settings();
		?>
		<section id="step01">
			<div class="section_title">
				<?php _e( 'Save API Credentials' , 'inbound-pro' ); ?>
			</div>
			<div class="section_content active">
				<form action="" method="post">

					<ol>
						<li>Visit <a href="https://console.developers.google.com/" rel="nofollow">https://console.developers.google.com/</a> and create a new project.</li>
						<li>Enable the API for Google Analytics</li>
						<li>Create a oauth credentials and set the following as your redirect URI:<?php echo admin_url('edit.php?inbound_ga_oauth=1'); ?></li>
						<li>Input API Keys below:</li>
					</ol>
					<?php

					self::render_settings($settings );

					?>
					<input name="save_credentials" type="submit" value="Save Credentials">
				</form>
			</div>
		</section>
		<?php

	}

	/**
	 *	Display step 2
	 */
	public static function display_step_2() {

		if ( !isset(self::$ga_settings['clientid']) || !self::$ga_settings['clientid'] ) {
			return;
		}

		if ( !isset(self::$ga_settings['clientsecret']) || !self::$ga_settings['clientsecret'] ) {
			return;
		}


		self::display_step_2_authorize();
		self::display_step_2_select_profiles();
		self::display_step_2_unauthorize();

	}

	/**
	 *	Requests user to authorize their google account
	 */
	public static function display_step_2_authorize() {
		global $google_oauth;
		
		if ( isset(self::$ga_settings['analyticAccessToken']) && self::$ga_settings['analyticAccessToken'] ) {
			return;
		}

		?>
		<section id="step02">
			<div class="section_title">
				<?php _e('Step 02: Authorize your account.' , INBOUNDNOW_TEXT_DOMAIN ); ?>
			</div>
			<div class="section_content active">
				<div class="hero-unit">
					<h1><?php _e('Sign In' , INBOUNDNOW_TEXT_DOMAIN ); ?></h1>
					<p><?php _e('Google Analytics data displayed in Google Charts using OAuth2 authorization.<br />
					Google account must have access to analytics.</p>' , INBOUNDNOW_TEXT_DOMAIN ); ?>
					<p><a class="btn btn-primary btn-large" href="<?php echo $google_oauth->loginurl ?>"><?php _e('Authorize with Google account' , INBOUNDNOW_TEXT_DOMAIN ); ?></a></p>

				</div>
			</div>
		</section>
		<?php
	}

	/**
	 *	Lists profiles and gives user opportunity to associate one with the website.
	 */
	public static function display_step_2_select_profiles() {
		if ( !isset(self::$ga_settings['analyticAccessToken'] ) || !self::$ga_settings['analyticAccessToken'] ) {
			return;
		}

		/* get profiles */
		self::load_profiles();

		$checked = ( isset(self::$ga_settings['disable_tracking_insert']) ) ? self::$ga_settings['disable_tracking_insert'] : false;

		?>
		<section id="step02">
			<div class="section_title">
				<?php _e( 'Associate Profile With Website.' , INBOUNDNOW_TEXT_DOMAIN ); ?>
			</div>
			<div class="section_content active">
				<div class="hero-unit">
					<form action="" method="post">
						<select name='linked_profile'>
							<option value=""><?php _e('Select profile','inbound-pro'); ?></option>
							<?php
					
							foreach (self::$ga_settings['profiles'] as $key => $profile ) {
								$selected = ( isset(self::$ga_settings['linked_profile']) && self::$ga_settings['linked_profile'] == $profile['profileid'] ) ? 'selected="true"' : '';
								echo '<option value="'.$profile['profileid'].'" '.$selected.'>'.$profile['name'].'</option>';
							}
							?>
						</select>
						<input name="link_profile" type="submit" value="<?php _e('Link Website' , INBOUNDNOW_TEXT_DOMAIN ); ?>">
						<?php
						if (isset(self::$ga_settings['linked_profile']) &&  self::$ga_settings['linked_profile'] ) {
							echo '<span class="label">'.__('linked!' , 'inbound-pro') .'</span>';
						}
						?>



					<br>
					</form>
					<i><small><?php _e( 'This extension automatically adds Google Universal Analytics tracking code to your theme\'s footer.' , 'inbound-pro' ); ?></small></i>
						<br>
						<input name="disable_tracking_insert" id="disable_tracking_insert" type="checkbox" value="1" <?php echo ($checked) ? 'checked="true"' : '' ; ?> ><?php _e('Click to disable automatic insertion of tracking code' , 'inbound-pro' ); ?>

				</div>
			</div>
		</section>
		<?php
	}
	

	/**
	 *	Gives user chance to unauthorize his account
	 */
	public static function display_step_2_unauthorize() {

		if ( !isset(self::$ga_settings['analyticAccessToken'] ) || !self::$ga_settings['analyticAccessToken'] ) {
			return;
		}

		?>
		<section id="step02">
			<div class="section_title">
				<?php _e( 'Unauthorize Account.' , INBOUNDNOW_TEXT_DOMAIN ); ?>
			</div>
			<div class="section_content active">
				<div class="hero-unit">
					<form action="" method="post">
						<input name="delete_credentials" type="submit" value="<?php _e('Unauthorize Account' , INBOUNDNOW_TEXT_DOMAIN ); ?>">
					</form>

				</div>
			</div>
		</section>
		<?php
	}

	
	/**
	 *	Give users an input to add the js script to the site theme
	 */
	public static function display_js_insert() {
	
		
		?>
		<section id="jsinsert">
			<div class="section_title">
				<?php _e( 'Analytics Code' , INBOUNDNOW_TEXT_DOMAIN ); ?>
			</div>
			<div class="section_content active">
				<div class="hero-unit">
					<form action=""method="post">
						<textarea name="inbound_ga_js_snippet" style='width:100%;' value='<?php ( isset( self::$ga_settings[ 'inbound_ga_js_snippet' ] ) ) ? self::$ga_settings[ 'inbound_ga_js_snippet' ] :	''; ?>'></textarea>
						<br>
						<i><small><?php _e( 'If you have not added your Google Analytics JS snippet to your website yet you can add it in here.' , 'inbound-pro' ); ?></small></i>
						<input name="js_insert" type="submit" value="<?php _e('Save Analytics Code' , INBOUNDNOW_TEXT_DOMAIN ); ?>">
					</form>
				</div>
			</div>
		</section>
		<?php
	}

	
	/**
	 *	Renders settings
	 */
	public static function render_settings( $custom_fields ) {
		$settings = self::$ga_settings;

		// Begin the field table and loop
		echo '<div class="form-table" id="inbound-meta">';

		foreach ($custom_fields as $field) {

			$field_id = $field['id'];

			$label_class = $field['id'] . "-label";
			$type_class = " inbound-" . $field['type'];
			$type_class_row = " inbound-" . $field['type'] . "-row";
			$type_class_option = " inbound-" . $field['type'] . "-option";
			$option_class = (isset($field['class'])) ? $field['class'] : '';

			/* if setting does has a stored value then use default value */
			( isset( self::$ga_settings[ $field_id ] ) ) ? 	$meta = self::$ga_settings[ $field_id ] : $meta = $field['default'];

			// Remove prefixes on global => true template options
			if ( isset($field['global']) && $field['global']	) {
				$field_id = $field['id'];
				$meta =	( isset( $settings[ $field_id ] ) ) ? $settings[ $field_id ] :	$field['default'];
			}


			// begin a table row with
			echo '<div class="'.$field['id'].$type_class_row.' div-'.$option_class.' inbound-email-option-row inbound-meta-box-row">';
			echo '<div id="inbound-'.$field_id.'" data-actual="'.$field_id.'" class="inbound-meta-box-label inbound-email-table-header '.$label_class.$type_class.'"><label for="'.$field_id.'">'.$field['label'].'</label></div>';
			echo '<div class="inbound-email-option-td inbound-meta-box-option '.$type_class_option.'" data-field-type="'.$field['type'].'">';
			switch($field['type']) {

				case 'text':
					echo '<input type="text" class="'.$option_class.' form-control" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
								<div class="inbound_email_tooltip" title="'.$field['description'].'"></div>';
					break;
				// radio
				case 'radio':
					foreach ($field['options'] as $value=>$label) {
						//echo $meta.":".$field_id;
						//echo "<br>";
						echo '<input type="radio" name="'.$field_id.'" id="'.$field_id.'" value="'.$value.'" ',$meta==$value ? ' checked="checked"' : '','/>';
						echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo '<div class="inbound_email_tooltip" title="'.$field['description'].'"></div>';
					break;
				// select
				case 'dropdown':
					echo '<select name="'.$field_id.'" id="'.$field_id.'" class="'.$field['id'].' form-control">';
					foreach ($field['options'] as $value=>$label) {
						echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
					}
					echo '</select><div class="inbound_email_tooltip" title="'.$field['description'].'"></div>';
					break;
			} //end switch
			echo '</div>';
			echo '</div>';
		} // end foreach
		echo '</div>';

	}
	
	/**
	 *  Check oauth response for errors
	 */
	public static function check_oauth_token_error( $tokens ) {
		
		if( is_object($tokens)){
			return;
		}
		
		$errors["Access Error"] = $tokens;
		self::$ga_settings['analyticAccessToken'] = "";
		self::$ga_settings['refreshToken'] = "";
		self::save_settings();
		error_log('rawr');
		echo $tokens;
		exit;			
		
	}
	
	public static function toggle_insert_mode() {
		self::$ga_settings['disable_tracking_insert'] = $_POST['toggle'];
		self::save_settings();
	}

}

$Inbound_GA_Admin_Settings = new Inbound_GA_Admin_Settings();

