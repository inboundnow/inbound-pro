<?php

/*
Plugin Name: Advanced Custom Fields: MANAGE_INBOUND_ADDONS
Plugin URI: PLUGIN_URL
Description: DESCRIPTION
Version: 1.0.0
Author: AUTHOR_NAME
Author URI: AUTHOR_URL
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/




// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-MANAGE_INBOUND_ADDONS', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );




// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_MANAGE_INBOUND_ADDONS( $version ) {

	include_once('acf-MANAGE_INBOUND_ADDONS-v5.php');

}

add_action('acf/include_field_types', 'include_field_types_MANAGE_INBOUND_ADDONS');




// 3. Include field type for ACF4
function register_fields_MANAGE_INBOUND_ADDONS() {

	include_once('acf-MANAGE_INBOUND_ADDONS-v4.php');

}

add_action('acf/register_fields', 'register_fields_MANAGE_INBOUND_ADDONS');


/* Set the Addons being used */
add_action('wp_ajax_inbound_toggle_addons_ajax', 'inbound_toggle_addons_ajax');
add_action('wp_ajax_nopriv_inbound_toggle_addons_ajax', 'inbound_toggle_addons_ajax');

function inbound_toggle_addons_ajax() {
      // Post Values
      $the_addon = (isset( $_POST['the_addon'] )) ? $_POST['the_addon'] : "";
      $toggle = (isset( $_POST['toggle'] )) ? $_POST['toggle'] : "";

    /* Store Script Data to Post */
    $toggled_addon_files = get_transient( 'inbound-now-active-addons' );

    if(is_array($toggled_addon_files)) {

        if($toggle === 'on') {
          // add or remove from list
          $toggled_addon_files[] = $the_addon;
        } else {
          unset($toggled_addon_files[$the_addon]);
          $toggled_addon_files = array_diff($toggled_addon_files, array($the_addon));
        }

    } else {
      // Create the first item in array
      if($toggle === 'on') {
      	$toggled_addon_files[0] = $the_addon;
      }
    }

    set_transient('inbound-now-active-addons', $toggled_addon_files );


    $output =  array('encode'=> 'end' );

    echo json_encode($output,JSON_FORCE_OBJECT);
    wp_die();
 }


?>