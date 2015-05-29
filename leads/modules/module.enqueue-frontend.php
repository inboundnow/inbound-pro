<?php

add_action('wp_enqueue_scripts', 'wpleads_enqueuescripts_header');

function wpleads_enqueuescripts_header() {
	global $post;

	$post_type = isset($post) ? get_post_type( $post ) : null;


}
