function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getUrlVar(name) {
    return getUrlVars()[name];
}

jQuery(document).ready(function ($) {
    // Getting URL var by its nam
    var byName = getUrlVar('tab');
    setTimeout(function () {
        $('#poststuff').fadeIn(300);
        $('#postcustom .hndle span').text('Raw Data');
    }, 300);

    // Set setting Tab
    setTimeout(function () {
        jQuery("#" + byName).click();
    }, 300);

    /* Update Setting URL */
    jQuery("body").on('click', '.nav-tab', function () {
        var this_id = jQuery(this).attr('id');
        if (history.pushState) {
            var newurl = window.location.href.replace(/tab=([^"]*)/g, 'tab=' + this_id);
            var current_tab = newurl.match(/tab=([^"]*)/g);
            if (typeof (current_tab) != "undefined" && current_tab != null && current_tab != "") {
                var current_tab = current_tab[0].replace("tab=", "");
                window.history.pushState({path: newurl}, '', newurl);
            } else {
                var newurl = window.location.href + '&tab=' + this_id;
                window.history.pushState({path: newurl}, '', newurl);
            }

        }
    });

    var rawData = jQuery("#postcustom");
    rawData.show();
    jQuery("#raw-data-display").append(rawData);

    jQuery('.row-actions').each(function () {
        var jQuerylist = jQuery(this);
        var jQueryfirstChecked = jQuerylist.parent().parent().find('.column-first-name');

        if (!jQueryfirstChecked.html())
            return;

        jQuerylist.appendTo(jQueryfirstChecked);
    });
    var session_count = jQuery('.wpleads-conversion-tracking-table').length;
    jQuery('.conversion-tracking-header h2 span.visit-number').each(function (i) {
        var sess = session_count - i;
        jQuery(this).text(sess);

    });
    var session_view = jQuery.cookie("lead-session-view-choice");
    setTimeout(function () {
        jQuery("#wplead_metabox_conversion h3, #wplead_metabox_conversion .hndle").unbind('click');
        if (session_view === "hide_sessions") {
            jQuery('.minimize-paths').click();
        }
    }, 800);

    jQuery("body").on('mouseenter', '.recent-conversion-item', function () {
        $(this).find('.lead-timeline-img').addClass('active-hover');
    });

    jQuery("body").on('mouseleave', '.recent-conversion-item', function () {
        $(this).find('.lead-timeline-img').removeClass('active-hover');
    });

    jQuery("body").on('click', '.minimize-paths', function () {

        var text = jQuery(this).text();
        if (text === "Shrink Session View") {
            jQuery('.session-item-holder, .time-on-page-label').hide();

            jQuery(".toggle-conversion-list").text("+");
            jQuery(this).text("Expand Session View");
            jQuery.cookie("lead-session-view-choice", "hide_sessions", {path: '/', expires: 7});

        } else {
            jQuery('.session-item-holder,.time-on-page-label').show();
            jQuery(".toggle-conversion-list").text("-");
            jQuery(this).text("Shrink Session View");
            jQuery.cookie("lead-session-view-choice", "show_sessions", {path: '/', expires: 7});
        }
    });

    jQuery("#wpleads_lead_tab_main_inner").fadeIn(1000);
    jQuery('.touchpoint-value').each(function () {
        var touch_val = jQuery(this).text();

        if (touch_val != "0") {
            jQuery(this).parent().show();
        }
        jQuery(this).find(".touchpoint-minute").show();
    });

    var hideempty = jQuery("#touch-point span:visible").length;
    var hideago = jQuery("#session-time-since:visible").length;


    jQuery("#submitdiv .hndle").text("Update Lead Information");
    var html = '<a class="add-new-h2" href="edit.php?post_type=wp-lead">Back</a>';
    jQuery('.add-new-h2').before(html);

    //populate country
    jQuery('.wpleads-country-dropdown').val(jQuery('#hidden-country-value').val());

    jQuery('.add-new-link').on('click', function (e) {
        var count = jQuery('#wpleads_websites-container .wpleads_link').size();
        var true_count = count + 1;
        var html = '<input name="wpleads_websites[' + count + ']" class="wpleads_link" type="text" size="70" value="" />';
        jQuery('#wpleads_websites-container').append(html);
    });

    jQuery('.wpleads_remove_link').live('click', function (e) {
        var this_id = jQuery(this).attr('id');
        jQuery('#wpleads_websites-' + this_id).remove();
    });

    jQuery.fn.tagcloud.defaults = {
        size: {start: 10, end: 25, unit: 'pt'},
        color: {start: '#bbb', end: '#2a95c5'}
    };

    jQuery(function () {
        jQuery('#lead-tag-cloud a').tagcloud();
    });

    jQuery('#wpleads_main_container input, #wpleads_main_container textarea, #wpleads_main_container select').each(
        /*
         */
        function () {
            // hide empty fields
            if (jQuery(this).val()) {
                jQuery(this).parent().parent().show();
            }

            if (!jQuery(this).val()) {
                jQuery(this).parent().parent().hide().addClass('hidden-lead-fields');
            }
        }
    );


    if (jQuery('#wpleads-td-wpleads_websites').hasClass('hidden-lead-fields')) {
        jQuery('.wpleads_websites').hide().addClass('hidden-lead-fields');
    }

    jQuery("#activity-data-display nav li").click(function () {
        jQuery(".active").removeClass("active");
        jQuery(this).addClass("active");
    });

    jQuery("#show-hidden-fields").click(function () {
        var $this = $(this);

        if ($this.text().trim() == "Show Empty Fields") {
            $this.text("Hide Empty Fields");
        } else {
            $this.text("Show Empty Fields");
        }
        jQuery(".hidden-lead-fields").toggle();
        jQuery("#add-notes").hide();
    });

    var notesarea = jQuery("#wpleads-td-wpleads_notes").text();
    if (notesarea === "") {
        jQuery("#wpleads-td-wpleads_notes textarea").hide().addClass('hidden-lead-fields');
        var expandnotes = "<span id='add-notes'>No Notes. Click here to add some.</span>";
        jQuery(expandnotes).appendTo(jQuery("#wpleads-td-wpleads_notes"));

    }

    jQuery("#add-notes").click(function () {
        jQuery("#wpleads-td-wpleads_notes textarea").toggle();
        jQuery("#add-notes").hide();
    });

    jQuery(".conversion-tracking-header").on("click", function (event) {
        //	alert("yes");
        var link = jQuery(this).find(".toggle-conversion-list");
        var conversion_log = jQuery(this).parent().find(".session-item-holder, .session-stats, .time-on-page-label").toggle();

        if (jQuery(conversion_log).is(":visible")) {
            link.text('-');
        } else {
            link.text('+');
        }
    });

    jQuery("body").on('click', '#conversion-total', function () {
        jQuery("#tabs-wpleads_lead_tab_conversions").click();
    });


    var textchange = jQuery("#timestamp").html().replace("Published", "Created");
    jQuery('#timestamp').html(textchange);
    var pageviews = jQuery(".marker").size();
    var totalconversions = jQuery(".wpleads-conversion-tracking-table").size();
    var conversion_empty = jQuery("#conversion-total").text();
    var views_empty = jQuery("#p-view-total").text();
    if (views_empty === "") {
        jQuery("#p-view-total").text(pageviews);
    }

    var conversion_empty = jQuery("#conversion-total").text();
    jQuery('h2 .nav-tab').eq(0).css("margin-left", "10px");

    jQuery("#message.updated").text("Lead Updated").css("padding", "10px");
    jQuery('.wpleads-conversion-tracking-table').each(function () {
        var number_of_pages = jQuery(this).find('.lp-page-view-item').size();
        jQuery(this).find("#pages-view-in-session").text(number_of_pages);
        if (number_of_pages == 1) {
            jQuery(this).find(".session-stats-header").hide();
            jQuery(this).find("#session-pageviews").hide();
        }
    });

    // view toggles
    jQuery(".view-this-lead-session a").on("click", function (event) {
        var s_number = jQuery(this).attr("rel");
        var correct_session = ".session_id_" + s_number;
        console.log(correct_session);
        jQuery(".conversion-session-view").hide();
        jQuery(correct_session).show();
    });


    // Sort by date. http://stackoverflow.com/questions/7211704/jquery-order-by-date-in-data-attribute
    jQuery(document).ready(function ($) {
        jQuery("#activity-data-display .recent-conversion-item").sort(function (a, b) {
            return new Date(jQuery(a).attr("data-date")) > new Date(jQuery(b).attr("data-date"));
        }).each(function () {
            var clone = jQuery(this).clone().addClass("cloned-item");
            jQuery("#all-lead-history").append(clone);

        })
        //jQuery(".cloned-item").wrap("<li>");
        jQuery(".lead-item-num, .lead-activity").hide();


        var reviews = jQuery('#all-lead-history .recent-conversion-item');
        reviews.tsort({attr: 'data-date', order: 'desc'});
        jQuery('#newest-event').click(function () {
            var which_sort = jQuery(".event-order-list").attr("data-change-sort");
            var the_list = jQuery(which_sort + ' .recent-conversion-item');
            the_list.tsort({attr: 'data-date', order: 'desc'});
            jQuery('.lead-sort-active').removeClass('lead-sort-active');
            jQuery(this).addClass('lead-sort-active');
            if(which_sort == '#lead-searches'){
                var errorCount = jQuery('#search-error-count').detach();
                jQuery('#lead-searches').append(errorCount);
            }
        });

        jQuery('#oldest-event').click(function () {
            var which_sort = jQuery(".event-order-list").attr("data-change-sort");
            var the_list = jQuery(which_sort + ' .recent-conversion-item');
            the_list.tsort({attr: 'data-date', order: 'asc'});
            jQuery('.lead-sort-active').removeClass('lead-sort-active');
            jQuery(this).addClass('lead-sort-active');
            if(which_sort == '#lead-searches'){
                var errorCount = jQuery('#search-error-count').detach();
                jQuery('#lead-searches').append(errorCount);
            }
        });

        jQuery('#highest').click(function () {
            reviews.tsort({attr: 'data-rating', order: 'desc'});
        });

        jQuery('#lowest').click(function () {
            reviews.tsort({attr: 'data-rating', order: 'asc'});
        });
    });

    // activity toggles
    jQuery("body").on('click', '.lead-activity-toggle', function (event) {
        event.preventDefault();
        var toggle_this = jQuery(this).attr("href");
        jQuery(".event-order-list").attr("data-change-sort", toggle_this);
        jQuery(".lead-activity").hide();
        jQuery("#all-lead-history").hide();
        var which_sort = jQuery(".event-order-list").attr("data-change-sort");
        var the_list = jQuery(which_sort + ' .recent-conversion-item');
        the_list.tsort({attr: 'data-date', order: 'desc'});
        jQuery(toggle_this).fadeIn(700);
        jQuery(".lead-item-num").show();
        if(which_sort == '#lead-searches'){
            var errorCount = jQuery('#search-error-count').detach();
            jQuery('#lead-searches').append(errorCount);
        }
    });

    jQuery("body").on('click', '.lead-activity-show-all', function () {
        event.preventDefault();
        jQuery(".lead-activity").hide();
        jQuery(".event-order-list").attr("data-change-sort", "#all-lead-history");
        jQuery("#all-lead-history").fadeIn(700);
        jQuery(".lead-item-num").hide();
    });


    jQuery(".possible-map-value").on("click", function (event) {
        jQuery(".toggle-val").removeClass("toggle-val");
        jQuery(this).toggleClass("toggle-val");
    });

    var lead_status = jQuery('select[name=wp_lead_status]').val();

    if (lead_status === "new") {
        var post_id = jQuery("#post_ID").val();
        jQuery.ajax({
            type: 'POST',
            url: wp_lead_map.ajaxurl,
            context: this,
            data: {
                action: 'wp_leads_auto_mark_as_read',
                page_id: post_id
            },

            success: function (data) {
                var self = this;
            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                alert("Error thrown not sure why");
            }
        });

    }


    jQuery('.lead_status_dropdown option').click(function () {
        jQuery('.lead_status_dropdown option').each(function () {
            if (jQuery(this).hasClass('selected')) {
                jQuery(this).removeClass('selected');
            }
        });
        jQuery(this).addClass('selected');
        jQuery('.dropdown h1').html($(this).html()).removeClass().addClass('selected-' + (jQuery(this).index() + 1));
    });
	
	/**if there's lists waiting for double optin, prevent the lead status from changing**/
	jQuery('input#publish[name="save"]').click(function(e){
		if(jQuery('.lead_status_dropdown').val() != 'double-optin' && jQuery('#wplead_list_category-need-double-optin').val() != null){
			e.preventDefault();
			alert('There are still lists that need to be opted into');
		}
		
	});

});
