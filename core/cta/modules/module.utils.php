<?php

/**
 * Demo template uses this
 * @package CTA
 */
if (!function_exists('cta_example_template_function')) {
	function cta_example_template_function() {
	  return 'Return value from cta_example_template_function()';
	}
}

/**
 * Load supportive css for IE8 and below
 * @package CTA
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

