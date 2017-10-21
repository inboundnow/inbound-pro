<?php

/**
 * Class Inbound_Mail_Daemon provides cronjob daemon for reading `inbound_email_queue` and processing sends.
 * @package Mailer
 * @subpackage Sending
 */
class Inbound_Mail_Daemon {

    static $table_name; /* name of the mysql table we use for querying queued emails */
    static $email_service; /* number of emails we send during a processing job	(wp_mail only) */
    static $send_limit; /* number of emails we send during a processing job	(wp_mail only) */
    static $thread_limit; /* number of emails we send during a processing job	(wp_mail only) */
    static $timestamp; /* the current date time in ISO 8601 gmdate() */
    static $dom; /* reusable object for parsing html for link modification */
    static $row; /* current mysql row object being processed */
    static $email_settings; /* settings array of the email being processed */
    static $templates; /* array of html templates for processing */
    static $tags; /* array of html templates for processing */
    static $email; /* arg array of email being processed */
    static $results; /* results from sql query */
    static $response; /* return result after send */
    static $error_mode; /* detects if there is an error flag already in the data base */
    static $last; /* time measurement */
    static $first; /* time measurement */

    /**
     *    Initialize class
     */
    function __construct() {

        /* Load static vars */
        self::load_static_vars();

        /* Load hooks */
        self::load_hooks();

    }

    /**
     *    Loads static variables
     */
    public static function load_static_vars() {
        global $wpdb, $inbound_settings;

        /* Set email service */
        self::$email_service = (isset($inbound_settings['mailer']['mail-service'])) ? $inbound_settings['mailer']['mail-service'] : 'sparkpost';

        /* Set send limit */
        self::$send_limit = (isset($inbound_settings['mailer']['processing-limit'])) ? $inbound_settings['mailer']['processing-limit'] : 100;

        /* Set thread limit */
        self::$thread_limit = (isset($inbound_settings['mailer']['thread-limit'])) ? $inbound_settings['mailer']['thread-limit'] : 1;

        /* Set target mysql table name */
        self::$table_name = $wpdb->prefix . "inbound_email_queue";

        /* Get now timestamp */
        self::$timestamp = gmdate("Y-m-d\\TG:i:s\\Z");

        /* Check if there is an error flag in db from previous processing run */
        self::$error_mode = Inbound_Options_API::get_option('inbound-email', 'errors-detected', false);

    }

    /*
    * Load Hooks & Filters
    */
    public static function load_hooks() {

        /* If no email service set then abort loading class */
        if (!self::$email_service) {
            return;
        }

        /* Adds mail processing to Inbound Heartbeat */
        add_action('inbound_mailer_heartbeat', array(__CLASS__, 'process_mail_queue'));

        /* For debugging */
        add_filter('init', array(__CLASS__, 'process_mail_queue'), 12);

    }


    public static function process_mail_queue() {

        if (!isset($_GET['forceprocess']) && current_filter() == 'init') {
            return;
        }

        /* send automation emails */
        self::send_automated_emails();

        /* send batch emails */
        self::send_batch_emails();

    }


    /**
     *    Tells WordPress to send emails as HTML
     */
    public static function toggle_email_type() {
        add_filter('wp_mail_content_type', array(__CLASS__, 'toggle_email_type_html'));
    }

    /**
     *    Set email type to html for wp_mail
     */
    public static function toggle_email_type_html($type) {
        return 'text/html';
    }

    /**
     *    Loads DOMDocument class object
     */
    public static function toggle_dom_parser() {
        self::$dom = new DOMDocument;
    }

    /**
     *    Rebuild links with tracking params
     */
    public static function rebuild_links($html) {
        preg_match_all('/href="([^\s"]+)/', $html, $links);

        if (!$links) {
            return $html;
        }

        /* Iterate over the extracted links and display their URLs */
        foreach ($links[1] as $link) {

            /* Do not modify unsubscribe links or non links */
            if (strstr($link, '?token=') || !strstr($link, '://')) {
                continue;
            }

            $safe_link = Inbound_API::analytics_track_links(array(
                'email_id' => self::$row->email_id,
                'lead_lists' => implode(',', self::$email_settings['recipients']),
                'id' => self::$row->lead_id,
                'lead_id' => self::$row->lead_id,
                'page_id' => 0,
                'vid' => self::$row->variation_id,
                'url' => $link,
                'utm_source' => urlencode(self::$email['email_title']),
                'utm_medium' => 'email',
                'utm_campaign' => '',
                'tracking_id' => urlencode(self::$email['email_title'])
            ));

            $html = str_replace("'" . $link . "'", "'" . $safe_link['url'] . "'", $html);
            $html = str_replace('"' . $link . '"', '"' . $safe_link['url'] . '"', $html);

        }

        return $html;
    }


