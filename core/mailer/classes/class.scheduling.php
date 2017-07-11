<?php
/**
 * Class Inbound_Mailer_Scheduling provides methods for scheduling emails, generating batches, and other supportive routines
 *
 * @package Mailer
 * @subpackage  Scheduling
 */

class Inbound_Mailer_Scheduling {

    static $email_id; /* placeholder for email id */
    static $settings; /* placeholder for email settings */
    static $batches; /* placeholder for lead batches */
    static $recipients; /* placeholder will allow manually defining recipents */

    /**
     *    Determine batching patterns
     * @param INT $email_id
     */
    public static function create_batches($email_id) {

        $params = array();

        /* get settings */
        $settings = Inbound_Mailer_Scheduling::$settings;

        /* get variations */
        $variations = $settings['variations'];

        /* count variations */
        $variation_count = count($variations);
        if (isset(Inbound_Mailer_Scheduling::$recipients)) {
            $settings['recipients'] = Inbound_Mailer_Scheduling::$recipients;
        }


        /* Prepare leads lookup */
        $params = array(
            'include_lists' => $settings['recipients'],
            'return' => 'ID',
            'results_per_page' => -1,
            'orderby' => 'rand',
            'fields' => 'ids'
        );


        $results = Inbound_API::leads_get($params);
        $leads = $results->posts;

        $chunk_size = round(count($leads) / $variation_count);

        /* sometimes we may have less leads than variations */
        if ($chunk_size>1) {
            $batches = array_chunk($leads, $chunk_size);

            /* if the batch variation id is not already correct then change keys */
            $i = 0;
            foreach ($variations as $vid => $settings) {
                $batch_array[$vid] = $batches[$i];
                $i++;
            }
        } else {
            $batch_array[0] = $leads;
        }




        self::$batches = $batch_array;
        //error_log(print_r(self::$batches,true));
    }

    /**
     * Schedules email
     * @param $email_id Post ID of automated email to send
     * @param array $tokens Tokens belonging to Automation Rule Trigger
     * @param array $action Action information belonging to Automation Rule
     * @return int
     */
    public static function schedule_email($email_id , $tokens = array() , $action = array() , $recipients = null ) {
        global $wpdb;

        /* set recipeints */
        Inbound_Mailer_Scheduling::$recipients = ($recipients ) ?  $recipients : Inbound_Mailer_Scheduling::$recipients;

        /* load email settings into static variable */
        Inbound_Mailer_Scheduling::$settings = Inbound_Email_Meta::get_settings($email_id);

        /* Prepare lead batches */
        Inbound_Mailer_Scheduling::create_batches($email_id);

        /* Set target mysql table name */
        $table_name = $wpdb->prefix . "inbound_email_queue";

        /* Prepare Schedule time */
        $timestamp = Inbound_Mailer_Scheduling::get_timestamp();

        /* prepare rule and action id if none are set */
        if (!isset($action['rule_id'])) {
            $action['rule_id'] = 0;
            $action['job_id'] = 0;
        }

        /* prepare tokens for MySQL */
        $tokens_encoded = str_replace("'", "\\'", json_encode($tokens));

        /* check for a post id inside of tokens */
        $post_id =  (isset($tokens['post_object']) && isset($tokens['post_object']['ID'])) ? $tokens['post_object']['ID'] : 0;

        /* prepare multi insert query string - limit to 1000 inserts at a time */
        $send_count = 0;
        foreach (self::$batches as $vid => $leads) {
            $send_count = $send_count + count($leads);

            $query_values_array = array();
            $query_prefix = "INSERT INTO {$table_name} ( `email_id` , `variation_id` , `lead_id` , `type` , `tokens` ,`status` , `datetime` , `rule_id` , `job_id`, `list_ids`, `post_id` )";
            $query_prefix .= "VALUES";

            foreach ($leads as $ID) {
                $query_values_array[] = "( {$email_id} , {$vid} , {$ID} , '" . Inbound_Mailer_Scheduling::$settings['email_type'] . "' , '".$tokens_encoded."' ,'waiting' , '{$timestamp}' , '{$action['rule_id']}' , '{$action['job_id']}', '".json_encode(Inbound_Mailer_Scheduling::$recipients)."' , '".$post_id."')";
            }

            $value_batches = array_chunk($query_values_array, 500);
            foreach ($value_batches as $values) {
                $query_values = implode(',', $values);
                $query = $query_prefix . $query_values;
                $wpdb->query($query);
            }
        }

        return $send_count;
    }

