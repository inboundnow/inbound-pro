<?php

/**
 * Class covers methods handling landing page edit screen
 * @package LandingPages
 * @subpackage Templates
 */

class Landing_Pages_Variations {

    public function __construct() {
        self::load_hooks();
    }

    public static function load_hooks() {

        /* load ajax listeners */
        add_action('wp_ajax_lp_clear_stats_action', array(__CLASS__, 'ajax_clear_stats'));
        add_action('wp_ajax_lp_clear_stats_single', array(__CLASS__, 'ajax_clear_stats_single'));

        /* alter preview link */
        add_filter('post_type_link', array(__CLASS__, 'prepare_filter_link'), 10, 1);
    }


    /**
     * Deletes variation for    a call to action
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation to delete
     *
     */
    public static function delete_variation($landing_page_id, $variation_id) {

        $variations = Landing_Pages_Variations::get_variations($landing_page_id);

        /* unset variation */
        if (($key = array_search($variation_id, $variations)) !== false) {
            unset($variations[$key]);
        }


        /* set next variation to be open */
        $current_variation_id = current($variations);
        $_SESSION['lp_ab_test_open_variation'] = $current_variation_id;
        $_GET['lp-variation-id'] = $current_variation_id;

        /* update variations */
        Landing_Pages_Variations::update_variations($landing_page_id, $variations);


        if ($variation_id > 0) {
            $suffix = '-' . $variation_id;
            $len = strlen($suffix);
        } else {
            $suffix = '';
            $len = strlen($suffix);
        }

        /*delete each meta value associated with variation */
        global $wpdb;
        $data = array();
        $wpdb->query("
				SELECT `meta_key`, `meta_value`
				FROM $wpdb->postmeta
				WHERE `post_id` = " . $landing_page_id . "
			");

        foreach ($wpdb->last_result as $k => $v) {
            $data[$v->meta_key] = $v->meta_value;
        };

        /*echo $len;exit; */
        foreach ($data as $key => $value) {
            if (substr($key, -$len) == $suffix) {
                delete_post_meta($landing_page_id, $key, $value);
            }
        }


    }

    /**
     * Pauses variation for a call to action
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation to delete
     *
     */
    public static function pause_variation($landing_page_id, $variation_id) {
        update_post_meta($landing_page_id, 'lp_ab_variation_status-' . $variation_id, '0');
    }

    /**
     * Activations variation for a call to action
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation to play
     *
     */
    public static function play_variation($landing_page_id, $variation_id) {
        update_post_meta($landing_page_id, 'lp_ab_variation_status-' . $variation_id, 1);
    }


    /**
     * Updates 'inbound-email-variations' meta key with json object
     *
     * @param INT $landing_page_id id of call to action
     * @param variations ARRAY of variation data
     *
     */
    public static function update_variations($landing_page_id, $variations) {

        if (is_array($variations)) {
            $variations = implode(',', $variations);
        }

        update_post_meta($landing_page_id, 'lp-ab-variations', $variations);

    }

    /**
     * Increments impression count for given cta and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     */
    public static function record_impression($landing_page_id, $variation_id) {

        $impressions = get_post_meta($landing_page_id, 'lp-ab-variation-impressions--' . $variation_id, true);

        if (!is_numeric($impressions)) {
            $impressions = 1;
        } else {
            $impressions++;
        }

        update_post_meta($landing_page_id, 'lp-ab-variation-impressions--' . $variation_id, $impressions);
    }


    /**
     * Prepare a variation id for a new variation
     *
     * @param INT $landing_page_id id of landing page
     *
     * @returns INT $vid variation id
     */
    public static function prepare_new_variation_id($landing_page_id) {

        $variations = self::get_variations($landing_page_id);

        sort($variations, SORT_NUMERIC);

        $vid = end($variations);

        return $vid + 1;
    }

    /* Adds variation id onto base meta key
     *
     * @param id STRING of meta key to store data into for given setting
     * @param INT $variation_id id of variation belonging to call to action, will attempt to autodetect if left as null
     *
     * @returns STRING of meta key appended with variation id
     */
    public static function prepare_input_id($id, $variation_id = null, $legacy = true) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        if ($variation_id > 0 || !$legacy) {
            return $id . '-' . $variation_id;
        } else {
            return $id;
        }
    }

