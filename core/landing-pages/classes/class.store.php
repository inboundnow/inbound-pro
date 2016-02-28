<?php
/**
 * Inbound Now Store
 */
if ( ! class_exists( 'Inbound_Now_Store' ) ) {

    class Inbound_Now_Store {

        static function init() {
            if ( !class_exists('Inbound_Pro_Plugin')  ) {
                self::load_hooks();
            }
        }

        /**
         * Loads hooks and filters
         */
        public static function load_hooks() {
            add_action( 'wp_ajax_show_store_ajax' , array( __CLASS__ , 'show_store_ajax' ) );
            add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
            add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'print_scripts' ) );
        }

        /**
         * enqueues scripts and styles
         */
        public static function enqueue_scripts() {
            global $plugin_page;

            wp_enqueue_script('jquery');

        }

        public static function print_scripts() {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    jQuery('#menu-posts-landing-page a[href*="lp_store"]').each(function() {
                        jQuery(this).attr('target','_blank');
                        jQuery(this).attr('href','http://www.inboundnow.com/upgrade');
                    });
                });
            </script>
            <?php
        }

        public static function show_store_ajax() {
            if(empty($_POST) || !isset($_POST)) {
                return;
            } else {
                /* show store forever */
                $user_id = get_current_user_id();
                add_user_meta($user_id, 'inbound_show_store', true);
                die();
            }

        }

        /**
         *
         */
        public static function store_display(){
            global $current_user;

            $user_id = $current_user->ID;

            self::dom_output();

            if ( !get_user_meta($user_id, 'inbound_show_store') ) {
                self::inbound_store_notice();
            } else {
                /* normal display here */
                self::store_redirect();
            }

        }

        /* loads when user_meta opt in is NOT found */
        public static function inbound_store_notice(){

            echo '<div id="agreement" style="margin-top:30px;">
				<h1>WordPress Guidelines Compliance Agreement</h1>
				<h3>To ensure complaince with <a href="https://wordpress.org/plugins/about/guidelines/">WordPress.orgs Plugin Guidelines</a>, we need your express permission to load our <a target="_blank" href="http://www.inboundnow.com/market">marketplace</a>.

				<div class="details">
				    <br>
				    <br>
					<a href="#" id="accept-agreement" class="button button-primary">I accept this agreement, show me the goods!</a>
				</div>

				</div>'; ?>
            <script>
                jQuery(document).ready(function($) {

                    jQuery("#accept-agreement").on('click', function (e) {
                        e.preventDefault();

                        $('#agreement').slideToggle();

                        showInboundStore();

                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxadmin.ajaxurl,
                            data: {
                                action: 'show_store_ajax'
                            },
                            success: function(user_id){
                                console.log('user meta updated');
                            },
                            error: function(MLHttpRequest, textStatus, errorThrown){

                            }

                        });

                    });
                });
            </script>

        <?php }

        /**
         *
         */
        public static function store_redirect() { ?>
            <script>

                window.location = "http://www.inboundnow.com/market";

            </script>
        <?php
        }

        /* Always loads on store pages */
        public static function dom_output(){

            if (isset($_GET['inbound-store']) && $_GET['inbound-store'] === 'templates') {
                $url = 'http://www.inboundnow.com/products/landing-pages/templates/';
            } else if (isset($_GET['inbound-store']) && $_GET['inbound-store'] === 'addons') {
                $url = 'http://www.inboundnow.com/products/landing-pages/extensions/';
            } else {
                $url = LANDINGPAGES_STORE_URL;
            }
            ?>
            <style type="text/css">
                #setting-error-tgmpa, .updated, #wpfooter { display: none !important; }
                #wpwrap { background: #fff !important; }
                div#inbound-store-container { margin-top: 0px !important; }
                div#inbound-store-container iframe { width:100%; }
                #wpbody-content { padding-bottom: 0px !important; }
            </style>
            <script type='text/javascript'>
                function showInboundStore(){
                    new easyXDM.Socket({
                        remote: "<?php echo $url;?>",
                        container: document.getElementById("inbound-store-container"),
                        onMessage: function(message, origin){
                            var height = Number(message) + 1000;
                            this.container.getElementsByTagName("iframe")[0].scrolling="no";
                            this.container.getElementsByTagName("iframe")[0].style.height = height + "px";

                        },
                        onReady: function() {
                            socket.postMessage("Yay, it works!");
                            /*alert('run'); */
                        }
                    });

                    setTimeout(function() {
                        jQuery("#inbound-store-container iframe").css('height', window.outerHeight + "px");
                    }, 2000);
                }
            </script>

            <div id="inbound-store-container"></div>
        <?php }

    }

    Inbound_Now_Store::init();

}