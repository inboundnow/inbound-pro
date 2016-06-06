<?php
/**
*	Google Map Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['gmap'] = array(
		'no_preview' => true,
		'options' => array(
			'type' => array(
				'name' => __('Map Type', 'inbound-pro' ),
				'desc' => __('Select a map type', 'inbound-pro' ),
				'type' => 'select',
				'options' => array('HYBRID' => 'Hybrid', 'ROADMAP' => 'Road Map', 'SATELLITE' => 'Satellite', 'TERRAIN' => 'Terrain'),
				'std' => ''
			),
			'width' => array(
				'name' => __('Map Width', 'inbound-pro' ),
				'desc' => '',
				'type' => 'text',
				'std' => ''
			),
			'height' => array(
				'name' => __('Map Height', 'inbound-pro' ),
				'desc' => '',
				'type' => 'text',
				'std' => ''
			),
			'address' => array(
				'name' => __('Address', 'inbound-pro' ),
				'desc' => __('Enter your address here. ex: "New York" or "New York, USA" If you want a accurate position please fill the Latitude and Longitude fields', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'latitude' => array(
				'name' => __('Latitude', 'inbound-pro' ),
				'desc' => __('Latitude is specified in degrees within the range [-90, 90]', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'longitude' => array(
				'name' => __('Longitude', 'inbound-pro' ),
				'desc' => __('Longitude is specified in degrees within the range [-180, 180]', 'inbound-pro' ),
				'type' => 'text',
				'std' => ''
			),
			'zoom' => array(
				'name' => __('Zoom', 'inbound-pro' ),
				'desc' => __('Set the zoom level, numeric value from 1 to 19', 'inbound-pro' ),
				'type' => 'text',
				'std' => '8'
			),
			'pancontrol' => array(
				'name' => __('Pan Control', 'inbound-pro' ),
				'desc' => __('Enable pan control?', 'inbound-pro' ),
				'type' => 'select',
				'options' => array('true' => 'Enable', 'false' => 'Disable'),
				'std' => ''
			),
			'zoomcontrol' => array(
				'name' => __('Zoom Control', 'inbound-pro' ),
				'desc' => __('Enable zoom control?', 'inbound-pro' ),
				'type' => 'select',
				'options' => array('true' => 'Enable', 'false' => 'Disable'),
				'std' => ''
			),
			'maptypecontrol' => array(
				'name' => __('Map Type Control', 'inbound-pro' ),
				'desc' => __('Enable map type control?', 'inbound-pro' ),
				'type' => 'select',
				'options' => array('true' => 'Enable', 'false' => 'Disable'),
				'std' => ''
			),
			'content' => array(
				'name' => __('Popup Content', 'inbound-pro' ),
				'desc' => __('A descriptive text for the Google Map marker popup, ex: "My Office"', 'inbound-pro' ),
				'type' => 'textarea',
				'std' => ''
			)
		),
		'shortcode' => '[gmap type="{{type}}" address="{{address}}" lat="{{latitude}}" lng="{{longitude}}" zoom="{{zoom}}" pancontrol="{{pancontrol}}" zoomcontrol="{{zoomcontrol}}" maptypecontrol="{{maptypecontrol}}" width="{{width}}" height="{{height}}"]{{content}}[/gmap]',
		'popup_title' => 'Insert Google Map Shortcode'
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('gmap', 'inbound_shortcode_gmap');

	function inbound_shortcode_gmap( $atts, $content = null ) {

		extract(shortcode_atts(array(
			'type' => 'ROADMAP',
			'address' => '',
			'lat' => '',
			'lng' => '',
			'width' => '',
			'height' => 400,
			'zoom' => 8,
			'scroll' => 'true',
			'pancontrol' => 'true',
			'zoomcontrol' => 'true',
			'maptypecontrol' => 'true'
		), $atts));
		$out = '';
		if ( !$lat == '' && !$lng == '') :
		$out .= '<div id="google_map_'.preg_replace('![^a-z0-9]+!i', '', $lat) . preg_replace('![^a-z0-9]+!i', '', $lng) . $type .'" class="google_map"	style="width:'.$width.'px;height:'.$height.'px"></div>' . "\n";
		$out .= '
		<script type="text/javascript">
		(function($) {
			$("#google_map_'.preg_replace('![^a-z0-9]+!i', '', $lat) . preg_replace('![^a-z0-9]+!i', '', $lng) . $type .'").gMap({
				latitude:'.$lat.',
				longitude:'.$lng.',
				maptype:"'.$type.'",
				scrollwheel: '.$scroll.',
				zoom:'.$zoom.',
				controls: {
					panControl:'.$pancontrol.',
					zoomControl:'.$zoomcontrol.',
					mapTypeControl:'.$maptypecontrol.'
				},
				markers: [{
					latitude:'.$lat.',
					longitude:'.$lng.',
					html: "'.$content.'"
				}]
			});
		})(jQuery);
		</script>';
		else :
		$out .= '<div id="google_map_'.preg_replace('![^a-z0-9]+!i', '',$address) . $type .'" class="google_map" style="width:'.$width.'px;height:'.$height.'px"></div>' . "\n";
		$out .= '
		<script type="text/javascript">
		(function($) {
			$("#google_map_'.preg_replace('![^a-z0-9]+!i', '',$address) . $type .'").gMap({
				address:"'.$address.'",
				maptype:"'.$type.'",
				scrollwheel: '.$scroll.',
				zoom:'.$zoom.',
				controls: {
					panControl:'.$pancontrol.',
					zoomControl:'.$zoomcontrol.',
					mapTypeControl:'.$maptypecontrol.'
				},
				markers: [{
					address:"'.$address.'",
					html: "'.$content.'"
				}]
			});
		})(jQuery);
		</script>';
		endif;

		return $out;
	}