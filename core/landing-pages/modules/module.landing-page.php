<?php

/* LOAD TEMPLATE */
add_filter('single_template', 'lp_custom_template' , 13 );
function lp_custom_template($single) {
    global $wp_query, $post, $query_string;

	if ($post->post_type != "landing-page") {
		return $single;
	}
	$template = get_post_meta($post->ID, 'lp-selected-template', true);
	$template = apply_filters('lp_selected_template',$template);


	if (!isset($template)) {
		return $single;
	}

	if (strstr($template,'-slash-')) {
		$template = str_replace('-slash-','/',$template);
	}

	$my_theme =  wp_get_theme($template);

	if ($my_theme->exists()) {
		return $single;
	} else if ( $template != 'default' ) {

		$template = str_replace('_','-',$template);
		
		if ( file_exists( LANDINGPAGES_PATH.'templates/'.$template.'/index.php') ) {
			return LANDINGPAGES_PATH.'templates/'.$template.'/index.php';

		} else {
			return LANDINGPAGES_UPLOADS_PATH.$template.'/index.php';
		}
	}

    return $single;
}


/* LOAD & PRINT CUSTOM JS AND CSS */
add_action('wp_head','landing_pages_insert_custom_head');
function landing_pages_insert_custom_head()
{
	global $post;

	if (isset($post)&&'landing-page'==$post->post_type)
	{

		$custom_css_name = apply_filters('lp_custom_css_name','lp-custom-css');
		$custom_js_name = apply_filters('lp_custom_js_name','lp-custom-js');
		$custom_css = get_post_meta($post->ID, $custom_css_name, true);
		$custom_js = get_post_meta($post->ID, $custom_js_name, true);
		echo "<!-- This site landing page was built with the WordPress Landing Pages plugin - https://www.inboundnow.com/landing-pages/ -->";
		//Print Custom CSS
		if (!stristr($custom_css,'<style'))
		{
			echo '<style type="text/css" id="lp_css_custom">'.$custom_css.'</style>';
		}
		else
		{
			echo $custom_css;
		}
		//Print Custom JS
		if (!stristr($custom_js,'<script'))
		{
			echo '<script type="text/javascript" id="lp_js_custom">jQuery(document).ready(function($) {
			'.$custom_js.' });</script>';
		}
		else
		{
			echo $custom_js;
		}
   }
}

/* FOR DEFAULT TEMPLATE & NATIVE THEME TEMPLATES PREPARE THE CONVERSION AREA */
add_filter('the_content','landing_pages_add_conversion_area', 20);
add_filter('get_the_content','landing_pages_add_conversion_area', 20);
function landing_pages_add_conversion_area($content)
{

	if ('landing-page'==get_post_type() && !is_admin())
	{

		global $post;

		remove_action('the_content', 'landing_pages_add_conversion_area');

		$key = get_post_meta($post->ID, 'lp-selected-template', true);
		$key = apply_filters('lp_selected_template',$key);

		if (strstr($key,'-slash-'))
		{
			$key = str_replace('-slash-','/',$key);
		}

		$my_theme =  wp_get_theme($key);
		//echo $key;
		if ($my_theme->exists()||$key=='default')
		{

			global $post;
		    $wrapper_class = "";

			get_post_meta($post->ID, "default-conversion-area-placement", true);


			$position = get_post_meta($post->ID, "{$key}-conversion-area-placement", true);

			$position = apply_filters('lp_conversion_area_position', $position, $post, $key);

			$_SESSION['lp_conversion_area_position'] = $position;

			$conversion_area = lp_conversion_area(null,null,true,true);

			$conversion_area = "<div id='lp_container' class='$wrapper_class'>".$conversion_area."</div>";

			if ($position=='top')
			{
				$content = $conversion_area.$content;
			}
			else if ($position=='bottom')
			{
				$content = $content.$conversion_area;
			}
			else if ($position=='widget')
			{
				$content = $content;
			}
			else
			{
				$conversion_area = str_replace("id='lp_container'","id='lp_container' class='lp_form_$position' style='float:$position'",$conversion_area);
				$content = $conversion_area.$content;

			}

		}

	}

	return $content;
}

