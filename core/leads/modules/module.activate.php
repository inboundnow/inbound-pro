<?php


register_activation_hook( WPL_FILE , 'wpleads_activate');

function wpleads_activate() {
	global $wpdb;
	$blogids = ""; // define to kill error
	$multisite = 0;
	add_option( 'Leads_Activated', true ); // global definition for loading lead files
	// Makes sure the plugin is defined before trying to use it
	if ( ! function_exists( 'is_plugin_active_for_network' ) )
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );


	if ( is_plugin_active_for_network( WPL_CORE ) ) {
		if (function_exists('is_multisite') && is_multisite()) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				$multisite = 1;
		}
	}


	if (count($blogids)>1) {
		$count = count($blogids);
	} else {
		$count=1;
	}

	for ($i=0;$i<$count;$i++) {

		if ($multisite==1) {
			 switch_to_blog($blogids[$i]);
		}
		/* legacy support */
		$sql = "update {$wpdb->prefix}postmeta set meta_key = 'wpleads_conversion_count' where meta_key = 'wpl-lead-conversion-count'";
		$result = mysql_query($sql);

		$sql = "update {$wpdb->prefix}postmeta set meta_key = 'wpleads_page_view_count' where meta_key = 'wpl-lead-page-view-count'";
		$result = mysql_query($sql);

		$sql = "update {$wpdb->prefix}postmeta set meta_key = 'wpleads_raw_post_data' where meta_key = 'wpl-lead-raw-post-data'";
		$result = mysql_query($sql);

	}

}

register_deactivation_hook( WPL_FILE , 'wpleads_deactivate');
// toggle main lead stuff off
function wpleads_deactivate() {
	delete_option( 'Leads_Activated');
	wp_clear_scheduled_hook( 'wpleads_lead_automation_daily' );
}

?>