<?php
// Added Demo Landing on Install
add_action('admin_init', 'inbound_create_default_post_type');
function inbound_create_default_post_type(){


    $lp_default_options = get_option( 'lp_settings_general' );

    if ( isset( $lp_default_options["default_landing_page"] ) ) {
        return $lp_default_options["default_landing_page"];
    }

	return inbound_install_example_lander();
}

/**
*  Install example landing page and return landing page id
*/
function inbound_install_example_lander() {


	$landing_page_id = wp_insert_post(
        array(
            'post_title'     => __( 'A/B Testing Landing Page Example' , 'landing-pages'),
            'post_content'   => __( '<p>This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>' , 'post'),
            'post_status'    => 'publish',
            'post_type'      => 'landing-page',
        ) , true
    );
	shell_exec( json_encode( $landing_page_id) );

    // Variation A
    add_post_meta($landing_page_id, 'lp-main-headline', __( 'Main Catchy Headline (A)' , 'landing-pages') );
    add_post_meta($landing_page_id, 'lp-selected-template', 'svtle');
    add_post_meta($landing_page_id, 'svtle-conversion-area-content', '<h2>'.__( 'Form a' , 'landing-pages') .'</h2>[inbound_forms id="default_1" name="First, Last, Email Form"]' );
    add_post_meta($landing_page_id, 'svtle-main-content', __( '<p>This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>' , 'landing-pages') );

    // variation B
    add_post_meta($landing_page_id, 'lp-main-headline-1', __('Main Catchy Headline Two (B)' , 'landing-pages') );
    add_post_meta($landing_page_id, 'lp-selected-template-1', 'svtle');
    add_post_meta($landing_page_id, 'svtle-conversion-area-content-1', '<h2>'.__( 'Form B' , 'landing-pages') .'</h2>[inbound_forms id="default_1" name="First, Last, Email Form"]');
    add_post_meta($landing_page_id, 'svtle-main-content-1', '<p>(Version B) This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>');

    // Add A/B Testing meta
    add_post_meta($landing_page_id, 'lp-ab-variations', '0,1');
    add_post_meta($landing_page_id, 'lp-ab-variation-impressions-0', 30);
    add_post_meta($landing_page_id, 'lp-ab-variation-impressions-1', 35);
    add_post_meta($landing_page_id, 'lp-ab-variation-conversions-0', 10);
    add_post_meta($landing_page_id, 'lp-ab-variation-conversions-1', 15);

    // Add template meta A
    add_post_meta($landing_page_id, 'svtle-submit-button-color', '5baa1e');
    add_post_meta($landing_page_id, 'svtle-display-social', '0');
    add_post_meta($landing_page_id, 'svtle-logo', '/wp-content/plugins/landing-pages/templates/svtle/assets/images/inbound-logo.png');
    add_post_meta($landing_page_id, 'svtle-body-color', 'ffffff');
    add_post_meta($landing_page_id, 'svtle-sidebar', 'left');
    add_post_meta($landing_page_id, 'svtle-page-text-color', '4d4d4d');
    add_post_meta($landing_page_id, 'svtle-sidebar-color', 'ffffff');
    add_post_meta($landing_page_id, 'svtle-sidebar-text-color', '000000');
    add_post_meta($landing_page_id, 'svtle-header-color', 'ffffff');

    // Add template meta B
    add_post_meta($landing_page_id, 'svtle-submit-button-color-1', 'ff0c00');
    add_post_meta($landing_page_id, 'svtle-display-social-1', '0');
    add_post_meta($landing_page_id, 'svtle-logo-1', '/wp-content/plugins/landing-pages/templates/svtle/assets/images/inbound-logo.png');
    add_post_meta($landing_page_id, 'svtle-body-color-1', '51b0ef');
    add_post_meta($landing_page_id, 'svtle-sidebar-1', 'left');
    add_post_meta($landing_page_id, 'svtle-page-text-color-1', '000000');
    add_post_meta($landing_page_id, 'svtle-sidebar-color-1', '51b0ef');
    add_post_meta($landing_page_id, 'svtle-sidebar-text-color-1', '000000');
    add_post_meta($landing_page_id, 'svtle-header-color-1', '51b0ef');

    // Store our page IDs
    $options = array(
        "default_landing_page" => $landing_page_id
    );


    update_option( "lp_settings_general" , $options );

    return $landing_page_id;
}
/**
 * Debug Activation errors */