    /**
     *    Sends scheduled automated emails
     */
    public static function send_automated_emails() {
        global $wpdb;

        $query = "select * from " . self::$table_name . " WHERE `status` != 'processed' && `type` = 'automated' && `datetime` <	'" . self::$timestamp . "' && `email_id` = `email_id` order by email_id  ASC LIMIT " . self::$send_limit;
        self::$results = $wpdb->get_results($query);

        if (!self::$results) {
            return;
        }

        /* get first row of result set for determining email_id */
        self::$row = self::$results[0];

        /* Get email title */
        self::$email['email_title'] = get_the_title(self::$row->email_id);

        /* Get email settings if they have not been loaded yet */
        self::$email_settings = Inbound_Email_Meta::get_settings(self::$row->email_id);

        /* set list ids if available */
        if (isset(self::$row->list_ids)) {
            self::$email_settings['recipients'] = json_decode(self::$row->list_ids ,true);
        }

        /* Build array of html content for variations */
        self::get_templates();

        /* Get tags for this email */
        self::get_tags();

        /* Make sure we send emails as html */
        self::toggle_email_type();

        /* load dom parser class object */
        self::toggle_dom_parser();

        $i=0;
        //error_log('Starting Cronjob' . self::time_elapsed());
        foreach (self::$results as $row) {

            self::$row = $row;

            //error_log('Starting Email ' . self::time_elapsed());
            self::get_email();

            switch (self::$email_service) {
                case "sparkpost":
                    Inbound_Mailer_SparkPost::send_email( true ); /* send immediately */
                    break;
            }

            /* check response for errors  */
            self::check_response();
            //error_log('Check Response ' . self::time_elapsed());

            /* if error in batch then bail on processing job */
            if (self::$error_mode) {
                error_log('error mode');
                return;
            }
            self::delete_from_queue();

            //error_log('Delete  '.$i.' From Queue ' . self::time_elapsed());
            $i++;
        }

        //error_log('Done');
        //error_log('Rows processed ' . $i);
    }

    /**
     *    Sends scheduled batch emails
     */
    public static function send_batch_emails() {
        global $wpdb;

        /* Get results for singular email id */
        $query = "select * from " . self::$table_name . " WHERE `status` != 'processed' && `type` = 'batch' && email_id = email_id order by email_id ASC LIMIT " . self::$send_limit;
        self::$results = $wpdb->get_results($query);

        if (!self::$results) {
            return;
        }

        /* get datetime */
        $wordpress_date_time = date_i18n('Y-m-d G:i:s');

        /* get first row of result set for determining email_id */
        self::$row = self::$results[0];

        /* Get email title */
        self::$email['email_title'] = get_the_title(self::$row->email_id);

        /* Get email settings if they have not been loaded yet */
        self::$email_settings = Inbound_Email_Meta::get_settings(self::$row->email_id);

        /* Build array of html content for variations */
        self::get_templates();

        /* Get tags for this email */
        self::get_tags();

        /* Make sure we send emails as html */
        self::toggle_email_type();

        /* load dom parser class object */
        self::toggle_dom_parser();

        $send_count = 1;
        foreach (self::$results as $row) {

            self::$row = $row;

            /* make sure not to try and send more than wp can handle */
            if ($send_count > self::$send_limit) {
                return;
            }

            /* skip sending if lead has temprarily paused email sending */
            $pass = self::check_stop_rules();
            if (!$pass) {
                self::delete_from_queue();
                continue;
            }


            self::get_email();

            /* delete from database queue */
            if (!self::$email['send_address']) {
                self::delete_from_queue();
                continue;
            }

            switch (self::$email_service) {
                case "sparkpost":
                    Inbound_Mailer_SparkPost::send_email();
                    break;
            }

            /* check response for errors  */
            self::check_response();

            /* if error in batch then bail on processing job */
            if (self::$error_mode) {
                return;
            }

            self::delete_from_queue();

            $send_count++;
        }

        /* mark batch email as sent if no more emails with this email id exists */
        $count = $wpdb->get_var("SELECT COUNT(*) FROM " . self::$table_name . " where email_id = '" . self::$row->email_id . "'");
        if ($count < 1) {
            self::mark_email_sent();
        }
    }


