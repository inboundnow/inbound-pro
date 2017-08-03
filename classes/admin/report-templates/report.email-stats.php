<?php
/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */

if (!class_exists('Inbound_Mailer_Stats_Report')) {

    class Inbound_Mailer_Stats_Report extends Inbound_Reporting_Templates {

        static $range;
        static $page;
        static $offset;
        static $limit;
        static $total_events;
        static $total_pages;
        static $graph_data;
        static $top_variations;
        static $start_date;
        static $end_date;
        static $past_start_date;
        static $past_end_date;
        static $possible_actions;
        static $job_id;
        static $events = array();


        /**
         *  Load & display the template report
         *
         */
        public static function load_template() {
            self::load_data();

            self::display_header();
            self::print_css();
            parent::display_filters();
            self::display_chart();
            self::display_top_email_variations();
            self::display_all_events();
            parent::js_lead_table_sort();
            die();
        }

        /**
         * Shows report header
         */
        public static function display_header() {

            $title = get_the_title(intval($_REQUEST['email_id']));
            $permalink = get_the_permalink(intval($_REQUEST['email_id']));
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';

            ?>
            <head>
                <script type="text/javascript" src="<?php echo INBOUND_PRO_URLPATH ;?>assets/libraries/echarts/echarts.min.js"  /></script>
            </head>
            <aside class="profile-card">
                <header>
                    <h1><?php _e('Email Report Stats' , 'inbound-pro'); ?></h1>
                    <h2><?php echo $title; ?></h2>
                    <h3><a href="<?php echo $permalink; ?>" target="_self"><?php echo $permalink; ?></a></h3>
                </header>
            </aside>
            <?php
        }

        /*
         * Displays the email event stat data on a chart
         **/
        public static function display_chart() {

            self::$graph_data['current']= self::prepare_chart_data(self::$start_date, self::$end_date , 'current', intval($_REQUEST['email_id']));

            /* loop through  */
            ?>
            <div id="graph-container" style='height:350px;'></div>
            <script type="text/javascript">
                // based on prepared DOM, initialize echarts instance
                var myChart = echarts.init(document.getElementById('graph-container'));

                // specify chart configuration item and data
                var option = {
                    title: {
                        text: ''
                    },
                    tooltip : {
                        trigger: 'axis'
                    },
                    legend: {
                        data:['<?php echo sprintf( __('%s past %s days','inbound-pro') , self::$possible_actions[$_REQUEST['event_name']], self::$range ); ?>']
                    },
                    toolbox: {
                        show : true,
                        feature : {
                            mark : {show: true},
                            dataView : {show: true, readOnly: false},
                            magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    calculable : true,
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis : [
                        {
                            type : 'category',
                            boundaryGap : false,
                            data : <?php echo json_encode(self::$graph_data['current']['dates']); ?>

                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [

                        {
                            name:'<?php echo sprintf( __('%s over the past %s days','inbound-pro') , self::$possible_actions[$_REQUEST['event_name']], self::$range ); ?>',
                            type:'line',//55ddff , 55ff77
                            itemStyle: {normal: {color:'#55ddff', label:{show:false}}},
                            areaStyle: {normal: {color:'#55ddff', label:{show:true}}},
                            data:<?php echo json_encode(self::$graph_data['current']['actions_counted']); ?>

                        }
                    ]

                };
                // use configuration item and data specified to show chart
                myChart.setOption(option);
            </script>
            <?php
        }

        /**
         * Displays a list of the email's variation action stats
         */
        public static function display_top_email_variations() {
            $email = get_post(intval($_REQUEST['email_id']));

            /* if there are no stored actions for this variation, output a message and exit */
            if(empty(self::$top_variations)){
                ?>
                <div class="flexbox-container email-variation-stats-container">
                    <div>
                        <h3><?php echo sprintf(__('No %s Logged For This Email', 'inbound-pro'), self::$possible_actions[$_REQUEST['event_name']]); ?></h3>
                    </div>
                </div>
                <?php
                return;
            }

            ?>
            <div class="flexbox-container email-variation-stats-container">
                <div>
                    <table class="email-variation-stats">
                        <thead>
                        <tr>
                            <th scope="col" class="">
                                <span><?php _e('Email Variation' , 'inbound-pro'); ?></span>
                            </th>
                            <th scope="col" class="">
                                <span><?php echo sprintf(__('Total %s For This Variation', 'inbound-pro'), self::$possible_actions[$_REQUEST['event_name']]); ?></span>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="">

                        <?php
                        $variation_letters = array(
                         'A', 'B', 'C', 'D', 'E', 'F',
                         'G', 'H', 'I', 'J', 'K', 'L',
                         'M', 'N', 'O', 'P', 'Q', 'R',
                         'S', 'T', 'U', 'V', 'W', 'X',
                         'Y', 'Z');

                        $i = 0;
                        foreach(self::$top_variations as $variant_id => $action_count){
                            //if($i >= 10){break;}//uncomment to make this a top 10 list
                            ?>
                            <tr id="" class="">
                                <td class="">
                                    <span class="top-count-num">
                                        <a href="<?php echo admin_url('post.php?post=' . intval($_REQUEST['email_id']) . '&inbvid=' . $variant_id . '&action=edit'); ?>" target="_self">
                                            <?php echo $variation_letters[$variant_id]; ?>
                                        </a>
                                    </span>
                                </td>
                                <td class="">
                                    <?php
                                    echo $action_count;
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        }
                        ?>
                        </tbody>
                    </table>

                </div>
            </div>
            <?php
        }

        /**
         * Displays a sortable list of all leads that have had an interaction with the given email
         */
        public static function display_all_events(){

            /*exit if there's no lead data to show or if the lead table isn't supposed to show*/
            if(empty(self::$events) || $_REQUEST['display_lead_table'] !== 'true'){
                return;
            }

            /* array to store tokens and urls into memory to save database calls */
            $url_array = array();

            $variation_letters = array(
             'A', 'B', 'C', 'D', 'E', 'F',
             'G', 'H', 'I', 'J', 'K', 'L',
             'M', 'N', 'O', 'P', 'Q', 'R',
             'S', 'T', 'U', 'V', 'W', 'X',
             'Y', 'Z');

            ?>
            <div class="flexbox-container lead-action-data-list">
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th><?php _e('Avatar', 'inbound-pro'); ?></th>
                                <th class="sort-lead-report-by" sort-by="report-name-field-header">
                                    <?php _e('Lead Name', 'inbound-pro'); ?>
                                </th>
                                <?php
                                 if ( $_REQUEST['event_name'] == 'sparkpost_click' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('URL', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }
                                 ?>
                                 <?php
                                 if ( $_REQUEST['event_name'] == 'sparkpost_rejected' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('Reason', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }
                                 ?>
                                 <?php
                                 if ( $_REQUEST['event_name'] == 'inbound_unsubscribe' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('Message', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }
                                 ?>
                                <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                    <?php echo sprintf(__('Email Variation %s', 'inbound-pro'), self::$possible_actions['singular_form'][$_REQUEST['event_name']]); ?>
                                </th>
                                <th class="sort-lead-report-by" sort-by="report-date-header" sort-order="0">
                                    <?php echo sprintf(__('Email %s On', 'inbound-pro'), self::$possible_actions['singular_form'][$_REQUEST['event_name']]); ?>
                                    <i class="fa fa-caret-up lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php
                            $logged_event = array();
                            $action_number = 0;
                            foreach(self::$events as $index => $event){

                                /*if a lead has been sent the same email variation more than once, skip*/
                                if(isset($logged_event[$event['variation_id']][$event['lead_id']])){
                                    continue;
                                }
                                $logged_event[$event['variation_id']][$event['lead_id']] = 1;

                                $lead = get_post($event['lead_id']);

                                $lead_exists = ($lead) ? true : false;

                                if($lead_exists){
                                    $lead_name = get_post_meta($lead->ID, 'wpleads_name', true);
                                    if(empty($lead_name)){
                                        $lead_name = 'N/A';
                                    }

                                }else{
                                    $lead_name = __('Lead Deleted', 'inbound-pro');
                                }

                                ?>
                            <tr class="lead-table-data-report-row" data-name-field="<?php echo $lead_name; ?>" data-email-variation="<?php echo $event['variation_id']; ?>" data-date-number="<?php echo $action_number;?>">
                                <td class="lead-avatar">
                                    <?php $gravatar = ($lead_exists) ? Leads_Post_Type::get_gravatar($event['lead_id']) : $default_gravatar;
                                    echo '<img class="lead-grav-img " width="40" height="40" src="' . $gravatar . '">'; ?>
                                </td>
                                <td class="lead-name">
                                    <a href="<?php echo admin_url('post.php?post=' . $event['lead_id'] . '&action=edit&small_lead_preview=true&tb_hide_nav=true'); ?>"><?php echo $lead_name; ?></a>
                                </td>
                                <?php
                                if ( $_REQUEST['event_name'] == 'sparkpost_click' ) {
                                    $event_details = json_decode($event['event_details'] , true);

                                    $tracking_endpoint = apply_filters( 'inbound_event_endpoint', 'inbound' );

                                    if (strstr($event_details['target_link_url'] , '?token')) {
                                        $args['url'] = $event_details['target_link_url'];
                                        $args['label'] =__('Unsubscribe' , 'inbound-pro');

                                    } else if (strstr($event_details['target_link_url'], $tracking_endpoint)) {
                                        $token = end(explode('/', $event_details['target_link_url']));
                                        $args = Inbound_API::get_args_from_token($token);
                                        $args['url'] = ($args['url']) ? $args['url'] : '#'.$token;
                                        $args['label'] = ($args['url']) ? $args['url'] : sprintf(__('No URL for token %s' , 'inbound-pro') , $token);
                                    } else {
                                        $args['url'] = $event_details['target_link_url'];
                                        $args['label'] = $event_details['target_link_url'];
                                    }
                                    ?>
                                    <td class="clicked-url">
                                        <?php
                                        if (strstr($args['url'],':')) {
                                            echo '<a href="'.$args['url'].'" target="_blank">'.$args['label'].'</a>';
                                        } else {
                                            echo $args['label'];
                                        }
                                        ?>
                                    </td>
                                    <?php
                                }
                                ?>
                                <?php
                                if ( $_REQUEST['event_name'] == 'sparkpost_rejected' ) {
                                    $event_details = json_decode($event['event_details'] , true);

                                    ?>
                                    <td class="clicked-url">
                                      <?php echo (isset($event_details['description'])) ? $event_details['description'] : __('no reason supplied' , 'inbound-pro'); ?>
                                    </td>
                                    <?php
                                }
                                ?>
                                <?php
                                if ( $_REQUEST['event_name'] == 'inbound_unsubscribe' ) {
                                    $event_details = json_decode($event['event_details'] , true);

                                    ?>
                                    <td class="unsubscribe-message">
                                      <?php echo (isset($event_details['comments'])) ? sanitize_text_field($event_details['comments']) : __('...' , 'inbound-pro'); ?>
                                    </td>
                                    <?php
                                }
                                ?>
                                <td class="email-variation"><?php echo $variation_letters[$event['variation_id']]; ?></td>
                                <td class="datestamp">
                                    <p class="mod-date" >
                                        <em> <?php echo date("F j, Y, g:i a" , strtotime($event['datetime'])); ?></em>
                                    </p>
                                </td>
                            </tr>
                            <?php
                                $action_number++;
                            }   ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?php _e('Avatar', 'inbound-pro'); ?></th>
                                <th class="sort-lead-report-by" sort-by="report-name-field-header">
                                    <?php _e('Lead Name', 'inbound-pro'); ?>
                                </th>
                                <?php
                                 if ( $_REQUEST['event_name'] == 'sparkpost_click' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('URL', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }
                                 ?>
                                <?php
                                 if ( $_REQUEST['event_name'] == 'sparkpost_rejected' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('Reason', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }

                                 if ( $_REQUEST['event_name'] == 'inbound_unsubscribe' ) {
                                 ?>
                                     <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                         <?php _e('Message', 'inbound-pro'); ?>
                                     </th>
                                 <?php
                                 }
                                 ?>
                                <th class="sort-lead-report-by" sort-by="report-email-variation-header">
                                    <?php echo sprintf(__('Email Variation %s', 'inbound-pro'), self::$possible_actions['singular_form'][$_REQUEST['event_name']]); ?>
                                </th>
                                <th class="sort-lead-report-by" sort-by="report-date-header">
                                    <?php echo sprintf(__('Email %s On', 'inbound-pro'), self::$possible_actions['singular_form'][$_REQUEST['event_name']]); ?>
                                    <i class="fa fa-caret-up lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <select id="limit-select">
                            <option value="50" <?php selected(self::$limit , '50'); ?>>50 <?php _e('per page' , 'inbound-pro'); ?></option>
                            <option value="100" <?php selected(self::$limit , '100'); ?>>100 <?php _e('per page' , 'inbound-pro'); ?></option>
                            <option value="300" <?php selected(self::$limit , '300'); ?>>300 <?php _e('per page' , 'inbound-pro'); ?></option>
                            <option value="500" <?php selected(self::$limit , '500'); ?>>500 <?php _e('per page' , 'inbound-pro'); ?></option>
                    </select>
                    <script type="text/javascript">
                        /**
                         *  reloads the report template with an updated limit value
                         */
                        function reload_limit(name, value) {
                            var str = location.search;
                            if (new RegExp("[&?]"+name+"([=&].+)?$").test(str)) {
                                str = str.replace(new RegExp("(?:[&?])"+name+"[^&]*", "g"), "")
                            }
                            str += "&";
                            str += name + "=" + value;
                            str = "?" + str.slice(1);
                            // there is an official order for the query and the hash if you didn't know.
                            location.assign(location.origin + location.pathname + str + location.hash)
                        };

                        /* on page load */
                        (function() {
                            var limit_select = document.getElementById('limit-select');

                            limit_select.onchange = function() {
                                var elem = (typeof this.selectedIndex === "undefined" ? window.event.srcElement : this);
                                var value = elem.value || elem.options[elem.selectedIndex].value;

                                reload_limit('limit' , value);
                            }
                        })();
                    </script>
                    <div class="pagination">

                        <?php
                        $url = basename($_SERVER['REQUEST_URI']);
                        parse_str($url , $report_args);

                        $report_args = array('action' => $report_args['index_php?action']) + $report_args;
                        unset($report_args['index_php?action']);
                        unset($report_args['index_php?class']);
                        unset($report_args['tb_hide_nav']);
                        unset($report_args['TB_iframe']);
                        if( self::$page > 1 ){
                            $report_args['page_number'] = self::$page - 1;
                            $link = add_query_arg( $report_args , admin_url( 'index.php' ) );
                            echo '<a href="' . $link . '" >&laquo;</a>';
                        }

                        for ($i=0;$i<self::$total_pages;$i++) {
                            $page_num = $i +1;
                            $report_args['page_number'] = $page_num;
                            $link = add_query_arg( $report_args , admin_url('index.php') );

                            echo '<a href="' . $link . '" ' . (self::$page == $page_num ? 'class="active"' : '' ) . '>' . $page_num . '</a>';
                        }

                        if( self::$offset  < self::$total_events ){
                            $report_args['page_number'] = self::$page + 1;
                            $link = add_query_arg( $report_args , admin_url( 'index.php' ) );
                            echo '<a href="' . $link . '">&raquo;</a>';
                        }
                        ?>

                    </div>
                </div>
            </div>


            <?php


        }

        /**
         * Print Report CSS rules into the document
         */
        public static function print_css() {

            ?>
            <style type="text/css">
                body {
                    font-family: sans-serif;
                    color: #444;
                }
                table {
                    border-spacing: 0;
                    margin-bottom: 1rem;
                    border-radius: 0;
                    font-size: 9px;
                    width:100%;
                }

                th {
                    background-color: #f1f1f1;
                    color:#000;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -user-select: none;
                }

                th,
                td {
                    min-width: 70px;
                    padding: 10px 5px;
                    text-align:center;
                }

                tbody tr:nth-child(even) {
                    background-color: #f1f1f1;
                }

                #search {
                    margin-bottom: 10px;
                }

                #page-navigation {
                    display: flex;
                    margin-top: 5px;
                }

                #page-navigation p {
                    margin-left: 5px;
                    margin-right: 5px;
                }

                #page-navigation button {
                    background-color: #42b983;
                    border-color: #42b983;
                    color: rgba(255, 255, 255, 0.66);
                }

                .lead-grav-img {
                    background: #FCFDFE;
                    border: 1px solid #E0E0E0;
                    -moz-border-radius:  0px;
                    border-radius: 0px;
                }
                a {
                    color:#1585cf;
                    text-decoration:none;
                }

                .fa-sort-desc {
                    position:relative;
                    top:-2px;
                    left:2px;
                }

                /* header */
                @import url(http://fonts.googleapis.com/css?family=Lato:300,400);
                @-webkit-keyframes pulsate {
                    0% {
                        -webkit-transform: scale(0.6, 0.6);
                        transform: scale(0.6, 0.6);
                        opacity: 0.0;
                    }
                    50% {
                        opacity: 1.0;
                    }
                    100% {
                        -webkit-transform: scale(1, 1);
                        transform: scale(1, 1);
                        opacity: 0.0;
                    }
                }
                @keyframes pulsate {
                    0% {
                        -webkit-transform: scale(0.6, 0.6);
                        transform: scale(0.6, 0.6);
                        opacity: 0.0;
                    }
                    50% {
                        opacity: 1.0;
                    }
                    100% {
                        -webkit-transform: scale(1, 1);
                        transform: scale(1, 1);
                        opacity: 0.0;
                    }
                }
                body {
                    background-repeat: repeat, no-repeat;
                    background-size: auto, 100% 100%;
                    background-attachment: fixed;
                    margin:0px;

                }

                .profile-card {
                    height: 125px;
                    position: relative;
                    background-position: 50% 50%;
                    background-repeat: no-repeat;
                    background-size: cover;
                    background-color:#1a1d23;
                    color: #ecf0f1;
                    font-family: "Lato", "Helvetica Neue",Helvetica,Arial,sans-serif;
                    font-size: 16px;
                    line-height: 1.5;
                    font-weight: 300;
                    width: 100%;
                    margin-bottom:21px;
                }
                .profile-card header {
                    width: 100%;
                    height: 100%;
                    text-align: center;
                    /* FF3.6+ */
                    background: -webkit-gradient(linear, left bottom, right top, color-stop(0%, rgba(0, 0, 0, 0.9)), color-stop(100%, transparent));
                    /* Chrome,Safari4+ */
                    background: -webkit-linear-gradient(45deg, rgba(0, 0, 0, 0.9) 0%, transparent 100%);
                    /* Chrome10+,Safari5.1+ */
                    /* Opera 11.10+ */
                    /* IE10+ */
                    background: linear-gradient(45deg, rgba(0, 0, 0, 0.9) 0%, transparent 100%);
                    /* W3C */
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc000000', endColorstr='#00000000',GradientType=1 );
                    /* IE6-9 fallback on horizontal gradient */
                }
                .profile-card header .profile-img-link {
                    position: relative;
                }
                .profile-card header .profile-img-link:before {
                    content: "";
                    border: 15px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    height: 90px;
                    width: 90px;
                    position: absolute;
                    left: 0;
                    bottom: 3px;
                    -webkit-animation: pulsate 1.6s ease-out;
                    animation: pulsate 1.6s ease-out;
                    -webkit-animation-iteration-count: infinite;
                    animation-iteration-count: infinite;
                    opacity: 0.0;
                    z-index: 99;
                }
                .profile-card header img {
                    position: relative;
                    border-radius: 50%;
                    height: 90px;
                    width: 90px;
                    padding: 0;
                    margin: 0;
                    border: 15px solid transparent;
                    margin-top: 12px;
                    z-index: 9999;
                    -webkit-transition: all .3s ease-out;
                    transition: all .3s ease-out;
                }
                .profile-card header a:hover img {
                    -webkit-transform: scale(1.06, 1.06);
                    transform: scale(1.06, 1.06);
                }
                .profile-card header a:hover:before {
                    -webkit-animation: none;
                    animation: none;
                }
                .profile-card header h1 {
                    text-align: center;
                    font-size: 28px;
                    opacity: 0.9;
                    margin-top: 0px;
                    margin-bottom: 3px;
                    padding-top:10px;
                }
                .profile-card header h2 {
                    font-size: 18px;
                    margin-top: 0;
                    opacity: 0.9;
                    margin-top: 0px;
                    margin-bottom: 3px;
                }
                .profile-card header h3 {
                    font-size: 14px;
                    margin-top: 0;
                    opacity: 0.9;
                    margin-top: 0px;
                    margin-bottom: 3px;
                }
                .profile-card .profile-bio {
                    position: absolute;
                    bottom: 0;
                }
                .profile-card .profile-bio p {
                    margin: 24px;
                    text-align: center;
                    opacity: 0.9;
                }
                .profile-card .profile-social-links {
                    position: relative;
                    background-color: white;
                    margin: 0 auto;
                    text-align: center;
                    padding: 6px 0;
                }
                .profile-card .profile-social-links li {
                    display: inline-block;
                    padding: 3px 5px 0;
                }
                .profile-card .profile-social-links li img {
                    height: 28px;
                    opacity: 0.8;
                    -webkit-transition: all .2s ease-out;
                    transition: all .2s ease-out;
                }
                .profile-card .profile-social-links li a:hover img {
                    opacity: 1;
                    -webkit-transform: scale(1.1, 1.1);
                    transform: scale(1.1, 1.1);
                }
                .profile-card .profile-social-links:after {
                    bottom: 100%;
                    left: 50%;
                    border: solid transparent;
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border-color: rgba(255, 255, 255, 0);
                    border-bottom-color: #ffffff;
                    border-width: 10px;
                    margin-left: -10px;
                }

                .fa-pencil-square-o {
                    font-size:21px;
                }

                .fa-hourglass-half {
                    cursor:default;
                    color: lightgrey;
                }

                .flexbox-container {
                    display: -ms-flex;
                    display: -webkit-flex;
                    display: flex;
                }

                .flexbox-container > div {
                    width: 100%;
                    padding: 10px;
                }

                .flexbox-container > div:first-child {
                    margin-right: 20px;
                }

                hr {
                    margin-top:10px;
                    margin-left:10px;
                    margin-right:10px;
                    pacity:.2;
                }

                .visitors-stream-view {
                    margin-left:10px;
                    margin-right:10px;
                    margin-bottom:44px;
                }

                #graph-container {
                    margin-top:10px;
                    margin-left:auto;
                    margin-right:auto;
                }

                .email-variation-stats td, {
                    height:40px;
                }

                .pagination {
                    display: inline-block;
                    padding: 0;
                    margin: 8px 0;
                }

                .pagination a {
                    color: black;
                    float: left;
                    padding: 8px 16px;
                    text-decoration: none;
                }


                .pagination a.active {
                    background-color: #1585cf;
                    color: white;
                }

                .pagination a:hover:not(.active) {background-color: #ddd;}

                #limit-select {
                    height:30px;
                    text-align:right;
                    width:100%;
                }
            </style>
            <link rel='stylesheet' id='fontawesome-css'  href='<?php echo INBOUNDNOW_SHARED_URLPATH ;?>assets/fonts/fontawesome/css/font-awesome.min.css?ver=4.6.1' type='text/css' media='all' />
            <?php
        }

        /**
         * Load event data into static variables
         *
         **/
        public static function load_data() {
            global $wpdb;

            self::$range =  (isset($_REQUEST['range'])) ? intval($_REQUEST['range']) : 30;
            self::$page = (isset($_GET['page_number'])) ? (int) $_GET['page_number'] : 1;
            self::$limit = (isset($_GET['limit'])) ? (int) $_GET['limit'] : 50;
            self::$offset = (self::$page>1) ? (int) self::$page * self::$limit  : self::$limit;
            $dates = Inbound_Reporting_Templates::prepare_range( self::$range );
            self::$start_date = $dates['start_date'];
            self::$end_date = $dates['end_date'];
            self::$past_start_date = $dates['past_start_date'];
            self::$past_end_date = $dates['past_end_date'];
            self::$job_id = (isset($_REQUEST['job_id']) && $_REQUEST['job_id']) ? $wpdb->esc_like($_REQUEST['job_id']) : "%_%";

            /* get "current" email stats for the selected action */
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
                'event_name_2' => (isset($_REQUEST['event_name_2'])) ? sanitize_text_field($_REQUEST['event_name_2']) : false,
                'job_id' => self::$job_id,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'limit' => self::$limit,
                'offset' => self::$offset,
            );
            self::$events = self::get_email_event_stats($params);


            /* get "current" email stats for the selected action */
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'job_id' => self::$job_id,
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
                'event_name_2' => (isset($_REQUEST['event_name_2'])) ? sanitize_text_field($_REQUEST['event_name_2']) : false,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date
            );
            self::$graph_data['current'] = self::get_email_event_stats($params , true);

            /* calculate total events for pagination */
            self::$total_events = count(self::$graph_data['current']);

            /* get the action stats for the selected email's variations */
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'job_id' => self::$job_id,
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
            );
            self::$top_variations = self::get_top_email_variants($params);


            /* calculate total pages */
            self::$total_pages = self::$total_events / self::$limit;
            self::$total_pages = (self::$total_pages > 1) ? ceil(self::$total_pages) : 1;


            /* make labels for the possible events to query data for - for UI purposes */
            $params = array(
                'sparkpost_delivery' => __('Sends', 'inbound-pro'),
                'sparkpost_open' => __('Opens', 'inbound-pro'),
                'unopened' => __('Unopened Emails', 'inbound-pro'),
                'sparkpost_click' => __('Clicks', 'inbound-pro'),
                'sparkpost_bounce' => __('Bounces', 'inbound-pro'),
                'sparkpost_rejected' => __('Rejects', 'inbound-pro'),
                'inbound_unsubscribe' => __('Unsubscribes', 'inbound-pro'),
                'inbound_mute' => __('Mutes', 'inbound-pro'),
                'singular_form' => array(
                    'sparkpost_delivery' => __('Sent', 'inbound-pro'),
                    'sparkpost_open' => __('Opened', 'inbound-pro'),
                    'unopened' => __('Unopened', 'inbound-pro'),
                    'sparkpost_click' => __('Clicked', 'inbound-pro'),
                    'sparkpost_bounce' => __('Bounced', 'inbound-pro'),
                    'sparkpost_rejected' => __('Rejected', 'inbound-pro'),
                    'inbound_unsubscribe' => __('Unsubscribed', 'inbound-pro'),
                    'inbound_mute' => __('Muted', 'inbound-pro'),
                ),
                'lead_table' => array(
                    'sparkpost_delivery' => __('been sent an', 'inbound-pro'),
                    'sparkpost_open' => __('opened an', 'inbound-pro'),
                    'unopened' => __('not opened an', 'inbound-pro'),
                    'sparkpost_click' => __('clicked an', 'inbound-pro'),
                    'sparkpost_bounce' => __('bounced an', 'inbound-pro'),
                    'sparkpost_rejected' => __('rejected an', 'inbound-pro'),
                    'inbound_unsubscribe' => __('unsubscribed from an', 'inbound-pro'),
                    'inbound_mute' => __('muted an', 'inbound-pro'),
                ),
            );
            self::$possible_actions = $params;

		}

        /**
         * Formats the email action data into a form echarts can use
         */
        public static function prepare_chart_data( $start_date, $end_date, $period = 'current' ) {
            /* prepare empty dates */
            $dates = Inbound_Reporting_Templates::get_days_from_range($start_date,$end_date);

            /**if the graph to display is a type that maintains a running total, and adds or subtracts from that total**/
            if(isset($_REQUEST['standing_total_graph']) && $_REQUEST['standing_total_graph'] == true){
                /* create new temporary arrays with different structures */
                $temp = array();
                $temp_2 = array();
                $logged_ids = array();
                foreach (self::$graph_data[$period] as $key => $data) {
                    /*if the present data item should be removed from the running total*/
                    if(isset($data['item_to_remove'])){
                        if(!isset($logged_ids[$data['variation_id']]['removals'][$data['lead_id']])){
                            $logged_ids[$data['variation_id']]['removals'][$data['lead_id']] = 1;
                            $temp_2[substr($data['datetime'], 0, 10)][] = $data['id'];
                        }
                    }else{
                        if(!isset($logged_ids[$data['variation_id']]['additions'][$data['lead_id']])){
                            $logged_ids[$data['variation_id']]['additions'][$data['lead_id']] = 1;
                            $temp[substr($data['datetime'], 0, 10)][] = $data['id'];
                        }
                    }
                }
                $past_count = 0;
                $actions_array = array();
                $formatted = array();

                foreach ($dates as $index => $date) {

                    if (isset($temp[$date])) {
                        $formatted[$date]['date'] = $date;
                        $past_count += count($temp[$date]);
                        $actions_array[$index] = $past_count;
                    } else {
                        $formatted[$date]['date'] = $date;
                        $actions_array[$index] = $past_count;
                    }

                    if(isset($temp_2[$date])){
                        $remove_count = count($temp_2[$date]);
                        if($remove_count > $past_count){
                            $actions_array[$index] = 0;
                            $past_count = 0;
                        }else{
                            $actions_array[$index] -= $remove_count;
                            $past_count -= $remove_count;
                        }
                    }
                }

            }else{
                /* create new temporary array with different structure */
                $temp = array();
                $logged_ids = array();
                foreach (self::$graph_data[$period] as $key => $data) {
                    if(!isset($logged_ids[$data['variation_id']][$data['lead_id']])){
                        $logged_ids[$data['variation_id']][$data['lead_id']] = 1;
                        $temp[substr($data['datetime'], 0, 10)][] = $data['lead_id'];
                    }
                }

                $actions_array = array();
                $formatted = array();
                foreach ($dates as $index => $date) {
                    if (isset($temp[$date])) {
                        $formatted[$date]['date'] = $date;
                        $actions_array[] = count($temp[$date]);
                    } else {
                        $formatted[$date]['date'] = $date;
                        $actions_array[] = 0;
                    }
                }

            }

            return array( 'dates' => array_keys($formatted), 'actions_counted' => array_values($actions_array) );

        }

        /*
         * Gets the event stats for the email variations of the selected email
         * @params array(event_name, email_id)
         */
        public static function get_top_email_variants($args){
            global $wpdb;

            $events = array();  //for calculating unopened emails

            $table_name = $wpdb->prefix . 'inbound_events';

            /* to find out how many unopens there are we first have to query the opens,
             * then subtract those from the sent foreach variation */
            if($args['event_name'] == 'unopened'){
                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT `variation_id`, `lead_id` AS `lead_id` from {$table_name} " .
                        "WHERE `event_name` = 'sparkpost_open' " .
                        "AND `email_id` = '%d'".
                        "AND `job_id` LIKE '%s'"
                        , $args['email_id'] , $args['job_id']
                    ), ARRAY_A
                );

                /* make a list of all the variations a lead has opened */
                foreach($results as $key => $value){
                    $events[$value['lead_id']][$value['variation_id']] = 1;
                }

                /* change the event_name to get the sent */
                $args['event_name'] = 'sparkpost_delivery';
            }

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT `variation_id`, `lead_id` AS `lead_id` from {$table_name} " .
                    "WHERE `event_name` = '%s' " .
                    "AND `email_id` = '%d' " .
                    "AND `job_id` LIKE '%s' " .
                    "ORDER BY `datetime` ASC"
                    , $args['event_name'], $args['email_id'], $args['job_id']
                ), ARRAY_A
            );

            $variant_count = array();
            $logged_ids = array();

            /* count the number times a variation shows up in the results  */
            foreach($results as $key => $value){

                /* only log the unique times an action occured to a lead.
                 * If an email is sent to the same lead 3 times, still only 1 person is reached */
                if(!isset($logged_ids[$value['variation_id']][$value['lead_id']])){
                    $logged_ids[$value['variation_id']][$value['lead_id']] = 1;

                    if(!isset($events[$value['lead_id']][$value['variation_id']])){
                        @$variant_count[$value['variation_id']] += 1;
                    }
                }
            }

            /*sort the variations by value, greatest to smallest*/
            arsort($variant_count);

            return $variant_count;

        }

        /*
         * Gets email action stats for the selected email
         * @params: array(email_id, event_name, start_date, end_date)
         **/
        public static function get_email_event_stats($args , $ignore_limit = false){
            global $wpdb;

            /*change the event name to deliveries in order to get the unopened*/
            if($args['event_name'] == 'unopened'){
                $args['event_name'] = 'sparkpost_delivery';
            }

            $table_name = $wpdb->prefix . 'inbound_events';
            $query_pagination = "";

            /* look for distinct lead id unsubscribes if unsubscribe event */
            $query = "SELECT * from {$table_name} ";

            if($args['event_name'] == 'inbound_unsubscribe'){
                    $query = "SELECT distinct lead_id, email_id, datetime,event_details,variation_id  from (SELECT lead_id,email_id, datetime, event_details, variation_id from {$table_name} ";
                    $query .=   " WHERE `email_id` = '%d'" .
                                " AND `event_name` = '%s'" .
                                " AND `job_id` LIKE '%s'" .
                                " AND datetime >= '%s' AND datetime <= '%s'" .
                                " ORDER BY {$table_name} . `datetime` " .
                                " ) as filter GROUP BY lead_id, email_id,event_details,datetime,variation_id";
            } else {
                    $query = "SELECT * from {$table_name} ";
                    $query .=   " WHERE `email_id` = '%d'" .
                                " AND `event_name` = '%s'" .
                                " AND `job_id` LIKE '%s'" .
                                " AND datetime >= '%s' AND datetime <= '%s'" .
                                " ORDER BY {$table_name} . `datetime` " .
                                "";
            }

            /* add limit and offset if present */
            if (isset(self::$offset) && !$ignore_limit) {
                $query_pagination .= "  LIMIT ".self::$limit." OFFSET ".self::$offset."  ";
            }

            /* *
            error_log(sprintf(
                $query.$query_pagination,
                $args['email_id'], $args['event_name'], $args['job_id'], $args['start_date'], $args['end_date'], $args['end_date']
            ));
            /* */

           /* *
           echo sprintf(

               $query.$query_pagination,
               $args['email_id'], $args['event_name'], $args['job_id'], $args['start_date'], $args['end_date'], $args['end_date']
           );
           /**/

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    $query.$query_pagination,
                    $args['email_id'], $args['event_name'], $args['job_id'], $args['start_date'], $args['end_date'], $args['end_date']
                ), ARRAY_A
            );

            //echo $query.$query_pagination;
            //print_r($results);exit;


            /*if a second event is being queried for*/
            if($args['event_name_2'] != false && !empty($args['event_name_2'])){
                $two_events['event_one'] = $results;

                $query = "SELECT * from {$table_name} " .
                        " WHERE `email_id` = %d" .
                        " AND `job_id` = %s" .
                        " AND `event_name` = %s" .
                        " AND datetime >= %s AND datetime <= %s" .
                        " ORDER BY {$table_name} . `datetime` ASC";

                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        $query.$query_pagination,
                        $args['email_id'], $args['job_id'], $args['event_name_2'], $args['start_date'], $args['end_date']
                    ), ARRAY_A
                );

                $two_events['event_two'] = $results;

                return self::process_multiple_events($two_events, sanitize_text_field($_REQUEST['event_action']));
            }

            return $results;
        }

        /**
         * Processes multiple streams of lead data to return a single time line to display
         *
         */
        public static function process_multiple_events($event_array, $array_action){

            if($array_action === 'merge'){
                return array_merge($event_array['event_one'], $event_array['event_two']);
            }

            if($array_action === 'remove_opens'){
                foreach($event_array['event_two'] as $key => $data){
                    $event_array['event_two'][$key]['item_to_remove'] = 1;
                }
                return array_merge($event_array['event_one'], $event_array['event_two']);
            }

        }


    }

    new Inbound_Mailer_Stats_Report;
}
