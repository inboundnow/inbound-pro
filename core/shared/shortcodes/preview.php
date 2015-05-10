<?php

/*	Include Wordpress
 *	--------------------------------------------------------------------------- */
if (defined('ABSPATH')) {
	require_once( ABSPATH . 'wp-load.php' );
} else {
	$absolute_path = __FILE__;
	$path_to_file = explode( 'wp-content', $absolute_path );
	$path_to_wp = $path_to_file[0];
	require_once( $path_to_wp . '/wp-load.php' );
}

/*	Get Shortcodes
 *	--------------------------------------------------------------------------- */

$broekn = "divider_options=%22%3Ca%20href=%22http://glocal.dev/wp-admin/edit.php?post_type=inbound-forms%22%3ELeads%3C/a%3E%22";

$test = "http://glocal.dev/wp-content/plugins/leads/shared/shortcodes/preview.php?post=1544&sc=[inbound_form%20id=%221544%22%20name=%22New%20Icon%20Form%22%20redirect=%22http://fontawesome.io/%22%20notify=%22ccc%22%20layout=%22vertical%22%20font_size=%2216%22%20%20labels=%22top%22%20icon=%22check-circle-o%22%20submit=%22Submit%22%20width=%22%22]

[inbound_field%20label=%22First%20Name%22%20type=%22divider%22%20description=%22%22%20required=%220%22%20dropdown=%22%22%20radio=%22%22%20%20checkbox=%22%22%20placeholder=%22%22%20html=%22%22%20dynamic=%22%22%20map_to=%22%22%20

divider_options=%22%3Ca%20href=%22%22%3ETest%3C/a%3E%22]

[/inbound_form]";

$html_test = "divider_options=%22&lt;h3&gt;Hi&lt;/h3&gt;%22";
$html_test2 = "divider_options=%22<h3>Hi</h3>%22";
$extra_content = "";
$html_test = preg_replace("/%22/", "'", $html_test);
$test =  html_entity_decode( trim( $html_test2 ) );
//echo $test;
	$shortcode = html_entity_decode( trim( $_GET['sc'] ) );
	// SET CORRECT FILE PATHS FOR SCRIPTS
	if ( defined( 'WPL_URL' )) {
	   $final_path = WPL_URL . "/";
	} else if (defined( 'LANDINGPAGES_URLPATH' )){
		$final_path = LANDINGPAGES_URLPATH;
	} else if (defined( 'WP_CTA_URLPATH' )){
		$final_path = WP_CTA_URLPATH;
	} else {
		$final_path = preg_replace("/\/shared\/shortcodes\//", "/", INBOUND_FORMS);
	}
/* HTML MATCHES */
// $test = 'html="&lt;span%20class="test"&gt;tes&lt;/span&gt;"';
// preg_match_all('%\[inbound_form_test\s*(?:(layout)\s*=\s*(.*?))?\](.*?)\[/inbound_form_test\]%', $shortcode, $matches);
// preg_match_all('/'.$varname.'\s*?=\s*?(.*)\s*?(;|$)/msU',$shortcode,$matches);


$horiz = "";
if (preg_match("/horizontal/i", $shortcode)) {
$horiz = "<h2 title='Open preview in new tab' class='open_new_tab'>Horizontal Previews detected.<br>Click to Preview Horizontal shortcode in new tab</h2>";
}


	$shortcode = str_replace('\"', '"', $shortcode);
	$shortcode = str_replace('&lt;', '<', $shortcode);
	$shortcode = str_replace('&gt;', '>', $shortcode);
	$shortcode = str_replace('{{child}}', '', $shortcode);
	$shortcode = str_replace('label=""', 'label="Default"', $shortcode);
	//$field_name_fallback = ($field_name === "") ? 'fallback_name' : '0';
	?>
	<!DOCTYPE HTML>
	<html lang="en">
	<head>
	<link rel="stylesheet" type="text/css" href="../shortcodes/css/frontend-render.css" media="all" />

