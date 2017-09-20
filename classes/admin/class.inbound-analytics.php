<?php

/**
 * Class for initializing Inbound Analytics component across WordPress
 * @package     InboundPro
 * @subpackage  InboundAnalytics
 */
class Inbound_Analytics {
    static $templates;
    static $range;
    static $dates;
    static $automated_report;

    /**
     *  Initiate class
     */
    public function __construct() {

        self::load_hooks();
    }

    /**
     *  Load hooks and filters
     */
    public static function load_hooks() {

        /* load static vars */
        add_action('admin_init', array(__CLASS__, 'load_static_vars'), 10);

        /* disable legacy inbound statistics metaboxes */
        remove_action('init', 'inbound_load_legacy_statistics', 10);

        /* remove navigation tabs from lead profile */
        //add_filter('wpl_lead_tabs', array(__CLASS__, 'filter_profile_tabs'), 10, 1);

        /* Load Google Charting API & Inbound Analytics Styling CSS*/
        add_action('admin_enqueue_scripts', array(__CLASS__, 'load_scripts'));

        /* Add sidebar metabox to content administration area */
        add_action('add_meta_boxes', array(__CLASS__, 'load_metaboxes'));

        /* Register Columns */
        add_filter('manage_posts_columns', array(__CLASS__, 'register_columns'));
        add_filter('manage_pages_columns', array(__CLASS__, 'register_columns'));

        /* Prepare Column Data */
        add_action("manage_posts_custom_column", array(__CLASS__, 'prepare_column_data'), 10, 2);
        add_action("manage_pages_custom_column", array(__CLASS__, 'prepare_column_data'), 10, 2);

        /* setup column sorting */
        add_filter("manage_edit-post_sortable_columns", array(__CLASS__, 'define_sortable_columns'));
        add_filter("manage_edit-page_sortable_columns", array(__CLASS__, 'define_sortable_columns'));
        add_action('posts_clauses', array(__CLASS__, 'process_column_sorting'), 1, 2);

        /* setup screen options */
        add_filter( 'screen_settings',array( __CLASS__ , 'add_screen_option_field'), 10, 2 );
        add_filter( 'init', array( __CLASS__, 'set_screen_option'), 1 );

    }

    /**
     * load static varaibles
     */
    public static function load_static_vars() {
        self::load_range();
    }

    /**
     * Loads user defined range
     * @return array of range and dates
     */
    public static function load_range() {
        self::$range = get_user_option(
            'inbound_screen_option_range',
            get_current_user_id()
        );

        self::$range = (self::$range) ? self::$range : 90;

        self::$dates = Inbound_Reporting_Templates::prepare_range(self::$range);

        return array('range' => self::$range, 'dates' => self::$dates);
    }

    /**
     * Loads scripts and stu;es
     */
    public static function load_scripts() {

        global $post;


        if (!isset($post) || strstr($post->post_type, 'inbound-')) {
            return;
        }

        $screen = get_current_screen();

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        if (!isset($screen) || $screen->action == 'new' || $screen->action == 'add' || $screen->base == 'edit') {
            return;
        }

        wp_register_script('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/js/bootstrap.min.js');
        wp_enqueue_script('bootstrap');

        /* BootStrap CSS */
        wp_register_style('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/css/bootstrap.css');
        wp_enqueue_style('bootstrap');

        wp_enqueue_style('inbound-analytics-css', INBOUND_PRO_URLPATH . 'assets/css/admin/reporting.quick-view.css');

    }

