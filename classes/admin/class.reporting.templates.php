<?php

/**
 *
 */

if ( !class_exists('Inbound_Reporting_Templates') ) {

    class Inbound_Reporting_Templates {
        static $prompt_upgrade;

        /**
         * Inbound_Reporting_Templates constructor.
         */
        public function __construct() {


            /* Load and Dislay Correct Template */
            add_action('admin_init' , array( __CLASS__ , 'display_template' ) );

            /* add property to screen options */
            add_filter( 'screen_settings',array( __CLASS__ , 'add_screen_option_field'), 10, 2 );

            /* save screen options */
            add_filter( 'init', array( __CLASS__, 'set_screen_option'), 1 );
        }

        /**
         * Hooked into 'screen_settings'. Adds the field to the settings area
         *
         * @access public
         * @return string The settings fields
         */

        public static function add_screen_option_field($rv, $screen) {

            $screen = get_current_screen();

            $whitelist = array('edit-post' , 'edit-page', 'post', 'page');
            if (!$screen || !in_array( $screen->id , $whitelist ) ) {
                return;
            }

            $val = get_user_option(
                'inbound_screen_option_range',
                get_current_user_id()
            );

            $val = ($val) ? $val : 90;

            $rv .= '<fieldset class="">';

            $rv .= '<legend>' . __('Inbound Analytics') . '</legend>';

            $rv .=  __('Reporting range in days' , 'inbound-pro' ). ':';

            $rv .= '<select  name="inbound_screen_option_range" class="" id="" style="width:100px;" ';

            $ranges = array(1,7,30,90,360);

            foreach ($ranges as $range) {
                $rv .= '<option value="'.$range.'" '. ( $val==$range ? 'selected="true"' : '' ).'">'.$range.' ' . __('days','inbound-pro') .'</option>';
            }

            $rv .= '</select></fieldset>';

            return $rv;

        }


        /**
         * Listen for updated option and save.
         *
         */
        public static function set_screen_option() {

            if (!isset($_POST['inbound_screen_option_range'])) {
                return;
            }

            $response = update_user_option(
                get_current_user_id(),
                'inbound_screen_option_range',
                intval($_POST['inbound_screen_option_range'])
            );


        }

        /**
         *
         */
        public static function display_template() {

            if ( !isset( $_REQUEST['action'] )  || $_REQUEST['action'] != 'inbound_generate_report' ) {
                return;
            }

            /* Check Permissions */
            if ( INBOUND_ACCESS_LEVEL === 0 || INBOUND_ACCESS_LEVEL === 9) {
                $class = 'Inbound_Upgrade_For_More_Reports';
            } else {
                $class = $_REQUEST['class'];
            }

            call_user_func(array( $class , 'load_template'));
        }

        /**
         *    Prepares dates to process
         */
        public static function prepare_range( $range = 90 ) {

            global $post;

            $today = new DateTime(date_i18n('Y-m-d G:i:s T'));
            $dates['end_date'] = $today->format('Y-m-d G:i:s T');
            $today->modify('-'.$range.' days');
            $dates['start_date'] =  $today->format('Y-m-d G:i:s T');

            /* generate dates for previous date-range */
            $today->modify('-'.$range.' days');
            $dates['past_start_date'] = $today->format('Y-m-d G:i:s T');
            $dates['past_end_date'] = $dates['start_date'];

            return $dates;
        }

        /**
         * takes two dates formatted as YYYY-MM-DD and creates an
         * inclusive array of the dates between the from and to dates.
         * could test validity of dates here but I'm already doing
         * that in the main script
         * @param $start_time
         * @param $end_time
         * @return array
         */
        public static function get_days_from_range( $start_time , $end_time )      {

            $aryRange=array();

            $iDateFrom=mktime(1,0,0,substr($start_time,5,2),     substr($start_time,8,2),substr($start_time,0,4));
            $iDateTo=mktime(1,0,0,substr($end_time,5,2),     substr($end_time,8,2),substr($end_time,0,4));

            if ($iDateTo>=$iDateFrom)
            {
                array_push($aryRange,date('Y-m-d',$iDateFrom));
                while ($iDateFrom<$iDateTo)
                {
                    $iDateFrom+=86400; // add 24 hours
                    array_push($aryRange,date('Y-m-d',$iDateFrom));
                }
            }

            return $aryRange;
        }


        /**
         *
         */
        public static function display_filters() {
            ?>
            <div class="report-filters">
                <?php

                if (isset($_REQUEST['range'])) {
                    ?>
                    <div class="tag tag-range">&nbsp;
                        <?php echo intval($_REQUEST['range']) .' '. __( 'days' , 'inboud-pro'); ?> &nbsp; <i class="fa fa-calendar" aria-hidden="true"></i>
                    </div>
                    <?php
                }
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
                if (isset($_REQUEST['event_name'])) {
                    ?>
                    <div class="tag"><span><?php _e('event' , 'inbound-pro'); ?></span>
                        <?php echo sanitize_text_field($_REQUEST['event_name']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                    </div>
                    <?php
                }
                if (isset($_REQUEST['list_id'])) {
                    ?>
                    <div class="tag"><span><?php _e('list id' , 'inbound-pro'); ?></span>
                        <?php echo intval($_REQUEST['list_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
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

                .tag-range {
                    background-color:darkgray !important;
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