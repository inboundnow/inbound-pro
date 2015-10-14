<?php


/**
 * Get the folder name of the parent directory of the calling file
 * @param $path
 * @return mixed
 */
function inbound_get_parent_directory($path) {
    if (stristr($_SERVER['SERVER_SOFTWARE'], 'Win32')) {
        $array = explode('\\', $path);
        $count = count($array);
        $key = $count - 1;
        $parent = $array[$key];
        return $parent;
    } else if (stristr($_SERVER['SERVER_SOFTWARE'], 'IIS')) {
        $array = explode('\\', $path);
        $count = count($array);
        $key = $count - 1;
        $parent = $array[$key];
        return $parent;
    } else {
        $array = explode('/', $path);
        $count = count($array);
        $key = $count - 1;
        $parent = $array[$key];
        return $parent;
    }
}
