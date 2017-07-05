<?php

/**
 * Class Inbound_Events
 * Stores action events into MySQL database table
 * Stores: form submissions events, cta link clicks, email link clicks, custom events
 *
 */
class Inbound_Events {

    /**
     * Inbound_Events constructor.
     */
    public function __construct(){
        self::add_hooks();
    }

    /**
     * Define WordPress hooks and filters
     */
    public static function add_hooks() {
        /* create events table if does not exist */
        add_action('inbound_shared_activate' , array( __CLASS__ , 'create_events_table' ));

        /* create page_views table if does not exist */
        add_action('inbound_shared_activate' , array( __CLASS__ , 'create_page_views_table' ));

        /* create link_tracking table if does not exist */
        add_action('inbound_shared_activate' , array( __CLASS__ , 'create_link_tracking_table' ));

        /* listen for Inbound Form submissions and record event to events table */
        add_action('inbound_store_lead_post' , array( __CLASS__ , 'store_form_submission'), 10 , 1);

        /* listen for Email Clicks and record event to events table */
        add_action('inbound_email_click_event' , array( __CLASS__ , 'store_email_click'), 10 , 1);

        /* listen for list add events and record event to events table */
        add_action('add_lead_to_lead_list' , array( __CLASS__ , 'store_list_add_event'), 10 , 2);

        /* Saves all all incoming POST data as meta pairs */
        add_action('before_delete_post', array(__CLASS__, 'delete_related_events'));

    }

    /**
     * Creates inbound_events
     */
    public static function create_events_table(){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        /* if table already created then bail */
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            return;
        }

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `event_name` varchar(255) NOT NULL,
			  `page_id` mediumint(20) NOT NULL,
			  `variation_id` mediumint(9) NOT NULL,
			  `form_id` mediumint(20) NOT NULL,
			  `cta_id` mediumint(20) NOT NULL,
			  `email_id` mediumint(20) NOT NULL,
			  `rule_id` mediumint(20) NOT NULL,
			  `job_id` mediumint(20) NOT NULL,
			  `list_id` mediumint(20) NOT NULL,
			  `lead_id` mediumint(20) NOT NULL,
              `comment_id` mediumint(20) NOT NULL,
			  `lead_uid` varchar(255) NOT NULL,
			  `session_id` varchar(255) NOT NULL,
			  `event_details` text NOT NULL,
			  `source` text NOT NULL,
			  `funnel` text NOT NULL,
			  `datetime` datetime NOT NULL,

