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
		    $page_string = isset($_GET["page"]) ? $_GET["page"] : "null";
		    $user_id = $current_user->ID;

		    if ( ! get_user_meta($user_id, 'inbound_translate_ignore') ) {

		             echo '<div class="updated">
		             	<h2>Help Translate Inbound Now Marketing Plugins & get free access to a pro account</h2>
		                 <p style="width:80%;">Want to get free access to all <a href="http://www.inboundnow.com/market/" target="_blank">inbound now pro addons and templates</a>?</p>
		                 <p style="width:80%;">Help translate Inbound Now\'s marketing plugins to your <a href="http://docs.inboundnow.com/guide/inbound-translations-project/" target="_blank">native langauge</a>!</p>
		                 <a class="button button-primary button-large" href="http://www.inboundnow.com/translate-inbound-now/" target="_blank">Help Translate the plugins</a>
		                 <a class="button button-large" href="?inbound_translate_ignore=0">No Thanks</a>
		             <br><br></div>';

		    }
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

/* Template page notices

add_action('admin_notices', 'lp_template_page_notice');
add_action('admin_init', 'lp_template_page_ignore');
function lp_template_page_ignore() {
    global $current_user;
        $user_id = $current_user->ID;
        if ( isset($_GET['lp_template_page_ignore']) && '0' == $_GET['lp_template_page_ignore'] ) {
             add_user_meta($user_id, 'lp_template_page_ignore', 'true', true);
    }
}
// Start Landing Page Welcome
add_action('admin_notices', 'lp_activation_notice');
function lp_activation_notice() {
    global $current_user ;
        $user_id = $current_user->ID;
    if ( ! get_user_meta($user_id, 'lp_activation_ignore_notice') ) {
        echo '<div class="updated"><p>';
        echo "<a style='float:right;' href='?lp_activation_message_ignore=0'>Dismiss This</a>Welcome to the WordPress Landing Page Plugin! Need help getting started? View the <strong>Quickstart Guide</strong><br>
        Want to get notified about WordPress Landing Page Plugin updates, new features, new landing page design templates, and add-ons? <br>
        Form here | ";
        echo "</p></div>";
    }
}
add_action('admin_init', 'lp_activation_message_ignore');
function lp_activation_message_ignore() {
    global $current_user;
        $user_id = $current_user->ID;
        if ( isset($_GET['lp_activation_message_ignore']) && '0' == $_GET['lp_activation_message_ignore'] ) {
             add_user_meta($user_id, 'lp_activation_ignore_notice', 'true', true);
    }
} */
