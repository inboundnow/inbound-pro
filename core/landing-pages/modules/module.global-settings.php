<?php


//define main tabs and bind display functions
if (isset($_GET['page'])&&($_GET['page']=='lp_global_settings'&&$_GET['page']=='lp_global_settings'))
{
	add_action('admin_init','lp_global_settings_enqueue');
	function lp_global_settings_enqueue()
	{
		wp_enqueue_style('lp-css-global-settings-here', LANDINGPAGES_URLPATH . 'css/admin-global-settings.css');
	}
}

/**
 * Add action links in Plugins table
 */
add_filter( 'plugin_action_links_landing-pages/landing-pages.php', 'landing_page_plugin_action_links' );
function landing_page_plugin_action_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'edit.php?post_type=landing-page&page=lp_global_settings' ) . '">' . __( 'Settings', 'ts-fab' ) . '</a>'
		),
		$links
	);

}

/**
 * Add meta links in Plugins table
 */

add_filter( 'plugin_row_meta', 'landing_pages_plugin_meta_links', 10, 2 );
function landing_pages_plugin_meta_links( $links, $file ) {

	$plugin = 'landing-pages/landing-pages.php';

	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="http://www.inboundnow.com/membership-packages/">Upgrade to Pro</a>' )
		);
	}
	return $links;
}

function lp_get_global_settings() {
	global $lp_global_settings;

	/* Setup Main Navigation Tab and Settings */
	$tab_slug = 'lp-main';
	$lp_global_settings[$tab_slug]['label'] = 'Global Settings';


	$lp_global_settings[$tab_slug]['settings'] = array(
		array(
			'id'  => 'lp_global_settings_main_header',
			'type'  => 'header',
			'default'  => __('<h4>Landing Pages Core Settings</h4>' , 'landing-pages') ,
			'options' => null
		),
		array(
			'id'  => 'landing-page-permalink-prefix',
			'label' => __( 'Default Landing Page Permalink Prefix' , 'landing-pages'),
			'description' => __("Enter in the <span style='color:red;'>prefix</span> for landing page URLs (aka permalinks).<br><br>This is the URL Slug that will be in the landing page URL.<br><br> Example: http://www.yoursite.com/<span style='color:red;'>PREFIX</span>/landing-page .  Enter in a single word like 'go'" , 'landing-pages') ,
			'type'  => 'text',
			'default'  => 'go',
			'options' => null
		),
		array(
			'id'  => 'landing-page-rotation-halt',
			'label' => __('Sticky Variations' , 'landing-pages'),
			'description' => __("With this setting enabled the landing pages plugin will prevent landing page version a/b rotation for a specific visitor that has viewed the page.<br><br>This pause on the a/b rotation will automatically expire after 30 days." , 'landing-pages'),
			'type'  => 'radio',
			'default'  => '0',
			'options' => array('1'=>'on','0'=>'off')
		),
		array(
			'id'  => 'inbound_compatibility_mode',
			'label' => 'Turn on compability mode',
			'description' => "<p>This option turns on compability mode for the inbound now plugins. This is typically used if you are experiencing bugs caused by third party plugin conflicts.</p>",
			'type'  => 'radio',
			'default'  => '0',
			'options' => array('1'=>'On','0'=>'Off')
		),
		array(
			'id'  => 'landing-page-disable-turn-off-ab',
			'label' => __('Turn Off AB Testing?' , 'landing-pages') ,
			'description' => __("This will disable the AB testing functionality of your landing pages. This is to comply with Googles new PPC regulations with redirects. After saving this option <a href='/wp-admin/options-permalink.php'>visit this page to flush/reset your permalinks</a>" , 'landing-pages'),
			'type'  => 'radio',
			'default'  => '0',
			'options' => array('0'=>'No Keep it on','1'=>'Yes turn AB testing Off')
		)
	);


	/* Setup License Keys Tab */
	$tab_slug = 'lp-license-keys';
	$lp_global_settings[$tab_slug]['label'] = __( 'License Keys' , 'landing-pages');

	/* Setup Extensions Tab */
	$lp_global_settings['lp-extensions']['label'] = __( 'Extensions' , 'landing-pages');
	$lp_global_settings['lp-extensions']['settings'] = array(
													array(
														'id'  => 'lp-ext-header',
														'type'  => 'header',
														'default'  => '',
														'options' => null
													)
												);

	/* Setup Debug Tab */

	$lp_global_settings['lp-debug']['label'] = __( 'Debug' , 'landing-pages');
	$lp_global_settings['lp-debug']['settings'] = array(
													array(
														'id'  => 'lp-debug-header',
														'type'  => 'header',
														'default'  => '',
														'options' => null
													)
												);

	$lp_global_settings = apply_filters('lp_define_global_settings',$lp_global_settings);

	return $lp_global_settings;
}


