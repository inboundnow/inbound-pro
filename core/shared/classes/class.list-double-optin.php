<?php

if (!class_exists('Inbound_List_Double_Optin')) {

    class Inbound_List_Double_Optin {

        function __construct() {
            self::add_hooks();
        }

        public static function add_hooks() {

            add_action('admin_enqueue_scripts' , array(__CLASS__ , 'enqueue_scripts'));

            /* Modify the Edit lead list page */
            add_action('wplead_list_category_edit_form_fields', array(__CLASS__, 'add_list_settings'));

            /**/
            add_action('add_lead_to_lead_list', array(__CLASS__, 'remove_from_double_optin_list'), 10, 2);

            /* Add the setting saver and getter */
            add_action('wplead_list_category_edit_form', array(__CLASS__, 'lead_list_save_settings'));

            /* Save the settings to the term meta */
            add_action('wp_ajax_lead_list_save_settings', array(__CLASS__, 'ajax_lead_list_save_settings'));
        }


        /**
         *
         */
        public static function enqueue_scripts() {
            $screen = get_current_screen();

            if (!isset($screen) || $screen->id !='edit-wplead_list_category') {
                return;
            }

            wp_enqueue_script('jquery');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');

        }

        public static function get_default_email_content($settings) {

            if ($settings['logo']) {
                $logo_html = '<tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                  <td class="logo" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; vertical-align: top; text-align: center;">
                                    <img src="'.$settings['logo'].'" width="" style="max-width:500px;padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; max-width: 100%; margin: 30px 0;">
                                  </td>
                              </tr>';
            } else {
                $logo_html = "";
            }

            $html = '<!DOCTYPE html>
                    <html >
                      <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                        <title style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                            ' . $settings['email_subject'] . '
                         </title>
                      </head>

                        <body style="margin: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; padding: 0; border-top: 2px solid #26334D; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; background-color: white; height: 100%; line-height: 1.6; width: 100%;">
                          <table class="body-wrap" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; width: 100%; background-color: white;">
                            <tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                              <td style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; vertical-align: top;"></td>
                              <td width="400" class="container" style="font-size: 14px; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; padding: 0; line-height: 22px; vertical-align: top; margin: 0 auto; display: block; max-width: 400px; clear: both;">
                                <div class="content" style="padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; margin: 0 auto; max-width: 400px; display: block;">
                                  <table width="100%" cellpadding="0" cellspacing="0" class="main" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                    '. $logo_html .'
                                    <tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                      <td class="content-wrap" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; vertical-align: top;">
                                        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                          <tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                            <td class="content-block" style="font-size: 14px; margin: 0; box-sizing: border-box; line-height: 22px; vertical-align: top; color: #8F9BB3; padding: 20px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, \'Lucida Grande\', sans-serif; background: #ffffff; border-radius: 3px; box-shadow: 0 0 0 1px #D8DDE2;">
                                              <p style="margin: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; font-weight: normal; margin-bottom: 0; padding: 0 20px;">
                                              '.$settings['message'].'</p>
                                             </td>
                                          </tr>
                                          <tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                                            <td class="action" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; vertical-align: top; padding-top: 20px;">
                                              <a href="[inbound-list-double-optin-link]" style="line-height: 22px; margin: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #ffffff; font-size: 18px; padding: 20px; display: block; font-weight: bold; background: #2980b9; border-radius: 3px; text-decoration: none; text-align: center;">'.$settings['button_text'].'</a>
                                            </td>
                                          </tr>
                                        </table>
                                      </td>
                                    </tr>
                                  </table>
                                </div>
                              </td>
                            </tr>
                          </table>
                        <div class="footer" style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; width: 100%; clear: both;">
                          <table width="100%" class="footer-table" style="margin: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px; padding: 40px 20px; background: white;">
                            <tr style="margin: 0; padding: 0; box-sizing: border-box; font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; color: #8F9BB3; font-size: 14px; line-height: 22px;">
                              <td class="aligncenter content-block" style="margin: 0; box-sizing: border-box; line-height: 22px; vertical-align: top; padding: 20px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, \'Lucida Grande\', sans-serif; color: #8F9BB3; text-align: center; font-size: 12px;">
                                '.html_entity_decode($settings['footer_text']).'
                              </td>
                            </tr>
                          </table>
                        </div>
                      </body>
                    </html>
                    ';

            return $html;
        }

        /**
         * Gets the list id of the double optin waiting list
         * @return mixed
         */
        public static function get_double_optin_waiting_list() {
            /*get the double optin waiting list id*/
            if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
                $double_optin_list_id = get_option('list-double-optin-list-id', '');
            } else {
                $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                $double_optin_list_id = $settings['leads']['list-double-optin-list-id'];
            }

            return $double_optin_list_id;
        }

        /**
         * Saves the list id of the double optin waiting list
         * @return mixed
         */
        public static function save_double_optin_waiting_list() {
            /*get the double optin waiting list id*/
            if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
                update_option('list-double-optin-list-id', $term_id['term_id']);
            } else {
                $settings = Inbound_Options_API::get_option('inbound-pro', 'settings');
                $settings['leads']['list-double-optin-list-id'] = $term_id['term_id'];
                Inbound_Options_API::update_option('inbound-pro', 'settings', $settings);
            }
        }

        /**
         * Saves form options to the term meta
         */
        public static function ajax_lead_list_save_settings(){
            $data = stripslashes_deep($_POST['data']);
            $cleaned = array();
            foreach($data as $key => $value){
                $cleaned[sanitize_text_field($key)] = sanitize_text_field($value);
            }

            /*get the existing stored settings*/
            $meta = get_term_meta((int)$_POST['id'], 'wplead_lead_list_meta_settings', true);

            /**if the settings aren't empty, add each cleaned setting to the settings**/
            if(!empty($meta)){
                foreach($cleaned as $setting_name => $setting_value){
                    $meta[$setting_name] = $setting_value;
                }
            }else{
                /*if the settings are empty, just push the cleaned data*/
                $meta = $cleaned;
            }

            update_term_meta((int)$_POST['id'], 'wplead_lead_list_meta_settings', $meta);

            echo json_encode(__('Settings Updated!', 'inbound-pro'));

            die();
        }

        /**
         * Gets the values of all inboundnow-lead-list-option class inputs, and sends them to ajax_lead_list_save_settings for saving.
         * The element attribute "name" is the key for the settings.
         */
        public static function lead_list_save_settings($list){
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery('input#submit, .edit-tag-actions .button').on('click', function(){
                        var settingData = {};
                        var id = "<?php echo $list->term_id; ?>";
                        /*get the value of all inboundnow lead list options*/
                        jQuery('.double-optin-setting').each(function(){
                            settingData[jQuery(this).attr("name")] = jQuery(this).val();
                        });

                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: {
                                action: 'lead_list_save_settings',
                                id: id,
                                data: settingData,
                            },
                            success: function(response){
                                console.log(JSON.parse(response));
                            },
                        });

                    });
                });
            </script>
            <?php
        }


        /**
         * @param $list
         */
        public static function add_list_settings($list) {

            /* first let's make sure this is not our list for storing unconfirmed leads */
            $double_optin_list_id = self::get_double_optin_waiting_list();

            if ($list->term_id == $double_optin_list_id) {
                return;
            }

            /* get settings */
            $settings = get_term_meta($list->term_id, 'wplead_lead_list_meta_settings', true);
            $settings['double_optin'] = (isset($settings['double_optin'])) ? $settings['double_optin'] : 0;
            $settings['double_optin_email_template'] = (isset($settings['double_optin_email_template'])) ? $settings['double_optin_email_template'] : __('Please confirm your subscription' , 'inbound-pro' );
            $settings['double_optin_email_confirmation_logo'] = (isset($settings['double_optin_email_confirmation_logo'])) ? $settings['double_optin_email_confirmation_logo'] : '';
            $settings['double_optin_email_confirmation_subject'] = (isset($settings['double_optin_email_confirmation_subject'])) ? $settings['double_optin_email_confirmation_subject'] : __('Please confirm your subscription' , 'inbound-pro' );
            $settings['double_optin_email_confirmation_message'] = (isset($settings['double_optin_email_confirmation_message'])) ? $settings['double_optin_email_confirmation_message'] : __('To activate your subscription, please confirm your email address. If you received this by mistake, please disregard this email.' , 'inbound-pro' );
            $settings['double_optin_email_confirmation_button_text'] = (isset($settings['double_optin_email_confirmation_button_text'])) ? $settings['double_optin_email_confirmation_button_text'] : __('Confirm email address' , 'inbound-pro' );
            $settings['double_optin_email_confirmation_footer_text'] = (isset($settings['double_optin_email_confirmation_footer_text'])) ? $settings['double_optin_email_confirmation_footer_text'] : sprintf( __('Powered by %sInbound Now%s' , 'inbound-pro' ) , '<a href="http://www.inboundnow.com">' , '</a>' );

            /* get email templates */
            $emails = array();
            if (defined('INBOUND_PRO_CURRENT_VERSION')) {

                if(class_exists('Inbound_Mailer_Post_Type')){
                    $emails = Inbound_Mailer_Post_Type::get_automation_emails_as('ARRAY');
                }

                $emails = ($emails) ? $emails : array(__('No Automation emails detected. ', 'inbound-pro'));
            }
            ?>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Double Opt-in' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <select id="double_optin_toggle" class="double-optin-setting" name="double_optin" title="<?php _e('Enable list Double Opt In to send leads an email requesting consent to being added to a list.', 'inbound-pro'); ?>">
                        <option value="0" <?php selected($settings['double_optin'], 0); ?> ><?php _e('Off' , 'inbound-pro'); ?></option>
                        <option value="1" <?php selected($settings['double_optin'], 1); ?> ><?php _e('On' , 'inbound-pro'); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="form-field double-optin-enabled " id="double-optin-email-template">
                <th valign="top" scope="row">
                    <label><?php _e('Select Email Template', 'inbound-pro'); ?></label>
                </th>
                <td>
                    <select name="double_optin_email_template" id="double_optin_email_template" class="double-optin-setting" >
                        <option value='default-email-template' <?php selected($settings['double_optin_email_template'], 'default-email-template'); ?>>
                            <?php _e('Use the default email template', 'inbound-pro'); ?>
                        </option>
                        <?php
                        foreach ($emails as $id => $label) {
                            echo '<option value="' . $id . '" '.selected($settings['double_optin_email_template'], $id) .'>' . $label . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field double-optin-enabled default-email-setting" id="">
                <th valign="top" scope="row">
                    <label> <?php _e( 'Email Subject' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <input type="text" name="double_optin_email_confirmation_subject" size="30" id="double_optin_email_confirmation_subject" class="double-optin-setting" autocomplete="off"  value="<?php echo $settings['double_optin_email_confirmation_subject']; ?>">
                </td>
            </tr>
            <tr class="form-field double-optin-enabled default-email-setting" id="">
                <th valign="top" scope="row">
                    <label><?php _e( 'Email Logo' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <input type="text" id="double_optin_email_confirmation_logo" name="double_optin_email_confirmation_logo" value="<?php echo esc_url( $settings['double_optin_email_confirmation_logo'] ); ?>"  class="double-optin-setting" />
                    <input id="double_optin_email_confirmation_logo_button"   type="button" class="button" value="<?php _e( 'Upload Logo', 'inbound-pro' ); ?>" />
                </td>
            </tr>
            <tr class="form-field double-optin-enabled default-email-setting" id="">
                <th valign="top" scope="row">
                    <label><?php _e( 'Email Message' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <textarea  name="double_optin_email_confirmation_message" class="double-optin-setting"  style="width:100%"><?php echo $settings['double_optin_email_confirmation_message']; ?></textarea>
                </td>
            </tr>
            <tr class="form-field double-optin-enabled default-email-setting" id="">
                <th valign="top" scope="row">
                    <label><?php _e( 'Button Text' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <input type="text" name="double_optin_email_confirmation_button_text" size="30"  class="double-optin-setting"   value="<?php echo $settings['double_optin_email_confirmation_button_text']; ?>">
                </td>
            </tr>
            <tr class="form-field double-optin-enabled default-email-setting" id="">
                <th valign="top" scope="row">
                    <label><?php _e( 'Footer Text' , 'inbound-pro' ); ?></label>
                </th>
                <td>
                    <input type="text" name="double_optin_email_confirmation_footer_text" size="30"  class="double-optin-setting"   value="<?php echo htmlentities($settings['double_optin_email_confirmation_footer_text']); ?>">
                </td>
            </tr>
            <tr class="form-field double-optin-enabled confirmation-shortcode-notice" id="">
                <th valign="top" scope="row">
                    <label><?php _e('Note' , 'inbound-pro'); ?></label>
                </th>
                <td>
                    <p><?php _e('When creating your own confirmation template you should use the shortcode below to render your confirmation link. ' , 'inbound-pro' ); ?></p>
                    <pre>[inbound-list-double-optin-link]</pre>
                </td>
            </tr>
            <style type="text/css">
                #wpfooter {
                    position: initial;
                }
                .double-optin-enabled {
                    display:table-row;
                }
            </style>

            <script>
                jQuery(document).ready(function () {

                    /* listen for logo upload */
                    jQuery('#double_optin_email_confirmation_logo_button').click(function() {
                        tb_show( '' , 'media-upload.php?referer=wptuts-settings&type=image&TB_iframe=true&post_id=0', false);
                        return false;
                    });

                    window.send_to_editor = function(html) {
                        var image_url = jQuery('img',html).attr('src');
                        jQuery('#double_optin_email_confirmation_logo').val(image_url);
                        tb_remove();
                    }

                    /*if the double optin status has changed*/
                    jQuery('#double_optin_toggle').on('change', function () {
                        if (jQuery('#double_optin_toggle').val() != '1') {
                            jQuery('.double-optin-enabled').css({'display': 'none'});
                        } else {
                            jQuery('.double-optin-enabled').css({'display': 'table-row'});
                        }
                    });

                    /*if the double optin status has changed*/
                    jQuery('#double_optin_email_template').on('change', function () {
                        if (jQuery('#double_optin_email_template').val() == 'default-email-template') {
                            jQuery('.default-email-setting').css({'display': 'table-row'});
                            jQuery('.confirmation-shortcode-notice').css({'display': 'none'});
                        } else {
                            jQuery('.default-email-setting').css({'display': 'none'});
                            jQuery('.confirmation-shortcode-notice').css({'display': 'table-row'});
                        }
                    });

                    /*trigger a refresh of the email inputs just after the page is loaded*/
                    setTimeout(function () {
                        jQuery('#double_optin_toggle').trigger('change');
                        jQuery('#double_optin_email_template').trigger('change');
                    }, 240);

                });
            </script>

            <?php
        }

        /**
         *  Sends A Double Optin Confirmation to Lead After Conversion
         */
        public static function send_double_optin_confirmation($lead) {

            /*get the lists*/
            $lists = get_post_meta($lead['id'], 'double_optin_lists', true);

            /*exit if there aren't any lists*/
            if (!isset($lists) || empty($lists)) {
                return;
            }

            /**Loop through the double optin lists the lead has waiting for a response.
             *
             * If the response email is an automated one, shoot it off here.
             * If it's a custom template, add it to the email_contents array to be processed further down the page**/
            $email_contents = array();
            foreach ($lists as $list_id) {
                $list_settings = get_term_meta((int)$list_id, 'wplead_lead_list_meta_settings', true);


                /* if there is a double optin email template and its not a custom one */
                if (!empty($list_settings['double_optin_email_template']) && $list_settings['double_optin_email_template'] != 'default-email-template') {
                    $vid = Inbound_Mailer_Variations::get_next_variant_marker($list_settings['double_optin_email_template']);

                    $args = array(
                        'email_id' => $list_settings['double_optin_email_template'],
                        'vid' => $vid,
                        'email_address' => $lead['email'],
                        'lead_id' => $lead['id'],
                        'tags' => array('inbound-forms'),
                        'lead_lists' => $lists,
                    );

                    $response = Inbound_Mail_Daemon::send_solo_email($args);

                } else if ($list_settings['double_optin_email_template'] == 'default-email-template' && !empty($list_settings['double_optin_email_confirmation_subject'])) {
                    /* if there is an email template and it's a custom one*/

                    /*add the email to the queue of emails to send*/
                    $email_contents[$list_id]['logo'] = $list_settings['double_optin_email_confirmation_logo'];
                    $email_contents[$list_id]['email_subject'] = $list_settings['double_optin_email_confirmation_subject'];
                    $email_contents[$list_id]['message'] = $list_settings['double_optin_email_confirmation_message'];
                    $email_contents[$list_id]['button_text'] = $list_settings['double_optin_email_confirmation_button_text'];
                    $email_contents[$list_id]['footer_text'] = $list_settings['double_optin_email_confirmation_footer_text'];
                }
            }

            /*exit if there are no response emails*/
            $email_contents = array_filter($email_contents);
            if (!$email_contents) {
                return;
            }


            foreach ($email_contents as $list_id => $email_content) {

                $content = self::get_default_email_content($email_content);
                $confirm_subject = $email_content['email_subject'];

                $args = array(
                    'lead_id' => $lead['id'],
                    'list_ids' => $lists,
                    'email_id' => $lead['email']
                );

                $content = self::add_confirm_link_shortcode_params($content, $args);
                $content = do_shortcode($content , false);
                $content = str_replace(']]>', ']]&gt;', $content);

                $from_name = get_option('blogname', '');
                $from_email = get_option('admin_email');

                $headers = "From: " . $from_name . " <" . $from_email . ">\n";
                $headers .= 'Content-type: text/html';
                $headers = apply_filters('list_double_optin_lead_conversion/headers', $headers);

                wp_mail($lead['email'], $confirm_subject, $content, $headers);

                /* only send once */
                return;
            }

        }

        /**
         * Adds the lead id, the ids of the lists for the lead to confirm, and the lead's email to the shortcode
         * Also removes all text except the link text from the shortcode
         * params: $content: HTML string; the email content, $args : array(lead_id, email_id, list_ids); args to add to the shortcode.
         *
         * This could be reformatted into a general shortcode search and replace function
         */
        public static function add_confirm_link_shortcode_params($content, $args) {
            //regex for finding shortcodes, from https://core.trac.wordpress.org/browser/tags/3.6.1/wp-includes/shortcodes.php#L211
            $shortcode_regex = '/\['                             // Opening bracket
                . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
                . "(inbound-list-double-optin-link)"         // 2: Shortcode name
                . '(?![\\w-])'                       // Not followed by word character or hyphen
                . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
                . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
                . '(?:'
                . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
                . '[^\\]\\/]*'               // Not a closing bracket or forward slash
                . ')*?'
                . ')'
                . '(?:'
                . '(\\/)'                        // 4: Self closing tag ...
                . '\\]'                          // ... and closing bracket
                . '|'
                . '\\]'                          // Closing bracket
                . '(?:'
                . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
                . '[^\\[]*+'             // Not an opening bracket
                . '(?:'
                . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
                . '[^\\[]*+'         // Not an opening bracket
                . ')*+'
                . ')'
                . '\\[\\/\\2\\]'             // Closing shortcode tag
                . ')?'
                . ')'
                . '(\\]?)/';

            preg_match_all($shortcode_regex, $content, $matches);

            /**This adds the lead id, list ids, and lead email to each shortcode.  //There shouldn't be more than one shortcode though...
             * It also removes any atts other than the link text**/
            for ($i = 0; $i < count($matches[0]); $i++) {
                /*if the current shortcode is inbound-inbound-list-double-optin-link*/
                if ($matches[2][$i] == 'inbound-list-double-optin-link') {
                    /*if no link text has been specified*/
                    $replacement_shortcode = '[list-double-optin-link lead_id=' . (int)$args['lead_id'] . ' list_ids=' . implode(',', $args['list_ids']) . ' email_id=' . sanitize_email($args['email_id']) . ' ]';
                    $content = str_replace($matches[0][$i], $replacement_shortcode, $content);
                }
            }

            return $content;
        }

        /**
         * Removes leads from the waiting for double optin confirmation list if they've been added to lists directly
         */
        public static function remove_from_double_optin_list($lead_id, $list_ids) {
            $double_optin_lists = get_post_meta($lead_id, 'double_optin_lists', true);

            /* exit if there's no double optin lists */
            if (empty($double_optin_lists)) {
                return;
            }

            /* exit if the lead hasn't been added to a double optin list */
            if (!in_array($list_ids, $double_optin_lists)) {
                return;
            }

            if (!is_array($list_ids)) {
                $list_ids = array($list_ids);
            }


            foreach ($list_ids as $list_id) {
                if (in_array($list_id, $double_optin_lists)) {
                    $index = array_search($list_id, $double_optin_lists);
                    unset($double_optin_lists[$index]);
                }
            }

            /**if there are still lists awaiting double optin confirmation after list values have been removed**/
            if (!empty($double_optin_lists)) {
                /*update the meta listing with the remaining lists*/
                update_post_meta($lead_id, 'double_optin_lists', array_values($double_optin_lists));
            } else {
                /**if there are no lists awaiting double optin confirmation**/

                /*get the double optin waiting list id*/
                if (!defined('INBOUND_PRO_CURRENT_VERSION')) {
                    $double_optin_list_id = get_option('list-double-optin-list-id', '');
                } else {
                    $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                    $double_optin_list_id = $settings['leads']['list-double-optin-list-id'];
                }

                /*remove the meta listing for double optin*/
                delete_post_meta($lead_id, 'double_optin_lists');
                /*remove this lead from the double optin list*/
                wp_remove_object_terms($lead_id, (int)$double_optin_list_id, 'wplead_list_category');
                /*update the lead status*/
                update_post_meta($lead_id, 'wp_lead_status', 'read');
            }

        }


    }

    new Inbound_List_Double_Optin;
}
