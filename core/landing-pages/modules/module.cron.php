<?php

add_filter( 'cron_schedules', 'filter_cron_schedules' );
function filter_cron_schedules( $schedules ) {
    $schedules['three_minutes'] = array(
        'interval' => 180, // 1 week in seconds
        'display'  => __( 'Every 3 minutes' ),
    );

    return $schedules;
}
 if (!wp_next_scheduled('sfs_cron_cache')) {
          wp_schedule_event(time(), 'hourly', 'sfs_cron_cache'); // hourly, daily, twicedaily, three_minutes
 }
if (isset($_GET['cron-check'])) {
     $get_count = get_option( 'cron-tester');
     echo 'cron counter: ' . $get_count;
 }

// cache feed counts
add_action('sfs_cron_cache', 'sfs_cache_data');
function sfs_cache_data() {
     $get_count = get_option( 'cron-tester');
     $new_count = $get_count + 1;
     update_option( 'cron-tester', $new_count );
     /*
     $page_conversion_data = get_post_meta( 1523, 'inbound_conversion_data', TRUE );
     $page_conversion_data = json_decode($page_conversion_data,true);
     $version = '0';
     if (is_array($page_conversion_data)){
          $convert_count = count($page_conversion_data) + 1;
          $page_conversion_data[$convert_count]['lead_id'] = 99;
          $page_conversion_data[$convert_count]['variation'] = $version;
          $page_conversion_data[$convert_count]['datetime'] = '2013-12-16 12:29:46 UTC';
     } else {
          $convert_count = 1;
          $page_conversion_data[$convert_count]['lead_id'] = 99;
          $page_conversion_data[$convert_count]['variation'] = $version;
          $page_conversion_data[$convert_count]['datetime'] = '2013-12-16 12:29:46 UTC';
     }
     $page_conversion_data = json_encode($page_conversion_data);
     update_post_meta(1523, 'inbound_conversion_data', $page_conversion_data);
     */

}