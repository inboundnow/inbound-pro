<?php

/* Use me for time debugging!

	$start_time = microtime(TRUE);

	$end_time = microtime(TRUE);
	echo $end_time - $start_time;
	exit;

*/

/**
 * Class for loading and rendering call to actions
 * @package CTA
 * @subpackage Display
 */

if ( !class_exists( 'CTA_Render' ) ) {


    /* Provide way to call the singleton instance */
    function CTA_Render() {
        return CTA_Render::instance();
    }

    /* Initialize first singleton instance at init */
    add_action('init','wp_cta_load_calls_to_action', 11);
    function wp_cta_load_calls_to_action() {
        $calls_to_action = CTA_Render();
    }

    class CTA_Render {

        private static $instance;
        private $cta_templates;
        private $obj;
        private $obj_id;
        private $obj_nature;
        private $cta_display_list;
        private $cta_dataset;
        private $cta_content_placement;
        private $selected_cta;
        private $cta_template;
        private $is_preview;
        private $cta_width;
        private $cta_height;

        public static function instance() {
            if ( !isset( self::$instance ) && ! ( self::$instance instanceof CTA_Render )) {
                self::$instance = new CTA_Render;

                /* Load CSS Template Parser */
                require_once(WP_CTA_PATH.'assets/lib/Sabberworm/load-css-parse.php');

                /* load cta template data */
                $CTA_Load_Extensions = CTA_Load_Extensions();
                self::$instance->cta_templates = $CTA_Load_Extensions->template_definitions;

                /* load cta(s) */
                self::$instance->setup_hooks();
            }

            return self::$instance;
        }

        /**
         *  Load Hooks and Filters
         */
        function setup_hooks() {
            /* Get Global $post Object */
            add_action( 'wp', array( $this, 'setup_static_environment_vars'), 1 );

            /* Check for CTA */
            add_action( 'wp_cta_after_global_init', array( $this, 'setup_cta_direct_placement'), 1 );

            /* Enqueue CTA js * css */
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts'), 20 );

            /* Apply custom JS & CSS for CTA */
            add_action( 'wp_head', array( $this, 'load_custom_js_css'));

            /* Add CTA Render to Content */
            add_filter( 'the_content', array( $this, 'add_cta_to_post_content'), apply_filters('cta_the_content_priority', 15) );

            /* Add CTA Render to Dynamic Widget */
            add_filter( 'wp_cta_after_global_init', array( $this, 'add_cta_to_dynamic_widget'), 10);

            /* Add Shortcode Support for CTA */
            add_shortcode( 'cta', array( $this, 'process_shortcode_cta'));

            /* Listen for CTA previews */
            add_action( 'template_redirect', array( $this, 'preview_cta'), 2 );

            /* Modify admin URL for previews */
            add_filter( 'admin_url', array( $this, 'modify_admin_url'));

            /* wpautop only up to 3th priority & reenable autoembed */
            remove_filter('the_content','wpautop');
            add_filter('the_content','wpautop' , 3 );
            add_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 2 );

        }

        /**
         *  Detect& store data about the load environment into static variables
         */
        public function setup_static_environment_vars() {
            global $wp_query;

            /* running these on paged renders causes pagniation to break */
            if ( get_query_var('page') < 1 ) {
                self::$instance->obj = $wp_query->get_queried_object();
                self::$instance->obj_id = $wp_query->get_queried_object_id();
                $paged = false;
            } else {
                $paged = true;
            }

            if (!isset(self::$instance->obj)) {
                self::$instance->obj = new stdClass();
                self::$instance->obj->post_type = 'none';
            }

            switch (true) {
                case is_home():
                    self::$instance->obj_nature = 'home';
                    BREAK;
                case $paged:
                    self::$instance->obj_nature = 'paged';
                case is_front_page():
                    self::$instance->obj_nature = 'home';
                    BREAK;
                case is_singular():
                    self::$instance->obj_nature = 'single';
                    BREAK;
                case is_category():
                    self::$instance->obj_nature = 'category';
                    BREAK;
                case is_tag():
                    self::$instance->obj_nature = 'tag';
                    BREAK;
                case is_search():
                    self::$instance->obj_nature = 'search';
                    BREAK;
                case is_admin():
                    self::$instance->obj_nature = 'admin';
                    BREAK;
                case is_archive():
                    self::$instance->obj_nature = 'archive';
                    BREAK;
                case is_post_type_archive():
                    self::$instance->obj_nature = 'archive';
                    BREAK;
                case is_feed():
                    self::$instance->obj_nature = 'feed';
                    BREAK;
                case is_sticky():
                    self::$instance->obj_nature = 'sticky';
                    BREAK;

            }

            do_action('wp_cta_after_global_init' ,	$this );
        }

        /**
         *  	Determine if content ID has Calls to Actions assigned to it
         */
        public function setup_cta_direct_placement( $is_preview = false ) {

            /* Determine which CTA's should be rotated on this page - When in preview mode use CTA ID as selected CTA */
            if ($is_preview === true) {
                $cta_display_list = array( self::$instance->obj_id );
            } else {
                $cta_display_list = get_post_meta( self::$instance->obj_id, 'cta_display_list', true );
            }

            $cta_display_list = apply_filters('wp_cta_display_list', $cta_display_list );

            if ( !$cta_display_list ) {
                return;
            }

            /* Determine where we should place the call to action selected to appear on this page */
            self::$instance->cta_content_placement = get_post_meta( self::$instance->obj_id, 'cta_content_placement',	true);

            self::$instance->cta_content_placement = apply_filters('wp_cta_content_placement', self::$instance->cta_content_placement );

            if ( self::$instance->cta_content_placement == 'off' ) {
                return;
            }

            /* Generate a dataset of data related to selected CTAs */
            self::$instance->selected_cta = self::$instance->prepare_cta_dataset( $cta_display_list );	/* builds a list of ct */
        }


        /**
         * Place CTA content in middle of text
         * @param $content
         * @param $cta
         * @return string
         */
        public static function place_in_middle( $content , $cta ) {

            $target = '<p>';
            $target_count = substr_count($content, $target);
            $middle = $target_count / 2;

            $middle = ($middle && $middle > 1 ) ? round($middle) : $middle;

            $content = explode("</p>", $content);
            $new_content = '';
            for ($i = 0; $i < count($content); $i++) {

                /* cta to top when ol or li not detected */
                if ($i != $middle) {
                    $new_content.= $content[$i] . '</p>';
                    continue;
                }

                /* Handle paragraphs with special presentations differently*/
                $special = array('<ul>','<ol>','<blockquote>','<object>','<iframe>');
                if (array_search(strtolower($content[$i]), array_map('strtolower', $special))) {
                    /* cta to end when ol or li detected */
                    $new_content.= $content[$i];
                    $new_content.= '<p>'.$cta.'</p>';

                } else {
                    /* cta to top when ol or li not detected */
                    $new_content.= '<p>'.$cta.'</p>';
                    $new_content.= $content[$i];
                }

                /* add p back */
                $new_content.= '</p>';
            }



            return $new_content;

        }

        /**
         *  Generate a set of data related to CTA(s)
         *  @param ARRAY $cta_display_list array of cta id(s)
         */
        public static function prepare_cta_dataset( $cta_display_list, $variation_id = null) {
            global $CTA_Variations;

            if ( !$cta_display_list ) {
                return array();
            }

            foreach ($cta_display_list as $key => $cta_id) {

                $url = get_permalink( $cta_id );

                $cta_obj[$cta_id]['id'] = $cta_id;
                $cta_obj[$cta_id]['url'] = $url;

                /* If variation is predefined load only that variations data else load all variation data for a given cta */
                if ( $variation_id !== null ) {
                    $cta_obj[$cta_id]['variations'] = $CTA_Variations->get_variations( $cta_id, $variation_id );
                } else {
                    $cta_obj[$cta_id]['variations'] = $CTA_Variations->get_variations( $cta_id );
                }

                /* Get meta of cta */
                $meta = get_post_meta(	$cta_id ); // move to ext

                /* if no meta then bail, this is an unprepared CTA */
                if (!$meta) {
                    return;
                }

                /* Loop through cta variations and improve data set */
                foreach ($cta_obj[$cta_id]['variations'] as $vid => $variation) {

                    /* if variation does not have a selected template then treat as broken and unset from dataset */
                    if ( !isset($meta['wp-cta-selected-template-' . $vid ][0]) ) {
                        unset($cta_obj[$cta_id]['variations'][$vid]);
                        continue;
                    }

                    /* if cta is paused and not in preview mode then unset from dataset */
                    if ( $variation['status'] == 'paused' && !isset($_GET['wp-cta-variation-id']) ) {
                        unset($cta_obj[$cta_id]['variations'][$vid]);
                        continue;
                    }

                    $template_slug = $meta['wp-cta-selected-template-' . $vid ][0];
                    $cta_obj[$cta_id]['templates'][$vid]['slug'] = $template_slug;
                    $cta_obj[$cta_id]['meta'][$vid]['wp-cta-selected-template-'.$vid] = $template_slug;

                    /* determine where template exists for asset loading	*/
                    if (file_exists( WP_CTA_PATH.'templates/'.$template_slug )) {
                        $cta_obj[$cta_id]['templates'][$vid]['path'] = WP_CTA_PATH.'templates/'.$template_slug.'/';
                        $cta_obj[$cta_id]['templates'][$vid]['urlpath'] = WP_CTA_URLPATH.'templates/'.$template_slug.'/';
                    } else {
                        //query_posts ($query_string . '&showposts=1');
                        $cta_obj[$cta_id]['templates'][$vid]['path'] = WP_CTA_UPLOADS_PATH.$template_slug.'/';
                        $cta_obj[$cta_id]['templates'][$vid]['urlpath'] = WP_CTA_UPLOADS_URLPATH.$template_slug.'/';
                    }

                    /* get variation meta */
                    $cta_obj[$cta_id]['meta'][$vid] = CTA_Variations::get_variation_meta ( $cta_id, $vid );

                }

            }


            /* let them improve or alter the dataset */
            $cta_obj = apply_filters( 'wp_cta_obj', $cta_obj );


            /* return one cta out of list of available ctas */
            $key = array_rand($cta_obj);

            return $cta_obj[$key];
        }

        /**
         *  Loop through cta varaition html and create masked links
         *  @param HTML $varaition_html html of variation being processed
         *  @param ARRAY $selected_cta dataset of parent call to action
         *  @param INT $vid id of variation being processed
         */
        public static function prepare_tracked_links( $variation_html, $selected_cta, $vid ) {
            global $post;

			$variation_html = do_shortcode($variation_html);
            $doc = new DOMDocument();

            if (!function_exists('mb_convert_encoding')) {
                @$doc->loadHTML($variation_html);
            } else {
                @$doc->loadHTML( mb_convert_encoding($variation_html, 'HTML-ENTITIES', 'UTF-8'));
            }

            foreach($doc->getElementsByTagName('a') as $anchor) {
                /* skip links with do-not-track in class */
                $class = $anchor->getAttribute('class');

                if (strstr( $class, 'do-not-track' )) {
                    continue;
                }

                $href = $anchor->getAttribute('href');

                if (strstr( $href, 'do-not-track' )) {
                    continue;
                }

                /* if not a valid link move on */
                if ( !strstr( $href , '.' ) && !strstr( $href , 'tel:' )&& !strstr( $href , 'mailto:' ) ) {
                    continue;
                }

                /* add nofollow to link */
                $rel = array();

                if ($anchor->hasAttribute('rel') AND ($relAtt = $anchor->getAttribute('rel')) !== '') {
                    $rel = preg_split('/\s+/', trim($relAtt));
                }

                if (in_array('nofollow', $rel)) {
                    continue;
                }

                $rel[] = 'nofollow';
                $anchor->setAttribute('rel', implode(' ', $rel));

                /* prepare tracked link */
                $link = Inbound_API::analytics_track_links( array(
                    'cta_id' => $selected_cta['id'],
                    'id' => null, /* lead_id - let's not set this here */
                    'page_id' => ( isset($post) && $post->ID  ? $post->ID : null ) ,
                    'vid' => $vid ,
                    'url' => $href ,
                    'tracking_id' => __( sprintf( 'Call to Action Click (cta_id:%s) (vid:%s)', $selected_cta['id'], $vid ), 'inbound-pro' ) /* required but not being used atm */
                ));

                /* standardize & symbol */
                $link['url'] = str_replace('&amp;', '&' , $link['url'] );
                //$href = str_replace('&amp;', '&' , $href );

                $anchor->setAttribute('rel', implode(' ', $rel));

                $anchor->setAttribute('href', $link['url']);

            }

            $doc->saveHTML();

            $variation_html = '';

            foreach($doc->getElementsByTagName('body')->item(0)->childNodes as $element) {
                $variation_html .= $doc->saveXML($element, LIBXML_NOEMPTYTAG);
            }

            /* remove cdata */
            $variation_html = str_replace('<![CDATA[' , '' , $variation_html);
            $variation_html = str_replace(']]>' , '' , $variation_html);

            return $variation_html;
        }

        /**
         *  Given a template slug, get it's asset files.
         */
        static function get_template_asset_files($template) {

            $files = get_transient('wp_cta_assets_'.$template['slug']);

            if ($files) {
                return $files;
            } else {
                $files = array();
            }

            /*	Check if Dirs exist first */
            $has_js_dir = WP_CTA_PATH.'templates/'.$template['slug'].'/assets/js/';
            $has_style_dir = WP_CTA_PATH.'templates/'.$template['slug'].'/assets/css/';

            if(file_exists($has_js_dir)) {
                /* get js files */
                $results = scandir($template['path'].'assets/js/');

                foreach ($results as $name) {
                    if (pathinfo($name, PATHINFO_EXTENSION) != 'js') {
                        continue;
                    }
                    $files['js'][] = $template['urlpath'].'assets/js/'.$name;
                }
            }

            if(file_exists($has_style_dir)) {
                /* get css files */
                $results = scandir($template['path'].'assets/css/');
                foreach ($results as $name) {
                    if (pathinfo($name, PATHINFO_EXTENSION) != 'css') {
                        continue;
                    }
                    $files['css'][] = $template['urlpath'].'assets/css/'.$name;
                }


            }

            set_transient( 'wp_cta_assets_'.$template['slug'], $files, 60*60*12 );
            return $files;
        }

        /**
         *  Enqueue CSS & JS
         */
        public function enqueue_scripts() {
            global $post, $inbound_settings;

            /* Get static variables */
            self::$instance->split_testing = CTA_Settings::get_setting('wp-cta-main-split-testing', 1 );
            self::$instance->sticky_cta = CTA_Settings::get_setting('wp-cta-main-sticky-ctas', 1 );

            $post_id = self::$instance->obj_id;

            /* Setup determine variation global function */
            if ( isset( $_GET['wp-cta-variation-id'] ) ) {
                self::$instance->selected_cta['id'] =  intval($_GET['wp-cta-variation-id']);
            }

            /* determine ajax url */
            $ajax_url =  admin_url( 'admin-ajax.php' );

            /* determine if inbound_track_lead action will be available for use */
            $inbound_settings['inbound-analytics-rules'] = ( isset( $inbound_settings['inbound-analytics-rules']) ) ? $inbound_settings['inbound-analytics-rules'] : array();
            $page_tracking = ( isset( $inbound_settings['inbound-analytics-rules']['page-tracking']) ) ? $inbound_settings['inbound-analytics-rules']['page-tracking'] : 'on';
            $page_tracking = ( class_exists('Inbound_Pro_Leads') ) ? $page_tracking : 'off';

            /* cta preview mode uses shortcodes that call this manually */
            if (isset($post) && $post->post_type == 'wp-call-to-action' ) {
                $cta_id = 0;
            } else {
                $cta_id = self::$instance->selected_cta['id'];
            }

            wp_enqueue_script( 'cta-load-variation', WP_CTA_URLPATH . 'assets/js/cta-variation.js', array('jquery') , null , false);
            wp_localize_script( 'cta-load-variation', 'cta_variation', array('cta_id' => $cta_id, 'admin_url' => admin_url( 'admin-ajax.php'), 'home_url' => get_home_url(), 'split_testing' => self::$instance->split_testing, 'sticky_cta' => self::$instance->sticky_cta ,  'page_tracking' => $page_tracking ));


            /* If placement is popup load popup asset files */
            if ( self::$instance->cta_content_placement === 'popup') {
                $popup_timeout = get_post_meta($post_id, 'wp_cta_popup_timeout', TRUE);
                $pop_time_final = (!empty($post_id)) ? $popup_timeout * 1000 : 3000;
                $popup_cookie = get_post_meta($post_id, 'wp_cta_popup_cookie', TRUE);
                $popup_cookie_length = get_post_meta($post_id, 'wp_cta_popup_cookie_length', TRUE);
                $popup_pageviews = get_post_meta($post_id, 'wp_cta_popup_pageviews', TRUE);
                $global_cookie = get_option( 'wp-cta-main-global-cookie', 0 );
                $global_cookie_length = get_option( 'wp-cta-main-global-cookie-length', 30 );

                $popup_params = array(	'timeout' => $pop_time_final,
                    'c_status' => $popup_cookie,
                    'c_length' => $popup_cookie_length,
                    'page_views'=> $popup_pageviews,
                    'global_c_status' => $global_cookie,
                    'global_c_length' => $global_cookie_length
                );

                wp_enqueue_style('maginificient-popup', INBOUNDNOW_SHARED_URLPATH . 'assets/css/magnific-popup.css');
                wp_enqueue_script('maginificient-popup', INBOUNDNOW_SHARED_URLPATH . 'assets/js/global/jquery.magnific-popup.min.js',array('jquery'), null , false);
                wp_localize_script( 'maginificient-popup', 'wp_cta_popup', $popup_params );
                wp_enqueue_script('cta-popup-onpage', WP_CTA_URLPATH . 'assets/js/cta-popup-onpage.js', array('jquery', 'maginificient-popup'), null , false);
            }

            if (!self::$instance->selected_cta) {
                return;
            }

            if (self::$instance->is_preview) {
                return;
            }

            /* Import CSS & JS from Assets folder and Enqueue */
            $loaded = array();
            foreach (self::$instance->selected_cta['templates'] as $template) {
                if ( in_array( $template['slug'], $loaded) ) {
                    continue;
                }

                $loaded[] = $template['slug'];
                $assets = self::$instance->get_template_asset_files($template);
                $localized_template_id = str_replace( '-', '_', $template['slug'] );
                if (is_array($assets)) {
                    foreach ($assets as $type => $file) {
                        if (!is_array($file)) {
                            continue;
                        }

                        switch ($type) {
                            case 'js':
                                foreach ($file as $js)
                                {
                                    wp_enqueue_script( md5($js) ,$js, array( 'jquery') , null , true);
                                    wp_localize_script( md5($js), $localized_template_id, array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => self::$instance->obj_id, 'post_type' => self::$instance->obj->post_type));
                                }
                                break;
                            case 'css':
                                foreach ($file as $css)
                                {
                                    wp_enqueue_style( md5($css), $css );
                                }
                                break;
                        }
                    }
                }
            }
        }

        /**
         * Prints / Returns Custom JS & CSS Related to Call to Action
         */
        public static function load_custom_js_css( $selected_cta = null, $return = false ) {

            global $post;
            $inline_content = "";

            ($selected_cta) ? $selected_cta : $selected_cta = self::$instance->selected_cta;

            if (!isset($selected_cta['id'])){
                return;
            }

            foreach ($selected_cta['variations'] as $vid => $variation) {

                /* account for preview mode */
                if (isset($_GET['wp-cta-variation-id']) && ( $vid != $_GET['wp-cta-variation-id'] ) ) {
                    continue;
                }


                $meta = $selected_cta['meta'][$vid];

                $template_slug = $selected_cta['meta'][$vid]['wp-cta-selected-template-'.$vid];
                $custom_css = CTA_Variations::get_variation_custom_css ( $selected_cta['id'], $vid );


                $dynamic_css = self::$instance->cta_templates[$template_slug]['css-template'];
                $dynamic_css = self::$instance->replace_template_variables( $selected_cta, $dynamic_css, $vid );

                $css_id_preface = "#wp_cta_" . $selected_cta['id'] . "_variation_" . $vid;

                $dynamic_css = str_replace("{{", "", $dynamic_css);
                $dynamic_css = str_replace("}}", "", $dynamic_css);

                $dynamic_css = self::$instance->parse_css_template($dynamic_css, $css_id_preface);

                $css_styleblock_class = apply_filters( 'wp_cta_styleblock_class', '', $selected_cta['id'], $vid );

                $custom_css = strip_tags($custom_css,'<style>');

                /* If style.css exists in root cta directory, insert here */
                $slug = $selected_cta['templates'][$vid]['slug'];
                $has_style = WP_CTA_PATH.'templates/'.$slug.'/style.css';
                $has_style_url = WP_CTA_URLPATH.'templates/'.$slug.'/style.css';
                if(file_exists($has_style)) {
                    $inline_content .= '<link rel="stylesheet" href="'.$has_style_url.'">';
                }

                /* Print Cusom CSS */
                $inline_content .= '<style type="text/css" id="wp_cta_css_custom_'.$selected_cta['id'].'_'.$vid.'" class="wp_cta_css_'.$selected_cta['id'].' '.$css_styleblock_class.'">'.$custom_css.' '.$dynamic_css.'</style>';

                $custom_js = CTA_Variations::get_variation_custom_js ( $selected_cta['id'], $vid );

                if (!stristr($custom_css,'<script'))
                {
                    $inline_content .= '<script type="text/javascript">jQuery(document).ready(function($) {
					'.$custom_js.' });</script>';
                }
                else
                {
                    $inline_content .= $custom_js;
                }
            }

            if ( $return ) {
                return $inline_content;
            } else {
                echo $inline_content;
            }
        }

        /**
         *  Replaced tokens in call to action template with values
         */
        public function replace_template_variables( $selected_cta, $template, $vid ) {

            /* Ger template slug */
            $template_slug = $selected_cta['meta'][$vid]['wp-cta-selected-template-'.$vid];

            /* Get all tokens */
            preg_match_all('/{%+(.*?)%}/', $template, $php_tokens); // check for conditionals

            /* Get width and height */
            $w = (isset($selected_cta['meta'][$vid]['wp_cta_width-'.$vid])) ? $selected_cta['meta'][$vid]['wp_cta_width-'.$vid] : 'auto';
            $h = (isset($selected_cta['meta'][$vid]['wp_cta_height-'.$vid])) ? $selected_cta['meta'][$vid]['wp_cta_height-'.$vid] : 'auto';

            /* validate/correct impropper css property value setup */
            $width = CTA_Render::validate_css_property_value($w, 'width');
            $height = CTA_Render::validate_css_property_value($h, 'height');

            /* replace core tokens if available */
            $template = str_replace( '{{cta-id}}', $selected_cta['id'], $template );
            $template = str_replace( '{{variation-id}}', $vid, $template );
            $template = str_replace( '{{template-urlpath}}', $selected_cta['templates'][$vid]['urlpath'], $template );
            $template = str_replace( '{{wordpress-ajaxurl}}', admin_url( 'admin-ajax.php'), $template );
            $template = str_replace( '{{cta-width}}', $width, $template );
            $template = str_replace( '{{cta-height}}', $height, $template );
            $template = str_replace( '{{width}}', $w, $template );
            $template = str_replace( '{{height}}', $h, $template );

            /* Add special loop here with filter for custom tokens */
            $false_match = array();
            $count_of_loop = count($selected_cta['meta'][$vid]);
            $token_array = array();
            $final_token_array = array();
            $global_val_array = array();

            if (!isset($selected_cta['meta'][$vid]) ) { $selected_cta['meta'][$vid] = array(); }

            foreach ($selected_cta['meta'][$vid] as $key=>$value) {

                if (strlen($key)> 90) {
                    continue;
                }

                $key = str_replace( $template_slug.'-', '', $key );
                $key = str_replace('-'.$vid, '', $key );

                if ($key==='content'){
                    continue;
                }
                $original_value = $value;
                $correct_key = '';

                $thispattern = '/{{'.$key.'\|+(.*?)}}/';
                preg_match_all($thispattern, $template, $token_matchs);
                /*
                echo "<pre>";
                if (!empty($token_matchs[0])){
                    print_r($token_matchs[0]);
                }
                echo "</pre>";
                */


                $pattern = '/{{'.$key.'\|+(.*?)}}/';
                if (preg_match($pattern, $template, $token_matches)) {
                    //print_r($token_matches);

                    $show_debug_token = false; // Set to true to view the debugs
                    $raw_php_function = false; // Adds ability to run raw php
                    $token_match = $token_matches[0];
                    //echo "TOKEN:" . $token_match . "<br>";
                    //$pos = strrpos($token_match, "|");
                    if (preg_match('/\|/', $token_match)) {
                        //echo "False match:" . $key . " <br>";
                        $false_match_item = $template_slug.'-' . $key . '-'.$vid;
                        $false_match[] = $false_match_item;
                        if ($show_debug_token) {
                            echo "<br><span style='color:red'>Token MATCH ON:</span> " . $token_match . "<br>";
                        }

                        $clean_key = str_replace(array("{", "}"), "", $token_match);

                        $separate_token = explode('|', $clean_key); // split at pipe


                        $correct_key = $separate_token[0];
                        $full = $template_slug.'-' . $correct_key . '-'.$vid;
                        // Set Correct Value
                        $value = $selected_cta['meta'][$vid][$full]; // reset value to correct key;
                        $key = $clean_key; // set correct key
                        $global_val_array[$correct_key] = $value;
                        //echo $key;

                        // Merge and fix missing vars
                        $final_token_array[$value] = $token_matchs[0];
                        $token_array = array_merge($token_array, $token_matchs[0]);


                        /* Run Special Parse Functions Here */
                        $run_function = $separate_token[1];
                        $function_name = explode("(", $run_function);

                        preg_match('#\((.*?)\)#', $run_function, $fun_match);
                        if (is_array($fun_match)){

                            $function_args = (isset($fun_match[1])) ? $fun_match[1] : '';
                            $function_args_array = explode(',', $function_args);
                            $args = $function_args_array;
                            if(empty($args[0])) {
                                if ($show_debug_token) {
                                    echo "NO params set default value<br>";
                                }
                                $args[0] = $value;
                            }
                        }

                        if(preg_match("/php:/", $run_function)) {
                            if ($show_debug_token) {
                                echo "PHP function";
                                echo $function_name[0];
                            }
                            $php_function = str_replace("php:", '', $function_name[0]);
                            $raw_php_function = true; // Adds ability to run raw php
                        }

                        $function_args = array();
                        $function_args[0] = $value;
                        foreach ($args as $arr_key => $arr_value) {

                            if ($arr_value === "this"){
                                $function_args[$arr_key + 1] = $value;
                                if ($show_debug_token) {
                                    //echo "arg" . $arr_key. ":" . $arr_value;
                                }
                                // first value always user input val
                            } else {
                                $function_args[$arr_key + 1] = $arr_value;
                            }

                            if ($show_debug_token) {
                                echo "arg" . $arr_key. ":" . $arr_value . ", ";
                            }

                        }

                        $function_args = array_unique($function_args); // dedupe values

                        if (count($function_args) < 2 ) {
                            $function_args = $function_args[0]; // send single value to function
                        }
                        //echo $run_function;
                        /* Function temp references
                        replace: {{ "I like %this% and %that%."|replace({'%this%': foo, '%that%': "bar"}) }}
                        */
                        if ($raw_php_function) {
                            $template_function = $php_function;
                        } else {
                            $template_function = 'inbound_template_' . $function_name[0];
                        }

                        /* If function exists run it */
                        if (function_exists($template_function)) {
                            $value = $template_function($function_args);
                            if ($show_debug_token) {
                                echo "<br>Running Function: <strong>" .	$template_function . "</strong> with args <strong>";
                                print_r($function_args);
                                echo "</strong><br>";
                                echo "<br>";
                                $look_for = "{{" .$key . "}}";
                                $reg = preg_quote( "{{" .$key . "}}");
                                echo "replace " . $look_for . " with ". $value;
                                //$clean = '/'.$look_for.'/';
                                //str_ireplace( $look_for, $value, $template);
                                //$template = preg_replace($clean, $value, $template);
                                //preg_match($clean, $template, $temp_match);
                                //print_r($temp_match);
                            }
                        }

                    }



                }

                $template = str_ireplace( '{{'.$key.'}}', $value, $template); // single space
                $template = str_ireplace( '{{ '.$key.' }}', $value, $template); // double space
                $template = str_ireplace( '{{'.$correct_key.'}}', $original_value, $template); // single space
                $template = str_ireplace( '{{ '.$correct_key.' }}', $original_value, $template); // double space
                $template = str_ireplace( '{'.$key.'}', $value, $template); // legacy template token
            }

            $advanced_token_array = array_unique($token_array); // Needs to be looped through to clean up missed tokens

            foreach ($advanced_token_array as $key => $thisvalue) {
                //echo $thisvalue . '<br>';
                $full_token = $thisvalue;
                $thisvalue = str_replace(array("{{", "}}"), "", $thisvalue);
                $separate_token = explode('|', $thisvalue); // split at pipe
                $correct_key = $separate_token[0];
                $value = $global_val_array[$correct_key];

                /* run function procssing here inbound_run_processing might be able to replace code above eventually */
                $template = self::support_conditional_tags( $full_token, $correct_key, $value, $template );
            }

            /* Remove tokens that arent matched */
            $clean_unmatched_regex = '/{{+(.*?)}}/';
            $template = preg_replace($clean_unmatched_regex, "", $template);

            /* Do PHP processing from {%php php_function %} tokens */
            preg_match_all('/{%php+(.*?)%}/', $template, $php_tokens);
            foreach ($php_tokens as $key => $value) {
                if (is_array($value) && !empty($value[0])) {
                    $debug_output = false;
                    foreach ($value as $test => $phpcode) {
                        $clean_val = str_replace(array("{%php", "%}"), "", $phpcode);
                        $return_val = eval($clean_val);
                        //echo $return_val;

                        if($debug_output)
                        {
                            echo "<br>PHP : " . $clean_val . "<br>";
                            echo "PHP evaled: " . "<br>";
                            echo "<br>Replacement " . $test . "<br>";
                        }


                        $template = str_ireplace( $phpcode, $return_val, $template);
                    }
                }
            }
            //(?<={% if )(.*)(?=%})
            /* Match Conditionals {% if {{var-name}} === "XXXX" %} tokens */
            preg_match_all('/{%+(.*?)%}/', $template, $conditional_tokens);
            $conditional_tokens = array_filter($conditional_tokens);

            foreach ($conditional_tokens as $key => $value) {
                if (is_array($value) && !empty($value[0])) {
                    $debug_output = false;
                    foreach ($value as $test => $conditional_code) {
                        //echo $conditional_code;
                        $clean_val = trim(str_replace(array("{%", "%}"), "", $conditional_code));
                        $pieces = explode(" ", $clean_val); // explode string into function parts

                        if (count($pieces) > 2) {
                            $function = $pieces[0] . "(" . $pieces[1] . $pieces[2] . $pieces[3] . ") {";
                            $function .= 'return TRUE;';
                            $function .= '}';

                            $return_val = eval($function);

                            if (!$return_val){
                                $template = self::delete_all_inbetween($conditional_code, '{% endif %}', $template);
                            }

                        }

                        //$value = $global_val_array[$correct_key];
                        //$return_val = eval($clean_val);
                        //echo $return_val;
                        if($debug_output) {
                            error_log("<br>Template:".$template_slug);
                            error_log("<br>Conditional : " . $clean_val);
                            error_log("PHP evaled: ");
                            error_log("<br>Replacement " . $test . "<br>");
                        }
                        /* Clean all Conditional Tokens out of final output */
                        $template = str_ireplace( $conditional_code, '', $template);
                    }
                }
            }

            /* Add target tags to links */
            if (get_post_meta( $selected_cta['id'], 'wp-cta-link-open-option-' . $vid, true ) == 'new_tab' ) {
                $template = str_replace('<a ', '<a target="_blank" ', $template);
            }

            return $template;
        }

        /**
         *  Adds support for conditional tags to the token engine
         */
        public static function support_conditional_tags($token_match, $key, $value, $template) {

            if (!preg_match('/\|/', $token_match)) {
                return $template;
            }

            $show_debug_token = false;
            $raw_php_function = false; // Adds ability to run raw php

            if ($show_debug_token) {
                echo "<br><span style='color:red'>Token MATCH ON:</span> " . $token_match . " Val: ". $value . "<br>";
            }

            $clean_key = str_replace(array("{", "}"), "", $token_match);

            $separate_token = explode('|', $clean_key); // split at pipe
            $correct_key = $separate_token[0];

            /* Run Special Parse Functions Here */
            $run_function = $separate_token[1];
            $function_name = explode("(", $run_function);

            preg_match('#\((.*?)\)#', $run_function, $fun_match);
            if (is_array($fun_match)){

                $function_args = (isset($fun_match[1])) ? $fun_match[1] : '';
                $function_args_array = explode(',', $function_args);
                $args = $function_args_array;
                if(empty($args[0])) {
                    if ($show_debug_token) {
                        echo "NO params set default value<br>";
                    }
                    $args[0] = $value;
                }
            }

            if(preg_match("/php:/", $run_function)) {
                if ($show_debug_token) {
                    echo "PHP function";
                    echo $function_name[0];
                }
                $php_function = str_replace("php:", '', $function_name[0]);
                $raw_php_function = true; // Adds ability to run raw php
            }

            $function_args = array();
            $function_args[0] = $value;
            foreach ($args as $arr_key => $arr_value) {

                if ($arr_value === "this"){
                    $function_args[$arr_key + 1] = $value;
                    if ($show_debug_token) {
                        //echo "arg" . $arr_key. ":" . $arr_value;
                    }
                    // first value always user input val
                } else {
                    $function_args[$arr_key + 1] = $arr_value;
                }

                if ($show_debug_token) {
                    echo "arg" . $arr_key. ":" . $arr_value . ", ";
                }

            }

            $function_args = array_unique($function_args); // dedupe values

            if (count($function_args) < 2 ) {
                $function_args = $function_args[0]; // send single value to function
            }

            /* Function temp references
            replace: {{ "I like %this% and %that%."|replace({'%this%': foo, '%that%': "bar"}) }}
            */

            if ($raw_php_function) {
                $template_function = $php_function;
            } else {
                $template_function = 'inbound_template_' . $function_name[0];
            }

            /* If function exists run it */
            if (function_exists($template_function)) {
                $value = $template_function($function_args);
                if ($show_debug_token) {
                    echo "<br>Running Function: <strong>" .	$template_function . "</strong> with args <strong>";
                    print_r($function_args);
                    echo "</strong><br>";

                    $look_for = "{{" .$key . "}}";
                    $reg = preg_quote( "{{" .$key . "}}");
                    echo "Replace " . $token_match . " with ". $value . "<br>";

                }
            }

            $template = str_ireplace( $token_match, $value, $template); // single space


            return $template;
        }

        /**
         *	Deletes content discovered in string between two other stringds
         *	@param STRING $beginning
         *	@param STRING $end
         *	@param STRING $string
         *	@return STRING modified string
         */
        public static function delete_all_inbetween($beginning, $end, $string) {

            $beginningPos = strpos($string, $beginning);
            $endPos = strpos($string, $end);

            if (!$beginningPos || !$endPos) {
                return $string;
            }

            $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

            return str_replace($textToDelete, '', $string);
        }



        /**
         *  Parse CSS and prepend the call to action / varition id
         */
        public static function parse_css_template( $dynamic_css, $css_id_preface ) {
            $dynamic_css = str_replace('{{', '[[', $dynamic_css);
            $dynamic_css = str_replace('}}', ']]', $dynamic_css);

            $oParser = new Sabberworm\CSS\Parser($dynamic_css);

            $oCss = $oParser->parse();

            foreach($oCss->getAllDeclarationBlocks() as $oBlock) {
                foreach($oBlock->getSelectors() as $oSelector) {
                    $oSelector->setSelector($css_id_preface.' '.$oSelector->getSelector());
                }
            }

            $dynamic_css = $oCss->__toString();

            $dynamic_css = str_replace('[[', '{{', $dynamic_css);
            $dynamic_css = str_replace(']]', '}}', $dynamic_css);

            return $oCss->__toString();
        }


        /**
         *  Validate CSS values
         */
        public static function validate_css_property_value($input, $css_prop) {
            if (preg_match("/px/", $input))	{
                $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
            } else if (preg_match("/auto/", $input)) {
                $input = " ".$css_prop.': '.$input.';';
            } else if (preg_match("/%/", $input)) {
                $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
            } else if (preg_match("/em/", $input)) {
                $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
            } else {
                $input = " ".$css_prop.": $input" . "px;";
            }

            return $input;
        }

        /**
         *  Creates html of cta variations and sets their visibility to hidden. Javascript will be used to display the correct variation.
         *  @param ARRAY $selected_cta dataset containing meta information on call to action
         *  @returns STRING $cta_template prepared html
         */
        function build_cta_content( $selected_cta = null ) 	{

            ($selected_cta) ? $selected_cta : $selected_cta = self::$instance->selected_cta;

            /* debug information */
            if (isset($_GET['debug-cta'])) {
                echo "<pre>";
                print_r($selected_cta);
                echo "</pre>";
            }

            /* Helper Function to output Template Tokens */
            if (isset($_GET['cta-tokens'])) {
                $template_slug = $selected_cta['templates'][0]['slug'];
                $token_array = $selected_cta['meta'][0];
                $ignore = array('_edit_last', 'wp-cta-selected-template', 'cta_ab_variations','wp-cta-variations', 'wp-cta-variation-notes', 'wp-cta-custom-css', 'wp-cta-custom-js', 'wp-cta-link-open-option', '_edit_lock', 'wp_cta_width', 'wp_cta_height');
                foreach ($token_array as $key => $value) {
                    $key = str_replace( $template_slug.'-', '', $key );
                    $key = str_replace('-0', '', $key );
                    if (!in_array($key, $ignore)) {
                        echo "{{" .$key . "}}<br>";
                    }
                }
            }

            /* Reveal Variation if Preview */
            (self::$instance->is_preview) ? $display = 'none' : $display = 'none';

            /* Pepare Container Margins if Available */
            $margin_top = (isset($selected_cta['margin-top'])) ? $selected_cta['margin-top'] : '0';
            $margin_bottom = (isset($selected_cta['margin-bottom'])) ? $selected_cta['margin-bottom'] : '0';

            /* discover the shortest variation height */
            foreach ($selected_cta['variations'] as $vid => $variation ) {
                $meta = $selected_cta['meta'][$vid];
                if ( isset($meta['wp_cta_height-'.$vid]) && is_int( $meta['wp_cta_height-'.$vid]) ) {
                    $heights[] = $meta['wp_cta_height-'.$vid];
                }
            }

            /* get the maximum height of all variations and use it as the min height */
            if (isset($heights)) {
                asort($heights);
                $min_height = $heights[0];
            } else {
                $min_height = 'auto;';
            }

            /* build cta container class */
            $cta_container_class = "wp_cta_container cta_outer_container";
            $cta_container_class =	apply_filters('wp_cta_container_class', $cta_container_class, $selected_cta['id'] );

            /* build cta parent container */
            $cta_template = "<div id='wp_cta_".$selected_cta['id']."_container' class='{$cta_container_class}' style='margin-top:{$margin_top}px;margin-bottom:{$margin_bottom}px;position:relative;' >";

            /* build cta content */
            foreach ($selected_cta['variations'] as $vid => $variation ) {

                /* if cta preview mode is on then skip non-selected variations */
                if (isset($_GET['wp-cta-variation-id']) && $vid!=$_GET['wp-cta-variation-id']) {
                    continue;
                }

                /* get width and height values */
                $w = (isset($selected_cta['meta'][$vid]['wp_cta_width-'.$vid])) ? $selected_cta['meta'][$vid]['wp_cta_width-'.$vid] : 'auto';
                $h = (isset($selected_cta['meta'][$vid]['wp_cta_height-'.$vid])) ? $selected_cta['meta'][$vid]['wp_cta_height-'.$vid] : 'auto';

                /* validate/correct impropper css property value setup */
                $width = self::$instance->validate_css_property_value($w, 'width');
                $height = self::$instance->validate_css_property_value($h, 'height');

                /* add cta width values into array pool for later use */
                $width_array[$vid] = $w;
                self::$instance->cta_width = $width_array;

                /* add height values into array pool for later use */
                $height_array[$vid] = $h;
                self::$instance->cta_height = $height_array;

                /* get variation's template slug name */
                $template_slug = $selected_cta['meta'][$vid]['wp-cta-selected-template-'.$vid];

                /* prepare the variation class */
                $cta_variation_class = "inbound-cta-container wp_cta_content wp_cta_variation wp_cta_".$selected_cta['id']."_variation_".$vid."";
                $cta_variation_class =	apply_filters('wp_cta_variation_class', $cta_variation_class, $selected_cta['id'], $vid );

                /* prepare additional attributes for cta varaition */
                $cta_variation_attributes = apply_filters('wp_cta_variation_attributes', '', $selected_cta['id'], $vid);

                /* Prepare variation HTML container */
                $cta_template .= "<div id='wp_cta_".$selected_cta['id']."_variation_".$vid."' class='".$cta_variation_class."' style='display:{$display}; margin:auto;".$width . $height."' ".$cta_variation_attributes." data-variation='".$vid."' data-cta-id='".$selected_cta['id']."'>";

                $variation_html = self::$instance->cta_templates[$template_slug]['html-template'];

                /* Replace common token variables with their value */
                $variation_html = CTA_Render::replace_template_variables( $selected_cta, $variation_html, $vid	);

                /* replace all internal links with masked tracked links */
                $variation_html = CTA_Render::prepare_tracked_links( $variation_html, $selected_cta, $vid );

                $cta_template .= $variation_html;

                /* close variation container */
                $cta_template .= "</div>";

            }

            $cta_template .='</div>';

            return $cta_template;
        }

        /**
         *  Adds CTA to post content
         */
        function add_cta_to_post_content( $content ) {
            global $post;

            if (!isset($post) || is_feed()) {
                return $content;
            }

            if (!self::$instance->selected_cta || self::$instance->cta_content_placement=='off'){
                return $content;
            }

            if (self::$instance->cta_content_placement=='widget_1') {
                return $content;
            }

            /* Remove Additional Filters */
            remove_filter( 'the_content', array( $this, 'add_cta_to_post_content'), apply_filters('cta_the_content_priority', 5) );

            self::$instance->cta_template = self::$instance->build_cta_content();

            if (self::$instance->cta_content_placement=='above') {

                $content = "<div class='above_content'>" . self::$instance->cta_template. "</div>" . $content;

            } elseif (self::$instance->cta_content_placement=='middle') {
                $content = self::place_in_middle($content , self::$instance->cta_template);

                /* $count = strlen($content);
                $half =	$count/2;
                $left = substr($content, 0, $half);
                $right = substr($content, $half);
                $right = explode('. ',$right);
                $right[1] = self::$instance->cta_template.$right[1];
                $right = implode('. ',$right);
                $content =	$left.$right; */

            } elseif (self::$instance->cta_content_placement=='below') {
                $content = $content . "<div class='below_content'>" . self::$instance->cta_template . "</div>";

            } elseif (self::$instance->cta_content_placement=='popup') {
                $width = 0;

                foreach (self::$instance->cta_width as $vid => $value) {
                    if ($value>$width) {
                        $width = $value;
                    }
                }

                $width = str_replace('px','',$width);

                $content = $content . "<a id='cta-no-show' class='popup-modal' href='#wp-cta-popup'>Open modal</a><div id='wp-cta-popup' class='mfp-hide white-popup-block' style='display:none;width:".$width."px;'><button title='Close (Esc)' type='button' class='mfp-close'>&times;</button>" . self::$instance->cta_template . "</div>";
                //print_r(self::$instance->cta_width);exit;
                foreach (self::$instance->cta_width as $key => $value) {
                    $content .= "<span class='data-vid-w-".$key."' data-width='" . $value ."'></span>";
                }
                foreach (self::$instance->cta_height as $key => $value) {
                    $content .= "<span class='data-vid-h-".$key."' data-height='" . $value ."'></span>";
                }

                /**
                 * Add CSS
                 */
                $css = "<style type='text/css'>/* Custom CSS */
                            #cta-no-show, #the-popup-id, #cta-popup-id {
                              display: none !important;
                            }
                            #wordpress-cta {
                              text-align: center;
                            }
                            .white-popup-block {
                            background: transparent;
                            padding: 0px 0px;
                            text-align: left;
                            max-width: 750px;
                            margin: 40px auto;
                            position: relative;
                            }
                            .shortcode-popup-block {
                            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                            background: #fff;
                            padding: 0px;
                            text-align: left;
                            max-width: 85%;
                            margin: 20px auto;
                            position: relative;
                            }
                            .mfp-close {
                            color:#000 !important;
                            }</style>";

                $content = $content.$css;

                if (isset($_SESSION['inbound_popup']) && isset($post) && $_SESSION['inbound_popup'] == $post->ID && !current_user_can('manage_options')) {
                    return $content;
                }

                /* fix for popup size */
                $content .=	"<script>";
                $content .= "	jQuery(document).ready(function($) {";
                $content .= "		setTimeout(function() {";
                $content .= "		var vid = $('.inbound-cta-container:visible').attr('data-variation');";
                $content .= "		var vidw = '.data-vid-w-' + vid;";
                $content .= "		var vidh = '.data-vid-h-' + vid;";
                $content .= "		var h = $(vidh).attr('data-height');";
                $content .= "		var w = $(vidw).attr('data-width');";
                $content .= "		jQuery('.white-popup-block').css({'height': h, 'width': w});";
                $content .= "	 }, 500);";
                $content .= "	});";
                $content .= "</script>";

                $_SESSION['inbound_popup'] = $post->ID;

            }

            return $content;
        }

        /**
         *  Determines if cta is set to display in dynamic widget placeholder and if it is then redners it inside the appropriate hook
         */
        function add_cta_to_dynamic_widget() {
            if (!self::$instance->selected_cta || self::$instance->cta_content_placement=='off'){
                return;
            }

            self::$instance->cta_template = self::$instance->build_cta_content();

            if (self::$instance->cta_content_placement=='widget_1') {
                add_action('wp_cta_cta_dynamic_widget', array( $this, 'render_widget'), 10 );
            }
        }

        /**
         *  Renders shortcode in wp_cta_cta_dynamic_widget action hook
         */
        function render_widget() {
            echo do_shortcode(self::$instance->cta_template);
        }

        /**
         *  This method processes the [cta] shortcode
         */
        function process_shortcode_cta( $atts ) {
            extract(shortcode_atts(array(
                'id' => '',
                'vid' => null,
                'align' => 'none'
            ), $atts));

            $selected_cta	= self::$instance->prepare_cta_dataset( array($id), $vid );

            if ( !$selected_cta ) {
                return "";
            }

            $custom_css_js = self::load_custom_js_css($selected_cta, true);

            $cta_template = self::$instance->build_cta_content($selected_cta);

            /* account for preview mode  */
            $vid = (isset($_GET['wp-cta-variation-id'])) ? intval($_GET['wp-cta-variation-id']) : $vid;

            $script = self::$instance->load_shortcode_variation_js( $id, $vid, true );

            if ($align === 'right') {
                return	$script . $custom_css_js . '<div style="float:right;">' . do_shortcode($cta_template) . "</div>";
            }

            if ($align === 'left') {
                return	$script . $custom_css_js . '<div style="float:left;">' . do_shortcode($cta_template) . "</div>";
            }

            return	$script . $custom_css_js . do_shortcode($cta_template);
        }

        /**
         * Returns or Echos Script That Reveals Call to Action Variation
         * @param INT $cta_id
         * @param INT $variation_id
         * @param BOOL $return If set to true will return instead of print
         * @return STRING $script javascript code
         */
        function load_shortcode_variation_js( $cta_id, $variation_id = null, $return = false ) {

            $script =	"<script type='text/javascript'>";
            $script .= "	wp_cta_load_variation( '" .$cta_id ."', '" .$variation_id ."' )";
            $script .= "</script>";

            if ($return) {
                return $script;
            } else {
                echo $script;
            }
        }

        /**
         *  Preview CTA
         */
        function preview_cta() {

            if ((isset(self::$instance->obj->post_type) && self::$instance->obj->post_type != 'wp-call-to-action' ) || ( !isset(self::$instance->obj->post_type) && !isset($_GET['wp-cta-variation-id']))){
                return;
            }

            self::$instance->is_preview = true;

            self::$instance->setup_cta_direct_placement( true );

            $cta_id = self::$instance->obj->ID;

            if (!isset(self::$instance->selected_cta)) {
                return;
            }

            echo '<html style="margin-top:0px !important;">';
            echo '<head>';
            $template_path = get_stylesheet_directory_uri();
            $site_url = site_url();
            echo '<link rel="stylesheet" href="'.$template_path.'/style.css">';
            echo '<link rel="stylesheet" href="'.$site_url.'/wp-content/plugins/cta/shared/shortcodes/css/frontend-render.css">';

            do_action('wp_head' );

            wp_print_styles();
            echo '<style type="text/css">';
            echo 'body .wp_cta_container { margin-top:50px !important; }';
            echo '</style>';
            echo '</head>';

            echo '<body style="backgorund-image:none;background-color:#fff;width:100%;">';
            echo '<div id="cta-preview-container" style="margin:auto;">';

            if ( isset($_GET['post_id'] ) || isset($_GET['wp-cta-variation-id']) ) {
                echo do_shortcode('[cta id="'.$cta_id.'" vid="'.intval($_GET['wp-cta-variation-id']).'"]');
            } else {
                echo do_shortcode('[cta id="'.$cta_id.'"]');
            }

            echo "</div>";

            if (!isset($_GET['inbound-preview']) && is_user_logged_in()) { ?>
                <script type="text/javascript">

                    /* add jquery function to center cta in preview mode */
                    jQuery.fn.cta_center = function () {
                        this.css("position","absolute");
                        this.css("top", Math.max(0, ((jQuery(window).height() - jQuery(this).outerHeight()) / 2) +
                        jQuery(window).scrollTop()) + "px");
                        this.css("left", Math.max(0, ((jQuery(window).width() - jQuery(this).outerWidth()) / 2) +
                        jQuery(window).scrollLeft()) + "px");
                        return this;
                    }
                </script>
            <?php }

            if(isset($_GET['wp-cta-variation-id'])) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        jQuery('.wp_cta_<?php echo $cta_id; ?>_variation_<?php echo intval($_GET['wp-cta-variation-id']); ?>').show();
                    });
                </script>
                <?php
            }

            do_action('wp_footer');

            echo '</body>';
            echo '</html>';
            exit;
        }



        function modify_admin_url( $link ) {
            if (isset($_GET['page'])) {
                return $link;
            }

            if ((isset($post) && 'wp-call-to-action' == $post->post_type ) || ( isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='wp-call-to-action')) {
                $params['inbound-editor'] = 'false';
                if(isset($_GET['inbound-editor']) && $_GET['inbound-editor'] == 'true') {
                    $params['inbound-editor'] = 'true';
                }
                if(isset($_REQUEST['inbound-editor']) && $_REQUEST['inbound-editor'] == 'true') {
                    $params['inbound-editor'] = 'true';
                }
                $link = add_query_arg( $params, $link );
            }

            return $link;
        }
    }
}
/*
Util functions for token parser.
usage: {{token|function_name}}
usage: {{token|color}} run inbound_template_color on token
 */
