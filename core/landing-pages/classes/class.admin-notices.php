<?php

class Landing_Pages_Admin_Notices {

    public function __construct() {
        self::add_hooks();
    }


    public static function add_hooks() {
        add_action('admin_notices', array( __CLASS__ , 'dont_install_landing_page_templates_here') );
        add_action('admin_notices', array( __CLASS__  , 'get_more_templates_notice' ) );
        add_action('admin_notices', array( __CLASS__ , 'permalink_structure_notice' ) );
    }

    /**
     * Persistant message to not install landing page themes at templates.
     */
    public static function dont_install_landing_page_templates_here() {

        $screen = get_current_screen();

        if( $screen->id === 'themes' ||
            $screen->id === 'theme-install' ||
            $screen->id === 'update' && isset($_GET['action']) && $_GET['action'] === "upload-theme"
          ) {

            $doc = 'http://docs.inboundnow.com/guide/installing-new-templates/';
            $link = admin_url( 'edit.php?post_type=landing-page&page=lp_templates_upload' );

        ?>
        <div class="error">
            <h3 style='font-weight:normal;'>
                <strong><?php _e( 'Attention Landing Page Users:' , 'landing-pages' ); ?></strong>
            </h3>
            <p><?php _e( sprintf( 'If you are trying to install a <strong>landing page template</strong> from Inbound Now, %s Please Follow these instructions%s' , '<a href=\'http://docs.inboundnow.com/guide/installing-new-templates/\' target=\'_blank\'>' , '</a>' ) , 'landing-pages' ); ?>
            </p>

            <p><?php echo "<p>Landing page templates need to be installed <a href='".$link."'>here</a> in the <strong><a href='".$link."'>Landing pages</a> > <a href='".$link."'>Manage templates area</a></strong>"; ?>
            </p>
        </div>
        <?php
        }
    }


    /**
     * Call to action to download more templates
     */
    public static function get_more_templates_notice() {
        global $pagenow;
        $page_string = isset($_GET["page"]) ? $_GET["page"] : "null";
        if ((($pagenow == 'edit.php') && ($page_string == "lp_manage_templates")) || (($pagenow == "post-new.php") && (isset($_GET['post_type']) && $_GET['post_type'] == "landing-page"))) {
            ?>
            <div id="more-templates-button" style="display:none;">
                <a target="_blank" href="/wp-admin/edit.php?post_type=landing-page&page=lp_store&inbound-store=templates" class="button new-lp-button button-primary button-large"><?php _e( 'Download Additional Landing Page Templates' , 'landing-pages' ); ?></a>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var moretemp = jQuery("#more-templates-button");
                    jQuery("#bulk_actions").prepend(moretemp);
                    jQuery(".lp-selection-heading h1").append(moretemp);
                    jQuery(".lp-selection-heading #more-templates").css("float","right");
                    jQuery(moretemp).show();
                });
            </script>
            <?php
        }
    }


    /**
     * Notice to tell people that a permalink structure besides default must be selected to enable split testing
     */
    public static function permalink_structure_notice(){
        global $pagenow;

        if ( !get_option('permalink_structure') ) {
            ?>
            <div class="error">
                <p>
                    <?php _e( 'We\'ve noticed that your permalink settings are set to the default setting. Landing Page varation roation is not possible on this setting. To enable roation please go into Settings->Permalinks and update them to a different format.' , 'landing-pages' ); ?>
                </p>
            </div>
            <?php
        }
    }


}

new Landing_Pages_Admin_Notices;