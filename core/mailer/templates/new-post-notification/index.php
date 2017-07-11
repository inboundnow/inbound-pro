<?php
/**
 * Template Name: Cerberus Fluid
 * @package  Mailer
 * @author   Inbound Now
 */

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

    $post_id = get_the_ID();

    /*Logo and Main images*/
    $logo_image = get_field("logo_image", $post_id);
    $main_image = get_field("featured_image", $post_id);

    /*Main content*/
    $optional_email_header_text = get_field("optional_email_header_text", $post_id);
    $email_header_text = get_field("email_header_text", $post_id);
    $main_content_a = get_field("main_content_a", $post_id);
    $email_button_text = get_field("email_button_text", $post_id);
    $email_button_link = get_field("email_button_link", $post_id);
    $email_button_color = get_field("email_button_color", $post_id);
    $email_button_text_color = get_field("email_button_text_color", $post_id);
    $email_button_hover_color = get_field("email_button_hover_color", $post_id);
    $button_font_size = get_field("button_font_size", $post_id);
    $main_content_b = get_field("main_content_b", $post_id);
    $main_content_font_size = get_field("main_content_font_size", $post_id);

    /*Columns*/
    $footer_text_font_size = get_field("footer_text_font_size", $post_id);
    $column_display_single_column_or_double_column = get_field("column_display_single_column_or_double_column", $post_id);

    /* Latest Posts */
    $show_latest_posts = get_field("show_latest_posts", $post_id);
    $show_latest_posts_category = get_field("show_latest_posts_category", $post_id);
    $show_latest_posts_limit = get_field("show_latest_posts_limit", $post_id);
    $posts_button_text = get_field("posts_button_text", $post_id);
    $posts_button_text_color = get_field("posts_button_text_color", $post_id);
    $posts_button_color = get_field("posts_button_color", $post_id);

    /*Email colors tab*/
    $email_background_color = get_field("email_background_color", $post_id);
    $email_content_background_color = get_field("email_content_background_color", $post_id);
    $email_text_color = get_field("email_text_color", $post_id);


    /*Contact Info tab*/
    $contact_information_content = get_field("contact_information_content", $post_id);
    $contact_information_text_color = get_field("contact_information_text_color", $post_id);
    $contact_information_font_size = get_field("contact_information_font_size", $post_id);
    $unsubscribe_link_text = get_field("unsubscribe_link_text", $post_id);
    $unsubscribe_link_color = get_field("unsubscribe_link_color", $post_id);
    $unsubscribe_link_text_size = get_field("unsubscribe_link_text_size", $post_id);


    $email_width = /*wp_get_attachment_metadata*/
        ($logo_image);

    /*Get the logo size. And if it's bigger than 200, set it for 200*/
    $unknown_logo_width = getimagesize($logo_image);
    if ($unknown_logo_width[0] >= 200) {
        $logo_width = 200;
    } else {
        $logo_width = $unknown_logo_width;
    }


    ?>

    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8"> <!-- utf-8 works for most cases -->
        <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Use the latest (edge) version of IE rendering engine -->
        <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

        <!-- Web Font / @font-face : BEGIN -->
        <!-- NOTE: If web fonts are not required, lines 9 - 26 can be safely removed. -->

        <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
        <!--[if mso]>
        <style>
            * {
                font-family: sans-serif !important;
            }
        </style>
        <![endif]-->

        <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
        <!--[if !mso]><!-->
        <!-- insert web font reference, eg: <link href='https://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'> -->
        <!--<![endif]-->

        <!-- Web Font / @font-face : END -->

        <!-- CSS Reset -->
        <style type="text/css">

            /* What it does: Remove spaces around the email design added by some email clients. */
            /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
            html,
            body {
                margin: 0 auto !important;
                padding: 0 !important;
                height: 100% !important;
                width: 100% !important;
            }

            /* What it does: Stops email clients resizing small text. */
            * {
                -ms-text-size-adjust: 100%;
                -webkit-text-size-adjust: 100%;
            }

            /* What is does: Centers email on Android 4.4 */
            div[style*="margin: 16px 0"] {
                margin: 0 !important;
            }

            /* What it does: Stops Outlook from adding extra spacing to tables. */
            table,
            td {
                mso-table-lspace: 0pt !important;
                mso-table-rspace: 0pt !important;
            }

            /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
            table {
                border-spacing: 0 !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
                Margin: 0 auto !important;
            }

            table table table {
                table-layout: auto;
            }

            /* What it does: Uses a better rendering method when resizing images in IE. */
            img {
                -ms-interpolation-mode: bicubic;
            }

            /* What it does: A work-around for iOS meddling in triggered links. */
            .mobile-link--footer a,
            a[x-apple-data-detectors] {
                color: inherit !important;
                text-decoration: underline !important;
            }

        </style>

        <!-- Progressive Enhancements -->
        <style>

            /* What it does: Hover styles for buttons */
            .button-td,
            .button-a {
                transition: all 100ms ease-in;
            }

            .button-td:hover,
            .button-a:hover {
                background: <?php echo $email_button_hover_color; ?> !important;
            }

            .button-a:hover {
                border: 15px solid <?php echo $email_button_hover_color; ?> !important;
            }

            @media only screen and (max-width: 480px) {
                .col2img {
                    height: auto !important;
                    width: 100% !important;
                }

                .devicewidth {
                    width: 95%;
                    margin-left: 10px;
                    margin-right: 10px;
                    text-align: center;
                    float: none;
                }

                .container {
                    width:95%;
                }
            }

        </style>
        <?php do_action('mailer/email/header'); ?>
    </head>
    <body width="100%" bgcolor="#222222" style="Margin: 0;">
    <center style="width: 100%; background: <?php echo $email_background_color; ?>;">

        <!-- Visually Hidden Preheader Text : BEGIN -->
        <div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
            <?php echo $optional_email_header_text; ?>
        </div>
        <!-- Visually Hidden Preheader Text : END -->

        <!--
            Set the email width. Defined in two places:
            1. max-width for all clients except Desktop Windows Outlook, allowing the email to squish on narrow but never go wider than 600px.
            2. MSO tags for Desktop Windows Outlook enforce a 600px width.
        -->
        <div style="max-width: 600px; margin: auto;" class="container">
            <!--[if (gte mso 9)|(IE)]>
            <table cellspacing="0" cellpadding="0" border="0" width="600" align="center" class="container">
                <tr>
                    <td>
            <![endif]-->

            <!-- Email Header : BEGIN -->
            <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
                <tr>
                    <td style="padding: 20px 0; text-align: center">
                        <img src="<?php echo $logo_image; ?>" width="<? echo $logo_width; ?>" alt="logo_image" border="0" style="width: 100%; max-width: 200px;">
                    </td>
                </tr>
            </table>
            <!-- Email Header : END -->

            <!-- Email Body : BEGIN -->
            <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px; background-color:<?php echo $email_content_background_color; ?>;">

                <!-- Hero Image, Flush : BEGIN -->
                <?php
                if ($main_image && strstr($main_image, '//')) {
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $main_image; ?>" width="600" height="" alt="main_image" border="0" align="center" style="width: 100%; max-width: 600px;">
                        </td>
                    </tr>
                    <?php
                }
                ?> <!-- Hero Image, Flush : BEGIN -->
                <?php
                if ($email_header_text) {
                    ?>
                    <tr>
                        <td style="font-family: Helvetica, arial, sans-serif; font-size: 25px; color: #282828; text-align:center; line-height: 24px;padding-top: 40px;padding-left: 10px;padding-right: 10px;">
                            <?php echo $email_header_text; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <!-- Hero Image, Flush : END -->

                <!-- 1 Column Text + Button : BEGIN -->
                <tr>
                    <td>
                        <table cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td style="padding-left: 40px; padding-right: 40px; padding-bottom: 40px;padding-top: 25px; font-family: sans-serif; font-size: <?php echo $main_content_font_size; ?>; mso-height-rule: exactly; line-height: 20px; color: <?php echo $email_text_color; ?>;">
                                    <div style="text-align:center;width:100%"><?php

                                        echo $main_content_a;

                                        ?></div>
                                    <br><br>
                                    <!-- Button : Begin -->
                                    <table cellspacing="0" cellpadding="0" border="0" align="center" style="Margin: auto;">
                                        <tr>
                                            <td style="border-radius: 3px; background: <?php echo $email_button_color; ?>; text-align: center;" class="button-td">
                                                <a href="<?php echo $email_button_link; ?>" style="background: <?php echo $email_button_color; ?>; border: 15px solid <?php echo $email_button_color; ?>; font-family: sans-serif; font-size: <?php echo $button_font_size; ?>; line-height: 1.1; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;<span style="color: <?php echo $email_button_text_color; ?>"><?php echo $email_button_text; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- Button : END -->
                                    <br>
                                    <?php
                                    $main_content_b = do_shortcode($main_content_b);
                                    echo strip_tags($main_content_b, '<div><table><tr><td><i><b><strong><br><p><a><style><script>');
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- Email Body : END -->
            <?php
            if ($show_latest_posts == 'yes') {
                $args = array(
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'category',
                            'field' => 'term_id',
                            'terms' => $show_latest_posts_category
                        )
                    ),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'post_staus' => 'published',
                    'limit' => (int)$show_latest_posts_limit + 1
                );

                $posts = get_posts($args);
                $count = 0;
                foreach ($posts as $post) {

                    if (!$count) {
                        $count++;
                        continue;
                    }

                    $permalink = get_the_permalink($post->ID);
                    $thumbnail = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
                    $excerpt = $post->post_object;
                    if (!$excerpt) {
                        $excerpt = wp_trim_words($post->post_content, 8);
                    }
                    ?>
                    <div style="height:25px"></div>
                    <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px; background-color:<?php echo $email_content_background_color; ?> ">
                        <tr>
                            <td align="center" height="100%" valign="top" width="100%" style="background-color: <?php echo $email_content_background_color; ?>;">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <?php
                                            $right_col_width = '100%';
                                            if ($thumbnail) {
                                                $right_col_width = "50%";
                                                ?>


                                                <!-- Start of left column -->
                                                <table width="280" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                    <tbody>
                                                    <!-- image -->
                                                    <tr>
                                                        <td width="280" height="220" align="center" class="devicewidth">
                                                            <img src="<?php echo $thumbnail; ?>" alt="" border="0" width="280" height="220" style="width:280px; max-width:280px; border:none; outline:none; text-decoration:none;" class="col2img">
                                                        </td>
                                                    </tr>
                                                    <!-- /image -->
                                                    </tbody>
                                                </table>
                                                <!-- end of left column -->
                                                <!-- spacing for mobile devices-->
                                                <table align="left" border="0" cellpadding="0" cellspacing="0" class="mobilespacing">
                                                    <tbody>
                                                    <tr>
                                                        <td width="100%" height="15" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">
                                                            &nbsp;</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <?php

                                            } ?>
                                            <!-- end of for mobile devices-->
                                            <!-- start of right column -->
                                            <table width="<?php echo $right_col_width; ?>" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth" style="padding-top:20px">
                                                            <tbody>
                                                            <!-- title -->
                                                            <tr>
                                                                <td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #282828; text-align:left; line-height: 24px;padding-top: 8px;padding-left:0px;;">
                                                                    <?php echo $post->post_title; ?>
                                                                </td>
                                                            </tr>
                                                            <!-- end of title -->
                                                            <!-- Spacing -->
                                                            <tr>
                                                            <tr>
                                                                <td width="100%" height="15" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">
                                                                    &nbsp;</td>
                                                            </tr>
                                                            <!-- /Spacing -->
                                                            <!-- content -->
                                                            <tr>
                                                                <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:left; line-height: 24px;padding-left:0px;">
                                                                    <?php echo strip_shortcodes($excerpt); ?>
                                                                </td>
                                                            </tr>
                                                            <!-- end of content -->
                                                            <!-- Spacing -->
                                                            <tr>
                                                                <td width="100%" height="15" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">
                                                                    &nbsp;</td>
                                                            </tr>
                                                            <!-- /Spacing -->
                                                            <!-- read more -->
                                                            <tr>
                                                                <td style="padding-left:0px;padding-bottom:10px;">
                                                                    <table width="120" height="32" bgcolor=" <?php echo $posts_button_color; ?>" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="border-radius:3px;" st-button="learnmore">
                                                                        <tbody>
                                                                        <tr>
                                                                            <td height="9" align="center" style="font-size:1px; line-height:1px;">
                                                                                &nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="14" align="center" valign="middle" style="background: <?php echo $posts_button_color; ?>;font-family: Helvetica, Arial, sans-serif; font-size: 13px; font-weight:bold;color: <?php echo $posts_button_text_color; ?>; text-align:center; line-height: 14px; ; -webkit-text-size-adjust:none;" st-title="fulltext-btn">
                                                                                <a style="text-decoration: none;color: <?php echo $posts_button_text_color; ?>; text-align:center;" href="<?php echo $permalink; ?>"><?php echo $posts_button_text; ?></a>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="9" align="center" style="font-size:1px; line-height:1px;">
                                                                                &nbsp;</td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <!-- end of read more -->
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <!-- end of right column -->
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>


                            </td>
                        </tr>
                    </table>
                    <?php
                }

            }
            ?>
            <!-- Hero Image, Flush : BEGIN -->


            <!-- Email Footer : BEGIN -->
            <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px; text-align: center;">
                <tr>
                    <td style="padding: 40px 10px;width: 100%; font-family: sans-serif; mso-height-rule: exactly; /*line-height:18px*/; text-align: center; color: <?php echo $contact_information_text_color; ?>;">
                        <div style="font-size:<?php echo $contact_information_font_size; ?>;  color: <?php echo $contact_information_text_color; ?>;" ;><?php echo $contact_information_content; ?></div>
                        <br><br>
                        <unsubscribe style="text-decoration:underline;">
                            <a href="<?php echo do_shortcode('[unsubscribe-link]'); ?>" style="color: <?php echo $unsubscribe_link_color; ?> ; font-size: <?php echo $unsubscribe_link_text_size; ?> ; text-decoration:none;margin: 0;padding: 0;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;"><?php echo $unsubscribe_link_text; ?></a>
                        </unsubscribe>

                    </td>
                </tr>
            </table>
            <!-- Email Footer : END -->

            <!--[if (gte mso 9)|(IE)]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </div>
    </center>
    <?php do_action('mailer/email/footer'); ?>
    </body>
    </html>


    <?php

endwhile; endif;
