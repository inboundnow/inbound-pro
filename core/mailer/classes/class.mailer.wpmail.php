<?php

/**
 * Class Inbound_Mailer_WPMail extends Inbound_Mail_Daemon to power WPMail sends
 * @package Mailer
 * @subpackage WPMail
 */
class Inbound_Mailer_WPMail extends Inbound_Mail_Daemon {

    /**
     *	Sends email to WPMail
     */
    public static function send_email() {
        $settings = Inbound_Mailer_Settings::get_settings();

        $to = self::$email['send_address'];
        $subject = self::$email['subject'];
        $body = self::$email['body'];
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $result = wp_mail( $to, $subject, $body, $headers );

        if ($result) {
            self::$response = array('message'=>__('success','inbound-pro'));
        } else {
            self::$response = array('message'=>__('fail','inbound-pro'));
        }

        do_action( 'mailer/wp_mail/send' , self::$email  );
    }

}
