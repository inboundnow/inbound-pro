<?php


class CTA_Customizer {

	/**
	*	Initiates class CTA_Customizer
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
		if (isset($_GET['wp_cta_iframe_window'])) 	{
			/* Enqueue Scripts  */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_preview_container_scripts' ));

			/* Loads Preview Iframe in wp_head */
			add_action('wp_head', array( __CLASS__ , 'load_preview_iframe' ) );
		}

		/* Load customizer launch */
		if (isset($_GET['cta-template-customize']) && $_GET['cta-template-customize']=='on') {
			add_filter('wp_head', array( __CLASS__ , 'launch_customizer' ) );
		}

		/* Load only on cta settings page when it customizer mode */
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

		/* Enqueue customizer CSS */
		wp_enqueue_style('wp_cta_ab_testing_customizer_css', WP_CTA_URLPATH . 'css/customizer-ab-testing.css');

	}

	public static function enqueue_preview_iframe_scripts() {
		show_admin_bar( false );
		wp_register_script('lp-customizer-load-js', WP_CTA_URLPATH . 'js/customizer.load.js', array('jquery'));
		wp_enqueue_script('lp-customizer-load-js');
	}


	public static function enqueue_settings_scripts() {
		//show_admin_bar( false ); // doesnt work
		$screen = get_current_screen();
		wp_enqueue_style('cta-customizer-admin', WP_CTA_URLPATH . 'css/new-customizer-admin.css');
		if ( ( isset($screen) && $screen->post_type != 'wp-call-to-action' ) ){
			return;
		}
		wp_enqueue_script('cta-frontend-editor-js', WP_CTA_URLPATH . 'js/customizer.save.js');
		wp_enqueue_script('cta-customizer-admin', WP_CTA_URLPATH . 'js/admin/new-customizer-admin.js');

	}

	/* Adds CTA Preview Iframe */
	public static function load_preview_iframe() {
		global $CTA_Variations;

		$wp_cta_variation = (isset($_GET['wp-cta-variation-id'])) ? $_GET['wp-cta-variation-id'] : '0';
		$cta_id = $_GET['post_id'];

		$variations = $CTA_Variations->get_variations( $cta_id );
		$post_type_is = get_post_type($cta_id);
		?>

		<link rel="stylesheet" href="<?php echo WP_CTA_URLPATH . 'css/customizer-ab-testing.css';?>" />
		<style type="text/css">

		#variation-list {
			position: absolute;
			top: 0px;
			left:0px;
			padding-left: 5px;
		}
		#variation-list h3 {
			text-decoration: none;
			border-bottom: none;
		}
		#variation-list div {
			display: inline-block;
		}
		#current_variation_id, #current-post-id {
			display: none !important;
		}


		<?php if ($post_type_is !== "wp-call-to-action") {
		echo "#variation-list {display:none !important;}";
		} ?>
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var current_page = jQuery("#current_variation_id").text();
				// reload the iframe preview page (for option toggles)
				jQuery('.variation-wp-cta').on('click', function (event) {
					varaition_is = jQuery(this).attr("id");
					var original_url = jQuery(parent.document).find("#TB_iframeContent").attr("src");
					var current_id = jQuery("#current-post-id").text();
					someURL = original_url;

					splitURL = someURL.split('?');
					someURL = splitURL[0];
					new_url = someURL + "?wp-cta-variation-id=" + varaition_is + "&wp_cta_iframe_window=on&post_id=" + current_id;
					//console.log(new_url);
					jQuery(parent.document).find("#TB_iframeContent").attr("src", new_url);
				});

			 });
			</script>
		<?php
		echo "<span id='current-post-id'>$cta_id</span>";

		echo '</div>';

	}


	public static function launch_customizer() {

		global $post;

		$page_id = $post->ID;
		$permalink = get_permalink( $page_id );

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
		$wp_cta_variation = (isset($_GET['wp-cta-variation-id'])) ? $_GET['wp-cta-variation-id'] : '0';

		$params = '?wp-cta-variation-id='.$wp_cta_variation.'&cache_bust='.$randomString.'&live-preview-area='.$randomString;

		$preview_link = add_query_arg( array(  'cache_bust' => $randomString , 'live-preview-area' => 'true' , 'wmode' => 'opaque') , get_permalink( $page_id ) );
		$preview_link = apply_filters( 'wp_cta_customizer_preview_link' , $preview_link );

		$admin_url = admin_url();
		$customizer_link = add_query_arg( array( 'wp-cta-variation-id' => $wp_cta_variation , 'action' => 'edit' , 'frontend' => 'true' ), admin_url() .'post.php?post='.$page_id );

		wp_enqueue_style('wp_cta_ab_testing_customizer_css', WP_CTA_URLPATH . 'css/customizer-ab-testing.css');
		?>

		<style type="text/css">
			#wpadminbar {
				z-index: 99999999999 !important;
			}
			#wp-cta-live-preview #wpadminbar {
				margin-top:0px;
			}
			.wp-cta-load-overlay {
				position: absolute;
				z-index: 9999999999 !important;
				z-index: 999999;
				background-color: #000;
				opacity: 0;
				background: -moz-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
				background: -webkit-gradient(radial,center center,0px,center center,100%,color-stop(0%,rgba(0,0,0,0.4)),color-stop(100%,rgba(0,0,0,0.9)));
				background: -webkit-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
				background: -o-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
				background: -ms-radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
				background: radial-gradient(center,ellipse cover,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#66000000',endColorstr='#e6000000',GradientType=1);
				-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
				filter: alpha(opacity=50);

			}

			body.customize-support, body {
				background-color: #eee !important;
			background-image: linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)), linear-gradient(45deg, rgb(213, 213, 213) 25%, transparent 25%, transparent 75%, rgb(213, 213, 213) 75%, rgb(213, 213, 213)) !important;
			background-size: 60px 60px !important;
			background-position: 0 0, 30px 30px !important;
			}


		</style>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery("#wp-admin-bar-edit a").text("Main Edit Screen");

			setTimeout(function() {
				jQuery(document).find("#wp-cta-live-preview").contents().find("#wpadminbar").hide()
				jQuery(document).find("#wp-cta-live-preview").contents().find("html").css("margin-bottom", "-28px");

			}, 2000);
		 });

		</script>

		<?php
		global $post;
		global $wp_query;

		$version = $_GET['wp-cta-variation-id'];

		$current_page_id = $wp_query->get_queried_object_id();

		$width = get_post_meta($current_page_id, 'wp_cta_width-'.$version, true);
		$height = get_post_meta($current_page_id, 'wp_cta_height-'.$version, true);
		//$replace = get_post_meta( 2112, 'wp_cta_global_bt_lists', true); // move to ext

		$correct_height = self::get_correct_dimensions($height, 'height');
		(!$correct_height) ? $correct_height = 'auto' : $correct_height = $correct_height;
		$correct_width = 'width:100%;';

		?>
		<?php
		echo '<div class="wp-cta-load-overlay" style="top: 0;bottom: 0; left: 0;right: 0;position: fixed;opacity: .8; display:none;"></div>';
		echo '<table style="width:100%">';
		echo '	<tr>';
		echo '		<td style="width:35%">';
		echo '			<iframe id="wp_cta_customizer_options" src="'.$customizer_link.'" style="width: 32%; height: 100%; position: fixed; left: 0px; z-index: 999999999; top: 26px;"></iframe>';
		echo '		</td>';

		echo '		<td>';
		echo '			<iframe id="wp-cta-live-preview" scrolling="no" src="'.$preview_link.'" style="max-width: 68%; '.$correct_width.' height:1000px; left: 32%; position: fixed;  top: 20%; z-index: 1; border: none; overflow:hidden;
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

$CTA_Customizer = new CTA_Customizer();