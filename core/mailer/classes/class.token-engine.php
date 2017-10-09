<?php


/**
 * Class Inbound_Mailer_Tokens description could be refactored. Hosts various methods related to the unsubscribe token, 'lead fields' WYSIWYG feature support, various shortcodes that can be used inside an email, and the previewing of post data inside an automated email.
 *
 * @package Mailer
 * @subpackage	ShortcodesAndTokens
 */


class Inbound_Mailer_Tokens {


    /**
     *  Initialize class
     */
    public function __construct() {


        self::load_hooks();
    }

    /**
     *  Loads hooks and filters
     */
    public function load_hooks() {


        /* Listen for and process email tokens for email previews */
        add_filter('acf/format_value', array(__CLASS__, 'process_tokens') , 10 , 1);

        if (isset($_GET['disable_shortcodes'])) {
            return;
        }

        /* Add button  */
        add_action('media_buttons_context', array(__class__, 'token_button'), 99);

        /* Load supportive libraries */
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_js'));

        /* Add shortcode generation dialog */
        add_action('admin_footer', array(__CLASS__, 'token_generation_popup'));

        /* Add supportive js */
        add_action('admin_footer', array(__CLASS__, 'token_generation_js'));

        /* Add supportive css */
        add_action('admin_footer', array(__CLASS__, 'token_generation_css'));

        /* Add shortcode handler */
        add_shortcode('lead-field', array(__CLASS__, 'process_lead_field_shortcode'));

        /* Add shortcode handler */
        add_shortcode('post-link', array(__CLASS__, 'process_post_link_shortcode'));

        /* Add shortcode handler */
        add_shortcode('post-title', array(__CLASS__, 'process_post_title_shortcode'));

        /* Add shortcode handler */
        add_shortcode('post-content', array(__CLASS__, 'process_post_content_shortcode'));

        /* Add shortcode handler */
        add_shortcode('featured-image', array(__CLASS__, 'process_post_featured_image_shortcode'));

        /* Add shortcode handler for [unsubscribe-link] */
        add_shortcode('unsubscribe-link', array(__CLASS__, 'process_unsubscribe_link'));

        /* Add information to posts/pages on how to use content shortcodes */
        add_action('add_meta_boxes', array(__CLASS__, 'load_metaboxes'));

        /* Saves all all incoming POST data as meta pairs */
        add_action('save_post', array(__CLASS__, 'action_save_data'));

        /* on draft to publish update post locked status to pending */
        // to do
    }

    /**
     *  Displays token select button
     */
    public static function token_button() {
        global $post;

        if (
            !isset($post)
            ||
            (
                $post->post_type != 'inbound-email'
                &&
                $post->post_type != 'wp-lead'
            )
        ) {
            return;
        }

        $html = '<a href="#" class="button lead-fields-button" id="lead-fields-button-' . rand(10, 1200) . '" style="padding-left: .4em;"  >';
        $html .= '<span class="wp-media-buttons-icon" id="inbound_lead_fields_button"></span>' . __('Lead Fields', 'inbound-pro') . '</a>';

        return $html;
    }

    /**
     *  Enqueue JS
     */
    public static function enqueue_js() {
        global $post;

        if (!isset($post) || $post->post_type != 'inbound-email') {
            return;
        }

        /* Enqueue popupModal */
        wp_enqueue_script('popModal_js', INBOUND_EMAIL_URLPATH . 'assets/libraries/popModal/popModal.min.js', array('jquery'));
        wp_enqueue_style('popModal_css', INBOUND_EMAIL_URLPATH . 'assets/libraries/popModal/popModal.min.css');


    }