    /**
     *    Adds sidebar metabox to all post types
     */
    public static function load_metaboxes() {
        $screen = get_current_screen();

        if (!isset($screen) || $screen->action == 'new' || $screen->action == 'add') {
            return;
        }

        /* Get public post types to add metabox to */
        $post_types = get_post_types(array('public' => true), 'names');

        /* Clean post types of known non-applicants */
        $exclude[] = 'attachment';
        $exclude[] = 'revisions';
        $exclude[] = 'nav_menu_item';
        $exclude[] = 'wp-lead';
        $exclude[] = 'automation';
        $exclude[] = 'rule';
        $exclude[] = 'list';
        $exclude[] = 'wp-call-to-action';
        $exclude[] = 'tracking-event';
        $exclude[] = 'inbound-forms';
        $exclude[] = 'email-template';
        $exclude[] = 'inbound-log';
        $exclude[] = 'landing-page';
        $exclude[] = 'acf-field-group';
        $exclude[] = 'email';
        $exclude[] = 'inbound-email';

        /* Add metabox to post types */
        foreach ($post_types as $post_type) {

            if (!in_array($post_type, $exclude)) {
                add_meta_box('inbound-analytics', __('Inbound Analytics', 'inbound-pro'), array(__CLASS__, 'display_quick_view'), $post_type, 'side', 'high');
            }
        }
    }

    /**
     * Hooked into 'screen_settings'. Adds the field to the settings area
     *
     * @access public
     * @return string The settings fields
     */

    public static function add_screen_option_field($rv, $screen) {

        $screen = get_current_screen();

        $whitelist = array('edit-post' , 'edit-page', 'post', 'page');
        if (!$screen || !in_array( $screen->id , $whitelist ) ) {
            return;
        }

        $val = get_user_option(
            'inbound_screen_option_range',
            get_current_user_id()
        );

        $val = ($val) ? $val : 90;

        $rv .= '<fieldset class="">';

        $rv .= '<legend>' . __('Inbound Analytics') . '</legend>';

        $rv .=  __('Reporting range in days' , 'inbound-pro' ). ':';

        $rv .= '<select  name="inbound_screen_option_range" class="" id="" style="width:100px;" ';

        $ranges = array(1,7,30,90,360);

        foreach ($ranges as $range) {
            $rv .= '<option value="'.$range.'" '. ( $val==$range ? 'selected="true"' : '' ).'">'.$range.' ' . __('days','inbound-pro') .'</option>';
        }

        $rv .= '</select></fieldset>';

        return $rv;

    }



    /**
     * Listen for updated screen options and save.
     *
     */
    public static function set_screen_option() {

        if (!isset($_POST['inbound_screen_option_range'])) {
            return;
        }

        $response = update_user_option(
            get_current_user_id(),
            'inbound_screen_option_range',
            intval($_POST['inbound_screen_option_range'])
        );


    }

    /**
     *    Displays Inbound Analytics sidebar (quick view)
     */
    public static function display_quick_view() {
        /* sets the default quick view template */
        $template_class_name = apply_filters('inbound-ananlytics/quick-view', 'Inbound_Quick_View');

        $template_class = new $template_class_name;
        $template_class->load_template(array());

        self::prepare_modal_container();
    }

    /**
     * Preapre Modal
     */
    public static function prepare_modal_container() {
        ?>

        <div class="modal" id='ia-modal-container'>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <iframe class='ia-frame'></iframe>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php
    }

    /**
     *    Register Columns
     */
    public static function register_columns($cols) {


        $cols['inbound_impressions'] = __('Impressions', 'inbound-pro');
        $cols['inbound_visitors'] = __('Visitors', 'inbound-pro');
        $cols['inbound_actions'] = __('Actions', 'inbound-pro');

        return $cols;

    }


    /**
     *    Prepare Column Data
     */
    public static function prepare_column_data($column, $post_id) {
        global $post;

        switch ($column) {
            case "inbound_impressions":
                $params = array(
                    'page_id' => $post_id,
                    'start_date' => self::$dates['start_date'],
                    'end_date' => self::$dates['end_date']
                );
                $results = Inbound_Events::get_page_views_by('page_id', $params);
                ?>
                <a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id=' . $post->ID . '&class=Inbound_Impressions_Report&range=' . self::$range . '&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox' title="<?php echo sprintf(__('past %s days', 'inbound-pro'), self::$range); ?>">
                    <?php echo count($results); ?>
                </a>
                <?php
                break;
            case "inbound_visitors":
                $params = array(
                    'page_id' => $post_id,
                    'start_date' => self::$dates['start_date'],
                    'end_date' => self::$dates['end_date']
                );
                $results = Inbound_Events::get_visitors($params);
                ?>
                <a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id=' . $post->ID . '&class=Inbound_Visitors_Report&range=' . self::$range . '&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox' title="<?php echo sprintf(__('past %s days', 'inbound-pro'), self::$range); ?>">
                    <?php echo count($results); ?>
                </a>
                <?php
                break;
            case "inbound_actions":
                $results = Inbound_Events::get_page_actions_count($post_id, 'any', self::$dates['start_date'], self::$dates['end_date']);
                ?>
                <a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id=' . $post->ID . '&class=Inbound_Events_Report&range=' . self::$range . '&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox' title="<?php echo sprintf(__('past %s days', 'inbound-pro'), self::$range); ?>">
                    <?php echo $results; ?>
                </a>
                <?php
                break;

        }
    }


