<?php
/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */


if (!class_exists('Inbound_Visitor_Event_Report')) {

    class Inbound_Visitor_Event_Report extends Inbound_Reporting_Templates {

        static $lead;
        static $range;
        static $events;


        /**
         *    Given the $_GET['analytics_range'] parameter set the timespan to request analytics data on
         */
        public static function define_range() {
            if (!isset($_GET['range'])) {
                self::$range = 30;
            } else {
                self::$range = $_GET['range'];
            }
        }



        /**
         *    Loads the analytics template
         *
         */
        public static function load_template() {
            self::load_data();


            if (self::$lead) {
                $lead_meta = get_post_meta(self::$lead->ID);

                $first_name = (isset($lead_meta['wpleads_first_name'][0])) ? $lead_meta['wpleads_first_name'][0] : __('n/a');
                $last_name = (isset($lead_meta['wpleads_last_name'][0])) ? $lead_meta['wpleads_first_name'][0] : '';
                $name = $first_name . ' ' .$last_name;
                $lead_exists =true;
            } else {
                $lead_exists = false;
                $name = __('This visitor does not have a lead profile' , 'inbound-pro' );
            }

            ?>



            <aside class="profile-card">

                <header>

                    <!-- here’s the avatar -->
                    <a href="<?php echo ( self::$lead ) ? 'post.php?action=edit&post=' . self::$lead->ID . '&amp;small_lead_preview=true&tb_hide_nav=true' : '#'; ?>" target="_self" class="profile-img-link">
                        <?php
                        $default = INBOUND_PRO_URLPATH . '/assets/images/gravatar-unknown.png';
                        $gravatar = ($lead_exists) ? Leads_Post_Type::get_gravatar(self::$lead->ID , 100) : $default;
                        echo '<img src="' . $gravatar . '">';
                        ?>
                    </a>

                    <!-- the username -->
                    <h1><?php echo $name; ?> <a href="<?php echo ( self::$lead ) ? 'post.php?action=edit&post=' . self::$lead->ID . '&amp;small_lead_preview=true&tb_hide_nav=true' : '#'; ?>" target="_self">

                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a></h1>
                </header>

                <!-- some social links to show off -->
                <ul class="profile-social-links">


                    <!-- add or remove social profiles as you see fit -->

                </ul>

            </aside>
            <!-- that’s all folks! -->

            <?php
            /* show applied filters */
            Inbound_Reporting_Templates::display_filters();
            ?>

            <table class="visits-by-lead">
                <thead>
                <tr>
                    <th scope="col" class="manage-column sort-lead-report-by" sort-by="report-name-field-header">
                        <span><?php _e('Page Visited' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="manage-column column-modified sort-lead-report-by" sort-by="report-date-header" sort-order="1">
                        <span><?php _e('Datetime Visited' , 'inbound-pro'); ?>
                            <i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                        </span>
                    </th>
                    <th scope="col" class="manage-column column-modified ">
                        <span><?php _e('Origin' , 'inbound-pro'); ?></span>
                    </th>
                </tr>
                </thead>
                <tbody id="the-list">

                <?php
                $date_number = 0;
                foreach (self::$events as $key => $event) {
                    $title = get_the_title($event['page_id']);
                    $permalink = get_permalink($event['page_id']);
                    $title_text = ($title) ? $title : 'ZZZZZZZZZZZZZZZZZZZZZ'; //zzzz's so when sorting alphabetically, emptys are on the bottom
                    
                    $title = ($title) ? $title . ' <i class="fa fa-external-link" aria-hidden="true"></i>' : __('Page does not exist' , 'inbound-pro');
                    $permalink = ($permalink) ? $permalink : '#'.$event['id'];

                   ?>
                    <tr id="post-98600" class="hentry lead-table-data-report-row" data-name-field="<?php echo $title_text; ?>" data-date-number="<?php echo $date_number;?>">
                        <td style="">
                            <a href="<?php echo $permalink; ?>" target="_blank"><?php echo $title; ?> </a>
                        </td>
                        <td class="" data-colname="">
                            <p class="mod-date"><em> <?php echo date("F j, Y, g:i a" , strtotime($event['datetime'])); ?>
                        </td>
                        <td class="" data-colname="">
                            <?php
                            echo $event['source'];
                            ?>
                        </td>
                    </tr>
                <?php
                    $date_number++;
                }
                ?>
                </tbody>

                <tfoot>
                <tr>
                    <th scope="col" class="manage-column  ">
                        <span><?php _e('Page Visited' , 'inbound-pro'); ?></span>
                    </th>
                    <th scope="col" class="manage-column column-modified sort-lead-report-by" sort-by="report-date-header" sort-order="1">
                        <span>
                            <?php _e('Datetime Visited' , 'inbound-pro'); ?>
                            <i class="fa fa-caret-down lead-report-sort-indicater" aria-hidden="true" style="padding-left:4px"></i>
                        </span>
                    </th>
                    <th scope="col" class="manage-column column-modified ">
                        <span><?php _e('Origin' , 'inbound-pro'); ?></span>
                    </th>
                </tr>
                </tfoot>

            </table>
            <?php

            self::print_css();
            parent::js_lead_table_sort();
            die();
        }

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
                    width:97%;
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
                    padding: 10px 5px;
                    text-align:center;
                    width: 33%;
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

                /* sssssssssssssssssssssssssssssssssssssssssssss */
                /* go on then, styles go here.. knock yourself out! */
                @import url(//fonts.googleapis.com/css?family=Lato:300,400);
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
                    height: 185px;
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
                    margin-bottom: 8px;
                }
                .profile-card header h2 {
                    font-size: 18px;
                    margin-top: 0;
                    opacity: 0.9;
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

                .visits-by-lead {
                    margin-left:10px;
                    margin-right:10px;
                    margin-top:20px;
                }

            </style>
            <link rel='stylesheet' id='fontawesome-css'  href='<?php echo INBOUNDNOW_SHARED_URLPATH ;?>assets/fonts/fontawesome/css/font-awesome.min.css?ver=4.6.1' type='text/css' media='all' />

            <?php
        }

        /**
         *    Loads data from Inbound Cloud given parameters
         *
         * @param ARRAY $args
         */
        public static function load_data() {
            $dates = Inbound_Reporting_Templates::prepare_range( intval($_REQUEST['range']) );

            /* determine lookup param */
            $lookup =  (isset($_REQUEST['lead_id'])) ? 'lead_id' : 'lead_uid';

            $params = array(
                'page_id' => (isset($_REQUEST['page_id'])) ? sanitize_text_field($_REQUEST['page_id']) : 0,
                'lead_uid' => (isset($_REQUEST['lead_uid'])) ? sanitize_text_field($_REQUEST['lead_uid']) : 0,
                'lead_id' => (isset($_REQUEST['lead_id'])) ? sanitize_text_field($_REQUEST['lead_id']) : 0,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'orderby' => 'ORDER BY datetime DESC, session_id DESC, lead_id DESC'
            );


            self::$events = Inbound_Events::get_page_views_by( 'mixed' , $params);


            self::$lead = ($lookup == 'lead_id' ) ? get_post(intval($_REQUEST['lead_id'])) : false;

            if (!self::$lead && isset(self::$events[0]['lead_id']) && self::$events[0]['lead_id'] ) {
                self::$lead = get_post(self::$events[0]['lead_id']);
            }

        }
    }

    new Inbound_Visitor_Event_Report;

}
