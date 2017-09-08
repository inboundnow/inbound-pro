<?php

/**
*  Class contains methods related to split testing statistics
 * @package LandingPages
 * @subpackage Tracking
 */

class Landing_Pages_Split_Testing_Stats {

    /**
    *  Initiate class
    */
    public function __construct() {
        self::load_hooks();
    }

    /**
    *  load hooks and filters
    */
    public static function load_hooks() {

        /* adds page impression to split testing statistics */
        add_action( 'lp_record_impression' , array( __CLASS__ , 'record_impression' ) , 10, 3);

        /* adds landing page conversion to split testing statistics */
        add_action( 'inbound_track_link', array(__CLASS__, 'record_conversion'));

        /* adds landing page conversion to split testing statistics */
        add_filter( 'inboundnow_store_lead_pre_filter_data' , array( __CLASS__ , 'record_conversion' ) ,10,1);

    }

    /**
     * Records landing page & non landing page impression
     * @param $post_id
     * @param string $post_type
     * @param int $variation_id
     */
    public static function record_impression($post_id, $post_type , $variation_id = 0) {

        /* ignore mshots and previews from admin area */
        if (strstr( $_SERVER['HTTP_REFERER'] , 'edit.php?post_type=landing-page' )) {
            return;
        }
        if (strstr( $_SERVER['HTTP_REFERER'] , 'post.php' )) {
            return;
        }
        if (strstr( $_SERVER['HTTP_REFERER'] , 'edit.php' )) {
            return;
        }

        /* If Landing Page Post Type */
        if ( $post_type == 'landing-page' ) {
            $impressions = Landing_Pages_Variations::get_impressions( $post_id, $variation_id );
            $impressions++;
            Landing_Pages_Variations::set_impressions_count( $post_id, $variation_id, $impressions );
        }
        /* If Non Landing Page Post Type */
        else {
            $impressions = Landing_Pages_Split_Testing_Stats::get_impressions_count( $post_id );
            $impressions++;
            Landing_Pages_Split_Testing_Stats::set_impressions_count( $post_id, $impressions );
        }
    }

    /**
     * Records lead creation events and link click events as split testing conversion
     * @param $data
     */
    public static function record_conversion($data) {

        if (!isset( $data['page_id'] ) ) {
            return $data;
        }

        /* this filter is used by Inbound Pro to check if visitor's ip is on a not track list */
        $do_not_track = apply_filters('inbound_analytics_stop_track' , false );

        if ( $do_not_track ) {
            return $data;
        }

        $post_type = get_post_type($data['page_id']);
        $data['vid'] = (isset($data['vid'])) ? $data['vid'] : 0;
        $data['vid'] = (isset($data['variation'])) ? $data['variation'] : $data['vid'];

        if (current_filter() == 'inbound_track_link' && $post_type != 'landing-page' ) {
            return;
        } else if ($post_type === 'landing-page' ) {
            $data['vid'] = (isset($data['lp-variation-id'])) ? $data['lp-variation-id'] : $data['vid']; $data['vid'];
            $conversions = Landing_Pages_Variations::get_conversions( $data['page_id'] , $data['vid'] );
            $conversions++;
            Landing_Pages_Variations::set_conversions_count( $data['page_id'] , $data['vid'] , $conversions );
        }
        /* increment conversions for non landing pages */
        else  {
            $conversions = Landing_Pages_Split_Testing_Stats::get_conversions_count( $data['page_id'] );
            $conversions++;
            Landing_Pages_Split_Testing_Stats::set_conversions_count( $data['page_id'] , $conversions );
        }

        return $data;
    }

    /**
     *  Register Columns
     */
    public static function register_columns( $cols ) {

        $cols['inbound_impressions'] = __( 'Impressions' , 'inbound-email' );
        $cols['inbound_conversions'] = __( 'Conversions' , 'inbound-email' );
        $cols['inbound_conversion_rate'] = __( 'Conversion Rate' , 'inbound-email' );

        return $cols;
    }

    /**
     *  Prepare Column Data
     */
    public static function prepare_column_data( $column , $post_id ) {
        global $post;

        switch ($column) {
            case "inbound_impressions":
                echo self::get_impressions_count( $post->ID );
                break;
            case "inbound_conversions":
                echo self::get_conversions_count( $post->ID );
                break;
            case "inbound_conversion_rate":
                echo self::get_conversion_rate( $post->ID);
                break;
        }
    }


    /**
     * Returns impression count for non landing pages. See Landing_Pages_Variations class for retrieving landing page statistics
     *
     * @param INT $post_id id of call to action
     *
     * @return INT impression count
     */
    public static function get_impressions_count( $post_id ) {

        $impressions = get_post_meta( $post_id , '_inbound_impressions_count' , true);

        if (!is_numeric($impressions)) {
            $impressions = 0;
        }

        return $impressions;
    }

    /**
     * Returns conversion count for non landing page. See Landing_Pages_Variations class for retrieving landing page statistics
     *
     * @param INT $post_id id
     *
     * @return INT impression count
     */
    public static function get_conversions_count( $post_id ) {


        $conversions = get_post_meta( $post_id , '_inbound_conversions_count' , true);

        if (!is_numeric($conversions)) {
            $conversions = 0;
        }

        return $conversions;
    }

    /**
     * Returns conversion count for non landing page.  See Landing_Pages_Variations class for retrieving landing page statistics
     *
     * @param INT $post_id id
     *
     * @return INT
     */
    public static function get_conversion_rate( $post_id ) {

        $impressions = Landing_Pages_Split_Testing_Stats::get_impressions_count( $post_id );
        $conversions = Landing_Pages_Split_Testing_Stats::get_conversions_count( $post_id );

        if ($impressions > 0) {
            $conversion_rate = $conversions / $impressions;
            $conversion_rate_number = $conversion_rate * 100;
            $conversion_rate_number = round($conversion_rate_number, 2);
            $conversion_rate = $conversion_rate_number;
        } else {
            $conversion_rate = 0;
        }

        return $conversion_rate;
    }


    /**
     * Set impression count
     */
    public static function set_impressions_count( $post_id , $count ) {
        update_post_meta( $post_id, '_inbound_impressions_count', $count );
    }

    /**
     * Set conversion count
     */
    public static function set_conversions_count( $post_id , $count ) {
        update_post_meta( $post_id, '_inbound_conversions_count', $count );
    }

}


new Landing_Pages_Split_Testing_Stats;
