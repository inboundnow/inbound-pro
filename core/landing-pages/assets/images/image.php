<?php


if (!function_exists('inbound_sanitize_this')) {
    function inbound_sanitize_this($color) {
        if (!strstr($color,'#')) {
            $color = '#'. $color;
        }

        // 3 or 6 hex digits, or the empty string.
        if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {


            return $color;
        }
    }
}

// file: image.php
// Dynamically Create a clear png for css background opacities
header("Content-type: image/png");

$hex_value = inbound_sanitize_this($_GET['hex']);

if (isset($_GET['trans'])) {
    $trans_value = intval($_GET['trans']);
} else {
    $trans_value = 50;
}

if (!function_exists('_inbound_HexToRGB')) {
    // Convert Hex to RGB Value
    function _inbound_HexToRGB($hex) {
        $hex = preg_replace("/#/", "", $hex);
        $color = array();

        if (strlen($hex) == 3) {
            $color['r'] = hexdec(substr($hex, 0, 1) . $r);
            $color['g'] = hexdec(substr($hex, 1, 1) . $g);
            $color['b'] = hexdec(substr($hex, 2, 1) . $b);
        } else if (strlen($hex) == 6) {
            $color['r'] = hexdec(substr($hex, 0, 2));
            $color['g'] = hexdec(substr($hex, 2, 2));
            $color['b'] = hexdec(substr($hex, 4, 2));
        }

        return $color;

    }
}
$RBG_array = _inbound_HexToRGB($hex_value);

if (isset($RBG_array)) {
    $red = (isset($RBG_array['r'])) ? $RBG_array['r'] : '0';
    $green = (isset($RBG_array['g'])) ? $RBG_array['g'] : '0';
    $blue = (isset($RBG_array['b'])) ? $RBG_array['b'] : '0';

    // Set the image
    $img = imagecreatetruecolor(10, 10); // 10 x 10 px
    imagesavealpha($img, true);

    // Fill the image with transparent color
    $color = imagecolorallocatealpha($img, $red, $green, $blue, $trans_value);
    imagefill($img, 0, 0, $color);

    // Return the image
    imagepng($img);

    // Destroy image
    imagedestroy($img);

}

// usage in html: <image src="path-to-file/image.php?hex=HEXCOLOR">
// Make sure to add in the HEX GET Parameters with ?hex= and ?trans= for transparency
// example: <image src="path-to-file/image.php?hex=ffffff"> will call white transparent png
