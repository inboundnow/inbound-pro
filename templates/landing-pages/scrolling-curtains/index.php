<?php 
/*****************************************/
// Template Title:  Scrolling Curtains
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Declare Template Key */
$key = lp_get_parent_directory(dirname(__FILE__)); 
$path = LANDINGPAGES_UPLOADS_URLPATH.$key.'/';

$url = plugins_url();
/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
lp_init();


//add_action( 'wp_enqueue_scripts', 'scrolling_curtain_enqueue' ); 
function scrolling_curtain_enqueue()
{
	global $path;
	//echo $path.'js/curtain.js';exit;
	wp_enqueue_script('lp-scrolling-curtains', $path.'js/curtain.js', array('jquery'));
}	

// Convert Hex to RGB Value for submit button
function Hex_2_RGB($hex) {
        $hex = ereg_replace("#", "", $hex);
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

/* Load $post data */
if (have_posts()) : while (have_posts()) : the_post();
   
/* Pre-load meta data into variables */ 
/*$logo_img = lp_get_value($post, $key, 'logo-image-id');*/ 
$h1_color=lp_get_value($post, $key, 'h1-color'); 
$h2_color=lp_get_value($post, $key, 'h2-color'); 
$h3_color=lp_get_value($post, $key, 'h3-color'); 
$form_text_color=lp_get_value($post, $key, 'form-text-color'); 

$splash_page2_content = lp_get_value($post, $key, 'splash-page2');

$splash_page1_background_color = lp_get_value($post, $key, 'splash-page1-background-color'); 
$splash_page2_background_color = lp_get_value($post, $key, 'splash-page2-background-color'); 
$splash_page3_background_color = lp_get_value($post, $key, 'splash-page3-background-color'); 

$splash_page1_bg_image = lp_get_value($post, $key, 'splash-page1-image-id'); 
$splash_page2_bg_image = lp_get_value($post, $key, 'splash-page2-image-id'); 
$splash_page3_bg_image = lp_get_value($post, $key, 'splash-page3-image-id'); 

$transparency_color = lp_get_value($post, $key, 'blockquote-transparency-color');
$submit_button_color = lp_get_value($post, $key, 'submit-button-color');

$submit_text = lp_get_value($post, $key, 'submit-text-id');

//Gradient Color Setting
$gradient_color1=lp_get_value($post, $key, 'gradient-color1');

if(empty($gradient_color1)){ $gradient_color1="ffffff";}

$gradient_color2=lp_get_value($post, $key, 'gradient-color2');   

if(empty($gradient_color1)){ $gradient_color1="F89D42";}

$footer = lp_get_value($post, $key, 'footer');
$footer_color = lp_get_value($post, $key, 'footer-color');

$link_text_page2=lp_get_value($post, $key, 'link-text-page2');
$link_text_page3=lp_get_value($post, $key, 'link-text-page3');

if(empty($link_text_page2)){ $link_text_page2="Page 2";}

if(empty($link_text_page3)){ $link_text_page3="Page 3";}

$content = lp_content_area(null,null,true); 

$RBG_array = Hex_2_RGB($submit_button_color);
$red = $RBG_array['r'];
$green = $RBG_array["g"];
$blue = $RBG_array["b"]; 

$RBG_array2 = Hex_2_RGB($gradient_color1);
$g_red1=$RBG_array2['r'];
$g_green1 = $RBG_array2["g"];
$g_blue1 = $RBG_array2["b"]; 

$RBG_array3 = Hex_2_RGB($gradient_color2);
$g_red2=$RBG_array3['r'];
$g_green2 = $RBG_array3["g"];
$g_blue2 = $RBG_array3["b"]; 

$RBG_array4 = Hex_2_RGB($transparency_color);
$q_red=$RBG_array4['r'];
$q_green = $RBG_array4["g"];
$q_blue = $RBG_array4["b"]; 
    


?>
<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title><?php wp_title();?></title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<?php /* Load all functions hooked to lp_head including global js and global css */
	wp_head(); // Load Regular WP Head
	lp_head(); // Load Custom Landing Page Specific Header Items
	?>
    <link rel="stylesheet" href="<?php echo $path;?>css/base.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $path;?>css/curtain.css" type="text/css" media="all" />
	<link href='http://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet' type='text/css'>
	<script src="<?php echo $path;?>js/curtain.js" type="text/javascript"></script>
	<style type="text/css">
		<?php if(!empty($splash_page1_bg_image)){ ?>
	  	  #main{ background:url("<?php echo $splash_page1_bg_image;?>") no-repeat fixed center center / cover  #<?php echo $splash_page1_background_color;?>;
		  }
		<?php } else { ?>
	       #main{ background-color:#<?php echo $splash_page1_background_color;?>;
		  }
		<?php }
	      if(!empty($splash_page2_bg_image)){ ?>
	  	  #about{ background:url("<?php echo $splash_page2_bg_image;?>") no-repeat fixed center center / cover  #<?php echo $splash_page2_background_color;?>;
		  }
		<?php } else { ?>
	       #about{ background-color:#<?php echo $splash_page2_background_color;?>;
		  }
		<?php }
		  if(!empty($splash_page3_bg_image)){ ?>
	  	  #register{ background:url("<?php echo $splash_page3_bg_image;?>") no-repeat fixed center center / cover  #<?php echo $splash_page3_background_color;?>;
		  }
		<?php } else { ?>
	       #register{ background-color:#<?php echo $splash_page2_background_color;?>;
		  }
		<?php }	
		  if(!empty($transparency_color)){ ?>
		  #about .quote { background-color:rgba(<?php echo $q_red;?>, <?php echo $q_green;?>, <?php echo $q_blue;?>, 0.5); }
		<?php 
		}
		if(!empty($h1_color)){ 
		?>
			h1, .curtain-links { color: #<?php echo $h1_color;?>; }
		<?php
		}		  
	 	if(!empty($h2_color)){ ?>
		  h2{ color: #<?php echo $h2_color;?>; }
		<?php }
		  if(!empty($footer_color)){ ?>
		  .footer{ color: #<?php echo $footer_color;?>; }
		<?php }
		  if(!empty($h3_color)){ ?>
		  h3{ color: #<?php echo $h3_color;?>; }
		<?php } 		 ?>
	
/* end form styles */
<?php if ($submit_button_color != "") {
  echo"input[type='submit'] {
	   background: -moz-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -ms-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -o-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba($red,$green,$blue, 0.5)), to(rgba($red,$green,$blue, 0.7)));
	   background: -webkit-linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));
	   background: linear-gradient(rgba($red,$green,$blue, 0.5), rgba($red,$green,$blue, 0.7));}";
    }	
	