/* Add Extensions License Key Header if Extensions are present */
add_filter('lp_define_global_settings', 'lp_add_extension_license_key_header', 2, 1);
function lp_add_extension_license_key_header($lp_global_settings) {
	if (array_key_exists('lp-license-keys',$lp_global_settings)) {
		$lp_global_settings['lp-license-keys']['settings'][] = 	array(
				'id'  => 'extensions-license-keys-header',
				'description' => __( "Head to http://www.inboundnow.com/ to retrieve your license key for this template." , 'landing-pages'),
				'type'  => 'header',
				'default' => '<h3 class="lp_global_settings_header">'. __( 'Extension Licensing' , 'landing-pages') .'</h3>'
		);
	}

	return $lp_global_settings;
}

/* Provide backwards compatibility for older data array model */
add_filter('lp_define_global_settings','lp_rebuild_old_global_settings_configurations_to_suit_new_convention', 99, 1);
function lp_rebuild_old_global_settings_configurations_to_suit_new_convention($lp_global_settings)
{
	//print_r($lp_global_settings);exit;
	foreach ($lp_global_settings as $parent_tab => $aa)
	{
		if (is_array($aa))
		{

			foreach ($aa as $k=>$aaa)
			{
				/* change 'options' key to 'settings' */
				if ($k=='options')
				{
					if (is_array($aaa))
					{
						foreach ($aaa as $kk => $aaaa)
						{
							$lp_global_settings[$parent_tab]['settings'][] = $aaaa;
						}
					}
					unset($lp_global_settings[$parent_tab][$k]);
				}

			}
		}
	}
	return $lp_global_settings;
}

function lp_display_global_settings_js()
{
	if (isset($_GET['tab']))
	{
		$default_id = $_GET['tab'];
	}
	else
	{
		$default_id ='lp-main';
	}
	?>
	<script type='text/javascript'>
		jQuery(document).ready(function()
		{
			//jQuery('#<? echo $default_id; ?>').css('display','block');
			//jQuery('#<? echo $default_id; ?>').css('display','block');
			 setTimeout(function() {
				var getoption = document.URL.split('&option=')[1];
				var showoption = "#" + getoption;
				jQuery(showoption).click();
			}, 100);

			jQuery('.lp-nav-tab').live('click', function() {
				var this_id = this.id.replace('tabs-','');
				//alert(this_id);
				jQuery('.lp-tab-display').css('display','none');
				jQuery('#'+this_id).css('display','block');
				jQuery('.lp-nav-tab').removeClass('nav-tab-special-active');
				jQuery('.lp-nav-tab').addClass('nav-tab-special-inactive');
				jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');
				jQuery('#id-open-tab').val(this_id);
			});
			var form_sys = jQuery("#sys-inbound-form");
			jQuery("#in-sys-info").after(form_sys);
			jQuery("#sys-inbound-form").show();
		});
	</script>
	<?php
}

