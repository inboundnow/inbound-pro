<?php
/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */

if ( !class_exists('Inbound_CTA_Quick_View') ) {

    class Inbound_CTA_Quick_View extends Inbound_Reporting_Templates {

        static $range;
        static $gaData;
        static $statistics;

        /**
         *    Initializes class
         */
        public static function init() {

            /* build timespan for analytics report */
            self::define_range();

            /* Display date range conditions */
            add_action('inbound-analytics/cta/quick-view', array(__CLASS__, 'display_navigation'), 1);

            /* set Inbound powered content stats */
            add_action('inbound-analytics/cta/quick-view', array(__CLASS__, 'display_statistics_breakdown'), 20);

            /* set Inbound powered content stats */
            add_action('inbound-analytics/cta/quick-view', array(__CLASS__, 'display_cta_click_breakdown'), 40);

            /* set Inbound powered content stats */
            add_action('inbound-analytics/cta/quick-view', array(__CLASS__, 'display_converting_post_breakdown'), 50);


        }

        /**
         *    Given the $_GET['analytics_range'] parameter set the timespan to request analytics data on
         */
        public static function define_range() {
            if (!isset($_GET['range'])) {
                self::$range = get_user_option(
                    'inbound_screen_option_range',
                    get_current_user_id()
                );
                self::$range = (self::$range) ? self::$range : 90;
            } else {
                self::$range = $_GET['range'];
            }
        }

        /**
         * Load impressions from inbound_page_views table
         * @return mixed
         */
        public static function load_impressions() {

            $data = apply_filters('inbound-analytics/cta/quick-view/load-impressions' , self::$statistics );

            /* allow this data to be set by a 3rd party */
            if (isset($data['impressions'])) {
                self::$statistics['impressions'] = $data['impressions'];
                return self::$statistics;
            }

            self::$statistics['impressions']['current'][self::$range] = self::get_impressions(array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            self::$statistics['impressions']['past'][self::$range] = self::get_impressions(array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine rate */
            self::$statistics['impressions']['difference'][self::$range] = self::get_percentage_change(self::$statistics['impressions']['current'][self::$range], self::$statistics['impressions']['past'][self::$range]);

            return self::$statistics;
        }

        /**
         * Load visitors from inbound_page_views table
         * @return mixed
         */
        public static function load_visitors() {

            $data = apply_filters('inbound-analytics/cta/quick-view/load-visitors' , self::$statistics );

            /* allow this data to be set by a 3rd party */
            if (isset($data['visitors'])) {
                self::$statistics['visitors'] = $data['visitors'];
                return self::$statistics;
            }

            /* get visitor count in current time period */
            self::$statistics['visitors']['current'][self::$range] = self::get_visitors(array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            /* get visitor count in past time period */
            self::$statistics['visitors']['past'][self::$range] = self::get_visitors(array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine rate */
            self::$statistics['visitors']['difference'][self::$range] = self::get_percentage_change(self::$statistics['visitors']['current'][self::$range], self::$statistics['visitors']['past'][self::$range]);


        }

        /**
         *
         */
        public static function load_actions() {
            self::load_submissions();
            self::load_cta_clicks();
            self::load_CTA_variation_click_stats();
            self::load_post_CTA_conversion_stats();
        }


        /**
         *
         */
        public static function load_submissions() {
            /* get form submission count in current time period */
            self::$statistics['submissions']['current'][self::$range] = self::get_submissions(array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            /* get action count in past time period */
            self::$statistics['submissions']['past'][self::$range] = self::get_submissions(array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine difference rate */
            self::$statistics['submissions']['difference'][self::$range] = self::get_percentage_change(self::$statistics['submissions']['current'][self::$range], self::$statistics['submissions']['past'][self::$range]);

            /* determine action to impression rate for current time period */
            self::$statistics['submissions']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['submissions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

            /* determine action to impression rate for current time period */
            self::$statistics['submissions']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['submissions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

            /* determine action to impression rate for past time period */
            self::$statistics['submissions']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['submissions']['rate']['current'][self::$range], self::$statistics['submissions']['rate']['past'][self::$range]);

        }

        public static function load_cta_clicks() {

            /* get cta clickthrough count in current time period */
            self::$statistics['cta-clicks']['current'][self::$range] = self::get_tracked_cta_clicks(array(
                'per_days' => self::$range,
                'skip' => 0
            ));

            /* get cta clickthrough count in past time period */
            self::$statistics['cta-clicks']['past'][self::$range] = self::get_tracked_cta_clicks(array(
                'per_days' => self::$range,
                'skip' => 1
            ));

            /* determine difference rate */
            self::$statistics['cta-clicks']['difference'][self::$range] = self::get_percentage_change(self::$statistics['cta-clicks']['current'][self::$range], self::$statistics['cta-clicks']['past'][self::$range]);

            /* determine action to impression rate for current time period */
            self::$statistics['cta-clicks']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['cta-clicks']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

            /* determine action to impression rate for past time period */
            self::$statistics['cta-clicks']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['cta-clicks']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

            /* determine action to impression rate for past time period */
            self::$statistics['cta-clicks']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['cta-clicks']['rate']['current'][self::$range], self::$statistics['cta-clicks']['rate']['past'][self::$range]);

        }


        /**
         * Loads CTA variation click stats for each variation of a CTA
         * These are the CTA variation clicks past/present, the CTA click rate past/present,
         * and the change in the click rate between the past and the present
         *
         */
        public static function load_CTA_variation_click_stats(){
            $variation_click_stats = self::get_variation_click_stats();
            $impressions = self::get_cta_impressions();

            /* if there aren't any click stats, exit*/
            if(empty($variation_click_stats)){
                return;
            }

            /* create the variation click data for each variation */
            foreach($variation_click_stats['current_stats']['variation_id'] as $id => $click_events){
                self::$statistics['variation_clicks'][$id]['current_clicks'] = ($click_events) ? count($click_events) : 0;
                self::$statistics['variation_clicks'][$id]['past_clicks'] = (isset($variation_click_stats['past_stats']['variation_id'][$id])) ? count($variation_click_stats['past_stats']['variation_id'][$id]) : 0;
                self::$statistics['variation_clicks'][$id]['rate']['current'] = (isset($impressions[$id]['current'])) ? (self::$statistics['variation_clicks'][$id]['current_clicks'] / $impressions[$id]['current']) : 0;
                self::$statistics['variation_clicks'][$id]['rate']['past'] = (isset($impressions[$id]['past'])) ? (self::$statistics['variation_clicks'][$id]['past_clicks'] / $impressions[$id]['past']) : 0;
                self::$statistics['variation_clicks'][$id]['change'] = self::get_percentage_change(self::$statistics['variation_clicks'][$id]['rate']['current'], self::$statistics['variation_clicks'][$id]['rate']['past']);

            }
        }

        /**
         * Loads post conversion stats for the given CTA
         *
         */
        public static function load_post_CTA_conversion_stats(){
            global $post;

            $post_conversion_stats = self::get_post_conversion_stats();

            if($post_conversion_stats == null){
                return;
            }

            foreach($post_conversion_stats['current'] as $id => $conversion_count){
                self::$statistics['post_cta_conversions'][$id]['current_conversions'] = (isset($conversion_count)) ? $conversion_count : 0;
                self::$statistics['post_cta_conversions'][$id]['past_conversions'] = (isset($post_conversion_stats['past'][$id])) ? $post_conversion_stats['past'][$id] : 0;
                self::$statistics['post_cta_conversions'][$id]['change'] = self::get_percentage_change(self::$statistics['post_cta_conversions'][$id]['current_conversions'], self::$statistics['post_cta_conversions'][$id]['past_conversions']);
            }
        }

        /**
         *    Loads the analytics template
         *
         */
        public static function load_template() {
            do_action('inbound-analytics/cta/quick-view' , self::$statistics );
        }

        public static function display_navigation() {
            global $post;
            $base = 'post.php?post=' . $post->ID . '&action=edit';
            ?>
            <ul class="nav nav-pills date-range">
                <li <?php echo (self::$range == 1) ? "class='active'" : "class=''"; ?> data-range='1' title='<?php _e('Past 24 hours', 'inbound-pro'); ?>'>
                    <a href='<?php echo $base; ?>&range=1'>1</a>
                </li>
                <li <?php echo (self::$range == 7) ? "class='active'" : "class=''"; ?> data-range='7' title='<?php _e('Past 7 days', 'inbound-pro'); ?>'>
                    <a href='<?php echo $base; ?>&range=7'>7</a>
                </li>
                <li <?php echo (self::$range == 30) ? "class='active'" : "class=''"; ?> data-range='30' title='<?php _e('Past 30 days', 'inbound-pro'); ?>'>
                    <a href='<?php echo $base; ?>&range=30'>30</a>
                </li>
                <li <?php echo (self::$range == 90) ? "class='active'" : "class=''"; ?> data-range='90' title='<?php _e('Past 90 days', 'inbound-pro'); ?>'>
                    <a href='<?php echo $base; ?>&range=90'>90</a>
                </li>
                <li <?php echo (self::$range == 365) ? "class='active'" : "class=''"; ?> data-range='365' title='<?php _e('Past 365 days', 'inbound-pro'); ?>'>
                    <a href='<?php echo $base; ?>&range=365'>365</a>
                </li>
            </ul>
            <?php
        }

        public static function display_sparkline() {
            /*
            ?>
            <br>
            <img src='<?php echo INBOUND_GA_URLPATH; ?>assets/img/example-sparkline.png' title='Example sparkline'>
            <?php
            */
        }

        public static function display_statistics_breakdown() {
            self::load_impressions();
            self::load_actions();
            self::load_visitors();

            global $post;

            ?>
            <table class='ia-table-summary'>
                <tr>
                    <td class='ia-td-th'>
                        <?php _e('Statistic', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Count', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Rate', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Change', 'inbound-pro'); ?>
                    </td>
                </tr>
                <tr>
                    <td class='ia-td-label'>
                        <label title='<?php _e('Total number of visits to this page', 'inbound-pro'); ?>'>
                            <?php _e('Impressions:', 'inbound-pro'); ?>
                        </label>
                    </td>
                    <td>
                        <a href='<?php echo admin_url('index.php?action=inbound_generate_report&obj_key=cta_id&cta_id='.$post->ID.'&class=Inbound_Impressions_Report&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'><?php echo self::$statistics['impressions']['current'][self::$range]; ?></a>
                    </td>
                    <td>

                    </td>
                    <td>
					<span class='stat label <?php echo (self::$statistics['impressions']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s impressions in the last %s days versus %s impressions in the prior %s day period)', 'inbound-pro'), self::$statistics['impressions']['current'][self::$range], self::$range, self::$statistics['impressions']['past'][self::$range], self::$range); ?>" data-toggle="tooltip" data-placement="left"><?php echo self::prepare_rate_format(self::$statistics['impressions']['difference'][self::$range]); ?></a>
                    </td>
                </tr>
                <tr>
                    <td class='ia-td-label'>
                        <label title='<?php _e('Total number of visitors', 'inbound-pro'); ?>'>
                            <?php _e('Visitors:', 'inbound-pro'); ?>
                        </label>
                    </td>
                    <td>
                        <a href='<?php echo admin_url('index.php?action=inbound_generate_report&obj_key=cta_id&cta_id='.$post->ID.'&class=Inbound_Visitors_Report&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                            <?php echo self::$statistics['visitors']['current'][self::$range]; ?>
                        </a>
                    </td>

                    <td>

                    </td>
                    <td>
                        <span class='stat label  <?php echo (self::$statistics['visitors']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s visitors in the last %s days versus %s visitors in the prior %s day period)', 'inbound-pro'), self::$statistics['visitors']['current'][self::$range], self::$range, self::$statistics['visitors']['past'][self::$range], self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['visitors']['difference'][self::$range]); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class='ia-td-label'>
                        <label title='<?php _e('Total number of Inbound Form submissions originating from this page', 'inbound-pro'); ?>'>
                            <?php _e('Inbound Form Submission', 'inbound-pro'); ?>:
                        </label>
                    </td>
                    <td class='ia-td-value'>
                        <a href='<?php echo admin_url('index.php?action=inbound_generate_report&cta_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_form_submission&range='.self::$range.'&title='.__('Inbound Form Submissions', 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                            <?php echo self::$statistics['submissions']['current'][self::$range]; ?>
                        </a>
                    </td>
                    <td class='ia-td-value'>
                        <span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>'><?php echo self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range], false); ?></span>
                    </td>
                    <td class='ia-td-value'>
                        <span class='stat label <?php echo (self::$statistics['submissions']['rate']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus a %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['submissions']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['submissions']['rate']['difference'][self::$range]); ?></span>
                    </td>
                </tr>
                <tr>
                    <td class='ia-td-label'>
                        <label title='<?php _e('Total number of clicked tracked call to action links related to this page.', 'inbound-pro'); ?>'>
                            <?php _e('CTA Click:', 'inbound-pro'); ?>
                        </label>
                    </td>
                    <td class='ia-td-value'>
                        <a href='<?php echo admin_url('index.php?action=inbound_generate_report&cta_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_cta_click&range='.self::$range.'&title='.__('CTA Clicks', 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                            <?php echo self::$statistics['cta-clicks']['current'][self::$range]; ?>
                        </a>
                    </td>
                    <td class='ia-td-value'>
                        <span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus a %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range], false); ?></span>
                    </td>
                    <td class='ia-td-value'>
                        <span class="stat label  <?php echo (self::$statistics['cta-clicks']['rate']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>" title="<?php echo sprintf(__('%s action rate in the last %s days versus a %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['difference'][self::$range]); ?></span>
                    </td>
                </tr>
                <?php
                do_action('inbound-analytics/cta/quick-view/stats-breakdown' , self::$statistics );
                ?>
            </table>
            <?php
        }


        /**
         * Displays a table list of posts that have generated CTA clicks
         *
         */
        public static function display_converting_post_breakdown(){
            global $post;

            /* exit if there's no click stats */
            if(empty(self::$statistics['variation_clicks'])){
                return;
            }

            ?>
            <br>
            <table class='ia-table-summary'>
                <tr>
                    <td class='ia-td-th'>
                        <?php _e('Converting Post', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Count', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>

                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Change', 'inbound-pro'); ?>
                    </td>
                </tr>
                <?php foreach(self::$statistics['post_cta_conversions'] as $id => $stats ){
                    $post_title = get_the_title($id);
                    /* if the title is too long, shorten it */
                    if(strlen($post_title) >= 29){
                        $post_title = substr($post_title, 0, 26) . '...';
                    }
                    ?>
                    <tr>
                        <td class='ia-td-label'>
                            <label title='<?php echo sprintf(__('CTA Conversion events generated by this post in the last %d days', 'inbound-pro'), self::$range); ?>'>
                                <a href="<?php the_permalink($id); ?>"><?php echo $post_title; ?></a>
                            </label>
                        </td>
                        <td class='ia-td-value'>
                            <a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$id.'&cta_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_cta_click&event_name_2=inbound_form_submission&range='.self::$range.'&title='.sprintf(__('%s Conversions', 'inbound-pro'), get_the_title($post->ID)).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                                <?php echo self::$statistics['post_cta_conversions'][$id]['current_conversions']; ?>
                            </a>
                        </td>
                        <td></td>
                        <td class='ia-td-value'>
                            <span class='stat label <?php echo (self::$statistics['post_cta_conversions'][$id]['change'] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('There were %s conversions in the last %s days on this post, versus %s conversions in the prior %s day period', 'inbound-pro'), self::$statistics['post_cta_conversions'][$id]['current_conversions'], self::$range, self::$statistics['post_cta_conversions'][$id]['past_conversions'], self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['post_cta_conversions'][$id]['change']); ?></span>
                        </td>
                    </tr>
                <?php } ?>
                <?php
                do_action('inbound-analytics/cta/quick-view/converting-post-breakdown' , self::$statistics , self::$range ) ; ?>
            </table>
            <?php
        }

        /**
         * Displays a table list of clicks on this CTA's variations
         *
         */
        public static function display_cta_click_breakdown(){
            global $post, $CTA_Variations;

            /* exit if there's no click stats */
            if(empty(self::$statistics['variation_clicks'])){
                return;
            }

            ?>
            <br>
            <table class='ia-table-summary'>
                <tr>
                    <td class='ia-td-th'>
                        <?php _e('Converting Variation', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Count', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Rate', 'inbound-pro'); ?>
                    </td>
                    <td class='ia-td-th'>
                        <?php _e('Change', 'inbound-pro'); ?>
                    </td>
                </tr>
                <?php foreach(self::$statistics['variation_clicks'] as $id => $stats ){
                    $variation_letter = $CTA_Variations->vid_to_letter($post->ID, $id);
                    ?>
                    <tr>
                        <td class='ia-td-label'>
                            <label title='<?php echo sprintf(__('Clicks generated by variation %s of this CTA in %d days', 'inbound-pro'), $variation_letter, self::$range); ?>'><?php echo sprintf(__('Variation: %s', 'inbound-pro'), $variation_letter, self::$range); ?>:</label>
                        </td>
                        <td class='ia-td-value'>
                            <a href='<?php echo admin_url('index.php?action=inbound_generate_report&cta_id='.$post->ID.'&variation_id='.$id.'&class=Inbound_Event_Report&event_name=inbound_cta_click&event_name_2=inbound_form_submission&range='.self::$range.'&title='.sprintf(__('%s: Variation &quot;%s&quot; Conversions', 'inbound-pro'), get_the_title($post->ID), $variation_letter).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                                <?php echo $stats['current_clicks']; ?>
                            </a>
                        </td>
                        <td class='ia-td-value'>
                            <span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>'><?php echo self::prepare_rate_format(self::$statistics['variation_clicks'][$id]['rate']['current'], false); ?></span>
                        </td>
                        <td class='ia-td-value'>
                            <span class='stat label <?php echo (self::$statistics['variation_clicks'][$id]['change'] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus a %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['variation_clicks'][$id]['rate']['current']), self::$range, self::prepare_rate_format(self::$statistics['variation_clicks'][$id]['rate']['past']), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['variation_clicks'][$id]['change']); ?></span>
                        </td>
                    </tr>
                <?php } ?>
                <?php
                do_action('inbound-analytics/cta/quick-view/variation-click-breakdown' , self::$statistics , self::$range ) ; ?>
            </table>
            <?php
        }

        public static function get_impressions($args) {
            global $post;

            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            $args['cta_id'] = $post->ID;

            if ($args['skip']) {
                $args['start_date'] = date( 'Y-m-d' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
                $args['end_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
            } else {
                $args['start_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
                $args['end_date'] = $wordpress_date_time;
            }

            $result = Inbound_Events::get_page_views_by('cta_id' , $args );

            return count($result);
        }

        /**
         * @param $args
         * @return int
         */
        public static function get_visitors($args) {
            global $post;

            $wordpress_date_time =  date_i18n('Y-m-d G:i:s');

            $args['cta_id'] = $post->ID;

            if ($args['skip']) {
                $args['start_date'] = date( 'Y-m-d' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
                $args['end_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
            } else {
                $args['start_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
                $args['end_date'] = $wordpress_date_time;
            }

            return Inbound_Events::get_visitors_count($post->ID, $args);
        }


        public static function get_actions($args) {
            global $post;

            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            if ($args['skip']) {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
                $end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
            } else {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
                $end_date = $wordpress_date_time;
            }

            return Inbound_Events::get_page_actions_count($post->ID, $activity = 'any', $start_date, $end_date);
        }

        public static function get_submissions($args) {
            global $post;

            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            if ($args['skip']) {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
                $end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
            } else {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
                $end_date = $wordpress_date_time;
            }


            return count(Inbound_Events::get_form_submissions_by('cta_id', array('cta_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date)));
        }

        /**
         * Get count of cta click events given a cta_id and server other arguments including date time constrictions
         * @param $args
         * @return int
         */
        public static function get_tracked_cta_clicks($args) {
            global $post;

            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            if ($args['skip']) {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
                $end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
            } else {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
                $end_date = $wordpress_date_time;
            }

            return count(Inbound_Events::get_cta_clicks_by('cta_id', array('cta_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date)));
        }


        /**
         * Get count of content click events given a cta_id and server other arguments including date time constrictions
         * @param $args
         * @return int
         */
        public static function get_tracked_content_clicks($args) {
            global $post;

            $wordpress_date_time = date_i18n('Y-m-d G:i:s');

            if ($args['skip']) {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
                $end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
            } else {
                $start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
                $end_date = $wordpress_date_time;
            }

            $events = Inbound_Events::get_content_clicks_by('cta_id', array('cta_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date));

            return count($events);
        }

        /**
         * Gets the click stats for each variation of the given CTA ID
         * @param $params
         * @return array
         */
        public static function get_variation_click_stats(){
            global $post;

            $params = array('cta_id' => $post->ID);

            if(isset($_GET['range'])){
                $range = sanitize_text_field($_GET['range']);
            }else{
                $range = 365;
            }

            /** get the cta click events between two given dates **/
            $current_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $params['end_date'] = $current_range->format('Y-m-d G:i:s T');
            $current_range->modify( ('-' . $range . 'days') );
            $params['start_date'] = $current_range->format('Y-m-d G:i:s T');

            $variation_click_stats['current_stats'] = Inbound_Events::get_cta_conversions('cta_id', $params);

            /** get the cta clicks between two dates further back in time for something to compare stats against **/
            $past_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $past_range->modify( ('-' . $range . 'days') );
            $params['end_date'] = $past_range->format('Y-m-d G:i:s T');
            $past_range->modify( ('-' . $range . 'days') );
            $params['start_date'] = $past_range->format('Y-m-d G:i:s T');

            $variation_click_stats['past_stats'] = Inbound_Events::get_cta_conversions('cta_id', $params);

            /* count the current result details */
            $current_counter = array();
            $current_content = array();
            foreach($variation_click_stats['current_stats'] as $event){
                $current_content['variation_id'][$event['variation_id']][] = true;
            }

            /* count the past result details */
            $past_counter = array();
            $past_content = array();
            foreach($variation_click_stats['past_stats'] as $event){
                $past_content['variation_id'][$event['variation_id']][] = true;
            }

            /*
             * if there aren't any variation stats for the current time length,
             * sort the past time length's variation clicks from most clicks to least,
             * and according to that order, set the current time's variation clicks to 0
             */
            if(empty($current_content['variation_id'])){
                if(!empty($past_content['variation_id'])){
                    arsort($past_content['variation_id']);
                    foreach($past_content['variation_id'] as $key => $values){
                        $current_content['variation_id'][$key] = 0;
                    }
                }else{
                    /* if there aren't any past stats either, exit */
                    return;
                }
            }else{
                arsort($current_content['variation_id']);

                /** if there are past cta records, check to see if there are any variations missing from the current list **/
                if(!empty($past_content['variation_id'])){
                    $missing_variations = array_diff_key($past_content['variation_id'], $current_content['variation_id']);
                    if(!empty($missing_variations)){
                        foreach($missing_variations as $variation => $values){
                            $current_content['variation_id'][$variation] = 0;
                        }
                    }
                }
            }

            return array('current_stats' => $current_content, 'past_stats' => $past_content);
        }

        /**
         * Returns CTA impressions, sorted by variation id, from the events table
         * @return array
         */
        public static function get_cta_impressions(){
            global $post;

            $impressions = array();
            $params = array();
            $params['cta_id'] = $post->ID;

            if(isset($_GET['range'])){
                $range = sanitize_text_field($_GET['range']);
            }else{
                $range = 365;
            }

            /** get the cta click events between two given dates **/
            $current_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $params['end_date'] = $current_range->format('Y-m-d G:i:s T');
            $current_range->modify( ('-' . self::$range . 'days') );
            $params['start_date'] = $current_range->format('Y-m-d G:i:s T');

            $impressions['current'] = Inbound_Events::get_page_views_by('cta_id', $params);

            $past_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $past_range->modify( ('-' . self::$range . 'days') );
            $params['end_date'] = $past_range->format('Y-m-d G:i:s T');
            $past_range->modify( ('-' . self::$range . 'days') );
            $params['start_date'] = $past_range->format('Y-m-d G:i:s T');

            $impressions['past'] = Inbound_Events::get_page_views_by('cta_id', $params);

            $processed_data = array();

            /* process the impression data for the current timespan */
            if(!empty($impressions['current'])){
                $temp_data = array();
                foreach($impressions['current'] as $impression_data){
                    $temp_data[$impression_data['variation_id']][] = $impression_data['variation_id'];
                }

                foreach($temp_data as $variation => $data){
                    $processed_data[$variation]['current'] = count($data);
                }
            }

            /* process the impression data for the past timespan */
            if(!empty($impressions['past'])){
                $temp_data = array();
                foreach($impressions['past'] as $impression_data){
                    $temp_data[$impression_data['variation_id']][] = $impression_data['variation_id'];
                }

                foreach($temp_data as $variation => $data){
                    $processed_data[$variation]['past'] = count($data);
                }
            }

            return $processed_data;
        }

        /**
         * Returns an array of posts where the current CTA was clicked and how many times it was clicked
         * @return array
         */
        public static function get_post_conversion_stats($get_past_clicks = false){
            global $post;

            $post_conversions = array();
            $params = array();
            $params['cta_id'] = $post->ID;

            /** get the cta click events between two given dates **/
            $current_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $params['end_date'] = $current_range->format('Y-m-d G:i:s T');
            $current_range->modify( ('-' . self::$range . 'days') );
            $params['start_date'] = $current_range->format('Y-m-d G:i:s T');

            $post_conversions['current'] = Inbound_Events::get_cta_conversions('cta_id', $params);

            $past_range = new DateTime(date_i18n('Y-m-d G:i:s'));
            $past_range->modify( ('-' . self::$range . 'days') );
            $params['end_date'] = $past_range->format('Y-m-d G:i:s T');
            $past_range->modify( ('-' . self::$range . 'days') );
            $params['start_date'] = $past_range->format('Y-m-d G:i:s T');

            $post_conversions['past'] = Inbound_Events::get_cta_conversions('cta_id', $params);

            $clicked_posts = array();
            $non_existant_pages = array();
            /* if there are */
            if(!empty($post_conversions['past'])){
                foreach($post_conversions['past'] as $click_data){
                    /* if the post id has already been added to the clicked array, just add another count of it */
                    if(isset($clicked_posts['past'][$click_data['page_id']])){
                        $clicked_posts['past'][$click_data['page_id']]++;

                    }elseif($click_data['page_id'] != '0' && !isset($non_existant_pages[$click_data['page_id']])){
                        /* if the post hasn't been added to the clicked list, check to see if the post exists */
                        $post_exists = get_post($click_data['page_id']);

                        if($post_exists !== null){
                            /* if it does, add it to the clicked list */
                            $clicked_posts['past'][$click_data['page_id']] = 1;
                        }else{
                            /* if it doesn't, add it to the nonexistant list so we don't check it next time */
                            $non_existant_pages[$click_data['page_id']] = true;
                        }
                    }
                }
            }

            if(!empty($post_conversions['current'])){
                foreach($post_conversions['current'] as $click_data){
                    /* if the post id has already been added to the clicked array, just add another count of it */
                    if(isset($clicked_posts['current'][$click_data['page_id']])){
                        $clicked_posts['current'][$click_data['page_id']]++;

                    }elseif($click_data['page_id'] != '0' && !isset($non_existant_pages[$click_data['page_id']])){
                        /* if the post hasn't been added to the clicked list, check to see if the post exists */
                        $post_exists = get_post($click_data['page_id']);

                        if($post_exists !== null){
                            /* if it does, add it to the clicked list */
                            $clicked_posts['current'][$click_data['page_id']] = 1;
                        }else{
                            /* if it doesn't, add it to the nonexistant list so we don't check it next time */
                            $non_existant_pages[$click_data['page_id']] = true;
                        }
                    }
                }

                /* check to see if there's any posts from the 'past' that aren't in the 'current' list */
                if(!empty($clicked_posts['past'])){
                    foreach($clicked_posts['past'] as $id => $conversion_count){
                        /* if the post isn't in the 'current' list, set the click total for it to 0 */
                        if(!isset($clicked_posts['current'][$id])){
                            $clicked_posts['current'][$id] = 0;
                        }
                    }
                }
            }else{
                if(!empty($clicked_posts['past'])){
                    foreach($clicked_posts['past'] as $id => $conversion_count){
                        /* if the post isn't in the 'current' list, set the click total for it to 0 */
                        if(!isset($clicked_posts['current'][$id])){
                            $clicked_posts['current'][$id] = 0;
                        }
                    }
                }else{
                    /* if there aren't 'current' OR 'past' posts, set clicked_posts to null */
                    $clicked_posts = null;
                }
            }

            return $clicked_posts;
        }

        /**
         * Returns the CTA click rate difference between two given points in time
         */
        public static function get_percentage_change($current, $past) {

            if (!$past && $current) {
                return 1;
            }
            if (!$past && !$current) {
                return 0;
            }

            /* find the percent change by subtracting ($c/$p) from 1 .
             * If $c = 1 and $p = 3, dividing them returns 0.33 .
             * But since we want the current change relative to the past,
             * we subtract it from 1 to return 0.66
             * */
            $rate = (1 - ($current / $past));

            /* if the past is greater than the present, multiple by 100 so the 'change' format displays correctly */
            if($past > $current){
                $rate = $rate * 100;
            }

            /* return the rounded $rate and add a - to reverse the sign*/
            return round(-$rate, 2);
        }

        public static function prepare_rate_format($rate, $plusminus = true) {
            $plus = ($plusminus) ? '+' : '';

            if ($rate == 1) {
                return '100%';
            } else if ($rate > 0) {
                return $plus . round($rate, 2) * 100 . '%';
            } else if ($rate == 0) {
                return '0%';
            } else {
                return  round($rate, 2) . '%';
            }
        }
    }

    add_action('admin_init', array('Inbound_CTA_Quick_View', 'init'), 10);
}
