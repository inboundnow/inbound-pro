<?php

/**
 * Class Inbound_Mailer_Direct_Email_Leads extends lead profile with statistic reports and direct email capabilities
 * @package Mailer
 * @subpackage Leads
 */
class Inbound_Mailer_Direct_Email_Leads {
    static $range;

    /**
     * Inbound_Mailer_Direct_Email_Leads constructor.
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     *
     */
    public static function add_hooks() {
        /*get the mail service settings*/
        $settings = Inbound_Mailer_Settings::get_settings();


        /*Add the "Email Lead" tab to the edit lead list of tabs*/
        add_filter('wpl_lead_tabs', array(__CLASS__, 'add_direct_email_tab'), 10, 1);

        /*Add the contents to the "Email Lead" tab*/
        add_action('wpl_print_lead_tab_sections', array(__CLASS__, 'add_direct_email_tab_contents'));

        /*Add ajax listener for populating the address headers when a premade template is selected*/
        add_action('wp_ajax_get_addressing_settings', array(__CLASS__, 'ajax_get_addressing_settings'));

        /*Add ajax listener for sending the email*/
        add_action('wp_ajax_send_email_to_lead', array(__CLASS__, 'ajax_send_email_to_lead'));

        /*Adds direct messages to quick stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_direct_messages') , 20 , 1);

        /*Adds direct messages to quick stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_unsubscribes') , 20 , 1);

        /*Adds email clicks to quick stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_email_clicks') , 20 , 1 );

        /*Adds email opens to quick stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_email_opens') , 20 , 1 );

        /*Adds email opens to quick stats */
        add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_email_bounces') , 20 , 1 );

    }

    /**
     * @param $tabs
     * @return mixed
     */
    public static function add_direct_email_tab($tabs) {
        $args = array(
            'id' => 'wpleads_lead_tab_direct_email',
            'label' => __('Direct Email', 'inbound-pro')
        );

        array_push($tabs, $args);

        return $tabs;
    }