function lp_display_global_settings()
{
	global $wpdb;


	$lp_global_settings = lp_get_global_settings();
	$htaccess = "";
	if ( (isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') === false) && file_exists( get_home_path() . ".htaccess" ) ) {
		$htaccess_file = get_home_path() . "/.htaccess";
		$f             = fopen( $htaccess_file, 'r' );
		$contentht     = fread( $f, filesize( $htaccess_file ) );
		$contentht     = esc_textarea( $contentht );

		if ( !is_writable( $htaccess_file ) ) {
			$content = " <div class=\"error\"><h3>" . __( "Oh no! Your .htaccess is not writable and A/B testing won't work unless you make your .htaccess file writable.", 'landing-pages') . "</h3></div>";
			echo $content;
			}
		else {
			$htaccess = '<textarea readonly="readonly" onclick="this.focus();this.select()" style="width: 90%;" rows="15" name="robotsnew">' . $contentht . '</textarea><br/>';
		}
	}
	//print_r($lp_global_settings);
	$active_tab = 'lp-main';
	if (isset($_REQUEST['open-tab']))
	{
		$active_tab = $_REQUEST['open-tab'];
	}


	do_action('lp_pre_display_global_settings');

	lp_display_global_settings_js();
	lp_save_global_settings();

	echo '<h2 class="nav-tab-wrapper">';

	foreach ($lp_global_settings as $key => $data)
	{
		$label = (isset($data['label'])) ? $data['label'] : 'Main';
		?>
		<a  id='tabs-<?php echo $key; ?>' class="lp-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $label; ?></a>
		<?php
	}

	echo "</h2><div class='lp-settings-tab-sidebar'>";

	echo "<div class='lp-sidebar-settings'><h2 style='font-size:16px;'>Like the Plugin? Leave us a review</h2><center><a class='review-button' href='http://wordpress.org/support/view/plugin-reviews/landing-pages?rate=5#postform' target='_blank'>Leave a Quick Review</a></center><small>Reviews help constantly improve the plugin & keep us motivated! <strong>Thank you for your support!</strong></small></div><div class='lp-sidebar-settings'><h2>Help keep the plugin up to date, awesome & free!</h2><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
		<input type='hidden' name='cmd' value='_s-xclick'>
		<input type='hidden' name='hosted_button_id' value='GKQ2BR3RKB3YQ'>
		<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
		<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'></form>
		<small>Spare some change? Buy us a coffee/beer.<strong> We appreciate your continued support.</strong></small></div><div class='lp-sidebar-settings'><h2 style='font-size:18px;'>Follow Updates on Facebook</h2><iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'></iframe></div></div>";
	echo "<form action='edit.php?post_type=landing-page&page=lp_global_settings' method='POST'>
	<input type='hidden' name='nature' value='lp-global-settings-save'>
	<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";


	foreach ($lp_global_settings as $key => $data)
	{
		lp_render_global_settings($key,$data['settings'], $active_tab);
	}

	echo '<div style="float:left;padding-left:9px;padding-top:20px;">
			<input type="submit" value="Save Settings" tabindex="5" id="lp-button-create-new-group-open" class="button-primary" >
		</div>';
	echo "</form>";
	?>
	<div id="lp-additional-resources" class="clear">
		<hr>
	<div id="more-templates">
		<center>
		<a href="http://www.inboundnow.com/products/landing-pages/templates/" target="_blank"><img src="<?php echo LANDINGPAGES_URLPATH;?>/images/templates-image.png"></a>

		</center>
	</div>
	<div id="more-addons">
		<center>
		<a href="http://www.inboundnow.com/products/landing-pages/extensions/" target="_blank"><img src="<?php echo LANDINGPAGES_URLPATH;?>/images/add-on-image.png"></a>
	</center>
	</div>
	<div id="custom-templates">
		<center><a href="http://dev.inboundnow.com/submit-a-work-request/" target=="_blank"><img src="<?php echo LANDINGPAGES_URLPATH;?>/images/custom-setup-image.png"></a>
		</center>
	</div>
	</div>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var debug = jQuery("#php-sql-lp-version");
	   jQuery(debug).prependTo("#lp-debug");
	   jQuery("#php-sql-lp-version").show();
	 });

	</script>
	<div id="php-sql-lp-version" style="display:none;">
	<div id="inbound-install-status">
	 <h3><?php _e( 'Installation Status' , 'landing-pages'); ?></h3>
		  <table  id="lp-wordpress-site-status">

			<tr valign="top">
			   <th scope="row"><label><?php _e( 'PHP Version' , 'landing-pages'); ?></label></th>
				<td class="installation_item_cell">
					<strong><?php echo phpversion(); ?></strong>
				</td>
				<td>
					<?php
						if(version_compare(phpversion(), '5.3.3', '>')){
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/tick.png"/>
							<?php
						}
						else{
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/cross.png"/>
							<span class="installation_item_message"><?php _e("Landing Pages requires PHP 5 or above.", "gravityforms"); ?></span>
							<?php
						}
					?>
				</td>
			</tr>
			<tr valign="top">
			   <th scope="row"><label><?php _e( 'MySQL Version' , 'landing-pages'); ?></label></th>
				<td class="installation_item_cell">
					<strong><?php echo $wpdb->db_version();?></strong>
				</td>
				<td>
					<?php
						if(version_compare($wpdb->db_version(), '5.0.0', '>')){
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/tick.png"/>
							<?php
						}
						else{
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/cross.png"/>
							<span class="installation_item_message"><?php _e("Gravity Forms requires MySQL 5 or above.", "gravityforms"); ?></span>
							<?php
						}
					?>
				</td>
			</tr>
			<tr valign="top">
			   <th scope="row"><label><?php _e( 'WordPress Version' , 'landing-pages'); ?></label></th>
				<td class="installation_item_cell">
					<strong><?php echo get_bloginfo("version"); ?></strong>
				</td>
				<td>
					<?php
						if(version_compare(get_bloginfo("version"), '3.6', '>')){
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/tick.png"/>
							<?php
						}
						else{
							?>
							<img src="<?php echo LANDINGPAGES_URLPATH;?>/images/cross.png"/>
							<span class="installation_item_message"><?php _e( 'landing pages requires version X or higher' , 'landing-pages'); ?></span>
							<?php
						}
					?>
				</td>
			</tr>
			 <tr valign="top">
			   <th scope="row"><label><?php _e( 'Landing Page Version' , 'landing-pages'); ?></label></th>
				<td class="installation_item_cell">
					<strong><?php _e('Version' , 'landing-pages'); ?> <?php echo LANDINGPAGES_CURRENT_VERSION;?></strong>
				</td>
				<td>

				</td>
			</tr>
		</table>
		</div>
		<div id="inbound-sys-info">
			<span id="in-sys-info"></span>
		</div>
		<div id="htaccess-contents">

		<?php if ($htaccess != "") {
			echo "<h3>". __('The contents of your .htaccess file' , 'landing-pages') .":</h3>";
			echo $htaccess;
		}	?>
		</div>
	</div>
