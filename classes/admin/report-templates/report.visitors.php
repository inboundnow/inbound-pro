<?php

/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */

if (!class_exists('Inbound_Visitors_Report')) {

    class Inbound_Visitors_Report extends Inbound_Reporting_Templates {

        static $range;
        static $obj_key;
        static $obj_id;
        static $graph_data;
        static $visits;
        static $top_visitors;
        static $top_sources;
        static $start_date;
        static $end_date;
        static $past_start_date;
        static $past_end_date;


        /**
         *  Create range static variable based on REQUEST data or set default to 30 days
         */
        public static function define_static_variables() {
            if (!isset($_REQUEST['range'])) {
                self::$range = 30;
            } else {
                self::$range = intval($_REQUEST['range']);
            }

            /* get target object */
            self::$obj_key =  (isset($_GET['obj_key'])) ? sanitize_text_field($_GET['obj_key']) : 'page_id';
            self::$obj_id =  (isset($_GET[self::$obj_key])) ? (int) $_GET[self::$obj_key] : 0;


            $dates = Inbound_Reporting_Templates::prepare_range( self::$range );
            self::$start_date = $dates['start_date'];
            self::$end_date = $dates['end_date'];
            self::$past_start_date = $dates['past_start_date'];
            self::$past_end_date = $dates['past_end_date'];
        }

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
            self::display_top_widgets();
            self::display_all_visitors();
            parent::js_lead_table_sort();
            die();
        }

        /**
         * Shows report header
         */
        public static function display_header() {

            $title = get_the_title(self::$obj_id);
            $permalink = get_the_permalink(self::$obj_id);
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';

            ?>
            <head>
                <script type="text/javascript" src="<?php echo INBOUND_PRO_URLPATH ;?>assets/libraries/echarts/echarts.min.js"  /></script>
            </head>
            <aside class="profile-card">

                <header>
                    <h1><?php _e('Visitors' , 'inbound-pro'); ?></h1>
                    <h2><?php echo $title; ?></h2>
                    <h3><a href="<?php echo $permalink; ?>" target="_self"><?php echo $permalink; ?></a></h3>
                </header>

                <!-- some social links to show off -->
                <ul class="profile-social-links">


                </ul>

            </aside>
            <?php
        }

        public static function display_chart() {

            self::$graph_data['current']= self::prepare_chart_data(self::$start_date, self::$end_date , 'current');
            self::$graph_data['past']= self::prepare_chart_data(self::$past_start_date, self::$past_end_date , 'past');


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
                        data:['<?php echo sprintf( __('Visitors past %s days','inbound-pro') , self::$range ); ?>',
                            '<?php echo sprintf( __('Visitors prior %s days','inbound-pro') , self::$range ); ?>']
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
                            name:'<?php echo sprintf( __('Visitors past %s days','inbound-pro') , self::$range ); ?>',
                            type:'line',/*55ddff , 55ff77*/
                            itemStyle: {normal: {color:'#55ddff', label:{show:false}}},
                            areaStyle: {normal: {color:'#55ddff', label:{show:true}}},
                            data:<?php echo json_encode(self::$graph_data['current']['visits']); ?>

                        },
                        {
                            name:'<?php echo sprintf( __('Visitors prior %s days','inbound-pro') , self::$range ); ?>',
                            type:'line', /* 3d3d3d , 6655ff */
                            itemStyle: {normal: {color:'#6655ff', label:{show:true}}},
                            areaStyle: {normal: {color:'#6655ff', label:{show:true}}},
                            data:<?php echo json_encode(self::$graph_data['past']['visits']); ?>

                        }
                    ]
                };

                // use configuration item and data specified to show chart
                myChart.setOption(option);
            </script>
            <?php
        }

        /**
         * Displays 'top 10' lists included in this report
         */
        public static function display_top_widgets() {
            ?>

            <div class="flexbox-container top-10-widgets">
                <div>
                    <h3><?php _e( 'Top Visitors' , 'inbound-pro' ); ?></h3>
                    <table class="top-ten-visitors">
                        <thead>
                        <tr>
                            <th scope="col" class="">
                                #
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Visitor' , 'inbound-pro'); ?></span>
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Impressions' , 'inbound-pro'); ?></span>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="">

                        <?php

                        $i = 1;
                        foreach (self::$top_visitors as $key => $event) {
                            $lead = get_post($event['lead_id']);

                            $lead_exists = ($lead) ? true : false;

                            if ($event['lead_id']) {
                                $lead_meta = get_post_meta($event['lead_id'], '', true);
                            }

                            $lead_meta['wpleads_first_name'][0] = ($lead_exists && isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __('Anonymous','inbound-pro');
                            $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_last_name'][0] : '';

                            ?>
                            <tr id="" class="">
                                <td class="">
                                    <span class="top-count-num"><?php echo $i; ?></span>
                                </td>
                                <td class="">
                                    <?php
                                    if ($lead_exists) {
                                        ?>
                                        <a href="<?php echo 'post.php?action=edit&post=' . $event['lead_id'] . '&amp;small_lead_preview=true&tb_hide_nav=true'; ?>" target="_self">
                                            <?php echo $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0]; ?>
                                        </a>
                                        <?php
                                    } else {
                                        echo $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0];
                                    }
                                    ?>
                                </td>
                                <td class="" >
                                    <a href="<?php echo admin_url('index.php?action=inbound_generate_report&class=Inbound_Visitor_Report&'
                                                                    .($lead_exists ? 'lead_uid='. $event['lead_uid'] : 'lead_uid='.$event['lead_uid'] )
                                                                    .(isset($_REQUEST['source']) ? '&source='. urlencode(sanitize_text_field($_REQUEST['source'])) : '' )
                                                                    . '&'.self::$obj_key.'='.self::$obj_id
                                                                    .'&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1503&height=400'); ?>" target="_self">
                                        <?php echo $event['count']; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        }
                        ?>
                        </tbody>


                    </table>

                </div>
                <div>
                    <h3><?php _e( 'Top Sources' , 'inbound-pro' ); ?></h3>

                    <table class="top-ten-sources">
                        <thead>
                        <tr>
                            <th scope="col" class="">
                                #
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Source' , 'inbound-pro'); ?></span>
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Visitors' , 'inbound-pro'); ?></span>
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Impressions' , 'inbound-pro'); ?></span>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="">

                        <?php

                        $i = 1;
                        foreach (self::$top_sources as $key => $event) {

                            ?>
                            <tr id="" class="">
                                <td class="">
                                    <span class="top-count-num"><?php echo $i; ?></span>
                                </td>
                                <td class="">
                                    <?php

                                    if (strstr( $event['source'] , 'http' ) ) {
                                        $end = ( strlen($event['source']) > 65 ) ? '...' : '';
                                        echo '<a target="_blank" href="'.$event['source'].'">'. substr($event['source'], 0 , 65).$end .'</a>';
                                    } else {
                                       echo ($event['source']) ? $event['source']: __('Direct Traffic' , 'inbound-pro') ;
                                    }

                                    ?>
                                </td>
                                <td class="" >
                                    <a target="_self" href="<?php echo admin_url('index.php?action=inbound_generate_report&'.self::$obj_key.'='.self::$obj_id.'&source='.urlencode($event['source']).'&class=Inbound_Visitors_Report&range='.self::$range.'&tb_hide_nav=true'); ?>" class="">
                                        <?php echo $event['visitors']; ?>
                                    </a>
                                </td>
                                <td class="" >
                                    <?php echo $event['page_views_total']; ?>
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
         * Displays a table of visitors that have visited the contentitem under review
         */
        public static function display_all_visitors() {
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';
            ?>
            <div class="visitors-stream-view">
            <h3><?php _e( 'Stream' , 'inbound-pro' ); ?></h3>
            <table class="">
                <thead>
                <tr>
                    <th scope="col" class=" column-lead-picture">

                    </th>
                    <th scope="col" class="sort-lead-report-by" sort-by="report-name-field-header">
                        <span><?php _e('Visitor' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Source' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="sort-lead-report-by" sort-by="report-impressions-header">
                        <span><?php _e('Impressions' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="sort-lead-report-by" sort-by="report-lead-id-header">
                        <span><?php _e('Lead' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="sort-lead-report-by" sort-by="report-date-header" sort-order="1">
                        <span><?php _e('Last Visit' , 'inbound-pro'); ?>
                            <i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                        </span>
                    </th>
                </tr>
                </thead>
                <tbody id="the-list">

                <?php
                $date_number = 0;
                foreach (self::$visits as $key => $event) {
                    $lead = get_post($event['lead_id']);

                    $lead_exists = ($lead) ? true : false;

                    $lead_meta = get_post_meta($event['lead_id'] , '' ,  true);

                    $lead_meta['wpleads_first_name'][0] = ($lead_exists && isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __('n/a' , 'inbound-pro');
                    $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_last_name'][0] : '';
                    $lead_name = $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0];
                    ?>
                    <tr id="post-98600" class="hentry lead-table-data-report-row"  data-name-field="<?php echo $lead_name; ?>" data-lead-id="<?php echo $event['lead_id']; ?>" data-impressions="<?php echo $event['count']; ?>" data-date-number="<?php echo $date_number;?>" >
                        <td class="lead-picture">
                            <?php
                            $gravatar = ($lead_exists) ? Leads_Post_Type::get_gravatar($event['lead_id']) : $default_gravatar;
                            echo '<img class="lead-grav-img " width="40" height="40" src="' . $gravatar . '">';
                            ?>
                        </td>
                        <td class="" >
                            <?php
                            if ( $lead_exists ) {
                                ?>
                                <a href="<?php echo'post.php?action=edit&post=' . $event['lead_id'] . '&amp;small_lead_preview=true&tb_hide_nav=true'; ?>" target="_self">
                                    <?php echo $lead_name; ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <i class="fa fa-hourglass-half" aria-hidden="true">

                                <?php
                            }
                            ?>
                        </td>
                        <td class="" >
                            <?php

                            if (strstr( $event['source'] , 'http' ) ) {
                                $end = ( strlen($event['source']) > 65 ) ? '...' : '';
                                echo '<a target="_blank" href="'.$event['source'].'">'. substr($event['source'], 0 , 65).$end .'</a>';
                            } else {
                                echo ($event['source']) ? $event['source']: __('Direct Traffic' , 'inbound-pro') ;
                            }

                            ?>
                        </td>                        
                        <td class="" >
                            <a href="<?php echo admin_url('index.php?action=inbound_generate_report&class=Inbound_Visitor_Event_Report&'.($lead_exists ? 'lead_uid=' . $event['lead_uid'] : 'lead_uid='.$event['lead_uid'] ) . '&'.self::$obj_key.'='.self::$obj_id.'&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1503&height=400'); ?>" target="_self">
                                <?php echo $event['count']; ?>
                            </a>
                        </td>
                        <td class="">
                            <a href="<?php echo ($lead_exists) ? 'post.php?action=edit&post=' . $event['lead_id'] . '&amp;small_lead_preview=true&tb_hide_nav=true' : '#'  ; ?>" target="_self">
                                <i <?php echo ( $lead_exists ) ? 'class="fa fa-user inbound-tooltip" title="'.__('Lead exists. Click to view.','inbound-pro').'"' : 'class="fa fa-hourglass-half" title="'.__('Lead does not exist in database yet.','inbound-pro').'"'; ?>" aria-hidden="true">

                                </i>
                            </a>
                        </td>
                        <td class="" >
                            <p class="mod-date"><em> <?php echo date("F j, Y, g:i a" , strtotime($event['datetime'])); ?>
                                </em>
                            </p>
                        </td>
                    </tr>
                    <?php
                    $date_number++;
                }
                ?>
                </tbody>

                <tfoot>
                <tr>
                    <th scope="col" class=" column-lead-picture">
                    </th>

                    <th scope="col" class=" column-first-name  desc sort-lead-report-by" sort-by="report-name-field-header">
                        <span><?php _e('Visitor' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="  desc">
                        <span><?php _e('Last Source' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class=" column-title column-primary  desc sort-lead-report-by" sort-by="report-impressions-header">
                        <span><?php _e('Impressions' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="  desc sort-lead-report-by" sort-by="report-lead-id-header">
                        <span><?php _e('Lead' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="  desc sort-lead-report-by" sort-by="report-date-header">
                        <span><?php _e('Last Visit' , 'inbound-pro'); ?><i class="fa fa-sort-desc" aria-hidden="true"></i></span>
                    </th>
                </tr>
                </tfoot>

            </table>
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
                    width: 50%;
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

                .top-ten-visitors td, .top-ten-sources td {
                    height:40px;
                }

            </style>
            <link rel='stylesheet' id='fontawesome-css'  href='<?php echo INBOUNDNOW_SHARED_URLPATH ;?>assets/fonts/fontawesome/css/font-awesome.min.css?ver=4.6.1' type='text/css' media='all' />
            <?php
        }

        /**
         * Load event data into static variables
         *
         */
        public static function load_data() {
            /* build timespan for analytics report */
            self::define_static_variables();

            /* get daily visitor counts - group by lead_uid */
            $params = array(
                self::$obj_key => self::$obj_id,
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date
            );
            self::$graph_data['current'] = Inbound_Events::get_visitors_by_dates($params);

            /* get daily visitor counts - group by lead_uid */
            $params = array(
                self::$obj_key => self::$obj_id,
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$past_start_date,
                'end_date' => self::$past_end_date
            );
            self::$graph_data['past'] = Inbound_Events::get_visitors_by_dates($params);

            /* get all visitors - group by lead_uid */
            $params = array(
                self::$obj_key => self::$obj_id,
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date
            );
            self::$visits = Inbound_Events::get_visitors($params);

            /* get top 10 visitors */
            $params = array(
                self::$obj_key => self::$obj_id,
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'order_by' => 'count desc',
                'group_by' => 'session_id',
                'limit' => 10
            );
            self::$top_visitors = Inbound_Events::get_visitors($params);

            /* get top 10 sources */
            $params = array(
                self::$obj_key => self::$obj_id,
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'order_by' => 'visitors desc',
                'limit' => 10
            );
            self::$top_sources = Inbound_Events::get_visitors_group_by_source($params);
		}

        /**
         *
         */
        public static function prepare_chart_data( $start_date, $end_date , $period = 'current' ) {
            /* prepare empty dates */
            $dates = Inbound_Reporting_Templates::get_days_from_range($start_date,$end_date);

            /* create new temporary array with different structure */
            $temp = array();
            foreach (self::$graph_data[$period] as $key=> $data) {
                $temp[$data['date']] = $data['visitors'];
            }

            $date_array = array();
            $visits_array = array();
            $formatted = array();
            foreach ($dates as $index => $date) {
                if (isset($temp[$date])) {
                    $formatted[$date]['date'] = $date;
                    $formatted[$date]['visits'] = $temp[$date];
                    $date_array[$date] = $date;
                    $visits_array[]= $temp[$date];
                } else {
                    $formatted[$date]['date'] = $date;
                    $formatted[$date]['visits'] = 0;
                    $date_array[$date] = $date;
                    $visits_array[]= 0;
                }
            }

           return array( 'data' => $formatted, 'dates' => array_keys($formatted), 'visits' => array_values($visits_array));
        }
    }

    new Inbound_Visitors_Report;

}
