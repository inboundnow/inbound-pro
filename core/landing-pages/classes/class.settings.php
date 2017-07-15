<?php

/**
 * Class provides a data interface for retrieving and storing landing page settings into the GPL legacy setting system or the Inbound Pro settings system.
 * @package LandingPages
 * @subpackage DataInterfaces
 */

class Landing_Pages_Settings {

    /**
     * initiate class
     */
    public function __construct() {
        self::load_hooks();
    }

    /**
     * Load hooks and filters
     */
    public static function load_hooks() {

        /* load settings scripts and styles */
        add_action('admin_enqueue_scripts', array( __CLASS__ , 'enqueue_settings_scripts' ) );

        /* display system info */
        add_action('admin_footer', array( __CLASS__ , 'display_system_info' ) );

        /* download system info  */
        add_action( 'admin_init', array( __CLASS__ , 'download_system_info' ) );

        /*  Add settings to inbound pro  */
        add_filter('inbound_settings/extend', array( __CLASS__  , 'define_pro_settings' ) );
    }

    /**
     *  Adds pro admin settings
     */
    public static function define_pro_settings( $settings ) {
        global $inbound_settings;

        $settings['inbound-pro-setup'][] = array(
            'group_name' => LANDINGPAGES_PLUGIN_SLUG ,
            'keywords' => __('landing pages, landers, permalinks,sticky,ab testing' , 'inbound-pro'),
            'fields' => array (
                array(
                    'id'  => 'header-landing-page',
                    'type'  => 'header',
                    'default'  => __('Landing Pages Settings', 'inbound-pro' ),
                    'options' => null
                ),
                array(
                    'id'  => 'landing-page-permalink-prefix',
                    'label'  => __('Permalink Prefix', 'inbound-pro' ),
                    'description'  => __("The prefix for landing page URL permalink. Example: http://www.yoursite.com/PREFIX/landing-page. Inbound Now also provides an extension that will remove the prefix if desired in the extensions area." , 'inbound-pro') ,
                    'type'  => 'text',
                    'default'  => 'go',
                ),
                array(
                    'id'  => 'landing-page-rotation-halt',
                    'label' => __('Sticky Variations' , 'inbound-pro'),
                    'description' => __("With this setting enabled the landing pages plugin will prevent landing page version a/b rotation for a specific visitor that has viewed the page for 30 days." , 'inbound-pro'),
                    'type'  => 'radio',
                    'default'  => '0',
                    'options' => array('1'=>'on','0'=>'off')
                ),
                array(
                    'id'  => 'landing-page-disable-turn-off-ab',
                    'label' => __('Turn Off AB Testing?' , 'inbound-pro') ,
                    'description' => __("This will disable the AB testing functionality of your landing pages. This is to comply with Googles new PPC regulations with redirects. After saving this option <a href='/wp-admin/options-permalink.php'>visit this page to flush/reset your permalinks</a>" , 'inbound-pro'),
                    'type'  => 'radio',
                    'default'  => '0',
                    'options' => array('0'=>'No Keep it on','1'=>'Yes turn AB testing Off')
                ),
                array(
                    'id'  => 'landing-page-enable-featured-image',
                    'label' => __('Enable Featured Images' , 'inbound-pro') ,
                    'description' => __("Enable this setting if you plan to include the landing-page post type in any frontend post archives that leverages the featured image system." , 'inbound-pro'),
                    'type'  => 'radio',
                    'default'  => '0',
                    'options' => array('0'=>__('Off','inbound-pro'), '1'=> __('On' , 'inbound-pro') )
                )
            )

        );


        return $settings;
    }

