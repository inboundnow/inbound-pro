<?php

/**
 * Class adds Lead elements to WordPress User profile
 *
 * @package     Leads
 * @subpackage  UserProfile
 */

if (!class_exists('Leads_User_Profile')) {

    class Leads_User_Profile {

        function __construct() {
            add_filter( 'show_user_profile', array( __CLASS__, 'add_lead_list_setup' ), 10 );
            add_filter( 'edit_user_profile', array( __CLASS__, 'add_lead_list_setup' ), 10 );

            add_filter( 'edit_user_profile_update', array( __CLASS__, 'save_user_data' ), 10 );
            add_filter( 'personal_options_update', array( __CLASS__, 'save_user_data' ), 10 );
        }


        public static function add_lead_list_setup( $user )  {

            if ( !current_user_can('editor') && !current_user_can('administrator') ) {
                return;
            }

            $lead_id = LeadStorage::lookup_lead_by_email($user->user_email);

            echo '<h2>' . __( 'Lead Profile' , 'inbound-pro' ) . '</h2>';

            if (!$lead_id) {
                echo '<i>'.__( 'A lead profile does not exist yet for this user.','inbound-pro') . '</i>';
                return;
            }

            echo '<input type="hidden" name="lead_id" value="'.$lead_id.'">';
            echo '<a href="'.admin_url('post.php?post='.$lead_id.'&action=edit').'" class="button button-secondary">'.__("View Lead Profile" , "inbound-pro" ).'</a>';

            echo '<h2>' . __( 'Lead Lists' , 'inbound-pro' ) . '</h2>';
            echo '<div class="user-profile-lead-lists">';

            $lead_lists = Inbound_Leads::get_lead_lists_by_lead_id($lead_id);

            $args = array(
                'descendants_and_self'  => false,
                'selected_cats'         => false,
                'popular_cats'          => false,
                'walker'                => null,
                'taxonomy'              => 'wplead_list_category',
                'checked_ontop'         => false
            );
            wp_terms_checklist( $lead_id , $args );
            echo '</div>';
            ?>
            <style>
                .user-profile-lead-lists ul:not(:first-child) {
                    margin-left:20px;
                }
                .user-profile-lead-lists ul {
                    list-style-type:none;
                    margin-top:2px;
                }
            </style>
            <?php

        }

        public static function save_user_data( $user_id ) {

            if (!isset($_REQUEST['tax_input']) || !isset($_REQUEST['lead_id']) ) {
                return;
            }

            if (!isset($_REQUEST['tax_input']['wplead_list_category'])) {
                return;
            }

            foreach ($_REQUEST['tax_input']['wplead_list_category'] as $list_id) {
                Inbound_Leads::add_lead_to_list( intval($_REQUEST['lead_id']) , $list_id );
            }

        }
    }

    new Leads_User_Profile();

}