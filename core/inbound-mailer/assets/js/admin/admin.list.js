
function inbound_stats_lookup( email_ids, i , callback , response ) {

    var end = false;
    var old_email_id = email_ids[i - 1];
    var email_id = email_ids[i];

    if (!email_ids[i] && i != -1){
        end = true;
    }

    if (typeof response == 'object' && response && typeof response['totals'] != 'undefined'  ) {
        jQuery( '.td-col-sends[data-email-id="' + old_email_id + '"]').find('.email-report-link').text(response['totals']['sent']).end().find( '.col-ajax-spinner' ).remove();
        jQuery( '.td-col-opens[data-email-id="' + old_email_id + '"]').find('.email-report-link').text(response['totals']['opens']).end().find( '.col-ajax-spinner' ).remove();
        jQuery( '.td-col-clicks[data-email-id="' + old_email_id + '"]').find('.email-report-link').text(response['totals']['clicks']).end().find( '.col-ajax-spinner' ).remove();
        jQuery( '.td-col-unsubs[data-email-id="' + old_email_id + '"]').find('.email-report-link').text(response['totals']['unsubs']).end().find( '.col-ajax-spinner' ).remove();
        jQuery( '.td-col-mutes[data-email-id="' + old_email_id + '"]').find('.email-report-link').text(response['totals']['mutes']).end().find( '.col-ajax-spinner' ).remove();
    }

    i++;

    if (end){
        return true;
    }

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
            action: 'inbound_load_email_row_stats',
            email_id: email_id,
            fast_ajax: true,
            load_plugins: ["_inbound-now/inbound-pro.php","inbound-pro/inbound-pro.php"]
        },
        dataType: 'json',
        timeout: 20000,
        success: function (response) {
            callback( email_ids, i, callback , response);
        },
        error: function (request, status, err) {
            var response = {};
            console.log(err);
            callback(email_ids, i, callback , response);
        }
    });
}

jQuery(document).ready( function($) {

    /* Let's use ajax to discover and set the sends/opens/conversions */

    var email_ids = [];
    var i = 0
    jQuery( jQuery('.td-col-sends').get() ).each( function( $ ) {
        var email_id = jQuery(this).attr('data-email-id');
        var email_status = jQuery(this).attr('data-email-status');

        if (email_status=='unsent' ) {
            jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-unsubs[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-mutes[data-email-id="' + email_id + '"]').text('0');
        } else  if (email_status=='sending') {
            jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text('-');
            jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text('-');
            jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text('-');
            jQuery( '.td-col-unsubs[data-email-id="' + email_id + '"]').text('-');
            jQuery( '.td-col-mutes[data-email-id="' + email_id + '"]').text('-');
        } else  if (email_status=='draft') {
            jQuery( '.td-col-sends[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-opens[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-clicks[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-unsubs[data-email-id="' + email_id + '"]').text('0');
            jQuery( '.td-col-mutes[data-email-id="' + email_id + '"]').text('0');
        } else {
            email_ids[i] = email_id;
            i++;
        }
    });

    inbound_stats_lookup(  email_ids, 0 , inbound_stats_lookup , 'start' );
});
