/* This is the customizer parent window containing the editor and preview frames */
var InboundCustomizerParent = (function () {

  var _privateMethod = function () {};

  var myObject = {
    init:  function () {
        console.log('parent init');
        jQuery("#wp-admin-bar-edit a").text("Main Edit Screen");
    },
    togglePreviewReload:  function () {
        /* triggered from editor frame */
        setTimeout(function() {
          console.log('reload preview');
          document.getElementById('wp-cta-live-preview').src = document.getElementById('wp-cta-live-preview').src
          //document.getElementById('wp-cta-live-preview').contentDocument.location.reload(true);

        }, 1500);
    }
  };

  return myObject;

})();

jQuery(document).ready(function($) {
   InboundCustomizerParent.init();
});