    public static function process_column_sorting($pieces, $query) {

        global $wpdb, $table_prefix;

        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        $whitelist = array('post', 'page');

        if (!isset($screen) || !in_array($screen->post_type, $whitelist)) {
            return $pieces;
        }

        if ($query->is_main_query() && ($orderby = $query->get('orderby'))) {


            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            $order = strtoupper($query->get('order'));

            if (!in_array($order, array('ASC', 'DESC'))) {
                $order = 'ASC';
            }

            switch ($orderby) {

                case 'inbound_impressions':

                    $pieces['join'] .= " LEFT JOIN {$table_prefix}inbound_page_views ee ON ee.page_id = {$wpdb->posts}.ID  AND ee.datetime >= '" . self::$dates['start_date'] . "' AND  datetime <= '" . self::$dates['end_date'] . "'";

                    $pieces['groupby'] = " {$wpdb->posts}.ID";

                    $pieces['orderby'] = "COUNT(ee.page_id) $order ";

                    break;

                case 'inbound_visitors':

                    $pieces['join'] .= " LEFT JOIN (select lead_id, page_id from {$table_prefix}inbound_page_views group by lead_id) ee ON ee.page_id = {$wpdb->posts}.ID   AND ee.datetime >= '" . self::$dates['start_date'] . "' AND  datetime <= '" . self::$dates['end_date'] . "'";

                    $pieces['groupby'] = " {$wpdb->posts}.ID ";

                    $pieces['orderby'] = "COUNT(ee.lead_id) $order ";

                    break;


                case 'inbound_actions':

                    $pieces['join'] .= " LEFT JOIN {$table_prefix}inbound_events ee ON ee.page_id = {$wpdb->posts}.ID AND ee.datetime >= '" . self::$dates['start_date'] . "' AND  datetime <= '" . self::$dates['end_date'] . "'";

                    $pieces['groupby'] = " {$wpdb->posts}.ID";

                    $pieces['orderby'] = "COUNT(ee.page_id) $order ";

                    break;
            }
        } else {
            $pieces['orderby'] = " post_modified  DESC , " . $pieces['orderby'];
        }

        return $pieces;
    }

    public static function load_email_stats($post_id) {

        if (isset(self::$stats[$post_id])) {
            return self::$stats[$post_id];
        }

        self::$stats[$post_id] = Inbound_Email_Stats::get_email_timeseries_stats();
        return self::$stats[$post_id];
    }

    /**
     * Defines sortable columns
     * @param $columns
     * @return mixed
     */
    public static function define_sortable_columns($columns) {

        $columns['inbound_impressions'] = 'inbound_impressions';
        /* $columns['inbound_visitors'] = 'inbound_visitors'; */
        $columns['inbound_actions'] = 'inbound_actions';

        return $columns;
    }

    /**
     * Filter to unset tabs that are irrelevant to non-subscribers inside the lead profile
     * @param $tabs
     */
    public static function filter_profile_tabs($tabs) {

        if (INBOUND_ACCESS_LEVEL > 0 && INBOUND_ACCESS_LEVEL != 9 ) {
            unset($tabs['wpleads_lead_tab_activity']);
            unset($tabs['wpleads_lead_tab_conversions']);
        }

        return $tabs;
    }

}

new Inbound_Analytics();