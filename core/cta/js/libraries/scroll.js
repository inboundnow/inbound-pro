// For sliding CTA

function getScrollY() {
    scrOfY = 0;
    if( typeof( window.pageYOffset ) == "number" ) {
        scrOfY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        scrOfY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        scrOfY = document.documentElement.scrollTop;
    }
    return scrOfY;
}

jQuery(function($){
    var wp_cta_closed                = false;
    var wp_cta_hidden                = true;
    var wp_cta_ga_track_view         = true;
    var wp_cta_ga                    = typeof(_gaq ) != 'undefined';
    var wp_cta_ga_opt_noninteraction = wp_cta_slideout.ga_opt_noninteraction == 1;
    var speed = parseInt(wp_cta_slideout.speed);

    function wp_cta_show_box() {
        var lastScreen = false;
        if (wp_cta_slideout.offset_element && $(wp_cta_slideout.offset_element) ) {
            if ($(wp_cta_slideout.offset_element).length > 0 && wp_cta_slideout.offset_element != "") {
                lastScreen = getScrollY() + $(window).height() > $(wp_cta_slideout.offset_element).offset().top;
            } else {
                lastScreen = getScrollY() + $(window).height() >= $(document).height() * wp_cta_slideout.offset_percent / 100;
            }
        } else {
            lastScreen = ( getScrollY() + $(window).height() >= $(document).height() * wp_cta_slideout.offset_percent / 100 );
        }
        if (lastScreen && !wp_cta_closed) {
            if (wp_cta_slideout.animation == "fade") {
                $("#wp_cta_box").fadeIn("slow");
            } else if ( wp_cta_slideout.position == 'left' ) {
                $("#wp_cta_box").stop().animate({left:wp_cta_slideout.css_side+"px"}, speed);
            } else {
                $("#wp_cta_box").stop().animate({right:wp_cta_slideout.css_side+"px"}, speed);
            }
            wp_cta_hidden = false;
            if ( wp_cta_ga && wp_cta_ga_track_view && wp_cta_slideout.ga_track_views == 1 ) {
                _gaq.push( [ '_trackEvent', 'upPrev', wp_cta_slideout.title, null, 0, wp_cta_ga_opt_noninteraction ] );
                wp_cta_ga_track_view = false;
            }
        }
        else if (wp_cta_closed && getScrollY() == 0) {
            wp_cta_closed = false;
        }
        else if (!wp_cta_hidden) {
            wp_cta_hidden = true;
            // on Hide
            if (wp_cta_slideout.animation == "fade") {
                $("#wp_cta_box").fadeOut("slow");
            } else if ( wp_cta_slideout.keep_open == 'yes' ) {
                console.log('keep open');
            } else if ( wp_cta_slideout.position == 'left' ) {
                $("#wp_cta_box").stop().animate({left:"-" + ( wp_cta_slideout.css_width + wp_cta_slideout.css_side + 50 ) + "px"}, 6000);
            } else {
                $("#wp_cta_box").stop().animate({right:"-" + ( wp_cta_slideout.css_width + wp_cta_slideout.css_side + 50 ) + "px"}, 6000);
            }
        }
    }
    $(window).bind('scroll', function() {
        wp_cta_show_box();
    });
    if ($(window).height() == $(document).height()) {
        wp_cta_show_box();
    }
    $("#wp_cta_close").click(function() {
        $("#wp_cta_box").remove(); // kill CTA for now
        if (wp_cta_slideout.animation == "fade") {
            $("#wp_cta_box").fadeOut("slow");
        } else if ( wp_cta_slideout.position == 'left' ) {
            $("#wp_cta_box").stop().animate({left:"-" + ( wp_cta_slideout.css_width + 50 ) + "px"},  speed);
        } else {
            $("#wp_cta_box").stop().animate({right:"-" + ( wp_cta_slideout.css_width + 50 ) + "px"},  speed);
        }
        wp_cta_closed = true;
        wp_cta_hidden = true;
        return false;
    });
    $('#wp_cta_box').addClass( wp_cta_slideout.compare );
    if( wp_cta_slideout.url_new_window == 1 || wp_cta_slideout.ga_track_clicks == 1 ) {
        $('#wp_cta_box a').click(function() {
            if ( wp_cta_slideout.url_new_window == 1) {
                window.open($(this).attr('href'));
            }
            if ( wp_cta_ga && wp_cta_slideout.ga_track_clicks == 1 ) {
                _gaq.push( [ '_trackEvent', 'WP_CTA', wp_cta_slideout.title, $(this).html(), 1, wp_cta_ga_opt_noninteraction ] );
            }
            if ( wp_cta_slideout.url_new_window == 1) {
                return false;
            }
        });
    }
});