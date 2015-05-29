
/* Get url param */
function getURLParameter(name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
}

jQuery(document).ready(function($) {

  // Getting URL var by its nam
  var byName = getURLParameter('tab');

  // Set setting Tab
  setTimeout(function() {
      jQuery("#" + byName).click();
  }, 300);

    /* Update Setting URL */
  jQuery("body").on('click', '.nav-tab', function () {
    var this_id = jQuery(this).attr('id');
    var show = "#" + this_id.replace('tabs-', '');
    jQuery('.wpl-tab-display').css('display','none');
    jQuery('.wpl-nav-tab').removeClass('nav-tab-special-active');
    jQuery('.wpl-nav-tab').addClass('nav-tab-special-inactive');
    jQuery("#" + this_id).addClass('nav-tab-special-active');
    jQuery(show).css('display','block');
    if (history.pushState) {
        var newurl = window.location.href.replace(/tab=([^"]*)/g, 'tab=' + this_id);
        var current_tab = newurl.match(/tab=([^"]*)/g);
        if (typeof (current_tab) != "undefined" && current_tab != null && current_tab != "") {
          var current_tab = current_tab[0].replace("tab=","");
          window.history.pushState({path:newurl},'',newurl);
        } else {
          var newurl = window.location.href + '&tab=' + this_id;
          window.history.pushState({path:newurl},'',newurl);
        }

    }
  });

});