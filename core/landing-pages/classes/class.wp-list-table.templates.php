<?php

/**
 * Class displays activated landing page templates
 *
 * @package     LandingPages
 * @subpackage  Templates
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Landing_Pages_Templates_List_Table extends WP_List_Table {

    private $template_data;
    private $found_data;
    private $singular;
    private $plural;
    private $api_key;

    function __construct() {

        $lp_data = Landing_Pages_Load_Extensions::get_extended_data();;
        $final_data = array();

        foreach ($lp_data as $key => $data) {
            $array_core_templates = array('countdown-lander', 'default', 'demo', 'dropcap', 'half-and-half', 'simple-two-column', 'super-slick', 'svtle', 'tubelar', 'rsvp-envelope', 'simple-solid-lite', 'three-column-lander');

            if ($key == 'lp' || substr($key, 0, 4) == 'ext-') {
                continue;
            }


            if (isset($data['info']['data_type']) && $data['info']['data_type'] == 'metabox') {
                continue;
            }


            if (in_array($key, $array_core_templates)) {
                continue;
            }

            if (isset($_POST['s']) && !empty($_POST['s'])) {
                if (!stristr($data['info']['label'], $_POST['s'])) {
                    continue;
                }
            }


            if (isset($data['thumbnail'])) {
                $thumbnail = $data['thumbnail'];
            } else if ($key == 'default') {
                $thumbnail = get_bloginfo('template_directory') . "/screenshot.png";
            } else {
                $thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $key . "/thumbnail.png";
            }

            $this_data['ID'] = $key;
            $this_data['template'] = $key;

            (array_key_exists('info', $data)) ? $this_data['name'] = $data['info']['label'] : $this_data['name'] = $data['label'];
            (array_key_exists('info', $data)) ? $this_data['category'] = $data['info']['category'] : $this_data['category'] = $data['category'];
            (array_key_exists('info', $data)) ? $this_data['description'] = $data['info']['description'] : $this_data['description'] = $data['description'];

            $this_data['thumbnail'] = $thumbnail;
            if (isset($data['info']['version']) && !empty($data['info']['version'])) {
                $this_data['version'] = $data['info']['version'];
            } else {
                $this_data['version'] = "1.0.1";
            }

            $final_data[] = $this_data;
        }

        $this->template_data = $final_data;

        $this->singular = 'ID';
        $this->plural = 'ID';
        $this->screen = get_current_screen();

        $args = $this->_args;
        $args['plural'] = sanitize_key('');
        $args['singular'] = sanitize_key('');

        $this->_args = $args;
        $this->api_key = get_option('inboundnow_master_license_key', '');

    }

    function get_columns() {
        $columns = array('cb' => '<input type="checkbox" />', 'template' => __('Template', 'landing-pages'), 'description' => __('Description', 'landing-pages'), 'category' => __('Category', 'landing-pages'), 'version' => __('Current Version', 'landing-pages')

        );
        return $columns;
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="template[]" value="%s" />', $item['ID']);
    }

    function get_sortable_columns() {
        $sortable_columns = array('template' => array('template', false), 'category' => array('category', false), 'version' => array('version', false));

        return $sortable_columns;
    }

    function usort_reorder($a, $b) {

        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'template';

        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        $result = strcmp($a[$orderby], $b[$orderby]);

        return ($order === 'asc') ? $result : -$result;
    }

    function prepare_items() {

        $columns = $this->get_columns();

        $hidden = array('ID');
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        if (is_array($this->template_data)) {
            usort($this->template_data, array(&$this, 'usort_reorder'));
        }

        $per_page = 10;
        $current_page = $this->get_pagenum();


        $total_items = count($this->template_data);

        if (is_array($this->template_data)) {
            $this->found_data = $this->template_data;
        }


        $this->set_pagination_args(array('total_items' => $total_items,
            'per_page' => $per_page
        ));


        $this->items = $this->found_data;
        $this->_screen = get_current_screen();
        $this->screen = get_current_screen();
    }

    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'template':
                return '<div class="capty-wrapper" style="overflow: hidden; position: relative; "><div class="capty-image"><img src="' . $item['thumbnail'] . '" class="template-thumbnail" alt="' . $item['name'] . '" id="id_' . $item['ID'] . '" title="' . $item['name'] . '">
                            </div><div class="capty-caption" style="text-align:center;width:158px;margin-left:0px;color:#ffffff;background:#000;height: 20px; opacity: 0.7; top:-82px;position: relative;">' . $item['name'] . '</div></div>';
            case 'category':
                return '<span class="post-state">
							<span class="pending states">' . $item[$column_name] . '</span>
							</span>';
            case 'description':
                return $item[$column_name];
            case 'version':
                echo self::check_template_for_update($item);
                return;
            case 'actions':
                echo lp_templates_print_delete_button($item);

                return;
            default:
                return print_r($item, true); /*Show the whole array for troubleshooting purposes */
        }
    }

    function admin_header() {
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;

        if ('lp_manage_templates' != $page) {
            return;
        }
    }

    function no_items() {
        _e('No premium templates installed. Templates included in the Landing Pages core plugin will not be listed here.', 'landing-pages');
    }

    function get_bulk_actions() {

        if (defined('INBOUND_PRO_PATH') && Inbound_Pro_Plugin::get_customer_status() > 0 ) {
            return array(

                '0' => __('See Inbound Pro -> Templates for template options. ', 'landing-pages'),

            );
        }

        $actions = array(

            'upgrade' => __('Upgrade', 'landing-pages'), 'delete' => __('Delete', 'landing-pages'),

        );

        return $actions;
    }

    /**
     * Checks template for update
     * @param $item
     * @return string
     */
    function check_template_for_update($item) {
        $version = $item['version'];

        if (defined('INBOUND_PRO_PATH') && Inbound_Pro_Plugin::get_customer_status() > 0 ) {
            return $version;
        }

        $api_response = self::poll_api($item);

        if ($api_response) {
            if (version_compare($version, $api_response['new_version'], '<')) {
                $template_page = LANDINGPAGES_STORE_URL . "/downloads/" . $item['ID'] . "/";
                $html = '<div class="update-message">' . $item['version'] . ' &nbsp;&nbsp; <font class="update-available">Version ' . $api_response['new_version'] . __( 'available' , 'inbound-pro' ). '</font><br> <a title="' . $item['name'] . '" class="thickbox" href="' . $template_page . '" target="_blank">'. __( 'View template details' , 'inbound-pro' ) .'</a> ';
                $html .= 'or <a href="?post_type=landing-page&page=lp_manage_templates&action=upgrade&template%5B%5D=' . $item['ID'] . '">'. __( 'update now' , 'inbound-pro' ) .'</a>.</div>';
                return $html;
            } else {
                return $item['version'];
            }
        } else {
            return $item['version'];
        }
    }

    /**
     * Check Inbound Now API to see if template is ready for an update
     * @param $item
     * @return bool
     */
    function poll_api( $item ) {
        $api_params = array('edd_action' => 'inbound_get_version', 'license' => $this->api_key, 'name' => $item['name'], 'slug' => $item['ID'], 'nature' => 'template',);

        $request = wp_remote_post('https://www.inboundnow.com', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)) {
            $request = json_decode(wp_remote_retrieve_body($request), true);

            if ($request) $request['sections'] = maybe_unserialize($request['sections']);
            return $request;
        } else {
            return false;
        }
    }


}