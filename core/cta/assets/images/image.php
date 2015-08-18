<?php 
// file: image.php
// Dynamically Create a clear png for css background opacities
header("Content-type: image/png");

$hex_value = $_GET['hex'];

if (isset($_GET['trans'])) {
    $trans_value = $_GET['trans']; 
}
else {
    $trans_value = 50;
}

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
$red = $RBG_array [r];
$green = $RBG_array [g];
$blue = $RBG_array [b];
 
// Set the image 
$img = imagecreatetruecolor(10,10); // 10 x 10 px
imagesavealpha($img, true); 

// Fill the image with transparent color 
$color = imagecolorallocatealpha($img,$red,$green,$blue,$trans_value); 
imagefill($img, 0, 0, $color); 

// Return the image
imagepng($img); 

// Destroy image 
imagedestroy($img);

// usage in html: <image src="path-to-file/image.php?hex=HEXCOLOR">
// Make sure to add in the HEX GET Parameters with ?hex= and ?trans= for transparency
// example: <image src="path-to-file/image.php?hex=ffffff"> will call white transparent png
?>