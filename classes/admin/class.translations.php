<?php

/**
 * Class for detecting new translation versions, prompting translation package update, and retrieving new translation packages
 * Prompts user to download latest translations
 * @package     InboundPro
 * @subpackage  Translations
 */

class Inbound_Translation_Updater {
    static $old_version;
    static $enable;
    static $translations_path;
    static $response;

    /**
     * Inbound_Translation_Updater constructor.
     */
    public function __construct() {
        global $inbound_settings;

        self::$old_version = get_option('inbound_translation_version' , 0);
        self::$enable = (isset($inbound_settings['translations']['toggle-translations-updater'])) ? $inbound_settings['translations']['toggle-translations-updater'] : 'on';
        self::$translations_path = self::get_language_path();

        if ( version_compare(self::$old_version , INBOUND_PRO_TRANSLATIONS_VERSION) === -1 && self::$enable == 'on' )  {
            add_action('admin_notices', array( __CLASS__ , 'throw_notice' ) );
            add_filter( 'admin_footer' , array( __CLASS__ , 'add_js' ) );
            add_action( 'wp_ajax_inbound_update_translations', array( __CLASS__ , 'ajax_update_translations' ) );
        }

    }

    /**
     * Display notice to download translations
     */
    public static function throw_notice() {
        $bases = array('dashboard','toplevel_page_inbound-pro');
        $screen = get_current_screen();

        if (!in_array($screen->base, $bases)) {
           return;
        }
        ?>
        <div class="updated" id="inbound_translation_notification">
            <p><?php _e( sprintf( 'A new translation package is available for Inbound Pro. %s' , '<a href="#" id="inbound-install-translations">'.__( 'Please click to download (this may take a moment). ' , 'inbound-pro' ).' <i class="fa fa-download" aria-hidden="true"></i></a><span class="spinner" id="inbound-spinner"></span> ') , 'inbound-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Returns path to /wp-content/uploads/inbound-pro/asset/lang and creates it if it doesn't exist
     * @return string
     */
    public static function get_language_path() {
        if (!is_dir( INBOUND_PRO_UPLOADS_PATH . 'assets/images/' )) {
            wp_mkdir_p( INBOUND_PRO_UPLOADS_PATH . 'assets/lang' );
        }

        return INBOUND_PRO_UPLOADS_PATH . 'assets/lang/';
    }

    /**
     * Adds JS to handle download requests
     */
    public static function add_js() {
        ?>
        <script type="text/javascript">
            /* Load listeners after document loaded */
            var InboundTranslationsUpdater = (function () {

                var construct = {
                    /**
                     *  Initialize JS Class
                     */

                    /**
                     *  Initialize Script
                     */
                    init : function () {
                        InboundTranslationsUpdater.addListeners();
                    },

                    /**
                     *  adds listeners to fire the overlow
                     */
                    addListeners : function () {

                        /* on update */
                        jQuery('body').on('click', '#inbound-install-translations', function () {
                            jQuery('#inbound-install-translations').hide();
                            jQuery('#inbound-spinner').css('float','none');
                            jQuery('#inbound-spinner').css('margin-top','-6px');
                            jQuery('#inbound-spinner').css('visibility','initial');
                            InboundTranslationsUpdater.updateTranslations();
                            /* show spinner */
                            /* fire ajax */
                        });

                    },

                    updateTranslations : function () {
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: 'inbound_update_translations'
                            },
                            dataType: 'html',
                            timeout: 360000,
                            success: function (response) {
                                jQuery('#inbound_translation_notification').remove();
                            },
                            error: function (request, status, err) {
                                alert(err);
                            }
                        });
                    }
                }


                return construct;

            })();


            jQuery(document).ready(function () {
                InboundTranslationsUpdater.init();
            });
        </script>
        <?php
    }

    public static function ajax_update_translations() {
        self::get_translations_zip();
        self::install_transations();
    }

    public static function get_translations_zip() {
        self::$response =  wp_remote_get(
            'https://github.com/inboundnow/translations/raw/master/translations.zip' ,
            array(
                'timeout'     => 500,
                'redirection'     => 5,
                'decompress'  => false
            )
        );
    }

    /**
     * Install translations
     */
    public static function install_transations() {

        if (empty(self::$response['body'])) {
            echo json_encode(self::$response);
            exit;
        }

        /* load pclzip */
        include_once( ABSPATH . '/wp-admin/includes/class-pclzip.php');

        /* create temp file */
        $temp_file = tempnam('/tmp', 'TEMPPLUGIN' );

        /* write zip file to temp file */
        $handle = fopen($temp_file, "w");
        fwrite($handle, self::$response['body']);
        fclose($handle);


        /* extract temp file to plugins direction */
        $archive = new PclZip($temp_file);


        $result = $archive->extract( PCLZIP_OPT_PATH, self::$translations_path , PCLZIP_OPT_REPLACE_NEWER );


        if ($result == 0) {
            echo '{pclzip error}';
            exit;
        }

        /* delete templ file */
        unlink($temp_file);

        update_option('inbound_translation_version' , INBOUND_PRO_TRANSLATIONS_VERSION , true);
        echo 'success';exit;
    }

}

new Inbound_Translation_Updater;