    /**
     *
     */
    public static function add_direct_email_tab_contents() {
        global $post;


        /* Enqueue Sweet Alert support  */
        wp_enqueue_script('sweet-alert-js', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.js');
        wp_enqueue_style('sweet-alert-css', INBOUND_EMAIL_URLPATH . 'assets/libraries/SweetAlert/sweet-alert.css');


        /*get the email address we're sending to*/
        $recipient_email_addr = Leads_Field_Map::get_field($post->ID, 'wpleads_email_address');

        /*get all the "automated" emails*/
        $email_templates = get_posts(array(
            'numberposts' => -1,
            'post_status' => 'automated',
            'post_type' => 'inbound-email',
        ));

        /*get the current user*/
        $user = wp_get_current_user();

        /*get the current user's email*/
        $parts = explode('@', $user->data->user_email );
        $user_email = $parts[0];

        /*get the mail service settings*/
        $inbound_settings = Inbound_Mailer_Settings::get_settings();

        /***setup the sending domains dropdown***/
        /*get the available Sparkpost sending domains*/
        if($inbound_settings['mail-service'] == 'sparkpost' ){
            $sparkpost = new Inbound_SparkPost(  $inbound_settings['sparkpost-key'] );
            $domain_query = $sparkpost->get_domains();
            /*if there are no errors*/
            if(!isset($domain_query['errors']) && empty($domain_query['errors'])){
                if (count($domain_query['results']) <1 ) {
                    $sending_dropdown = '<option value="null">' . __('No Domains Detected', 'inbound-pro') . '</option>';
                } else {
                    $sending_dropdown = '';
                }

                foreach($domain_query as $domains){
                    foreach($domains as $domain){
                        /*if the sending domain is owned, or has DKIM or SPF setup*/
                        if($domain['status']['ownership_verified'] == 1 || $domain['status']['spf_status'] == 'valid' || $domain['status']['dkim_status'] == 'valid'){

                            /*if the user's email is hosted on a verified sending domain*/
                            if(substr($user->email, strpos($user->email, '@') +1) == $domain['domain']){

                                /*set that domain as the selected one for the domain selector dropdown*/
                                $sending_dropdown .= '<option value="' . '@' . $domain['domain'] . '" selected="selected">' . '@' . $domain['domain'] . '</option>';

                            }else{

                                $sending_dropdown .= '<option value="' . '@' . $domain['domain'] . '">' . '@' . $domain['domain'] . '</option>';

                            }
                        }
                    }
                }
                $sending_dropdown .= '<option value="">' . __('Type out full address', 'inbound-pro') . '</option>';
                echo '<select id="sending-domain-selector" class="form-control">'.$sending_dropdown.'</select>';
            }else{

            }
        }

        /*put the email ids and names in an array for use in the email dropdown selector*/
        $template_id_and_name = array();
        foreach ($email_templates as $email_template) {
            $template_id_and_name[$email_template->ID] = $email_template->post_title;
        }


        /*these are the fields in the "Email Lead" tab*/
        $custom_fields = array(
            'use_premade_template' => array(
                'label' => __('Use template?', 'inbound-pro'),
                'description' => __('Use this to choose whether to send a custom or a premade email', 'inbound-pro'),
                'id' => 'premade_template_chooser',
                'type' => 'dropdown',
                'default' => '0',
                'class' => 'premade_template_chooser',
                'options' => array('0' => 'No', '1' => 'Yes'),
            ),
            'subject' => array(
                'description' => __('Subject line of the email. This field is variation dependant!', 'inbound-pro'),
                'label' => __('Subject', 'inbound-pro'),
                'id' => 'subject',
                'type' => 'text',
                'default' => '',
                'class' => 'direct_email_lead_field',
            ),
            'from_name' => array(
                'label' => __('From Name', 'inbound-pro'),
                'description' => __('The name of the sender. This field is variation dependant!', 'inbound-pro'),
                'id' => 'from_name',
                'type' => 'text',
                'default' => $user->display_name,
                'class' => 'direct_email_lead_field',
            ),
            'from_email' => array(
                'label' => __('From Email', 'inbound-pro'),
                'description' => __('The email address of the sender. This field is variation dependant!', 'inbound-pro'),
                'id' => 'from_email',
                'type' => 'text',
                'default' => $user_email,
                'class' => 'direct_email_lead_field',
            ),
            'reply_email' => array(
                'label' => __('Reply Email', 'inbound-pro'),
                'description' => __('The email address recipients can reply to. This field is variation dependant!', 'inbound-pro'),
                'id' => 'reply_email',
                'type' => 'text',
                'default' => $user->user_email,
                'class' => 'direct_email_lead_field',
            ),
            'recipient_email_address' => array(
                'label' => __('Recipient', 'inbound-pro'),
                'description' => __('The email address of the recipient.', 'inbound-pro'),
                'id' => 'recipient_email_address',
                'type' => 'text',
                'default' => $recipient_email_addr,
                'class' => '',
            ),
            'email_message_box' => array(
                'label' => __('Message', 'inbound-pro'),
                'description' => __('Use this editor to create a short custom email messages', 'inbound-pro'),
                'id' => 'email_message_box',
                'type' => 'wysiwyg',
                'default' => __('Email content goes in here. You may want to send yourself one to see how it looks.', 'inbound-pro'),
                'class' => 'email_message_box',
                'disable_variants' => '1',
            ),
            'premade_email_templates' => array(
                'label' => __('Select Template', 'inbound-pro'),
                'description' => __('Use this to select which premade email to use.', 'inbound-pro'),
                'id' => 'premade_template_selector',
                'type' => 'dropdown',
                'default' => '0',
                'class' => 'premade_template_selector',
                'options' => $template_id_and_name,
            ),
            'email_variation' => array(
                'label' => __('Choose varation', 'inbound-pro'),
                'description' => __('Use this to select which variation of the premade email to use.', 'inbound-pro'),
                'id' => 'email_variation_selector',
                'type' => 'dropdown',
                'default' => '0',
                'class' => 'email_variation_selector',
                'options' => array('0' => 'A'),
            )
        );

        ?>


        <div class="lead-profile-section" id="wpleads_lead_tab_direct_email">
            <?php

            Inbound_Mailer_Metaboxes::render_settings('inbound-email', $custom_fields, $post);
            Inbound_Metaboxes_Leads::display_profile_image();
            ?>


            <button id="send-email-button" type="button" style="">
                <?php _e('Send Email', 'inbound-pro'); ?>
                <i class="fa fa-envelope" aria-hidden="true"></i>
            </button>
        </div>
        </div>


        <script>
            jQuery(document).ready(function () {
                var variationSettings;
                var sendingDomainSelector = jQuery('#sending-domain-selector').remove();

                /*page load actions*/
                jQuery('.premade_template_selector').css('display', 'none');
                jQuery('.email_variation_selector').css('visibility', 'hidden');
                jQuery('.inbound-tooltip').css('display', 'none');
                jQuery('.open-marketing-button-popup.inbound-marketing-button.button, a.button.lead-fields-button').css('display', 'none');

                if(jQuery(sendingDomainSelector).val() != undefined){
                    jQuery('.from_email > .inbound-email-option-td.inbound-meta-box-option.inbound-text-option').append(sendingDomainSelector);
                    jQuery('input#from_email').css({'width' : '34%'});
                }

                jQuery('#premade_template_chooser').on('change', function () {
                    if (jQuery('#premade_template_chooser').val() == 1) {
                        jQuery('div.email_message_box.inbound-wysiwyg-row.div-email_message_box.inbound-email-option-row.inbound-meta-box-row').css('display', 'none');
                        jQuery('.premade_template_selector, .email_variation_selector').css('display', 'block');
                        jQuery('.direct_email_lead_field, .div-direct_email_lead_field').css('display', 'none');
                    } else {
                        jQuery('div.email_message_box.inbound-wysiwyg-row.div-email_message_box.inbound-email-option-row.inbound-meta-box-row').css('display', 'block');
                        jQuery('.premade_template_selector, .email_variation_selector').css('display', 'none');
                        jQuery('.direct_email_lead_field, .div-direct_email_lead_field').css('display', '');
                    }

                });

                jQuery('#premade_template_selector').on('change', function () {
                    var id = jQuery('#premade_template_selector').val();
                    console.log(id);
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'get_addressing_settings',
                            email_id: id,

                        },
                        success: function (response) {
                            response = JSON.parse(response);
                            //					console.log(response);
                            variationSettings = response.variations;
                            //					console.log(variationSettings);

                            jQuery('#email_variation_selector').find('option').remove();
                            if (variationSettings.length > 1) {
                                var alphabetObject = {
                                    0: 'A', 1: 'B', 2: 'C', 3: 'D', 4: 'E',
                                    5: 'F', 6: 'G', 7: 'H', 8: 'I', 9: 'J',
                                    10: 'K', 11: 'L', 12: 'M', 13: 'N', 14: 'O',
                                    15: 'P', 16: 'Q', 17: 'R', 18: 'S', 19: 'T',
                                    20: 'U', 21: 'V', 22: 'W', 23: 'X', 24: 'Y',
                                    25: 'Z',
                                }
                                console.log(variationSettings.length);

                                for (var index = 0; index < variationSettings.length; index++) {
                                    jQuery('#email_variation_selector').append('<option value="' + index + '">' + alphabetObject[index] + '</option>');
                                }

                                jQuery('.email_variation_selector').css('visibility', 'visible');

                            } else {
                                jQuery('#email_variation_selector').append('<option value="0">A</option>');
                                jQuery('.email_variation_selector').css('visibility', 'hidden');
                            }

                        },
                        error: function (MLHttpRequest, textStatus, errorThrown) {
                            alert("<?php _e('Ajax not enabled', 'inbound-pro'); ?>");
                        },
                    });

                });

                /*Send the email*/
                jQuery('#send-email-button').on('click', function () {
                    var postId = <?php echo $post->ID; ?>;
                    var userId = <?php echo $user->ID; ?>;
                    var subject = jQuery('#subject').val();
                    var fromName = jQuery('#from_name').val();
                    var sendingDomain = (jQuery('#sending-domain-selector').val() != undefined) ? jQuery('#sending-domain-selector').val() : '';
                    var fromEmail = jQuery('#from_email').val() + sendingDomain;
                    var replyEmail = jQuery('#reply_email').val();
                    var emailContent = get_tinymce_content();
                    var recipientEmail = jQuery('#recipient_email_address').val();
                    var usePremadeTemplate = jQuery('#premade_template_chooser').val();
                    var isPremadeTemplate = jQuery('#premade_template_chooser').val();
                    var premadeEmailId = jQuery('#premade_template_selector').val();
                    var variationSelected = jQuery('#email_variation_selector').val();

                    swal({
                        title: "<?php _e('Please wait', 'inbound-pro'); ?>",
                        text: "<?php _e('We are sending a your email now.', 'inbound-pro'); ?>",
                        imageUrl: '<?php echo INBOUND_EMAIL_URLPATH; ?>/assets/images/loading_colorful.gif'
                    });


                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'send_email_to_lead',
                            post_id: postId,
                            user_id: userId,
                            subject: subject,
                            from_name: fromName,
                            from_email: fromEmail,
                            reply_email: replyEmail,
                            email_content: emailContent,
                            recipient_email: recipientEmail,
                            use_premade_template: usePremadeTemplate,
                            is_premade_template: isPremadeTemplate,
                            premade_email_id: premadeEmailId,
                            variation_selected: variationSelected
                        },

                        success: function (response) {
                            response = JSON.parse(response);
                            console.log(response);

                            /**error check**/
                            /*if it's a basic error, like a field isn't filled in*/
                            if (response.basic_error) {
                                swal({
                                    title: response.title,
                                    text: response.basic_error,
                                    type: 'error',
                                });
                                return false;
                            }

                            /*if it's a system error, like some data wasn't supplied*/
                            if (response.system_error) {
                                swal({
                                    title: response.title,
                                    text: response.system_error,
                                    type: 'error',
                                });
                                return false;
                            }

                            /*if it's a Sparkpost error, like some data wasn't supplied*/
                            console.log(response);
                            if (response.errors) {
                                swal({
                                    title: response.errors[0].message,
                                    text: response.errors[0].description,
                                    type: 'error',
                                });
                                return false;
                            }


                            /*...no errors? THEN SUCCESS!*/
                            if (response.success) {
                                swal({
                                    title: response.title,
                                    text: response.success,
                                    type: 'success',
                                });
                                return false;
                            }

                            alert(response);
                            jQuery('.confirm').click();


                        },
                        error: function (MLHttpRequest, textStatus, errorThrown) {
                            alert("<?php _e('Ajax not enabled', 'inbound-pro'); ?>");
                        },


                    });
                });


                function get_tinymce_content() {
                    if (jQuery('#wp-inbound_email_message_box-wrap').hasClass('tmce-active')) {
                        return tinyMCE.activeEditor.getContent();

                    } else {
                        return jQuery('textarea.email_message_box').val();
                    }
                }

            });
        </script>
        <style type="text/css">
           #wpleads_lead_tab_direct_email #show-hidden-fields, #wpleads_lead_tab_direct_email #show-edit-user {
               display:none;
           }
           #wpleads_lead_tab_direct_email .form-table {
               float:right;
               width:70%;
           }
           #wpleads_lead_tab_direct_email .inbound-meta-box-label {
               width:98px;
           }

           #wpleads_lead_tab_direct_email #send-email-button {
                text-align:center;
                margin-left:auto;
                margin-right:auto;
                width:100%;
                padding:10px;
                cursor:pointer
           }

           #wpleads_lead_tab_direct_email #sending-domain-selector{
               width: 45%;
               display: inherit;
               padding: 2px;
               height: 33px;
               vertical-align: top;
            }

           #wpleads_lead_tab_direct_email .direct_email_lead_field {
               display:inherit;
           }

           #wpleads_lead_tab_direct_email #left-sidebar {
               width:20%;
           }

           .email_message_box .inbound-wysiwyg-option {
               display:table-row-group;
           }
        </style>

        <?php
    }


    /**
     * Determines if automation email has variations.
     * echos
     */
    public static function ajax_get_addressing_settings() {
        if (isset($_POST['email_id']) && !empty($_POST['email_id'])) {

            $id = intval($_POST['email_id']);
            $email_settings = Inbound_Email_Meta::get_settings($id);
            echo json_encode($email_settings);
        }
        die();

    }


    /**
     *
     */
    public static function ajax_send_email_to_lead() {

        /*if the email is a premade auto one, make sure the info is provided to send it and send it*/
        if ($_POST['use_premade_template'] == '1') {
            self::send_automated_email_to_lead();
        } else {
            self::send_direct_message_to_lead();
        }

    }

    /**
     * Sends an pre-fabricated automated email to the lead
     */
    public static function send_automated_email_to_lead() {
        /*make sure the settings have been supplied*/
        if (empty($_POST['post_id']) && $_POST['post_id'] != '0') {
            echo json_encode(array('system_error' => __('The lead id was not supplied', 'inbound-pro'), 'title' => __('System Error:', 'inbound-pro')));
            die();
        }
        if (empty($_POST['recipient_email']) || !is_email($_POST['recipient_email'])) {
            echo json_encode(array('basic_error' => __('There\'s an error with the recipient email', 'inbound-pro'), 'title' => __('Field Error:', 'inbound-pro')));
            die();
        }
        if (empty($_POST['premade_email_id']) && $_POST['premade_email_id'] != '0') {
            echo json_encode(array('system_error' => __('The email template id was not supplied', 'inbound-pro'), 'title' => __('System Error:', 'inbound-pro')));
            die();
        }
        if (empty($_POST['variation_selected']) && $_POST['variation_selected'] != '0') {
            echo json_encode(array('system_error' => __('The variation id was not supplied', 'inbound-pro'), 'title' => __('System Error:', 'inbound-pro')));
            die();
        }

        $post_id = intval($_POST['post_id']);
        $recipient_email = sanitize_text_field($_POST['recipient_email']);
        $premade_email_id = intval($_POST['premade_email_id']);
        $variation_selected = intval($_POST['variation_selected']);

        /*sending args*/
        $args = array(
            'email_address' => $recipient_email,
            'email_id' => $premade_email_id,
            'vid' => $variation_selected,
            'lead_id' => $post_id,
            'is_test' => 0
        );

        /*send the email!*/
        Inbound_Mail_Daemon::send_solo_email($args);
        echo json_encode(array('success' => __('Your email has been sent!', 'inbound-pro'), 'title' => __('SUCCESS!', 'inbound-pro')));
        die();
    }

    /**
     *
     */
    public static function send_direct_message_to_lead() {

        /* these are the variables set by the user */
        $data = array(
            'subject' => array(
                'label' => __('Subject', 'inbound-pro'),
                'value' => sanitize_text_field($_POST['subject'])
            ),
            'from_name' => array(
                'label' => __('From Name', 'inbound-pro'),
                'value' => sanitize_text_field($_POST['from_name'])
            ),
            'from_email' => array(
                'label' => __('From Email', 'inbound-pro'),
                'value' => sanitize_text_field($_POST['from_email'])
            ),
            'reply_email' => array(
                'label' => __('Reply Email', 'inbound-pro'),
                'value' => sanitize_text_field($_POST['reply_email'])
            ),
            'recipient_email' => array(
                'label' => __('Recipeient Email', 'inbound-pro'),
                'value' => sanitize_text_field($_POST['recipient_email'])
            ),
            'email_content' => array(
                'label' => __('Email Content', 'inbound-pro'),
                'value' => strip_tags($_POST['email_content'],'<br><i><b><strong><div><span><h1><h2><h3><h4><table><tr><td><tbody>')
            )
        );

        /*check to make sure the variables are set*/
        foreach ($data as $key => $value) {
            if (empty($value['value'])) {
                echo json_encode(array('basic_error' => __('Please fill in the ' . $value['label'], 'inbound-pro'), 'title' => __('Empty field', 'inbound-pro')));
                die();
            }

        }

        /* sanitise hidden values */
        $data['post_id'] = (int) $_POST['post_id'];
        $data['user_id'] = (int) $_POST['user_id'];

        /*check to make sure the email addresses are setup correctly*/
        if (!is_email($data['recipient_email']['value'])) {
            echo json_encode(array('basic_error' => __('There\'s an error with the Recipient Email Address', 'inbound-pro'), 'title' => __('Field Error:', 'inbound-pro')));
            die();
        }

        if (!is_email($data['from_email']['value'])) {
            echo json_encode(array('basic_error' => __('There\'s an error with the From Email', 'inbound-pro'), 'title' => __('Field Error:', 'inbound-pro')));
            die();
        }

        if (!is_email($data['reply_email']['value'])) {
            echo json_encode(array('basic_error' => __('There\'s an error with the Reply Email', 'inbound-pro'), 'title' => __('Field Error:', 'inbound-pro')));
            die();
        }

        /*check to make sure the post and user ids have been supplied*/
        if (empty($data['post_id'])) {
            echo json_encode(array('system_error' => __('The post id was not supplied', 'inbound-pro'), 'title' => __('System Error:', 'inbound-pro')));
            die();
        }
        if (empty($data['user_id'])) {
            echo json_encode(array('system_error' => __('The user id was not supplied', 'inbound-pro'), 'title' => __('System Error:', 'inbound-pro')));
            die();
        }


        /*get the current time according to the wp format*/
        $timezone = get_option('timezone_string' , 'EST');
        $timezone = ($timezone) ? $timezone : 'EST';
        $time = new DateTime('', new DateTimeZone($timezone));
        $format = get_option('date_format') . ' \a\t ' . get_option('time_format');


        /*assemble the post data*/
        $direct_email = array(
            'post_title' => __('Direct email to ', 'inbound-pro') . $data['recipient_email']['value'] . __(' on ', 'inbound-pro') . $time->format($format),
            'post_content' => wpautop($data['email_content']['value']),
            'post_status' => 'direct_email',
            'post_author' => $data['user_id'],
            'post_type' => 'inbound-email',
        );

        /*create the email*/
        $data['direct_email_id'] = wp_insert_post($direct_email);

        /* assemble the settings mailer uses for sending the email */
        $mailer_settings = array(
            'variations' => array(
                0 => array(
                    'selected_template' => '',
                    'user_ID' => $data['user_id'],
                    'subject' => $data['subject']['value'],
                    'from_name' => $data['from_name']['value'],
                    'from_email' => $data['from_email']['value'],
                    'reply_email' => $data['reply_email']['value'],
                    'variation_status' => 'active',
                ),
            ),
            'email_type' => 'direct',
        );

        /*add the settings to the email*/
        Inbound_Email_Meta::update_settings($data['direct_email_id'], $mailer_settings);

        /*sending args*/
        $args = array(
            'email_address' => $data['recipient_email']['value'],
            'email_id' => $data['direct_email_id'],
            'vid' => 0,
            'lead_id' => $data['post_id'],
            'is_test' => 0,
            'is_direct' => true
        );

        /*and send*/
        $response = Inbound_Mail_Daemon::send_solo_email($args);
        $repsonse = wp_remote_retrieve_body( $response );


        if (isset($response['errors'])) {
            wp_delete_post($data['direct_email_id'], true);
            echo json_encode($response);
        } else {
            echo json_encode(array('success' => __('Your custom email has been sent!', 'inbound-pro'), 'title' => __('SUCCESS!', 'inbound-pro')));
            $data['response'] = $response;
            self::store_direct_mail_event( $data );
        }

        //		echo json_encode(error_get_last()); //debug
        die();
    }

    /**
     * @param $data
     */
    public static function store_direct_mail_event( $data ) {
        /* recipients */
        $args = array(
            'event_name' => 'inbound_direct_message',
            'email_id' => $data['direct_email_id'],
            'variation_id' =>  0,
            'form_id' => 0,
            'lead_id' => $data['post_id'],
            'event_details' => json_encode($data)
        );

        Inbound_Events::store_event($args);
    }

    /**
     * Adds Inbound Form Submissions to Quick Stat Box
     */
    public static function display_quick_stat_direct_messages($post) {
        global $post;
        if (!isset($_REQUEST['range'])) {
            self::$range = 90;
        } else {
            self::$range = intval($_REQUEST['range']);
        }
        ?>
        <div class="quick-stat-label">
            <div class="label_1"><?php _e('Direct E-Mails', 'inbound-pro'); ?>:</div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_direct_message&range=10000&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo self::get_direct_mail_count($post->ID); ?>
                    </a>
                    <?php
                } else {
                    echo self::get_direct_mail_count($post->ID);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }

    /**
     * Adds Inbound Form Submissions to Quick Stat Box
     */
    public static function display_quick_stat_unsubscribes($post) {
        global $post;
        ?>
        <div class="quick-stat-label">
            <div class="label_1"><?php _e('Unsubscribes', 'inbound-pro'); ?>:</div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_unsubscribe&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo self::get_unsubscribes_count($post->ID); ?>
                    </a>
                    <?php
                } else {
                    echo self::get_unsubscribes_count($post->ID);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }


    /**
     * Adds a quick stat form email clicks to the Quick Stats box
     */
    public static function display_quick_stat_email_clicks() {
        global $post;

        /* get daily action counts for chart 1 */
        $params = array(
            'lead_id' => $post->ID,
            'event_name' => 'sparkpost_click'
        );
        $click_events = Inbound_Events::get_events($params);


        ?>
        <div  class="quick-stat-label">
            <div class="label_1">
                <?php _e('Email Clicks', 'inbound-pro'); ?>
            </div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=sparkpost_click&range='.self::$range.'&title='.__('Email Clicks' , 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo count($click_events); ?>
                    </a>
                    <?php
                } else {
                    echo count($click_events);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php

    }

    /**
     * Adds a quick stat form email clicks to the Quick Stats box
     */
    public static function display_quick_stat_email_opens() {
        global $post;

        /* get daily action counts for chart 1 */
        $params = array(
            'lead_id' => $post->ID,
            'event_name' => 'sparkpost_open'
        );
        $opens = Inbound_Events::get_events($params);


        ?>
        <div  class="quick-stat-label">
            <div class="label_1">
                <?php _e('Email Opens', 'inbound-pro'); ?>
            </div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=sparkpost_open&range='.self::$range.'&title='.__('Email Opens' , 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo count($opens); ?>
                    </a>
                    <?php
                } else {
                    echo count($opens);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php

    }

    /**
     * Adds a quick stat form email clicks to the Quick Stats box
     */
    public static function display_quick_stat_email_bounces() {
        global $post;

        /* get daily action counts for chart 1 */
        $params = array(
            'lead_id' => $post->ID,
            'event_name' => 'sparkpost_bounce'
        );

        $bounces = Inbound_Events::get_events($params);


        ?>
        <div  class="quick-stat-label">
            <div class="label_1">
                <?php _e('Email Bounces', 'inbound-pro'); ?>
            </div>
            <div class="label_2">
                <?php
                if (class_exists('Inbound_Analytics')) {
                    ?>
                    <a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=sparkpost_bounce&title='.__('SparkPost Bounces' , 'inbound-pro') .'&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
                        <?php echo count($bounces); ?>
                    </a>
                    <?php
                } else {
                    echo count($opens);
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php

    }

    /**
     * Gets number of direct mail messages sent to lead
     */
    public static function get_direct_mail_count( $lead_id  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'"';

        $query .= 'AND `event_name` = "inbound_direct_message"';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;
    }

    /**
     * Gets number of direct mail messages sent to lead
     */
    public static function get_unsubscribes_count( $lead_id  ){
        global $wpdb;

        $table_name = $wpdb->prefix . "inbound_events";

        $query = 'SELECT count(*) FROM '.$table_name.' WHERE `lead_id` = "'.$lead_id.'"';

        $query .= 'AND `event_name` = "inbound_unsubscribe"';

        $count = $wpdb->get_var( $query , 0, 0 );

        /* return null if nothing there */
        return ($count) ? $count : 0;
    }
}


add_action('admin_init', 'inbound_confirm_email_service_provider');
/**
 *    Only load Inbound_Mailer_Direct_Email_Leads if an email service provider has been selected
 */
function inbound_confirm_email_service_provider() {
    $email_settings = Inbound_Mailer_Settings::get_settings();
    if (isset($email_settings['mail-service']) && $email_settings['mail-service'] != 'none' ) {
        new Inbound_Mailer_Direct_Email_Leads;
    }
}

