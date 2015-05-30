<?php

/**
*  Demo template uses this
*/
if (!function_exists('inbound_email_example_template_function')) {
	function inbound_inbound_email_example_template_function() {
	  return 'Return value from inbound_email_example_template_function()';
	}
}

/**
*  Load supportive css for IE8 and below
*/
function inbound_email_kill_ie8() {
    global $is_IE;
    if ( $is_IE ) {
        echo '<!--[if lt IE 9]>';
        echo '<link rel="stylesheet" type="text/css" href="'.INBOUND_EMAIL_URLPATH.'/assets/css/ie8-and-down.css" />';
        echo '<![endif]-->';
    }
}
add_action( 'wp_head', 'inbound_email_kill_ie8' );


/* Use me for time debugging!

	$start_time = microtime(TRUE);

	$end_time = microtime(TRUE);
	echo $end_time - $start_time;
	exit;

*/

