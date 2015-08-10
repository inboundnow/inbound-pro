<?php

class Landing_Pages_Click_Tracking {

    /**
     * Initiate class
     */
    public function __construct() {
        self::add_hooks();
    }

    public static function add_hooks() {

        add_action('wp_footer', array( __CLASS__ , 'build_trackable_links') );
        /* Click Tracking init */
        add_action('init', array( __CLASS__ , 'intecept_tracked_link' ), 11);
    }

    /**
     * Generate a trackable link. In the case of landing pages we are still using the legacy model
     */
    public static function build_trackable_links() {
        global $post;

        if (!isset($post) || $post->post_type != 'landing-page') {
            return;
        }

        $variation = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : 0;
        $variation = preg_replace('/[^-a-zA-Z0-9_]/', '', $variation);

        ?>
        <script type="text/javascript">
            if ( typeof jQuery != 'undefined' ) {
                jQuery(document).ready(function($) {

                    var lead_cpt_id = _inbound.Utils.readCookie("wp_lead_id");
                    var lead_email = _inbound.Utils.readCookie("wp_lead_email");
                    var lead_unique_key = _inbound.Utils.readCookie("wp_lead_uid");

                    if (typeof (lead_cpt_id) != "undefined" && lead_cpt_id !== null) {
                        string = "&wpl_id=" + lead_cpt_id + "&l_type=wplid";
                    } else if (typeof (lead_email) != "undefined" && lead_email !== null && lead_email !== "") {
                        string = "&wpl_id=" + lead_email + "&l_type=wplemail";
                    } else if (typeof (lead_unique_key) != "undefined" && lead_unique_key !== null && lead_unique_key !== "") {
                        string = "&wpl_id=" + lead_unique_key + "&l_type=wpluid";
                    } else {
                        string = "";
                    }

                    var external = RegExp('^((f|ht)tps?:)?//(?!' + location.host + ')');
                    jQuery('.wpl-track-me-link, .inbound-special-class, .link-click-tracking a').not("#wpadminbar a").each(function () {

                        jQuery(this).attr("data-event-id", '<?php echo $post->ID; ?>').attr("data-cta-varation", '<?php echo $variation;?>');

                        var orignalurl = jQuery(this).attr("href");

                        var link_is = external.test(orignalurl);

                        if (link_is === true) {
                            base_url = window.location.origin;
                        } else {
                            base_url = orignalurl;
                        }

                        var variation_id = "&vid=" + jQuery(this).attr("data-cta-varation");
                        var this_id = jQuery(this).attr("data-event-id");
                        var newurl = base_url + "?lp_redirect_" + this_id + "=" + encodeURIComponent( orignalurl ) + variation_id + string;
                        jQuery(this).attr("href", newurl);
                    });
                });
            }
        </script>
        <?php

    }

