<?php

define('INBOUND_FAST_AJAX' , true );

/**
 * Enable Fast Ajax
 */
add_filter( 'option_active_plugins', 'ajax_disable_plugins' );
function ajax_disable_plugins($plugins){

    /* load all plugins if not in ajax mode */
    if ( !defined( 'DOING_AJAX' ) )  {
        return $plugins;
    }

    /* load all plugins if fast_ajax is set to false */
    if ( !isset($_REQUEST['fast_ajax']) || !$_REQUEST['fast_ajax'] )  {
        return $plugins;
    }

    /* disable all plugins if none are told to load by the load_plugins array */
    if ( !isset($_REQUEST['load_plugins']) || !$_REQUEST['load_plugins'] )  {
        return array();
    }

    /* unset plugins not included in the load_plugins array */
    foreach ($plugins as $key => $plugin_path) {


        /* convert json */
        if (!is_array($_REQUEST['load_plugins']) && $_REQUEST['load_plugins']) {
            $_REQUEST['load_plugins'] = json_decode($_REQUEST['load_plugins'],true);
        }

        if (!in_array($plugin_path, $_REQUEST['load_plugins'] )) {
            unset($plugins[$key]);
        }
    }

    return $plugins;
}