//update_option('plugin_error',  ''); //clear
/*
add_action('activated_plugin','activation_save_error');

function activation_save_error(){
    update_option('plugin_error',  ob_get_contents());
}*/
//echo "Errors:" . get_option('plugin_error');

/**
 * Include the TGM_Plugin_Activation class.
 */
if(!defined('INBOUND_PRO_PATH')) {
 require_once(LANDINGPAGES_PATH."/libraries/class-tgm-plugin-activation.php");
 add_action( 'tgmpa_register', 'lp_install_register_required_plugins' );
}

/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function lp_install_register_required_plugins() {
    //

    /**
     * Array of plugin arrays. Required keys are name, slug and required.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(

        // This is an example of how to include a plugin pre-packaged with a theme
        /*  array(
              'name'                  => 'TGM Example Plugin', // The plugin name
              'slug'                  => 'tgm-example-plugin', // The plugin slug (typically the folder name)
              'source'                => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source
              'required'              => true, // If false, the plugin is only 'recommended' instead of required
              'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
              'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
              'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
              'external_url'          => '', // If set, overrides default API URL and points to an external URL
          ), */

        // This is an example of how to include a plugin from the WordPress Plugin Repository
        array(
            'name'      => __('WordPress Leads' , 'landing-pages') .' <span class=\'inbound-install-notice\'> - '. __('This <b>free</b> landing page addon will give you the ability to track and manage incoming web leads. Gather advanced Lead Intelligence and close more deals.', 'landing-pages') .'</span>',
            'slug'      => 'leads',
            'required'  => false,
        ),
        array(
            'name'      => __('WordPress Calls to Action' , 'landing-pages') .' <span class=\'inbound-install-notice\'> - '. __('This <b>free</b> landing page addon will drive more traffic into your Landing Pages with Targeted Calls to Action in your sites sidebars & content. Create popups to capture visitor attention and convert more leads.' , 'landing-pages') .'</span>',
            'slug'      => 'cta',
            'required'  => false,
        ),

    );

    // Change this to your theme text domain, used for internationalising strings
    $theme_text_domain = 'landing-pages';

    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'domain'            => $theme_text_domain,           // Text domain - likely want to be the same as your theme.
        'default_path'      => '',                           // Default absolute path to pre-packaged plugins
        'parent_menu_slug'  => 'themes.php',         // Default parent menu slug
        'parent_url_slug'   => 'themes.php',         // Default parent URL slug
        'menu'              => 'install-inbound-plugins',   // Menu slug
        'has_notices'       => true,                         // Show admin notices or not
        'is_automatic'      => false,            // Automatically activate plugins after installation or not
        'message'           => '',               // Message to output right before the plugins table
        'strings'           => array(
            'page_title'                                => __( 'Install Required Plugins', $theme_text_domain ),
            'menu_title'                                => __( 'Install Plugins', $theme_text_domain ),
            'installing'                                => __( 'Installing Plugin: %s', $theme_text_domain ), // %1$s = plugin name
            'oops'                                      => __( 'Something went wrong with the plugin API.', $theme_text_domain ),
            'notice_can_install_required'               => _n_noop( 'WordPress Landing Pages requires the following plugin: %1$s', 'WordPress Landing Pages highly requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
            'notice_can_install_recommended'            => _n_noop( 'WordPress Landing Pages highly recommends the following complimentary plugin: %1$s', 'WordPress Landing Pages highly recommends the following complimentary plugins: %1$s.' ), // %1$s = plugin name(s)
            'notice_cannot_install'                     => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
            'notice_can_activate_required'              => _n_noop( 'The following required plugin is currently inactive: %1$s', 'The following required plugins are currently inactive: %1$s' ), // %1$s = plugin name(s)
            'notice_can_activate_recommended'           => _n_noop( 'The following recommended plugin is currently inactive: %1$s', 'The following recommended plugins are currently inactive: %1$s' ), // %1$s = plugin name(s)
            'notice_cannot_activate'                    => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
            'notice_ask_to_update'                      => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s' ), // %1$s = plugin name(s)
            'notice_cannot_update'                      => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
            'install_link'                              => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
            'activate_link'                             => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
            'return'                                    => __( 'Return to Required Plugins Installer', $theme_text_domain ),
            'plugin_activated'                          => __( 'Plugin activated successfully.', $theme_text_domain ),
            'complete'                                  => __( 'All plugins installed and activated successfully. %s', $theme_text_domain ),
            // %1$s = dashboard link
        )
    );

    inbound_activate( $plugins, $config );

}