    /**
     * Loads default global settings & extends them
     */
    public static function get_stand_alone_settings() {
        global $lp_global_settings;

        /* Setup Main Navigation Tab and Settings */
        $tab_slug = 'lp-main';
        $lp_global_settings[$tab_slug]['label'] = __( 'Global Settings' , 'inbound-pro' );


        $lp_global_settings[$tab_slug]['settings'] = array(
            array(
                'id'  => 'lp_global_settings_main_header',
                'type'  => 'header',
                'default'  => __('Landing Pages Core Settings' , 'inbound-pro') ,
                'options' => null
            ),
            array(
                'id'  => 'landing-page-permalink-prefix',
                'label' => __( 'Default Landing Page Permalink Prefix' , 'inbound-pro'),
                'description' => __("Enter in the <span style='color:red;'>prefix</span> for landing page URLs (aka permalinks).<br><br>This is the URL Slug that will be in the landing page URL.<br><br> Example: http://www.yoursite.com/<span style='color:red;'>PREFIX</span>/landing-page .  Enter in a single word like 'go'" , 'inbound-pro') ,
                'type'  => 'text',
                'default'  => 'go',
                'options' => null
            ),
            array(
                'id'  => 'landing-page-rotation-halt',
                'label' => __('Sticky Variations' , 'inbound-pro'),
                'description' => __("With this setting enabled the landing pages plugin will prevent landing page version a/b rotation for a specific visitor that has viewed the page.<br><br>This pause on the a/b rotation will automatically expire after 30 days." , 'inbound-pro'),
                'type'  => 'radio',
                'default'  => '0',
                'options' => array('1'=>'on','0'=>'off')
            ),
            array(
                'id'  => 'landing-page-disable-turn-off-ab',
                'label' => __('Turn Off AB Testing?' , 'inbound-pro') ,
                'description' => __("This will disable the AB testing functionality of your landing pages. This is to comply with Googles new PPC regulations with redirects. After saving this option <a href='/wp-admin/options-permalink.php'>visit this page to flush/reset your permalinks</a>" , 'inbound-pro'),
                'type'  => 'radio',
                'default'  => '0',
                'options' => array('0'=>'No Keep it on','1'=>'Yes turn AB testing Off')
            ),
            array(
                'id'  => 'landing-page-enable-featured-image',
                'label' => __('Enable Featured Images' , 'inbound-pro') ,
                'description' => __("Enable this setting if you plan to include the landing-page post type in any frontend post archives that leverages the featured image system." , 'inbound-pro'),
                'type'  => 'radio',
                'default'  => '0',
                'options' => array('0'=>__('Off','inbound-pro'), '1'=> __('On' , 'inbound-pro') )
            )
        );


        if (
            !defined('INBOUND_ACCESS_LEVEL')
            ||
            ( defined('INBOUND_ACCESS_LEVEL') && INBOUND_ACCESS_LEVEL < 1 )
        ) {
            /* Setup License Keys Tab */
            $lp_global_settings['lp-license-keys']['label'] = __('API Key Setup', 'inbound-pro');
            $lp_global_settings['lp-license-keys']['settings'][] = array(
                'id' => 'extensions-license-keys-header',
                'description' => __("Head to http://www.inboundnow.com/account to retrieve your API key for this template.", 'inbound-pro'),
                'type' => 'header',
                'default' => '<h3 class="lp_global_settings_header">' . __('Inbound API Key', 'inbound-pro') . '</h3>'
            );
        }

        if (!defined('INBOUND_ACCESS_LEVEL') ) {
            /* Setup Extensions Tab */
            $lp_global_settings['lp-extensions']['label'] = __( 'Extensions' , 'inbound-pro');
            $lp_global_settings['lp-extensions']['settings'] = array(
                array(
                    'id'  => 'lp-ext-header',
                    'type'  => 'header',
                    'default'  => '',
                    'options' => null
                )
            );
        }

        /* Setup Debug Tab */
        $lp_global_settings['lp-debug']['label'] = __( 'Debug' , 'inbound-pro');
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

    /**
     * Get setting value from DB. Handles stand alone landing pages plugin differently from Inbound Pro included landing pages plugin
     * @param $field_id
     * @param $default
     * @return mixed
     */
    public static function get_setting( $field_id , $default ) {
        global $inbound_settings;
        $value = $default;

        if (defined('INBOUND_PRO_CURRENT_VERSION')) {
            $field_id = str_replace('lp-main-' , '', $field_id );
            $value = (isset($inbound_settings['landing-pages'][$field_id])) ? $inbound_settings['landing-pages'][$field_id] : $default;
        } else {
            $value = get_option( $field_id, $default );
        }

        return $value;
    }


    /**
     * Enqueue scripts and styles for settings page
     */
    public static function enqueue_settings_scripts() {

        if ( !isset($_GET['page'])  || $_GET['page']!='lp_global_settings'  ) {
            return;
        }

        wp_enqueue_style('lp-css-global-settings-here', LANDINGPAGES_URLPATH . 'assets/css/admin/global-settings.css', array() , null);
        wp_enqueue_script('lp-settings-js', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.global-settings.js', array() , null);

        /* load ToolTipster */
        wp_enqueue_style('tooltipster', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/css/tooltipster.css', array() , null);
        wp_enqueue_style('tooltipster-theme', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/css/themes/tooltipster-noir.css', array() , null);
        wp_enqueue_script('tooltipster', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/ToolTipster/js/jquery.tooltipster.min.js', array() , null);
    }

    /**
     * Add action links in Plugins table
     */
    public static function extend_plugin_quicklinks( $links ) {

        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'edit.php?post_type=landing-page&page=lp_global_settings' ) . '">' . __( 'Settings', 'ts-fab' ) . '</a>'
            ),
            $links
        );
    }


    /**
     * Displays global settings container
     */
    public static function display_settings() {
        global $wpdb;


        $lp_global_settings = self::get_stand_alone_settings();

        $htaccess = "";
        if ((isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') === false) && file_exists(get_home_path() . ".htaccess")) {
            $htaccess_file = get_home_path() . "/.htaccess";
            $f = fopen($htaccess_file, 'r');
            $contentht = fread($f, filesize($htaccess_file));
            $contentht = esc_textarea($contentht);

            if (!is_writable($htaccess_file)) {
                $content = " <div class=\"error\"><h3>" . __("Oh no! Your .htaccess is not writable and A/B testing won't work unless you make your .htaccess file writable.", 'inbound-pro') . "</h3></div>";
                echo $content;
            } else {
                $htaccess = '<textarea readonly="readonly" onclick="this.focus();this.select()" style="width: 90%;" rows="15" name="robotsnew">' . $contentht . '</textarea><br/>';
            }
        }

        
        $active_tab = 'lp-main';
        if (isset($_REQUEST['open-tab'])) {
            $active_tab = sanitize_title($_REQUEST['open-tab']);
        }

        do_action('lp_pre_display_global_settings');

        self::save_stand_alone_settings();

        echo '<h2 class="nav-tab-wrapper">';

        foreach ($lp_global_settings as $key => $data) {
            if (!isset($data['label'])) {
                continue;
            }
            ?>
            <a id='tabs-<?php echo $key; ?>'
               class="lp-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $data['label']; ?></a>
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


        foreach ($lp_global_settings as $key => $data) {
            self::render_stand_alone_settings($key, $data['settings'], $active_tab);
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
                    <a href="http://www.inboundnow.com/marketplace/?show=landing-pages" target="_blank"><img
                            src="<?php echo LANDINGPAGES_URLPATH; ?>assets/images/templates-image.png"></a>

                </center>
            </div>
            <div id="more-addons">
                <center>
                    <a href="http://www.inboundnow.com/marketplace/?show=extensions" target="_blank"><img
                            src="<?php echo LANDINGPAGES_URLPATH; ?>assets/images/add-on-image.png"></a>
                </center>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var debug = jQuery("#php-sql-lp-version");
                jQuery(debug).prependTo("#lp-debug");
                jQuery("#php-sql-lp-version").show();
            });

        </script>
        <div id="php-sql-lp-version" style="display:none;">
            <div id="inbound-install-status">
                <h3><?php _e('Installation Status', 'inbound-pro'); ?></h3>
                <table id="lp-wordpress-site-status">

                    <tr valign="top">
                        <th scope="row"><label><?php _e('PHP Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong><?php echo phpversion(); ?></strong>
                        </td>
                        <td>
                            <?php
                            if (version_compare(phpversion(), '5.3.3', '>')) {
                                ?>
                                ✓
                                <?php
                            } else {
                                ?>
                                &times;
                                <span
                                    class="installation_item_message"><?php _e("Landing Pages requires PHP 5 or above.", "gravityforms"); ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php _e('MySQL Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong><?php echo $wpdb->db_version(); ?></strong>
                        </td>
                        <td>
                            <?php
                            if (version_compare($wpdb->db_version(), '5.0.0', '>')) {
                                ?>
                                ✓
                                <?php
                            } else {
                                ?>
                                &times;
                                <span
                                    class="installation_item_message"><?php _e("Gravity Forms requires MySQL 5 or above.", "gravityforms"); ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php _e('WordPress Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong><?php echo get_bloginfo("version"); ?></strong>
                        </td>
                        <td>
                            <?php
                            if (version_compare(get_bloginfo("version"), '3.6', '>')) {
                                ?>
                                ✓
                                <?php
                            } else {
                                ?>
                               &times;
                                <span
                                    class="installation_item_message"><?php _e('landing pages requires version X or higher', 'inbound-pro'); ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php _e('Landing Page Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong><?php _e('Version', 'inbound-pro'); ?><?php echo LANDINGPAGES_CURRENT_VERSION; ?></strong>
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
                    echo "<h3>" . __('The contents of your .htaccess file', 'inbound-pro') . ":</h3>";
                    echo $htaccess;
                } ?>
            </div>
        </div>
        <?php
    }

    public static function display_system_info($hook) {
        global $wpdb;
        $screen = get_current_screen();

        if ($screen->id != 'landing-page_page_lp_global_settings') {
            return;
        }

        if (get_bloginfo('version') < '3.4') {
            $theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
            $theme = $theme_data['Name'] . ' ' . $theme_data['Version'];
        } else {
            $theme_data = wp_get_theme();
            $theme = $theme_data->Name . ' ' . $theme_data->Version;
        }

        /* Try to identifty the hosting provider */
        $host = false;
        if (defined('WPE_APIKEY')) {
            $host = 'WP Engine';
        } elseif (defined('PAGELYBIN')) {
            $host = 'Pagely';
        }

        ?>

        <form id="sys-inbound-form"
              action="<?php echo esc_url(admin_url('edit.php?post_type=landing-page&page=lp_global_settings')); ?>"
              method="post" dir="ltr">
            <h2><?php _e('System Information', 'inboundnow') ?></h2>
            <input type="hidden" name="inbound-action" value="inbound-download-sysinfo"/>
            <style type="text/css">#inbound-download-sysinfo {
                    display: none;
                }</style>
            <?php submit_button(__('Download System Info File for Support Requests', 'inboundnow'), 'primary', 'inbound-download-sysinfo', false); ?>
            <textarea readonly="readonly" onclick="this.focus();this.select()" id="copy-inbound-info"
                      name="landing_pages_sysinfo"
                      title="<?php _e('To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'edd'); ?>">
                ### Begin System Info ###

                ## Please include this information when posting support requests ##

                Multisite: <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

                SITE_URL: <?php echo site_url() . "\n"; ?>
                HOME_URL: <?php echo home_url() . "\n"; ?>

                Landing Page Version: <?php echo LANDINGPAGES_CURRENT_VERSION . "\n"; ?>
                Upgraded From: <?php echo get_option('lp_version_upgraded_from', 'None') . "\n"; ?>
                WordPress Version: <?php echo get_bloginfo('version') . "\n"; ?>
                Permalink Structure: <?php echo get_option('permalink_structure') . "\n"; ?>
                Active Theme: <?php echo $theme . "\n"; ?>
                <?php if ($host) : ?>
                    Host:                        <?php echo $host . "\n"; ?>
                <?php endif; ?>

                Registered Post Stati: <?php echo implode(', ', get_post_stati()) . "\n\n"; ?>

                PHP Version: <?php echo PHP_VERSION . "\n"; ?>
                MySQL Version: <?php
                                $con=mysqli_connect("localhost","my_user","my_password","my_db");

                                if (mysqli_connect_errno()) {
                                    echo "Failed to connect to MySQL: " . mysqli_connect_error(). "\n";
                                }

                                echo mysqli_get_server_info($con). "\n";

                                ?>
                Web Server Info: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

                PHP Safe Mode: <?php echo ini_get('safe_mode') ? "Yes" : "No\n"; ?>
                PHP Memory Limit: <?php echo ini_get('memory_limit') . "\n"; ?>
                PHP Upload Max Size: <?php echo ini_get('upload_max_filesize') . "\n"; ?>
                PHP Post Max Size: <?php echo ini_get('post_max_size') . "\n"; ?>
                PHP Upload Max Filesize: <?php echo ini_get('upload_max_filesize') . "\n"; ?>
                PHP Time Limit: <?php echo ini_get('max_execution_time') . "\n"; ?>
                PHP Max Input Vars: <?php echo ini_get('max_input_vars') . "\n"; ?>

                WP_DEBUG: <?php echo defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

                WP Table Prefix: <?php echo "Length: " . strlen($wpdb->prefix);
                echo " Status:";
                if (strlen($wpdb->prefix) > 16) {
                    echo " ERROR: Too Long";
                } else {
                    echo " Acceptable";
                }
                echo "\n"; ?>

                Show On Front: <?php echo get_option('show_on_front') . "\n" ?>
                Page On Front: <?php $id = get_option('page_on_front');
                echo get_the_title($id) . ' (#' . $id . ')' . "\n" ?>
                Page For Posts: <?php $id = get_option('page_for_posts');
                echo get_the_title($id) . ' (#' . $id . ')' . "\n" ?>

                Session: <?php echo isset($_SESSION) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
                Session Name: <?php echo esc_html(ini_get('session.name')); ?><?php echo "\n"; ?>
                Cookie Path: <?php echo esc_html(ini_get('session.cookie_path')); ?><?php echo "\n"; ?>
                Save Path: <?php echo esc_html(ini_get('session.save_path')); ?><?php echo "\n"; ?>
                Use Cookies: <?php echo ini_get('session.use_cookies') ? 'On' : 'Off'; ?><?php echo "\n"; ?>
                Use Only Cookies: <?php echo ini_get('session.use_only_cookies') ? 'On' : 'Off'; ?><?php echo "\n"; ?>

                WordPress Memory Limit:		NA
                DISPLAY ERRORS: <?php echo (ini_get('display_errors')) ? 'On (' . ini_get('display_errors') . ')' : 'N/A'; ?><?php echo "\n"; ?>
                FSOCKOPEN: <?php echo (function_exists('fsockopen')) ? __('Your server supports fsockopen.', 'edd') : __('Your server does not support fsockopen.', 'edd'); ?><?php echo "\n"; ?>
                cURL: <?php echo (function_exists('curl_init')) ? __('Your server supports cURL.', 'edd') : __('Your server does not support cURL.', 'edd'); ?><?php echo "\n"; ?>
                SOAP Client: <?php echo (class_exists('SoapClient')) ? __('Your server has the SOAP Client enabled.', 'edd') : __('Your server does not have the SOAP Client enabled.', 'edd'); ?><?php echo "\n"; ?>
                SUHOSIN: <?php echo (extension_loaded('suhosin')) ? __('Your server has SUHOSIN installed.', 'edd') : __('Your server does not have SUHOSIN installed.', 'edd'); ?><?php echo "\n"; ?>

                - INSTALLED LP TEMPLATES:
                <?php
                /* Show templates that have been copied to the theme's edd_templates dir */
                $dir = LANDINGPAGES_UPLOADS_PATH . '/*';
                if (!empty($dir)) {
                    foreach (glob($dir) as $file) {
                        echo "Template: " . basename($file) . "\n";
                    }
                } else {
                    echo 'No overrides found';
                }
                ?>

                - ACTIVE PLUGINS:
                <?php
                $plugins = get_plugins();
                $active_plugins = get_option('active_plugins', array());

                foreach ($plugins as $plugin_path => $plugin) {
                    /* If the plugin isn't active, don't show it. */
                    if (!in_array($plugin_path, $active_plugins)) continue;

                    echo $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
                }

                if (is_multisite()) {
                    ?>

                    - NETWORK ACTIVE PLUGINS:

                    <?php
                    $plugins = wp_get_active_network_plugins();
                    $active_plugins = get_site_option('active_sitewide_plugins', array());

                    foreach ($plugins as $plugin_path) {
                        $plugin_base = plugin_basename($plugin_path);

                        /* If the plugin isn't active, don't show it. */
                        if (!array_key_exists($plugin_base, $active_plugins)) continue;

                        $plugin = get_plugin_data($plugin_path);

                        echo $plugin['Name'] . ' :' . $plugin['Version'] . "\n";
                    }

                }


                ?>

                ### End System Info ###</textarea>
        </form>
        <?php
    }

    /**
     * download sytem info - Not sure this is hooked into anything
     */
    public static function download_system_info() {

        if (isset($_POST['inbound-action']) && $_POST['inbound-action'] === 'inbound-download-sysinfo') {
            nocache_headers();
            header( "Content-type: text/plain" );
            header( 'Content-Disposition: attachment; filename="inbound-system-info.txt"' );

            echo wp_strip_all_tags( $_POST['landing_pages_sysinfo'] );
            wp_die('');
        }

    }

    /**
     * Saves global settings
     */
    public static function save_stand_alone_settings() {

        $lp_global_settings = self::get_stand_alone_settings();

        if (!isset($_POST['nature'])) {
            return;
        }

        foreach ($lp_global_settings as $key => $data) {
            $tab_settings = $lp_global_settings[$key]['settings'];

            /* loop through fields and save the data */
            foreach ($tab_settings as $field) {

                $field['id'] = $key . "-" . $field['id'];

                if (array_key_exists('option_name', $field) && $field['option_name']) {
                    $field['id'] = $field['option_name'];
                }

                if ($field['id'] == 'lp-main-landing-page-permalink-prefix') {
                    /*echo "here"; */
                    global $wp_rewrite;
                    $wp_rewrite->flush_rules();
                }
                if ($field['type']=='inboundnow-license-key') {
                    if (defined('INBOUND_ACCESS_LEVEL')  ) {
                        return;
                    }
                    /* error_log(print_r($field, true)); */
                    $slug = (isset($field['remote_download_slug'])) ? $field['remote_download_slug'] : $field['slug'];
                    $api_params = array(
                        'edd_action' => 'inbound_check_license',
                        'license' =>   sanitize_text_field($_POST['inboundnow_master_license_key']),
                        'item_name' => $slug
                    );

                    /* Call the edd API */
                    $response = wp_remote_get(add_query_arg($api_params, INBOUNDNOW_STORE_URL ), array('timeout' => 30, 'sslverify' => false));

                    /* make sure the response came back okay */
                    if (is_wp_error($response)) {
                        break;
                    }

                    /* decode the license data */
                    $license_data = json_decode(wp_remote_retrieve_body($response));


                    /* $license_data->license will be either "active" or "inactive" */
                    update_option('lp_license_status-' . $field['slug'], $license_data->license);
                } else {
                    if (isset($_POST[$field['id']])) {
                        update_option($field['id'], sanitize_text_field($_POST[$field['id']]));
                    }
                }


                do_action('lp_save_global_settings', $field);
            }
        }
    }

    /**
     * Render Settings
     * @param $key
     * @param $custom_fields
     * @param $active_tab
     */
    public static function render_stand_alone_settings($key, $custom_fields, $active_tab) {
        if (!$custom_fields) {
            return;
        }

        $master_license_key = get_option('inboundnow_master_license_key', '');

        if ($key == $active_tab) {
            $display = 'block';
        } else {
            $display = 'none';
        }

        /*echo $display; */

        /* Use nonce for verification */
        echo "<input type='hidden' name='lp_{$key}_custom_fields_nonce' value='" . wp_create_nonce('lp-nonce') . "' />";

        /* Begin the field table and loop */
        echo '<table class="lp-tab-display" id="' . $key . '" style="display:' . $display . '">';
        /*print_r($custom_fields);exit; */
        foreach ($custom_fields as $field) {

            /* get value of this field if it exists for this post */
            if (isset($field['default'])) {
                $default = $field['default'];
            } else {
                $default = null;

            }

            $field['id'] = $key . "-" . $field['id'];

            if (array_key_exists('option_name', $field) && $field['option_name']) $field['id'] = $field['option_name'];

            $field['value'] = get_option($field['id'], $default);

            /* begin a table row with */
            echo '<tr><th class="lp-gs-th" valign="top" style="font-weight:300;">';
            if ($field['type'] == 'header') {
                echo '<h4>' . $field['default'] . '</h4>';
            } else {
                echo '<div class="inbound-setting-label tooltip" title="' . $field['description'] . '">' . $field['label'] . '</div>';
            }
            echo '</th><td>';

            switch ($field['type']) {
                /* text */
                case 'colorpicker':
                    if (!$field['value']) {
                        $field['value'] = $field['default'];
                    }
                    echo '<input type="text" class="jpicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="5" />';
                    continue 2;
                case 'datepicker':
                    echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="8" />';
                    continue 2;
                case 'inboundnow-license-key':

                    if (defined('INBOUND_PRO_PATH')) {
                        continue;
                    }

                    if ($master_license_key) {
                        $field['value'] = $master_license_key;
                        $input_type = 'hidden';
                    } else {
                        $input_type = 'text';
                    }


                    $license_status = self::check_license_status($field);

                    echo '<input  type="' . $input_type . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="30" />';


                    echo '<input type="hidden" name="lp_license_status-' . $field['slug'] . '" id="' . $field['id'] . '" value="' . $license_status . '" size="30" />';

                    if ($license_status == 'valid') {
                        echo '<div class="lp_license_status_valid">Enabled</div>';
                    } else {
                        echo '<div class="lp_license_status_invalid">Disabled</div>';
                    }

                    continue 2;
                case 'text':
                    echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="30" class=""  />';
                    continue 2;
                /* textarea */
                case 'textarea':
                    echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="106" rows="6"></textarea>';
                    continue 2;
                /* wysiwyg */
                case 'wysiwyg':
                    wp_editor($field['value'], $field['id'], $settings = array());
                    echo '<span class="description">' . $field['description'] . '</span><br><br>';
                    continue 2;
                /* media */
                case 'media':
                    /*echo 1; exit; */
                    echo '<label for="upload_image">';
                    echo '<input name="' . $field['id'] . '"  id="' . $field['id'] . '" type="text" size="36" name="upload_image" value="' . $field['value'] . '" />';
                    echo '<input data-field-id="' . $field['id'] . '" class="upload_image_button" id="uploader_' . $field['id'] . '" type="button" value="'. __( 'Upload Image' , 'inbound-pro' ) .'"/>';
                    continue 2;
                /* checkbox */
                case 'checkbox':
                    $i = 1;
                    echo "<table>";
                    if (!isset($field['value'])) {
                        $field['value'] = array();
                    } elseif (!is_array($field['value'])) {
                        $field['value'] = array($field['value']);
                    }
                    foreach ($field['options'] as $value => $label) {
                        if ($i == 5 || $i == 1) {
                            echo "<tr>";
                            $i = 1;
                        }
                        echo '<td><input type="checkbox" name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="' . $value . '" ', in_array($value, $field['value']) ? ' checked="checked"' : '', '  class="tooltip tool_text" title="' . $field['description'] . '" />';
                        echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label></td>';
                        if ($i == 4) {
                            echo "</tr>";
                        }
                        $i++;
                    }
                    echo "</table>";
                    continue 2;
                /* radio */
                case 'radio':
                    foreach ($field['options'] as $value => $label) {
                        /*echo $meta.":".$field['id'] ; */
                        /*echo "<br>"; */
                        echo '<input type="radio" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" ', $field['value'] == $value ? ' checked="checked"' : '', ' class="tooltip tool_radio" title="' . $field['description'] . '" />';
                        echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label> &nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    continue 2;
                /* select */
                case 'dropdown':
                    echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
                    foreach ($field['options'] as $value => $label) {
                        echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="' . $value . '">' . $label . '</option>';
                    }
                    echo '</select>';
                    continue 2;
                case 'html':
                    echo $field['default'];
                    continue 2;


            } /*end switch */

            do_action('lp_render_global_settings', $field);

            echo '</td></tr>';
        } /* end foreach */
        echo '</table>'; /* end table */
    }

    /**
     * Check license status
     * @param $field
     * @return bool|string
     */
    public static function check_license_status($field) {

        $license_status = get_option('lp_license_status-' . $field['slug']);

        if ( $license_status == 'valid') {
            return "valid";
        } else {
            return "invalid";
        }
    }
}

new Landing_Pages_Settings;