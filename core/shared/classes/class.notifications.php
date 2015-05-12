<?php
/**
 * Admin notications
 *
 */
if ( ! class_exists( 'Inbound_Notices' ) ) {

	class Inbound_Notices {

		static function init() {
			/* determines if in ajax mode */
			add_action('admin_notices', array( __CLASS__ , 'inbound_notice'));
			add_action('admin_init',  array( __CLASS__ , 'inbound_notice_ignore'));
		}

		/* Fix JavaScript Conflicts in WordPress */
		public static function inbound_notice(){
		    global $pagenow;
		    global $current_user ;

		    $post_types = array('landing-page','wp-call-to-action','wp-lead');

		    $page_string = isset($_GET["page"]) ? $_GET["page"] : "null";
		    $user_id = $current_user->ID;

		    if ( get_user_meta($user_id, 'inbound_translate_ignore') || ( !isset($_GET['post_type']) || !in_array( $_GET['post_type'] , $post_types) ) ) {
                return;
            }

            echo '<div class="updated">
                    <h2>'. __( 'Help Translate Inbound Now Marketing Plugins' , INBOUNDNOW_TEXT_DOMAIN ) .'</h2>
                     <p style="width:80%;">'. sprintf( __( 'Help translate Inbound Now\'s marketing plugins to your %s native langauge %s!' , INBOUNDNOW_TEXT_DOMAIN ) , '<a href="http://docs.inboundnow.com/guide/inbound-translations-project/" target="_blank">' , '</a>' ) .'</p>
                     <a class="button button-primary button-large" href="http://www.inboundnow.com/go/signup-to-become-a-translator/" target="_blank">'. __( 'Help Translate the plugins' , INBOUNDNOW_TEXT_DOMAIN ) .'</a>
                     <a class="button button-large" href="?inbound_translate_ignore=0">' . __( 'No Thanks' , INBOUNDNOW_TEXT_DOMAIN ) .'</a>
                     <br><br>
                  </div>';


		}

		public static function inbound_notice_ignore() {
		    global $current_user;
		    $user_id = $current_user->ID;
	        if (isset($_GET['inbound_translate_ignore']) && '0' == $_GET['inbound_translate_ignore'] ) {
	             add_user_meta($user_id, 'inbound_translate_ignore', 'true', true);
	    	}
		}

	}

Inbound_Notices::init();

}

