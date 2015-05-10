<?php
include( 'shortcodes-fields.php' );
$popup = trim( $_GET['popup'] );

$shortcode = new Inbound_Shortcodes_Fields( $popup );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head></head>
<body>
    <div id="inbound-shortcodes-popup">

        <div id="inbound-shortcodes-wrap">
            <div id="inbound-shortcodes-form-wrap">
                <div id="inbound-shortcodes-form-head">
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
                                <td class="field" style="width:500px;"><a href="#" id="inbound_insert_shortcode" class="button-primary inbound-shortcodes-insert"><?php _e('Insert Shortcode', 'leads'); ?></a></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>

            <div id="inbound-shortcodes-preview-wrap">
                <div id="inbound-shortcodes-preview-head">
                    <?php _e('Shortcode Preview', 'leads'); ?>
                </div>
                <?php if( $shortcode->no_preview ) : ?>
                    <div id="inbound-shortcodes-nopreview"><?php _e('Shortcode has no preview', 'leads'); ?></div>
                <?php else : ?>
                    <iframe src="<?php echo INBOUND_FORMS; ?>preview.php?sc=" width="285" scrollbar='true' frameborder="0" id="inbound-shortcodes-preview"></iframe>
                <?php endif; ?>
            </div>
            <div class="clear"></div>
        </div>

    </div>
    <div id="popup-controls">
        <a href="#" id="inbound_save_form" style="display:none;" class="button-primary">Save Form & Insert</a>
        <a href="#" id="inbound_insert_shortcode_two" class="button-primary inbound-shortcodes-insert-two"><?php _e('Insert Shortcode', 'leads'); ?></a>
        <a href="#" id="shortcode_cancel" class="button inbound-shortcodes-insert-cancel">Cancel</a>

    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {

        jQuery('.child-clone-row').first().attr('id', 'row-1');
        setTimeout(function() {
                jQuery('#inbound-shortcodes-form input:visible').first().focus();
        }, 500);

    //jQuery("body").on('click', '.child-clone-row', function () {
       // jQuery(".child-clone-row").toggle();
       // jQuery(this).show();
    //});
    });
</script>
</body>
</html>
