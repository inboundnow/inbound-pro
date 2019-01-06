<?php
/**
 * Trigger Email Send Event
 * @name Trigger Email Send Event
 * @description Triggered manually from within post edit screen
 * @author Inbound Now
 * @contributors: Hudson Atwell
 * @package Automation
 * @subpackage Triggers
 *
 */

if (!class_exists('Inbound_Automation_Trigger_inbound_trigger_email')) {

    class Inbound_Automation_Trigger_inbound_trigger_email {

        static $trigger;

        function __construct() {
            self::$trigger = 'inbound_trigger_email';
            add_filter('inbound_automation_triggers', array(__CLASS__, 'define_trigger'), 1, 1);
            add_action('activate/automation', array(__CLASS__, 'create_dummy_event'));
        }

        /* Build Trigger Definitions */
        public static function define_trigger($triggers) {

            /* Set & Extend Trigger Argument Filters */
            $arguments = apply_filters('trigger/'.self::$trigger.'/trigger_arguments/', array(
                'post_object' => array(
                    'id' => 'post_object',
                    'label' => __('Trigger Data', 'inbound-pro'),
                    'callback' => array(
                        get_class(), 'enrich_post_data'
                    )
                )
            ));

            /* Set & Extend Action DB Lookup Filters */
            /* no db filters */
            $db_lookup_filters = apply_filters('trigger/'.self::$trigger.'/db_arguments', array());

            /* Set & Extend Available Actions */
            $actions = apply_filters('trigger/'.self::$trigger.'/actions', array(
                'send_email', 'wait', 'relay_data'
            ));

            /* define trigger */
            $triggers[self::$trigger] = array(
                'label' => __('On email trigger event (button event)', 'inbound-pro'),
                'description' => __('This event is fired from within the post edit screen. It typically is related to new post notifications.', 'inbound-pro'),
                'action_hook' => self::$trigger,
                'arguments' => $arguments,
                'db_lookup_filters' => $db_lookup_filters,
                'actions' => $actions
            );

            return $triggers;
        }

        /**
         * Adds tag and category ids and names to post object
         * @param $args
         * @return array
         */
        public static function enrich_post_data($args) {

            if (is_object($args)) {
                $args = (array)$args;
            }

            if (isset($args['ID'])) {
                $args['permalink'] = get_the_permalink($args['ID']);
                $args['featured_image'] = wp_get_attachment_url(get_post_thumbnail_id($args['ID']));
                $args['site_name'] = get_bloginfo('name');
                $args['tag_ids'] = wp_get_post_tags($args['ID'], array('fields' => 'ids'));
                $args['tag_names'] = wp_get_post_tags($args['ID'], array('fields' => 'names'));
                $args['cat_ids'] = wp_get_object_terms($args['ID'], 'category', array('fields' => 'ids'));
                $args['cat_names'] = wp_get_object_terms($args['ID'], 'category', array('fields' => 'names'));
            }

            if (isset($args['post_author'])) {
                $args['post_author_name'] = get_the_author_meta('user_nicename', $args['post_author']);
            }

            return $args;
        }

        /**
         * Simulate trigger - perform on plugin activation
         */
        public static function create_dummy_event() {

            $event = array(
                'post_object' => array(
                    'routing_key' => 'password123',
                    'ID' => 444,
                    'post_author' => 2,
                    'post_date' => '2017-03-04 14:12:16',
                    'post_date_gmt' => '2017-03-04 20:12:16',
                    'post_content' => 'Testing Content',
                    'post_title' => 'Example Post Title',
                    'post_excerpt' => '',
                    'post_status' => 'publish',
                    'comment_status' => 'open',
                    'ping_status' => 'open',
                    'post_password' => '',
                    'post_name' => 'example-post-title',
                    'to_ping' => '',
                    'pinged' => '',
                    'post_modified' => '2019-03-04 16:37:12',
                    'post_modified_gmt' => '2019-03-04 22:37:12',
                    'post_content_filtered' => '',
                    'post_parent' => 0,
                    'guid' => 'http://inboundsoon.dev/?p=444',
                    'menu_order' => 0,
                    'post_type' => 'post',
                    'post_mime_type' => '',
                    'comment_count' => 0,
                    'filter' => 'raw',
                    'tag_ids' => array('0' => 216),
                    'tag_names' => array('0' => 'tags'),
                    'cat_ids' => array('0' => 7),
                    'cat_names' => Array('0' => 'Post Category'),
                    'post_author_name' => 'inbound-now'
                )
            );

            $inbound_arguments = Inbound_Options_API::get_option('inbound_automation', 'arguments');
            $inbound_arguments = ($inbound_arguments) ? $inbound_arguments : array();

            /* if data exists do not update arguments object */
            if (isset($inbound_arguments[self::$trigger])) {
                return;
            }

            $inbound_arguments[self::$trigger] = $event;

            Inbound_Options_API::update_option('inbound_automation', 'arguments', $inbound_arguments);
        }

    }

    /* Load Trigger */
    new Inbound_Automation_Trigger_inbound_trigger_email;

}