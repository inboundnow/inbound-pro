<?php
/**
 * Global Shared function for all inbound now plugins
 */

/* Get Page Id for Lead Tracking, fallback if $post->ID fails */
if (!function_exists('wpl_url_to_postid')) {
	function wpl_url_to_postid($url) {
		global $wp_rewrite;

		$url = apply_filters('url_to_postid', $url);

		$id = url_to_postid($url);
		if (isset($id)&&$id>0){
			return $id;
		}

		// First, check to see if there is a 'p=N' or 'page_id=N' to match against
		if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
			$id = absint($values[2]);
			if ( $id ){
				return $id;
			}
		}

		//first check if URL is homepage
		$wordpress_url = get_bloginfo('url');
		if (substr($wordpress_url, -1, -1)!='/') {
			$wordpress_url = $wordpress_url."/";
		}

		if (str_replace('/','',$url)==str_replace('/','',$wordpress_url)) {
			return get_option('page_on_front');
		}

		// Check to see if we are using rewrite rules
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
		if ( empty($rewrite) ){
			return 0;
		}

		// Get rid of the #anchor
		$url_split = explode('#', $url);
		$url = $url_split[0];

		// Get rid of URL ?query=string
		$url_split = explode('?', $url);
		$url = $url_split[0];

		// Add 'www.' if it is absent and should be there
		if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
			$url = str_replace('://', '://www.', $url);

		// Strip 'www.' if it is present and shouldn't be
		if ( false === strpos(home_url(), '://www.') )
			$url = str_replace('://www.', '://', $url);

		// Strip 'index.php/' if we're not using path info permalinks
		if ( !$wp_rewrite->using_index_permalinks() )
			$url = str_replace('index.php/', '', $url);

		if ( false !== strpos($url, home_url()) ) {
			// Chop off http://domain.com
			$url = str_replace(home_url(), '', $url);
		} else {
			// Chop off /path/to/blog
			$home_path = parse_url(home_url());
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
			$url = str_replace($home_path, '', $url);
		}

		// Trim leading and lagging slashes
		$url = trim($url, '/');

		$request = $url;
		// Look for matches.
		$request_match = $request;
		foreach ( (array)$rewrite as $match => $query) {
			// If the requesting file is the anchor of the match, prepend it
			// to the path info.
			if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) ){
				$request_match = $url . '/' . $request;
			}

			if ( preg_match("!^$match!", $request_match, $matches) ) {
				// Got a match.
				// Trim the query of everything up to the '?'.
				$query = preg_replace("!^.+\?!", '', $query);

				// Substitute the substring matches into the query.
				$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

				// Filter out non-public query vars
				global $wp;
				parse_str($query, $query_vars);
				$query = array();
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array($key, $wp->public_query_vars) ){
						$query[$key] = $value;
					}
				}

			// Taken from class-wp.php
			foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
				if ( $t->query_var )
					$post_type_query_vars[$t->query_var] = $post_type;

			foreach ( $wp->public_query_vars as $wpvar ) {
				if ( isset( $wp->extra_query_vars[$wpvar] ) ) {
					$query[$wpvar] = $wp->extra_query_vars[$wpvar];
				} elseif ( isset( $_POST[$wpvar] ) ){
					$query[$wpvar] = sanitize_text_field($_POST[$wpvar]);
				} elseif ( isset( $_GET[$wpvar] ) ){
					$query[$wpvar] = sanitize_text_field($_GET[$wpvar]);
				} elseif ( isset( $query_vars[$wpvar] ) ){
					$query[$wpvar] = sanitize_text_field($query_vars[$wpvar]);
				}

				if ( !empty( $query[$wpvar] ) ) {
					if ( ! is_array( $query[$wpvar] ) ) {
						$query[$wpvar] = (string) $query[$wpvar];
					} else {
						foreach ( $query[$wpvar] as $vkey => $v ) {
							if ( !is_object( $v ) ) {
								$query[$wpvar][$vkey] = (string) $v;
							}
						}
					}

					if ( isset($post_type_query_vars[$wpvar] ) ) {
						$query['post_type'] = $post_type_query_vars[$wpvar];
						$query['name'] = $query[$wpvar];
					}
				}
			}

				// Do the query
				$query = new WP_Query($query);
				if ( !empty($query->posts) && $query->is_singular ) {
					return $query->post->ID;
				} else {
					return 0;
				}
			}
		}
		return 0;
	}
}
// Get Page Id for Lead Tracking, fallback if wpl_url_to_postid() fails
if (!function_exists('wp_leads_get_page_final_id')) {
	function wp_leads_get_page_final_id(){
			global $post;
			if (!isset($post)) {
					return;
			}

			$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$current_url = preg_replace('/\?.*/', '', $current_url);
			$page_id = wpl_url_to_postid($current_url);
			$site_url = get_option('siteurl');
			$clean_current_url = rtrim($current_url,"/");

			// If homepage
			if($clean_current_url === $site_url){
				$page_id = get_option('page_on_front'); //
			}
			// If category page
			if (is_category() || is_archive()) {
					$cat = get_category_by_path(get_query_var('category_name'),false);
					$page_id = "cat_" . $cat->cat_ID;
					$post_type = "category";
			}
			// If tag page
			if (is_tag()){
				$page_id = "tag_" . get_query_var('tag_id');
			}

			if(is_home()) {
				$page_id = get_option( 'page_for_posts' );
			} elseif(is_front_page()){
				$page_id = get_option('page_on_front');
			}

			if ($page_id === 0) {
				$page_id = $post->ID;
			}

			return $page_id;
	}
}

