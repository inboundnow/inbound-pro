<?php

/**
 * Class for defining and executing database routines. Updater methods fired are stored in transient to prevent repeat processing
 * @package LandingPages
 * @subpackage Activation
 */
class Landing_Pages_Activation_Update_Routines {


    /*
    * @introduced: 1.5.7
    * @migration-type: Meta pair migragtion
    * @migration: convert meta key lp-conversion-area to template-name-conversion-area-content-{vid}
    */
    public static function migrate_legacy_conversion_area_contents() {

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "1.5.7") === 1) {
            return;
        }

        /* for all landing pages load variations */
        $landing_pages = get_posts(array(
            'post_type' => 'landing-page',
            'posts_per_page' => -1
        ));

        foreach ($landing_pages as $post) {

            /* for all variations loop through and migrate_data */
            (get_post_meta($post->ID, 'lp-ab-variations', true)) ? $variations = get_post_meta($post->ID, 'lp-ab-variations', true) : $variations = array('0' => '0');

            if (!is_array($variations) && strlen($variations) > 1) {
                $variations = explode(',', $variations);
            }

            foreach ($variations as $key => $vid) {

                ($vid) ? $suffix = '-' . $vid : $suffix = '';

                $selected_template = get_post_meta($post->ID, 'lp-selected-template' . $suffix, true);

                if (!$selected_template) {
                    continue;
                }

                /* discover legacy main content */
                ($vid) ? $conversion_area_content = get_post_meta($post->ID, 'conversion-area-content' . $suffix, true) : $conversion_area_content = get_post_meta($post->ID, 'lp-conversion-area', true);

                /* Now if the new key is not already poplated, copy the content to the new key */
                $check = get_post_meta($post->ID, $selected_template . '-conversion-area-content' . $suffix, true);
                if (!$check) {
                    update_post_meta($post->ID, $selected_template . '-conversion-area-content' . $suffix, $conversion_area_content);
                }

            }

        }
    }


    /*
    * @introduced: 1.5.7
    * @migration-type: Meta pair migragtion
    * @migration: mirgrates post_content and content-{vid} values to template-name-main-content-{vid}

    */
    public static function migrate_legacy_main_content() {

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "1.5.8") === 1) {
            return;
        }

        /* for all landing pages load variations */
        $landing_pages = get_posts(array(
            'post_type' => 'landing-page',
            'posts_per_page' => -1
        ));

        foreach ($landing_pages as $post) {

            /* for all variations loop through and migrate_data */
            (get_post_meta($post->ID, 'lp-ab-variations', true)) ? $variations = get_post_meta($post->ID, 'lp-ab-variations', true) : $variations = array('0' => '0');

            if (!is_array($variations) && strlen($variations) > 1) {
                $variations = explode(',', $variations);
            }

            foreach ($variations as $key => $vid) {

                ($vid) ? $suffix = '-' . $vid : $suffix = '';

                $selected_template = get_post_meta($post->ID, 'lp-selected-template' . $suffix, true);
                if (!$selected_template) {
                    continue;
                }

                /* discover legacy main content */
                ($vid) ? $content = get_post_meta($post->ID, 'content' . $suffix, true) : $content = $post->post_content;

                /* Now if the new key is not already poplated, copy the content to the new key */
                $check = get_post_meta($post->ID, $selected_template . '-main-content' . $suffix, true);
                if (!$check) {
                    update_post_meta($post->ID, $selected_template . '-main-content' . $suffix, $content);
                }

            }

        }
    }

    /*
    * @introduced: 1.5.8
    * @migration-type: template migragtion
    * @migration: Moves legacy templates to uploads folder
    *
    */
    public static function updater_move_legacy_templates() {

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "1.5.8") === 1) {
            return;
        }

        /* move copy of legacy core templates to the uploads folder and delete from core templates directory */
        $templates_to_move = array('rsvp-envelope', 'super-slick');
        chmod(LANDINGPAGES_UPLOADS_PATH, 0755);

        $template_paths = Landing_Pages_Load_Extensions::get_core_template_ids();
        if (count($template_paths) > 0) {
            foreach ($template_paths as $name) {
                if (in_array($name, $templates_to_move)) {
                    $old_path = LANDINGPAGES_PATH . "templates/$name/";
                    $new_path = LANDINGPAGES_UPLOADS_PATH . "$name/";

                    /*
                    echo "oldpath: $old_path<br>";
                    echo "newpath: $new_path<br>";
                    */

                    @mkdir($new_path, 0775);
                    chmod($old_path, 0775);

                    self::move_files($old_path, $new_path);

                    rmdir($old_path);
                }
            }
        }
    }

    /* Private Method - Moves files from one folder the older. This is not an updater process */
    private static function move_files($old_path, $new_path) {

        $files = scandir($old_path);

        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }

            if ($file == ".DS_Store") {
                unlink($old_path . $file);
                continue;
            }

            if (is_dir($old_path . $file)) {
                @mkdir($new_path . $file . '/', 0775);
                chmod($old_path . $file . '/', 0775);
                lp_move_template_files($old_path . $file . '/', $new_path . $file . '/');
                rmdir($old_path . $file);
                continue;
            }

            /*
            echo "oldfile:".$old_path.$file."<br>";
            echo "newfile:".$new_path.$file."<br>";
            */

            if (copy($old_path . $file, $new_path . $file)) {
                unlink($old_path . $file);
            }
        }

        $delete = (isset($delete)) ? $delete : false;

        if (!$delete) {
            return;
        }
    }


    /*
    * @introduced: 1.7.5
    * @migration-type: Meta key rename
    * @migration: renames all instances of inbound_conversion_data to _inbound_conversion_data

    */
    public static function meta_key_change_conversion_object() {
        global $wpdb;

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "1.7.5") === 1) {
            return;
        }

        $wpdb->query("UPDATE $wpdb->postmeta SET `meta_key` = REPLACE (`meta_key` , 'inbound_conversion_data', '_inbound_conversion_data')");
    }

    /*
    * @introduced: 1.8.9
    * @migration-type: Meta pair migragtion
    * @migration: Make a meta pair copy of wp_content into 'content' meta key for variation 0 to use (refactor session)
    * @moredetails: Before 1.8.9 we did not source post_content from a meta pair when variation 0 was served. In a step to refactor and normalize we now pull post_content from the meta pair with key 'content'. For now (subject to further improvements in the future), variation id 0 pulls from 'content' meta key while varation 1+ pulls from 'content-{varation_id}' meta key. *
    */
    public static function prepare_content_meta_key_for_variation_0() {

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "1.8.9") === 1) {
            return;
        }

        /* for all landing pages load variations */
        $landing_pages = get_posts(array(
            'post_type' => 'landing-page',
            'posts_per_page' => -1
        ));

        /* loop through landing pages and copy post content into meta object */
        foreach ($landing_pages as $post) {
            $content = $post->post_content;
            update_post_meta($post->ID, 'content', $content);
        }
    }

    /*
    * @introduced: 2.0.4
    * @migration-type: Meta pair migragtion
    * @migration: mirgrates lp_ab_variation_status  to lp_ab_variation_status-0
    * @migration: mirgrates lp-variation-notes  to lp-variation-notes-0

    */
    public static function migrate_variation_status_metapair() {

        /* ignore if not applicable */
        $previous_installed_version = get_transient('lp_current_version');

        if (version_compare($previous_installed_version, "2.0.4") === 1) {
            return;
        }

        /* for all landing pages load variations */
        $landing_pages = get_posts(array(
            'post_type' => 'landing-page',
            'posts_per_page' => -1
        ));

        foreach ($landing_pages as $post) {

            /* mirgrates lp_ab_variation_status  to lp_ab_variation_status-0 */
            $status = get_post_meta($post->ID, 'lp_ab_variation_status', true);
            update_post_meta($post->ID, 'lp_ab_variation_status-0', $status);

            /* mirgrates lp_ab_variation_status  to lp_ab_variation_status-0 */
            $status = get_post_meta($post->ID, 'lp-variation-notes', true);
            update_post_meta($post->ID, 'lp-variation-notes-0', $status);

        }
    }

}


/* Declare Helper Functions here */
function lp_move_template_files($old_path, $new_path) {


}