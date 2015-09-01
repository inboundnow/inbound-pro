<?php
/**
 * This is the original template. It has been left here so that it can be modified in the future.
 * The html of this file must be passed through an inliner in order to work correctly.
 * Having the css with the html in the same file is not enough because there are email clients that strip the css code out.
 * The html of this template has been passed through http://templates.mailchimp.com/resources/inline-css/
 * After the html is passed through the inliner it's necessary to check and fix the php inside the html because the inliner turns '<' and '>' characters in their html entities
 * The result is the index.php file that produces the actual email code.
 * 
 * Template Name: Gallery
 * @package  Inbound Email
 * @author   Inbound Now
*/

/* Declare Template Key */
$key = basename(dirname(__FILE__));

/* do global action */
do_action('inbound_mail_header');

/* Load post */
if (have_posts()) : while (have_posts()) : the_post();

$post_id		 = get_the_ID();

/* Main content */
$logo_url		 = get_field('logo_url', $post_id);
$header_bg_color = get_field('header_bg_color', $post_id);
$header_bg_image = get_field('header_bg_image', $post_id);
$home_page_url	 = get_field('home_page_url', $post_id);

/* Email Body */
$email_title	= get_field('email_title', $post_id);
$email_bg_color = get_field('email_bg_color', $post_id);
$bg_color_dec	= hexdec(substr($email_bg_color, 1));
$divider_color	= dechex($bg_color_dec - 1184274);

