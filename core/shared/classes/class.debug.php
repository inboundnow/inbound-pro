<?php

/**
 * Class for adding dev debug listeners
 * @package     Shared
 * @subpackage  DebugTricks
 */
class Inbound_Debug_Scripts {

    /**
     * Construct class / add hooks & filters
     */
    public function __construct() {
        add_action('init', array(__CLASS__, 'inbound_output_meta_debug'));
    }

    /**
     * dump post type object meta data
     */
    public static function inbound_output_meta_debug() {
        /*print all global fields for post */
        if (isset($_GET['debug']) && (isset($_GET['post']) && is_numeric($_GET['post']))) {
            global $wpdb;
            $data = array();
            $wpdb->query("
			SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = " . intval($_GET['post']) . "
			");

            foreach ($wpdb->last_result as $k => $v) {
                $data[$v->meta_key] = $v->meta_value;
            };
            if (isset($_GET['post'])) {
                echo "<pre>";
                print_r($data);
                echo "</pre>";
            }
        }
    }

}


new Inbound_Debug_Scripts;
