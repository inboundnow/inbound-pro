<?php

/**
 *    Shows Preview of Inbound Email
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
        add_action('inbound-mailer/email/header', array(__CLASS__, 'load_header_scripts'), 11);
        add_action('inbound-mailer/email/footer', array(__CLASS__, 'load_footer_scripts'), 11);
        add_filter('single_template', array(__CLASS__, 'load_email'), 11);
    }

    /**
     * loads jquery for web version of email
     */
    public static function load_header_scripts() {
        global $post;


        self::$smartbar_enable = get_field("smartbar_enable", $post->ID);

        if (!self::$smartbar_enable) {
            return;
        }

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

        if (!self::$smartbar_enable) {
            return;
        }

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
            return;
        }


        if (file_exists(INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php')) {
            return INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php';
        } else if (file_exists(INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php')) {
            return INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php';
        } else if (file_exists(INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php')) {
            return INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php';
        }


        return $single;
    }
}

$Inbound_Email_Preview = new Inbound_Email_Preview();

if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array (
        'key' => 'group_5733a7e9ec745',
        'title' => 'Email Smart Bar',
        'fields' => array (
            array (
                'key' => 'field_5733a7fb94506',
                'label' => 'Enable smartbar',
                'name' => 'smartbar_enable',
                'type' => 'true_false',
                'instructions' => 'Smartbar adds a customizable header bar to the public version of your email. ',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
            ),
            array (
                'key' => 'field_5733a87694507',
                'label' => 'Smartbar content',
                'name' => 'smartbar_content',
                'type' => 'wysiwyg',
                'instructions' => 'Insert a form shortcode connected to a lead list here.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array (
                'key' => 'field_5733a93d94508',
                'label' => 'Background color',
                'name' => 'smartbar_background_color',
                'type' => 'color_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
            ),
            array (
                'key' => 'field_5733ab4b30af1',
                'label' => 'Default font color',
                'name' => 'smartbar_font_color',
                'type' => 'color_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '#ffffff',
            ),
            array (
                'key' => 'field_5733a96d94509',
                'label' => 'Smartbar CSS',
                'name' => 'smartbar_css',
                'type' => 'wysiwyg',
                'instructions' => 'Build your smartbar CSS here.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 'body nav .subscribe-prompt{
		text-align:center;
		margin-left:auto;
		margin-right:auto;
		margin-top:10px;
		width:98%;
}

.inbound-form-wrapper {
		height:32px;
}

form {
	 margin:0px;
}

nav input {
		font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
		font-size: 9px !important;
		border: 2px solid #ABB0B2;
		color: #343434;
		background-color: #fff;
		padding: .7em 0em .7em 1em;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		display: inline-block;
		margin: 0;
		width:90%;
}

.inbound-field {
		display:inline;
}

nav button {
		cursor:pointer;
		font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
		font-size: 8px !important;
		letter-spacing: .03em;
		color: #fff;
		background-color: #2386C8;
		padding: .7em 2em;
		margin-left:5px;
		border: 2px solid #2386C8;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		display: inline-block;
}

/* hides labels - use placeholders */
.inbound-label {
	 display:none;
}

table:first-of-type {
	padding-top: 50px;
}',
                'tabs' => 'text',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
            array (
                'key' => 'field_5733aae69450a',
                'label' => 'Smartbar JS',
                'name' => 'smartbar_js',
                'type' => 'wysiwyg',
                'instructions' => 'Accepts jQuery. ',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '<script type=\'text/css\'>
</script>',
                'tabs' => 'text',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'inbound-email',
                ),
            ),
        ),
        'menu_order' => 888,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));

endif;