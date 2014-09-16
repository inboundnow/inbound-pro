<?php
/**
 * Compability Mode Deregisters All Third party scripts not in the whitelist
 * - The class was made to cut down on third party support requests
 *
 * Call the dequeue manually Inbound_Compatibility::inbound_compatibilities_mode();
 */

/* From Global Settings if compat mode toggled on turn off third party scripts */
add_action('admin_enqueue_scripts', 'inbound_turn_on_compatiblity', 110);
if (!function_exists('inbound_turn_on_compatiblity')) {
  function inbound_turn_on_compatiblity() {
    $screen = get_current_screen();

    // Add all Plugin Screens to Array
    $inbound_screens =  Inbound_Compatibility::return_inbound_now_screens(); // grabs our plugin screen ids

    // If Not Inbound Now Screen Exit function
    if (!in_array($screen->id, $inbound_screens)) {
              return;
    }

    $lead_compatiblity = get_option( 'wpl-main-inbound_compatibility_mode', $default = false );
    $cta_compatiblity = get_option( 'wp-cta-main-inbound_compatibility_mode', $default = false );
    $lp_compatiblity = get_option( 'lp-main-inbound_compatibility_mode', $default = false );
    if ( $lead_compatiblity || $cta_compatiblity || $lp_compatiblity ) {
      Inbound_Compatibility::inbound_compatibilities_mode(); // kill third party scripts
    }
  }
}

add_action('admin_notices', 'inbound_compability_admin_notice'); // disable compat notice
if (!function_exists('inbound_compability_admin_notice')) {
  function inbound_compability_admin_notice(){
    $lead_compatiblity = get_option( 'wpl-main-inbound_compatibility_mode', $default = false );
    $cta_compatiblity = get_option( 'wp-cta-main-inbound_compatibility_mode', $default = false );
    $lp_compatiblity = get_option( 'lp-main-inbound_compatibility_mode', $default = false );
    if ($lead_compatiblity) {
      $link = admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings' );
    } elseif ($cta_compatiblity) {
      $link = admin_url( 'edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings' );
    } elseif ($lp_compatiblity) {
      $link = admin_url( 'edit.php?post_type=landing-page&page=lp_global_settings' );
    }

    if ( $lead_compatiblity || $cta_compatiblity || $lp_compatiblity ) {
      echo '<div class="updated">
         <p>Inbound Now Compatibility Mode is currently activated. To turn off go to <a href="'.$link.'">global settings</a> and toggle off</p>
      </div>';
    }
  }
}

if (!class_exists('Inbound_Compatibility')) {
  class Inbound_Compatibility {
    static $add_inbound_compatibility;

    /**
     * Dequeue all third party scripts on page
     * @return [type] [description]
     */
    static function inbound_compatibilities_mode() {

        if (is_admin()){
          $screen = get_current_screen();
        } else {
          $screen = '';
        }
          global $wp_scripts;

          // Match our plugins and whitelist them
          $registered_scripts = $wp_scripts->registered;
          $inbound_white_list = array();
          foreach ($registered_scripts as $handle) {
              if(preg_match("/\/plugins\/leads\//", $handle->src)) {
                //echo $handle->handle;
                $inbound_white_list[] = $handle->handle;
              }
              if(preg_match("/\/plugins\/cta\//", $handle->src)) {
                //echo $handle->handle;
                $inbound_white_list[]= $handle->handle;
              }
              if(preg_match("/\/plugins\/landing-pages\//", $handle->src)) {
                //echo $handle->handle;
                $inbound_white_list[]= $handle->handle;
              }
          }
          //print_r($inbound_white_list);

          /* NEED Filter for ADDONs */


          $scripts_queued = $wp_scripts->queue; /* All Queued Scripts */    //print_r($wp_scripts->queue);

          // Wordpress Core Scripts List
          $wp_core_scripts = array("jcrop", "swfobject", "swfupload", "swfupload-degrade", "swfupload-queue", "swfupload-handlers", "jquery", "jquery-form", "jquery-color", "jquery-masonry", "jquery-ui-core", "jquery-ui-widget", "jquery-ui-mouse", "jquery-ui-accordion", "jquery-ui-autocomplete", "jquery-ui-slider", "jquery-ui-progressbar", "jquery-ui-tabs", "jquery-ui-sortable", "jquery-ui-draggable", "jquery-ui-droppable", "jquery-ui-selectable", "jquery-ui-position", "jquery-ui-datepicker", "jquery-ui-tooltip", "jquery-ui-resizable", "jquery-ui-dialog", "jquery-ui-button", "jquery-effects-core", "jquery-effects-blind", "jquery-effects-bounce", "jquery-effects-clip", "jquery-effects-drop", "jquery-effects-explode", "jquery-effects-fade", "jquery-effects-fold", "jquery-effects-highlight", "jquery-effects-pulsate", "jquery-effects-scale", "jquery-effects-shake", "jquery-effects-slide", "jquery-effects-transfer", "wp-mediaelement", "schedule", "suggest", "thickbox", "hoverIntent", "jquery-hotkeys", "sack", "quicktags", "iris", "farbtastic", "colorpicker", "tiny_mce", "autosave", "wp-ajax-response", "wp-lists", "common", "editorremov", "editor-functions", "ajaxcat", "admin-categories", "admin-tags", "admin-custom-fields", "password-strength-meter", "admin-comments", "admin-users", "admin-forms", "xfn", "upload", "postbox", "slug", "post", "page", "link", "comment", "comment-reply", "admin-gallery", "media-upload", "admin-widgets", "word-count", "theme-preview", "json2", "plupload", "plupload-all", "plupload-html4", "plupload-html5", "plupload-flash", "plupload-silverlight", "underscore", "backbone");


             foreach ($scripts_queued as $key => $value) {
              //echo $key . $value;
              if (!in_array($value, $inbound_white_list) && !in_array($value, $wp_core_scripts)){
                wp_dequeue_script( $value );
                //echo $key . $value;
              }
             }
      }

      static function return_inbound_now_screens(){
          $inbound_screens = array(
          'wp-lead_page_wpleads_global_settings',
          'wp-lead_page_lead_management',
          'edit-list',
          'edit-wp-lead',
          'wp-lead',
          'edit-wplead_list_category',
          'edit-inbound-forms',
          'inbound-forms',
          'edit-landing-page',
          'landing-page',
          'edit-landing_page_category',
          'landing-page_page_lp_manage_templates',
          'landing-page_page_lp_global_settings',
          'landing-page_page_lp_store',
          'edit-wp-call-to-action',
          'edit-wp_call_to_action_category',
          'wp-call-to-action',
          'wp-call-to-action_page_wp_cta_manage_templates',
          'wp-call-to-action_page_wp_cta_global_settings',
          );
          // add filter
          return $inbound_screens;
      }
  }
}
