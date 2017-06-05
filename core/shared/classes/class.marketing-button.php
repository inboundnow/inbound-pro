<?php
/**
 * Inbound Marketing Button in editor
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Inbound_Marketing_Button {

    public function __construct() {
        self::init();
    }

    public function init() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'load_marketing_button_js'), 101);
        add_action( 'media_buttons', array(__CLASS__, 'inbound_marketing_button'), 11);
        add_action( 'admin_footer', array(__CLASS__, 'for_popup'));
    }
    static function load_marketing_button_js() {
        wp_enqueue_script('inbound-marketing-button', INBOUNDNOW_SHARED_URLPATH . 'assets/js/admin/marketing-button.js' , array() , null , false );
        wp_enqueue_script('maginificient-popup', INBOUNDNOW_SHARED_URLPATH . 'assets/js/global/jquery.magnific-popup.min.js' , array() , null , false );
        wp_enqueue_style('maginificient-popup-css', INBOUNDNOW_SHARED_URLPATH . 'assets/css/magnific-popup.css');
    }
    /*
     There are two places the marketing button renders:
     in normal WP editors and via JS for ACF normal
     */
    static function inbound_marketing_button($editor_id) {
        global $pagenow, $typenow, $wp_version;
        $output = '';
        /** Only run in post/page creation and edit screens */
        if (in_array($pagenow, array('post.php','page.php','post-new.php','post-edit.php' ))) {
            /* check current WP version */
            if ( version_compare( $wp_version, '3.5', '<' ) ) {
                $img = '<img width="20" height="20" src="'.INBOUNDNOW_SHARED_URLPATH.'assets/images/global/inbound-icon.png" />';
            } else {
                $img = '<span class="wp-media-buttons-icon" id="inboundnow-media-button"></span>';
            }
            $output = '<a style="padding-left: 3px;" href="#inbound-marketing-popup" class="open-marketing-button-popup inbound-marketing-button button" data-editor="'.$editor_id.'_ifr" class="button">'.$img.'Marketing</a>';
        }
        echo $output;
    }

    static function for_popup() {
        global $pagenow, $typenow;
        // Only run in post/page creation and edit screens
        if (in_array($pagenow, array('post.php','page.php','post-new.php','post-edit.php'))) { ?>
            <style type="text/css">
                #inbound-shortcodes-popup {
                    min-height: 650px;
                }
                #marketing-popup-controls {
                    position: fixed;
                    bottom: 20px;
                    width: 100%;
                }
                .marketing-back-button {
                    position: absolute;
                    top: 15px;
                    left: 20px;
                    cursor: pointer;
                }
                #inbound-shortcodes-form-head {
                    text-align: center;
                }
                #inbound-shortcodes-preview {
                    height: 607px;
                }
                .inbound-short-list {
                    padding: 40px;
                }
                .inbound-short-list li {
                    position: relative;
                    padding-left: 30px;
                    margin-bottom: 29px;
                    display: block;
                    font-size: 19px;
                    vertical-align: top;
                }
                .inbound-short-list span.new-sc-icons {
                    top:-1px;
                }

                .shortcode-popup-block {
                    max-height: 660px;
                    overflow: auto;
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                    background: #fff;
                    padding: 0px;
                    text-align: left;
                    max-width: 85%;
                    margin: 20px auto;
                    position: relative;
                }

                .mfp-bg {
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 90000;
                    overflow: hidden;
                    position: fixed;
                    background: #0b0b0b;
                    opacity: 0.8;
                }


                .mfp-wrap {
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 100001;
                    position: fixed;
                    outline: none !important;
                    -webkit-backface-visibility: hidden;
                }

                .mfp-container {
                    position: relative;
                    display: inline-block;
                    vertical-align: middle;
                    margin: 0 auto;
                    text-align: left;
                    z-index: 100002;
                }


                #popup-controls {
                    z-index: 999999;
                    width: 100%;
                    margin: auto;

                    position: fixed;
                }
            </style>
            <div id="inbound-marketing-popup" class="shortcode-popup-block mfp-hide">
                <ul class="inbound-short-list" style="display: block;">
                    <li class="launch-marketing-shortcode" data-launch-sc="quick-forms">
                        <span class="new-sc-icons mceIcon mce_editor-icon-quick-forms"></span>
                        <?php _e('Insert Existing Form' , 'inbound-pro' ); ?>
                    </li>
                    <li class="launch-marketing-shortcode" data-launch-sc="button">
                        <span class="new-sc-icons mceIcon mce_editor-icon-button"></span>
                        <?php _e('Build a Button' , 'inbound-pro' ); ?>
                    </li>
                    <li class="launch-marketing-shortcode" data-launch-sc="call-to-action">
                        <span class="new-sc-icons mceIcon mce_editor-icon-call-to-action"></span>
                        <?php _e('Call to Action Shortcodes' , 'inbound-pro' ); ?>
                    </li>
                    <li class="launch-marketing-shortcode" data-launch-sc="social-share">
                        <span class="new-sc-icons mceIcon mce_editor-icon-social-share"></span>
                        <?php _e('Social Share' , 'inbound-pro' ); ?>
                    </li>
                    <li class="launch-marketing-shortcode" data-launch-sc="lists">
                        <span class="new-sc-icons mceIcon mce_editor-icon-lists"></span>
                        <?php _e('Insert Icon List' , 'inbound-pro' ); ?>
                    </li>
                    <li class="launch-marketing-shortcode" data-launch-sc="columns">
                        <span class="new-sc-icons mceIcon mce_editor-icon-columns"></span>
                        <?php _e('Insert Columns' , 'inbound-pro' ); ?>
                    </li>
                </ul>
                <div id="iframe-target"></div>

            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    /* See marketing-button.js */


                    jQuery("body").on('click', '.marketing-back-button', function () {
                        // toggle display
                        jQuery("#iframe-target").html('');
                        jQuery('.select2-drop').remove();
                        jQuery('.inbound-short-list').show();

                    });


                });

            </script>
            <?php
        }
    }
}

new Inbound_Marketing_Button();