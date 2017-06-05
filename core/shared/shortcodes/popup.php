<?php

include( 'shortcodes-fields.php' );
$popup = trim( sanitize_text_field($_GET['popup']) );

$shortcode = new Inbound_Shortcodes_Fields( $popup );

if( !$shortcode->no_preview ) {
    $style = "";
} else {
    $style = "width:100%;";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!--<link rel="stylesheet" type="text/css" href="../shortcodes/css/shortcodes.css" media="all" />-->

</head>
<body>
    <div id="inbound-shortcodes-popup">

        <div id="inbound-shortcodes-wrap">

            <div id="inbound-shortcodes-form-wrap" style="<?php echo $style;?>">
                <div id="inbound-shortcodes-form-head">
                <span class="marketing-back-button">⬅ Go Back </span>
                    <?php echo $shortcode->popup_title; ?>
                    <?php $shortcode_id = strtolower(str_replace(array(' ','-'),'_', $shortcode->popup_title));  ?>
                </div>
                <form method="post" id="inbound-shortcodes-form">
                    <input type="hidden" id="inbound_current_shortcode" value="<?php echo $shortcode_id;?>">
                    <table id="inbound-shortcodes-form-table">
                        <?php echo $shortcode->output; ?>
                        <tbody style="display:none;">
                            <tr class="form-row" style="text-align: center;">
                                <?php if( ! $shortcode->has_child ) : ?><td class="label">&nbsp;</td><?php endif; ?>
                                <td class="field" style="width:500px;"><a href="#" id="inbound_insert_shortcode" class="button-primary inbound-shortcodes-insert"><?php _e('Insert Shortcode', 'inbound-pro' ); ?></a></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>


            <?php if( !$shortcode->no_preview ) { ?>
            <div id="inbound-shortcodes-preview-wrap">
                <div id="inbound-shortcodes-preview-head">
                    <?php _e('Shortcode Preview', 'inbound-pro' ); ?>
                </div>

                <iframe src="<?php echo INBOUND_FORMS; ?>preview.php?sc=" width="285" scrollbar='true' frameborder="0" id="inbound-shortcodes-preview"></iframe>

            </div>
            <?php } ?>
            <div class="clear"></div>

            <div id="marketing-popup-controls" style="z-index: 999999;">
                    <a href="#" id="marketing-insert-shortcode" class="button-primary">
                        Insert Shortcode
                    </a>
                    <a href="#" id="cancel_marketing_button" class="button">
                        Cancel
                    </a>
            </div>

        </div>
    </div>
</body>
</html>
