<?php
if(!class_exists('Inbound_Confirm_Double_Optin')){
    
    class Inbound_Confirm_Double_Optin{

        /**
         * Initialize class
         */
        function __construct(){
            self::add_hooks();
        }
        
        /**
         * Load Hooks and Filters
         */
        public static function add_hooks(){

            /* Shortcode for displaying list double opt in confirmation form */
            add_action( 'init' , array( __CLASS__, 'process_confrimation' ), 20 );

            /* Process shortcode to produce the confirmation link,
             * the name is different from the one the user uses to prevent early rendering */
            add_shortcode( 'list-double-optin-link', array( __CLASS__, 'render_confirm_link' ) );

        }


        /**
         * @param $atts
         * @return string
         */
        public static function process_confrimation(){
            global $inbound_settings;

            if (!isset($_REQUEST['inbound-action']) || $_REQUEST['inbound-action'] != 'confirm' ) {
                return;
            }

            /* get all lead lists */
            $lead_lists = Inbound_Leads::get_lead_lists_as_array();

            /* decode token */
            $params = Inbound_API::get_args_from_token( sanitize_text_field($_GET['token'] ));

            if ( !isset( $params['lead_id'] ) ) {
                return;
            }

            self::confirm_being_added_to_lists($params);
        }


        /**
        * Creates the double optin confirmation link
        * The shorcode used by the user is: inbound-list-double-optin-link.
        * But Inbound_List_Double_Optin::add_confirm_link_shortcode_params trims the name to: list-double-optin-link.
        * Then it gets rendered.
        * The reason for this is so the shorcode isn't rendered until the atts have been added to it.
        */
        public static function render_confirm_link( $params ) {

            $params = shortcode_atts( array(
                'lead_id' => '',
                'list_ids' => '-1',
                'email_id' => '-1'
            ), $params, 'list-double-optin-link');
            /* check to see if lead id is set as a REQUEST */
            if ( isset($params['lead_id']) ) {
                $params['lead_id'] = intval($params['lead_id']);
            } else if ( isset($_REQUEST['lead_id']) ) {
                $params['lead_id'] = intval($_REQUEST['lead_id']);
            } else if ( isset($_COOKIE['wp_lead_id']) ) {
                $params['lead_id'] = intval($_COOKIE['wp_lead_id']);
            }
            /* Add variation id to confirm link */
            $params['variation_id'] = ( isset($_REQUEST['inbvid']) )  ? intval($_REQUEST['inbvid']) : intval(0);

            /* generate confirm link */
            $confirm_link =  self::generate_confirm_link( $params );
            return $confirm_link;
        }



        /**
         *  Generates confirm url given lead id and lists
         *  @param ARRAY $params contains: lead_id (INT ), list_ids (MIXED), email_id (INT)
         *  @return STRING $confirm_url
         */
        public static function generate_confirm_link( $params ) {
            if (!isset($params['lead_id']) || !$params['lead_id']) {
                return __( '#confirm-not-available-in-online-mode' , 'inbound-pro' );
            }
            if (isset($_GET['lead_lists']) && !is_array($_GET['lead_lists'])){
                $params['list_ids'] = explode( ',' , $_GET['lead_lists']);
            } else if (isset($params['list_ids']) && !is_array($params['list_ids'])) {
                $params['list_ids'] = explode( ',' , $params['list_ids']);
            }
            $args = array_merge( $params , $_GET );
            $token = Inbound_API::analytics_get_tracking_code( $args );

            if(!defined('INBOUND_PRO_CURRENT_VERSION')){
                $double_optin_page_id = get_option('list-double-optin-page-id', '');
            }else{
                $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                $double_optin_page_id = $settings['leads']['list-double-optin-page-id'];
            } 
    
            if ( empty($double_optin_page_id) )  {
                $post = get_page_by_title( __( 'Confirm Subscription' , 'inbound-pro' ) );
                $double_optin_page_id =  $post->ID;
            }

            $base_url = get_permalink( $double_optin_page_id  );

            return add_query_arg( array( 'token'=>$token , 'inbound-action' => 'confirm' ) , $base_url );
        }
        


        /**
         *  Decodes confirm token into an array of parameters
         *  @param STRING $reader_id Encoded lead id.
         *  @return ARRAY $confirm array of confirmation data
         */
        public static function decode_confirm_token( $token ) {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decrypted_string =
                trim(
                    mcrypt_decrypt(
                        MCRYPT_RIJNDAEL_256 ,  substr( SECURE_AUTH_KEY , 0 , 16 )   ,  base64_decode( str_replace(array('-', '_', '^'), array('+', '/', '='), $token ) ) , MCRYPT_MODE_ECB, $iv
                    )
                );
            return json_decode($decrypted_string , true);
        }

        /**
         * Adds the lead to lists he selected when filling out the confirmation form.
         * If all_lists was selected, all lists currently waiting for confirmation will be selected and the lead will be added to those.
         */
        public static function confirm_being_added_to_lists($params, $all = false){
            
            /*get the double optin waiting list id*/
            if(!defined('INBOUND_PRO_CURRENT_VERSION')){
                $double_optin_list_id = get_option('list-double-optin-list-id', '');
            }else{
                $settings = Inbound_Options_API::get_option('inbound-pro', 'settings', array());
                $double_optin_list_id = $settings['leads']['list-double-optin-list-id'];
            }


            /*get the lists waiting to be opted into*/
            $stored_double_optin_lists = get_post_meta($params['lead_id'], 'double_optin_lists', true);
            
            /*if there aren't any lists, exit*/
            if(empty($stored_double_optin_lists)){
                return;
            }
            
            /*if opt into all lists has been selected, set list ids to all stored list ids*/
            if($all){
                $params['list_ids'] = $stored_double_optin_lists;
            }
            
            /**for each supplied list, add the lead to the list. 
             * And remove the list id from the array of lists needing to be opted into**/
            foreach($params['list_ids'] as $list_id){
                Inbound_Leads::add_lead_to_list($params['lead_id'], $list_id);
                
                if(in_array($list_id, $stored_double_optin_lists)){
                    $index = array_search($list_id, $stored_double_optin_lists);
                    unset($stored_double_optin_lists[$index]);
                }
            }

            /**if there are still lists awaiting double optin confirmation after the "waiting" meta listing has been updated**/
            if(!empty($stored_double_optin_lists)){
                /*update the "waiting" meta listing with the remaining lists*/
                update_post_meta($params['lead_id'], 'double_optin_lists', array_values($stored_double_optin_lists));
            }else{
            /**if there are no lists awaiting double optin confirmation**/
                /*remove the meta listing for double optin*/
                delete_post_meta($params['lead_id'], 'double_optin_lists');
                /*remove this lead from the double optin list*/
                wp_remove_object_terms($params['lead_id'], $double_optin_list_id, 'wplead_list_category');
                /*update the lead status*/
                update_post_meta( $params['lead_id'], 'wp_lead_status', 'active');            
            }
        }
    }
    new Inbound_Confirm_Double_Optin;

}
