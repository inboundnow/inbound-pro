<?php

/**
 * Class extends Inbound Forms to support `automated` inbound-email templates for instant followup emails
 * @package Mailer
 * @subpackage InboundForms
 */

class Inbound_Mailer_Forms_Integration {

    /**
     * Inbound_Mailer_Forms_Integration constructor.
     */
    public function __construct() {
        self::add_hooks();
    }

    /**
     * Add hooks and filters
     */
    public static function add_hooks() {
        add_filter( 'inbound-forms/email-reponse-hijack' , array( __CLASS__ , 'send_confirmation_email') , 10 , 3 );
        add_action( 'inbound-forms/before-email-reponse-setup' , array( __CLASS__ , 'extend_inbound_forms' ));
    }



    /**
     * Add email response setup options back into Inbound Forms
     */
    public static function extend_inbound_forms( $email_template ) {
        global $post;

        $email_template =	get_post_meta( $post->ID, 'inbound_email_send_notification_template' , TRUE );
        $emails = Inbound_Mailer_Post_Type::get_automation_emails_as( 'ARRAY' );
        if (!$emails) {
            $emails[] = __( 'No Automation emails detected. Please create an automated email first.' , 'inbound-pro' );
        }

        ?>

        <div style='display:block; overflow: auto;'>
            <div id=''>
                <label for="inbound_email_send_notification_template"><?php _e( 'Select Response Email Template' , 'inbound-pro' ); ?></label>
                <select name="inbound_email_send_notification_template" id="inbound_email_send_notification_template">
                    <option value='custom' <?php	selected( 'custom' , $email_template); ?>><?php _e( 'Do not use a premade email template' , 'inbound-pro' ); ?></option>
                    <?php

                    foreach ($emails as $id => $label) {
                        echo '<option value="'.$id.'" '. selected($id , $email_template , false ) .'>'.$label.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    <?php
    }

    /**
     * Hijack inbound form's email response system and use inbound mailer to send email response
     * @param $confirm_subject
     * @param $form_meta_data
     * @param $form_post_data
     * @return bool
     */
    public static function send_confirmation_email( $confirm_subject , $form_meta_data , $form_post_data) {
        $template_id = $form_meta_data['inbound_email_send_notification_template'][0];

        /* If Email Template Selected Use That */
        if ( !$template_id || $template_id == 'custom' ) {
            return false;
        }

        /* urldecode form post */
        $form_post_data = array_map('urldecode' , $form_post_data);

        $lead_id = Inbound_API::leads_get_id_from_email( $form_post_data['wpleads_email_address'] );
        $vid = Inbound_Mailer_Variations::get_next_variant_marker( $template_id );

        $args = array(
            'email_id' => $template_id,
            'vid' => $vid,
            'email_address' => $form_post_data['wpleads_email_address'],
            'lead_id' => $lead_id,
            'tags' => array('inbound-forms'),
            'email_recipients' => explode(',' , $form_post_data['inbound_form_lists'])
        );

        $response = Inbound_Mail_Daemon::send_solo_email( $args );

        /* returning true tells our legacy system to quit */
        return true;

    }


    /**
     * filter inbound form email response body
     * @param $confirm_body
     * @param $form_meta_data
     * @return mixed
     */
    public static function filter_lead_email_response_body( $confirm_body , $form_meta_data ) {

        $template_id = $form_meta_data['inbound_email_send_notification_template'];

        /* If Email Template Selected Use That */
        if ( empty($template_id) || $template_id == 'custom' ) {
            return $confirm_body;
        }

        $template_array = self::get_email_template( $template_id );

        return ( !empty($template_array['body']) ) ? $template_array['body'] :  $confirm_body;

    }

}

new Inbound_Mailer_Forms_Integration;