/* Footer */
$terms_page_url    = get_field('terms_page_url', $post_id);
$privacy_page_url  = get_field('privacy_page_url', $post_id);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width" />
        <!--<link rel="stylesheet" href="ink.css"> -->         
        <!-- For testing only -->
        <style type="text/css">
            /**********************************************
            * Ink v1.0.5 - Copyright 2013 ZURB Inc        *
            **********************************************/
            /* Client-specific Styles & Reset */
            #outlook a
            {
            padding: 0;
            }
            body
            {
            width: 100% !important;
            min-width: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            }
            .ExternalClass
            {
            width: 100%;
            }
            .ExternalClass, 
            .ExternalClass p, 
            .ExternalClass span, 
            .ExternalClass font, 
            .ExternalClass td, 
            .ExternalClass div
            {
            line-height: 100%;
            }
            #backgroundTable
            {
            margin: 0;
            padding: 0;
            width: 100% !important;
            line-height: 100% !important;
            }
            img
            {
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            width: auto;
            max-width: 100%;
            float: left;
            clear: both;
            display: block;
            }
            center
            {
            width: 100%;
            min-width: 580px;
            }
            a img
            {
            border: none;
            }
            p
            {
            margin: 0 0 0 10px;
            }
            table
            {
            border-spacing: 0;
            border-collapse: collapse;
            }
            td
            {
            word-break: break-word;
            -webkit-hyphens: auto;
            -moz-hyphens: auto;
            hyphens: auto;
            border-collapse: collapse !important;
            }
            table, tr, td
            {
            padding: 0;
            text-align: left;
            }
            hr
            {
            color: #d9d9d9;
            background-color: #d9d9d9;
            height: 1px;
            border: none;
            }
            /* Responsive Grid */
            table.body
            {
            height: 100%;
            width: 100%;
            }
            table.container
            {
            width: 580px;
            margin: 0 auto;
            text-align: inherit;
            }
            table.row
            {
            padding: 0px;
            width: 100%;
            position: relative;
            }
            table.container table.row
            {
            display: block;
            }
            td.wrapper
            {
            padding: 10px 20px 0px 0px;
            position: relative;
            }
            table.columns,
            table.column
            {
            margin: 0 auto;
            }
            table.columns td,
            table.column td
            {
            padding: 0px 0px 10px;
            }
            table.columns td.sub-columns,
            table.column td.sub-columns,
            table.columns td.sub-column,
            table.column td.sub-column
            {
            padding-right: 10px;
            }
            td.sub-column, td.sub-columns
            {
            min-width: 0px;
            }
            table.row td.last,
            table.container td.last
            {
            padding-right: 0px;
            }
            table.one
            {
            width: 30px;
            }
            table.two
            {
            width: 80px;
            }
            table.three
            {
            width: 130px;
            }
            table.four
            {
            width: 180px;
            }
            table.five
            {
            width: 230px;
            }
            table.six
            {
            width: 280px;
            }
            table.seven
            {
            width: 330px;
            }
            table.eight
            {
            width: 380px;
            }
            table.nine
            {
            width: 430px;
            }
            table.ten
            {
            width: 480px;
            }
            table.eleven
            {
            width: 530px;
            }
            table.twelve
            {
            width: 580px;
            }
            table.one center
            {
            min-width: 30px;
            }
            table.two center
            {
            min-width: 80px;
            }
            table.three center
            {
            min-width: 130px;
            }
            table.four center
            {
            min-width: 180px;
            }
            table.five center
            {
            min-width: 230px;
            }
            table.six center
            {
            min-width: 280px;
            }
            table.seven center
            {
            min-width: 330px;
            }
            table.eight center
            {
            min-width: 380px;
            }
            table.nine center
            {
            min-width: 430px;
            }
            table.ten center
            {
            min-width: 480px;
            }
            table.eleven center
            {
            min-width: 530px;
            }
            table.twelve center
            {
            min-width: 580px;
            }
            table.one .panel center
            {
            min-width: 10px;
            }
            table.two .panel center
            {
            min-width: 60px;
            }
            table.three .panel center
            {
            min-width: 110px;
            }
            table.four .panel center
            {
            min-width: 160px;
            }
            table.five .panel center
            {
            min-width: 210px;
            }
            table.six .panel center
            {
            min-width: 260px;
            }
            table.seven .panel center
            {
            min-width: 310px;
            }
            table.eight .panel center
            {
            min-width: 360px;
            }
            table.nine .panel center
            {
            min-width: 410px;
            }
            table.ten .panel center
            {
            min-width: 460px;
            }
            table.eleven .panel center
            {
            min-width: 510px;
            }
            table.twelve .panel center
            {
            min-width: 560px;
            }
            .body .columns td.one,
            .body .column td.one
            {
            width: 8.333333%;
            }
            .body .columns td.two,
            .body .column td.two
            {
            width: 16.666666%;
            }
            .body .columns td.three,
            .body .column td.three
            {
            width: 25%;
            }
            .body .columns td.four,
            .body .column td.four
            {
            width: 33.333333%;
            }
            .body .columns td.five,
            .body .column td.five
            {
            width: 41.666666%;
            }
            .body .columns td.six,
            .body .column td.six
            {
            width: 50%;
            }
            .body .columns td.seven,
            .body .column td.seven
            {
            width: 58.333333%;
            }
            .body .columns td.eight,
            .body .column td.eight
            {
            width: 66.666666%;
            }
            .body .columns td.nine,
            .body .column td.nine
            {
            width: 75%;
            }
            .body .columns td.ten,
            .body .column td.ten
            {
            width: 83.333333%;
            }
            .body .columns td.eleven,
            .body .column td.eleven
            {
            width: 91.666666%;
            }
            .body .columns td.twelve,
            .body .column td.twelve
            {
            width: 100%;
            }
            td.offset-by-one
            {
            padding-left: 50px;
            }
            td.offset-by-two
            {
            padding-left: 100px;
            }
            td.offset-by-three
            {
            padding-left: 150px;
            }
            td.offset-by-four
            {
            padding-left: 200px;
            }
            td.offset-by-five
            {
            padding-left: 250px;
            }
            td.offset-by-six
            {
            padding-left: 300px;
            }
            td.offset-by-seven
            {
            padding-left: 350px;
            }
            td.offset-by-eight
            {
            padding-left: 400px;
            }
            td.offset-by-nine
            {
            padding-left: 450px;
            }
            td.offset-by-ten
            {
            padding-left: 500px;
            }
            td.offset-by-eleven
            {
            padding-left: 550px;
            }
            td.expander
            {
            visibility: hidden;
            width: 0px;
            padding: 0 !important;
            }
            table.columns .text-pad,
            table.column .text-pad
            {
            padding-left: 10px;
            padding-right: 10px;
            }
            table.columns .left-text-pad,
            table.columns .text-pad-left,
            table.column .left-text-pad,
            table.column .text-pad-left
            {
            padding-left: 10px;
            }
            table.columns .right-text-pad,
            table.columns .text-pad-right,
            table.column .right-text-pad,
            table.column .text-pad-right
            {
            padding-right: 10px;
            }
            /* Block Grid */
            .block-grid
            {
            width: 100%;
            max-width: 580px;
            }
            .block-grid td
            {
            display: inline-block;
            padding: 10px;
            }
            .two-up td
            {
            width: 270px;
            }
            .three-up td
            {
            width: 173px;
            }
            .four-up td
            {
            width: 125px;
            }
            .five-up td
            {
            width: 96px;
            }
            .six-up td
            {
            width: 76px;
            }
            .seven-up td
            {
            width: 62px;
            }
            .eight-up td
            {
            width: 52px;
            }
            /* Alignment & Visibility Classes */
            table.center, td.center
            {
            text-align: center;
            }
            h1.center,
            h2.center,
            h3.center,
            h4.center,
            h5.center,
            h6.center
            {
            text-align: center;
            }
            span.center
            {
            display: block;
            width: 100%;
            text-align: center;
            }
            img.center
            {
            margin: 0 auto;
            float: none;
            }
            .show-for-small,
            .hide-for-desktop
            {
            display: none;
            }
            /* Typography */
            body, table.body, h1, h2, h3, h4, h5, h6, p, td
            {
            color: #222222;
            font-family: "Helvetica", "Arial", sans-serif;
            font-weight: normal;
            padding: 0;
            margin: 0;
            text-align: left;
            line-height: 1.3;
            }
            h1, h2, h3, h4, h5, h6
            {
            word-break: normal;
            }
            h1
            {
            font-size: 40px;
            }
            h2
            {
            font-size: 36px;
            }
            h3
            {
            font-size: 32px;
            }
            h4
            {
            font-size: 28px;
            }
            h5
            {
            font-size: 24px;
            }
            h6
            {
            font-size: 20px;
            }
            body, table.body, p, td
            {
            font-size: 14px;
            line-height: 19px;
            }
            p.lead, p.lede, p.leed
            {
            font-size: 18px;
            line-height: 21px;
            }
            p
            {
            margin-bottom: 10px;
            }
            small
            {
            font-size: 10px;
            }
            a
            {
            color: #2ba6cb;
            text-decoration: none;
            }
            a:hover
            {
            color: #2795b6 !important;
            }
            a:active
            {
            color: #2795b6 !important;
            }
            a:visited
            {
            color: #2ba6cb !important;
            }
            h1 a, 
            h2 a, 
            h3 a, 
            h4 a, 
            h5 a, 
            h6 a
            {
            color: #2ba6cb;
            }
            h1 a:active, 
            h2 a:active,  
            h3 a:active, 
            h4 a:active, 
            h5 a:active, 
            h6 a:active
            {
            color: #2ba6cb !important;
            }
            h1 a:visited, 
            h2 a:visited,  
            h3 a:visited, 
            h4 a:visited, 
            h5 a:visited, 
            h6 a:visited
            {
            color: #2ba6cb !important;
            }
            /* Panels */
            .panel
            {
            background: #f2f2f2;
            border: 1px solid #d9d9d9;
            padding: 10px !important;
            }
            .sub-grid table
            {
            width: 100%;
            }
            .sub-grid td.sub-columns
            {
            padding-bottom: 0;
            }
            /* Buttons */
            table.button,
            table.tiny-button,
            table.small-button,
            table.medium-button,
            table.large-button
            {
            width: 100%;
            overflow: hidden;
            }
            table.button td,
            table.tiny-button td,
            table.small-button td,
            table.medium-button td,
            table.large-button td
            {
            display: block;
            width: auto !important;
            text-align: center;
            background: #2ba6cb;
            border: 1px solid #2284a1;
            color: #ffffff;
            padding: 8px 0;
            }
            table.tiny-button td
            {
            padding: 5px 0 4px;
            }
            table.small-button td
            {
            padding: 8px 0 7px;
            }
            table.medium-button td
            {
            padding: 12px 0 10px;
            }
            table.large-button td
            {
            padding: 21px 0 18px;
            }
            table.button td a,
            table.tiny-button td a,
            table.small-button td a,
            table.medium-button td a,
            table.large-button td a
            {
            font-weight: bold;
            text-decoration: none;
            font-family: Helvetica, Arial, sans-serif;
            color: #ffffff;
            font-size: 16px;
            }
            table.tiny-button td a
            {
            font-size: 12px;
            font-weight: normal;
            }
            table.small-button td a
            {
            font-size: 16px;
            }
            table.medium-button td a
            {
            font-size: 20px;
            }
            table.large-button td a
            {
            font-size: 24px;
            }
            table.button:hover td,
            table.button:visited td,
            table.button:active td
            {
            background: #2795b6 !important;
            }
            table.button:hover td a,
            table.button:visited td a,
            table.button:active td a
            {
            color: #fff !important;
            }
            table.button:hover td,
            table.tiny-button:hover td,
            table.small-button:hover td,
            table.medium-button:hover td,
            table.large-button:hover td
            {
            background: #2795b6 !important;
            }
            table.button:hover td a,
            table.button:active td a,
            table.button td a:visited,
            table.tiny-button:hover td a,
            table.tiny-button:active td a,
            table.tiny-button td a:visited,
            table.small-button:hover td a,
            table.small-button:active td a,
            table.small-button td a:visited,
            table.medium-button:hover td a,
            table.medium-button:active td a,
            table.medium-button td a:visited,
            table.large-button:hover td a,
            table.large-button:active td a,
            table.large-button td a:visited
            {
            color: #ffffff !important;
            }
            table.secondary td
            {
            background: #e9e9e9;
            border-color: #d0d0d0;
            color: #555;
            }
            table.secondary td a
            {
            color: #555;
            }
            table.secondary:hover td
            {
            background: #d0d0d0 !important;
            color: #555;
            }
            table.secondary:hover td a,
            table.secondary td a:visited,
            table.secondary:active td a
            {
            color: #555 !important;
            }
            table.success td
            {
            background: #5da423;
            border-color: #457a1a;
            }
            table.success:hover td
            {
            background: #457a1a !important;
            }
            table.alert td
            {
            background: #c60f13;
            border-color: #970b0e;
            }
            table.alert:hover td
            {
            background: #970b0e !important;
            }
            table.radius td
            {
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            }
            table.round td
            {
            -webkit-border-radius: 500px;
            -moz-border-radius: 500px;
            border-radius: 500px;
            }
            /* Outlook First */
            body.outlook p
            {
            display: inline !important;
            }
            /*  Media Queries */
            @media only screen and (max-width: 600px)
            {
            table[class="body"] img
            {
            width: auto !important;
            height: auto !important;
            }
            table[class="body"] center
            {
            min-width: 0 !important;
            }
            table[class="body"] .container
            {
            width: 95% !important;
            }
            table[class="body"] .row
            {
            width: 100% !important;
            display: block !important;
            }
            table[class="body"] .wrapper
            {
            display: block !important;
            padding-right: 0 !important;
            }
            table[class="body"] .columns,
            table[class="body"] .column
            {
            table-layout: fixed !important;
            float: none !important;
            width: 100% !important;
            padding-right: 0px !important;
            padding-left: 0px !important;
            display: block !important;
            }
            table[class="body"] .wrapper.first .columns,
            table[class="body"] .wrapper.first .column
            {
            display: table !important;
            }
            table[class="body"] table.columns td,
            table[class="body"] table.column td
            {
            width: 100% !important;
            }
            table[class="body"] .columns td.one,
            table[class="body"] .column td.one
            {
            width: 8.333333% !important;
            }
            table[class="body"] .columns td.two,
            table[class="body"] .column td.two
            {
            width: 16.666666% !important;
            }
            table[class="body"] .columns td.three,
            table[class="body"] .column td.three
            {
            width: 25% !important;
            }
            table[class="body"] .columns td.four,
            table[class="body"] .column td.four
            {
            width: 33.333333% !important;
            }
            table[class="body"] .columns td.five,
            table[class="body"] .column td.five
            {
            width: 41.666666% !important;
            }
            table[class="body"] .columns td.six,
            table[class="body"] .column td.six
            {
            width: 50% !important;
            }
            table[class="body"] .columns td.seven,
            table[class="body"] .column td.seven
            {
            width: 58.333333% !important;
            }
            table[class="body"] .columns td.eight,
            table[class="body"] .column td.eight
            {
            width: 66.666666% !important;
            }
            table[class="body"] .columns td.nine,
            table[class="body"] .column td.nine
            {
            width: 75% !important;
            }
            table[class="body"] .columns td.ten,
            table[class="body"] .column td.ten
            {
            width: 83.333333% !important;
            }
            table[class="body"] .columns td.eleven,
            table[class="body"] .column td.eleven
            {
            width: 91.666666% !important;
            }
            table[class="body"] .columns td.twelve,
            table[class="body"] .column td.twelve
            {
            width: 100% !important;
            }
            table[class="body"] td.offset-by-one,
            table[class="body"] td.offset-by-two,
            table[class="body"] td.offset-by-three,
            table[class="body"] td.offset-by-four,
            table[class="body"] td.offset-by-five,
            table[class="body"] td.offset-by-six,
            table[class="body"] td.offset-by-seven,
            table[class="body"] td.offset-by-eight,
            table[class="body"] td.offset-by-nine,
            table[class="body"] td.offset-by-ten,
            table[class="body"] td.offset-by-eleven
            {
            padding-left: 0 !important;
            }
            table[class="body"] table.columns td.expander
            {
            width: 1px !important;
            }
            table[class="body"] .right-text-pad,
            table[class="body"] .text-pad-right
            {
            padding-left: 10px !important;
            }
            table[class="body"] .left-text-pad,
            table[class="body"] .text-pad-left
            {
            padding-right: 10px !important;
            }
            table[class="body"] .hide-for-small,
            table[class="body"] .show-for-desktop
            {
            display: none !important;
            }
            table[class="body"] .show-for-small,
            table[class="body"] .hide-for-desktop
            {
            display: inherit !important;
            }
            }








