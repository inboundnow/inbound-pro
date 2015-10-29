<?php

/**
*  Demo template uses this
*/
if (!function_exists('cta_example_template_function')) {
	function cta_example_template_function() {
	  return 'Return value from cta_example_template_function()';
	}
}

add_filter( 'plugin_row_meta', 'calls_to_action_plugin_meta_links', 10, 2 );
function calls_to_action_plugin_meta_links( $links, $file ) {

    $plugin = 'cta/calls-to-action.php';

    // create link
    if ( $file == $plugin ) {
        return array_merge(
            $links,
            array( '<a href="http://www.inboundnow.com/membership-packages/">Upgrade to Pro</a>' )
        );
    }
    return $links;
}

/**
*  Load supportive css for IE8 and below
*/
function wp_cta_kill_ie8() {
    global $is_IE;
    if ( $is_IE ) {
        echo '<!--[if lt IE 9]>';
        echo '<link rel="stylesheet" type="text/css" href="'.WP_CTA_URLPATH.'/css/ie8-and-down.css" />';
        echo '<![endif]-->';
    }
}
add_action( 'wp_head', 'wp_cta_kill_ie8' );


/* Use me for time debugging!

	$start_time = microtime(TRUE);

	$end_time = microtime(TRUE);
	echo $end_time - $start_time;
	exit;

*/

