<?php
/* Template Functions */
if (!function_exists('inbound_Hex_2_RGB')) {
	function inbound_delete_all_between($beginning, $end, $string) {
	  $beginningPos = strpos($string, $beginning);
	  $endPos = strpos($string, $end);
	  if (!$beginningPos || !$endPos) {
	    return $string;
	  }

	  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

	  return str_replace($textToDelete, '', $string);
	}
}
	if (!function_exists('cta_example_template_function')) {
		function cta_example_template_function() {
		  return 'Return value from cta_example_template_function()';
		}
	}
	/* Will move DO NOT DELTE */
	if (!function_exists('inbound_run_processing')) {
	function inbound_run_processing($token_match, $key, $value, $template) {

		if (preg_match('/\|/', $token_match)) {
		   //echo "False match:" . $key . " <br>";
		   $show_debug_token = false;
		   $raw_php_function = false; // Adds ability to run raw php
		   if ($show_debug_token) {
		   	echo "<br><span style='color:red'>Token MATCH ON:</span> " . $token_match . " Val: ". $value . "<br>";
		   }

		   $clean_key = str_replace(array("{", "}"), "", $token_match);

		   $separate_token = explode('|', $clean_key); // split at pipe
		   $correct_key = $separate_token[0];

		   /* Run Special Parse Functions Here */
		   $run_function = $separate_token[1];
		   $function_name = explode("(", $run_function);

		   	preg_match('#\((.*?)\)#', $run_function, $fun_match);
		   	if (is_array($fun_match)){

		   		$function_args = (isset($fun_match[1])) ? $fun_match[1] : '';
		   		$function_args_array = explode(',', $function_args);
		   		$args = $function_args_array;
		   		if(empty($args[0])) {
		   			if ($show_debug_token) {
		   			echo "NO params set default value<br>";
		   			}
		   			$args[0] = $value;
		   		}
		   	}

		   	if(preg_match("/php:/", $run_function)) {
		   		if ($show_debug_token) {
		   		echo "PHP function";
		   		echo $function_name[0];
		   		}
		   		$php_function = str_replace("php:", '', $function_name[0]);
		   		$raw_php_function = true; // Adds ability to run raw php
		   	}

		   $function_args = array();
		   $function_args[0] = $value;
		   foreach ($args as $arr_key => $arr_value) {

		   		if ($arr_value === "this"){
		   			$function_args[$arr_key + 1] = $value;
		   			if ($show_debug_token) {
		   			//echo "arg" . $arr_key. ":" . $arr_value;
		   			}
		   			 // first value always user input val
		   		} else {
		   		  $function_args[$arr_key + 1] = $arr_value;
		   		}

		   		if ($show_debug_token) {
		   		 echo "arg" . $arr_key. ":" . $arr_value . ", ";
		   		}

		   }

		   $function_args = array_unique($function_args); // dedupe values

		   if (count($function_args) < 2 ) {
			$function_args = $function_args[0]; // send single value to function
		   }
		   //echo $run_function;
		   /* Function temp references
		    replace: {{ "I like %this% and %that%."|replace({'%this%': foo, '%that%': "bar"}) }}
			*/
			if ($raw_php_function) {
				$template_function = $php_function;
			} else {
				$template_function = 'inbound_template_' . $function_name[0];
			}

		   /* If function exists run it */
		   if (function_exists($template_function)) {
		   		$value = $template_function($function_args);
		   		if ($show_debug_token) {
		   		echo "<br>Running Function: <strong>" .  $template_function . "</strong> with args <strong>";
		   		print_r($function_args);
		   		echo "</strong><br>";

		   		$look_for = "{{" .$key . "}}";
		   		$reg = preg_quote( "{{" .$key . "}}");
		   		echo "Replace " . $token_match . " with ". $value . "<br>";

		   		}
		 	}
		 	$template = str_ireplace( $token_match , $value , $template); // single space
		}
		return $template;
	}
}
/* Will move soon. Keep here now */
if (!function_exists('inbound_template_color')) {
	function inbound_template_color($args){
		$prefix = "#";
		if (is_array($args)){
			$color = $args[0];
		} else {
			$color = $args;
		}
		if(preg_match("/rbg/", $color)) {
			$prefix = "";
		}

		// http://stackoverflow.com/questions/5098583/how-do-i-make-a-lighter-version-of-a-colour-using-php
		return $prefix . $color;
	}
}
if (!function_exists('inbound_template_brightness')) {
	function inbound_template_brightness($args){

			$hex_color = $args[0];
			$hue = intval($args[1]);

			$format = 'hex';
			if (strpos($hex_color,'#') !== false) {
			    $input = $hex_color;
			} else {
				$input = "#" . $hex_color;
			}

			$col = Array(
			    hexdec(substr($input,1,2)),
			    hexdec(substr($input,3,2)),
			    hexdec(substr($input,5,2))
			);

			$color_scheme_array =
			array(
					100 => array( $col[0]/4, $col[1]/4, $col[2]/4),
					95 => array( $col[0]/3, $col[1]/3, $col[2]/3),
					90 => array( $col[0]/2.7, $col[1]/2.7, $col[2]/2.7),
					85 => array( $col[0]/2.5, $col[1]/2.5, $col[2]/2.5),
					80 => array( $col[0]/2.2, $col[1]/2.2, $col[2]/2.2),
					75 => array( $col[0]/2, $col[1]/2, $col[2]/2),
					70 => array( $col[0]/1.7, $col[1]/1.7, $col[2]/1.7),
					65 => array( $col[0]/1.5, $col[1]/1.5, $col[2]/1.5),
					60 => array( $col[0]/1.3,$col[1]/1.3,$col[2]/1.3),
					55 => array( $col[0]/1.1,$col[1]/1.1,$col[2]/1.1),
					50 => array( $col[0],$col[1],$col[2]),
					45 => array( 255-(255-$col[0])/1.1, 255-(255-$col[1])/1.1, 255-(255-$col[2])/1.1),
					40 => array( 255-(255-$col[0])/1.3, 255-(255-$col[1])/1.3, 255-(255-$col[2])/1.3),
					35 => array( 255-(255-$col[0])/1.5, 255-(255-$col[1])/1.5, 255-(255-$col[2])/1.5),
					30 => array( 255-(255-$col[0])/1.7, 255-(255-$col[1])/1.7, 255-(255-$col[2])/1.7),
					25 => array( 255-(255-$col[0])/2, 255-(255-$col[1])/2, 255-(255-$col[2])/2),
					20 => array( 255-(255-$col[0])/2.2, 255-(255-$col[1])/2.2, 255-(255-$col[2])/2.2),
					15 => array( 255-(255-$col[0])/3, 255-(255-$col[1])/2.7, 255-(255-$col[2])/3),
					10 => array(255-(255-$col[0])/5, 255-(255-$col[1])/5, 255-(255-$col[2])/5),
					5 => array(255-(255-$col[0])/10, 255-(255-$col[1])/10, 255-(255-$col[2])/10),
					0 => array(255-(255-$col[0])/15, 255-(255-$col[1])/15, 255-(255-$col[2])/15)
					);

			($format === 'hex') ? $sign = "#" : $sign = '';
			$return_scheme = array();
			foreach ($color_scheme_array as $key => $val) {

				$each_color_return =	$sign.sprintf("%02X%02X%02X", $val[0], $val[1], $val[2]);
			    $return_scheme[$key] = $each_color_return;

			}
				//return $closest;
				if(isset($_GET['color_scheme'])) {
					foreach ($return_scheme as $key => $hex_value) {
						echo "<div style='background:$hex_value; display:block; width:100%;'>$key</div>";
					}
				}

				$new_color = $return_scheme[$hue];
				if (strpos($new_color,'#') !== false) {
				    $return_color = $new_color;
				} else {
					$return_color = "#" . $new_color;
				}

				return $return_color;

	}
}