    /**
     *    Send email by lead id
     */
    public static function send_solo_email($args) {
        global $wpdb;

        if (!$args['email_id'] || !$args['email_address']) {
            return;
        }

        /* setup test tags */
        self::$tags[$args['email_id']] = (isset($args['tags'])) ? $args['tags'] : array('test');

        /* setup email send params */
        self::$row = new stdClass();
        self::$row->email_id = $args['email_id'];
        self::$row->variation_id = $args['vid'];
        self::$row->lead_id = (isset($args['lead_id'])) ? $args['lead_id'] : 0;
        self::$row->datetime = gmdate('Y-m-d h:i:s \G\M\T');
        self::$row->rule_id = (isset($args['rule_id'])) ? $args['rule_id'] : 0;
        self::$row->job_id = (isset($args['job_id'])) ? $args['job_id'] : 0;
        self::$row->tokens = (isset($args['tokens'])) ? $args['tokens'] : '';

        /* load extras */
        self::$email_settings = Inbound_Email_Meta::get_settings(self::$row->email_id);
        self::$email_settings['recipients'] = (isset($args['lead_lists'])) ? $args['lead_lists'] : array();
        self::get_templates(self::$row->variation_id);
        self::toggle_dom_parser();


        /* build email */
        self::$email['send_address'] = $args['email_address'];
        self::$email['subject'] = self::get_variation_subject();
        self::$email['from_name'] = self::get_variation_from_name();
        self::$email['from_email'] = self::get_variation_from_email();
        self::$email['email_title'] = get_the_title(self::$row->email_id);
        self::$email['reply_email'] = self::get_variation_reply_email();

        /* If direct email we won't load from template */
        if (isset($args['is_direct']) && $args['is_direct']) {
            $email = get_post($args['email_id']);
            self::$email['body'] = do_shortcode($email->post_content);
        } else {
            self::$email['body'] = self::get_email_body();
        }

        if (isset($args['is_test']) && $args['is_test']) {
            self::$email['is_test'] = true;
        }

        switch (self::$email_service) {
            case "sparkpost":
                Inbound_Mailer_SparkPost::send_email(true);
                break;
        }

        /* return response */
        return self::$response;

    }


    /**
     *    Updates the status of the email in the queue
     */
    public static function delete_from_queue() {
        global $wpdb;

        $query = "delete from " . self::$table_name . " where `id` = '" . self::$row->id . "'";
        $wpdb->query($query);

    }

    /**
     *    Updates the post status of an email to sent
     */
    public static function mark_email_sent() {

        $wordpress_date_time = date_i18n('Y-m-d G:i:s');
        $today = new DateTime($wordpress_date_time);
        $schedule_date = new DateTime(self::$row->datetime);
        $interval = $today->diff($schedule_date);

        $status = ($interval->format('%R') == '+') ? 'scheduled' : 'sent';

        $args = array(
            'ID' => self::$row->email_id,
            'post_status' => $status,
        );

        wp_update_post($args);
    }


    /**
     *    Gets array of raw html for each variation
     */
    public static function get_templates($variation_id = null) {


        /* setup static var as empty array */
        self::$templates = array();

        if (!isset(self::$email_settings['variations']) || !self::$email_settings['variations']) {
            return array();
        }


        foreach (self::$email_settings['variations'] as $vid => $variation) {

            if ($variation_id !== null && $vid != $variation_id) {
                continue;
            }

            /* get permalink */
            $permalink = get_post_permalink(self::$row->email_id);

            /* query args */
            $query_args = array('inbvid' => $vid, 'disable_shortcodes' => true);

            /* encode post_id */
            if (isset(self::$row->post_id)) {
                $query_args['post_id'] = self::$row->post_id ;
            }

            /* encode tokens */
            if (self::$row->tokens && strlen(self::$row->tokens) < 1000 )  {
                $token = Inbound_API::analytics_get_tracking_code(json_decode(self::$row->tokens,true));
                $query_args['tokens'] = $token ;
            }

            /* add param */
            $permalink = add_query_arg( $query_args , $permalink);

            /* Stash variation template in static array */
            self::$templates[self::$row->email_id][$vid] = self::get_variation_html($permalink);
        }

    }