			  UNIQUE KEY id (id)
			) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $results = dbDelta( $sql );

    }


    /**
     * Creates inbound_events
     */
    public static function create_page_views_table(){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";
        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `page_id` varchar(20) NOT NULL,
			  `variation_id` mediumint(9) NOT NULL,
			  `lead_id` mediumint(20) NOT NULL,
			  `lead_uid` varchar(255) NOT NULL,
			  `list_id` mediumint(20) NOT NULL,
			  `session_id` varchar(255) NOT NULL,
			  `source` text NOT NULL,
			  `datetime` datetime NOT NULL,
			  `ip` varchar(45) NOT NULL,

			  UNIQUE KEY id (id)
			) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

    }


    /**
     * Creates inbound_tracked_links table
     */
    public static function create_link_tracking_table(){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_tracked_links";
        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE $table_name (
			  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `token` tinytext NOT NULL,
			  `args` text NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

    }

    /**
     * Stores a form submission event into events table
     * @param $lead
     */
    public static function store_form_submission( $lead ){

        if (!isset($lead['raw_params']) )  {
            return;
        }

        parse_str($lead['raw_params'] , $raw_params );
        $details = array_merge($raw_params,$lead);

        if (!isset($raw_params['inbound_form_id'])) {
            return;
        }

        if (isset($raw_params['wp_cta_id'])) {
            $lead['cta_id'] = $raw_params['wp_cta_id'];
            $lead['variation'] = $raw_params['wp_cta_vid'];
        } else {
            $lead['cta_id'] = 0;
        }

        $args = array(
            'event_name' => 'inbound_form_submission',
            'page_id' => $lead['page_id'],
            'variation_id' =>  $lead['variation'],
            'form_id' => (isset($raw_params['inbound_form_id'])) ? $raw_params['inbound_form_id'] : '',
            'lead_id' => $lead['id'],
            'cta_id' => $lead['cta_id'],
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : '' ),
            'event_details' => json_encode($details),
            'datetime' => $lead['wordpress_date_time']
        );

        self::store_event($args);
    }


    /**
     * Stores inbound email click event into events table
     * @param $args
     */
    public static function store_email_click( $args ){
        global $wp_query;


        $args['event_name'] = 'inbound_email_click';

        self::store_event($args);
    }

    /**
     * Stores inbound mailer unsubscribe event into events table
     * @param $args
     */
    public static function store_unsubscribe_event( $args ){
        global $wp_query;

        $args['event_name'] = 'inbound_unsubscribe';

        self::store_event($args);
    }

    /**
     * Stores inbound lead list addition event(s) into events table
     * @param $args
     */
    public static function store_list_add_event( $lead_id , $list_id ){
        global $wp_query;

        $args['event_name'] = 'inbound_list_add';
        $args['lead_id'] = $lead_id;

        if (is_array($list_id)) {
            foreach($list_id as $id) {
                $args['list_id'] = $id;
                self::store_event($args);
            }
        } else {
            $args['list_id'] = $list_id;
            self::store_event($args);
        }
    }

    /**
     * Stores inbound mailer mute event into events table
     * @param $args
     */
    public static function store_mute_event( $args ){
        global $wp_query;

        $args['event_name'] = 'inbound_mute';

        self::store_event($args);
    }

    /**
     * Stores search made events into the events table
     * @param $args
     */
    public static function store_search_event( $args ){
        global $wp_query;

        $args['event_name'] = 'search_made';

        self::store_event($args);
    }

    /**
     * Stores comment made events into the events table
     * @param $args
     */
    public static function store_comment_event( $args ){
        global $wp_query;

        $args['event_name'] = 'comment_made';

        self::store_event($args);
    }
    
    /**
     * Add event to inbound_events table
     * @param $args
     */
    public static function store_event( $args ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $timezone_format = 'Y-m-d G:i:s T';
        $wordpress_date_time =  date_i18n($timezone_format);

        /* event name required */
        if (!isset($args['event_name'])) {
            return;
        }

        $defaults = array(
            'page_id' => '',
            'variation_id' => '',
            'form_id' => '',
            'cta_id' => '',
            'email_id' => '',
            'rule_id' => '',
            'job_id' => '',
            'list_id' => '',
            'lead_id' => ( isset($_COOKIE['wp_lead_id']) ? $_COOKIE['wp_lead_id'] : '' ),
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : '' ),
            'session_id' => ( isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : session_id() ),
            'comment_id' => '',
            'event_name' => $args['event_name'],
            'event_details' => '',
            'datetime' => $wordpress_date_time,
            'funnel' => ( isset($_SESSION['inbound_page_views']) ? $_SESSION['inbound_page_views'] : '' ),
            'source' => ( isset($_COOKIE['inbound_referral_site']) ? $_COOKIE['inbound_referral_site'] : '' )
        );

        $args = array_merge( $defaults , $args );

        /* prepare funnel array */
        if ($args['funnel']) {
            /* check if valid json or if slashes need to be stripepd out */
            if (!self::isJson($args['funnel'])) {
                $args['funnel'] = stripslashes($args['funnel']);
            }

            /* decode into array for modification */
            $funnel = json_decode( $args['funnel'] , true);

            $stored_views = array();

            foreach ($funnel as $page_id => $visits ) {

                if (!is_numeric($page_id)) {
                    continue;
                }

                if (is_array($visits)) {
                    foreach ($visits as $visit) {
                        $stored_views[stripslashes($visit)] = strval($page_id);
                    }
                } else {
                    $stored_views[stripslashes($visits)] = strval($page_id);
                }
            }

            /* order by date */
            ksort($stored_views);

            /* add original funnel with timestamps to event details */
            if (is_array($args['event_details'])) {
                $args['event_details']['funnel'] = $funnel;
            }

            /* clean funnel of timestamps */
            $args['funnel'] = json_encode($stored_views);

        }

        /* json encode event details if array */
        if ($args['event_details'] && is_array($args['event_details'])) {
            $args['event_details'] = json_encode($args['event_details']);
        }


        /* unset non db ready keys */
        foreach ($args as $key => $value) {
            if (!isset($defaults[$key])) {
                unset($args[$key]);
            }
        }

        /* add event to event table */
        $wpdb->insert(
            $table_name,
            $args
        );

        /* check error messages for broken tables */
        if (isset($wpdb->last_error)) {
            switch ($wpdb->last_error) {
                case "Unknown column 'funnel' in 'field list'":
                    self::create_events_table();
                    break;
                                
                case "Unknown column 'comment_id' in 'field list'":
                    self::create_events_table();
                    break;
            }
        }

    }

    /**
     * Stores page view event int inbound_page_views table
     * @param $args
     */
    public static function store_page_view( $args ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";
        $wordpress_date_time =  date_i18n('Y-m-d G:i:s T');

        $defaults = array(
            'page_id' => '',
            'variation_id' => '',
            'lead_id' => ( isset($_COOKIE['wp_lead_id']) ? $_COOKIE['wp_lead_id'] : '' ),
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : '' ),
            'session_id' => ( isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : session_id() ),
            'datetime' => $wordpress_date_time,
            'source' => ( isset($_COOKIE['inbound_referral_site']) ? $_COOKIE['inbound_referral_site'] : '' ),
            'ip' => LeadStorage::lookup_ip_address()
        );

        $args = array_merge( $defaults , $args );


        if (!$args['page_id']) {
            return;
        }

        /* unset non db ready keys */
        foreach ($args as $key => $value) {
            if (!isset($defaults[$key])) {
                unset($args[$key]);
            }
        }

        /* add page view to inbound_page_views table */
        $wpdb->insert(
            $table_name,
            $args
        );

    }

    public static function delete_related_events( $post_id , $vid = 'all' ) {
        global $wpdb;

        $post = get_post($post_id);

        $table_name = $wpdb->prefix . "inbound_events";

        switch ($post->post_type) {
            case  'inbound-email':
                $where = array(
                    'email_id' => $post_id
                );
                break;
            case  'wp-lead':
                $where = array(
                    'lead_id' => $post_id
                );
                break;
            case  'wp-call-to-action':
                $where = array(
                    'cta_id' => $post_id
                );
                break;
            default:
                $where = array(
                    'page_id' => $post_id
                );
                break;
        }

        if ($vid != 'all'){
            $where['variation_id'] = $vid;
        }

        $wpdb->delete( $table_name, $where, $where_format = null );

    }

    /**
     * Checks if an event has already been created. Right now mailer is the only tool leveraging this.
     * It will need to be improved to support other uses
     * @param $args
     * @return bool
     */
    public static function event_exists( $args ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $defaults = array(
            'page_id' => '',
            'variation_id' => '',
            'form_id' => '',
            'cta_id' => '',
            'email_id' => '',
            'lead_id' => ( isset($_COOKIE['wp_lead_id']) ? $_COOKIE['wp_lead_id'] : '' ),
            'lead_uid' => ( isset($_COOKIE['wp_lead_uid']) ? $_COOKIE['wp_lead_uid'] : '' ),
            'session_id' => '',
            'comment_id' => '',
            'event_name' => $args['event_name'],
            'event_details' => '',
            //'datetime' => (isset($args['wordpress_date_time'],
            'funnel' => ( isset($_SESSION['inbound_page_views']) ? $_SESSION['inbound_page_views'] : '' ),
            'source' => ( isset($_COOKIE['inbound_referral_site']) ? $_COOKIE['inbound_referral_site'] : '' )
        );


        $args = array_merge( $defaults , $args );

        if($wpdb->get_row("SELECT * FROM $table_name WHERE event_name = '".$args['event_name']."' && email_id = '".$args['email_id']."' && lead_id = '".$args['email_id']."' && variation_id = '".$args['variation_id']."' ", ARRAY_A)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all Inbound Form submission events related to lead ID
     */
    public static function get_form_submissions( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_form_submission" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Agnostically get all form submission events related to lead ID
     */
    public static function get_all_form_submissions( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` LIKE "%_form_submission" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Determine information about point of event capture
     * @param $event
     * @return mixed
     */
    public static function get_event_capture_data( $event ) {
        switch( $event['event_name'] ) {
            case 'inbound_cta_click':
                $link = admin_url('post.php?post='.$event['cta_id'].'&action=edit');
                $title = get_the_title($event['cta_id']);
                $capture_id = $event['cta_id'];
                break;
            case 'inbound_content_click':
                $details = json_decode($event['event_details'],true);
                $link = $details['url'];
                $title = $details['url'];
                $capture_id = '';
                break;
            case 'inbound_form_submission':
                $link = admin_url('post.php?post='.$event['form_id'].'&action=edit');
                $title = get_the_title($event['form_id']);
                $capture_id = $event['form_id'];
                break;
            case 'cf7_form_submission':
                $link = admin_url('post.php?page=wpcf7&post='.$event['form_id'].'&action=edit');
                $title = get_the_title($event['form_id']);
                $capture_id = $event['form_id'];
                break;
            case 'ninja_form_submission':
                $link = admin_url('post.php?page=ninja-forms&form_id='.$event['form_id'].'&action=edit');
                $title = get_the_title($event['form_id']);
                $capture_id = $event['form_id'];
                break;
            case 'inbound_list_add':
                $link = "";
                $title = "";
                $capture_id = "";
                break;
            case 'sparkpost_delivery':
                $link = "";
                $title = "";
                $capture_id = "";
                break;
            default:
                $link = "";
                $title = "";
                $capture_id = "";
                break;
        }

        $array['link'] = (isset($link)) ? $link : '#';
        $array['title'] = (isset($title)) ? $title : __('n/a','inbound-pro');
        $array['capture_id'] = (isset($capture_id)) ? $capture_id : 0;

        return apply_filters('inbound-events/capture-data' , $array , $event);
    }

    /**
     * Get form submission events given conditions
     *
     */
    public static function get_form_submissions_by( $nature = 'lead_id' ,  $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $query = 'SELECT * FROM '.$table_name.' WHERE ';

        switch ($nature) {
            case 'lead_id':
                $query .= '`lead_id` = "'.$params['lead_id'].'" ';
                break;
            case 'page_id':
                $query .= '`page_id` = "'.$params['page_id'].'" ';
                break;
            case 'cta_id':
                $query .= '`cta_id` = "'.$params['cta_id'].'" ';
                break;
        }

        /* add date constraints if applicable */
        if (isset($params['start_date'])) {
            $query .= 'AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        if (isset($params['variation_id'])) {
            $query .= 'AND variation_id = "'.$params['variation_id'].'" ';
        }

        $query .= 'AND `event_name` = "inbound_form_submission" ORDER BY `datetime` DESC';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get page view events related to lead ID
     */
    public static function get_page_views( $lead_id , $lead_uid = 0 , $page_id = 0 ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'"';

        $query .=' AND `page_id` != "0"';

        if ($page_id) {
            $query .=' AND page_views_id` = "'.$page_id.'"';
        }

        $query .='ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get page view events given conditions
     *
     */
    public static function get_page_views_by( $nature = 'lead_id' ,  $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";
        $query = 'SELECT * FROM '.$table_name.' WHERE ';


        switch ($nature) {
            case 'lead_id':
                $query .=' `lead_id` = "'.$params['lead_id'].'" ';
                break;
            case 'lead_uid':
                $query .=' `lead_uid` = "'.$params['lead_uid'].'" ';
                break;
            case 'page_id':
                $query .=' `page_id` = "'.$params['page_id'].'" ';
                break;
            case 'mixed':
                if (isset($params['lead_id']) && $params['lead_id'] ) {
                    $queries[] = ' `lead_id` = "'.$params['lead_id'].'" ';
                }
                if (isset($params['lead_uid']) && $params['lead_uid']) {
                    $queries[] = ' `lead_uid` = "'.$params['lead_uid'].'" ';
                }
                if (isset($params['page_id']) && $params['page_id']) {
                    $queries[] = ' `page_id` = "'.$params['page_id'].'" ';
                }

                /* combine queries into a usable string */
                foreach ($queries as $i => $q) {
                    $query .= $q . ( isset($queries[$i+1]) ? ' AND ' : '' );
                }

                break;
        }


        $query .=' AND `page_id` != "0" ';

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        if (isset($params['group_by'])) {
            $query .= ' GROUP BY `'.$params['group_by'].'` ';
        }

        $query .= ' ORDER BY `datetime` DESC';
        //print_r($query);exit;
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }



    /**
     * Get page view events given conditions
     *
     */
    public static function get_page_views_by_dates( $params ){
        global $wpdb;

        $params['group_by'] = (isset($params['group_by'])) ? $params['group_by'] : 'lead_uid';
        $params['order_by'] = (isset($params['order_by'])) ? $params['order_by'] : 'datetime DESC';

        $table_name = $wpdb->prefix . "inbound_page_views";

        $query = 'SELECT *, count(*) as impressions, count(date(datetime)) as impressions_per_day, date(datetime) as date  FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'"';


        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        $query .=' AND `page_id` != "0" ';

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .= 'GROUP BY DATE(datetime)';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get page view count given lead_id
     *
     */
    public static function get_page_views_count( $lead_id  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'"';

        $query .=' AND `page_id` != "0" ';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;

    }

    /**
     * Get sources given lead_id
     *
     */
    public static function get_lead_sources( $lead_id  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT *, count(*) as count FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" GROUP BY source';

        $events = $wpdb->get_results( $query , ARRAY_A );

        $sources = array();
        foreach ($events as $key => $event) {
            $sources[$event['datetime']] = $event['source'];
        }
        return $sources;

    }


    /**
     * Get visitor count given page_id
     *
     */
    public static function get_visitors_count( $page_id , $params = array() ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";

        $query = 'SELECT * FROM '.$table_name.' WHERE `page_id` = "'.$page_id.'" ';

        if (isset($params['start_date'])) {
            $query .= 'AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .='GROUP BY lead_uid';

        $results = $wpdb->get_results( $query , ARRAY_A );

        /* return null if nothing there */
        return count($results);

    }


    /**
     * Get page view events given conditions
     *
     */
    public static function get_visitors( $params ){
        global $wpdb;

        $params['group_by'] = (isset($params['group_by'])) ? $params['group_by'] : 'lead_uid';
        $params['order_by'] = (isset($params['order_by'])) ? $params['order_by'] : 'datetime DESC';

        $table_name = $wpdb->prefix . "inbound_page_views";
        $query = 'SELECT *, count('.$params['group_by'].') as count FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'"';

        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .= ' GROUP BY `'.$params['group_by'].'` ';

        if (isset($params['order_by'])) {
            $query .= ' ORDER BY '.$params['order_by'].' ';
        }

        if (isset($params['limit'])) {
            $query .= ' LIMIT '.$params['limit'];;
        }

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get page view events given conditions
     *
     */
    public static function get_visitors_by_dates( $params ){
        global $wpdb;

        $params['group_by'] = (isset($params['group_by'])) ? $params['group_by'] : 'lead_uid';
        $params['order_by'] = (isset($params['order_by'])) ? $params['order_by'] : 'datetime DESC';

        $table_name = $wpdb->prefix . "inbound_page_views";
        $query = 'SELECT count(date(datetime)) as visits_per_day, date(datetime) as date , sum(visits) as visitors FROM ( ';

        $query .= ' SELECT *, count('.$params['group_by'].') as visits FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'"';


        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .= ' GROUP BY '.$params['group_by'].' ';


        $query .= ') AS concat_date GROUP BY DATE(datetime)';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get page view events given conditions
     *
     */
    public static function get_visitors_group_by_source( $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_page_views";
        $query = 'SELECT * , count(source) as visitors, sum(page_views) as page_views_total  FROM ( ';

        $query .= ' SELECT *, count(lead_uid) as page_views FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'"';

        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .= ' GROUP BY lead_uid ';

        $query .= ') AS s1 GROUP BY source';

        $query .= ' ORDER BY visitors DESC, page_views DESC  ';

        if (isset($params['limit'])) {
            $query .= ' LIMIT '.$params['limit'];;
        }

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }


    /**
     * Get events given parameters
     *
     */
    public static function get_events( $params ){
        global $wpdb;

        $params['order_by'] = (isset($params['order_by'])) ? $params['order_by'] : 'datetime DESC';

        $table_name = $wpdb->prefix . "inbound_events";
        $query = 'SELECT *';

        if (isset($params['group_by']) && $params['group_by'] ) {
            $query .=' , count('.$params['group_by'].') as count ';
        }

        $query .=' FROM '.$table_name.' WHERE 1=1 ';

        if (isset($params['page_id']) && $params['page_id'] ) {
            $query .= ' AND page_id = "'.$params['page_id'].'" ';
        }

        if (isset($params['event_name']) && $params['event_name'] ) {
            $query .= ' AND event_name = "'.$params['event_name'].'" ';
        }

        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        if (isset($params['lead_id']) && $params['lead_id'] ) {
            $query .= ' AND lead_id = "'.$params['lead_id'].'" ';
        }

        if (isset($params['start_date']) && $params['start_date']) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        if (isset($params['group_by']) && $params['group_by']) {
            $query .= ' GROUP BY `' . $params['group_by'] . '` ';
        }

        if (isset($params['order_by']) && $params['order_by']) {
            $query .= ' ORDER BY '.$params['order_by'].' ';
        }

        if (isset($params['limit'])) {
            $query .= ' LIMIT '.$params['limit'];;
        }
        
        if (isset($params['offset']) && $params['offset']) {
            $query .= ' OFFSET '.$params['offset'].' ';
        }

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Returns a label for an event given an event_name
     */
    public static function get_event_label( $event_name , $plural = true) {
        switch($event_name) {
            case 'inbound_form_submission':
                return ($plural) ?  __('Inbound Form Submissions' , 'inbound-pro') : __('Inbound Form Submission' , 'inbound-pro');
                break;
            case 'inbound_cta_click':
                return ($plural) ?  __('CTA Clicks' , 'inbound-pro') : __('CTA Click' , 'inbound-pro');
                break;
            case 'inbound_content_click':
                return ($plural) ?  __('Content Clicks' , 'inbound-pro') : __('Content Click' , 'inbound-pro');
                break;
            case 'inbound_direct_messege':
                return ($plural) ?  __('Direct Messages' , 'inbound-pro') : __('Direct Message' , 'inbound-pro');
                break;
            case 'inbound_list_add':
                return ($plural) ?  __('Lead Added to Lists' , 'inbound-pro') : __('Lead Added to List' , 'inbound-pro');
                break;
            case 'sparkpost_delivery':
                return ($plural) ?  __('SparkPost Deliveries' , 'inbound-pro') : __('SparkPost Delivery' , 'inbound-pro');
                break;
            case 'search_made':
                return ($plural) ?  __('Searches Made' , 'inbound-pro') : __('Search Made' , 'inbound-pro');
                break;
            case 'comment_made':
                return ($plural) ?  __('Comments Made' , 'inbound-pro') : __('Comment Made' , 'inbound-pro');
                break;
        }

        return apply_filters('inbound-events/event-label' , $event_name , $plural );
    }

    /**
     * Get all possible event names
     */
    public static function get_event_names( $params = array() ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT DISTINCT(event_name) FROM '.$table_name;

        if (isset($params['page_id']) && $params['page_id']) {
            $query .= ' WHERE `page_id` = "'.$params['page_id'].'" ';
        }

        $query .= ' ORDER BY `event_name` DESC ';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all mute events given an email id
     */
    public static function get_events_count( $event_name ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `event_name` = "'.$event_name.'"';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;

    }

    /**
     * Get page view events given conditions
     *
     */
    public static function get_events_by_dates( $params ){
        global $wpdb;

        $params['group_by'] = (isset($params['group_by'])) ? $params['group_by'] : 'event_name';
        $params['order_by'] = (isset($params['order_by'])) ? $params['order_by'] : 'datetime DESC';

        $table_name = $wpdb->prefix . "inbound_events";
        $query = 'SELECT date(datetime) as date , sum(events) as events_count FROM ( ';

        $query .= ' SELECT *, count('.$params['group_by'].') as events FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'"';

        if (isset($params['event_name']) && $params['event_name'] ) {
            $query .= ' AND event_name = "'.$params['event_name'].'" ';
        }

        if (isset($params['source']) && $params['source'] ) {
            $query .= ' AND source = "'.$params['source'].'" ';
        }

        if (isset($params['lead_id']) && $params['lead_id'] ) {
            $query .= ' AND lead_id = "'.$params['lead_id'].'" ';
        }

        if (isset($params['start_date'])) {
            $query .= ' AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        $query .= ' GROUP BY '.$params['group_by'].' ';


        $query .= ') AS concat_date GROUP BY DATE(datetime)';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all cta click events related to lead ID
     */
    public static function get_cta_clicks( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_cta_click" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get cta click events given conditions
     *
     */
    public static function get_cta_clicks_by( $nature = 'lead_id' ,  $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $query = 'SELECT * FROM '.$table_name.' WHERE ';

        switch ($nature) {
            case 'lead_id':
                $query .= '`lead_id` = "'.$params['lead_id'].'" ';
                break;
            case 'page_id':
                $query .= '`page_id` = "'.$params['page_id'].'" ';
                break;
            case 'cta_id':
                $query .= '`cta_id` = "'.$params['cta_id'].'" ';
                break;
        }

        /* add date constraints if applicable */
        if (isset($params['start_date'])) {
            $query .= 'AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        if (isset($params['variation_id'])) {
            $query .= 'AND variation_id = "'.$params['variation_id'].'" ';
        }

        $query .= 'AND `event_name` = "inbound_cta_click" ORDER BY `datetime` DESC';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all cta click events related to lead ID
     */
    public static function get_content_clicks( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_content_click" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get cta click events given conditions
     *
     */
    public static function get_content_clicks_by( $nature = 'lead_id' ,  $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $query = 'SELECT * FROM '.$table_name.' WHERE ';

        switch ($nature) {
            case 'lead_id':
                $query .= '`lead_id` = "'.$params['lead_id'].'" ';
                break;
            case 'page_id':
                $query .= '`page_id` = "'.$params['page_id'].'" ';
                break;
            case 'cta_id':
                $query .= '`cta_id` = "'.$params['cta_id'].'" ';
                break;
        }

        /* add date constraints if applicable */
        if (isset($params['start_date'])) {
            $query .= 'AND datetime >= "'.$params['start_date'].'" AND  datetime <= "'.$params['end_date'].'" ';
        }

        if (isset($params['variation_id'])) {
            $query .= 'AND variation_id = "'.$params['variation_id'].'" ';
        }

        $query .= 'AND `event_name` = "inbound_content_click" ORDER BY `datetime` DESC';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all email click events related to lead ID
     */
    public static function get_email_clicks( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_email_click" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all unsubscribe events given a lead id
     */
    public static function get_unsubscribes( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_unsubscribe" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all unsubscribe events given an email id
     */
    public static function get_unsubscribes_by_email( $email_id, $vid = null  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"';

        if ($vid>-1) {
            $query .= 'AND `variation_id` = "'.$vid.'"';
        }
        $query .= 'AND `event_name` = "inbound_unsubscribe"';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all unsubscribe events given an email id
     */
    public static function get_unsubscribes_count_by_email_id( $email_id , $vid = null ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"';

        if ($vid>-1) {
            $query .= 'AND `variation_id` = "'.$vid.'"';
        }

        $query .= 'AND `event_name` = "inbound_unsubscribe"';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;


    }

    /**
     * Get all mute events given a lead id
     */
    public static function get_mutes( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "inbound_mute" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all mute events given an email id
     */
    public static function get_mutes_by_email( $email_id, $vid = null  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"';

        if ($vid>-1) {
            $query .= 'AND `variation_id` = "'.$vid.'"';
        }
        $query .= 'AND `event_name` = "inbound_mute"';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all mute events given an email id
     */
    public static function get_mutes_count_by_email_id( $email_id , $vid = null ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `email_id` = "'.$email_id.'"';

        if ($vid>-1) {
            $query .= 'AND `variation_id` = "'.$vid.'"';
        }

        $query .= 'AND `event_name` = "inbound_mute"';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;

    }


    /**
     * Get custom event data by lead id
     */
    public static function get_custom_event_data( $lead_id ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` NOT LIKE "inbound_%" ORDER BY `datetime` DESC';
        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     * Get all all custom event data by a certain indicator
     */
    public static function get_custom_event_data_by( $nature = 'lead_id' , $params ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        switch ($nature) {
            case 'lead_id':
                $query = 'SELECT * FROM '.$table_name.' WHERE `lead_id` = "'.$params['lead_id'].'" AND `event_name` NOT LIKE "inbound_%" ORDER BY `datetime` DESC';
                break;
            case 'page_id':
                $query = 'SELECT * FROM '.$table_name.' WHERE `page_id` = "'.$params['page_id'].'" AND `event_name` NOT LIKE "inbound_%" ORDER BY `datetime` DESC';
                break;
        }

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }


    /**
     * Get date of latest activity
     * @param $lead_id
     * @param string $activity
     * @return datetime or null
     */
    public static function get_last_activity($lead_id , $activity = 'any' ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        switch ($activity) {
            case 'any':
                $query = 'SELECT `datetime` FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" ORDER BY `datetime` DESC LIMIT 1';
                $results = $wpdb->get_results( $query , ARRAY_A );
                break;
            default:
                $query = 'SELECT `datetime` FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "'.$activity.'" ORDER BY `datetime` DESC LIMIT 1';
                $results = $wpdb->get_results( $query , ARRAY_A );
                break;
        }

        /* return latest activity if recorded */
        if (isset($results[0]['datetime'])) {
            return $results[0]['datetime'];
        }

        /* return null if nothing there */
        return null;
    }

    /**
     * Get count of event activity given a lead id
     * @param $lead_id
     * @param string $activity
     * @return datetime or null
     */
    public static function get_total_activity($lead_id , $activity = 'any' , $blacklist = array() ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        switch ($activity) {
            case 'any':
                $query = 'SELECT count(*) FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" ';
                break;
            default:
                $query = 'SELECT count(*) FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'" AND `event_name` = "'.$activity.'"';
                break;
        }

        /* add blacklist queries */
        foreach ($blacklist as $event_name ) {
            $query .= ' AND `event_name` != "'.$event_name.'"';
        }

        /* return latest activity if recorded */
        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return $count;
    }

    /**
     * @param int $page_id
     * @param string $activity [any or custom event type]
     * @param datetime $start_date
     * @param datetime $end_date
     */
    public static function get_page_actions_count($page_id , $activity = 'any' , $start_date = null, $end_date = null ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        switch ($activity) {
            case 'any':
                $query = 'SELECT count(*) FROM '.$table_name.' WHERE `page_id` = "'.$page_id.'"';
                break;
            default:
                $query = 'SELECT count(*) FROM '.$table_name.' WHERE `page_id` = "'.$page_id.'" AND `event_name` = "'.$activity.'"';
                break;
        }

        if (isset($start_date) && $start_date) {
            $query .= 'AND datetime >= "'.$start_date.'" AND  datetime <= "'.$end_date.'" ';
        }


        /* return latest activity if recorded */
        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;
    }
 
    /**
     * Checks to see if a comment has already been logged in the events table
     * @param $comment_id
     */
    public static function comment_exists($comment_id){
        global $wpdb;
        
        $table_name = $wpdb->prefix . "inbound_events";
        
        $comment_row_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `id` FROM {$table_name} WHERE `comment_id` = %d",
                $comment_id
            )
        );  
        
        return $comment_row_id;  
    }

    /**
     * Removes a comment from the events database
     * @param $comment_id
     */
    public static function remove_comment_event($comment_id){
        global $wpdb;
        
        $table_name = $wpdb->prefix . "inbound_events";
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} WHERE `comment_id` = %d",
                $comment_id
            )
        );
   }
   
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

new Inbound_Events();
