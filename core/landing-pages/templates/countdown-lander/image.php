<?php
// file: image.php
// Dynamically Create a clear png for css background opacities
header("Content-type: image/png");

$hex_value = $_GET['hex'];
// Convert Hex to RGB Value
function HexToRGB($hex) {
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

$RBG_array = HexToRGB($hex_value);
$red = (isset($RBG_array['r'])) ? $RBG_array['r'] : '0';
$green = (isset($RBG_array['g'])) ? $RBG_array['g'] : '0';
$blue = (isset($RBG_array['b'])) ? $RBG_array['b'] : '0';

// Set the image
$img = imagecreatetruecolor(10,10); // 10 x 10 px
imagesavealpha($img, true);

// Fill the image with transparent color
$color = imagecolorallocatealpha($img,$red,$green,$blue,50); // 50 is the opacity
imagefill($img, 0, 0, $color);

// Return the image
imagepng($img);

// Destroy image
imagedestroy($img);

// usage in html: <image src="path-to-file/image.php?hex=HEXCOLOR">
// Make sure to add in the HEX GET Parameters with ?hex=
// example: <image src="path-to-file/image.php?hex=ffffff"> will call white transparent png
?>