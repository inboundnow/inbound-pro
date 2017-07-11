<?php

/**
 * Class for creating example landing page on first install and prompting Leads and CTA GPL plugin downloads when Landing Pages GPL installed.
 * @package LandingPages
 * @subpackage Activation
 */

class Landing_Pages_Install {

    /**
     * Initiate class
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     * Load hooks and filters
     */
    public static function add_hooks() {
        add_action( 'admin_init', array( __CLASS__ , 'install_example_landing_page_check') );

        /* load styles and scripts */
        add_action('admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts' ) );

    }

    /**
     * Enqueue scripts and styles
     */
    public static function enqueue_scripts() {
        global $plugin_page;
        if ( $plugin_page != 'install-inbound-plugins' ) {
            return;
        }

        wp_enqueue_script('inbound-install-plugins', LANDINGPAGES_URLPATH . 'assets/js/admin/install-plugins.js' , array() , null);
        wp_enqueue_style('inbound-install-plugins-css', LANDINGPAGES_URLPATH . 'assets/css/admin/install-plugins.css', array() , null);
    }

    /**
     * Creates example landing page if needs creating else returns the lp id on record
     * @returns INT
     */
    public static function install_example_landing_page_check() {
        $lp_default_options = get_option( 'lp_settings_general' );

        if ( isset( $lp_default_options["default_landing_page"] ) ) {
            return $lp_default_options["default_landing_page"];
        }

        return self::install_example_landing_page();
    }


    /**
     *  Install example landing page and return landing page id
     * @returns INT $landing_page_id
     */
    public static function install_example_landing_page() {


        $landing_page_id = wp_insert_post(
            array(
                'post_title'     => __( 'A/B Testing Landing Page Example' , 'landing-pages'),
                'post_content'   => __( '<p>This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>' , 'post'),
                'post_status'    => 'publish',
                'post_type'      => 'landing-page',
            ) , true
        );

        /* Variation A */
        add_post_meta($landing_page_id, 'lp-main-headline', __( 'Main Catchy Headline (A)' , 'landing-pages') );
        add_post_meta($landing_page_id, 'lp-selected-template', 'svtle');
        add_post_meta($landing_page_id, 'svtle-conversion-area-content', '<h2>'.__( 'Form a' , 'landing-pages') .'</h2>[inbound_forms id="default_1" name="First, Last, Email Form"]' );
        add_post_meta($landing_page_id, 'svtle-main-content', __( '<p>This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>' , 'landing-pages') );

        /* variation B */
        add_post_meta($landing_page_id, 'lp-main-headline-1', __('Main Catchy Headline Two (B)' , 'landing-pages') );
        add_post_meta($landing_page_id, 'lp-selected-template-1', 'svtle');
        add_post_meta($landing_page_id, 'svtle-conversion-area-content-1', '<h2>'.__( 'Form B' , 'landing-pages') .'</h2>[inbound_forms id="default_1" name="First, Last, Email Form"]');
        add_post_meta($landing_page_id, 'svtle-main-content-1', '<p>(Version B) This is the first paragraph of your landing page where you want to draw the viewers in and quickly explain your value proposition.</p><p><strong>Use Bullet Points to:</strong><ul><li>Explain why they should fill out the form</li><li>What they will learn if they download</li><li>A problem this form will solve for them</li></ul></p><p>Short ending paragraph reiterating the value behind the form</p>');

        /*  Add A/B Testing meta */
        add_post_meta($landing_page_id, 'lp-ab-variations', '0,1');
        add_post_meta($landing_page_id, 'lp-ab-variation-impressions-0', 30);
        add_post_meta($landing_page_id, 'lp-ab-variation-impressions-1', 35);
        add_post_meta($landing_page_id, 'lp-ab-variation-conversions-0', 10);
        add_post_meta($landing_page_id, 'lp-ab-variation-conversions-1', 15);

        /* Add template meta A */
        add_post_meta($landing_page_id, 'svtle-submit-button-color', '5baa1e');
        add_post_meta($landing_page_id, 'svtle-display-social', '0');
        add_post_meta($landing_page_id, 'svtle-logo', '/wp-content/plugins/landing-pages/templates/svtle/assets/images/inbound-logo.png');
        add_post_meta($landing_page_id, 'svtle-body-color', 'ffffff');
        add_post_meta($landing_page_id, 'svtle-sidebar', 'left');
        add_post_meta($landing_page_id, 'svtle-page-text-color', '4d4d4d');
        add_post_meta($landing_page_id, 'svtle-sidebar-color', 'ffffff');
        add_post_meta($landing_page_id, 'svtle-sidebar-text-color', '000000');
        add_post_meta($landing_page_id, 'svtle-header-color', 'ffffff');

        /* Add template meta B */
        add_post_meta($landing_page_id, 'svtle-submit-button-color-1', 'ff0c00');
        add_post_meta($landing_page_id, 'svtle-display-social-1', '0');
        add_post_meta($landing_page_id, 'svtle-logo-1', '/wp-content/plugins/landing-pages/templates/svtle/assets/images/inbound-logo.png');
        add_post_meta($landing_page_id, 'svtle-body-color-1', '51b0ef');
        add_post_meta($landing_page_id, 'svtle-sidebar-1', 'left');
        add_post_meta($landing_page_id, 'svtle-page-text-color-1', '000000');
        add_post_meta($landing_page_id, 'svtle-sidebar-color-1', '51b0ef');
        add_post_meta($landing_page_id, 'svtle-sidebar-text-color-1', '000000');
        add_post_meta($landing_page_id, 'svtle-header-color-1', '51b0ef');

        /* Store our page IDs */
        $options = array(
            "default_landing_page" => $landing_page_id
        );


        update_option( "lp_settings_general" , $options );

        return $landing_page_id;
    }


}


new Landing_Pages_Install;