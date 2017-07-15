<?php

/**
 * Class for managing legacy settings area for GPL Calls to Action plugin
 *
 * @package CTA
 * @subpackage GPLSettings
 */

if (!class_exists('CTA_Settings')) {

    class CTA_Settings {

        static $core_settings;
        static $active_tab;

        /**
         *    Initializes class
         */
        public function __construct() {
            self::add_hooks();
        }

        /**
         *    Loads hooks and filters
         */
        public static function add_hooks() {
            add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
            add_filter('plugin_action_links_cta/calls-to-action.php', array(__CLASS__, 'plugin_action_links'));
            /*  Add settings to inbound pro  */
            add_filter('inbound_settings/extend', array( __CLASS__  , 'define_pro_settings' ) );

        }

        /**
         *    Load CSS & JS
         */
        public static function enqueue_scripts() {
            $screen = get_current_screen();

            if ((isset($screen) && $screen->base != 'wp-call-to-action_page_wp_cta_global_settings')) {
                return;
            }

            wp_enqueue_style('wp-cta-css-global-settings-here', WP_CTA_URLPATH . 'assets/css/admin-global-settings.css');
        }

        /**
         *  Adds pro admin settings
         */
        public static function define_pro_settings( $settings ) {
            global $inbound_settings;

            $settings['inbound-pro-setup'][] = array(
                'group_name' => WP_CTA_SLUG ,
                'keywords' => __('cta,call to action,calls to action' , 'inbound-pro'),
                'fields' => array (
                    array(
                        'id'  => 'header-cta',
                        'type'  => 'header',
                        'default'  => __('Call To Action Settings', 'inbound-pro' ),
                        'options' => null
                    ),
                    array(
                        'id' => 'split-testing',
                        'label' => __('Split Testing.', 'inbound-pro'),
                        'description' => __('Enabling this setting may improve server performance at the loss of split testing. Only version A( id 0) will be displayed for every CTA when split testing is disabled.', 'inbound-pro'),
                        'type' => 'radio',
                        'default' => 1,
                        'options' => array(0 => 'Off', 1 => 'On')
                    ),
                    array(
                        'id' => 'sticky-ctas',
                        'label' => __('Sticky CTAs.', 'inbound-pro'),
                        'description' => __('Disabling this will cause call to actions to rotate variations on every page load for the same user.', 'inbound-pro'),
                        'type' => 'radio',
                        'default' => 1,
                        'options' => array(0 => 'Off', 1 => 'On')
                    )
                )

            );


            return $settings;
        }

        /**
         *    Get global setting data
         */
        public static function define_stand_alone_settings() {
            global $wp_cta_global_settings;

            // Setup navigation and display elements
            $tab_slug = 'wp-cta-main';
            $wp_cta_global_settings[$tab_slug]['label'] = 'Global Settings';


            $wp_cta_global_settings[$tab_slug]['settings'] =
                array(
                    array(
                        'id' => 'split-testing',
                        'label' => __('Split Testing.', 'inbound-pro'),
                        'description' => __('Turning this off may improve server performance. Only version A will be displayed for every CTA.', 'inbound-pro'),
                        'type' => 'radio',
                        'default' => 1,
                        'options' => array(0 => 'Off', 1 => 'On')
                    ),

                    array(
                        'id' => 'sticky-ctas',
                        'label' => __('Sticky CTAs.', 'inbound-pro'),
                        'description' => __('Disabling this will cause call to actions to rotate variations on every page load for the same user.', 'inbound-pro'),
                        'type' => 'radio',
                        'default' => 1,
                        'options' => array(0 => 'Off', 1 => 'On')
                    )
                );

            if (
            !defined('INBOUND_ACCESS_LEVEL')
            ) {
                /* Setup License Keys Tab */
                $tab_slug = 'wp-cta-license-keys';
                $wp_cta_global_settings[$tab_slug]['label'] = __('Inbound Now API Key', 'inbound-pro');

                /* Setup Extensions Tab */
                $tab_slug = 'wp-cta-extensions';
                $wp_cta_global_settings[$tab_slug]['label'] = __('Extensions', 'inbound-pro');
            }

            $wp_cta_global_settings = apply_filters('wp_cta_define_global_settings', $wp_cta_global_settings);


            self::$core_settings = $wp_cta_global_settings;

        }

        /**
         * Add action links in Plugins table
         */

        public static function plugin_action_links($links) {

            return array_merge(
                array(
                    'settings' => '<a href="' . admin_url('edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings') . '">' . __('Settings', 'ts-fab') . '</a>'
                ),
                $links
            );

        }

        /**
         *    Displays nav tabs
         */
        public static function display_navigation() {

            self::$active_tab = 'wp-cta-main';
            if (isset($_REQUEST['open-tab'])) {
                self::$active_tab = sanitize_text_field($_REQUEST['open-tab']);
            }

            echo '<h2 class="nav-tab-wrapper">';

            foreach (self::$core_settings as $key => $data) {
                ?>
                <a id='tabs-<?php echo $key; ?>' class="wp-cta-nav-tab nav-tab nav-tab-special<?php echo self::$active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $data['label']; ?></a>
                <?php
            }
            echo "</h2>";

            echo "<form action='edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings' method='POST'>
		<input type='hidden' name='nature' value='wp-cta-global-settings-save'>
		<input type='hidden' name='open-tab' id='id-open-tab' value='" . self::$active_tab . "'>";

        }


        /**
         *    Display sidebar
         */
        public static function display_sidebar() {
            ?>

            <div class='wp-cta-settings-tab-sidebar'>
                <div class='wp-cta-sidebar-settings'>
                    <h2 style='font-size:17px;'>
                        <?php _e('Like the Plugin? Leave us a review', 'inbound-pro'); ?>
                    </h2>
                    <center>
                        <a class='review-button' href='http://wordpress.org/support/view/plugin-reviews/cta?rate=5#postform' target='_blank'>
                            <?php _e('Leave a Review', 'inbound-pro'); ?>
                        </a>
                    </center>
                    <small>
                        <?php _e('Reviews help constantly improve the plugin & keep us motivated! <strong>Thank you for your support!</strong>', 'inbound-pro'); ?>
                    </small>
                </div>
                <div class='wp-cta-sidebar-settings'>
                    <h2>
                        <?php _e('Help keep the plugin up to date, awesome & free!', 'inbound-pro'); ?>
                    </h2>
                    <form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
                        <input type='hidden' name='cmd' value='_s-xclick'>
                        <input type='hidden' name='hosted_button_id' value='GKQ2BR3RKB3YQ'>
                        <input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
                        <img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>
                    </form>
                    <small>
                        <?php _e('Spare some change? Buy us a coffee/beer.<strong> We appreciate your continued support.</strong>', 'inbound-pro'); ?>
                    </small>
                </div>
                <div class='wp-cta-sidebar-settings'>
                    <h2 style='font-size:18px;'>
                        <?php _e('Follow Updates on Facebook', 'inbound-pro'); ?>
                    </h2>
                    <iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'>
                    </iframe>
                </div>
            </div>
            <?php
        }

        /**
         *    Display global settings
         */
        public static function display_stand_alone_settings() {
            global $wpdb;

            self::define_stand_alone_settings();

            self::inline_js();
            self::save_stand_alone_settings();
            self::display_sidebar();
            self::display_navigation();

            foreach (self::$core_settings as $key => $data) {
                if (isset($data['settings'])) {
                    self::save_stand_alone_setting($key, $data['settings']);
                }
            }


            echo '<div style="float:left;padding-left:9px;padding-top:20px;">
				<input type="submit" value="Save Settings" tabindex="5" id="wp-cta-button-create-new-group-open" class="button-primary" >
			</div>';
            echo "</form>";
            ?>

            <div class="clear" id="php-sql-wp-cta-version">
                <h3><?php _e('Installation Status', 'inbound-pro'); ?></h3>
                <table class="form-table" id="wp-cta-wordpress-site-status">

                    <tr valign="top">
                        <th scope="row"><label><?php _e('PHP Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong><?php echo phpversion(); ?></strong>
                        </td>
                        <td>
                            <?php
                            if (version_compare(phpversion(), '5.0.0', '>')) {
                                ?>
                                ✓
                                <?php
                            } else {
                                ?>
                                &times;
                                <span class="installation_item_message"><?php _e("Inbound Now requires PHP 5 or above.", "cta"); ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label>MySQL Version</label></th>
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
                                <span class="installation_item_message"><?php _e("Inbound Now requires MySQL 5 or above.", "cta"); ?></span>
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
                            if (version_compare(get_bloginfo("version"), '3.3', '>')) {
                                ?>
                                ✓
                                <?php
                            } else {
                                ?>
                                &times;
                                <span class="installation_item_message"><?php _e('landing pages requires version X or higher', 'inbound-pro') ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label><?php _e('WordPress CTA Version', 'inbound-pro'); ?></label></th>
                        <td class="installation_item_cell">
                            <strong>Version <?php echo WP_CTA_CURRENT_VERSION; ?></strong>
                        </td>
                        <td>

                        </td>
                    </tr>
                </table>
            </div>
            <?php
        }


        /**
         * Renders setting field for Landing Page stand alone version
         * @param STRING $key tab key
         * @param ARRAY $custom_fields field settings
         */
        public static function save_stand_alone_setting($key, $custom_fields) {

            ($key == self::$active_tab) ? $display = 'block' : $display = 'none';

            if (!$custom_fields) {
                return;
            }

            // Use nonce for verification
            echo "<input type='hidden' name='wp_cta_{$key}_custom_fields_nonce' value='" . wp_create_nonce('wp-cta-nonce') . "' />";

            // Begin the field table and loop
            echo '<table class="wp-cta-tab-display" id="' . $key . '" style="display:' . $display . '">';

            foreach ($custom_fields as $field) {
                // get value of this field if it exists for this post
                if (isset($field['default'])) {
                    $default = $field['default'];
                } else {
                    $default = null;
                }

                $field['id'] = $key . "-" . $field['id'];

                if (array_key_exists('option_name', $field) && $field['option_name'])
                    $field['id'] = $field['option_name'];

                $field['value'] = get_option($field['id'], $default);

                // begin a table row with
                echo '<tr><th class="wp-cta-gs-th options-' . $field['id'] . '" valign="top">';
                if ($field['type'] == 'header') {
                    echo $field['default'];
                } else {
                    echo "<small>" . $field['label'] . "</small>";
                }
                echo '</th><td>';
                switch ($field['type']) {
                    // text
                    case 'colorpicker':
                        if (!$field['value']) {
                            $field['value'] = $field['default'];
                        }
                        echo '<input type="text" class="jpicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="5" />
									<div class="wp_cta_tooltip tool_color" title="' . $field['description'] . '"></div>';
                        break;
                    case 'header':
                        $extra = (isset($field['description'])) ? $field['description'] : '';
                        echo $extra;
                        break;
                    case 'datepicker':
                        echo '<input id="datepicker-example2" class="Zebra_DatePicker_Icon" type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="8" />
									<div class="wp_cta_tooltip tool_date" title="' . $field['description'] . '"></div><p class="description">' . $field['description'] . '</p>';
                        break;
                    case 'text':
                        echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" size="30" />
									<div class="wp_cta_tooltip tool_text" title="' . $field['description'] . '"></div>';
                        break;
                    // textarea
                    case 'textarea':
                        echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="106" rows="6">' . $field['value'] . '</textarea>
									<div class="wp_cta_tooltip tool_textarea" title="' . $field['description'] . '"></div>';
                        break;
                    // wysiwyg
                    case 'wysiwyg':
                        wp_editor($field['value'], $field['id'], $settings = array());
                        echo '<span class="description">' . $field['description'] . '</span><br><br>';
                        break;
                    // media
                    case 'media':

                        echo '<label for="upload_image">';
                        echo '<input name="' . $field['id'] . '"	id="' . $field['id'] . '" type="text" size="36" name="upload_image" value="' . $field['value'] . '" />';
                        echo '<input class="upload_image_button" id="uploader_' . $field['id'] . '" type="button" value="Upload Image" />';
                        echo '<br /><div class="wp_cta_tooltip tool_media" title="' . $field['description'] . '"></div>';
                        break;
                    // checkbox
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
                            echo '<td><input type="checkbox" name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="' . $value . '" ', in_array($value, $field['value']) ? ' checked="checked"' : '', '/>';
                            echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label></td>';
                            if ($i == 4) {
                                echo "</tr>";
                            }
                            $i++;
                        }
                        echo "</table>";
                        echo '<br><div class="wp_cta_tooltip tool_checkbox" title="' . $field['description'] . '"></div>';
                        break;
                    // radio
                    case 'radio':
                        foreach ($field['options'] as $value => $label) {
                            //echo $meta.":".$field['id'];
                            //echo "<br>";
                            echo '<input type="radio" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" ', $field['value'] == $value ? ' checked="checked"' : '', '/>';
                            echo '<label for="' . $value . '">&nbsp;&nbsp;' . $label . '</label> &nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        echo '<div class="wp_cta_tooltip tool_radio" title="' . $field['description'] . '"></div>';
                        break;
                    // select
                    case 'dropdown':
                        echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
                        foreach ($field['options'] as $value => $label) {
                            echo '<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="' . $value . '">' . $label . '</option>';
                        }
                        echo '</select><br /><div class="wp_cta_tooltip tool_dropdown" title="' . $field['description'] . '"></div>';
                        break;
                    case 'html':
                        //print_r($field);
                        echo $field['value'];
                        echo '<br /><div class="wp_cta_tooltip tool_dropdown" title="' . $field['description'] . '"></div>';
                        break;


                } //end switch


                do_action('wp_cta_render_global_settings', $field);

                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
        }

        /**
         *    Renders supporting JS
         */
        public static function inline_js() {

            ?>
            <script type='text/javascript'>
                jQuery(document).ready(function ($) {

                    setTimeout(function () {
                        var getoption = document.URL.split('&option=')[1];
                        var showoption = "#" + getoption;
                        jQuery(showoption).click();
                    }, 100);
                    var getCookieByMatch = function (regex) {
                        var cs = document.cookie.split(/;\s*/), ret = [], i;
                        for (i = 0; i < cs.length; i++) {
                            if (cs[i].match(regex)) {
                                ret.push(cs[i]);
                            }
                        }
                        return ret;
                    };

                    jQuery('.wp-cta-nav-tab').live('click', function () {
                        var this_id = this.id.replace('tabs-', '');
                        //alert(this_id);
                        jQuery('.wp-cta-tab-display').css('display', 'none');
                        jQuery('#' + this_id).css('display', 'block');
                        jQuery('.wp-cta-nav-tab').removeClass('nav-tab-special-active');
                        jQuery('.wp-cta-nav-tab').addClass('nav-tab-special-inactive');
                        jQuery('#tabs-' + this_id).addClass('nav-tab-special-active');
                        jQuery('#id-open-tab').val(this_id);
                    });

                });
            </script>
            <?php

        }


        /**
         *    Listens for POST & saves settings changes
         */
        public static function save_stand_alone_settings() {

            if (!isset($_POST['nature'])) {
                return;
            }

            self::define_stand_alone_settings();

            foreach (self::$core_settings as $key => $data) {
                if (!isset(self::$core_settings[$key]['settings'])) {
                    continue;
                }

                $tab_settings = self::$core_settings[$key]['settings'];

                // loop through fields and save the data
                foreach ($tab_settings as $field) {

                    $field['id'] = $key . "-" . $field['id'];

                    if (array_key_exists('option_name', $field) && $field['option_name'])
                        $field['id'] = $field['option_name'];

                    $field['old_value'] = get_option($field['id']);
                    (isset($_POST[$field['id']])) ? $new = $_POST[$field['id']] : $new = null;


                    if ((isset($new) && ($new !== $field['old_value'])) || !isset($field['old_value'])) {

                        $bool = update_option($field['id'], $new);

                        if ($field['type'] == 'license-key') {
                            // retrieve the license from the database
                            $license = trim(get_option('edd_sample_license_key'));

                            // data to send in our API request
                            $api_params = array(
                                'edd_action' => 'activate_license',
                                'license' => $new,
                                'item_name' => $field['slug'] // the name of our product in EDD
                            );

                            // Call the custom API.
                            $response = wp_remote_get(add_query_arg($api_params, WP_CTA_STORE_URL), array('timeout' => 15, 'sslverify' => false));

                            // make sure the response came back okay
                            if (is_wp_error($response))
                                break;

                            // decode the license data
                            $license_data = json_decode(wp_remote_retrieve_body($response));

                            // $license_data->license will be either "active" or "inactive"
                            $license_status = update_option('wp_cta_license_status-' . $field['slug'], $license_data->license);
                        }
                    } elseif (!$new && $field['old_value']) {

                        if ($field['type'] == 'license-key') {
                            $master_key = get_option('inboundnow_master_license_key', '');
                            if ($master_key) {
                                $bool = update_option($field['id'], $master_key);
                            } else {
                                update_option($field['id'], '');
                            }
                        } else {
                            $bool = update_option($field['id'], $field['default']);
                        }
                    } else {
                        if ($field['type'] == 'license-key' && $new) {

                            $license_status = get_option('wp_cta_license_status-' . $field['slug']);

                            if ($license_status == 'valid' && $new == $field['old_value']) {
                                continue;
                            }

                            /* retrieve the license from the database */
                            $license = trim(get_option('edd_sample_license_key'));

                            /* data to send in our API request */
                            $api_params = array(
                                'edd_action' => 'activate_license',
                                'license' => $new,
                                'item_name' => $field['slug'] // the name of our product in EDD
                            );

                            /* Call the custom API. */
                            $response = wp_remote_get(add_query_arg($api_params, WP_CTA_STORE_URL), array('timeout' => 15, 'sslverify' => false));

                            /* make sure the response came back okay */
                            if (is_wp_error($response))
                                break;

                            /* decode the license data */
                            $license_data = json_decode(wp_remote_retrieve_body($response));

                            /* $license_data->license will be either "active" or "inactive" */
                            $license_status = update_option('wp_cta_license_status-' . $field['slug'], $license_data->license);
                        }
                    }

                    do_action('wp_cta_save_global_settings', $field);
                }

            }

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
                $field_id = str_replace('wp-cta-main-' , '', $field_id );
                $value = (isset($inbound_settings[WP_CTA_SLUG][$field_id])) ? $inbound_settings[WP_CTA_SLUG][$field_id] : $default;
            } else {
                $value = get_option( $field_id, $default );
            }

            return $value;
        }
    }

    /**
     *    Loads CTA_Settings on admin_init
     */
    function load_CTA_Settings() {
        new CTA_Settings;
    }

    add_action('admin_init', 'load_CTA_Settings');

}