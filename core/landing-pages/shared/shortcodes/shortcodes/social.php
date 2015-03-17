<?php
/**
*	Social Links Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['social_links'] = array(
		'no_preview' => true,
		'options' => array(
			'facebook' => array(
				'name' => __('Facebook', 'leads'),
				'desc' => __('Enter your facebook profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'twitter' => array(
				'name' => __('Twitter', 'leads'),
				'desc' => __('Enter your twitter profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'google_plus' => array(
				'name' => __('Google+', 'leads'),
				'desc' => __('Enter your google plus profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'linkedin' => array(
				'name' => __('Linkedin', 'leads'),
				'desc' => __('Enter your linkedin profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'github' => array(
				'name' => __('Github', 'leads'),
				'desc' => __('Enter your github profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'pinterest' => array(
				'name' => __('Instagram', 'leads'),
				'desc' => __('Enter your instagram profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'pinterest' => array(
				'name' => __('Pinterest', 'leads'),
				'desc' => __('Enter your pinterest profile URL', 'leads'),
				'type' => 'text',
				'std' => ''
			),
			'rss' => array(
				'name' => __('RSS', 'leads'),
				'desc' => __('Enter your RSS feeds URL', 'leads'),
				'type' => 'text',
				'std' => ''
			)
		),
		'shortcode' => '[social_links facebook="{{facebook}}" twitter="{{twitter}}" google_plus="{{google_plus}}" linkedin="{{linkedin}}" github="{{github}}" pinterest="{{pinterest}}" /]',
		'popup_title' => 'Insert Social Link Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('social_links', 'inbound_shortcode_social_links');

	function inbound_shortcode_social_links( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'facebook' => '',
			'twitter' => '',
			'google_plus' => '',
			'linkedin' => '',
			'github' => '',
			'instagram' => '',
			'pinterest' => '',
			'rss' => ''
		), $atts));

		$out = '';

		$out .= '<ul class="inboundnow-social-links">';
		if( $facebook ) { $out .= '<li class="facebook"><a href="'. $facebook .'"><i class="icon-facebook icon-large"></i></a></li>'; }
		if( $twitter ) { $out .= '<li class="twitter"><a href="'. $twitter .'"><i class="icon-twitter icon-large"></i></a></li>'; }
		if( $google_plus ) { $out .= '<li class="google-plus"><a href="'. $google_plus .'"><i class="icon-google-plus icon-large"></i></a></li>'; }
		if( $linkedin ) { $out .= '<li class="linkedin"><a href="'. $linkedin .'"><i class="icon-linkedin icon-large"></i></a></li>'; }
		if( $github ) { $out .= '<li class="github"><a href="'. $github .'"><i class="icon-github icon-large"></i></a></li>'; }
		if( $instagram ) { $out .= '<li class="instagram"><a href="'. $instagram .'"><i class="icon-camera-retro icon-large"></i></a></li>'; }
		if( $pinterest ) { $out .= '<li class="pinterest"><a href="'. $pinterest .'"><i class="icon-pinterest icon-large"></i></a></li>'; }
		if( $rss ) { $out .= '<li class="rss"><a href="'. $rss .'"><i class="icon-rss icon-large"></i></a></li>'; }
		$out .= '</ul>';

		return $out;
	}