<?php
}

add_action('admin_footer', 'landing_pages_load_sys_info');
function landing_pages_load_sys_info($hook)
{
	global $wpdb;
	$screen = get_current_screen();
	//echo $screen->id;
	if ( $screen->id != 'landing-page_page_lp_global_settings')
	        return; // exit if incorrect screen id

	if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}

	// Try to identifty the hosting provider
	$host = false;
	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	}

	?>

	<form id="sys-inbound-form" action="<?php echo esc_url( admin_url( 'edit.php?post_type=landing-page&page=lp_global_settings' ) ); ?>" method="post" dir="ltr">
	<h2><?php _e( 'System Information', 'inboundnow' ) ?></h2>
	<input type="hidden" name="inbound-action" value="inbound-download-sysinfo" />
	<style type="text/css">#inbound-download-sysinfo {display: none;}</style>
	<?php submit_button( __( 'Download System Info File for Support Requests', 'inboundnow' ), 'primary', 'inbound-download-sysinfo', false ); ?>
<textarea readonly="readonly" onclick="this.focus();this.select()" id="copy-inbound-info" name="landing_pages_sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'edd' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

Multisite:					<?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:					<?php echo site_url() . "\n"; ?>
HOME_URL:					<?php echo home_url() . "\n"; ?>

Landing Page Version:		<?php echo LANDINGPAGES_CURRENT_VERSION . "\n"; ?>
Upgraded From:				<?php echo get_option( 'lp_version_upgraded_from', 'None' ) . "\n"; ?>
WordPress Version:			<?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:			<?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:				<?php echo $theme . "\n"; ?>
<?php if( $host ) : ?>
Host:						<?php echo $host . "\n"; ?>
<?php endif; ?>

