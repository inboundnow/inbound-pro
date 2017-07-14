<?php

/**
 * Class for viewing event funnel reports as well as creating funnels for monitoring.
 * @package     InboundPro
 * @subpackage  Funnels
 */

class Inbound_Funnel_Reporting {
    static $events;
    static $selected_range;
    static $selected_event;
    static $secondary_grouping_field;

    /**
     * constructor.
     */
    public function __construct(){
        self::load_hooks();
    }

    /**
     * Load hooks and filters
     */
    public static function load_hooks(){
        /* enqueue js and css */
        add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );

        /* set funnel display page */
        add_action( 'admin_menu' , array( __CLASS__ , 'listen_display') , 30);
    }

    /**
     *	Enqueue scripts & stylesheets
     */
    public static function enqueue_scripts() {

        $screen = get_current_screen();

        /* Load assets for inbound pro page */
        if (isset($screen) && $screen->base == 'inbound-pro_page_inbound-reporting') {
            wp_enqueue_style('inbound-reporting-funnels', INBOUND_PRO_URLPATH . 'assets/css/admin/reporting.funnel.css');
            wp_enqueue_style('fontawesome', INBOUNDNOW_SHARED_URLPATH . 'assets/fonts/fontawesome/css/font-awesome.min.css');

            wp_enqueue_script('jquery');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        /* Load assets for inbound pro page */
        if (isset($screen) && $screen->base == 'admin_page_inbound-view-funnel-path') {
            wp_enqueue_style('inbound-reporting-funnels', INBOUND_PRO_URLPATH . 'assets/css/admin/reporting.funnel.css');
            wp_enqueue_style('inbound-reporting-funnels-view', INBOUND_PRO_URLPATH . 'assets/css/admin/reporting.funnel.view.css');
            wp_enqueue_style('fontawesome', INBOUNDNOW_SHARED_URLPATH . 'assets/fonts/fontawesome/css/font-awesome.min.css');
        }

    }

    /**
     *
     */
    public static function listen_display() {

        /* Add page handler to wordpress */
        add_submenu_page(
            null,
            __( 'View Funnel', 'inbound-pro' ),
            __( 'View Funnel', 'inbound-pro' ),
            'manage_options',
            'inbound-view-funnel-path',
            array( __CLASS__ , 'display_funnel' )
        );

    }

    /**
     * Load UI
     */
    public static function load_ui() {
        self::load_defaults();
        self::load_event_select();
        self::print_page_header();
        self::print_dates_menu();
        self::print_events_menu();
        self::print_advanced_settings_menu();
        self::print_table();
    }

    /**
     * Load defaults
     */
    public static function load_defaults() {
        self::$selected_event = (isset($_GET['event_name'])) ? sanitize_text_field($_GET['event_name']) : 'inbound_form_submission';
        self::$selected_range = (isset($_GET['range']) ) ? sanitize_text_field($_GET['range']) : 'all'; /*default is 5 years aka 'all' */

        /* get secondary grouping column for MySQL data */
        switch(self::$selected_event) {
            case 'inbound_form_submission':
                self::$secondary_grouping_field = 'form_id';
                break;
            case 'inbound_cta_click':
                self::$secondary_grouping_field = 'cta_id';
                break;
            case 'inbound_content_click':
                self::$secondary_grouping_field = 'inbound_content_click';
                break;
            default:
                self::$secondary_grouping_field = 'page_id';
                break;
        }
    }

    public static function load_event_select() {
        self::$events = Inbound_Events::get_event_names();

        /* setup event blacklist - some events blacklisted are depriciated events */
        $blacklist = array('mute','inbound_mute','inbound_unsubscribe','inbound_email_click','click','inbound_page_view');

        /* remove sparkpost events */
        foreach (self::$events as $key => $event) {
            if (strstr($event['event_name'],'sparkpost_')) {
                unset(self::$events[$key]);
                continue;
            }

            if (in_array($event['event_name'], $blacklist)) {
                unset(self::$events[$key]);
                continue;
            }

            /* get count for event */
            self::$events[$key]['count'] = Inbound_Events::get_events_count($event['event_name']);
        }

        self::$events = array_values(self::$events);

        return self::$events;
    }

    public static function print_page_header() {
        ?>
        <h1><?php _e( 'Funnel Tracking' , 'inbound-pro' ); ?></h1>
        <?php

    }

    public static function print_dates_menu() {
        $choices = array(
            'all' => __('All' , 'inbound-pro'),
            '7' => __('7 days' , 'inbound-pro'),
            '30' => __('30 days' , 'inbound-pro'),
            '90' => __('90 days' , 'inbound-pro'),
            '365' => __('355 days' , 'inbound-pro'),
        );

        $count = count($choices);
        ?>
        <ul class="subsubsub ul-dates" style="width:100%">
            <?php
            $i = 0;
            foreach ($choices as $period => $label) {
                $i++;
                echo '<li class="'.$period.'"><a href="admin.php?page=inbound-reporting&event_name='. self::$selected_event .'&range='. $period .'" '. (self::$selected_range == $period  ? 'class="current"' : '' ) .'> '.$label.' </a> '. ( $i==$count ? '' : '|'  ).'</li>';
            }
            ?>
        </ul>
        <br>
        <?php

    }

    public static function print_events_menu() {
        ?>
        <ul class="subsubsub ul-events" style="width:100%">
            <?php
            foreach (self::$events as $key => $event) {
                $next = $key + 1;
                echo '<li class="'.$event['event_name'].'"><a href="admin.php?page=inbound-reporting&preiod='.self::$selected_range.'&event_name='.$event['event_name'].'" '. (self::$selected_event == $event['event_name']  ? 'class="current"' : '' ) .'> '.$event['event_name'].' <span class="count">('.$event['count'].')</span></a> '. ( isset(self::$events[$next]) ? '|' : ''  ).'</li>';
            }
            ?>
        </ul>
        <?php
    }

    public static function print_advanced_settings_menu() {
        $inbound_forms = Inbound_Forms::get_inbound_forms();
        $ctas = CTA_Post_Type::get_ctas_as_array();
        ?>
        <br>
        <span class="button button-primary" id="funnels-advanced-settings-button"><?php _e( 'Advanced Settings' , 'inbound-pro'); ?></span>
        <div class="funnels-advanced-settings-container">
            <table>
                <tr data-event="inbound_form_submission">
                    <td class="label">
                        <?php _e( 'Narrow by form id:' , 'inbound-pro' ); ?>
                    </td>
                    <td class="setting">
                        <select id="form_id">
                            <option value="0"><?php _e('All Forms','inbound-pro'); ?></option>
                            <?php
                            foreach($inbound_forms as $id => $label) {
                                echo '<option value="'.$id.'">'.$label.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr data-event="inbound_form_submission">
                    <td class="label">
                        <?php _e( 'Narrow by call to action id:' , 'inbound-pro' ); ?>
                    </td>
                    <td class="setting">
                        <select id="form_id">
                            <option value="0"><?php _e('All Calls to Action','inbound-pro'); ?></option>
                            <?php
                            foreach($ctas as $id => $label) {
                                echo '<option value="'.$id.'">'.$label.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public static function print_table() {
        $funnels = self::get_funnels_by_event( self::$selected_event , self::$selected_range , self::$secondary_grouping_field);
        ?>
        <table class="funnel-report">
            <tr>
                <th>
                    <?php _e('Event Count' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Pages in Funnel' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Event Name' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Capture Page' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Capture Item' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Details') ?>
                </th>
            </tr>
            <?php

            foreach ($funnels as $key => $funnel) {

                ?>

                <tr>
                    <td class="funnel-count">
                        <?php echo $funnel['count'] ?>
                    </td>
                    <td class="funnel-pages-count">
                        <?php
                        $funnel_count = json_decode($funnel['funnel'],true);
                        echo count($funnel_count);
                        ?>
                    </td>
                    <td class="funnel-event-name">
                        <?php echo $funnel['event_name'] ?>
                    </td>
                    <td class="funnel-capture-page">
                        <?php
                        $preview_permalink = get_permalink($funnel['page_id']);
                        $edit_permalink = get_edit_post_link($funnel['page_id']);
                        $title = get_the_title($funnel['page_id']);

                        if ($edit_permalink) {
                            echo '<a href="' . $edit_permalink . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                            echo '<a href="' . $preview_permalink . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                            echo '<span title="'.$preview_permalink.'">' . $title . '</span>';
                        } else {
                            echo $funnel['page_id'] .' '. __('not found' , 'inbound-pro');
                        }
                        ?>
                    </td>
                    <td class="funnel-capture-item">
                        <?php
                        $preview_permalink = get_permalink($funnel[self::$secondary_grouping_field]);
                        $edit_permalink = get_edit_post_link($funnel[self::$secondary_grouping_field]);
                        $title = get_the_title($funnel[self::$secondary_grouping_field]);

                        if ($edit_permalink) {
                            echo '<a href="' . $edit_permalink . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                            echo '<a href="' . $preview_permalink . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                            echo '<span title="'.$preview_permalink.'">' . $title . '</span>';
                        }else {
                            echo $funnel['page_id'] .' '. __('not found' , 'inbound-pro');
                        }
                        ?>
                    </td>
                    <td class="funnel-details">
                        <a title='<?php _e('View Funnel Path' , 'inbound-pro'); ?>' class='thickbox' href='admin.php?page=inbound-view-funnel-path&inbound_popup_preview=on&range=<?php echo self::$selected_range; ?>&capture_page=<?php echo $event['page_id']; ?>&event_name=<?php echo $funnel['event_name']; ?>&funnel=<?php echo $funnel['funnel']; ?>&source=<?php echo $funnel['source']; ?>&TB_iframe=true&width=640&height=703' target='_blank'><?php _e('View Funnel','inbound-pro'); ?></a>
                    </td>
                </tr>
                <?php
            }

            ?>
        </table>
        <?php
    }

    public static function get_funnels_by_event( $event_name , $range, $group_col_2 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $wordpress_date_time =  date_i18n('Y-m-d G:i:s');

        if ($range == 'all') {
            $range = 365*5;
        }

        $start_date = date( 'Y-m-d G:i:s' , strtotime("-" . $range ." days" , strtotime($wordpress_date_time )));
        $end_date = $wordpress_date_time;

        $query = 'SELECT *, count(*) as count FROM '.$table_name.' WHERE datetime between "'.$start_date.'" AND "'.$end_date.'" AND event_name = "'.$event_name.'" AND CHAR_LENGTH(funnel) > 4 AND page_id!="0" GROUP BY concat( funnel, '.$group_col_2.') ORDER BY count DESC';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    public static function get_funnel_sources( $funnel , $event_name, $range ) {
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";
        $wordpress_date_time =  date_i18n('Y-m-d G:i:s');

        if ($range == 'all') {
            $range = 365*5;
        }

        $start_date = date( 'Y-m-d G:i:s' , strtotime("-" . $range ." days" , strtotime($wordpress_date_time )));
        $end_date = $wordpress_date_time;

        $query = 'SELECT * FROM '.$table_name.' WHERE datetime between "'.$start_date.'" AND "'.$end_date.'" AND event_name = "'.$event_name.'" AND funnel = "'.$funnel.'"  AND funnel = "'.$funnel.'" ORDER BY source DESC';

        $results = $wpdb->get_results( $query , ARRAY_A );

        return $results;
    }

    /**
     *
     */
    public static function display_funnel() {

        $funnel = json_decode(stripslashes($_GET['funnel']), true);
        $event_name = sanitize_text_field($_GET['event_name']);
        $range = sanitize_text_field($_GET['range']);
        $capture_page = sanitize_text_field($_GET['capture_page']);


        ?>
        <!--<header>
            <p>Worked on all modern browers</p>
            <h1>High Performer</h1>
        </header>-->
        <ul class="timeline">
            <?php
            $i = 0;
            foreach( $funnel as $page_id ) {

                $i++;

                /* determine if last in loop*/
                if (!isset($funnel[$i])) {
                    if ($page_id != $capture_page) {
                        $funnel[] = $capture_page;
                    }
                }

                if (is_numeric($page_id)) {
                    $post = get_post($page_id);
                    $link = get_permalink($page_id);
                    $title = $post->title;
                    $excerpt = $post->post_excerpt;
                    $type = $post->post_type;
                }

                if (strstr($page_id , 'cat_')) {
                    $cat_id = str_replace('cat_' , '' , $page_id );
                    $title = get_cat_name($cat_id);
                    $$link = get_category_link($cat_id);
                    $excerpt = "";
                    $type = __('Category','inbound-pro');

                }

                if (strstr($page_id , 'tag_')) {
                    $tag_id = str_replace('tag_' , '' , $page_id );
                    $tag = get_tag($tag_id);
                    $title = $tag->name;
                    $link = get_tag_link($tag_id);
                    $excerpt = "";
                    $type = __('Tag','inbound-pro');
                }



                if (!$post) {
                    ?>
                    <li >
                        <div class="direction-l" >
                            <div class="flag-wrapper" >
                                <span class="hexa" ></span >
                            </div >
                            <div class="desc" ><?php _e( 'Post or Page Not Found' , 'inbound-pro'); ?></div >
                        </div >
                    </li >
                    <?php
                    continue;
                }

                ?>
                <!--Item -->
                <li >
                    <div class="direction-l" >
                        <div class="flag-wrapper" >
                            <span class="hexa" ></span >
                            <span class="flag" > <?php echo $title; ?></span >
                            <span class="time-wrapper" ><span class="time" > <?php echo $post_type; ?> </span ></span >
                        </div >
                        <div class="desc" > <?php echo $excerpt; ?>.</div >
                    </div >
                </li >
                <?php
            }
            ?>

        </ul>
        <div>
            <header>
                <h1><?php _e('Sources' , 'inbound-pro' ); ?></h1>
            </header>
        </div>
        <?php
        $events = self::get_funnel_sources( $_GET['funnel'] , $event_name, $range);
        echo '<ul>';

        ?>
        <table class="funnel-report">
            <tr>
                <th>
                    <?php _e('Datetime' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Source' , 'inbound-pro') ?>
                </th>
                <th>
                    <?php _e('Lead' , 'inbound-pro') ?>
                </th>
            </tr>
            <?php

            foreach ($events as $key => $event) {

                ?>

                <tr>
                    <td class="funnel-count">
                        <?php echo $event['datetime'] ?>
                    </td>
                    <td class="funnel-event-name">
                        <a href="<?php echo $event['source']; ?>" target="_blank">
                            <?php echo $event['source'] ?>
                        </a>
                    </td>
                    <td class="funnel-event-name">
                        <a href="<?php echo get_edit_post_link($event['lead_id']); ?>" target="_blank">
                            <?php echo $event['lead_id'] ?>
                        </a>

                    </td>

                </tr>
                <?php
            }

            ?>
        </table>
        <?php

        echo '</ul>';
    }
}

new Inbound_Funnel_Reporting;