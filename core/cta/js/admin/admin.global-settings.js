
function getUrlVars() {
           var vars = [], hash;
           var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
           for(var i = 0; i < hashes.length; i++)
           {
             hash = hashes[i].split('=');
             vars.push(hash[0]);
             vars[hash[0]] = hash[1];
           }
           return vars;
};

function getUrlVar(name){
           return getUrlVars()[name];
};

jQuery(document).ready(function($) {

  // Getting URL var by its nam
  var byName = getUrlVar('tab');

  // Set setting Tab
  setTimeout(function() {
      jQuery("#" + byName).click();
  }, 300);
    /* Update Setting URL */
  jQuery("body").on('click', '.nav-tab', function () {
    var this_id = jQuery(this).attr('id');
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