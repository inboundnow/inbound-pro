<?php

class Leads_Post_Type {

    /**
     * Initiate class
     */
    public function __construct() {
        self::load_hooks();
    }

    /**
     * Load hooks and filters
     */
    public static function load_hooks() {

        /* add custom columns to wp-lead post type */
        add_filter("manage_wp-lead_posts_columns", array(__CLASS__, 'register_columns'));

        /* process custom columns */
        add_action("manage_posts_custom_column", array(__CLASS__, 'render_columns'), 10, 2);

        /* setup column sorting */
        add_filter("manage_edit-wp-lead_sortable_columns", array(__CLASS__, 'define_sortable_columns'));
        add_action('posts_clauses', array(__CLASS__, 'process_column_sorting'), 1, 2);

        /* modify row actions */
        add_filter('post_row_actions', array(__CLASS__, 'filter_row_actions'), 10, 2);

        /* adds ability to filter leads by custom taxonomy */
        add_action('restrict_manage_posts', array(__CLASS__, 'define_filters'));
        add_filter('parse_query', array(__CLASS__, 'process_filters'), 10, 1);

        /* setup bulk edit options */
        add_action('admin_footer-edit.php', array(__CLASS__, 'register_bulk_edit_fields'));

        /* process bulk actions  */
        add_action('load-edit.php', array(__CLASS__, 'process_bulk_actions'));

         /* record last time a piece of meta data was updated */
        add_action('added_post_meta', array(__CLASS__, 'record_meta_update'), 10, 4);
        add_action('updated_post_meta', array(__CLASS__, 'record_meta_update'), 10, 4);

        /* redirect lead notification email links to lead profile */
        add_action('admin_init', array(__CLASS__, 'redirect_email_profile_links'));

        /* gets json object of all lead meta data */
        add_action('wp_ajax_inbound_get_all_lead_data', array(__CLASS__, 'ajax_get_all_lead_data'));

        /* mark lead status as read */
        add_action('wp_ajax_wp_leads_mark_as_read_save', array(__CLASS__, 'ajax_mark_lead_as_read'));
        /* mark lead status as unread */
        add_action('wp_ajax_wp_leads_mark_as_read_undo', array(__CLASS__, 'ajax_mark_lead_as_unread'));
        /* mark lead status as read on first open */
        add_action('wp_ajax_wp_leads_auto_mark_as_read', array(__CLASS__, 'ajax_auto_mark_as_read'));

        /* add extra menu items */
        add_action('admin_menu', array(__CLASS__, 'setup_admin_menu'));

        /* enqueue scripts and styles in admin  */
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));

    }

    /**
     * Register custom columns for the wp-lead post type
     * @param $cols
     * @return array
     */
    public static function register_columns($cols) {

        $cols = array(
            "cb" => "<input type=\"checkbox\" />",
            "lead-picture" => __('Lead', 'inbound-pro' ),
            "first-name" => __('First Name', 'inbound-pro' ),
            "last-name" => __('Last Name', 'inbound-pro' ),
            "title" => __('Email', 'inbound-pro' ),
            "status" => __('Status', 'inbound-pro' ),
            'action-count' => __('Actions', 'inbound-pro' ),
            "page-views" => __('Page Views', 'inbound-pro' ),
            "modified" => __('Updated', 'inbound-pro' )
        );

        /* not sure about this, needs documentation - H */
        if (isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != "") {
            $the_val = $_GET['wp_leads_filter_field'];
            $nice_names = wpl_nice_field_names();
            if (array_key_exists($the_val, $nice_names)) {
                $the_val = $nice_names[$the_val];
            }
            $cols['custom'] = $the_val;
        }

        return $cols;
    }

    /**
     * Renders custom columns for wp-lead post type
     * @param $column
     * @param $post_id
     * @return mixed
     */
    public static function render_columns($column, $post_id) {
        global $post;

        if ($post->post_type != 'wp-lead') {
            return $column;
        }

        switch ($column) {
            case "lead-picture":
                $gravatar = self::get_gravatar($post->ID);
                echo '<img class="lead-grav-img" width="50" height="50" src="' . $gravatar . '">';
                break;
            case "first-name":
                $first_name = get_post_meta($post_id, 'wpleads_first_name', true);
                if (!$first_name || $first_name == 'false') {
                    $first_name = __('n/a', 'inbound-pro' );
                }
                echo $first_name;
                break;
            case "last-name":
                $last_name = get_post_meta($post_id, 'wpleads_last_name', true);
                if (!$last_name) {
                    $last_name = __('n/a', 'inbound-pro' );
                }
                echo $last_name;
                break;
            case "status":
                $lead_status = get_post_meta($post_id, 'wp_lead_status', true);
                self::display_status_pill($lead_status);
                break;
            case "action-count":
                $actions = Inbound_Events::get_total_activity($post_id);
                echo $actions;
                break;
            case "custom":
                if (isset($_GET['wp_leads_filter_field'])) {
                    $the_val = $_GET['wp_leads_filter_field'];
                }
                $custom_val = get_post_meta($post_id, $the_val, true);
                if (!$custom_val) {
                    $custom_val = 'N/A';
                }
                echo $custom_val;
                break;
            case "page-views":
                $page_view_count = Inbound_Metaboxes_Leads::get_page_view_count($post_id);
                echo($page_view_count ? $page_view_count : 0);
                break;
            case "company":
                $company = get_post_meta($post_id, 'wpleads_company_name', true);
                echo $company;
                break;
            case 'modified':
                $m_orig = get_post_field('post_modified', $post_id, 'raw');
                $m_stamp = strtotime($m_orig);
                $modified = date('n/j/y g:i a', $m_stamp);

                echo '<p class="mod-date">';
                echo '<em>' . $modified . '</em><br />';
                echo '</p>';
                break;
        }
    }

    /**
     * Defines sortable columns
     * @param $columns
     * @return mixed
     */
    public static function define_sortable_columns($columns) {

        $columns['first-name'] = 'first-name';
        $columns['last-name'] = 'last-name';
        $columns['status'] = 'status';
        $columns['company'] = 'company';
        $columns['modified'] = 'modified';
        $columns['action-count'] = 'action-count';
        $columns['page-views'] = 'page-views';
        if (isset($_GET['wp_leads_filter_field'])) {
            $the_val = $_GET['wp_leads_filter_field'];
            $columns['custom'] = $the_val;
        }


        return $columns;
    }

    /**
     * Adds custom taxonomies of wp-lead post type to filter controls
     */
    public static function define_filters() {
        global $typenow, $wpdb;

        if ($typenow != "wp-lead") {
            return;
        }

        /* add lead status filter */
        $statuses = Inbound_Leads::get_lead_statuses();
        echo '<select name="wp_lead_status">';
        echo '<option value="0">'.__('Lead Status','inbound-pro').'</option>';
        foreach( $statuses as $status)  {
            if (isset($_GET['wp_lead_status'])) {
                $selected = $status['key'] == $_GET['wp_lead_status'] ? ' selected ' : '';
            } else {
                $selected = '';
            }
            echo '<option value="'.$status['key'].'" data-color="'.$status['color'].'" ' .  $selected . '>' . $status['label'] .  '</option>';
        }
        echo "</select>";

        /* prepare taxonomy filters */
        $filters = get_object_taxonomies($typenow);
        foreach ($filters as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            (isset($_GET[$tax_slug])) ? $current = $_GET[$tax_slug] : $current = 0;
            wp_dropdown_categories(array('show_option_all' => __($tax_obj->label), 'taxonomy' => $tax_slug, 'name' => $tax_obj->name, 'orderby' => 'name', 'selected' => $current, 'hierarchical' => $tax_obj->hierarchical, 'show_count' => true, 'hide_empty' => false));
        }

    }

    /**
     * Modifies action row
     * @param $actions
     * @param $post
     * @return mixed
     */
    public static function filter_row_actions($actions, $post) {
        if ($post->post_type != 'wp-lead') {
            return $actions;
        }

        if (isset( $actions['edit'])) {
            $actions['edit'] = str_replace('Edit', __('View', 'inbound-pro' ), $actions['edit']);
        }

        unset($actions['inline hide-if-no-js']);

        return $actions;
    }

    public static function process_column_sorting($pieces, $query) {

        global $wpdb, $table_prefix;

        if (!isset($_GET['post_type']) || $_GET['post_type'] != 'wp-lead') {
            return $pieces;
        }

        if ($query->is_main_query() && ($orderby = $query->get('orderby'))) {
            $order = strtoupper($query->get('order'));

            if (!in_array($order, array('ASC', 'DESC'))) {
                $order = 'ASC';
            }

            switch ($orderby) {

                case 'first-name':

                    $pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = 'wpleads_first_name'";

                    $pieces['orderby'] = " wp_rd.meta_value $order , " . $pieces['orderby'];

                    break;

                case 'last-name':

                    $pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = 'wpleads_last_name'";

                    $pieces['orderby'] = " wp_rd.meta_value $order , " . $pieces['orderby'];

                    break;

                case 'status':

                    $pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = 'wp_lead_status'";

                    $pieces['orderby'] = " wp_rd.meta_value $order , " . $pieces['orderby'];

                    break;

                case 'page-views':

                    $pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = 'wpleads_page_view_count'";

                    $pieces['orderby'] = " wp_rd.meta_value $order , " . $pieces['orderby'];

                    break;


                case 'action-count':

                    $pieces['join'] .= " LEFT JOIN {$table_prefix}inbound_events ee ON ee.lead_id = {$wpdb->posts}.ID ";

                    $pieces['groupby'] = " {$wpdb->posts}.ID ";

                    $pieces['orderby'] = "COUNT(ee.lead_id) $order ";

                    break;
            }
        } else {
            $pieces['orderby'] = " post_modified  DESC , " . $pieces['orderby'];
        }


        return $pieces;
    }

    /**
     * Handles modification to the filter query
     * @param $query
     */
    public static function process_filters($query) {
        global $pagenow, $post;

        if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != 'wp-lead') {
            return;
        }

        $filters = get_object_taxonomies('wp-lead');
        $qv = &$query->query_vars;

        foreach ($filters as $tax_slug) {

            if ($pagenow == 'edit.php' && isset($qv[$tax_slug]) && is_numeric($qv[$tax_slug])) {
                if ($qv[$tax_slug] != 0) {
                    $term = get_term_by('id', $qv[$tax_slug], $tax_slug);
                    $qv[$tax_slug] = $term->slug;
                }
            }
        }

        /* prepare query by date */
        if (isset($_GET['current_date'])) {
            $timezone_day = _x('d', 'timezone date format');
            $wordpress_date_day = date_i18n($timezone_day);
            set_query_var('day', $wordpress_date_day); // Show only leads from today
            return;
        }

        /* prepare query by month */
        if (isset($_GET['current_month'])) {
            $timezone_month = _x('m', 'timezone date format');
            $wordpress_date_month = date_i18n($timezone_month);
            set_query_var('monthnum', $wordpress_date_month); // Show only leads from today
            return;
        }

        /* prepare query by custom meta field */
        if (isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != '') {
            $query->query_vars['meta_key'] = $_GET['wp_leads_filter_field'];
            if (isset($_GET['wp_leads_filter_field_val']) && $_GET['wp_leads_filter_field_val'] != '') {
                $query->query_vars['meta_value'] = $_GET['wp_leads_filter_field_val'];
            }
        }

        /* preapre query by email search */
        if (isset($_GET['lead-email']) && $_GET['lead-email'] != '') {
            $query->query_vars['meta_key'] = 'wpleads_email_address';
            $query->query_vars['meta_value'] = $_GET['lead-email'];

        }

        /* preapre query by status */
        if (isset($_GET['wp_lead_status']) && $_GET['wp_lead_status'] != '0') {
            $query->query_vars['meta_key'] = 'wp_lead_status';
            $query->query_vars['meta_value'] = $_GET['wp_lead_status'];
        }
    }

    /**
     * Adds additiona options to bulk edit fields
     */
    public static function register_bulk_edit_fields() {
        global $post_type;

        if ($post_type != 'wp-lead') {
            return;
        }

        $lists = wpleads_get_lead_lists_as_array();

        $html = "<select id='wordpress_list_select' name='action_wordpress_list_id'>";
        foreach ($lists as $id => $label) {
            $html .= "<option value='" . $id . "'>" . $label . "</option>";
        }
        $html .= "</select>";


        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {

                jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List', 'lp') ?>').appendTo("select[name='action']");
                jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List', 'lp') ?>').appendTo("select[name='action2']");

                jQuery(document).on('change', 'select[name=action]', function () {
                    var this_id = jQuery(this).val();
                    if (this_id.indexOf("export-csv") >= 0) {
                        jQuery('#posts-filter').prop('target', '_blank');
                    }
                    else if (this_id.indexOf("export-xml") >= 0) {
                        jQuery('#posts-filter').prop('target', '_blank');
                    }
                    else if (this_id.indexOf("add-to-list") >= 0) {
                        var html = "<?php echo $html; ?>";

                        jQuery("select[name='action']").after(html);
                    }
                    else {
                        jQuery('#posts-filter').prop('target', 'self');
                        jQuery('#wordpress_list_select').remove();
                    }
                });

            });
        </script>
        <?php
    }

    /**
     * process bulk actions for wp-lead post type
     */
    public static function process_bulk_actions() {

        if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != 'wp-lead' || !isset($_REQUEST['post'])) {
            return;
        }

        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();


        if (!current_user_can('manage_options')) {
            die();
        }

        $post_ids = array_map('intval', $_REQUEST['post']);

        switch ($action) {
            case 'add-to-list':
                $list_id = $_REQUEST['action_wordpress_list_id'];
                $added = 0;

                foreach ($post_ids as $post_id) {

                    $list_cpt = get_post($list_id, ARRAY_A);
                    $list_slug = $list_cpt['post_name'];
                    $list_title = $list_cpt['post_title'];

                    wpleads_add_lead_to_list($list_id, $post_id, $add = true);
                    $added++;
                }
                $sendback = add_query_arg(array('added' => $added, 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids)), $sendback);
                break;
            default:
                return;
        }

        // 4. Redirect client
        wp_redirect($sendback);
        exit();

    }

    /**
     * Prepare nice names for custom fields
     * @return array
     */
    public static function prepare_nice_names() {
        $nice_names = array("wpleads_company_name" => "Company Name", "wpleads_city" => "City", "wpleads_areaCode" => "Area Code", "wpleads_country_name" => "Country Name", "wpleads_region_code" => "State Abbreviation", "wpleads_region_name" => "State Name", "wp_lead_status" => "Lead Status", "events_triggered" => "Number of Events Triggered", "lp_page_views_count" => "Page View Count");

        $nice_names = apply_filters('wpleads_sort_by_custom_field_nice_names', $nice_names);
        return $nice_names;
    }

    /**
     * Listens for a change to a leads meta data and update the change timestamp
     * @param $meta_id
     * @param $post_id
     * @param $meta_key
     * @param $meta_value
     */
    public static function record_meta_update($meta_id, $post_id, $meta_key, $meta_value) {
        $ignore = array('_edit_lock', '_edit_last');
        $post_type = get_post_type($post_id);
        if ($post_type != 'wp-lead' || in_array($meta_key, $ignore)) {
            return;
        }

        remove_action('updated_post_meta', 'wpleads_after_post_meta_change', 10);
        remove_action('added_post_meta', 'wpleads_after_post_meta_change', 10);

        $timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
        $wordpress_date_time = date_i18n($timezone_format);

        update_post_meta($post_id, 'wpleads_last_updated', $wordpress_date_time);
        do_action('wpleads_after_post_meta_change', $post_id);
    }

    /**
     * Listens for incoming profile access link (found in a new lead notification)
     */
    public static function redirect_email_profile_links() {
        global $wpdb;

        if (!isset($_GET['lead-email-redirect']) || $_GET['lead-email-redirect'] != '') {
            return;
        }

        $lead_id = $_GET['lead-email-redirect'];
        $query = $wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . '
            WHERE post_title = %s
            AND post_type = \'wp-lead\'', $lead_id);

        $wpdb->query($query);

        if ($wpdb->num_rows) {
            $lead_ID = $wpdb->get_var($query);
            $url = admin_url();
            $redirect = $url . 'post.php?post=' . $lead_ID . '&action=edit';
            wp_redirect($redirect, 301);
            exit;
        }

    }

    /**
     * Add action links in Plugins table
     */
    public static function display_plugin_quick_links($links) {

        return array_merge(
            array(
                'settings' => '<a href="' . admin_url('edit.php?post_type=wp-lead&page=wpleads_global_settings') . '">' . __('Settings', 'ts-fab') . '</a>'
            ),
            $links
        );

    }

    public static function display_status_pill( $status ) {
        global $lead_statuses;

        if (!$lead_statuses) {
            $lead_statuses = Inbound_Leads::get_lead_statuses();
        }

        $pill = ( isset($lead_statuses[$status]) ) ? $lead_statuses[$status] : $lead_statuses['new'];


        echo '<label class="lead-status-pill lead-status-' . $pill['key'] . '" style="background-color:'.$pill['color'].'">';
        echo $pill['label'];
        echo '</label>';
    }

    /**
     * Converts ARRAY to XML
     * @param $array
     * @param $node_name
     * @return string $xml
     */
    public static function convert_array_to_xml($mixed, $node_name) {
        $xml = '';

        if (is_array($mixed) || is_object($mixed)) {
            foreach ($mixed as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '		<' . $key . '>' . "\n			" . self::convert_array_to_xml($value, $node_name) . '		</' . $key . '>' . "\n";
            }
        } else {
            $xml = htmlspecialchars($mixed, ENT_QUOTES) . "\n";
        }

        return $xml;
    }

    /**
     * Prepares XML from Lead data
     * @param $array
     * @param string $node_block
     * @param string $node_name
     * @return string $xml
     */
    public static function build_lead_xml($array, $node_block = 'lead_data', $node_name = 'lead_data') {

        $xml = "";
        $xml .= '	<' . $node_block . '>' . "\n";
        $xml .= "" . self::convert_array_to_xml($array, $node_name);
        $xml .= '	</' . $node_block . '>' . "\n";

        return $xml;
    }

    /**
     * Adds menu items to wp-lead's post type
     */
    public static function setup_admin_menu() {

        /* Lead Management */
        add_submenu_page(
            'edit.php?post_type=wp-lead',
            __('Bulk Actions', 'inbound-pro' ),
            __('Bulk Actions', 'inbound-pro' ),
            'edit_leads',
            'lead_management',
            array('Leads_Manager', 'display_ui')
        );

        /* Manage Forms */
        add_submenu_page(
            'edit.php?post_type=wp-lead',
            __('Forms', 'inbound-pro' ),
            __('Forms', 'inbound-pro' ),
            'edit_leads',
            'inbound-forms-redirect',
            100
        );

        /* Settings */
        add_submenu_page(
            'edit.php?post_type=wp-lead',
            __('Settings', 'inbound-pro' ),
            __('Settings', 'inbound-pro' ),
            'edit_leads',
            'wpleads_global_settings',
            array('Leads_Settings', 'display_settings')
        );


    }

    /**
     *
     */
    public static function get_gravatar($lead_id, $size = 50) {
        $email = get_post_meta($lead_id, 'wpleads_email_address', true);

        $size = ($size) ? $size : 50;

        $default = WPL_URLPATH . '/assets/images/gravatar_default_50.jpg'; // doesn't work for some sites

        $gravatar = "//www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
        $extra_image = get_post_meta($lead_id, 'lead_main_image', true);

        // Fix for localhost view
        if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
            $gravatar = $default;
        }

        if (preg_match("/gravatar_default_/", $gravatar) && $extra_image != "") {
            $gravatar = $extra_image;
        }

        return $gravatar;
    }


    /**
     * Enqueue scripts and styles for admin
     */
    public static function enqueue_admin_scripts($hook) {
        global $post;

        $post_type = isset($post) ? get_post_type($post) : null;

        $screen = get_current_screen();

        /*  list setup page */
        if ($screen->id === 'edit-wplead_list_category') {
            wp_enqueue_script('wpleads-list-page', WPL_URLPATH . 'assets/js/wpl.list-page.js', array('jquery'));
            wp_enqueue_style('wpleads-list-page-css', WPL_URLPATH . 'assets/css/wpl.list-page.css');
            return;
        }

        /*  list page */
        if ($screen->id === 'edit-wp-lead') {
            wp_enqueue_script('wpleads-list', WPL_URLPATH . 'assets/js/wpl.leads-list.js');
            wp_enqueue_style('wpleads-list-css', WPL_URLPATH . 'assets/css/wpl.leads-list.css');
            return;
        }
    }

    /**
     * Ajax listener to return json object of all lead meta data
     */
    public static function ajax_get_all_lead_data() {

        if (!current_user_can('activate_plugins')) {
            return;
        }

        $wp_lead_id = $_POST['wp_lead_id'];
        if (isset($wp_lead_id) && is_numeric($wp_lead_id)) {
            global $wpdb;
            $data = array();
            $wpdb->query($wpdb->prepare("
		  SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = %d", $wp_lead_id
            ));

            foreach ($wpdb->last_result as $k => $v) {
                $data[$v->meta_key] = $v->meta_value;
            };

            echo json_encode($data, JSON_FORCE_OBJECT);
            wp_die();
        }
    }

    /**
     * Ajax listener to mark lead as read
     */
    public static function ajax_mark_lead_as_read() {
        global $wpdb;

        $post_id = intval($_POST['page_id']);

        update_post_meta($post_id, 'wp_lead_status', 'read');
        header('HTTP/1.1 200 OK');
        exit;
    }

    /**
     * Ajax listener to mark lead as unread
     */
    public static function ajax_mark_lead_as_unread() {
        global $wpdb;

        $post_id = intval($_POST['page_id']);

        update_post_meta($post_id, 'wp_lead_status', 'new');
        header('HTTP/1.1 200 OK');
        exit;
    }

    /**
     * Ajax listener to automatically mark a lead as read when the lead is opened for the first time
     */
    public static function ajax_auto_mark_as_read() {
        global $wpdb;

        $post_id = intval($_POST['page_id']);

        update_post_meta($post_id, 'wp_lead_status', 'read');
        header('HTTP/1.1 200 OK');
    }
}

new Leads_Post_Type;