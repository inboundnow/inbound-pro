<?php
if (!class_exists('Inbound_Automation_Lead_Profile')) {

    class Inbound_Automation_Lead_Profile {


        /**
         * Inbound_Automation_Lead_Profile constructor.
         */
        public function __construct() {
            self::add_hooks();
        }

        /**
         *
         */
        public static function add_hooks() {
            /* Add Metaboxes */
            add_action('add_meta_boxes', array(__CLASS__, 'define_metaboxes'));

            /* Add Save Actions */
            add_action('save_post', array(__CLASS__, 'save_data'));

        }

        /**
         * Defines MetaBoxes
         */
        public static function define_metaboxes() {
            global $post, $wp_meta_boxes;

            if ($post->post_type != 'wp-lead') {
                return;
            }

            $toggle = get_post_meta( $post->ID , 'inbound_automation_mute' );

            /* Show quick stats */
            add_meta_box('wplead-automation-emails-mute', __("Inbound Automation", 'inbound-pro'), array(__CLASS__, 'display_inbound_automation_settings'), 'wp-lead', 'side', 'low');
        }

        public static function display_inbound_automation_settings() {
            global $post;

            $toggle = get_post_meta( $post->ID , 'inbound_automation_mute' , true);

            ?>
            <table>
                <tr>
                    <td>
                        <?php _e('Mute all automation emails' , 'inbound-pro'); ?>
                    </td>
                    <td>
                        <input type="checkbox" name="inbound_automation_mute" <?php checked(true , $toggle);?> >
                    </td>
                </tr>
            </table>
            <?php
        }

        /**
         *    Save meta data
         */
        public static function save_data($post_id) {
            global $post;

            if (!isset($post) || $post->post_type != 'wp-lead') {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            /* lead status */
            if (isset($_POST['inbound_automation_mute'])) {
                update_post_meta($post_id, 'inbound_automation_mute', (bool) $_POST['inbound_automation_mute']);
            }
        }

    }

    new Inbound_Automation_Lead_Profile;
}