    /**
     * Convert permalink to the correct variation preview link
     * @param $link
     * @return mixed
     */
    public static function prepare_filter_link($link) {

        if (!is_admin() || !function_exists('get_current_screen')) {
            return $link;
        }

        $screen = get_current_screen();

        if (!isset($screen) || $screen->parent_file != 'edit.php?post_type=landing-page' || strstr($link, '%')) {
            return $link;
        }

        if (strstr($link, 'lp-variation-id')) {
            return $link;
        }


        $vid = self::get_current_variation_id();

        $link = add_query_arg(array('lp-variation-id' => $vid), $link);

        return $link;
    }


    /**
     * Sets the variation status to a custom status
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation to delete
     * @param STRING $status custom status
     *
     */
    public static function set_variation_status($landing_page_id, $variation_id, $status = '1') {

        update_post_meta($landing_page_id, 'lp_ab_variation_status-' . $variation_id, $status);

    }


    /**
     *  Updates variation marker (used for single sends)
     * @param INT $landing_page_id
     * @param INT $variation_marker
     */
    public static function set_variation_marker($landing_page_id, $variation_marker) {

    }

    /**
     * Manually sets conversion count for given cta id and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     * @param INT $count
     *
     */
    public static function set_impressions_count($landing_page_id, $variation_id = 0, $count) {
        update_post_meta($landing_page_id, 'lp-ab-variation-impressions-' . $variation_id, $count);
    }

    /**
     * Manually sets conversion count for given cta id and variation id
     *
     * @param INT $landing_page_id id of landing page
     * @param INT $variation_id id of variation
     * @param INT $count
     */
    public static function set_conversions_count($landing_page_id, $variation_id, $count) {
        update_post_meta($landing_page_id, 'lp-ab-variation-conversions-' . $variation_id, $count);
    }

    /**
     * Returns array of variation data given a landing page id
     *
     * @param INT $landing_page_id id of landing page
     *
     * @returns ARRAY of variation data
     */
    public static function get_variations($landing_page_id) {

        $variations = get_post_meta($landing_page_id, 'lp-ab-variations', true);

        if (!is_array($variations) && $variations) {
            $variations = explode(',', $variations);
        }

        if (!is_array($variations) || !$variations) {
            $variations = array(0 => "0");
        }

        return $variations;
    }


    /**
     * Returns the status of a variation given landing_page_id and vid
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id variation id of call to action
     *
     * @returns STRING status
     */
    public static function get_variation_status($landing_page_id, $variation_id = null) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $variation_status = get_post_meta($landing_page_id, 'lp_ab_variation_status-' . $variation_id, true);