    /**
     *  Token/Shortcode generation script
     */
    public static function token_generation_popup() {
        global $post;

        if (!isset($post) || $post->post_type != 'inbound-email') {
            return;
        }

        $fields = Leads_Field_Map::build_map_array();
        ?>
        <div id="lead_fields_popup_container" style="display:none;">
            <table>
                <tr>
                    <td class='lf-label'>
                        <?php _e('Select Field', 'inbound-mail'); ?>
                    </td>
                    <td class='lf-value'>
                        <select id='lf-field-dropdown' class='form-control'>
                            <?php
                            array_shift($fields);
                            foreach ($fields as $id => $label) {
                                echo '<option value="' . $id . '">' . $label . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='lf-label'>
                        <?php _e('Set Default', 'inbound-mail'); ?>
                    </td>
                    <td class='lf-value'>
                        <input id="lf-default" value="" class='form-control'>
                    </td>
                </tr>
                <tr>
                    <td class='lf-submit' colspan='2'>
                        <span class='button-primary lf-submit-button' id="lf-insert-shortcode" href='#'><?php _e('Insert Shortcode', 'inbound-pro'); ?></span>
                    </td>
                </tr>
            </table>

        </div>
        <?php
    }

    /**
     *  Loads JS to support the token generation thickbox
     */
    public static function token_generation_js() {
        global $post;

        if (isset($post) && $post->post_type != 'inbound-email') {
            return;
        }

        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function () {

                /* Add listener to throw popup on button click */
                jQuery('body').on('click', '.lead-fields-button', function () {
                    jQuery('#' + this.id).popModal({
                        html: jQuery('#lead_fields_popup_container').html(),
                        placement: 'bottomLeft',
                        showCloseBut: true,
                        onDocumentClickClose: true,
                        onDocumentClickClosePrevent: '',
                        overflowContent: false,
                        inline: true,
                        beforeLoadingContent: 'Please, waiting...',
                        onOkBut: function () {
                        },
                        onCancelBut: function () {
                        },
                        onLoad: function () {
                        },
                        onClose: function () {
                        }
                    });
                });

                /* Add listener to generate shortcode */
                jQuery('body').on('click', '#lf-insert-shortcode', function () {
                    LFShortcode.build_shortcode();
                });

            });

            var LFShortcode = (function () {

                var field_id;
                var field_default;
                var shortcode;

                var construct = {
                    /**
                     *  Builds shortcode given inputs
                     */
                    build_shortcode: function () {
                        this.field_id = jQuery('#lf-field-dropdown').val();
                        this.field_default = jQuery('#lf-default').val();
                        this.generate_shortcode();
                        this.insert_shortcode();
                    },
                    /**
                     *  Generates html shortcode from given inputs
                     */
                    generate_shortcode: function () {
                        this.shortcode = '[lead-field id="'
                            + this.field_id
                            + '" default="'
                            + this.add_slashes(this.field_default)
                            + '"]';
                    },
                    /**
                     *  insert shortcode
                     */
                    insert_shortcode: function () {
                        wp.media.editor.insert(this.shortcode);
                        jQuery('.close').click();
                    },
                    /**
                     *  escapes quotation marks
                     */
                    add_slashes: function (string) {
                        return string.replace('"', '\"');
                        ;
                    }
                }

                return construct;
            })();
        </script>
        <?php
    }

    /**
     *  Loads CSS to support the token generation popup
     */
    public static function token_generation_css() {
        global $post;

        if (isset($post) && $post->post_type != 'inbound-email') {
            return;
        }

        ?>
        <style type='text/css'>
            .lf-label {
                width: 80px;
            }

            .lf-submit {
                padding-top: 10px;
                margin-bottom: -10px
            }

            .lf-submit-button {
                width: 100%;
            }
        </style>
        <?php
    }


    /**
     *  Process unsubscribe token
     */
    public static function process_unsubscribe_link($params) {

        $params = shortcode_atts(array(
            'lead_id' => '',
            'list_ids' => '-1',
            'email_id' => '-1',
            'rule_id' => '-1',
            'job_id' => '-1'
        ), $params);


        /* check to see if lead id is set as a REQUEST */
        if (isset($params['lead_id'])) {
            $params['lead_id'] = intval($params['lead_id']);
        } else if (isset($_REQUEST['lead_id'])) {
            $params['lead_id'] = intval($_REQUEST['lead_id']);
        } else if (isset($_COOKIE['wp_lead_id'])) {
            $params['lead_id'] = intval($_COOKIE['wp_lead_id']);
        }

        /* Add variation id to unsubscribe link */
        $params['variation_id'] = (isset($_REQUEST['inbvid'])) ? intval($_REQUEST['inbvid']) : intval(0);

        /* generate unsubscribe link */
        $unsubscribe_link = Inbound_Mailer_Unsubscribe::generate_unsubscribe_link($params);

        return $unsubscribe_link;
    }


    /**
     *  Process [lead-field] shortcode
     * @param ARRAY $params
     */
    public static function process_lead_field_shortcode($params) {
        global $post;

        $lead_id = null;

        $params = shortcode_atts(array('default' => '', 'id' => '','key' => '', 'lead_id' => null), $params);

        /* check to see if lead id is set as a REQUEST */
        if (isset($params['lead_id'])) {
            $lead_id = intval($params['lead_id']);
        } else if (isset($_REQUEST['lead_id'])) {
            $lead_id = intval($_REQUEST['lead_id']);
        } else if (isset($_COOKIE['wp_lead_id'])) {
            $lead_id = intval($_COOKIE['wp_lead_id']);
        }

        /* return default if no lead id discovered */
        if (!$lead_id) {
            return $params['default'];
        }

        $params['key'] = (isset($params['key'])) ? $params['key']: $params['id'] ;

        /* get lead value */
        $value = Leads_Field_Map::get_field($lead_id, $params['id']);

        /* return lead field value if it exists */
        if ($value) {
            return (is_array($value)) ? implode(',' , $value ) : $value;
        } else {
            return $params['default'];
        }
    }

