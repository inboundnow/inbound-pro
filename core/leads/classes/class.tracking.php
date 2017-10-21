<?php

/**
 * Class provides search and comment tracking features
 *
 * @package Leads
 * @subpackage Tracking
 */


class Leads_Tracking {

    /**
     * Initiate class
     */
    public function __construct() {
        self::add_hooks();
    }


    /**
     * Load hooks and filters
     */
    public static function add_hooks() {
        /* listen for set cookie calls */
        add_action('wp_head', array(__CLASS__, 'set_cookies'));

        /* listens for comments approval status to change and either saves them to events or removes them */
        add_action('transition_comment_status', array(__CLASS__, 'track_comment_approval'), 10, 3);

        /* listens for comments that are auto approved, and saves them to events */
        add_action('wp_insert_comment', array(__CLASS__, 'track_comment_inserts'), 10, 2);

        /* saves user searches to events */
        add_action('wp_ajax_inbound_search_store', array(__CLASS__, 'ajax_inbound_search_store'), 10, 1);
        add_action('wp_ajax_nopriv_inbound_search_store', array(__CLASS__, 'ajax_inbound_search_store'), 10, 1);
    }

    /**
     * Listens for special frontend calls to set cookies
     */
    public static function set_cookies() {
        global $wpdb;

        if (isset($_GET['wpl_email'])) {
            $lead_id = $_GET['wpl_email'];

            $query = $wpdb->prepare(
                'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
                $lead_id
            );

            $wpdb->query($query);

            if ($wpdb->num_rows) {
                $lead_ID = $wpdb->get_var($query);
                setcookie('wp_lead_id', $lead_ID, time() + (20 * 365 * 24 * 60 * 60), '/');
            }
        }
    }

    /**
     * Cookies visitor's browser with proper lead list given a lead id
     * @param  string $lead_id - lead CPT id
     * @return sets cookie of lists lead belongs to
     */
    public static function cookie_lead_lists($lead_id, $lists = null) {

        if (is_array($lists)) {
            $lead_lists = array('ids' => $lists);
        } else {
            $terms = get_the_terms($lead_id, 'wplead_list_category');

            $lists = array();
            if ($terms && !is_wp_error($terms)) {

                foreach ($terms as $term) {
                    $lists[] = $term->term_id;
                }
            }
        }

        $lead_lists = json_encode(array('ids' => $lists));;

        setcookie('wp_lead_list', $lead_lists, time() + (20 * 365 * 24 * 60 * 60), '/');

    }

    /**
     * Cookies visitor's browser with proper lead list given a lead id
     * @param  string $lead_id - lead CPT id
     * @return sets cookie of lists lead belongs to
     */
    public static function cookie_lead_tags($lead_id, $tags = null) {
        $lead_tags = array();

        if (is_array($tags)) {
            $lead_tags = array('ids' => $tags);
        } else {
            $terms = get_the_terms($lead_id, 'lead-tags');
            $tags = array();

            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $tags[] = $term->term_id;
                }
            }

