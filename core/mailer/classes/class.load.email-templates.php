<?php

/**
 * This class loads email templates
 *
 * @package Mailer
 * @subpackage Templates
 */
class Inbound_Mailer_Load_Templates {
    private static $instance;
    public $template_definitions;
    public $definitions;
    public $template_categories;

    public static function instance() {
        if (!isset(self::$instance) && !(self::$instance instanceof Inbound_Mailer_Load_Templates)) {
            self::$instance = new Inbound_Mailer_Load_Templates;
            self::$instance->load_template_config_files();
            self::$instance->load_definitions();
            self::$instance->read_template_categories();
        }

        return self::$instance;
    }

    /**
     *  Loads email template config files
     */
    public static function load_template_config_files() {
        /* load templates from wp-content/plugins/inbound-email/templates/ */

        $core_templates = self::$instance->get_core_templates();

        foreach ($core_templates as $name) {
            if ($name != ".svn") {
                include_once(INBOUND_EMAIL_PATH . "templates/$name/config.php");
            }
        }

        /* load templates from uploads folder */
        $uploaded_templates = self::$instance->get_uploaded_templates();

        foreach ($uploaded_templates as $name) {
            include_once(INBOUND_EMAIL_UPLOADS_PATH . "$name/config.php");
        }

        /* load templates included by current active WordPress theme */
        $included_templates = self::$instance->get_theme_included_templates();

        foreach ($included_templates as $name) {
            include_once(INBOUND_EMAIL_THEME_TEMPLATES_PATH . "$name/config.php");
        }

        /* load smartbar acf */
        include_once( INBOUND_EMAIL_PATH . 'assets/acf/smartbar.php');

        self::$instance->template_definitions = $inbound_email_data;
    }

    /**
     *  Gets array of core email templates
     */
    public static function get_core_templates() {
        $core_templates = array();
        $template_path = INBOUND_EMAIL_PATH . "templates/";
        $results = scandir($template_path);

        //scan through templates directory and pull in name paths
        foreach ($results as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

            if (is_dir($template_path . '/' . $name)) {
                $core_templates[] = $name;
            }
        }

        return $core_templates;
    }

    /**
     *  Gets an array of 3rd party email templates
     */
    public static function get_uploaded_templates() {
        //scan through templates directory and pull in name paths
        $uploaded_templates = array();

        if (!is_dir(INBOUND_EMAIL_UPLOADS_PATH)) {
            wp_mkdir_p(INBOUND_EMAIL_UPLOADS_PATH);
        }

        $templates = scandir(INBOUND_EMAIL_UPLOADS_PATH);


        //scan through templates directory and pull in name paths
        foreach ($templates as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

            if (is_dir(INBOUND_EMAIL_UPLOADS_PATH . '/' . $name)) {
                $uploaded_templates[] = $name;
            }
        }

        return $uploaded_templates;
    }

    /**
     *  Gets an array of 3rd party email templates included in active WordPress theme
     */
    public static function get_theme_included_templates() {
        //scan through templates directory and pull in name paths
        $included_templates = array();

        if (!is_dir(INBOUND_EMAIL_THEME_TEMPLATES_PATH)) {
            return $included_templates;
        }

        $templates = scandir(INBOUND_EMAIL_THEME_TEMPLATES_PATH);


        //scan through templates directory and pull in name paths
        foreach ($templates as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') continue;

            if (is_dir(INBOUND_EMAIL_THEME_TEMPLATES_PATH . '/' . $name)) {
                $included_templates[] = $name;
            }
        }

        return $included_templates;
    }

    /**
     *  Builds a list of template categories
     */
    public static function read_template_categories() {

        $template_cats = array();

        if (!isset(self::$instance->definitions)) {
            return;
        }

        //print_r($extension_data);
        foreach (self::$instance->definitions as $key => $val) {

            if (strstr($key, 'inbound-email') || !isset($val['info']['category'])) continue;

            /* allot for older lp_data model */
            if (isset($val['category'])) {
                $cats = $val['category'];
            } else {
                if (isset($val['info']['category'])) {
                    $cats = $val['info']['category'];
                }
            }

            $cats = explode(',', $cats);

            foreach ($cats as $cat_value) {
                $cat_value = trim($cat_value);
                $name = str_replace(array('-', '_'), ' ', $cat_value);
                $name = ucwords($name);

                if (!isset($template_cats[$cat_value])) {
                    $template_cats[$cat_value]['count'] = 1;
                } else {
                    $template_cats[$cat_value]['count']++;
                }

                $template_cats[$cat_value]['value'] = $cat_value;
                $template_cats[$cat_value]['label'] = "$name";
            }
        }

        self::$instance->template_categories = $template_cats;
    }

    /**
     *  Makes template definitions filterable
     */
    public static function load_definitions() {
        $inbound_email_data = self::$instance->template_definitions;
        self::$instance->definitions = apply_filters('inbound_email_template_data', $inbound_email_data);
    }
}

/**
 *  Allows quick calling of instance
 */
function Inbound_Mailer_Load_Templates() {
    return Inbound_Mailer_Load_Templates::instance();
}

/**
 *  Loads email templates on admin init
 */
function inbound_email_load_templates() {
    Inbound_Mailer_Load_Templates::instance();
}

add_action('admin_init', 'inbound_email_load_templates');