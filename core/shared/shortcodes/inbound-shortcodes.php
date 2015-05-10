<?php


//=============================================
// Define constants
//=============================================
if (!defined('INBOUND_FORMS')) {
	define('INBOUND_FORMS', plugin_dir_url(__FILE__));
}

if (!defined('INBOUND_FORMS_PATH')) {
	define('INBOUND_FORMS_PATH', plugin_dir_path(__FILE__));
}

if (!defined('INBOUND_FORMS_BASENAME')) {
	define('INBOUND_FORMS_BASENAME', plugin_basename(__FILE__));
}

if (!defined('INBOUND_FORMS_ADMIN')) {
	define('INBOUND_FORMS_ADMIN', get_bloginfo('url') . "/wp-admin");
}



/*	InboundNow Shortcodes Class
 *	--------------------------------------------------------- */
if (!class_exists('Inbound_Shortcodes')) {

class Inbound_Shortcodes {
	static $add_script;

	/*	Contruct
	*	--------------------------------------------------------- */
	static function init() {

		self::$add_script = true;
		add_action('admin_enqueue_scripts', array( __CLASS__, 'loads' ));
		add_action('init', array( __CLASS__, 'shortcodes_tinymce' ));
		add_action('init', array( __CLASS__, 'shortcodes_include' ));

		add_action( 'wp_enqueue_scripts',	array(__CLASS__, 'frontend_loads')); // load styles
		add_shortcode('list', array(__CLASS__, 'inbound_shortcode_list'));
		add_shortcode('button', array(__CLASS__, 'inbound_shortcode_button'));
		add_shortcode('social_share',	array(__CLASS__, 'inbound_shortcode_social_links'));
		//add_action('admin_notices', array(__CLASS__, 'inbound_shortcode_prompt'));
			//add_action('admin_init', array(__CLASS__, 'inbound_shortcode_prompt_ignore'));
		//add_action( 'wp_ajax_inbound_shortcode_prompt_ajax',	array(__CLASS__, 'inbound_shortcode_prompt_ajax'));
	}

	public static function shortcodes_include() {
		require_once( 'shortcodes-includes.php' );
	}

	/*	Loads
	*	--------------------------------------------------------- */
	static function loads($hook) {

		global $post;

		if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {

			/* dequeue third party scripts */
			global $wp_scripts;
			if ( !empty( $wp_scripts->queue ) ) {
				$store = $wp_scripts->queue; // store the scripts
				foreach ( $wp_scripts->queue as $handle ) {
					wp_dequeue_script( $handle );
				}
			}

			wp_enqueue_script('jquery' );
			wp_enqueue_script('jquery-cookie', INBOUDNOW_SHARED_URLPATH . 'assets/js/global/jquery.cookie.js', array( 'jquery' ));
			wp_enqueue_script('jquery-total-storage', INBOUDNOW_SHARED_URLPATH . 'assets/js/global/jquery.total-storage.min.js', array( 'jquery' ));
			wp_enqueue_style('inbound-shortcodes', INBOUDNOW_SHARED_URLPATH . 'shortcodes/css/shortcodes.css');
			wp_enqueue_script('jquery-ui-sortable' );
			wp_enqueue_script('inbound-shortcodes-plugins', INBOUDNOW_SHARED_URLPATH . 'shortcodes/js/shortcodes-plugins.js', array( 'jquery', 'jquery-cookie' ));

			if (isset($post)&&post_type_supports($post->post_type,'editor')||isset($post)&&'wp-call-to-action' === $post->post_type) {
				wp_enqueue_script('inbound-shortcodes', INBOUDNOW_SHARED_URLPATH . 'shortcodes/js/shortcodes.js', array( 'jquery', 'jquery-cookie' ));
				$form_id = (isset($_GET['post'])) ? $_GET['post'] : '';
				wp_localize_script( 'inbound-shortcodes', 'inbound_shortcodes', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) , 'adminurl' => admin_url(), 'inbound_shortcode_nonce' => wp_create_nonce('inbound-shortcode-nonce') , 'form_id' => $form_id ) );
				wp_enqueue_script('selectjs', INBOUDNOW_SHARED_URLPATH . 'shortcodes/js/select2.min.js');
				wp_enqueue_style('selectjs', INBOUDNOW_SHARED_URLPATH . 'shortcodes/css/select2.css');
			}

			// Forms CPT only
			if ((isset($post)&&'inbound-forms'=== $post->post_type)||( isset($_GET['post_type']) && $_GET['post_type']==='inbound-forms')) {
				wp_enqueue_style('inbound-forms-css', INBOUDNOW_SHARED_URLPATH . 'shortcodes/css/form-cpt.css');
				wp_enqueue_script('inbound-forms-cpt-js', INBOUDNOW_SHARED_URLPATH . 'shortcodes/js/form-cpt.js');
				wp_localize_script( 'inbound-forms-cpt-js', 'inbound_forms', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'inbound_shortcode_nonce' => wp_create_nonce('inbound-shortcode-nonce'), 'form_cpt' => 'on' ) );
			}

			// Check for active plugins and localize
			$plugins_loaded = array();

			if (is_plugin_active('landing-pages/landing-pages.php')) {
				array_push($plugins_loaded, "landing-pages");
			}

			if (is_plugin_active('cta/calls-to-action.php')) {
				array_push($plugins_loaded, "cta");
			}
			if (is_plugin_active('leads/leads.php')) {
				//array_push($plugins_loaded, "leads");
				//array_push($plugins_loaded, "leads");
			}

			wp_localize_script( 'inbound-shortcodes-plugins', 'inbound_load', array( 'image_dir' => INBOUDNOW_SHARED_URLPATH . 'shortcodes/', 'inbound_plugins' => $plugins_loaded, 'pop_title' => 'Insert Shortcode' ));

			if (isset($post)&&$post->post_type=='inbound-forms') {
				require_once( 'shortcodes-fields.php' );
				add_action( 'admin_footer',	array(__CLASS__, 'inbound_forms_header_area'));
			}

			/* Requeue third party scripts */
			foreach ( $store as $handle ) {
				wp_enqueue_script( $handle );
			}
		}
	}

	static function frontend_loads() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		wp_enqueue_style('inbound-shortcodes', INBOUDNOW_SHARED_URLPATH . 'shortcodes/css/frontend-render.css');

	}

	// Currently off
	static function shortcodes_admin_head() { ?>
		<script type="text/javascript">
		/* <![CDATA[ */
		// Load inline scripts var image_dir = "<?php // echo INBOUND_FORMS; ?>", test = "<?php // _e('Insert Shortcode', 'leads'); ?>";
		/* ]]> */
		</script>
		<?php
	}

	/*	TinyMCE
	*	--------------------------------------------------------- */
	static function shortcodes_tinymce() {
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;

		if ( get_user_option('rich_editing') == 'true' ) {
			add_filter( 'mce_external_plugins', array( __CLASS__, 'add_rich_plugins' ) );
			add_filter( 'mce_buttons', array( __CLASS__, 'register_rich_buttons' ) );
		}
	}

	static function add_rich_plugins( $plugins ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugins['Inbound_Shortcodes'] = INBOUDNOW_SHARED_URLPATH . 'shortcodes/js/tinymce.js';
		return $plugins;

	}

	static function register_rich_buttons( $buttons ) {
		array_push( $buttons, "|", 'Inbound_ShortcodesButton' );
		return $buttons;
	}

	static function inbound_shortcode_button( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'style'=> 'default',
			'font_size' => '',
			'color' => '',
			'text_color' => '',
			'width'=> '',
			'icon' => '',
			'url' => '',
			'target' => ''
		), $atts));
		$style = 'default'; // default setting
		$class = "inbound-button inbound-special-class";
		if (preg_match("/#/", $color)){
			$color = (isset($color)) ? "background-color: $color;" : '';
		} else {
			$color = (isset($color)) ? "background-color: #$color;" : '';
		}

		if (preg_match("/#/", $text_color)){
			$text_color = (isset($text_color)) ? " color: $text_color;" : '';
		} else {
			$text_color = (isset($text_color)) ? " color: #$text_color;" : '';
		}

		// recheck this
		if (preg_match("/px/", $width)){
			$width = (isset($width)) ? " width: $width;" : '';
		} else if (preg_match("/%/", $width)) {
			$width = (isset($width)) ? " width: $width;" : '';
		} else if (preg_match("/em/", $width)) {
			$width = (isset($width)) ? " width: $width;" : '';
		} else {
			$width = ($width != "") ? " width:" . $width . "px;" : '';
		}

		if (preg_match("/px/", $font_size)){
			$font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
		} else if (preg_match("/%/", $font_size)) {
			$font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
		} else if (preg_match("/em/", $font_size)) {
			$font_size = (isset($font_size)) ? " font-size: $font_size;" : '';
		} else {
			$font_size = (isset($font_size)) ? " font-size:" . $font_size . "px;" : '';
		}

		$icon_raw = 'fa-'. $icon . " font-awesome fa";
		$target = (isset($font_size)) ? " target='$target'" : '';
		$button_start = "";

			switch( $style ) {

				case 'default':
				$button	= $button_start;
				$button .= '<a class="'. $class .'" href="'. $url .'"'. $target .' style="'.$color.$text_color.$width.$font_size.'"><i class="'.$icon_raw.'"></i>' . $content .'</a>';
				$button .= $button_start;
				break;

				case 'flat' :
				$button	= $button_start;
				$button .= '<a href="'. $url .'"'. $target .' class="inbound-flat-btn facebook"><span class="'.$icon_raw.' icon"></span><span>'.$content.'</span></a>';

				$button .= $button_start;
				break;
				case 'sunk' :
				$button	= $button_start;
				$button .= '<div class="inbound-sunk-button-wrapper">
						<a href="'. $url .'"'. $target .' class="inbound-sunk-button inbound-sunk-light"><span class="'.$icon_raw.' icon"></span>'.$content.'</a>
						</div>';

				$button .= $button_start;
				break;
			}


		return $button;
	}
 /*
	static function inbound_shortcode_prompt($hook) {

		global $pagenow, $current_user, $post;
		$user_id = $current_user->ID;

		if ( ! get_user_meta($user_id, 'inbound_shortcode_ignore') && ( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) ) {
			$url = $_SERVER['REQUEST_URI'];
			echo '<div class="updated inbound-shortcode-trigger" style="position:relative;">
			<a style="position: absolute; font-size: 13px; top: 0px; right: 30px; color:red;" href="'.$url.'&inbound_shortcode_ignore=0">
			Sounds good! Dismiss this
			</a>
			Looks like you haven\'t clicked the <img style="vertical-align: bottom;" src="'.INBOUDNOW_SHARED_URLPATH . 'assets/' ..'images/global/shortcodes-blue.png"> button <span style="background:yellow">(highlighted in yellow)</span> in the content editor below. There are some great shortcodes for you to use!
			</div>';
			echo "<style type='text/css'>.mce_Inbound_ShortcodesButton { background-color: yellow; }</style>";

		}
	}

	static function inbound_shortcode_prompt_ignore() {
		global $pagenow, $current_user, $post;
			$user_id = $current_user->ID;
			if (( $pagenow == 'post-new.php' || $pagenow == 'post.php' )) {
			if ( isset($_GET['inbound_shortcode_ignore']) && '0' == $_GET['inbound_shortcode_ignore'] ) {
				add_user_meta($user_id, 'inbound_shortcode_ignore', 'true', true);
			}
			}
	}

	static function inbound_shortcode_prompt_ajax() {
		$user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : 1;
		add_user_meta($user_id, 'inbound_shortcode_ignore', 'true', true);
	} */

	static function inbound_shortcode_social_links( $atts, $content = null ) {
		$final_path = INBOUND_FORMS;
			extract(shortcode_atts(array(
			'style' => 'bar',
			'align' => '',
			'heading' => '',
			'heading_align' => '',
			'link' => '',
			'text' => '',
			'facebook' => '',
			'twitter' => '',
			'google_plus' => '',
			'linkedin' => '',
			'pinterest' => '',
			), $atts));
			$float = "";
			if($style == 'bar') {
			$class = 'mt-share-inline-bar-sm';
			} else if ($style == 'circle') {
			$class = 'mt-share-inline-circle-sm';
			} else if ($style == 'square') {
			$class = 'mt-share-inline-square-sm';
			} else if ($style == 'black'){
			$class ="mt-share-inline-square-bw-sm";
			}
			$alignment = "";
			$margin_setting = 'margin-right';
			$header_align = "display:block;";
			if($align == 'horizontal') {
			$alignment = 'inline-block';
			$margin_setting = 'margin-right';
			if($heading_align == 'inline' ){
				$header_align = "display:inline-block; padding-right: 10px; height: 32px; vertical-align: top;";
				$float = "float: left;";
			}

			} else if ($align == 'vertical') {
			$alignment = 'block';
			$margin_setting = 'margin-top';
			$header_align = "display:inline-block; padding-right: 10px; float:left;";
			if($heading_align == 'above' ){
				$header_align = "display:block; padding-right: 10px;";
			}
			}

			if ($link == ""){
			$link = get_permalink();
			}
			if ($text == ""){
			$text = get_the_title();
			}

			$out = "";
			if ($heading != ""){
			$heading = "<span class='inbound-social-share-header' style='$header_align'>$heading</span>";
			}
			$out .= '<span class="inbound-social-share-bar-container">' . $heading;
			if( $facebook ) {
			$out .= '<a class="mt-facebook '.$class.'" style="'.$float.'" href="https://www.facebook.com/sharer/sharer.php?u='.$link.'"><img src="'.$final_path.'images/facebook@2x.png"></a>';
			}
			if( $twitter ) {
			$out .= '<a class="mt-twitter '.$class.'" style="'.$float.'" href="http://twitter.com/intent/tweet?text='.$text.'&amp;url='.$link.'" target="_blank"><img src="'.$final_path.'images/twitter@2x.png"></a>';
			}
			if( $google_plus ) {
			$out .= '<a class="mt-google '.$class.'" style="'.$float.'" href="https://plus.google.com/share?url='.$link.'"><img src="'.$final_path.'images/google@2x.png"></a>';
			}
			if( $linkedin ) {
			$out .= '<a class="mt-linkedin '.$class.'" style="'.$float.'" href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.$link.'&amp;summary='.$text.'"><img src="'.$final_path.'images/linkedin@2x.png"></a>';
			}
			if( $pinterest ) {
			$out .= '<a class="mt-pinterest '.$class.'" style="'.$float.'" href="http://www.pinterest.com/pin/create/button/?url='.$link.'&amp;media=&amp;guid=1234&amp;description='.$text.'"><img src="'.$final_path.'images/pinterest@2x.png"></a>';
			}
			$out .= '</span>';
			$out .= '<style type="text/css">a.mt-share-inline-bar-sm img {
			width: 34px;
			height: auto;
			border: 0px;
			}
			.inbound-social-share-bar-container {
			display: inline-block;
			}
			.inbound-social-share-header {
			vertical-align: middle;
			}
			a.mt-share-inline-bar-sm:hover {
			z-index: 50;
			-webkit-transform: scale3d(1.075, 1.075, 1.075);
			}
			a.mt-share-inline-bar-sm {
			display: '.$alignment.';
			width: 64px;
			height: 32px;
			border-top-left-radius: 0px;
			border-top-right-radius: 0px;
			border-bottom-right-radius: 0px;
			border-bottom-left-radius: 0px;
			margin-right: 0px;
			text-align: center;
			position: relative;
			transition: all 100ms ease-in;
			-webkit-transition: all 100ms ease-in;
			-webkit-transform: scale3d(1, 1, 1);
			}
			a.mt-share-inline-circle-sm img {
			width: 34px;
			height: 34px;
			border: 0px;
			}
			a.mt-share-inline-circle-sm {
			display: '.$alignment.';
			width: 34px;
			height: 34px;
			border-top-left-radius: 50%;
			border-top-right-radius: 50%;
			border-bottom-right-radius: 50%;
			border-bottom-left-radius: 50%;
			'.$margin_setting.': 4px;
			}
			a.mt-share-inline-square-sm img {
			width: 34px;
			height: auto;
			border: 0px;
			}
			a.mt-share-inline-square-sm {
			display: '.$alignment.';
			width: 34px;
			height: 34px;
			border-top-left-radius: 2px;
			border-top-right-radius: 2px;
			border-bottom-right-radius: 2px;
			border-bottom-left-radius: 2px;
			'.$margin_setting.': 4px;}
			.mt-google:hover {
			background-color: rgb(225, 95, 79);
			}
			.mt-google {
			background-color: rgb(221, 75, 57);
			}
			.mt-linkedin:hover {
			background-color: rgb(16, 135, 192);
			}
			.mt-linkedin {
			background-color: rgb(14, 118, 168);
			}
			.mt-twitter:hover {
			background-color: rgb(8, 187, 255);
			}
			.mt-twitter {
			background-color: rgb(0, 172, 238);
			}
			.mt-facebook:hover {
			background-color: rgb(66, 100, 170);
			}
			.mt-facebook {
			background-color: rgb(59, 89, 152);
			}
			.mt-pinterest:hover {
			background-color: rgb(221, 42, 48);
			}
			.mt-pinterest {
			background-color: rgb(204, 33, 39);
			}
			a.mt-share-inline-square-bw-sm img {
			width: 34px;
			height: 34px;
			}
			a.mt-share-inline-square-bw-sm.mt-google:hover {
			background-color: rgb(221, 75, 57) !important;
			}
			a.mt-share-inline-square-bw-sm.mt-linkedin:hover {
			background-color: rgb(14, 118, 168) !important;
			}
			a.mt-share-inline-square-bw-sm.mt-twitter:hover {
			background-color: rgb(0, 172, 238) !important;
			}
			a.mt-share-inline-square-bw-sm.mt-facebook:hover {
			background-color: rgb(59, 89, 152) !important;
			}
			a.mt-share-inline-square-bw-sm.mt-pinterest:hover{
			background-color: #dd2a30 !important;
			}
			a.mt-share-inline-square-bw-sm {
			display: '.$alignment.';
			width: 34px;
			height: 34px;
			border-top-left-radius: 2px;
			border-top-right-radius: 2px;
			border-bottom-right-radius: 2px;
			border-bottom-left-radius: 2px;
			'.$margin_setting.': 4px;
			text-align: center;
			background-color: rgb(51, 51, 51);
			transition: background-color 300ms ease-in;
			-webkit-transition: background-color 300ms ease-in;
			}</style>';
			return $out;
	}


	static function inbound_shortcode_list( $atts, $content = null){
		extract(shortcode_atts(array(
			'icon' => 'check-circle',
			'color' => '',
			'font_size'=> '16',
			'bottom_margin' => '5',
			'icon_color' => "",
			'text_color' => "",
			'columns' => "1",
			), $atts));

		$final_text_color = "";
		$alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$num = substr(str_shuffle($alpha_numeric), 0, 10);
		$icon = ($icon != "") ? $icon : 'check-circle';

		if ($text_color != "") {
			$text_color = str_replace("#", "", $text_color);
			$final_text_color = "color:#" . $text_color . ";";
		}

		$final_icon_color = "";
		if ($icon_color != "") {
			$icon_color = str_replace("#", "", $icon_color);
			$final_icon_color = "color:#" . $icon_color . ";";
		}

		$font_size = str_replace("px", "", $font_size);
		$bottom_margin = str_replace("px", "", $bottom_margin);
		$icon_size = $font_size + 2;
		$line_size = $font_size + 2;

		if ($content === "(Insert Your Unordered List Here. Use the List insert button in the editor. Delete this text)") {
			$content = "<ul>
				<li>Sentence number 1</li>
				<li>Sentence number 2</li>
				<li>Sentence number 3</li>
				<li>Sentence number 4</li>
				</ul>";
		}

		$list_count = 0;
		$inputs = preg_match_all('/\<li(.*?)\>/s',$content, $matches);

		if (!empty($matches[0])) {
			foreach ($matches[0] as $key => $value)
			{
				$list_count++;
			}
		}

		$loop_split =	ceil($list_count / $columns);
		/*********** Need to finish this with column layout
		$form = preg_match_all('/\<ul(.*?)<\/ul>/s',$content, $twomatches);

		if (!empty($twomatches[0]))
		{
			foreach ($twomatches[0] as $key=> $value)
			{
				//echo $value;
				$inputs = preg_match_all('/\<li(.*?)<\/li>/s',$value, $threematches);
				if (!empty($threematches[0]))
				{
					$li_num = count($threematches[0]);
					$split_num =	$li_num / $columns;

					echo $columns . " columns<br>";
					echo $split_num . " split number";
					$li_count = 1;
					//echo "<ul>";
					$reset = 'on';
					echo '<div id="inbound-list" class="inbound-list inbound-row class-'.$num.' fa-list-'.$icon.'">';
					foreach ($threematches[0] as $key => $list_item)
					{
					if ($reset === 'on') {
						echo "<div class='inbound-grid inbound-".$columns."-col'>";
						echo "<ul>";
					}

					echo $list_item;
					if ($li_count % $split_num == 0) {
						echo "</ul>";
						echo "</div>";
						$reset = 'on';
					} else {
						$reset = "off";
					// echo $li_count . " split " . $split_num;
					}

					$li_count++;
					/**
						$new_value = $value;
						$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/','class="$2 lp-track-link"', $new_value);
						$content = str_replace($value, $new_value, $content);

					}
				}
				echo "</div><br>";
			}
		}
		**************/

		$columns = (isset($columns)) ? $columns : '1';
		// http://csswizardry.com/demos/multiple-column-lists/
		$column_css = "";

		if ($columns === "2"){
			$column_css = "#inbound-list.class-".$num." ul { clear:both;} #inbound-list.class-".$num." li { width: 50%; float: left; display: inline;}";
		} else if ($columns === "3") {
			$column_css = "#inbound-list.class-".$num." ul { clear:both;} #inbound-list.class-".$num." li { width: 33.333%; float: left; display: inline;}";
		} else if ($columns === "4") {
			$column_css = "#inbound-list.class-".$num." ul { clear:both;} #inbound-list.class-".$num." li { width: 25%; float: left; display: inline;}";
		} else if ($columns === "5") {
			$column_css = "#inbound-list.class-".$num." ul { clear:both;} #inbound-list.class-".$num." li { width: 19.5%; float: left; display: inline;}";
		}

		return '<div id="inbound-list" class="inbound-list class-'.$num.' fa-list-'.$icon.'">'. do_shortcode($content).'</div>' . '<style type="text/css">
			#inbound-list.class-'.$num.' li {
			'.$final_text_color.'
			list-style: none;
			font-weight: 500;
			font-size: '.$font_size.'px;
			vertical-align: top;
			margin-bottom: '.$bottom_margin.'px;
			}
			#inbound-list.class-'.$num.' li:before {
			background: transparent;
			border-radius: 50% 50% 50% 50%;
			'.$final_icon_color.'
			display: inline-block;
			font-family: \'FontAwesome\';
			font-size: '.$icon_size.'px;
			line-height: '.$line_size.'px;
			margin-right: 0.5em;
			margin-top: 0;
			text-align: center;
			}
			'.$column_css.'
			@media only screen and (max-width: 580px) {
			#inbound-list.class-'.$num.' li {
				width:100%;
			}
			}
			</style>';
	}

	static function inbound_forms_header_area()
	{
		global $post;

		$post_id = $post->ID;
		$post_title = get_the_title( $post_id );
		$popup = trim(get_post_meta($post->ID, 'inbound_shortcode', true));
		$form_serialize = get_post_meta($post->ID, 'inbound_form_values', true);
		$field_count = get_post_meta($post->ID, 'inbound_form_field_count', true);
		$short_shortcode = "";
		$shortcode = new Inbound_Shortcodes_Fields( 'forms' );

		if ( empty ( $post ) || 'inbound-forms' !== get_post_type( $GLOBALS['post'] ) ) {
				return;
		}

		?>
		<div id="entire-form-area">
		<div id="cpt-form-shortcode"><?php echo $popup;?></div>
		<div id="cpt-form-serialize-default"><?php echo $form_serialize;?></div>
		<div id="form-leads-list">
			<h2><?php _e( 'Form Conversions' , 'inbound-pro' ); ?></h2>
			<ol id="form-lead-ul">
				<?php

				$lead_conversion_list = get_post_meta( $post_id , 'lead_conversion_list', TRUE );
				if ($lead_conversion_list) {
					$lead_conversion_list = json_decode($lead_conversion_list,true);
					foreach ($lead_conversion_list as $key => $value) {
						$email = $lead_conversion_list[$key]['email'];
						echo '<li><a title="'.__( 'View this Lead' , 'inbound-pro' ) .'" href="'.esc_url( admin_url( add_query_arg( array( 'post_type' => 'wp-lead', 'lead-email-redirect' => $email ), 'edit.php' ) ) ).'">'.$lead_conversion_list[$key]['email'].'</a></li>';
					}

				} else {
					echo '<span id="no-conversions">'. __( 'No Conversions Yet!' , 'inbound-pro' ) .'</span>';
				}
				?>
			</ol>
		</div>
		<div id="inbound-email-response">
			<h2><?php _e( 'Set Email Response to Send to the person filling out the form' , 'inbound-pro' ); ?></h2>
			<?php
			$values = get_post_custom( $post->ID );
			$selected = isset( $values['inbound_email_send_notification'] ) ? esc_attr( $values['inbound_email_send_notification'][0] ) : "";
			$email_subject = get_post_meta( $post->ID, 'inbound_confirmation_subject', TRUE );
			$email_templates = self::get_email_templates();
			$email_template =	get_post_meta( $post->ID, 'inbound_email_send_notification_template' , TRUE );

			?>
			<div style='display:block; overflow: auto;'>
				<div id='email-confirm-settings'>
					<label for="inbound_email_send">Email Confirmation is currently: </label>
					<select name="inbound_email_send_notification" id="inbound_email_send_notification">
						<option value="off" <?php selected( $selected, 'off' ); ?>>Off</option>
						<option value="on" <?php selected( $selected, 'on' ); ?>>On</option>
						<!-- Action hook here for custom lead status addon -->
					</select>
				</div>
			</div>

			<?php

			if ($email_templates) {

				?>
				<div	style='display:block; overflow: auto;'>
					<div id=''>
						<label for="inbound_email_send_notification_template"><?php _e( 'Select Response Email Template' , 'inbound-pro' ); ?></label>
						<select name="inbound_email_send_notification_template" id="inbound_email_send_notification_template">
							<option value='custom' <?php	selected( 'custom' , $email_template); ?>><?php _e( 'Do not use a premade email template' , 'inbound-pro' ); ?></option>
							<?php

							foreach ($email_templates as $id => $label) {
								echo '<option value="'.$id.'" '. selected($id , $email_template , false ) .'>'.$label.'</option>';
							}
							?>
						</select>
					</div>
				</div>
				<table class='widefat tokens'>
					<tr><td>
					<h2>Available Dynamic Email Tokens</h2>
					<ul id="email-token-list">
						<li class='core_token' title='Email address of sender' style='cursor:pointer;'>{{admin-email-address}}</li>
						<li class='core_token' title='Name of this website' style='cursor:pointer;'>{{site-name}}</li>
						<li class='core_token' title='URL of this website' style='cursor:pointer;'>{{site-url}}</li>
						<li class='core_token' title='Datetime of Sent Email.' style='cursor:pointer;'>{{date-time}}</li>
						<li class='lead_token' title='First & Last name of recipient' style='cursor:pointer;'>{{lead-full-name}}</li>
						<li class='lead_token' title='First name of recipient' style='cursor:pointer;'>{{lead-first-name}}</li>
						<li class='lead_token' title='Last name of recipient' style='cursor:pointer;'>{{lead-last-name}}</li>

						<li class='lead_token' title='Email address of recipient' style='cursor:pointer;'>{{lead-email-address}}</li>
						<li class='lead_token' title='Company Name of recipient' style='cursor:pointer;'>{{lead-company-name}}</li>
						<li class='lead_token' title='Address Line 1 of recipient' style='cursor:pointer;'>{{lead-address-line-1}}</li>
						<li class='lead_token' title='Address Line 2 of recipient' style='cursor:pointer;'>{{lead-address-line-2}}</li>
						<li class='lead_token' title='City of recipient' style='cursor:pointer;'>{{lead-city}}</li>
						<li class='lead_token' title='Name of Inbound Now form user converted on' style='cursor:pointer;'>{{form-name}}</li>
						<li class='lead_token' title='Page the visitor singed-up on.' style='cursor:pointer;'>{{source}}</li>
					</ul>
					</td>
					</tr>
				</table>
				<?php
			}

			?>

			<input type="text" name="inbound_confirmation_subject" placeholder="Email Subject Line" size="30" value="<?php echo $email_subject;?>" id="inbound_confirmation_subject" autocomplete="off">

		</div>
		<div id="inbound-shortcodes-popup">
			<div id="short_shortcode_form">
				Copy Shortcode: <input type="text" class="regular-text code short-shortcode-input" readonly="readonly" id="shortcode" name="shortcode" value='[inbound_forms id="<?php echo $post_id;?>" name="<?php echo $post_title;?>"]'>
			</div>
			<div id="inbound-shortcodes-wrap">
						<div id="inbound-shortcodes-form-wrap">
							<div id="inbound-shortcodes-form-head">
								<?php echo $shortcode->popup_title; ?>
								<?php $shortcode_id = strtolower(str_replace(array(' ','-'),'_', $shortcode->popup_title));	?>
							</div>
							<form method="post" id="inbound-shortcodes-form">
								<input type="hidden" id="inbound_current_shortcode" value="<?php echo $shortcode_id;?>">
								<table id="inbound-shortcodes-form-table">
									<?php echo $shortcode->output; ?>
									<tbody style="display:none;">
										<tr class="form-row" style="text-align: center;">
											<?php if( ! $shortcode->has_child ) : ?><td class="label">&nbsp;</td><?php endif; ?>
											<td class="field" style="width:500px;"><a href="#" id="inbound_insert_shortcode" class="button-primary inbound-shortcodes-insert"><?php _e('Insert Shortcode', 'leads'); ?></a></td>
										</tr>
									</tbody>
								</table>
							</form>
						</div>

						<div id="inbound-shortcodes-preview-wrap">
							<div id="inbound-shortcodes-preview-head">
								<?php _e('Form Preview', 'leads'); ?>
							</div>
							<?php if( $shortcode->no_preview ) : ?>
								<div id="inbound-shortcodes-nopreview"><?php _e('Shortcode has no preview', 'leads'); ?></div>
							<?php else : ?>
								<iframe src='<?php echo INBOUDNOW_SHARED_URLPATH . 'shortcodes/'; ?>preview.php?sc=&post=<?php echo $_GET['post']; ?>' width="285" scrollbar='true' frameborder="0" id="inbound-shortcodes-preview"></iframe>
							<?php endif; ?>
						</div>
						<div class="clear"></div>
			</div>

		</div>
		<div id="popup-controls">
					<a href="#" id="inbound_insert_shortcode_two" class="button-primary inbound-shortcodes-insert-two"><?php _e('Insert Shortcode', 'leads'); ?></a>
					<a href="#" id="shortcode_cancel" class="button inbound-shortcodes-insert-cancel">Cancel</a>
					<a href="#" id="inbound_save_form" style="display:none;" class="button">Save As New Form</a>
				</div>
		</div>

		<script type="text/javascript">

			function inbound_forms_select_email_template() {
				var selected = jQuery('#inbound_email_send_notification_template').val();

				if ( selected != 'custom') {
					jQuery('#postdivrich').hide();
					jQuery('#inbound_confirmation_subject').hide();
					jQuery('.tokens').hide();
				} else {
					jQuery('#postdivrich').show();
					jQuery('#inbound_confirmation_subject').show();
					jQuery('.tokens').show();
				}
			}

			jQuery(document).ready(function($) {

				jQuery('.child-clone-row').first().attr('id', 'row-1');
				setTimeout(function() {
						jQuery('#inbound-shortcodes-form input:visible').first().focus();
				}, 500);

				/* Hide Options Based on Selected Template */
				jQuery('body').on('change' , '#inbound_email_send_notification_template' , function() {
					inbound_forms_select_email_template();
				});

			});
		</script>

			<?php
	}

	public static function get_email_templates() {


			$templates = get_posts(array(
									'post_type' => 'email-template',
									'posts_per_page' => -1
								));


			foreach ( $templates as $template ) {
				$email_templates[$template->ID] = $template->post_title;
			}

			$email_templates = ( isset($email_templates) ) ? $email_templates : array();

			return $email_templates;

	}
}
}
/*	Initialize InboundNow Shortcodes
 *	--------------------------------------------------------- */
Inbound_Shortcodes::init();

?>
