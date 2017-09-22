<?php
/**
 * Class for managing Inbound Pro core and extension settings. Also powers welcome page.
 * @package     InboundPro
 * @subpackage  Settings
 */

class Inbound_Pro_Settings {
	static $tab; /* placeholder for page currently opened */
	static $settings_fields; /* configuration dataset */
	static $settings_values; /* configuration dataset */

	/**
	 *	Load hooks and listners
	 */
	public static function init() {
		self::$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'inbound-pro-setup';
		self::activation_redirect();
		self::add_hooks();
	}


	/**
	 *	Loads hooks and filters
	 */
	public static function add_hooks() {

		/* add listener for importing settings from json file */
		add_action( 'admin_init' , array( __CLASS__ , 'import_settings_from_json' ) );

		/* enqueue js and css */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );

		/* add ajax listener for setting saves */
		add_action( 'wp_ajax_inbound_pro_update_setting' , array( __CLASS__ , 'ajax_update_settings' ) );

		/* add ajax listener for custom fields setting saves */
		add_action( 'wp_ajax_inbound_pro_update_custom_fields' , array( __CLASS__ , 'ajax_update_custom_fields' ) );

		/* add ajax listener for custom fields setting saves */
		add_action( 'wp_ajax_inbound_pro_update_lead_statuses' , array( __CLASS__ , 'ajax_update_lead_statuses' ) );

		/* add ajax listener for IP Addresses setting saves */
		add_action( 'wp_ajax_inbound_pro_update_ip_addresses' , array( __CLASS__ , 'ajax_update_ip_addresses' ) );

		/* add ajax listener for export inbound now settings */
		add_action( 'wp_ajax_inbound_pro_export_settings' , array( __CLASS__ , 'ajax_export_settings' ) );

