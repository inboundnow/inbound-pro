<?php
/* Inbound Now Menu Class */

if (!class_exists('Inbound_GA_Adminbar')) {

    class Inbound_GA_Adminbar {

        public static function init() {
            self::hooks();
        }

        /**
         *  Loads Hooks & Filters
         */
        public static function hooks() {
            add_filter( 'inbound_menu_debug' , array (__CLASS__ , 'load_debug_links') , 10 , 2);
        }

        /**
         *  Loads debug menu item section
         */
        public static function load_debug_links( $secondary_menu_items , $debug_key ) {
            $actual_link = "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $reset_link = add_query_arg( array('inbound_ga_reset_page_stats' => true ) , $actual_link );

            $secondary_menu_items['inbound-ga-refresh-stats'] = array(
                'parent' => $debug_key,
                'title'  => __( 'Reset Google Analytics Stats on This Page', 'inbound-pro' ),
                'href'   => $reset_link,
                'meta'   => array( 'title' =>  __( 'Reset Google Analytics Stats on This Page.', 'inbound-pro' ) )
            );

            return $secondary_menu_items;
        }

    }

    add_action('init' , array( 'Inbound_GA_Adminbar' , 'init' ) );
}
