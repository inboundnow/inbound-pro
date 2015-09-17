<?php
/*****************************************/
// Template Title:  Tubelar
// Plugin: Landing Pages - Inboundnow.com
/*****************************************/

/* Include Sharrreme Library */
include_once(LANDINGPAGES_PATH . 'assets/libraries/shareme/library.shareme.php');

/* Declare Template Key */
$key = basename(dirname(__FILE__));
$path = LANDINGPAGES_URLPATH . 'templates/' . $key . '/';
$url = plugins_url();

/* Include ACF Field Definitions  */
include_once(LANDINGPAGES_PATH.'templates/'.$key.'/config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('lp_init');

/* Load $post data */
if (have_posts()) : while (have_posts()) :
the_post();

/* Pre-load meta data into variables */
$yt_video = get_field( 'tubelar-yt-video' , $post->ID );
$logo = get_field( 'tubelar-logo' , $post->ID , false ); /* acf 4 images need false for formatting */
$sidebar = get_field( 'tubelar-sidebar' , $post->ID );
$controls = get_field( 'tubelar-controls' , $post->ID );
$boxcolor = get_field( 'tubelar-box-color' , $post->ID );
$textcolor = get_field( 'tubelar-text-color' , $post->ID );
$clear_bg_settings = get_field( 'tubelar-clear-bg-settings' , $post->ID );
$social_display = get_field( 'tubelar-display-social' , $post->ID );
$content = get_field( 'tubelar-main-content' , $post->ID );
$conversion_area = get_field( 'tubelar-conversion-area-content' , $post->ID );
$main_headline = get_field( 'lp-main-headline' , $post->ID ); /* legacy support */

// function to parse url and grab id
function youtubeid($url) {
    if (preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $match)) {
        $match = $match[0];
    }
    return $match;
}

$videoid = youtubeid($yt_video);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php wp_title(); ?></title>
    <?php /* Load all functions hooked to lp_head including global js and global css */
    wp_head(); // Load Regular WP Head
    do_action('lp_head'); // Load Custom Landing Page Specific Header Items
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="<?php echo $path; ?>assets/css/screen.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        .inbound-field input[type=text], .inbound-field input[type=url], .inbound-field input[type=email], .inbound-field input[type=tel], .inbound-field input[type=number], .inbound-field input[type=password] {
            width: 93%;
        }

        #inbound_form_submit {
            padding: 10px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .black-65 {
            background: url('<?php echo $path; ?>assets/img/black-65-trans.png');
        }

        <?php if ($sidebar == "lp_left") { echo "#main {float: right;} #tube-sidebar { width: 320px;}"; }?>
        <?php if ($textcolor != "") { echo "#wrapper {color: $textcolor;}  #video-controls a {color: $textcolor;}
                                            input[type=\"text\"], input[type=\"email\"] {
                                            border: 1px solid $textcolor;
                                            opacity: 0.8;}"; } ?>
        <?php if ($clear_bg_settings === "transparent"){
                if ($boxcolor != "") { echo ".black-50{background: url('".$path."image.php?hex=$boxcolor');}"; }
            } 	?>
        <?php if ($clear_bg_settings === "solid"){
            //echo $boxcolor;exit;
            echo ".black-50{background: $boxcolor}";
        } ?>
    </style>
    <script type="text/javascript" charset="utf-8" src="<?php echo $path; ?>assets/js/jquery.tubular.1.0.js"></script>
    <script type="text/javascript">
        jQuery('document').ready(function () {
            var options = {videoId: '<?php echo $videoid; ?>', start: 3};
            jQuery('#wrapper').tubular(options);
        });
    </script>

</head>
<body>


<div id="wrapper" class="clearfix">

    <div id="logo">

        <?php if ($logo != "") { ?>
            <img src="<?php echo $logo; ?>" alt="logo" id="logo"/>
        <?php } else { ?>
            <img src="<?php echo $path; ?>assets/img/inbound-now-logo.png" alt="Inbound Now Logo" id="logo"/>
        <?php } ?>

    </div>

    <div id="main">
        <?php if ($social_display === "1") { // Show Social Media Icons ?>
            <?php lp_social_media("vertical"); // print out social media buttons?>
        <?php } ?>
        <style type="text/css">
            #lp-social-buttons {
                top: 175px;
            }</style>
        <div class="black-50">
            <h1><?php echo $main_headline; ?></h1>

            <?php echo $content; ?>

        </div>


    </div>

    <div id="tube-sidebar">

        <div class="black-50">

            <?php echo $conversion_area; /* Print out form content */ ?>

        </div>
    </div>
    <?php if ($controls === "1") { // Show video controls ?>
        <div id="controls">
            <p id="video-controls" class="black-50 control-margin"><a href="#" class="tubular-play"><?php echo __('Play','landing-pages'); ?></a> | <a
                    href="#" class="tubular-pause"><?php echo __('Pause','landing-pages'); ?></a>
                <!-- Other Controls | <a href="#" class="tubular-volume-up">Volume Up</a> | <a href="#" class="tubular-volume-down">Volume Down</a> | <a href="#" class="tubular-mute">Mute</a>-->
            </p>
        </div>
    <?php } ?>


</div>
<!-- #wrapper -->
<?php break;
endwhile;
endif;

do_action('lp_footer');
wp_footer();
?>

</body>
</html>