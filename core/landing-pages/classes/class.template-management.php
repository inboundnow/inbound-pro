<?php

/**
* Class for managing and installing landing page templates manually. This feature is redundant for Inbound Pro subscribers using 1-click-installations.
* @package LandingPages
* @subpackage Templates
*/

class  Landing_Pages_Template_Management {

    public function  __construct() {
        self::load_hooks();
        self::load_views();
    }

    /**
     * Loads hooks and filters
     */
    public static function load_hooks() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts_manage'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts_upload'));

        /* create hidden pages for template upload management */
        add_action('admin_menu', array( __CLASS__ , 'create_pages' ) );
    }


    /**
     * Add support for additional pages
     */
    public static function create_pages() {

        if ( !current_user_can('manage_options') ) {
            return;
        }

        global $_registered_pages;

        $hookname = get_plugin_page_hookname('lp_templates_upload', 'edit.php?post_type=landing-page');
        if (!empty($hookname)) {
            add_action($hookname, 'lp_templates_upload');
        }
        $_registered_pages[$hookname] = true;

        $hookname = get_plugin_page_hookname('lp_templates_search', 'edit.php?post_type=landing-page');
        /*echo $hookname;exit; */
        if (!empty($hookname)) {
            add_action($hookname, 'lp_templates_search');
        }
        $_registered_pages[$hookname] = true;

    }

    /**
     * Enqueue admin scripts for Template Manager
     */
    public static function enqueue_admin_scripts_manage() {
        $screen = get_current_screen();

        if ($screen->base != 'landing-page_page_lp_manage_templates') {
            return;
        }

        wp_enqueue_style( 'lp-css-templates' , LANDINGPAGES_URLPATH . 'assets/css/admin-templates.css', array() , null);
        wp_enqueue_script( 'lp-js-templates' , LANDINGPAGES_URLPATH . 'assets/js/admin/admin.templates.js', array() , null);
    }

    /**
     * Enqueue admin scripts for Template Uploader
     */
    public static function enqueue_admin_scripts_upload() {
        $screen = get_current_screen();

        if ($screen->base != 'landing-landing-page_page_lp_templates_upload') {
            return;
        }

        wp_enqueue_script( 'lp-js-templates-upload' , LANDINGPAGES_URLPATH . 'assets/js/admin/admin.templates-upload.js', array() , null);
    }

    /**
     * Renders the correct views
     */
    public static function load_views() {

    }

    /**
     * Display Template List Table
     */
    public static function display_template_management_view() {
        self::run_management_actions();

        echo '<div class="wrap">';
        ?>

        <h2><?php  _e( 'Manage Templates' , 'inbound-pro' ); ?>
            <a href="edit.php?post_type=landing-page&page=lp_templates_upload"
               class="add-new-h2"><?php echo esc_html_x('Add New Template', 'landing-pages'); ?></a>
        </h2>
        <?php

        $myListTable = new Landing_Pages_Templates_List_Table();
        $myListTable->prepare_items();
        ?>
        <form method="post">
            <input type="hidden" name="page" value="my_list_test"/>
            <?php $myListTable->search_box('search', 'search_id'); ?>
        </form>
        <form method="post" id='bulk_actions'>

        <?php
        $myListTable->display();

        echo '</div></form>';
    }

    /**
     * Displays template upload view
     */
    public static function display_upload_view() {
        self::run_upload_actions();
        self::display_upload_prompt();
        self::display_template_search();
    }

    /**
     * Performs template management actions
     */
    public static function run_management_actions() {
        if (!isset($_REQUEST['action'])) {
            return;
        }

        switch ($_REQUEST['action']):
            case 'upgrade':
                if (count($_REQUEST['template']) > 0) {
                    foreach ($_REQUEST['template'] as $key => $slug) {
                        self::action_upgrade_template( $slug );
                    }
                }
                break;
            case 'delete':
                if (count($_REQUEST['template']) > 0) {

                    foreach ($_REQUEST['template'] as $key => $slug) {
                        self::delete_template(LANDINGPAGES_UPLOADS_PATH . $slug, $slug);
                    }
                }
                break;
        endswitch;


        echo('<meta http-equiv="refresh" content="0;url=edit.php?post_type=landing-page&page=lp_manage_templates">');
        exit;
    }

    /**
     * Performs template upload action
     */
    public static function run_upload_actions() {

        if (!$_FILES || !wp_verify_nonce($_POST["lp_wpnonce"], 'lp-nonce')) {
            return;
        }

        $name = $_FILES['templatezip']['name'];
        $name = preg_replace('/\((.*?)\)/', '', $name);
        $name = str_replace(array(' ', '.zip'), '', $name);
        $name = trim($name);

        include_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

        $zip = new PclZip($_FILES['templatezip']["tmp_name"]);

        $uploads = wp_upload_dir();
        $uploads_path = $uploads['basedir'];
        $extended_path = $uploads_path . '/landing-pages/templates/';
        if (!is_dir($extended_path)) {
            wp_mkdir_p($extended_path);
        }

        if (($list = $zip->listContent()) == 0) {
            die("There was a problem. Please try again!");
        }

        $is_template = false;
        foreach ($list as $key => $val) {
            foreach ($val as $k => $v) {
                if (strstr($v, '/config.php')) {
                    $is_template = true;
                    break;
                } else if (strstr($v, 'config.php')) {
                    $misconfig = 1;
                }else if ($is_template == true) {
                    break;
                }
            }
        }

        if (!$is_template) {
            echo '<br><br><br><br>';
            if ($misconfig) {
                die( __('We\'ve detected that this is a landing page template, but the files need to be zipped inside the parent folder. Please restructure your zip file so that it\'s contents are inside a parent directory with your template\'s name.' , 'inbound-pro' ));

            } else {
                die( __('WARNING! This zip file does not seem to be a template file! If you are trying to install a Landing Page extension please use the Plugin\'s upload section! Please press the back button and try again!' , 'inbound-pro' ));
            }
        }


        if ($result = $zip->extract(PCLZIP_OPT_PATH, $extended_path, PCLZIP_OPT_REPLACE_NEWER) == 0) {
            die( __( 'There was a problem. Please try again!' , 'inbound-pro' ) );
        } else {
            unlink($_FILES['templatezip']["tmp_name"]);
            echo '<div class="updated"><p>' . __( 'Template uploaded successfully!' , 'inbound-pro' ) . '</div>';
        }
    }

    /**
     * Display upload prompt
     */
    public static function display_upload_prompt() {
        require_once (ABSPATH .'wp-includes/pluggable.php');
        ?>
        <div class="wrap templates_upload">
            <div class="icon32" id="icon-plugins"><br></div><h2><?php _e( 'Install Templates' , 'landing-pages'); ?></h2>

            <ul class="subsubsub">
                <li class="plugin-install-manager"><a href="<?php echo admin_url('edit.php?post_type=landing-page&page=lp_manage_templates' ); ?>" id='manage'><?php _e( 'Back' ,'landing-pages'); ?></a> |</li>
                <li class="plugin-install-dashboard"><a target="_blank" href="https://www.inboundnow.com/market/?show=landing-pages" id='menu_search'><?php _e( 'Find New Templates' ,'landing-pages'); ?></a> |</li>
                <li class="plugin-install-upload"><a class="current" href="#upload" id='menu_upload'><?php _e( 'Upload' , 'landing-pages'); ?></a> </li>
            </ul>

            <br class="clear">
            <h4><?php _e('Install Landing Pages template by uploading them here in .zip format' , 'inbound-pro'); ?></h4>

            <p class="install-help"><?php _e( 'Warning: Do not upload landing page extensions here or you will break the plugin! <br>Extensions are uploaded in the WordPress plugins section.' , 'inbound-pro'); ?></p>
            <form action="" class="wp-upload-form" enctype="multipart/form-data" method="post">
                <input type="hidden" value="<?php echo wp_create_nonce('lp-nonce'); ?>" name="lp_wpnonce" id="_wpnonce">
                <input type="hidden" value="/wp-admin/plugin-install.php?tab=upload" name="_wp_http_referer">
                <label for="pluginzip" class="screen-reader-text"><?php _e( 'Template zip file' , 'inbound-pro'); ?></label>
                <input type="file" name="templatezip" id="templatezip">
                <input type="submit" value="Install Now" class="button" id="install-template-submit" name="install-template-submit" disabled="">
            </form>
        </div>
    <?php
    }

    /**
     * Display template search input
     */
    public static function display_template_search() {
        ?>

         <div class="wrap templates_search" style='display:none'>
             <div class="icon32" id="icon-plugins"><br></div><h2><?php _e( 'Search Templates' , 'inbound-pro'); ?></h2>

             <ul class="subsubsub">
                 <li class="plugin-install-dashboard"><a href="#search" id='menu_search'><?php _e( 'Search' , 'inbound-pro'); ?></a> |</li>
                 <li class="plugin-install-upload"><a class="current" href="#upload" id='menu_upload'><?php _e( 'Upload' , 'inbound-pro'); ?></a> </li>
             </ul>

             <br class="clear">
             <p class="install-help"><?php _e( 'Search the Inboundnow marketplace for free and premium templates.' , 'inbound-pro'); ?></p>
             <form action="edit.php?post_type=landing-page&page=lp_store" method="POST" id="">
                 <input type="search" autofocus="autofocus" value="" name="search">
                 <label for="plugin-search-input" class="screen-reader-text"><?php _e( 'Search Templates' , 'inbound-pro'); ?></label>
                 <input type="submit" value="Search Templates" class="button" id="plugin-search-input" name="plugin-search-input">
             </form>
         </div>

        <?php
    }

    /**
     * Perform action: upgrade ticket
     * @param $slug
     */
    public static function action_upgrade_template( $slug ) {
        global $lp_data;
        $data = $lp_data[$slug]['info'];
        $item['ID'] = $slug;
        $item['template'] = $slug;
        $item['name'] = $data['label'];
        $item['category'] = $data['category'];
        $item['description'] = $data['description'];

        $response = self::poll_api($item);
        $package = $response['package'];
        IF (!isset($package) || empty($package)) return;

        $zip_array = wp_remote_get( $package , array( 'timeout' => 60 , 'sslverify'   => false ) );

        ($zip_array['response']['code'] == 200) ? $zip = $zip_array['body'] : die("<div class='error'><p>{$slug}: Invalid download location (Version control not provided).</p></div>");

        $uploads = wp_upload_dir();
        $uploads_dir = $uploads['path'];

        $temp = ini_get('upload_tmp_dir');
        if (empty($temp)) {
            $temp = "/tmp";
        }

        $file_path = $temp . "/" . $slug . ".zip";


        file_put_contents($file_path, $zip);

        include_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

        $zip = new PclZip($file_path);
        $uploads = wp_upload_dir();
        $uploads_path = $uploads['basedir'];
        $extended_path = $uploads_path . '/landing-pages/templates/';


        if (!is_dir($extended_path)) {
            wp_mkdir_p($extended_path);
        }

        $result = $zip->extract(PCLZIP_OPT_PATH, $extended_path, PCLZIP_OPT_REPLACE_NEWER);

        if (!$result) {
            die("There was a problem. Please try again!");
        } else {
            /*print_r($result);exit; */
            unlink($file_path);
            echo '<div class="updated"><p>' . $data['label'] . ' upgraded successfully!</div>';
        }
    }

    /**
     * Action: delete template
     */
    public static function delete_template($dir, $slug) {
        global $lp_data;
        $data = $lp_data[$slug];

        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir) || is_link($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::delete_template($dir . "/" . $item, $slug)) {

                chmod($dir . "/" . $item, 0777);

                if (!self::delete_template($dir . "/" . $item, $slug)) {
                    return false;
                }
            };
        }
        return rmdir($dir);


        echo '<div class="updated"><p>' . $data['label'] . ' deleted successfully!</div>';
    }

    /**
     * Check Inbound Now API to see if template is ready for an update
     * @param $item
     * @return bool
     */
    public static function poll_api( $item ) {
        $api_params = array('edd_action' => 'get_version', 'license' => get_option('lp-license-keys-' . $item['ID']), 'name' => $item['name'], 'slug' => $item['ID'], 'nature' => 'template',);

        $request = wp_remote_post(LANDINGPAGES_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)) {
            $request = json_decode(wp_remote_retrieve_body($request), true);
            if ($request) $request['sections'] = maybe_unserialize($request['sections']);
            return $request;
        } else {
            return false;
        }
    }


}


/**
 * Instantiate class
 */
new Landing_Pages_Template_Management;


/**
 * Shorthand function to load template upload view
 */
function lp_templates_upload() {
    Landing_Pages_Template_Management::display_upload_view();
}

/**
 * Shorthand function to load template management view
 */
function lp_manage_templates() {
    Landing_Pages_Template_Management::display_template_management_view();
}