/* PHP 5.2 json encode fallback */
if (!function_exists('json_encode_fallback')) {
  function json_encode_fallback($a=false) {
    if (is_null($a)) {
    	return 'null';
    }
    if ($a === false) {
    	return 'false';
    }
    if ($a === true) {
    	return 'true';
    }
    if (is_scalar($a)) {
      if (is_float($a)) {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a)) {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      } else {
        return $a;
      }
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v) {
      	$result[] = json_encode($v);
      }
      return '[' . join(',', $result) . ']';
    } else {
      foreach ($a as $k => $v){
      	$result[] = json_encode($k).':'.json_encode($v);
      }
      return '{' . join(',', $result) . '}';
    }
  }
}


/* Legacy Support */
if (!function_exists('wpleads_add_lead_to_list')) {
	function wpleads_add_lead_to_list( $list_id, $lead_id ) {
		wp_set_post_terms( $lead_id, intval($list_id), 'wplead_list_category', true);
		do_action('post_add_lead_to_lead_list' , $lead_id , $list_id );
	}
}

/* Legacy Support */
if (!function_exists('wpleads_get_lead_lists_as_array')) {

	/* Get Array of Lead Lists from taxonomy */
	function wpleads_get_lead_lists_as_array() {
		$array = array();

		$args = array(
			'hide_empty' => false,
		);

		$terms = get_terms('wplead_list_category', $args);

		foreach ( $terms as $term  ) {
			$array[$term->term_id] = $term->name;
		}

		return $array;
	}
}

// Convert Hex to RGB Value for submit button
if (!function_exists('inbound_Hex_2_RGB')) {
	function inbound_Hex_2_RGB($hex) {
	        $hex = preg_replace("/#/", "", $hex);
	        $color = array();

	        if(strlen($hex) == 3) {
	            $color['r'] = hexdec(substr($hex, 0, 1) . $r);
	            $color['g'] = hexdec(substr($hex, 1, 1) . $g);
	            $color['b'] = hexdec(substr($hex, 2, 1) . $b);
	        }
	        else if(strlen($hex) == 6) {
	            $color['r'] = hexdec(substr($hex, 0, 2));
	            $color['g'] = hexdec(substr($hex, 2, 2));
	            $color['b'] = hexdec(substr($hex, 4, 2));
	        }
	        return $color;
	}
}

/**
 * Grabs Color Scheme from Hex Color. Returns lighter & darker versions of the orignal hex color
 *
 * @access public
 * @since 1.5
 * @param string $hex_color hexidemical color
 * @return array $return_scheme Color Schemes
 */
if (!function_exists('inbound_color_scheme')) {
	function inbound_color_scheme($hex_color, $format = 'hex' ){
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

			return $return_scheme;

		}
}
/**
 * Grabs Specific Color from Lighter/Darker Hex Color Scheme
 *
 * @access public
 * @since 1.5
 * @param array $color_scheme_array from inbound_color_scheme() function
 * @param int $value Light/darkness 1-100
 * @return string $color returns Hex Colors with #
 */
if (!function_exists('inbound_color')) {
	function inbound_color($color_scheme_array, $value) {

	//print_r($color_scheme_array);
	foreach ($color_scheme_array as $key => $val) {
		$closest[$key] = abs($key - $value);
	}
	asort($closest);
	$return_hex_val = key($closest);
	$color = $color_scheme_array[$return_hex_val];

	return $color;
	}
}
?>