    /**
     *    Gets tags & sets them into static array
     */
    public static function get_tags() {

        $array = array();

        /* Mandrill can't accept user defined tags due to tag limitations
        $terms = wp_get_post_terms( self::$row->email_id , 'inbound_email_tag' );

        foreach ($terms as $term) {
            $array[] = $term->name;
        }
        */

        $array[] = self::$email_settings['email_type'];

        self::$tags[self::$row->email_id] = $array;
    }

    /**
     * Prepares email data for sending
     * @return ARRAY $email
     */
    public static function get_email() {

        self::$email['send_address'] = Leads_Field_Map::get_field(self::$row->lead_id, 'wpleads_email_address');

        //error_log('Send Address ' . self::time_elapsed());
        self::$email['from_name'] = self::get_variation_from_name();
        self::$email['from_email'] = self::get_variation_from_email();
        self::$email['reply_email'] = self::get_variation_reply_email();

        //error_log('Reply Email ' . self::time_elapsed());
        self::$email['body'] = self::get_email_body();

        self::$email['subject'] = self::get_variation_subject();

    }

    /**
     *    Generates targeted email body html
     */
    public static function get_email_body() {
        $last = self::$last;
        //error_log('Bodypart #1' . self::time_elapsed($last));
        /* set required variables if empty */
        self::$email_settings['recipients'] = (isset(self::$email_settings['recipients'])) ? self::$email_settings['recipients'] : array();

        $html = self::$templates[self::$row->email_id][self::$row->variation_id];

        /* add lead id to all shortcodes before processing */
        $html = str_replace('[lead-field ', '[lead-field lead_id="' . self::$row->lead_id . '" ', $html);

        $unsubscribe = do_shortcode('[unsubscribe-link lead_id="' . self::$row->lead_id . '" list_ids="' . implode(',', self::$email_settings['recipients']) . '" email_id="' . self::$row->email_id . '" rule_id="' . self::$row->rule_id . '" job_id="' . self::$row->job_id . '"]');

        //error_log('Bodypart #2' . self::time_elapsed($last));

        /* add lead id & list ids to unsubscribe shortcode */
        $html = str_replace('[unsubscribe-link]', $unsubscribe, $html);

        $html = Inbound_List_Double_Optin::add_confirm_link_shortcode_params($html, array(
            'lead_id' => self::$row->lead_id,
            'list_ids' => self::$email_settings['recipients'],
            'email_id' => self::$row->email_id
        ));

        /* clean mal formatted quotations */
        $html = str_replace('&#8221;', '"', $html);

        /* remove ctas before rendering shortcodes */
        $pattern = '/\[(cta).*?\]/';
        $content = preg_replace($pattern, '', $html);

        /* process shortcodes */
        $html = do_shortcode($html);

        //error_log('Bodypart #3' . self::time_elapsed($last));

        /* add tracking params to links */
        //$html = self::rebuild_links($html);


        //error_log('Bodypart #4' . self::time_elapsed($last));

        /* remove script tags */
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);


        //error_log('Body Complete' . self::time_elapsed());