            $lead_tags = array('ids' => $tags);
        }

        $tags_json = json_encode($lead_tags);
        setcookie('wp_lead_tags', $tags_json, time() + (20 * 365 * 24 * 60 * 60), '/');

    }

    /**
     * wp_leads_update_page_view_obj updates page_views meta for known leads
     * @param  ARRAY $lead_data array of data associated with page view event
     */

    public static function update_page_views_object($lead_data) {

        if (!$lead_data['page_id']) {
            return;
        }

        $current_page_view_count = get_post_meta($lead_data['lead_id'], 'wpleads_page_view_count', true);

        $increment_page_views = $current_page_view_count + 1;

        update_post_meta($lead_data['lead_id'], 'wpleads_page_view_count', $increment_page_views); // update count

        $time = current_time('timestamp', 0); // Current wordpress time from settings
        $wordpress_date_time = date("Y-m-d G:i:s T", $time);

        $page_view_data = get_post_meta($lead_data['lead_id'], 'page_views', TRUE);
        //echo $lead_data['page_id']; // for debug

        // If page_view meta exists do this
        if ($page_view_data) {
            $current_count = 0; // default
            $timeout = 30;  // 30 Timeout analytics tracking for same page timestamps
            $page_view_data = json_decode($page_view_data, true);

            // increment view count on page
            if (isset($page_view_data[$lead_data['page_id']])) {
                $current_count = count($page_view_data[$lead_data['page_id']]);
                $last_view = $page_view_data[$lead_data['page_id']][$current_count - 1];
                $timeout = abs(strtotime($last_view) - strtotime($wordpress_date_time));
            }

            // If page hasn't been viewed in past 30 seconds. Log it
            if ($timeout >= 30) {
                $page_view_data[$lead_data['page_id']][$current_count] = $wordpress_date_time;
                $page_view_data = json_encode($page_view_data);
                update_post_meta($lead_data['lead_id'], 'page_views', $page_view_data);
            }

        } else {
            // Create page_view meta if it doesn't exist
            $page_view_data = array();
            $page_view_data[$lead_data['page_id']][0] = $wordpress_date_time;
            $page_view_data = json_encode($page_view_data);
            update_post_meta($lead_data['lead_id'], 'page_views', $page_view_data);
        }

        /**
         * Runs hook that tells WordPress lead data has been updated
         * @package Leads
         * @subpackage Hooks
         */
        do_action('wplead_page_view', $lead_data);
    }

    /**
     * Tracks the change of comment statuses. Approved or Unapproved
     * @param $new_status
     * @param $old_status
     * @param $comment [the comment object]
     */
    public static function track_comment_approval($new_status, $old_status, $comment) {

        /* if comment tracking is turned off exit */
        if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
            if (get_option('wpl-main-comment-tracking', '') == 0) {
                return;
            }
        } else {
            $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
            if ($settings['leads']['comment-tracking'] == 0) {
                return;
            }
        }

        /* if the comment has been approved, add it to the db */
        if ($comment->comment_approved == 1) {

            /* get the lead id */
            $lead_id = LeadStorage::lookup_lead_by_email($comment->comment_author_email);

            /*if the lead exists*/
            if ($lead_id) {

                /* if the comment isn't already stored */
                if (!Inbound_Events::comment_exists($comment->comment_ID)) {

                    /* exit if the comment came from a source we don't want to listen to */
                    $agents_to_ignore = array('WooCommerce');
                    if (in_array($comment->comment_agent, $agents_to_ignore)) {
                        return;
                    }

                    $datetime = date_i18n('Y-m-d G:i:s T', strtotime($comment->comment_date));

                    $args = array(
                        'page_id' => $comment->comment_post_ID,
                        'lead_id' => $lead_id,
                        'comment_id' => $comment->comment_ID,
                        'event_details' => json_encode(array(
                            'comment_id' => $comment->comment_ID,
                            'comment_content' => ($comment->comment_content) ? $comment->comment_content : '',
                            'comment_author' => ($comment->comment_author) ? $comment->comment_author : '',
                            'comment_author_email' => ($comment->comment_author_email) ? $comment->comment_author_email : '',
                            'comment_author_url' => ($comment->comment_author_url) ? $comment->comment_author_url : '',
                            'comment_agent' => ($comment->comment_agent) ? $comment->comment_agent : '',
                            'comment_type' => ($comment->comment_type) ? $comment->comment_type : '',
                            'comment_parent' => ($comment->comment_parent) ? $comment->comment_parent : '',
                            'user_id' => ($comment->user_id) ? $comment->user_id : '',
                            'page_id' => $comment->comment_post_ID,
                        )),
                        'datetime' => $datetime,
                    );
                    /* store the approved comment event */
                    Inbound_Events::store_comment_event($args);
                }
            }
        }

        /* if the comment was unapproved later, remove it from the db */
        if ($comment->comment_approved != 1) {

            $lead_id = LeadStorage::lookup_lead_by_email($comment->comment_author_email);

            if ($lead_id) { // if there isn't a lead id, then the comment isn't stored in the db

                Inbound_Events::remove_comment_event((int)$comment->comment_ID);
            }
        }
    }

    /**
     * Tracks comments that are auto approved
     * @param $id [comment id]
     * @param $comment [the comment object]
     */
    public static function track_comment_inserts($id, $comment) {
        global $inbound_settings;

        $inbound_settings['leads']['comment-tracking'] = (isset($inbound_settings['leads']['comment-tracking'])) ? $inbound_settings['leads']['comment-tracking'] : 1;
        /* exit if comment tracking is turned off */
        if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
            if (get_option('wpl-main-comment-tracking', '') == 0) {
                return;
            }
        } else {
            $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
            if ($inbound_settings['leads']['comment-tracking'] == 0) {
                return;
            }
        }

        if ($comment->comment_approved == 1) {

            $lead_id = LeadStorage::lookup_lead_by_email($comment->comment_author_email);

            if ($lead_id) {

                /* exit if the comment came from a source we don't want to listen to */
                $agents_to_ignore = array('WooCommerce');
                if (in_array($comment->comment_agent, $agents_to_ignore)) {
                    return;
                }

                /* get the current time */
                $datetime = date_i18n('Y-m-d G:i:s');

                $args = array(
                    'page_id' => $comment->comment_post_ID,
                    'lead_id' => $lead_id,
                    'comment_id' => $comment->comment_ID,
                    'event_details' => json_encode(array(
                        'comment_id' => $comment->comment_ID,
                        'comment_content' => ($comment->comment_content) ? $comment->comment_content : '',
                        'comment_author' => ($comment->comment_author) ? $comment->comment_author : '',
                        'comment_author_email' => ($comment->comment_author_email) ? $comment->comment_author_email : '',
                        'comment_author_url' => ($comment->comment_author_url) ? $comment->comment_author_url : '',
                        'comment_agent' => ($comment->comment_agent) ? $comment->comment_agent : '',
                        'comment_type' => ($comment->comment_type) ? $comment->comment_type : '',
                        'comment_parent' => ($comment->comment_parent) ? $comment->comment_parent : '',
                        'user_id' => ($comment->user_id) ? $comment->user_id : '',
                        'page_id' => $comment->comment_post_ID,
                    )),
                    'datetime' => $datetime,
                );

                Inbound_Events::store_comment_event($args);
            }
        }
    }

    /**
     * Stores the user's searches in the database
     * @param $_POST ['data'] (array) lead data to store
     * @param $_POST ['nonce'] (string)
     */
    public static function ajax_inbound_search_store() {
        error_log('ajax_inbound_search_store');
        $timezone = get_option('gmt_offset');
        $data = json_decode(stripslashes(urldecode($_POST['data'])), true);
        $lead_id = intval($_POST['lead_id']);

        /* sanitize the input */
        $clean_data = array();
        foreach ($data as $key => $value) {
            error_log('data');
            $clean_data[$key] = array_map(function ($sub_value) {

                if (!isset($sub_value) && empty($sub_value)) {
                    return 0;
                }

                /* if the value isn't url encoded, return the sanitized value */
                if (urldecode($sub_value) === $sub_value) {
                    return sanitize_text_field($sub_value);
                }

                return sanitize_text_field(urldecode($sub_value));

            }, $value);
        }

        /* if a lead id isn't supplied directly, see if one was passed in the search data */
        if (!$lead_id) {
            error_log('here 1');
            $lead_id = 0;
            $emails = array();
            foreach ($clean_data as $key => $value) {
                if (isset($value['lead_id']) && !empty($value['lead_id'])) {
                    $lead_id = $value['lead_id'];
                    break;
                }

                if (is_email($value['email']) && !empty($value['email'])) {
                    $email[] = $value['email'];
                }
            }
        }

        /* if there isn't a lead id */
        if ($lead_id === 0) {
            error_log('here 2');
            // see if the email is provided, get lead_id from that if possible
            if (!empty($emails)) {
                $counted_emails = array_count_values($emails);
                foreach ($counted_emails as $email => $count) {
                    $lead = get_page_by_title($email, 'OBJECT', 'wp-lead');
                    if (!empty($lead) && !null) {
                        $lead_id = $lead->ID;
                        break;
                    }
                }
            }

            // exit if we still don't have a lead id
            if ($lead_id == 0) {
                error_log('die');
                // echo json_encode( array( 'error' => __( 'No id provided', 'inbound-pro' ) ) );
                die();
            }
        }

        if (!wp_verify_nonce($_POST['nonce'], 'inbound_lead_' . $lead_id . '_nonce')) {
            error_log('nonce');
            // echo json_encode( array( 'error' => __( 'Invalid nonce', 'inbound-pro' ) ) );
            die();
        }

        // check to see if the lead exists
        $lead = get_post($lead_id);

        // if the lead doesn't exist, exit
        if (!$lead) {
            error_log('no lead');
            die();
        }

        foreach ($clean_data as $key => $value) {
            /* if the gmt offset is set, account for wp time */
            if (!empty($timezone) || $timezone === 0) {
                $value['timestamp'] += ($timezone * 3600);
            }

            $args = array(
                'page_id' => $value['page_id'],
                'lead_id' => $lead_id,
                'lead_uid' => $value['user_UID'],
                'variation_id' => $value['variation'],
                'event_details' => json_encode(array(
                    'search_data' => $value['search_data'],
                    'post_type' => $value['post_type'],
                    'ip_address' => $value['ip_address'],
                    'page_id' => $value['page_id'],
                    'datetime' => date_i18n('Y-m-d G:i:s T', $value['timestamp']),
                )),
                'source' => $value['source'],
                'datetime' => date('Y-m-d G:i:s T', $value['timestamp']),
            );

            Inbound_Events::store_search_event($args);
            error_log('stored');
        }

        wp_send_json(array('success' => __('Searches successfully stored!', 'inbound-pro')));

    }
}

new Leads_Tracking;
