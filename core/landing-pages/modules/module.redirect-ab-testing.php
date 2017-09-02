<?php

define("QUICK_CACHE_ALLOWED", false);
define("DONOTCACHEPAGE", true);
define('DONOTCACHCEOBJECT', true);
define('DONOTCDN', true);

if (file_exists('./../../../../wp-load.php')) {
    include_once('./../../../../wp-load.php');
} else if (file_exists('./../../../../../wp-load.php')) {
    include_once('./../../../../../wp-load.php');
} else if (file_exists('./../../../../../../wp-load.php')) {
    include_once('./../../../../../../wp-load.php');
} else if (file_exists('./../../../../../../../wp-load.php')) {
    include_once('./../../../../../../../wp-load.php');
}

/**
 * Class LP_Variation_Rotation provides an external WP instance set of classes for controlling landing page rotation memory
 * @package LandingPages
 * @subpackage Variations
 */

class LP_Variation_Rotation {

    static $permalink_name;
    static $post_id;
    static $sticky_variations;
    static $last_loaded_variation;
    static $variations;
    static $marker;
    static $next_marker;
    static $destination_url;

    /**
     *    Executes Class
     */
    public function __construct() {

        self::load_variables();
        /*self::run_debug(); */
        self::redirect();

    }

    /**
     *    Loads Static Variables
     */
    private static function load_variables() {

		self::$permalink_name = (isset($_GET['permalink_name'])) ? sanitize_text_field($_GET['permalink_name']) : null;
		self::$post_id = self::load_post_id();
		self::$sticky_variations = Landing_Pages_Settings::get_setting('lp-main-landing-page-rotation-halt', false);
        self::$last_loaded_variation = (isset($_COOKIE['lp-loaded-variation-' . self::$permalink_name])) ? intval($_COOKIE['lp-loaded-variation-' . self::$permalink_name]) : null;

		if (self::$sticky_variations && self::$last_loaded_variation) {

            self::$destination_url = self::$last_loaded_variation;

            if (!isset($_GET)) {
                return;
            }

            $begin = (strstr(self::$destination_url, '?')) ? '' : '?';

            /* Keep GET Params */
            foreach ($_GET as $key => $value) {
                if ($key != "permalink_name") {
                    $old_params .= "&$key=" . $value;
                }
            }

            self::$destination_url = self::$destination_url . $begin . $old_params;

        } else {
            self::$variations = self::load_variations();
            self::$marker = self::load_marker();
            self::$next_marker = self::discover_next_variation();
            self::$destination_url = self::build_destination_url();
        }

	}

    /**
     *    Debug Information - Prints Class Variable Data
     */
    static function run_debug() {
        echo self::$variations . '<br>';
        echo self::$marker . '<br>';
        echo self::$next_marker . '<br>';
        echo self::$destination_url . '<br>';
        exit;
    }

    /**
     *    Loads the ID of the Landing Page
     */
    static function load_post_id() {
        global $wpdb;

        $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='landing-page'", self::$permalink_name));

        return $post_id;

    }

    /**
     *    Loads an Array of Active Variations Associated with Landing Page
     */
    static function load_variations() {

        $live_variations = array();

        $variations_string = get_post_meta(self::$post_id, 'lp-ab-variations', true);
        $variations = explode(',', $variations_string);
        $variations = array_filter($variations, 'is_numeric');

        /* Check the Status of Each Variation and Keep Live Ones */
        foreach ($variations as $key => $vid) {


            $variation_status = get_post_meta(self::$post_id, 'lp_ab_variation_status-' . $vid, true);

            if (!is_numeric($variation_status) || $variation_status == 1) {
                $live_variations[] = $vid;
            }

        }

        return $live_variations;
    }

    /**
     *    Loads Variation ID of Last Variation Loaded
     */
    static function load_marker() {

        $marker = get_post_meta(self::$post_id, 'lp-ab-variations-marker', true);

        if (!is_numeric($marker) || !in_array($marker, self::$variations)) {

            $marker = current(self::$variations);
        }

        return $marker;
    }

    /**
     *    Discovers Next Variation in Line
     */
    static function discover_next_variation() {

        /* Set Pointer to Correct Location in Variations Array */
        while (self::$marker != current(self::$variations)) {
            next(self::$variations);
        }

        /* Discover the next variation in the array */
        next(self::$variations);

        /* If the pointer is empty then reset array */
        if (!is_numeric(current(self::$variations))) {
            reset(self::$variations);
        }

        /* Save as Historical Data */
        update_post_meta(self::$post_id, 'lp-ab-variations-marker', current(self::$variations));

        return current(self::$variations);

    }

    /**
     *    Builds Redirect URL & Stores Cookie Data
     */
    static function build_destination_url() {

        /* Load Base URL */
        $url = get_permalink(self::$post_id);
        $old_params = null;

        /* Keep GET Params */
        foreach ($_GET as $key => $value) {
            if ($key != "permalink_name") {
                $old_params .= "&$key=" . $value;
            }
        }

        /* Build Final URL and Set Memory Cookies */
        $url = $url . "?lp-variation-id=" . self::$next_marker . $old_params;

        /* Set Memory Cookies */
        setcookie('lp-loaded-variation-' . self::$permalink_name, $url, time() + 60 * 60 * 24 * 30, "/");
        setcookie('lp-variation-id', self::$next_marker, time() + 3600, "/");

        return $url;
    }

    /**
     *    Redirects to Correct Variation
     */
    static function redirect() {
        if (count(self::$variations) > 1) {
            header("HTTP/1.1 302 Temporary Redirect");
        } else {
            header("HTTP/1.1 301 Moved Permanently");
        }

        header("Location: " . self::$destination_url);
        exit;
    }
}

new LP_Variation_Rotation;