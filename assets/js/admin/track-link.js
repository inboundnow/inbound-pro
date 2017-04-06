jQuery( document ).ready( function( jQuery ) {

    if (typeof wpLink == 'undefined') {
        return;
    }

    jQuery('#link-options').append('<div style="margin-left: 61px">' +
        '<label>' +
            '<input type="checkbox" name="inbound-track-link" id="inbound-track-link" value="true">' +
        '</label>' +
        '<span> <b>Track clicks through Inbound Pro?</b></span>' +
    '</div>'
    ).css('max-width','70%');

    wpLink.getAttrs = function() {
        wpLink.correctURL();
        return {
            class:      jQuery( '#inbound-track-link' ).prop( 'checked' ) ? 'inbound-track-link' : '',
            href:       jQuery.trim( jQuery( '#wp-link-url' ).val() ),
            target:     jQuery( '#wp-link-target' ).prop( 'checked' ) ? '_blank' : ''
        };
    }

    jQuery('#most-recent-results').css('top','241px');
});