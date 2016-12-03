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

            $_REQUEST['class']::load_template();
        }

        /**
         *    Prepares dates to process
         */
        public static function prepare_range( $range = 90 ) {

            global $post;

            $today = new DateTime(date('Y-m-d G:i:s T'));
            $dates['end_date'] = $today->format('Y-m-d G:i:s T');
            $today->modify('-'.$range.' days');
            $dates['start_date'] =  $today->format('Y-m-d G:i:s T');

            return $dates;
        }


        public static function display_filters() {
            ?>
            <div class="report-filters">
                <?php
                if (isset($_REQUEST['page_id'])) {
                    ?>
                    <div class="tag"><span><?php _e('page id' , 'inbound-pro'); ?></span>
                        <?php echo intval($_REQUEST['page_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                    </div>
                    <?php
                }
                if (isset($_REQUEST['lead_id']) && $_REQUEST['lead_id'] ) {
                    ?>
                    <div class="tag"><span><?php _e('lead id' , 'inbound-pro'); ?></span>
                        <?php echo intval($_REQUEST['lead_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                    </div>
                    <?php
                }
                if (isset($_REQUEST['lead_uid'])) {
                    ?>
                    <div class="tag"><span><?php _e('lead uid' , 'inbound-pro'); ?></span>
                        <?php echo sanitize_text_field($_REQUEST['lead_uid']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                    </div>
                    <?php
                }
                if (isset($_REQUEST['source'])) {
                    ?>
                    <div class="tag"><span><?php _e('source' , 'inbound-pro'); ?></span>
                        <?php echo sanitize_text_field($_REQUEST['source']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                    </div>
                    <?php
                }
                ?>
            </div>
            <style type="text/css">
                .tag {
                    height:12px;
                    background: none repeat scroll 0 0 skyblue;
                    border-radius: 2px;
                    color: white;
                    cursor: default;
                    display: inline-block;
                    position: relative;
                    white-space: nowrap;
                    padding: 6px 7px 4px 0;
                    margin: 5px 10px 0 0;
                    font-size:10px
                }

                .tag span {
                    background: none repeat scroll 0 0 gainsboro;
                    border-radius: 2px 0 0 2px;
                    margin-right: 5px;
                    padding: 6px 10px 5px;
                }

                .fa-tag {
                    margin-left:4px;
                }
                .report-filters {
                    margin-left:10px;
                    margin-right:10px;
                }
            </style>
            <?php
        }
    }

    new Inbound_Reporting_Templates;
}