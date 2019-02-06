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
        $body = self::generate_tracking_pixel($body);
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: '.self::$email['from_name'].'<'.self::$email['from_email'].'>';
        $headers[] = 'Reply-To: '.self::$email['reply_email'].'';


        $result = wp_mail( $to, $subject, $body, $headers );

        if ($result) {
            self::$response = array(
                'message'=> 'success',
                'email' => self::$email
            );
        } else {
            self::$response = array(
                'message'=> 'fail',
                'email' => self::$email
            );
        }

        do_action( 'mailer/wp_mail/send' , self::$response , (array) self::$email , (array) self::$row  );
    }

    /**
     * Generates & appends Tracking Pixel HTML
     * @param $body
     * @return string
     */
    public static function generate_tracking_pixel( $body ) {

        $params = (array) self::$row;

        /* unset unneeded keys */
        unset($params['tokens']);
        unset($params['datetime']);

        /* set action for processing */
        $params['action'] = 'wpmail_open';

        $tracking_url = add_query_arg( $params , site_url());

        $tracking_html = "<img src='{$tracking_url}' width=0 height=0>";

        return $body.$tracking_html;
    }



}