<?php // FIX THESE AND ROLL SHARE TRACKING INTO SHARED
		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'inbound-analytics' , $final_path . 'shared/assets/js/frontend/analytics/inboundAnalytics.js');
		$inbound_localized_data = array('post_id' => 'test',
										'ip_address' => 'test',
										'wp_lead_data' => 'test',
										'admin_url' => 'test',
										'track_time' => 'test',
										'post_type' => 'test',
										'page_tracking' => 'test',
										'search_tracking' => 'test',
										'comment_tracking' => 'test',
										'custom_mapping' => 'test',
										'inbound_track_exclude' => 'test',
										'inbound_track_include' => 'test'
										);
		wp_localize_script( 'inbound-analytics' , 'inbound_settings', $inbound_localized_data);
		wp_head();
?>
<style type="text/css">
html {margin: 0 !important;}
body {padding: 30px 15px;
background:#fff;
padding-top: 5px;}
.bottom-insert-button {
position: fixed;
bottom: 5px;
left: 10%;
text-align: center;
margin: auto;
width: 80%;
display: inline-block;
text-decoration: none;
font-size: 17px;
line-height: 23px;
height: 24px;
margin: 0;
padding: 0 10px 1px;
cursor: pointer;
border-width: 1px;
border-style: solid;
-webkit-border-radius: 3px;
-webkit-appearance: none;
border-radius: 3px;
white-space: nowrap;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;

background-color: #21759B;
background-image: -webkit-gradient(linear,left top,left bottom,from(#2A95C5),to(#21759B));
background-image: -webkit-linear-gradient(top,#2A95C5,#21759B);
background-image: -moz-linear-gradient(top,#2a95c5,#21759b);
background-image: -ms-linear-gradient(top,#2a95c5,#21759b);
background-image: -o-linear-gradient(top,#2a95c5,#21759b);
background-image: linear-gradient(to bottom,#2A95C5,#21759B);
border-color: #21759B;
border-bottom-color: #1E6A8D;
-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5);
box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5);
color: #FFF;
text-decoration: none;
text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);

}
.disclaimer {
top: 0px;
display: inline-block;
margin-bottom: 20px;
font-size: 11px;
display: none;
}
.open_new_tab {
color: #2465D8;
margin-bottom: 15px;
cursor: pointer;
font-size: 12px;
text-align: center;
margin-top: 0px;
display: none;
}
#close-preview-window {
	float: right;
	display: none;
}
<?php if (preg_match("/social_share/i", $shortcode)) {
echo "body {
padding: 10px 0px !important;
padding-left: 5px !important;
}";
$extra_content = "<p>This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode. This is dummy text and not part of the shortcode.</p>";
}?>
			</style>
		</head>
		<body>

			<div id="close-preview-window"><a href="javascript:window.close()" class="close_window">close window</a></div>
			<?php //echo "Shortcode: <textarea style='width:100%; height:50px;'>". $shortcode ."</textarea><br><br>"; ?>
			<?php echo $horiz;
				if ($horiz != ""){ ?>
					<script type="text/javascript">
					function OpenInNewTab(url) {
					  var win=window.open(url, '_blank');
					  win.focus();
					}

					jQuery(document).ready(function($) {
					   var this_link = window.location.href;
					   jQuery("body").on('click', '.open_new_tab', function () {
					   		OpenInNewTab(this_link);
    					});
					   	if ( window.self === window.top ) {

							jQuery("#close-preview-window").show();
						} else {
							jQuery(".open_new_tab").show();
							jQuery(".disclaimer").show();
						}
					 });
					</script>

				<?php }
			?>

			<?php

			echo do_shortcode( $shortcode ) . $extra_content; ?>

			<?php // echo "<br>". $shortcode; ?>

		<?php wp_footer();?>
		<script>
		(function () {

		  if ( !window.jQuery ) {
		    var s = document.createElement('script');
		    s.setAttribute('src', '//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js');
		    document.body.appendChild(s);
		    //console.log('jquery loaded!');
		  }
		  // document.body.innerHTML = document.body.innerHTML.replace( /ERROR: AffiliateID invalid/g, ""); // remove text
		})();
		jQuery(document).ready(function($) {
		   jQuery("body").on('click', '.inbound-button.inbound-special-class', function (e) {
		   	e.preventDefault();
		   	var current_link = $(this).attr('href');
		   	var link_text = (current_link != "" ? "Linked to " + current_link : ". Please Enter a URL in the button options");
		   	alert('Sweet button! Link disabled in preview window ' + link_text);
		   });
		 });

		</script>
		</body>
	</html>