    /**
     * Intecept tracked link, process it, and redirect visitor
     */
    public static function intecept_tracked_link() {
        global $wpdb;
        if ($qs = $_SERVER['REQUEST_URI']) {
            parse_str($qs, $output);
            (isset($output['l_type'])) ? $type = $output['l_type'] : $type = "";
            (isset($output['wpl_id'])) ? $lead_id = $output['wpl_id'] : $lead_id = "";
            (isset($output['vid'])) ? $variation_id = $output['vid'] : $variation_id = null;
            $pos = strpos($qs, 'lp_redirect');
            if (!(false === $pos)) {
                $link = substr($qs, $pos);
                $link = str_replace('lp_redirect=', '', $link); /* clean url */

                /* Extract the ID and get the link */
                $pattern = '/lp_redirect_(\d+?)\=/';
                preg_match($pattern, $link, $matches);
                $link = preg_replace($pattern, '', $link);
                $landing_page_id = $matches[1]; /* Event ID */
                $lead_ID = false;
                $append = true;
                /* If lead post id exists */
                if ($type === 'wplid') {
                    $lead_ID = $lead_id;
                }
                /* If lead email exists */
                elseif ($type === 'wplemail') {
                    $query = $wpdb->prepare(
                        'SELECT ID FROM ' . $wpdb->posts . '
				WHERE post_title = %s
				AND post_type = \'wp-lead\'',
                        $lead_id
                    );
                    $wpdb->query( $query );
                    if ( $wpdb->num_rows ) {
                        $lead_ID = $wpdb->get_var( $query );
                    }
                }
                /* If lead wp_uid exists */
                elseif ($type === 'wpluid') {
                    $query = $wpdb->prepare(
                        'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta
				WHERE meta_value = %s',
                        $lead_id
                    );
                    $wpdb->query( $query );
                    if ( $wpdb->num_rows ) {
                        $lead_ID = $wpdb->get_var( $query );
                    } else {
                        $lead_ID = $lead_id;
                        $append = false;
                    }
                }

                /* Save click! */
                self::store_click( $landing_page_id, $variation_id); /* Store CTA data to CTA CPT */

                if( $lead_ID && $append != false ) {
                    /* Add landing page click to lead profile */
                    self::log_lead_click($landing_page_id, $lead_ID, $variation_id);
                }
                $link = preg_replace('/(?<=wpl_id)(.*)(?=&)/s', '', $link); /* clean url */
                $link = preg_replace('/&wpl_id&l_type=(\D*)/', '', $link); /* clean url2 */
                $link = preg_replace('/&vid=(\d*)/', '', $link); /* clean url3 */
                $link = urldecode( $link );
                /* Redirect */
                header("HTTP/1.1 302 Temporary Redirect");
                header("Location:" . $link);
                /* I'm outta here! */
                exit(1);
            }
        }
    }

    /**
     * Stores click data
     */
    public static function store_click($landing_page_id, $variation_id){
        $conversions = Landing_Pages_Variations::get_conversions( $landing_page_id , $variation_id );
        $conversions++;
        Landing_Pages_Variations::set_conversions_count( $landing_page_id , $variation_id , $conversions );
    }

    /**
     * Log Landing Page click data into Lead Profile. Needs refactoring
     */
    public static function log_lead_click($landing_page_id, $lead_ID, $variation_id) {

        /* Current wordpress time from settings */
        $time = current_time( 'timestamp', 0 );
        $wordpress_date_time = date("Y-m-d G:i:s T", $time);

        if ( !$lead_ID ) {
            return;
        }

        $conversion_data = get_post_meta( $lead_ID, 'wpleads_conversion_data', TRUE );
        $individual_event_count = get_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, TRUE );
        $individual_event_count = ($individual_event_count != "") ? $individual_event_count : 0;
        $individual_event_count++;
        /* todo replace times */
        $meta = get_post_meta( $lead_ID, 'times', TRUE );
        $meta++;
        $conversions_count = get_post_meta($lead_ID,'wpl-lead-conversion-count', true);
        $conversions_count++;
        if ($conversion_data) {

            $conversion_data = json_decode($conversion_data,true);
            $conversion_data[$meta]['id'] = $landing_page_id;
            $conversion_data[$meta]['variation'] = $variation_id;
            $conversion_data[$meta]['datetime'] = $wordpress_date_time;
            $conversion_data = json_encode($conversion_data);
            update_post_meta( $lead_ID, 'wpleads_conversion_data', $conversion_data );
            update_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, $individual_event_count );
        } else {
            $conversion_data[1]['id'] = $landing_page_id;
            $conversion_data[1]['variation'] = $variation_id;
            $conversion_data[1]['datetime'] = $wordpress_date_time;
            $conversion_data[1]['first_time'] = 1;
            /* Add in exact link url clicked */
            $conversion_data = json_encode($conversion_data);
            update_post_meta( $lead_ID, 'wpleads_conversion_data', $conversion_data );
            update_post_meta( $lead_ID, 'wpleads_landing_page_'.$landing_page_id, $individual_event_count );
            /*	update_post_meta( $lead_ID, 'lt_event_tracked_'.$landing_page_id, $individual_event_count ); */
        }
        update_post_meta( $lead_ID, 'times', $meta );
        update_post_meta( $lead_ID, 'wpl-lead-conversion-count', $meta );
        /* Need to call conversion paths too */
    }

}

new Landing_Pages_Click_Tracking;