        if (!is_numeric($variation_status)) {
            return 1;
        } else {
            return $variation_status;
        }

    }


    /**
     *  Get next variation ID available
     * @param INT $landing_page_id
     * @return INT $next_variant_marker
     */
    public static function get_next_variant_marker($landing_page_id) {


    }

    /**
     * Returns the permalink of a variation given landing_page_id and vid
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id variation id of call to action
     *
     * @returns STRING permalink
     */
    public static function get_variation_permalink($landing_page_id, $variation_id = null) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $permalink = get_permalink($landing_page_id);

        return add_query_arg(array('lp-variation-id' => $variation_id), $permalink);
    }


    /**
     * Gets the call to action variation notes
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id variation id of call to action variation, will attempt to autodetect if left as null
     *
     * @return STRING $notes variation notes.
     */
    public static function get_variation_notes($landing_page_id, $variation_id = null) {
        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $variation_notes = get_post_meta($landing_page_id, 'lp-variation-notes-' . $variation_id, true);

        return $variation_notes;
    }

    /**
     * Gets the call to action variation custom css
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id variation id of call to action variation, will attempt to autodetect if left as null
     *
     * @return STRING $custom_css.
     */
    public static function get_custom_css($landing_page_id, $variation_id = null) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        return Landing_Pages_Variations::get_setting_value('lp-custom-css', $landing_page_id, $variation_id, '');

    }

    /**
     * Gets the call to action variation custom js
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id variation id of call to action variation, will attempt to autodetect if left as null
     *
     * @return STRING $custom_js.
     */
    public static function get_custom_js($landing_page_id, $variation_id = null) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        return Landing_Pages_Variations::get_setting_value('lp-custom-js', $landing_page_id, $variation_id, '');
    }

    /*
    * Gets the current variation id
    *
    * @returns INT of variation id
    */
    public static function get_current_variation_id() {
        global $post, $current_variation_id;

        if (isset($_SESSION['lp_ab_test_open_variation']) && isset($_GET['ab-action']) && is_admin()) {
            return $_SESSION['lp_ab_test_open_variation'];
        }

        /* check to see if this has already been set during the instance */
        if (is_numeric($current_variation_id)) {
            return $current_variation_id;
        }

        if (isset($_REQUEST['lp-variation-id'])) {
            $_SESSION['lp_ab_test_open_variation'] = intval($_REQUEST['lp-variation-id']);
            $current_variation_id = intval($_REQUEST['lp-variation-id']);
        }

        if (isset($_GET['message']) && $_GET['message'] == 1 && isset($_SESSION['lp_ab_test_open_variation'])) {
            $current_variation_id = $_SESSION['lp_ab_test_open_variation'];
        }

        if (isset($_GET['ab-action']) && $_GET['ab-action'] == 'delete-variation') {
            $current_variation_id = 0;
            $_SESSION['lp_ab_test_open_variation'] = 0;
        }

        if (isset($_GET['new_meta_key'])) {
            $current_variation_id = $_GET['new_meta_key'];
        }

        if (!isset($current_variation_id)) {
            if (!isset($post) && isset($_GET['post'])) {
                $post_id = $_GET['post'];
            } else if (isset($post)) {
                $post_id = $post->ID;
            } else {
                $post_id = 0;
            }

            $variations = self::get_variations($post_id);
            $id = array_values($variations);
            $current_variation_id = array_shift($id);
        }

        $GLOBALS['current_variation_id'] = $current_variation_id;

        return $current_variation_id;
    }

    /*
    * Looks up the variation id we should use for prepopulating settings on cloned variations and new variations
    *
    * @returns INT of variation id
    */
    public static function get_new_variation_reference_id($landing_page_id, $variation_id = null) {
        global $post;

        /* if no variation set look for variation */
        if (!isset($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        /* listen for new variation */
        if (isset($_REQUEST['new-variation']) && !isset($_REQUEST['clone'])) {
            $variations = Landing_Pages_Variations::get_variations($landing_page_id);
            $variation_id = key($variations);
        }

        /* listen for clone variation */
        if (isset($_REQUEST['new-variation']) && isset($_REQUEST['clone'])) {
            $variation_id = intval($_REQUEST['clone']);
        }

        return $variation_id;
    }

    /*
    * Gets the next available variation id
    *
    * @returns INT of variation id
    */
    public static function get_next_available_variation_id($landing_page_id) {

        $variations = Landing_Pages_Variations::get_variations($landing_page_id);
        $array_variations = $variations;

        end($array_variations);

        $last_variation_id = key($array_variations);

        return $last_variation_id + 1;
    }

    /*
    * Gets string id of template given email id
    *
    * @param INT $landing_page_id of call to action
    * @param INT $variation_id of variation id
    *
    * @returns STRING id of selected template
    */
    public static function get_current_template($landing_page_id, $variation_id = null) {

        if (!is_numeric($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        return Landing_Pages_Variations::get_setting_value('lp-selected-template', $landing_page_id, $variation_id, 'default');

    }

    /**
     * Get Screenshot URL for Call to Action preview. If local environment show template thumbnail.
     *
     * @param INT $landing_page_id id if of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     * @return STRING url of preview
     */
    public static function get_screenshot_url($landing_page_id, $variation_id = null) {

        if ($variation_id === null) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $template = Landing_Pages_Variations::get_current_template($landing_page_id, $variation_id);

        if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {

            if (file_exists(INBOUND_EMAIL_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png')) {
                $screenshot = INBOUND_EMAIL_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png';
            } else {
                $screenshot = INBOUND_EMAIL_URLPATH . 'templates/' . $template . '/thumbnail.png';
            }

        } else {
            $screenshot = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';
        }

        return $screenshot;
    }


    /**
     * Returns impression for given cta and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     * @return INT impression count
     */
    public static function get_impressions($landing_page_id, $variation_id) {

        if (!is_numeric($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $impressions = get_post_meta($landing_page_id, 'lp-ab-variation-impressions-' . $variation_id, true);
        $impressions = (is_numeric($impressions)) ? $impressions : 0;

        return $impressions;
    }

    /**
     * Returns impression for given cta and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     * @return INT conversion count
     */
    public static function get_conversions($landing_page_id, $variation_id) {

        if (!is_numeric($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        $conversions = get_post_meta($landing_page_id, 'lp-ab-variation-conversions-' . $variation_id, true);
        $conversions = (is_numeric($conversions)) ? $conversions : 0;

        return $conversions;
    }

    /**
     * Returns conversion rate for given cta and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     * @return INT conversion rate
     */
    public static function get_conversion_rate($landing_page_id, $variation_id) {

        $impressions = Landing_Pages_Variations::get_impressions($landing_page_id, $variation_id);
        $conversions = Landing_Pages_Variations::get_conversions($landing_page_id, $variation_id);

        if ($impressions > 0) {
            $conversion_rate = $conversions / $impressions;
            $conversion_rate_number = $conversion_rate * 100;
            $conversion_rate_number = round($conversion_rate_number, 2);
            $conversion_rate = $conversion_rate_number;
        } else {
            $conversion_rate = 0;
        }

        return $conversion_rate;
    }

    /**
     * @param $landing_page_id
     * @param null $variation_id
     */
    public static function get_conversion_area($landing_page_id, $variation_id = null) {

        return Landing_Pages_Variations::get_setting_value('lp-conversion-area', $landing_page_id, $variation_id);
    }

    /**
     * Returns conversion area placement
     * @param $landing_page_id
     * @param null $variation_id
     */
    public static function get_conversion_area_placement($landing_page_id, $variation_id = null) {
        $template = Landing_Pages_Variations::get_current_template($landing_page_id);
        return Landing_Pages_Variations::get_setting_value($template . '-conversion-area-placement', $landing_page_id, $variation_id);
    }

    /**
     * @param $landing_page_id
     * @param null $variation_id
     */
    public static function get_post_content($landing_page_id, $variation_id = null) {
        if (!is_numeric($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        return Landing_Pages_Variations::get_setting_value('content', $landing_page_id, $variation_id);
    }

    /**
     * Get main headline
     */
    public static function get_main_headline($landing_page_id, $variation_id = null) {

        if (!is_numeric($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        return Landing_Pages_Variations::get_setting_value('lp-main-headline', $landing_page_id, $variation_id, '');
    }

    /**
     * Gets thumbnail for selected template
     */
    public static function get_template_thumbnail($template) {

        if (file_exists(LANDINGPAGES_UPLOADS_PATH . $template . '/thumbnail.png')) {
            return LANDINGPAGES_UPLOADS_URLPATH . $template . '/thumbnail.png';
        } else {
            return LANDINGPAGES_URLPATH . 'templates/' . $template . '/thumbnail.png';
        }
    }


    /**
     * Gets stored setting value
     * @param $key
     * @param $landing_page_id
     * @param $variation_id
     * @param string $default
     * @return string
     */
    public static function get_setting_value($key, $landing_page_id, $variation_id = null, $default = '') {

        /* if no variation set look for variation */
        if (!isset($variation_id)) {
            $variation_id = Landing_Pages_Variations::get_current_variation_id();
        }

        /* listen for new variation */
        if (isset($_REQUEST['new-variation']) && !isset($_REQUEST['clone'])) {
            $variations = Landing_Pages_Variations::get_variations($landing_page_id);
            $variation_id = key($variations);
        }

        /* listen for clone variation */
        if (isset($_REQUEST['new-variation']) && isset($_REQUEST['clone'])) {
            $variation_id = intval($_REQUEST['clone']);
        }

        if ($variation_id > 0) {
            if (metadata_exists('post', $landing_page_id, $key . '-' . $variation_id)) {
                return get_post_meta($landing_page_id, $key . '-' . $variation_id, true);
            } else {
                return $default;
            }
        } else {

            if (metadata_exists('post', $landing_page_id, $key)) {
                return get_post_meta($landing_page_id, $key, true);
            } else {
                return $default;
            }
        }

    }


    /**
     * Increments conversion count for given landing page id and variation id
     *
     * @param INT $landing_page_id id of landing page
     * @param INT $variation_id id of variation belonging to call to action
     *
     */
    public static function record_conversion($landing_page_id, $variation_id) {

        $conversions = self::get_conversions($landing_page_id, $variation_id);

        if (!is_numeric($conversions)) {
            $conversions = 1;
        } else {
            $conversions++;
        }

        self::set_conversions_count($landing_page_id, $variation_id, $conversions);
    }

    /**
     * Appends current variation id onto a URL
     *
     * @param link STRING URL that param will be appended onto
     *
     *
     * @return STRING modified URL.
     */
    public static function append_variation_id_to_url($link) {
        global $post;

        if (!isset($post) || $post->post_type != 'landing-pages') {
            return $link;
        }

        $current_variation_id = Landing_Pages_Variations::get_current_variation_id();


        $link = add_query_arg(array('inbvid' => $current_variation_id), $link);

        return $link;
    }

    /**
     * Discovers which alphabetic letter should be associated with a given cta's variation id.
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $variation_id id of variation belonging to call to action
     *
     * @return STRING alphebit letter.
     */
    public static function vid_to_letter($landing_page_id, $variation_id) {
        $variations = Landing_Pages_Variations::get_variations($landing_page_id);

        $i = 0;
        foreach ($variations as $key => $variation) {
            if ($variation_id == $key) {
                break;
            }
            $i++;
        }

        $alphabet = array(__('A', 'landing-pages'), __('B', 'landing-pages'), __('C', 'landing-pages'), __('D', 'landing-pages'), __('E', 'landing-pages'), __('F', 'landing-pages'), __('G', 'landing-pages'), __('H', 'landing-pages'), __('I', 'landing-pages'), __('J', 'landing-pages'), __('K', 'landing-pages'), __('L', 'landing-pages'), __('M', 'landing-pages'), __('N', 'landing-pages'), __('O', 'landing-pages'), __('P', 'landing-pages'), __('Q', 'landing-pages'), __('R', 'landing-pages'), __('S', 'landing-pages'), __('T', 'landing-pages'), __('U', 'landing-pages'), __('V', 'landing-pages'), __('W', 'landing-pages'), __('X', 'landing-pages'), __('Y', 'landing-pages'), __('Z', 'landing-pages'));

        if (isset($alphabet[$i])) {
            return $alphabet[$i];
        }
    }


    /**
     * Adds Ajax for Clear Stats button
     * clear stats for all variations
     */
    public static function ajax_clear_stats() {
        global $wpdb;

        $landing_page_id = intval($_POST['page_id']);

        $variations = self::get_variations($landing_page_id);

        foreach ($variations as $vid) {
            update_post_meta($landing_page_id, 'lp-ab-variation-impressions-' . $vid, 0, false);
            update_post_meta($landing_page_id, 'lp-ab-variation-conversions-' . $vid, 0, false);
        }

        header('HTTP/1.1 200 OK');
    }


    /**
     * Adds Ajax for Clear Stats button
     * clear stats for single variations
     */
    public static function ajax_clear_stats_single() {
        global $wpdb;

        $landing_page_id = intval($_POST['page_id']);
        $vid = intval($_POST['variation']);

        self::set_impressions_count($landing_page_id, $vid, 0);
        self::set_conversions_count($landing_page_id, $vid, 0);

        header('HTTP/1.1 200 OK');
    }

}

new Landing_Pages_Variations();
