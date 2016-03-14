<?php

if ( !class_exists('Inbound_GA_Post_Types') ) {

    class Inbound_GA_Post_Types {

        static $stats;

        function __construct() {
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
            add_filter( 'admin_footer' , array( __CLASS__ , 'cache_data' ) );


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
                    ?>
                    <div class="td-col-sends" data-post-id="<?php echo $post->ID; ?>" ><img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;"></div>
                    <?php
                    break;
                case "inbound_ga_stats_visitors":
                    ?>
                    <div class="td-col-sends" data-post-id="<?php echo $post->ID; ?>" ><img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;"></div>
                    <?php
                    break;
                case "inbound_ga_stats_actions":
                    ?>
                    <div class="td-col-sends" data-post-id="<?php echo $post->ID; ?>" ><img src="<?php echo INBOUND_GA_URLPATH; ?>assets/img/ajax_progress.gif" class="col-ajax-spinner" style="margin-top:3px;"></div>
                    <?php
                    break;

            }
        }

        public static function load_email_stats( $email_id ) {

            if ( isset(self::$stats[$email_id]) ) {
                return self::$stats[$email_id];
            }

            self::$stats[$email_id] = Inbound_Email_Stats::get_email_timeseries_stats();
            return self::$stats[$email_id];
        }

        /**
         * Defines sortable columns
         * @param $columns
         * @return mixed
         */
        public static function define_sortable_columns($columns) {

            $columns['inbound_ga_stats_impressions'] = 'inbound_ga_stats_impressions';
            $columns['inbound_ga_stats_visitors'] = 'inbound_ga_stats_visitors';
            $columns['inbound_ga_stats_actions'] = 'inbound_ga_stats_visitors';

            return $columns;
        }

        public static function cache_data() {
            if (!get_transient('inbound-email-stats-cache')) {

            }
        }
    }

    /* Load Post Type Pre Init */
    new Inbound_GA_Post_Types();
}