<?php
/**
*	Video Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['video'] = array(
		'no_preview' => true,
		'options' => array(
			'url' => array(
				'name' => __('Video URL', 'inbound-pro' ),
				'desc' => __('Paste the video URL here, click <a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">here</a> to see all available video hosts.', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			)
		),
		'shortcode' => '[video url="{{url}}" /]',
		'popup_title' => 'Insert Video Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('video', 'inbound_shortcode_video');

	function inbound_shortcode_video( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'url' => ''
		), $atts));

		return '<div class="video-container">'. wp_oembed_get( $url ) .'</div>';
	}