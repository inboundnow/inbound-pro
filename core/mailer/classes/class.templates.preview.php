<?php

/**
 * Class Inbound_Email_Preview powers the 'web version' / frontend email render feature
 *
 * @package Mailer
 * @subpackage	Templates
 */

class Inbound_Email_Preview {
    static $smartbar_enable;
    static $smartbar_content;
    static $smartbar_background_color;
    static $smartbar_font_color;
    static $smartbar_padding;
    static $smartbar_js;
    static $smartbar_css;

    /**
     *    Initializes class
     */
    function __construct() {
        self::load_hooks();
    }

    /**
     *    Loads hooks and filters
     */
    public function load_hooks() {
        add_action('mailer/email/header', array(__CLASS__, 'load_header_scripts'), 11);
        add_action('mailer/email/footer', array(__CLASS__, 'load_footer_scripts'), 11);
        add_filter('single_template', array(__CLASS__, 'load_email'), 11);
    }

    /**
     * loads jquery for web version of email
     */
    public static function load_header_scripts() {
        global $post;


        self::$smartbar_enable = get_field("smartbar_enable", $post->ID);



        self::$smartbar_content = str_replace( array("\n" , "\t"  ) , "" , get_field("smartbar_content", $post->ID ) );
        self::$smartbar_content = stripslashes( self::$smartbar_content );
        self::$smartbar_content = str_replace( "'" , '"' , self::$smartbar_content );
        self::$smartbar_content = addslashes( self::$smartbar_content );
        self::$smartbar_background_color = get_field("smartbar_background_color", $post->ID );
        self::$smartbar_font_color = get_field("smartbar_font_color", $post->ID );
        self::$smartbar_padding = get_field("smartbar_padding", $post->ID );
        self::$smartbar_css = str_replace( array("\n" , "\t"  ) , "" , strip_tags(get_field("smartbar_css", $post->ID )));
        self::$smartbar_js = get_field("smartbar_js", $post->ID );

        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>

        <?php  if (!self::$smartbar_enable) {
            return;
        }
        ?>
        <style type="text/css">
            nav {
                transition: all .1s ease-in-out .1s;
                color: #fff;
                padding: 0;
                width: 100%;
                position: fixed;
                display:inline-flex;
            }

            <?php
            if (self::$smartbar_background_color) : ?>
                nav {
                    background-color: self::$smartbar_background_color;
                }
            <?php endif ?>


            body {
                padding:0px;
                margin:0px;
            }

        </style>
        <?php
    }

    /**
     * loads subscribe call to action for web version of email
     */
    public static function load_footer_scripts() {

        do_action('wp_enqueue_scripts');

        global $wp_scripts;

        /* load inbound analytics */
        if (isset($wp_scripts->registered['inbound-analytics'])) {

            if (isset($wp_scripts->registered['inbound-analytics']->extra['data'])) {
                echo '<script type="text/javascript">';
                echo $wp_scripts->registered['inbound-analytics']->extra['data'];
                echo '</script>';
            }
            echo '<script src="'.$wp_scripts->registered['inbound-analytics']->src.'"></script>';
        }

        if (!self::$smartbar_enable) {
            return;
        }

        ?>
        <script type="text/javascript">
            var Subscribe = (function () {
                var smartbar_content,
                    smartbar_css,
                    smartbar_font_color,
                    smartbar_background_color,
                    smartbar_padding;

                var methods = {

                    /**
                     *  Initialize Script
                     */
                    init: function () {
                        Subscribe.setupVars();
                        Subscribe.addListeners();
                        Subscribe.createNav();
                    },
                    /**
                     * Setup Variables
                     */
                    setupVars: function () {
                        Subscribe.smartbar_content = JSON.parse('<?php echo json_encode(array('html'=>self::$smartbar_content) ); ?>');
                        Subscribe.smartbar_css = JSON.parse('<?php echo json_encode(array('text'=>self::$smartbar_css) ); ?>');
                        Subscribe.smartbar_font_color = <?php echo json_encode(self::$smartbar_font_color ); ?>;
                        Subscribe.smartbar_background_color = <?php echo json_encode(self::$smartbar_background_color ); ?>;

                    },
                    /**
                     * Add Listeners
                     */
                    addListeners: function () {

                    },

                    /**
                     * Create Navigation Elements
                     */
                    createNav: function () {
                        var nav = jQuery("<nav></nav>").attr('class', 'subscribe-container');
                        var prompt = jQuery("<div></div>").attr('class', 'subscribe-prompt').html(Subscribe.smartbar_content.html);
                        var content = jQuery("<div></div>").attr('class', 'subscribe-content');
                        var css = jQuery("<style></style>").text(Subscribe.smartbar_css.text);
                        var browser_link = jQuery('.view-in-browser').hide();

                        nav.prepend(prompt);
                        nav.prepend(content);
                        nav.prepend(content);

                        jQuery('body').prepend(css);
                        jQuery('body').prepend(nav);

                        Subscribe.stickNav();

                    },
                    stickNav: function () {
                        var lastScrollTop = 0,
                            header = jQuery('nav'),
                            headerHeight = header.height();

                        header.css( 'margin-bottom'  ,headerHeight+'px' );

                        jQuery(window).scroll(function () {
                            var scrollTop = jQuery(window).scrollTop()
                            jQuery('.scrollTop').html(scrollTop);

                            if (scrollTop > lastScrollTop) {
                                header.css('top','-'+headerHeight+'px')
                                //header.animate({top:'-'+headerHeight+'px'}, 200)
                            } else {
                                header.css('top','0px')
                                //header.animate({top:'0px'}, 200)
                            }

                            lastScrollTop = scrollTop;

                        });
                    },
                    expandNav: function () {

                    },
                    collapseNav: function () {

                    }
                }

                return methods;
            })();
            Subscribe.init();
        </script>
        <?php
    }

    /**
     *    Detects request to view inbound-email post type and loads correct email template
     */
    public static function load_email($template) {

        global $wp_query, $post, $query_string, $Inbound_Mailer_Variations;

        if ($post->post_type != "inbound-email") {
            return $template;
        }

        /* Load email templates */
        Inbound_Mailer_Load_Templates();

        $vid = $Inbound_Mailer_Variations->get_current_variation_id();
        $template = $Inbound_Mailer_Variations->get_current_template($post->ID, $vid);

        if (!isset($template)) {
            error_log('Template not selected for variation');
            return $template;
        }


        if (file_exists(INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php')) {
            return INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php';
        } else if (file_exists(INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php')) {
            return INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php';
        } else if (file_exists(INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php')) {
            return INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php';
        }


        return $template;
    }
}

$Inbound_Email_Preview = new Inbound_Email_Preview();