Registered Post Stati:			<?php echo implode( ', ', get_post_stati() ) . "\n\n"; ?>

PHP Version:				<?php echo PHP_VERSION . "\n"; ?>
MySQL Version:				<?php echo mysql_get_server_info( $wpdb->dbh ) . "\n"; ?>
Web Server Info:				<?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Safe Mode:				<?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:			<?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Upload Max Size:		<?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:			<?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:		<?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:				<?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:			<?php echo ini_get( 'max_input_vars' ) . "\n"; ?>

WP_DEBUG:				<?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:				<?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:				<?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:				<?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Page For Posts:				<?php $id = get_option( 'page_for_posts' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>

Session:						<?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:				<?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:					<?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:					<?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:				<?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:			<?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

WordPress Memory Limit:		NA
DISPLAY ERRORS:			<?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:				<?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'edd' ) : __( 'Your server does not support fsockopen.', 'edd' ); ?><?php echo "\n"; ?>
cURL:						<?php echo ( function_exists( 'curl_init' ) ) ? __( 'Your server supports cURL.', 'edd' ) : __( 'Your server does not support cURL.', 'edd' ); ?><?php echo "\n"; ?>
SOAP Client:					<?php echo ( class_exists( 'SoapClient' ) ) ? __( 'Your server has the SOAP Client enabled.', 'edd' ) : __( 'Your server does not have the SOAP Client enabled.', 'edd' ); ?><?php echo "\n"; ?>
SUHOSIN:					<?php echo ( extension_loaded( 'suhosin' ) ) ? __( 'Your server has SUHOSIN installed.', 'edd' ) : __( 'Your server does not have SUHOSIN installed.', 'edd' ); ?><?php echo "\n"; ?>

- INSTALLED LP TEMPLATES:
<?php
// Show templates that have been copied to the theme's edd_templates dir
$dir = LANDINGPAGES_UPLOADS_PATH. '/*';
if (!empty($dir)){
foreach ( glob( $dir ) as $file ) {
	echo "Template: " . basename( $file ) . "\n";
}
}
else {
echo 'No overrides found';
}
?>

- ACTIVE PLUGINS:
<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
// If the plugin isn't active, don't show it.
if ( ! in_array( $plugin_path, $active_plugins ) )
	continue;

echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

if ( is_multisite() )
{
	?>

	- NETWORK ACTIVE PLUGINS:

	<?php
	$plugins = wp_get_active_network_plugins();
	$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

	foreach ( $plugins as $plugin_path )
	{
		$plugin_base = plugin_basename( $plugin_path );

		// If the plugin isn't active, don't show it.
		if ( ! array_key_exists( $plugin_base, $active_plugins ) )
			continue;

		$plugin = get_plugin_data( $plugin_path );

		echo $plugin['Name'] . ' :' . $plugin['Version'] ."\n";
	}

}


?>

### End System Info ###</textarea>
</form>
	<?php
}