    /**
     *    Unscheduled email
     */
    public static function unschedule_email( $email_id ) {

        do_action('mailer/unschedule-email' , $email_id );

        /* remove any error flags */
        Inbound_Options_API::update_option('inbound-email', 'errors-detected', false);
    }

    /**
     *    Get timestamp given saved timezone information
     */
    public static function get_timestamp() {
        global $inbound_settings;

        /* Set email service */
        $email_service = (isset($inbound_settings['mailer']['mail-service'])) ? $inbound_settings['mailer']['mail-service'] : 'sparkpost' ;

        $settings = Inbound_Mailer_Scheduling::$settings;

        if ($settings['email_type'] == 'automated') {
            return gmdate("Y-m-d\\TG:i:s\\Z");
        }

        $tz = explode('-UTC', $settings['timezone']);
        $timezone = timezone_name_from_abbr($tz[0], 60 * 60 * intval($tz[1]));

        date_default_timezone_set($timezone);

        switch ($email_service) {
            case "sparkpost":
                $timestamp = date("Y-m-d\\TG:i:s\\Z", strtotime($settings['send_datetime']));
                break;
        }



        return $timestamp;
    }


    /**
     *    Get's current utc timezone offset
     */
    public static function get_current_timezone() {
        $gmt_offset = get_option('gmt_offset');
        $timezone = timezone_name_from_abbr("", $gmt_offset * 60 * 60, 0);
        $timezone = ($timezone) ? $timezone : get_option('timezone_string');
        $dateTime = new DateTime();
        $dateTime->setTimeZone(new DateTimeZone($timezone));

        return array('abbr' => $dateTime->format('T'), 'offset' => $gmt_offset);
    }

