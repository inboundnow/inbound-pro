<?php

/**
 *  This class loads installed extensions
 */

class Inbound_Analytics {
    static $templates;

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

        /* disable legacy inbound statistics metaboxes */
        remove_action('init' , 'inbound_load_legacy_statistics' , 10 );

        /* Setup Automatic Updating & Licensing */
        add_action( 'admin_init', array( __CLASS__ , 'load_templates') , 99 );

        /* Load Google Charting API & Inbound Analytics Styling CSS*/
        add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'load_scripts') );

        /* Add sidebar metabox to content administration area */
        add_action( 'add_meta_boxes' , array( __CLASS__ , 'load_metaboxes' ) );

        /* Register Columns */
        add_filter( 'manage_posts_columns' , array( __CLASS__ , 'register_columns') );
        add_filter( 'manage_page_columns' , array( __CLASS__ , 'register_columns') );

        /* Prepare Column Data */
        add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );
        add_action( "manage_pages_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );

        /* setup column sorting */
        add_filter("manage_edit-post_sortable_columns", array( __CLASS__ , 'define_sortable_columns' ));
        add_action( 'posts_clauses', array( __CLASS__ , 'process_column_sorting' ) , 1 , 2 );


   }

    /**
     * Loads Google charting scripts
     */
    public static function load_scripts() {

        global $post;


        if (!isset($post) || strstr( $post->post_type , 'inbound-' ) ) {
            return;
        }

        $screen = get_current_screen();

        if (!isset($screen) || $screen->action == 'new' || $screen->action == 'add' || $screen->base =='edit') {
            return;
        }

        wp_register_script('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/js/bootstrap.min.js');
        wp_enqueue_script('bootstrap');

        /* BootStrap CSS */
        wp_register_style('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/css/bootstrap.css');
        wp_enqueue_style('bootstrap');

        /* disables modal links
        wp_register_script( 'ia-content-loader' , INBOUND_GA_URLPATH.'assets/js/content.loader.js');
        wp_enqueue_script( 'ia-content-loader' );
        */

        wp_register_style( 'inbound-analytics-css' , INBOUND_GA_URLPATH. 'assets/css/style.css');
        wp_enqueue_style( 'inbound-analytics-css' );

    }

    /**
     *	Imports analytic templates & sets data into static variable
     */
    public static function load_templates() {
        self::$templates = apply_filters( 'inbound_analytics_templates' , array() );
    }

    /**
     *	Adds sidebar metabox to all post types
     */
    public static function load_metaboxes() {
        $screen = get_current_screen();

        if (!isset($screen) || $screen->action == 'new' || $screen->action == 'add') {
            return;
        }

        /* Get post types to add metabox to */
        $post_types= get_post_types('','names');

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
        foreach ($post_types as $post_type ) {

            if (!in_array($post_type,$exclude))
            {
                add_meta_box( 'inbound-analytics', __( 'Inbound Analytics' , 'inbound-pro' ) , array( __CLASS__ , 'display_quick_view' ) , $post_type, 'side', 'high');
            }
        }
    }

    /**
     *	Displays Inbound Analytics sidebar (quick view)
     */
    public static function display_quick_view() {
        /* sets the default quick view template */
        $template_class_name = apply_filters('inbound-ananlytics/quick-view' , 'Inbound_Quick_View' );

        $template_class = new $template_class_name;
        $template_class->load_template( array() );

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
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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
     *  	Register Columns
     */
    public static function register_columns( $cols ) {


        $cols['inbound_impressions'] = __( 'Impressions' , 'inbound-pro' );
        $cols['inbound_visitors'] = __( 'Visitors' , 'inbound-pro' );
        $cols['inbound_actions'] = __( 'Actions' , 'inbound-pro' );

        return $cols;

    }


    /**
     *  	Prepare Column Data
     */
    public static function prepare_column_data( $column , $post_id ) {
        global $post, $Inbound_Mailer_Variations;

        switch ($column) {
            case "inbound_impressions":
                $results = Inbound_Events::get_page_views_by('page_id' , array('page_id'=>$post_id) );
                echo count($results);
                break;
            case "inbound_visitors":
                $results = Inbound_Events::get_visitors_count( $post_id );
                echo $results;
                break;
            case "inbound_actions":
                $results = Inbound_Events::get_page_actions( $post_id , 'any' );
                echo $results;
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

            $order = strtoupper( $query->get( 'order' ) );

            if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
                $order = 'ASC';
            }

            switch( $orderby ) {

                case 'inbound_impressions':

                    $pieces[ 'join' ] .= " RIGHT JOIN {$table_prefix}inbound_page_views ee ON ee.page_id = {$wpdb->posts}.ID ";

                    $pieces[ 'groupby' ] = " {$wpdb->posts}.ID";

                    $pieces[ 'orderby' ] = "COUNT(ee.page_id) $order ";

                    break;
                /*
                case 'inbound_visitors':

                    $pieces[ 'join' ] .= " RIGHT JOIN (select lead_id, page_id from {$table_prefix}inbound_page_views group by lead_id) ee ON ee.page_id = {$wpdb->posts}.ID  ";

                    $pieces[ 'groupby' ] = " {$wpdb->posts}.ID, ee.lead_id ";

                    $pieces[ 'orderby' ] = "COUNT(ee.lead_id) $order ";

                    error_log(print_r($pieces,true));
                    break;
                */

                case 'inbound_actions':

                    $pieces[ 'join' ] .= " RIGHT JOIN {$table_prefix}inbound_events ee ON ee.page_id = {$wpdb->posts}.ID ";

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

        $columns['inbound_impressions'] = 'inbound_impressions';
        /* $columns['inbound_visitors'] = 'inbound_visitors'; */
        $columns['inbound_actions'] = 'inbound_actions';

        return $columns;
    }

}

new Inbound_Analytics();