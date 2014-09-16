<?php
/*
*	Utilities functions used throughout the plugin
*/

/* GET POST ID FROM URL FOR LANDING PAGES */
function lp_url_to_postid($url)
{
	global $wpdb;

	if (strstr($url,'?landing-page='))
	{
		$url = explode('?landing-page=',$url);
		$url = $url[1];
		$url = explode('&',$url);
		$post_id = $url[0];

		return $post_id;
	}

	//first check if URL is homepage
	$wordpress_url = get_bloginfo('url');
	if (substr($wordpress_url, -1, -1)!='/')
	{
		$wordpress_url = $wordpress_url."/";
	}

	if (str_replace('/','',$url)==str_replace('/','',$wordpress_url))
	{
		return get_option('page_on_front');
	}

	$parsed = parse_url($url);
	$url = $parsed['path'];

	$parts = explode('/',$url);

	$count = count($parts);
	$count = $count -1;

	if (empty($parts[$count]))
	{
		$i = $count-1;
		$slug = $parts[$i];
	}
	else
	{
		$slug = $parts[$count];
	}

	$my_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$slug' AND post_type='landing-page'");

	if ($my_id)
	{
		return $my_id;
	}
	else
	{
		return 0;
	}
}

/* REMOTE CONNECT  - MAY NEED TO BE REPLACED WITH WP_REMOTE_GET */
if (!function_exists('lp_remote_connect'))
{
	function lp_remote_connect($url)
	{
		$method1 = ini_get('allow_url_fopen') ? "Enabled" : "Disabled";
		if ($method1 == 'Disabled')
		{
			//do curl
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$url");
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
			curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
			curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
			$string = curl_exec($ch);
		}
		else
		{
			$string = file_get_contents($url);
		}

		return $string;
	}
}


// Fix wp_title for known bad behavior themes */
add_action('wp','landingpage_fix_known_wp_title_isses' , 10);
function landingpage_fix_known_wp_title_isses() {

	if ('landing-page' != get_post_type()){
		return;
	}

	remove_filter( 'wp_title', 'genesis_doctitle_wrap' , 20 );
	remove_filter( 'wp_title', 'genesis_default_title' , 10 );
}
// Fix qtranslate issues
if (!function_exists('inbound_qtrans_disable')) {
	function inbound_qtrans_disable() {
		global $typenow, $pagenow;

		if (in_array($typenow, array('landing-page'||'wp-call-to-action')) && // post_types where qTranslate should be disabled
		    in_array($pagenow, array('post-new.php', 'post.php'))) {
		    remove_action('admin_head', 'qtrans_adminHeader');
		    remove_filter('admin_footer', 'qtrans_modifyExcerpt');
		    remove_filter('the_editor', 'qtrans_modifyRichEditor');
		}
	}
}
add_action('current_screen', 'inbound_qtrans_disable');

function lp_fix_seo_title()
{
	if ('landing-page' == get_post_type())
	{
		global $post;
	if (get_post_meta($post->ID, '_yoast_wpseo_title', true)) {
		$seotitle = get_post_meta($post->ID, '_yoast_wpseo_title', true) . " ";
	// All in one seo get_post_meta($post->ID, '_aioseop_title', true) for future use
	} else {
		$seotitle = $seotitle = get_post_meta($post->ID, 'lp-main-headline', true) . " "; }
	}
	return $seotitle;
}

// Add Custom Class to Landing Page Nav Menu to hide/remove
// remove_filter( 'wp_nav_menu_args', 'lp_wp_nav_menu_args' ); // Removes navigation hide
add_filter( 'wp_nav_menu_args', 'lp_wp_nav_menu_args' );
function lp_wp_nav_menu_args( $args = '' ) {
	global $post;
	
	if (!isset($post)) {
		return $args;
	}
	
	$variations = get_post_meta($post->ID, 'lp-ab-variations', true);
	$var = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : '';
	if ($var === "0"){
		$template_name = 'lp-selected-template';
	} else {
		$template_name = 'lp-selected-template-'.$var;
	}
	$template_name = get_post_meta($post->ID, $template_name, true);

	if ( 'landing-page' == get_post_type() ) {
		$nav_status = get_post_meta($post->ID, 'default-lp_hide_nav', true);

		if ($nav_status === 'off' && $template_name === 'default' || empty($nav_status) && $template_name === 'default') {
			if (isset($args['container_class'])) {
				$current_class = " ".$args['container_class'];
			}

			$args['container_class'] = "custom_landing_page_nav{$current_class}";

			$args['echo'] = false; // works!
		}
	}


	return $args;
}

