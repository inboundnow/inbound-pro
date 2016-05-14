<?php

if ( !class_exists('Inbound_GA_Post_Types') ) {

    class Inbound_GA_Post_Types {

        static $stats;
        static $range;
        static $statistics;

        function __construct() {
            self::$range = 90;
            if (isset($_GET['inbound_ga_reset_page_stats'])) {
                delete_transient( 'inbound_ga_post_list_cache' );
            }
            self::$statistics = get_transient( 'inbound_ga_post_list_cache' );

            self::load_hooks();
        }

        private function load_hooks() {
            /* load settings */
            $ga_settings = get_option('inbound_ga' , false );

            if (!isset($ga_settings['linked_profile']) ||  !$ga_settings['linked_profile']) {
                return;
            }

            /* Register Columns */
            add_filter( 'manage_posts_columns' , array( __CLASS__ , 'register_columns') );
            add_filter( 'manage_page_columns' , array( __CLASS__ , 'register_columns') );

            /* Prepare Column Data */
            add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );
            add_action( "manage_pages_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );

            /* setup column sorting */
            add_filter("manage_edit-post_sortable_columns", array( __CLASS__ , 'define_sortable_columns' ));
            add_action( 'posts_clauses', array( __CLASS__ , 'process_column_sorting' ) , 1 , 2 );

            /* Cache statistics data */
            add_filter( 'admin_footer' , array( __CLASS__ , 'load_footer_js_css' ) );

            /* Adds listener for loading impression data */
            add_action( 'wp_ajax_inbound_load_ga_stats' , array( __CLASS__ , 'get_post_statics' ) );

        }

        /**
         *  	Register Columns
         */
        public static function register_columns( $cols ) {

            self::$stats = get_transient( 'inbound-ga-stats-cache');

            if (!is_array(self::$stats)) {
                self::$stats = array();
            }

            $cols['inbound_ga_stats_impressions'] = __( 'Impressions' , 'inbound-pro' );
            $cols['inbound_ga_stats_visitors'] = __( 'Visitors' , 'inbound-pro' );
            $cols['inbound_ga_stats_actions'] = __( 'Actions' , 'inbound-pro' );

            return $cols;

        }


        /**
         *  	Prepare Column Data
         */
        public static function prepare_column_data( $column , $post_id ) {
            global $post, $Inbound_Mailer_Variations;

            switch ($column) {
                case "inbound_ga_stats_impressions":
                    if (isset(self::$statistics[$post->ID])) {
                        echo self::$statistics[$post->ID]['impressions']['current'][self::$range];
                    } else if ($post->post_status=='publish'){
                        ?>
                        <div class="td-col-impressions" data-post-id="<?php echo $post->ID; ?>" data-post-status="<?php echo $post->post_status; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">

                        </div>
                        <?php
                    }else {
                        echo '-';
                    }
                    break;
                case "inbound_ga_stats_visitors":
                    if (isset(self::$statistics[$post->ID])) {
                        echo self::$statistics[$post->ID]['visitors']['current'][self::$range];
                    } else if ($post->post_status=='publish'){
                        ?>
                        <div class="td-col-visitors" data-post-id="<?php echo $post->ID; ?>" data-post-status="<?php echo $post->post_status; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">
                        </div>
                        <?php
                    } else {
                        echo '-';
                    }
                    break;
                case "inbound_ga_stats_actions":
                    if (isset(self::$statistics[$post->ID])) {
                        echo self::$statistics[$post->ID]['actions']['current'][self::$range];
                    } else if ($post->post_status=='publish'){
                        ?>
                        <div class="td-col-actions" data-post-id="<?php echo $post->ID; ?>" data-post-status="<?php echo $post->post_status; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">
                        </div>
                        <?php
                    } else {
                        echo '-';
                    }
                    break;

            }
        }



        public static function process_column_sorting(  $pieces, $query ) {

            global $wpdb, $table_prefix;

            if (!function_exists('get_current_screen')) {
                return;
            }

            $screen = get_current_screen();

            $whitelist = array('post','page');

            if(!isset($screen) || !in_array($screen->post_type , $whitelist )) {
                return $pieces;
            }

            if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {


                $wordpress_date_time =  date_i18n('Y-m-d G:i:s');


                $start_date = date( 'Y-m-d G:i:s' , strtotime("-".self::$range." days" , strtotime($wordpress_date_time )));
                $end_date = $wordpress_date_time;


                $order = strtoupper( $query->get( 'order' ) );

                if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
                    $order = 'ASC';
                }

                switch( $orderby ) {

                    case 'inbound_ga_stats_actions':

                        $pieces[ 'join' ] .= " RIGHT JOIN {$table_prefix}inbound_events ee ON ee.page_id = {$wpdb->posts}.ID  AND ee.datetime >= '".$start_date."' AND  ee.datetime <= '".$end_date."'";

                        $pieces[ 'groupby' ] = " {$wpdb->posts}.ID";

                        $pieces[ 'orderby' ] = "COUNT(ee.page_id) $order ";

                        break;
                }
            } else {
                $pieces[ 'orderby' ] = " post_modified  DESC , " . $pieces[ 'orderby' ];
            }

            return $pieces;
        }

        public static function load_email_stats( $post_id ) {

            if ( isset(self::$stats[$post_id]) ) {
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

            //$columns['inbound_ga_stats_impressions'] = 'inbound_ga_stats_impressions';
            //$columns['inbound_ga_stats_visitors'] = 'inbound_ga_stats_visitors';
            $columns['inbound_ga_stats_actions'] = 'inbound_ga_stats_actions';

            return $columns;
        }

        public static function load_footer_js_css() {
            $screen = get_current_screen();

            $whitelist = array('post','page');

            if(!isset($screen) || !in_array($screen->post_type , $whitelist )) {
                return;
            }

            $transient = get_transient( 'inbound_ga_post_list_cache' );
            $js_array = json_encode($transient);


            ?>
            <script type="text/javascript">
                <?php
                echo "var cache = JSON.parse('". $js_array . "');\n";
                ?>

                function inbound_ga_listings_lookup( cache, post_ids, i , callback , response ) {
                    var end = false;
                    var old_post_id = post_ids[i - 1];
                    var post_id = post_ids[i];

                    if (!post_ids[i] && i != -1){
                       end = true;
                    }


                    if (typeof response == 'object' && response && typeof response['impressions'] != 'undefined'  ) {
                        jQuery('.td-col-impressions[data-post-id="' + old_post_id + '"]').text(response['impressions']['current']['90']);
                        jQuery('.td-col-visitors[data-post-id="' + old_post_id + '"]').text(response['visitors']['current']['90']);
                        jQuery('.td-col-actions[data-post-id="' + old_post_id + '"]').text(response['actions']['current']['90']);
                    } else if (typeof response == 'object' && old_post_id != -1 ) {
                        jQuery('.td-col-impressions[data-post-id="' + old_post_id + '"]').text('-');
                        jQuery('.td-col-visitors[data-post-id="' + old_post_id + '"]').text('-');
                        jQuery('.td-col-actions[data-post-id="' + old_post_id + '"]').text('-');

                    }

                    i++;

                    if (end){
                        return true;
                    }

                    if (typeof cache[post_id] != 'undefined') {
                        jQuery( '.td-col-impressions[data-post-id="' + post_id + '"]').text( cache[post_id].impressions.current['<?php echo self::$range; ?>'] );
                        jQuery( '.td-col-visitors[data-post-id="' + post_id + '"]').text(cache[post_id].visitors.current['<?php echo self::$range; ?>']);
                        jQuery( '.td-col-actions[data-post-id="' + post_id + '"]').text(cache[post_id].actions.current['<?php echo self::$range; ?>']);
                    } else {
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: 'inbound_load_ga_stats',
                                post_id: post_id,
                                fast_ajax: true,
                                load_plugins: ["_inbound-now/inbound-pro.php", "inbound-google-analytics/inbound-google-analytics.php"]
                            },
                            dataType: 'json',
                            async: true,
                            timeout: 15000,
                            success: function (response) {
                                callback(cache, post_ids, i, callback , response);
                            },
                            error: function (request, status, err) {
                                var response = {};
                                callback(cache, post_ids, i, callback , response);
                            }
                        });
                    }
                }

                jQuery(document).ready( function($) {
                    /* Let's use ajax to discover and set the sends/opens/conversions */

                    var post_ids = [];
                    var i = 0;
                    jQuery( jQuery('.td-col-impressions').get() ).each( function( $ ) {
                        var post_id = jQuery(this).attr('data-post-id');
                        var post_status = jQuery(this).attr('data-post-status');
                        if (post_status=='publish') {
                            post_ids[i] = post_id;
                        }
                        i++;
                    });

                    inbound_ga_listings_lookup( cache, post_ids, 0 , inbound_ga_listings_lookup , null );
            });
            </script>
            <?php
        }

        /**
         *  Gets JSON object containing email send statistics return cached data if cached
         */
        public static function get_post_statics() {
            global $post;

            $transient = get_transient( 'inbound_ga_post_list_cache' );

            if (isset($transient[$_REQUEST['post_id']]) && $transient[$_REQUEST['post_id']] ) {
                echo json_encode($transient[$_REQUEST['post_id']]);
                header('HTTP/1.1 200 OK');
                exit;
            }

            $post = get_post($_REQUEST['post_id']);
            $transient[$_REQUEST['post_id']] = Analytics_Template_Content_Quick_View::load_data();

            echo json_encode($transient[$_REQUEST['post_id']]);

            set_transient( 'inbound_ga_post_list_cache' , $transient , 60 * 30 );

            header('HTTP/1.1 200 OK');
            exit;
        }

        public static function load_stats() {

            self::$statistics['impressions']['current'][self::$range] = Analytics_Template_Content_Quick_View::get_impressions( array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            self::$statistics['impressions']['past'][self::$range] = Analytics_Template_Content_Quick_View::get_impressions( array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine rate */
            self::$statistics['impressions']['difference'][self::$range] = Analytics_Template_Content_Quick_View::get_percentage_change( self::$statistics['impressions']['current'][self::$range] , self::$statistics['impressions']['past'][self::$range] );

            /* get visitor count in current time period */
            self::$statistics['visitors']['current'][self::$range] = Analytics_Template_Content_Quick_View::get_visitors( array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            /* get visitor count in past time period */
            self::$statistics['visitors']['past'][self::$range] = Analytics_Template_Content_Quick_View::get_visitors( array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine rate */
            self::$statistics['visitors']['difference'][self::$range] = Analytics_Template_Content_Quick_View::get_percentage_change( self::$statistics['visitors']['current'][self::$range] , self::$statistics['visitors']['past'][self::$range] );

            /* get action count in current time period */
            self::$statistics['actions']['current'][self::$range] = Analytics_Template_Content_Quick_View::get_actions( array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            /* get action count in past time period */
            self::$statistics['actions']['past'][self::$range] = Analytics_Template_Content_Quick_View::get_actions( array(
                'per_days' => self::$range,
                'skip' => 1
            ));


            /* determine difference rate */
            self::$statistics['actions']['difference'][self::$range] = self::get_percentage_change( self::$statistics['actions']['current'][self::$range] , self::$statistics['actions']['past'][self::$range] );


            return self::$statistics;
        }
    }

    /* Load Post Type Pre Init */
    new Inbound_GA_Post_Types();
}