/* DISPLAY LANDING PAGE CONVERSION AREA */
function lp_conversion_area($post = null, $content=null,$return=false, $doshortcode = true, $rebuild_attributes = true)
{
	if (!isset($post)) {
		global $post;
	}

	$wrapper_class = "";

	$content = get_post_meta($post->ID, 'lp-conversion-area', true);

	$content = apply_filters('lp_conversion_area_pre_standardize',$content, $post, $doshortcode);

	$wrapper_class = lp_discover_important_wrappers($content);

	if ($doshortcode)
	{
		$content = do_shortcode($content);
	}


	$content = apply_filters('lp_conversion_area_post',$content, $post);

	if(!$return)
	{
		$content = str_replace('<p><div id="inbound-form-wrapper"', '<div id="inbound-form-wrapper"',  $content);
		$content = preg_replace('/<p[^>]*><\/p[^>]*>/', '', $content); // remove empty p tags
		$content = preg_replace('/<\/p>/', '', $content); // remove last empty p tag
		echo do_shortcode($content);

	}
	else
	{
		return $content;
	}

}

/* ADD SHORTCODE TO DISPLAY LANDING PAGE CONVERSION AREA */
add_shortcode( 'lp_conversion_area', 'lp_conversion_area_shortcode');
function lp_conversion_area_shortcode( $atts, $content = null )
{
	extract(shortcode_atts(array(
		'id' => '',
		'align' => ''
		//'style' => ''
	), $atts));


	$conversion_area = lp_conversion_area($post = null, $content=null,$return=true, $doshortcode = true, $rebuild_attributes = true);


	return $conversion_area;
}

/* DISPLAY MAIN HEADLINE OF CALLING TEMPLATE */
function lp_main_headline($post = null, $headline=null,$return=false)
{
	if (!isset($post))
		global $post;

	if (!$headline)
	{
		$main_headline =  lp_get_value($post, 'lp', 'main-headline');
		$main_headline = apply_filters('lp_main_headline',$main_headline, $post);

		if(!$return)
		{
			echo $main_headline;

		}
		else
		{
			return $main_headline;
		}
	}
	else
	{
		$main_headline = apply_filters('lp_main_headline',$main_headline, $post);
		if(!$return)
		{
			echo $headline;
		}
		else
		{
			return $headline;
		}
	}
}

/* DISPLAY MAIN CONTENT AREA OF LANDING PAGE TEMPLATE */
function lp_content_area($post = null, $content=null,$return=false )
{
	if (!isset($post))
		global $post;

	if (!$content)
	{
		global $post;

		if (!isset($post)&&isset($_REQUEST['post']))
		{

			$post = get_post($_REQUEST['post']);
		}

		else if (!isset($post)&&isset($_REQUEST['lp_id']))
		{
			$post = get_post($_REQUEST['lp_id']);
		}

		//var_dump($post);
		$content_area = $post->post_content;

		if (!is_admin()) {
			$content_area = apply_filters('the_content', $content_area);
		}

		$content_area = apply_filters('lp_content_area',$content_area, $post);

		if(!$return)
		{
			echo $content_area;

		}
		else
		{
			return $content_area;
		}
	}
	else
	{
		if(!$return)
		{
			echo $content_area;
		}
		else
		{
			return $content_area;
		}
	}
}

/* ADD BODY CLASS TO LANDING PAGE TEMPLATE */
function lp_body_class()
{
	global $post;
	global $lp_data;
	// Need to add in lp_right or lp_left classes based on the meta to float forms
	// like $conversion_layout = lp_get_value($post, $key, 'conversion-area-placement');
	if (get_post_meta($post->ID, 'lp-selected-template', true))
	{
		$lp_body_class = "template-" . get_post_meta($post->ID, 'lp-selected-template', true);
		 $postid = "page-id-" . get_the_ID();
		echo 'class="';
		echo $lp_body_class . " " . $postid . " wordpress-landing-page";
		echo '"';
	}
	return $lp_body_class;
}

