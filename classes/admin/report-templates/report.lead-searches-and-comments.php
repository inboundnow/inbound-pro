<?php
/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */

if( !class_exists( 'Inbound_Search_And_Comment_Report' ) ){
    
    class Inbound_Search_And_Comment_Report extends Inbound_Reporting_Templates {
        
        static $range;
        static $page;
        static $limit;
        static $offset;
        static $total_events;
        static $total_pages;
        static $show_graph;
        static $graph_data;
        static $event_names;
        static $events;
        static $top_events;
        static $top_sources;
        static $start_date;
        static $end_date;
        static $chart_date_range;


        /**
         *  Load & display the template report
         *
         */
        public static function load_template() {
            self::load_data();

            self::display_header();
            self::print_css();
            parent::display_filters();
            self::display_all_events();
            self::display_chart();
            parent::js_lead_table_sort();
            die();
        }

        /**
         * Shows report header
         */
        public static function display_header() {

            $report_headline = (isset($_REQUEST['title'])) ? $_REQUEST['title'] : __( 'Actions' , 'inbound-pro' );

            if( isset($_REQUEST['page_id']) ) {
                $title = (isset($_REQUEST['page_id'])) ? get_the_title(intval($_REQUEST['page_id'])) : '';
                $permalink = get_the_permalink(intval($_REQUEST['page_id']));
            } else {
                $title = (isset($_REQUEST['lead_id'])) ? get_the_title(intval($_REQUEST['lead_id'])) : '';
                $permalink = "";
            }

            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';

            ?>
            <head>
                <script type="text/javascript" src="<?php echo INBOUND_PRO_URLPATH ;?>assets/libraries/echarts/echarts.min.js"  /></script>
            </head>
            <aside class="profile-card">

                <header>
                    <h1><?php echo $report_headline; ?></h1>
                    <h2><?php echo $title; ?></h2>
                    <?php
                    if ($permalink) {
                    ?>
                       <h3><a href="<?php echo $permalink; ?>" target="_self"><?php echo $permalink; ?></a></h3>
                    <?php
                    }
                    ?>
                </header>

                <!-- some social links to show off -->
                <ul class="profile-social-links">


                </ul>

            </aside>
            <?php
        }

        public static function display_chart() {

            if ( self::$show_graph === 'false' || empty( self::$total_events ) ) {
                return;
            }

            $event_name = ( isset( $_REQUEST['event_name'] ) ) ? sanitize_text_field( $_REQUEST['event_name'] ) : '';

            /* loop through  */
            ?>
            <h3 id="graph-header">
            <?php
                $label = Inbound_Events::get_event_label( $event_name , true);
                
                echo sprintf( __( '%s over the past %d days' ), $label, self::$chart_date_range );
            ?>
            </h3>
            <div id="graph-container" style='height:350px;'>
            </div>

            <script type="text/javascript">
                // based on prepared DOM, initialize echarts instance
                var myChart = echarts.init(document.getElementById( 'graph-container' ));

                // specify chart configuration item and data
                var option = {
                    title: {
                        text: ''
                    },
                    tooltip : {
                        trigger: 'axis'
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
                            data : <?php echo json_encode(self::$graph_data['dates']); ?>

                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        {
                            name : '<?php echo Inbound_Events::get_event_label( $event_name , true)  ?>',
                            type : 'line',/*55ddff , 55ff77*/
                            itemStyle : {normal: {color:'#55ddff', label:{show:false}}},
                            areaStyle : {normal: {color:'#55ddff', label:{show:true}}},
                            data : <?php echo json_encode(self::$graph_data[$event_name]['actions']); ?>
                        }
                    ]
                };

                // use configuration item and data specified to show chart
                myChart.setOption(option);
            </script>
            <?php
        }

        /**
         * Displays a table of the lead's comments or searches
         */
        public static function display_all_events() {
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';
            $lead_id = ( isset( $_REQUEST['lead_id'] ) ) ? intval( $_REQUEST['lead_id'] ) : '';

            if( self::$total_events == 0 || empty( self::$total_events ) ){
                if( $_REQUEST['event_name'] == 'search_made' ){
                    $user_action = 'searches';
                } else {
                    $user_action = 'comments';
                }
                
                ?>
                <div class="no-content-message">
                    <h2 class="no-content-text"><?php echo sprintf( __( 'It looks like the lead hasn\'t made any %s yet.', 'inbound-pro'), $user_action ); ?></h2>
                </div>
                    
                <?php
                return;
            }

            ?>
            <div class="visitors-stream-view">
                <table class="">
                    <thead>
                        <tr>
                            <th><?php _e( 'Avatar', 'inbound-pro' ); ?></th>
                            <th>
                                <?php _e( 'Lead Name', 'inbound-pro' ); ?>
                            </th>
                            <th>
                                <?php 
                                    if( $_REQUEST['event_name'] === 'search_made' ){
                                        _e( 'Search Details', 'inbound-pro' );
                                    }else{
                                        _e( 'Comment Details', 'inbound-pro' );
                                    }
                                ?>
                            </th>
                            <th>
                                <?php _e( 'URL', 'inbound-pro' ); ?>
                            </th>
                            <th class="sort-lead-report-by" sort-by="report-date-header" sort-order="1">
                                <?php _e( 'Date', 'inbound-pro' ); ?>
                                <i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="the-list">

                    <?php

                    $lead = get_post($lead_id);

                    $lead_exists = ($lead) ? true : false;

                    $lead_meta = get_post_meta($lead_id , '' ,  true);

                    $lead_meta['wpleads_first_name'][0] = ($lead_exists && isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __( 'n/a' , 'inbound-pro' );
                    $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_last_name'][0] : '';
                    $lead_name = $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0];
                    
                    $gravatar = ($lead_exists) ? Leads_Post_Type::get_gravatar($lead_id) : $default_gravatar;
                    
                    $action_number = 0;
                    foreach (self::$events as $key => $event) {
                        ?>
                        <tr id="post-98600" class="hentry lead-table-data-report-row" data-date-number="<?php echo $action_number;?>" >
                            <td class="lead-picture">
                                <?php
                                echo '<img class="lead-grav-img " width="40" height="40" src="' . $gravatar . '"  title="'. $lead_name .'">';
                                ?>
                            </td>
                            <td class="" >
                                <label>
                                <?php echo $lead_name; ?>
                                </label>
                            </td>
                            <td class="" >
                                <div id="wrapper">
                                    <div class="hoverme">
                                        <a href="" target="_self" style="cursor:pointer">
                                        <?php
                                        if( $event['event_name'] === 'search_made' ){
                                            $icon_type = 'fa-question';
                                        }else{
                                            $icon_type = 'fa-comments';
                                        }
                                        ?>
                                        <i class="fa <?php echo $icon_type; ?> inbound-tooltip detail-icon" aria-hidden="true"> </i>
                                        </a>
                                        <div class="pop">
                                            <div id="lead-action-tracking" class="wpleads-lead-action-tracking-table">
                                               <?php
                                                self::print_action_popup( $event );
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                            <td class="" >
                                <?php
                                if ( !empty( $event['event_details'] ) && $event['page_id'] !== '0' ) {
                                    $search_post = get_post( (int)$event['page_id'] );
                                   
                                    if( !empty( $search_post ) ){
                                        $end = ( strlen( $search_post->post_title ) > 35 ) ? '...' : '';
                                        echo '<a target="_blank" href="' . get_the_permalink( $search_post->ID ) . '">' . substr( $search_post->post_title, 0 , 35 ) . $end . '</a>';
                                    }else{
                                        _e( 'On a deleted post', 'inbound-pro' );
                                    }
                                    
                                } else {
                                    _e( 'On The Search Page' , 'inbound-pro' );
                                }
                                ?>
                            </td>
                            <td class="" >
                                <p class="mod-date"><em> <?php echo date_i18n("F j, Y, g:i a" , strtotime($event['datetime'])); ?>
                                    </em>
                                </p>
                            </td>
                        </tr>
                        <?php
                        $action_number++;
                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?php _e( 'Avatar', 'inbound-pro' ); ?></th>
                            <th>
                                <?php _e( 'Lead Name', 'inbound-pro' ); ?>
                            </th>
                             <th>
                                 <?php 
                                    if( $_REQUEST['event_name'] === 'search_made' ){
                                        _e( 'Search Details', 'inbound-pro' );
                                    }else{
                                        _e( 'Comment Details', 'inbound-pro' );
                                    }
                                ?>
                             </th>
                             <th>
                                 <?php _e( 'URL', 'inbound-pro' ); ?>
                             </th>
                            <th class="sort-lead-report-by" sort-by="report-date-header" sort-order="1">
                                <?php _e( 'Date', 'inbound-pro' ); ?>
                                <i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
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
            <?php
        }

        /**
         * Generate hidden popup containing the search or comment content
         * @param $event
         */
        public static function print_action_popup( $event ) {

             /* get details */
            $event['event_details'] = json_decode( $event['event_details'], true );
            $event['event_details'] = (is_array( $event['event_details'] )) ? $event['event_details'] : array();

             ?>
            <div class="session-item-holder">
                     <div class="popup-header">
                         <strong>
                            <?php   
                            if( $event['event_name'] === 'search_made' ){
                                _e( 'Search Details' , 'inbound-pro' );
                            }else{
                                _e( 'Comment Details' , 'inbound-pro' );
                            } ?>
                        </strong>
                    </div>
                    <?php
                    /* show source */
                    ?>
                    <div class="lp-page-view-item ">
                        <div class="path-left">
                            <span class="marker">
                             <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </span>
                            <?php
                            if ( !empty( $event['event_details'] ) && $event['page_id'] !== '0' ) {
                                $search_post = get_post( (int)$event['page_id'] );
                               
                                if( !empty( $search_post ) ){
                                    $end = ( strlen( $search_post->post_title ) > 35 ) ? '...' : '';
                                    echo '<a target="_blank" href="' . get_the_permalink( $search_post->ID ) . '">' . substr( $search_post->post_title, 0 , 35 ) . $end . '</a>';
                                }else{
                                    _e( 'On a deleted post', 'inbound-pro' );
                                }
                                
                            } else {
                                _e( 'On The Search Page' , 'inbound-pro' );
                            }
                            ?>
                        </div>
                        <div class="path-right">
                        </div>
                    </div>
                    <?php
                    $event['event_details'] = ($event['event_details']) ? $event['event_details'] : array();

                    if (!$event['event_details']) {
                    ?>
                    <div class="lp-page-view-item inbetween ">
                        <div class="path-left">
                            <?php _e( 'no data' , 'inbound-pro' ); ?>
                        </div>
                        <div class="path-right">
                            <span class="time-on-page">
                            </span>
                        </div>
                    </div>
                    <?php
                    }else{
                    ?>
                    <div class="lp-page-view-item inbetween ">
                        <div class="path-left details-content">
                            <?php  
                            /* For the purpose of extensibility, the query string is tokenized.
                             * |field| field_a |value| value_a |field| field_b |value| value_b
                             * First the string is split on the |field| token, so we get strings like: "field_a |value| value_a"
                             * Then each string is split on the |value| token so we get: the field name, and the field value.
                             *
                             * At the moment, only text from the search field is mapped.
                             **/

                                if( $event['event_name'] === 'search_made' ){
                                    /* split the search detail string into an array of (field name/field value) strings */
                                    $search_details = explode( '|field|', $event['event_details']['search_data'] );
                                    
                                    foreach($search_details as $field_details){
                                        /* split the name/value string into an array of (field name, field value) */
                                         $field_details = explode( '|value|', $field_details );
                                        
                                        /* if there is a search query */
                                        if(!empty($field_details[1])){
                                            
                                            /* since only the seach input is mapped, only output text from it */
                                            if($field_details[0] === 'search_text'){
                                                    echo '<p class="search-query-details">'  . sprintf( __('Search Query:  %s', 'inbound-pro' ), $field_details[1] ) . '</p>';
                                                }else{
                                                    echo '<p class="search-query-details">'  . __('No search saved. The search was made, but it wasn\'t logged for some reason.', 'inbound-pro' ) . '</p>';
                                                }
                                        }else{
                                            echo '<p class="search-query-details">'  . __('No search saved. The search was made, but it wasn\'t logged for some reason.', 'inbound-pro' ) . '</p>';
                                        }
                                    }
                                    
                                }elseif( $event['event_name'] === 'comment_made' ){
                                    /* format the comment so it looks like the posted comment*/
                                    echo apply_filters( 'the_content', $event['event_details']['comment_content'] );
                                }
                            ?>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
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

                .report-filters{
                    margin-bottom: 14px;
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

                hr {
                    margin-top:10px;
                    margin-left:10px;
                    margin-right:10px;
                    pacity:.2;
                }
                
                .no-content-message{
                    height: 100%;
                    width: 100%;
                    background: url(<?php echo INBOUND_PRO_URLPATH; ?>assets/images/empty-report-background.png) center no-repeat;
                    -webkit-background-size: cover;
                    -moz-background-size: cover;
                    -o-background-size: cover;
                    background-size: cover;
                }
                
                .no-content-text{
                    padding-top: 100px;
                    text-align: center;
                }
                
                .visitors-stream-view {
                    margin-left:10px;
                    margin-right:10px;
                    margin-bottom:44px;
                }

                #graph-header{
                    text-align: center;
                }

                #graph-container {
                    margin-top:10px;
                    margin-left:auto;
                    margin-right:auto;
                }

                .top-ten-viewers td, .top-ten-sources td {
                    height:40px;
                }

                .hoverme{
                  margin: auto;
                  outline: 0;
                  cursor: pointer;
                }


                .hoverme:hover > .pop{
                    opacity: 1;
                    z-index: 99999;
                }

                .pop {
                    opacity: 0;
                    width: 315px;
                    z-index: -1;
                    margin: auto;
                    transition: all .3s ease;
                    text-align:left;
                    position:absolute;
                    padding:20px 0px 20px 20px;
                    margin-left: 62px;
                    margin-top: -33px;
                    cursor:default;
                    overflow: auto;
                    max-height: 100%;
                }

                .visitors-stream-view .detail-icon {
                    font-size: 12px;
                }

                .lp-page-view-item {
                    display: inline-block;
                    width: 260px;
                    padding: 12px 20px;
                    position: relative;
                    font-size: 13px;
                    margin: 0;
                }
                .lp-page-view-item:not(:last-child) {
                    border-bottom: 1px solid #EBEBEA;
                }


                #lead-action-tracking {
                    background-color:#fff;
                    font-size: 12px;
                    margin-bottom: 10px;
                    text-align: left;
                    border: 1px solid #CECDCA;
                    -webkit-box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    -moz-box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    z-index: 200;
                }

                #lead-action-tracking th {
                    font-size: 14px;
                    font-weight: normal;
                    color: #039;
                    padding: 10px 8px;
                    border-bottom: 2px solid #6678b1;
                }

                #lead-action-tracking td {
                    color: #669;
                    padding: 9px 8px 0px 8px;
                }

                #lead-action-tracking tbody tr:hover td {
                    color: #009;
                }

                .session-item-holder {
                    background-color:#fff;
                    cursor:default;
                    padding-top:5px;
                    padding-bottom:5px;
                    padding-right:5px;
                    padding-left:5px;
                    overflow: hidden;
                }

                .pop .marker {
                    margin-right:5px;
                }

                .fa-map-marker {
                    padding-left:3px;
                    color:aquamarine;
                }

                .fa-crosshairs {
                    color:mediumpurple;
                }

                .pop .header, .pop .footer {
                    height:10px;
                    background-color:white;
                }

                .inbetween {
                   font-size:11px;
                }


                .popup-header {
                    padding-top:10px;
                    width:100%;
                    text-align:center;
                }
                
                tr:nth-child(even) {
                    background-color: #f1f1f1;
                }
                
                .details-content {
                    background:#f5f5f5; 
                    padding:8px; 
                    border-radius: 5px;
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
         */
        public static function load_data() {

            $event_name = (isset( $_REQUEST['event_name'] )) ? sanitize_text_field( $_REQUEST['event_name'] ) : '';
            
            if( empty( $event_name ) ){
                return;
            }
            
            /* build timespan for analytics report */
            self::$range =  (!isset($_REQUEST['range'])) ? intval($_REQUEST['range']) : 10000;
            self::$page = (isset($_GET['page_number'])) ? (int) $_GET['page_number'] : 1;
            self::$limit = (isset($_GET['limit'])) ? (int) $_GET['limit'] : 50;
            self::$offset = (self::$page>1) ? (int) self::$page * self::$limit : 0;

            $dates = Inbound_Reporting_Templates::prepare_range( self::$range );
            self::$start_date = $dates['start_date'];
            self::$end_date = $dates['end_date'];
            self::$show_graph = (isset( $_REQUEST['show_graph'] )) ? $_REQUEST['show_graph'] : false;

            /* get the stored data, either searches or comments */  
            $params = array(
                'event_name' => $event_name,
                'page_id' => (isset($_REQUEST['page_id'])) ? intval( $_REQUEST['page_id'] ) : '',
                'lead_id' => (isset($_REQUEST['lead_id'])) ? intval( $_REQUEST['lead_id'] ) : '',
                'list_id' => (isset($_REQUEST['list_id'])) ? intval( $_REQUEST['list_id'] ) : '',
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field( urldecode( $_REQUEST['source'] ) ) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'group_by' => (isset( $_REQUEST['group_by'] )) ? intval( $_REQUEST['group_by'] ) : '',
                'limit' => self::$limit,
                'offset' => self::$offset,
            );

            self::$events = Inbound_Events::get_events( $params );

            /* calculate total events for pagination */
            self::$total_events = Inbound_Events::get_total_activity( $params['lead_id'], $params['event_name'] );

            /* calculate total pages */
            self::$total_pages = self::$total_events / self::$limit;
            self::$total_pages = (self::$total_pages > 1) ? ceil( self::$total_pages ) : 1;

            if ( self::$show_graph === 'false' ) {
                return;
            }

            self::$chart_date_range = (isset( $_REQUEST['chart_date_range'] )) ? intval( $_REQUEST['chart_date_range'] ) : 90 ;
            
            $dates = Inbound_Reporting_Templates::prepare_range( self::$chart_date_range );
            $chart_start_date = $dates['start_date'];
            $chart_end_date = $dates['end_date'];            

            /* get action counts */
            $params = array(
                'lead_id' => (isset( $_REQUEST['lead_id'] )) ? intval( $_REQUEST['lead_id'] ) : '',
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'event_name' => $event_name,
            );
            
            self::$graph_data[$event_name] = self::get_comment_or_search_count( $params );
            self::$graph_data[$event_name] = self::prepare_chart_data( $chart_start_date, $chart_end_date, $event_name );
            self::$graph_data['dates'] = self::$graph_data[$event_name]['dates'];
            

        }

        /**
         * Gets the lead's searches or comments for use in the chart display
         */
        public static function get_comment_or_search_count( $params ){
            global $wpdb;
            
            if( empty( $params['lead_id'] ) ){
                return '';
            }
            
            $table_name = $wpdb->prefix . 'inbound_events';
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT %s as event_name, datetime as date ' .
                    'FROM ' . $table_name . ' WHERE `lead_id` = %d ' .
                    'AND `event_name` = %s ' .
                    'AND datetime >= %s AND  datetime <= %s',
                    $params['event_name'], $params['lead_id'], $params['event_name'], $params['start_date'], $params['end_date']
                ), 'ARRAY_A'
            );
               
            return $results;
        }

        /**
         * Prepare the queried data for display in the chart
         */
        public static function prepare_chart_data( $start_date, $end_date , $event_name ) {
            /* prepare empty dates */
            $dates = Inbound_Reporting_Templates::get_days_from_range( $start_date, $end_date );

            /* create new temporary array with different structure */
            $temp = array();
            foreach ( self::$graph_data[$event_name] as $key=> $data ) {
                $short_date = substr( $data['date'], 0, 10 );
                $temp[$short_date][] = $data['event_name'];
            }

            $events_array = array();
            $formatted = array();
            foreach ( $dates as $index => $date ) {
                if (isset($temp[$date])) {
                    $formatted[$date]['date'] = $date;
                    $events_array[] = count( $temp[$date] );
                } else {
                    $formatted[$date]['date'] = $date;
                    $events_array[] = 0;
                }
            }

            return array( 'dates' => array_keys( $formatted ), 'actions' => array_values( $events_array ) );
        }
    }

    new Inbound_Search_And_Comment_Report;

}
