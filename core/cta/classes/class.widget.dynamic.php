<?php

/**
 * Class to create CTA Placeholder widget.
 *
 * @package CTA
 * @subpackage Core
 */

if (!class_exists('CTA_Dynamic_Widget')) {

    class CTA_Dynamic_Widget extends WP_Widget {

        public function __construct() {

            /* Widget settings. */
            $widget_ops = array('classname' => 'class_CTA_Dynamic_Widget', 'description' => __('Use this widget to accept Calls to Action placements.', 'inbound-pro'));

            /* Create the widget. */
            parent::__construct('id_wp_cta_dynamic_widget', __('Call to Action Placement Holder', 'inbound-pro'), $widget_ops);
        }

        /**
         * How to display the widget on the screen.
         */
        public function widget($args, $instance) {

            do_action('wp_cta_cta_dynamic_widget');

        }

        /**
         * Update the widget settings.
         */
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            /* Strip tags for title and name to remove HTML (important for text inputs). */
            $instance['title'] = strip_tags($new_instance['title']);
            return $instance;
        }

        /**
         * Displays the widget settings controls on the widget panel.
         * Make use of the get_field_id() and get_field_name() function
         * when creating your form elements. This handles the confusing stuff.
         */
        public function form($instance) {

            /* Set up some default widget settings. */
            $defaults = array();
            $instance = wp_parse_args((array)$instance, $defaults); ?>

            <!-- Widget Title: Text Input -->
            <p>
                <?php _e('This call to action area is dynamic. It will be completely empty unless you have toggled on a call to action on the individual pages settings and selected the "sidebar" option.', 'inbound-pro'); ?>
            </p>

            <?php
        }
    }


    /**
     *  Loads the dynamic widget class
     */
    function cta_load_dynamic_widget() {
        register_widget('CTA_Dynamic_Widget');
    }

    add_action('widgets_init', 'cta_load_dynamic_widget');

}