		/* listen for fast ajax installation */
		add_action( 'inbound-settings/after-field-value-update' , array( __CLASS__ , 'ajax_toggle_fast_ajax' ) , 10 , 1 );
	}

	/**
	 *	Enqueue scripts & stylesheets
	 */
	public static function enqueue_scripts() {
		global $inbound_settings;

		$screen = get_current_screen();

		$counts = array();
		$counts['extensions'] = (isset($inbound_settings['system']['counts']['needs-update']['extensions'])) ? $inbound_settings['system']['counts']['needs-update']['extensions'] : 0;
		$counts['templates'] = (isset($inbound_settings['system']['counts']['needs-update']['templates'])) ? $inbound_settings['system']['counts']['needs-update']['templates'] : 0;

		/* load wp-admin global js & css */
		wp_enqueue_style('inbound-wp-admin', INBOUND_PRO_URLPATH . 'assets/css/admin/wp-admin.css');
		wp_enqueue_script('inbound-wp-admin', INBOUND_PRO_URLPATH . 'assets/js/admin/wp-admin.js', array('jquery') );
		wp_localize_script('inbound-wp-admin', 'inboundWPAdmin' ,  array('counts' =>  $counts , 'tb_hide_nav' =>  (isset($_GET['tb_hide_nav']) && $_GET['tb_hide_nav'] == 'true') ? true : false ) );

		/* Load assets for inbound pro page */
		if (isset($screen) && $screen->base != 'toplevel_page_inbound-pro' ){
			return;
		}

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('modernizr');
		wp_enqueue_script('underscore');
		add_thickbox();

		/* load shuffle.js */
		wp_enqueue_script('shuffle', INBOUND_PRO_URLPATH . 'assets/libraries/Shuffle/jquery.shuffle.modernizr.min.js' , array( 'jquery') );

		/* load jquery minicolors.js */
		wp_enqueue_script('minicolors', INBOUND_PRO_URLPATH . 'assets/libraries/MiniColors/jquery.minicolors.js' , array( 'jquery') );
		wp_enqueue_style('minicolors', INBOUND_PRO_URLPATH . 'assets/libraries/MiniColors/jquery.minicolors.css');

		/* load custom CSS & JS for inbound pro welcome */
		wp_enqueue_style('inbound-settings', INBOUND_PRO_URLPATH . 'assets/css/admin/settings.css');
		wp_enqueue_script('inbound-settings', INBOUND_PRO_URLPATH . 'assets/js/admin/settings.js', array('jquery', 'jquery-ui-sortable') );
		wp_localize_script('inbound-settings', 'inboundSettingsLocalVars' ,  array('apiURL' => Inbound_API_Wrapper::get_api_url() , 'siteURL' => site_url() , 'inboundProURL' => INBOUND_PRO_URLPATH ) );

		/* load Ink */
		wp_enqueue_style('Ink', INBOUND_PRO_URLPATH . 'assets/libraries/Ink/css/ink-flex.min.css');

		/* Load selective bootstrap */
		wp_enqueue_style('bootstrap-tooltip', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/css/tooltip.min.css');
		wp_enqueue_script('bootstrap-tooltip', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/js/tooltip.min.js');

		/* load fontawesome */
		wp_enqueue_style('fontawesome', INBOUNDNOW_SHARED_URLPATH . 'assets/fonts/fontawesome/css/font-awesome.min.css');
	}

	/**
	 *	Listens for the _inbound_pro_welcome transient and if it exists then redirect to the welcome page
	 */
	public static function activation_redirect() {

		if ( get_transient('_inbound_pro_welcome') ) {
			$redirect = admin_url('admin.php?page=inbound-pro&tab=inbound-pro-welcome');
			header('Location: ' . $redirect);
			delete_transient( '_inbound_pro_welcome' );
			exit;
		}
	}

	/**
	 *  Setup Core Settings
	 */
	public static function extend_settings() {

		self::$settings_fields = array(
			'inbound-pro-setup' => array(
				/* add api key group to setup page */
				array(
					'group_name' => 'api-key',
					'keywords' => __('api key' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'api-key',
							'type'	=> 'api-key',
							'default'	=> '',
							'placeholder'	=> __( 'Enter api key here' , 'inbound-pro' ),
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),

				/* add core plugin exclusion options */
				array(
					'group_name' => 'inbound-core-loading',
					'keywords' => __('activate,deactivate,enable,disable,turn off,turn on' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'load-core-comonents-header',
							'type'	=> 'header',
							'default'	=> __( 'Toggle Core Components On/Off' , 'inbound-pro' ),
							'placeholder'	=> null,
							'options' => false,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-landing-pages',
							'type'	=> 'radio',
							'label'	=> __( 'Landing Pages' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this off to stop loading Landing Pages component.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-calls-to-action',
							'type'	=> 'radio',
							'label'	=> __( 'Calls to Action' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this off to stop loading Calls to Action component.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),

							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-leads',
							'type'	=> 'radio',
							'label'	=> __( 'Leads' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this off to stop loading Leads component.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),

							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-email-automation',
							'type'	=> 'radio',
							'label'	=> __( 'Mailer & Automation' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this off to stop loading Mailer & Marketing Automation component. These components require an active pro membership.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => (INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9  ? false : true ),
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						)
					)
				),
				/* add custom lead fields field group to setup page */
				array(
					'group_name' => 'leads-custom-fields',
					'keywords' => __('leads, field mapping, custom fields' , 'inbound-pro'),
					'fields' => array (
						array(
							'id'  => 'header-lead-fields',
							'type'  => 'header',
							'default'  => __('Lead Field Management', 'inbound-pro' ),
							'options' => null
						),
						array (
							'id'	=> 'leads-custom-fields',
							'type'	=> 'custom-fields-repeater',
							'default'	=> '',
							'placeholder'	=> null,
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),
				/* add custom lead fields field group to setup page */
				array(
					'group_name' => 'lead-statuses',
					'keywords' => __('labels,status,tags,fields' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'lead-statuses',
							'type'	=> 'lead-status-repeater',
							'default'	=> '',
							'placeholder'	=> null,
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),
				/* add Analytics exclusion options */
				array(
					'group_name' => 'inbound-analytics-rules',
					'keywords' => __('analytics,tracking,ipaddress,ip address,admin tracking' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'analytics-header',
							'type'	=> 'header',
							'default'	=> __( 'Inbound Analytics' , 'inbound-pro' ),
							'placeholder'	=> null,
							'options' => false,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'page-tracking',
							'type'	=> 'radio',
							'label'	=> __( 'Page Tracking' , 'inbound-pro' ),
							'description'	=> __( 'Turning this off will disable the visitor impression tracking engine. Does not affect impression recording for CTAs, Landing Pages, or Emails. Will affect lead visit statistics. This should not be turned off unless your server is experiencing resource shortage issues.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'off' => __( 'Off' , 'inbound-pro' ),
								'on' => __( 'On' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'admin-tracking',
							'type'	=> 'radio',
							'label'	=> __( 'Admin Tracking' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this to on to prevent impression/conversion tracking for logged in administrators.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'off' => __( 'Off' , 'inbound-pro' ),
								'on' => __( 'On' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'exclude-ip-addresses',
							'type'	=> 'ip-address-repeater',
							'default'	=> '',
							'placeholder'	=> null,
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),
				/* add core plugin exclusion options */
				array(
					'group_name' => 'inbound-acf',
					'keywords' => __('advanced custom fields,acf' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'acf-header',
							'type'	=> 'header',
							'default'	=> __( 'ACF Options' , 'inbound-pro' ),
							'placeholder'	=> null,
							'options' => false,
							'hidden' => ( INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9  ? false : true ),
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-acf-lite',
							'type'	=> 'radio',
							'label'	=> __( 'ACF Lite Mode' , 'inbound-pro' ),
							'description'	=> __( 'If you are presented with this option then ACF5 is being loaded. Turning Lite Mode to off will tell it to also load it\'s additional UI features.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => ( INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9 ? false : true ),
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						)
					),
				),
				/* add core plugin exclusion options */
				array(
					'group_name' => 'inbound-fast-ajax',
					'keywords' => __('ajax,speed,optimization' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'fast-ajax-header',
							'type'	=> 'header',
							'default'	=> __( 'Fast(er) Ajax' , 'inbound-pro' ),
							'placeholder'	=> null,
							'options' => false,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-fast-ajax',
							'type'	=> 'radio',
							'label'	=> __( 'Enable Fast Ajax' , 'inbound-pro' ),
							'description'	=> __( 'Turning this feature will install a mu-plugin that Inbound Now will use to improve ajax response times. We recommend turning this on.' , 'inbound-pro' ),
							'default'	=> 'off',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						)
					),
				),
				/* add custom lead fields field group to setup page */
				array(
					'group_name' => 'translations',
					'keywords' => __('translations,enable,disable,notifications' , 'inbound-pro'),
					'fields' => array (
						array(
							'id'  => 'header-notifications',
							'type'  => 'header',
							'default'  => __('Translations', 'inbound-pro' ),
							'options' => null
						),
						array (
							'id'	=> 'toggle-translations',
							'type'	=> 'radio',
							'label'	=> __( 'Enable Translations' , 'inbound-pro' ),
							'description'	=> __( 'Enabling this feature will tell Inbound Now to load translated strings if they are available.' , 'inbound-pro' ),
							'default'	=> 'off',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'toggle-translations-updater',
							'type'	=> 'radio',
							'label'	=> __( 'Translation Update Notifications' , 'inbound-pro' ),
							'description'	=> __( 'This is a consmetic feature that enables customers not to be notified when new trasnlation packages are available.' , 'inbound-pro' ),
							'default'	=> 'on',
							'placeholder'	=> null,
							'options' => array(
								'on' => __( 'On' , 'inbound-pro' ),
								'off' => __( 'Off' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'translations-html',
							'type'	=> 'html',
							'label'	=> __( 'Help Inbound Now Translate' , 'inbound-pro' ),
							'description'	=> sprintf( __( 'Click %shere%s to help improve the quality of Inbound Now translations.' , 'inbound-pro' ), '<a href="http://docs.inboundnow.com/guide/inbound-translations-project/">' , '</a>' ),
						)
					),
				)
			)
		);

		self::$settings_fields = apply_filters( 'inbound_settings/extend' , self::$settings_fields );

	}

	/**
	 *	Render Pro Display
	 */
	public static function display() {

		?>

		<div class="ink-grid">


			<?php
			self::display_nav_menu();

			echo '<section class="column-group gutters article">';

			switch ( self::$tab ) {
				case 'inbound-pro-welcome':
					echo '<section class="xlarge-70 large-70 medium-60 small-100 tiny-100 welcome-screen-content">';
					self::display_welcome();
					echo '</section>';
					echo '<section class="xlarge-30 large-30 medium-30 small-100 tiny-100">';
					self::display_blog_posts();
					self::display_social_ctas();
					echo '</section>';
					BREAK;
				case 'inbound-pro-settings':
					echo '<section class="xlarge-70 large-70 medium-60 small-100 tiny-100 welcome-screen-content">';
					self::display_settings();
					echo '</section>';
					echo '<section class="xlarge-30 large-30 medium-30 small-100 tiny-100">';
					self::display_blog_posts();
					self::display_social_ctas();
					echo '</section>';
					BREAK;
				case 'inbound-pro-import-export':
					self::display_import_export();
					BREAK;
				case 'inbound-pro-setup':
					self::display_setup();
					BREAK;
				case 'inbound-my-account':
					self::display_setup();
					BREAK;
			}
			echo '</section>';
			self::display_footer();
			?>
		</div>
		<?php
	}

	/**
	 *  Display Pro Welcome Screen
	 */
	public static function display_welcome() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

		?>


		<h1><?php _e('Welcome to Inbound Pro ' . INBOUND_PRO_CURRENT_VERSION ,'inbound-pro'); ?></h1>

		<p><?php _e('Is this your first time? Please take a moment to review the sections below before getting started.','inbound-pro'); ?></p>

		<h2>Overview/Quick Links</h2>

		<ul class="features">
			<li><a href='https://www.inboundnow.com/inbound-analytics/' target="_blank"><?php _e('Inbound Analytics' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/landing-pages/' target="_blank"><?php _e('Landing Pages' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/cta/' target="_blank"><?php _e('Calls to Action' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/leads/' target="_blank"><?php _e('Leads' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/double-optin/' target="_blank"><?php _e('Double Optin' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/mailer/' target="_blank"><?php _e('Email Component' , 'inbound-pro'); ?></a></li>
			<li><a href='https://www.inboundnow.com/automation/' target="_blank"><?php _e('Marketing Automation' , 'inbound-pro'); ?></a></li>
		</ul>
		<h2>Inbound Pro API Key</h2>

		<p>Setting up your Inbound Pro API Key enables plugin updates and subscriber features. You can find your API Key inside the Inbound Now website's customer <a href="https://www.inboundnow.com/account/">account area</a>. </p>


		<h2>Tracking Controls</h2>
		<p>In your Inbound Pro Settings area you will see a new place where you can disable tracking on admin accounts and disable tracking by IP addresses.</p>

		<img class="size-full wp-image-138044" src="http://www.inboundnow.com/wp-content/uploads/2012/05/2015-10-13_1746.png" alt="Disable tracking by ip and admin"  />

		<h2>Creating Mappable Fields</h2>
		<p>In our current setup we have to use PHP code inserts to add non-native lead fields. Now we can do it straight from the settings area. We can also edit the labels of core fields and change the order they appear within a Lead Profile.</p>

		<img class="size-full wp-image-138045" src="http://www.inboundnow.com/wp-content/uploads/2012/05/2015-10-13_1748.png" alt="Add and edit mappable lead fields"  />

		<h2>Better Extension Management</h2>
		<p>It's now easier to work with extension settings. Quickly view installed extensions and jump right to their settings area at the click of a button. Uninstallation is easy too.</p>

		<img class="aligncenter size-full wp-image-138164" src="http://www.inboundnow.com/wp-content/uploads/2012/05/extension-settings.gif" alt="extension settings"  />


		<h2>SparkPost for Email</h2>

		<p>Inbound Pro's email component is currently provided by SparkPost. SparkPost provides it's users with 15,000 free sends a month and then an affordable rate their after. You can read more about SparkPost from their <a href="http://www.sparkpost.com" target="_blank">website</a>.</p>

		<h2>Subscribers Only: One click extension/theme installations</h2>

		<p>Our new Inbound Pro component will read the permissions of you API key and allow for one-click installations and uninstallations of all Inbound Now templates and plugins. </p>

		<img class="wp-image-138043 size-full" src="http://www.inboundnow.com/wp-content/uploads/2012/05/2015-10-13_1743.png" alt="One click template installations"  />

		<?php
		//self::render_fields( 'inbound-pro-welcome' );
		?>
		<?php
	}

	/**
	 *	Display Inbound Pro Setup page
	 */
	public static function display_setup() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

		?>

		<div class="xlarge-100 large-100 medium-100 small-100 tiny-100">
			<?php
			self::render_fields( 'inbound-pro-setup' );
			?>
		</div>
		<?php
	}

	/**
	 *	Display Inbound Pro Import Export Settings Option
	 */
	public static function display_import_export() {

		?>

		<div class="xlarge-100 large-100 medium-100 small-100 tiny-100">
			<div class="import-export-container">
				<h2><?php _e('Export Inbound Now Settings' , 'inbound-pro'); ?></h2>
				<p><?php _e('This will export all Inbound Now core settings and extension settings for use with future installs. Extensions and templates will still need to be activated manually.' , 'inbound-pro'); ?></p>
				<span class="button button-secondary" id="export-settings">
					<?php _e('Generate JSON Backup' , 'inbound-pro'); ?>
				</span>
				<br><br>
				<br>
				<h2><?php _e('Import Inbound Now Settings' , 'inbound-pro'); ?></h2>
				<?php
				if (isset($_FILES) && $_FILES ) {
					?>
					<p><?php _e('Your settings have been imported!.' , 'inbound-pro'); ?></p>
					<?php
				} else {
				?>
				<p><?php _e('Warning, this will overwrite Inbound Now settings already in place for this WordPress instance.' , 'inbound-pro'); ?></p>

				<form action="<?php echo admin_url('admin.php?tab=inbound-pro-import-export&page=inbound-pro'); ?>" class="wp-upload-form" enctype="multipart/form-data" method="post">
				<input type="file" name="jsonfile" id="jsonfile">
				<input type="hidden" name="inbound-action" value="import-json">
				<input type="submit" value="<?php _e('Import JSON' , 'inbound-pro'); ?>" class="button" id="import-settings" name="install-template-submit" disabled="">
				</form>
				<?php
				}
				?>
				<br><br><br>
			</div>
		</div>
		<?php
	}

	/**
	 *	Display Inbound Pro Settings page
	 */
	public static function display_settings() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

		self::render_fields( 'inbound-pro-settings' );
	}


	/**
	 *	Display Sidebar
	 */
	public static function display_sidebar() {


		?>

		<!-- 	Nav to Tools -->
		<?php
		self::display_social_ctas();

		?>

		<?php
	}

	/**
	 *  Display latest news from Inbound Now
	 */
	public static function display_blog_posts() {
		$blogs = Inbound_API_Wrapper::get_blog_posts();
		?>
		<ul class="unstyled">
			<!--- Show blog posts --->
			<?php
			$i=0;
			$limit = 20;
			foreach ($blogs as $item) {
				if ($i>5) {
					break;
				}

				$excerpt = explode('The post' ,  $item['description']);
				$excerpt = $excerpt[0];

				?>
				<div class="all-80 small-50 tiny-50">
					<h6 class='sidebar-h6'><?php echo $item['title']; ?></h6>
					<!--<img class="half-bottom-space" src="holder.js/1200x600/auto/ink" alt="">-->
					<p><a href='<?php echo $item['guid']; ?>' target='_blank'><?php _e( 'Read more &#8594;' , 'inbound-pro'); ?></a></p>
				</div>
				<?php
				$i++;
			}
			?>
		</ul>
		<?php
	}


	public static function display_social_ctas() {
		?>

		<a href="https://twitter.com/inboundnow" class="twitter-follow-button" data-show-count="false">Follow @inboundnow</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		<iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:116px;' allowTransparency='true'>
		</iframe>
		<?php
	}

	/**
	 *	Display Footer
	 */
	public static function display_footer() {
		$docs = Inbound_API_Wrapper::get_docs();
		if (!$docs) {
			$docs = array();
		}
		?>


		<footer class="clearfix pro-footer">
			<div class="ink-grid">
				<ul class="unstyled inline half-vertical-space">
					<li class="active"><a href="http://www.inboundnow.com" target='_blank'><?php _e( 'Inbound Now' , 'inbound-pro' ); ?></a></li>
					<li class="active"><a href="http://www.twitter.com/inboundnow" target='_blank'><?php _e( 'Twitter' , 'inbound-pro' ); ?></a></li>
					<li class="active"><a href="http://www.github.com/inboundnow" target='_blank'><?php _e( 'GitHub' , 'inbound-pro' ); ?></a></li>
					<li class="active"><a href="http://support.inboundnow.com" target='_blank'><?php _e( 'Support' , 'inbound-pro' ); ?></a></li>
					<li class="active"><a href="http://docs.inboundnow.com" target='_blank'><?php _e( 'Documentation' , 'inbound-pro' ); ?></a></li>
					<li class="active"><a href="http://www.inboundnow.com/translate-inbound-now/" target='_blank'><?php _e( 'Translations' , 'inbound-pro' ); ?></a></li>
				</ul>
			</div>
		</footer>
		<?php
	}

	/**
	 *	Render About InboundNow Nav
	 */
	static function display_nav_menu() {

		$pages_array = array(
			'inbound-pro-setup' => __( 'Core Settings' , 'inbound-pro' ),
			'inbound-pro-settings' => __( 'Extension Settings' , 'inbound-pro' ),
			'inbound-pro-import-export' => __( 'Import/Export Settings' , 'inbound-pro' ),
			'inbound-pro-welcome' => __( 'Welcome Screen' , 'inbound-pro' ),
			'inbound-my-account' => __( 'My Account' , 'inbound-pro' )
		);

		$pages_array = apply_filters( 'inbound_pro_nav' , $pages_array );

		echo '<header class="vertical-space">';
		echo '	<h1><img src="'. INBOUND_PRO_URLPATH . 'assets/images/logos/inbound-now-logo.png" style="width:262px;" title="' . __( 'Inbound Now Professional Suite' , 'inbound-pro' ) .'"></h1>';
		echo ' 	<nav class="ink-navigation">';
		echo ' 		<ul class="menu horizontal black">';

		foreach ($pages_array as $key => $value) {

			if ($key=='inbound-my-account') {
				echo '<li class=""><a target="_blank" href="https://www.inboundnow.com/account">'.$value.'</a></li>';
				continue;
			}

			$active = ( self::$tab === $key) ? 'active' : '';
			echo '<li class="'.$active.'"><a href="'.esc_url(admin_url(add_query_arg( array( 'tab' => $key , 'page' => 'inbound-pro' ), 'admin.php' ) ) ).'">';
			echo $value;
			echo '</a>';
			echo '</li>';
		}

		echo '		</ul>';
		echo '	</nav>';
		echo '</header>';

	}

	/**
	 *  Displays search filter
	 */
	public static function display_search() {
		?>
		<div class="">
			<div class='templates-filter-group'>
				<input class="filter-search" type="search" placeholder="<?php _e(' Search Settings... ' , 'inbound-pro' ); ?>">
			</div>
		</div>

		<?php
	}

	/**
	 *  Renders settings given a fields dataset
	 *  @param STRING $fieldgroup_key identification key for which field group to render
	 */
	public static function render_fields( $page ) {
		echo '<div class="wrap">';
		/* render search filter */
		self::display_search();

		echo '	<div id="grid" class="container-fluid">';

		self::$settings_fields[ $page ] = (isset(self::$settings_fields[ $page ])) ? self::$settings_fields[ $page ] : array();

		if (isset($_GET['debug']) && $_GET['debug'] == 2) {
			echo '<pre>';
			print_r(self::$settings_fields[ $page ]);
			echo '</pre>';
		}

		foreach( self::$settings_fields[ $page ] as $priority => $group ) {
			echo '<div class="inbound-settings-group " data-keywords="'.$group['keywords'].','.$group['group_name'].'" data-group-name="'.$group['group_name'].'" id="'.$group['group_name'].'">';
			foreach( $group['fields'] as $field ) {

				/* get value if available else set to default */
				$field['default'] =  (isset($field['default'])) ? $field['default'] : '';
				$field['class'] =  (isset($field['class'])) ? $field['class'] : '';
				$field['value'] = (isset(self::$settings_values[ $group['group_name'] ][ $field['id'] ])) ? self::$settings_values[ $group['group_name'] ][ $field['id'] ] : $field['default'];

				if (isset($_GET['debug'])) {
					echo '<pre>';
					echo $group['group_name'];
					echo '<br>';
					print_r($field);
					echo '</pre>';
					continue;
				}

				/* rend field */
				self::render_field( $field , $group );

			}

			echo '</div>';
		}
		echo '  </div>';
		echo '</div>';
	}

	/**
	 *  Renders label and input given field dataset
	 *  @param ARRAY $field dataset containing field information
	 *  @param ARRAY $group dataset containing fieldgroup information
	 */
	public static function render_field( $field , $group ) {

		if (isset($field['reveal']) && is_array($field['reveal'])) {
			$data = ' data-reveal-selector="'.$field['reveal']['selector'].'" data-reveal-value="'.$field['reveal']['value'].'" ';
		} else {
			$data = '';
		}

		/* prepare additional classes */
		if ( isset($field['hidden']) && $field['hidden'] ) {
			$field['class'] = $field['class'] . ' hidden';
		}

		/* run class variable through filter */
		$field['class'] = apply_filters('inbound-settings/field/class', $field['class'] );

		echo '<div class="inbound-setting '.$field['class'].' " '.$data.' data-field-id="'.$field['id'].'" id="field-'.$field['id'].'">';
		switch($field['type']) {
			// text
			case 'api-key':

				echo '<div class="api-key">';
				echo '	<label>'.__('Inbound API Key:' , 'inbound-pro' ) .'</label>';
				echo '		<input type="text" class="api" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.$field['placeholder'].'" value="'.$field['value'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"  data-special-handler="true"/>';
				echo '</div>';
				echo '<div class="api-key-reauth">';
				echo '		<span class="ink-button blue" id="reauthorize-api-key" />' . __('Rebuild Permissions' , 'inbound-pro') . '</span>';
				echo '</div>';
				break;
			case 'header':
				$extra = (isset($field['default'])) ? $field['default'] : '';
				echo '<h3 class="inbound-header">'.$extra.'</h3>';
				break;
			case 'sub-header':
				$extra = (isset($field['default'])) ? $field['default'] : '';
				echo '<h4 class="inbound-header">'.$extra.'</h4>';
				break;
			case 'oauth-button':
				$data = '';
				$oauth_class = '';
				$unauth_class = '';

				/* build data attriubtes */
				foreach ( $field['oauth_map'] as $key => $selector ) {
					$data .= " data-".$key."='".$selector."'";
				}

				/* discover if access key and access token already exists */
				if (isset(self::$settings_values[ $group['group_name'] ][ 'oauth' ])) {
					$params['action'] = 'revoke_oauth_tokens';
					$params['group'] = $group['group_name'];
					$oauth_class = "hidden";
					$unauth_class = "";
				} else {
					$oauth_class = "";
					$unauth_class = "hidden";
				}
				echo '<button class="ink-button orange unauth thickbox '.$unauth_class.'" id="'.$field['id'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'" '.$data.' >'.__( 'Un-Authorize' , 'inbound-pro' ).'</button>';
				$class = 'hidden';

				$params['action'] = 'request_access_token';
				$params['group'] = $group['group_name'];
				$params['oauth_urls'] = (isset($field['oauth_urls'])) ? $field['oauth_urls'] : '';
				$params['oauth_map'] = $field['oauth_map'];
				$params['TB_iframe'] = 'true';
				$params['width'] = '800';
				$params['height'] = '500';

				echo '<a href="'. admin_url( add_query_arg( $params , 'admin.php' ) ) .'"  class="ink-button green oauth thickbox '.$oauth_class.'" id="'.$field['id'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'" '.$data.' >'.__( 'Authorize' , 'inbound-pro' ).'</a>';


				break;
			case 'text':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';
				echo '	<div class="inbound-text-field">';
				echo '		<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ) .'"  value="'.$field['value'].'" size="30"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'" '.( isset( $field['readonly'] ) ? 'readonly' : '' ) .'/>';
				echo '	</div>';
				echo '	<div class="inbound-tooltip-field">';
				echo '		<i class="inbound-tooltip fa fa-question-circle tool_text" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			case 'number':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';
				echo '	<div class="inbound-text-field">';
				echo '		<input type="number" min="0" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ) .'"  value="'.$field['value'].'" size="2"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
				echo '	</div>';
				echo '	<div class="inbound-tooltip-field">';
				echo '		<i class="inbound-tooltip fa fa-question-circle tool_text" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// ol
			case 'ol':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label><strong>'.$field['label'] .'</strong></label>';
				echo '	</div><br>';
				echo '	<div class="inbound-ol-field">';
				echo '		<ol>';
				foreach ($field['options'] as $option) {
					echo '		<li>'. $option. '</li>';
				}
				echo '		</ol>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// ul
			case 'ul':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label><strong>'.$field['label'] .'</strong></label>';
				echo '	</div><br>';
				echo '	<div class="inbound-ul-field">';
				echo '		<ul>';
				foreach ($field['options'] as $option) {
					echo '		<li>'. $option. '</li>';
				}
				echo '		</ul>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// textarea
			case 'textarea':
				echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'">'.$field['value'].'</textarea>
						<i class="inbound-tooltip fa-question-circle tool_textarea" title="'.$field['description'].'"></i>';
				break;
			// wysiwyg
			case 'wysiwyg':
				wp_editor( $field['value'], $field['id'], $settings = array() );
				echo	'<span class="description">'.$field['description'].'</span><br><br>';
				break;
			// media
			case 'media':

				echo '<label for="upload_image">';
				echo '<input name="'.$field['id'].'"	id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
				echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
				echo '<br /><i class="inbound-tooltip fa-question-circle tool_media" title="'.$field['description'].'"></i>';
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
					echo '<td><input type="checkbox" name="'.$field['id'].'[]" id="'.$field['id'].'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','  data-field-type="'.$field['type'].'"  data-field-group="'.$group['group_name'].'"/>';
					echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
					if ($i==4)
					{
						echo "</tr>";
					}
					$i++;
				}
				echo "</table>";
				echo '<br><i class="inbound-tooltip fa-question-circle tool_checkbox" title="'.$field['description'].'"></i>';
				break;
			// radio
			case 'radio':

				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';

				echo '	<div class="inbound-radio-field">';
				foreach ($field['options'] as $value=>$label) {
					//echo $meta.":".$field['id'];
					//echo "<br>";
					echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
					echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo '	</div>';

				echo '	<div class="inbound-tooltip-field">';
				echo '		<br /><i class="inbound-tooltip fa fa-question-circle tool_dropdown" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';


				break;
			// select
			case 'dropdown':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';
				echo '	<div class="inbound-dropdown-field">';

				echo '		<select name="'.$field['id'].'" id="'.$field['id'].'"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'">';
				/* no objects allowed - bug fix */
				$field['value'] = (!is_object($field['value'])) ? $field['value'] : '';

				foreach ($field['options'] as $value=>$label) {
					echo '		<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
				}

				echo '		</select>';
				echo '	</div>';
				echo '	<div class="inbound-tooltip-field">';
				echo '		<br /><i class="inbound-tooltip fa fa-question-circle tool_dropdown" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';
				break;
			case 'html':
				echo $field['value'];
				echo ( !empty($field['description']) ) ? $field['description'] : '';

				if ( isset($field['callback']) ) {
					if (is_array($field['callback'])) {
						call_user_func(
							array($field['callback'][0], $field['callback'][1]),
							$field
						);
					} else {
						call_user_func(	$field['callback'] , $field	);
					}
				}

				break;
			case 'custom-fields-repeater':
				$fields = Leads_Field_Map::get_lead_fields();
				$fields = Leads_Field_Map::prioritize_lead_fields( $fields );
				$field_types = Leads_Field_Map::build_field_types_array();
				$mandatory = array('wpleads_email_address','wpleads_first_name','wpleads_last_name');

				/* add hide/show toggle */
				echo '<div class="toggle-custom-fields-container">';
				echo '	<label><input type="checkbox" data-special-handler="true" id="show-hide-disabled-custom-fields"> ' .__('Show Disabled Fields' , 'inbound-pro') . '</label>';
				echo '</div>';
				echo '<div class="repeater-custom-fields">';
				echo '		<div class="map-row-headers column-group">';
				echo '			<div class="map-key-header all-5">';
				echo '				<th>' . __( 'Order' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-5">';
				echo '				<th>' . __( 'Delete' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-5">';
				echo '				<th>' . __( 'Enable' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-25">';
				echo '				<th>' . __( 'Field Key' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-30">';
				echo '			<th>' . __( 'Field Label' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-20">';
				echo '			<th>' . __( 'Field Type' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo ' 		</div>';

				echo ' 	<form data="'.$field['type'].'" id="custom-fields-form">';
				echo ' 	<ul class="field-map" id="field-map">';

				foreach( $fields as $key => $field ) {

					$read_only =  (isset($field['enable']) && $field['enable'] == 'off') ? 'readonly' : '';


					echo '	<li class="map-row custom-fields-row column-group '.$read_only.'"  status-priority="'.$key.'">';
					echo '		<div class="map-handle all-5">';
					echo '			<span class="drag-handle">';
					echo '				<input type="hidden" class="field-priority" name="fields['.$field['key'].'][priority]" value="'.$key.'">';
					echo '				<i class="fa fa-arrows"></i>';
					echo '			</span>';
					echo '		</div>';
					echo '		<div class="map-actions all-5">';
					echo '			<div class="edit-btn-group ">';
					if (!isset($field['nature']) || $field['nature'] != 'core') {
						echo '			<span class="fa fa-trash ink-button green  delete-custom-field" id="remove-field"></span>';
						echo '			<span class="ink-button red delete-custom-field-confirm hidden" id="remove-field-confirm" title="confirm">'.__( 'Confirm' , ' inbound-pro' ).'</span>';

					} else {
						echo '			<i class="fa fa-lock" title="'.__( 'This field cannot be deleted' , 'inbound-pro' ).'"></i>';
					}
					echo '			</div>';
					echo '		</div>';
					echo '		<div class="map-handle all-5">';
					if (!in_array($field['key'],$mandatory)) {
						echo '				<input type="hidden" class="toggle-lead-field" name="fields['.$field['key'].'][enable]" value="off" data-special-handler="true"  data-field-type="mapped-field">';
						echo '				<input type="checkbox" class="toggle-lead-field" name="fields['.$field['key'].'][enable]" '. ( !isset($field['enable']) || $field['enable'] == 'on' ? 'checked="true"' : '' ) .' data-special-handler="true"  data-field-type="mapped-field">';
					} else {
						echo '<i class="fa fa-lock" aria-hidden="true" title="'.__('This field cannot be disbled.','inbound-pro').'"></i>';
					}
					echo '		</div>';
					echo '		<div class="map-key all-25">';
					echo '				<input '.$read_only.' type="text" class="field-key" data-special-handler="true" data-field-type="mapped-field" name="fields['.$field['key'].'][key]" value="'.$field['key'].'" '. ( isset($field['nature']) && $field['nature'] == 'core' ? 'disabled' : '' ) .' required>';
					echo '		</div>';
					echo '		<div class="map-label all-30">';
					echo '				<input '.$read_only.' type="text" class="field-label" data-special-handler="true" data-field-type="mapped-field"  name="fields['.$field['key'].'][label]" value="'.$field['label'].'" required>';
					echo '		</div>';
					echo '		<div class="map-label all-20">';
					echo '				<select '.$read_only.' type="text" class="field-type" data-special-handler="true" data-field-type="mapped-field"  name="fields['.$field['key'].'][type]"  '. ( isset($field['nature']) && $field['nature'] == 'core' ? 'disabled' : '' ) .'>';

					foreach ( $field_types as $type => $label ) {
						echo 				'<option value="'.$type.'" '.( isset($field['type']) && $field['type'] == $type ? 'selected="selected"' : '' ).'>'.$label.'</option>';
					}

					echo '				</select>';
					echo '		</div>';
					echo '	</li>';
				}

				echo '	</ul>';
				echo '	</form>';
				echo '	<form id="add-new-custom-field-form">';
				echo '	<div class="map-row-addnew column-group">';
				echo '		<div class="map-handle all-5 ">';
				echo '			<span class="drag-handle">';
				echo '				<i class="fa fa-arrows"></i>';
				echo '			</span>';

				echo '		</div>';
				echo '		<div class="map-key all-25">';
				echo '				<input type="text"  name="fields[new][key]" data-special-handler="true" id="new-key" placeholder="'.__('Enter field key here' , 'inbound-pro' ).'" required>';
				echo '		</div>';
				echo '		<div class="map-label all-30">';
				echo '				<input type="text" name="fields[new][label]" data-special-handler="true" id="new-label" placeholder="'.__('Enter field label here' , 'inbound-pro' ).'" required>';
				echo '		</div>';

				echo '		<div class="map-label all-20">';
				echo '				<select type="text" class="field-type" data-special-handler="true" id="new-type"  name="fields[new][type]">';
				foreach ( $field_types as $type => $label ) {
					echo 				'<option value="'.$type.'">'.$label.'</option>';
				}
				echo '				</select>';
				echo '		</div>';
				echo '		<div class="map-actions all-30">';
				echo '			<div class="edit-btn-group">';
				echo '				<button type="submit" class="ink-button blue" id="add-custom-field">'.__( 'Create new field' , ' inbound-pro' ).'</button>';
				echo '			</div>';
				echo '		</div>';
				echo '	</div>';
				echo '	</form>';
				echo '</div>';
				BREAK;
			case 'lead-status-repeater':
				$statuses = Inbound_Leads::get_lead_statuses();

				echo '<div class="repeater-lead-statuses">';
				echo '	<h4>'.__('Manage Lead Statuses:' , 'inbound-pro' ) .'</h4>';

				echo '		<div class="status-row-headers column-group">';
				echo '			<div class="status-key-header all-5">';
				echo '				<th> </th>';
				echo '			</div>';
				echo '			<div class="status-key-header all-25">';
				echo '				<th>' . __( 'Label' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="status-key-header all-30">';
				echo '			<th>' . __( 'Color' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="status-key-header all-15">';
				echo '			<th>' . __( 'Action' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo ' 		</div>';

				echo ' 	<form data="'.$field['type'].'" id="lead-statuses-form">';
				echo ' 	<ul class="lead-statuses ui-sortable" id="lead-statuses">';


				foreach( $statuses as $key => $status ) {

					echo '	<li class="status-row column-group"  status-priority="'.$status['priority'].'">';
					echo '		<div class="map-handle all-5">';
					echo '			<span class="drag-handle">';
					echo '				<i class="fa fa-arrows"></i>';
					echo '			</span>';
					echo '		</div>';
					echo '		<div class="map-key all-25">';
					echo '				<input type="hidden" class="status-priority" name="statuses['.$status['key'].'][priority]" value="'.$status['priority'].'">';
					echo '				<input type="hidden" class="status-key" name="statuses['.$status['key'].'][key]" value="'.$status['key'].'">';
					echo '				<input type="text" class="status-label" data-special-handler="true" data-field-type="lead-status-field" name="statuses['.$status['key'].'][label]" value="'.$status['label'].'" '. ( isset($status['nature']) && $status['nature'] == 'core' ? '' : '' ) .' required>';
					echo '		</div>';
					echo '		<div class="map-label all-30">';
					echo '				<input type="text" class="status-colorpicker form-control" data-special-handler="true" data-field-type="lead-status-field"  name="statuses['.$status['key'].'][color]" value="'.$status['color'].'" required>';
					echo '		</div>';
					echo '		<div class="map-actions all-30">';

					echo '			<div class="edit-btn-group ">';
					echo '				<span class="ink-button red delete-lead-status '.( !isset($status['nature']) || $status['nature'] != 'core'  ? '' : 'hidden' ).'" id="remove-field">'.__( 'remove' , ' inbound-pro' ).'</span>';
					echo '			</div>';
					echo '			<div class="edit-btn-group ">';
					echo '				<span class="ink-button red delete-lead-status-confirm hidden" id="remove-status-confirm">'.__( 'confirm removal' , ' inbound-pro' ).'</span>';
					echo '			</div>';

					echo '		</div>';
					echo '	</li>';
				}

				echo '	</ul>';
				echo '	</form>';
				echo '	<form id="add-new-lead-status-form">';
				echo '	<div class="map-row-addnew column-group">';
				echo '		<div class="map-handle all-5 ">';
				echo '			<span class="drag-handle">';
				echo '				<i class="fa fa-arrows"></i>';
				echo '			</span>';

				echo '		</div>';
				echo '		<div class="map-label all-30">';
				echo '				<input type="text" name="statuses[new][label]" data-special-handler="true" id="new-status-label" placeholder="'.__('Enter field label here' , 'inbound-pro' ).'" required>';
				echo '		</div>';

				echo '		<div class="map-color all-25">';
				echo '				<input type="text"  name="statuses[new][color]" class="status-colorpicker" data-special-handler="true" id="new-status-color" placeholder="'.__('Color' , 'inbound-pro' ).'" required>';
				echo '		</div>';

				echo '		<div class="map-actions all-30">';
				echo '		</div>';
				echo '	</div>';
				echo '	<div class="map-row-addnew">';
				echo '		<div class="edit-btn-group all-30">';
				echo '				<button type="submit" class="ink-button blue" id="add-lead-status">'.__( 'create status' , ' inbound-pro' ).'</button>';
				echo '		</div>';
				echo '	</div>';
				echo '	</form>';
				echo '</div>';
				BREAK;

			case 'ip-address-repeater':
				self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
				$ip_addresses = ( isset(self::$settings_values['inbound-analytics-rules']['ip-addresses']) && self::$settings_values['inbound-analytics-rules']['ip-addresses'] ) ? self::$settings_values['inbound-analytics-rules']['ip-addresses'] : array('') ;

				echo '<div class="repeater-ip-addresses">';
				echo '	<h4>'.__('Exclude IPs from Tracking:' , 'inbound-pro' ) .'</h4>';
				echo ' 	<form data="'.$field['type'].'" id="ip-addresses-form">';
				echo ' 		<ul class="field-ip-addresses" id="field-ip-address">';


				foreach( $ip_addresses as $key => $ip_address ) {

					echo '		<li class="ip-address-row column-group '. ( !$ip_address ? 'hidden' : '' ) .'"  data-priority="'.$key.'">';
					echo '			<div class="ip-address all-70">';
					echo '				<input type="ip-address" class="field-ip-address" data-special-handler="true" data-field-type="ip-address" name="ip-addresses[]" value="'.$ip_address.'" required>';
					echo '			</div>';
					echo '			<div class="ip-address-actions all-20">';

					echo '				<div class="edit-btn-group ">';
					echo '					<span class="ink-button red delete-ip-address '.( !isset($field['nature']) || $field['nature'] != 'core'  ? '' : 'hidden' ).'" id="remove-field">'.__( 'remove' , ' inbound-pro' ).'</span>';
					echo '				</div>';
					echo '				<div class="edit-btn-group ">';
					echo '					<span class="ink-button red delete-ip-address-confirm hidden" id="remove-ip-confirm">'.__( 'confirm removal' , ' inbound-pro' ).'</span>';
					echo '				</div>';

					echo '			</div>';
					echo '		</li>';
				}

				echo '		</ul>';
				echo '	</form>';

				/* add new ip address html */
				echo '	<form id="add-new-ip-address-form">';
				echo '		<div class="ip-address-row-addnew column-group">';
				echo '			<div class="ip-address all-80">';
				echo '					<input type="text"  name="ip-addresses" data-special-handler="true" id="new-ip-address" placeholder="'.__('Enter IP Address ' , 'inbound-pro' ).'" required>';
				echo '			</div>';
				echo '			<div class="ip-address-actions all-20">';
				echo '				<div class="edit-btn-group">';
				echo '					<button type="submit" class="ink-button blue" id="add-ip-address">'.__( 'add new ip address' , ' inbound-pro' ).'</button>';
				echo '				</div>';
				echo '			</div>';
				echo '		</div>';
				echo '	</form>';

				echo '</div>';
				break;
		} //end switch
		echo '</div>';

	}

	/**
	 *  Ajax listener for saving updated field data
	 */
	public static function ajax_update_settings() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings[ $data['fieldGroup'] ][ $data['name'] ] = $data['value'];
		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );

		do_action('inbound-settings/after-field-value-update' , $data );

		/* echo id of field being modified for js to use in callback */
		echo $data['name'];exit;
	}

	/**
	 *  Ajax listener for saving updated custom field data
	 */
	public static function ajax_update_custom_fields() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings[ 'leads-custom-fields' ] =  $data;

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}

	/**
	 *  Ajax listener for saving updated custom field data
	 */
	public static function ajax_export_settings() {

		/* GETTING CORRECT FILE PATH */
		$path = INBOUND_PRO_UPLOADS_PATH . 'settings/';
		$filename = "inbound-settings-".date("m.d.y.") . substr( md5(rand()), 0, 7);


		if(!file_exists($path)){
			mkdir($path, 0755, true);
			$indexphp = fopen( $path. 'index.php' ,"x+");
			fclose($indexphp);
		}

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/json");
		header("Content-Disposition: attachment; filename=".$path."/".$filename.".json");
		header("Expires: 0");
		header("Pragma: public");

		$file = @fopen($path."/".$filename.".json","a");

		if(!$file){
			$returnArray = array(
				'status' => 0,
				'error' => 'Unable to create file. Please check you uploads folder permission!!.',
				'url' => ''
			);
			die(json_encode($returnArray));
		}

		/* Get Setting */
		$settings = get_option( 'inbound-pro' , array() );

		/* unset configuration memory */
		unset($settings['configuration']);

		fwrite($file, json_encode($settings));
		fclose($file);

		die( INBOUND_PRO_UPLOADS_URLPATH . 'settings/' . $filename.".json" );

	}

	/**
	 *  Ajax listener for saving updated custom field data
	 */
	public static function ajax_update_lead_statuses() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

		$settings[ 'lead-statuses' ] =  $data;
		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}

	/**
	 *  Ajax listener for saving updated ip addresses to not track
	 */
	public static function ajax_update_ip_addresses() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		$ip_addresses = array_filter($data['ip-addresses']);
		$ip_addresses = array_map('trim',$ip_addresses);

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings['inbound-analytics-rules'][ 'ip-addresses' ] = $ip_addresses;

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}

	/**
	 * listen for request to install / uninstall fast ajax
	 */
	public static function ajax_toggle_fast_ajax( $data ) {

		if ( !isset($data['toggle-fast-ajax']) ) {
			return;
		}

		switch ( $data['toggle-fast-ajax'] ) {
			case "on":
				self::install_mu_plugin_fast_ajax();
				break;
			case "off":
				self::install_mu_plugin_fast_ajax( true );
				break;
		}
	}

	/**
	 * Listen for command to import settings from uploaded json file
	 */
	public static function import_settings_from_json() {
		if (!isset($_POST['inbound-action'])) {
			return;
		}

		if ($_GET['tab'] != 'inbound-pro-import-export') {
			return;
		}

		$file_name = $_FILES['jsonfile']['name'];
		$file_size = $_FILES['jsonfile']['size'];
		$file_temp = $_FILES['jsonfile']['tmp_name'];
		$file_ext = strtolower(end(explode('.', $file_name)));

		if ($file_ext != 'json') {
			die(__('Error: This is not an Inbound Now Settings .json file.','inbound-pro'));
		}

		$json = file_get_contents($_FILES["jsonfile"]["tmp_name"]);
		$settings_array = json_decode($json,true);

		if (!is_array($settings_array)) {
			die(__('Error: json file is corrupt.','inbound-pro'));
		}

		/* get current settings */
		$settings = get_option( 'inbound-pro' , array() );

		/* set configuration memory */
		$settings_array['configuration'] = $settings['configuration'];

		/* update with new imported settings */
		update_option( 'inbound-pro' , $settings_array );

	}

	/**
	 * Installs fast-ajax.php mu plugin
	 */
	public static function install_mu_plugin_fast_ajax( $delete = false ) {

		$mu_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
		$mu_dir = untrailingslashit( $mu_dir );

		$source = INBOUND_PRO_PATH . 'assets/mu-plugins/fast-ajax.php';
		$dest = $mu_dir . '/fast-ajax.php';

		$result = array( 'status' => 'OK', 'error' => '' );

		if ( file_exists( $dest ) && !unlink( $dest ) ) {
			$result['error'] = sprintf(
				__( '<strong>Error!</strong> Could not remove the mu plugin.', 'inbound-pro' ),
				$dest );
			$result['status'] = 'ERROR';
		} else {

			/* delete only */
			if ($delete) {
				return;
			}

			// INSTALL
			if ( !wp_mkdir_p( $mu_dir ) ) {
				$result['error'] = sprintf(
					__( '<strong>Error!</strong> The following directory could not be created: <code>%s</code>.', 'nelioab' ),
					$mu_dir );
				$result['status'] = 'ERROR';
			}
			if ( $result['status'] !== 'ERROR' && !copy( $source, $dest ) ) {
				$result['error'] = sprintf(
					__( '<strong>Error!</strong> Could not copy Nelio\'s performance MU-Plugin from <code>%1$s</code> to <code>%2$s</code>.', 'nelioab' ),
					$source, $dest );
				$result['status'] = 'ERROR';
			}

			/* if there was an error installing set to off */
			if ($result['status'] == 'ERROR') {
				/* Update Setting */
				$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
				$settings[ $data['fieldGroup'] ][ $data['name'] ] = 'Off';
				Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
			}
		}


		header( 'Content-Type: application/json' );
		echo 'toggle-fast-ajax';
		die();
	}
}

Inbound_Pro_Settings::init();
