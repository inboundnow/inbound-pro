<?php
/**
 * Class for loading extensions and templates. Mostly templates. No extensions use this class anymore.
 * @package LandingPages
 * @subpackage Templates
 */

class Landing_Pages_Load_Extensions {

    /**
     *  Initializes Landing_Pages_Load_Extensions
     */
    public function __construct() {
        /* load hooks & filters  */
        self::load_hooks();
    }

    /**
     *  Loads hooks and filiters
     */
    public static function load_hooks() {

        /*load core & uploaded templates */
        add_action('admin_init',array(__CLASS__,'load_core_template_configurations') , 5  );
        add_action('admin_init',array(__CLASS__,'load_uploaded_template_configurations') , 5  );
        add_action('admin_init',array(__CLASS__,'load_theme_privided_template_configurations') , 5  );

        /* Adds core metabox settings to extension data array */
        add_filter('lp_extension_data', array(__CLASS__, 'add_core_setting_data'), 1, 1);

        /* Modifies legacy template data key names for old, un-updated legacy templates */
        add_filter('lp_extension_data', array(__CLASS__, 'add_legacy_data_support'), 10, 1);

    }

    /**
     *  Adds core metaboxes setting data using lp_extension_data filter
     */
    public static function add_core_setting_data($data) {

        $data['lp']['settings'] = array(array('id' => 'selected-template', 'label' => __('Select Template', 'landing-pages'), 'description' => __("This option provides a placeholder for the selected template data.", 'landing-pages'), 'type' => 'radio', /* this is not honored. Template selection setting is handled uniquely by core. */
            'default' => 'default', 'options' => null /* this is not honored. Template selection setting is handled uniquely by core. */), array('id' => 'main-headline', 'label' => __('Set Main Headline', 'landing-pages'), 'description' => __("Set Main Headline", 'landing-pages'), 'type' => 'text', /* this is not honored. Main Headline Input setting is handled uniquely by core. */
            'default' => '', 'options' => null),);

        return $data;
    }

    /**
     *  Looks for occurances of 'options' in template & extension data arrays and replaces key with 'settings'
     */
    public static function add_legacy_data_support($data) {

        foreach ($data as $parent_key => $subarray) {
            if (is_array($subarray)) {
                foreach ($subarray as $k => $subsubarray) {
                    /* change 'options' key to 'settings' */
                    if ($k == 'options') $data[$parent_key]['settings'] = $subsubarray;

                    if ($k == 'category') $data[$parent_key]['info']['category'] = $subsubarray;

                    if ($k == 'version') $data[$parent_key]['info']['version'] = $subsubarray;

                    if ($k == 'label') $data[$parent_key]['info']['label'] = $subsubarray;

                    if ($k == 'description') $data[$parent_key]['info']['description'] = $subsubarray;
                }
            }
        }

        return $data;
    }

    /**
     * Loads core template config.php files
     *
     * @returns ARRAY contains template setting data
     */
    public static function load_core_template_configurations() {
        global $lp_data;

        $template_ids = self::get_core_template_ids();

        /*Now load all config.php files with their custom meta data */
        if (count($template_ids) > 0) {
            foreach ($template_ids as $name) {
                if ($name != ".svn" && $name != ".git") {
                    include_once(LANDINGPAGES_PATH . "/templates/$name/config.php");
                }
            }
        }

    }

    /**
     * Loads uploaded template config.php files
     *
     */
    public static function load_uploaded_template_configurations() {
        global $lp_data;

        $template_ids = self::get_uploaded_template_ids();

        /* loop through template ids and include their config file */
        foreach ($template_ids as $name) {
            $match = FALSE;
            if (strpos($name, 'tmp') !== FALSE || strpos($name, 'template-generator') !== FALSE) {
                $match = TRUE;
            }
            if ($name != ".svn" && $name != ".git" && $name != 'template-generator' && $match === FALSE) {
                if (file_exists(LANDINGPAGES_UPLOADS_PATH . "$name/config.php")) {
                    include_once(LANDINGPAGES_UPLOADS_PATH . "$name/config.php");
                }
            }
        }

    }

    /**
     * Loads landing pages found in theme forlder
     *
     */
    public static function load_theme_privided_template_configurations() {
        global $lp_data;

        $template_ids = self::get_theme_provided_template_ids();

        /* loop through template ids and include their config file */
        foreach ($template_ids as $name) {
            if (file_exists(LANDINGPAGES_THEME_TEMPLATES_PATH . "$name/config.php")) {
                include_once(LANDINGPAGES_THEME_TEMPLATES_PATH . "$name/config.php");
            }
        }

    }

    /**
     * Gets array of landing page templates provided by WordPress theme
     *
     * @returns ARRAY $template_ids array of uploaded template ids
     */
    public static function get_theme_provided_template_ids() {

        $template_ids = array();


        if (!is_dir( LANDINGPAGES_THEME_TEMPLATES_PATH )) {
            return $template_ids;
        }

        $results = scandir(LANDINGPAGES_THEME_TEMPLATES_PATH);

        foreach ($results as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') {
                continue;
            }

            if (is_dir(LANDINGPAGES_THEME_TEMPLATES_PATH . '/' . $name)) {
                $template_ids[] = $name;
            }
        }

        return $template_ids;
    }

    /**
     * Gets array of uploaded template paths
     *
     * @returns ARRAY $template_ids array of uploaded template ids
     */
    public static function get_uploaded_template_ids() {
        $template_ids = array();

        if (!is_dir(LANDINGPAGES_UPLOADS_PATH)) {
            wp_mkdir_p(LANDINGPAGES_UPLOADS_PATH);
        }

        $results = scandir(LANDINGPAGES_UPLOADS_PATH);

        foreach ($results as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') {
                continue;
            }

            if (is_dir(LANDINGPAGES_UPLOADS_PATH . '/' . $name)) {
                $template_ids[] = $name;
            }
        }

        return $template_ids;
    }

    /**
     * Gets array of uploaded template paths
     *
     * @returns ARRAY $template_ids array of uploaded template ids
     */
    public static function get_core_template_ids() {
        $template_ids = array();

        $template_path = LANDINGPAGES_PATH . "/templates/";
        $results = scandir($template_path);

        /*scan through templates directory and pull in name paths */
        foreach ($results as $name) {
            if ($name === '.' or $name === '..' or $name === '__MACOSX') {
                continue;
            }

            if (is_dir($template_path . '/' . $name)) {
                $template_ids[] = $name;
            }
        }

        return $template_ids;
    }

    /**
     *  Get's array of template categories from loaded templates
     *
     * @returns ARRAY $template_cats array if template categories
     */
    public static function get_template_categories() {
        $template_settings = self::get_extended_data();

        foreach ($template_settings as $key => $val) {
            if ($key == 'lp' || substr($key, 0, 4) == 'ext-' || isset($val['info']['data_type']) && $val['info']['data_type'] == 'metabox') {
                continue;
            }

            /* account for legacy data models */
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

        return $template_cats;
    }

    /**
     *  Get's template and extension setting data
     *
     * @retuns ARRAY of template & extension data
     */
    public static function get_extended_data() {
        global $lp_data;

        $lp_data = apply_filters('lp_extension_data', $lp_data);

        return $lp_data;
    }


}

/* Initialize Landing_Pages_Load_Extensions */
new Landing_Pages_Load_Extensions;

