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


            /* Register Columns */
            add_filter( 'manage_posts_columns' , array( __CLASS__ , 'register_columns') );
            add_filter( 'manage_page_columns' , array( __CLASS__ , 'register_columns') );

            /* Prepare Column Data */
            add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );
            add_action( "manage_pages_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );

            /* setup column sorting */
            add_filter("manage_edit-post_sortable_columns", array( __CLASS__ , 'define_sortable_columns' ));

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
                    } else {
                        ?>
                        <div class="td-col-impressions" data-post-id="<?php echo $post->ID; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">

                        </div>
                        <?php
                    }
                    break;
                case "inbound_ga_stats_visitors":
                    if (isset(self::$statistics[$post->ID])) {
                        echo self::$statistics[$post->ID]['visitors']['current'][self::$range];
                    } else {
                        ?>
                        <div class="td-col-visitors" data-post-id="<?php echo $post->ID; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">
                        </div>
                        <?php
                    }
                    break;
                case "inbound_ga_stats_actions":
                    if (isset(self::$statistics[$post->ID])) {
                        echo self::$statistics[$post->ID]['actions']['current'][self::$range];
                    }
                    else {
                        ?>
                        <div class="td-col-actions" data-post-id="<?php echo $post->ID; ?>">
                            <img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;">
                        </div>
                        <?php
                    }
                    break;

            }
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
            $columns['inbound_ga_stats_actions'] = 'inbound_ga_stats_visitors';

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
                function
                jQuery(document).ready( function($) {
                    <?php
                    echo "var cache = JSON.parse('". $js_array . "');\n";
                    ?>

                    /* Let's use ajax to discover and set the sends/opens/conversions */
                    jQuery( jQuery('.td-col-impressions').get() ).each( function( $ ) {
                        var post_id = jQuery(this).attr('data-post-id');
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
                                    post_id: post_id
                                },
                                dataType: 'json',
                                async: true,
                                timeout: 10000,
                                success: function (response) {

                                    if (!Object.keys(response).length) {
                                        response['totals'] = [];
                                        response['totals']['impressions'] = 0;
                                        response['totals']['visitors'] = 0;
                                        response['totals']['actions'] = 0;
                                    }

                                    jQuery( '.td-col-impressions[data-post-id="' + post_id + '"]').text(response['impressions']['current']['90']);
                                    jQuery( '.td-col-visitors[data-post-id="' + post_id + '"]').text(response['visitors']['current']['90']);
                                    jQuery( '.td-col-actions[data-post-id="' + post_id + '"]').text(response['actions']['current']['90']);

                                },
                                error: function (request, status, err) {
                                    //alert(status);
                                }
                            });
                        }

                    });
            });
            </script>
            <?php
        }

        /**
         *  Gets JSON object containing email send statistics return cached data if cached
         */
        public static function get_post_statics() {
            global $post;
            $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
            $transient = get_transient( 'inbound_ga_post_list_cache' );

            if (isset($transient[$_REQUEST['post_id']])) {
                return $transient[$_REQUEST['post_id']];
            }

            $post = get_post($_REQUEST['post_id']);
            $transient[$_REQUEST['post_id']] = Analytics_Template_Content_Quick_View::load_data();

            echo json_encode($transient[$_REQUEST['post_id']]);

            set_transient( 'inbound_ga_post_list_cache' , $transient , 60 * 30 );

            header('HTTP/1.1 200 OK');
            exit;
        }

        public static function load_stats() {
            //error_log(self::$range);
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