    /**
     * Shortcode to generate post content
     */
    public static function process_post_content_shortcode($params) {

        $params = shortcode_atts(array('id' => null, 'strip-images' => false, 'strip-links' => false), $params);


        $post = get_post(trim($params['id']));

        /* remove cta shortcode */
        $pattern = '/\[(cta).*?\]/';
        $content = preg_replace($pattern, '', $post->post_content);
        $content = do_shortcode($content);
        $content = wpautop($content);

        if ($params['strip-images'] && $params['strip-images'] != "false") {
            $content = strip_tags($content, '<a><span><div><i><b><small><pre><table><p><h1><h2><h3><h4><h5>');
        }

        if ($params['strip-links'] && $params['strip-links'] != "false") {
            $content = strip_tags($content, '<span><div><i><b><small><pre><table><p><h1><h2><h3><h4><h5><img>');
        }

        return trim($content);
    }

    /**
     * Shortcode to generate post content
     */
    public static function process_post_featured_image_shortcode($params) {
        global $post;

        $params = shortcode_atts(array('id' => null), $params);

        $post = get_post($params['id']);

        return wp_get_attachment_url(get_post_thumbnail_id($post->ID));
    }

    /**
     * Shortcode to generate post content
     */
    public static function process_post_title_shortcode($params) {
        $params = shortcode_atts(array('id' => null), $params);

        $post = get_post($params['id']);

        return $post->post_title;
    }


    /**
     * Shortcode to generate post content
     */
    public static function process_post_link_shortcode($params) {
        $params = shortcode_atts(array('id' => null), $params);

        return get_permalink($params['id']);
    }

    /**
     * Hooks into acf/format_value and decodes string is tokens are available via URL
     * Also is callable directly
     * @param $field_value
     * @param $tokens
     * @return mixed|void
     */
    public static function process_tokens($field_value , $tokens = array() ) {
        global $post, $email_tokens;

        if ( !$tokens && ( !isset($post->post_type) || $post->post_type != 'inbound-email' )) {
            return $field_value;
        }
        if (!is_string($field_value) || !strstr($field_value,'{{')) {
            return $field_value;
        }

        if ( !$email_tokens ) {
            $email_tokens = array();

            if ($tokens) {
                $email_tokens = $tokens;
                $email_tokens = self::flatten_array($email_tokens);
                $email_tokens = (is_array($email_tokens)) ? $email_tokens : array();
            }

            /* if post id is present lets set the global email_tokens variable */
            if (isset($_GET['post_id']) && $_GET['post_id']) {
                $post = get_post((int) $_GET['post_id']);
                $email_tokens = (array) $post;
                $email_tokens['permalink'] = get_the_permalink((int) $_GET['post_id']);
                $email_tokens['featured_image'] = wp_get_attachment_url(get_post_thumbnail_id((int)$post->ID));

                wp_reset_query();
            }

            /* if tokens are set let's see if we can make use of them. Token strings will fail if they exceed 2000 characters */
            if (isset($_GET['tokens']) && !isset($_GET['tokens'])) {
                $tokens = Inbound_API::get_args_from_token($_GET['tokens']);
                $tokens = self::flatten_array($tokens);
                if (is_array($tokens)) {
                    $email_tokens = $email_tokens + $tokens;
                }
            }

        }

        preg_match_all("/{{(.*?)}}/", $field_value, $matches );

        if (!isset($matches[1])) {
            return $field_value;
        }


        foreach ($matches[1] as $token_key) {
            if (isset($email_tokens[$token_key])) {
                $field_value = str_replace('{{' . $token_key . '}}', $email_tokens[$token_key], $field_value);
            } else {
                $field_value = str_replace('{{' . $token_key . '}}', '', $field_value);
            }
        }

        /* remove cta shortcodes */
        $pattern = '/\[(cta).*?\]/';
        $field_value = preg_replace($pattern, '', $field_value);

        /* remove javascript */
        $pattern = '/<script\b[^>]*>(.*?)<\/script>/is';
        $field_value = preg_replace($pattern, '', $field_value);

        return $field_value;
    }


    public static function flatten_array($array) {
        $flatten = array();

        foreach ($array as $k => $value) {

            if (is_array($value)) {
                foreach ($value as $k1 => $v1) {

                    $flatten[$k1] = $v1;
                }
            } else {
                $flatten[$k] = $value;
            }
        }

        return $flatten;
    }


