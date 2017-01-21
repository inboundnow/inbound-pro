jQuery(document).ready(function () {

    if (inboundWPAdmin.counts.extensions > 0) {
        var outterSpan = jQuery('<span>').addClass('update-plugins count-5');
        var innerSpan = jQuery('<span>').addClass('plugin-count').text(inboundWPAdmin.counts.extensions);
        outterSpan.append(innerSpan);
        outterSpan.css('margin-left', '5px');
        outterSpan.appendTo('a[href$="admin.php?page=inbound-manage-extensions"]');
        jQuery('input[value="needs-update"]').click();
    }

    if (inboundWPAdmin.counts.templates > 0) {
        var outterSpan = jQuery('<span>').addClass('update-plugins count-5');
        var innerSpan = jQuery('<span>').addClass('plugin-count').text(inboundWPAdmin.counts.extensions);
        outterSpan.append(innerSpan);
        outterSpan.css('margin-left', '5px');
        outterSpan.appendTo('a[href$="admin.php?page=inbound-manage-templates"]');
        jQuery('input[value="needs-update"]').click();
    }

    /* Add navigation to thickbox if contains class inbound-thickbox */
    if (!inboundWPAdmin.tb_hide_nav) {

        jQuery(document).on('thickbox:iframe:loaded', '#TB_window', function (e) {

            var nav_container = jQuery('<div>').attr('id', 'inbound-thickbox-nav').css('position', 'absolute');

            var back_button = jQuery('<span>').addClass('tb_window_nav').html('<i class="fa fa-step-backward" aria-hidden="true"></i>').click(function () {
                var thickboxFrame = document.getElementById("TB_iframeContent");
                thickboxFrame.contentWindow.history.back();
            });

            var forward_button = jQuery('<span>').addClass('tb_window_nav').html('<i class="fa fa-step-forward" aria-hidden="true"></i>').click(function () {
                var thickboxFrame = document.getElementById("TB_iframeContent");
                thickboxFrame.contentWindow.history.forward();
            });

            back_button.appendTo(nav_container);
            forward_button.appendTo(nav_container);

            nav_container.prependTo('#TB_window');

            jQuery('#TB_ajaxWindowTitle').text('');
        });
    }

    /* Add listeners for oauth close button */
    jQuery(document).on('tb_unload', '#TB_window', function (e) {
        jQuery('#inbound-thickbox-nav').remove();
    });


});