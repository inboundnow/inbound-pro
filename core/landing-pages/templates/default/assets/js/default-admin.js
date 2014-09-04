jQuery(document).ready(function ($) {
jQuery('#default-conversion-area-placement').live('change', function () {
        var input = jQuery(this).attr('value');
        if (input == 'right') {
            alert("yes right");
        } else {
            alert("nope");
        }

    });
});