    /**
     * Loads Metaboxes
     */
    public static function load_metaboxes() {
        global $post, $Inbound_Mailer_Variations;

        if ($post->post_type != 'post' && $post->post_type != 'page') {
            return;
        }

        /* Show Selected Template */
        add_meta_box('inbound-email-content-shortcodes', __('Email Setup', 'inbound-pro'), array(__CLASS__, 'display_email_shortcodes'), $post->post_type, 'side', 'low');
    }

    /**
     * Updates email variation data on post save
     *
     * @param INT $inbound_email_id of email id
     *
     */
    public static function action_save_data($post_id) {

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['inbound_automation_email_sent']) && $_POST['inbound_automation_email_sent'] ) {
            update_post_meta( $post_id, 'inbound_automation_email_sent' , (bool) $_POST['inbound_automation_email_sent'] , false );
        } else  {
            delete_post_meta( $post_id , 'inbound_automation_email_sent');
        }

    }

    public static function display_email_shortcodes() {
        global $post;

        $has_been_sent = get_post_meta($post->ID , 'inbound_automation_email_sent' , true);

        $automated_emails = Inbound_Mailer_Post_Type::get_automation_emails_as('ARRAY');
        if (!$automated_emails) {
            $automated_emails['#no-email-selected'] = __('No Automation emails detected. Please create an automated email first.', 'inbound-pro');
        }

        $permalink_array = array();
        foreach ($automated_emails as $email_id => $email_name) {
            if (!is_numeric($email_id)) {
                continue;
            }
            $email_permalink = get_the_permalink($email_id);
            $permalink_array[urlencode($email_permalink)] = $email_name;
        }

        $tokens = array('{{post_title}}', '{{post_content}}', '{{post_excerpt}}', '{{permalink}}', '{{featured_image}}', '{{author_name}}');
        $tokens_string = implode('' , $tokens );
        ?>
        <div>
            <table style='width:100%'>

                <tr>
                    <td style='width:22%'>
                        <div class="lp_tooltip" style="display:inline" title="<?php echo sprintf(__('Use these tokens inside email an automated email: %s', 'inbound-pro') , $tokens_string ); ?>">
                            <?php _e('Post Tokens', 'inbound-pro'); ?>
                            <i class="fa fa-question-circle"></i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" name="inbound_automation_email_sent" value="true" <?php checked($has_been_sent, true); ?>> <?php _e('Locked' , 'inbound-pro'); ?>
                        <div class="lp_tooltip" style="display:inline" title="<?php _e('If our Marketing Automation component sends this post as an email to a lead list then this checkbox will enable automatically. Enabling this checkbox will prevent Marketing Automation component from sending it again. This prevents accidental double sending', 'inbound-pro'); ?>">
                            <i class="fa fa-question-circle"></i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <center>
                            <span style="width:100%;margin-top:5px;" class="button button-default" id="generate-batch-email"><?php _e('Preview as Email', 'inbound-pro'); ?></span>
                        </center>
                    </td>
                </tr>
            </table>
        </div>
        <script>
            jQuery(document).ready(function () {
                /* Add listener to prompt sweet alert on unschedule */
                jQuery('body').on('click', '#generate-batch-email', function (e) {
                    MailerListener.generate_email();
                });

            });
            var MailerListener = (function () {

                var Init = {
                    /**
                     *    Initialize immediate UI modifications
                     */
                    init: function () {

                    },
                    /**
                     *    Prompts send test email dialog
                     */
                    generate_email: function () {

                        /* Throw confirmation for scheduling */
                        swal({
                            title: "<?php _e('Preview Email Version', 'inbound-pro'); ?>",
                            text: "",
                            type: "info",
                            showCancelButton: true,
                            confirmButtonColor: "#2ea2cc",
                            confirmButtonText: "<?php _e('Get Preview Link', 'inbound-pro'); ?>",
                            closeOnConfirm: true,
                            selectField: {
                                placeholder: '<?php _e('Select Automated Email Template.', 'inbound-pro'); ?>',
                                padding: '20px',
                                width: '400px',
                                options: <?php echo json_encode($permalink_array); ?>
                            }
                        }, function (email_permalink) {

                            if (!email_permalink) {
                                return;
                            }

                            window.prompt("Copy to clipboard: Ctrl+C, Enter", decodeURIComponent(email_permalink) + '?post_id=<?php echo $post->ID; ?>');


                        });

                    }


                }

                return Init;

            })();
        </script>
        <?php
    }

}

/**
 *  Loads token engine on the administrator side
 */
function inbound_load_token_engine() {
    new Inbound_Mailer_Tokens();
}

add_action('init', 'inbound_load_token_engine', 1);