if (!function_exists('inbound_template_color')) {
    function inbound_template_color($args){
        $prefix = "#";
        if (is_array($args)){
            $color = $args[0];
        } else {
            $color = $args;
        }
        if(preg_match("/rbg/", $color)) {
            $prefix = "";
        }

        return $prefix . $color;
    }
}
if (!function_exists('inbound_template_brightness')) {
    function inbound_template_brightness($args){

        $hex_color = $args[0];
        $hue = intval($args[1]);

        $format = 'hex';
        if (strpos($hex_color,'#') !== false) {
            $input = $hex_color;
        } else {
            $input = "#" . $hex_color;
        }

        $col = Array(
            hexdec(substr($input,1,2)),
            hexdec(substr($input,3,2)),
            hexdec(substr($input,5,2))
        );

        $color_scheme_array =
            array(
                100 => array( $col[0]/4, $col[1]/4, $col[2]/4),
                95 => array( $col[0]/3, $col[1]/3, $col[2]/3),
                90 => array( $col[0]/2.7, $col[1]/2.7, $col[2]/2.7),
                85 => array( $col[0]/2.5, $col[1]/2.5, $col[2]/2.5),
                80 => array( $col[0]/2.2, $col[1]/2.2, $col[2]/2.2),
                75 => array( $col[0]/2, $col[1]/2, $col[2]/2),
                70 => array( $col[0]/1.7, $col[1]/1.7, $col[2]/1.7),
                65 => array( $col[0]/1.5, $col[1]/1.5, $col[2]/1.5),
                60 => array( $col[0]/1.3,$col[1]/1.3,$col[2]/1.3),
                55 => array( $col[0]/1.1,$col[1]/1.1,$col[2]/1.1),
                50 => array( $col[0],$col[1],$col[2]),
                45 => array( 255-(255-$col[0])/1.1, 255-(255-$col[1])/1.1, 255-(255-$col[2])/1.1),
                40 => array( 255-(255-$col[0])/1.3, 255-(255-$col[1])/1.3, 255-(255-$col[2])/1.3),
                35 => array( 255-(255-$col[0])/1.5, 255-(255-$col[1])/1.5, 255-(255-$col[2])/1.5),
                30 => array( 255-(255-$col[0])/1.7, 255-(255-$col[1])/1.7, 255-(255-$col[2])/1.7),
                25 => array( 255-(255-$col[0])/2, 255-(255-$col[1])/2, 255-(255-$col[2])/2),
                20 => array( 255-(255-$col[0])/2.2, 255-(255-$col[1])/2.2, 255-(255-$col[2])/2.2),
                15 => array( 255-(255-$col[0])/3, 255-(255-$col[1])/2.7, 255-(255-$col[2])/3),
                10 => array(255-(255-$col[0])/5, 255-(255-$col[1])/5, 255-(255-$col[2])/5),
                5 => array(255-(255-$col[0])/10, 255-(255-$col[1])/10, 255-(255-$col[2])/10),
                0 => array(255-(255-$col[0])/15, 255-(255-$col[1])/15, 255-(255-$col[2])/15)
            );

        $sign = ($format === 'hex') ? "#" : '';
        $return_scheme = array();

        foreach ($color_scheme_array as $key => $val) {
            $each_color_return = $sign.sprintf("%02X%02X%02X", $val[0], $val[1], $val[2]);
            $return_scheme[$key] = $each_color_return;
        }

        if(isset($_GET['color_scheme'])) {
            foreach ($return_scheme as $key => $hex_value) {
                echo "<div style='background:$hex_value; display:block; width:100%;'>$key</div>";
            }
        }

        $new_color = $return_scheme[$hue];
        if (strpos($new_color,'#') !== false) {
            $return_color = $new_color;
        } else {
            $return_color = "#" . $new_color;
        }

        return $return_color;

    }
}

function wp_cta_check_active() {
    return 1;
}