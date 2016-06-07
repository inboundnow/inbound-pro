<?php

/**
*  This class adds a impressions/conversions counter box to all post types that are not a landing page
*/

if (!class_exists('Landing_Pages_Stats')) {

	/**
	*  Adds impression and conversion tracking statistics to all pieces of content
	*/
	class Landing_Pages_Stats {

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

            /* records page impression */
            add_action( 'lp_record_impression' , array( __CLASS__ , 'record_impression' ) , 10, 3);

            /* record landing page conversion */
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
            if (strstr( $_SERVER['HTTP_REFERER'] , admin_url() )) {
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
                $impressions = Landing_Pages_Stats::get_impressions_count( $post_id );
                $impressions++;
                Landing_Pages_Stats::set_impressions_count( $post_id, $impressions );
            }
        }

        /**
         * Listens for new lead creation events and if the lead converted on a landing page then capture the conversion
         * @param $data
         */
        public static function record_conversion($data) {

            if (!isset( $data['page_id'] ) ) {
                return $data;
            }

            $post = get_post( $data['page_id'] );
            if ($post) {
                $data['post_type'] = $post->post_type;
            }

            /* this filter is used by Inbound Pro to check if visitor's ip is on a not track list */
            $do_not_track = apply_filters('inbound_analytics_stop_track' , false );

            if ( $do_not_track ) {
                return $data;
            }

            /* increment conversions for landing pages */
            if( isset($data['post_type']) && $data['post_type'] === 'landing-page' ) {
                $conversions = Landing_Pages_Variations::get_conversions( $data['page_id'] , $data['variation'] );
                $conversions++;
                Landing_Pages_Variations::set_conversions_count( $data['page_id'] , $data['variation'] , $conversions );

            }
            /* increment conversions for non landing pages */
            else  {
                $conversions = Landing_Pages_Stats::get_conversions_count( $data['page_id'] );
				$conversions++;
                Landing_Pages_Stats::set_conversions_count( $data['page_id'] , $conversions );
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

            $impressions = Landing_Pages_Stats::get_impressions_count( $post_id );
            $conversions = Landing_Pages_Stats::get_conversions_count( $post_id );

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


	new Landing_Pages_Stats;
}