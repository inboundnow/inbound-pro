<?php

/**
 * Expanded Report
 */

if (!class_exists('Inbound_Visitors_Report')) {

    class Inbound_Visitors_Report extends Inbound_Reporting_Templates {

        static $range;
        static $visits;
        static $top_visitors;
        static $top_sources;
        static $start_date;
        static $end_date;


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
            parent::display_filters();
            self::display_top_widgets();
            self::display_all_visitors();
            self::print_css();

            die();
        }

        /**
         * Shows report header
         */
        public static function display_header() {

            $title = get_the_title(intval($_REQUEST['page_id']));
            $permalink = get_the_permalink(intval($_REQUEST['page_id']));
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';

            ?>
            <aside class="profile-card">

                <header>
                    <h1><?php _e('Incomming Visitors' , 'inbound-pro'); ?></h1>
                    <h2><?php echo $title; ?></h2>
                    <h3><a href="<?php echo $permalink; ?>" target="_self"><?php echo $permalink; ?></a></h3>
                </header>

                <!-- some social links to show off -->
                <ul class="profile-social-links">


                </ul>

            </aside>
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
                                <span><?php _e('Visits' , 'inbound-pro'); ?></span>
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
                            $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_first_name'][0] : '';

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
                                                                    . '&page_id='.intval($_REQUEST['page_id'])
                                                                    .'&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1503&height=400'); ?>" target="_self">
                                        <?php echo $event['visits']; ?>
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
                                    <?php echo $event['source']; ?>
                                </td>
                                <td class="" >
                                    <a target="_self" href="<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$event['page_id'].'&source='.urlencode($event['source']).'&class=Inbound_Visitors_Report&range='.self::$range.'&tb_hide_nav=true'); ?>" class="">
                                        <?php echo $event['visits']; ?>
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
        public static function display_all_visitors() {
            $default_gravatar = INBOUND_PRO_URLPATH . 'assets/images/gravatar-unknown.png';
            ?>
            <div class="visitors-stream-view">
            <h3><?php _e( 'Visitors' , 'inbound-pro' ); ?></h3>
            <table class="">
                <thead>
                <tr>
                    <th scope="col" class=" column-lead-picture">
                        <?php _e('' , 'inbound-pro'); ?>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Visitor' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Last Visit' , 'inbound-pro'); ?><i class="fa fa-sort-desc" aria-hidden="true"></i></span>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Source' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Visits' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="">
                        <span><?php _e('Lead' , 'inbound-pro'); ?></span>
                    </th>
                </tr>
                </thead>
                <tbody id="the-list">

                <?php

                foreach (self::$visits as $key => $event) {
                    $lead = get_post($event['lead_id']);

                    $lead_exists = ($lead) ? true : false;

                    $lead_meta = get_post_meta($event['lead_id'] , '' ,  true);

                    $lead_meta['wpleads_first_name'][0] = ($lead_exists && isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __('n/a' , 'inbound-pro');
                    $lead_meta['wpleads_last_name'][0] = ($lead_exists && isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_first_name'][0] : '';
                    ?>
                    <tr id="post-98600" class="hentry">
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
                                    <?php echo $lead_meta['wpleads_first_name'][0] . ' ' . $lead_meta['wpleads_last_name'][0]; ?>
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
                            <p class="mod-date"><em> <?php echo date("F j, Y, g:i a" , strtotime($event['datetime'])); ?>
                                </em>
                            </p>
                        </td>
                        <td class="" >
                            <p class="mod-date"><em> <?php echo $event['source']; ?>
                                </em>
                            </p>
                        </td>
                        <td class="" >
                            <a href="<?php echo admin_url('index.php?action=inbound_generate_report&class=Inbound_Visitor_Report&'.($lead_exists ? 'lead_uid=' . $event['lead_uid'] : 'lead_uid='.$event['lead_uid'] ) . '&page_id='.intval($_REQUEST['page_id']).'&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1503&height=400'); ?>" target="_self">
                                <?php echo $event['visits']; ?>
                            </a>
                        </td>
                        <td class="">
                            <a href="<?php echo ($lead_exists) ? 'post.php?action=edit&post=' . $event['lead_id'] . '&amp;small_lead_preview=true&tb_hide_nav=true' : '#'  ; ?>" target="_self">
                                <i <?php echo ( $lead_exists ) ? 'class="fa fa-user inbound-tooltip" title="'.__('Lead exists. Click to view.','inbound-pro').'"' : 'class="fa fa-hourglass-half" title="'.__('Lead does not exist in database yet.','inbound-pro').'"'; ?>" aria-hidden="true">

                                </i>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>

                <tfoot>
                <tr>
                    <th scope="col" class=" column-lead-picture">
                        <?php _e('' , 'inbound-pro'); ?></th>

                    <th scope="col" class=" column-first-name  desc">
                        <span><?php _e('Visitor' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="  desc">
                        <span><?php _e('Datetime' , 'inbound-pro'); ?><i class="fa fa-sort-desc" aria-hidden="true"></i></span>
                    </th>
                    <th scope="col" class="  desc">
                        <span><?php _e('Last Source' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class=" column-title column-primary  desc">
                        <span><?php _e('Visits' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="  desc">
                        <span><?php _e('Lead' , 'inbound-pro'); ?></span>
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

            /* get all visitors - group by lead_uid */
            $params = array(
                'page_id' => intval($_REQUEST['page_id']),
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date']
            );
            self::$visits = Inbound_Events::get_visitors($params);

            /* get top 10 visitors */
            $params = array(
                'page_id' => intval($_REQUEST['page_id']),
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'order_by' => 'visits',
                'limit' => 10
            );
            self::$top_visitors = Inbound_Events::get_visitors($params);

            /* get top 10 sources */
            $params = array(
                'page_id' => intval($_REQUEST['page_id']),
                'source' => (isset($_REQUEST['source']) ) ? sanitize_text_field(urldecode($_REQUEST['source'])) : '' ,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'group_by' => 'source',
                'order_by' => 'visits',
                'limit' => 10
            );
            self::$top_sources = Inbound_Events::get_visitors($params);

		}
    }

    new Inbound_Visitors_Report;

}