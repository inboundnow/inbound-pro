jQuery(document).ready( function($) {
    /* Let's use ajax to discover and set the sends/opens/conversions */
    jQuery( jQuery('.td-col-sends').get() ).each( function( $ ) {

        var email_id = jQuery(this).attr('data-email-id');
        var email_status = jQuery(this).attr('data-email-status');

        /* set unsent emails to zero */
        if (email_status=='unsent' ) {
            jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-unsubs[data-email-id="' + email_id + '"]').text('0');
            return;
        }

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: 'inbound_load_email_row_stats',
                email_id: email_id
            },
            dataType: 'json',
            timeout: 10000,
            success: function (response) {
                console.log(response);
                if (!Object.keys(response).length) {
                    response['totals'] = [];
                    response['totals']['sent'] = 0;
                    response['totals']['opens'] = 0;
                    response['totals']['clicks'] = 0;
                    response['totals']['unsubs'] = 0;
                }

                jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text(response['totals']['sent']);
                jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text(response['totals']['opens']);
                jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text(response['totals']['clicks']);
                jQuery( '.td-col-unsubs[data-email-id="' + email_id + '"]').text(response['totals']['unsubs']);

            },
            error: function (request, status, err) {
                //alert(status);
            }
        });
    });
});