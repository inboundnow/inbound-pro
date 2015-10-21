jQuery(document).ready( function($) {
    /* Let's use ajax to discover and set the sends/opens/conversions */
    jQuery( jQuery('.td-col-sends').get() ).each( function( $ ) {

        var email_id = jQuery(this).attr('data-email-id');

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
                }

                jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text(response['totals']['sent']);
                jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text(response['totals']['opens']);
                jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text(response['totals']['clicks']);

            },
            error: function (request, status, err) {
                //alert(status);
            }
        });
    });
});