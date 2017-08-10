<?php

/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */


if (!class_exists('Inbound_Events_Report')) {

    class Inbound_Events_Report extends Inbound_Reporting_Templates {

        static $range;
        static $show_graph;
        static $graph_data;
        static $event_names;
        static $events;
        static $top_events;
        static $top_sources;
        static $start_date;
        static $end_date;
        static $exclude_events;


        /**
         *  Create range static variable based on REQUEST data or set default to 30 days
         */
        public static function define_range() {
            if (!isset($_REQUEST['range'])) {
                self::$range = 30;
            } else {
                self::$range = intval($_REQUEST['range']);
            }
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
            //self::display_action_breakdown();
            self::display_all_events();
            self::display_chart();
            die();
        }

        /**
         * Shows report header
         */
        public static function display_header() {

            $report_headline = (isset($_REQUEST['title'])) ? $_REQUEST['title'] : __('Actions' , 'inbound-pro');

            if( isset($_REQUEST['page_id']) ) {
                $title = (isset($_REQUEST['page_id'])) ? get_the_title(sanitize_text_field($_REQUEST['page_id'])) : '';
                $permalink = get_the_permalink(sanitize_text_field($_REQUEST['page_id']));
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

            if (self::$show_graph === 'false') {
                return;
            }

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
                        data:[<?php
                                $i = 0;
                                foreach(self::$event_names as $name) {
                                    echo "'" . Inbound_Events::get_event_label( $name['event_name'] , true) ."'" . (isset(self::$event_names[$i+1]) ? ',' : '');
                                    $i++;
                                }
                                ?>]
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

                    <?php
                        $i = 0;
                        foreach(self::$event_names as $event) {
                            /* account for events related to CTA conversion */
                            if (isset($_REQUEST['cta_id'])) {
                                if ($event['event_name'] != 'inbound_cta_click' && !strstr($event['event_name'] , 'form_submission')) {
                                    continue;
                                }
                            }
                            ?>{
                                name:'<?php echo Inbound_Events::get_event_label( $event['event_name'] , true)  ?>',
                                type:'line',/*55ddff , 55ff77*/
                                itemStyle: {normal: {color:'#55ddff', label:{show:false}}},
                                areaStyle: {normal: {color:'#55ddff', label:{show:true}}},
                                data:<?php echo json_encode(self::$graph_data[$event['event_name']]['actions']); ?>
                            }
                            <?php
                            echo (isset(self::$event_names[$i+1])) ? ',' : '';
                            $i++;
                        }
                        ?>
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
        public static function display_action_breakdown() {
            ?>

            <div class="flexbox-container ">
                <div>
                    <h3><?php _e( 'Actions' , 'inbound-pro' ); ?></h3>
                    <table class="top-actions">
                        <thead>
                        <tr>
                            <th scope="col" class="">
                                <span><?php _e('Event Name' , 'inbound-pro'); ?></span>
                            </th>
                            <th scope="col" class="">
                                <span><?php _e('Action Count' , 'inbound-pro'); ?></span>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="">

                        <?php

                        $i = 1;
                        foreach (self::$top_events as $key => $event) {

                            ?>
                            <tr id="" class="">
                                <td class="">
                                    <?php
                                    echo Inbound_Events::get_event_label($event['event_name']);
                                    ?>
                                </td>
                                <td class="" >
                                    <a href="<?php echo admin_url('index.php?action=inbound_generate_report&class=Inbound_Visitor_Event_Report&'
                                                                    .($lead_exists ? 'lead_uid='. $event['lead_uid'] : 'lead_uid='.$event['lead_uid'] )
                                                                    .(isset($_REQUEST['source']) ? '&source='. urlencode(sanitize_text_field($_REQUEST['source'])) : '' )
                                                                    . '&page_id='.sanitize_text_field($_REQUEST['page_id'])
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
            </div>
            <?php
        }


        /**
         * Displays a table of visitors that have visited the contentitem under review
         */
        public static function display_all_events() {
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';
            ?>
            <div class="visitors-stream-view">
            <h3><?php /* _e( 'Stream' , 'inbound-pro' ); */ ?></h3>
            <table class="">
                <tbody id="the-list">

                <?php

                foreach (self::$events as $key => $event) {
                    $lead = get_post($event['lead_id']);

                    $lead_exists = ($lead) ? true : false;

                    $lead_meta = get_post_meta($event['lead_id'] , '' ,  true);

                    $lead_meta['wpleads_first_name'][0] = ($lead_exists && isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __('n/a' , 'inbound-pro');
                    $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_last_name'][0] : '';
                    ?>
                    <tr id="post-98600" class="hentry">
                        <td class="lead-picture">
                            <?php
                            $gravatar = ($lead_exists) ? Leads_Post_Type::get_gravatar($event['lead_id']) : $default_gravatar;
                            echo '<img class="lead-grav-img " width="40" height="40" src="' . $gravatar . '"  title="'.$lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0] .'">';
                            ?>
                        </td>
                        <td class="" >
                            <label>
                            <?php echo $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0]; ?>
                            </label>
                            <p>
                            <?php
                            echo Inbound_Events::get_event_label( $event['event_name'] , false );
                            ?>
                            </p>
                            <p>
                            <?php
                            $capture = Inbound_Events::get_event_capture_data( $event );
                            echo  ($capture['link'] != '#' ) ? '<a target="_black" href="'.$capture['link'].'">'.$capture['title'].'</a>' : '';
                            ?>
                            </p>
                        </td>
                        <td class="" >
                            <?php
                            if ($event['lead_id']) {
                            ?>
                             <a href="<?php echo 'post.php?action=edit&post=' . $event['lead_id'] . '&amp;small_lead_preview=true&tb_hide_nav=true'   ; ?>" title="<?php echo $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0]; ?>" target="_self">
                                <i class="fa fa-user inbound-tooltip" aria-hidden="true"></i>
                             </a>
                            <?php
                            } else {
                                ?>
                                <i class="fa fa-hourglass-half inbound-tooltip" aria-hidden="true"></i>
                                <?php
                            }
                            ?>

                        </td>
                        <td class="" >
                            <div id="wrapper">
                                <div class="hoverme">
                                    <a href="" target="_self" style="cursor:pointer">
                                    <i class="fa fa-search inbound-tooltip" aria-hidden="true"> </i>
                                    </a>
                                    <?php
                                    self::print_popup( $event , $capture );
                                    ?>
                                </div>
                            </div>

                        </td>
                        <td class="" >
                            <?php

                            if (strstr( $event['source'] , 'http' ) ) {
                                $end = ( strlen($event['source']) > 35 ) ? '...' : '';
                                echo '<a target="_blank" href="'.$event['source'].'">'. substr($event['source'], 0 , 35).$end .'</a>';
                            } else {
                                echo ($event['source']) ? $event['source']: __('Direct Traffic' , 'inbound-pro') ;
                            }

                            ?>
                        </td>
                        <td class="">
                        </td>
                        <td class="" >
                            <p class="mod-date"><em> <?php echo date("F j, Y, g:i a" , strtotime($event['datetime'])); ?>
                                </em>
                            </p>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            </div>
            <?php
        }

        /**
         * Generate hidden popup containing information about funnel
         * @param $event
         * @param $capture contains details about the event capture device eg: form, cta
         */
        public static function print_popup( $event , $capture ) {
            ?>
            <div class="pop">
                <div id="conversion-tracking" class="wpleads-conversion-tracking-table">
                   <?php
                    self::print_popup_funnel(  $event , $capture );
                    self::print_popup_lists(  $event , $capture );
                    self::print_popup_event_details(  $event , $capture );
                    ?>
                </div>
            </div>
          <?php
        }

        public static function print_popup_funnel(  $event , $capture ) {
            $exclude = array('inbound_list_add','sparkpost_delivery');

            if (in_array($event['event_name'], $exclude)) {
                return;
            }

             /* get funnel */
            $event['funnel'] = json_decode($event['funnel'],true);
            $event['funnel'] = (is_array($event['funnel'])) ? $event['funnel'] : array();

             ?>
            <div class="session-item-holder">
                     <div class="popup-header"><strong><?php _e('Traffic Funnel' , 'inbound-pro'); ?></strong></div>
                    <?php
                    /* show source */
                    ?>
                    <div class="lp-page-view-item ">
                        <div class="path-left">
                            <span class="marker">
                             <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </span>
                            <?php
                            if (strstr( $event['source'] , 'http' ) ) {
                                $end = ( strlen($event['source']) > 35 ) ? '...' : '';
                                echo '<a target="_blank" href="'.$event['source'].'">'. substr($event['source'], 0 , 35).$end .'</a>';
                            } else {
                                echo ($event['source']) ? $event['source']: __('Direct Traffic' , 'inbound-pro') ;
                            }
                            ?>
                        </div>
                        <div class="path-right">
                        </div>
                    </div>
                    <?php

                    $count = 1;
                    $event['funnel'] = ($event['funnel']) ? $event['funnel'] : array();

                    if (!$event['funnel']) {
                        ?>
                        <div class="lp-page-view-item inbetween ">
                            <div class="path-left">
                                <?php _e('no data' , 'inbound-pro'); ?>
                            </div>
                            <div class="path-right">
                                <span class="time-on-page">
                                </span>
                            </div>
                        </div>
                        <?php
                    }

                    foreach($event['funnel'] as $page_id) {

                        if (!$page_id) {
                            continue;
                        }

                        if (strpos($page_id, 'cat_') !== false) {
                            $page_type = __('Category' , 'inbound-pro');
                            $cat_id = str_replace("cat_", "", $page_id);
                            $page_title = get_cat_name($cat_id) . " ". _e('Category Page' , 'inbound-pro');
                            $tag_names = '';
                            $page_permalink = get_category_link($cat_id);

                        } elseif (strpos($page_id, 'tag_') !== false) {
                            $page_type = __('tag' , 'inbound-pro');
                            $tag_id = str_replace("tag_", "", $page_id);
                            $tag = get_tag($tag_id);
                            $page_title = $tag->name . " - Tag Page";
                            $tag_names = '';
                            $page_permalink = get_tag_link($tag_id);

                        } elseif (strpos($page_id, '/') !== false) {
                            $page_permalink = site_url($page_id);
                            $page_id = url_to_postid($url);
                            $page = get_post($page_id);
                            $page_type = (isset($page->post_type)) ? $page->post_type : 'unknown' ;
                            $page_title = (isset($page->post_title)) ? $page->post_type : 'unknown' ;
                        } else {
                            $page_type = __('post' , 'inbound-pro');
                            $page_title = get_the_title($page_id);
                            $page_title = ($page_id != 0) ? $page_title : 'N/A';
                            $page_permalink = get_permalink($page_id);
                        }

                        $page_title_short = strlen($page_title) > 65 ? substr($page_title, 0, 65) . "..." : $page_title;
                        ?>
                        <div class="lp-page-view-item inbetween ">
                            <div class="path-left">
                                <span class="marker">
                                    <i class="fa fa-arrow-down" aria-hidden="true"></i>
                                </span>
                                <a href='<?php echo $page_permalink; ?>' title='<?php echo $page_title; ?>' target='_blank'>
                                    <?php echo  '<u>'. $page_title_short. '</u> - ' .$page_type; ?>
                                </a>
                            </div>
                            <div class="path-right">
                                <span class="time-on-page">
                                </span>
                            </div>
                        </div>
                        <?php
                        $count++;
                    }

                    ?>
                    <div class="lp-page-view-item ">
                        <div class="path-left">
                            <span class="marker">
                                <i class="fa fa-crosshairs" aria-hidden="true"></i>
                            </span>
                            <b>
                            <?php _e('converted!' , 'inbound-pro' ) ?>
                            </b>
                            <br>
                        </div>
                        <div class="path-right">

                        </div>
                    </div>
                    <?php
                    ?>
                </div>
                <?php
        }

        public static function print_popup_lists(  $event , $capture ) {
            if (!$event['list_id']) {
                return;
            }

            $term = get_term($event['list_id'] , 'wplead_list_category');
            ?>
            <div class="session-item-holder">
                <div class="popup-header"><strong><?php _e('List Added' , 'inbound-pro'); ?></strong></div>
                <div>
                    <ul>
                     <li><?php echo (isset($term->name)) ? $term->name : '' ;?></li>
                    </ul>
                </div>
            </div>
            <?php

        }

        public static function print_popup_event_details(  $event , $capture ) {
            if (!$event['email_id']) {
                return;
            }

            $array = json_decode($event['event_details'] , true);

            ?>

            <div class="session-item-holder">
                <div class="popup-header"><strong><?php _e('Message Details' , 'inbound-pro'); ?></strong></div>
                <div>
                   <center><i>More information coming soon.</i></center>
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
                    width: 300px;
                    z-index: -1;
                    margin: auto;
                    transition: all .3s ease;
                    text-align:left;
                    position:absolute;
                    padding:20px;
                    margin-left: 62px;
                    margin-top: -33px;
                    cursor:default;
                }

                .visitors-stream-view .fa-filter {
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


                #conversion-tracking {
                    backgroud-color:#fff;
                    font-size: 12px;
                    margin-bottom: 10px;
                    text-align: left;
                    border: 1px solid #CECDCA;
                    -webkit-box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    -moz-box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                    z-index: 200;
                }

                #conversion-tracking th {
                    font-size: 14px;
                    font-weight: normal;
                    color: #039;
                    padding: 10px 8px;
                    border-bottom: 2px solid #6678b1;
                }

                #conversion-tracking td {
                    color: #669;
                    padding: 9px 8px 0px 8px;
                }

                #conversion-tracking tbody tr:hover td {
                    color: #009;
                }

                .session-item-holder {
                    background-color:#fff;
                    cursor:default;
                    padding-top:5px;
                    padding-bottom:5px;
                    padding-right:5px;
                    padding-left:5px;
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
            self::define_range();

            $dates = Inbound_Reporting_Templates::prepare_range( self::$range );
            self::$start_date = $dates['start_date'];
            self::$end_date = $dates['end_date'];
            self::$show_graph = (isset($_REQUEST['show_graph'])) ? $_REQUEST['show_graph'] : true;
            self::$exclude_events = (isset($_REQUEST['exclude_events'])) ? $_REQUEST['exclude_events'] : false;

            /* get all events - group by lead_uid */
            $params = array(
                'event_name' => (isset($_REQUEST['event_name'])) ? sanitize_text_field($_REQUEST['event_name']) : '',
                'page_id' => (isset($_REQUEST['page_id'])) ? intval($_REQUEST['page_id']) : '',
                'lead_id' => (isset($_REQUEST['lead_id'])) ? intval($_REQUEST['lead_id']) : '',
                'list_id' => (isset($_REQUEST['list_id'])) ? intval($_REQUEST['list_id']) : '',
                'cta_id' => (isset($_REQUEST['cta_id'])) ? intval($_REQUEST['cta_id']) : '',
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => self::$start_date,
                'end_date' => self::$end_date,
                'group_by' => (isset($_REQUEST['group_by'])) ? intval($_REQUEST['group_by']) : '',
                'exclude_events' => self::$exclude_events
            );

            self::$events = Inbound_Events::get_events($params);

            if (self::$show_graph === 'false' ) {
                return;
            }

            $args = array();
            if (isset($_REQUEST['page_id'])) {
                $args = $args + array( 'page_id' => intval($_REQUEST['page_id']) );
            }

            self::$event_names = Inbound_Events::get_event_names( $args );

            foreach(self::$event_names as $event) {

                /* account for events related to CTA conversion */
                if (isset($_REQUEST['cta_id'])) {
                    if ($event['event_name'] != 'inbound_cta_click' && !strstr($event['event_name'] , 'form_submission')) {
                        continue;
                    }
                }

                /* get action counts */
                $params = array(
                    'page_id' => (isset($_REQUEST['page_id'])) ? intval($_REQUEST['page_id']) : '',
                    'lead_id' => (isset($_REQUEST['lead_id'])) ? intval($_REQUEST['lead_id']) : '',
                    'list_id' => (isset($_REQUEST['list_id'])) ? intval($_REQUEST['list_id']) : '',
                    'cta_id' => (isset($_REQUEST['cta_id'])) ? intval($_REQUEST['cta_id']) : '',
                    'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                    'start_date' => self::$start_date,
                    'end_date' => self::$end_date,
                    'event_name' => $event['event_name']
                );
                self::$graph_data[$event['event_name']] = Inbound_Events::get_events_by_dates($params);
                self::$graph_data[$event['event_name']] = self::prepare_chart_data( self::$start_date , self::$end_date , $event['event_name']);
                self::$graph_data['dates'] = self::$graph_data[$event['event_name']]['dates'];
            }


		}

        /**
         *
         */
        public static function prepare_chart_data( $start_date, $end_date , $event_name ) {
            /* prepare empty dates */
            $dates = Inbound_Reporting_Templates::get_days_from_range($start_date,$end_date);

            /* create new temporary array with different structure */
            $temp = array();
            foreach (self::$graph_data[$event_name] as $key=> $data) {
                $temp[$data['date']] = $data['events_count'];
            }

            $date_array = array();
            $events_array = array();
            $formatted = array();
            foreach ($dates as $index => $date) {
                if (isset($temp[$date])) {
                    $formatted[$date]['date'] = $date;
                    $formatted[$date]['actions'] = $temp[$date];
                    $date_array[$date] = $date;
                    $events_array[]= $temp[$date];
                } else {
                    $formatted[$date]['date'] = $date;
                    $formatted[$date]['actions'] = 0;
                    $date_array[$date] = $date;
                    $events_array[]= 0;
                }
            }

            return array( 'data' => $formatted, 'dates' => array_keys($formatted), 'actions' => array_values($events_array));

        }
    }

    new Inbound_Events_Report;

}