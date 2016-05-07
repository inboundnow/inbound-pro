<?php

/**
 *	Shows Preview of Inbound Email
 */
class Inbound_Email_Preview {

	/**
	 *	Initializes class
	 */
	function __construct() {
		self::load_hooks();
	}

	/**
	 *	Loads hooks and filters
	 */
	public function load_hooks() {
		add_action( 'inbound-mailer/email/footer' , array( __CLASS__ , 'load_scripts' ) , 11 );
		add_filter( 'single_template' , array( __CLASS__ , 'load_email' ) , 11 );
	}

	public static function load_scripts() {
		?>
		<script type="text/javascript">
		var Subscribe = (function () {
		var nav_background_color,
		nav_font_color,
		contents_background_color,
		contents_font_color;

		/**
		*  Initialize Script
		*/
		init = function () {
		setupVars();
		addListeners();
		createNav();

		},
		/**
		* Setup Variables
		*/
		setupVars = function() {

		},
		/**
		* Add Listeners
		*/
		addListeners = function() {

		},

		/**
		* Create Navigation Elements
		*/
		createNav = function () {
		var nav = jQuery("<nav></nav>")attr('id' , 'nav-subscribe').text = 'hello';
		var prompt = jQuery("<div></div>");
		var content = jQuery("<div></div>");

		nav.prepend(content);
		nav.prepend(prompt);

		jQuery('body').prepend(nav);

		},
		expandNav = function () {

		},
		collapseNav = function () {

		}
		});

		Subscribe.init();
		</script>
		<?php
	}

	/**
	 *	Detects request to view inbound-email post type and loads correct email template
	 */
	public static function load_email( $template ) {

		global $wp_query, $post, $query_string, $Inbound_Mailer_Variations;

		if ( $post->post_type != "inbound-email" ) {
			return $template;
		}

		/* Load email templates */
		Inbound_Mailer_Load_Templates();

		$vid = $Inbound_Mailer_Variations->get_current_variation_id();
		$template = $Inbound_Mailer_Variations->get_current_template( $post->ID , $vid );

		if (!isset($template)) {
			return;
		}


		if (file_exists(INBOUND_EMAIL_PATH.'templates/'.$template.'/index.php')) {
			return INBOUND_EMAIL_PATH . 'templates/' . $template . '/index.php';
		} else if (file_exists(INBOUND_EMAIL_UPLOADS_PATH .$template.'/index.php')) {
			return INBOUND_EMAIL_UPLOADS_PATH . $template . '/index.php';
		} else if (file_exists(INBOUND_EMAIL_THEME_TEMPLATES_PATH .$template.'/index.php')) {
			return INBOUND_EMAIL_THEME_TEMPLATES_PATH . $template . '/index.php';
		}



		return $single;
	}
}

$Inbound_Email_Preview = new Inbound_Email_Preview();