</style>
        <style type="text/css">
            /* Your custom styles go here */

.view-website
{
    vertical-align: middle;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #555555;
    line-height: 20px;
}

.trouble-viewing
{
    text-align: center;
}

.header-top-gap
{
    height: 20px;
}

.header-bottom-gap
{
    height: 20px;
}

.logo-cell
{
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    vertical-align: middle;
}

.header-line
{
    width: 100%;
    max-width: 600px;
}

.trouble-view-cell
{
    margin: 0;
    padding: 0;
    background-color: #efefef;
}

.main-header
{
    height: 100px;
    max-height: 100px;
}

.main-header img
{
    max-height: 100px;
}

.logo-cell a
{
    display: inline-block;
}

.before-title
{
    height: 30px;
}

.title-cell
{
    height: 18px;
    padding-top: 0 !important;
    padding-bottom: 20px !important;
}

.main-title h2
{
    font-size: 20px;
    font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
    line-height: 18px;
    color: #777777;
    margin: 0;
    padding: 0 0 0 5px;
    text-align: left;
    font-weight: bold;
}

.after-title
{
    height: 30px;
    padding-top: 0 !important;
}

.gallery-cell
{
    padding-top: 0 !important;
}

.wrapper.last.gallery-cell
{
    padding-left: 8px;
    padding-right: 2px;
}

