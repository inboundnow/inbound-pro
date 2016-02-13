<?php

/*
Plugin Name: Advanced Custom Fields: Google Font Selector
Plugin URI: https://github.com/danielpataki/ACF-Google-Font-Selector
Description: A field for Advanced Custom Fields which allows users to select Google fonts with advanced options
Version: 3.0.1
Author: Daniel Pataki
Author URI: http://danielpataki.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Include Common Functions
include_once('functions.php');


add_action('plugins_loaded', 'acfgfs_load_textdomain');
/**
 * Load Text Domain
 *
 * Loads the textdomain for translations
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function acfgfs_load_textdomain() {
	load_plugin_textdomain( 'acf-google-font-selector-field', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}


add_action('acf/include_field_types', 'include_field_types_google_font_selector');
/**
 * ACF 5 Field
 *
 * Loads the field for ACF 5
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function include_field_types_google_font_selector( $version ) {
	include_once('acf-google_font_selector-v5.php');
}

add_action('acf/register_fields', 'register_fields_google_font_selector');
/**
 * ACF 4 Field
 *
 * Loads the field for ACF 4
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function register_fields_google_font_selector() {
	include_once('acf-google_font_selector-v4.php');
}

add_action( 'admin_notices', 'acfgfs_setup_nag' );
/**
 * API Key Nag
 *
 * Displays a message promting users to supply the Google API key. It can be
 * added in the settings page, or it can be defined as a constant.
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function acfgfs_setup_nag() {
	if( !defined( 'ACFGFS_API_KEY' ) && !get_option('acfgfs_api_key') ) :
    ?>
    <div class="update-nag">
        <p><?php echo sprintf( __( 'The Google Font Selector Field requires an Google API key to work. You can set your API key on the <a href="%s">options page</a>. If you need help getting an API key <a href="%s">click here</a>', 'acf-google-font-selector-field' ), admin_url( 'options-general.php?page=acfgfs-settings' ), 'https://wordpress.org/plugins/acf-google-font-selector-field/'  ); ?></p>
    </div>
    <?php
	endif;
}

add_action('admin_menu', 'acfgfs_settings_page');
/**
 * Add Setting Page
 *
 * Adds the settings page which contains the field for the Google API Key
 * Also initializes the settings that holds the value.
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function acfgfs_settings_page() {
	if( !defined('ACFGFS_API_KEY') ) {
    	add_options_page( _x( 'Google Font Selector', 'In the title tag of the page', 'acf-google-font-selector-field'  ), _x( 'Google Font Selector', 'Menu title',  'acf-google-font-selector-field' ), 'manage_options', 'acfgfs-settings', 'acfgfs_settings_page_content');

    	add_action( 'admin_init', 'acfgfs_register_settings' );
	}
}


/**
 * Register Settings
 *
 * Registers plugin-wide settings, we use this for the Google API key
 *
 * @author Daniel Pataki
 * @since 1.0.0
 *
 */
function acfgfs_register_settings() {
	register_setting( 'acfgfs', 'acfgfs_api_key' );
}


 /**
 * Settings Page Content
 *
 * The UI for the settings page. It contains the form, as well as
 * a quick check to make sure the given credentials work.
 *
 * @author Daniel Pataki
 * @since 1.0.0
 *
 */
function acfgfs_settings_page_content() {
?>
<div class="wrap">
<h2><?php _e( 'Google Font Selector', 'acf-google-font-selector-field' ) ?></h2>

<form method="post" action="options.php">
    <?php settings_fields( 'acfgfs' ); ?>
    <?php do_settings_sections( 'acfgfs' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Google API Key', 'acf-google-font-selector-field' ) ?></th>
        <td><input type="text" name="acfgfs_api_key" value="<?php echo esc_attr( get_option('acfgfs_api_key') ); ?>" /></td>
        </tr>
    </table>

    <?php
        $api_key = esc_attr( get_option('acfgfs_api_key') );
        if( !empty( $_GET['settings-updated'] ) && !empty( $api_key ) ) {

			$request = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $api_key );
			$response = json_decode( $request['body'], true );

            if( !empty( $response['error']['errors'] ) ) {
                echo '<div id="acfgfs-api-key-error" class="error acfgfs-error">
<p><strong>' . __( 'We tried to verify your API key but it seems it is incorrect. Make sure to copy-paste it from Google exactly.', 'acf-google-font-selector-field' ) . '</strong></p></div>';
            }
        }
    ?>

    <?php submit_button(); ?>

</form>
</div>
<?php
}




?>
