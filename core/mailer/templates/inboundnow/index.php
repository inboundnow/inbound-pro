<?php
/**
 * Template Name: Inbound Now
 * @package  Mailer
 * @author   Inbound Now
 */

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

    /* Header */
    $post_id = get_the_ID();

    $contrast_background_color = get_field("contrast_background_color", $post_id);
    $content_background_color = get_field("content_background_color", $post_id);
    $content_color = get_field("content_color", $post_id);
    $show_email_content_border = get_field("show_email_content_border", $post_id);
    $logo = get_field("logo", $post_id);
    $logo_positioning = get_field("logo_positioning", $post_id);
    $logo_url = get_field("logo_url", $post_id);
    $headline = get_field("headline", $post_id);
    $font = get_field("email_font", $post_id);
    $headline_size = get_field("headline_size", $post_id);
    $sub_headline = get_field("sub_headline", $post_id);
    $sub_headline_size = get_field("sub_headline_size", $post_id);
    $featured_image = get_field("featured_image", $post_id);
    $featured_image_width = get_field("image_width", $post_id);
    $featured_image_height = get_field("image_height", $post_id);
    $message_content = get_field("message_content", $post_id);
    $align_message_content = get_field("align_message_content", $post_id);
    $unsubscribe_text = get_field("unsubscribe_text", $post_id);
    $footer_address = get_field("footer_address", $post_id);
    $hide_show_email_in_browser = get_field("hide_show_email_in_browser", $post_id);

    ?>
    <html>
    <head>
        <?php do_action('mailer/email/header'); ?>
    </head>
    <body bgcolor="<?php echo $contrast_background_color; ?>" style="">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="<?php echo $contrast_background_color; ?>" style="background-color:<?php echo $contrast_background_color; ?>;margin:0;padding:0;color:#444444;font-family: 'proxima_nova_regular', Arial, Helvetica, sans-serif;  font-size: 17px;line-height: 35px; color: #70767e;  ">
        <tbody>
        <tr>
            <td>
                <table width="603" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                    <?php
                    if (!$hide_show_email_in_browser) {
                        ?>
                        <tr class="view-in-browser">
                            <td valign="bottom" colspan="3" align="center" style="padding:30px 0 20px 0;">
                                <a href="<?php echo get_permalink( $post_id ); ?>" style="font-size:12px;word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #505050;font-weight: normal;text-decoration: underline;">View this email in your browser</a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>

                    <tr>
                        <td valign="top">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                   style=" background-color:<?php echo $content_background_color;?>;<?php echo ($show_email_content_border) ? 'border-bottom:#cccccc solid 1px;border-left:#cccccc solid 1px;border-right:#cccccc solid 1px; border-top: #cccccc solid 1px;' : '' ; ?>">
                                <tbody>
                                <?php
                                if ($logo) {

                                    ?>
                                    <tr>
                                        <td align="<?php echo $logo_positioning; ?>" style="padding:40px 20px 0px 20px;">
                                            <?php

                                            if ($logo_url) {
                                                echo '<a href="' . $logo_url . '">';
                                            }
                                            ?>
                                            <img src="<?php echo $logo; ?>" alt=" " border="0">
                                            <?php
                                            if ($logo_url) {
                                                echo '</a>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <?php if ($headline) {
                                    ?>
                                    <tr>
                                        <td align="center">
                                            <div class="inbound-editable">


                                                <h1 style="margin-bottom:10px;margin-top:30px;padding:0;font-size:<?php echo $headline_size; ?>;font-weight:normal;color:<?php echo $content_color; ?>;">
                                                    <?php echo $headline; ?>
                                                </h1>
                                                <?php if ($sub_headline) {

                                                    echo '<span style="font-size:'.$sub_headline_size.';color:#9a9a9a; ">' . $sub_headline . '</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <?php
                                if ($featured_image) {
                                    ?>
                                    <tr>
                                        <td align="center" style="padding:30px 0 25px 0;">
                                            <div  class="inbound-editable">

                                                <?php echo '<img src="'. $featured_image.'" width="'.$featured_image_width.'" height="'.$featured_image_height.'" alt=" ">'; ?>


                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>

                                <tr>
                                    <td align="center" style="background-position:center top;background-repeat:no-repeat;padding:20px 15px 30px 48px;">

                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td align="<?php echo $align_message_content; ?>" style=" color:<?php echo $content_color; ?>;line-height:30px;padding:0 40px 0 0;font-family: 'proxima_nova_regular', arial, sans-serif;  font-size: 17px;line-height: 35px; color: #70767e;">
                                                    <?php
                                                    echo $message_content
                                                    ?>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>

                                </tr>
                                <tr>
                                    <td align="left"
                                        style=" color:<?php echo $content_color; ?>;line-height:17px;padding:20px 40px 40px 40px;font-family:<?php echo $font; ?>">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="font-size:11px;color:#999999;margin:0;padding:20px 20px 30px 20px; font-family:<?php echo $font; ?>">

                                <?php echo do_shortcode($unsubscribe_text); ?>
                                <span style="text-align:center; display:inline-block; font-size:10px;">
                                    <?php echo $footer_address; ?>
                                </span>
                            </p>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <?php do_action('mailer/email/footer'); ?>
    </body>
    </html>

    <?php

endwhile; endif;