?> 
#lp_container_form label, .lp-span { color: #<?php echo $form_text_color; ?>;}
.curtain-links {
	position: absolute;
bottom: 20px;
left: 44%;}
.pannel-2 .curtain-links {
	bottom: 200px;
}
.lp-span {
	margin-bottom: 20px;
	//margin-top: 20px;
}
.gform_title, .lp_container_form h1, .lp_container_form h2, .lp_container_form h3, .lp_container_form h4 {
	text-align: center;
}
.gform_title {
	font-size: 20px;
}
.lp_form_field, input[type='submit'] {
	margin: auto;

}
#lp_container_form input[type='submit'] {
	margin-left: 39px;
}
</style>
</head>
<body style="height: 5411px;">
<?php
//<ul id="menu">
//    <li><a href="#about" class="curtain-links">Panel 2</a></li>
//    <li><a href="#register" class="curtain-links">Panel 3</a></li>
//</ul>
?>
<ol class="curtains">
    <li data-position="0" data-height="598" style="min-height: 100%; z-index: 999; _margin-top: -342px; display: block;" id="main" class="cover current">
        <article>           
            <?php 
			if(!empty($content)){
				echo $content;	
			}else{?>
			 <h1>HOLY SMOKES BATMAN!</h1>
			 <h2>SCROLL DOWN FOR YOUR CHANCE TO WIN TICKETS!</h2>
			 <br><br>
            <img class="main_logo" src="<?php echo $path;?>images/batman-the-dark-knight-rises.jpg" alt="coming soon"><BR>
			<br><br>
            <a href="#about" class="curtain-links">[ CLICK HERE OR SCROLL DOWN ]</a>
            <?php }?>
        </article>
    </li>
    <li class="" data-position="598" data-height="<?php echo $pane_2_height; ?>" style="min-height: 100%;height: <?php echo $pane_2_height; ?>px; z-index: 998; margin-top: 0px;" id="about">

        <article class='pannel-2'>
         	<?php 
				if ($splash_page2_content)
				{
					echo $splash_page2_content;
				}
				else
				{
					?>					
					<center><img src='<?php echo $path;?>images/batman-logo.png' style='max-height:200px;' ></center>
					<br><br>
					<h2>Special Midnight Showing</h2>
					
					<blockquote>
					National Cinemas will be holding a special midnight showing for The Dark Night Rises on Sunday July 15th in the following cities: 
						<ul>
							<li> Alton, Mi
							<li> Belton, Mi
							<li> Canton, Mi
							<li> Fairfax, Mi
						</ul>
						
					This is an invite only event so scroll below to request your invites! 
					</blockquote>
					<br><br><br><br>
					
					<h2>Event Rules</h2> 
					<blockquote> 
					
						<ul>
							<li>Be 13 years or older or chaperoned by an adult.</li>
							<li>Each signup is limited to 4 tickets</li>
							<li>Tickets will cost $12.00/adult and $8.00/child; 3D glasses included!</li>
						</ul>
						<div>Scroll down to register!</div>						
					</blockquote>
					
					<br><br><br><br>
					<a href="#register" class="curtain-links">[ CLICK HERE OR SCROLL DOWN ]</a>
					<?php
				}
				?>   
         </article>
    </li>
    <li data-position="3211" data-height="<?php echo $panel_3_height; ?>" style="min-height: 100%;height: <?php echo $panel_3_height; ?>px; z-index: 997; display: none;" id="register">

        <article class='pannel-3'>
			<?php
			
			
			if (!lp_get_value($post, 'lp', 'conversion-area'))
			{
				?>
				<center><img src='<?php echo $path;?>images/cinema-logo.jpg' style='max-height:200px;' title='National Cinemas Proudly Presents!'></center>
				<br><br>
				<div id="lp_container_form">
					<h3>Signup for a chance to win free tickets!</h3>
					<ul class="updates">								
								<li>
									<!-- Subscribe to a mailing list: -->
								  <div id="mc_embed_signup">
									<span class="email_text">Ticket Request:</span>
									<form style="width:100px;margin: 0; padding: 0;display:inline;" action="" method="post" id="lp-form-mc-embedded-subscribe-form mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="lp-form lp-form-track validate" target="_blank">
									<span class="mc-field-group" style='display:inline;'>
									  <input name="EMAIL" placeholder="you@email.com" class="required email" id="mce-EMAIL" type="email">
									</span>
									  <span id="mce-responses" class="" style='display:inline;'>
									  </span>
									  <span class="" style='display:inline;'>
										  <input value="Signup Now!" name="subscribe" id="mc-embedded-subscribe" class="button" type="submit">
									  </span> 

									</form>
								</div>
								</li>
								<br><br>
								<span class="org small">*Once you have signed up you will receive an email with ticket purchasing options! Thank you for being a valued customer!</span>
								

							
					</ul>
				</div>
				<?php
			}
			else
			{
				lp_conversion_area();
			}
			?>
			
         </article>
		 <center>
		 <div class='footer'>
		 <?php if(!empty($footer)){?>     
			<?php echo $footer;?>
		 <?php }?>
		 </div>	
		 </center>
		 
       
    </li>	
</ol>
<script>

jQuery(document).ready(function () { 
    jQuery(function(){
       jQuery('.curtains').curtain({
           scrollSpeed: 450,
           controls: '.menu',
           curtainLinks: '.curtain-links'
       });
    });
});
</script>	
<?php 
break;
endwhile; endif; // End wordpress loop

lp_footer();
wp_footer();
?>    
</body>
</html>