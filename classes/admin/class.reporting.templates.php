<?php


if ( !class_exists('Inbound_Reporting_Templates') ) {

    class Inbound_Reporting_Templates {

        public function __construct() {

            /* Load and Dislay Correct Template */
            add_action('admin_init' , array( __CLASS__ , 'display_template' ) );

        }

        public static function display_template() {

            if ( !isset( $_REQUEST['action'] )  || $_REQUEST['action'] != 'inbound_generate_report' ) {
                return;
            }

            $args = array(
                'date-range' => $_REQUEST['date_range']
            );

            $_REQUEST['report_class_name']::load_template( $args );

            exit;

        }

    }

    new Inbound_Reporting_Templates;
}