add_action( 'init', 'inboundnow_generate_sysinfo_download' );
//Generates the System Info Download File
function inboundnow_generate_sysinfo_download() {

	if (isset($_POST['inbound-action']) && $_POST['inbound-action'] === 'inbound-download-sysinfo') {
		nocache_headers();
		header( "Content-type: text/plain" );
		header( 'Content-Disposition: attachment; filename="inbound-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['landing_pages_sysinfo'] );
		inbound_die();
	}

}

function inbound_die()
{
	add_filter( 'wp_die_ajax_handler', '_edd_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_edd_die_handler', 10, 3 );
	wp_die('');
}

function lp_save_global_settings()
{

	$lp_global_settings = lp_get_global_settings();

	if (!isset($_POST['nature'])) {
		return;
	}

	foreach ($lp_global_settings as $key=>$data)
	{
		$tab_settings = $lp_global_settings[$key]['settings'];
		// loop through fields and save the data
		foreach ($tab_settings as $field)
		{
			$field['id']  = $key."-".$field['id'];

			if (array_key_exists('option_name',$field) && $field['option_name'] ) {
				$field['id'] = $field['option_name'];
			}

			$field['old_value'] = get_option($field['id'] );
			(isset($_POST[$field['id'] ]))? $field['new_value'] = $_POST[$field['id'] ] : $field['new_value'] = null;

			if (( $field['new_value'] !== null) && ( $field['new_value'] !== $field['old_value'] ))
			{
				update_option($field['id'] ,$field['new_value']);
				if ($field['id'] =='main-landing-page-permalink-prefix')
				{
					//echo "here";
					global $wp_rewrite;
					$wp_rewrite->flush_rules();
				}
				if ($field['type']=='license-key')
				{
					$master_key = get_option('inboundnow_master_license_key' );

					if ($master_key) {
						$field['new_value'] = $master_key;
					}

					$api_params = array(
						'edd_action'=> 'activate_license',
						'license' 	=> $field['new_value'],
						'item_name' =>  $field['slug'] ,
						'cache_bust'=> substr(md5(rand()),0,7)
					);

					$response = wp_remote_get( add_query_arg( $api_params, LANDINGPAGES_STORE_URL ), array( 'timeout' => 30, 'sslverify' => false ) );


					if ( is_wp_error( $response ) ) {
						break;
					}


					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					$license_status = update_option('lp_license_status-'.$field['slug'], $license_data->license);

				}
			}
			else if ($field['new_value'] == null)
			{
				if ($field['type']=='license-key')
				{
					$master_key = get_option('inboundnow_master_license_key' );

					if ($master_key)
					{
						$bool = update_option($field['id'], $master_key );
						$license_status = update_option('lp_license_status-'.$field['slug'], '');
					}
					else
					{
						update_option($field['id'], '' );
						$license_status = update_option('lp_license_status-'.$field['slug'], '');
					}
				}
			}

			//exit;
			do_action('lp_save_global_settings',$field);
		} // end foreach
		//exit;
	}
}


function lp_render_global_settings($key,$custom_fields,$active_tab)
{
	if (!$custom_fields)
		return;

	$master_license_key = get_option('inboundnow_master_license_key' , '');

	if ($key==$active_tab)
	{
		$display = 'block';
	}
	else
	{
		$display = 'none';
	}

	//echo $display;

	// Use nonce for verification
	echo "<input type='hidden' name='lp_{$key}_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";

	// Begin the field table and loop
	echo '<table class="lp-tab-display" id="'.$key.'" style="display:'.$display.'">';
	//print_r($custom_fields);exit;
	foreach ($custom_fields as $field) {
		//echo $field['type'];exit;
		//print_r($field);
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

		$field['value'] = get_option($field['id'] , $default);

		// begin a table row with
		echo '<tr><th class="lp-gs-th" valign="top" style="font-weight:300;">';
			if ($field['type']=='header')
			{
				echo $field['default'];
			}
			else
			{
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
					echo '<input type="text" class="jpicker" name="'.$field['id'] .'" id="'.$field['id'] .'" value="'.$field['value'].'" size="5" />
							<div class="lp_tooltip tool_color" title="'.$field['description'].'"></div>';
					continue 2;
				case 'datepicker':
					echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="'.$field['id'] .'" id="'.$field['id'] .'" value="'.$field['value'].'" size="8" />
							<div class="lp_tooltip tool_date" title="'.$field['description'].'"></div><p class="description">'.$field['description'].'</p>';
					continue 2;
				case 'license-key':
					if ($master_license_key)
					{
						$field['value'] = $master_license_key;
						$input_type = 'hidden';
					}
					else
					{
						$input_type = 'text';
					}

					$license_status = lp_check_license_status($field);

					echo '<input  type="'.$input_type.'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['value'].'" size="30" />';


					echo '<input type="hidden" name="lp_license_status-'.$field['slug'].'" id="'.$field['id'] .'" value="'.$license_status.'" size="30" />';

					if ($license_status=='valid')
					{
						echo '<div class="lp_license_status_valid">Enabled</div>';
					}
					else
					{
						echo '<div class="lp_license_status_invalid">Disabled</div>';
					}

					echo '<div class="lp_tooltip tool_text" title="'.$field['description'].'"></div>';

					continue 2;
				case 'text':
					echo '<input type="text" name="'.$field['id'] .'" id="'.$field['id'] .'" value="'.$field['value'].'" size="30" />
							<div class="lp_tooltip tool_text" title="'.$field['description'].'"></div>';
					continue 2;
				// textarea
				case 'textarea':
					echo '<textarea name="'.$field['id'] .'" id="'.$field['id'] .'" cols="106" rows="6">'.$field['value'].'</textarea>
							<div class="lp_tooltip tool_textarea" title="'.$field['description'].'"></div>';
					continue 2;
				// wysiwyg
				case 'wysiwyg':
					wp_editor( $field['value'], $field['id'] , $settings = array() );
					echo	'<span class="description">'.$field['description'].'</span><br><br>';
					continue 2;
				// media
					case 'media':
					//echo 1; exit;
					echo '<label for="upload_image">';
					echo '<input name="'.$field['id'] .'"  id="'.$field['id'] .'" type="text" size="36" name="upload_image" value="'.$field['value'].'" />';
					echo '<input class="upload_image_button" id="uploader_'.$field['id'] .'" type="button" value="Upload Image" />';
					echo '<br /><div class="lp_tooltip tool_media" title="'.$field['description'].'"></div>';
					continue 2;
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
							echo '<td><input type="checkbox" name="'.$field['id'] .'[]" id="'.$field['id'] .'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','/>';
							echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
						if ($i==4)
						{
							echo "</tr>";
						}
						$i++;
					}
					echo "</table>";
					echo '<br><div class="lp_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
					continue 2;
				// radio
				case 'radio':
					foreach ($field['options'] as $value=>$label) {
						//echo $meta.":".$field['id'] ;
						//echo "<br>";
						echo '<input type="radio" name="'.$field['id'] .'" id="'.$field['id'] .'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','/>';
						echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo '<div class="lp_tooltip tool_radio" title="'.$field['description'].'"></div>';
					continue 2;
				// select
				case 'dropdown':
					echo '<select name="'.$field['id'] .'" id="'.$field['id'] .'">';
					foreach ($field['options'] as $value=>$label) {
						echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
					}
					echo '</select><br /><div class="lp_tooltip tool_dropdown" title="'.$field['description'].'"></div>';
					continue 2;
				case 'html':
					//print_r($field);
					echo $field['default'];
				continue 2;



			} //end switch

			do_action('lp_render_global_settings',$field);

		echo '</td></tr>';
	} // end foreach
	echo '</table>'; // end table
}


function lp_check_license_status($field)
{

	$date = date("Y-m-d");
	$cache_date = get_option($field['id']."-expire");
	$license_status = get_option('lp_license_status-'.$field['slug']);

	if (isset($cache_date)&&($date<$cache_date)&&$license_status=='valid')
	{
		return "valid";
	}

	if ($field['value'])
	{
		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $field['value'],
			'key' => $field['value'],
			'item_name' => urlencode( $field['slug'] ),
			'cache_bust'=> substr(md5(rand()),0,7)
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, LANDINGPAGES_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'valid' ) {
			$newDate = date('Y-m-d', strtotime($license_data->expires) );
			update_option($field['id']."-expire", $newDate);
			return 'valid';
			// this license is still valid
		} else {
			return 'invalid';
		}
	}
	else
	{
		return 'invalid';
	}
}

