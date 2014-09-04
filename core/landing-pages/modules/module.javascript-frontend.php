<?php

add_action('wp_enqueue_scripts','lp_fontend_enqueue_scripts');

function lp_fontend_enqueue_scripts($hook) {
	global $post;

	if (!isset($post)) {
		return;
	}
	/* dequeue third party scripts */
	global $wp_scripts;
	if ( !empty( $wp_scripts->queue ) ) {
	
	    $store = $wp_scripts->queue; // store the scripts
	    
		foreach ( $wp_scripts->queue as $handle ) {
	          wp_dequeue_script( $handle );
	    }
		
	}
	
	/* Load jQuery */
	wp_enqueue_script('jquery');

	/* Load Page Tracking */
	$variation = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : '0';
	wp_enqueue_script( 'landing-page-view-track' , LANDINGPAGES_URLPATH . 'js/page_view_track.js', array( 'jquery','jquery-cookie'));
	wp_localize_script( 'landing-page-view-track' , 'landing_path_info', array( 'post_id' => $post->ID , 'post_type' => $post->post_type , 'variation' => $variation, 'admin_url' => admin_url( 'admin-ajax.php' )));

	
	if (isset($post)&&$post->post_type=='landing-page') {

	$form_prepopulation = get_option( 'lp-main-landing-page-prepopulate-forms' , 1);
	// load form pre-population script
	if ($form_prepopulation) {
		wp_register_script('form-population',LANDINGPAGES_URLPATH . 'js/jquery.form-population.js', array( 'jquery', 'jquery-cookie'	));
		wp_enqueue_script('form-population');
	}

	if (isset($_GET['template-customize']) &&$_GET['template-customize']=='on') {
		echo "<style type='text/css'>#variation-list{background:#eaeaea !important; top: 26px !important; height: 35px !important;padding-top: 10px !important;}#wpadminbar {height: 29px !important;}</style>"; // enqueue styles not firing
	}
	if (isset($_GET['live-preview-area'])) {
		show_admin_bar( false );
		wp_register_script('lp-customizer-load-js', LANDINGPAGES_URLPATH . 'js/customizer.load.js', array('jquery'));
		wp_enqueue_script('lp-customizer-load-js');
		// wp_enqueue_style('lp-customizer-load-css', LANDINGPAGES_URLPATH . 'css/customizer-load.css'); doesn't work
		/* Almost working look into this again TODO
			define("QUICK_CACHE_ALLOWED", false);
			define("DONOTCACHEPAGE", true);
			define('DONOTCACHCEOBJECT', true);
			define('DONOTCDN', true);

			// Function to wrap outputted meta in spans for front end editing
			add_filter( 'lp_get_value', 'lp_customizer_add_span_meta' , 10 , 4);
			function lp_customizer_add_span_meta( $content , $post = null , $key=null, $id=null)
			{
				$id = apply_filters('lp_customizer_span_id',$id);
				$exclude_list = "color|default|tile|repeat-x|repeat-y|left|right";
				// need to exclude these matches only if exact match with no other content
				// Need to exclude /images/img.jpg
				// Need to find single strings with only a url to a .png,.jpg, .gif file and exclude
				// Check for media upload type and ignore. Also ignore common setting words
				//echo $key.':'.$id.":".$content;
				//echo "<hr>";
				//echo "<br>";
				//<img alt="" src="/wp-content/uploads/landing-pages/templates/minimal-responsive/img/placeholder.jpg" /> matches the below preg match but we only want to match the string if its exactly /wp-content/uploads/landing-pages/templates/minimal-responsive/img/placeholder.jpg and nothing else
				if (!@preg_match('/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i', $content)&&!strstr($content,'/wp-content/') && !@preg_match('/^[a-f0-9]{1,}$/is', $content) && $content != "color") {
					$content = "<span id='$key-$id' class='live-preview-area-box'>" . $content . "</span>";
				}

				return $content;
			}

			add_filter( 'lp_main_headline', 'lp_customizer_add_span_title' ,99);
			function lp_customizer_add_span_title( $content, $id ='title' )
			{

				$id = apply_filters('lp_customizer_span_id' , $id );
				$content = "<span id='lp-main-headline' class='live-preview-area-box' >" . $content . "</span>";

				return $content;
			}

			// Function to wrap outputted meta in spans for front end editing
			add_filter( 'the_content', 'lp_customizer_add_span_content' );
			function lp_customizer_add_span_content( $content , $id = 'content' )
			{

				$id = apply_filters('lp_customizer_span_id', $id );
				$content = "<span id='the-content' class='live-preview-area-box' >" . $content . "</span>";

				return $content;
			}

			// Function to wrap outputted meta in spans for front end editing
			add_filter( 'lp_conversion_area', 'lp_customizer_add_span_conversion_area' );
			function lp_customizer_add_span_conversion_area( $content , $id = 'lp-conversion-area' )
			{
				//echo "here";exit;
				$id = apply_filters('lp_customizer_span_id', $id );
				$content = "<span id='lp-conversion-area' class='live-preview-area-box' >" . $content . "</span>";

				return $content;
			} */
		}
	}
	/* Requeue third party scripts */
	if(is_array($store)) {
		foreach ( $store as $handle ) {
		    wp_enqueue_script( $handle );
		}
	}

}

/* CLEAN URL OF VARIATION GET TAGS */
add_action('wp_head', 'lp_header_load');
function lp_header_load(){
	global $post;
	if (isset($post) && $post->post_type=='landing-page') {
		wp_enqueue_style('inbound-wordpress-base', LANDINGPAGES_URLPATH . 'css/frontend/global-landing-page-style.css');
		wp_enqueue_style('inbound-shortcodes', INBOUND_FORMS.'css/frontend-render.css');
		if (isset($_GET['lp-variation-id']) && !isset($_GET['template-customize']) && !isset($_GET['iframe_window']) && !isset($_GET['live-preview-area'])) {
		do_action('landing_page_header_script');
		?>
		<?php if(!defined('Inbound_Now_Disable_URL_CLEAN')) { ?>
		<script type="text/javascript">
		//var inbound_param_overide = 'off';
		// Automation pass params to GA. Look for documentation
		if (typeof window.history.pushState == 'function') {
				var current=window.location.href;var cleanparams=current.split("?");var clean_url=cleanparams[0];history.replaceState({},"landing page",clean_url);
		}</script>
		<?php } ?>
		<?php }
	}
}