///////// Remove all base css from the current active wordpress theme in landing pages
//currently removes all css from wp_head and re-enqueues the admin bar css.
add_action('wp_print_styles', 'lp_remove_all_styles', 100);
function lp_remove_all_styles() {
	if (!is_admin()) {
		if ( 'landing-page' == get_post_type() ) {
			global $post;
			$template = get_post_meta($post->ID, 'lp-selected-template', true);

			if (strstr($template,'-slash-')) {
				$template = str_replace('-slash-','/',$template);
			}

			$my_theme =  wp_get_theme($template);

			if ($my_theme->exists()||$template=='default') {
				return;
			} else {
				global $wp_styles;
				//print_r($wp_styles);
				$registered_scripts = $wp_styles->registered;
				$inbound_white_list = array();
				foreach ($registered_scripts as $handle) {
				    if(preg_match("/\/plugins\/leads\//", $handle->src)) {
				      //echo $handle->handle;
				      $inbound_white_list[] = $handle->handle;
				    }
				    if(preg_match("/\/plugins\/cta\//", $handle->src)) {
				      //echo $handle->handle;
				      $inbound_white_list[]= $handle->handle;
				    }
				    if(preg_match("/\/plugins\/landing-pages\//", $handle->src)) {
				      //echo $handle->handle;
				      $inbound_white_list[]= $handle->handle;
				    }
				}
				//print_r($inbound_white_list);
				$wp_styles->queue = $inbound_white_list;
				//$wp_styles->queue = array(''); // removes all styles
				//wp_register_style( 'admin-bar' );
				wp_enqueue_style( 'admin-bar' );
			}
		}
	}

}
// Remove all body_classes from custom landing page templates - disabled but you can use the function above to model native v non-native template conditionals.
//add_action('wp','landingpage_remove_plugin_filters');

function landingpage_remove_plugin_filters() {

    global $wp_filter;
    global $wp;

    if ($wp->query_vars["post_type"] == 'landing-page') {
       add_filter('body_class','landing_body_class_names');
    }
}

function landing_body_class_names($classes) {
	global $post;

	if('landing-page' == get_post_type() )
	{
		$arr = array();
		$template_id = get_post_meta($post->ID, 'lp-selected-template', true);
		$arr[] = 'template-' . $template_id;
	}

    return $arr;
}

add_action('admin_head', 'inbound_build_template_options');
function inbound_build_template_options() {
	global $lp_data;
	global $post;

	if (isset($post)&&$post->post_type=='landing-page' && (isset($_GET['lp-config']))) {
	//$lp_data = lp_get_extension_data(); // Not Working
	$key = get_post_meta( $post->ID, $key = 'lp-selected-template', true );

	$options_array = $lp_data[$key]['settings'];

	    foreach ($options_array as $key => $value) {
	      $name = str_replace(array('-'),'_', $value['id']);
	      echo "$" . $name  . " = " .  'lp_get_value(' . '$'. 'post, ' . '$'. 'key, '. " '" . $value['id'] . "' " . ');' . "\n";
	      echo "<br>";
	    }
	    echo "<pre>";
	    foreach ($options_array as $key => $value) {
	      $name = str_replace(array('-'),'_', $value['id']);
	      if($value['type'] === 'colorpicker') {
	    // echo "$" . $name  . " = " .  'wp_cta_get_value(' . '$'. 'post, ' . '$'. 'key, '. " '" . $value['id'] . "' " . ');' . "\n";
	     //echo "<br>";
	      echo "\n";
	      echo "if (" . " $" . "$name " . "!= \"\" ) {";
	      echo "\n";
	      echo "echo \".css_element { color: #$" . "".$name."" . ";}\"; \n"; // change sidebar color
	      echo "}";
	      echo "\n";
	      }
	    }
	    echo "</pre>";
	    /**/
	}
}

if (is_admin())
{
	add_filter( 'wpseo_metabox_prio', 'lp_wpseo_priority');
	function lp_wpseo_priority(){return 'low';}
}
