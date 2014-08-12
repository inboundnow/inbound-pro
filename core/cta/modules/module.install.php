<?php // Added Demo Landing on Install
add_action('init', 'inbound_create_default_post_type_cta');
function inbound_create_default_post_type_cta(){
    // NEED to insert custom meta as well
    //delete_transient( 'wp-lead-fields');
    $option_name = "wp_cta_settings_general";
    $option_key = "default_call_to_action";
    $current_user = wp_get_current_user();
    add_option( $option_name, '' );
    //delete_option( 'lp_settings_general' );
    $lp_default_options = get_option($option_name);
    // Create Default if it doesn't exist
    if ( ! isset( $lp_default_options[$option_key] ) ) {
        $default_lander = wp_insert_post(
                array(
                    'post_title'     => __( 'A/B Testing Call To Action Example' , 'cta' ),
                    'post_content'   => '',
                    'post_status'    => 'publish',
                    'post_author'    => $current_user->ID,
                    'post_type'      => 'wp-call-to-action',
                    'comment_status' => 'closed'
                )
            );
        // Variation A
        add_post_meta($default_lander, 'wp-cta-selected-template', 'flat-cta');
        add_post_meta($default_lander, 'wp_cta_width-0', '310');
        add_post_meta($default_lander, 'wp_cta_height-0', '300');
        add_post_meta($default_lander, 'flat-cta-header-text', __( 'Snappy Headline' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-sub-header-text', __('Awesome Subheadline Text Goes here' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-text-color', '000000');
        add_post_meta($default_lander, 'flat-cta-content-color', '60BCF0');
        add_post_meta($default_lander, 'flat-cta-content-text-color', 'ffffff');
        add_post_meta($default_lander, 'flat-cta-submit-button-color', 'ffffff');
        add_post_meta($default_lander, 'flat-cta-submit-button-text', __( 'Download Now' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-link_url', 'http://www.inboundnow.com');

        // Varaition B
        add_post_meta($default_lander, 'wp-cta-selected-template-1', 'flat-cta');
        add_post_meta($default_lander, 'wp_cta_width-1', '310');
        add_post_meta($default_lander, 'wp_cta_height-1', '300');
        add_post_meta($default_lander, 'flat-cta-header-text-1', __( 'Great Offer' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-sub-header-text-1', __( 'Amazing Deals Await!<br> Click below to find<br> amazing deals' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-text-color-1', '000000');
        add_post_meta($default_lander, 'flat-cta-content-color-1', 'f22424');
        add_post_meta($default_lander, 'flat-cta-content-text-color-1', 'ffffff');
        add_post_meta($default_lander, 'flat-cta-submit-button-color-1', 'ffffff');
        add_post_meta($default_lander, 'flat-cta-submit-button-text-1', __( 'Learn More' , 'cta' ));
        add_post_meta($default_lander, 'flat-cta-link_url-1', 'http://www.inboundnow.com');

        // Add A/B Testing meta
        add_post_meta($default_lander, 'wp-cta-variations', '{ "0":{"status":"active"} , "1":{"status":"active"} }');
        add_post_meta($default_lander, 'wp-cta-ab-variation-impressions-0', 115);
        add_post_meta($default_lander, 'wp-cta-ab-variation-impressions-1', 113);
        add_post_meta($default_lander, 'wp-cta-ab-variation-conversions-0', 15);
        add_post_meta($default_lander, 'wp-cta-ab-variation-conversions-1', 27);

        add_post_meta($default_lander, 'link_open_option', 'this_window');

        // Store our page IDs
        $options = array(
            $option_key => $default_lander
        );

        update_option( $option_name, $options );
    }
}
?>