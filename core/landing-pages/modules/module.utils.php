<?php
/*
*	Utilities functions used throughout the plugin
*/

/* Fix wp_title for known bad behavior themes */
add_action('wp', 'landingpage_fix_known_wp_title_isses', 10);
function landingpage_fix_known_wp_title_isses() {

    if ('landing-page' != get_post_type()) {
        return;
    }

    remove_filter('wp_title', 'genesis_doctitle_wrap', 20);
    remove_filter('wp_title', 'genesis_default_title', 10);
}

/* Fix qtranslate issues */
if (!function_exists('inbound_qtrans_disable')) {
    function inbound_qtrans_disable() {
        global $typenow, $pagenow;

        if (in_array($typenow, array('landing-page' || 'wp-call-to-action')) && /* post_types where qTranslate should be disabled */
            in_array($pagenow, array('post-new.php', 'post.php'))
        ) {
            remove_action('admin_head', 'qtrans_adminHeader');
            remove_filter('admin_footer', 'qtrans_modifyExcerpt');
            remove_filter('the_editor', 'qtrans_modifyRichEditor');
        }
    }
    add_action('current_screen', 'inbound_qtrans_disable');
}




/**
 * Add namespaces for legacy classes to try and prevent fatals
 */
if (!class_exists('LP_EXTENSION_UPDATER') ){
    /**
     * Class LP_EXTENSION_UPDATER depreciated class name
     * @package xDepreciated
     */
    class LP_EXTENSION_UPDATER { };

    /**
     * Class LP_EXTENSION_LICENSENING depreciated class name
     * @package xDepreciated
    */
    class LP_EXTENSION_LICENSENING { };
}