/* GET PARENT DIRECTORY OF CALLING TEMPLATE */
function lp_get_parent_directory($path)
{
	if(stristr($_SERVER['SERVER_SOFTWARE'], 'Win32')){
		$array = explode('\\',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
    } else if(stristr($_SERVER['SERVER_SOFTWARE'], 'IIS')){
        $array = explode('\\',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
    }else {
		$array = explode('/',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
	}
}

/* GET META VALUE FOR LANDING PAGE TEMPLATE SETTING */
function lp_get_value($post, $key, $id)
{

	if (isset($post))
	{

		$return = do_shortcode(get_post_meta($post->ID, $key.'-'.$id , true));
		$return = apply_filters('lp_get_value',$return,$post,$key,$id);

		return $return;
	}
}

/* CALLBACK TO GENERATE DROPDOWN OF LANDING PAGES - MAY BE UNUSED */
function lp_generate_drowndown($select_id, $post_type, $selected = 0, $width = 400, $height = 230,$font_size = 13,$multiple=true)
{
	$post_type_object = get_post_type_object($post_type);
	$label = $post_type_object->label;

	if ($multiple==true)
	{
		$multiple = "multiple='multiple'";
	}
	else
	{
		$multiple = "";
	}

	$posts = get_posts(array('post_type'=> $post_type, 'post_status'=> 'publish', 'suppress_filters' => false, 'posts_per_page'=>-1));
	echo '<select name="'. $select_id .'" id="'.$select_id.'" class="lp-multiple-select" style="width:'.$width.'px;height:'.$height.'px;font-size:'.$font_size.'px;"  '.$multiple.'>';
	foreach ($posts as $post) {
		echo '<option value="', $post->ID, '"', $selected == $post->ID ? ' selected="selected"' : '', '>', $post->post_title, '</option>';
	}
	echo '</select>';
}

/* REMOVE CUSTOM FIELDS METABOX FROM LANDING PAGE CPT */
add_action( 'in_admin_header', 'lp_in_admin_header');
function lp_in_admin_header()
{
	global $post;
	global $wp_meta_boxes;

	if (isset($post)&&$post->post_type=='landing-page')
	{
		unset( $wp_meta_boxes[get_current_screen()->id]['normal']['core']['postcustom'] );
	}
}

/* DETECTION FOR GRAVITY FORM CLASS AND OTHER IMPORTANT CLASSES */
function lp_discover_important_wrappers($content)
{
	$wrapper_class = "";
	if (strstr($content,'gform_wrapper'))
	{
		$wrapper_class = 'gform_wrapper';
	}
	return $wrapper_class;
}

/* ADDS IN TRACKING SUPPORT FOR LINKS FOUND IN CONVERSION AREA WHEN THERE ARE NO FORMS DETECTED */
function lp_rebuild_attributes( $content=null , $wrapper_class=null )
{
	if (strstr($content,'<form'))
		return $content;

	// Standardize all links
	$inputs = preg_match_all('/\<a(.*?)\>/s',$content, $matches);
	if (!empty($matches[0]))
	{
		foreach ($matches[0] as $key => $value)
		{
			if ($key==0)
			{
				$new_value = $value;
				$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/','class="$2 wpl-track-me-link"', $new_value);



				$content = str_replace($value, $new_value, $content);
				break;
			}
		}
	}

	$check_wrap = preg_match_all('/lp_container_noform/s',$content, $check);
	if (empty($check[0]))
	{
		$content = "<div id='lp_container_noform'  class='$wrapper_class link-click-tracking'>{$content}</div>";
	}

	return $content;
}

/* LEGACY CODE FOR ADDING LANDING PAGE TEMPLATE METABOX SETTINGS TO TEMPLATE METABOX */
function lp_add_option($key,$type,$id,$default=null,$label=null,$description=null, $options=null)
{
	switch ($type)
	{
		case "colorpicker":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'colorpicker',
			'default'  => $default
			);
			break;
		case "text":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'text',
			'default'  => $default
			);
			break;
		case "license-key":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'license-key',
			'default'  => $default,
			'slug' => $id
			);
			break;
		case "textarea":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'textarea',
			'default'  => $default
			);
			break;
		case "wysiwyg":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'wysiwyg',
			'default'  => $default
			);
			break;
		case "media":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'media',
			'default'  => $default
			);
			break;
		case "checkbox":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'checkbox',
			'default'  => $default,
			'options' => $options
			);
			break;
		case "radio":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    =>$id,
			'type'  => 'radio',
			'default'  => $default,
			'options' => $options
			);
			break;
		case "dropdown":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'dropdown',
			'default'  => $default,
			'options' => $options
			);
			break;
		case "datepicker":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'datepicker',
			'default'  => $default
			);
			break;
		case "default-content":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'default-content',
			'default'  => $default
			);
			break;
		case "html":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'html',
			'default'  => $default
			);
			break;
		case "custom-css":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $id,
			'type'  => 'turn-off-editor',
			'default'  => $default // inline css
			);
			break;
		case "description-block":
			return array(
			'label' => $label,
			'description'  => $description,
			'id'    => $key.'-'.$id,
			'type'  => 'description-block',
			'default'  => $default
			);
			break;
	}
}

/* LEGACY CALLBACKS -- STILL USED BY SOME OLDER EXTENSIONS AND TEMPLATES */
function lp_list_feature()
{
	return null;
}


function lp_global_config()
{
	do_action('lp_global_config');
}

if (!function_exists('lp_init')) {
	function lp_init() {
		do_action('lp_init');
	}
}

function lp_head()
{
	do_action('lp_head');
}

function lp_footer()
{
	do_action('lp_footer');
}