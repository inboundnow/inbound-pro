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
        add_filter( "manage_wp-lead_posts_columns", array( __CLASS__ , 'register_columns' ) );

        /* process custom columns */
        add_action( "manage_posts_custom_column", array( __CLASS__ , 'render_columns' ), 10, 2 );

        /* setup column sorting */
        add_filter("manage_edit-wp-lead_sortable_columns", array( __CLASS__ , 'define_sortable_columns' ));

        /* modify row actions */
        add_filter('post_row_actions', array( __CLASS__ , 'filter_row_actions' ), 10, 2);

        /* adds ability to filter leads by custom taxonomy */
        add_action( 'restrict_manage_posts', array( __CLASS__ , 'define_filters' ) );
        add_filter( 'parse_query' , array( __CLASS__ , 'process_filters' ) , 10 , 1);

        /* record last time a piece of meta data was updated */
        add_action( 'added_post_meta', array( __CLASS__  , 'record_meta_update' ), 10, 4 );
        add_action( 'updated_post_meta', array( __CLASS__  , 'record_meta_update' ), 10, 4 );

        /* redirect lead notification email links to lead profile */
        add_action('admin_init', array( __CLASS__ , 'redirect_email_profile_links' ) );

        /* gets json object of all lead meta data */
        add_action('wp_ajax_inbound_get_all_lead_data', array( __CLASS__ , 'ajax_get_all_lead_data' ) );

        /* mark lead status as read */
        add_action('wp_ajax_wp_leads_mark_as_read_save', array( __CLASS__ , 'ajax_mark_lead_as_read' ) );
        /* mark lead status as unread */
        add_action('wp_ajax_wp_leads_mark_as_read_undo', array( __CLASS__ , 'ajax_mark_lead_as_unread' ) );
        /* mark lead status as read on first open */
        add_action('wp_ajax_wp_leads_auto_mark_as_read', array( __CLASS__ , 'ajax_auto_mark_as_read' ));

        /* setup bulk edit options */
        add_action('admin_footer-edit.php', array( __CLASS__ , 'register_bulk_edit_fields' ));

        /* process bulk actions  */
        add_action('load-edit.php',  array( __CLASS__ , 'process_bulk_actions' ));

        /* prepare admin notifications for bulk actions */
        add_action( 'admin_notices', array( __CLASS__ , 'display_admin_notices' ) );
    }

    /**
     * Register custom columns for the wp-lead post type
     * @param $cols
     * @return array
     */
    public static function register_columns( $cols ) {

        $cols = array(
            "cb" => "<input type=\"checkbox\" />",
            "lead-picture" => __( 'Lead' , 'leads' ),
            "first-name" => __( 'First Name' , 'leads' ),
            "last-name" => __( 'Last Name' , 'leads' ),
            "title" => __( 'Email' , 'leads' ),
            "status" => __( 'Status' , 'leads' ),
            'conversion-count' => __( 'Conversion Count' , 'leads' ),
            "page-views" => __( 'Total Page Views' , 'leads' ),
            "date" => __( 'Created' , 'leads' )
        );

        /* not sure about this, needs documentation - H */
        if (isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != "") {
            $the_val = $_GET['wp_leads_filter_field'];
            $nice_names = wpl_nice_field_names();
            if(array_key_exists($the_val, $nice_names)){
                $the_val = $nice_names[$the_val];
            }
            $cols['custom'] =  $the_val;
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
                $email = get_post_meta($post_id, 'wpleads_email_address', true);
                $size = 50;
                $url = site_url();
                $default = WPL_URLPATH . '/images/gravatar_default_50.jpg'; // doesn't work for some sites

                $gravatar = "//www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
                $extra_image = get_post_meta($post_id, 'lead_main_image', true);
                /*
                Super expensive call. Need more elegant solution
                 $response = get_headers($gravatar);
                if ($response[0] === "HTTP/1.0 302 Found"){
                    $gravatar = $url . '/wp-content/plugins/leads/images/gravatar_default_50.jpg';
                } else {
                    $gravatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
                }
                */
                // Fix for localhost view
                if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
                    $gravatar = $default;
                }
                if (preg_match("/gravatar_default_/", $gravatar) && $extra_image != "") {
                    $gravatar = $extra_image;
                    $gravatar2 = $extra_image;
                }
                echo '<img class="lead-grav-img" width="50" height="50" src="' . $gravatar . '">';
                break;
            case "first-name":
                $first_name = get_post_meta($post_id, 'wpleads_first_name', true);
                if (!$first_name || $first_name == 'false') {
                    $first_name = __('n/a', 'leads');
                }
                echo $first_name;
                break;
            case "last-name":
                $last_name = get_post_meta($post_id, 'wpleads_last_name', true);
                if (!$last_name) {
                    $last_name = __('n/a', 'leads');
                }
                echo $last_name;
                break;
            case "status":
                $lead_status = get_post_meta($post_id, 'wp_lead_status', true);
                echo $lead_status;
                break;
            case "conversion-count":
                $last_conversion = get_post_meta($post_id, 'wpleads_conversion_data', true);
                $last_conversion = json_decode($last_conversion, true);
                if (is_array($last_conversion)) {
                    $count_conversions = count($last_conversion);
                } else {
                    $count_conversions = get_post_meta($post_id, 'wpleads_conversion_count', true);
                }
                echo $count_conversions;
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
                $page_views = get_post_meta($post_id, 'page_views', true);
                $page_view_array = json_decode($page_views, true);
                $page_view_count = 0;
                if (is_array($page_view_array)) {
                    foreach ($page_view_array as $key => $val) {
                        $page_view_count += count($page_view_array[$key]);
                    }
                } else {
                    $page_view_count = get_post_meta($post_id, 'wpleads_page_view_count', true);
                }
                echo $page_view_count;
                break;
            case "company":
                $company = get_post_meta($post_id, 'wpleads_company_name', true);
                echo $company;
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

        /* prepare taxonomy filters */
        $filters = get_object_taxonomies($typenow);
        foreach ($filters as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            (isset($_GET[$tax_slug])) ? $current = $_GET[$tax_slug] : $current = 0;
            wp_dropdown_categories(array('show_option_all' => __($tax_obj->label), 'taxonomy' => $tax_slug, 'name' => $tax_obj->name, 'orderby' => 'name', 'selected' => $current, 'hierarchical' => $tax_obj->hierarchical, 'show_count' => true, 'hide_empty' => false));
        }

        /* preapre custom field filters */
        $query = "SELECT DISTINCT($wpdb->postmeta.meta_key)
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta
                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                WHERE $wpdb->posts.post_type = '%s'
                AND $wpdb->postmeta.meta_key != ''
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'";

        $fields = $wpdb->get_col($wpdb->prepare($query, 'wp-lead'));
        ?>
        <select name="wp_leads_filter_field" id="lead-meta-filter">
            <option value="" class='lead-meta-empty'>
                <?php _e('Filter By Custom Fields', 'baapf'); ?>
            </option>
            <?php
            $current = isset($_GET['wp_leads_filter_field']) ? $_GET['wp_leads_filter_field'] : '';
            $current_v = isset($_GET['wp_leads_filter_field_val']) ? $_GET['wp_leads_filter_field_val'] : '';

            $nice_names = self::prepare_nice_names();
            foreach ($fields as $field) {

                if (array_key_exists($field, $nice_names)) {
                    $label = $nice_names[$field];
                    echo "<option value='$field' " . selected($current, $field) . ">$label</option>";
                }

            }
            ?>
        </select>
        <span class='lead_meta_val'>
            <?php _e('Value:', 'leads'); ?>
        </span>
        <input type="TEXT"  name="wp_leads_filter_field_val" class="lead_meta_val" placeholder="<?php _e('Leave Blank to Search All'  ,'leads' ); ?>" value="<?php echo $current_v; ?>"/>
        <?php
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

        $actions['edit'] = str_replace('Edit', __('View', 'leads') , $actions['edit']);
        unset($actions['inline hide-if-no-js']);

        return $actions;
    }

    /**
     * Handles modification to the filter query
     * @param $query
     */
    public static function process_filters( $query ) {
        global $pagenow, $post;

        if ( !isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != 'wp-lead' ) {
            return;
        }

        $filters = get_object_taxonomies( 'wp-lead' );
        $qv = &$query->query_vars;

        foreach ( $filters as $tax_slug ) {

            if( $pagenow=='edit.php' && isset($qv[$tax_slug]) && is_numeric($qv[$tax_slug]) ) {
                if($qv[$tax_slug] != 0){
                    $term = get_term_by('id',$qv[$tax_slug],$tax_slug);
                    $qv[$tax_slug] = $term->slug;
                }
            }
        }

        /* prepare query by date */
        if( isset($_GET['current_date']) ) {
            $timezone_day = _x('d', 'timezone date format');
            $wordpress_date_day =  date_i18n($timezone_day);
            set_query_var('day', $wordpress_date_day ); // Show only leads from today
            return;
        }

        /* prepare query by month */
        if( isset($_GET['current_month']) ) {
            $timezone_month = _x('m', 'timezone date format');
            $wordpress_date_month =  date_i18n($timezone_month);
            set_query_var('monthnum', $wordpress_date_month ); // Show only leads from today
            return;
        }

        /* prepare query by custom meta field */
        if ( isset($_GET['wp_leads_filter_field']) && $_GET['wp_leads_filter_field'] != '') {
            $query->query_vars['meta_key'] = $_GET['wp_leads_filter_field'];
            if (isset($_GET['wp_leads_filter_field_val']) && $_GET['wp_leads_filter_field_val'] != '') {
                $query->query_vars['meta_value'] = $_GET['wp_leads_filter_field_val'];
            }
        }

        /* preapre query by email search */
        if ( isset($_GET['lead-email']) && $_GET['lead-email'] != '') {
            $query->query_vars['meta_key'] = 'wpleads_email_address';
            if (isset($_GET['lead-email']) && $_GET['lead-email'] != '') {
                $query->query_vars['meta_value'] = $_GET['lead-email'];
            }
        }
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
            case 'export-csv':
                $exported = 0;

                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header('Content-Description: File Transfer');
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=leads-export-csv-" . date("m.d.y") . ".csv");
                header("Expires: 0");
                header("Pragma: public");

                $fh = @fopen('php://output', 'w');

                //get all keys
                foreach ($post_ids as $post_id) {
                    $this_lead_data = get_post_custom($post_id);

                    foreach ($this_lead_data as $key => $val) {
                        $lead_meta_pairs[$key] = $key;
                    }
                }

                // Add a header row if it hasn't been added yet
                fputcsv($fh, array_keys($lead_meta_pairs));
                $headerDisplayed = true;


                foreach ($post_ids as $post_id) {
                    unset($this_row_data);

                    $this_lead_data = get_post_custom($post_id);


                    foreach ($lead_meta_pairs as $key => $val) {

                        if (isset($this_lead_data[$key])) {
                            $val = $this_lead_data[$key];
                            if (is_array($val)) $val = implode(';', $val);
                        } else {
                            $val = "";
                        }

                        $this_row_data[$key] = $val;
                    }

                    fputcsv($fh, $this_row_data);
                    $exported++;
                }
                // Close the file
                fclose($fh);

                // build the redirect url
                $sendback = add_query_arg(array('exported' => $exported, 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids)), $sendback);

                // Make sure nothing else is sent, our file is done
                exit;
                break;
            case 'export-xml':
                echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
                foreach ($post_ids as $post_id) {
                    $this_lead_data = get_post_custom($post_id);

                    foreach ($this_lead_data as $key => $val) {

                        if (is_array($val)) {
                            $this_lead_data[$key] = implode(',', $val);
                        }
                    }

                    unset($this_lead_data['_edit_lock']);
                    unset($this_lead_data['_yoast_wpseo_linkdex']);

                    $xml = self::build_lead_xml($this_lead_data);
                    echo $xml;
                }
                // Make sure nothing else is sent, our file is done
                exit;

                // build the redirect url
                $sendback = add_query_arg(array('exported' => $exported, 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids)), $sendback);
                break;
            case 'export-list':
                $list_id = $_REQUEST['action_wordpress_list_id'];
                $exported = 0;

                foreach ($post_ids as $post_id) {

                    $list_cpt = get_post($list_id, ARRAY_A);
                    $list_slug = $list_cpt['post_name'];
                    $list_title = $list_cpt['post_title'];

                    $wplead_cat = get_term_by('slug', $list_slug, 'wplead_list_category');
                    $wplead_cat_id = $wplead_cat->term_id;

                    $exported++;
                }
                $sendback = add_query_arg(array('exported' => $exported, 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids)), $sendback);
                break;
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
        $nice_names = array("wpleads_company_name" => "Company Name", "wpleads_city" => "City", "wpleads_areaCode" => "Area Code", "wpleads_country_name" => "Country Name", "wpleads_region_code" => "State Abbreviation", "wpleads_region_name" => "State Name", "wp_lead_status" => "Lead Status", "events_triggered" => "Number of Events Triggered", "lp_page_views_count" => "Page View Count", "wpleads_conversion_count" => "Number of Conversions");

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
    public static function record_meta_update( $meta_id, $post_id, $meta_key, $meta_value ) {
        $ignore = array ('_edit_lock', '_edit_last');
        $post_type = get_post_type($post_id);
        if ( $post_type != 'wp-lead' || in_array( $meta_key , $ignore ) ) {
            return;
        }

        remove_action( 'updated_post_meta' , 'wpleads_after_post_meta_change' , 10 );
        remove_action( 'added_post_meta' , 'wpleads_after_post_meta_change' , 10 );

        $timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
        $wordpress_date_time =  date_i18n($timezone_format);

        update_post_meta( $post_id , 'wpleads_last_updated' , $wordpress_date_time );
        do_action( 'wpleads_after_post_meta_change' , $post_id );
    }

    /**
     * Listens for incoming profile access link (found in a new lead notification)
     */
    public static function redirect_email_profile_links() {
        global $wpdb;

        if ( !isset($_GET['lead-email-redirect']) || $_GET['lead-email-redirect'] != '') {
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

                jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List','lp') ?>').appendTo("select[name='action']");
                jQuery('<option>').val('add-to-list').text('<?php _e('Add to Contact List' , 'lp') ?>').appendTo("select[name='action2']");

                jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action']");
                jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action2']");

                jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action']");
                jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action2']");

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
     * Display admin notices for bulk actions
     */
    public static function display_admin_notices() {
        global $post_type, $pagenow;
        if ($pagenow == 'edit.php' && $post_type == 'wp-lead' && isset($_REQUEST['exported']) && (int)$_REQUEST['exported']) {
            $message = sprintf(_n('Lead exported.', '%s lead exported.', $_REQUEST['exported']), number_format_i18n($_REQUEST['exported']));
            echo "<div class=\"updated\"><p>{$message}</p></div>";
        }
        if ($pagenow == 'edit.php' && $post_type == 'wp-lead' && isset($_REQUEST['added']) && (int)$_REQUEST['added']) {
            $message = sprintf(_n('Lead Added.', '%s leads added to list.', $_REQUEST['added']), number_format_i18n($_REQUEST['added']));
            echo "<div class=\"updated\"><p>{$message}</p></div>";
        }
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
     * Ajax listener to return json object of all lead meta data
     */
    public static function ajax_get_all_lead_data() {
        $wp_lead_id = $_POST['wp_lead_id'];
        if (isset($wp_lead_id) && is_numeric($wp_lead_id)) {
            global $wpdb;
            $data   =   array();
            $wpdb->query($wpdb->prepare("
		  SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = %d", $wp_lead_id
            ));

            foreach($wpdb->last_result as $k => $v) {
                $data[$v->meta_key] =   $v->meta_value;
            };

            echo json_encode($data,JSON_FORCE_OBJECT);
            wp_die();
        }
    }

    /**
     * Ajax listener to mark lead as read
     */
    public static function  ajax_mark_lead_as_read() {
        global $wpdb;
        $newrules = $_POST['j_rules'];

        $post_id = mysql_real_escape_string($_POST['page_id']);

        add_post_meta($post_id, 'wp_lead_status', 'Read', true) or update_post_meta($post_id, 'wp_lead_status', $newrules);
        header('HTTP/1.1 200 OK');
        exit;
    }

    /**
     * Ajax listener to mark lead as unread
     */
     public static function ajax_mark_lead_as_unread() {
         global $wpdb;

         $newrules = "New Lead";

         $post_id = mysql_real_escape_string($_POST['page_id']);

         add_post_meta($post_id, 'wp_lead_status', 'New Lead', true) or update_post_meta($post_id, 'wp_lead_status', $newrules);
         header('HTTP/1.1 200 OK');
         exit;
     }

     /**
      * Ajax listener to automatically mark a lead as read when the lead is opened for the first time
      */
     public static function ajax_auto_mark_as_read() {
         global $wpdb;

         $newrules = "Read";
         $post_id = mysql_real_escape_string($_POST['page_id']);

         add_post_meta($post_id, 'wp_lead_status', 'Read', true) or update_post_meta($post_id, 'wp_lead_status', $newrules);
         header('HTTP/1.1 200 OK');
     }
}

new Leads_Post_Type;