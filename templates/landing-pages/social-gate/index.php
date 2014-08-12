<?php
/**
 * Template Name:  Social Gate Template
 * @package  WordPress Landing Pages
 * @author   Your Name Here!
 * @example  example.com/landing-page
 * @version  1.0
 * @since    1.0
 */

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__));
$path = LANDINGPAGES_UPLOADS_URLPATH ."$key/";
$url = plugins_url();

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

 /* Initialize ajax.social-gate.js */
// Enable tracking on Social Media Shares
add_action('wp_enqueue_scripts','lp_social_gate_register_ajax');
function lp_social_gate_register_ajax() {
  global $path;
  
  $current_url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."";
  // embed the javascript file that makes the AJAX request
  wp_enqueue_script( 'lp-social-gate-ajax-request', $path . 'assets/js/ajax.social-gate.js', array( 'jquery' ) );
  wp_localize_script( 'lp-social-gate-ajax-request', 'myajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'current_url' =>  $current_url) );
} 

/* Load $post data and start normal WordPress Loop */
if (have_posts()) : while (have_posts()) : the_post();

/* Pre-load meta data into variables */
//Main Content Area
$content = lp_get_value($post, $key, 'main-content');
$conversion_area = lp_get_value($post, $key, 'conversion-area-content');
// Headline Text Color: Use this setting to change the Heading Text Color
$headline_color = lp_get_value($post, $key, 'headline-color');
// Main Text Color: Use this setting to change the Heading Text Color
$text_color = lp_get_value($post, $key, 'text-color');
// Content Background Color: Use this setting to change the Heading Text Color
$content_background = lp_get_value($post, $key, 'content-background');
// Unlock Heading: Unlock area heading
$unlock_heading = lp_get_value($post, $key, 'unlock-heading');
// Unlock Text: Unlock area heading
$unlock_text = lp_get_value($post, $key, 'unlock-text');
// Facebook Like URL: Unlock area heading
$facebook_link = lp_get_value($post, $key, 'facebook-link');
// Twitter URL for Tweet: Unlock area heading
$twitter_link = lp_get_value($post, $key, 'twitter-link');
// Message in Tweet: Unlock area heading
$twitter_text = lp_get_value($post, $key, 'twitter-text');
// URL for Google +1: Unlock area heading
$google_link = lp_get_value($post, $key, 'google-link');
// Link to Download: Unlock area heading
$download_link = lp_get_value($post, $key, 'download-link');
// Style of Social Gate: Unlock area heading
$style = lp_get_value($post, $key, 'style');
// Background Settings: Unlock area heading
$background_style = lp_get_value($post, $key, 'background-style');
// Background Image: Unlock area heading
$background_image = lp_get_value($post, $key, 'background-image');
// Background Color: Unlock area heading
$background_color = lp_get_value($post, $key, 'background-color');
$opacity = lp_get_value($post, $key, 'opacity');

// Background Options Logic
if ( $background_style === "fullscreen" ) {
  $bg_style = 'background: url('.$background_image.') no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale");
  -ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$background_image.'", sizingMethod="scale")";';
  };

if ( $background_style === "color" ) {
  $bg_style = 'background: #'.$background_color.';';
  };

if ( $background_style === "tile" ) {
  $bg_style = 'background: url('.$background_image.') repeat; ';
  };

if ( $background_style === "repeat-x" ) {
  $bg_style = 'background: url('.$background_image.') repeat-x; ';
  };  

if ( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
  };

if ( $background_style === "repeat-y" ) {
  $bg_style = 'background: url('.$background_image.') repeat-y; ';
  };  
// Social Unlock Styles
if ( $style === "default" ) {
  $unlock_style = 'ui-social-locker-secrets';
  };

if ( $style === "glass" ) {
  $unlock_style = 'ui-social-locker-glass';
  };

if ( $style === "dandy" ) {
  $unlock_style = 'ui-social-locker-dandyish';
  }; 

if ( $style === "none" ) {
  $unlock_style = '';
  };     

  
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title><?php wp_title(); ?></title>

<style type="text/css">
/* Inline Style Changes go here */
body { <?php echo $bg_style; ?> }
<?php echo "#content-area {background: url('/wp-content/plugins/landing-pages/images/image.php?hex=$content_background&trans=$opacity'); border-radius: 8px; padding: 10px;}";?>
  <?php if ($headline_color != "") {
            echo "header h1 { color: #$headline_color;} "; // change sidebar color
        } ?> 
   <?php if ($text_color != "") {
            echo "#content-area { color: #$text_color;} "; // change sidebar color
        } ?>        
</style>
<?php wp_head(); // Load Regular WP Head ?>
<?php do_action('lp_head'); // Load Custom Landing Page Specific Header Items ?>
</head>
	
<?php wp_enqueue_script( 'jquery');
  wp_enqueue_script('jquery-cookie', LANDINGPAGES_URLPATH . 'js/jquery.cookie.js');?>	

	
  <script src="<?php echo $path;?>assets/js/jquery.op.sociallocker.min.js"></script>
  <link type="text/css" rel="stylesheet" href="<?php echo $path;?>assets/css/jquery.op.sociallocker.min.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo $path;?>assets/css/style.css" />


    
  <script>
    jQuery(document).ready(function ($) {

        
        jQuery("#built-in-themes .to-lock-1").socialLock({
    
            // text that appears inside the locker
            // 
          text: {
            header: "<?php echo $unlock_heading;?>",
            message: "<?php echo $unlock_text;?>" }, 
            // add your extra css classes here or use one of the style presets
          style: "<?php echo $unlock_style; ?>",

          twitter: {
              url: "<?php echo $twitter_link; ?>",
              text: "<?php echo $twitter_text;?>" },
          facebook: {
              url: "<?php echo $facebook_link;?>"},
              appId: "427542137301809", 
          google: {
              url: "<?php echo $google_link;?>" },

		      events: {
                unlock: function () {
                    //alert('The content was unlocked!');
					lp_social_gate_unlock_event();
                    jQuery.cookie("social-unlock-<?php the_ID(); ?>", "unlocked", { expires: 7 });
                    jQuery(function(){
                      var count = 10;
                      countdown = setInterval(function(){
                        jQuery(".wait").html("or wait " + count + " seconds!");
                        if (count == 0) {
                          window.location = '<?php echo $download_link;?>';
                        }
                        count--;
                      }, 1000);
                    });
                }
                
            }
      
		});

    });
  </script> 
  <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>

  <div class="wrap">
 
    <article id="built-in-themes">
	<header>
    	<h1><?php the_title(); ?></h1>
       
 
    </header>

    <section class="example">
      <div id="content-area">
        <div id="content-main">
          <?php echo do_shortcode($content); ?>
        </div>
        <div class="pattern-background">
       

        <p class="to-lock-1 to-lock">
          <span class="lock-inner">
         
              Thank You for Sharing! <a href="<?php echo $download_link;?>">Click here</a> <span class="wait"></span><?php echo do_shortcode( $conversion_area ); /* Print out form content */ ?>
        
        </span>
        </p> 
      
        </div>
       </div>  
    </section>

</article>
        
  </div>

<?php break; endwhile; endif; // end WordPress Loop
do_action('lp_footer'); // Load custom landing page footer items
wp_footer(); // Load regular WordPress Footer
?> 
</body>
</html>