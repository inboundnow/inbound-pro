<?php

/**
 * Class for loading and displaying expanded template reports
 * @package     InboundPro
 * @subpackage  Reports
 */
class Inbound_Reporting_Templates {
    static $prompt_upgrade;

    /**
     * Inbound_Reporting_Templates constructor.
     */
    public function __construct() {


        /* Load and Dislay Correct Template */
        add_action('admin_init' , array( __CLASS__ , 'display_template' ) );

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

        $today = new DateTime(date_i18n('Y-m-d G:i:s'));
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
    public static function get_days_from_range( $start_time , $end_time ){

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($start_time,5,2), substr($start_time,8,2), substr($start_time,0,4));
        $iDateTo = mktime(1, 0, 0, substr($end_time,5,2), substr($end_time,8,2), substr($end_time,0,4));

        if ($iDateTo >= $iDateFrom)
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
                    <?php echo intval($_REQUEST['range']) .' '. __( 'days' , 'inbound-pro'); ?> &nbsp; <i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['page_id'])) {
                ?>
                <div class="tag"><span><?php _e('Page Id' , 'inbound-pro'); ?></span>
                    <?php echo sanitize_text_field($_REQUEST['page_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['lead_id']) && $_REQUEST['lead_id'] ) {
                ?>
                <div class="tag"><span><?php _e('Lead Id' , 'inbound-pro'); ?></span>
                    <?php echo intval($_REQUEST['lead_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['lead_uid'])) {
                ?>
                <div class="tag"><span><?php _e('Lead UID' , 'inbound-pro'); ?></span>
                    <?php echo sanitize_text_field($_REQUEST['lead_uid']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['source'])) {
                ?>
                <div class="tag"><span><?php _e('Source' , 'inbound-pro'); ?></span>
                    <?php echo sanitize_text_field($_REQUEST['source']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['event_name'])) {
                ?>
                <div class="tag"><span><?php _e('Event' , 'inbound-pro'); ?></span>
                    <?php echo Inbound_Events::get_event_label(sanitize_text_field($_REQUEST['event_name']), false); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['list_id'])) {
                ?>
                <div class="tag"><span><?php _e('List Id' , 'inbound-pro'); ?></span>
                    <?php echo intval($_REQUEST['list_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
                </div>
                <?php
            }
            if (isset($_REQUEST['job_id']) && $_REQUEST['job_id'] ) {
                ?>
                <div class="tag"><span><?php _e('Job Id' , 'inbound-pro'); ?></span>
                    <?php echo intval($_REQUEST['job_id']); ?> <i class="fa fa-tag" aria-hidden="true"></i>
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

    public static function js_lead_table_sort(){

        ?>
        <script type="text/javascript" src="<?php echo includes_url('/js/jquery/jquery.js'); ?>"></script>
        <script type="text/javascript">
            jQuery(document).ready(function(){

                var lastAction = 'report-date-header';

                jQuery('.sort-lead-report-by').on('click', function(){

                    var action = jQuery(this).attr('sort-by');
                    var sortHeader = jQuery('.sort-lead-report-by[sort-by="' + action + '"]');  //clicked header/footer elements
                    var sortOrder = jQuery(this).attr('sort-order');  //counter for determining the sort order
                    var rows = jQuery('.lead-table-data-report-row');

                    jQuery('.sort-lead-report-by').find('.lead-report-sort-indicater').remove();

                    if(sortOrder % 2){ //if even, sort the items ASC, if odd sort DESC
                        jQuery(sortHeader).append('<i class="fa fa-caret-up lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>');
                    }else{
                        jQuery(sortHeader).append('<i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>');
                    }

                    /** if the leads are to be sorted by name **/
                    if(action === 'report-name-field-header'){

                        /* if lastAction == action, reverse the existing name sort order */
                        if(lastAction == action){
                            var reversedRows = rows.get().reverse();

                            jQuery(sortHeader).attr('sort-order', (parseInt(sortOrder) + 1)); //increment the ASC vs DESC counter

                            jQuery('.lead-table-data-report-row').remove();
                            jQuery('#the-list').append(reversedRows);

                            return;
                        }else{

                            rows.sort(function(a, b){

                                var nameA = a.getAttribute('data-name-field').toLowerCase();
                                var nameB = b.getAttribute('data-name-field').toLowerCase();

                                if(nameA > nameB){
                                    return 1;
                                }

                                if(nameA < nameB){
                                    return -1;
                                }
                                /* if name A == name B, sort by date number. Oldest first*/
                                if(nameA == nameB){
                                    var num1 = a.getAttribute('data-date-number');
                                    var num2 = b.getAttribute('data-date-number');

                                    return num1 - num2;
                                }
                            });
                        }
                    }

                    /** if the leads are to be sorted by email variation id **/
                    if(action === 'report-email-variation-header'){

                        /* get the email variations in the list */
                        var variationIds = [];
                        for(var j = 0; j < 25; j++){
                            if(jQuery('.lead-table-data-report-row[data-email-variation=' + j + ']').length > 0){
                                variationIds.push(j);
                            }
                        }

                        /* if the sort order is to be reversed */
                        if(lastAction == action){

                            if(sortOrder % 2){  //if the variations are to be sorted DESC, reverse the list of variation ids then process
                               variationIds = variationIds.reverse();
                            }

                            jQuery(sortHeader).attr('sort-order', (parseInt(sortOrder) + 1));

                            var tempRows = [];

                            for(var i in variationIds){
                                var shortRows = jQuery('.lead-table-data-report-row[data-email-variation=' + variationIds[i] + ']');

                                shortRows.sort(function(a, b){
                                    return a.getAttribute('data-date-number') - b.getAttribute('data-date-number');

                                });

                                tempRows = tempRows.concat(shortRows.get());
                            }

                            rows = tempRows;

                            jQuery('.lead-table-data-report-row').remove();
                            jQuery('#the-list').append(rows);

                            return;

                        }else{

                            var tempRows = [];

                            for(var i in variationIds){
                                var shortRows = jQuery('.lead-table-data-report-row[data-email-variation=' + variationIds[i] + ']');

                                shortRows.sort(function(a, b){
                                    return a.getAttribute('data-date-number') - b.getAttribute('data-date-number');

                                });

                                tempRows = tempRows.concat(shortRows.get());
                            }

                            rows = tempRows;
                        }
                    }

                    /** if the leads are to be sorted by action date**/
                    if(action === 'report-date-header'){

                        /* if the last row action was this one, just reverse the existing rows*/
                        if(lastAction == action){
                            var reversedRows = rows.get().reverse();

                            jQuery(sortHeader).attr('sort-order', (parseInt(sortOrder) + 1));

                            jQuery('.lead-table-data-report-row').remove();
                            jQuery('#the-list').append(reversedRows);

                            return;
                        }else{
                            rows.sort(function(a, b){
                                return a.getAttribute('data-date-number') - b.getAttribute('data-date-number');
                            });
                        }
                    }

                    /** if the leads are to be sorted by id **/
                    if(action === 'report-lead-id-header'){

                        /* if the last row action was this one, just reverse the existing rows*/
                        if(lastAction == action){
                            var reversedRows = rows.get().reverse();

                            jQuery(sortHeader).attr('sort-order', (parseInt(sortOrder) + 1));

                            jQuery('.lead-table-data-report-row').remove();
                            jQuery('#the-list').append(reversedRows);

                            return;
                        }else{
                            rows.sort(function(a, b){
                                return a.getAttribute('data-lead-id') - b.getAttribute('data-lead-id');
                            });
                        }
                    }

                    /** if leads are to be sorted by impressions **/
                    if(action === 'report-impressions-header'){
                        /* if the last row action was this one, just reverse the existing rows*/
                        if(lastAction == action){
                            var reversedRows = rows.get().reverse();

                            jQuery(sortHeader).attr('sort-order', (parseInt(sortOrder) + 1));

                            jQuery('.lead-table-data-report-row').remove();
                            jQuery('#the-list').append(reversedRows);

                            return;
                        }else{
                            rows.sort(function(a, b){
                                return a.getAttribute('data-impressions') - b.getAttribute('data-impressions');
                            });
                        }
                    }

                    jQuery('.sort-lead-report-by').attr('sort-order', 0);  //unset all sort order counters
                    jQuery(jQuery('.sort-lead-report-by[sort-by="' + action + '"]')).attr('sort-order', 1);  //set the counter for the clicked header

                    jQuery('.lead-table-data-report-row').remove();
                    jQuery('#the-list').append(rows);

                    lastAction = action;

                });
            });
        </script>
        <?php
    }

}


new Inbound_Reporting_Templates;