        return $html;

    }

    /**
     * Generate HTML for email
     * @param STRING $permalink
     * @return STRING
     */
    public static function get_variation_html($permalink) {

        $response = wp_remote_get($permalink, array('timeout' => 120));

        if (is_wp_error($response)) {
            error_log(print_r($response, true));
            print_r($response);
            exit;
        }

        $html = wp_remote_retrieve_body($response);
        return $html;
    }

    /**
     *    Gets the subject line from variation settings
     */
    public static function get_variation_subject() {

        /* add lead id to all shortcodes before processing */
        $subject = str_replace('[lead-field ', '[lead-field lead_id="' . self::$row->lead_id . '" ', self::$email_settings['variations'] [self::$row->variation_id] ['subject']);

        $email_tokens = json_decode(self::$row->tokens , true);

        /* rebuild tokens if id present */
        if (isset(self::$row->post_id)) {
            $post = get_post(self::$row->post_id);
            $email_tokens = (array) $post;
            $email_tokens['permalink'] = get_the_permalink((int) self::$row->post_id);
            $email_tokens['featured_image'] = wp_get_attachment_url(get_post_thumbnail_id(self::$row->post_id));
            wp_reset_query();
        }

        /* process tokens */
        $subject = Inbound_Mailer_Tokens::process_tokens($subject , $email_tokens );

        return do_shortcode($subject);
    }

    /**
     *    Gets the from name from variation settings
     */
    public static function get_variation_from_name() {
        return self::$email_settings['variations'] [self::$row->variation_id] ['from_name'];
    }

    /**
     *    Gets the from email from variation settings
     */
    public static function get_variation_from_email() {
        return self::$email_settings['variations'] [self::$row->variation_id] ['from_email'];
    }

    /**
     *    Gets the reply email from variation settings
     */
    public static function get_variation_reply_email() {
        return self::$email_settings['variations'] [self::$row->variation_id] ['reply_email'];
    }


    /**
     *    Generate text version of html email automatically
     */
    public static function get_text_version() {


    }

    /**
     * Check stop rules for lead
     * returns true to OK sending and false to prevent sending
     */
    public static function check_stop_rules() {

        $stop_rules = Inbound_Mailer_Unsubscribe::get_stop_rules(self::$row->lead_id);
        $wordpress_date_time = date_i18n('Y-m-d G:i:s');

        $passed = 0;
        $failed = 0;

        foreach (self::$email_settings['recipients'] as $list_id) {
            if (!isset($stop_rules[$list_id])) {
                $passed++;
                continue;
            }

            /* if lead has unsubscribed to this list skip it */
            if ($stop_rules[$list_id] == 'unsubscribed') {
                $failed++;
                continue;
            }

            /* if not a datetime string then skip stop rule (treat as pass) */
            if (!is_string($stop_rules[$list_id])) {
                $passed++;
                continue;
            }

            /* if lead has emails for this list paused then set $pass to false */
            if (strtotime($stop_rules[$list_id]) > strtotime($wordpress_date_time)) {
                $failed++;
            } else {
                $passed++;
                Inbound_Mailer_Unsubscribe::remove_stop_rule(self::$row->lead_id, $list_id);
            }
        }

        if ($passed >= $failed) {
            return true;
        } else {
            return false;
        }

    }

    /**
     *  Checks Email Service Response for Errors
     */
    public static function check_response() {
        global $current_user, $post;
        $user_id = $current_user->ID;

        /* check if there is an error and if there is then exit */
        if (isset(self::$response['status']) && self::$response['status'] == 'error' || isset(self::$response['error'])) {
            if (isset($resonse['description'])) {
                self::$response['message'] = self::$response['message'] . ' : ' . self::$response['description'];
            }

            Inbound_Options_API::update_option('inbound-email', 'errors-detected', self::$response['message']);
            self::$error_mode = true;
            return;
        }

        /* if error mode is on and response is good then turn it off */
        if (self::$error_mode) {
            Inbound_Options_API::update_option('inbound-email', 'errors-detected', false);
            self::$error_mode = false;
        }

    }

    public static function time_elapsed( $last = null)
    {

        self::$last = ($last) ? $last : self::$last;
        $now = microtime(true);

        if (self::$last != null) {

            $elapsed =  '<!---- ' . date("H:i:s", $now - self::$first) . ' ' . date("H:i:s", $now - self::$last) . '  now: '.$now.' last: '.self::$last.' -->';
        }

        self::$last = ($last) ? $last :$now;
        self::$first = (self::$first) ? self::$first : self::$last;
        return $elapsed;
    }
}

/**
 *    Load Mail Daemon on init
 */
function load_inbound_mail_daemon() {
    new Inbound_Mail_Daemon();
}

add_action('init', 'load_inbound_mail_daemon', 2);