/**
 * SETUP DEBUG TOOLS
 */

add_action( 'init', 'inbound_meta_debug' );
if (!function_exists('inbound_meta_debug')) {
	function inbound_meta_debug(){
	//print all global fields for post
	if (isset($_GET['debug'])) {
			global $wpdb;
			$data   =   array();
			$wpdb->query("
			  SELECT `meta_key`, `meta_value`
				FROM $wpdb->postmeta
				WHERE `post_id` = ".$_GET['post']."
			");
			foreach($wpdb->last_result as $k => $v){
				$data[$v->meta_key] =   $v->meta_value;
			};
			if (isset($_GET['post']))
			{
				echo "<pre>";
				print_r( $data);
				echo "</pre>";
			}
		}
	}
}


add_action( 'wp_head', 'wp_cta_kill_ie8' );
function wp_cta_kill_ie8() {
    global $is_IE;
    if ( $is_IE ) {
        echo '<!--[if lt IE 9]>';
        echo '<link rel="stylesheet" type="text/css" href="'.WP_CTA_URLPATH.'/css/ie8-and-down.css" />';
        echo '<![endif]-->';
    }
}


// Fix SEO Title Tags to not use the_title
//add_action('wp','wpcta_seo_title_filters');
function wpcta_seo_title_filters() {

    global $wp_filter;
    global $wp;
	print_r($wp);exit;
    if (strstr())
	{
       add_filter('wp_title', 'wp_cta_fix_seo_title', 100);
    }
}

function wp_cta_fix_seo_title()
{
	if ('wp-call-to-action' == get_post_type())
	{
		global $post;
	if (get_post_meta($post->ID, '_yoast_wpseo_title', true)) {
		$seotitle = get_post_meta($post->ID, '_yoast_wpseo_title', true) . " ";
	// All in one seo get_post_meta($post->ID, '_aioseop_title', true) for future use
	} else {
		$seotitle = $seotitle = get_post_meta($post->ID, 'wp-cta-main-headline', true) . " "; }
	}
	return $seotitle;
}

// Add Custom Class to Landing Page Nav Menu to hide/remove
add_filter( 'wp_nav_menu_args', 'wp_cta_wp_nav_menu_args' );
function wp_cta_wp_nav_menu_args( $args = '' )
{
	global $post;
	if ( 'wp-call-to-action' == get_post_type() ) {
		$nav_status = get_post_meta($post->ID, 'default-wp_cta_hide_nav', true);
		if ($nav_status === 'off' || empty($nav_status)) {
			if (isset($args['container_class']))
			{
				$current_class = " ".$args['container_class'];
			}

			$args['container_class'] = "custom_wp_call_to_action_nav{$current_class}";

			$args['echo'] = false; // works!
		}
	}


	return $args;
}

// Remove Base Theme Styles from templates
add_action('wp_print_styles', 'wp_cta_remove_all_styles', 100);
function wp_cta_remove_all_styles()
{
	if (!is_admin())
	{
		if ( 'wp-call-to-action' == get_post_type() )
		{
			global $post;
			$template = get_post_meta($post->ID, 'wp-cta-selected-template', true);

			if (strstr($template,'-slash-'))
			{
				$template = str_replace('-slash-','/',$template);
			}

			$my_theme =  wp_get_theme($template);

			if ($my_theme->exists()||$template=='blank-template')
			{
				return;
			}
			else
			{
				global $wp_styles;
				$wp_styles->queue = array();
				//wp_register_style( 'admin-bar' );
				wp_enqueue_style( 'admin-bar' );
			}
		}
	}

}
/* Use me for time debugging!

	$start_time = microtime(TRUE);

	$end_time = microtime(TRUE);
	echo $end_time - $start_time;
	exit;

*/


/**
	class css2string {
	    var $css;

	    function parseStr($string) {
	        preg_match_all( '/(?ims)([a-z0-9, \s\.\:#_\-@]+)\{([^\}]*)\}/', $string, $arr);
	        $this->css = array();
	        foreach ($arr[0] as $i => $x)
	        {
	            $selector = trim($arr[1][$i]);
	            $rules = explode(';', trim($arr[2][$i]));
	            $this->css[$selector] = array();
	            foreach ($rules as $strRule)
	            {
	                if (!empty($strRule))
	                {
	                    $rule = explode(":", $strRule);
	                    $this->css[$selector][trim($rule[0])] = trim($rule[1]);
	                }
	            }
	        }
	    }

	    function arrayImplode($glue,$separator,$array) {
	        if (!is_array($array)) return $array;
	        $styleString = array();
	        foreach ($array as $key => $val) {
	            if (is_array($val))
	                $val = implode(',',$val);
	            $styleString[] = "{$key}{$glue}{$val}";

	        }
	        return implode($separator,$styleString);
	    }

	    function getSelector($selectorName) {
	        return $this->arrayImplode(":",";",$this->css[$selectorName]);
	    }

	}
	$cssString = "
	h1 {
	  font-size: 15px;
	  font-weight: bold;
	  font-style: italic;
	  font-family: Verdana, Arial, Helvetica, sans-serif;
	}

	div.item {
	  font-size: 12px;
	  border:1px solid #EEE;
	}";

	$getStyle = new css2string();
	$getStyle->parseStr($cssString);
	echo $getStyle->getSelector("div.item"); */

// Remove all body_classes from custom landing page templates - disabled but you can use the function above to model native v non-native template conditionals.
/**
add_action('wp','wpcta_remove_plugin_filters');

function wpcta_remove_plugin_filters() {

    global $wp_filter;
    global $wp;
    if ($wp->query_vars["post_type"] == 'wp-call-to-action') {
       add_filter('body_class','wp_cta_body_class_names');
    }
}

function wp_cta_body_class_names($classes) {
	 global $post;
	if('wp-call-to-action' == get_post_type() ) {
 	$arr = array();
    $template_id = get_post_meta($post->ID, 'wp-cta-selected-template', true);
    $arr[] = 'template-' . $template_id;
 }
    return $arr;
}*/