<?php
/* Inbound Now Menu Class */

if (!class_exists('Inbound_Menus_Adminbar')) {
    class Inbound_Menus_Adminbar {

        static $add_menu;
        static $go_button;
        static $inboundnow_menu_key;
        static $inboundnow_menu_secondary_group_key;
        static $load_forms;
        static $load_landingpages;
        static $load_callstoaction;
        static $load_leads;

        public static function init() {
            // Exit if admin bar not there
            if (!is_user_logged_in() || !is_admin_bar_showing() || !current_user_can('activate_plugins')) {
                return;
            }

            self::$add_menu = true;
            self::$go_button = '<input type="submit" value="' . __('GO', 'inbound-pro') . '" class="inbound-search-go"  /></form>';
            self::$inboundnow_menu_key = 'inbound-admin-bar';
            self::$inboundnow_menu_secondary_group_key = 'inbound-secondary';
            self::hooks();

        }


        /**
         *  Loads Hooks & Filters
         */
        public static function hooks() {

            /* load main hook */
            add_action('admin_bar_menu', array(__CLASS__, 'load_inboundnow_menu'), 98);

            /* add filters here */
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_callstoaction'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_landingpages'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_leads'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_mailer'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_automation'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_forms'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_manage_templates'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_settings'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_analytics'), 10);
            add_filter('inboundnow_menu_primary', array(__CLASS__, 'load_seo'), 10);


            add_filter('inboundnow_menu_secondary', array(__CLASS__, 'load_support'), 10);
            add_filter('inboundnow_menu_secondary', array(__CLASS__, 'load_inbound_hq'), 10);
            add_filter('inboundnow_menu_secondary', array(__CLASS__, 'load_debug'), 10);

            /* Enqueue JS/CSS */
            add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_js_css'));

        }


        /**
         *  Loads the inbound now menu into the admin_bar_menu hook
         */
        public static function load_inboundnow_menu() {
            global $wp_admin_bar;

            $primary_menu_items = apply_filters('inboundnow_menu_primary', array());
            $secondary_menu_items = apply_filters('inboundnow_menu_secondary', array());

            /* Add Parent Nav Menu - Inbound Marketing*/
            $wp_admin_bar->add_menu(array(
                'id' => self::$inboundnow_menu_key,
                'title' => __(' Marketing', 'inbound-pro'),
                'href' => "",
                'meta' => array('class' => 'inbound-nav-marketing', 'title' => 'Inbound Marketing Admin')
            ));


            /** Add Primary Menu Items */
            foreach ($primary_menu_items as $id => $menu_item) {
                /** Add in the item ID */
                $menu_item['id'] = $id;

                /** Add meta target to each item where it's not already set, so links open in new window/tab */
                if (!isset($menu_item['meta']['target'])) {
                    $menu_item['meta']['target'] = '_blank';
                }

                /** Add class to links that open up in a new window/tab */
                if ('_blank' === $menu_item['meta']['target']) {

                    if (!isset($menu_item['meta']['class'])) {
                        $menu_item['meta']['class'] = '';
                    }

                    $menu_item['meta']['class'] .= 'inbound-new-tab';
                }

                /** Add menu items */
                $wp_admin_bar->add_node($menu_item);
            }

            //var_dump($wp_admin_bar);exit;

            /* Add Secondary Menu Item Group */
            $wp_admin_bar->add_group(array(
                'parent' => self::$inboundnow_menu_key,
                'id' => self::$inboundnow_menu_secondary_group_key,
                'meta' => array('class' => 'ab-sub-secondary')
            ));

            foreach ($secondary_menu_items as $id => $menu_item) {
                $menu_item['id'] = $id;
                $menu_item['meta'] = (isset($menu_item['meta']) ) ? $menu_item['meta'] : array();

                if (!isset($menu_item['meta']['target'])) {
                    $menu_item['meta']['target'] = '_blank';
                }

                if ( '_blank' === $menu_item['meta']['target']) {
                    if (!isset($menu_item['meta']['class'])) {
                        $menu_item['meta']['class'] = '';
                    }

                    $menu_item['meta']['class'] .= ' inbound-new-tab';
                }

                $wp_admin_bar->add_node($menu_item);
            }

            /* add lead search */
            if (class_exists('Inbound_Pro_Plugin') && is_admin()) {
                $args = array(
                    'id' => 'lead_search',
                    'title' => '<i class="fa fa-search" aria-hidden="true" style="font-family:FontAwesome;"></i>',
                    'href' => '#lead-search',
                    'meta' => array('class' => 'adminbar-leads-search')
                );
                $wp_admin_bar->add_node($args);
            }

        }

        /**
         *  Loads leads menu items
         */
        public static function load_leads($menu_items) {
            /* Check if Leads Active */
            if (!function_exists('wpleads_check_active')) {
                return $menu_items;
            }

            $leads_key = 'inbound-leads';
            self::$load_forms = true;
            self::$load_leads = true;

            /* 1 - Lead Parent */
            $menu_items[$leads_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Leads', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=wp-lead'),
                'meta' => array('target' => '', 'title' => _x('Manage Forms', 'inbound-pro'))
            );

            /* 1.1 - Leads search form */
            $leads_search_text = __('Search All Leads', 'inbound-pro');
            $menu_items['inbound-leads-search'] = array(
                'parent' => $leads_key,
                'title' => '<form id="inbound-menu-form" method="get" action="' . admin_url('edit.php?post_type=wp-lead') . '" class=" " target="_blank">
				<input id="search-inbound-menu" type="text" placeholder="' . $leads_search_text . '" onblur="this.value=(this.value==\'\') ? \'' . $leads_search_text . '\' : this.value;" onfocus="this.value=(this.value==\'' . $leads_search_text . '\') ? \'\' : this.value;" value="' . $leads_search_text . '" name="s" value="' . esc_attr('Search Leads', 'inbound-pro') . '" class="text inbound-search-input" />
				<input type="hidden" name="post_type" value="wp-lead" />
				<input type="hidden" name="post_status" value="all" />
				' . self::$go_button,
                'href' => false,
                'meta' => array('target' => '', 'title' => _x('Search Leads', 'Translators: For the tooltip', 'inbound-pro'))
            );

            /* 1.2 - View All Leads */
            $menu_items['inbound-leads-view'] = array(
                'parent' => $leads_key,
                'title' => __('View All Leads', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=wp-lead'),
                'meta' => array('target' => '', 'title' => __('View All Forms', 'inbound-pro'))
            );

            /* 1.3 - View Lead Lists */
            $menu_items['inbound-leads-list'] = array(
                'parent' => $leads_key,
                'title' => __('View Lead Lists', 'inbound-pro'),
                'href' => admin_url('edit-tags.php?taxonomy=wplead_list_category&post_type=wp-lead'),
                'meta' => array('target' => '', 'title' => __('View Lead Lists', 'inbound-pro'))
            );

            /* 1.4 - Create New Lead */
            $menu_items['inbound-leads-add'] = array(
                'parent' => $leads_key,
                'title' => __('Create New Lead', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=wp-lead'),
                'meta' => array('target' => '', 'title' => __('Add new lead', 'inbound-pro'))
            );

            return $menu_items;
        }

        /**
         *  Loads Calls To Action Menu Items
         */
        public static function load_callstoaction($menu_items) {

            /* Check if Calls To Action Active */
            if (!function_exists('cta_check_active')) {
                return $menu_items;
            }

            $cta_key = 'inbound-cta';
            self::$load_forms = true;
            self::$load_callstoaction = true;

            /* 1 - Calls to Action */
            $menu_items[$cta_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Call to Actions', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=wp-call-to-action'),
                'meta' => array('target' => '', 'title' => __('View All Landing Pages', 'inbound-pro'))
            );

            /* 1.1 - View Calls to Action */
            $menu_items['inbound-cta-view'] = array(
                'parent' => $cta_key,
                'title' => __('View Calls to Action List', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=wp-call-to-action'),
                'meta' => array('target' => '', 'title' => __('View All Landing Pages', 'inbound-pro'))
            );

            /* 1.2 - Add Calls to Action */
            $menu_items['inbound-cta-add'] = array(
                'parent' => $cta_key,
                'title' => __('Add New Call to Action', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=wp-call-to-action'),
                'meta' => array('target' => '', 'title' => __('Add new call to action', 'inbound-pro'))
            );

            /* 1.3 - Calls to Action Categories */
            $menu_items['inbound-cta-categories'] = array(
                'parent' => $cta_key,
                'title' => __('Categories', 'inbound-pro'),
                'href' => admin_url('edit-tags.php?taxonomy=wp_call_to_action_category&post_type=wp-call-to-action'),
                'meta' => array('target' => '', 'title' => __('Landing Page Categories', 'inbound-pro'))
            );

            /* 1.4 - Settings */
            if (current_user_can('manage_options')) {
                $menu_items['inbound-cta-settings'] = array(
                    'parent' => $cta_key,
                    'title' => __('Settings', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings'),
                    'meta' => array('target' => '', 'title' => __('Manage Call to Action Settings', 'inbound-pro'))
                );
            }

            return $menu_items;
        }

        /**
         *  Loads Landing Page Menu Items
         */
        public static function load_landingpages($menu_items) {
            /* Check if Landing Pages Active */
            if (!function_exists('lp_check_active')) {
                return $menu_items;
            }

            $landing_pages_key = 'inbound-landingpages';
            self::$load_forms = true;
            self::$load_landingpages = true;

            /* 1 - Landing Pages */
            $menu_items[$landing_pages_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Landing Pages', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=landing-page'),
                'meta' => array('target' => '', 'title' => __('View All Landing Pages', 'inbound-pro'))
            );

            /* 1.1 - View Landing Pages */
            $menu_items['inbound-landingpages-view'] = array(
                'parent' => $landing_pages_key,
                'title' => __('View Landing Pages List', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=landing-page'),
                'meta' => array('target' => '', 'title' => __('View All Landing Pages', 'inbound-pro'))
            );

            /* 1.2 - Add New Landing Pages */
            $menu_items['inbound-landingpages-add'] = array(
                'parent' => $landing_pages_key,
                'title' => __('Add New Landing Page', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=landing-page'),
                'meta' => array('target' => '', 'title' => __('Add new Landing Page', 'inbound-pro'))
            );

            /* 1.3 - Landing Pages Categories */
            $menu_items['inbound-landingpages-categories'] = array(
                'parent' => $landing_pages_key,
                'title' => __('Categories', 'inbound-pro'),
                'href' => admin_url('edit-tags.php?taxonomy=landing_page_category&post_type=landing-page'),
                'meta' => array('target' => '', 'title' => __('Landing Page Categories', 'inbound-pro'))
            );

            /* 1.4 - Landing Pages Settings */
            if (current_user_can('manage_options')) {
                $menu_items['inbound-landingpages-settings'] = array(
                    'parent' => $landing_pages_key,
                    'title' => __('Settings', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=landing-page&page=lp_global_settings'),
                    'meta' => array('target' => '', 'title' => __('Manage Landing Page Settings', 'inbound-pro'))
                );
            }


            return $menu_items;
        }

        /**
         *  Loads Email Menu Items
         */
        public static function load_mailer($menu_items) {
            /* Check if Landing Pages Active */
            if (!function_exists('mailer_check_active')) {
                return $menu_items;
            }

            $mailer_key = 'mailer';

            /* 1 - Inbound Mailer Component */
            $menu_items[$mailer_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Email', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=inbound-email'),
                'meta' => array('target' => '', 'title' => __('View All E-Mails', 'inbound-pro'))
            );

            /* 1.1 - View Email */
            $menu_items['mailer-view'] = array(
                'parent' => $mailer_key,
                'title' => __('View Email List', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=inbound-email'),
                'meta' => array('target' => '', 'title' => __('View All E-Mails', 'inbound-pro'))
            );

            /* 1.2 - Add New Email */
            $menu_items['mailer-create'] = array(
                'parent' => $mailer_key,
                'title' => __('Create New eMail', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=inbound-email'),
                'meta' => array('target' => '', 'title' => __('Create New E-Mail', 'inbound-pro'))
            );

            return $menu_items;
        }

        /**
         *  Loads Automation Menu Items
         */
        public static function load_automation($menu_items) {
            /* Check if Landing Pages Active */
            if (!function_exists('inbound_automation_check_active')) {
                return $menu_items;
            }

            $automation_key = 'automation';

            /* 1 - Inbound Automation Component */
            $menu_items[$automation_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Automation', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=automation'),
                'meta' => array('target' => '', 'title' => __('View All Rules', 'inbound-pro'))
            );

            /* 1.1 - View Rules */
            $menu_items['automation-view'] = array(
                'parent' => $automation_key,
                'title' => __('View Rules', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=automation'),
                'meta' => array('target' => '', 'title' => __('View All Rules', 'inbound-pro'))
            );

            /* 1.2 - Add New Rule */
            $menu_items['automation-create'] = array(
                'parent' => $automation_key,
                'title' => __('Create New Rule', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=automation'),
                'meta' => array('target' => '', 'title' => __('Create New Rule', 'inbound-pro'))
            );

            return $menu_items;
        }

        public static function load_forms($menu_items) {
            /* Check if Leads Active */
            if (!self::$load_forms) {
                return $menu_items;
            }

            $forms_key = 'inbound-forms';

            /* 1 - Manage Forms  */
            $menu_items[$forms_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Manage Forms', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=inbound-forms'),
                'meta' => array('target' => '', 'title' => _x('Manage Forms', 'inbound-pro'))
            );

            /* 1.1 - View All Forms */
            $menu_items['inbound-forms-view'] = array(
                'parent' => $forms_key,
                'title' => __('View All Forms', 'inbound-pro'),
                'href' => admin_url('edit.php?post_type=inbound-forms'),
                'meta' => array('target' => '', 'title' => __('View All Forms', 'inbound-pro'))
            );

            /* 1.1.x Get Forms and List */
            $forms = get_posts(array('post_type' => 'inbound-forms', 'post_status' => 'published'));
            foreach ($forms as $form) {
                $menu_items['inbound-form-' . $form->ID] = array(
                    'parent' => 'inbound-forms-view',
                    'title' => $form->post_title,
                    'href' => admin_url('post.php?post=' . $form->ID . '&action=edit'),
                    'meta' => array('target' => '_blank', 'title' => $form->post_title)
                );
            }

            /* 1.2 - Create New Form */
            $menu_items['inbound-forms-add'] = array(
                'parent' => $forms_key,
                'title' => __('Create New Form', 'inbound-pro'),
                'href' => admin_url('post-new.php?post_type=inbound-forms'),
                'meta' => array('target' => '', 'title' => __('Add new call to action', 'inbound-pro'))
            );

            return $menu_items;
        }

        public static function load_manage_templates($menu_items) {
            if (!isset(self::$load_landingpages) || !isset(self::$load_callstoaction)) {
                return $menu_items;
            }

            $templates_key = 'inbound-templates';

            /* 1 - Manage Templates */
            $menu_items[$templates_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Manage Templates', 'inbound-pro'),
                'href' => "",
                'meta' => array('target' => '', 'title' => _x('Manage Templates', 'inbound-pro'))
            );

            /* 1.1 - Get More Templates */
            $menu_items['inbound-gettemplates'] = array(
                'parent' => $templates_key,
                'title' => __('Download More Templates', 'inbound-pro'),
                'href' => "http://www.inboundnow.com/market",
                'meta' => array('target' => '', 'title' => __('Download More Templates', 'inbound-pro'))
            );

            /* 1.1 - Landing Page Templates */
            if (isset(self::$load_landingpages)) {
                $menu_items['inbound-landingpagetemplates'] = array(
                    'parent' => $templates_key,
                    'title' => __('Landing Page Templates', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=landing-page&page=lp_manage_templates'),
                    'meta' => array('target' => '', 'title' => __('Landing Page Templates', 'inbound-pro'))
                );
            }

            /* 1.1 - Call To Action Templates */
            if (isset(self::$load_callstoaction)) {
                $menu_items['inbound-ctatemplates'] = array(
                    'parent' => $templates_key,
                    'title' => __('Call to Action Templates', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=wp-call-to-action&page=wp_cta_manage_templates'),
                    'meta' => array('target' => '', 'title' => __('Call to Action Templates', 'inbound-pro'))
                );
            }

            return $menu_items;
        }

        public static function load_settings($menu_items) {
            $settings_key = 'inbound-settings';

            /* 1 - Global Settings */
            $menu_items[$settings_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Settings', 'inbound-pro'),
                'href' => "",
                'meta' => array('target' => '', 'title' => _x('Manage Settings', 'inbound-pro'))
            );

            /* 1.1 - Call to Action Settings */
            if (defined('INBOUND_PRO_PATH')) {
                $menu_items['inbound-now-settings'] = array(
                    'parent' => $settings_key,
                    'title' => __('Inbound Pro Settings', 'inbound-pro'),
                    'href' => admin_url('admin.php?page=inbound-pro'),
                    'meta' => array('target' => '', 'title' => __('Inbound Pro Settings', 'inbound-pro'))
                );
                $menu_items['inbound-now-extension-settings'] = array(
                    'parent' => $settings_key,
                    'title' => __('Extension Settings', 'inbound-pro'),
                    'href' => admin_url('admin.php?tab=inbound-pro-settings&page=inbound-pro'),
                    'meta' => array('target' => '', 'title' => __('Extension Settings', 'inbound-pro'))
                );
            }

            /* 1.1 - Call to Action Settings */
            if (self::$load_callstoaction) {
                $menu_items['inbound-ctasettings'] = array(
                    'parent' => $settings_key,
                    'title' => __('Call to Action Settings', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=wp-call-to-action&page=wp_cta_global_settings'),
                    'meta' => array('target' => '', 'title' => __('Call to Action Settings', 'inbound-pro'))
                );
            }

            if (self::$load_landingpages) {
                $menu_items['inbound-landingpagesettings'] = array(
                    'parent' => $settings_key,
                    'title' => __('Landing Page Settings', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=landing-page&page=lp_global_settings'),
                    'meta' => array('target' => '', 'title' => __('Landing Page Settings', 'inbound-pro'))
                );
            }

            if (self::$load_leads) {
                $menu_items['inbound-leadssettings'] = array(
                    'parent' => $settings_key,
                    'title' => __('Lead Settings', 'inbound-pro'),
                    'href' => admin_url('edit.php?post_type=wp-lead&page=wpleads_global_settings'),
                    'meta' => array('target' => '', 'title' => __('Lead Settings', 'inbound-pro'))
                );
            }

            return $menu_items;
        }

        public static function load_analytics($menu_items) {
            $analytics_key = 'inbound-analytics';

            /* 1 - Analytics */
            $menu_items[$analytics_key] = array(
                'parent' => self::$inboundnow_menu_key,
                'title' => __('Analytics (coming soon)', 'inbound-pro'),
                'href' => '#',
                'meta' => array('target' => '', 'title' => __('Analytics (coming soon)', 'inbound-pro'))
            );

            return $menu_items;
        }

        public static function load_seo($menu_items) {
            $seo_key = 'inbound-seo';

            if (function_exists('is_plugin_active') && is_plugin_active('wordpress-seo/wp-seo.php')) {
                $menu_items[$seo_key] = array(
                    'parent' => self::$inboundnow_menu_key,
                    'title' => __('SEO by Yoast', 'inbound-pro'),
                    'href' => admin_url('admin.php?page=wpseo_dashboard'),
                    'meta' => array('target' => '', 'title' => __('Manage SEO Settings', 'inbound-pro'))
                );
            }

            return $menu_items;
        }

        public static function load_support($secondary_menu_items) {
            $support_key = 'inbound-support';

            /* 1 - Support Form */
            $secondary_menu_items[$support_key] = array(
                'parent' => self::$inboundnow_menu_secondary_group_key,
                'title' => __('Support Forum', 'inbound-pro'),
                'href' => 'https://support.inboundnow.com/',
                'meta' => array('target' => '_blank', 'title' => __('Support Forum', 'inbound-pro'))
            );

            /* 1 - Documentation */
            $secondary_menu_items['inbound-docs'] = array(
                'parent' => self::$inboundnow_menu_secondary_group_key,
                'title' => __('Documentation', 'inbound-pro'),
                'href' => 'http://docs.inboundnow.com/',
                'meta' => array('title' => __('Documentation', 'inbound-pro'))
            );

            /* 1 - Doc Search */
            $search_docs_text = __('Search Docs', 'inbound-pro');

            $secondary_menu_items['inbound-docs-searchform'] = array(
                'parent' => self::$inboundnow_menu_secondary_group_key,
                'title' => '<form method="get" id="inbound-menu-form" action="//www.inboundnow.com/support/search/?action=bbp-search-request" class=" " target="_blank">
			  <input id="search-inbound-menu" type="text" placeholder="' . $search_docs_text . '" onblur="this.value=(this.value==\'\') ? \'' . $search_docs_text . '\' : this.value;" onfocus="this.value=(this.value==\'' . $search_docs_text . '\') ? \'\' : this.value;" value="' . $search_docs_text . '" name="bbp_search" value="' . esc_attr('Search Docs', 'inbound-pro') . '" class="text inbound-search-input" />
			  <input type="hidden" name="post_type[]" value="docs" />
			  <input type="hidden" name="post_type[]" value="page" />' . self::$go_button,
                'href' => false,
                'meta' => array('target' => '', 'title' => _x('Search Docs', 'Translators: For the tooltip', 'inbound-pro'))
            );

            return $secondary_menu_items;
        }

        public static function load_inbound_hq($secondary_menu_items) {
            $hq_key = 'inbound-hq';

            /* 1 - Inbound Now Plugin HQ */
            $secondary_menu_items[$hq_key] = array(
                'parent' => self::$inboundnow_menu_secondary_group_key,
                'title' => __('Inbound Now Plugin HQ', 'inbound-pro'),
                'href' => 'https://www.inboundnow.com/',
                'meta' => array('title' => __('Inbound Now Plugin HQ', 'inbound-pro'))
            );

            /* 1.1 - GitHub Link */
            $secondary_menu_items['inbound-sites-dev'] = array(
                'parent' => $hq_key,
                'title' => __('GitHub Repository Developer Center', 'inbound-pro'),
                'href' => 'https://github.com/inboundnow',
                'meta' => array('title' => __('GitHub Repository Developer Center', 'inbound-pro'))
            );

            /* 1.2 - Offical Blog */
            $secondary_menu_items['inbound-sites-blog'] = array(
                'parent' => $hq_key,
                'title' => __('Official Blog', 'inbound-pro'),
                'href' => 'https://www.inboundnow.com/blog/',
                'meta' => array('title' => __('Official Blog', 'inbound-pro'))
            );

            /* 1.3 - My Account */
            $secondary_menu_items['inboundsitesaccount'] = array(
                'parent' => $hq_key,
                'title' => __('My Account', 'inbound-pro'),
                'href' => 'https://www.inboundnow.com/marketplace/account/',
                'meta' => array('title' => __('My Account', 'inbound-pro'))
            );

            /* 1.3.1 - Purchase History */
            $secondary_menu_items['inboundsitesaccount-history'] = array(
                'parent' => 'inboundsitesaccount',
                'title' => __('Purchase History', 'inbound-pro'),
                'href' => 'https://www.inboundnow.com/marketplace/account/purchase-history/',
                'meta' => array('title' => __('Purchase History', 'inbound-pro'))
            );

            return $secondary_menu_items;
        }

        /**
         *  Loads debug menu item section
         */
        public static function load_debug($secondary_menu_items) {
            $debug_key = 'inbound-debug';

            /* 1 - Debug Tools */
            $secondary_menu_items[$debug_key] = array(
                'parent' => self::$inboundnow_menu_secondary_group_key,
                'title' => __('<span style="color:#fff;font-size: 13px;margin-top: -1px;display: inline-block;">Debug Tools</span>', 'inbound-pro'),
                'href' => "#",
                'meta' => ""
            );

            /* 1.1 - 1.2 - Link Setup */
            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $param = (preg_match("/\?/", $actual_link)) ? "&" : '?';
            if (preg_match("/inbound-dequeue-scripts/", $actual_link)) {
                $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            } else {
                $actual_link = $actual_link . $param . 'inbound-dequeue-scripts';
            }

            $actual_link_two = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $param_two = (preg_match("/\?/", $actual_link_two)) ? "&" : '?';
            if (preg_match("/inbound_js/", $actual_link_two)) {
                $actual_link_two = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            } else {
                $actual_link_two = $actual_link_two . $param_two . 'inbound_js';
            }

            /* 1.1 - Check for JS Errors */
            $secondary_menu_items['inbound-debug-checkjs'] = array(
                'parent' => $debug_key,
                'title' => __('Check for Javascript Errors', 'inbound-pro'),
                'href' => $actual_link_two,
                'meta' => array('title' => __('Click here to check javascript errors on this page', 'inbound-pro'))
            );

            /* 1.2 - Check for JS Errors */
            $secondary_menu_items['inbound-debug-turnoffscripts'] = array(
                'parent' => $debug_key,
                'title' => __('Remove Javascript Errors', 'inbound-pro'),
                'href' => $actual_link,
                'meta' => array('title' => __('Click here to remove broken javascript to fix issues', 'inbound-pro'))
            );

            /* 1.2 - Force Run All Database Routines */
            $secondary_menu_items['inbound-debug-force-shared-db-routines'] = array(
                'parent' => $debug_key,
                'title' => __('Force apply all shared database routines.', 'inbound-pro'),
                'href' => admin_url('index.php?force_upgrade_routines=true'),
                'meta' => array('title' => __('Click here to re-run all database upgrade routines. ', 'inbound-pro'))
            );

            return apply_filters('inbound_menu_debug', $secondary_menu_items, $debug_key);
        }

        /**
         *  Enqueues admin js and css
         */
        public static function enqueue_js_css() {
            if (!is_user_logged_in()) {
                return;
            }
            wp_enqueue_style('inbound_menu', INBOUNDNOW_SHARED_URLPATH . 'assets/css/admin/wpadminbar.css');
        }
    }

    add_action('init', array('Inbound_Menus_Adminbar', 'init'));
}
