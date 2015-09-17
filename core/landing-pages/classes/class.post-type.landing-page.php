<?php

if ( !class_exists('Landing_Pages_Post_Type') ) {

    class Landing_Pages_Post_Type {

        function __construct() {
            self::load_hooks();
        }

        /**
         * setup hooks and filters
         */
        private function load_hooks() {
            add_action('init', array( __CLASS__ , 'register_post_type' ) );
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

            /* add quick actions to lists mode */
            add_filter('post_row_actions', array(__CLASS__, 'add_quick_actions' ) , 10, 2);

            /* enqueue scripts for landing page listings */
            add_action( 'admin_enqueue_scripts' , array(__CLASS__, 'enqueue_admin_scripts' ) );

            /* enqueue scripts for landing page listings */
            if (isset($_GET['dont_save'])
                || isset($_GET['iframe_window'])
                || isset($_GET['inbound-preview']) ) {
                add_action('wp_enqueue_scripts', array(__CLASS__, 'stop_stat_tracking'));
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

            $slug = get_option( 'lp-main-landing-page-permalink-prefix', 'go' );
            $labels = array(
                'name' => _x('Landing Pages', 'post type general name' , 'landing-pages' ),
                'singular_name' => _x('Landing Page', 'post type singular name' , 'landing-pages' ),
                'add_new' => _x('Add New', 'Landing Page' , 'landing-pages' ),
                'add_new_item' => __('Add New Landing Page' , 'landing-pages' ),
                'edit_item' => __('Edit Landing Page' , 'landing-pages' ),
                'new_item' => __('New Landing Page' , 'landing-pages' ),
                'view_item' => __('View Landing Page' , 'landing-pages' ),
                'search_items' => __('Search Landing Page' , 'landing-pages' ),
                'not_found' =>  __('Nothing found' , 'landing-pages' ),
                'not_found_in_trash' => __('Nothing found in Trash' , 'landing-pages' ),
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
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => 32,
                'supports' => array('title','custom-fields','editor','thumbnail', 'excerpt')
            );

            register_post_type( 'landing-page' , $args );
        }

        /**
         * Register landing page taxonomies
         */
        public static function register_taxonomies() {

            $args = array(
                'hierarchical' => true,
                'label' => __("Categories", 'landing-pages'),
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
                "thumbnail-lander" => __("Preview", 'landing-pages'),
                "title" => __("Landing Page Title", 'landing-pages'),
                "stats" => __("Variation Testing Stats", 'landing-pages'),
                "impressions" => __("Total<br>Visits", 'landing-pages'),
                "actions" => __("Total<br>Conversions", 'landing-pages'),
                "cr" => __("Total<br>Conversion Rate", 'landing-pages')
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

            /* listing page only */
            if ($screen->id == 'edit-landing-page' ) {
                /* load stat clear handlers */
                wp_enqueue_script( 'lp-admin-clear-stats-ajax-request', LANDINGPAGES_URLPATH . 'assets/js/ajax.clearstats.js', array( 'jquery' ) );
                wp_localize_script( 'lp-admin-clear-stats-ajax-request', 'ajaxadmin', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'lp_clear_nonce' => wp_create_nonce('lp-clear-nonce') ) );

                /* load qtip for popup data */
                wp_enqueue_script('jquery-qtip', LANDINGPAGES_URLPATH . 'assets/libraries/jquery-qtip/jquery.qtip.min.js');
                wp_enqueue_script('load-qtip', LANDINGPAGES_URLPATH . 'assets/libraries/jquery-qtip/load.qtip.js', array('jquery-qtip'));
                wp_enqueue_style('qtip-css', LANDINGPAGES_URLPATH . 'assets/css/jquery.qtip.min.css'); /*Tool tip css */

                wp_enqueue_script('landing-page-list', LANDINGPAGES_URLPATH . 'assets/js/admin/admin.landing-page-list.js');
                wp_enqueue_style('landing-page-list-css', LANDINGPAGES_URLPATH.'assets/css/admin/landing-page-list.css');
                wp_enqueue_script('jqueryui');

            }



            /* load css when landing page iframe preview is being loaded from within wp-admin */
            if (isset($_GET['iframe_window'])) {
                wp_enqueue_style('lp_ab_testing_customizer_css', LANDINGPAGES_URLPATH . 'assets/css/frontend/customizer-preview.css');
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

            wp_enqueue_style('inbound-wordpress-base', LANDINGPAGES_URLPATH . 'assets/css/frontend/global-landing-page-style.css');
            wp_enqueue_style('inbound-shortcodes', INBOUND_FORMS.'css/frontend-render.css');


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

                    echo "<a title='" . __('Click to Preview this variation', 'landing-pages') . "' class='thickbox' href='" . $permalink . "?lp-variation-id=0&iframe_window=on&post_id=" . $post->ID . "&TB_iframe=true&width=640&height=703' target='_blank'><img src='" . $thumbnail . "' style='width:130px;height:110px;' title='Click to Preview'></a>";
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
            $this_path = LANDINGPAGES_PATH;
            $this_path = explode('wp-content', $this_path);
            $this_path = "wp-content" . $this_path[1];

            $slug = get_option('lp-main-landing-page-permalink-prefix', 'go');
            /*echo $slug;exit; */
            $ab_testing = get_option('lp-main-landing-page-disable-turn-off-ab', "0");
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

            $this_path = LANDINGPAGES_PATH;
            $this_path = explode('wp-content', $this_path);
            $this_path = "wp-content" . $this_path[1];
            $slug = get_option('lp-main-landing-page-permalink-prefix', 'go');

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

            if ($variations) if ($variations) {

                echo "<span class='show-stats button'> " . __('Show Variation Stats', 'landing-pages') . "</span>";
                echo "<ul class='lp-varation-stat-ul'>";
                $first_status = get_post_meta($post->ID, 'lp_ab_variation_status', true); /* Current status */
                $first_notes = get_post_meta($post->ID, 'lp-variation-notes', true);
                $cr_array = array();
                $i = 0;
                $impressions = 0;
                $conversions = 0;
                foreach ($variations as $key => $vid) {
                    $letter = Landing_Pages_Variations::vid_to_letter($post->ID, $vid); /* convert to letter */
                    $each_impression = get_post_meta($post->ID, 'lp-ab-variation-impressions-' . $vid, true); /* get impressions */
                    $v_status = get_post_meta($post->ID, 'lp_ab_variation_status-' . $vid, true); /* Current status */
                    if ($i === 0) {
                        $v_status = $first_status;
                    } /* get status of first */
                    (($v_status === "")) ? $v_status = "1" : $v_status = $v_status; /* Get on/off status */
                    $each_notes = get_post_meta($post->ID, 'lp-variation-notes-' . $vid, true); /* Get Notes */
                    if ($i === 0) {
                        $each_notes = $first_notes;
                    } /* Get first notes */
                    $each_conversion = get_post_meta($post->ID, 'lp-ab-variation-conversions-' . $vid, true);
                    (($each_conversion === "")) ? $final_conversion = 0 : $final_conversion = $each_conversion;
                    $impressions += get_post_meta($post->ID, 'lp-ab-variation-impressions-' . $vid, true);
                    $conversions += get_post_meta($post->ID, 'lp-ab-variation-conversions-' . $vid, true);
                    if ($each_impression != 0) {
                        $conversion_rate = $final_conversion / $each_impression;
                    } else {
                        $conversion_rate = 0;
                    }
                    $conversion_rate = round($conversion_rate, 2) * 100;
                    $cr_array[] = $conversion_rate;
                    if ($v_status === "0") {
                        $final_status = __("(Paused)", 'landing-pages');
                    } else {
                        $final_status = "";
                    }
                    /*if ($cr_array[$i] > $largest) {
                    $largest = $cr_array[$i];
                     }
                    (($largest === $conversion_rate)) ? $winner_class = 'lp-current-winner' : $winner_class = ""; */
                    (($final_conversion === "1")) ? $c_text = __('conversion', 'landing-pages') : $c_text = __("conversions", 'landing-pages');
                    (($each_impression === "1")) ? $i_text = __('visit', 'landing-pages') : $i_text = __("visits", 'landing-pages');
                    (($each_notes === "")) ? $each_notes = __('No notes', 'landing-pages') : $each_notes = $each_notes;
                    $data_letter = "data-letter=\"" . $letter . "\"";
                    $edit_link = admin_url('post.php?post=' . $post->ID . '&lp-variation-id=' . $vid . '&action=edit');
                    $popup = "data-notes=\"<span class='lp-pop-description'>" . $each_notes . "</span><span class='lp-pop-controls'><span class='lp-pop-edit button-primary'><a href='" . $edit_link . "'>Edit This variation</a></span><span class='lp-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='" . $permalink . "?lp-variation-id=" . $vid . "&iframe_window=on&post_id=" . $post->ID . "&TB_iframe=true&width=640&height=703' target='_blank'>Preview This variation</a></span><span class='lp-bottom-controls'><span class='lp-delete-var-stats' data-letter='" . $letter . "' data-vid='" . $vid . "' rel='" . $post->ID . "'>Clear These Stats</span></span></span>\"";
                    echo "<li rel='" . $final_status . "' data-postid='" . $post->ID . "' data-letter='" . $letter . "' data-lp='' class='lp-stat-row-" . $vid . " " . $post->ID . '-' . $conversion_rate . " status-" . $v_status . "'><a " . $popup . " " . $data_letter . " class='lp-letter' title='click to edit this variation' href='" . $edit_link . "'>" . $letter . "</a><span class='lp-numbers'> <span class='lp-impress-num'>" . $each_impression . "</span><span class='visit-text'>" . $i_text . " with</span><span class='lp-con-num'>" . $final_conversion . "</span> " . $c_text . "</span><a " . $popup . " " . $data_letter . " class='cr-number cr-empty-" . $conversion_rate . "' href='" . $edit_link . "'>" . $conversion_rate . "%</a></li>";
                    $i++;
                }
                echo "</ul>";
                $winning_cr = max($cr_array); /* best conversion rate */
                if ($winning_cr != 0) {
                    echo "<span class='variation-winner-is'>" . $post->ID . "-" . $winning_cr . "</span>";
                }
                /*echo "Total Visits: " . $impressions; */
                /*echo "Total Conversions: " . $conversions; */
            } else {
                $notes = get_post_meta($post->ID, 'lp-variation-notes', true); /* Get Notes */
                $cr = self::show_aggregated_stats("cr");
                $edit_link = admin_url('post.php?post=' . $post->ID . '&lp-variation-id=0&action=edit');
                $start_test_link = admin_url('post.php?post=' . $post->ID . '&lp-variation-id=1&action=edit&new-variation=1&lp-message=go');
                (($notes === "")) ? $notes = __('No notes', 'landing-pages') : $notes = $notes;
                $popup = "data-notes=\"<span class='lp-pop-description'>" . $notes . "</span><span class='lp-pop-controls'><span class='lp-pop-edit button-primary'><a href='" . $edit_link . "'>Edit This variation</a></span><span class='lp-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='" . $permalink . "?lp-variation-id=0&iframe_window=on&post_id=" . $post->ID . "&TB_iframe=true&width=640&height=703' target='_blank'>" . __('Preview This variation', 'landing-pages') . "</a></span><span class='lp-bottom-controls'><span class='lp-delete-var-stats' data-letter='A' data-vid='0' rel='" . $post->ID . "'>" . __('Clear These Stats', 'landing-pages') . "</span></span></span>\"";
                echo "<ul class='lp-varation-stat-ul'><li rel='' data-postid='" . $post->ID . "' data-letter='A' data-lp=''><a " . $popup . " data-letter=\"A\" class='lp-letter' title='click to edit this variation' href='" . $edit_link . "'>A</a><span class='lp-numbers'> <span class='lp-impress-num'>" . self::show_aggregated_stats("impressions") . "</span><span class='visit-text'>visits with</span><span class='lp-con-num'>" . self::show_aggregated_stats("actions") . "</span> conversions</span><a class='cr-number cr-empty-" . $cr . "' href='" . $edit_link . "'>" . $cr . "%</a></li></ul>";
                echo "<div class='no-stats-yet'>" . __('No A/B Tests running for this landing page', 'landing-pages') . ". <a href='" . $start_test_link . "'>" . __('Start one', 'landing-pages') . "</a></div>";
            }
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
         * Adds quick action
         */
        public static function add_quick_actions($actions, $post) {
            if ($post->post_type != 'landing-page') {
                return $actions;
            }

            $actions['clear'] = '<a href="#clear-stats" id="lp_clear_' . $post->ID . '" class="clear_stats" title="' . esc_attr(__("Clear impression and conversion records", 'landing-pages')) . '" >' . __('Clear All Stats', 'landing-pages') . '</a><span class="hover-description">' . __('Hover over the letters to the right for more options', 'landing-pages') . '</span>';

            return $actions;
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
            wp_enqueue_script('stop-inbound-stats-js', LANDINGPAGES_URLPATH . 'assets/js/stop_page_stats.js');
            wp_enqueue_style('inbound-preview-window-css', LANDINGPAGES_URLPATH . 'assets/css/iframe-preview.css');
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
                do_action('landing_page_header_script');
                ?>

                <?php
                if(!defined('Inbound_Now_Disable_URL_CLEAN')) {
                ?>
                    <script type="text/javascript">
                        /* Then strip params if pushstate exists */
                        if (typeof window.history.pushState == 'function') {
                            var cleanparams=window.location.href.split("?");
                            var clean_url=cleanparams[0];history.replaceState({},"landing page",clean_url);
                        }
                    </script>
                <?php
                }
            }

        }
    }

    /* Load Post Type Pre Init */
    new Landing_Pages_Post_Type();
}