<?php

/**
 * Class provides visual editor support for email component
 * @package Mailer
 * @subpackage Management
 */
class Inbound_Mailer_Customizer {

	/**
	*	Initiates class Inbound_Mailer_Customizer
	*/
	public function	__construct() {

		self::load_hooks();

		/* If preview mode in effect then kill admin bar */
		if (isset($_GET['cache_bust'])) {
			show_admin_bar( false );
		}
	}

	/**
	*	Loads hooks and filters
	*/
	public static function load_hooks() {

		/* Load only on iframe container window */
		if (isset($_GET['inbound_email_iframe_window'])) 	{
			/* Enqueue Scripts  */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_preview_container_scripts' ));
		}

		/* Load customizer launch */
		if (isset($_GET['email-customizer']) && $_GET['email-customizer']=='on') {
			add_action('inbound_mail_header', array( __CLASS__ , 'launch_customizer' ) );
		}

		/* Load only on imbound-mail settings page when it customizer mode */
		if (isset($_GET['frontend']) && $_GET['frontend'] === 'true') {
			/* Enqueue Scripts  */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_settings_scripts' ));

		}

		if (isset($_GET['live-preview-area'])) {
			/* Enqueue Scripts  */
			add_action( 'wp_enqueue_scripts' , array( __CLASS__ , 'enqueue_preview_iframe_scripts' ));
		}

	}

	public static function enqueue_preview_container_scripts() {
		wp_enqueue_script('jquery');
		/* Enqueue customizer CSS */
		wp_enqueue_style('inbound_email_ab_testing_customizer_css', INBOUND_EMAIL_URLPATH . 'assets/css/customizer-ab-testing.css');

	}

	public static function enqueue_preview_iframe_scripts() {
		show_admin_bar( false );
		wp_register_script('lp-customizer-load-js', INBOUND_EMAIL_URLPATH . 'assets/js/customizer.load.js', array('jquery'));
		wp_enqueue_script('lp-customizer-load-js');
	}


	public static function enqueue_settings_scripts() {
		//show_admin_bar( false ); // doesnt work
		$screen = get_current_screen();
		wp_enqueue_style('inbound-email-customizer-admin-css', INBOUND_EMAIL_URLPATH . 'assets/css/new-customizer-admin.css');
		if ( ( isset($screen) && $screen->post_type != 'inbound-email' ) ){
			return;
		}

		wp_enqueue_script('inbound-email-customizer-admin-js', INBOUND_EMAIL_URLPATH . 'assets/js/customizer.admin.js');

	}


	public static function launch_customizer() {

		global $post;

		$page_id = $post->ID;
		$permalink = get_permalink( $page_id );

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
		$inbound_email_variation = (isset($_GET['inbvid'])) ? intval($_GET['inbvid']) : '0';

		$params = '?inbvid='.$inbound_email_variation.'&cache_bust='.$randomString.'&live-preview-area='.$randomString;

		$preview_link = add_query_arg( array(  'cache_bust' => $randomString , 'live-preview-area' => 'true' , 'wmode' => 'opaque') , get_permalink( $page_id ) );
		$preview_link = apply_filters( 'inbound_email_customizer_preview_link' , $preview_link );

		$admin_url = admin_url();
		$customizer_link = add_query_arg( array( 'inbvid' => $inbound_email_variation , 'action' => 'edit' , 'frontend' => 'true' ), admin_url() .'post.php?post='.$page_id );

		wp_enqueue_style('inbound_email_ab_testing_customizer_css', INBOUND_EMAIL_URLPATH . 'assets/css/customizer-ab-testing.css');
		?>
		<head>
		<style type="text/css">
			#wpadminbar {
				z-index: 99999999999 !important;
			}
			#inbound-mailer-live-preview #wpadminbar {
				margin-top:0px;
			}
			.inbound-mailer-load-overlay {
				position: absolute;
				z-index: 9999999999 !important;
				z-index: 999999;
				background-color: #000;
				opacity: 0;
			}

			body.customize-support, body {
				background-color: #eee !important;
				//background-image: linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)), linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)) !important;
				background-size: 60px 60px !important;
				background-position: 0 0, 30px 30px !important;
			}


		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			jQuery('#inbound_email_customizer_options').load(function(){
				jQuery('#inbound_email_customizer_options').contents().find(".action-save").on('click', function(event) {
					setTimeout( function() {
						document.getElementById('inbound-mailer-live-preview').src = document.getElementById('inbound-mailer-live-preview').src;
					} , 1500 );
				});
			});
		 });

		</script>
		</head>

		<?php
		global $post;
		global $wp_query;



		$current_page_id = $wp_query->get_queried_object_id();

		$width = get_post_meta($current_page_id, 'inbound_email_width-'.$inbound_email_variation, true);
		$height = get_post_meta($current_page_id, 'inbound_email_height-'.$inbound_email_variation, true);
		//$replace = get_post_meta( 2112, 'inbound_email_global_bt_lists', true); // move to ext

		$correct_height = self::get_correct_dimensions($height, 'height');
		(!$correct_height) ? $correct_height = 'auto' : $correct_height = $correct_height;
		$correct_width = 'width:100%;';

		?>
		<?php
		echo '<div class="mailer-load-overlay" style="top: 0;bottom: 0; left: 0;right: 0;position: fixed;opacity: .8; display:none;"></div>';
		echo '<table style="width:100%">';
		echo '	<tr>';
		echo '		<td style="width:35%">';
		echo '			<iframe id="inbound_email_customizer_options" src="'.$customizer_link.'" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>';
		echo '		</td>';

		echo '		<td>';
		echo '			<iframe id="mailer-live-preview" scrolling="yes" src="'.$preview_link.'" style="margin-top:25px;max-width: 68%; '.$correct_width.' height:1000px; left: 32%; position: fixed;  z-index: 1; border: none; overflow:hidden;
		//background-image: linear-gradient(45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194)), linear-gradient(-45deg, rgb(194, 194, 194) 25%, transparent 25%, transparent 75%, rgb(194, 194, 194) 75%, rgb(194, 194, 194));
		 background-position: initial initial; background-repeat: initial initial;"></iframe>';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';
		wp_footer();
		exit;
	}

	/**
	*  Looks at user inputed width and height and prepares correct format
	*/
	public static function get_correct_dimensions($input, $css_prop) {

		if (preg_match("/px/i", $input)){
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else if (preg_match("/%/", $input)) {
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else if (preg_match("/em/", $input)) {
		   $input = (isset($input)) ? " ".$css_prop.": $input;" : '';
		} else {
		   $input = " ".$css_prop.": $input" . "px;";
 		}

		return $input;
	}

}

$Inbound_Mailer_Customizer = new Inbound_Mailer_Customizer();