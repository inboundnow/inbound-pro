<?php 

// Convert Hex to RGB Value for submit button
if (!function_exists('inbound_Hex_2_RGB')) {
	function inbound_Hex_2_RGB($hex) {
	        $hex = preg_replace("/#/", "", $hex);
	        $color = array();
	 
	        if(strlen($hex) == 3) {
	            $color['r'] = hexdec(substr($hex, 0, 1) . $r);
	            $color['g'] = hexdec(substr($hex, 1, 1) . $g);
	            $color['b'] = hexdec(substr($hex, 2, 1) . $b);
	        }
	        else if(strlen($hex) == 6) {
	            $color['r'] = hexdec(substr($hex, 0, 2));
	            $color['g'] = hexdec(substr($hex, 2, 2));
	            $color['b'] = hexdec(substr($hex, 4, 2));
	        }
	        return $color;    
	}
}
/**
 * Grabs Color Scheme from Hex Color. Returns lighter & darker versions of the orignal hex color
 *
 * @access public
 * @since 1.5
 * @param string $hex_color hexidemical color
 * @return array $return_scheme Color Schemes
 */
if (!function_exists('inbound_color_scheme')) {
	function inbound_color_scheme($hex_color, $format = 'hex' ){
		if (strpos($hex_color,'#') !== false) {
		    $input = $hex_color;
		} else {
			$input = "#" . $hex_color;
		}

		$col = Array(
		    hexdec(substr($input,1,2)),
		    hexdec(substr($input,3,2)),
		    hexdec(substr($input,5,2))
		);

		$color_scheme_array = 
		array(
				100 => array( $col[0]/4, $col[1]/4, $col[2]/4), 
				95 => array( $col[0]/3, $col[1]/3, $col[2]/3),
				90 => array( $col[0]/2.7, $col[1]/2.7, $col[2]/2.7),
				85 => array( $col[0]/2.5, $col[1]/2.5, $col[2]/2.5),
				80 => array( $col[0]/2.2, $col[1]/2.2, $col[2]/2.2),
				75 => array( $col[0]/2, $col[1]/2, $col[2]/2),
				70 => array( $col[0]/1.7, $col[1]/1.7, $col[2]/1.7),
				65 => array( $col[0]/1.5, $col[1]/1.5, $col[2]/1.5),
				60 => array( $col[0]/1.3,$col[1]/1.3,$col[2]/1.3),
				55 => array( $col[0]/1.1,$col[1]/1.1,$col[2]/1.1),
				50 => array( $col[0],$col[1],$col[2]),
				45 => array( 255-(255-$col[0])/1.1, 255-(255-$col[1])/1.1, 255-(255-$col[2])/1.1),
				40 => array( 255-(255-$col[0])/1.3, 255-(255-$col[1])/1.3, 255-(255-$col[2])/1.3),
				35 => array( 255-(255-$col[0])/1.5, 255-(255-$col[1])/1.5, 255-(255-$col[2])/1.5),
				30 => array( 255-(255-$col[0])/1.7, 255-(255-$col[1])/1.7, 255-(255-$col[2])/1.7),
				25 => array( 255-(255-$col[0])/2, 255-(255-$col[1])/2, 255-(255-$col[2])/2),
				20 => array( 255-(255-$col[0])/2.2, 255-(255-$col[1])/2.2, 255-(255-$col[2])/2.2),
				15 => array( 255-(255-$col[0])/3, 255-(255-$col[1])/2.7, 255-(255-$col[2])/3),
				10 => array(255-(255-$col[0])/5, 255-(255-$col[1])/5, 255-(255-$col[2])/5),
				5 => array(255-(255-$col[0])/10, 255-(255-$col[1])/10, 255-(255-$col[2])/10),
				0 => array(255-(255-$col[0])/15, 255-(255-$col[1])/15, 255-(255-$col[2])/15)
				); 

		($format === 'hex') ? $sign = "#" : $sign = '';
		$return_scheme = array();
		foreach ($color_scheme_array as $key => $val) {

			$each_color_return =	$sign.sprintf("%02X%02X%02X", $val[0], $val[1], $val[2]);
		    $return_scheme[$key] = $each_color_return;

		}
			//return $closest;
			if(isset($_GET['color_scheme'])) {
				foreach ($return_scheme as $key => $hex_value) {
					echo "<div style='background:$hex_value; display:block; width:100%;'>$key</div>";
				}
			} 

			return $return_scheme;

		}
}
/**
 * Grabs Specific Color from Lighter/Darker Hex Color Scheme
 *
 * @access public
 * @since 1.5
 * @param array $color_scheme_array from inbound_color_scheme() function
 * @param int $value Light/darkness 1-100
 * @return string $color returns Hex Colors with #
 */
if (!function_exists('inbound_color')) {
function inbound_color($color_scheme_array, $value) {

//print_r($color_scheme_array);
foreach ($color_scheme_array as $key => $val) {
	$closest[$key] = abs($key - $value);
}
asort($closest);
$return_hex_val = key($closest);
$color = $color_scheme_array[$return_hex_val];

return $color;
}
}
?>