    /**
     *    Get array of timezones
     */
    public static function get_timezones() {
        return array(
            array('abbr' => 'NUT', 'name' => __('Niue Time', 'inbound-email'), 'utc' => 'UTC-11'),
            array('abbr' => 'SST', 'name' => __('Samoa Standard Time', 'inbound-email'), 'utc' => 'UTC-11'),
            array('abbr' => 'CKT', 'name' => __('Cook Island Time', 'inbound-email'), 'utc' => 'UTC-10'),
            array('abbr' => 'HAST', 'name' => __('Hawaii-Aleutian Standard Time', 'inbound-email'), 'utc' => 'UTC-10'),
            array('abbr' => 'HST', 'name' => __('Hawaii Standard Time', 'inbound-email'), 'utc' => 'UTC-10'),
            array('abbr' => 'TAHT', 'name' => __('Tahiti Time', 'inbound-email'), 'utc' => 'UTC-10'),
            array('abbr' => 'MART', 'name' => __('Marquesas Islands Time', 'inbound-email'), 'utc' => 'UTC-9:30'),
            array('abbr' => 'MIT', 'name' => __('Marquesas Islands Time', 'inbound-email'), 'utc' => 'UTC-9:30'),
            array('abbr' => 'AKST', 'name' => __('Alaska Standard Time', 'inbound-email'), 'utc' => 'UTC-9'),
            array('abbr' => 'GAMT', 'name' => __('Gambier Islands', 'inbound-email'), 'utc' => 'UTC-9'),
            array('abbr' => 'GIT', 'name' => __('Gambier Island Time', 'inbound-email'), 'utc' => 'UTC-9'),
            array('abbr' => 'HADT', 'name' => __('Hawaii-Aleutian Daylight Time', 'inbound-email'), 'utc' => 'UTC-9'),
            array('abbr' => 'AKDT', 'name' => __('Alaska Daylight Time', 'inbound-email'), 'utc' => 'UTC-8'),
            array('abbr' => 'CIST', 'name' => __('Clipperton Island Standard Time', 'inbound-email'), 'utc' => 'UTC-8'),
            array('abbr' => 'PST', 'name' => __('Pacific Standard Time (North America)', 'inbound-email'), 'utc' => 'UTC-8'),
            array('abbr' => 'MDT', 'name' => __('Mountain Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-7'),
            array('abbr' => 'PDT', 'name' => __('Pacific Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-7'),
            array('abbr' => 'PDT', 'name' => __('Pacific Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-7'),
            array('abbr' => 'CST', 'name' => __('Central Standard Time (North America)', 'inbound-email'), 'utc' => 'UTC-6'),
            array('abbr' => 'EAST', 'name' => __('Easter Island Standard Time', 'inbound-email'), 'utc' => 'UTC-6'),
            array('abbr' => 'GALT', 'name' => __('Galapagos Time', 'inbound-email'), 'utc' => 'UTC-6'),
            array('abbr' => 'MDT', 'name' => __('Mountain Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-6'),
            array('abbr' => 'CDT', 'name' => __('Central Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'COT', 'name' => __('Colombia Time', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'CST', 'name' => __('Cuba Standard Time', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'EASST', 'name' => __('Easter Island Standard Summer Time', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'ECT', 'name' => __('Ecuador Time', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'EST', 'name' => __('Eastern Standard Time (North America)', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'PET', 'name' => __('Peru Time', 'inbound-email'), 'utc' => 'UTC-5'),
            array('abbr' => 'VET', 'name' => __('Venezuelan Standard Time', 'inbound-email'), 'utc' => 'UTC-4:30'),
            array('abbr' => 'AMT', 'name' => __('Amazon Time (Brazil)[2]', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'AST', 'name' => __('Atlantic Standard Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'BOT', 'name' => __('Bolivia Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'CDT', 'name' => __('Cuba Daylight Time[3]', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'CLT', 'name' => __('Chile Standard Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'COST', 'name' => __('Colombia Summer Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'ECT', 'name' => __('Eastern Caribbean Time (does not recognise DST)', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'EDT', 'name' => __('Eastern Daylight Time (North America)', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'FKT', 'name' => __('Falkland Islands Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'GYT', 'name' => __('Guyana Time', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'PYT', 'name' => __('Paraguay Time (Brazil)[7]', 'inbound-email'), 'utc' => 'UTC-4'),
            array('abbr' => 'NST', 'name' => __('Newfoundland Standard Time', 'inbound-email'), 'utc' => 'UTC-3:30'),
            array('abbr' => 'NT', 'name' => __('Newfoundland Time', 'inbound-email'), 'utc' => 'UTC-3:30'),
            array('abbr' => 'ADT', 'name' => __('Atlantic Daylight Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'AMST', 'name' => __('Amazon Summer Time (Brazil)[1]', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'ART', 'name' => __('Argentina Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'BRT', 'name' => __('Brasilia Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'CLST', 'name' => __('Chile Summer Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'FKST', 'name' => __('Falkland Islands Standard Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'FKST', 'name' => __('Falkland Islands Summer Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'GFT', 'name' => __('French Guiana Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'PMST', 'name' => __('Saint Pierre and Miquelon Standard Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'PYST', 'name' => __('Paraguay Summer Time (Brazil)', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'ROTT', 'name' => __('Rothera Research Station Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'SRT', 'name' => __('Suriname Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'UYT', 'name' => __('Uruguay Standard Time', 'inbound-email'), 'utc' => 'UTC-3'),
            array('abbr' => 'NDT', 'name' => __('Newfoundland Daylight Time', 'inbound-email'), 'utc' => 'UTC-2:30'),
            array('abbr' => 'FNT', 'name' => __('Fernando de Noronha Time', 'inbound-email'), 'utc' => 'UTC-2'),
            array('abbr' => 'GST', 'name' => __('South Georgia and the South Sandwich Islands', 'inbound-email'), 'utc' => 'UTC-2'),
            array('abbr' => 'PMDT', 'name' => __('Saint Pierre and Miquelon Daylight time', 'inbound-email'), 'utc' => 'UTC-2'),
            array('abbr' => 'UYST', 'name' => __('Uruguay Summer Time', 'inbound-email'), 'utc' => 'UTC-2'),
            array('abbr' => 'AZOST', 'name' => __('Azores Standard Time', 'inbound-email'), 'utc' => 'UTC-1'),
            array('abbr' => 'CVT', 'name' => __('Cape Verde Time', 'inbound-email'), 'utc' => 'UTC-1'),
            array('abbr' => 'EGT', 'name' => __('Eastern Greenland Time', 'inbound-email'), 'utc' => 'UTC-1'),
            array('abbr' => 'GMT', 'name' => __('Greenwich Mean Time', 'inbound-email'), 'utc' => 'UTC'),
            array('abbr' => 'UCT', 'name' => __('Coordinated Universal Time', 'inbound-email'), 'utc' => 'UTC'),
            array('abbr' => 'UTC', 'name' => __('Coordinated Universal Time', 'inbound-email'), 'utc' => 'UTC'),
            array('abbr' => 'WET', 'name' => __('Western European Time', 'inbound-email'), 'utc' => 'UTC'),
            array('abbr' => 'Z', 'name' => __('Zulu Time (Coordinated Universal Time)', 'inbound-email'), 'utc' => 'UTC'),
            array('abbr' => 'EGST', 'name' => __('Eastern Greenland Summer Time', 'inbound-email'), 'utc' => 'UTC+00'),
            array('abbr' => 'BST', 'name' => __('British Summer Time (British Standard Time from Feb 1968 to Oct 1971)', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'CET', 'name' => __('Central European Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'DFT', 'name' => __('AIX specific equivalent of Central European Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'IST', 'name' => __('Irish Standard Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'MET', 'name' => __('Middle European Time Same zone as CET', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'WAT', 'name' => __('West Africa Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'WEDT', 'name' => __('Western European Daylight Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'WEST', 'name' => __('Western European Summer Time', 'inbound-email'), 'utc' => 'UTC+01'),
            array('abbr' => 'CAT', 'name' => __('Central Africa Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'CEDT', 'name' => __('Central European Daylight Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'CEST', 'name' => __('Central European Summer Time (Cf. HAEC)', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'EET', 'name' => __('Eastern European Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'HAEC', 'name' => __('Heure Avance d\'Europe Centrale francised name for CEST', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'IST', 'name' => __('Israel Standard Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'MEST', 'name' => __('Middle European Saving Time Same zone as CEST', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'SAST', 'name' => __('South African Standard Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'WAST', 'name' => __('West Africa Summer Time', 'inbound-email'), 'utc' => 'UTC+02'),
            array('abbr' => 'AST', 'name' => __('Arabia Standard Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'EAT', 'name' => __('East Africa Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'EEDT', 'name' => __('Eastern European Daylight Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'EEST', 'name' => __('Eastern European Summer Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'FET', 'name' => __('Further-eastern European Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'IDT', 'name' => __('Israel Daylight Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'IOT', 'name' => __('Indian Ocean Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'SYOT', 'name' => __('Showa Station Time', 'inbound-email'), 'utc' => 'UTC+03'),
            array('abbr' => 'IRST', 'name' => __('Iran Standard Time', 'inbound-email'), 'utc' => 'UTC+03:30'),
            array('abbr' => 'AMT', 'name' => __('Armenia Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'AZT', 'name' => __('Azerbaijan Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'GET', 'name' => __('Georgia Standard Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'GST', 'name' => __('Gulf Standard Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'MSK', 'name' => __('Moscow Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'MUT', 'name' => __('Mauritius Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'RET', 'name' => __('R??union Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'SAMT', 'name' => __('Samara Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'SCT', 'name' => __('Seychelles Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'VOLT', 'name' => __('Volgograd Time', 'inbound-email'), 'utc' => 'UTC+04'),
            array('abbr' => 'AFT', 'name' => __('Afghanistan Time', 'inbound-email'), 'utc' => 'UTC+04:30'),
            array('abbr' => 'AMST', 'name' => __('Armenia Summer Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'HMT', 'name' => __('Heard and McDonald Islands Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'MAWT', 'name' => __('Mawson Station Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'MVT', 'name' => __('Maldives Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'ORAT', 'name' => __('Oral Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'PKT', 'name' => __('Pakistan Standard Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'TFT', 'name' => __('Indian/Kerguelen', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'TJT', 'name' => __('Tajikistan Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'TMT', 'name' => __('Turkmenistan Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'UZT', 'name' => __('Uzbekistan Time', 'inbound-email'), 'utc' => 'UTC+05'),
            array('abbr' => 'IST', 'name' => __('Indian Standard Time', 'inbound-email'), 'utc' => 'UTC+05:30'),
            array('abbr' => 'SLST', 'name' => __('Sri Lanka Time', 'inbound-email'), 'utc' => 'UTC+05:30'),
            array('abbr' => 'NPT', 'name' => __('Nepal Time', 'inbound-email'), 'utc' => 'UTC+05:45'),
            array('abbr' => 'BIOT', 'name' => __('British Indian Ocean Time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'BST', 'name' => __('Bangladesh Standard Time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'BTT', 'name' => __('Bhutan Time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'KGT', 'name' => __('Kyrgyzstan time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'VOST', 'name' => __('Vostok Station Time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'YEKT', 'name' => __('Yekaterinburg Time', 'inbound-email'), 'utc' => 'UTC+06'),
            array('abbr' => 'CCT', 'name' => __('Cocos Islands Time', 'inbound-email'), 'utc' => 'UTC+06:30'),
            array('abbr' => 'MMT', 'name' => __('Myanmar Time', 'inbound-email'), 'utc' => 'UTC+06:30'),
            array('abbr' => 'MYST', 'name' => __('Myanmar Standard Time', 'inbound-email'), 'utc' => 'UTC+06:30'),
            array('abbr' => 'CXT', 'name' => __('Christmas Island Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'DAVT', 'name' => __('Davis Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'HOVT', 'name' => __('Khovd Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'ICT', 'name' => __('Indochina Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'KRAT', 'name' => __('Krasnoyarsk Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'OMST', 'name' => __('Omsk Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'THA', 'name' => __('Thailand Standard Time', 'inbound-email'), 'utc' => 'UTC+07'),
            array('abbr' => 'ACT', 'name' => __('ASEAN Common Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'AWST', 'name' => __('Australian Western Standard Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'BDT', 'name' => __('Brunei Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'CHOT', 'name' => __('Choibalsan', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'CIT', 'name' => __('Central Indonesia Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'CST', 'name' => __('China Standard Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'CT', 'name' => __('China time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'HKT', 'name' => __('Hong Kong Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'IRDT', 'name' => __('Iran Daylight Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'MYT', 'name' => __('Malaysia Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'PHT', 'name' => __('Philippine Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'SGT', 'name' => __('Singapore Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'SST', 'name' => __('Singapore Standard Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'ULAT', 'name' => __('Ulaanbaatar Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'WST', 'name' => __('Western Standard Time', 'inbound-email'), 'utc' => 'UTC+08'),
            array('abbr' => 'CWST', 'name' => __('Central Western Standard Time (Australia)', 'inbound-email'), 'utc' => 'UTC+08:45'),
            array('abbr' => 'AWDT', 'name' => __('Australian Western Daylight Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'EIT', 'name' => __('Eastern Indonesian Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'IRKT', 'name' => __('Irkutsk Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'JST', 'name' => __('Japan Standard Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'KST', 'name' => __('Korea Standard Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'TLT', 'name' => __('Timor Leste Time', 'inbound-email'), 'utc' => 'UTC+09'),
            array('abbr' => 'ACST', 'name' => __('Australian Central Standard Time', 'inbound-email'), 'utc' => 'UTC+09:30'),
            array('abbr' => 'CST', 'name' => __('Central Standard Time (Australia)', 'inbound-email'), 'utc' => 'UTC+09:30'),
            array('abbr' => 'AEST', 'name' => __('Australian Eastern Standard Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'ChST', 'name' => __('Chamorro Standard Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'CHUT', 'name' => __('Chuuk Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'DDUT', 'name' => __('Dumont d\'Urville Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'EST', 'name' => __('Eastern Standard Time (Australia)', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'PGT', 'name' => __('Papua New Guinea Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'VLAT', 'name' => __('Vladivostok Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'YAKT', 'name' => __('Yakutsk Time', 'inbound-email'), 'utc' => 'UTC+10'),
            array('abbr' => 'ACDT', 'name' => __('Australian Central Daylight Time', 'inbound-email'), 'utc' => 'UTC+10:30'),
            array('abbr' => 'CST', 'name' => __('Central Summer Time (Australia)', 'inbound-email'), 'utc' => 'UTC+10:30'),
            array('abbr' => 'LHST', 'name' => __('Lord Howe Standard Time', 'inbound-email'), 'utc' => 'UTC+10:30'),
            array('abbr' => 'AEDT', 'name' => __('Australian Eastern Daylight Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'KOST', 'name' => __('Kosrae Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'LHST', 'name' => __('Lord Howe Summer Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'MIST', 'name' => __('Macquarie Island Station Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'NCT', 'name' => __('New Caledonia Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'PONT', 'name' => __('Pohnpei Standard Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'SAKT', 'name' => __('Sakhalin Island time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'SBT', 'name' => __('Solomon Islands Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'VUT', 'name' => __('Vanuatu Time', 'inbound-email'), 'utc' => 'UTC+11'),
            array('abbr' => 'NFT', 'name' => __('Norfolk Time', 'inbound-email'), 'utc' => 'UTC+11:30'),
            array('abbr' => 'FJT', 'name' => __('Fiji Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'GILT', 'name' => __('Gilbert Island Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'MAGT', 'name' => __('Magadan Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'MHT', 'name' => __('Marshall Islands', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'NZST', 'name' => __('New Zealand Standard Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'PETT', 'name' => __('Kamchatka Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'TVT', 'name' => __('Tuvalu Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'WAKT', 'name' => __('Wake Island Time', 'inbound-email'), 'utc' => 'UTC+12'),
            array('abbr' => 'CHAST', 'name' => __('Chatham Standard Time', 'inbound-email'), 'utc' => 'UTC+12:45'),
            array('abbr' => 'NZDT', 'name' => __('New Zealand Daylight Time', 'inbound-email'), 'utc' => 'UTC+13'),
            array('abbr' => 'PHOT', 'name' => __('Phoenix Island Time', 'inbound-email'), 'utc' => 'UTC+13'),
            array('abbr' => 'TOT', 'name' => __('Tonga Time', 'inbound-email'), 'utc' => 'UTC+13'),
            array('abbr' => 'CHADT', 'name' => __('Chatham Daylight Time', 'inbound-email'), 'utc' => 'UTC+13:45'),
            array('abbr' => 'LINT', 'name' => __('Line Islands Time', 'inbound-email'), 'utc' => 'UTC+14'),
            array('abbr' => 'TKT', 'name' => __('Tokelau Time', 'inbound-email'), 'utc' => 'UTC+14'),
        );
    }


}
