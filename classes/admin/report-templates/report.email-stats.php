<?php

if( !class_exists( 'Inbound_Mailer_Stats_Report' ) ){
    
    class Inbound_Mailer_Stats_Report extends Inbound_Reporting_Templates {

        static $range;
        static $graph_data;
        static $top_variations;
        static $start_date;
        static $end_date;
        static $past_start_date;
        static $past_end_date;
        static $possible_actions;
        


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
            self::display_chart();
            self::display_top_email_variations();
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
            self::$graph_data['past']= self::prepare_chart_data(self::$past_start_date, self::$past_end_date , 'past', intval($_REQUEST['email_id'])); 
            
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
                        data:['<?php echo sprintf( __('%s past %s days','inbound-pro') , self::$possible_actions[$_REQUEST['event_name']], self::$range ); ?>',
                              '<?php echo sprintf( __('%s prior %s days','inbound-pro') , self::$possible_actions[$_REQUEST['event_name']], self::$range ); ?>']
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

                        },
                        {
                            name:'<?php echo sprintf( __('%s over the prior %s days','inbound-pro') , self::$possible_actions[$_REQUEST['event_name']], self::$range ); ?>',
                            type:'line', // 3d3d3d , 6655ff 
                            itemStyle: {normal: {color:'#6655ff', label:{show:true}}},
                            areaStyle: {normal: {color:'#6655ff', label:{show:true}}},
                            data:<?php echo json_encode(self::$graph_data['past']['actions_counted']); ?>

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
                    <h3><?php echo sprintf(__('Total %s For The Variations Of "%s" ', 'inbound-pro'), self::$possible_actions[$_REQUEST['event_name']], $email->post_title); ?></h3>
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

            </style>
            <link rel='stylesheet' id='fontawesome-css'  href='<?php echo INBOUNDNOW_SHARED_URLPATH ;?>assets/fonts/fontawesome/css/font-awesome.min.css?ver=4.6.1' type='text/css' media='all' />
            <?php
        }

        /**
         * Load event data into static variables
         *
         **/
        public static function load_data() {
            /* build timespan for analytics report */
            self::define_range();

            $dates = Inbound_Reporting_Templates::prepare_range( self::$range );
            self::$start_date = $dates['start_date'];
            self::$end_date = $dates['end_date'];
            self::$past_start_date = $dates['past_start_date'];
            self::$past_end_date = $dates['past_end_date'];

            /* get "current" email stats for the selected action */
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
                'start_date' => self::$start_date,
                'end_date' => self::$end_date
            );
            self::$graph_data['current'] = self::get_email_event_stats($params);

            /* get "past" email stats for the selected action*/
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
                'start_date' => self::$past_start_date,
                'end_date' => self::$past_end_date
            );
            self::$graph_data['past'] = self::get_email_event_stats($params);

            /* get the action stats for the selected email's variations */
            $params = array(
                'email_id' => intval($_REQUEST['email_id']),
                'event_name' => sanitize_text_field($_REQUEST['event_name']),
            );
            self::$top_variations = self::get_top_email_variants($params);

            /* make labels for the possible events to query data for - for UI purposes */
            $params = array(
                'sparkpost_delivery' => __('Sends', 'inbound-pro'),
                'sparkpost_open' => __('Opens', 'inbound-pro'),
                'sparkpost_click' => __('Clicks', 'inbound-pro'),
                'inbound_unsubscribe' => __('Unsubscribes', 'inbound-pro'),
                'inbound_mute' => __('Mutes', 'inbound-pro'),
            );
            self::$possible_actions = $params;
		}

        /**
         * Formats the email action data into a form echarts can use
         */
        public static function prepare_chart_data( $start_date, $end_date, $period = 'current' ) {
            /* prepare empty dates */
            $dates = Inbound_Reporting_Templates::get_days_from_range($start_date,$end_date);

         
            /* create new temporary array with different structure */
            $temp = array();
            $logged_ids = array();
            foreach (self::$graph_data[$period] as $key => $data) {
                if(!isset($logged_ids[$data['variation_id']][$data['lead_id']])){
                    $logged_ids[$data['variation_id']][$data['lead_id']] = 1;
                    $temp[substr($data['datetime'], 0, 10)][] = $data['id'];
                }

            }

            $actions_array = array();
            $formatted = array();
            foreach ($dates as $index => $date) {
                if (isset($temp[$date])) {
                    $formatted[$date]['date'] = $date;
                    $actions_array[]= count($temp[$date]);
                } else {
                    $formatted[$date]['date'] = $date;
                    $actions_array[]= 0;
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

            $table_name = $wpdb->prefix . 'inbound_events';
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT `variation_id`, `lead_id` AS `lead_id` from {$table_name} " . 
                    "WHERE `event_name` = %s " .
                    "AND `email_id` = %d"
                    , $args['event_name'], $args['email_id']
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
                    @$variant_count[$value['variation_id']] += 1;
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
        public static function get_email_event_stats($args){
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'inbound_events';
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from {$table_name} WHERE `email_id` = %d" .
                    " AND `event_name` = %s" .
                    " AND datetime >= %s AND datetime <= %s" .
                    " ORDER BY {$table_name} . `datetime` ASC", 
                    $args['email_id'], $args['event_name'], $args['start_date'], $args['end_date']
                ), ARRAY_A
            );

            return $results;
        }

    }

    new Inbound_Mailer_Stats_Report;

}


?>
