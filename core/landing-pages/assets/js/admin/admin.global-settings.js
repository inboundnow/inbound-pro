function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

function getUrlVar(name) {
    return getUrlVars()[name];
};

jQuery(document).ready(function ($) {

    jQuery(document).ready(function() {
        jQuery('.tooltip').tooltipster({
            contentAsHTML: true,
            interactive: true,
            maxWidth: 350,
            position: "right",
            theme: "tooltipster-noir"
        });
    });

    // Getting URL var by its nam
    var byName = getUrlVar('tab');

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

    setTimeout(function() {
        var getoption = document.URL.split('&option=')[1];
        var showoption = "#" + getoption;
        jQuery(showoption).click();
    }, 100);

    /* Navigate tabs */
    jQuery('.lp-nav-tab').live('click', function() {
        var this_id = this.id.replace('tabs-','');
        jQuery('.lp-tab-display').css('display','none');
        jQuery('#'+this_id).css('display','block');
        jQuery('.lp-nav-tab').removeClass('nav-tab-special-active');
        jQuery('.lp-nav-tab').addClass('nav-tab-special-inactive');
        jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');
        jQuery('#id-open-tab').val(this_id);
    });
    var form_sys = jQuery("#sys-inbound-form");
    jQuery("#in-sys-info").after(form_sys);
    jQuery("#sys-inbound-form").show();

});