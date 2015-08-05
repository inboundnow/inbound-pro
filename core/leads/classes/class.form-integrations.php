<?php

/**
 * Class Leads_Form_Integrations
 * This calss helps add basic tracking to 3rd party form plugins
 */

class Leads_Form_Integrations {

    public function __construct() {
        self::add_hooks();
    }

    public static function add_hooks() {
        /* contact form 7 */
        add_filter( 'wpcf7_form_class_attr', array( __CLASS__ , 'wpl_contact_form_7' ) );

        /* gravity form */
        add_filter("gform_form_tag", array( __CLASS__ , "wpl_gravity_forms" ) , 10, 2);

        /* ninja forms */
        add_filter( 'ninja_forms_form_class', array( __CLASS__ , 'wpl_ninja_forms' ) , 10, 2 );
    }

    /**
     * Contact form 7 support
     * @param $content
     * @return mixed
     */
    public static function wpl_contact_form_7( $content ) {
        $rl_formfind = '/wpcf7-form/';
        $rl_formreplace = 'wpcf7-form wpl-track-me';
        $content = preg_replace( $rl_formfind, $rl_formreplace, $content );
        return $content;
    }

    /**
     * Gravity Forms
     * @param $form_tag
     * @param $form
     * @return mixed
     */
    public static function wpl_gravity_forms($form_tag, $form){

        if (stristr($form_tag,'class=')) {
            $form_tag = preg_replace("|class='(.*?)'|", "class='$1 wpl-track-me'", $form_tag);
        } else {
            $form_tag = preg_replace("|action='(.*?)'|", "action='$1' class='wpl-track-me' ", $form_tag);
        }
        return $form_tag;
    }

    /**
     * Ninja Forms
     * @param $form_class
     * @param $form_id
     * @return string
     */
    public static function wpl_ninja_forms( $form_class, $form_id ){
        $form_class = $form_class." wpl-track-me";
        return $form_class;
    }
}

new Leads_Form_Integrations;