.wrapper.gallery-cell.left
{
    padding-left: 2px;
    padding-right: 8px;
}

.vert-gap
{
    height: 10px;
}

.cell-caption
{
    height: 40px;
}

.cell-caption.left
{
    padding-right: 10px;
}

.cell-caption.last
{
    padding-left: 10px;
}

.gallery-gap
{
    height: 30px;
}

.callout
{
}

.gallery a img
{
    height: 200px;
    width: 270px;
    border: 4px solid #ffffff;
}

.caption a
{
    text-align: left;
    text-decoration: none;
    color: #565249;
    font-weight: bold;
}

.callout-cell
{
    background-color: <?php echo $callout_bg_color; ?>;
}

.gallery.right
{
    padding-bottom: 0;
}

.gallery.left
{
    padding-bottom: 0;
}

.callout-cell .callout
{
    padding: 25px 35px 35px;
    text-align: center;
}

.callout-button-cell
{
    text-align: center;
    height: 49px;
    font-family: 'helvetica neue',helvetica,arial,sans-serif;
    font-size: 20px;
    background-color: <?php echo $callout_button_color; ?>;
    vertical-align: middle;
    text-decoration: none;
}

.callout-button-container
{
    margin: 25px auto 0;
    border: 0;
}

.callout-button-cell a
{
    color: <?php echo $callout_button_font_color; ?>;
    padding: 10px 20px;
}

