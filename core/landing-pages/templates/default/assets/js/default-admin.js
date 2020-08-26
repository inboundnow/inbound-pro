jQuery(document).ready(function ($) {
jQuery('body').on('change', '#default-conversion-area-placement' , function () {
        var input = jQuery(this).attr('value');
        if (input == 'right') {
            alert("yes right");
        } else {
            alert("nope");
        }

    });
});