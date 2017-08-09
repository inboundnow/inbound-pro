<?php

/**
 * Class for  registering the landing page CPT and expanding the CPT's listing page with additional data
 * @package LandingPages
 * @subpackage Management
 */


class Landing_Pages_Post_Type {

    function __construct() {
        self::load_hooks();
    }

    /**
     * setup hooks and filters
     */
    private function load_hooks() {
        add_action('init', array( __CLASS__ , 'register_post_type' ) );
        add_action( 'admin_init' , array( __CLASS__ , 'register_role_capabilities' ) ,999);
        add_action('init', array( __CLASS__ , 'register_taxonomies' ) );
        add_action('init', array( __CLASS__ , 'add_rewrite_rules') );
        add_filter('mod_rewrite_rules', array( __CLASS__ , 'filter_rewrite_rules' ) , 1);

        /* adds & managed collumns */
        add_filter("manage_edit-landing-page_columns", array( __CLASS__ , 'register_columns' ) );
        add_action("manage_posts_custom_column", array( __CLASS__ , "display_columns" ) );
        add_filter('landing-page_orderby', 'lp_column_orderby', 10, 2);

        /* disable SEO Filter */
        if ((isset($_GET['post_type']) && ($_GET['post_type'] == 'landing-page'))) {
            add_filter('wpseo_use_page_analysis', '__return_false');
        }

        /* adds category to landing page sorting filter */
        add_action('restrict_manage_posts', array( __CLASS__, 'sort_by_category' ) );
        add_filter('parse_query', array( __CLASS__ , 'sort_by_category_prepare_query' ));

        /* make columns sortable */
        add_filter('manage_edit-landing-page_sortable_columns', array( __CLASS__ , 'define_sortable_columns' ));

        /* add styling handlers to custom post states */
        add_filter('display_post_states', array( __CLASS__ , 'filter_custom_post_states' ) );

        /* enqueue scripts for landing page listings */
        add_action( 'admin_enqueue_scripts' , array(__CLASS__, 'enqueue_admin_scripts' ) );


        /* enqueue scripts for landing page listings */
        if (isset($_GET['dont_save'])
            || isset($_GET['iframe_window'])
            || isset($_GET['inbound-preview']) ) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'stop_stat_tracking') , 20);
        }

        /* load iframed preview page when preview is clicked from AB stats box */
        if (isset($_GET['iframe_window'])) {
            /*add_action('wp_head', array( __CLASS__ , 'load_preview_iframe' ) );*/
            add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts_iframe') );
        }

        /* Miscelanous wp_head - Should probably be refactored into enqueue - h */
        add_action('wp_head', array(__CLASS__, 'wp_head' ));

    }

    /**
     * register post type
     */
    public static function register_post_type() {

        $slug = Landing_Pages_Settings::get_setting( 'lp-main-landing-page-permalink-prefix', 'go' );
        $featured_images = Landing_Pages_Settings::get_setting( 'lp-main-landing-page-enable-featured-image', false );

        $capabilities = array('title','custom-fields','editor', 'revisions');

        if ($featured_images) {
            array_push($capabilities , 'thumbnail');
        }

        $labels = array(
            'name' => __('Landing Pages', 'inbound-pro' ),
            'singular_name' => __('Landing Page', 'inbound-pro' ),
            'add_new' => __('Add New',  'inbound-pro' ),
            'add_new_item' => __('Add New Landing Page' , 'inbound-pro' ),
            'edit_item' => __('Edit Landing Page' , 'inbound-pro' ),
            'new_item' => __('New Landing Page' , 'inbound-pro' ),
            'view_item' => __('View Landing Page' , 'inbound-pro' ),
            'search_items' => __('Search Landing Page' , 'inbound-pro' ),
            'not_found' =>  __('Nothing found' , 'inbound-pro' ),
            'not_found_in_trash' => __('Nothing found in Trash' , 'inbound-pro' ),
            'parent_item_colon' => ''
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'menu_icon' => '',
            'rewrite' => array("slug" => "$slug",'with_front' => false),
            'capability_type' => array('landing_page','landing_pages'),
            'map_meta_cap' => true,
            'hierarchical' => false,
            'menu_position' => 32,
            'supports' => $capabilities
        );

        register_post_type( 'landing-page' , $args );
    }

    /**
     * Register Role Capabilities
     */
    public static function register_role_capabilities() {
        // Add the roles you'd like to administer the custom post types
        $roles = array('inbound_marketer','administrator');

        // Loop through each role and assign capabilities
        foreach($roles as $the_role) {

            $role = get_role($the_role);
            if (!$role) {
                continue;
            }

            $role->add_cap( 'read' );
            $role->add_cap( 'read_landing_page');
            $role->add_cap( 'read_private_landing_pages' );
            $role->add_cap( 'edit_landing_page' );
            $role->add_cap( 'edit_landing_pages' );
            $role->add_cap( 'edit_others_landing_pages' );
            $role->add_cap( 'edit_published_landing_pages' );
            $role->add_cap( 'publish_landing_pages' );
            $role->add_cap( 'delete_landing_pages' );
            $role->add_cap( 'delete_others_landing_pages' );
            $role->add_cap( 'delete_private_landing_pages' );
            $role->add_cap( 'delete_published_landing_pages' );
        }
    }

    /**
     * Register landing page taxonomies
     */
    public static function register_taxonomies() {

        $args = array(
            'hierarchical' => true,
            'label' => __("Categories", 'inbound-pro'),
            'singular_label' => __("Landing Page Category",
                'landing-pages'),
            'show_ui' => true,
            'query_var' => true,
            "rewrite" => true
        );

        register_taxonomy( 'landing_page_category', array('landing-page'), $args);
    }



    /**
     * Register columns
     *
     * @param $columns
     * @return array
     */
    public static function register_columns($columns) {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "thumbnail-lander" => __("Preview", 'inbound-pro'),
            "title" => __("Landing Page Title", 'inbound-pro'),
            "stats" => __("Split Testing Results", 'inbound-pro'),
            "impressions" => __("Total<br>Visits", 'inbound-pro'),
            "actions" => __("Total<br>Conversions", 'inbound-pro'),
            "cr" => __("Total<br>Conversion Rate", 'inbound-pro')
        );
        return $columns;
    }

    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts( $hook ) {
        global $post;
        $screen = get_current_screen();

        if (!isset($post) ||$post->post_type != 'landing-page') {
            return;
        }

        wp_enqueue_style('lp-content-stats', LANDINGPAGES_URLPATH . 'assets/css/admin/content-stats.css', array() , null);

        /* listing page only */
        if ($screen->id == 'edit-landing-page' ) {
            /* load stat clear handlers */
            wp_enqueue_script( 'lp-admin-clear-stats-ajax-request', LANDINGPAGES_URLPATH . 'assets/js/ajax.clearstats.js', array( 'jquery' ) , null );
            wp_localize_script( 'lp-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'lp_clear_nonce' => wp_create_nonce('lp-clear-nonce') ) );

            wp_enqueue_script('landing-page-list', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.landing-page-list.js', array() , null);
            wp_enqueue_style('landing-page-list-css', LANDINGPAGES_URLPATH.'assets/css/admin/landing-page-list.css', array() , null);
            wp_enqueue_script('jqueryui');

        }



        /* load css when landing page iframe preview is being loaded from within wp-admin */
        if (isset($_GET['iframe_window'])) {
            wp_enqueue_style('lp_ab_testing_customizer_css', LANDINGPAGES_URLPATH . 'assets/css/frontend/customizer-preview.css', array() , null);
        }
    }

    /**
     * Enqueue frontend scripts
     */
    public static function enqueue_frontend_scripts() {
        global $post;
        if ( !isset($post) && $post->post_type=='landing-page') {
            return;
        }

        wp_enqueue_style('inbound-wordpress-base', LANDINGPAGES_URLPATH . 'assets/css/frontend/global-landing-page-style.css', array() , null);
        wp_enqueue_style('inbound-shortcodes', INBOUND_FORMS.'css/frontend-render.css', array() , null);


    }

    /**
     * Display column data
     * @param $columns
     * @return array
     */
    public static function display_columns($column) {
        global $post;

        if ($post->post_type != 'landing-page') return;

        switch ($column) {
            case 'ID':
                echo $post->ID;
                BREAK;
            case 'thumbnail-lander':

                $template = get_post_meta($post->ID, 'lp-selected-template', true);
                $permalink = get_permalink($post->ID);
                $datetime = the_modified_date('YmjH', null, null, false);
                $permalink = $permalink = $permalink . '?dt=' . $datetime;

                if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {

                    if(file_exists(LANDINGPAGES_UPLOADS_PATH . $template . '/thumbnail.png')) {
                        $thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $template . '/thumbnail.png';
                    } else if(file_exists(LANDINGPAGES_UPLOADS_PATH . $template . '/thumbnail.jpg')) {

                        $thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $template . '/thumbnail.jpg';

                    } else {
                        $thumbnail = LANDINGPAGES_URLPATH . 'templates/' . $template . '/thumbnail.png';
                    }

                } else {
                    $thumbnail = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';
                }

                echo "<a title='" . __('Click to Preview this variation', 'inbound-pro') . "' class='thickbox' href='" . $permalink . "?lp-variation-id=0&iframe_window=on&post_id=" . $post->ID . "&TB_iframe=true&width=640&height=703' target='_blank'><img src='" . $thumbnail . "' style='width:155px;height:110px;' title='Click to Preview'></a>";
                BREAK;
            case "stats":
                self::show_stats();
                BREAK;
            case "impressions" :
                echo self::show_aggregated_stats("impressions");
                BREAK;
            case "actions":
                echo self::show_aggregated_stats("actions");
                BREAK;
            case "cr":
                echo self::show_aggregated_stats("cr") . "%";
                BREAK;
            case "template":
                $template_used = Landing_Pages_Variations::get_current_template( $post->ID );
                echo $template_used;
                BREAK;
        }
    }


    /**
     * Define Sortable Columns
     */
    public static function define_sortable_columns($columns) {

        return array(
            'title' 			=> 'title',
            'impressions'		=> 'impressions',
            'actions'			=> 'actions',
            'cr'				=> 'cr'
        );

    }

    /**
     * Define Row Actions
     */
    public static function filter_row_actions( $actions , $post ) {

        if ($post->post_type=='wp-call-to-action') 			{
            $actions['clear'] = '<a href="#clear-stats" id="wp_cta_clear_'.$post->ID.'" class="clear_stats" title="'
                . __( 'Clear impression and conversion records', 'cta' )
                . '" >' .	__( 'Clear All Stats' , 'cta') . '</a>';

            /* show shortcode */
            $actions['clear'] .= '<br><span style="color:#000;">' . __( 'Shortcode:' , 'cta' ) .'</span> <input type="text" style="width: 60%; text-align: center;" class="regular-text code short-shortcode-input" readonly="readonly" id="shortcode" name="shortcode" value="[cta id=\''.$post->ID.'\']">';
        }

        return $actions;

    }


    /**
     * Needs further refactoring & documentation
     * @param $type_of_stat
     * @return float|int
     */
    public static function show_aggregated_stats($type_of_stat) {
        global $post;

        $variations = get_post_meta($post->ID, 'lp-ab-variations', true);
        $variations = explode(",", $variations);

        $impressions = 0;
        $conversions = 0;

        foreach ($variations as $vid) {
            $each_impression = get_post_meta($post->ID, 'lp-ab-variation-impressions-' . $vid, true);
            $each_conversion = get_post_meta($post->ID, 'lp-ab-variation-conversions-' . $vid, true);
            (($each_conversion === "")) ? $final_conversion = 0 : $final_conversion = $each_conversion;
            $impressions += get_post_meta($post->ID, 'lp-ab-variation-impressions-' . $vid, true);
            $conversions += get_post_meta($post->ID, 'lp-ab-variation-conversions-' . $vid, true);
        }

        if ($type_of_stat === "actions") {
            return $conversions;
        }
        if ($type_of_stat === "impressions") {
            return $impressions;
        }
        if ($type_of_stat === "cr") {
            if ($impressions != 0) {
                $conversion_rate = $conversions / $impressions;
            } else {
                $conversion_rate = 0;
            }
            $conversion_rate = round($conversion_rate, 2) * 100;
            return $conversion_rate;
        }

    }

    /**
     * Adds rewrite rules
     */
    public static function add_rewrite_rules() {
        if ( !class_exists('Inbound_Pro_Plugin')){
            $this_path = LANDINGPAGES_PATH;
            $this_path = explode('wp-content', $this_path);
            $this_path = "wp-content" . $this_path[1];
        } else {
            $this_path = INBOUND_PRO_PATH;
            $this_path = explode('wp-content', $this_path);
            $this_path = "wp-content" . $this_path[1] . "core/landing-pages/";
        }

        /* handles local environment */
        $this_path = str_replace("\\" , "/" , $this_path);

        $slug = Landing_Pages_Settings::get_setting( 'lp-main-landing-page-permalink-prefix', 'go' );

        $ab_testing = Landing_Pages_Settings::get_setting('lp-main-landing-page-disable-turn-off-ab', "0");
        if ($ab_testing === "0") {
            add_rewrite_rule("$slug/([^/]*)/([0-9]+)/", "$slug/$1?lp-variation-id=$2", 'top');
            add_rewrite_rule("$slug/([^/]*)?", $this_path . "modules/module.redirect-ab-testing.php?permalink_name=$1 ", 'top');
            add_rewrite_rule("landing-page=([^/]*)?", $this_path . 'modules/module.redirect-ab-testing.php?permalink_name=$1', 'top');
        }

    }

    /**
     * Adds conditions to rewrite rules
     * @param $rules
     * @return string
     */
    public static function filter_rewrite_rules( $rules ) {
        if (stristr($rules, 'RewriteCond %{QUERY_STRING} !lp-variation-id')) {
            return $rules;
        }

        $rules_array = preg_split('/$\R?^/m', $rules);

        if (count($rules_array) < 3) {
            $rules_array = explode("\n", $rules);
            $rules_array = array_filter($rules_array);
        }

        /* print_r($rules_array);exit; */


        $slug = Landing_Pages_Settings::get_setting( 'lp-main-landing-page-permalink-prefix', 'go' );

        $i = 0;
        foreach ($rules_array as $key => $val) {

            if (stristr($val, "RewriteRule ^{$slug}/([^/]*)? ") || stristr($val, "RewriteRule ^{$slug}/([^/]*)/([0-9]+)/ ")) {
                $new_val = "RewriteCond %{QUERY_STRING} !lp-variation-id";
                $rules_array[$i] = $new_val;
                $i++;
                $rules_array[$i] = $val;
                $i++;
            } else {
                $rules_array[$i] = $val;
                $i++;
            }
        }

        $rules = implode("\r\n", $rules_array);


        return $rules;
    }


    /**
     * Show stats container on Landing Page lists page
     */
    public static function show_stats() {

        global $post;
        $permalink = get_permalink($post->ID);
        $variations = Landing_Pages_Variations::get_variations($post->ID);

        echo "<span class='show-stats button'> " . __('Show Variation Stats', 'inbound-pro') . "</span>";
        echo "<ul class='lp-varation-stat-ul'>";
        $cr_array = array();
        $i = 0;

        foreach ($variations as $key => $vid) {
            $letter = Landing_Pages_Variations::vid_to_letter($post->ID, $key); /* convert to letter */
            $impressions = Landing_Pages_Variations::get_impressions($post->ID, $vid);
            $conversions = Landing_Pages_Variations::get_conversions($post->ID, $vid);

            /* get variation status */
            $status = Landing_Pages_Variations::get_variation_status( $post->ID, $vid ); /* Current status */

            /* Get variation notes */
            $each_notes = Landing_Pages_Variations::get_variation_notes( $post->ID, $vid );

            if ($impressions) {
                $conversion_rate = $conversions / $impressions;
            } else {
                $conversion_rate = 0;
            }
            $conversion_rate = round($conversion_rate, 2) * 100;

            $cr_array[] = $conversion_rate;

            $data_letter = "data-letter=\"" . $letter . "\"";
            $edit_link = admin_url('post.php?post=' . $post->ID . '&lp-variation-id=' . $vid . '&action=edit');
            echo "<li rel='" . $status . "' data-postid='" . $post->ID . "' data-letter='" . $letter . "' data-lp='' class='lp-stat-row-" . $vid . " " . $post->ID . '-' . $conversion_rate . " status-" . $status . "'><a  class='lp-letter' title='click to edit this variation' href='" . $edit_link . "'>" . $letter . "</a><span class='lp-numbers'><span class='lp-visitors'><span class='visit-text'>" . __( 'Impressions' , 'inbound-pro' ) . "</span><span class='lp-impress-num'>" . $impressions . "</span></span> <span class='lp-conversions'> <span class='lp-conversion-txt'>" . __( 'Conversions' , 'inbound-pro' ) . "</span> <span class='lp-con-num'>" . $conversions . "</span> </span> </span><a ". $data_letter . " class='cr-number cr-empty-" . $conversion_rate . "' href='" . $edit_link . "'>" . $conversion_rate . "%</a></li>";
            $i++;
        }
        echo "</ul>";
        $winning_cr = max($cr_array); /* best conversion rate */
        if ($winning_cr != 0) {
            echo "<span class='variation-winner-is'>" . $post->ID . "-" . $winning_cr . "</span>";
        }
        /*echo "Total Visits: " . $impressions; */
        /*echo "Total Conversions: " . $conversions; */

    }


    /**
     * Show dropdown of landing page categories
     */
    public static function sort_by_category() {
        global $typenow;

        if ($typenow != "landing-page") {
            return;
        }


        $filters = get_object_taxonomies($typenow);

        foreach ($filters as $tax_slug) {

            $tax_obj = get_taxonomy($tax_slug);
            (isset($_GET[$tax_slug])) ? $current = $_GET[$tax_slug] : $current = 0;
            wp_dropdown_categories(
                array(
                    'show_option_all' => __('Show All ' . $tax_obj->label),
                    'taxonomy' => $tax_slug,
                    'name' => $tax_obj->name,
                    'orderby' => 'name',
                    'selected' => $current,
                    'hierarchical' => $tax_obj->hierarchical,
                    'show_count' => false,
                    'hide_empty' => true
                )
            );
        }
    }

    /**
     * Convert the category id to the taxonomy id during a query
     */
    public static function sort_by_category_prepare_query() {
        global $pagenow;
        $qv = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($qv['landing_page_category']) && is_numeric($qv['landing_page_category'])) {
            $term = get_term_by('id', $qv['landing_page_category'], 'landing_page_category');
            $qv['landing_page_category'] = $term->slug;
        }
    }

    /**
     * Add styling handlers to custom post states
     */
    public static function filter_custom_post_states($post_states) {
        foreach ($post_states as &$state) {
            $state = '<span class="' . strtolower($state) . ' states">' . str_replace(' ', '-', $state) . '</span>';
        }
        return $post_states;
    }

    /**
     * Loads preview iframe. Currently disabled. Plans to update @DavidWells
     */
    public static function load_preview_iframe() {

        $variation_id = Landing_Pages_Variations::get_current_variation_id();
        $landing_page_id = $_GET['post_id'];

        $variations = Landing_Pages_Variations::get_variations( $landing_page_id );
        ?>
        <link rel="stylesheet" href="<?php echo LANDINGPAGES_URLPATH . 'assets/css/customizer-ab-testing.css';?>"/>
        <style type="text/css">

            #variation-list {
                position: absolute;
                top: 0px;
                left: 0px;
                padding-left: 5px;
            }

            #variation-list h3 {
                text-decoration: none;
                border-bottom: none;
            }

            #variation-list div {
                display: inline-block;
            }

            #current_variation_id, #current-post-id {
                display: none !important;
            }

        </style>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var current_page = jQuery("#current_variation_id").text();
                /* reload the iframe preview page (for option toggles) */
                jQuery('.variation-lp').on('click', function (event) {
                    variation_is = jQuery(this).attr("id");
                    var original_url = jQuery(parent.document).find("#TB_iframeContent").attr("src");
                    var current_id = jQuery("#current-post-id").text();
                    someURL = original_url;

                    splitURL = someURL.split('?');
                    someURL = splitURL[0];
                    new_url = someURL + "?lp-variation-id=" + variation_is + "&iframe_window=on&post_id=" + current_id;
                    jQuery(parent.document).find("#TB_iframeContent").attr("src", new_url);
                });
            });
        </script>
        <?php
    }

    /**
     * Load JS to disable stats from working for preview windows
     */
    public static function stop_stat_tracking() {
        show_admin_bar(false);
        wp_enqueue_script('stop-inbound-stats-js', LANDINGPAGES_URLPATH . 'assets/js/stop_page_stats.js' , array('inbound-analytics') , null );
        wp_enqueue_style('inbound-preview-window-css', LANDINGPAGES_URLPATH . 'assets/css/iframe-preview.css' , array() , null);
    }

    /**
     * Load misc wp_head
     */
    public static function wp_head() {
        global $post;

        if (isset($post) && $post->post_type !=='landing-page') {
            return;
        }
        /* if is tiny iframe preview window force these styles */
        if (isset($_GET['dont_save'])) { ?>
            <style type="text/css">
                :root:root:root #wpadminbar {
                    display:none !important;
                }
                :root:root:root {
                    margin-top: 0px !important;
                    min-height: 714px !important;
                }
            </style>
        <?php }

        if (isset($_GET['lp-variation-id']) && !isset($_GET['inbound-customizer']) && !isset($_GET['iframe_window']) && !isset($_GET['live-preview-area'])) {
            ?>

            <?php
            if(!defined('Inbound_Now_Disable_URL_CLEAN')) {
            ?>
                <script type="text/javascript">
                    /* Then strip params if pushstate exists */
                    if (typeof window.history.pushState == 'function') {
                        var cleanparams=window.location.href.split("?");
                        var clean_url= landing_pages_remove_variation_param();
                        history.replaceState({},"landing page",clean_url);
                    }
                    function landing_pages_remove_variation_param() {
                        var urlparts= window.location.href.split('?');
                        if (urlparts.length>=2) {

                            var prefix= encodeURIComponent('lp-variation-id')+'=';
                            var pars= urlparts[1].split(/[&;]/g);

                            /* reverse iteration as may be destructive */
                            for (var i= pars.length; i-- > 0;) {
                                //idiom for string.startsWith
                                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                                    pars.splice(i, 1);
                                }
                            }

                            url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
                            return url;
                        } else {
                            return url;
                        }
                    }
                </script>
            <?php
            }
        }

    }
}

/* Load Post Type Pre Init */
new Landing_Pages_Post_Type();