.callout .callout-button-cell
{
    padding-bottom: 0;
}

.callout-text
{
    font-family: 'helvetica neue',helvetica,arial,sans-serif;
    font-size: <?php echo $callout_font_size; ?>;
    color: <?php echo $callout_font_color; ?>;
}

.footer-cell
{
    text-align: center;
    margin-left: auto;
    margin-right: auto;
	border-top: 1px solid #<?php echo $divider_color; ?>;
}

.footer-cell p
{
    text-align: center;
    text-decoration: none;
}

.footer-cell p a
{
    color: #8f8a83;
    text-decoration: none;
}

.footer-container
{
    background-color: <?php echo $email_bg_color; ?>;
}

.header-cell
{
    background-image: url('<?php echo $header_bg_image; ?>');
    background-size: cover;
    background-color: <?php echo $header_bg_color; ?>;
}

.email-body-cell
{
    background-color: <?php echo $email_bg_color; ?>;
}

.header-cell .header-row .main-header table.ten.columns {
	vertical-align: middle;
}


</style>
    </head>
     <body class="container" style="min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;width: 100% !important;">
        <table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;">
            <tr style="padding: 0;text-align: left;">
                <td class="center" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;text-align: center;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
                    <table class="container" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: inherit;width: 580px;margin: 0 auto;">
                        <tr style="padding: 0;text-align: left;">
                            <td class="wrapper last trouble-view-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;background-color: #efefef;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr style="padding: 0;text-align: left;">
                                        <td class="center" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: center;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
                                            <div class="view-website" style="vertical-align: middle;font-family: Arial, Helvetica, sans-serif;font-size: 10px;color: #555555;line-height: 20px;">
                                                Trouble viewing? Read this 
                                                <a href="<?php echo get_permalink( $post_id ); ?>" style="color: #990000;text-decoration: none;" class="do-not-tracks">online</a>.
                                            </div>
                                        </td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="container header" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: inherit;width: 580px;margin: 0 auto;">
            <tr class="" style="padding: 0;text-align: left;">
                <td class="header-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;background-image: url(<?php echo $header_bg_image;?>): ;background-size: cover;background-color: <?php echo $header_bg_color;?>: ;border-collapse: collapse !important;">
                    <table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper last header-top-gap" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 20px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <table class="header-row row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper main-header" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 100px;max-height: 100px;position: relative;border-collapse: collapse !important;">
                                <table class="one columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 30px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="wrapper main-header" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 100px;max-height: 100px;position: relative;border-collapse: collapse !important;">
                                <table class="ten columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 480px;vertical-align: middle;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="logo-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: center;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;margin-left: auto;margin-right: auto;vertical-align: middle;border-collapse: collapse !important;">
                                            <a name="anchor" href="<?php echo $home_page_url; ?>" style="color: #2ba6cb;text-decoration: none;display: inline-block;">
                                                <img src="<?php echo $logo_url; ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: auto;max-width: 100%;float: left;clear: both;display: block;border: none;max-height: 100px;">
                                            </a>
                                        </td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="wrapper last main-header" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 100px;max-height: 100px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="one columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 30px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper last header-bottom-gap" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 20px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="container mail-body" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: inherit;width: 580px;margin: 0 auto;">
            <tr class="" style="padding: 0;text-align: left;">
                <td class="email-body-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;background-color: <?php echo $email_bg_color;?>: ;border-collapse: collapse !important;">
                    <table class="row mail-title" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper last before-title" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 30px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper last title-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 18px;position: relative;padding-right: 0px;border-collapse: collapse !important;padding-top: 0 !important;padding-bottom: 20px !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="main-title left-text-pad" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-left: 10px;border-collapse: collapse !important;">
                                            <h2 style="color: #777777;font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight: bold;padding: 0 0 0 5px;margin: 0;text-align: left;line-height: 18px;word-break: normal;font-size: 16px;"><?php echo $email_title; ?></h2>
                                        </td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
					
					<?php
						if ( function_exists('have_rows') ) {
							if (have_rows('gallery')) {
								while ( have_rows('gallery')) {
									the_row();

									switch( get_row_layout() ) {
										case 'gallery_row':
											$left_image				= get_sub_field('left_image');
											$left_image_url			= get_sub_field('left_image_url');
											$left_image_title		= get_sub_field('left_image_title');
											$left_image_title_url	= get_sub_field('left_image_title_url');
											$left_image_author		= get_sub_field('left_image_author');
											$right_image			= get_sub_field('right_image');
											$right_image_url		= get_sub_field('right_image_url');
											$right_image_title		= get_sub_field('right_image_title');
											$right_image_title_url	= get_sub_field('right_image_title_url');
											$right_image_author		= get_sub_field('right_image_author');
											?>
											<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
												<tr class="" style="padding: 0;text-align: left;">
													<td class="wrapper gallery-cell left" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-left: 2px;padding-right: 8px;border-collapse: collapse !important;padding-top: 0 !important;">
														<table class="six columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 280px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="gallery left" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-bottom: 0;border-collapse: collapse !important;">
																	<a name="anchor" href="<?php echo $left_image_url; ?>" style="color: #2ba6cb;text-decoration: none;">
																		<img src="<?php echo $left_image; ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 270px;max-width: 100%;float: left;clear: both;display: block;border: 4px solid #ffffff;height: 200px;">
																	</a>                                                                                                                                                                                                                                                                                                                                                                                                                                                        &nbsp;
																</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
													<td class="wrapper last gallery-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 2px;padding-left: 8px;border-collapse: collapse !important;padding-top: 0 !important;">
														<table class="six columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 280px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="gallery right" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-bottom: 0;border-collapse: collapse !important;">
																	<a name="anchor" href="<?php echo $right_image_url; ?>" style="color: #2ba6cb;text-decoration: none;">
																		<img src="<?php echo $right_image; ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 270px;max-width: 100%;float: left;clear: both;display: block;border: 4px solid #ffffff;height: 200px;">
																	</a>                                                                                                                                                                                                                                                                                                                                                                                                                                                        &nbsp;
																</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
												<tr class="" style="padding: 0;text-align: left;">
													<td class="wrapper cell-caption left" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 40px;position: relative;padding-right: 10px;border-collapse: collapse !important;">
														<table class="six columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 280px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="caption left-text-pad" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-left: 10px;border-collapse: collapse !important;">
																	<a name="anchor" href="<?php echo $left_image_title_url; ?>" style="color: #565249 !important;text-decoration: none;text-align: left;font-weight: bold;"><?php echo $left_image_title; ?></a>
																	<br>
																	<span style="font-size: 12px;"><?php echo $left_image_author; ?></span>
																</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
													<td class="wrapper last cell-caption" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 40px;position: relative;padding-left: 10px;padding-right: 0px;border-collapse: collapse !important;">
														<table class="six columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 280px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="caption left-text-pad" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;padding-left: 10px;border-collapse: collapse !important;">
																	<a name="anchor" href="<?php echo $right_image_title_url; ?>" style="color: #565249 !important;text-decoration: none;text-align: left;font-weight: bold;"><?php echo $right_image_title; ?></a>
																	<br>
																	<span style="font-size: 12px;"><?php echo $right_image_author; ?></span>
																</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
												<tr class="" style="padding: 0;text-align: left;">
													<td class="wrapper last gallery-gap" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 30px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
														<table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<?php
											break;
										
										case 'callout':
											$callout_message			= get_sub_field('callout_message');
											$callout_font_size			= get_sub_field('callout_font_size');
											$callout_font_color			= get_sub_field('callout_font_color');
											$callout_bg_color			= get_sub_field('callout_bg_color');
											$callout_button_message		= get_sub_field('callout_button_message');
											$callout_button_link		= get_sub_field('callout_button_link');
											$callout_button_color		= get_sub_field('callout_button_color');
											$callout_button_font_color	= get_sub_field('callout_button_font_color');
											?>
											<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
												<tr class="" style="padding: 0;text-align: left;">
													<td class="wrapper last callout-cell" style="background-color: <?php echo $callout_bg_color; ?>;word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px; ;position: relative;padding-right: 0px;border-collapse: collapse !important;">
														<table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="callout" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 25px 35px 35px;text-align: center;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
																	<span class="callout-text" style="font-family: 'helvetica neue',helvetica,arial,sans-serif;font-size: <?php echo $callout_font_size;?>px ;color: <?php echo $callout_font_color;?>"><?php echo $callout_message; ?></span>
																	<table class="callout-button-container" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 25px auto 0;border: 0;">
																		<tr class="" style="padding: 0;text-align: left;">
																			<td class="callout-button-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: center;font-family: 'helvetica neue',helvetica,arial,sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 20px;height: 49px;background-color: <?php echo $callout_button_color;?>;vertical-align: middle;text-decoration: none;padding-bottom: 0;border-collapse: collapse !important;">
																				<a name="anchor" href="<?php echo $callout_button_link; ?>" style="padding: 10px 20px; color: <?php echo $callout_button_font_color;?> !important;text-decoration: none; ;"><?php echo $callout_button_message; ?></a>
																			</td>
																		</tr>
																	</table>
																</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
												<tr class="" style="padding: 0;text-align: left;">
													<td class="wrapper last gallery-gap" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;height: 30px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
														<table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
															<tr class="" style="padding: 0;text-align: left;">
																<td class="" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">&nbsp;</td>
																<td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<?php
											break;
									}
								}
							}
						}
						?>										
                </td>
            </tr>
        </table>
        <table class="footer-container container" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: inherit;background-color: <?php echo $email_bg_color;?>: ;width: 580px;margin: 0 auto;">
            <tr class="" style="padding: 0;text-align: left;">
                <td class="center" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;text-align: center;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
                    <table class="row" style="border-spacing: 0;border-collapse: collapse;padding: 0px;text-align: left;width: 100%;position: relative;display: block;">
                        <tr class="" style="padding: 0;text-align: left;">
                            <td class="wrapper last" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 10px 20px 0px 0px;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;position: relative;padding-right: 0px;border-collapse: collapse !important;">
                                <table class="twelve columns" style="border-spacing: 0;border-collapse: collapse;padding: 0;text-align: left;margin: 0 auto;width: 580px;">
                                    <tr class="" style="padding: 0;text-align: left;">
                                        <td class="center footer-cell" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px 0px 10px;text-align: center;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;margin-left: auto;margin-right: auto;border-top: 1px solid #<?php echo $divider_color;?>: ;border-collapse: collapse !important;">
                                            <p style="margin: 0;margin-bottom: 10px;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;padding: 0;text-align: center;line-height: 19px;font-size: 14px;text-decoration: none;">
												<?php 
												if ( $terms_page_url ) { 
													?><a href="<?php echo $terms_page_url; ?>" style="color: #8f8a83;text-decoration: none;">Terms</a> |
												<?php } ?>
												<?php 
												if ( $privacy_page_url ) { 
													?><a href="<?php echo $privacy_page_url; ?>" style="color: #8f8a83;text-decoration: none;">Privacy</a> |
												<?php } 
												?><a href="<?php echo do_shortcode('[unsubscribe-link]'); ?>" style="color: #8f8a83;text-decoration: none;">
                                                    <?php _e('Unsubscribe from this list' , 'inbound-mailer' ); ?>
                                                </a></p>
                                        </td>
                                        <td class="expander" style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0 !important;text-align: left;color: #222222;font-family: 'Helvetica', 'Arial', sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;visibility: hidden;width: 0px;border-collapse: collapse !important;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php

endwhile; endif;