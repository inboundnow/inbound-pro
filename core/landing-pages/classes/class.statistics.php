<?php

class Landing_Pages_Statistics {
    static $variations;

    /**
     * Gets a comprehensive statistics array given a landing page id
     */
     public static function read_statistics( $landing_page_id ) {
        self::$variations = self::get_variations( $landing_page_id );

        $statistics['variations'] = self::$variations;
        $statistics['impressions'] = self::get_impressions( $landing_page_id );
        $statistics['conversions'] = self::get_conversions( $landing_page_id );

        return $statistics;
     }


    /**
     * Returns array of variation data given a landing page id
     *
     * @param INT $landing_page_id id of landing page
     * @param INT $vid id of specific variation
     *
     * @returns ARRAY of variation data
     */
    public static function get_variations( $landing_page_id ) {
        if (!$landing_page_id) {
            return array();
        }

        $variations = (self::$variations) ? self::$variations :  get_post_meta(  $landing_page_id , 'lp-ab-variations' , true );

        if (!is_array($variations)) {
            $variations = explode( ',' , $variations );
        }

        $variations = array_filter( $variations , 'is_numeric' );

        return ( $variations ? $variations : array() );
    }


    /**
     * Gets impressions count for landing page variations
     * @param $landing_page_id
     * @return ARRAY
     */

    public static function get_impressions( $landing_page_id ) {
        $variations = (self::$variations) ? self::$variations : self::get_variations( $landing_page_id );

        $impressions = array();
        foreach ($variations as $vid) {
            $impressions[$vid] = self::get_impressions_count( $landing_page_id  ,  $vid );
        }

        return $impressions;
    }


    /**
     * Gets conversion counts for landing page variations
     * @param $landing_page_id
     * @return ARRAY
     */

    public static function get_conversions( $landing_page_id ) {
        $variations = (self::$variations) ? self::$variations : self::get_variations( $landing_page_id );

        $impressions = array();
        foreach ($variations as $vid) {
            $impressions[$vid] = self::get_impressions_count( $landing_page_id  ,  $vid );
        }

        return $impressions;
    }


    /**
     * Returns impression for given cta and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $vid id of variation belonging to call to action
     *
     * @return INT impression count
     */
    public static function get_impressions_count( $landing_page_id , $vid ) {

        $impressions = get_post_meta( $landing_page_id , 'lp-ab-variation-impressions-'.$vid , true);

        if (!is_numeric($impressions)) {
            $impressions = 0;
        }

        return $impressions;
    }
    /**
     * Returns conversion count for given landing page id and variation id
     *
     * @param INT $landing_page_id id of call to action
     * @param INT $vid id of variation belonging to call to action
     *
     * @return INT impression count
     */
    public static function get_conversions_count( $landing_page_id , $vid = 0 ) {


        $conversions = get_post_meta( $landing_page_id , 'lp-ab-variation-conversions-'.$vid , true);

        if (!is_numeric($conversions)) {
            $conversions = 0;
        }

        return $conversions;
    }


    /**
     * Set impression count
     */
    public static function set_impression_count( $landing_page_id , $vid , $count ) {
        update_post_meta( $landing_page_id, 'lp-ab-variation-impressions-'.$vid, $count );
    }

    /**
     * Set conversion count
     */
    public static function set_conversion_count( $landing_page_id , $vid , $count ) {
        update_post_meta( $landing_page_id, 'lp-ab-variation-conversions-'.$vid, $count );
    }

}

