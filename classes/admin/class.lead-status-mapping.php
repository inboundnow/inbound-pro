<?php

/**
 * Class for extending lead statuses into a user programable state
 * @package     InboundPro
 * @subpackage  LeadStatuses
 */
class Inbound_Lead_Statuses {

    static $statuses_map;

    /**
     *  initialize class
     */
    public function __construct() {
        self::get_lead_statuses();
        self::load_hooks();
    }

    /**
     *  load hooks and filters
     */
    public static function load_hooks() {
        add_filter( 'leads/statuses' , array( __CLASS__ , 'merge_fields' ) , 99  );
    }

    /**
     *  Get mappable fields
     */
    public static function get_lead_statuses() {
        $settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , Inbound_Leads::get_lead_statuses() );

        self::$statuses_map = (isset($settings['lead-statuses'])) ?  $settings['lead-statuses'] : array();

    }

    /**
     *  Merge the user's custom fields into the hard coded default fields
     *  @param ARRAY $mappable_fields
     */
    public static function merge_fields( $statuses ) {

        if (!isset(self::$statuses_map['statuses'])) {
            return $statuses;
        }

        $cleaned = array();
        foreach( self::$statuses_map['statuses'] as $key => $status ) {

            /* search core statuses and alter label and priority based on user setting */
            $present = false;
            foreach( $statuses as $i => $s) {

                if (!isset($s['key'])) {
                   continue;
                }

                if ( $s['key'] == $status['key'] ) {
                    $cleaned[$s['key']]['priority'] = $status['priority'];
                    $cleaned[$s['key']]['label'] = (isset($status['label'])) ? $status['label'] : $s['label'];
                    $cleaned[$s['key']]['color'] = (isset($status['color'])) ? $status['color'] : $s['color'];
                    $cleaned[$s['key']]['key'] = $status['key'];
                    $cleaned[$s['key']]['nature'] = 'core';
                    $present = true;
                }
            }

            /* if custom field detected add field to field map */
            if (!$present) {
                $cleaned[$key] = $status;
            }
        }

        return  $cleaned;
    }

    /**
     *  Priorize Lead Fields Array
     *  @param ARRAY $fields simplified id => label array of lead fields
     *  @param STRING $sort_flags default = SORT_ASC
     */
    public static function prioritize_lead_statuses( $statuses,  $sort_flags=SORT_ASC ) {

        $prioritized = array();
        $i=0;

        /* loop through once and set status with priority defined into place */
        foreach ($statuses as $key => $status) {
            if (isset($status['priority'])) {
                $prioritized[$status['priority']] = $status;
                $i++;
            }
        }

        ksort($prioritized, $sort_flags);

        $statuses = array();
        foreach( $prioritized as $i => $status) {
            $statuses[$status['key']] = $status;
        }

        return $statuses;

    }



}

add_action( 'init' , 'load_Inbound_Lead_Statuses' );
function load_Inbound_Lead_Statuses() {
    new Inbound_Lead_Statuses;
}